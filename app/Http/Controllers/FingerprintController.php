<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\BiometricTemplate;
use App\Models\User;
use App\Services\BiometricCrypto;
use App\Services\ZktecoAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FingerprintController extends Controller
{
    protected ZktecoAdapter $device;
    protected BiometricCrypto $crypto;

    public function __construct(ZktecoAdapter $device, BiometricCrypto $crypto)
    {
        $this->device = $device;
        $this->crypto = $crypto;
    }

    public function show(Request $request)
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('hrm.fingerprint', compact('employees'));
    }

    public function capture(Request $request)
    {

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);
        $employee = Employee::findOrFail((int) $validated['employee_id']);


        if (! $this->device->handshake()) {
            return redirect()->back()->with('error', 'Device handshake failed');
        }
        try {

            $result = $this->device->captureTemplate(3);

            $enc = $this->crypto->encrypt($result['template']);

            $record = BiometricTemplate::create([
                'employee_id' => $employee->id,
                'device_sn' => $result['device_sn'],
                'algorithm' => $result['algorithm'],
                'dpi' => $result['dpi'],
                'quality_score' => $result['quality'],
                'captured_at' => now(),
                'ciphertext' => $enc['ciphertext'],
                'iv' => $enc['iv'],
                'tag' => $enc['tag'],
                'created_by' => Auth::id(),
            ]);

            // Update the employee's fingerprint_id reference
            $employee->update(['fingerprint_id' => $record->id]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Fingerprint Capture',
                'meta' => ['employee_id' => $employee->id, 'template_id' => $record->id, 'quality' => $result['quality']],
                'ip' => $request->ip(),
            ]);
            return redirect()->back()->with('success', 'Fingerprint captured successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Capture failed']);
        }
    }
}
