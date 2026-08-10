@extends('portal.parent.layout')
@section('title','Fees')
@section('content')
<div class="flex items-center gap-3 mb-5">
  <a href="{{ route('parent.dashboard') }}" class="text-gray-400 hover:text-gray-600 text-xl">←</a>
  <h2 class="text-lg font-bold text-gray-800">Fees — {{ $student->user->name }}</h2>
</div>
@if($totalDue>0)
  <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
    <span class="text-2xl">⚠️</span>
    <div><p class="font-semibold text-orange-700">Rs. {{ number_format($totalDue,2) }} outstanding</p><p class="text-xs text-orange-600 mt-0.5">Please pay at the school office.</p></div>
  </div>
@else
  <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
    <span class="text-2xl">✅</span><p class="font-semibold text-emerald-700">All fees are clear. No outstanding balance.</p>
  </div>
@endif
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="px-5 py-3 border-b border-gray-100"><h3 class="font-semibold text-gray-700">Fee History</h3></div>
  @forelse($records as $fee)
    <div class="px-5 py-3.5 border-b border-gray-50 last:border-0 flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-800">{{ $fee->feeType?->name }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $fee->fee_month?->format('F Y') }} @if($fee->receipt_no)· #{{ $fee->receipt_no }}@endif</p>
      </div>
      <div class="text-right">
        <p class="text-sm font-semibold text-gray-800">Rs. {{ number_format($fee->amount_due,0) }}</p>
        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium mt-1 {{ $fee->status==='paid'?'bg-emerald-100 text-emerald-700':($fee->status==='overdue'?'bg-red-100 text-red-700':($fee->status==='partial'?'bg-yellow-100 text-yellow-700':'bg-orange-100 text-orange-700')) }}">{{ ucfirst($fee->status) }}</span>
      </div>
    </div>
  @empty
    <div class="p-10 text-center text-gray-400">No fee records found.</div>
  @endforelse
</div>
@endsection