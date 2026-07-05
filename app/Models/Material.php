<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'created_by',
        'is_active',
        'order_number',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
