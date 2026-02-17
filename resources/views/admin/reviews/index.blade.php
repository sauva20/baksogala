@extends('layouts.admin')

@section('title', 'Manajemen Review')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-title">Monitoring Review Pelanggan</h1>
        <p class="text-muted">Kelola ulasan yang akan ditampilkan di Landing Page (Maksimal 10 ulasan terbaru yang ditandai VIP/Featured).</p>
    </div>

    <div class="card-panel" style="background: white; border-radius: 12px; padding: 20px; shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th>Foto</th>
                        <th>Status Tampil</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td>
                            <strong>{{ $review->order->customer_name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">Order #{{ $review->order_id }}</small>
                        </td>
                        <td>
                            <div style="color: #ffc700;">
                                @for($i=0; $i < $review->rating; $i++) <i class="fas fa-star"></i> @endfor
                            </div>
                        </td>
                        <td>
                            <p style="max-width: 300px; font-size: 0.9rem;" title="{{ $review->comment }}">
                                "{{ Str::limit($review->comment, 80) }}"
                            </p>
                        </td>
                        <td>
                            @if($review->photo)
                                <img src="{{ asset('uploads/' . $review->photo) }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($review->is_featured)
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Tampil di Home</span>
                            @else
                                <span class="badge bg-secondary">Disembunyikan</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST" style="display:inline-block;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $review->is_featured ? 'btn-outline-danger' : 'btn-primary' }}">
                                    {{ $review->is_featured ? 'Sembunyikan' : 'Tampilkan di Home' }}
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus review ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Belum ada ulasan masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection