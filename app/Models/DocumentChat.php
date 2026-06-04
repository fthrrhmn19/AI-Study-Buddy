<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DocumentChat extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'document_chats';

    protected $fillable = [
        'user_id',
        'document_id',
        'user_message',
        'ai_response',
        'action_type',
        'provider',
        'model',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', '_id');
    }
}
