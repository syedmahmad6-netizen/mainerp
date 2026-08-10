<x-filament-panels::page>

{{-- Print styles — only applies when printing --}}
<style>
@media print {
    /* Hide everything except card grid */
    .fi-topbar, .fi-sidebar, .fi-header, .fi-page-header, form, .no-print {
        display: none !important;
    }
    body { background: white !important; }
    .card-grid {
        display: flex !important;
        flex-wrap: wrap;
        gap: 8mm;
        padding: 10mm;
    }
    .id-card {
        width: 85.6mm;
        height: 54mm;
        page-break-inside: avoid;
        break-inside: avoid;
    }
}
@media screen {
    .id-card { margin-bottom: 1rem; }
}
</style>

{{-- Filter Form --}}
<div class="no-print">
    <x-filament-panels::form wire:submit="generate">
        {{ $this->form }}
        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
</div>

{{-- ID Cards Grid --}}
@if(!empty($this->students))
@php $school = tenant()->getSchool(); $pc = $school->setting('primary_color','#059669'); @endphp

<div class="no-print mt-4 mb-2 flex items-center justify-between">
    <p class="text-sm text-gray-500">{{ count($this->students) }} cards ready. Click Print All or use Ctrl+P.</p>
    <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition">🖨️ Print All Cards</button>
</div>

<div class="card-grid flex flex-wrap gap-4">
    @foreach($this->students as $s)
    @php
        $name        = $s['user']['name'] ?? '—';
        $admNo       = $s['admission_number'] ?? '—';
        $rollNo      = $s['roll_number'] ?? '—';
        $dob         = isset($s['date_of_birth']) ? \Carbon\Carbon::parse($s['date_of_birth'])->format('d M Y') : '—';
        $className   = '—';
        $sectionName = '—';
        $yearName    = '—';
        $parentPhone = '—';
        if (!empty($s['section'])) {
            $className   = $s['section']['school_class']['name'] ?? '—';
            $sectionName = $s['section']['name'] ?? '—';
            $yearName    = $s['section']['academic_year']['name'] ?? '—';
        }
        if (!empty($s['parents'])) {
            $parentPhone = $s['parents'][0]['user']['phone'] ?? '—';
        }
        $photo = !empty($s['user']['profile_photo'])
            ? asset('storage/'.$s['user']['profile_photo'])
            : 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=fff&background='.ltrim($pc,'#').'&size=80';
    @endphp

    {{-- ID Card (CR80 size: 85.6mm × 54mm) --}}
    <div class="id-card" style="
        width: 340px;
        height: 215px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: white;
        position: relative;
        display: flex;
        flex-direction: column;
    ">
        {{-- Card Header (School Branding) --}}
        <div style="background: {{ $pc }}; padding: 8px 12px; display:flex; align-items:center; gap:8px;">
            @if($school->logo)
                <img src="{{ asset('storage/'.$school->logo) }}" style="height:28px;width:28px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.5);">
            @else
                <div style="height:28px;width:28px;border-radius:50%;background:rgba(255,255,255,0.25);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:11px;">
                    {{ strtoupper(substr($school->name,0,2)) }}
                </div>
            @endif
            <div style="flex:1;min-width:0;">
                <div style="color:white;font-weight:700;font-size:11px;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $school->name }}</div>
                <div style="color:rgba(255,255,255,0.8);font-size:9px;">Student Identity Card</div>
            </div>
            <div style="background:rgba(255,255,255,0.2);color:white;font-size:8px;padding:2px 6px;border-radius:4px;white-space:nowrap;">{{ $yearName }}</div>
        </div>

        {{-- Card Body --}}
        <div style="flex:1;display:flex;padding:10px 12px;gap:10px;">
            {{-- Photo --}}
            <div style="flex-shrink:0;">
                <img src="{{ $photo }}" style="width:62px;height:72px;object-fit:cover;border-radius:6px;border:2px solid #e5e7eb;">
            </div>

            {{-- Info --}}
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:6px;line-height:1.2;word-break:break-word;">{{ $name }}</div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:3px 8px;">
                    <div>
                        <div style="font-size:7.5px;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Class</div>
                        <div style="font-size:10px;font-weight:600;color:#1f2937;">{{ $className }} – {{ $sectionName }}</div>
                    </div>
                    <div>
                        <div style="font-size:7.5px;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Roll No.</div>
                        <div style="font-size:10px;font-weight:600;color:#1f2937;">{{ $rollNo !== '—' ? $rollNo : 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size:7.5px;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Adm. No.</div>
                        <div style="font-size:9px;font-weight:500;color:#374151;font-family:monospace;">{{ $admNo }}</div>
                    </div>
                    <div>
                        <div style="font-size:7.5px;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Date of Birth</div>
                        <div style="font-size:9px;color:#374151;">{{ $dob }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Footer --}}
        <div style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:5px 12px;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:8px;color:#6b7280;">
                📞 Parent: <span style="color:#374151;font-weight:500;">{{ $parentPhone }}</span>
            </div>
            <div style="font-size:7px;color:#9ca3af;">gnosis.ac.pk</div>
        </div>
    </div>
    @endforeach
</div>

@endif
</x-filament-panels::page>