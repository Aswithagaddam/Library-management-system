<?php

namespace App\Http\Controllers;                    

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Notification;
use Auth;
use Carbon\Carbon;

class LibraryController extends Controller
{
    public function catalog(Request $request)
    {
        $query = $request->input('query');
        $books = Book::when($query, function($q) use ($query) {
            $q->where('title', 'like', "%$query%")
              ->orWhere('author', 'like', "%$query%")
              ->orWhere('isbn', 'like', "%$query%");
        })->get();
        return view('catalog', compact('books'));
    }

    public function issueReturn()
    {
        $books = Book::all();
        return view('issue_return', compact('books'));
    }

    public function showBookForm($id = null)
    {
        $book = $id ? Book::findOrFail($id) : null;
        return view('book_form', compact('book'));
    }

    public function saveBook(Request $request, $id = null)
    {
        $data = $request->validate([
            'title' => 'required',
            'author' => 'required',
            'isbn' => 'required|unique:books,isbn,' . $id,
            'available' => 'nullable|boolean'
        ]);
        $data['available'] = $request->has('available');
        if ($id) {
            Book::findOrFail($id)->update($data);
            return redirect()->route('catalog')->with('success', 'Book updated!');
        } else {
            Book::create($data);
            return redirect()->route('catalog')->with('success', 'Book added!');
        }
    }

    public function deleteBook($id)
    {
        Book::findOrFail($id)->delete();
        return redirect()->route('catalog')->with('success', 'Book deleted!');
    }

    public function issueBook(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $student = User::where('email', $request->input('student_email'))->where('role', 'student')->first();
        if (!$student) return back()->with('error', 'Invalid student email!');
        if (!$book->available) return back()->with('error', 'Book is already issued!');
        $book->available = false;
        $book->issued_to = $student->id;
        $book->due_date = Carbon::now()->addDays(7);
        $book->save();
        Transaction::create([
            'book_id' => $book->id,
            'user_id' => $student->id,
            'action' => 'issue',
            'date' => Carbon::now(),
            'due_date' => $book->due_date
        ]);
        Notification::create([
            'user_id' => $student->id,
            'message' => "Book \"{$book->title}\" issued. Due on {$book->due_date->toDateString()}."
        ]);
        return back()->with('success', 'Book issued!');
    }

    public function returnBook($id)
    {
        $book = Book::findOrFail($id);
        if ($book->available) return back()->with('error', 'Book is not issued!');
        $fine = $this->calculateFine($book->due_date);
        Transaction::create([
            'book_id' => $book->id,
            'user_id' => $book->issued_to,
            'action' => 'return',
            'date' => Carbon::now(),
            'fine' => $fine
        ]);
        Notification::create([
            'user_id' => $book->issued_to,
            'message' => "Book \"{$book->title}\" returned. Fine: \${$fine}"
        ]);
        $book->available = true;
        $book->issued_to = null;
        $book->due_date = null;
        $book->save();
        return back()->with('success', "Book returned! Fine: \${$fine}");
    }

    public function requestIssue($id)
    {
        $book = Book::findOrFail($id);
        if (!$book->available) return back()->with('error', 'Book is not available!');
        Notification::create([
            'user_id' => User::where('role', 'librarian')->first()->id,
            'message' => "Student \"".Auth::user()->name."\" requested to issue \"{$book->title}\"."
        ]);
        return back()->with('info', 'Request sent to librarian!');
    }

    public function profile()
    {
        $user = Auth::user();
        $myBooks = Book::where('issued_to', $user->id)->get();
        $transactions = Transaction::where('user_id', $user->id)->get();
        return view('profile', compact('user', 'myBooks', 'transactions'));
    }

    public function notifications()
    {
        $user = Auth::user();
        $notes = Notification::where('user_id', $user->id)->orWhere(function($q) use ($user) {
            if ($user->role == 'librarian') $q->orWhere('user_id', $user->id);
        })->get();
        return view('notifications', compact('notes'));
    }

    private function calculateFine($dueDate)
    {
        $now = Carbon::now();
        $due = Carbon::parse($dueDate);
        $diff = $now->diffInDays($due, false);
        return $diff < 0 ? abs($diff) * 2 : 0;
    }
}