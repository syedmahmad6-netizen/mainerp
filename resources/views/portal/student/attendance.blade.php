@extends('portal.student.layout')
@section('title','Attendance')
@section('content')
<div class="flex items-center gap-3 mb-5">
  <a href="{{ route('student.dashboard') }}" class="text-gray-400 hover:text-gray-600 text-xl">←</a>
  <h2 class="text-lg font-bold text-gray-800">My Attendance</h2>
</div>
<form method="GET" class="mb-5 flex gap-2">
  <input type="month" name="month" value="{{ $month }}" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
  <button class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-medium">Go</button>
</form>
<div class="grid grid-cols-4 gap-3 mb-5">
  @foreach([['label'=>'Present','key'=>'present','c'=>'emerald'],['label'=>'Absent','key'=>'absent','c'=>'red'],['label'=>'Late','key'=>'late','c'=>'yellow'],['label'=>'Leave','key'=>'leave','c'=>'blue']] as $s)
    <div class="bg-white rounded-xl p-3 text-center shadow-sm border border-gray-100">
      <div class="text-2xl font-bold text-{{ $s['c'] }}-600">{{ $summary[$s['key']] }}</div>
      <div class="text-xs text-gray-500 mt-1">{{ $s['label'] }}</div>
    </div>
  @endforeach
</div>
<div class="bg-white rounded-xl p-4 mb-5 shadow-sm border border-gray-100">
  <div class="flex justify-between text-sm mb-2">
    <span class="font-medium text-gray-700">Attendance Rate</span>
    <span class="font-bold {{ $pct>=75?'text-emerald-600':'text-red-500' }}">{{ $pct }}%</span>
  </div>
  <div class="bg-gray-100 rounded-full h-3">
    <div class="h-3 rounded-full {{ $pct>=75?'bg-emerald-500':'bg-red-500' }}" style="width:{{ min($pct,100) }}%"></div>
  </div>
  @if($pct<75)<p class="text-xs text-red-500 mt-2">⚠️ Your attendance is below 75%. Please attend regularly.</p>@endif
</div>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
  <h3 class="font-semibold text-gray-700 mb-4">{{ $from->format('F Y') }}</h3>
  <div class="grid grid-cols-7 gap-1 mb-2">
    @foreach(['Mo','Tu','We','Th','Fr','Sa','Su'] as $d)
      <div class="text-center text-xs text-gray-400 font-medium py-1">{{ $d }}</div>
    @endforeach
  </div>
  <div class="grid grid-cols-7 gap-1">
    @for($i=0;$i<($from->dayOfWeekIso-1);$i++)<div></div>@endfor
    @for($day=1;$day<=$to->day;$day++)
      @php $dk=$from->copy()->setDay($day)->format('Y-m-d'); $rec=$records[$dk]??null; $isWe=$from->copy()->setDay($day)->isWeekend();
      $col=$rec?match($rec->status){'present'=>'bg-emerald-100 text-emerald-700 font-semibold','absent'=>'bg-red-100 text-red-700 font-semibold','late'=>'bg-yellow-100 text-yellow-700 font-semibold','leave'=>'bg-blue-100 text-blue-700 font-semibold',default=>'bg-gray-100'}:($isWe?'bg-gray-50 text-gray-300':'bg-gray-50 text-gray-400'); @endphp
      <div class="aspect-square flex items-center justify-center rounded-lg text-xs {{ $col }}" title="{{ $rec?ucfirst($rec->status):'' }}">{{ $day }}</div>
    @endfor
  </div>
  <div class="flex flex-wrap gap-3 mt-4 text-xs text-gray-500">
    <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-emerald-100 inline-block"></span>Present</span>
    <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-red-100 inline-block"></span>Absent</span>
    <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-yellow-100 inline-block"></span>Late</span>
    <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-blue-100 inline-block"></span>Leave</span>
  </div>
</div>
@endsection