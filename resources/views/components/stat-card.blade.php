@props(['title', 'value'])

<div class="bg-white p-6 rounded-xl shadow-md">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 truncate">{{ $title }}</p>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $value }}</p>
        </div>
        @if (isset($icon))
            <div class="bg-primary-100 p-3 rounded-full">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
