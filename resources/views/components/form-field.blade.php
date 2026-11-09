@props(['name', 'label', 'value' => '', 'placeholder' => '', 'multiline' => false, 'type' => 'text', 'required' => true])

<div class="form__row">
    <label for="{{ $name }}">{{ $label }}</label>
    @if ($multiline)
        <textarea class="textarea" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}" required>{{ old($name, $value) }}</textarea>
    @else
        <input class="field" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" @required($required)>
    @endif
    @error($name)
        <p class="form__error">{{ $message }}</p>
    @enderror
</div>
