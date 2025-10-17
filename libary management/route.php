<?php
use App\Http\Controllers\LibraryController;

Route::middleware(['auth'])->group(function() {
    Route::get('/catalog', [LibraryController::class, 'catalog'])->name('catalog');
    Route::get('/issue-return', [LibraryController::class, 'issueReturn'])->name('issueReturn');
    Route::get('/book-form/{id?}', [LibraryController::class, 'showBookForm'])->name('bookForm');
    Route::post('/book-form/{id?}', [LibraryController::class, 'saveBook'])->name('saveBook');
    Route::delete('/book/{id}', [LibraryController::class, 'deleteBook'])->name('deleteBook');
    Route::post('/issue-book/{id}', [LibraryController::class, 'issueBook'])->name('issueBook');
    Route::post('/return-book/{id}', [LibraryController::class, 'returnBook'])->name('returnBook');
    Route::post('/request-issue/{id}', [LibraryController::class, 'requestIssue'])->name('requestIssue');
    Route::get('/profile', [LibraryController::class, 'profile'])->name('profile');
    Route::get('/notifications', [LibraryController::class, 'notifications'])->name('notifications');
});
Auth::routes();
Route::get('/', function() { return redirect('/catalog'); });
