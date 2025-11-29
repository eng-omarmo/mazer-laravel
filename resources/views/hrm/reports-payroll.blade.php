@extends('layouts.master')
@section('title','Payroll Report')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Payroll Report</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payroll</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="get" action="{{ route('hrm.reports.payroll') }}" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="draft" {{ request('status')==='draft'?'selected':'' }}>Draft</option>
                            <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                            <option value="paid" {{ request('status')==='paid'?'selected':'' }}>Paid</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" min="2000" max="2100" value="{{ request('year') }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Month</label>
                        <input type="number" name="month" min="1" max="12" value="{{ request('month') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select">
                            <option value="">All</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}" {{ request('employee_id')==$e->id?'selected':'' }}>{{ $e->first_name }} {{ $e->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Commissions</h6><div class="h4">{{ number_format($totalAllow,2) }}</div></div></div></div>
                    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Deductions</h6><div class="h4">{{ number_format($totalDeduct,2) }}</div></div></div></div>
                    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Payment Completion</h6><div class="h4">{{ $paymentCompletionRate }}%</div></div></div></div>
                    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Avg Approval Hours</h6><div class="h4">{{ $avgApprovalHours }}</div></div></div></div>
                </div>
                <h6 class="mt-3">Total Cost (last 12 months)</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Year</th><th>Month</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($totalCostByMonth as $r)
                                <tr><td>{{ $r->year }}</td><td>{{ $r->month }}</td><td>{{ number_format($r->total,2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <h6 class="mt-3">Payroll Items</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Employee</th><th>Period</th><th>Basic</th><th>Allowances</th><th>Deductions</th><th>Net</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($items as $p)
                                <tr>
                                    <td>{{ optional($p->employee)->first_name }} {{ optional($p->employee)->last_name }}</td>
                                    <td>{{ $p->year }}-{{ str_pad($p->month,2,'0',STR_PAD_LEFT) }}</td>
                                    <td>{{ number_format($p->basic_salary,2) }}</td>
                                    <td>{{ number_format($p->allowances,2) }}</td>
                                    <td>{{ number_format($p->deductions,2) }}</td>
                                    <td>{{ number_format($p->net_pay,2) }}</td>
                                    <td><span class="badge bg-{{ $p->status==='paid'?'success':($p->status==='approved'?'primary':'secondary') }}">{{ ucfirst($p->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
