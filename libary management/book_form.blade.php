@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title mb-4">{{ $book ? 'Edit' : 'Add' }} Book</h3>
                <form method="POST" action="{{ $book ? route('saveBook', $book->id) : route('saveBook') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" value="{{ old('title', $book->title ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Author</label>
                        <input type="text" class="form-control" name="author" value="{{ old('author', $book->author ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ISBN</label>
                        <input type="text" class="form-control" name="isbn" value="{{ old('isbn', $book->isbn ?? '') }}" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="available" id="availableCheck" {{ old('available', $book->available ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="availableCheck">Available</label>
                    </div>
                    <button class="btn btn-primary">{{ $book ? 'Update' : 'Add' }} Book</button>
                    <a class="btn btn-secondary ms-2" href="{{ route('catalog') }}">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
