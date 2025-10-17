@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Book Catalog</h2>
    @if(Auth::user()->role == 'librarian')
        <a class="btn btn-success" href="{{ route('bookForm') }}">Add Book</a>
    @endif
</div>
<form method="GET" action="{{ route('catalog') }}" class="mb-3">
    <input class="form-control" name="query" placeholder="Search by title, author, ISBN..." value="{{ request('query') }}">
</form>
<div class="row">
    @forelse($books as $book)
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm {{ $book->available ? 'card-available' : 'card-unavailable' }}">
                <div class="card-body">
                    <h5 class="card-title">{{ $book->title }}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">{{ $book->author }}</h6>
                    <p class="card-text">ISBN: {{ $book->isbn }}</p>
                    <p class="card-text">Status: <span class="{{ $book->available ? 'text-success' : 'text-danger' }}">{{ $book->available ? 'Available' : 'Issued' }}</span></p>
                    @if(Auth::user()->role == 'librarian')
                        <a class="btn btn-sm btn-primary me-1" href="{{ route('bookForm', $book->id) }}">Edit</a>
                        <form method="POST" action="{{ route('deleteBook', $book->id) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this book?')">Delete</button>
                        </form>
                    @elseif(Auth::user()->role == 'student' && $book->available)
                        <form method="POST" action="{{ route('requestIssue', $book->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-success">Request Issue</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-warning">No books found.</div></div>
    @endforelse
</div>
@endsection
@endsection