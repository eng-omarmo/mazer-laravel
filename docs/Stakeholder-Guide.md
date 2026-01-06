# Stakeholder Guide

This guide explains the system purpose, main features, and how stakeholders can use it effectively. Screenshot placeholders are included; capture images and place them in `docs/images/`.

## Overview

- Core domain: HRM (Human Resource Management) with Payroll lifecycle and Attendance tracking.
- Admin UI based on the Mazer template for a clean, consistent experience.
- Wallet enables simple funding and budget checks before payroll approval.

## Navigation

- Dashboard: `Dashboard` link in the sidebar.
- HRM modules: `Employees`, `Departments`, `Leaves`, `Attendance`, `Payroll`, `Payroll Batches`, `Wallet` via the sidebar.
- Account: `Account` under Administration for profile and password.

## Key Modules

### Dashboard
- Snapshot of recent activity and shortcuts.
- Shows batch statuses, including `paid` in green.
- Screenshot: `./images/dashboard.png`.

### Employees
- List, add, and update employee records.
- Stores department mapping and documents.
- Screenshot: `./images/employees-list.png`.

### Departments
- Manage organizational departments and assign department heads.
- Screenshot: `./images/departments.png`.

### Document Verification
- Track and verify employee documents.
- Screenshot: `./images/documents-verification.png`.

### Leaves
- Request, approve, or reject leave applications.
- Filter by status, type, employee, and date range.
- Screenshot: `./images/leaves.png`.

### Attendance
- Daily logs with department filters.
- Monthly summary reporting and CSV export.
- Screenshot: `./images/attendance.png`.

### Payroll
- Manage payroll items (employee, period, salary, allowances, deductions).
- Statuses: `draft`, `approved`, `paid`.
- Screenshot: `./images/payroll-list.png`.

### Payroll Batches
- Group payroll items by month/year and manage at batch level.
- Actions:
  - Submit: move from `draft` to `submitted`.
  - Approve: requires sufficient wallet balance; sets batch and its payrolls to `approved` and deducts from wallet.
  - Mark Paid: sets batch and payrolls to `paid`. Gateway integration can be added later.
- Auto updates: batch becomes `paid` when all payroll lines are `paid`.
- Screenshot: `./images/payroll-batch.png`.

### Wallet
- Holds the main balance used to approve payroll batches.
- Deposit funds; approval deducts the batch total from the wallet.
- Screenshot: `./images/wallet.png`.

### Account
- Profile information and password forms aligned with Mazer UI.
- Recent activity table.
- Screenshot: `./images/account.png`.

## User Roles

- Finance/Admin can approve and mark batches as paid.
- Regular users can view and manage permitted HRM items per policy.

## Workflows

### Payroll Lifecycle
- Create payrolls in `draft`.
- Submit a batch (month/year) to `submitted`.
- Approve batch:
  - System checks `Wallet` balance against `batch.total_amount`.
  - If insufficient, error: “Insufficient wallet balance”.
  - If sufficient, deducts from wallet and sets batch/payrolls to `approved`.
- Mark Paid:
  - Marks batch and payroll items as `paid`.
  - Batch flips to `paid` automatically when all included payrolls are paid.

### Wallet Funding

- Wallet balance updates immediately; Fetches data from somxchange merchant account.

## Screenshots – Capture Checklist

Save images under `mazer-laravel/docs/images/` and name them as below.

- Dashboard: `dashboard.png` — from `Dashboard`.
- Employees list: `employees-list.png` — from `HRM → Employees`.
- Departments: `departments.png` — from `HRM → Departments`.
- Documents: `documents-verification.png` — from `HRM → Documents`.
- Leaves: `leaves.png` — from `HRM → Leaves`.
- Attendance: `attendance.png` — from `HRM → Attendance`.
- Payroll: `payroll-list.png` — from `HRM → Payroll`.
- Batches: `payroll-batch.png` — from `HRM → Payroll → Batches`.
- Wallet: `wallet.png` — from `HRM → Wallet`.
- Account: `account.png` — from `Administration → Account`.

## Technical Notes

- Wallet
  - Model: `app/Models/Wallet.php` (`main()` ensures a single primary wallet).
  - Migration: `database/migrations/2025_11_22_000310_create_wallets_table.php`.
  - Controller: `app/Http/Controllers/WalletController.php` for `index` and `deposit`.
- Payroll Batch Approval
  - Approval deducts wallet balance before setting status to `approved`.
  - See `app/Http/Controllers/PayrollBatchController.php` approve method.
- Batch Totals
  - Recalculated on payroll update/delete in `app/Http/Controllers/PayrollController.php`.
- Routes
  - Core HRM: `routes/web.php` under `Route::prefix('hrm')->name('hrm.')`.

## Integrating a Payment Gateway (Later)

- Hook into either batch `approve` or `paid` actions to call gateway APIs.
- Validate available funds using `Wallet::main()->balance` before committing payment.
- Store gateway responses in a future audit log table (optional).

## Troubleshooting

- Insufficient Wallet Balance: Deposit funds in `Wallet` before approving a batch.
- Access Denied on Approve/Paid: Ensure Finance/Admin role.
- Batch Not Marked Paid: Confirm all payrolls in the batch are `paid`.

## Features Report

- Employees
  - Create, edit, view employee profiles with department linkage.
  - Attach and verify documents; search and paginate lists.
- Departments
  - CRUD for departments; assign heads; list members via employees.
- Leaves
  - Request, approve, reject; filter by status, type, employee, date range.
- Attendance
  - Daily logs with department filter; monthly summary; CSV export; personal history.
- Payroll (Items)
  - Per-employee pay details (salary, allowances, deductions, net pay).
  - Status flow: `draft → approved → paid`; individual item approval and payment.
- Payroll Batches
  - Group items by month/year; actions: submit, approve, reject, mark paid.
  - Auto-batch-paid when all lines are paid; totals maintained on item changes.
- Wallet
  - Deposit funds; approval deducts batch total; prevents approval if insufficient.
- Account
  - Update profile and password; recent activity table; Mazer-styled UI.
- Security
  - Role checks for Finance/Admin on batch approvals and payments.
- UX
  - Mazer admin template; Bootstrap forms; responsive cards and tables.
- Reporting
  - Dashboard badges and status visibility; attendance CSV export; batch totals.
- Roadmap
  - Payment gateway integration; audit logs; multi-wallet; analytics widgets.

## HRM Reports

### Employee Report
- Purpose: Provide visibility into workforce composition and status.
- Key Fields: Name, Department, Position, Hire Date, Status (active/inactive), Document compliance.
- Filters: Department, Status, Position, Hire Date range.
- Metrics:
  - Headcount per department.
  - Active vs. inactive ratio.
  - Compliance rate (verified documents / total documents).
- Actions: View profile, update details, upload/verify documents.
- Export: CSV (recommended for headcount and compliance summaries).
- Source Views: `HRM → Employees` list and employee detail pages.

### Leave Report
- Purpose: Track leave utilization and approval outcomes.
- Key Fields: Employee, Type (annual, sick, etc.), Status (pending/approved/rejected), Start/End Dates, Duration.
- Filters: Status, Type, Employee, Date range.
- Metrics:
  - Approval rate by department and leave type.
  - Average leave duration.
  - Pending requests aging (days since request).
- Actions: Approve/Reject, edit leave entries.
- Export: CSV for HR audit and monthly summaries.
- Source Views: `HRM → Leaves` list with filters.

### Attendance Report
- Purpose: Monitor daily presence and monthly summaries.
- Key Fields: Date, Employee, Department, Status (present/absent/late), Notes.
- Filters: Date, Status, Department.
- Metrics:
  - Attendance rate by department and date range.
  - Late occurrences per employee.
  - Absence trends (weekly/monthly).
- Actions: Add/Edit daily logs, export monthly summary.
- Export: CSV (built-in for monthly summary).
- Source Views: `HRM → Attendance` (Daily Logs and Monthly Summary).

### Payroll Report
- Purpose: Analyze payroll costs and status progression.
- Key Fields (Items): Employee, Year, Month, Basic Salary, Allowances, Deductions, Net Pay, Status.
- Key Fields (Batches): Year, Month, Total Employees, Total Amount, Status, Submitted/Approved/Paid timestamps.
- Filters: Status (`draft/approved/paid`), Year, Month, Employee.
- Metrics:
  - Total payroll cost per month/year.
  - Allowance and deduction distributions.
  - Approval velocity (submitted → approved time), payment completion rate.
- Actions: Approve/Mark Paid at batch level; edit or approve items.
- Export: CSV recommended via custom export (future enhancement) or database export.
- Source Views: `HRM → Payroll` (items) and `HRM → Payroll → Batches` (batch summaries).
