@extends('layouts.app')
@section('content')
<h2>Issue/Return Books</h2>
<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Title</th><th>Author</th><th>ISBN</th><th>Status</th><th>Issued To</th><th>Due Date</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($books as $book)
            <tr>
                <td>{{ $book->title }}</td>
                <td>{{ $book->author }}</td>
                <td>{{ $book->isbn }}</td>
                <td>{!! $book->available ? '<span class="text-success">Available</span>' : '<span class="text-danger">Issued</span>' !!}</td>
                <td>{{ $book->issuedToUser->name ?? '-' }}</td>
                <td>{{ $book->due_date ? \Carbon\Carbon::parse($book->due_date)->toDateString() : '-' }}</td>
                <td>
                    @if($book->available)
                        <form method="POST" action="{{ route('issueBook', $book->id) }}">
                            @csrf
                            <input type="email" name="student_email" placeholder="Student Email" required>
                            <button class="btn btn-sm btn-success">Issue</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('returnBook', $book->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-warning">Return</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<a class="btn btn-secondary" href="{{ route('catalog') }}">Back to Catalog</a>
@endsection
