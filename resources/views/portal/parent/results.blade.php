@extends('portal.parent.layout')
@section('title','Results')
@section('content')
<div class="flex items-center gap-3 mb-5">
  <a href="{{ route('parent.dashboard') }}" class="text-gray-400 hover:text-gray-600 text-xl">←</a>
  <div>
    <h2 class="text-lg font-bold text-gray-800">Results — {{ $student->user->name }}</h2>
    <p class="text-xs text-gray-500">Published results only</p>
  </div>
</div>
@forelse($exams as $ed)
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-4 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
      <div>
        <p class="font-semibold text-gray-800">{{ $ed['exam']->name }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $ed['exam']->examType?->name }} @if($ed['exam']->start_date)· {{ $ed['exam']->start_date->format('d M Y') }}@endif</p>
      </div>
      <div class="text-right">
        <span class="inline-block px-3 py-1 rounded-full text-sm font-bold {{ in_array($ed['grade'],['A1','A'])?'bg-emerald-100 text-emerald-700':(in_array($ed['grade'],['B','C'])?'bg-blue-100 text-blue-700':(in_array($ed['grade'],['D','E'])?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700')) }}">{{ $ed['grade'] }}</span>
        <p class="text-xs text-gray-500 mt-1">{{ $ed['percentage'] }}%</p>
      </div>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50"><tr>
        <th class="px-5 py-2 text-left text-xs text-gray-500 font-medium">Subject</th>
        <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Marks</th>
        <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Grade</th>
        <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Result</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-50">
        @foreach($ed['results'] as $r)
          <tr>
            <td class="px-5 py-2.5 text-gray-700">{{ $r->subject?->name }}</td>
            <td class="px-3 py-2.5 text-center text-gray-600">{{ $r->obtained_marks }}<span class="text-gray-400">/{{ $r->total_marks }}</span></td>
            <td class="px-3 py-2.5 text-center font-semibold {{ $r->grade==='F'?'text-red-500':'text-gray-700' }}">{{ $r->grade }}</td>
            <td class="px-3 py-2.5 text-center text-xs font-bold {{ $r->is_pass?'text-emerald-600':'text-red-500' }}">{{ $r->is_pass?'PASS':'FAIL' }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot class="bg-gray-50 font-semibold"><tr>
        <td class="px-5 py-2.5 text-gray-700">Total</td>
        <td class="px-3 py-2.5 text-center">{{ $ed['total_obtained'] }}/{{ $ed['total_marks'] }}</td>
        <td class="px-3 py-2.5 text-center text-cp font-bold">{{ $ed['grade'] }}</td>
        <td class="px-3 py-2.5 text-center font-bold {{ $ed['percentage']>=40?'text-emerald-600':'text-red-500' }}">{{ $ed['percentage'] }}%</td>
      </tr></tfoot>
    </table>
  </div>
@empty
  <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">No published results yet.</div>
@endforelse
@endsection