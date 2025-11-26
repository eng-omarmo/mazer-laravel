@extends('layouts.master')
@section('title','Onboard Employee')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3><i class="bi bi-person-plus me-2"></i>Onboard Employee</h3>
                <p class="text-muted">Create employee profile and upload HR documents</p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <nav aria-label="breadcrumb" class="breadcrumb-header">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('hrm.employees.index') }}">
                                <i class="bi bi-people me-1"></i>Employees
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Onboard</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card shadow-sm">
            <div class="card-body">

                <form method="post" action="{{ route('hrm.employees.store') }}" enctype="multipart/form-data">
                    @csrf


                    {{-- ================================
                        SECTION 1 — PERSONAL INFORMATION
                    ================================= --}}
                    <h5 class="mt-3 mb-3">
                        <i class="bi bi-person-circle me-2"></i>Personal Information
                    </h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control">
                            @error('first_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-control">
                            @error('last_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identity Document Number</label>
                            <input type="text" name="identity_doc_number" value="{{ old('identity_doc_number') }}" class="form-control">
                            @error('identity_doc_number')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fingerprint ID</label>
                            <input type="text" name="fingerprint_id" value="{{ old('fingerprint_id') }}" class="form-control">
                            @error('fingerprint_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>


                    {{-- ================================
                        SECTION 2 — JOB INFORMATION
                    ================================= --}}
                    <h5 class="mt-4 mb-3">
                        <i class="bi bi-briefcase-fill me-2"></i>Job Information
                    </h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select department</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->code }} - {{ $dept->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('department_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" value="{{ old('position') }}" class="form-control">
                            @error('position')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hire Date</label>
                            <input type="date" name="hire_date" value="{{ old('hire_date') }}" class="form-control">
                            @error('hire_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>


                    {{-- ================================
                        SECTION 3 — SALARY & BENEFITS
                    ================================= --}}
                    <h5 class="mt-4 mb-3">
                        <i class="bi bi-cash-stack me-2"></i>Salary & Benefits
                    </h5>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Salary</label>
                            <input type="number" step="0.01" name="salary" value="{{ old('salary') }}" class="form-control">
                            @error('salary')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Commission</label>
                            <input type="number" step="0.01" name="bonus" value="{{ old('bonus') }}" class="form-control">
                            @error('bonus')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>


                    {{-- ================================
                        SECTION 4 — REFERENCE INFORMATION
                    ================================= --}}
                    <h5 class="mt-4 mb-3">
                        <i class="bi bi-telephone-forward me-2"></i>Reference Information
                    </h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference Full Name</label>
                            <input type="text" name="reference_full_name" value="{{ old('reference_full_name') }}" class="form-control">
                            @error('reference_full_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference Phone Number</label>
                            <input type="text" name="reference_phone" value="{{ old('reference_phone') }}" class="form-control">
                            @error('reference_phone')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>


                    {{-- ================================
                        SECTION 5 — DOCUMENT UPLOADS
                    ================================= --}}
                    <h5 class="mt-4 mb-3">
                        <i class="bi bi-folder2-open me-2"></i>Documents Upload
                    </h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CV (PDF/DOC/DOCX)</label>
                            <input type="file" name="cv" class="form-control">
                            @error('cv')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract (PDF/DOC/DOCX)</label>
                            <input type="file" name="contract" class="form-control">
                            @error('contract')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identity Document (PDF/DOC/JPG/PNG)</label>
                            <input type="file" name="identity_document" class="form-control">
                            @error('identity_document')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>


                    {{-- ACTION BUTTONS --}}
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save
                        </button>

                        <a href="{{ route('hrm.employees.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>
@endsection
