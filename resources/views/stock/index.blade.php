{{-- resources/views/stock/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Stock Overview - Distributor Portal')
@section('page-content')
<div x-data="stockPage()" x-init="init()" class="max-w-7xl mx-auto px-2 sm:px-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Stock Overview</h1>
            <p class="text-sm text-gray-500 mt-0.5">Real-time inventory levels, values &amp; movement history</p>
        </div>
        <div class="flex gap-2">
            <button @click="openAdjustModal()" class="bg-primary text-white text-xs px-3 py-1.5 rounded-lg flex items-center gap-1 hover:opacity-90">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Adjust Stock
            </button>
            <button @click="activeTab='overview'"
                :class="activeTab==='overview'?'bg-primary text-white':'bg-white text-gray-600 border border-gray-200'"
                class="text-xs px-3 py-1.5 rounded-lg transition-all">Overview</button>
            <button @click="activeTab='movements'; loadMovements()"
                :class="activeTab==='movements'?'bg-primary text-white':'bg-white text-gray-600 border border-gray-200'"
                class="text-xs px-3 py-1.5 rounded-lg transition-all">Movements</button>
        </div>
    </div>

    {{-- ===== SUMMARY CARDS ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4" x-show="activeTab==='overview'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3">
            <p class="text-xs text-gray-500 mb-1">Total Products</p>
            <p class="text-xl font-bold text-gray-800" x-text="summary.total_unique_products ?? '—'"></p>
            <p class="text-xs text-gray-400 mt-0.5" x-text="(summary.total_stock_records ?? 0)+' records'"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3">
            <p class="text-xs text-gray-500 mb-1">Total Units</p>
            <p class="text-xl font-bold text-gray-800" x-text="num(summary.total_units)"></p>
            <p class="text-xs text-gray-400 mt-0.5" x-text="num(summary.total_available)+' available'"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-3">
            <p class="text-xs text-green-600 mb-1">Stock Value</p>
            <p class="text-xl font-bold text-green-700" x-text="'$'+money(summary.total_value)"></p>
            <p class="text-xs text-green-400 mt-0.5" x-text="num(summary.total_reserved)+' reserved'"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-3">
            <p class="text-xs text-amber-600 mb-1">Low Stock</p>
            <p class="text-xl font-bold text-amber-600" x-text="summary.low_stock ?? 0"></p>
            <p class="text-xs text-amber-400 mt-0.5">Needs reorder</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-3">
            <p class="text-xs text-red-600 mb-1">Out of Stock</p>
            <p class="text-xl font-bold text-red-600" x-text="(summary.out_of_stock ?? 0)+(summary.all_reserved ?? 0)"></p>
            <p class="text-xs text-red-400 mt-0.5">Urgent attention</p>
        </div>
    </div>

    {{-- ===== OVERVIEW TAB ===== --}}
    <div x-show="activeTab==='overview'">

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2.5 mb-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative max-w-[170px] w-full">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" @input.debounce.300ms="doSearch()" placeholder="Search product/SKU..." class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                </div>
                <select x-model="filterWarehouse" @change="doSearch()" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs max-w-[140px] focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">All Warehouses</option>
                    <template x-for="w in warehouses" :key="w.id"><option :value="w.id" x-text="w.name"></option></template>
                </select>
                <select x-model="filterStatus" @change="doSearch()" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs max-w-[120px] focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                    <option value="all_reserved">All Reserved</option>
                </select>
                <select x-model="filterCategory" @change="doSearch()" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs max-w-[140px] focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">All Categories</option>
                    <template x-for="c in categories" :key="c.id"><option :value="c.id" x-text="c.name"></option></template>
                </select>
                <select x-model="filterSupplier" @change="doSearch()" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs max-w-[140px] focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">All Suppliers</option>
                    <template x-for="s in suppliers" :key="s.id"><option :value="s.id" x-text="s.name"></option></template>
                </select>
                <button @click="clearFilters()" class="text-xs text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">Clear All</button>
                <span class="text-xs text-gray-400 ml-auto" x-text="pager.total+' records'"></span>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-card-sm min-w-full divide-y divide-gray-200">
                    <thead class="table-header-branded">
                        <tr>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">#</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">Product / SKU</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider hidden md:table-cell">Category</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider hidden md:table-cell">Warehouse</th>
                            <th class="px-2 py-2.5 text-center text-xs font-semibold uppercase tracking-wider">Total</th>
                            <th class="px-2 py-2.5 text-center text-xs font-semibold uppercase tracking-wider">Reserved</th>
                            <th class="px-2 py-2.5 text-center text-xs font-semibold uppercase tracking-wider">Available</th>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">Value</th>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        {{-- Loading --}}
                        <template x-if="pager.loading">
                            <tr><td colspan="9" class="px-6 py-16 text-center">
                                <svg class="spinner w-8 h-8 text-primary mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <p class="text-sm text-gray-400 mt-2">Loading stock data...</p>
                            </td></tr>
                        </template>

                        {{-- Empty --}}
                        <template x-if="!pager.loading && pager.items.length === 0">
                            <tr><td colspan="9" class="px-6 py-16 text-center">
                                <svg class="w-14 h-14 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-base font-semibold text-gray-400">No Stock Records</p>
                                <p class="text-sm text-gray-400 mt-1">Receive purchase orders to see inventory here</p>
                            </td></tr>
                        </template>

                        {{-- Rows --}}
                        <template x-for="(item, index) in pager.items" :key="item.id">
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="px-3 py-2.5 text-sm text-gray-500 whitespace-nowrap" x-text="(pager.currentPage-1)*pager.perPage+index+1"></td>
                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    <p class="text-sm font-semibold text-gray-800" x-text="item.product?.material_name || '—'"></p>
                                    <p class="text-xs text-gray-400" x-show="item.product?.sku" x-text="'SKU: '+item.product.sku"></p>
                                </td>
                                <td class="px-3 py-2.5 text-sm text-gray-600 whitespace-nowrap hidden md:table-cell" x-text="item.product?.category?.name || '—'"></td>
                                <td class="px-3 py-2.5 text-sm text-gray-600 whitespace-nowrap hidden md:table-cell" x-text="item.warehouse?.name || '—'"></td>
                                <td class="px-2 py-2.5 text-center text-sm font-semibold text-gray-700 whitespace-nowrap" x-text="num(item.total_quantity)"></td>
                                <td class="px-2 py-2.5 text-center text-sm text-gray-500 whitespace-nowrap" x-text="num(item.reserved_quantity)"></td>
                                <td class="px-2 py-2.5 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-xs rounded-full font-bold"
                                        :class="availColor(item)"
                                        x-text="num(item.available_quantity)"></span>
                                </td>
                                <td class="px-3 py-2.5 text-center text-sm text-gray-700 font-medium whitespace-nowrap hidden sm:table-cell" x-text="'$'+money(item.stock_value)"></td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-xs rounded-full font-semibold"
                                        :class="statusColor(item.status)"
                                        x-text="item.status"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            @include('components.pagination-footer', ['prefix' => 'pager.'])
        </div>
    </div>

    {{-- ===== MOVEMENTS TAB ===== --}}
    <div x-show="activeTab==='movements'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2.5 mb-3">
            <div class="flex flex-wrap items-center gap-2">
                <select x-model="mvWarehouse" @change="loadMovements()" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs max-w-[150px] focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">All Warehouses</option>
                    <template x-for="w in warehouses" :key="w.id"><option :value="w.id" x-text="w.name"></option></template>
                </select>
                <select x-model="mvType" @change="loadMovements()" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs max-w-[150px] focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">All Types</option>
                    <option value="purchase_received">Purchase Received</option>
                    <option value="sales_shipped">Sales Shipped</option>
                    <option value="sales_returned">Sales Return</option>
                    <option value="purchase_returned">Purchase Return</option>
                    <option value="manual_addition">Manual Addition</option>
                    <option value="manual_removal">Manual Removal</option>
                    <option value="reserved">Reserved</option>
                    <option value="released">Released</option>
                </select>
                <button @click="mvWarehouse=''; mvType=''; loadMovements()" class="text-xs text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">Clear</button>
                <span class="text-xs text-gray-400 ml-auto" x-text="mvTotal+' movements'"></span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-card-sm min-w-full divide-y divide-gray-200">
                    <thead class="table-header-branded">
                        <tr>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">Date / Time</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">Product</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider hidden md:table-cell">Type</th>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider">Qty Change</th>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">Before</th>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">After</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider hidden lg:table-cell">Reference</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider hidden lg:table-cell">By</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider hidden xl:table-cell">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <template x-if="mvLoading">
                            <tr><td colspan="9" class="px-6 py-10 text-center"><svg class="spinner w-6 h-6 text-primary mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></td></tr>
                        </template>
                        <template x-if="!mvLoading && movements.length === 0">
                            <tr><td colspan="9" class="px-6 py-10 text-center text-sm text-gray-400">No stock movements recorded yet.</td></tr>
                        </template>
                        <template x-for="m in movements" :key="m.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap" x-text="fmtDate(m.created_at)"></td>
                                <td class="px-3 py-2 text-sm font-medium text-gray-800 whitespace-nowrap" x-text="m.product?.material_name || '—'"></td>
                                <td class="px-3 py-2 whitespace-nowrap hidden md:table-cell">
                                    <span class="px-2 py-0.5 text-xs rounded-full font-medium" :class="m.quantity_change > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" x-text="m.type_label"></span>
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <span class="text-sm font-bold" :class="m.quantity_change > 0 ? 'text-green-600' : 'text-red-600'" x-text="(m.quantity_change > 0 ? '+' : '')+num(m.quantity_change)"></span>
                                </td>
                                <td class="px-3 py-2 text-center text-sm text-gray-500 whitespace-nowrap hidden sm:table-cell" x-text="num(m.quantity_before)"></td>
                                <td class="px-3 py-2 text-center text-sm text-gray-700 font-semibold whitespace-nowrap hidden sm:table-cell" x-text="num(m.quantity_after)"></td>
                                <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap hidden lg:table-cell" x-text="m.reference_number || '—'"></td>
                                <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap hidden lg:table-cell" x-text="m.user?.name || '—'"></td>
                                <td class="px-3 py-2 text-xs text-gray-400 max-w-[120px] truncate hidden xl:table-cell" x-text="m.notes || ''"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            {{-- Simple pagination for movements --}}
            <div class="flex justify-between items-center px-4 py-2 border-t" x-show="mvTotal > mvPerPage">
                <button @click="mvPage--; loadMovements()" :disabled="mvPage <= 1" class="text-xs px-3 py-1 rounded border disabled:opacity-30">← Prev</button>
                <span class="text-xs text-gray-500" x-text="'Page '+mvPage"></span>
                <button @click="mvPage++; loadMovements()" :disabled="movements.length < mvPerPage" class="text-xs px-3 py-1 rounded border disabled:opacity-30">Next →</button>
            </div>
        </div>
    </div>

    {{-- ===== ADJUST STOCK MODAL ===== --}}
    <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/50" @click="adjustModal=false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Adjust Stock</h3>
                <button @click="adjustModal=false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Product <span class="text-red-500">*</span></label>
                    <select x-model="adjProduct" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <option value="">Select Product</option>
                        <template x-for="p in allProducts" :key="p.id">
                            <option :value="p.id" x-text="p.material_name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Warehouse <span class="text-red-500">*</span></label>
                    <select x-model="adjWarehouse" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <option value="">Select Warehouse</option>
                        <template x-for="w in warehouses" :key="w.id"><option :value="w.id" x-text="w.name"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Adjustment Type</label>
                    <div class="flex gap-2">
                        <button @click="adjType='add'"
                            :class="adjType==='add'?'bg-green-600 text-white':'bg-white text-gray-600 border border-gray-200'"
                            class="flex-1 px-3 py-2 text-sm rounded-lg transition-colors">
                            <span class="font-semibold">+ Add Stock</span>
                        </button>
                        <button @click="adjType='remove'"
                            :class="adjType==='remove'?'bg-red-600 text-white':'bg-white text-gray-600 border border-gray-200'"
                            class="flex-1 px-3 py-2 text-sm rounded-lg transition-colors">
                            <span class="font-semibold">− Remove Stock</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" x-model="adjQty" min="1" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none" placeholder="Enter quantity">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                    <input type="text" x-model="adjReason" placeholder="e.g. Stock count correction" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5 pt-4 border-t">
                <button @click="adjustModal=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button @click="submitAdjust()" :disabled="adjLoading"
                    class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:opacity-90 disabled:opacity-50 transition-colors"
                    x-text="adjLoading ? 'Processing...' : 'Confirm Adjustment'"></button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function stockPage() {
    return {
        // ── Tabs ──
        activeTab: 'overview',

        // ── Summary ──
        summary: {},

        // ── Pager ──
        pager: { loading: true, items: [], currentPage: 1, perPage: 10, total: 0, totalPages: 1,
            get visiblePages() { return [] }, fetchPage() { return Promise.resolve(false) },
            setSearch() {}, refresh() { return Promise.resolve(false) },
            goToPage() {}, changePerPage() {} },

        // ── Filters ──
        search: '', filterWarehouse: '', filterStatus: '', filterCategory: '', filterSupplier: '',
        warehouses: [], categories: [], suppliers: [], allProducts: [],

        // ── Movements ──
        movements: [], mvLoading: false, mvPage: 1, mvPerPage: 15, mvTotal: 0,
        mvWarehouse: '', mvType: '',

        // ── Adjust modal ──
        adjustModal: false, adjProduct: '', adjWarehouse: '', adjType: 'add',
        adjQty: 1, adjReason: '', adjLoading: false,

        // ── Helpers ──
        num(v) { return (v ?? 0).toLocaleString ? (v ?? 0).toLocaleString() : (v ?? 0) },
        money(v) { return ((v ?? 0) / 1).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
        fmtDate(d) { try { let dt = new Date(d); return dt.toLocaleDateString() + ' ' + dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) } catch (e) { return d || '' } },
        availColor(item) {
            let a = item.available_quantity || 0; let r = item.reorder_level || 10; let t = item.total_quantity || 0;
            if (t <= 0) return 'bg-gray-100 text-gray-500';
            if (a <= 0) return 'bg-red-100 text-red-700';
            if (a <= r) return 'bg-amber-100 text-amber-700';
            return 'bg-green-100 text-green-700';
        },
        statusColor(s) {
            if (s === 'In Stock') return 'bg-green-100 text-green-700';
            if (s === 'Low Stock') return 'bg-amber-100 text-amber-700';
            if (s === 'Out of Stock') return 'bg-red-100 text-red-700';
            if (s === 'All Reserved') return 'bg-orange-100 text-orange-700';
            return 'bg-gray-100 text-gray-600';
        },

        // ── Init ──
        async init() {
            try { this.pager = Alpine.store('pager').create({ endpoint: '/api/v1/stock/overview', perPage: 10 }) } catch (e) {
                console.error('Pager init:', e); this.pager.loading = false; return;
            }
            await Promise.all([this.fetchSummary(), this.fetchDependencies(), this.pager.fetchPage()]);
        },

        // ── Fetch summary ──
        async fetchSummary() {
            try {
                let d = await Alpine.store('api').get('/api/v1/stock/summary');
                if (d?.status && d.data) {
                    // Flatten: merge totals + status_counts into summary
                    let s = d.data;
                    this.summary = {
                        ...(s.totals || {}),
                        ...(s.status_counts || {}),
                        low_stock_alert: s.low_stock_alert || [],
                        by_warehouse: s.by_warehouse || [],
                    };
                }
            } catch (e) { console.error('Summary fetch error:', e); }
        },

        // ── Fetch warehouses, categories, suppliers, products ──
        async fetchDependencies() {
            try {
                let [w, c, s, p] = await Promise.all([
                    Alpine.store('api').get('/api/v1/warehouse', { per_page: 200 }),
                    Alpine.store('api').get('/api/v1/category', { per_page: 200 }),
                    Alpine.store('api').get('/api/v1/supplier', { per_page: 200 }),
                    Alpine.store('api').get('/api/v1/material', { per_page: 500 }),
                ]);
                this.warehouses = this.extract(w);
                this.categories = this.extract(c);
                this.suppliers = this.extract(s);
                this.allProducts = this.extract(p);
            } catch (e) { console.error('Dependencies fetch error:', e); }
        },

        extract(resp) {
            if (!resp?.status) return [];
            return Array.isArray(resp.data) ? resp.data : (resp.data?.data || []);
        },

        // ── Search ──
        doSearch() {
            let p = {};
            if (this.search) p.search = this.search;
            if (this.filterWarehouse) p.warehouse_id = this.filterWarehouse;
            if (this.filterStatus) p.status = this.filterStatus;
            if (this.filterCategory) p.category_id = this.filterCategory;
            if (this.filterSupplier) p.supplier_id = this.filterSupplier;
            try { this.pager.setSearch(p); this.fetchSummary(); } catch (e) {}
        },

        clearFilters() {
            this.search = this.filterWarehouse = this.filterStatus = this.filterCategory = this.filterSupplier = '';
            this.doSearch();
        },

        // ── Movements ──
        async loadMovements() {
            this.mvLoading = true;
            try {
                let p = { per_page: this.mvPerPage, page: this.mvPage };
                if (this.mvWarehouse) p.warehouse_id = this.mvWarehouse;
                if (this.mvType) p.type = this.mvType;
                let d = await Alpine.store('api').get('/api/v1/stock/movements', p);
                if (d?.status) {
                    this.movements = Array.isArray(d.data) ? d.data : (d.data?.data || []);
                    this.mvTotal = d.data?.total || d.data?.meta?.total || this.movements.length;
                }
            } catch (e) { /* silent */ }
            finally { this.mvLoading = false; }
        },

        // ── Adjust modal ──
        openAdjustModal() {
            this.adjProduct = ''; this.adjWarehouse = ''; this.adjType = 'add';
            this.adjQty = 1; this.adjReason = ''; this.adjustModal = true;
        },

        async submitAdjust() {
            if (!this.adjProduct || !this.adjWarehouse || !this.adjQty || this.adjQty < 1) {
                return alert('Please fill all required fields (product, warehouse, quantity).');
            }
            this.adjLoading = true;
            try {
                let d = await Alpine.store('api').post('/api/v1/stock/adjust', {
                    product_id: parseInt(this.adjProduct),
                    warehouse_id: parseInt(this.adjWarehouse),
                    quantity: parseInt(this.adjQty),
                    type: this.adjType,
                    reason: this.adjReason || null
                });
                if (d?.status) {
                    this.adjustModal = false;
                    try { Alpine.store('toast').success('Stock adjusted successfully!'); } catch (e) {}
                    this.pager.refresh();
                    this.fetchSummary();
                } else {
                    alert(d?.message || 'Adjustment failed.');
                }
            } catch (e) {
                alert('Error: ' + (e?.message || 'Network error'));
            } finally {
                this.adjLoading = false;
            }
        },
    };
}
</script>
@endpush
