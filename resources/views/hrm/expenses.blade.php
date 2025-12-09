@extends('layouts.master')
@section('title','Expenses')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Expenses</h3>
                <p class="text-subtitle text-muted">Register and track expenses</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Expenses</li>
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

                @php
                $role = strtolower(auth()->user()->role ?? 'hrm');
                @endphp
                <div class="d-flex mb-3 align-items-end gap-2">
                    @if(in_array($role, ['credit_manager', 'admin']))
                    <a href="{{ route('hrm.expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Expense</a>
                    @endif
                    <form class="row g-2" method="get" action="{{ route('hrm.expenses.index') }}">
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
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Payment</th>
                                <th>Approval</th>

                                <th>Supplier</th>
                                <th>Organization</th>
                                <th>Document</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $x)
                            <tr>
                                <td>{{ $x->created_at->format('Y-m-d') }}</td>
                                <td>{{ $x->type }}</td>
                                <td>{{ number_format($x->amount,2) }}</td>
                                <td>{{ number_format($x->totalPaid(),2) }}</td>
                                <td>{{ number_format($x->remaining(),2) }}</td>
                                <td><span class="badge bg-{{ $x->payment_status==='paid'?'success':($x->payment_status==='partial'?'warning':'secondary') }}">{{ $x->payment_status==='paid'?'Completed':($x->payment_status==='partial'?'Partial Paid':'Pending') }}</span></td>
                                <td><span class="badge bg-{{ $x->approvalStatus()==='approved'?'success':($x->approvalStatus()==='rejected'?'danger':'secondary') }}">{{ ucfirst($x->approvalStatus()) }}</span></td>

                                <td>{{ optional($x->supplier)->name }}</td>
                                <td>{{ optional($x->organization)->name }}</td>
                                <td>
                                    @if($x->document_path)
                                    <a href="{{ asset('storage/'.$x->document_path) }}" target="_blank">View</a>
                                    @else
                                    -
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $x->status==='approved'?'success':($x->status==='reviewed'?'primary':'secondary') }}">{{ ucfirst($x->status) }}</span></td>
                                <td class="d-flex gap-1">
                                    @if(in_array($role, ['credit_manager', 'admin']) && $x->status === 'pending')
                                    <a href="{{ route('hrm.expenses.edit', $x) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                    @endif

                                    @if(in_array($role, ['finance', 'admin']))
                                    <a href="{{ route('hrm.expenses.show', $x) }}" class="btn btn-primary btn-sm"><i class="bi bi-cash"></i> Pay</a>
                                    @endif

                                    @if($x->status==='pending' && in_array($role, ['finance', 'admin']))
                                    <form method="post" action="{{ route('hrm.expenses.review', $x) }}">
                                        @csrf
                                        <button class="btn btn-outline-warning btn-sm"><i class="bi bi-clipboard-check"></i> Review</button>
                                    </form>
                                    @endif
                                    @if($x->status==='reviewed' && in_array($role, ['admin']))
                                    <form method="post" action="{{ route('hrm.expenses.approve', $x) }}">
                                        @csrf
                                        <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle"></i> Approve</button>
                                    </form>
                                    @endif
                                    @if(in_array($role, ['admin']))
                                    <form method="post" action="{{ route('hrm.expenses.destroy', $x) }}" onsubmit="return confirm('Delete this expense?')">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No expenses</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $expenses->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
