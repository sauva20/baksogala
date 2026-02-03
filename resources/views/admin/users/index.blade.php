@extends('layouts.admin')

@section('title', 'Data Pelanggan')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin_users.css') }}">
@endsection

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Data Pelanggan (CRM)</h1>
            <p class="text-muted">Analisis loyalitas dan riwayat belanja pelanggan.</p>
        </div>
        <div class="header-actions">
            {{-- Tombol Sortir VIP --}}
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'vip']) }}" class="btn-filter {{ request('sort') == 'vip' ? 'active' : '' }}">
                <i class="fas fa-crown"></i> Urutkan Sultan (VIP)
            </a>
        </div>
    </div>

    {{-- SEARCH & TOOLS --}}
    <div class="toolbar-panel">
        <form action="{{ route('admin.users.index') }}" method="GET" class="search-form">
            <div class="search-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari nama atau email..." value="{{ request('search') }}">
            </div>
        </form>
    </div>

    {{-- USER TABLE --}}
    <div class="card-panel">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th width="60">Pelanggan</th>
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
                            <div class="user-profile">
                                <div class="avatar-circle">{{ substr($user->name, 0, 1) }}</div>
                                <div class="user-info">
                                    <span class="name">{{ $user->name }}</span>
                                    {{-- Logika Badge VIP: Jika belanja > 500rb --}}
                                    @if($user->total_spent > 500000)
                                        <span class="badge-vip"><i class="fas fa-crown"></i> VIP</span>
                                    @elseif($user->created_at->diffInDays() < 7)
                                        <span class="badge-new">NEW</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <span><i class="far fa-envelope"></i> {{ $user->email }}</span>
                                {{-- Jika ada no hp --}}
                                @if($user->phone)
                                    <span><i class="fas fa-phone"></i> {{ $user->phone }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-money">
                            Rp {{ number_format($user->total_spent ?? 0, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="order-count">{{ $user->completed_orders_count ?? 0 }} Pesanan</span>
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button class="btn-icon btn-detail" onclick="showUserDetail({{ $user->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            {{-- Link WA jika ada HP --}}
                            @if($user->phone)
                                <a href="https://wa.me/{{ $user->phone }}" target="_blank" class="btn-icon btn-wa">
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
        <div class="pagination-wrapper mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>

{{-- MODAL DETAIL USER (CRM VIEW) --}}
<div id="userModal" class="modal-backdrop">
    <div class="modal-content-custom modal-lg">
        <div class="modal-header">
            <h2 id="modalUserName">Detail Pelanggan</h2>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            
            {{-- Info Cards --}}
            <div class="crm-stats">
                <div class="stat-box">
                    <span class="label">Total Spent</span>
                    <h3 id="modalTotalSpent">Rp 0</h3>
                </div>
                <div class="stat-box">
                    <span class="label">Email</span>
                    <h4 id="modalEmail">-</h4>
                </div>
                <div class="stat-box">
                    <span class="label">Bergabung Sejak</span>
                    <h4 id="modalJoinDate">-</h4>
                </div>
            </div>

            <h4 class="section-title mt-4">Riwayat Pesanan Terakhir</h4>
            <div class="order-timeline" id="modalOrderList">
                {{-- Diisi via JS --}}
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

    // Fungsi Fetch Detail User (AJAX)
    async function showUserDetail(id) {
        modal.classList.add('show');
        
        // Reset isi
        document.getElementById('modalUserName').innerText = 'Loading...';
        document.getElementById('modalOrderList').innerHTML = '<p class="text-center text-muted">Memuat data...</p>';

        try {
            // Panggil API (Route harus dibuat nanti)
            // Kita pakai teknik sederhana: ambil data JSON
            // PENTING: Anda harus punya route 'admin.users.show' yang return JSON
            // Untuk sementara, kita simulasi pakai fetch ke endpoint yang kita buat di controller
            
            const response = await fetch(`/admin/users/${id}`); 
            const data = await response.json();

            // Isi Data Profil
            document.getElementById('modalUserName').innerText = data.name;
            document.getElementById('modalEmail').innerText = data.email;
            document.getElementById('modalTotalSpent').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total_spent || 0);
            document.getElementById('modalJoinDate').innerText = new Date(data.created_at).toLocaleDateString('id-ID');

            // Isi Riwayat Order
            let ordersHtml = '';
            if(data.orders && data.orders.length > 0) {
                data.orders.forEach(order => {
                    // Tentukan warna status
                    let badgeClass = 'bg-secondary';
                    if(order.status == 'completed') badgeClass = 'bg-success';
                    if(order.status == 'pending') badgeClass = 'bg-warning';
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
                ordersHtml = '<div class="empty-history">Belum ada riwayat pesanan.</div>';
            }
            document.getElementById('modalOrderList').innerHTML = ordersHtml;

        } catch (error) {
            console.error(error);
            document.getElementById('modalOrderList').innerHTML = '<p class="text-danger text-center">Gagal memuat data.</p>';
        }
    }
</script>
@endsection