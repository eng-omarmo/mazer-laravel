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
                                    <th>Advance Deduction</th>
                                    <th>Net Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batch->payrolls as $p)
                                @php
                                    $advances = \App\Models\EmployeeAdvance::where('employee_id',$p->employee_id)->whereIn('status',['approved'])->orderBy('date')->get();
                                    $remaining = $advances->sum(fn($a) => (float)($a->remaining_balance ?? $a->amount));
                                @endphp
                                <tr class="batch-line" data-remaining="{{ $remaining }}">
                                    <td>{{ $p->employee->first_name }} {{ $p->employee->last_name }}</td>
                                    <td><input type="number" step="0.01" name="lines[{{ $p->id }}][basic_salary]" value="{{ $p->basic_salary }}" class="form-control line-basic"></td>
                                    <td><input type="number" step="0.01" name="lines[{{ $p->id }}][allowances]" value="{{ $p->allowances }}" class="form-control line-allow"></td>
                                    <td><input type="number" step="0.01" name="lines[{{ $p->id }}][deductions]" value="{{ $p->deductions }}" class="form-control line-deduct"></td>
                                    <td><span class="line-net">{{ number_format($p->net_pay,2) }}</span></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="lines[{{ $p->id }}][advance_deduction]" value="{{ $p->advance_deduction ?? 0 }}" class="form-control line-adv-input">
                                    </td>
                                    <td><strong class="line-netpaid">{{ number_format(($p->net_pay - ($p->advance_deduction ?? 0)),2) }}</strong></td>
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

                <script>
                document.addEventListener('DOMContentLoaded', function(){
                    const rows = document.querySelectorAll('.batch-line');
                    function recalc(row){
                        const basic = parseFloat(row.querySelector('.line-basic').value||'0');
                        const allow = parseFloat(row.querySelector('.line-allow').value||'0');
                        const deduct = parseFloat(row.querySelector('.line-deduct').value||'0');
                        const net = basic + allow - deduct;
                        const remaining = parseFloat(row.dataset.remaining||'0');
                        const advInput = row.querySelector('.line-adv-input');
                        let advVal = parseFloat(advInput.value);
                        if (isNaN(advVal)) advVal = 0;
                        const exceeds = advVal > remaining;
                        let feedback = row.querySelector('.adv-feedback');
                        if (exceeds) {
                            advInput.classList.add('is-invalid');
                            if (!feedback) {
                                feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback adv-feedback';
                                advInput.closest('td').appendChild(feedback);
                            }
                            feedback.textContent = `Exceeds remaining balance. Max: ${remaining.toFixed(2)}`;
                        } else {
                            advInput.classList.remove('is-invalid');
                            if (feedback) feedback.remove();
                        }
                        row.querySelector('.line-net').textContent = net.toFixed(2);
                        const netPaid = net - Math.min(advVal, net);
                        row.querySelector('.line-netpaid').textContent = netPaid.toFixed(2);
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

                @else
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Basic</th>
                                <th>Commissions</th>
                                <th>Deductions</th>
                                <th>Net</th>
                                <th>Advance Deduction</th>
                                <th>Net Paid</th>
                                <th>Advance Deduction Repayment</th>
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
                                <td>{{ number_format($p->advance_deduction ?? 0,2) }}</td>
                                <td><strong>{{ number_format(($p->net_pay - ($p->advance_deduction ?? 0)),2) }}</strong></td>
                                <td>
                                    @php
                                    $advances = \App\Models\EmployeeAdvance::where('employee_id',$p->employee_id)->whereIn('status',['approved'])->orderBy('date')->get();
                                    $remaining = $advances->sum(fn($a) => (float)($a->remaining_balance ?? $a->amount));
                                    $nextDue = $advances->filter(fn($a) => $a->next_due_date)->sortBy('next_due_date')->first();
                                    @endphp
                                    <div>
                                        <span data-bs-toggle="tooltip" title="Original deduction from this payroll">Deducted: {{ number_format($p->advance_deduction ?? 0,2) }}</span>
                                    </div>
                                    <div>
                                        <span data-bs-toggle="tooltip" title="Current repayment status">Status: {{ $remaining>0?'In Progress':'Settled' }}</span>
                                    </div>
                                    <div>
                                        <span data-bs-toggle="tooltip" title="Remaining total across advances">Remaining: {{ number_format($remaining,2) }}</span>
                                    </div>

                                    <div>
                                        <span data-bs-toggle="tooltip" title="Next scheduled repayment date">Next Due: {{ $nextDue ? $nextDue->next_due_date : '-' }}</span>
                                        @if($nextDue && $nextDue->isOverdue())
                                        <span class="badge bg-danger">Overdue</span>
                                        @endif
                                    </div>
                                </td>
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
                    <form method="post" action="{{ route('hrm.payroll.batches.paidAll') }}">
                        @csrf
                        <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                        <input type="hidden" name="year" value="{{ $batch->year }}">
                        <input type="hidden" name="month" value="{{ $batch->month }}">
                        <button type="submit" class="btn btn-outline-success"><i class="bi bi-cash"></i> Mark Batch Paid</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
