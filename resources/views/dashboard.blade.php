@extends('layouts.master')

@section('title','Dashboard')

@section('content')

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>HRM Overview</h3>
                <p class="text-subtitle text-muted">Comprehensive metrics across Employees, Departments, Leaves, Payroll, Attendance</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Overview</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-12">
                @if (session('status'))
                <div class="alert alert-warning">{{ session('status') }}</div>
                @endif
                @php
                $empCount = \App\Models\Employee::count();
                $deptCount = \App\Models\Department::count();
                $today = now()->toDateString();
                $presentToday = \App\Models\AttendanceLog::where('date',$today)->where('status','present')->count();
                $absentToday = \App\Models\AttendanceLog::where('date',$today)->where('status','absent')->count();
                $lateToday = \App\Models\AttendanceLog::where('date',$today)->where('status','late')->count();
                $earlyToday = \App\Models\AttendanceLog::where('date',$today)->where('status','early_leave')->count();
                $pendingLeaves = \App\Models\EmployeeLeave::where('status','pending')->count();
                $approvedLeaves = \App\Models\EmployeeLeave::where('status','approved')->count();
                $curYear = now()->year; $curMonth = now()->month;
                $batch = \App\Models\PayrollBatch::where('year',$curYear)->where('month',$curMonth)->first();
                $batchTotal = $batch?->total_amount ?? 0;
                $batchStatus = $batch?->status ?? 'none';
                $wallet = \App\Models\Wallet::main();
                $walletBalance = $wallet->balance;
                $walletCurrency = $wallet->currency;
                @endphp

                <div class="row">
                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon green"><i class="bi bi-people-fill"></i></div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Employees</h6>
                                        <h6 class="font-extrabold mb-0">{{ $empCount }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon blue"><i class="bi bi-diagram-3-fill"></i></div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Departments</h6>
                                        <h6 class="font-extrabold mb-0">{{ $deptCount }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon warning"><i class="bi bi-calendar-check"></i></div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Leaves Pending</h6>
                                        <h6 class="font-extrabold mb-0">{{ $pendingLeaves }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon success"><i class="bi bi-check2-circle"></i></div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Leaves Approved</h6>
                                        <h6 class="font-extrabold mb-0">{{ $approvedLeaves }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon info"><i class="bi bi-cash-stack"></i></div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Payroll {{ str_pad($curMonth,2,'0',STR_PAD_LEFT) }}/{{ $curYear }}</h6>
                                        <h6 class="font-extrabold mb-0">{{ number_format($batchTotal,2) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon secondary"><i class="bi bi-clipboard-check"></i></div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Batch Status</h6>
                                        <h6 class="font-extrabold mb-0">{{ ucfirst($batchStatus) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                @php
                $approvedAdvances = \App\Models\EmployeeAdvance::whereIn('status',["approved"]) ->get();
                $advOutstandingTotal = $approvedAdvances->sum(function($a){ return (float) ($a->remaining_balance ?? $a->amount); });
                $advOverdueCount = $approvedAdvances->filter(function($a){ return $a->next_due_date && \Carbon\Carbon::parse($a->next_due_date)->isPast(); })->count();
                $startMonth = now()->startOfMonth(); $endMonth = now()->endOfMonth();
                $repayMonth = \App\Models\AdvanceTransaction::where('type','repayment')->whereBetween('created_at', [$startMonth, $endMonth])->sum('amount');
                $curAdvDeduct = \App\Models\Payroll::where('year',$curYear)->where('month',$curMonth)->sum('advance_deduction');
                @endphp

                <div class="row mt-3">
                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon info">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Account Balance</h6>
                                        <h6 class="font-extrabold mb-0">
                                            {{ number_format($walletBalance,2) }} {{ $walletCurrency }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon danger">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Advances Outstanding</h6>
                                        <h6 class="font-extrabold mb-0">
                                            {{ number_format($advOutstandingTotal,2) }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon danger">
                                            <i class="bi bi-bell-fill"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Overdue Advances</h6>
                                        <h6 class="font-extrabold mb-0">{{ $advOverdueCount }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon primary">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Repayments This Month</h6>
                                        <h6 class="font-extrabold mb-0">{{ number_format($repayMonth,2) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-2 col-md-4">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon info">
                                            <i class="bi bi-cash"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Payroll Adv. Deduction</h6>
                                        <h6 class="font-extrabold mb-0">{{ number_format($curAdvDeduct,2) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 col-lg-6">
                        @php $todayLogs = \App\Models\AttendanceLog::with('employee.department')->where('date',$today)->orderBy('employee_id')->paginate(5, ['*'], 'attendance_page'); @endphp
                        <div class="card">
                            <div class="card-header">
                                <h4>Attendance Today</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-3 mb-3">
                                    <span class="badge bg-success">Present {{ $presentToday }}</span>
                                    <span class="badge bg-secondary">Absent {{ $absentToday }}</span>
                                    <span class="badge bg-warning">Late {{ $lateToday }}</span>
                                    <span class="badge bg-info">Early {{ $earlyToday }}</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Dept</th>
                                                <th>In</th>
                                                <th>Out</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($todayLogs as $l)
                                            <tr>
                                                <td>{{ $l->employee->first_name }} {{ $l->employee->last_name }}</td>
                                                <td>{{ optional($l->employee->department)->name }}</td>
                                                <td>{{ $l->check_in ?? '-' }}</td>
                                                <td>{{ $l->check_out ?? '-' }}</td>
                                                <td><span class="badge bg-{{ $l->status === 'present' ? 'success' : ($l->status === 'absent' ? 'secondary' : ($l->status === 'late' ? 'warning' : 'info')) }}">{{ ucfirst(str_replace('_',' ', $l->status)) }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $todayLogs->onEachSide(1)->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        @php $latestLeaves = \App\Models\EmployeeLeave::with('employee')->orderByDesc('created_at')->paginate(5, ['*'], 'leaves_page'); @endphp
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Leaves</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Period</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($latestLeaves as $lv)
                                            <tr>
                                                <td>{{ $lv->employee->first_name }} {{ $lv->employee->last_name }}</td>
                                                <td>{{ $lv->start_date }} - {{ $lv->end_date }}</td>
                                                <td><span class="badge bg-{{ $lv->status === 'approved' ? 'success' : ($lv->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($lv->status) }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $latestLeaves->onEachSide(1)->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 col-lg-6">
                        @php $latestEmps = \App\Models\Employee::with('department')->orderByDesc('created_at')->paginate(5, ['*'], 'employees_page'); @endphp
                        <div class="card">
                            <div class="card-header">
                                <h4>New Employees</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Hire Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($latestEmps as $e)
                                            <tr>
                                                <td>{{ $e->first_name }} {{ $e->last_name }}</td>
                                                <td>{{ optional($e->department)->name }}</td>
                                                <td>{{ $e->hire_date }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $latestEmps->onEachSide(1)->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        @php $latestBatches = \App\Models\PayrollBatch::orderByDesc('year')->orderByDesc('month')->paginate(5, ['*'], 'batches_page'); @endphp
                        <div class="card">
                            <div class="card-header">
                                <h4>Payroll Batches</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Employees</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($latestBatches as $b)
                                            <tr>
                                                <td>{{ $b->year }}-{{ str_pad($b->month,2,'0',STR_PAD_LEFT) }}</td>
                                                <td>{{ $b->total_employees }}</td>
                                                <td>{{ number_format($b->total_amount,2) }}</td>
                                                <td><span class="badge bg-{{ in_array($b->status,['approved','paid']) ? 'success' : ($b->status === 'submitted' ? 'primary' : ($b->status === 'rejected' ? 'danger' : 'secondary')) }}">{{ ucfirst($b->status) }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $latestBatches->onEachSide(1)->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        @php $latestRepay = \App\Models\AdvanceTransaction::with('advance.employee')->where('type','repayment')->orderByDesc('created_at')->paginate(5, ['*'], 'repay_page'); @endphp
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Advance Repayments</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Employee</th>
                                                <th>Amount</th>
                                                <th>Reference</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($latestRepay as $tx)
                                            <tr>
                                                <td>{{ $tx->created_at }}</td>
                                                <td>{{ optional($tx->advance->employee)->first_name }} {{ optional($tx->advance->employee)->last_name }}</td>
                                                <td>{{ number_format($tx->amount,2) }}</td>
                                                <td>{{ $tx->reference_type }} #{{ $tx->reference_id }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $latestRepay->onEachSide(1)->links('pagination::bootstrap-5') }}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('js')
<script></script>
@endsection
