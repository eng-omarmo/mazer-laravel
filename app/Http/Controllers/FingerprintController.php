<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
        return view('hrm.fingerprint');
    }

    public function capture(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);
        $user = User::findOrFail((int) $validated['user_id']);
        // Guard handled via route middleware

        if (! $this->device->handshake()) {
            return response()->json(['ok' => false, 'error' => 'Device handshake failed'], 503);
        }
        try {
            $result = $this->device->captureTemplate(3);
            $enc = $this->crypto->encrypt($result['template']);
            $record = BiometricTemplate::create([
                'user_id' => $user->id,
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
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Fingerprint Capture',
                'meta' => ['user_id' => $user->id, 'template_id' => $record->id, 'quality' => $result['quality']],
                'ip' => $request->ip(),
            ]);
            return response()->json(['ok' => true, 'template_id' => $record->id]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Capture failed'], 500);
        }
    }
}
