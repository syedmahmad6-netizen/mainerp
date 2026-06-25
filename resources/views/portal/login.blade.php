<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $school->name }} — Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 to-emerald-50 flex items-center justify-center p-4">
<div class="w-full max-w-md">
  <div class="text-center mb-8">
    @if($school->logo)
      <img src="{{ asset('storage/'.$school->logo) }}" class="h-20 w-20 rounded-full mx-auto mb-3 object-cover shadow-lg border-4 border-white">
    @else
      <div class="h-20 w-20 rounded-full bg-emerald-600 flex items-center justify-center mx-auto mb-3 shadow-lg border-4 border-white">
        <span class="text-white text-2xl font-bold">{{ strtoupper(substr($school->name,0,2)) }}</span>
      </div>
    @endif
    <h1 class="text-2xl font-bold text-gray-800">{{ $school->name }}</h1>
    <p class="text-gray-500 text-sm mt-1">Parent & Student Portal</p>
  </div>
  <div class="bg-white rounded-2xl shadow-xl p-8">
    <h2 class="text-lg font-semibold text-gray-700 mb-5">Sign in to your account</h2>
    @if($errors->any())
      <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 mb-4 text-sm">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('portal.login.post') }}">
      @csrf
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-600 mb-1.5">Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
          placeholder="your@email.com">
      </div>
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-600 mb-1.5">Password</label>
        <input type="password" name="password" required
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
          placeholder="••••••••">
      </div>
      <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition">Sign In →</button>
    </form>
    <p class="text-center text-xs text-gray-400 mt-6">No credentials? Contact your school office.</p>
  </div>
  <p class="text-center text-xs text-gray-400 mt-5">Powered by Gnosis Education Systems</p>
</div>
</body>
</html>