@extends('layouts.master')
@section('title','Expenses Report')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Expenses Report</h3>
                <p class="text-subtitle text-muted">Summary and trends</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Expenses Report</li>
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
                    <div class="col-md-4"><div class="h6">Total Paid</div><div class="h4">{{ number_format($totalPaid,2) }}</div></div>
                    <div class="col-md-4"><div class="h6">Total Remaining</div><div class="h4">{{ number_format($totalRemaining,2) }}</div></div>
                </div>

                <form class="row g-2 mb-3" method="get" action="{{ route('hrm.reports.expenses') }}">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                            <option value="reviewed" {{ request('status')==='reviewed'?'selected':'' }}>Reviewed</option>
                            <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
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
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Paid</th><th>Remaining</th><th>Supplier</th><th>Organization</th></tr></thead>
                        <tbody>
                            @forelse($items as $x)
                                <tr>
                                    <td>{{ $x->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $x->type }}</td>
                                    <td>{{ number_format($x->amount,2) }}</td>
                                    <td>{{ number_format($x->totalPaid(),2) }}</td>
                                    <td>{{ number_format($x->remaining(),2) }}</td>
                                    <td>{{ optional($x->supplier)->name }}</td>
                                    <td>{{ optional($x->organization)->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No items</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>

                <h6 class="mt-4">Last 12 months</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Month</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($monthly as $m)
                                <tr><td>{{ $m->ym }}</td><td>{{ number_format($m->total,2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

