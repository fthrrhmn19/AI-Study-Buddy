<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AiGeneration extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'ai_generations';

    protected $fillable = [
        'user_id',
        'material_id',
        'type',
        'prompt',
        'result',
        'provider',
        'model',
        'conversation_id',
        'parent_id',
        'source_context',
        'metadata',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getTable()
    {
        if ($this->table !== 'ai_generations') {
            return $this->table;
        }

        return (string) config('services.ai_history.collection', $this->table);
    }
}
