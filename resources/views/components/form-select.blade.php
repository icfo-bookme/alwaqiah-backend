@props([
    'label', 
    'name' => null, 
    'id' => null, 
    'placeholder' => 'Select an option'
])

<div>
    <label class="font-semibold text-sm text-slate-700 block mb-1">{{ $label }}</label>
    <select id="{{ $id ?? $name }}" 
            name="{{ $name }}"
            {{ $attributes->merge([
                'class' => 'w-full border border-slate-300 rounded-md p-2 bg-white text-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all cursor-pointer'
            ]) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
</div>