@props([
    'label',
    'name',
    'id' => null,
    'type' => 'text',
    'placeholder' => '',
    'value' => '',
    'required' => false
])

<div>
    <label class="font-semibold text-sm text-slate-700 block mb-1">{{ $label }} @if ($required)<span
            class="text-red-500">*</span>@endif</label>
    <input type="{{ $type }}" 
           id="{{ $id ?? $name }}" 
           name="{{ $name }}" 
           placeholder="{{ $placeholder }}" 
           value="{{ $value }}"
           {{ $attributes->merge([
               'class' => 'w-full border border-slate-300 rounded-md p-2 bg-white text-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400'
           ]) }}>
</div>