<?php

namespace App\Http\Controllers\api;

use App\Enums\AttendanceCode;
use App\Enums\ROLES;
use App\Http\Controllers\BaseController;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends BaseController
{
    public function checkIn(Request $request): JsonResponse
    {
        $this->normalizeAttendanceCode($request);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', $this->attendanceUserExistsRule()],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'attendance_code' => ['nullable', Rule::in(AttendanceCode::values())],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $date = $validated['date'] ?? Carbon::now()->toDateString();

        $attendance = Attendance::firstOrNew([
            'user_id' => $validated['user_id'],
            'date' => $date,
        ]);

        if (! empty($attendance->check_in_at)) {
            return response()->json([
                'message' => 'L\'employé a déjà enregistré son entrée pour cette date.',
                'attendance' => $this->formatAttendance($attendance),
            ], 409);
        }

        $attendanceCode = $validated['attendance_code'] ?? $attendance->attendance_code ?? AttendanceCode::PRESENT->value;
        $attendanceCodeEnum = AttendanceCode::from($attendanceCode);

        $attendance->fill([
            'attendance_code' => $attendanceCode,
            'check_in_at' => now(),
            'is_present' => $attendanceCodeEnum->isPresentByDefault(),
            'status' => $attendanceCodeEnum->isPresentByDefault() ? 'present' : 'absent',
            'work_units' => $attendanceCodeEnum->defaultWorkUnits(),
            'note' => $validated['note'] ?? $attendance->note,
        ]);
        $attendance->save();

        return response()->json([
            'message' => 'Entrée enregistrée.',
            'attendance' => $this->formatAttendance($attendance),
        ], 200);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $this->normalizeAttendanceCode($request);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', $this->attendanceUserExistsRule()],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'attendance_code' => ['nullable', Rule::in(AttendanceCode::values())],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $date = $validated['date'] ?? Carbon::now()->toDateString();

        $attendance = Attendance::where('user_id', $validated['user_id'])
            ->where('date', $date)
            ->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'Aucune présence trouvée pour cet employé pour cette date.',
            ], 404);
        }

        if (! empty($attendance->check_out_at)) {
            return response()->json([
                'message' => 'La sortie a déjà été enregistrée pour cette date.',
                'attendance' => $this->formatAttendance($attendance),
            ], 409);
        }

        $attendanceCode = $validated['attendance_code'] ?? $attendance->attendance_code ?? AttendanceCode::PRESENT->value;
        $attendanceCodeEnum = AttendanceCode::from($attendanceCode);

        $attendance->check_out_at = now();
        $attendance->attendance_code = $attendanceCode;
        $attendance->is_present = $attendanceCodeEnum->isPresentByDefault();
        $attendance->status = $attendanceCodeEnum->isPresentByDefault() ? 'present' : 'absent';
        $attendance->work_units = $attendanceCodeEnum->defaultWorkUnits();
        $attendance->note = $validated['note'] ?? $attendance->note;
        $attendance->save();

        return response()->json([
            'message' => 'Sortie enregistrée.',
            'attendance' => $this->formatAttendance($attendance),
        ], 200);
    }

    public function markAbsence(Request $request): JsonResponse
    {
        $this->normalizeAttendanceCode($request);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', $this->attendanceUserExistsRule()],
            'date' => ['required', 'date_format:Y-m-d'],
            'attendance_code' => ['nullable', Rule::in(AttendanceCode::values())],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $attendance = Attendance::firstOrNew([
            'user_id' => $validated['user_id'],
            'date' => $validated['date'],
        ]);

        $attendanceCode = $validated['attendance_code'] ?? AttendanceCode::UNPAID_ABSENCE->value;

        $attendance->fill([
            'attendance_code' => $attendanceCode,
            'check_in_at' => $attendance->check_in_at,
            'check_out_at' => $attendance->check_out_at,
            'is_present' => false,
            'status' => 'absent',
            'work_units' => 0,
            'note' => $validated['note'] ?? $attendance->note,
        ]);
        $attendance->save();

        return response()->json([
            'message' => 'Absence enregistrée.',
            'attendance' => $this->formatAttendance($attendance),
        ], 200);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', $this->attendanceUserExistsRule()],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $query = Attendance::query()->with('user');

        if (! empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (! empty($validated['date'])) {
            $query->whereDate('date', $validated['date']);
        }

        if (! empty($validated['from'])) {
            $query->whereDate('date', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->whereDate('date', '<=', $validated['to']);
        }

        $attendances = $query
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn(Attendance $attendance) => $this->formatAttendance($attendance));

        return response()->json([
            'data' => $attendances,
        ], 200);
    }

    public function markDay(Request $request): JsonResponse
    {
        $this->normalizeAttendanceCode($request);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', $this->attendanceUserExistsRule()],
            'date' => ['required', 'date_format:Y-m-d'],
            'attendance_code' => ['required', Rule::in(AttendanceCode::values())],
            'is_present' => ['nullable', 'boolean'],
            'work_units' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'check_in_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'check_out_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $attendance = Attendance::firstOrNew([
            'user_id' => $validated['user_id'],
            'date' => $validated['date'],
        ]);

        $attendanceCodeEnum = AttendanceCode::from($validated['attendance_code']);
        $isPresent = array_key_exists('is_present', $validated)
            ? (bool) $validated['is_present']
            : $attendanceCodeEnum->isPresentByDefault();
        $workUnits = array_key_exists('work_units', $validated)
            ? (float) $validated['work_units']
            : $attendanceCodeEnum->defaultWorkUnits();

        $attendance->fill([
            'attendance_code' => $validated['attendance_code'],
            'is_present' => $isPresent,
            'status' => $isPresent ? 'present' : 'absent',
            'work_units' => $workUnits,
            'check_in_at' => $validated['check_in_at'] ?? null,
            'check_out_at' => $validated['check_out_at'] ?? null,
            'note' => $validated['note'] ?? $attendance->note,
        ]);

        $attendance->save();

        return response()->json([
            'message' => 'Journée de pointage enregistrée.',
            'attendance' => $this->formatAttendance($attendance),
        ], 200);
    }

    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', $this->attendanceUserExistsRule()],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $to = $validated['to'] ?? Carbon::now()->endOfMonth()->toDateString();

        $query = Attendance::query()
            ->with('user')
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to);

        if (! empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        $attendances = $query->get();

        $totalsByCode = $attendances
            ->groupBy(fn(Attendance $attendance) => $attendance->attendance_code ?? AttendanceCode::UNPAID_ABSENCE->value)
            ->map(static fn($records) => $records->count())
            ->sortKeys()
            ->all();

        $perUser = $attendances
            ->groupBy('user_id')
            ->map(function ($records) {
                /** @var Attendance $first */
                $first = $records->first();

                return [
                    'user_id' => $first->user_id,
                    'employee_name' => $first->user?->name,
                    'present_days' => $records->where('is_present', true)->count(),
                    'absent_days' => $records->where('is_present', false)->count(),
                    'work_units_total' => (float) $records->sum('work_units'),
                    'codes' => $records
                        ->groupBy(fn(Attendance $attendance) => $attendance->attendance_code ?? AttendanceCode::UNPAID_ABSENCE->value)
                        ->map(static fn($items) => $items->count())
                        ->sortKeys()
                        ->all(),
                ];
            })
            ->values();

        return response()->json([
            'period' => [
                'from' => $from,
                'to' => $to,
            ],
            'totals' => [
                'present_days' => $attendances->where('is_present', true)->count(),
                'absent_days' => $attendances->where('is_present', false)->count(),
                'work_units_total' => (float) $attendances->sum('work_units'),
                'codes' => $totalsByCode,
            ],
            'users' => $perUser,
        ], 200);
    }

    protected function formatAttendance(Attendance $attendance): array
    {
        $attendanceCode = $attendance->attendance_code
            ?? ($attendance->is_present ? AttendanceCode::PRESENT->value : AttendanceCode::UNPAID_ABSENCE->value);
        $attendanceCodeEnum = AttendanceCode::tryFrom(strtoupper($attendanceCode));

        $status = $attendance->status ?? (
            ! empty($attendance->check_in_at) ? 'present' : 'absent'
        );

        return [
            'id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'employee' => $attendance->user,
            'date' => $attendance->date?->format('Y-m-d'),
            'is_present' => (bool) ($attendance->is_present ?? ! empty($attendance->check_in_at)),
            'status' => $status,
            'attendance_code' => $attendanceCode,
            'attendance_code_label' => $attendanceCodeEnum?->label() ?? $attendanceCode,
            'work_units' => (float) ($attendance->work_units ?? 0),
            'heure_entree' => $attendance->check_in_at?->format('H:i:s'),
            'heure_sortie' => $attendance->check_out_at?->format('H:i:s'),
            'check_in_at' => $attendance->check_in_at?->format('Y-m-d H:i:s'),
            'check_out_at' => $attendance->check_out_at?->format('Y-m-d H:i:s'),
            'note' => $attendance->note,
        ];
    }

    protected function normalizeAttendanceCode(Request $request): void
    {
        if (! $request->filled('attendance_code')) {
            return;
        }

        $request->merge([
            'attendance_code' => strtoupper((string) $request->input('attendance_code')),
        ]);
    }

    protected function attendanceUserExistsRule()
    {
        return Rule::exists('users', 'id')->where(function (Builder $query) {
            $query->whereIn('role', [
                ROLES::EMPLOYEE->value
            ]);
        });
    }
}
