@extends('layouts.admin')

@section('title', 'Manajemen Pesanan')

@section('styles')
    {{-- CSS Khusus Halaman Order --}}
    <link rel="stylesheet" href="{{ asset('assets/css/admin_orders.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        /* FIX PAGINATION NGEBUG (BIAR GAK VERTIKAL) */
        nav[role="navigation"] svg { width: 20px; } /* Ukuran panah pagination */
        .pagination { display: flex; list-style: none; padding: 0; gap: 5px; justify-content: center; }
        .pagination li { display: inline-block; }
        .pagination li a, .pagination li span { 
            padding: 8px 14px; border: 1px solid #ddd; border-radius: 5px; 
            text-decoration: none; color: #2c3e50; background: white;
        }
        .pagination li.active span { background-color: #B1935B; color: white; border-color: #B1935B; }
        .pagination li.disabled span { color: #ccc; }

        /* CSS POPUP DETAIL (MODAL) */
        .modal {
            display: none; 
            position: fixed; z-index: 9999; left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(2px);
        }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        
        .modal-content {
            background-color: #fff;
            margin: auto;
            width: 90%; max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease-out;
            position: relative;
            overflow: hidden;
        }
        @keyframes slideDown { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }

        .modal-header {
            background-color: #2c3e50;
            color: white; padding: 15px 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .modal-header h4 { margin: 0; font-size: 1.1rem; }
        .close-modal { color: white; font-size: 28px; cursor: pointer; font-weight: bold; line-height: 1; }
        .close-modal:hover { color: #ccc; }

        .modal-body { padding: 20px; max-height: 70vh; overflow-y: auto; }
        
        .detail-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .detail-table th { text-align: left; color: #888; border-bottom: 1px solid #eee; padding-bottom: 5px; font-size: 0.9em; }
        .detail-table td { padding: 10px 0; border-bottom: 1px solid #f9f9f9; vertical-align: top; }
        .item-qty { font-weight: bold; color: #B1935B; width: 40px; }
        .item-price { text-align: right; font-weight: 600; color: #555; }
        .item-note { display: block; font-size: 0.8rem; color: #e74c3c; font-style: italic; }

        .modal-footer {
            padding: 15px 20px; background-color: #f8f9fa; text-align: right; border-top: 1px solid #eee;
        }
        .loading-spinner { text-align: center; padding: 30px; color: #666; }

        /* BADGE STATUS PEMBAYARAN */
        .badge-payment { font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; font-weight: bold; display: inline-block; margin-top: 5px; }
        .badge-payment.paid { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .badge-payment.unpaid { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Highlight Tab Hari Ini */
        .status-tab.today-active {
            background-color: #B1935B; color: white; border-color: #B1935B;
            font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid" style="padding-bottom: 50px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Dapur & Kasir</h1>
            <p class="text-muted" style="margin-top: 5px;">
                @if(request('status') == 'today' || !request('status'))
                    Menampilkan pesanan masuk <strong>HARI INI ({{ date('d M Y') }})</strong>.
                @else
                    Mode Arsip / Filter Status.
                @endif
            </p>
        </div>
        <button class="btn-refresh" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Refresh</button>
    </div>

    {{-- Tabs --}}
    <div class="status-tabs-container">
        @php $s = request('status', 'today'); @endphp
        
        <a href="{{ route('admin.orders.index', ['status' => 'today']) }}" 
           class="status-tab {{ $s == 'today' ? 'today-active' : '' }}">
            <i class="fas fa-calendar-day"></i> HARI INI
        </a>

        <div style="border-left: 2px solid #ddd; height: 30px; margin: 0 10px;"></div>

        <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="status-tab {{ $s == 'new' ? 'active' : '' }}">Baru</a>
        <a href="{{ route('admin.orders.index', ['status' => 'preparing']) }}" class="status-tab {{ $s == 'preparing' ? 'active' : '' }}">Dimasak</a>
        <a href="{{ route('admin.orders.index', ['status' => 'ready']) }}" class="status-tab {{ $s == 'ready' ? 'active' : '' }}">Siap</a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="status-tab {{ $s == 'completed' ? 'active' : '' }}">Selesai</a>
        <a href="{{ route('admin.orders.index', ['status' => 'all']) }}" class="status-tab {{ $s == 'all' ? 'active' : '' }}">Semua Riwayat</a>
    </div>

    <div class="card-panel">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr><th>ID</th> <th>Pelanggan</th> <th>Lokasi</th> <th>Total & Bayar</th> <th>Status Pesanan</th> <th>Waktu</th> <th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr style="{{ in_array($order->status, ['new', 'pending', 'process', 'paid']) ? 'background-color: #fffde7;' : '' }}">
                            <td><span class="order-id">#{{ $order->id }}</span></td>
                            <td><strong>{{ $order->customer_name }}</strong><br><small class="text-muted">{{ $order->customer_phone }}</small></td>
                            <td><strong>{{ Str::before($order->shipping_address, '-') }}</strong><br><small>{{ Str::after($order->shipping_address, '-') }}</small></td>
                            
                            <td>
                                <div class="price-text">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                                <span class="payment-method" style="margin-right: 5px;">{{ strtoupper($order->payment_method) }}</span>
                                
                                @if($order->payment_status == 'paid')
                                    <span class="badge-payment paid"><i class="fas fa-check-circle"></i> LUNAS</span>
                                @else
                                    <span class="badge-payment unpaid"><i class="fas fa-times-circle"></i> BELUM BAYAR</span>
                                @endif
                            </td>

                            <td>
                                @if(in_array($order->status, ['new', 'pending', 'process', 'paid'])) 
                                    <span class="status-new">BARU!</span>
                                @else 
                                    <span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span> 
                                @endif
                            </td>
                            
                            <td>
                                {{ $order->created_at->format('H:i') }}
                                @if(!$order->created_at->isToday())
                                    <br><small class="text-muted">{{ $order->created_at->format('d/m') }}</small>
                                @endif
                            </td>

                            <td class="text-right">
                                <div class="action-buttons">
                                    @if(in_array($order->status, ['new', 'pending', 'process', 'paid']))
                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="form-update-status">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="preparing">
                                            <input type="hidden" name="action_label" value="Mulai Masak">
                                            <button type="submit" class="btn-icon btn-process" title="Mulai Masak"><i class="fas fa-fire"></i></button>
                                        </form>
                                    @elseif($order->status == 'preparing')
                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="form-update-status">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="ready">
                                            <input type="hidden" name="action_label" value="Sajikan">
                                            <button type="submit" class="btn-icon btn-ready" title="Siap Saji"><i class="fas fa-bell"></i></button>
                                        </form>
                                    @elseif($order->status == 'ready')
                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="form-update-status">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <input type="hidden" name="action_label" value="Selesaikan">
                                            <button type="submit" class="btn-icon btn-finish" title="Selesaikan"><i class="fas fa-check-double"></i></button>
                                        </form>
                                    @endif
                                    
                                    <button type="button" class="btn-icon btn-detail" onclick="showOrderDetail({{ $order->id }})" title="Lihat Rincian">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">Belum ada pesanan {{ request('status') == 'today' ? 'hari ini' : '' }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- PAGINATION --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>
</div>

{{-- MODAL POPUP --}}
<div id="orderDetailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Detail Pesanan #<span id="modalOrderId"></span></h4>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalLoading" class="loading-spinner"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Memuat Data...</div>
            <div id="modalContent" style="display: none;">
                <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dashed #ccc;">
                    <div style="font-weight: bold; font-size: 1.1em;" id="modalCustName"></div>
                    <div style="color: #666;" id="modalCustPhone"></div>
                    <div style="margin-top: 5px;">
                        <span style="background: #e3f2fd; padding: 2px 8px; border-radius: 4px; font-size: 0.9em; color: #1565c0;" id="modalTable"></span>
                        <span id="modalPaymentStatusBadge" class="badge-payment" style="margin-left: 10px;"></span>
                    </div>
                </div>
                
                <h5 style="margin: 0 0 10px 0; font-size: 0.9em; color: #888;">RINCIAN MENU</h5>
                <table class="detail-table"><tbody id="modalItemsList"></tbody></table>
                
                <div style="background: #f9f9f9; padding: 15px; margin-top: 15px; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9em; color: #666;">
                        <span>Subtotal</span><span id="modalSubtotal"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9em; color: #666; margin-bottom: 10px;">
                        <span>Biaya Layanan (0.7%)</span><span id="modalFee"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1em;">
                        <span>TOTAL</span><span style="color: #2c3e50;" id="modalTotal"></span>
                    </div>
                    <div style="margin-top: 10px; font-size: 0.9em;">
                        <strong>Catatan:</strong> <span id="modalNote" style="color: #555;">-</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" id="modalPrintLink" target="_blank" class="btn-icon btn-detail" style="width: auto; padding: 0 15px; text-decoration: none; font-size: 0.9em; height: 35px; line-height: 35px;">
                <i class="fas fa-print"></i> Cetak Struk
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById("orderDetailModal");
    
    function showOrderDetail(orderId) {
        modal.classList.add('show');
        document.getElementById("modalLoading").style.display = "block";
        document.getElementById("modalContent").style.display = "none";
        
        fetch(`/admin/orders/${orderId}/detail`)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    const order = data.order;
                    document.getElementById("modalOrderId").innerText = order.id;
                    document.getElementById("modalCustName").innerText = order.customer_name;
                    document.getElementById("modalCustPhone").innerText = order.customer_phone;
                    document.getElementById("modalTable").innerText = order.shipping_address;
                    document.getElementById("modalNote").innerText = order.notes || '-';
                    document.getElementById("modalPrintLink").href = `/pesanan/${order.id}/cetak`;
                    
                    const badge = document.getElementById("modalPaymentStatusBadge");
                    if(order.payment_status && order.payment_status.toLowerCase() === 'paid') {
                        badge.className = 'badge-payment paid';
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> LUNAS';
                    } else {
                        badge.className = 'badge-payment unpaid';
                        badge.innerHTML = '<i class="fas fa-times-circle"></i> BELUM BAYAR';
                    }

                    let itemsHtml = '';
                    let calculatedSubtotal = 0;
                    data.items.forEach(item => {
                        let price = parseInt(item.price.replace(/\./g, ''));
                        let sub = price * item.quantity;
                        calculatedSubtotal += sub;
                        itemsHtml += `
                            <tr>
                                <td class="item-qty">${item.quantity}x</td>
                                <td>
                                    <div>${item.menu_name}</div>
                                    ${item.item_notes ? `<span class="item-note">${item.item_notes}</span>` : ''}
                                </td>
                                <td class="item-price">Rp ${new Intl.NumberFormat('id-ID').format(sub)}</td>
                            </tr>`;
                    });
                    document.getElementById("modalItemsList").innerHTML = itemsHtml;

                    let totalStr = order.total_price.replace(/\./g, '');
                    let totalInt = parseInt(totalStr);
                    let serviceFee = totalInt - calculatedSubtotal;

                    document.getElementById("modalSubtotal").innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(calculatedSubtotal);
                    document.getElementById("modalFee").innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(serviceFee);
                    document.getElementById("modalTotal").innerText = 'Rp ' + order.total_price;
                    
                    document.getElementById("modalLoading").style.display = "none";
                    document.getElementById("modalContent").style.display = "block";
                }
            });
    }

    function closeModal() { modal.classList.remove('show'); }
    window.onclick = function(e) { if(e.target == modal) closeModal(); }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.form-update-status').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const label = this.querySelector('input[name="action_label"]').value;
                Swal.fire({
                    title: 'Konfirmasi', text: label + " pesanan ini?", icon: 'question',
                    showCancelButton: true, confirmButtonText: 'Ya', confirmButtonColor: '#3085d6'
                }).then((r) => { if(r.isConfirmed) this.submit(); });
            });
        });
    });
</script>
@endpush