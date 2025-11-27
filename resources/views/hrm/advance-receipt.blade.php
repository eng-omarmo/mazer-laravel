@extends('layouts.master')
@section('title','Repayment Receipt')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Repayment Receipt</h3>
                <p class="text-subtitle text-muted">Advance #{{ $transaction->advance_id }} repayment</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.advances.index') }}">Advances</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Receipt</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Employee:</strong> {{ $transaction->advance->employee->first_name }} {{ $transaction->advance->employee->last_name }}</div>
                    <div class="col-md-6"><strong>Date:</strong> {{ $transaction->created_at }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Advance ID:</strong> {{ $transaction->advance_id }}</div>
                    <div class="col-md-6"><strong>Amount:</strong> {{ number_format($transaction->amount,2) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Reference:</strong> {{ $transaction->reference_type }} #{{ $transaction->reference_id }}</div>
                    <div class="col-md-6"><strong>Status:</strong> {{ ucfirst($transaction->type) }}</div>
                </div>
                <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
            </div>
        </div>
    </section>
</div>
@endsection

