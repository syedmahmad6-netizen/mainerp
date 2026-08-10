<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $school->name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
@php $pc = $school->setting('primary_color','#059669'); @endphp
<style>:root{--cp:{{ $pc }}}.bg-cp{background-color:var(--cp)}.text-cp{color:var(--cp)}.border-cp{border-color:var(--cp)}</style>
</head>
<body class="bg-gray-50 min-h-screen">

{{-- School Header --}}
<header class="bg-cp text-white">
  <div class="max-w-4xl mx-auto px-6 py-8 text-center">
    @if($school->logo)
      <img src="{{ asset('storage/'.$school->logo) }}" class="h-20 w-20 rounded-full object-cover mx-auto mb-4 border-4 border-white shadow-lg">
    @else
      <div class="h-20 w-20 rounded-full bg-white bg-opacity-20 flex items-center justify-center mx-auto mb-4 text-3xl font-bold border-4 border-white shadow-lg">
        {{ strtoupper(substr($school->name,0,2)) }}
      </div>
    @endif
    <h1 class="text-2xl md:text-3xl font-extrabold">{{ $school->name }}</h1>
    @if($school->address)
      <p class="text-white text-opacity-80 text-sm mt-1">📍 {{ $school->city }}</p>
    @endif
  </div>
</header>

{{-- Login Cards --}}
<section class="max-w-4xl mx-auto px-6 py-10">
  <h2 class="text-center text-lg font-semibold text-gray-700 mb-6">Select your portal to login</h2>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">

    {{-- Parent Login --}}
    <a href="/login" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-1 transition-all text-center block">
      <div class="text-5xl mb-4">👨‍👩‍👦</div>
      <h3 class="font-bold text-gray-800 text-lg mb-1">Parent Portal</h3>
      <p class="text-sm text-gray-500 mb-4">View your child's attendance, results, fees, and timetable</p>
      <div class="inline-block bg-emerald-600 text-white text-sm font-semibold px-5 py-2 rounded-xl">Login as Parent</div>
    </a>

    {{-- Student Login --}}
    <a href="/login" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-1 transition-all text-center block">
      <div class="text-5xl mb-4">🎓</div>
      <h3 class="font-bold text-gray-800 text-lg mb-1">Student Portal</h3>
      <p class="text-sm text-gray-500 mb-4">Check your results, attendance, timetable, and notices</p>
      <div class="inline-block bg-blue-600 text-white text-sm font-semibold px-5 py-2 rounded-xl">Login as Student</div>
    </a>

    {{-- Staff Login --}}
    <a href="/admin" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-1 transition-all text-center block">
      <div class="text-5xl mb-4">👩‍🏫</div>
      <h3 class="font-bold text-gray-800 text-lg mb-1">Staff Portal</h3>
      <p class="text-sm text-gray-500 mb-4">For teachers, principal, and school management staff</p>
      <div class="inline-block bg-purple-600 text-white text-sm font-semibold px-5 py-2 rounded-xl">Login as Staff</div>
    </a>

  </div>

  {{-- Announcements --}}
  @if($announcements->count())
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-bold text-gray-800 text-lg mb-4">📢 Latest Announcements</h3>
    <div class="space-y-4">
      @foreach($announcements as $a)
        <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
          <div class="flex items-center gap-2 mb-1.5">
            <span class="text-xs px-2.5 py-1 rounded-full font-medium
              {{ $a->type==='urgent'?'bg-red-100 text-red-700':($a->type==='holiday'?'bg-green-100 text-green-700':($a->type==='event'?'bg-orange-100 text-orange-700':'bg-blue-100 text-blue-700')) }}">
              {{ $a->type_label }}
            </span>
            <span class="text-xs text-gray-400">{{ $a->published_at?->format('d M Y') }}</span>
          </div>
          <h4 class="font-semibold text-gray-800">{{ $a->title }}</h4>
          <div class="text-sm text-gray-600 mt-1 leading-relaxed line-clamp-2">{!! strip_tags($a->body) !!}</div>
        </div>
      @endforeach
    </div>
  </div>
  @endif

</section>

{{-- Footer --}}
<footer class="text-center text-xs text-gray-400 py-6 border-t border-gray-100 mt-auto">
  @if($school->phone)<p>📞 {{ $school->phone }}</p>@endif
  @if($school->email)<p>✉️ {{ $school->email }}</p>@endif
  <p class="mt-3 opacity-60">Powered by <a href="https://gnosis.ac.pk" class="underline">Gnosis Education Systems</a></p>
</footer>

</body>
</html>