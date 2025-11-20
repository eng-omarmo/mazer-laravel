@extends('layouts.master')
@section('title','Create Payroll')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Create Payroll</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.payroll.index') }}">Payroll</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('hrm.payroll.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select">
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}" {{ old('employee_id') == $e->id ? 'selected' : '' }}>{{ $e->first_name }} {{ $e->last_name }} ({{ $e->email }})</option>
                                @endforeach
                            </select>
                            @error('employee_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="{{ old('year') }}" min="2000" max="2100">
                            @error('year')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Month</label>
                            <input type="number" name="month" class="form-control" value="{{ old('month') }}" min="1" max="12">
                            @error('month')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Basic Salary</label>
                            <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary') }}">
                            @error('basic_salary')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Allowances</label>
                            <input type="number" step="0.01" name="allowances" class="form-control" value="{{ old('allowances',0) }}">
                            @error('allowances')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Deductions</label>
                            <input type="number" step="0.01" name="deductions" class="form-control" value="{{ old('deductions',0) }}">
                            @error('deductions')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('hrm.payroll.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection