@extends('layouts.admin')

@section('title', 'Data Pelanggan')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin_users.css') }}">
    
    <style>
        /* FIX PAGINATION NGEBUG (BIAR GAK VERTIKAL) */
        nav[role="navigation"] svg { width: 20px; } /* Ukuran panah pagination */
        .pagination { display: flex; list-style: none; padding: 0; gap: 5px; justify-content: center; margin-top: 20px; }
        .pagination li { display: inline-block; }
        .pagination li a, .pagination li span { 
            padding: 8px 14px; border: 1px solid #ddd; border-radius: 5px; 
            text-decoration: none; color: #2c3e50; background: white;
            font-size: 0.9rem;
        }
        .pagination li.active span { background-color: #B1935B; color: white; border-color: #B1935B; font-weight: bold; }
        .pagination li.disabled span { color: #ccc; cursor: not-allowed; }

        /* MODAL CRM STYLING */
        .modal-backdrop {
            display: none; position: fixed; z-index: 9999; left: 0; top: 0;
            width: 100%; height: 100%; overflow: auto;
            background-color: rgba(0,0,0,0.5); backdrop-filter: blur(3px);
        }
        .modal-backdrop.show { display: flex; align-items: center; justify-content: center; }
        
        .modal-content-custom {
            background-color: #fff; width: 95%; max-width: 700px;
            border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease-out; overflow: hidden;
        }
        @keyframes slideUp { from {transform: translateY(20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }

        .modal-header { background: #2F3D65; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { margin: 0; font-size: 1.2rem; font-weight: 700; color: #fff; }
        .btn-close { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }

        .modal-body { padding: 25px; max-height: 80vh; overflow-y: auto; }

        /* CRM STATS BOX */
        .crm-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 10px; border: 1px solid #eee; text-align: center; }
        .stat-box .label { display: block; font-size: 0.75rem; color: #888; text-transform: uppercase; margin-bottom: 5px; }
        .stat-box h3, .stat-box h4 { margin: 0; color: #2F3D65; font-weight: 700; }

        /* TIMELINE ORDER */
        .timeline-item { 
            display: flex; align-items: center; padding: 12px; border-bottom: 1px solid #f1f1f1; 
            gap: 15px; transition: 0.2s;
        }
        .timeline-item:hover { background: #fcfcfc; }
        .timeline-item .date { font-size: 0.8rem; color: #999; min-width: 80px; }
        .timeline-item .info { flex-grow: 1; }
        .timeline-item .info .order-id { font-weight: 700; color: #444; display: block; }
        .timeline-item .info .amount { font-size: 0.85rem; color: #B1935B; font-weight: 600; }
        
        .badge-mini { padding: 3px 8px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: white; }
    </style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Data Pelanggan (CRM)</h1>
            <p class="text-muted">Analisis loyalitas dan riwayat belanja pelanggan.</p>
        </div>
        <div class="header-actions">
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'vip']) }}" class="btn-filter {{ request('sort') == 'vip' ? 'active' : '' }}" style="background: #B1935B; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-crown"></i> Urutkan Sultan (VIP)
            </a>
        </div>
    </div>

    {{-- SEARCH & TOOLS --}}
    <div class="toolbar-panel mb-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="search-form">
            <div class="search-group" style="position: relative; max-width: 400px;">
                <i class="fas fa-search" style="position: absolute; left: 15px; top: 12px; color: #ccc;"></i>
                <input type="text" name="search" class="form-control" style="padding-left: 40px; border-radius: 25px;" placeholder="Cari nama atau email..." value="{{ request('search') }}">
            </div>
        </form>
    </div>

    {{-- USER TABLE --}}
    <div class="card-panel shadow-sm" style="background: white; border-radius: 12px; padding: 20px;">
        <div class="table-responsive">
            <table class="table table-hover table-custom">
                <thead class="bg-light">
                    <tr>
                        <th>Pelanggan</th>
                        <th>Kontak</th>
                        <th>Total Belanja (LTV)</th>
                        <th>Frekuensi</th>
                        <th>Bergabung</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="user-profile d-flex align-items-center gap-3">
                                <div class="avatar-circle" style="width:40px; height:40px; background:#2F3D65; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div class="user-info">
                                    <span class="name d-block font-weight-bold">{{ $user->name }}</span>
                                    @if($user->total_spent > 500000)
                                        <span class="badge badge-warning" style="background: #FFF9C4; color: #FBC02D; font-size: 0.7rem; padding: 2px 8px;"><i class="fas fa-crown"></i> VIP</span>
                                    @elseif($user->created_at->diffInDays() < 7)
                                        <span class="badge badge-info" style="font-size: 0.7rem; padding: 2px 8px;">NEW</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <small class="d-block text-muted"><i class="far fa-envelope"></i> {{ $user->email }}</small>
                                @if($user->phone)
                                    <small class="d-block text-muted"><i class="fas fa-phone"></i> {{ $user->phone }}</small>
                                @endif
                            </div>
                        </td>
                        <td class="text-money font-weight-bold" style="color: #2e7d32;">
                            Rp {{ number_format($user->total_spent ?? 0, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge badge-light border">{{ $user->completed_orders_count ?? 0 }} Pesanan</span>
                        </td>
                        <td><small>{{ $user->created_at->format('d M Y') }}</small></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-outline-primary" onclick="showUserDetail({{ $user->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($user->phone)
                                <a href="https://wa.me/{{ $user->phone }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-users-slash" style="font-size:3rem; color:#ddd; margin-bottom:10px;"></i>
                            <p class="text-muted">Tidak ada data pelanggan ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- PAGINATION --}}
        <div class="pagination-wrapper">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>

{{-- MODAL DETAIL USER (CRM VIEW) --}}
<div id="userModal" class="modal-backdrop">
    <div class="modal-content-custom">
        <div class="modal-header">
            <h2 id="modalUserName">Detail Pelanggan</h2>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            
            <div class="crm-stats">
                <div class="stat-box">
                    <span class="label">Total Spent</span>
                    <h3 id="modalTotalSpent">Rp 0</h3>
                </div>
                <div class="stat-box">
                    <span class="label">Email</span>
                    <h4 id="modalEmail" style="font-size: 0.9rem; word-break: break-all;">-</h4>
                </div>
                <div class="stat-box">
                    <span class="label">Bergabung Sejak</span>
                    <h4 id="modalJoinDate">-</h4>
                </div>
            </div>

            <h4 class="section-title mt-4" style="font-size: 1rem; border-bottom: 2px solid #f1f1f1; padding-bottom: 10px;">Riwayat Pesanan Terakhir</h4>
            <div class="order-timeline" id="modalOrderList">
                <p class="text-center text-muted">Sedang memuat...</p>
            </div>

        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('userModal');

    function closeModal() {
        modal.classList.remove('show');
    }

    async function showUserDetail(id) {
        modal.classList.add('show');
        document.getElementById('modalUserName').innerText = 'Loading...';
        document.getElementById('modalOrderList').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

        try {
            const response = await fetch(`/admin/users/${id}`); 
            const data = await response.json();

            document.getElementById('modalUserName').innerText = data.name;
            document.getElementById('modalEmail').innerText = data.email;
            document.getElementById('modalTotalSpent').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total_spent || 0);
            document.getElementById('modalJoinDate').innerText = new Date(data.created_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'});

            let ordersHtml = '';
            if(data.orders && data.orders.length > 0) {
                data.orders.forEach(order => {
                    let badgeClass = 'bg-secondary';
                    if(order.status == 'completed') badgeClass = 'bg-success';
                    if(order.status == 'pending' || order.status == 'preparing') badgeClass = 'bg-warning text-dark';
                    if(order.status == 'cancelled') badgeClass = 'bg-danger';

                    ordersHtml += `
                        <div class="timeline-item">
                            <div class="date">${new Date(order.created_at).toLocaleDateString('id-ID')}</div>
                            <div class="info">
                                <span class="order-id">Order #${order.id}</span>
                                <span class="amount">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</span>
                            </div>
                            <div class="status">
                                <span class="badge-mini ${badgeClass}">${order.status}</span>
                            </div>
                        </div>
                    `;
                });
            } else {
                ordersHtml = '<div class="text-center py-4 text-muted">Belum ada riwayat pesanan.</div>';
            }
            document.getElementById('modalOrderList').innerHTML = ordersHtml;

        } catch (error) {
            console.error(error);
            document.getElementById('modalOrderList').innerHTML = '<p class="text-danger text-center">Gagal memuat data.</p>';
        }
    }

    // Close modal on click outside
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endsection