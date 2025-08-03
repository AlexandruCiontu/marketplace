<x-filament-panels::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">OSS Reports</h2>

        @if(empty($reports))
            <p>No reports available.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Period</th>
                        <th class="px-4 py-2 text-left">Vendor</th>
                        <th class="px-4 py-2 text-left">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($reports as $report)
                        <tr>
                            <td class="px-4 py-2">{{ $report['period'] }}</td>
                            <td class="px-4 py-2">{{ $report['vendor'] }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ $report['url'] }}" class="text-primary-600 hover:underline">Download</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-filament-panels::page>
