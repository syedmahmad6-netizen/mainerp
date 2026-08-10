@extends('portal.student.layout')
@section('title','Timetable')
@section('content')
<div class="flex items-center gap-3 mb-5">
  <a href="{{ route('student.dashboard') }}" class="text-gray-400 hover:text-gray-600 text-xl">←</a>
  <h2 class="text-lg font-bold text-gray-800">My Timetable</h2>
</div>
@if(empty($grid))
  <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">No timetable set up yet.</div>
@else
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-xs border-collapse min-w-max">
      <thead class="bg-gray-50"><tr>
        <th class="border border-gray-100 px-3 py-2 text-left text-gray-600 font-medium w-24">Period</th>
        @foreach($days as $dn=>$d)<th class="border border-gray-100 px-3 py-2 text-center text-gray-600 font-medium">{{ $d }}</th>@endforeach
      </tr></thead>
      <tbody>
        @foreach($slots as $slot)
        <tr class="{{ $slot->is_break?'bg-amber-50':'hover:bg-gray-50' }}">
          <td class="border border-gray-100 px-3 py-2">
            <div class="font-medium text-gray-700">{{ $slot->name }}</div>
            <div class="text-gray-400">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}</div>
          </td>
          @foreach($days as $dn=>$d)
            @php $e=$grid[$dn][$slot->id]??null; @endphp
            <td class="border border-gray-100 px-2 py-1.5 text-center">
              @if($slot->is_break)<span class="text-amber-500 font-medium">Break</span>
              @elseif($e)
                <div class="rounded px-1.5 py-1 text-left" style="background:{{ $e->subject?->color??'#e0f2fe' }}22;border-left:2px solid {{ $e->subject?->color??'#0ea5e9' }}">
                  <div class="font-semibold text-gray-800">{{ $e->subject?->name }}</div>
                  <div class="text-gray-500 text-xs">{{ $e->teacher?->name }}</div>
                </div>
              @else<span class="text-gray-200">—</span>@endif
            </td>
          @endforeach
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection