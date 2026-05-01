<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Runner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Courier New', monospace; background: #0d0d0d; color: #e0e0e0; min-height: 100vh; }
        .shell { max-width: 640px; margin: 0 auto; padding: 2rem 1rem; }
        .header { border-bottom: 1px solid #2a2a2a; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .header small { font-size: 13px; color: #555; letter-spacing: 2px; text-transform: uppercase; }
        .header p { font-size: 20px; color: #c8f542; font-weight: bold; margin-top: 4px; }
        .section-label { color: #555; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; }
        .pin-input { width: 100%; background: #1a1a1a; border: 1px solid #333; color: #e0e0e0;
                     font-family: 'Courier New', monospace; font-size: 15px; padding: 10px 14px;
                     border-radius: 6px; letter-spacing: 6px; margin-bottom: 1.5rem; }
        .pin-input:focus { outline: none; border-color: #c8f542; }

        /* Worker buttons */
        .worker-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 1.5rem; }
        .worker-btn { font-family: 'Courier New', monospace; font-size: 12px; padding: 10px 12px;
                      border-radius: 6px; cursor: pointer; text-align: center; border: 1px solid; transition: all 0.15s; }
        .btn-status { background: #1a1a1a; border-color: #2a2a2a; color: #aaa; }
        .btn-status:hover { border-color: #c8f542; color: #c8f542; }
        .btn-start  { background: #1a2a10; border-color: #2a4a10; color: #c8f542; }
        .btn-start:hover  { background: #223a15; }
        .btn-stop   { background: #2a1010; border-color: #4a1010; color: #f54242; }
        .btn-stop:hover   { background: #3a1515; }

        /* Artisan command grid */
        .cmd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 1rem; }
        .cmd-btn { background: #1a1a1a; border: 1px solid #2a2a2a; color: #aaa;
                   font-family: 'Courier New', monospace; font-size: 12px; padding: 10px 12px;
                   border-radius: 6px; cursor: pointer; text-align: left; transition: all 0.15s; }
        .cmd-btn:hover, .cmd-btn.selected { border-color: #c8f542; color: #c8f542; background: #1e2410; }
        .run-btn { width: 100%; background: #c8f542; color: #0d0d0d; font-family: 'Courier New', monospace;
                   font-size: 14px; font-weight: bold; padding: 12px; border: none; border-radius: 6px;
                   cursor: pointer; letter-spacing: 1px; margin-bottom: 1.5rem; }
        .run-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Backfill section */
        .backfill-box { background: #111; border: 1px solid #2a2a2a; border-radius: 8px;
                        padding: 1rem; margin-bottom: 1.5rem; }
        .backfill-box .section-label { margin-bottom: 12px; color: #f5a623; }
        .backfill-row { display: grid; grid-template-columns: 1fr 80px; gap: 8px; margin-bottom: 8px; }
        .backfill-select, .backfill-input {
            background: #1a1a1a; border: 1px solid #333; color: #e0e0e0;
            font-family: 'Courier New', monospace; font-size: 13px; padding: 9px 12px;
            border-radius: 6px; width: 100%;
        }
        .backfill-select:focus, .backfill-input:focus { outline: none; border-color: #f5a623; }
        .backfill-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px; }
        .btn-dryrun {
            font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;
            border-radius: 6px; cursor: pointer; text-align: center;
            background: #1a1a10; border: 1px solid #4a4a10; color: #f5a623;
            transition: all 0.15s;
        }
        .btn-dryrun:hover { background: #2a2a15; }
        .btn-execute {
            font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;
            border-radius: 6px; cursor: pointer; text-align: center;
            background: #1a2a10; border: 1px solid #3a6a10; color: #c8f542;
            font-weight: bold; transition: all 0.15s;
        }
        .btn-execute:hover { background: #223a15; }
        .btn-dryrun:disabled, .btn-execute:disabled { opacity: 0.4; cursor: not-allowed; }

        /* Output */
        .output { background: #111; border: 1px solid #2a2a2a; border-radius: 6px; padding: 1rem;
                  font-size: 13px; line-height: 1.8; color: #888; min-height: 80px; white-space: pre-wrap; }
        .output.success { border-color: #2a4a10; color: #c8f542; }
        .output.error   { border-color: #4a1010; color: #f54242; }
        .output.warn    { border-color: #4a3a10; color: #f5a623; }
    </style>
</head>
<body>
<div class="shell">
    <div class="header">
        <small>artisan runner</small>
        <p>$ php artisan ▋</p>
    </div>

    <div class="section-label">pin</div>
    <input class="pin-input" type="password" id="pin" placeholder="••••••••" maxlength="8" />

    <div class="section-label">queue worker</div>
    <div class="worker-grid" style="margin-bottom:1.5rem;">
        <button class="worker-btn btn-status" onclick="checkStatus()">● cek status</button>
        <button class="worker-btn btn-start"  onclick="startWorker()">▶ start worker</button>
        <button class="worker-btn btn-stop"   onclick="stopWorker()">■ stop worker</button>
    </div>

    {{-- ── BACKFILL START SCANS ─────────────────────────────────── --}}
    <div class="backfill-box">
        <div class="section-label">⚠ backfill start scans (peserta tidak ter-detect)</div>
        <div class="backfill-row">
            <select class="backfill-select" id="backfillEvent">
                <option value="">-- Pilih Event --</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                @endforeach
            </select>
            <input class="backfill-input" type="number" id="backfillSpread"
                   value="60" min="1" max="600" title="Spread waktu (detik)">
        </div>
        <div style="color:#555; font-size:11px; margin-bottom:8px;">
            spread = sebaran detik antar peserta yang di-backfill (default: 60)
        </div>
        <div class="backfill-btns">
            <button class="btn-dryrun" id="btnDryRun" onclick="runBackfill(true)">
                🔍 dry run (preview)
            </button>
            <button class="btn-execute" id="btnExecute" onclick="runBackfill(false)">
                ✓ eksekusi backfill
            </button>
        </div>
    </div>

    <div class="section-label">artisan commands</div>
    <div class="cmd-grid">
        @foreach($commands as $cmd)
            <button class="cmd-btn {{ $loop->first ? 'selected' : '' }}"
                    data-cmd="{{ $cmd }}"
                    onclick="selectCmd(this)">{{ $cmd }}</button>
        @endforeach
    </div>

    <button class="run-btn" id="runBtn" onclick="runCommand()">RUN COMMAND</button>

    <div class="section-label">output</div>
    <div class="output" id="output">Masukkan PIN lalu pilih aksi.</div>
</div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let selectedCmd = document.querySelector('.cmd-btn.selected')?.dataset.cmd || '';

    function selectCmd(el) {
        document.querySelectorAll('.cmd-btn').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        selectedCmd = el.dataset.cmd;
    }

    function pin() {
        const v = document.getElementById('pin').value;
        if (!v) { flash('Masukkan PIN dulu.', 'error'); return null; }
        return v;
    }

    async function post(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body),
        });
        return res.json();
    }

    async function runCommand() {
        const p = pin(); if (!p) return;
        if (!selectedCmd) { flash('Pilih command dulu.', 'error'); return; }

        const btn = document.getElementById('runBtn');
        btn.disabled = true;
        flash('⏳ Running: ' + selectedCmd + '...', '');

        try {
            const data = await post('{{ route("artisan.runner.run") }}', { pin: p, command: selectedCmd });
            flash(data.output, data.success ? 'success' : 'error');
        } catch (e) {
            flash('Request gagal: ' + e.message, 'error');
        } finally {
            btn.disabled = false;
        }
    }

    async function runBackfill(isDryRun) {
        const p = pin(); if (!p) return;

        const eventId = document.getElementById('backfillEvent').value;
        const spread  = parseInt(document.getElementById('backfillSpread').value) || 60;

        if (!eventId) { flash('Pilih event dulu.', 'error'); return; }

        // Konfirmasi eksekusi (bukan dry run)
        if (!isDryRun) {
            const ok = confirm(
                '⚠ EKSEKUSI BACKFILL\n\n' +
                'Ini akan membuat raw log + validated time untuk semua peserta\n' +
                'yang belum ter-detect di checkpoint START.\n\n' +
                'Pastikan sudah dry run dulu dan hasilnya sesuai.\n\n' +
                'Lanjutkan?'
            );
            if (!ok) return;
        }

        const dryBtn = document.getElementById('btnDryRun');
        const exeBtn = document.getElementById('btnExecute');
        dryBtn.disabled = true;
        exeBtn.disabled = true;

        flash(
            isDryRun
                ? '⏳ Dry run backfill... (preview saja, tidak ada perubahan DB)'
                : '⏳ Eksekusi backfill... mohon tunggu',
            'warn'
        );

        try {
            const data = await post('{{ route("artisan.runner.backfill-start") }}', {
                pin:      p,
                event_id: parseInt(eventId),
                spread:   spread,
                dry_run:  isDryRun,
            });
            flash(data.output, data.success ? 'success' : 'error');
        } catch (e) {
            flash('Request gagal: ' + e.message, 'error');
        } finally {
            dryBtn.disabled = false;
            exeBtn.disabled = false;
        }
    }

    async function checkStatus() {
        const p = pin(); if (!p) return;
        flash('⏳ Mengecek status...', '');
        const data = await post('{{ route("artisan.runner.status") }}', { pin: p });
        flash(data.output, data.success ? 'success' : 'error');
    }

    async function startWorker() {
        const p = pin(); if (!p) return;
        flash('⏳ Starting worker...', '');
        const data = await post('{{ route("artisan.runner.start-worker") }}', { pin: p });
        flash(data.output, data.success ? 'success' : 'error');
    }

    async function stopWorker() {
        const p = pin(); if (!p) return;
        flash('⏳ Stopping worker...', '');
        const data = await post('{{ route("artisan.runner.stop-worker") }}', { pin: p });
        flash(data.output, data.success ? 'success' : 'error');
    }

    function flash(text, type) {
        const el = document.getElementById('output');
        el.className = 'output' + (type ? ' ' + type : '');
        el.textContent = text;
    }
</script>
</body>
</html>
