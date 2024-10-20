<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',       // Allow mass assignment for id
        'name',
        'phone',
        'gstNumber',
        'city',
    ];
}
