<x-filament-panels::page>
<x-filament-panels::form wire:submit="generate">
    {{ $this->form }}
    <x-filament-panels::form.actions :actions="$this->getFormActions()" />
</x-filament-panels::form>

@if(!empty($this->report))
<div class="mt-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
            <div class="text-2xl font-bold text-blue-600">Rs. {{ number_format($this->summary['total_due']) }}</div>
            <div class="text-xs text-gray-500 mt-1">Total Due</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
            <div class="text-2xl font-bold text-emerald-600">Rs. {{ number_format($this->summary['total_collected']) }}</div>
            <div class="text-xs text-gray-500 mt-1">Collected</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
            <div class="text-2xl font-bold text-orange-500">Rs. {{ number_format($this->summary['total_balance']) }}</div>
            <div class="text-xs text-gray-500 mt-1">Outstanding</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
            <div class="text-2xl font-bold text-gray-700">{{ $this->summary['total_records'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Total Records</div>
        </div>
    </div>

    {{-- Print Button --}}
    <div class="flex justify-between items-center mb-3">
        <h3 class="font-semibold text-gray-700">{{ count($this->report) }} Records</h3>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm text-gray-700 font-medium">🖨️ Print</button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500 font-medium">#</th>
                        <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500 font-medium">Student</th>
                        <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500 font-medium">Class</th>
                        <th class="border border-gray-100 px-3 py-2 text-left text-xs text-gray-500 font-medium">Fee Type</th>
                        <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500 font-medium">Month</th>
                        <th class="border border-gray-100 px-3 py-2 text-right text-xs text-gray-500 font-medium">Due</th>
                        <th class="border border-gray-100 px-3 py-2 text-right text-xs text-gray-500 font-medium">Paid</th>
                        <th class="border border-gray-100 px-3 py-2 text-right text-xs text-gray-500 font-medium">Balance</th>
                        <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500 font-medium">Status</th>
                        <th class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-500 font-medium">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->report as $i => $row)
                    <tr class="{{ $row['status']==='overdue'?'bg-red-50':($row['status']==='paid'?'':'bg-orange-50') }} hover:bg-gray-50">
                        <td class="border border-gray-100 px-3 py-2 text-gray-400 text-xs">{{ $i+1 }}</td>
                        <td class="border border-gray-100 px-3 py-2">
                            <div class="font-medium text-gray-800 text-xs">{{ $row['student'] }}</div>
                            <div class="text-gray-400 text-xs font-mono">{{ $row['admission_no'] }}</div>
                        </td>
                        <td class="border border-gray-100 px-3 py-2 text-xs text-gray-600">{{ $row['class'] }} – {{ $row['section'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-xs text-gray-600">{{ $row['fee_type'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-center text-xs text-gray-600">{{ $row['month'] }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-right text-xs font-mono">{{ number_format($row['amount_due']) }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-right text-xs font-mono text-emerald-600">{{ number_format($row['amount_paid']) }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-right text-xs font-mono font-bold {{ $row['balance']>0?'text-red-600':'text-emerald-600' }}">{{ number_format($row['balance']) }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $row['status']==='paid'?'bg-emerald-100 text-emerald-700':($row['status']==='overdue'?'bg-red-100 text-red-700':($row['status']==='partial'?'bg-yellow-100 text-yellow-700':'bg-orange-100 text-orange-700')) }}">
                                {{ ucfirst($row['status']) }}
                            </span>
                        </td>
                        <td class="border border-gray-100 px-3 py-2 text-center text-xs font-mono text-gray-500">{{ $row['receipt_no'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 font-semibold">
                    <tr>
                        <td colspan="5" class="border border-gray-100 px-3 py-2 text-xs text-gray-600">Totals</td>
                        <td class="border border-gray-100 px-3 py-2 text-right text-xs font-mono">Rs. {{ number_format($this->summary['total_due']) }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-right text-xs font-mono text-emerald-600">Rs. {{ number_format($this->summary['total_collected']) }}</td>
                        <td class="border border-gray-100 px-3 py-2 text-right text-xs font-mono text-red-600">Rs. {{ number_format($this->summary['total_balance']) }}</td>
                        <td colspan="2" class="border border-gray-100 px-3 py-2 text-xs text-gray-400">{{ $this->summary['paid_count'] }} paid · {{ $this->summary['pending_count'] }} pending</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>