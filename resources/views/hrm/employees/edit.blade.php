@extends('layouts.master')
@section('title','Edit Employee')
@section('content')
<div class="page-heading">
    <h3>Edit Employee</h3>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('hrm.employees.update',$employee) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name',$employee->first_name) }}" class="form-control" required>
                            @error('first_name')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name',$employee->last_name) }}" class="form-control" required>
                            @error('last_name')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email',$employee->email) }}" class="form-control" required>
                            @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone',$employee->phone) }}" class="form-control">
                            @error('phone')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control">{{ old('address',$employee->address) }}</textarea>
                        @error('address')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" value="{{ old('dob',$employee->dob) }}" class="form-control">
                            @error('dob')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male" @selected(old('gender',$employee->gender)==='male')>Male</option>
                                <option value="female" @selected(old('gender',$employee->gender)==='female')>Female</option>
                                <option value="other" @selected(old('gender',$employee->gender)==='other')>Other</option>
                            </select>
                            @error('gender')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select" required>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}" @selected(old('department_id',$employee->department_id)==$dept->department_id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" value="{{ old('designation',$employee->designation) }}" class="form-control">
                            @error('designation')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Join Date</label>
                            <input type="date" name="join_date" value="{{ old('join_date',$employee->join_date) }}" class="form-control">
                            @error('join_date')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Employment Type</label>
                            <select name="employment_type" class="form-select" required>
                                <option value="full-time" @selected(old('employment_type',$employee->employment_type)==='full-time')>Full-time</option>
                                <option value="part-time" @selected(old('employment_type',$employee->employment_type)==='part-time')>Part-time</option>
                                <option value="contract" @selected(old('employment_type',$employee->employment_type)==='contract')>Contract</option>
                            </select>
                            @error('employment_type')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" @selected(old('status',$employee->status)==='active')>Active</option>
                            <option value="resigned" @selected(old('status',$employee->status)==='resigned')>Resigned</option>
                            <option value="terminated" @selected(old('status',$employee->status)==='terminated')>Terminated</option>
                        </select>
                        @error('status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('hrm.employees.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection