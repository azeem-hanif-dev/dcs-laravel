{{-- Professional delete confirmation modal --}}
@props(['show' => 'deleteModalOpen', 'title' => 'Confirm Delete', 'itemName' => '', 'onConfirm' => 'deleteItem()', 'onCancel' => 'deleteModalOpen = false', 'saving' => 'saving'])

<div x-show="{{ $show }}" class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-transition.opacity.duration.200
    @keydown.escape.window="{{ $onCancel }}">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="{{ $onCancel }}"></div>

    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 overflow-hidden"
        @click.outside="{{ $onCancel }}">
        {{-- Warning header --}}
        <div class="bg-red-50 px-6 py-5 text-center border-b border-red-100">
            <div class="w-14 h-14 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-3">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            <p class="text-sm text-gray-500 mt-1.5">
                Are you sure you want to delete
                @if($itemName)
                <span class="font-semibold text-gray-700" x-text="{{ $itemName }}"></span>?
                @else
                this item? This action cannot be undone.
                @endif
            </p>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 flex justify-center gap-3 bg-gray-50">
            <button @click="{{ $onCancel }}"
                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl transition-colors shadow-sm">
                Cancel
            </button>
            <button @click="{{ $onConfirm }}"
                :disabled="{{ $saving }}"
                class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm flex items-center gap-2">
                <svg x-show="{{ $saving }}" class="spinner w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="{{ $saving }} ? 'Deleting...' : 'Delete'"></span>
            </button>
        </div>
    </div>
</div>
