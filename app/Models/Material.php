<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Material extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'materials';

    protected $fillable = [
        'user_id',
        'title',
        'subject',
        'content',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
