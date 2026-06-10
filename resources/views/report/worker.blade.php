{{-- resources/views/report/worker.blade.php --}}
@extends('layouts.admin')

@section('title', 'Worker Reports - Distributor Portal')

@section('page-content')
<div class="max-w-7xl mx-auto px-2 sm:px-4" x-data="workerReportData()" x-init="init()">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h1 class="text-xl font-semibold text-gray-800">Worker Reports</h1>
            <p class="text-sm text-gray-500 mt-1">View worker attendance and check-in/out records</p>
        </div>

        <div class="p-4 border-b border-gray-100 bg-gray-50 flex flex-col sm:flex-row gap-3 flex-wrap">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300="fetchData()" placeholder="Search by worker name..." class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            </div>
            <select x-model="workerFilter" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All Workers</option>
                <template x-for="w in workers" :key="w.id">
                    <option :value="w.id" x-text="w.name"></option>
                </template>
            </select>
            <input type="date" x-model="dateFrom" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            <input type="date" x-model="dateTo" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            <select x-model="statusFilter" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All Statuses</option>
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="late">Late</option>
                <option value="half-day">Half Day</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="table-header-branded uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Sr. No</th>
                        <th class="px-4 py-3">Worker</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Check In</th>
                        <th class="px-4 py-3">Check Out</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">Loading...</td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No records found.</td></tr>
                    </template>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500" x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="item.worker?.name || item.worker_name || '-'"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="item.date || '-'"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="item.check_in || '-'"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="item.check_out || '-'"></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="item.status === 'present' ? 'bg-green-100 text-green-700' : item.status === 'absent' ? 'bg-red-100 text-red-700' : item.status === 'late' ? 'bg-yellow-100 text-yellow-700' : item.status === 'half-day' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700'"
                                    x-text="item.status || '-'"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="openEditModal(item)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="confirmDelete(item)" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
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

    {{-- Edit Modal --}}
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md" @click.outside="closeModal()">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Edit Worker Report</h2>
                <button @click="closeModal()" class="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="half-day">Half Day</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check In Time</label>
                    <input type="time" x-model="form.check_in" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check Out Time</label>
                    <input type="time" x-model="form.check_out" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" x-model="form.date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
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
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Worker Report</h3>
                <p class="text-sm text-gray-500 mb-6">Are you sure you want to delete this record?</p>
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
function workerReportData() {
    return {
        items: [],
        workers: [],
        search: '',
        workerFilter: '',
        dateFrom: '',
        dateTo: '',
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
        form: { check_in: '', check_out: '', date: '', status: 'present' },
        toast: { show: false, message: '', type: 'success' },

        init() {
            this.fetchData();
            this.fetchWorkers();
        },

        async fetchData() {
            this.loading = true;
            try {
                let params = { page: this.currentPage, per_page: this.perPage, search: this.search, worker_id: this.workerFilter, date_from: this.dateFrom, date_to: this.dateTo, status: this.statusFilter };
                let resp = await $store.api.get('/api/v1/worker-reports', params);
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
        async fetchWorkers() { try { let data = await $store.api.get('/api/v1/staff', { per_page: 'all' }); this.workers = data.data || []; } catch(e) {} },

        openEditModal(item) {
            this.editingId = item.id || item.checkId;
            this.form = {
                check_in: item.check_in || '',
                check_out: item.check_out || '',
                date: item.date || '',
                status: item.status || 'present'
            };
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.editingId = null;
            this.form = { check_in: '', check_out: '', date: '', status: 'present' };
        },

        async saveItem() {
            this.saving = true;
            try {
                let body = JSON.parse(JSON.stringify(this.form)); body._method = 'PUT';
                await $store.api.post('/api/v1/worker-reports/' + this.editingId, body);
                $store.toast.success('Record updated'); this.closeModal(); this.fetchData();
            } catch(e) { $store.toast.error(e.message || 'Update failed'); } finally { this.saving = false; }
        },
        confirmDelete(item) { this.deleteTarget = item; this.showDeleteModal = true; },
        async deleteItem() {
            this.deleting = true;
            try { await $store.api.delete('/api/v1/worker-reports/' + (this.deleteTarget.id || this.deleteTarget.checkId)); $store.toast.success('Record deleted'); this.showDeleteModal = false; this.fetchData(); }
            catch(e) { $store.toast.error(e.message || 'Delete failed'); } finally { this.deleting = false; }
        },

        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.fetchData(); } },
        nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.fetchData(); } },
        goToPage(page) { this.currentPage = page; this.fetchData(); }
    }
}
</script>
@endpush
