# Expense Module Permissions

## Naming Conventions
- Hierarchical dot-notation per component: `expense.*`, `payment.*`, `admin.*`
- Clear action verbs: `view`, `approve`, `reject`, `request_modification`, `initiate`, `cancel`

## Permissions
- expense.view: View expense list and details
- expense.approve: Approve expenses after review
- expense.reject: Reject expenses during review
- expense.request_modification: Request changes from originator
- payment.initiate: Initiate payments for approved expenses
- payment.approve: Approve pending expense payments
- payment.view_history: View pending and completed payment records
- payment.cancel: Cancel or reject pending payments
- admin.expense_categories.manage: CRUD for expense categories
- admin.workflows.configure: Configure approval workflow steps and rules
- admin.reports.access: Access financial and expense-related reports

## Roles and Inheritance
- admin: Full access; receives all above permissions
- finance: payment.* plus expense.view and expense.approve
- hrm: expense.view, expense.request_modification, admin.reports.access
- credit_manager: expense.view, expense.request_modification
- employee: expense.view, payment.view_history (policy-scoped to own context)

## Middleware Integration
- Use `permission:*` middleware on routes
- Example: `->middleware('permission:payment.approve')`

## Blade Checks
- @can('expense.approve') for approve buttons
- @can('payment.initiate') for payment actions

## Audit Logging
- All permission and role changes logged in `activity_logs` via admin controllers

## API
- GET /api/permissions: List permissions
- GET /api/roles: List roles
- POST /api/roles/{role}/permissions: Sync role permissions
