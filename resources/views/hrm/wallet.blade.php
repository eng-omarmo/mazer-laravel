@extends('layouts.master')
@section('title','Wallet')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Wallet</h3>
                <p class="text-subtitle text-muted">Fund and monitor balance</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Wallet</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Main Wallet</h5>
                            <span class="badge bg-info">Balance {{ number_format($wallet->balance,2) }} {{ $wallet->currency }}</span>
                        </div>
                        <form method="post" action="{{ route('hrm.wallet.deposit') }}" class="row g-2">
                            @csrf
                            <div class="col">
                                <label class="form-label">Amount</label>
                                <input type="number" name="amount" step="0.01" class="form-control" required>
                            </div>
                            <div class="col-auto align-self-end">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-wallet2"></i> Deposit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection