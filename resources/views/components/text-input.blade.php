@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-rose-300 focus:ring-rose-300 rounded-md shadow-sm']) }}>
