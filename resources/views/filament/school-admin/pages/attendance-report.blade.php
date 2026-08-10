<x-filament-panels::page>
<x-filament-panels::form wire:submit="generate">
    {{ $this->form }}
    <x-filament-panels::form.actions :actions="$this->getFormActions()" />
</x-filament-panels::form>

@if(!empty($this->report))
<div class="mt-6">
    <div class="flex justify-between items-center mb-3">
        <h3 class="font-semibold text-gray-700">{{ count($this->report) }} Students</h3>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm text-gray-700 font-medium">🖨️ Print</button>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500">#</th>
                        <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500">Adm. No.</th>
                        <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500">Student Name</th>
                        @if(isset($this->report[0]['present']))
                            <th class="border border-gray-100 px-3 py-2 text-center text-xs text-emerald-600">P</th>
                            <th class="border border-gray-100 px-3 py-2 text-center text-xs text-red-600">A</th>
                            <th class="border border-gray-100 px-3 py-2 text-center text-xs text-yellow-600">L</th>
                            <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">Total</th>
                            <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">%</th>
                            <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">Status</th>
                        @else
                            <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">Status</th>
                            <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500">Remarks</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->report as $i => $row)
                    <tr class="{{ isset($row['status']) && $row['status']==='low'?'bg-red-50':'bg-white hover:bg-gray-50' }}">
                        <td class="border border-gray-100 px-3 py-2 text-gray-400 text-xs">{{ $i+1 }}</td>
                        <td class="border border-gray-100 px-3 py-2 font-mono text-xs text-gray-500">{{ $row['admission_no'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 font-medium text-gray-800 text-sm">{{ $row['name'] }}</td>
                        @if(isset($row['present']))
                            <td class="border border-gray-100 px-3 py-2 text-center font-semibold text-emerald-600">{{ $row['present'] }}</td>
                            <td class="border border-gray-100 px-3 py-2 text-center font-semibold text-red-600">{{ $row['absent'] }}</td>
                            <td class="border border-gray-100 px-3 py-2 text-center text-yellow-600">{{ $row['late'] }}</td>
                            <td class="border border-gray-100 px-3 py-2 text-center text-gray-600">{{ $row['total'] }}</td>
                            <td class="border border-gray-100 px-3 py-2 text-center font-bold {{ $row['percentage']>=$this->threshold?'text-emerald-600':'text-red-600' }}">{{ $row['percentage'] }}%</td>
                            <td class="border border-gray-100 px-3 py-2 text-center">
                                @if($row['status']==='good')
                                    <span class="text-xs px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full">✅ Good</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full">⚠️ Low</span>
                                @endif
                            </td>
                        @else
                            <td class="border border-gray-100 px-3 py-2 text-center">
                                @php $s=$row['status']??'not_marked'; @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                    {{ $s==='present'?'bg-emerald-100 text-emerald-700':($s==='absent'?'bg-red-100 text-red-700':($s==='late'?'bg-yellow-100 text-yellow-700':($s==='leave'?'bg-blue-100 text-blue-700':'bg-gray-100 text-gray-500'))) }}">
                                    {{ ucfirst($s) }}
                                </span>
                            </td>
                            <td class="border border-gray-100 px-3 py-2 text-xs text-gray-500">{{ $row['remarks']??'—' }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>