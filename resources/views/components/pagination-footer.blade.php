{{-- Professional pagination footer - works with both pager objects and inline properties --}}
{{-- Usage with pager object: @include('components.pagination-footer', ['prefix' => 'pager.']) --}}
{{-- Usage with inline data:   @include('components.pagination-footer', ['prefix' => '']) --}}
@props(['prefix' => 'pager.'])

<div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        {{-- Left: Showing info + per-page selector --}}
        <div class="flex items-center gap-3 text-xs text-gray-500">
            <span x-text="'Showing ' + (({{ $prefix }}currentPage - 1) * {{ $prefix }}perPage + 1) + ' to ' + Math.min({{ $prefix }}currentPage * {{ $prefix }}perPage, {{ $prefix }}total) + ' of ' + {{ $prefix }}total + ' entries'"></span>
            <div class="flex items-center gap-1.5">
                <span>Show</span>
                <select @change="{{ $prefix }}changePerPage($event.target.value)"
                    class="per-page-select">
                    <option value="10" :selected="{{ $prefix }}perPage == 10">10</option>
                    <option value="25" :selected="{{ $prefix }}perPage == 25">25</option>
                    <option value="50" :selected="{{ $prefix }}perPage == 50">50</option>
                    <option value="100" :selected="{{ $prefix }}perPage == 100">100</option>
                </select>
                <span>entries</span>
            </div>
        </div>

        {{-- Right: Pagination buttons --}}
        <div x-show="{{ $prefix }}totalPages > 1" class="flex items-center gap-1">
            <button @click="{{ $prefix }}changePage(1)" :disabled="{{ $prefix }}currentPage === 1"
                class="pagination-btn" title="First page">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
            </button>
            <button @click="{{ $prefix }}changePage({{ $prefix }}currentPage - 1)" :disabled="{{ $prefix }}currentPage === 1"
                class="pagination-btn" title="Previous page">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <template x-for="page in {{ $prefix }}visiblePages" :key="page">
                <button @click="{{ $prefix }}changePage(page)"
                    :class="page === {{ $prefix }}currentPage ? 'pagination-btn active' : 'pagination-btn'"
                    x-text="page"></button>
            </template>

            <button @click="{{ $prefix }}changePage({{ $prefix }}currentPage + 1)" :disabled="{{ $prefix }}currentPage === {{ $prefix }}totalPages"
                class="pagination-btn" title="Next page">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button @click="{{ $prefix }}changePage({{ $prefix }}totalPages)" :disabled="{{ $prefix }}currentPage === {{ $prefix }}totalPages"
                class="pagination-btn" title="Last page">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</div>
