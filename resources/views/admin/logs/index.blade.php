@extends('layouts.admin')

@section('title', 'System Audit Logs')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin_logs.css') }}">
@endsection

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Riwayat Log Sistem</h1>
            <p class="text-muted">Jejak audit keamanan dan aktivitas pengguna.</p>
        </div>
        <div class="header-stats">
            <div class="stat-item">
                <span class="label">Total Events</span>
                <span class="value">{{ $logs->total() }}</span>
            </div>
            <div class="stat-item danger">
                <span class="label">Critical</span>
                {{-- Contoh logic sederhana --}}
                <span class="value">{{ $logs->where('severity', 'danger')->count() }}</span>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="log-toolbar">
        <form action="{{ route('admin.logs.index') }}" method="GET" class="filter-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari ID, Deskripsi..." value="{{ request('search') }}">
            </div>
            
            <select name="module" onchange="this.form.submit()" class="filter-select">
                <option value="all">Semua Modul</option>
                @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                @endforeach
            </select>

            <select name="severity" onchange="this.form.submit()" class="filter-select">
                <option value="all">Semua Level</option>
                <option value="info" {{ request('severity') == 'info' ? 'selected' : '' }}>Info</option>
                <option value="warning" {{ request('severity') == 'warning' ? 'selected' : '' }}>Warning</option>
                <option value="danger" {{ request('severity') == 'danger' ? 'selected' : '' }}>Danger</option>
            </select>
        </form>
    </div>

    {{-- LOG TABLE --}}
    <div class="log-container">
        @forelse($logs as $log)
            <div class="log-entry {{ $log->severity }}">
                
                {{-- Baris Utama --}}
                <div class="log-header" onclick="toggleDetails({{ $log->id }})">
                    <div class="col-user">
                        <div class="avatar-small">{{ substr($log->user->name ?? 'S', 0, 1) }}</div>
                        <div class="user-meta">
                            <span class="name">{{ $log->user->name ?? 'System/Guest' }}</span>
                            <span class="ip">{{ $log->ip_address }}</span>
                        </div>
                    </div>

                    <div class="col-action">
                        <span class="badge-action {{ $log->action }}">{{ $log->action }}</span>
                        <span class="module-name">{{ $log->module }}</span>
                    </div>

                    <div class="col-desc">
                        {{ $log->description }}
                    </div>

                    <div class="col-time">
                        <span>{{ $log->created_at->diffForHumans() }}</span>
                        <small>{{ $log->created_at->format('H:i:s') }}</small>
                    </div>

                    <div class="col-chevron">
                        <i class="fas fa-chevron-down" id="icon-{{ $log->id }}"></i>
                    </div>
                </div>

                {{-- Detail Tersembunyi (Changes JSON) --}}
                <div class="log-details" id="details-{{ $log->id }}">
                    <div class="detail-grid">
                        <div class="tech-info">
                            <strong>User Agent:</strong> {{ $log->user_agent }} <br>
                            <strong>Timestamp:</strong> {{ $log->created_at }} <br>
                            <strong>Log ID:</strong> #{{ $log->id }}
                        </div>
                        
                        @if($log->changes)
                            <div class="code-block">
                                <div class="code-label">DATA CHANGES (JSON)</div>
                                <pre>@json($log->changes, JSON_PRETTY_PRINT)</pre>
                            </div>
                        @else
                            <div class="no-changes">Tidak ada perubahan data spesifik.</div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-log">
                <i class="fas fa-clipboard-check"></i>
                <p>Belum ada aktivitas tercatat.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>

<script>
    function toggleDetails(id) {
        const detail = document.getElementById(`details-${id}`);
        const icon = document.getElementById(`icon-${id}`);
        
        if (detail.style.maxHeight) {
            detail.style.maxHeight = null;
            detail.classList.remove('open');
            icon.style.transform = 'rotate(0deg)';
        } else {
            detail.style.maxHeight = detail.scrollHeight + "px";
            detail.classList.add('open');
            icon.style.transform = 'rotate(180deg)';
        }
    }
</script>
@endsection