@extends('layouts.master')
@section('title','Edit Employee')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Employee Department</h3>
                <p class="text-subtitle text-muted">Assign or change the employee's department</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.employees.index') }}">Employees</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('hrm.employees.update', $employee) }}">
                    @csrf
                    @method('patch')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">None</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->code }} - {{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Salary</label>
                            <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary', $employee->salary) }}">
                            @error('salary')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bonus</label>
                            <input type="number" step="0.01" name="bonus" class="form-control" value="{{ old('bonus', $employee->bonus) }}">
                            @error('bonus')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('hrm.employees.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection