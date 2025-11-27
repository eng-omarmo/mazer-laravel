@extends('layouts.master')
@section('title','Advance Details')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Advance for {{ $advance->employee->first_name }} {{ $advance->employee->last_name }}</h3>
                <p class="text-subtitle text-muted">Audit trail and balances</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.advances.index') }}">Advances</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Details</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Original Amount:</strong> {{ number_format($advance->amount,2) }}</div>
                    <div class="col-md-3"><strong>Granted Date:</strong> {{ $advance->date }}</div>
                    <div class="col-md-3"><strong>Installment:</strong> {{ $advance->installment_amount ? number_format($advance->installment_amount,2) : '-' }}</div>
                    <div class="col-md-3"><strong>Remaining:</strong> {{ number_format($advance->remaining_balance ?? $advance->amount,2) }}
                        @if($advance->isOverdue())
                            <span class="badge bg-danger" data-bs-toggle="tooltip" title="Repayment overdue">Overdue</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Next Due Date:</strong> {{ $advance->next_due_date ?: '-' }}</div>
                    <div class="col-md-3"><strong>Schedule:</strong> {{ ucfirst($advance->schedule_type ?: 'none') }}</div>
                    <div class="col-md-6">
                        <form class="row g-2" method="post" action="{{ route('hrm.advances.repay', $advance) }}">
                            @csrf
                            <div class="col-auto">
                                <label class="form-label">Repay Amount</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="Amount" required>
                            </div>
                            <div class="col-auto">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control">
                            </div>
                            <div class="col-auto align-self-end">
                                <button class="btn btn-success"><i class="bi bi-receipt"></i> Record Repayment</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Reference</th><th>Receipt</th></tr></thead>
                        <tbody>
                        @foreach($advance->transactions as $t)
                            <tr>
                                <td>{{ $t->created_at }}</td>
                                <td>{{ ucfirst($t->type) }}</td>
                                <td>{{ number_format($t->amount,2) }}</td>
                                <td>{{ $t->reference_type }} #{{ $t->reference_id }}</td>
                                <td>
                                    @if($t->type==='repayment')
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('hrm.advances.receipt', $t) }}"><i class="bi bi-printer"></i> Receipt</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
