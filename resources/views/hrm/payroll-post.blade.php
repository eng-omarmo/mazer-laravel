@extends('layouts.master')
@section('title','Post Payroll')
@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Generate Monthly Payroll</h3>
                <p class="text-subtitle text-muted">Select a month and review payroll lines</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('hrm.payroll.batches.index') }}">Payroll Batches</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Post</li>
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

                <form method="get" action="{{ route('hrm.payroll.batches.create') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" value="{{ $year }}" class="form-control" min="2000" max="2100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <input type="number" name="month" value="{{ $month }}" class="form-control" min="1" max="12">
                    </div>
                    <div class="col-md-3">
                        <input type="hidden" name="preview" value="1">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-eye"></i> Preview</button>
                    </div>
                </form>

                @if($preview)
                    <hr>
                    <form method="post" action="{{ route('hrm.payroll.batches.store') }}">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net</th>
                                        <th>Advance Deduction</th>
                                        <th>Net Paid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $e)
                                        @php
                                            $basic = $e->salary ?? 0;
                                            $bonus = $e->bonus ?? 0;
                                            $advances = \App\Models\EmployeeAdvance::where('employee_id', $e->id)
                                                ->whereIn('status', ['approved'])
                                                ->orderBy('date')
                                                ->get();
                                            $remainingTotal = $advances->sum(function($a){ return (float) ($a->remaining_balance ?? $a->amount); });
                                            $sumInstallments = $advances->sum(function($a){ $rem = (float) ($a->remaining_balance ?? $a->amount); $inst = (float) ($a->installment_amount ?? $rem); return min($inst, $rem); });
                                        @endphp
                                        <tr class="payroll-line" data-installments="{{ $sumInstallments }}" data-remaining="{{ $remainingTotal }}">
                                            <td>{{ $e->first_name }} {{ $e->last_name }} ({{ $e->email }})</td>
                                            <td>
                                                <input type="number" step="0.01" name="lines[{{ $e->id }}][basic_salary]" value="{{ $basic }}" class="form-control line-basic">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="lines[{{ $e->id }}][allowances]" value="{{ $bonus }}" class="form-control line-allow">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="lines[{{ $e->id }}][deductions]" value="0" class="form-control line-deduct">
                                            </td>
                                            <td>
                                                <span class="line-net">{{ number_format(($basic + $bonus),2) }}</span>
                                            </td>
                                        <td>
                                                <input type="number" step="0.01" name="lines[{{ $e->id }}][advance_deduction]" value="{{ number_format(min($sumInstallments, $remainingTotal, ($basic + $bonus)),2) }}" class="form-control line-adv-input">
                                            </td>
                                            <td>
                                                <strong class="line-netpaid">{{ number_format((($basic + $bonus) - min($sumInstallments, $remainingTotal, ($basic + $bonus))),2) }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function(){
                            const rows = document.querySelectorAll('.payroll-line');
                            function recalc(row){
                                const basic = parseFloat(row.querySelector('.line-basic').value||'0');
                                const allow = parseFloat(row.querySelector('.line-allow').value||'0');
                                const deduct = parseFloat(row.querySelector('.line-deduct').value||'0');
                                const net = basic + allow - deduct;
                                const installments = parseFloat(row.dataset.installments||'0');
                                const remaining = parseFloat(row.dataset.remaining||'0');
                                const input = row.querySelector('.line-adv-input');
                                const maxAdv = Math.max(0, Math.min(installments, remaining, net));
                                let inputVal = parseFloat(input.value||'0');
                                if (isNaN(inputVal) || inputVal < 0) inputVal = 0;
                                if (inputVal > maxAdv) inputVal = maxAdv;
                                input.value = inputVal.toFixed(2);
                                row.querySelector('.line-net').textContent = net.toFixed(2);
                                row.querySelector('.line-netpaid').textContent = (net - inputVal).toFixed(2);
                            }
                            rows.forEach(row=>{
                                ['.line-basic','.line-allow','.line-deduct'].forEach(sel=>{
                                    const input = row.querySelector(sel);
                                    input.addEventListener('input', ()=>recalc(row));
                                });
                                const advInput = row.querySelector('.line-adv-input');
                                advInput.addEventListener('input', ()=>recalc(row));
                                recalc(row);
                            });
                        });
                        </script>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Post Payroll</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
