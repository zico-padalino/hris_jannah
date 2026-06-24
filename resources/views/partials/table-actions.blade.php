@props([
    'module',
    'show' => null,
    'showAction' => 'view',
    'showLabel' => 'Detail',
    'edit' => null,
    'editAction' => 'edit',
    'editLabel' => 'Edit',
    'location' => null,
    'locationAction' => 'location',
    'locationLabel' => 'Tambah Lokasi',
    'delete' => null,
    'deleteAction' => 'delete',
    'deleteConfirm' => 'Yakin ingin menghapus data ini?',
    'deleteLabel' => 'Hapus',
    'extras' => [],
    'layout' => 'inline',
])

@php
    $user = auth()->user();
    $can = fn (string $actionKey) => $user?->canSeeModuleAction($module, $actionKey) ?? false;
    $layoutClass = $layout === 'bar' ? 'table-actions--bar' : '';
@endphp

<div {{ $attributes->merge(['class' => 'table-actions '.$layoutClass]) }} role="group" aria-label="Aksi baris">
    @if($show && $can($showAction))
        <a href="{{ $show }}" class="table-action-btn table-action-btn--view" title="{{ $showLabel }}" aria-label="{{ $showLabel }}">
            <svg class="table-action-btn__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="sr-only">{{ $showLabel }}</span>
        </a>
    @endif

    @if($edit && $can($editAction))
        <a href="{{ $edit }}" class="table-action-btn table-action-btn--edit" title="{{ $editLabel }}" aria-label="{{ $editLabel }}">
            <svg class="table-action-btn__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            <span class="sr-only">{{ $editLabel }}</span>
        </a>
    @endif

    @if($location && $can($locationAction))
        <a href="{{ $location }}" class="table-action-btn table-action-btn--location" title="{{ $locationLabel }}" aria-label="{{ $locationLabel }}">
            <svg class="table-action-btn__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            <span class="sr-only">{{ $locationLabel }}</span>
        </a>
    @endif

    @foreach($extras as $extra)
        @php
            $extraAction = $extra['action_key'] ?? 'extra';
        @endphp
        @if(is_array($extra) && ! empty($extra['href']) && $can($extraAction))
            <a
                href="{{ $extra['href'] }}"
                class="table-action-btn table-action-btn--{{ $extra['variant'] ?? 'accent' }}"
                title="{{ $extra['label'] ?? 'Aksi' }}"
                aria-label="{{ $extra['label'] ?? 'Aksi' }}"
            >
                @if(($extra['icon'] ?? 'shield') === 'shield')
                    <svg class="table-action-btn__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                @endif
                <span class="sr-only">{{ $extra['label'] ?? 'Aksi' }}</span>
            </a>
        @endif
    @endforeach

    @if($delete && $can($deleteAction))
        <form
            method="POST"
            action="{{ $delete }}"
            class="table-actions__form"
            onsubmit="return confirm(@js($deleteConfirm))"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="table-action-btn table-action-btn--delete" title="{{ $deleteLabel }}" aria-label="{{ $deleteLabel }}">
                <svg class="table-action-btn__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                <span class="sr-only">{{ $deleteLabel }}</span>
            </button>
        </form>
    @endif
</div>
