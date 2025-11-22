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
- Go to `Wallet` → enter amount → `Deposit`.
- Wallet balance updates immediately; no external gateway required now.

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