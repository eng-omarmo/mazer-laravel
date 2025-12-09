@extends('layouts.master')
@section('title','Employees')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Expense Payments</h3>
                <p class="text-subtitle text-muted">Manage pending expense payments</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Employees</li>
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
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Expense No</th>
                                <th>Supplier</th>
                                <th>Organization</th>
                                <th>Amount</th>
                                <th>Paid At</th>
                                <th>Note</th>
                                <th>Actions</th>

                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($payments as $payment)
                            <tr>
                                <td>{{ "#". $payment->expense->id}}</td>
                                <td>{{ optional($payment->expense->supplier)->name }}</td>
                                <td>{{ optional($payment->expense->organization)->name }}</td>
                                <td>{{ number_format($payment->amount,2) }}</td>
                                <td>{{ $payment->paid_at->format('d-m-Y H:i:s') }}</td>
                                <td>{{ $payment->note }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($role === 'admin')
                                        <form action="{{ route('hrm.expense-payments.approve', $payment) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <form action="{{ route('hrm.expense-payments.reject', $payment) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                        @endif

                                        <a href="{{ route('hrm.expenses.show', $payment->expense) }}" class="btn btn-info btn-sm">View Details</a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                {{ $payments->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
</div>
@endsection
