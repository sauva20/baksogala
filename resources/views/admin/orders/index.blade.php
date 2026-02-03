@extends('layouts.admin')

@section('title', 'Manajemen Pesanan')

@section('styles')
    {{-- Pertahankan CSS asli Anda --}}
    <link rel="stylesheet" href="{{ asset('assets/css/admin_orders.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        /* CSS POPUP DETAIL (MODAL) - Sesuai kode awal Anda */
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
    </style>
@endsection

@section('content')
<div class="container-fluid" style="padding-bottom: 50px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Dapur & Kasir</h1>
            <p class="text-muted" style="margin-top: 5px;">Pantau pesanan masuk.</p>
        </div>
        <button class="btn-refresh" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Refresh</button>
    </div>

    {{-- Tabs --}}
    <div class="status-tabs-container">
        @php $s = request('status', 'all'); @endphp
        <a href="{{ route('admin.orders.index', ['status' => 'all']) }}" class="status-tab {{ $s == 'all' ? 'active' : '' }}">Semua</a>
        <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="status-tab {{ $s == 'new' ? 'active' : '' }}">Baru</a>
        <a href="{{ route('admin.orders.index', ['status' => 'preparing']) }}" class="status-tab {{ $s == 'preparing' ? 'active' : '' }}">Dimasak</a>
        <a href="{{ route('admin.orders.index', ['status' => 'ready']) }}" class="status-tab {{ $s == 'ready' ? 'active' : '' }}">Siap</a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="status-tab {{ $s == 'completed' ? 'active' : '' }}">Selesai</a>
    </div>

    {{-- Table --}}
    <div class="card-panel">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr><th>ID</th> <th>Pelanggan</th> <th>Lokasi</th> <th>Total</th> <th>Status</th> <th>Waktu</th> <th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        {{-- Highlight Pesanan Baru --}}
                        <tr style="{{ in_array($order->status, ['new', 'pending', 'process', 'paid']) ? 'background-color: #fffde7;' : '' }}">
                            <td><span class="order-id">#{{ $order->id }}</span></td>
                            <td><strong>{{ $order->customer_name }}</strong><br><small class="text-muted">{{ $order->customer_phone }}</small></td>
                            <td><strong>{{ Str::before($order->shipping_address, '-') }}</strong><br><small>{{ Str::after($order->shipping_address, '-') }}</small></td>
                            <td><div class="price-text">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div><span class="payment-method">{{ strtoupper($order->payment_method) }}</span></td>
                            <td>
                                {{-- FIX: Tambahkan 'process' dan 'paid' ke kondisi status BARU --}}
                                @if(in_array($order->status, ['new', 'pending', 'process', 'paid'])) 
                                    <span class="status-new">BARU!</span>
                                @else 
                                    <span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span> 
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('H:i') }}</td>
                            <td class="text-right">
                                <div class="action-buttons">
                                    {{-- FIX LOGIC TOMBOL: Tambahkan 'process' & 'paid' agar tombol muncul untuk pesanan lunas --}}
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
                        <tr><td colspan="7" class="empty-state">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">{{ $orders->withQueryString()->links() }}</div>
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
                    <div style="margin-top: 5px;"><span style="background: #e3f2fd; padding: 2px 8px; border-radius: 4px; font-size: 0.9em; color: #1565c0;" id="modalTable"></span></div>
                </div>
                
                <h5 style="margin: 0 0 10px 0; font-size: 0.9em; color: #888;">RINCIAN MENU</h5>
                <table class="detail-table"><tbody id="modalItemsList"></tbody></table>
                
                <div style="background: #f9f9f9; padding: 15px; margin-top: 15px; border-radius: 8px;">
                    {{-- [BARU] Rincian Harga Lengkap --}}
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    document.getElementById("modalNote").innerText = order.order_notes || '-';
                    document.getElementById("modalPrintLink").href = `/pesanan/${order.id}`;
                    
                    let itemsHtml = '';
                    let calculatedSubtotal = 0;

                    data.items.forEach(item => {
                        let sub = parseFloat(item.subtotal);
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

                    // Hitung Biaya Layanan
                    let serviceFee = order.total_price - calculatedSubtotal;

                    // Isi Rincian Harga
                    document.getElementById("modalSubtotal").innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(calculatedSubtotal);
                    document.getElementById("modalFee").innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(serviceFee);
                    document.getElementById("modalTotal").innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(order.total_price);
                    
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