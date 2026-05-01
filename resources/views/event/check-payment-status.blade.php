{{-- resources/views/admin/transactions/check-payment-status.blade.php --}}
@extends('layouts.app')

@section('title', 'Cek Status Pembayaran Tripay')

<style>
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap');

    :root {
        --bg: #0d0f14;
        --surface: #161920;
        --border: #252830;
        --border-bright: #353a45;
        --text: #e2e5ec;
        --muted: #6b7180;
        --accent: #3b82f6;
        --accent-dim: rgba(59,130,246,0.12);
        --green: #22c55e;
        --green-dim: rgba(34,197,94,0.12);
        --yellow: #f59e0b;
        --yellow-dim: rgba(245,158,11,0.12);
        --red: #ef4444;
        --red-dim: rgba(239,68,68,0.12);
        --mono: 'IBM Plex Mono', monospace;
        --sans: 'IBM Plex Sans', sans-serif;
    }

    body { background: var(--bg); color: var(--text); font-family: var(--sans); }

    .ps-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 2rem 1.5rem 4rem;
    }

    /* Header */
    .ps-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
    }
    .ps-title {
        font-family: var(--mono);
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: -0.02em;
        color: var(--text);
        margin: 0 0 .25rem;
    }
    .ps-subtitle {
        font-size: .8rem;
        color: var(--muted);
        font-family: var(--mono);
        margin: 0;
    }
    .btn-refresh {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: .55rem 1.1rem;
        font-size: .82rem;
        font-family: var(--mono);
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: opacity .15s;
        white-space: nowrap;
    }
    .btn-refresh:hover { opacity: .85; color: #fff; }
    .btn-refresh svg { width: 15px; height: 15px; }

    /* Summary Cards */
    .ps-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .75rem;
        margin-bottom: 2rem;
    }
    .summary-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 1rem 1.2rem;
    }
    .summary-card .s-label {
        font-size: .7rem;
        font-family: var(--mono);
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: .4rem;
    }
    .summary-card .s-value {
        font-size: 1.6rem;
        font-weight: 600;
        font-family: var(--mono);
        line-height: 1;
    }
    .summary-card.s-total  .s-value { color: var(--text); }
    .summary-card.s-mismatch .s-value { color: var(--yellow); }
    .summary-card.s-synced .s-value { color: var(--green); }
    .summary-card.s-error  .s-value { color: var(--red); }
    .summary-card.s-mismatch { border-color: rgba(245,158,11,.25); background: var(--yellow-dim); }
    .summary-card.s-synced  { border-color: rgba(34,197,94,.2);   background: var(--green-dim); }
    .summary-card.s-error   { border-color: rgba(239,68,68,.2);   background: var(--red-dim); }

    /* Tabs */
    .ps-tabs {
        display: flex;
        gap: .25rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }
    .tab-btn {
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        padding: .6rem 1rem;
        font-size: .8rem;
        font-family: var(--mono);
        color: var(--muted);
        cursor: pointer;
        margin-bottom: -1px;
        display: flex;
        align-items: center;
        gap: .4rem;
        transition: color .15s;
    }
    .tab-btn:hover { color: var(--text); }
    .tab-btn.active { color: var(--text); border-bottom-color: var(--accent); }
    .tab-btn .tab-count {
        background: var(--border);
        border-radius: 99px;
        padding: .1rem .45rem;
        font-size: .7rem;
    }
    .tab-btn.active .tab-count { background: var(--accent); color: #fff; }

    /* Tab Panels */
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* Filter / Search bar */
    .ps-filter {
        display: flex;
        gap: .5rem;
        margin-bottom: 1rem;
    }
    .filter-input {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: .45rem .8rem;
        color: var(--text);
        font-family: var(--mono);
        font-size: .8rem;
        flex: 1;
        outline: none;
        transition: border-color .15s;
    }
    .filter-input:focus { border-color: var(--accent); }
    .filter-input::placeholder { color: var(--muted); }

    /* Table */
    .ps-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: 8px;
    }
    .ps-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .78rem;
        font-family: var(--mono);
    }
    .ps-table thead th {
        background: var(--surface);
        padding: .7rem 1rem;
        text-align: left;
        color: var(--muted);
        font-weight: 500;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .ps-table tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background .1s;
    }
    .ps-table tbody tr:last-child { border-bottom: none; }
    .ps-table tbody tr:hover { background: rgba(255,255,255,.02); }
    .ps-table td {
        padding: .75rem 1rem;
        vertical-align: middle;
        color: var(--text);
    }
    .ps-table td.muted { color: var(--muted); }
    .mono-sm { font-family: var(--mono); font-size: .75rem; }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .2rem .55rem;
        border-radius: 4px;
        font-size: .68rem;
        font-weight: 600;
        font-family: var(--mono);
        letter-spacing: .03em;
        white-space: nowrap;
    }
    .badge-yellow { background: var(--yellow-dim); color: var(--yellow); border: 1px solid rgba(245,158,11,.25); }
    .badge-green  { background: var(--green-dim);  color: var(--green);  border: 1px solid rgba(34,197,94,.2); }
    .badge-red    { background: var(--red-dim);    color: var(--red);    border: 1px solid rgba(239,68,68,.2); }
    .badge-blue   { background: var(--accent-dim); color: var(--accent); border: 1px solid rgba(59,130,246,.2); }
    .badge-gray   { background: rgba(107,113,128,.12); color: var(--muted); border: 1px solid rgba(107,113,128,.2); }
    .badge .dot   { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    /* Mismatch highlight */
    .row-mismatch td:first-child { border-left: 2px solid var(--yellow); }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--muted);
        font-family: var(--mono);
        font-size: .82rem;
    }
    .empty-state svg { width: 36px; height: 36px; opacity: .3; margin-bottom: .75rem; }

    /* Timestamp note */
    .ps-footer {
        margin-top: 1.25rem;
        font-size: .72rem;
        font-family: var(--mono);
        color: var(--muted);
    }
</style>


@section('content')
<div class="ps-wrap">

    {{-- Header --}}
    <div class="ps-header">
        <div>
            <h1 class="ps-title">// check-payment-status</h1>
            <p class="ps-subtitle">Membandingkan status transaksi DB vs Tripay secara realtime</p>
        </div>
        <a href="{{ route('transactions.check-payment-status') }}" class="btn-refresh">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Refresh
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="ps-summary">
        <div class="summary-card s-total">
            <div class="s-label">Total Dicek</div>
            <div class="s-value">{{ $summary['total_checked'] }}</div>
        </div>
        <div class="summary-card s-mismatch">
            <div class="s-label">Mismatch (Sudah Bayar)</div>
            <div class="s-value">{{ $summary['total_mismatch'] }}</div>
        </div>
        <div class="summary-card s-synced">
            <div class="s-label">Sinkron</div>
            <div class="s-value">{{ $summary['total_synced'] }}</div>
        </div>
        <div class="summary-card s-error">
            <div class="s-label">Error API</div>
            <div class="s-value">{{ $summary['total_error'] }}</div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="ps-tabs">
        <button class="tab-btn active" onclick="switchTab('mismatch', this)">
            ⚠ Mismatch
            <span class="tab-count">{{ count($mismatched) }}</span>
        </button>
        <button class="tab-btn" onclick="switchTab('all', this)">
            Semua
            <span class="tab-count">{{ count($all) }}</span>
        </button>
        @if(count($errors) > 0)
        <button class="tab-btn" onclick="switchTab('errors', this)">
            ✗ Error
            <span class="tab-count">{{ count($errors) }}</span>
        </button>
        @endif
    </div>

    {{-- TAB: Mismatch --}}
    <div id="tab-mismatch" class="tab-panel active">
        <div class="ps-filter">
            <input class="filter-input" type="text" placeholder="Cari merchant ref / tripay ref / participant..." oninput="filterTable('table-mismatch', this.value)">
        </div>
        <div class="ps-table-wrap">
            <table class="ps-table" id="table-mismatch">
                <thead>
                    <tr>
                        <th>Merchant Ref</th>
                        <th>Tripay Ref</th>
                        <th>Participant</th>
                        <th>Event</th>
                        <th>Total</th>
                        <th>Status DB</th>
                        <th>Status Tripay</th>
                        <th>Expired At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mismatched as $row)
                    <tr class="row-mismatch">
                        <td><span class="mono-sm">{{ $row['merchant_ref'] }}</span></td>
                        <td><span class="mono-sm">{{ $row['tripay_reference'] }}</span></td>
                        <td class="muted">{{ $row['participant_name'] ?? $row['participant_id'] }}</td>
                        <td class="muted">{{ $row['event_name'] ?? $row['event_id'] }}</td>
                        <td><span class="mono-sm">Rp {{ number_format($row['total_amount'], 0, ',', '.') }}</span></td>
                        <td><span class="badge badge-yellow"><span class="dot"></span>{{ $row['status_db'] }}</span></td>
                        <td><span class="badge badge-green"><span class="dot"></span>{{ $row['status_tripay'] }}</span></td>
                        <td class="muted mono-sm">{{ \Carbon\Carbon::parse($row['expired_at'])->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                <div>Tidak ada transaksi mismatch — semua status sinkron ✓</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TAB: Semua --}}
    <div id="tab-all" class="tab-panel">
        <div class="ps-filter">
            <input class="filter-input" type="text" placeholder="Cari merchant ref / tripay ref..." oninput="filterTable('table-all', this.value)">
        </div>
        <div class="ps-table-wrap">
            <table class="ps-table" id="table-all">
                <thead>
                    <tr>
                        <th>Merchant Ref</th>
                        <th>Tripay Ref</th>
                        <th>Participant</th>
                        <th>Total</th>
                        <th>Status DB</th>
                        <th>Status Tripay</th>
                        <th>Match?</th>
                        <th>Expired At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($all as $row)
                    <tr @if($row['mismatch'] ?? false) class="row-mismatch" @endif>
                        <td><span class="mono-sm">{{ $row['merchant_ref'] }}</span></td>
                        <td><span class="mono-sm">{{ $row['tripay_reference'] }}</span></td>
                        <td class="muted">{{ $row['participant_name'] ?? $row['participant_id'] }}</td>
                        <td><span class="mono-sm">Rp {{ number_format($row['total_amount'], 0, ',', '.') }}</span></td>
                        <td>
                            @php
                                $dbBadge = match($row['status_db']) {
                                    'PAID'    => 'badge-green',
                                    'UNPAID'  => 'badge-yellow',
                                    'EXPIRED' => 'badge-gray',
                                    'FAILED'  => 'badge-red',
                                    default   => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $dbBadge }}"><span class="dot"></span>{{ $row['status_db'] }}</span>
                        </td>
                        <td>
                            @if(isset($row['error']))
                                <span class="badge badge-red">ERROR</span>
                            @else
                                @php
                                    $tripayBadge = match($row['status_tripay']) {
                                        'PAID'    => 'badge-green',
                                        'UNPAID'  => 'badge-yellow',
                                        'EXPIRED' => 'badge-gray',
                                        'FAILED'  => 'badge-red',
                                        default   => 'badge-gray',
                                    };
                                @endphp
                                <span class="badge {{ $tripayBadge }}"><span class="dot"></span>{{ $row['status_tripay'] }}</span>
                            @endif
                        </td>
                        <td>
                            @if(isset($row['error']))
                                <span class="badge badge-gray">—</span>
                            @elseif($row['mismatch'])
                                <span class="badge badge-yellow">✗ Mismatch</span>
                            @else
                                <span class="badge badge-green">✓ Sync</span>
                            @endif
                        </td>
                        <td class="muted mono-sm">{{ \Carbon\Carbon::parse($row['expired_at'])->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25" /></svg>
                                <div>Tidak ada transaksi UNPAID yang ditemukan</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TAB: Errors --}}
    @if(count($errors) > 0)
    <div id="tab-errors" class="tab-panel">
        <div class="ps-table-wrap">
            <table class="ps-table" id="table-errors">
                <thead>
                    <tr>
                        <th>Merchant Ref</th>
                        <th>Tripay Ref</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($errors as $row)
                    <tr>
                        <td><span class="mono-sm">{{ $row['merchant_ref'] }}</span></td>
                        <td><span class="mono-sm">{{ $row['tripay_reference'] }}</span></td>
                        <td class="muted">{{ $row['error'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="ps-footer">
        ↳ dicek pada: {{ now()->format('d M Y, H:i:s') }} &nbsp;|&nbsp;
        {{ $summary['total_checked'] }} transaksi UNPAID dikirim ke Tripay API
    </div>
</div>

@push('scripts')
<script>
    function switchTab(name, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        btn.classList.add('active');
    }

    function filterTable(tableId, query) {
        const q = query.toLowerCase();
        const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }
</script>
@endpush
@endsection
