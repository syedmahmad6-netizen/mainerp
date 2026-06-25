@extends('portal.parent.layout')
@section('title','Dashboard')
@section('content')
<div class="mb-5">
  <h2 class="text-xl font-bold text-gray-800">Welcome, {{ auth()->user()->name }}</h2>
  <p class="text-sm text-gray-500 mt-0.5">Here is a summary of your children.</p>
</div>
@forelse($childStats as $cs)
  @php $student=$cs['student']; @endphp
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5 overflow-hidden">
    <div class="bg-gray-50 border-b border-gray-100 px-5 py-4 flex items-center gap-4">
      <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-lg shrink-0">
        {{ strtoupper(substr($student->user->name??'S',0,1)) }}
      </div>
      <div>
        <p class="font-semibold text-gray-800">{{ $student->user->name }}</p>
        <p class="text-xs text-gray-500">{{ $student->section?->schoolClass?->name }} @if($student->section) – Sec {{ $student->section->name }} @endif · Adm# {{ $student->admission_number }}</p>
      </div>
    </div>
    <div class="grid grid-cols-3 divide-x divide-gray-100">
      <a href="{{ route('parent.attendance',$student) }}" class="p-4 text-center hover:bg-gray-50 transition">
        <div class="text-2xl font-bold {{ $cs['attendance_pct']>=75?'text-emerald-600':'text-red-500' }}">{{ $cs['attendance_pct'] }}%</div>
        <div class="text-xs text-gray-500 mt-1">Attendance</div>
      </a>
      <a href="{{ route('parent.fees',$student) }}" class="p-4 text-center hover:bg-gray-50 transition">
        <div class="text-2xl font-bold {{ $cs['pending_fees']>0?'text-orange-500':'text-emerald-600' }}">
          {{ $cs['pending_fees']>0?'Rs.'.number_format($cs['pending_fees']):'✓' }}
        </div>
        <div class="text-xs text-gray-500 mt-1">Fee Due</div>
      </a>
      <a href="{{ route('parent.results',$student) }}" class="p-4 text-center hover:bg-gray-50 transition">
        <div class="text-2xl font-bold text-blue-600">{{ $cs['latest_exam']?->grade??'—' }}</div>
        <div class="text-xs text-gray-500 mt-1">Last Grade</div>
      </a>
    </div>
    <div class="px-5 py-3 flex gap-2 flex-wrap border-t border-gray-100">
      <a href="{{ route('parent.attendance',$student) }}" class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 transition">📅 Attendance</a>
      <a href="{{ route('parent.results',$student) }}"    class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 transition">📝 Results</a>
      <a href="{{ route('parent.fees',$student) }}"       class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 transition">💰 Fees</a>
      <a href="{{ route('parent.timetable',$student) }}"  class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 transition">🗓 Timetable</a>
    </div>
  </div>
@empty
  <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">No children linked. Contact the school.</div>
@endforelse
@if($announcements->count())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
  <h3 class="font-semibold text-gray-700 mb-4">📢 Recent Notices</h3>
  @foreach($announcements as $a)
    <div class="border-b border-gray-50 last:border-0 pb-3 mb-3 last:mb-0">
      <div class="flex items-center gap-2 mb-1">
        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $a->type==='urgent'?'bg-red-100 text-red-700':($a->type==='holiday'?'bg-green-100 text-green-700':'bg-blue-100 text-blue-700') }}">{{ $a->type_label }}</span>
        <span class="text-xs text-gray-400">{{ $a->published_at?->format('d M') }}</span>
      </div>
      <p class="text-sm font-medium text-gray-800">{{ $a->title }}</p>
    </div>
  @endforeach
  <a href="{{ route('parent.announcements') }}" class="text-xs text-cp font-medium mt-2 inline-block">View all →</a>
</div>
@endif
@endsection