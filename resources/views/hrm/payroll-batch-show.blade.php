@extends('layouts.master')
@section('title','Payroll Batch')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Batch {{ $batch->year }}-{{ str_pad($batch->month,2,'0',STR_PAD_LEFT) }}</h3>
                <p class="text-subtitle text-muted">Edit lines and manage status</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.payroll.batches.index') }}">Payroll Batches</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Batch</li>
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

                <div class="mb-3 d-flex gap-2 align-items-center">
                    <span><strong>Status:</strong> <span class="badge bg-{{ in_array($batch->status,['approved','paid']) ? 'success' : ($batch->status === 'submitted' ? 'primary' : ($batch->status === 'rejected' ? 'danger' : 'secondary')) }}">{{ ucfirst($batch->status) }}</span></span>
                    <span><strong>Total Employees:</strong> {{ $batch->total_employees }}</span>
                    <span><strong>Total Amount:</strong> {{ number_format($batch->total_amount,2) }}</span>
                </div>

                @if($batch->status !== 'approved')
                    <form method="post" action="{{ route('hrm.payroll.batches.update', $batch) }}">
                        @csrf
                        @method('patch')
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->payrolls as $p)
                                        <tr>
                                            <td>{{ $p->employee->first_name }} {{ $p->employee->last_name }}</td>
                                            <td><input type="number" step="0.01" name="lines[{{ $p->id }}][basic_salary]" value="{{ $p->basic_salary }}" class="form-control"></td>
                                            <td><input type="number" step="0.01" name="lines[{{ $p->id }}][allowances]" value="{{ $p->allowances }}" class="form-control"></td>
                                            <td><input type="number" step="0.01" name="lines[{{ $p->id }}][deductions]" value="{{ $p->deductions }}" class="form-control"></td>
                                            <td>{{ number_format($p->net_pay,2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
                            @if($batch->status === 'draft')
                                <form method="post" action="{{ route('hrm.payroll.batches.submit', $batch) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-send"></i> Submit</button>
                                </form>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Basic</th>
                                    <th>Allowances</th>
                                    <th>Deductions</th>
                                    <th>Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batch->payrolls as $p)
                                    <tr>
                                        <td>{{ $p->employee->first_name }} {{ $p->employee->last_name }}</td>
                                        <td>{{ number_format($p->basic_salary,2) }}</td>
                                        <td>{{ number_format($p->allowances,2) }}</td>
                                        <td>{{ number_format($p->deductions,2) }}</td>
                                        <td><strong>{{ number_format($p->net_pay,2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="mt-3 d-flex gap-2">
                    @if($batch->status === 'submitted')
                        <form method="post" action="{{ route('hrm.payroll.batches.approve', $batch) }}">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Approve</button>
                        </form>
                        <form method="post" action="{{ route('hrm.payroll.batches.reject', $batch) }}">
                            @csrf
                            <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> Reject</button>
                        </form>
                    @endif
                    @if($batch->status === 'approved')
                        <form method="post" action="{{ route('hrm.payroll.batches.paid', $batch) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success"><i class="bi bi-cash"></i> Mark Batch Paid</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection