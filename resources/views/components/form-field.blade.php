@props(['name', 'label', 'value' => '', 'placeholder' => '', 'multiline' => false])

<div class="form__row">
    <label for="{{ $name }}">{{ $label }}</label>
    @if ($multiline)
        <textarea class="textarea" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}" required>{{ old($name, $value) }}</textarea>
    @else
        <input class="field" id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" required>
    @endif
    @error($name)
        <p class="form__error">{{ $message }}</p>
    @enderror
</div>
