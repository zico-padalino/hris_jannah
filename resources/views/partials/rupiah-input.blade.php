@php
    $rawValue = $value ?? '';
    $digits = preg_replace('/\D/', '', (string) $rawValue);
    $displayValue = $digits !== '' ? number_format((int) $digits, 0, ',', '.') : '';
@endphp

<label @class(['block', $wrapperClass ?? null])>
    <span class="form-label">{{ $label }}</span>
    <div class="rupiah-field">
        <span class="rupiah-field__prefix" aria-hidden="true">Rp</span>
        <input
            type="text"
            inputmode="numeric"
            name="{{ $name }}"
            value="{{ $displayValue }}"
            @if($required ?? false) required @endif
            @if(! empty($id)) id="{{ $id }}" @endif
            data-rupiah-input
            class="rupiah-field__input"
            autocomplete="off"
        >
    </div>
</label>
