<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email'
    ];

    public function scopeWithName($query, $name)
    {
        return $query->where('name', $name);
    }

    public function scopeWithEmail($query, $email)
    {
        return $query->where('email', $email);
    }
}
