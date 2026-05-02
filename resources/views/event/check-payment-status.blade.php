@extends('layouts.app')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap');

    :root {
        --bg: #0d0f14;
        --surface: #161920;
        --border: #252830;
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

    .ps-page { background: var(--bg); color: var(--text); font-family: var(--sans); min-height: 100vh; }
    .ps-wrap { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

    .ps-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 1rem; margin-bottom: 2rem; padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
    }
    .ps-title { font-family: var(--mono); font-size: 1.25rem; font-weight: 600; letter-spacing: -0.02em; color: var(--text); margin: 0 0 .25rem; }
    .ps-subtitle { font-size: .8rem; color: var(--muted); font-family: var(--mono); margin: 0; }

    .btn-start {
        display: inline-flex; align-items: center; gap: .5rem;
        background: var(--accent); color: #fff; border: none; border-radius: 6px;
        padding: .55rem 1.1rem; font-size: .82rem; font-family: var(--mono);
        font-weight: 500; cursor: pointer; transition: opacity .15s; white-space: nowrap;
    }
    .btn-start:hover { opacity: .85; }
    .btn-start:disabled { opacity: .4; cursor: not-allowed; }
    .btn-start svg { width: 15px; height: 15px; }

    .ps-summary {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .75rem; margin-bottom: 2rem;
    }
    .summary-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 1rem 1.2rem; }
    .summary-card .s-label { font-size: .7rem; font-family: var(--mono); color: var(--muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: .4rem; }
    .summary-card .s-value { font-size: 1.6rem; font-weight: 600; font-family: var(--mono); line-height: 1; }
    .summary-card.s-total   .s-value { color: var(--text); }
    .summary-card.s-checked .s-value { color: var(--accent); }
    .summary-card.s-mismatch .s-value { color: var(--yellow); }
    .summary-card.s-error   .s-value { color: var(--red); }
    .summary-card.s-mismatch { border-color: rgba(245,158,11,.25); background: var(--yellow-dim); }
    .summary-card.s-error   { border-color: rgba(239,68,68,.2);   background: var(--red-dim); }

    .ps-progress-wrap { margin-bottom: 1.5rem; }
    .ps-progress-label { display: flex; justify-content: space-between; font-size: .72rem; font-family: var(--mono); color: var(--muted); margin-bottom: .4rem; }
    .ps-progress-bar { height: 4px; background: var(--border); border-radius: 99px; overflow: hidden; }
    .ps-progress-fill { height: 100%; background: var(--accent); border-radius: 99px; width: 0%; transition: width .3s ease; }

    .ps-table-wrap { overflow-x: auto; border: 1px solid var(--border); border-radius: 8px; }
    .ps-table { width: 100%; border-collapse: collapse; font-size: .78rem; font-family: var(--mono); }
    .ps-table thead th {
        background: var(--surface); padding: .7rem 1rem; text-align: left;
        color: var(--muted); font-weight: 500; font-size: .68rem;
        text-transform: uppercase; letter-spacing: .07em;
        border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .ps-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
    .ps-table tbody tr:last-child { border-bottom: none; }
    .ps-table tbody tr:hover { background: rgba(255,255,255,.02); }
    .ps-table td { padding: .75rem 1rem; vertical-align: middle; color: var(--text); }
    .ps-table td.muted { color: var(--muted); }
    .mono-sm { font-family: var(--mono); font-size: .75rem; }

    .badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .2rem .55rem; border-radius: 4px; font-size: .68rem;
        font-weight: 600; font-family: var(--mono); letter-spacing: .03em; white-space: nowrap;
    }
    .badge-yellow { background: var(--yellow-dim); color: var(--yellow); border: 1px solid rgba(245,158,11,.25); }
    .badge-green  { background: var(--green-dim);  color: var(--green);  border: 1px solid rgba(34,197,94,.2); }
    .badge-red    { background: var(--red-dim);    color: var(--red);    border: 1px solid rgba(239,68,68,.2); }
    .badge-gray   { background: rgba(107,113,128,.12); color: var(--muted); border: 1px solid rgba(107,113,128,.2); }
    .badge .dot   { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    .row-mismatch td:first-child { border-left: 2px solid var(--yellow); }
    .row-checking { opacity: .5; }

    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    .badge-loading { background: rgba(107,113,128,.12); color: var(--muted); border: 1px solid rgba(107,113,128,.2); animation: pulse 1s infinite; }

    .empty-state { text-align: center; padding: 3rem 1rem; color: var(--muted); font-family: var(--mono); font-size: .82rem; }
    .empty-state svg { width: 36px; height: 36px; opacity: .3; margin-bottom: .75rem; display: block; margin-inline: auto; }

    .ps-footer { margin-top: 1.25rem; font-size: .72rem; font-family: var(--mono); color: var(--muted); }
</style>

<div class="ps-page">
<div class="ps-wrap">

    {{-- Header --}}
    <div class="ps-header">
        <div>
            <h1 class="ps-title">// check-payment-status</h1>
            <p class="ps-subtitle">Cek status ke Tripay satu per satu secara realtime</p>
        </div>
        <button class="btn-start" id="btn-start" onclick="startChecking()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
            </svg>
            Mulai Cek
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="ps-summary">
        <div class="summary-card s-total">
            <div class="s-label">Total Transaksi</div>
            <div class="s-value" id="s-total">{{ count($transactions) }}</div>
        </div>
        <div class="summary-card s-checked">
            <div class="s-label">Sudah Dicek</div>
            <div class="s-value" id="s-checked">0</div>
        </div>
        <div class="summary-card s-mismatch">
            <div class="s-label">Mismatch (Sudah Bayar)</div>
            <div class="s-value" id="s-mismatch">0</div>
        </div>
        <div class="summary-card s-error">
            <div class="s-label">Error API</div>
            <div class="s-value" id="s-error">0</div>
        </div>
    </div>

    {{-- Progress --}}
    <div class="ps-progress-wrap" id="progress-wrap" style="display:none">
        <div class="ps-progress-label">
            <span id="progress-text">Memulai...</span>
            <span id="progress-pct">0%</span>
        </div>
        <div class="ps-progress-bar">
            <div class="ps-progress-fill" id="progress-fill"></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="ps-table-wrap">
        <table class="ps-table">
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
                @forelse($transactions as $t)
                <tr id="row-{{ $t['id'] }}">
                    <td><span class="mono-sm">{{ $t['merchant_ref'] }}</span></td>
                    <td><span class="mono-sm">{{ $t['tripay_reference'] }}</span></td>
                    <td class="muted">{{ $t['participant_name'] ?? '-' }}</td>
                    <td class="muted">{{ $t['event_name'] ?? '-' }}</td>
                    <td><span class="mono-sm">Rp {{ number_format($t['total_amount'], 0, ',', '.') }}</span></td>
                    <td><span class="badge badge-yellow"><span class="dot"></span>{{ $t['status_db'] }}</span></td>
                    <td id="tripay-status-{{ $t['id'] }}">
                        <span class="badge badge-gray">— belum dicek</span>
                    </td>
                    <td class="muted mono-sm">{{ $t['expired_at'] ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Tidak ada transaksi UNPAID
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ps-footer" id="ps-footer"></div>

</div>
</div>

<script>
    const transactions = @json($transactions);
    const baseUrl = '{{ url('/transactions/check-single-status') }}';

    let checked = 0, mismatched = 0, errors = 0;

    async function startChecking() {
        if (!transactions.length) return;

        const btn = document.getElementById('btn-start');
        btn.disabled = true;
        document.getElementById('progress-wrap').style.display = 'block';

        const total = transactions.length;

        for (const t of transactions) {
            const row = document.getElementById(`row-${t.id}`);
            const statusCell = document.getElementById(`tripay-status-${t.id}`);

            row.classList.add('row-checking');
            statusCell.innerHTML = `<span class="badge badge-loading">⟳ mengecek...</span>`;

            try {
                const res = await fetch(`${baseUrl}/${t.tripay_reference}`);
                const data = await res.json();

                if (data.success) {
                    const badgeMap = {
                        'PAID':    'badge-green',
                        'UNPAID':  'badge-yellow',
                        'EXPIRED': 'badge-gray',
                        'FAILED':  'badge-red',
                    };
                    const cls = badgeMap[data.status_tripay] ?? 'badge-gray';
                    statusCell.innerHTML = `<span class="badge ${cls}"><span class="dot"></span>${data.status_tripay}</span>`;

                    if (data.mismatch) {
                        row.classList.add('row-mismatch');
                        mismatched++;
                        document.getElementById('s-mismatch').textContent = mismatched;
                    }
                } else {
                    statusCell.innerHTML = `<span class="badge badge-red" title="${data.error ?? ''}">✗ Error</span>`;
                    errors++;
                    document.getElementById('s-error').textContent = errors;
                }
            } catch (e) {
                statusCell.innerHTML = `<span class="badge badge-red" title="${e.message}">✗ Network Error</span>`;
                errors++;
                document.getElementById('s-error').textContent = errors;
            }

            row.classList.remove('row-checking');
            checked++;
            document.getElementById('s-checked').textContent = checked;

            const pct = Math.round((checked / total) * 100);
            document.getElementById('progress-fill').style.width = pct + '%';
            document.getElementById('progress-pct').textContent = pct + '%';
            document.getElementById('progress-text').textContent = `Mengecek ${checked} / ${total}...`;
        }

        document.getElementById('progress-text').textContent = `✓ Selesai — ${checked} transaksi dicek`;
        btn.disabled = false;
        document.getElementById('ps-footer').textContent =
            `↳ selesai pada: ${new Date().toLocaleString('id-ID')} | ${checked} dicek, ${mismatched} mismatch, ${errors} error`;
    }
</script>

@endsection
