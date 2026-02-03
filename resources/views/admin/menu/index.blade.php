@extends('layouts.admin')

@section('title', 'Manajemen Menu')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin_menu.css') }}">
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        
        .modal-content-custom {
            background-color: #fefefe; margin: auto; padding: 0; border: 1px solid #888;
            width: 90%; max-width: 700px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            animation: slideDown 0.3s; position: relative;
        }
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .modal-header { background: #2c3e50; color: white; padding: 15px 25px; border-top-left-radius: 12px; border-top-right-radius: 12px; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { margin: 0; font-size: 1.25rem; font-weight: 600; }
        .btn-close { background: none; border: none; color: white; font-size: 2rem; cursor: pointer; line-height: 1; }
        
        .modal-body { padding: 25px; }
        .modal-footer { padding: 15px 25px; background: #f8f9fa; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; text-align: right; border-top: 1px solid #eee; }

        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .col-8 { flex: 2; } .col-4 { flex: 1; } .col-6 { flex: 1; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.95rem; }
        
        /* Checkbox Style */
        .options-group { display: flex; gap: 20px; align-items: center; background: #f1f3f5; padding: 10px; border-radius: 8px; }
        .checkbox-wrapper { display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal; margin: 0; }
        .checkbox-wrapper input { width: 18px; height: 18px; cursor: pointer; accent-color: #2c3e50; }

        .btn-save { background: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin-right: 10px; }
        
        .img-wrapper img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .badge-category { background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; color: #495057; font-weight: 600; }
    </style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Manajemen Menu</h1>
            <p class="text-muted">Kelola daftar makanan dan minuman Bakso Gala.</p>
        </div>
        <button class="btn-refresh" onclick="openModal('add')" style="background: #2c3e50; color: white;">
            <i class="fas fa-plus"></i> Tambah Menu
        </button>
    </div>

    {{-- FILTER --}}
    <div class="filter-bar mb-3">
        <form action="{{ route('admin.menu.index') }}" method="GET" class="filter-form" style="display: flex; gap: 10px;">
            <div class="search-group" style="flex: 1;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama menu..." value="{{ request('search') }}">
            </div>
            <select name="category" onchange="this.form.submit()" class="form-control" style="width: 200px;">
                <option value="all">Semua Kategori</option>
                <option value="Bakso Soun" {{ request('category') == 'Bakso Soun' ? 'selected' : '' }}>Bakso Soun</option>
                <option value="Bakmie" {{ request('category') == 'Bakmie' ? 'selected' : '' }}>Bakmie</option>
                <option value="Side Dish" {{ request('category') == 'Side Dish' ? 'selected' : '' }}>Side Dish</option>
                <option value="Drink" {{ request('category') == 'Drink' ? 'selected' : '' }}>Drink</option>
            </select>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="card-panel">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th width="80">Gambar</th>
                        <th>Info Menu</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menuItems as $item)
                    <tr>
                        <td>
                            <div class="img-wrapper">
                                <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}">
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: bold; font-size: 1.05em;">{{ $item->name }}</div>
                            <div class="text-muted" style="font-size: 0.85em;">{{ Str::limit($item->description, 50) }}</div>
                        </td>
                        <td><span class="badge-category">{{ $item->category }}</span></td>
                        <td style="font-weight: bold; color: #2c3e50;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>
                            @if($item->is_available) <span style="color: green; font-weight:bold;">● Ready</span> 
                            @else <span style="color: red; font-weight:bold;">● Habis</span> @endif
                            
                            @if($item->is_favorite) <span title="Favorit">⭐</span> @endif
                            @if($item->show_on_homepage) <span title="Home">🏠</span> @endif
                        </td>
                        <td class="text-right">
                            <button class="btn-icon" style="color: #2980b9; border:none; background:none; cursor:pointer;" onclick='openModal("edit", @json($item))'>
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                            <button class="btn-icon" style="color: #e74c3c; border:none; background:none; cursor:pointer;" onclick="confirmDelete({{ $item->id }})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <form id="delete-form-{{ $item->id }}" action="{{ route('admin.menu.destroy', $item->id) }}" method="POST" style="display: none;">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <p class="text-muted">Belum ada menu yang ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper mt-4">
            {{ $menuItems->withQueryString()->links() }}
        </div>
    </div>
</div>

{{-- MODAL FORM (ADD & EDIT) --}}
<div id="menuModal" class="modal"> 
    <div class="modal-content-custom">
        <div class="modal-header">
            <h2 id="modalTitle">Tambah Menu Baru</h2>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="menuForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="methodField"></div> {{-- Tempat @method('PUT') --}}

            <div class="modal-body">
                <div class="form-row">
                    <div class="col-8">
                        <label>Nama Menu</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label>Kategori</label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="Bakso Soun">Bakso Soun</option>
                            <option value="Bakmie">Bakmie</option>
                            <option value="Side Dish">Side Dish</option>
                            <option value="Drink">Drink</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>Deskripsi Singkat</label>
                    <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-row mt-3">
                    <div class="col-6">
                        <label>Harga (Rp)</label>
                        <input type="number" name="price" id="price" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label>Upload Gambar</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>

                {{-- Image Preview --}}
                <div id="imagePreviewBox" class="mt-2" style="display:none; text-align: center;">
                    <p style="font-size: 0.8em; margin-bottom: 5px;">Preview Gambar:</p>
                    <img id="imagePreview" src="" style="max-height: 120px; border-radius: 8px; border: 1px solid #ddd;">
                </div>

                <div class="options-group mt-4">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="is_available" id="is_available" value="1" checked> Tersedia
                    </label>
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="is_favorite" id="is_favorite" value="1"> Menu Favorit
                    </label>
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="show_on_homepage" id="show_on_homepage" value="1"> Tampil di Home
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-save">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modal = document.getElementById('menuModal');
    const form = document.getElementById('menuForm');
    const modalTitle = document.getElementById('modalTitle');
    const methodField = document.getElementById('methodField');
    const imgPreviewBox = document.getElementById('imagePreviewBox');
    const imgPreview = document.getElementById('imagePreview');

    // 1. FUNGSI BUKA MODAL
    function openModal(mode, data = null) {
        modal.classList.add('show');
        
        if (mode === 'add') {
            modalTitle.innerText = 'Tambah Menu Baru';
            form.action = "{{ route('admin.menu.store') }}";
            methodField.innerHTML = ''; // Hapus method PUT
            form.reset();
            
            // Reset Preview
            imgPreviewBox.style.display = 'none';
            imgPreview.src = '';
            
            document.getElementById('is_available').checked = true;
            document.getElementById('is_favorite').checked = false;
            document.getElementById('show_on_homepage').checked = false;

        } else {
            modalTitle.innerText = 'Edit Menu: ' + data.name;
            form.action = `/admin/menu/${data.id}`;
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">'; 
            
            // Isi Form
            document.getElementById('name').value = data.name;
            document.getElementById('category').value = data.category;
            document.getElementById('description').value = data.description;
            document.getElementById('price').value = data.price;
            
            document.getElementById('is_available').checked = (data.is_available == 1);
            document.getElementById('is_favorite').checked = (data.is_favorite == 1);
            document.getElementById('show_on_homepage').checked = (data.show_on_homepage == 1);

            // Preview Gambar Lama (Perbaikan Path)
            if(data.image_url) {
                // Hapus slash di awal jika ada
                let path = data.image_url.startsWith('/') ? data.image_url.substring(1) : data.image_url;
                imgPreview.src = "{{ asset('') }}" + path;
                imgPreviewBox.style.display = 'block';
            } else {
                imgPreviewBox.style.display = 'none';
            }
        }
    }

    function closeModal() {
        modal.classList.remove('show');
    }

    // 2. LIVE PREVIEW GAMBAR
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            imgPreview.src = reader.result;
            imgPreviewBox.style.display = 'block';
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    // 3. DELETE CONFIRMATION
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Menu?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }

    // Close modal if click outside
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    // --- SUCCESS ALERT ---
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    // --- ERROR ALERT (INI YANG SEBELUMNYA KURANG) ---
    @if($errors->any())
        let errorMsg = '<ul style="text-align:left;">';
        @foreach($errors->all() as $error)
            errorMsg += '<li>{{ $error }}</li>';
        @endforeach
        errorMsg += '</ul>';

        Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan!',
            html: errorMsg,
            confirmButtonColor: '#d33'
        });
    @endif
</script>
@endpush