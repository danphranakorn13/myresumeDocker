<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image',
        'created_by_user', // foreign key
        'updated_by_user' // foreign key
    ];

    /**
     * Relationship to Users Table
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user', 'id')->select(['id', 'fname', 'lname', 'avatar']);
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by_user', 'id')->select(['id', 'fname', 'lname', 'avatar']);
    }
}
