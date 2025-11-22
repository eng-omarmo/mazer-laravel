@extends('layouts.master')
@section('title','Transactions')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Transactions</h3>
                <p class="text-subtitle text-muted">Credits, debits and balance</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transactions</li>
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
                    <a href="{{ route('hrm.transactions.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create Transaction</a>
                    <div class="ms-auto">
                        <span class="badge bg-info">Balance {{ number_format($balance,2) }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Direction</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Employee</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $t)
                                <tr>
                                    <td>{{ $t->created_at }}</td>
                                    <td><span class="badge bg-{{ $t->direction === 'credit' ? 'success' : 'danger' }}">{{ ucfirst($t->direction) }}</span></td>
                                    <td>{{ ucfirst($t->type) }}</td>
                                    <td>{{ number_format($t->amount,2) }}</td>
                                    <td>{{ $t->reference }}</td>
                                    <td>{{ optional($t->employee)->first_name }} {{ optional($t->employee)->last_name }}</td>
                                    <td><span class="badge bg-{{ $t->status === 'posted' ? 'success' : ($t->status === 'failed' ? 'danger' : 'secondary') }}">{{ ucfirst($t->status) }}</span></td>
                                    <td class="d-flex gap-1">
                                        <a href="{{ route('hrm.transactions.edit', $t) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                        <form method="post" action="{{ route('hrm.transactions.destroy', $t) }}">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No transactions</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $transactions->links() }}
            </div>
        </div>
    </section>
</div>
@endsection