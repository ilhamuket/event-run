{{--
    event/_live-section-finish.blade.php
    @param array       $group            ['checkpoint' => object, 'participants' => Collection]
    @param bool        $hasGunTime       true kalau event ini pakai gun time
    @param object      $event
    @param string|null $selectedCategory slug kategori yang dipilih (dari filter)
--}}

@php
    $allParticipants = $group['participants'];

    $males   = $allParticipants->filter(fn ($item) => ($item['participant']->gender ?? '') === 'M')->values();
    $females = $allParticipants->filter(fn ($item) => ($item['participant']->gender ?? '') === 'F')->values();

    $rankBadge = fn (int $rank) => ($rank <= 3)
        ? 'bg-red-100 text-red-800'
        : 'bg-gray-100 text-gray-700';
@endphp



{{-- ══════════════════════════════════════════════════════ --}}
{{-- EDIT TIME MODAL                                       --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div id="edit-modal"
     class="fixed inset-0 z-50 items-center justify-center hidden bg-black/60 backdrop-blur-sm">
    {{-- stopPropagation di box agar klik dalam box tidak bubble ke overlay --}}
    <div id="edit-modal-box"
         class="w-full max-w-md mx-4 overflow-hidden bg-white shadow-2xl rounded-2xl">
        <div class="px-6 pt-6 pb-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">✏️ Edit Waktu Finish</h3>
                    <p id="edit-modal-subtitle" class="mt-0.5 text-xs text-gray-500"></p>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 transition hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-5 space-y-4">
            <input type="hidden" id="edit-bib">
            <div>
                <label class="block mb-1.5 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                    Elapsed Time (chip time)
                </label>
                <input
                    id="edit-elapsed"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    autocorrect="off"
                    autocapitalize="off"
                    spellcheck="false"
                    data-lpignore="true"
                    data-form-type="other"
                    placeholder="00:17:00"
                    class="w-full px-4 py-3 font-mono text-sm transition border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-800/30 focus:border-red-800"
                >
                <p class="mt-1 text-xs text-gray-400">Format HH:MM:SS · contoh: 00:17:00</p>
            </div>
            <p id="edit-error" class="hidden text-xs font-medium text-red-600"></p>
        </div>
        <div class="flex gap-3 px-6 pb-6">
            <button type="button" onclick="closeEditModal()"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                Batal
            </button>
            <button type="button" onclick="submitEditTime()"
                    id="edit-submit-btn"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-800 rounded-xl hover:bg-red-700 transition">
                Simpan
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- FINISH SECTION                                        --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="mb-8">

    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-xl">
                <svg class="w-5 h-5 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">🏁 Finish</h2>
                <p class="text-xs text-gray-500">
                    {{ $allParticipants->count() }} peserta
                    ({{ $males->count() }} pria · {{ $females->count() }} wanita)
                    @if($hasGunTime) · Ranking by Gun Time @endif
                </p>
            </div>
        </div>

        <a href="{{ route('event.export-finish', array_filter([
                'event'    => $event->slug,
                'category' => $selectedCategory ?? null,
            ])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition bg-green-600 rounded-lg shadow-sm hover:bg-green-700 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Export Excel
        </a>
    </div>

    {{-- ── MALE TABLE ──────────────────────────────────────── --}}
    @if($males->count())
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold tracking-wide text-blue-800 uppercase bg-blue-100 rounded-full">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9.5 14.5a5 5 0 1 1 7.071-7.071L18 6h-3V3l-1.5 1.5A7 7 0 1 0 16.5 16H14a5 5 0 0 1-4.5-1.5z"/>
                </svg>
                Pria · {{ $males->count() }} peserta
            </span>
        </div>

        {{-- Desktop --}}
        <div class="hidden overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl md:block">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase">
                        <th class="px-5 py-3 text-center">Rank</th>
                        <th class="px-5 py-3">Peserta</th>
                        <th class="px-5 py-3">Kategori</th>
                        @if($hasGunTime)
                            <th class="px-5 py-3 text-center">
                                Gun Time
                                <span class="block font-normal text-gray-400 normal-case">gun → finish</span>
                            </th>
                            <th class="px-5 py-3 text-center">
                                Chip Time
                                <span class="block font-normal text-gray-400 normal-case">rfid → finish</span>
                            </th>
                        @else
                            <th class="px-5 py-3 text-center">Elapsed Time</th>
                        @endif
                        <th class="px-5 py-3 text-center">Finish At</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($males as $i => $item)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-5 py-3 text-center">
                            <div class="inline-flex items-center justify-center w-9 h-9 font-bold rounded-xl text-sm {{ $rankBadge($i + 1) }}">
                                {{ $i + 1 }}
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center flex-shrink-0 bg-red-800 w-11 h-11 rounded-xl">
                                    <span class="text-xs font-bold text-white">{{ $item['participant']->bib }}</span>
                                </div>
                                <div>
                                    <div class="font-semibold leading-tight text-gray-900">{{ $item['participant']->display_name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $item['participant']->age ?? '-' }} th · {{ $item['participant']->city ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-700">
                            {{ $item['participant']->category?->name ?? '-' }}
                        </td>
                        @if($hasGunTime)
                            <td class="px-5 py-3 text-center">
                                <div class="font-mono text-base font-medium text-gray-400">
                                    {{ $item['participant']->formatted_gun_elapsed_time ?? '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="font-mono text-sm font-bold text-red-800">
                                    {{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}
                                </div>
                            </td>
                        @else
                            <td class="px-5 py-3 text-center">
                                <div class="font-mono text-base font-bold text-red-800">
                                    {{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}
                                </div>
                            </td>
                        @endif
                        <td class="px-5 py-3 font-mono text-xs text-center text-gray-500">
                            {{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <button
                                onclick="openEditModal('{{ $item['participant']->bib }}', '{{ $item['participant']->display_name }}')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition active:scale-95">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="space-y-3 md:hidden">
            @foreach($males as $i => $item)
            <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white bg-red-800 rounded-lg">
                            {{ $item['participant']->bib }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $item['participant']->display_name }}</div>
                            <div class="text-xs text-gray-400">{{ $item['participant']->age ?? '-' }} th · {{ $item['participant']->city ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                           onclick="openEditModal('{{ $item['participant']->bib }}', '{{ $item['participant']->display_name }}')"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <div class="flex items-center justify-center w-9 h-9 font-bold rounded-lg text-sm {{ $rankBadge($i + 1) }}">
                            {{ $i + 1 }}
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <div class="text-xs text-gray-400">Kategori</div>
                        <div class="text-xs font-medium text-gray-800">{{ $item['participant']->category?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">Finish At</div>
                        <div class="font-mono text-xs text-gray-700">{{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}</div>
                    </div>
                    @if($hasGunTime)
                        <div>
                            <div class="text-xs text-gray-400">Gun Time</div>
                            <div class="font-mono text-base font-bold text-red-800">{{ $item['participant']->formatted_gun_elapsed_time ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Chip Time</div>
                            <div class="font-mono text-sm font-medium text-gray-400">{{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}</div>
                        </div>
                    @else
                        <div class="col-span-2">
                            <div class="text-xs text-gray-400">Elapsed Time</div>
                            <div class="font-mono text-base font-bold text-red-800">{{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}</div>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── FEMALE TABLE ─────────────────────────────────────── --}}
    @if($females->count())
    <div class="mb-2">
        <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold tracking-wide text-pink-800 uppercase bg-pink-100 rounded-full">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a5 5 0 1 1 0 10A5 5 0 0 1 12 2zm0 12c-5.33 0-8 2.67-8 4v1h16v-1c0-1.33-2.67-4-8-4z"/>
                </svg>
                Wanita · {{ $females->count() }} peserta
            </span>
        </div>

        {{-- Desktop --}}
        <div class="hidden overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl md:block">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase">
                        <th class="px-5 py-3 text-center">Rank</th>
                        <th class="px-5 py-3">Peserta</th>
                        <th class="px-5 py-3">Kategori</th>
                        @if($hasGunTime)
                            <th class="px-5 py-3 text-center">
                                Gun Time
                                <span class="block font-normal text-gray-400 normal-case">gun → finish</span>
                            </th>
                            <th class="px-5 py-3 text-center">
                                Chip Time
                                <span class="block font-normal text-gray-400 normal-case">rfid → finish</span>
                            </th>
                        @else
                            <th class="px-5 py-3 text-center">Elapsed Time</th>
                        @endif
                        <th class="px-5 py-3 text-center">Finish At</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($females as $i => $item)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-5 py-3 text-center">
                            <div class="inline-flex items-center justify-center w-9 h-9 font-bold rounded-xl text-sm {{ $rankBadge($i + 1) }}">
                                {{ $i + 1 }}
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center flex-shrink-0 bg-pink-700 w-11 h-11 rounded-xl">
                                    <span class="text-xs font-bold text-white">{{ $item['participant']->bib }}</span>
                                </div>
                                <div>
                                    <div class="font-semibold leading-tight text-gray-900">{{ $item['participant']->display_name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $item['participant']->age ?? '-' }} th · {{ $item['participant']->city ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-700">
                            {{ $item['participant']->category?->name ?? '-' }}
                        </td>
                        @if($hasGunTime)
                            <td class="px-5 py-3 text-center">
                                <div class="font-mono text-base font-bold text-pink-700">
                                    {{ $item['participant']->formatted_gun_elapsed_time ?? '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="font-mono text-sm font-medium text-gray-400">
                                    {{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}
                                </div>
                            </td>
                        @else
                            <td class="px-5 py-3 text-center">
                                <div class="font-mono text-base font-bold text-pink-700">
                                    {{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}
                                </div>
                            </td>
                        @endif
                        <td class="px-5 py-3 font-mono text-xs text-center text-gray-500">
                            {{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <button
                                onclick="openEditModal('{{ $item['participant']->bib }}', '{{ $item['participant']->display_name }}')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition active:scale-95">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile --}}
        <div class="space-y-3 md:hidden">
            @foreach($females as $i => $item)
            <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white bg-pink-700 rounded-lg">
                            {{ $item['participant']->bib }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $item['participant']->display_name }}</div>
                            <div class="text-xs text-gray-400">{{ $item['participant']->age ?? '-' }} th · {{ $item['participant']->city ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            onclick="openEditModal('{{ $item['participant']->bib }}', '{{ $item['participant']->display_name }}')"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <div class="flex items-center justify-center w-9 h-9 font-bold rounded-lg text-sm {{ $rankBadge($i + 1) }}">
                            {{ $i + 1 }}
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <div class="text-xs text-gray-400">Kategori</div>
                        <div class="text-xs font-medium text-gray-800">{{ $item['participant']->category?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">Finish At</div>
                        <div class="font-mono text-xs text-gray-700">{{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}</div>
                    </div>
                    @if($hasGunTime)
                        <div>
                            <div class="text-xs text-gray-400">Gun Time</div>
                            <div class="font-mono text-base font-bold text-pink-700">{{ $item['participant']->formatted_gun_elapsed_time ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400">Chip Time</div>
                            <div class="font-mono text-sm font-medium text-gray-400">{{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}</div>
                        </div>
                    @else
                        <div class="col-span-2">
                            <div class="text-xs text-gray-400">Elapsed Time</div>
                            <div class="font-mono text-base font-bold text-pink-700">{{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}</div>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT                                            --}}
{{-- ══════════════════════════════════════════════════════ --}}
<script>
(function () {
    let pendingBib  = null;
    let pendingName = null;

    function showModal(id) { document.getElementById(id).classList.replace('hidden', 'flex'); }
    function hideModal(id) { document.getElementById(id).classList.replace('flex', 'hidden'); }

    // ── EDIT MODAL ────────────────────────────────────────────
    window.openEditModal = function (bib, name) {
        pendingBib  = bib;
        pendingName = name;
        document.getElementById('edit-bib').value = bib;
        document.getElementById('edit-modal-subtitle').textContent = 'BIB ' + bib + ' — ' + name;
        document.getElementById('edit-elapsed').value = '';
        document.getElementById('edit-error').classList.add('hidden');
        showModal('edit-modal');
        setTimeout(() => document.getElementById('edit-elapsed').focus(), 80);
    };

    window.closeEditModal = function () { hideModal('edit-modal'); };

    // ── Input: cukup ketik 6 digit, otomatis jadi HH:MM:SS ───
    const elapsedInput = document.getElementById('edit-elapsed');

    elapsedInput?.addEventListener('input', function () {
    // Ambil hanya digit, max 6
    const digits = this.value.replace(/\D/g, '').slice(0, 6);

    if (digits.length === 0) {
        this.value = '';
        return;
    }

    // Pad kiri dulu ke 6 digit, baru format
    const padded = digits.padStart(6, '0');
    const formatted = padded.slice(0, 2) + ':' + padded.slice(2, 4) + ':' + padded.slice(4, 6);

    this.value = formatted;

    const len = formatted.length;
    try { this.setSelectionRange(len, len); } catch (_) {}
});

    elapsedInput?.addEventListener('keyup',  e => e.stopPropagation());
    elapsedInput?.addEventListener('keypress', e => e.stopPropagation());

    elapsedInput?.addEventListener('input', function () {
        // Ambil hanya digit, max 6
        const digits = this.value.replace(/\D/g, '').slice(0, 6);

        // Pad kiri dengan 0 supaya selalu 6 digit saat format
        // tapi hanya format kalau sudah >= 1 digit
        let formatted = digits;
        if (digits.length > 4) {
            formatted = digits.slice(0, 2) + ':' + digits.slice(2, 4) + ':' + digits.slice(4);
        } else if (digits.length > 2) {
            formatted = digits.slice(0, 2) + ':' + digits.slice(2);
        }

        this.value = formatted;

        // Tempatkan cursor di akhir
        const len = formatted.length;
        try { this.setSelectionRange(len, len); } catch (_) {}
    });

    // ── Validasi & submit ─────────────────────────────────────
    function parseTime(str) {
        const match = str.trim().match(/^(\d{1,2}):(\d{2}):(\d{2})$/);
        if (!match) return null;
        const [, h, m, s] = match.map(Number);
        if (m >= 60 || s >= 60) return null;
        return { h, m, s };
    }

    window.submitEditTime = function () {
        const bib     = document.getElementById('edit-bib').value;
        const elapsed = document.getElementById('edit-elapsed').value;
        const errEl   = document.getElementById('edit-error');
        const btn     = document.getElementById('edit-submit-btn');

        errEl.classList.add('hidden');

        if (!parseTime(elapsed)) {
            errEl.textContent = 'Format tidak valid. Gunakan 6 digit, contoh: 001700 → 00:17:00';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled    = true;
        btn.textContent = 'Menyimpan…';

        fetch('{{ url('/') }}/admin/finish-time', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',// SESUDAH
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    ?? document.querySelector('input[name="_token"]')?.value
                    ?? '{{ csrf_token() }}',
                'Accept':       'application/json',
            },
            body: JSON.stringify({ bib, elapsed_time: elapsed }),
        })
        .then(async (res) => {
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message ?? 'HTTP ' + res.status);
            return json;
        })
        .then(() => {
            hideModal('edit-modal');
            if (typeof refreshLiveData === 'function') refreshLiveData(true);
            else window.location.reload();
        })
        .catch((err) => {
            errEl.textContent = 'Gagal menyimpan: ' + err.message;
            errEl.classList.remove('hidden');
        })
        .finally(() => {
            btn.disabled    = false;
            btn.textContent = 'Simpan';
        });
    };

    // ── Backdrop: tutup hanya kalau klik tepat di overlay ─────
    document.getElementById('edit-modal')?.addEventListener('click', function (e) {
        if (e.target === this) window.closeEditModal();
    });

    // ── Box: cegah semua event bubble ke overlay ──────────────
    const box = document.getElementById('edit-modal-box');
    ['click', 'keydown', 'keyup', 'keypress'].forEach(ev => {
        box?.addEventListener(ev, e => e.stopPropagation());
    });

})();
</script>
