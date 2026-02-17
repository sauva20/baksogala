<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Menampilkan daftar review untuk dimonitor admin.
     */
    public function index()
    {
        // Ambil review terbaru dengan relasi order biar nama pelanggan muncul
        $reviews = Review::with('order')->latest()->paginate(10);
        
        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Mengubah status 'is_featured' (Tampil/Sembunyi di Homepage).
     */
    public function toggleFeatured(Review $review)
    {
        // Balikkan status: 1 jadi 0, 0 jadi 1
        $review->is_featured = !$review->is_featured;
        $review->save();

        return back()->with('success', 'Status tampilan review berhasil diubah!');
    }

    /**
     * Menghapus review secara permanen.
     */
    public function destroy(Review $review)
    {
        $review->delete();
        
        return back()->with('success', 'Review telah dihapus.');
    }
}