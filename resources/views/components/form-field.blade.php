@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'options' => [],
    'style' => null,
    'extraAttributes' => [],
    'wrapperClass' => 'form-group',
    'id' => null,
])

@php
    $fieldId = $id ?? $name;
    $oldValue = old($name, $value);
@endphp

<div class="{{ $wrapperClass }}">
    <label for="{{ $fieldId }}">{{ strtoupper($label) }}</label>
    
    @if ($type === 'textarea')
        <textarea
            name="{{ $name }}"
            id="{{ $fieldId }}"
            placeholder="{{ $placeholder }}"
            style="{{ $style }}"
            class="@error($name) has-error @enderror"
            @foreach ($extraAttributes as $attr => $val)
                @if (is_numeric($attr)) {{ $val }} @else {{ $attr }}="{{ $val }}" @endif
            @endforeach
        >{{ $oldValue }}</textarea>

    @elseif ($type === 'select')
        <select
            name="{{ $name }}"
            id="{{ $fieldId }}"
            class="@error($name) has-error @enderror"
            @foreach ($extraAttributes as $attr => $val)
                @if (is_numeric($attr)) {{ $val }} @else {{ $attr }}="{{ $val }}" @endif
            @endforeach
        >
            <option value="">Chọn {{ Str::lower($label) }}</option>
            @foreach ($options as $key => $option)
                @if (is_array($option))
                    <option value="{{ $option['value'] }}" @selected($oldValue == $option['value'])>
                        {{ $option['text'] }}
                    </option>
                @else
                    <option value="{{ $key }}" @selected($oldValue == $key)>
                        {{ $option }}
                    </option>
                @endif
            @endforeach
        </select>

    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $fieldId }}"
            placeholder="{{ $placeholder }}"
            value="{{ $oldValue }}"
            class="@error($name) has-error @enderror"
            @foreach ($extraAttributes as $attr => $val)
                @if (is_numeric($attr)) {{ $val }} @else {{ $attr }}="{{ $val }}" @endif
            @endforeach
        >
    @endif

    @error($name)
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>
