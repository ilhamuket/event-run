{{--
    event/_live-content.blade.php
    Partial yang di-render ulang setiap polling AJAX.
    Hanya berisi section yang berubah (Finish, Checkpoint, Started, Not Started).
    Header, filter, dan summary card ada di live-preview.blade.php.
--}}

@if($checkpointGroups->isEmpty() && !$notStarted->count())
    <div class="p-16 text-center bg-white border border-gray-200 shadow-sm rounded-2xl">
        <h3 class="text-lg font-semibold text-gray-900">Belum ada aktivitas</h3>
        <p class="mt-2 text-sm text-gray-600">
            Race belum dimulai atau belum ada peserta yang terdeteksi
        </p>
    </div>
@else

    {{-- FINISH --}}
    @if(isset($checkpointGroups['finish']) && $checkpointGroups['finish']['participants']->count())
        @include('event.live-section-finish', ['group' => $checkpointGroups['finish']])
    @endif

    {{-- INTERMEDIATE CHECKPOINTS (reverse order: latest first) --}}
    @foreach($checkpointGroups as $key => $group)
        @if($key === 'finish' || $key === 'start') @continue @endif
        @if($group['participants']->count())
            @include('event.live-section-checkpoint', ['group' => $group])
        @endif
    @endforeach

    {{-- STARTED — passed start gate only --}}
    @if(isset($checkpointGroups['start']) && $checkpointGroups['start']['participants']->count())
        @include('event.live-section-started', ['group' => $checkpointGroups['start']])
    @endif

    {{-- NOT STARTED --}}
    @if($notStarted->count())
        @include('event.live-section-not-started', ['notStarted' => $notStarted])
    @endif

@endif
