{{-- resources/views/event/check-payment-status.blade.php --}}
@extends('layouts.app')
@section('title', 'Cek Status Pembayaran Tripay')

@push('styles')
{{-- CSS sama seperti sebelumnya, tambah style baru di bawah --}}
<style>
    /* ... semua CSS sebelumnya tetap sama ... */

    /* Progress bar */
    .ps-progress-wrap {
        margin-bottom: 1.5rem;
    }
    .ps-progress-label {
        display: flex;
        justify-content: space-between;
        font-size: .72rem;
        font-family: var(--mono);
        color: var(--muted);
        margin-bottom: .4rem;
    }
    .ps-progress-bar {
        height: 4px;
        background: var(--border);
        border-radius: 99px;
        overflow: hidden;
    }
    .ps-progress-fill {
        height: 100%;
        background: var(--accent);
        border-radius: 99px;
        width: 0%;
        transition: width .3s ease;
    }

    /* Spinning loader badge */
    .badge-loading {
        background: rgba(107,113,128,.12);
        color: var(--muted);
        border: 1px solid rgba(107,113,128,.2);
        animation: pulse 1s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

    /* Row state */
    .row-checking { opacity: .6; }
</style>
@endpush

@section('content')
<div class="ps-wrap">

    {{-- Header --}}
    <div class="ps-header">
        <div>
            <h1 class="ps-title">// check-payment-status</h1>
            <p class="ps-subtitle">Cek status ke Tripay satu per satu secara realtime</p>
        </div>
        <button class="btn-refresh" id="btn-start" onclick="startChecking()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
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
        <div class="summary-card s-synced">
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
        <table class="ps-table" id="main-table">
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
                <tr id="row-{{ $t['id'] }}" data-tripay-ref="{{ $t['tripay_reference'] }}">
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
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <div>Tidak ada transaksi UNPAID</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ps-footer" id="ps-footer"></div>
</div>
@endsection

@push('scripts')
<script>
const transactions = @json($transactions);
const checkUrl = (ref) => `{{ url('/transactions/check-single-status') }}/${ref}`;

let checked = 0, mismatched = 0, errors = 0;

async function startChecking() {
    if (!transactions.length) return;

    document.getElementById('btn-start').disabled = true;
    document.getElementById('btn-start').style.opacity = '0.5';
    document.getElementById('progress-wrap').style.display = 'block';

    const total = transactions.length;

    for (const t of transactions) {
        const row = document.getElementById(`row-${t.id}`);
        const statusCell = document.getElementById(`tripay-status-${t.id}`);

        // Set row as "checking"
        row.classList.add('row-checking');
        statusCell.innerHTML = `<span class="badge badge-loading">⟳ mengecek...</span>`;

        try {
            const res = await fetch(checkUrl(t.tripay_reference));
            const data = await res.json();

            if (data.success) {
                const badgeClass = {
                    'PAID': 'badge-green',
                    'UNPAID': 'badge-yellow',
                    'EXPIRED': 'badge-gray',
                    'FAILED': 'badge-red',
                }[data.status_tripay] ?? 'badge-gray';

                statusCell.innerHTML = `<span class="badge ${badgeClass}"><span class="dot"></span>${data.status_tripay}</span>`;

                if (data.mismatch) {
                    row.classList.add('row-mismatch');
                    mismatched++;
                    document.getElementById('s-mismatch').textContent = mismatched;
                }
            } else {
                statusCell.innerHTML = `<span class="badge badge-red" title="${data.error}">✗ Error</span>`;
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

        // Update progress bar
        const pct = Math.round((checked / total) * 100);
        document.getElementById('progress-fill').style.width = pct + '%';
        document.getElementById('progress-pct').textContent = pct + '%';
        document.getElementById('progress-text').textContent = `Mengecek ${checked} / ${total}...`;
    }

    // Done
    document.getElementById('progress-text').textContent = `✓ Selesai — ${checked} transaksi dicek`;
    document.getElementById('btn-start').disabled = false;
    document.getElementById('btn-start').style.opacity = '1';
    document.getElementById('ps-footer').textContent =
        `↳ selesai pada: ${new Date().toLocaleString('id-ID')} | ${checked} dicek, ${mismatched} mismatch, ${errors} error`;
}
</script>
@endpush
