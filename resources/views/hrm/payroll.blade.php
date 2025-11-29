@extends('layouts.master')
@section('title','Payroll')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Payroll</h3>
                <p class="text-subtitle text-muted">Manage monthly payrolls</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payroll</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="d-flex mb-3 align-items-end gap-2">
                    <a href="{{ route('hrm.payroll.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create Payroll</a>
                    <form class="row g-2" method="get" action="{{ route('hrm.payroll.index') }}">
                        <div class="col">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select">
                                <option value="">All</option>
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>{{ $e->first_name }} {{ $e->last_name }} ({{ $e->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" value="{{ request('year') }}" class="form-control" min="2000" max="2100">
                        </div>
                        <div class="col">
                            <label class="form-label">Month</label>
                            <input type="number" name="month" value="{{ request('month') }}" class="form-control" min="1" max="12">
                        </div>
                        <div class="col">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                @foreach(['draft','approved','paid'] as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Period</th>
                                <th>Basic</th>
                                <th>Allowances</th>
                                <th>Deductions</th>
                                <th>Net</th>
                                <th>Advance Deduction Repayment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payrolls as $p)
                                <tr>
                                    <td>{{ $p->employee->first_name }} {{ $p->employee->last_name }}</td>
                                    <td>{{ $p->year }}-{{ str_pad($p->month,2,'0',STR_PAD_LEFT) }}</td>
                                    <td>{{ number_format($p->basic_salary,2) }}</td>
                                    <td>{{ number_format($p->allowances,2) }}</td>
                                    <td>{{ number_format($p->deductions,2) }}</td>
                                    <td><strong>{{ number_format($p->net_pay,2) }}</strong></td>
                                    <td>
                                        @php
                                            $advances = \App\Models\EmployeeAdvance::where('employee_id',$p->employee_id)->whereIn('status',['approved'])->orderBy('date')->get();
                                            $remaining = $advances->sum(fn($a) => (float)($a->remaining_balance ?? $a->amount));
                                            $nextDue = $advances->filter(fn($a) => $a->next_due_date)->sortBy('next_due_date')->first();
                                        @endphp
                                        <div>
                                            <span data-bs-toggle="tooltip" title="Original deduction from this payroll">Deducted: {{ number_format($p->advance_deduction ?? 0,2) }}</span>
                                        </div>
                                        <div>
                                            <span data-bs-toggle="tooltip" title="Current repayment status">Status: {{ $remaining>0?'In Progress':'Settled' }}</span>
                                        </div>
                                        <div>
                                            <span data-bs-toggle="tooltip" title="Remaining total across advances">Remaining: {{ number_format($remaining,2) }}</span>
                                        </div>
                                        <div>
                                            <span data-bs-toggle="tooltip" title="Next scheduled repayment date">Next Due: {{ $nextDue ? $nextDue->next_due_date : '-' }}</span>
                                            @if($nextDue && $nextDue->isOverdue())
                                                <span class="badge bg-danger">Overdue</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $p->status === 'paid' ? 'success' : ($p->status === 'approved' ? 'primary' : 'secondary') }}">{{ ucfirst($p->status) }}</span>
                                    </td>
                                    <td class="d-flex gap-1">
                                        <a href="{{ route('hrm.payroll.edit', $p) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                                        <form method="post" action="{{ route('hrm.payroll.destroy', $p) }}" onsubmit="return confirm('Delete payroll?')">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                        </form>
                                        @if($p->status === 'draft')
                                            <form method="post" action="{{ route('hrm.payroll.approve', $p) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-check2-circle"></i> Approve</button>
                                            </form>
                                        @elseif($p->status === 'approved')
                                            <form method="post" action="{{ route('hrm.payroll.paid', $p) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-cash"></i> Mark Paid</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No payrolls</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                      {{ $payrolls->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
</div>
@endsection
