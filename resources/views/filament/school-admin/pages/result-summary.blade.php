<x-filament-panels::page>
    <x-filament-panels::form wire:submit="generate">
        {{ $this->form }}
        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>

    @if(!empty($this->results))
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $this->examName }}</h2>
                    <p class="text-sm text-gray-500">{{ $this->sectionName }} &mdash; {{ count($this->results) }} Students</p>
                </div>
                <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium">Print</button>
            </div>
            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border border-gray-200 px-3 py-2 text-left">Rank</th>
                            <th class="border border-gray-200 px-3 py-2 text-left">Adm. No.</th>
                            <th class="border border-gray-200 px-3 py-2 text-left">Student Name</th>
                            <th class="border border-gray-200 px-3 py-2 text-center">Total</th>
                            <th class="border border-gray-200 px-3 py-2 text-center">%</th>
                            <th class="border border-gray-200 px-3 py-2 text-center">Grade</th>
                            <th class="border border-gray-200 px-3 py-2 text-center">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->results as $row)
                        <tr class="{{ $row["result"]==="FAIL" ? "bg-red-50" : "bg-white hover:bg-gray-50" }}">
                            <td class="border border-gray-200 px-3 py-2 text-center font-bold">{{ $row["rank"] }}</td>
                            <td class="border border-gray-200 px-3 py-2 font-mono text-xs text-gray-500">{{ $row["admission_no"] }}</td>
                            <td class="border border-gray-200 px-3 py-2 font-medium text-gray-800">{{ $row["name"] }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-center font-semibold">{{ $row["total_obtained"] }}/{{ $row["total_marks"] }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-center font-bold {{ $row["percentage"]>=40 ? "text-green-600" : "text-red-600" }}">{{ $row["percentage"] }}%</td>
                            <td class="border border-gray-200 px-3 py-2 text-center">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ in_array($row["overall_grade"],["A1","A"]) ? "bg-green-100 text-green-700" : (in_array($row["overall_grade"],["B","C"]) ? "bg-blue-100 text-blue-700" : (in_array($row["overall_grade"],["D","E"]) ? "bg-yellow-100 text-yellow-700" : "bg-red-100 text-red-700")) }}">
                                    {{ $row["overall_grade"] }}
                                </span>
                            </td>
                            <td class="border border-gray-200 px-3 py-2 text-center">
                                <span class="{{ $row["result"]==="PASS" ? "text-green-600 font-bold" : "text-red-600 font-bold" }}">{{ $row["result"] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>