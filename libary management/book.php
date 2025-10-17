<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['title', 'author', 'isbn', 'available', 'issued_to', 'due_date'];
    public function issuedToUser() {
        return $this->belongsTo(User::class, 'issued_to');
    }
}