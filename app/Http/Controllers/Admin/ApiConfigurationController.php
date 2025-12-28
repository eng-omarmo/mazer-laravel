<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApiConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiConfigurationController extends Controller
{
    public function index()
    {
        $configs = ApiConfiguration::orderByDesc('created_at')->paginate(10);

        return view('admin.api-configurations.index', compact('configs'));
    }

    public function create()
    {
        return view('admin.api-configurations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255', 'unique:api_configurations,token'],
        ]);

        $config = ApiConfiguration::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created API Configuration',
            'meta' => ['id' => $config->id],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.api-configurations.index')->with('status', 'API configuration created');
    }

    public function edit(ApiConfiguration $apiConfiguration)
    {
        return view('admin.api-configurations.edit', ['config' => $apiConfiguration]);
    }

    public function update(Request $request, ApiConfiguration $apiConfiguration)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255', 'unique:api_configurations,token,'.$apiConfiguration->id],
        ]);

        $apiConfiguration->update($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated API Configuration',
            'meta' => ['id' => $apiConfiguration->id],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.api-configurations.index')->with('status', 'API configuration updated');
    }

    public function destroy(Request $request, ApiConfiguration $apiConfiguration)
    {
        $id = $apiConfiguration->id;
        $apiConfiguration->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted API Configuration',
            'meta' => ['id' => $id],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.api-configurations.index')->with('status', 'API configuration deleted');
    }
}
