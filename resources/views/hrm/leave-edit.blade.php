@extends('layouts.master')
@section('title','Edit Leave')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Leave</h3>
                <p class="text-subtitle text-muted">Update leave details</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.leave.index') }}">Leaves</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('hrm.leave.update', $leave) }}">
                    @csrf
                    @method('patch')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id', $leave->employee_id) == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->email }})</option>
                                @endforeach
                            </select>
                            @error('employee_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <input type="text" name="type" value="{{ old('type', $leave->type) }}" class="form-control">
                            @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date', $leave->start_date) }}" class="form-control">
                            @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date', $leave->end_date) }}" class="form-control">
                            @error('end_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="3">{{ old('reason', $leave->reason) }}</textarea>
                            @error('reason')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['pending','approved','rejected'] as $s)
                                    <option value="{{ $s }}" {{ old('status', $leave->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('hrm.leave.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection