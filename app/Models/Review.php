<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'rating',
        'comment',
        'photo',
        'is_featured', // Penting untuk kurasi AI
        'ai_analysis'  // Penting untuk alasan AI
    ];

    // Relasi ke Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi ke User melalui Order
    public function user()
    {
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'user_id');
    }
}