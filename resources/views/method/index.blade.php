{{-- resources/views/method/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Method Management - Digital Clean Solution')

@section('page-content')
<div class="max-w-7xl mx-auto px-2" x-data="methodData()" x-init="init()">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Method Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage cleaning methods and procedures</p>
            </div>
            <button @click="openAddModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:shadow-md transition-all text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Method
            </button>
        </div>

        <div class="p-4 border-b border-gray-100 bg-gray-50 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300="fetchData()" placeholder="Search methods..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            </div>
            <select x-model="categoryFilter" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All Categories</option>
                <template x-for="c in categories" :key="c.id">
                    <option :value="c.id" x-text="c.title || c.name"></option>
                </template>
            </select>
            <select x-model="activeFilter" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Sr. No</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Active</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">Loading...</td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No records found.</td></tr>
                    </template>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500" x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800" x-text="item.title"></div>
                                <div class="text-xs text-gray-500 truncate max-w-xs" x-text="item.description || ''"></div>
                            </td>
                            <td class="px-4 py-3 text-gray-600" x-text="item.category?.title || item.category?.name || item.category_id || '-'"></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="item.isActive || item.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                    x-text="item.isActive || item.is_active ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditModal(item)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="confirmDelete(item)" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-sm text-gray-500">
                Showing <span x-text="(currentPage - 1) * perPage + 1"></span> to <span x-text="Math.min(currentPage * perPage, totalItems)"></span> of <span x-text="totalItems"></span> entries
            </div>
            <div class="flex items-center gap-2">
                <button @click="prevPage()" :disabled="currentPage <= 1" class="px-3 py-1.5 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Previous</button>
                <template x-for="page in totalPages" :key="page">
                    <button @click="goToPage(page)" :class="page === currentPage ? 'bg-primary text-white' : 'border border-gray-300 hover:bg-gray-50'" class="px-3 py-1.5 text-sm rounded-md transition-colors" x-text="page"></button>
                </template>
                <button @click="nextPage()" :disabled="currentPage >= totalPages" class="px-3 py-1.5 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Next</button>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800" x-text="editingId ? 'Edit Method' : 'Add Method'"></h2>
                <button @click="closeModal()" class="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="Enter method title">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="form.description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none resize-none" placeholder="Enter method description..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Video Link</label>
                    <input type="url" x-model="form.videoLink" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="https://...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select x-model="form.category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Category</option>
                        <template x-for="c in categories" :key="c.id">
                            <option :value="c.id" x-text="c.title || c.name"></option>
                        </template>
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Active</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="form.isActive" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="saveItem()" :disabled="saving" class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:shadow-md transition-all disabled:opacity-50" x-text="saving ? 'Saving...' : 'Save'"></button>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.outside="showDeleteModal = false">
            <div class="p-6 text-center">
                <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Method</h3>
                <p class="text-sm text-gray-500 mb-6">Are you sure you want to delete this method? This action cannot be undone.</p>
                <div class="flex items-center justify-center gap-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                    <button @click="deleteItem()" :disabled="deleting" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50" x-text="deleting ? 'Deleting...' : 'Delete'"></button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-transition.duration.300ms class="fixed bottom-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white"
        :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'" x-text="toast.message" x-init="if (toast.show) { setTimeout(() => toast.show = false, 3000) }"></div>
</div>
@endsection

@push('scripts')
<script>
function methodData() {
    return {
        items: [],
        categories: [],
        search: '',
        categoryFilter: '',
        activeFilter: '',
        currentPage: 1,
        perPage: 10,
        totalItems: 0,
        totalPages: 1,
        loading: false,
        saving: false,
        deleting: false,
        showModal: false,
        showDeleteModal: false,
        editingId: null,
        deleteTarget: null,
        form: { title: '', description: '', videoLink: '', category_id: '', isActive: true },
        toast: { show: false, message: '', type: 'success' },

        init() {
            this.fetchData();
            this.fetchCategories();
        },

        getHeaders() {
            let token = localStorage.getItem('S_S_Token');
            return { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' };
        },

        async fetchData() {
            this.loading = true;
            try {
                let params = new URLSearchParams({ page: this.currentPage, per_page: this.perPage, search: this.search });
                if (this.categoryFilter) { params.set('category_id', this.categoryFilter); }
                if (this.activeFilter !== '') { params.set('isActive', this.activeFilter); }
                let res = await fetch('/api/v1/method?' + params, { headers: this.getHeaders() });
                let data = await res.json();
                this.items = data.data || [];
                this.totalItems = data.total || data.meta?.total || 0;
                this.currentPage = data.current_page || data.meta?.current_page || 1;
                this.perPage = data.per_page || data.meta?.per_page || 10;
                this.totalPages = data.last_page || data.meta?.last_page || 1;
            } catch(e) {
                this.showToast('Failed to fetch data', 'error');
            } finally {
                this.loading = false;
            }
        },

        async fetchCategories() {
            try {
                let res = await fetch('/api/v1/method-category?per_page=all', { headers: this.getHeaders() });
                let data = await res.json();
                this.categories = data.data || [];
            } catch(e) { console.error('Failed to fetch categories', e); }
        },

        openAddModal() {
            this.editingId = null;
            this.form = { title: '', description: '', videoLink: '', category_id: '', isActive: true };
            this.showModal = true;
        },

        openEditModal(item) {
            this.editingId = item.id;
            this.form = {
                title: item.title || '',
                description: item.description || '',
                videoLink: item.videoLink || item.video_link || '',
                category_id: item.category_id || item.category?.id || '',
                isActive: item.isActive || item.is_active || false
            };
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.editingId = null;
            this.form = { title: '', description: '', videoLink: '', category_id: '', isActive: true };
        },

        async saveItem() {
            if (!this.form.title) {
                this.showToast('Title is required', 'error');
                return;
            }
            this.saving = true;
            try {
                let url = this.editingId ? '/api/v1/method/' + this.editingId : '/api/v1/method';
                let method = this.editingId ? 'PUT' : 'POST';
                let body = {
                    title: this.form.title,
                    description: this.form.description,
                    videoLink: this.form.videoLink,
                    category_id: this.form.category_id,
                    isActive: this.form.isActive ? 1 : 0
                };
                if (method === 'PUT') { body._method = 'PUT'; }
                let res = await fetch(url, { method: 'POST', headers: this.getHeaders(), body: JSON.stringify(body) });
                let data = await res.json();
                if (res.ok) {
                    this.showToast(this.editingId ? 'Method updated' : 'Method created', 'success');
                    this.closeModal();
                    this.fetchData();
                } else {
                    this.showToast(data.message || 'Save failed', 'error');
                }
            } catch(e) {
                this.showToast('Save failed', 'error');
            } finally {
                this.saving = false;
            }
        },

        confirmDelete(item) {
            this.deleteTarget = item;
            this.showDeleteModal = true;
        },

        async deleteItem() {
            this.deleting = true;
            try {
                let res = await fetch('/api/v1/method/' + this.deleteTarget.id, { method: 'DELETE', headers: this.getHeaders() });
                if (res.ok) {
                    this.showToast('Method deleted', 'success');
                    this.showDeleteModal = false;
                    this.fetchData();
                } else {
                    let data = await res.json();
                    this.showToast(data.message || 'Delete failed', 'error');
                }
            } catch(e) {
                this.showToast('Delete failed', 'error');
            } finally {
                this.deleting = false;
            }
        },

        showToast(message, type) {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 3000);
        },

        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.fetchData(); } },
        nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.fetchData(); } },
        goToPage(page) { this.currentPage = page; this.fetchData(); }
    }
}
</script>
@endpush
