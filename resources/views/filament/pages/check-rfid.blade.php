<x-filament-panels::page>

<style>
    .rfid-scan-input {
        width: 100%;
        padding: 14px 18px;
        font-size: 1.25rem;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.1em;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #fff;
        color: #111827;
    }
    .dark .rfid-scan-input {
        background: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    .rfid-scan-input:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245,158,11,0.15);
    }
    .rfid-btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 22px;
        background: #f59e0b; color: #fff;
        font-weight: 600; font-size: 0.9rem;
        border: none; border-radius: 8px;
        cursor: pointer; transition: background 0.2s, transform 0.1s;
        white-space: nowrap;
    }
    .rfid-btn-primary:hover { background: #d97706; }
    .rfid-btn-primary:active { transform: scale(0.97); }
    .rfid-btn-secondary {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 18px;
        background: #f3f4f6; color: #374151;
        font-weight: 600; font-size: 0.9rem;
        border: 1px solid #e5e7eb; border-radius: 8px;
        cursor: pointer; transition: background 0.2s;
        white-space: nowrap;
    }
    .dark .rfid-btn-secondary { background: #374151; color: #d1d5db; border-color: #4b5563; }
    .rfid-btn-secondary:hover { background: #e5e7eb; }
    .dark .rfid-btn-secondary:hover { background: #4b5563; }

    .rfid-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    .dark .rfid-card { background: #1f2937; border-color: #374151; }

    .rfid-card-header {
        padding: 14px 20px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #6b7280;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .dark .rfid-card-header { background: #111827; border-color: #374151; color: #9ca3af; }

    .rfid-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 20px;
        border-bottom: 1px solid #f3f4f6;
        gap: 12px;
    }
    .dark .rfid-row { border-color: #374151; }
    .rfid-row:last-child { border-bottom: none; }
    .rfid-row:nth-child(even) { background: #fafafa; }
    .dark .rfid-row:nth-child(even) { background: #111827; }

    .rfid-label {
        font-size: 0.82rem;
        font-weight: 500;
        color: #6b7280;
        flex-shrink: 0;
    }
    .rfid-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: #111827;
        text-align: right;
        word-break: break-all;
    }
    .dark .rfid-value { color: #f3f4f6; }

    .rfid-mono { font-family: 'Courier New', monospace; letter-spacing: 0.05em; }
    .rfid-bib  { font-size: 1.4rem; font-weight: 800; color: #111827; }
    .dark .rfid-bib { color: #fff; }

    .badge-active   { display:inline-block; padding: 3px 10px; border-radius: 999px; font-size:0.75rem; font-weight:700; background:#dcfce7; color:#15803d; }
    .badge-inactive { display:inline-block; padding: 3px 10px; border-radius: 999px; font-size:0.75rem; font-weight:700; background:#fee2e2; color:#b91c1c; }

    .result-banner {
        display: flex; align-items: center; gap: 12px;
        padding: 16px 22px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-weight: 700; font-size: 1rem;
    }
    .result-banner.success { background: #f0fdf4; border: 1.5px solid #86efac; color: #15803d; }
    .result-banner.danger  { background: #fef2f2; border: 1.5px solid #fca5a5; color: #b91c1c; }
    .dark .result-banner.success { background: #052e16; border-color: #166534; color: #4ade80; }
    .dark .result-banner.danger  { background: #450a0a; border-color: #991b1b; color: #f87171; }

    .rfid-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 768px) { .rfid-grid { grid-template-columns: 1fr; } }

    .rfid-empty-state {
        display: flex; flex-direction: column; align-items: center;
        justify-content: center; gap: 14px; padding: 56px 24px; text-align: center;
    }
    .rfid-empty-icon { font-size: 3.5rem; opacity: 0.25; }
    .rfid-empty-title { font-size: 1rem; color: #6b7280; }
    .dark .rfid-empty-title { color: #9ca3af; }

    .rfid-not-found-body {
        display: flex; flex-direction: column; align-items: center;
        gap: 12px; padding: 40px 24px; text-align: center;
    }
    .rfid-not-found-tag {
        font-size: 1.5rem; font-family: monospace;
        font-weight: 800; color: #b91c1c;
        background: #fee2e2; padding: 8px 20px; border-radius: 8px;
        word-break: break-all;
    }
    .dark .rfid-not-found-tag { background: #450a0a; color: #f87171; }
    .rfid-not-found-hint { font-size: 0.85rem; color: #6b7280; max-width: 420px; }
    .dark .rfid-not-found-hint { color: #9ca3af; }
</style>

<div style="display:flex; flex-direction:column; gap:20px;">

    {{-- ── Input Card ── --}}
    <div class="rfid-card">
        <div class="rfid-card-header">
            🔍 &nbsp;Scan / Input RFID Tag
        </div>
        <div style="padding: 20px;">
            <p style="font-size:0.85rem; color:#6b7280; margin-bottom:14px;">
                Arahkan scanner RFID ke field di bawah, atau ketik manual lalu tekan <kbd style="background:#f3f4f6;border:1px solid #d1d5db;border-radius:4px;padding:1px 6px;font-size:0.8rem;">Enter</kbd>.
            </p>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <div style="flex:1; min-width:200px;">
                    <input
                        type="text"
                        id="rfid-input-field"
                        wire:model.live.debounce.400ms="rfid_input"
                        wire:keydown.enter="searchRfid"
                        placeholder="Scan RFID atau ketik tag..."
                        autocomplete="off"
                        class="rfid-scan-input"
                    />
                </div>
                <button wire:click="searchRfid" class="rfid-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:17px;height:17px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    Cek RFID
                </button>
                <button wire:click="resetForm" class="rfid-btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.635 19A9 9 0 1 0 4.582 9"/>
                    </svg>
                    Reset
                </button>
            </div>
        </div>
    </div>

    {{-- ── Result ── --}}
    @if ($searched)
        @if ($found && $result)

            {{-- Banner --}}
            <div class="result-banner success">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
                RFID Ditemukan &amp; Terdaftar
            </div>

            <div class="rfid-grid">

                {{-- Info RFID --}}
                <div class="rfid-card">
                    <div class="rfid-card-header">📡 &nbsp;Info RFID</div>
                    <div class="rfid-row">
                        <span class="rfid-label">RFID Tag</span>
                        <span class="rfid-value rfid-mono" style="font-size:0.78rem;">{{ $result['rfid_tag'] }}</span>
                    </div>
                    <div class="rfid-row">
                        <span class="rfid-label">Status</span>
                        <span>
                            @if ($result['is_active'])
                                <span class="badge-active">✓ Active</span>
                            @else
                                <span class="badge-inactive">✗ Inactive</span>
                            @endif
                        </span>
                    </div>
                    <div class="rfid-row">
                        <span class="rfid-label">Assigned At</span>
                        <span class="rfid-value">{{ $result['assigned_at'] }}</span>
                    </div>
                    <div class="rfid-row">
                        <span class="rfid-label">Assigned By</span>
                        <span class="rfid-value">{{ $result['assigned_by'] }}</span>
                    </div>
                    @if ($result['notes'] !== '-')
                        <div class="rfid-row" style="flex-direction:column; align-items:flex-start; gap:6px;">
                            <span class="rfid-label">Notes</span>
                            <span class="rfid-value" style="text-align:left;">{{ $result['notes'] }}</span>
                        </div>
                    @endif
                </div>

                {{-- Info Peserta --}}
                <div class="rfid-card">
                    <div class="rfid-card-header">🏃 &nbsp;Info Peserta</div>
                    <div class="rfid-row" style="background:#fffbeb;">
                        <span class="rfid-label">BIB</span>
                        <span class="rfid-bib">#{{ $result['bib'] }}</span>
                    </div>
                    <div class="rfid-row">
                        <span class="rfid-label">Nama</span>
                        <span class="rfid-value" style="font-size:1rem;">{{ $result['name'] }}</span>
                    </div>
                    <div class="rfid-row">
                        <span class="rfid-label">Event</span>
                        <span class="rfid-value">{{ $result['event'] }}</span>
                    </div>
                    <div class="rfid-row">
                        <span class="rfid-label">Kategori</span>
                        <span class="rfid-value">{{ $result['category'] }}</span>
                    </div>
                </div>

            </div>

        @else

            {{-- Banner --}}
            <div class="result-banner danger">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
                RFID Tidak Ditemukan
            </div>

            <div class="rfid-card">
                <div class="rfid-not-found-body">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:60px;height:60px;color:#fbbf24;opacity:0.7;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <div class="rfid-not-found-tag">{{ $rfid_input }}</div>
                    <p style="font-size:1rem; font-weight:700; color:#374151;" class="dark:text-gray-300">Tag ini belum di-mapping ke peserta manapun.</p>
                    <p class="rfid-not-found-hint">
                        Silakan lakukan assignment terlebih dahulu melalui menu <strong>RFID Assignments</strong>, atau periksa apakah tag sudah terdaftar dengan benar.
                    </p>
                </div>
            </div>

        @endif
    @else

        {{-- Empty state --}}
        <div class="rfid-card">
            <div class="rfid-empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:72px;height:72px;color:#9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75V16.5zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 18.75h.75v.75h-.75v-.75zM18.75 13.5h.75v.75h-.75v-.75zM18.75 18.75h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75V16.5z"/>
                </svg>
                <p class="rfid-empty-title" style="font-size:1rem; font-weight:600;">Siap untuk scan RFID</p>
                <p class="rfid-empty-title">Scan tag atau ketik manual di field atas untuk mengecek status peserta.</p>
            </div>
        </div>

    @endif

</div>

<script>
    document.addEventListener('livewire:navigated', () => focusRfidInput());
    document.addEventListener('livewire:updated',   () => focusRfidInput());

    function focusRfidInput() {
        const el = document.getElementById('rfid-input-field');
        if (el && document.activeElement !== el) el.focus();
    }

    focusRfidInput();
</script>

</x-filament-panels::page>
