@extends('layouts.master')
@section('title','Expense Payments Report')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Expense Payments Report</h3>
                <p class="text-subtitle text-muted">Summary of payments made</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payments Report</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><div class="h6">Total Amount</div><div class="h4">{{ number_format($totalAmount,2) }}</div></div>
                    <div class="col-md-4"><div class="h6">Approved Amount</div><div class="h4">{{ number_format($approvedAmount,2) }}</div></div>
                    <div class="col-md-4"><div class="h6">Pending Amount</div><div class="h4">{{ number_format($pendingAmount,2) }}</div></div>
                </div>

                <form class="row g-2 mb-3" method="get" action="{{ route('hrm.reports.payments') }}">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                            <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                            <option value="rejected" {{ request('status')==='rejected'?'selected':'' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">All</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ request('supplier_id')==$sup->id?'selected':'' }}>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Organization</label>
                        <select name="organization_id" class="form-select">
                            <option value="">All</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ request('organization_id')==$org->id?'selected':'' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <a href="{{ route('hrm.reports.payments.csv', request()->query()) }}" class="btn btn-success"><i class="bi bi-download"></i> CSV</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Date</th><th>Amount</th><th>Status</th><th>Note</th><th>Expense ID</th><th>Supplier</th><th>Organization</th></tr></thead>
                        <tbody>
                            @forelse($items as $p)
                                <tr>
                                    <td>{{ $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : '' }}</td>
                                    <td>{{ number_format($p->amount,2) }}</td>
                                    <td><span class="badge bg-{{ $p->status==='approved'?'success':($p->status==='rejected'?'danger':'warning') }}">{{ ucfirst($p->status) }}</span></td>
                                    <td>{{ $p->note }}</td>
                                    <td><a href="{{ route('hrm.expenses.show', $p->expense_id) }}">#{{ $p->expense_id }}</a></td>
                                    <td>{{ optional($p->expense->supplier)->name }}</td>
                                    <td>{{ optional($p->expense->organization)->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No payments</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
