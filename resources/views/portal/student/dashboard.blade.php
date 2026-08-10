@extends('portal.student.layout')
@section('title','Dashboard')
@section('content')
<div class="mb-5">
  <h2 class="text-xl font-bold text-gray-800">Hi, {{ auth()->user()->name }} 👋</h2>
  <p class="text-sm text-gray-500 mt-0.5">
    {{ $student->section?->schoolClass?->name }}
    @if($student->section) – Section {{ $student->section->name }} @endif
    &nbsp;·&nbsp; Adm# {{ $student->admission_number }}
  </p>
</div>

{{-- Stats Row --}}
<div class="grid grid-cols-3 gap-3 mb-5">
  <a href="{{ route('student.attendance') }}" class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100 hover:shadow-md transition">
    <div class="text-2xl font-bold {{ $attPct>=75?'text-emerald-600':'text-red-500' }}">{{ $attPct }}%</div>
    <div class="text-xs text-gray-500 mt-1">Attendance</div>
    <div class="text-xs {{ $attPct>=75?'text-emerald-500':'text-red-400' }} mt-0.5">{{ $attPct>=75?'Good':'Low ⚠️' }}</div>
  </a>
  <a href="{{ route('student.results') }}" class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100 hover:shadow-md transition">
    <div class="text-2xl font-bold text-blue-600">{{ $latestResult?->grade??'—' }}</div>
    <div class="text-xs text-gray-500 mt-1">Last Grade</div>
    <div class="text-xs text-gray-400 mt-0.5">{{ $latestResult?->exam?->name??'No results yet' }}</div>
  </a>
  <a href="{{ route('student.fees') }}" class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100 hover:shadow-md transition">
    <div class="text-2xl font-bold {{ $pendingFees>0?'text-orange-500':'text-emerald-600' }}">
      {{ $pendingFees>0?'⚠️':'✓' }}
    </div>
    <div class="text-xs text-gray-500 mt-1">Fees</div>
    <div class="text-xs {{ $pendingFees>0?'text-orange-400':'text-emerald-500' }} mt-0.5">
      {{ $pendingFees>0?'Rs.'.number_format($pendingFees).' due':'All clear' }}
    </div>
  </a>
</div>

{{-- Today's Classes --}}
@if($todaySlots && $todaySlots->count())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
  <h3 class="font-semibold text-gray-700 mb-3">📅 Today's Classes — {{ now()->format('l') }}</h3>
  <div class="space-y-2">
    @foreach($todaySlots as $slot)
      @if(!($slot->timeSlot?->is_break))
      <div class="flex items-center gap-3 p-2.5 rounded-xl" style="background:{{ $slot->subject?->color??'#e0f2fe' }}22; border-left:3px solid {{ $slot->subject?->color??'#0ea5e9' }}">
        <div class="text-xs text-gray-500 w-16 shrink-0 font-mono">
          {{ \Carbon\Carbon::parse($slot->timeSlot?->start_time)->format('h:i A') }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-800 truncate">{{ $slot->subject?->name }}</p>
          <p class="text-xs text-gray-500">{{ $slot->teacher?->name }}</p>
        </div>
        @if($slot->timeSlot?->name)
          <div class="text-xs text-gray-400 shrink-0">{{ $slot->timeSlot->name }}</div>
        @endif
      </div>
      @endif
    @endforeach
  </div>
</div>
@endif

{{-- Announcements --}}
@if($announcements->count())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
  <h3 class="font-semibold text-gray-700 mb-3">📢 Recent Notices</h3>
  @foreach($announcements as $a)
    <div class="border-b border-gray-50 last:border-0 pb-3 mb-3 last:mb-0">
      <div class="flex items-center gap-2 mb-1">
        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $a->type==='urgent'?'bg-red-100 text-red-700':($a->type==='holiday'?'bg-green-100 text-green-700':'bg-blue-100 text-blue-700') }}">
          {{ $a->type_label }}
        </span>
        <span class="text-xs text-gray-400">{{ $a->published_at?->format('d M') }}</span>
      </div>
      <p class="text-sm font-medium text-gray-800">{{ $a->title }}</p>
    </div>
  @endforeach
  <a href="{{ route('student.announcements') }}" class="text-xs text-cp font-medium mt-1 inline-block">View all →</a>
</div>
@endif
@endsection