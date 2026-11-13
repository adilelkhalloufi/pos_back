# POS Backend

A small Laravel backend for a point-of-sale system.

## Features

- REST API for inventory, sales, customers, and invoices
- Laravel Sanctum auth support
- CSV import and reporting helpers
- Attendance API with daily work codes (P, R, FE, CP, M, RCP, FT, AP, A, DC, ...)

## Attendance endpoints

Authenticated routes:

- `GET /api/attendance`
- `GET /api/attendance/summary?from=YYYY-MM-DD&to=YYYY-MM-DD&user_id=...`
- `POST /api/attendance/check-in`
- `POST /api/attendance/check-out`
- `POST /api/attendance/mark-absence`
- `POST /api/attendance/mark-day` (set one daily code and optional work units)

## Frontend guide (what to build)

### 1) Employees attendance page

Create a page like **"Pointage Employés"** with:

- Search input by employee name
- Employee list/table (name, role, status)
- Month selector (ex: January 2026)
- Action button **"Create / Update attendance"** for selected employee/day

Suggested flow:

1. Load employees from your users endpoint.
2. Filter list locally while typing in search.
3. When user selects an employee + day + code, call:
   - `POST /api/attendance/mark-day`

Example payload:

```json
{
  "user_id": 12,
  "date": "2026-01-15",
  "attendance_code": "P",
  "note": "normal shift"
}
```

---

### 2) Excel-like monthly attendance view

Create a second page like **"Vue Mensuelle Pointage"** (similar to your Excel):

- Rows = employees
- Columns = days of selected month (1..31)
- Cell value = attendance code (`P`, `R`, `CP`, `A`, `FT`, ...)
- Right-side totals columns per code (`P`, `R`, `Fe`, `CP`, `M`, `RCP`, `FT`, `AP`, `A`, `DC`)

Suggested API usage:

- Get daily records:
  - `GET /api/attendance?from=YYYY-MM-DD&to=YYYY-MM-DD`
- Get totals by period:
  - `GET /api/attendance/summary?from=YYYY-MM-DD&to=YYYY-MM-DD`

Use `summary.users[].codes` to fill totals per employee and `summary.totals.codes` for global totals.

---

### 3) Attendance codes to show in UI

- `P` = Présent
- `R` = Repos
- `FE` = Férié
- `F` = Formation
- `FT` = Formation/FT
- `CP` = Congé payé
- `AP` = Absence payée
- `A` = Absence non payée
- `M` = Maladie
- `CM` = Congé maladie
- `MP` = Maternité
- `RCP` = Récupération
- `DC` = Double shift

Tip: make this list a frontend constant for dropdowns and table legend.

## Quick start

1. Copy `.env.example` to `.env`
2. Run `composer install`
3. Run `php artisan migrate --seed`
4. Start with `php artisan serve`

## Notes

This repository contains the backend API only.
