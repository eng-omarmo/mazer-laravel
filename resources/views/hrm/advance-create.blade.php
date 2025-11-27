@extends('layouts.master')
@section('title','New Advance')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Record Advance</h3>
                <p class="text-subtitle text-muted">Create a salary advance for an employee</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.advances.index') }}">Advances</a></li>
                        <li class="breadcrumb-item active" aria-current="page">New</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="post" action="{{ route('hrm.advances.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="Optional notes">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Installment Amount</label>
                        <input type="number" step="0.01" name="installment_amount" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Next Due Date</label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Schedule</label>
                        <select name="schedule_type" class="form-select">
                            <option value="none">None</option>
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Biweekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
