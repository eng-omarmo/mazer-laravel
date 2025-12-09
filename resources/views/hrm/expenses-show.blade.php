@extends('layouts.master')
@section('title','Expense Details')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Expense Details</h3>
                <p class="text-subtitle text-muted">Payments & Approvals</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.expenses.index') }}">Expenses</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Details</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="row">
            <div class="col-lg-7">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4"><strong>Type</strong>
                                <div>{{ $expense->type }}</div>
                            </div>
                            <div class="col-md-4"><strong>Amount</strong>
                                <div>{{ number_format($expense->amount,2) }}</div>
                            </div>
                            <div class="col-md-4"><strong>Supplier</strong>
                                <div>{{ optional($expense->supplier)->name ?: '-' }}</div>
                            </div>
                            <div class="col-md-4"><strong>Organization</strong>
                                <div>{{ optional($expense->organization)->name ?: '-' }}</div>
                            </div>
                            <div class="col-md-4"><strong>Status</strong>
                                <div><span class="badge bg-{{ $expense->status==='approved'?'primary':'secondary' }}">{{ ucfirst($expense->status) }}</span></div>
                            </div>
                            <div class="col-md-4"><strong>Payment</strong>
                                <div><span class="badge bg-{{ $expense->paymentStatus()==='paid'?'success':($expense->paymentStatus()==='partial'?'warning':'secondary') }}">{{ ucfirst($expense->paymentStatus()) }}</span></div>
                            </div>
                            <div class="col-md-12"><strong>Outstanding Balance</strong>
                                <div class="h5">{{ number_format($expense->remaining(),2) }}</div>
                            </div>
                            <div class="col-md-12"><strong>Document</strong>
                                <div>
                                    @if($expense->document_path)
                                    <a href="{{ asset('storage/'.$expense->document_path) }}" target="_blank">View</a>
                                    @else - @endif
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6>Payment History</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>By</th>
                                        <th>Note</th>
                                        <th>Approval Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expense->payments as $p)
                                    <tr>
                                        <td>{{ $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : $p->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ number_format($p->amount,2) }}</td>
                                        <td>{{ optional($p->paid_by ? \App\Models\User::find($p->paid_by) : null)->name ?? '-' }}</td>
                                        <td>{{ $p->note ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $p->is_approved ? 'success' : 'danger' }}">{{ $p->is_approved ? 'Approved' : 'Pending' }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No payments</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Make a Payment</h6>
                        <form method="post" action="{{ route('hrm.expenses.pay', $expense) }}" class="row g-2">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->remaining()) }}" class="form-control">
                                @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="datetime-local" name="paid_at" value="{{ old('paid_at') }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Note</label>
                                <input type="text" name="note" value="{{ old('note') }}" class="form-control">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-cash"></i> Pay</button>
                                <a href="{{ route('hrm.expenses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                            </div>
                        </form>
                    </div>
                </div>


            </div>
        </div>
    </section>
</div>
@endsection
