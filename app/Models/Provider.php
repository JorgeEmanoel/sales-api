<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name',
        'document',
        'document_type',
        'shared'
    ];

    public const DOCUMENT_CPF = 'cpf';
    public const DOCUMENT_CNPJ = 'cnpj';

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

    public function setDocumentAttribute($value)
    {
        $this->attributes['document'] = preg_replace('/\D/', '', $value);
    }
}
