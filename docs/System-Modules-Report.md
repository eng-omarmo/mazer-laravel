# System Modules Report

This report outlines the key modules, their purpose, and entry points in the system. It includes references to routes and controllers for quick navigation.

## Dashboard
- Purpose: High-level overview and quick access to key areas.
- Route: `dashboard` in routes/web.php:18-20
- View: `resources/views/dashboard.blade.php`

## Account
- Purpose: Manage user profile and password.
- Routes:
  - `profile.edit` in routes/web.php:23
  - `profile.update` in routes/web.php:24
  - `profile.destroy` in routes/web.php:25
- Controller: `app/Http/Controllers/ProfileController.php`
- Views: `resources/views/profile/edit.blade.php` and partials under `resources/views/profile/partials/`

## Employees
- Purpose: CRUD for employee records, with department and documents.
- Routes:
  - `hrm.employees.index` in routes/web.php:28
  - `hrm.employees.create` in routes/web.php:29
  - `hrm.employees.store` in routes/web.php:30
  - `hrm.employees.show` in routes/web.php:31
  - `hrm.employees.edit` in routes/web.php:32
  - `hrm.employees.update` in routes/web.php:33
- Controller: `app/Http/Controllers/EmployeeController.php`

## Departments
- Purpose: Manage departments and assign heads.
- Routes:
  - `hrm.departments.index` in routes/web.php:39
  - `hrm.departments.create` in routes/web.php:40
  - `hrm.departments.store` in routes/web.php:41
  - `hrm.departments.show` in routes/web.php:42
  - `hrm.departments.edit` in routes/web.php:43
  - `hrm.departments.update` in routes/web.php:44
  - `hrm.departments.destroy` in routes/web.php:45
- Controller: `app/Http/Controllers/DepartmentController.php`

## Document Verification
- Purpose: Track and approve/reject employee documents.
- Routes:
  - `hrm.verification.index` in routes/web.php:35
  - `hrm.verification.approve` in routes/web.php:36
  - `hrm.verification.reject` in routes/web.php:37
- Controller: `app/Http/Controllers/DocumentVerificationController.php`

## Leaves
- Purpose: Request and manage employee leaves.
- Routes:
  - `hrm.leave.index` in routes/web.php:47
  - `hrm.leave.create` in routes/web.php:48
  - `hrm.leave.store` in routes/web.php:49
  - `hrm.leave.edit` in routes/web.php:50
  - `hrm.leave.update` in routes/web.php:51
  - `hrm.leave.destroy` in routes/web.php:52
  - `hrm.leave.approve` in routes/web.php:53
  - `hrm.leave.reject` in routes/web.php:54
- Controller: `app/Http/Controllers/LeaveController.php`

## Attendance
- Purpose: Record daily attendance, monthly summary, and export.
- Routes:
  - `hrm.attendance.index` in routes/web.php:65
  - `hrm.attendance.create` in routes/web.php:66
  - `hrm.attendance.store` in routes/web.php:67
  - `hrm.attendance.edit` in routes/web.php:68
  - `hrm.attendance.update` in routes/web.php:69
  - `hrm.attendance.summary` in routes/web.php:70
  - `hrm.attendance.export.csv` in routes/web.php:71
  - `hrm.attendance.my` in routes/web.php:72
- Controller: `app/Http/Controllers/AttendanceController.php`

## Payroll (Items)
- Purpose: Create and manage per-employee payroll entries.
- Routes:
  - `hrm.payroll.index` in routes/web.php:56
  - `hrm.payroll.create` in routes/web.php:57
  - `hrm.payroll.store` in routes/web.php:58
  - `hrm.payroll.edit` in routes/web.php:59
  - `hrm.payroll.update` in routes/web.php:60
  - `hrm.payroll.destroy` in routes/web.php:61
  - `hrm.payroll.approve` in routes/web.php:62
  - `hrm.payroll.paid` in routes/web.php:63
- Controller: `app/Http/Controllers/PayrollController.php`
- Batch totals recalculation: `app/Http/Controllers/PayrollController.php:69-74, 75-85, 117-129`

## Payroll Batches
- Purpose: Group monthly payrolls and manage submit/approve/reject/paid.
- Routes:
  - `hrm.payroll.batches.index` in routes/web.php:74
  - `hrm.payroll.batches.create` in routes/web.php:75
  - `hrm.payroll.batches.store` in routes/web.php:76
  - `hrm.payroll.batches.show` in routes/web.php:77
  - `hrm.payroll.batches.update` in routes/web.php:78
  - `hrm.payroll.batches.submit` in routes/web.php:79
  - `hrm.payroll.batches.approve` in routes/web.php:80
  - `hrm.payroll.batches.reject` in routes/web.php:81
  - `hrm.payroll.batches.paid` in routes/web.php:82
- Controller: `app/Http/Controllers/PayrollBatchController.php`
- Wallet deduction on approve: `app/Http/Controllers/PayrollBatchController.php:131-145`

## Wallet
- Purpose: Hold available funds; deposits and deductions for batch approvals.
- Routes:
  - `hrm.wallet.index` in routes/web.php:84
  - `hrm.wallet.deposit` in routes/web.php:85
- Controller: `app/Http/Controllers/WalletController.php`
- Model: `app/Models/Wallet.php` (`main()` to fetch/create primary wallet)

## UI Navigation
- Sidebar entries configured in `resources/views/layouts/sidebar.blade.php:66-84, 97-105`.

---

For a narrative overview and screenshots checklist, see `docs/Stakeholder-Guide.md`.