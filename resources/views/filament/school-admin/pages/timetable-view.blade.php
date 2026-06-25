<x-filament-panels::page>

    {{-- Filter Form --}}
    <x-filament-panels::form wire:submit="loadTimetable">
        {{ $this->form }}
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
            :full-width="false"
        />
    </x-filament-panels::form>

    {{-- Timetable Grid --}}
    @if(!empty($this->grid) || (isset($this->timeSlots) && count($this->timeSlots) > 0 && !empty($this->viewTitle)))
        <div class="mt-8">

            {{-- Title + Print button --}}
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    {{ $this->viewTitle }}
                </h2>
                <button
                    onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition"
                >
                    🖨️ Print
                </button>
            </div>

            {{-- Timetable Table --}}
            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                <table class="w-full text-sm border-collapse">

                    {{-- Header row: days --}}
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-200 px-3 py-3 text-left text-gray-600 font-semibold w-32">
                                Period
                            </th>
                            @foreach($this->days as $dayNum => $dayName)
                                <th class="border border-gray-200 px-3 py-3 text-center text-gray-700 font-semibold">
                                    {{ $dayName }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($this->timeSlots as $slot)
                            @php $isBreak = $slot['is_break'] ?? false; @endphp

                            <tr class="{{ $isBreak ? 'bg-amber-50' : 'bg-white hover:bg-gray-50' }}">

                                {{-- Period name + time --}}
                                <td class="border border-gray-200 px-3 py-3">
                                    <div class="font-semibold text-gray-800 text-xs">
                                        {{ $slot['name'] }}
                                    </div>
                                    <div class="text-gray-400 text-xs mt-0.5">
                                        @php
                                            $start = \Carbon\Carbon::parse($slot['start_time'])->format('h:i A');
                                            $end   = \Carbon\Carbon::parse($slot['end_time'])->format('h:i A');
                                        @endphp
                                        {{ $start }} – {{ $end }}
                                    </div>
                                </td>

                                {{-- Each day cell --}}
                                @foreach($this->days as $dayNum => $dayName)
                                    @php
                                        $entry = $this->grid[$dayNum][$slot['id']] ?? null;
                                    @endphp

                                    <td class="border border-gray-200 px-3 py-3 text-center align-middle">
                                        @if($isBreak)
                                            <span class="text-amber-600 text-xs font-medium">Break</span>

                                        @elseif($entry)
                                            @php
                                                $color = $entry->subject->color ?? '#6366f1';
                                                // Convert hex to light background (20% opacity)
                                                $bgStyle = "background-color: {$color}22; border-left: 3px solid {$color};";
                                            @endphp
                                            <div
                                                class="rounded-lg px-2 py-1.5 text-left"
                                                style="{{ $bgStyle }}"
                                            >
                                                <div class="font-semibold text-gray-800 text-xs leading-tight">
                                                    {{ $entry->subject->name ?? '—' }}
                                                    @if($entry->subject->code)
                                                        <span class="text-gray-400">({{ $entry->subject->code }})</span>
                                                    @endif
                                                </div>
                                                <div class="text-gray-500 text-xs mt-0.5">
                                                    👤 {{ $entry->teacher->name ?? '—' }}
                                                </div>

                                                {{-- Show section name in Teacher view --}}
                                                @if(isset($entry->section) && ($this->data['view_mode'] ?? 'class') === 'teacher')
                                                    <div class="text-gray-400 text-xs">
                                                        🏫 {{ $entry->section->schoolClass->name ?? '' }} – {{ $entry->section->name ?? '' }}
                                                    </div>
                                                @endif
                                            </div>

                                        @else
                                            <span class="text-gray-200 text-xs">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="mt-4 text-xs text-gray-400">
                💡 Colored cells show assigned subjects. Each color corresponds to a subject.
                Print this page or share with students.
            </div>

        </div>

    @elseif(!empty($this->viewTitle) && empty($this->grid))
        <div class="mt-8 text-center py-12 text-gray-400">
            <p class="text-lg">No timetable entries found.</p>
            <p class="text-sm mt-1">Add entries from <strong>Timetable Entries</strong> and try again.</p>
        </div>

    @else
        <div class="mt-8 text-center py-12 text-gray-300">
            <p>Select a class or teacher above, then click <strong>Show Timetable</strong>.</p>
        </div>
    @endif

</x-filament-panels::page>
