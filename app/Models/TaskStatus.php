<?php

namespace App\Models;

use App\Enums\StatusType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'type'];

    protected function casts(): array
    {
        return [
            'type' => StatusType::class ,
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}