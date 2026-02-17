@extends('layouts.admin')

@section('title', 'Manajemen Review')

@section('styles')
<style>
    /* --- Dashboard Stats --- */
    .review-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: var(--shadow-soft);
        border: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .bg-light-orange { background: #fffaf0; color: #ed8936; }
    .bg-light-blue { background: #ebf8ff; color: #4299e1; }
    .bg-light-green { background: #f0fff4; color: #48bb78; }

    /* --- Table Styling --- */
    .review-panel {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid #f0f0f0;
    }
    .table-custom thead {
        background: #f8fafc;
    }
    .table-custom th {
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #64748b;
        padding: 15px 20px;
        border-bottom: 2px solid #f1f5f9;
    }
    .table-custom td {
        padding: 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    /* --- Customer Profile --- */
    .customer-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .avatar-sm {
        width: 40px;
        height: 40px;
        background: var(--secondary-color);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    /* --- Star Rating --- */
    .stars { color: #fbbf24; font-size: 0.85rem; }

    /* --- Photo Thumbnail --- */
    .review-img-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #f1f5f9;
        cursor: zoom-in;
    }
    .review-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.3s;
    }
    .review-img-wrapper:hover img { transform: scale(1.1); }

    /* --- Action Buttons --- */
    .btn-action {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: 0.2s;
    }

    /* --- Mobile Responsive --- */
    @media (max-width: 768px) {
        .stat-card { padding: 15px; }
        .table-custom thead { display: none; }
        .table-custom, .table-custom tbody, .table-custom tr, .table-custom td {
            display: block;
            width: 100%;
        }
        .table-custom tr {
            margin-bottom: 15px;
            border: 1px solid #f1f5f9;
            border-radius: 15px;
            padding: 10px;
        }
        .table-custom td {
            text-align: left;
            padding: 8px 10px;
            border: none;
        }
        .table-custom td:before {
            content: attr(data-label);
            font-weight: bold;
            display: block;
            font-size: 0.7rem;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .text-right { text-align: left !important; margin-top: 10px; border-top: 1px solid #f1f5f9 !important; padding-top: 15px !important; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title" style="font-weight: 800; color: #1e293b;">Monitoring Review</h1>
            <p class="text-muted">Kelola ulasan terbaik untuk ditampilkan di etalase depan.</p>
        </div>
        <button class="btn btn-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <div class="review-stats">
        <div class="stat-card">
            <div class="stat-icon bg-light-blue"><i class="fas fa-comments"></i></div>
            <div>
                <small class="text-muted d-block">Total Review</small>
                <strong>{{ $reviews->total() }} Ulasan</strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-light-orange"><i class="fas fa-star"></i></div>
            <div>
                <small class="text-muted d-block">Rating Rata-rata</small>
                <strong>{{ number_format($reviews->avg('rating'), 1) }} / 5.0</strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-light-green"><i class="fas fa-home"></i></div>
            <div>
                <small class="text-muted d-block">Tampil di Home</small>
                <strong>{{ $reviews->where('is_featured', true)->count() }} Aktif</strong>
            </div>
        </div>
    </div>

    <div class="review-panel">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Penilaian</th>
                        <th>Komentar</th>
                        <th>Foto</th>
                        <th>Status Tampil</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td data-label="PELANGGAN">
                            <div class="customer-box">
                                <div class="avatar-sm">
                                    {{ strtoupper(substr($review->order->customer_name ?? 'P', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #334155;">{{ $review->order->customer_name ?? 'Pelanggan Setia' }}</div>
                                    <small class="text-muted">Order #{{ $review->order_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td data-label="PENILAIAN">
                            <div class="stars">
                                @for($i=1; $i<=5; $i++)
                                    <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        </td>
                        <td data-label="KOMENTAR">
                            <p style="font-size: 0.9rem; line-height: 1.5; color: #475569; margin-bottom: 0;">
                                "{{ Str::limit($review->comment, 100) }}"
                            </p>
                        </td>
                        <td data-label="FOTO">
                            @if($review->photo)
                                <div class="review-img-wrapper" onclick="window.open('{{ asset('uploads/' . $review->photo) }}', '_blank')">
                                    <img src="{{ asset('uploads/' . $review->photo) }}" alt="Review">
                                </div>
                            @else
                                <span class="text-muted" style="font-size: 0.8rem;">No Photo</span>
                            @endif
                        </td>
                        <td data-label="STATUS TAMPIL">
                            @if($review->is_featured)
                                <span class="badge" style="background: #dcfce7; color: #166534; border-radius: 50px; padding: 5px 12px; font-size: 0.7rem;">
                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                </span>
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #64748b; border-radius: 50px; padding: 5px 12px; font-size: 0.7rem;">
                                    HIDDEN
                                </span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="d-flex justify-content-md-end gap-2">
                                <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $review->is_featured ? 'btn-danger' : 'btn-success' }}" style="border-radius: 8px; font-weight: 600;">
                                        {{ $review->is_featured ? 'Sembunyikan' : 'Tampilkan' }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Hapus ulasan ini secara permanen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <img src="https://illustrations.popsy.co/gray/box-with-items.svg" style="width: 150px; margin-bottom: 20px; opacity: 0.5;">
                            <p class="text-muted">Belum ada review yang masuk saat ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-5">
        {{ $reviews->links() }}
    </div>
</div>
@endsection