<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends BaseController
{
    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
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

        $attendance->fill([
            'check_in_at' => now(),
            'is_present' => true,
            'status' => 'present',
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
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
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

        $attendance->check_out_at = now();
        $attendance->is_present = true;
        $attendance->status = 'present';
        $attendance->note = $validated['note'] ?? $attendance->note;
        $attendance->save();

        return response()->json([
            'message' => 'Sortie enregistrée.',
            'attendance' => $this->formatAttendance($attendance),
        ], 200);
    }

    public function markAbsence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $attendance = Attendance::firstOrNew([
            'user_id' => $validated['user_id'],
            'date' => $validated['date'],
        ]);

        $attendance->fill([
            'check_in_at' => $attendance->check_in_at,
            'check_out_at' => $attendance->check_out_at,
            'is_present' => false,
            'status' => 'absent',
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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
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
            ->map(fn (Attendance $attendance) => $this->formatAttendance($attendance));

        return response()->json([
            'data' => $attendances,
        ], 200);
    }

    protected function formatAttendance(Attendance $attendance): array
    {
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
            'heure_entree' => $attendance->check_in_at?->format('H:i:s'),
            'heure_sortie' => $attendance->check_out_at?->format('H:i:s'),
            'check_in_at' => $attendance->check_in_at?->format('Y-m-d H:i:s'),
            'check_out_at' => $attendance->check_out_at?->format('Y-m-d H:i:s'),
            'note' => $attendance->note,
        ];
    }
}
