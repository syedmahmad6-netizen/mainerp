<x-filament-panels::page>

    {{-- Filter Form --}}
    <x-filament-panels::form wire:submit="generate">
        {{ $this->form }}
        <x-filament-panels::form.actions
            :actions="[
                \Filament\Actions\Action::make('generate')
                    ->label('Generate Report')
                    ->submit('generate')
                    ->icon('heroicon-o-magnifying-glass')
            ]"
        />
    </x-filament-panels::form>

    {{-- Summary Table --}}
    @if(!empty($this->summary))
        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    Attendance Summary — {{ count($this->summary) }} Students
                </h3>
                <span class="text-sm text-gray-500">
                    ⚠️ Below {{ $this->threshold }}% = Low Attendance
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">#</th>
                            <th class="px-4 py-3 text-left font-medium">Adm. No.</th>
                            <th class="px-4 py-3 text-left font-medium">Student Name</th>
                            <th class="px-4 py-3 text-center font-medium text-green-600">Present</th>
                            <th class="px-4 py-3 text-center font-medium text-red-600">Absent</th>
                            <th class="px-4 py-3 text-center font-medium text-yellow-600">Late</th>
                            <th class="px-4 py-3 text-center font-medium text-blue-600">Leave</th>
                            <th class="px-4 py-3 text-center font-medium">Total Days</th>
                            <th class="px-4 py-3 text-center font-medium">Percentage</th>
                            <th class="px-4 py-3 text-center font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($this->summary as $i => $row)
                            <tr class="{{ $row['status'] === 'low' ? 'bg-red-50' : 'bg-white' }} hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $row['admission_no'] }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $row['name'] }}</td>
                                <td class="px-4 py-3 text-center text-green-700 font-semibold">{{ $row['present'] }}</td>
                                <td class="px-4 py-3 text-center text-red-700 font-semibold">{{ $row['absent'] }}</td>
                                <td class="px-4 py-3 text-center text-yellow-700">{{ $row['late'] }}</td>
                                <td class="px-4 py-3 text-center text-blue-700">{{ $row['leave'] }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $row['total'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($row['status'] === 'no_data')
                                        <span class="text-gray-400 text-xs">No data</span>
                                    @else
                                        <span class="font-bold {{ $row['percentage'] >= $this->threshold ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $row['percentage'] }}%
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($row['status'] === 'good')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">✅ Good</span>
                                    @elseif($row['status'] === 'low')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">⚠️ Low</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">— No Data</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Totals row --}}
                    <tfoot class="bg-gray-100 font-semibold text-gray-700">
                        <tr>
                            <td colspan="3" class="px-4 py-3">Total</td>
                            <td class="px-4 py-3 text-center text-green-700">{{ collect($this->summary)->sum('present') }}</td>
                            <td class="px-4 py-3 text-center text-red-700">{{ collect($this->summary)->sum('absent') }}</td>
                            <td class="px-4 py-3 text-center text-yellow-700">{{ collect($this->summary)->sum('late') }}</td>
                            <td class="px-4 py-3 text-center text-blue-700">{{ collect($this->summary)->sum('leave') }}</td>
                            <td class="px-4 py-3 text-center">{{ collect($this->summary)->sum('total') }}</td>
                            <td colspan="2" class="px-4 py-3 text-center text-xs text-gray-400">
                                {{ collect($this->summary)->where('status', 'low')->count() }} students below threshold
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @elseif(isset($this->data['section_id']))
        <div class="mt-6 text-center text-gray-400 py-10">
            Click "Generate Report" to load attendance data.
        </div>
    @endif

</x-filament-panels::page>
