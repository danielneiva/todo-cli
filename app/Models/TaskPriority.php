<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $level
 */
class TaskPriority extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'level'];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}