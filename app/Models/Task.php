<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'is_completed','todo_list_id'];

    public function todoList(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TodoList::class,'todo_list_id');
    }
}
