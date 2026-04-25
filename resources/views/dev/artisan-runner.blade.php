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
        .pin-input { width: 100%; background: #1a1a1a; border: 1px solid #333; color: #e0e0e0;
                     font-family: 'Courier New', monospace; font-size: 15px; padding: 10px 14px;
                     border-radius: 6px; letter-spacing: 6px; margin-bottom: 1.5rem; }
        .pin-input:focus { outline: none; border-color: #c8f542; }
        .cmd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 1.5rem; }
        .cmd-btn { background: #1a1a1a; border: 1px solid #2a2a2a; color: #aaa;
                   font-family: 'Courier New', monospace; font-size: 12px; padding: 10px 12px;
                   border-radius: 6px; cursor: pointer; text-align: left; transition: all 0.15s; }
        .cmd-btn:hover, .cmd-btn.selected { border-color: #c8f542; color: #c8f542; background: #1e2410; }
        .run-btn { width: 100%; background: #c8f542; color: #0d0d0d; font-family: 'Courier New', monospace;
                   font-size: 14px; font-weight: bold; padding: 12px; border: none; border-radius: 6px;
                   cursor: pointer; letter-spacing: 1px; margin-bottom: 1.5rem; }
        .run-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .output { background: #111; border: 1px solid #2a2a2a; border-radius: 6px; padding: 1rem;
                  font-size: 13px; line-height: 1.7; color: #888; min-height: 80px; white-space: pre-wrap; }
        .output.success { border-color: #2a4a10; color: #c8f542; }
        .output.error   { border-color: #4a1010; color: #f54242; }
    </style>
</head>
<body>
<div class="shell">
    <div class="header">
        <small>artisan runner</small>
        <p>$ php artisan ▋</p>
    </div>

    <input class="pin-input" type="password" id="pin" placeholder="PIN ••••" maxlength="8" />

    <div class="cmd-grid">
        @foreach($commands as $cmd)
            <button class="cmd-btn {{ $loop->first ? 'selected' : '' }}"
                    data-cmd="{{ $cmd }}"
                    onclick="selectCmd(this)">{{ $cmd }}</button>
        @endforeach
    </div>

    <button class="run-btn" id="runBtn" onclick="runCommand()">RUN COMMAND</button>

    <div style="color:#555;font-size:12px;margin-bottom:6px;">output:</div>
    <div class="output" id="output">Pilih command dan masukkan PIN, lalu tekan RUN.</div>
</div>

<script>
    let selectedCmd = document.querySelector('.cmd-btn.selected')?.dataset.cmd || '';

    function selectCmd(el) {
        document.querySelectorAll('.cmd-btn').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        selectedCmd = el.dataset.cmd;
    }

    async function runCommand() {
        const pin    = document.getElementById('pin').value;
        const output = document.getElementById('output');
        const btn    = document.getElementById('runBtn');

        if (!pin)         { flash(output, 'Masukkan PIN dulu.', 'error'); return; }
        if (!selectedCmd) { flash(output, 'Pilih command dulu.', 'error'); return; }

        btn.disabled  = true;
        output.className = 'output';
        output.textContent = '⏳ Running: ' + selectedCmd + '...';

        try {
            const res  = await fetch('{{ route("artisan.runner.run") }}', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ pin, command: selectedCmd }),
            });
            const data = await res.json();
            flash(output, data.output, data.success ? 'success' : 'error');
        } catch (e) {
            flash(output, 'Request gagal: ' + e.message, 'error');
        } finally {
            btn.disabled = false;
        }
    }

    function flash(el, text, type) {
        el.className = 'output ' + type;
        el.textContent = text;
    }
</script>
</body>
</html>
