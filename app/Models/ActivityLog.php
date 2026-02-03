<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'action', 'module', 'description', 
        'ip_address', 'user_agent', 'changes', 'severity'
    ];

    protected $casts = [
        'changes' => 'array', // Otomatis jadi array saat diambil
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}