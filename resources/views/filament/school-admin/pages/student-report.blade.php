<x-filament-panels::page>
<x-filament-panels::form wire:submit="generate">
    {{ $this->form }}
    <x-filament-panels::form.actions :actions="$this->getFormActions()" />
</x-filament-panels::form>

@if(!empty($this->report))
<div class="mt-6">
    <div class="flex justify-between items-center mb-3">
        <h3 class="font-semibold text-gray-700">{{ $this->totalCount }} Students</h3>
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
                        <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500">Father's Name</th>
                        <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">Class</th>
                        <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">Gender</th>
                        <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">Status</th>
                        <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">Contact</th>
                        <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">Admitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->report as $i => $row)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-100 px-3 py-2 text-gray-400 text-xs">{{ $i+1 }}</td>
                        <td class="border border-gray-100 px-3 py-2 font-mono text-xs text-gray-500">{{ $row['admission_no'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 font-medium text-gray-800">{{ $row['name'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-xs text-gray-600">{{ $row['father_name'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-center text-xs">
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ $row['class'] }}{{ $row['section']!=='—'?' – '.$row['section']:'' }}</span>
                        </td>
                        <td class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-600">{{ $row['gender'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $row['status']==='Active'?'bg-emerald-100 text-emerald-700':'bg-gray-100 text-gray-500' }}">{{ $row['status'] }}</span>
                        </td>
                        <td class="border border-gray-100 px-3 py-2 text-center text-xs font-mono text-gray-600">{{ $row['parent_phone']!=='—'?$row['parent_phone']:$row['phone'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500">{{ $row['admission_date'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>