@extends('portal.student.layout')
@section('title','Announcements')
@section('content')
<h2 class="text-lg font-bold text-gray-800 mb-5">📢 School Notices</h2>
@forelse($announcements as $a)
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $a->type==='urgent'?'bg-red-100 text-red-700':($a->type==='holiday'?'bg-green-100 text-green-700':($a->type==='event'?'bg-orange-100 text-orange-700':'bg-blue-100 text-blue-700')) }}">{{ $a->type_label }}</span>
      <span class="text-xs text-gray-400">{{ $a->published_at?->format('d M Y') }}</span>
    </div>
    <h3 class="font-semibold text-gray-800 mb-2">{{ $a->title }}</h3>
    <div class="text-sm text-gray-600 leading-relaxed">{!! $a->body !!}</div>
    @if($a->attachment)<a href="{{ asset('storage/'.$a->attachment) }}" target="_blank" class="inline-flex items-center gap-1 mt-3 text-xs text-blue-600 hover:underline">📎 View Attachment</a>@endif
  </div>
@empty
  <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">No announcements yet.</div>
@endforelse
{{ $announcements->links() }}
@endsection