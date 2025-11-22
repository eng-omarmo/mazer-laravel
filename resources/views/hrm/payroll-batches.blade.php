@extends('layouts.master')
@section('title','Payroll Batches')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Payroll Batches</h3>
                <p class="text-subtitle text-muted">Manage batches and statuses</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Batches</li>
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
                    <a href="{{ route('hrm.payroll.batches.create') }}" class="btn btn-primary"><i class="bi bi-upload"></i> Post Payroll</a>
                    <form class="row g-2" method="get" action="{{ route('hrm.payroll.batches.index') }}">
                        <div class="col">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" value="{{ request('year') }}" class="form-control" min="2000" max="2100">
                        </div>
                        <div class="col">
                            <label class="form-label">Month</label>
                            <input type="number" name="month" value="{{ request('month') }}" class="form-control" min="1" max="12">
                        </div>
                        <div class="col">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                @foreach(['draft','submitted','approved','rejected'] as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Total Employees</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $b)
                                <tr>
                                    <td>{{ $b->year }}-{{ str_pad($b->month,2,'0',STR_PAD_LEFT) }}</td>
                                    <td>{{ $b->total_employees }}</td>
                                    <td>{{ number_format($b->total_amount,2) }}</td>
                                    <td><span class="badge bg-{{ in_array($b->status,['approved','paid']) ? 'success' : ($b->status === 'submitted' ? 'primary' : ($b->status === 'rejected' ? 'danger' : 'secondary')) }}">{{ ucfirst($b->status) }}</span></td>
                                    <td class="d-flex gap-1">
                                        <a href="{{ route('hrm.payroll.batches.show', $b) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i> Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No batches</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $batches->links() }}
            </div>
        </div>
    </section>
</div>
@endsection