{{-- Professional table loader / skeleton state --}}
@props(['colspan' => '7', 'rows' => 5])

<template x-if="loading">
    <tr>
        <td colspan="{{ $colspan }}" class="px-6 py-20 text-center">
            <div class="flex flex-col items-center gap-3">
                <svg class="spinner w-10 h-10 text-primary" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-400">Loading data...</p>
                    <p class="text-xs text-gray-400 mt-0.5">Please wait a moment</p>
                </div>
            </div>
        </td>
    </tr>
</template>
