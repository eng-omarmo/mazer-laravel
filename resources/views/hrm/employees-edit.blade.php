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
                            <label class="form-label">Commission</label>
                            <input type="number" step="0.01" name="bonus" class="form-control" value="{{ old('bonus', $employee->bonus) }}">
                            @error('bonus')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference Full Name</label>
                            <input type="text" name="reference_full_name" class="form-control" value="{{ old('reference_full_name', $employee->reference_full_name) }}">
                            @error('reference_full_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference Phone Number</label>
                            <input type="text" name="reference_phone" class="form-control" value="{{ old('reference_phone', $employee->reference_phone) }}">
                            @error('reference_phone')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identity Document Number</label>
                            <input type="text" name="identity_doc_number" class="form-control" value="{{ old('identity_doc_number', $employee->identity_doc_number) }}">
                            @error('identity_doc_number')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fingerprint ID</label>
                            <input type="text" name="fingerprint_id" class="form-control" value="{{ old('fingerprint_id', $employee->fingerprint_id) }}">
                            @error('fingerprint_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $employee->account_number) }}">
                            @error('account_number')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Organization</label>
                            <select name="organization_id" class="form-select">
                                <option value="">None</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ (string)old('organization_id', $employee->organization_id)===(string)$org->id?'selected':'' }}>{{ $org->name }}</option>
                                @endforeach
                            </select>
                            @error('organization_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identity Document (PDF/DOC/JPG/PNG)</label>
                            <input type="file" name="identity_document" class="form-control">
                            @error('identity_document')<small class="text-danger">{{ $message }}</small>@enderror
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
