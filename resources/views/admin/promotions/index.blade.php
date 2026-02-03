@extends('layouts.admin')

@section('title', 'Manajemen Voucher')

@section('styles')
    {{-- Pastikan file CSS ini ada di public/assets/css/admin_promo.css --}}
    <link rel="stylesheet" href="{{ asset('assets/css/admin_promo.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="container-fluid">
    
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Diskon & Voucher</h1>
            <p class="text-muted">Kelola promo untuk menarik pelanggan lebih banyak.</p>
        </div>
        {{-- Tombol Trigger Modal Add --}}
        <button class="btn-add" onclick="openModal('add')">
            <i class="fas fa-ticket-alt"></i> Buat Voucher Baru
        </button>
    </div>

    {{-- Grid Voucher --}}
    <div class="promo-grid">
        {{-- INI YANG BENAR: Menggunakan $promotions --}}
        @forelse($promotions as $promo)
            <div class="ticket-card {{ $promo->is_active ? '' : 'inactive' }}">
                
                {{-- Bagian Kiri (Nilai Diskon) --}}
                <div class="ticket-left">
                    <div class="ticket-hole-top"></div>
                    <div class="ticket-hole-bottom"></div>
                    <div class="discount-wrapper">
                        @if($promo->type == 'percentage')
                            <span class="big-number">{{ intval($promo->discount_amount) }}%</span>
                            <span class="label">OFF</span>
                        @else
                            <span class="label">HEMAT</span>
                            {{-- Format Ribuan (10k) --}}
                            <span class="big-number small-font">Rp {{ number_format($promo->discount_amount/1000, 0) }}k</span>
                        @endif
                    </div>
                </div>

                {{-- Bagian Kanan (Info Detail) --}}
                <div class="ticket-right">
                    <div class="ticket-header">
                        <span class="badge-type">{{ $promo->type == 'percentage' ? 'Persentase' : 'Potongan Tetap' }}</span>
                        @if($promo->is_active && now() <= $promo->end_date)
                            <span class="status-dot active" title="Aktif"></span>
                        @else
                            <span class="status-dot inactive" title="Nonaktif / Expired"></span>
                        @endif
                    </div>
                    
                    <h3 class="voucher-code">{{ $promo->code }}</h3>
                    
                    <div class="voucher-meta">
                        <p><i class="far fa-calendar-alt"></i> s/d {{ date('d M Y', strtotime($promo->end_date)) }}</p>
                        <p><i class="fas fa-shopping-cart"></i> Min: Rp {{ number_format($promo->min_purchase, 0, ',', '.') }}</p>
                        <p><i class="fas fa-users"></i> Sisa: {{ $promo->quota }}</p>
                    </div>

                    <div class="ticket-actions">
                        {{-- FIX AMAN UNTUK TOMBOL EDIT --}}
                        <button class="btn-action edit" onclick='openModal("edit", @json($promo))'>
                            <i class="fas fa-pencil-alt"></i> Edit
                        </button>
                        <button class="btn-action delete" onclick="confirmDelete({{ $promo->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    {{-- Form Delete Hidden --}}
                    <form id="delete-form-{{ $promo->id }}" action="{{ route('admin.promotions.destroy', $promo->id) }}" method="POST" style="display:none">
                        @csrf @method('DELETE')
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: 1/-1; text-align:center; padding: 40px;">
                <i class="fas fa-ticket-alt" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                <h3>Belum ada voucher aktif</h3>
                <p class="text-muted">Buat voucher pertama Anda sekarang!</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrapper mt-4">
        {{ $promotions->links() }}
    </div>
</div>

{{-- MODAL FORM POPUP --}}
<div id="promoModal" class="modal-backdrop">
    <div class="modal-content-custom">
        <div class="modal-header">
            <h2 id="modalTitle" style="margin:0; font-size:1.2rem; color:#2F3D65;">Buat Voucher Baru</h2>
            <button class="btn-close" onclick="closeModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        
        <form id="promoForm" method="POST">
            @csrf
            <div id="methodField"></div>
            
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>Kode Voucher (Unik)</label>
                    <input type="text" name="code" id="code" class="form-control" style="text-transform:uppercase; font-weight:bold; letter-spacing:2px;" placeholder="CONTOH: GALA50" required>
                </div>

                <div class="form-row">
                    <div class="col-6">
                        <label>Tipe Diskon</label>
                        <select name="type" id="type" class="form-control" onchange="toggleMaxDiscount()">
                            <option value="fixed">Nominal (Rp)</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label>Nilai Potongan</label>
                        <input type="number" name="discount_amount" id="discount_amount" class="form-control" placeholder="10000 / 50" required>
                    </div>
                </div>

                <div class="form-group mb-3" id="maxDiscountBox" style="display:none;">
                    <label>Maksimal Potongan (Rp)</label>
                    <input type="number" name="max_discount" id="max_discount" class="form-control" placeholder="Opsional">
                </div>

                <div class="form-row">
                    <div class="col-6">
                        <label>Min. Belanja (Rp)</label>
                        <input type="number" name="min_purchase" id="min_purchase" class="form-control" value="0">
                    </div>
                    <div class="col-6">
                        <label>Kuota</label>
                        <input type="number" name="quota" id="quota" class="form-control" value="100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-6">
                        <label>Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label>Berakhir</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                    </div>
                </div>

                <div style="margin-top:15px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked style="width:18px; height:18px;">
                        <span>Aktifkan Voucher Ini</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-save">Simpan Voucher</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('promoModal');
    const form = document.getElementById('promoForm');
    
    // Toggle input Max Discount
    function toggleMaxDiscount() {
        const type = document.getElementById('type').value;
        const box = document.getElementById('maxDiscountBox');
        box.style.display = (type === 'percentage') ? 'block' : 'none';
    }

    // Fungsi Buka Modal
    function openModal(mode, data = null) {
        modal.classList.add('show'); // Menambah class .show agar CSS memunculkan modal
        
        if (mode === 'add') {
            document.getElementById('modalTitle').innerText = 'Buat Voucher Baru';
            form.action = "{{ route('admin.promotions.store') }}";
            document.getElementById('methodField').innerHTML = '';
            form.reset();
            // Default active
            document.getElementById('is_active').checked = true;
            toggleMaxDiscount();
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Voucher';
            form.action = `/admin/promotions/${data.id}`;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            
            // Isi Data
            document.getElementById('code').value = data.code;
            document.getElementById('type').value = data.type;
            document.getElementById('discount_amount').value = parseInt(data.discount_amount);
            document.getElementById('min_purchase').value = parseInt(data.min_purchase);
            document.getElementById('max_discount').value = data.max_discount ? parseInt(data.max_discount) : '';
            document.getElementById('quota').value = data.quota;
            
            // Format Tanggal YYYY-MM-DD
            document.getElementById('start_date').value = data.start_date.split('T')[0];
            document.getElementById('end_date').value = data.end_date.split('T')[0];
            
            document.getElementById('is_active').checked = data.is_active;
            
            toggleMaxDiscount();
        }
    }

    function closeModal() {
        modal.classList.remove('show');
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Voucher?',
            text: "Pelanggan tidak akan bisa menggunakannya lagi!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-'+id).submit();
            }
        })
    }

    // Pesan Sukses
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
    @endif
</script>
@endpush