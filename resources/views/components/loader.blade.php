{{-- resources/views/components/loader.blade.php --}}
<div x-show="show" x-transition.opacity
    class="flex items-center justify-center py-12">
    <div class="flex flex-col items-center gap-3">
        <svg class="spinner w-10 h-10" :class="colorClass || 'text-primary'" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <p class="text-sm text-gray-500" x-text="text || 'Loading...'"></p>
    </div>
</div>
