@extends('layouts.master')
@section('title','Advance Repayment Report')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Advance Repayment Report</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Advances</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Outstanding Total:</strong> {{ number_format($outstandingTotal,2) }}</div>
                    <div class="col-md-4"><strong>Fully Repaid:</strong> {{ $fullyRepaid }}</div>
                    <div class="col-md-4"><strong>Overdue:</strong> {{ $overdueCount }}</div>
                </div>
                <form method="get" class="row g-2 mb-3" action="{{ route('hrm.reports.advances') }}">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach(['pending','approved','paid'] as $s)
                                <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select">
                            <option value="">All</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ (string)request('employee_id')===(string)$emp->id?'selected':'' }}>{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto align-self-end">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                    <div class="col-auto align-self-end">
                        <a class="btn btn-outline-primary" href="{{ route('hrm.reports.advances.csv', request()->query()) }}"><i class="bi bi-download"></i> Export CSV</a>
                    </div>
                </form>
                @if(request('employee_id'))
                @php $emp = optional(optional($advances->first())->employee); $remTotal = \App\Models\EmployeeAdvance::whereIn('status',["paid"])->where('employee_id', request('employee_id'))->sum('remaining_balance'); @endphp
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Employee:</strong> {{ $emp->first_name }} {{ $emp->last_name }}</div>
                    <div class="col-md-4"><strong>Email:</strong> {{ $emp->email }}</div>
                    <div class="col-md-4"><strong>Remaining Total:</strong> {{ number_format($remTotal,2) }}</div>
                </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Original Amount</th>
                                <th>Remaining</th>
                                <th>Next Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($advances as $a)
                                <tr>
                                    <td>{{ $a->date }}</td>
                                    <td>{{ $a->employee->first_name }} {{ $a->employee->last_name }}</td>
                                    <td>{{ number_format($a->amount,2) }}</td>
                                    <td>{{ number_format($a->remaining_balance ?? $a->amount,2) }}</td>
                                    <td>{{ $a->next_due_date ?: '-' }} @if($a->isOverdue()) <span class="badge bg-danger">Overdue</span>@endif</td>
                                    <td><span class="badge bg-{{ $a->status==='paid'?'success':($a->status==='approved'?'primary':'secondary') }}">{{ ucfirst($a->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $advances->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
</div>
@endsection
