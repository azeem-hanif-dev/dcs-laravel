{{-- resources/views/material/order.blade.php --}}
@extends('layouts.admin')

@section('title', 'Material Order Management - Digital Clean Solution')

@section('page-content')
<div x-data="orderData()" x-init="init()" class="max-w-7xl mx-auto px-4">

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="relative w-full sm:w-72">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" @input.debounce.300ms="fetchItems()" placeholder="Search orders..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
        </div>
        <button @click="openAddModal()"
            class="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all duration-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Order
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-primary">
                    <tr>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Sr. No</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Date</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Notes</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-if="items.length === 0">
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No orders found.</td></tr>
                    </template>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 text-sm text-gray-700 whitespace-nowrap" x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td class="px-4 py-4 text-sm text-gray-900 whitespace-nowrap" x-text="item.orderDate || ''"></td>
                            <td class="px-4 py-4 text-sm whitespace-nowrap">
                                <span :class="statusBadgeClass(item.status)"
                                    class="px-2.5 py-1 rounded-full text-xs font-medium" x-text="item.status"></span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 max-w-[250px] truncate" x-text="item.notes || ''"></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <button @click="openStatusModal(item)" class="text-yellow-600 hover:text-yellow-800 bg-yellow-50 hover:bg-yellow-100 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">Status</button>
                                    <button @click="openEditModal(item)" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">Edit</button>
                                    <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4" x-show="totalPages > 1">
            <span class="text-sm text-gray-600" x-text="'Showing ' + ((currentPage-1)*perPage+1) + ' to ' + Math.min(currentPage*perPage, total) + ' of ' + total"></span>
            <div class="flex items-center gap-2">
                <button @click="changePage(1)" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 transition-colors">First</button>
                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 transition-colors">Prev</button>
                <template x-for="page in visiblePages" :key="page">
                    <button @click="changePage(page)" :class="page === currentPage ? 'bg-primary text-white' : 'border border-gray-300 hover:bg-gray-100'"
                        class="px-3 py-1.5 text-sm rounded-lg transition-colors" x-text="page"></button>
                </template>
                <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 transition-colors">Next</button>
                <button @click="changePage(totalPages)" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 transition-colors">Last</button>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" x-transition.opacity>
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="modalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-800" x-text="isEditing ? 'Edit Order' : 'Add Order'"></h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveItem()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordered By</label>
                    <input type="text" x-model="form.ordered_by" placeholder="Ordered by"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Date</label>
                    <input type="date" x-model="form.orderDate"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="form.status"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Fulfilled">Fulfilled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea x-model="form.notes" rows="3" placeholder="Order notes..."
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none resize-none"></textarea>
                </div>
                {{-- Order Items --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Items</label>
                    <div class="space-y-3 mb-3">
                        <template x-for="(oi, idx) in form.items" :key="idx">
                            <div class="flex items-end gap-3 bg-gray-50 p-3 rounded-xl">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">Material</label>
                                    <select x-model="oi.material_id"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                                        <option value="">Select Material</option>
                                        <template x-for="mat in materials" :key="mat.id">
                                            <option :value="mat.id" x-text="mat.materialName"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="w-20">
                                    <label class="block text-xs text-gray-500 mb-1">Qty</label>
                                    <input type="number" x-model="oi.quantity" placeholder="1" min="1"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs text-gray-500 mb-1">Price</label>
                                    <input type="number" step="0.01" x-model="oi.price" placeholder="0.00" min="0"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                                </div>
                                <button type="button" @click="removeOrderItem(idx)"
                                    class="text-red-500 hover:text-red-700 mb-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addOrderItem()"
                        class="text-sm text-primary hover:text-primary-dark font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Item
                    </button>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="modalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" :disabled="saving"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl transition-colors disabled:opacity-50" x-text="isEditing ? 'Update' : 'Save'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Status Update Modal --}}
    <div x-show="statusModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="statusModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-800">Update Status</h3>
                <button @click="statusModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="updateStatus()">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="statusForm.status"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Fulfilled">Fulfilled</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="statusModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" :disabled="saving"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl transition-colors disabled:opacity-50">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="deleteModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <svg class="w-12 h-12 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Order?</h3>
            <p class="text-sm text-gray-500 mb-6">This action cannot be undone.</p>
            <div class="flex justify-center gap-3">
                <button @click="deleteModalOpen = false"
                    class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                <button @click="deleteItem()" :disabled="saving"
                    class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50">Delete</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function orderData() {
    return {
        items: [],
        materials: [],
        search: '',
        currentPage: 1,
        perPage: 10,
        total: 0,
        totalPages: 1,
        modalOpen: false,
        statusModalOpen: false,
        deleteModalOpen: false,
        isEditing: false,
        saving: false,
        form: { id: null, ordered_by: '', orderDate: '', status: '', notes: '', items: [] },
        statusForm: { id: null, status: 'Pending' },
        errors: {},
        deleteId: null,

        get visiblePages() {
            let pages = [];
            let start = Math.max(1, this.currentPage - 2);
            let end = Math.min(this.totalPages, this.currentPage + 2);
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },

        async init() {
            await this.fetchMaterials();
            this.fetchItems();
        },

        async fetchItems() {
            try {
                let token = localStorage.getItem('S_S_Token');
                let params = new URLSearchParams({ page: this.currentPage, per_page: this.perPage });
                if (this.search) params.append('search', this.search);
                let res = await fetch('/api/v1/material-order?' + params.toString(), {
                    headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' }
                });
                let data = await res.json();
                if (data.status || data.success) {
                    this.items = data.data?.data || data.data || [];
                    this.total = data.data?.total || data.total || this.items.length;
                    this.totalPages = data.data?.last_page || data.last_page || Math.ceil(this.total / this.perPage) || 1;
                }
            } catch (e) { console.error('Fetch error:', e); }
        },

        async fetchMaterials() {
            try {
                let token = localStorage.getItem('S_S_Token');
                let res = await fetch('/api/v1/material?per_page=100', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' }
                });
                let data = await res.json();
                this.materials = data.data?.data || data.data || [];
            } catch (e) { console.error('Materials fetch error:', e); }
        },

        statusBadgeClass(status) {
            switch (status) {
                case 'Approved': return 'bg-blue-100 text-blue-800';
                case 'Pending': return 'bg-yellow-100 text-yellow-800';
                case 'Rejected': return 'bg-red-100 text-red-800';
                case 'Fulfilled': return 'bg-green-100 text-green-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        },

        addOrderItem() {
            this.form.items.push({ material_id: '', quantity: 1, price: 0 });
        },

        removeOrderItem(idx) {
            this.form.items.splice(idx, 1);
        },

        openAddModal() {
            this.isEditing = false;
            this.form = { id: null, ordered_by: '', orderDate: '', status: '', notes: '', items: [] };
            this.errors = {};
            this.modalOpen = true;
        },

        openEditModal(item) {
            this.isEditing = true;
            this.form = {
                id: item.id,
                ordered_by: item.ordered_by || '',
                orderDate: item.orderDate || '',
                status: item.status || '',
                notes: item.notes || '',
                items: (item.items || []).map(i => ({
                    material_id: i.material_id || i.materialId || '',
                    quantity: i.quantity || 1,
                    price: i.price || 0
                }))
            };
            this.errors = {};
            this.modalOpen = true;
        },

        async saveItem() {
            this.errors = {};
            this.saving = true;
            try {
                let token = localStorage.getItem('S_S_Token');
                let url = '/api/v1/material-order';
                let method = 'POST';
                if (this.isEditing) { url += '/' + this.form.id; method = 'PUT'; }
                let res = await fetch(url, {
                    method: method,
                    headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                let data = await res.json();
                if (res.ok) { this.modalOpen = false; this.fetchItems(); } else {
                    if (data.errors) this.errors = data.errors;
                    else if (data.message) alert(data.message);
                }
            } catch (e) { console.error('Save error:', e); }
            this.saving = false;
        },

        openStatusModal(item) {
            this.statusForm = { id: item.id, status: item.status || 'Pending' };
            this.statusModalOpen = true;
        },

        async updateStatus() {
            this.saving = true;
            try {
                let token = localStorage.getItem('S_S_Token');
                let res = await fetch('/api/v1/material-order/' + this.statusForm.id + '/status', {
                    method: 'PUT',
                    headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ status: this.statusForm.status })
                });
                let data = await res.json();
                if (res.ok) { this.statusModalOpen = false; this.fetchItems(); } else {
                    if (data.message) alert(data.message);
                }
            } catch (e) { console.error('Status update error:', e); }
            this.saving = false;
        },

        confirmDelete(item) {
            this.deleteId = item.id;
            this.deleteModalOpen = true;
        },

        async deleteItem() {
            this.saving = true;
            try {
                let token = localStorage.getItem('S_S_Token');
                let res = await fetch('/api/v1/material-order/' + this.deleteId, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                if (res.ok) { this.deleteModalOpen = false; this.fetchItems(); }
                else { let data = await res.json(); if (data.message) alert(data.message); }
            } catch (e) { console.error('Delete error:', e); }
            this.saving = false;
        },

        changePage(page) {
            if (page >= 1 && page <= this.totalPages) { this.currentPage = page; this.fetchItems(); }
        }
    }
}
</script>
@endpush
