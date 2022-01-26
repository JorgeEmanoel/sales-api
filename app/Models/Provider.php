<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'document',
        'document_type',
        'shared'
    ];

    public function procuts()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeNameLike($query, $name)
    {
        return $query->where(
            'name',
            'like',
            "%$name%"
        );
    }

    public function scopeDocumentLike($query, $document)
    {
        return $query->where(
            'document',
            'like',
            "%$document%"
        );
    }

    public function scopeDocumentType($query, $type)
    {
        return $query->where(
            'document_type',
            $type
        );
    }
}
