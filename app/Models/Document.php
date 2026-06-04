<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Document extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'documents';

    protected $fillable = [
        'user_id',
        'title',
        'subject',
        'file_name',
        'file_type',
        'extracted_text',
        'chapters',
    ];

    protected $casts = [
        'chapters' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
