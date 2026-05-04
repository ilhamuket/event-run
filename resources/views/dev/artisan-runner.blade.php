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
        .worker-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 6px; }
        .worker-btn { font-family: 'Courier New', monospace; font-size: 12px; padding: 10px 12px;
                      border-radius: 6px; cursor: pointer; text-align: center; border: 1px solid; transition: all 0.15s; }
        .btn-status { background: #1a1a1a; border-color: #2a2a2a; color: #aaa; }
        .btn-status:hover { border-color: #c8f542; color: #c8f542; }
        .btn-start  { background: #1a2a10; border-color: #2a4a10; color: #c8f542; }
        .btn-start:hover  { background: #223a15; }
        .btn-stop   { background: #2a1010; border-color: #4a1010; color: #f54242; }
        .btn-stop:hover   { background: #3a1515; }
        .worker-hint { color: #444; font-size: 11px; margin-bottom: 1.5rem; }

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

        /* Tool box (shared style untuk backfill & normalize) */
        .tool-box { background: #111; border: 1px solid #2a2a2a; border-radius: 8px;
                    padding: 1rem; margin-bottom: 1.5rem; }
        .tool-box .section-label { margin-bottom: 12px; }
        .tool-box .section-label.orange { color: #f5a623; }
        .tool-box .section-label.green  { color: #42f5a1; }
        .tool-box .section-label.blue   { color: #42b4f5; }
        .tool-row { display: grid; gap: 8px; margin-bottom: 8px; }
        .tool-row-2 { grid-template-columns: 1fr 80px; }
        .tool-row-3 { grid-template-columns: 1fr 1fr 80px; }
        .tool-select, .tool-input {
            background: #1a1a1a; border: 1px solid #333; color: #e0e0e0;
            font-family: 'Courier New', monospace; font-size: 13px; padding: 9px 12px;
            border-radius: 6px; width: 100%;
        }
        .tool-select:focus, .tool-input:focus { outline: none; }
        .tool-select.orange:focus { border-color: #f5a623; }
        .tool-select.green:focus  { border-color: #42f5a1; }
        .tool-select.blue:focus   { border-color: #42b4f5; }
        .tool-hint { color: #555; font-size: 11px; margin-bottom: 8px; }
        .tool-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px; }
        .tool-btns.three { grid-template-columns: 1fr 1fr 1fr; }

        /* Backfill START buttons (orange) */
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

        /* Backfill FINISH buttons (green) */
        .btn-finish-dry {
            font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;
            border-radius: 6px; cursor: pointer; text-align: center;
            background: #101a15; border: 1px solid #104a30; color: #42f5a1;
            transition: all 0.15s;
        }
        .btn-finish-dry:hover { background: #15251e; }
        .btn-finish-exec {
            font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;
            border-radius: 6px; cursor: pointer; text-align: center;
            background: #102015; border: 1px solid #206a40; color: #42f5a1;
            font-weight: bold; transition: all 0.15s;
        }
        .btn-finish-exec:hover { background: #153025; }

        /* Normalize buttons (blue) */
        .btn-norm-dry {
            font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;
            border-radius: 6px; cursor: pointer; text-align: center;
            background: #10181a; border: 1px solid #104a5a; color: #42b4f5;
            transition: all 0.15s;
        }
        .btn-norm-dry:hover { background: #152225; }
        .btn-norm-exec {
            font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;
            border-radius: 6px; cursor: pointer; text-align: center;
            background: #101a2a; border: 1px solid #103a6a; color: #42b4f5;
            font-weight: bold; transition: all 0.15s;
        }
        .btn-norm-exec:hover { background: #152233; }
        .btn-norm-force {
            font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;
            border-radius: 6px; cursor: pointer; text-align: center;
            background: #1a1020; border: 1px solid #4a1070; color: #c084fc;
            transition: all 0.15s;
        }
        .btn-norm-force:hover { background: #221530; }

        .btn-dryrun:disabled, .btn-execute:disabled,
        .btn-finish-dry:disabled, .btn-finish-exec:disabled,
        .btn-norm-dry:disabled, .btn-norm-exec:disabled, .btn-norm-force:disabled
            { opacity: 0.4; cursor: not-allowed; }

        /* Output */
        .output { background: #111; border: 1px solid #2a2a2a; border-radius: 6px; padding: 1rem;
                  font-size: 13px; line-height: 1.8; color: #888; min-height: 80px; white-space: pre-wrap; }
        .output.success { border-color: #2a4a10; color: #c8f542; }
        .output.error   { border-color: #4a1010; color: #f54242; }
        .output.warn    { border-color: #4a3a10; color: #f5a623; }
        .output.info    { border-color: #103a6a; color: #42b4f5; }
        .output.teal    { border-color: #104a30; color: #42f5a1; }
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

    {{-- ── WORKER ──────────────────────────────────────────────────── --}}
    <div class="section-label">queue worker</div>
    <div class="worker-grid">
        <button class="worker-btn btn-status" onclick="checkStatus()">● cek status</button>
        <button class="worker-btn btn-start"  onclick="startWorker()">▶ start workers</button>
        <button class="worker-btn btn-stop"   onclick="stopWorker()">■ stop all</button>
    </div>
    <div class="worker-hint">
        start = 3× rfid worker + 2× positions worker (total 5 process, paralel)
    </div>

    {{-- ── BACKFILL START SCANS ────────────────────────────────────── --}}
    <div class="tool-box">
        <div class="section-label orange">⚠ backfill start scans (peserta tidak ter-detect)</div>
        <div class="tool-row tool-row-2">
            <select class="tool-select orange" id="backfillEvent">
                <option value="">-- Pilih Event --</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                @endforeach
            </select>
            <input class="tool-input" type="number" id="backfillSpread"
                   value="60" min="1" max="600" title="Spread waktu (detik)">
        </div>
        <div class="tool-hint">spread = sebaran detik antar peserta yang di-backfill (default: 60)</div>
        <div class="tool-btns">
            <button class="btn-dryrun" id="btnDryRun" onclick="runBackfill(true)">
                🔍 dry run (preview)
            </button>
            <button class="btn-execute" id="btnExecute" onclick="runBackfill(false)">
                ✓ eksekusi backfill
            </button>
        </div>
    </div>

    {{-- ── BACKFILL FINISH SCANS ───────────────────────────────────── --}}
    <div class="tool-box">
        <div class="section-label green">🏁 backfill finish scans (peserta belum ter-detect di finish)</div>
        <div class="tool-row tool-row-2">
            <select class="tool-select green" id="backfillFinishEvent">
                <option value="">-- Pilih Event --</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                @endforeach
            </select>
            <input class="tool-input" type="number" id="backfillFinishCategory"
                   placeholder="cat ID" min="1" title="event_category_id (opsional)">
        </div>
        <div class="tool-hint">
            category ID opsional — kosongkan untuk semua kategori dalam event.
            finish time synthetic = rata-rata 25% peserta paling lambat di kategori.
        </div>
        <div class="tool-btns">
            <button class="btn-finish-dry" id="btnFinishDry" onclick="runBackfillFinish(true)">
                🔍 dry run (preview)
            </button>
            <button class="btn-finish-exec" id="btnFinishExec" onclick="runBackfillFinish(false)">
                ✓ eksekusi backfill finish
            </button>
        </div>
    </div>

    {{-- ── NORMALIZE FINISH TIMES ──────────────────────────────────── --}}
    <div class="tool-box">
        <div class="section-label blue">⏱ normalize finish times (chip & gun time kosong)</div>
        <div class="tool-row tool-row-2">
            <select class="tool-select blue" id="normEvent">
                <option value="">-- Pilih Event --</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                @endforeach
            </select>
            <input class="tool-input" type="number" id="normCategory"
                   placeholder="cat ID" min="1" title="event_category_id (opsional)">
        </div>
        <div class="tool-hint">
            category ID opsional — kosongkan untuk semua kategori dalam event
        </div>
        <div class="tool-btns three">
            <button class="btn-norm-dry"  id="btnNormDry"   onclick="runNormalize(true, false)">
                🔍 dry run
            </button>
            <button class="btn-norm-exec" id="btnNormExec"  onclick="runNormalize(false, false)">
                ✓ eksekusi
            </button>
            <button class="btn-norm-force" id="btnNormForce" onclick="runNormalize(false, true)">
                ⚡ force all
            </button>
        </div>
        <div class="tool-hint" style="margin-top:8px; margin-bottom:0;">
            <b>eksekusi</b> = hanya yang kosong &nbsp;|&nbsp; <b>force all</b> = timpa semua (recalculate ulang)
        </div>
    </div>

    {{-- ── NORMALIZE POSITIONS ────────────────────────────────────── --}}
    <div class="tool-box">
        <div class="section-label" style="color:#f59342;">🏅 normalize positions (category & general position)</div>
        <div class="tool-row tool-row-2">
            <select class="tool-select" id="normPosEvent" style="border-color:#333;">
                <option value="">-- Pilih Event --</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                @endforeach
            </select>
            <input class="tool-input" type="number" id="normPosCategory"
                placeholder="cat ID" min="1" title="event_category_id (opsional)">
        </div>
        <div class="tool-hint">
            Menghitung ulang category_position &amp; general_position dari chip time
            (validated finish − validated start). NULL chip time → posisi paling belakang.
            Aman dijalankan berkali-kali.
        </div>
        <div class="tool-btns">
            <button class="btn-dryrun" id="btnNormPosDry" onclick="runNormalizePositions(true)">
                🔍 dry run (preview)
            </button>
            <button class="btn-execute" id="btnNormPosExec" onclick="runNormalizePositions(false)">
                ✓ eksekusi
            </button>
        </div>
    </div>

    {{-- ── ARTISAN COMMANDS ────────────────────────────────────────── --}}
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

    // ── Artisan generic ────────────────────────────────────────────────────
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

    // ── Backfill start ─────────────────────────────────────────────────────
    async function runBackfill(isDryRun) {
        const p = pin(); if (!p) return;

        const eventId = document.getElementById('backfillEvent').value;
        const spread  = parseInt(document.getElementById('backfillSpread').value) || 60;

        if (!eventId) { flash('Pilih event dulu.', 'error'); return; }

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

        setBackfillBtns(true);
        flash(
            isDryRun
                ? '⏳ Dry run backfill... (preview saja, tidak ada perubahan DB)'
                : '⏳ Eksekusi backfill... mohon tunggu',
            'warn'
        );

        try {
            const data = await post('{{ route("artisan.runner.backfill-start") }}', {
                pin: p, event_id: parseInt(eventId), spread, dry_run: isDryRun,
            });
            flash(data.output, data.success ? 'success' : 'error');
        } catch (e) {
            flash('Request gagal: ' + e.message, 'error');
        } finally {
            setBackfillBtns(false);
        }
    }

    function setBackfillBtns(disabled) {
        ['btnDryRun', 'btnExecute'].forEach(id => document.getElementById(id).disabled = disabled);
    }

    // ── Backfill finish ────────────────────────────────────────────────────
    async function runBackfillFinish(isDryRun) {
        const p = pin(); if (!p) return;

        const eventId    = document.getElementById('backfillFinishEvent').value;
        const categoryId = document.getElementById('backfillFinishCategory').value;

        if (!eventId) { flash('Pilih event dulu.', 'error'); return; }

        if (!isDryRun) {
            const ok = confirm(
                '🏁 EKSEKUSI BACKFILL FINISH\n\n' +
                'Ini akan membuat raw log + validated time finish untuk semua\n' +
                'peserta yang belum ter-detect di checkpoint FINISH.\n\n' +
                'Finish time synthetic = rata-rata 25% peserta paling lambat\n' +
                'di kategori yang sama (+1 detik per peserta).\n\n' +
                'Pastikan sudah dry run dulu dan hasilnya sesuai.\n\n' +
                'Lanjutkan?'
            );
            if (!ok) return;
        }

        setBackfillFinishBtns(true);
        flash(
            isDryRun
                ? '⏳ Dry run backfill finish... (preview saja, tidak ada perubahan DB)'
                : '⏳ Eksekusi backfill finish... mohon tunggu',
            'teal'
        );

        try {
            const body = {
                pin:      p,
                event_id: parseInt(eventId),
                dry_run:  isDryRun,
            };
            if (categoryId) body.category_id = parseInt(categoryId);

            const data = await post('{{ route("artisan.runner.backfill-finish") }}', body);
            flash(data.output, data.success ? 'teal' : 'error');
        } catch (e) {
            flash('Request gagal: ' + e.message, 'error');
        } finally {
            setBackfillFinishBtns(false);
        }
    }

    function setBackfillFinishBtns(disabled) {
        ['btnFinishDry', 'btnFinishExec'].forEach(id => document.getElementById(id).disabled = disabled);
    }

    // ── Normalize finish ───────────────────────────────────────────────────
    async function runNormalize(isDryRun, isForce) {
        const p = pin(); if (!p) return;

        const eventId    = document.getElementById('normEvent').value;
        const categoryId = document.getElementById('normCategory').value;

        if (!eventId) { flash('Pilih event dulu.', 'error'); return; }

        if (isForce) {
            const ok = confirm(
                '⚡ FORCE NORMALIZE\n\n' +
                'Akan menghitung ulang chip_time & gun_time untuk SEMUA finisher\n' +
                '(termasuk yang sudah ada nilainya).\n\n' +
                'Lanjutkan?'
            );
            if (!ok) return;
        } else if (!isDryRun) {
            const ok = confirm(
                '✓ EKSEKUSI NORMALIZE FINISH\n\n' +
                'Akan mengisi elapsed_time & gun_elapsed_time yang masih kosong\n' +
                'berdasarkan data start/finish dari rfid_validated_times.\n\n' +
                'Lanjutkan?'
            );
            if (!ok) return;
        }

        setNormBtns(true);
        flash(
            isDryRun  ? '⏳ Dry run normalize finish... (preview, tidak ada perubahan DB)'
            : isForce ? '⏳ Force normalize semua finisher...'
                      : '⏳ Normalize finish times...',
            'info'
        );

        try {
            const body = {
                pin:      p,
                event_id: parseInt(eventId),
                dry_run:  isDryRun,
                force:    isForce,
            };
            if (categoryId) body.category_id = parseInt(categoryId);

            const data = await post('{{ route("artisan.runner.normalize-finish") }}', body);
            flash(data.output, data.success ? 'success' : 'error');
        } catch (e) {
            flash('Request gagal: ' + e.message, 'error');
        } finally {
            setNormBtns(false);
        }
    }

    function setNormBtns(disabled) {
        ['btnNormDry', 'btnNormExec', 'btnNormForce'].forEach(id =>
            document.getElementById(id).disabled = disabled
        );
    }

    // ── Worker controls ────────────────────────────────────────────────────
    async function checkStatus() {
        const p = pin(); if (!p) return;
        flash('⏳ Mengecek status...', '');
        const data = await post('{{ route("artisan.runner.status") }}', { pin: p });
        flash(data.output, data.success ? 'success' : 'error');
    }

    async function startWorker() {
        const p = pin(); if (!p) return;
        flash('⏳ Starting workers (3× rfid + 2× positions)...', 'warn');
        const data = await post('{{ route("artisan.runner.start-worker") }}', { pin: p });
        flash(data.output, data.success ? 'success' : 'error');
    }

    async function stopWorker() {
        const p = pin(); if (!p) return;
        flash('⏳ Stopping all workers...', '');
        const data = await post('{{ route("artisan.runner.stop-worker") }}', { pin: p });
        flash(data.output, data.success ? 'success' : 'error');
    }

    function flash(text, type) {
        const el = document.getElementById('output');
        el.className = 'output' + (type ? ' ' + type : '');
        el.textContent = text;
    }

        // ── Normalize positions ────────────────────────────────────────────────
    async function runNormalizePositions(isDryRun) {
        const p = pin(); if (!p) return;

        const eventId    = document.getElementById('normPosEvent').value;
        const categoryId = document.getElementById('normPosCategory').value;

        if (!eventId) { flash('Pilih event dulu.', 'error'); return; }

        if (!isDryRun) {
            const ok = confirm(
                '🏅 EKSEKUSI NORMALIZE POSITIONS\n\n' +
                'Akan menghitung ulang category_position & general_position\n' +
                'untuk semua peserta yang punya validated finish time,\n' +
                'berdasarkan chip time (finish rfid − start rfid).\n\n' +
                'Peserta tanpa scan start → posisi paling belakang.\n\n' +
                'Pastikan sudah dry run dulu dan hasilnya sesuai.\n\n' +
                'Lanjutkan?'
            );
            if (!ok) return;
        }

        setNormPosBtns(true);
        flash(
            isDryRun
                ? '⏳ Dry run normalize positions... (preview, tidak ada perubahan DB)'
                : '⏳ Eksekusi normalize positions... mohon tunggu',
            'warn'
        );

        try {
            const body = {
                pin:      p,
                event_id: parseInt(eventId),
                dry_run:  isDryRun,
            };
            if (categoryId) body.category_id = parseInt(categoryId);

            const data = await post('{{ route("artisan.runner.normalize-positions") }}', body);
            flash(data.output, data.success ? 'success' : 'error');
        } catch (e) {
            flash('Request gagal: ' + e.message, 'error');
        } finally {
            setNormPosBtns(false);
        }
    }

    function setNormPosBtns(disabled) {
        ['btnNormPosDry', 'btnNormPosExec'].forEach(id =>
            document.getElementById(id).disabled = disabled
        );
    }
</script>
</body>
</html>
