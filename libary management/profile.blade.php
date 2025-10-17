@extends('layouts.app')
@section('content')
<h2>My Profile</h2>
<h4 class="mt-4">My Books</h4>
<ul class="list-group mb-4">
    @forelse($myBooks as $b)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            {{ $b->title }} (Due: {{ $b->due_date ? \Carbon\Carbon::parse($b->due_date)->toDateString() : '-' }})
            <span class="badge bg-{{ (now()->diffInDays(\Carbon\Carbon::parse($b->due_date), false) < 0) ? 'danger' : 'success' }}">
                Fine: ${{ (now()->diffInDays(\Carbon\Carbon::parse($b->due_date), false) < 0) ? abs(now()->diffInDays(\Carbon\Carbon::parse($b->due_date), false)) * 2 : 0 }}
            </span>
        </li>
    @empty
        <li class="list-group-item">No books issued.</li>
    @endforelse
</ul>
<h4>Transaction History</h4>
<ul class="list-group">
    @forelse($transactions as $t)
        <li class="list-group-item">
            {{ ucfirst($t->action) }} "{{ $t->book->title ?? 'Unknown' }}" on {{ \Carbon\Carbon::parse($t->date)->toDateString() }}
            @if($t->fine) - Fine: ${{ $t->fine }} @endif
        </li>
    @empty
        <li class="list-group-item">No transactions.</li>
    @endforelse
</ul>
<a class="btn btn-secondary mt-3" href="{{ route('catalog') }}">Back to Catalog</a>
@endsection
