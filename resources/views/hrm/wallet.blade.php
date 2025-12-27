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
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Your Somxchange  Wallets</h5>
                            <span class="badge bg-secondary">{{ is_array($wallets ?? null) ? count($wallets) : 0 }} total</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Default</th>
                                        <th>Currency</th>
                                        <th>Code</th>
                                        <th>Balance</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($wallets ?? []) as $w)
                                        <tr>
                                            <td>{{ $w['id'] ?? '' }}</td>
                                            <td>
                                                @php $def = strtolower($w['is_default'] ?? 'No'); @endphp
                                                <span class="badge {{ $def === 'yes' ? 'bg-success' : 'bg-light text-dark' }}">{{ $w['is_default'] ?? 'No' }}</span>
                                            </td>
                                            <td>{{ data_get($w, 'currency.name') }}</td>
                                            <td>{{ data_get($w, 'currency.code') }}</td>
                                            <td>
                                                {{ number_format((float) ($w['balance'] ?? 0), 8) }}
                                                <span class="text-muted">{{ data_get($w, 'currency.symbol') }}</span>
                                            </td>
                                            <td>{{ \Illuminate\Support\Carbon::parse($w['updated_at'] ?? null)->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center">No external wallets found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
