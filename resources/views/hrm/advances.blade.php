@extends('layouts.master')
@section('title','Employee Advances')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Employee Advances</h3>
                <p class="text-subtitle text-muted">Record and manage salary advances</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Advances</li>
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
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="d-flex flex-wrap mb-3 align-items-end gap-2">
                    <a href="{{ route('hrm.advances.create') }}" class="btn btn-primary"><i class="bi bi-cash"></i> New Advance</a>
                    <form class="row g-2" method="get" action="{{ route('hrm.advances.index') }}">
                        <div class="col">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select">
                                <option value="">All</option>
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}" {{ request('employee_id')==$e->id?'selected':'' }}>{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                @foreach(['pending','approved','paid'] as $s)
                                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto align-self-end">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Amount</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($advances as $a)
                                <tr>
                                    <td>{{ $a->date }}</td>
                                    <td>{{ $a->employee->first_name }} {{ $a->employee->last_name }}</td>
                                    <td>{{ number_format($a->amount,2) }}</td>
                                    <td>{{ $a->reason }}</td>
                                    <td><span class="badge bg-{{ $a->status==='paid'?'success':($a->status==='approved'?'primary':'secondary') }}">{{ ucfirst($a->status) }}</span></td>
                                    <td class="d-flex gap-1">

                                            @if($a->status==='pending')
                                                <form method="post" action="{{ route('hrm.advances.approve', $a) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2-circle"></i> Approve</button>
                                                </form>
                                            @endif
                                            @if($a->status==='approved')
                                                <form method="post" action="{{ route('hrm.advances.paid', $a) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-cash"></i> Mark Paid</button>
                                                </form>
                                            @endif
                                 
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No advances</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $advances->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
