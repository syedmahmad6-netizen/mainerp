<x-filament-panels::page>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-700">SMS Log (Last 100)</h3>
            <span class="text-xs text-gray-400">{{ count($this->logs) }} records</span>
        </div>
        @if(empty($this->logs))
            <div class="p-10 text-center text-gray-400">No SMS sent yet.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs text-gray-500 font-medium">Recipient</th>
                        <th class="px-4 py-2 text-left text-xs text-gray-500 font-medium">Message</th>
                        <th class="px-4 py-2 text-center text-xs text-gray-500 font-medium">Type</th>
                        <th class="px-4 py-2 text-center text-xs text-gray-500 font-medium">Status</th>
                        <th class="px-4 py-2 text-right text-xs text-gray-500 font-medium">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($this->logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5">
                            <div class="font-medium text-gray-800 text-xs">{{ $log['recipient_name'] ?: '—' }}</div>
                            <div class="text-gray-500 text-xs font-mono">{{ $log['recipient_phone'] }}</div>
                        </td>
                        <td class="px-4 py-2.5 max-w-xs">
                            <div class="text-xs text-gray-600 line-clamp-2">{{ $log['message'] }}</div>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">{{ str_replace('_',' ',ucfirst($log['trigger_type'])) }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $log['status']==='sent'?'bg-emerald-100 text-emerald-700':($log['status']==='failed'?'bg-red-100 text-red-700':'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($log['status']) }}
                            </span>
                            @if($log['error_message'])
                                <div class="text-xs text-red-400 mt-0.5">{{ $log['error_message'] }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($log['created_at'])->format('d M, h:i A') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</x-filament-panels::page>