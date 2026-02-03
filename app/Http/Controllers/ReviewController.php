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
        'is_featured', // <--- WAJIB ADA
        'ai_analysis'  // <--- WAJIB ADA
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}