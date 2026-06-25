<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Gnosis Education Systems — School ERP for Pakistan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">

{{-- Navbar --}}
<nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <div class="h-9 w-9 bg-emerald-600 rounded-xl flex items-center justify-center">
        <span class="text-white font-bold text-sm">G</span>
      </div>
      <span class="text-xl font-bold text-gray-900">Gnosis</span>
      <span class="text-sm text-gray-400 hidden sm:inline">Education Systems</span>
    </div>
    <a href="/super-admin" class="text-sm text-emerald-600 font-semibold hover:underline">Admin Login →</a>
  </div>
</nav>

{{-- Hero --}}
<section class="bg-gradient-to-br from-emerald-50 to-teal-100 py-20 px-6">
  <div class="max-w-4xl mx-auto text-center">
    <div class="inline-block bg-emerald-100 text-emerald-700 text-sm font-medium px-4 py-1.5 rounded-full mb-6">
      🎓 Built for Pakistani Private Schools
    </div>
    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-6">
      Complete School Management<br><span class="text-emerald-600">in One Platform</span>
    </h1>
    <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
      Manage students, teachers, fees, attendance, results, and parent communication — all from a single, secure, cloud-based platform.
    </p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="/super-admin" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-8 py-3.5 rounded-xl transition text-center">
        Get Started →
      </a>
      <a href="mailto:info@gnosis.ac.pk" class="bg-white border border-gray-200 hover:border-gray-300 text-gray-700 font-semibold px-8 py-3.5 rounded-xl transition text-center">
        Contact Us
      </a>
    </div>
  </div>
</section>

{{-- Features Grid --}}
<section class="py-20 px-6 bg-white">
  <div class="max-w-6xl mx-auto">
    <h2 class="text-3xl font-bold text-center text-gray-900 mb-3">Everything Your School Needs</h2>
    <p class="text-center text-gray-500 mb-12">One platform, all your operations — no more spreadsheets or paper registers.</p>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
      @foreach([
        ['icon'=>'👨‍🎓','title'=>'Student Management','desc'=>'Admissions, profiles, promotions, transfers'],
        ['icon'=>'💰','title'=>'Fee Management','desc'=>'Structures, challan, receipts, outstanding reports'],
        ['icon'=>'📅','title'=>'Attendance','desc'=>'Daily marking, monthly reports, SMS alerts'],
        ['icon'=>'🗓','title'=>'Timetable','desc'=>'Visual grid, teacher scheduling, conflict detection'],
        ['icon'=>'📝','title'=>'Exams & Results','desc'=>'Matric grading (A1/A/B/C/D/E/F), merit lists'],
        ['icon'=>'📱','title'=>'Parent & Student Portal','desc'=>'Real-time access to results, fees, attendance'],
        ['icon'=>'📢','title'=>'Announcements','desc'=>'School-wide or class-specific notices with SMS'],
        ['icon'=>'📊','title'=>'Reports','desc'=>'Fee collection, attendance, student list reports'],
        ['icon'=>'🔒','title'=>'Multi-School SaaS','desc'=>'Each school gets its own secure subdomain'],
      ] as $feature)
      <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-md transition">
        <div class="text-3xl mb-3">{{ $feature['icon'] }}</div>
        <h3 class="font-semibold text-gray-800 mb-1">{{ $feature['title'] }}</h3>
        <p class="text-sm text-gray-500">{{ $feature['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Pricing --}}
<section class="py-20 px-6 bg-gray-50">
  <div class="max-w-5xl mx-auto">
    <h2 class="text-3xl font-bold text-center text-gray-900 mb-3">Simple, Affordable Pricing</h2>
    <p class="text-center text-gray-500 mb-12">All prices in PKR. No hidden charges.</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      @foreach([
        ['name'=>'Starter','price'=>'4,500','period'=>'/month','students'=>'Up to 150 students','color'=>'border-gray-200'],
        ['name'=>'Growth', 'price'=>'9,000','period'=>'/month','students'=>'Up to 400 students','color'=>'border-emerald-400','popular'=>true],
        ['name'=>'Premium','price'=>'16,000','period'=>'/month','students'=>'Up to 800 students','color'=>'border-blue-400'],
      ] as $plan)
      <div class="bg-white rounded-2xl p-6 border-2 {{ $plan['color'] }} relative">
        @if(isset($plan['popular']))
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</div>
        @endif
        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>
        <div class="text-3xl font-extrabold text-emerald-600 mb-1">Rs. {{ $plan['price'] }}<span class="text-base font-normal text-gray-400">{{ $plan['period'] }}</span></div>
        <p class="text-sm text-gray-500 mb-4">{{ $plan['students'] }}</p>
        <a href="mailto:info@gnosis.ac.pk" class="block text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition text-sm">Get Started</a>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-10 px-6 text-center">
  <p class="font-semibold text-white mb-1">Gnosis Education Systems</p>
  <p class="text-sm mb-3">Pakistan's modern school management platform</p>
  <p class="text-xs">info@gnosis.ac.pk &nbsp;·&nbsp; gnosis.ac.pk</p>
  <p class="text-xs mt-4 opacity-40">© {{ date('Y') }} Gnosis Education Systems. All rights reserved.</p>
</footer>

</body>
</html>