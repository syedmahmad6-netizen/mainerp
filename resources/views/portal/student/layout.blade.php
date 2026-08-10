<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>@yield('title','Student Portal') — {{ tenant()->getSchool()->name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
@php $school=tenant()->getSchool(); $pc=$school->setting('primary_color','#059669'); @endphp
<style>:root{--cp:{{ $pc }}}.bg-cp{background-color:var(--cp)}.text-cp{color:var(--cp)}</style>
</head>
<body class="bg-gray-50 min-h-screen pb-20 md:pb-0">
<header class="bg-cp text-white shadow-md sticky top-0 z-30">
  <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      @if($school->logo)
        <img src="{{ asset('storage/'.$school->logo) }}" class="h-8 w-8 rounded-full object-cover">
      @else
        <div class="h-8 w-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($school->name,0,2)) }}</div>
      @endif
      <div>
        <p class="font-semibold text-sm leading-none">{{ $school->name }}</p>
        <p class="text-xs opacity-80 mt-0.5">Student Portal</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-sm hidden sm:block opacity-90">{{ auth()->user()->name }}</span>
      <form method="POST" action="{{ route('portal.logout') }}">@csrf
        <button class="text-xs bg-white bg-opacity-20 hover:bg-opacity-30 px-3 py-1.5 rounded-lg transition">Logout</button>
      </form>
    </div>
  </div>
</header>
<div class="max-w-4xl mx-auto md:flex md:gap-6 md:p-6">
  <nav class="hidden md:block w-48 shrink-0">
    <div class="bg-white rounded-2xl shadow-sm p-3 sticky top-20">
      @php $navItems=[
        ['route'=>'student.dashboard',     'label'=>'Dashboard',    'icon'=>'🏠'],
        ['route'=>'student.attendance',    'label'=>'Attendance',   'icon'=>'📅'],
        ['route'=>'student.results',       'label'=>'Results',      'icon'=>'📝'],
        ['route'=>'student.fees',          'label'=>'Fees',         'icon'=>'💰'],
        ['route'=>'student.timetable',     'label'=>'Timetable',    'icon'=>'🗓'],
        ['route'=>'student.announcements', 'label'=>'Notices',      'icon'=>'📢'],
      ]; @endphp
      @foreach($navItems as $item)
        <a href="{{ route($item['route']) }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium mb-1 transition {{ request()->routeIs($item['route'])?'bg-emerald-50 text-cp font-semibold':'text-gray-600 hover:bg-gray-50' }}">
          {{ $item['icon'] }} {{ $item['label'] }}
        </a>
      @endforeach
    </div>
  </nav>
  <main class="flex-1 min-w-0 p-4 md:p-0">@yield('content')</main>
</div>
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-30 flex">
  @foreach([['route'=>'student.dashboard','label'=>'Home','icon'=>'🏠'],['route'=>'student.attendance','label'=>'Attend','icon'=>'📅'],['route'=>'student.results','label'=>'Results','icon'=>'📝'],['route'=>'student.fees','label'=>'Fees','icon'=>'💰'],['route'=>'student.announcements','label'=>'Notices','icon'=>'📢']] as $item)
    <a href="{{ route($item['route']) }}" class="flex-1 flex flex-col items-center py-2 text-xs {{ request()->routeIs($item['route'])?'text-cp font-semibold':'text-gray-500' }}">
      <span class="text-xl mb-0.5">{{ $item['icon'] }}</span>{{ $item['label'] }}
    </a>
  @endforeach
</nav>
</body>
</html>