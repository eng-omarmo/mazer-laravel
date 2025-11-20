<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Recent Activity') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Your latest actions for transparency') }}</p>
    </header>

    <div class="mt-4">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left">
                        <th class="py-2 pr-4">{{ __('Action') }}</th>
                        <th class="py-2 pr-4">{{ __('IP') }}</th>
                        <th class="py-2">{{ __('Time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-t">
                            <td class="py-2 pr-4">{{ str_replace('_',' ', ucfirst($log->action)) }}</td>
                            <td class="py-2 pr-4">{{ $log->ip }}</td>
                            <td class="py-2">{{ $log->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-2">{{ __('No recent activity') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>