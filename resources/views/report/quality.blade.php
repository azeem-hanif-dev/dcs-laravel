{{-- resources/views/report/quality.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quality Controller - Distributor Portal')

@section('page-content')
<div class="max-w-7xl mx-auto px-2 sm:px-4" x-data="qualityReportData()" x-init="init()">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Quality Controller (Inspections)</h1>
                <p class="text-sm text-gray-500 mt-1">Manage quality inspection reports</p>
            </div>
            <button @click="openAddModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:shadow-md transition-all text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Inspection
            </button>
        </div>

        <div class="p-4 border-b border-gray-100 bg-gray-50 flex flex-col sm:flex-row gap-3">
            <div class="relative max-w-xs w-full">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300="fetchData()" placeholder="Search by project, task, worker..." class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            </div>
            <select x-model="statusFilter" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="table-header-branded uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Sr. No</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">Worker</th>
                        <th class="px-4 py-3">Rating</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loading state --}}
                    <template x-if="loading">
                        <tr><td colspan="7" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="spinner w-10 h-10 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <div><p class="text-sm font-medium text-gray-400">Loading data...</p><p class="text-xs text-gray-400 mt-0.5">Please wait a moment</p></div>
                            </div>
                        </td></tr>
                    </template>
                    {{-- Empty state --}}
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="7" class="px-6 py-20 text-center">
                            <div class="max-w-sm mx-auto">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <h3 class="text-base font-semibold text-gray-400 mb-1">No Records Found</h3>
                                <p class="text-sm text-gray-400">No quality inspection reports available</p>
                            </div>
                        </td></tr>
                    </template>
                    {{-- Data rows --}}
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500" x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="item.project?.name || '-'"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="item.task?.title || item.task_id || '-'"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="item.worker?.name || item.worker_id || '-'"></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="item.rating >= 4 ? 'bg-green-100 text-green-700' : item.rating >= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'">
                                    <template x-for="i in 5">
                                        <svg class="w-3 h-3" :class="i <= item.rating ? 'text-current' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </template>
                                    <span x-text="item.rating" class="ml-1"></span>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="item.status === 'completed' ? 'bg-green-100 text-green-700' : item.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : item.status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'"
                                    x-text="item.status || '-'"></span>
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
                <h2 class="text-lg font-semibold text-gray-800" x-text="editingId ? 'Edit Inspection' : 'Add Inspection'"></h2>
                <button @click="closeModal()" class="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                    <select x-model="form.project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Project</option>
                        <template x-for="p in projects" :key="p.id">
                            <option :value="p.id" x-text="p.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task</label>
                    <select x-model="form.task_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Task</option>
                        <template x-for="t in tasks" :key="t.id">
                            <option :value="t.id" x-text="t.title || t.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Worker</label>
                    <select x-model="form.worker_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Worker</option>
                        <template x-for="w in workers" :key="w.id">
                            <option :value="w.id" x-text="w.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reviewer</label>
                    <select x-model="form.reviewer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Reviewer</option>
                        <template x-for="r in reviewers" :key="r.id">
                            <option :value="r.id" x-text="r.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating (1-5)</label>
                    <div class="flex items-center gap-1">
                        <template x-for="i in 5">
                            <button type="button" @click="form.rating = i" class="text-2xl transition-colors" :class="i <= form.rating ? 'text-yellow-400' : 'text-gray-300'" x-html="i <= form.rating ? '&#9733;' : '&#9734;'"></button>
                        </template>
                        <span class="ml-2 text-sm text-gray-500" x-text="form.rating + '/5'"></span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select x-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Type</option>
                        <option value="internal">Internal</option>
                        <option value="external">External</option>
                        <option value="client">Client</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
                    <textarea x-model="form.comments" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none resize-none" placeholder="Enter comments..."></textarea>
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
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Inspection</h3>
                <p class="text-sm text-gray-500 mb-6">Are you sure you want to delete this inspection? This action cannot be undone.</p>
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
function qualityReportData() {
    return {
        items: [],
        projects: [],
        tasks: [],
        workers: [],
        reviewers: [],
        search: '',
        statusFilter: '',
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
        form: { project_id: '', task_id: '', worker_id: '', reviewer_id: '', rating: 0, comments: '', status: 'pending', type: '' },
        toast: { show: false, message: '', type: 'success' },

        init() {
            this.fetchData();
            this.fetchProjects();
            this.fetchWorkers();
        },

        async fetchData() {
            this.loading = true;
            try {
                let params = { page: this.currentPage, per_page: this.perPage, search: this.search, status: this.statusFilter };
                let resp = await $store.api.get('/api/v1/quality-reports', params);
                if (resp && resp.status) {
                    let payload = resp.data;
                    if (payload && !Array.isArray(payload) && Array.isArray(payload.data)) {
                        this.items = payload.data;
                        this.totalItems = payload.total || 0;
                        this.currentPage = payload.current_page || 1;
                        this.perPage = payload.per_page || 10;
                        this.totalPages = payload.last_page || 1;
                    } else {
                        this.items = Array.isArray(payload) ? payload : [];
                        this.totalItems = this.items.length;
                        this.totalPages = Math.ceil(this.totalItems / this.perPage) || 1;
                    }
                }
            } catch(e) { console.error(e); $store.toast.error('Failed to fetch data'); } finally { this.loading = false; }
        },
        async fetchProjects() { try { let data = await $store.api.get('/api/v1/project', { per_page: 'all' }); this.projects = data.data || []; } catch(e) {} },
        async fetchTasks() {
            if (!this.form.project_id) { this.tasks = []; return; }
            try { let data = await $store.api.get('/api/v1/task', { project_id: this.form.project_id }); this.tasks = data.data || []; } catch(e) {}
        },
        async fetchWorkers() { try { let data = await $store.api.get('/api/v1/staff', { per_page: 'all' }); this.workers = data.data || []; this.reviewers = data.data || []; } catch(e) {} },

        openAddModal() {
            this.editingId = null;
            this.form = { project_id: '', task_id: '', worker_id: '', reviewer_id: '', rating: 0, comments: '', status: 'pending', type: '' };
            this.tasks = [];
            this.showModal = true;
        },

        openEditModal(item) {
            this.editingId = item.id;
            this.form = {
                project_id: item.project_id || '',
                task_id: item.task_id || '',
                worker_id: item.worker_id || '',
                reviewer_id: item.reviewer_id || '',
                rating: item.rating || 0,
                comments: item.comments || '',
                status: item.status || 'pending',
                type: item.type || ''
            };
            if (item.project_id) this.fetchTasks();
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.editingId = null;
            this.form = { project_id: '', task_id: '', worker_id: '', reviewer_id: '', rating: 0, comments: '', status: 'pending', type: '' };
        },

        async saveItem() {
            if (!this.form.project_id || !this.form.task_id || !this.form.worker_id) { $store.toast.error('Please fill all required fields'); return; }
            this.saving = true;
            try {
                let method = this.editingId ? 'PUT' : 'POST';
                let url = this.editingId ? '/api/v1/quality-reports/' + this.editingId : '/api/v1/quality-reports';
                let body = JSON.parse(JSON.stringify(this.form));
                if (method === 'PUT') body._method = 'PUT';
                await $store.api.post(url, body);
                $store.toast.success(this.editingId ? 'Inspection updated' : 'Inspection created');
                this.closeModal(); this.fetchData();
            } catch(e) { $store.toast.error(e.message || 'Save failed'); } finally { this.saving = false; }
        },
        confirmDelete(item) { this.deleteTarget = item; this.showDeleteModal = true; },
        async deleteItem() {
            this.deleting = true;
            try { await $store.api.delete('/api/v1/quality-reports/' + this.deleteTarget.id); $store.toast.success('Inspection deleted'); this.showDeleteModal = false; this.fetchData(); }
            catch(e) { $store.toast.error(e.message || 'Delete failed'); } finally { this.deleting = false; }
        },

        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.fetchData(); } },
        nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.fetchData(); } },
        goToPage(page) { this.currentPage = page; this.fetchData(); }
    }
}
</script>
@endpush
