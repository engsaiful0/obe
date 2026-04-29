@props([
    'name' => 'password',
    'id' => null,
    'value' => '',
    'placeholder' => 'Enter your password',
    'required' => false,
    'class' => '',
    'label' => 'Password',
    'showLabel' => true,
    'errorKey' => null,
    'helpText' => null
])

@php
    $inputId = $id ?? $name;
    $errorKey = $errorKey ?? $name;
@endphp

<div class="form-password-toggle {{ $class }}">
    @if($showLabel)
        <label class="form-label" for="{{ $inputId }}">{{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    <div class="input-group input-group-merge">
        <input 
            type="password" 
            id="{{ $inputId }}" 
            name="{{ $name }}" 
            value="{{ old($name, $value) }}" 
            placeholder="{{ $placeholder }}"
            class="form-control @error($errorKey) is-invalid @enderror"
            @if($required) required @endif
            {{ $attributes }}
        />
        <span class="input-group-text cursor-pointer password-toggle" title="Show password">
            <i class="ti ti-eye-off"></i>
        </span>
    </div>
    
    @if($helpText)
        <div class="form-text">{{ $helpText }}</div>
    @endif
    
    @error($errorKey)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>






