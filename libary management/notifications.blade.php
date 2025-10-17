@extends('layouts.app')
@section('content')
<h2>Notifications</h2>
<ul class="list-group mb-4">
    @forelse($notes as $n)
        <li class="list-group-item">{{ $n->message }}</li>
    @empty
        <li class="list-group-item">No notifications.</li>
    @endforelse
</ul>
<a class="btn btn-secondary" href="{{ route('catalog') }}">Back to Catalog</a>
@endsection
