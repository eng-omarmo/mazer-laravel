<div class="table-responsive">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>{{ __('Action') }}</th>
                <th>{{ __('IP') }}</th>
                <th>{{ __('Time') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ str_replace('_',' ', ucfirst($log->action)) }}</td>
                    <td>{{ $log->ip }}</td>
                    <td>{{ $log->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="3">{{ __('No recent activity') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>