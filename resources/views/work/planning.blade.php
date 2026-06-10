{{-- resources/views/work/planning.blade.php --}}
@extends('layouts.admin')

@section('title', 'Worker Planning - Distributor Portal')

@section('page-content')
<div x-data="workerPlanningData()" x-init="fetchDependencies().then(() => fetchWorkPlans())" class="max-w-7xl mx-auto px-2 sm:px-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Worker Planning</h1>
        <div class="flex items-center gap-3">
            <select x-model="filterWorkerId" @change="currentPage = 1"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="">All Workers</option>
                <template x-for="w in workers" :key="w.id">
                    <option :value="w.id" x-text="w.name"></option>
                </template>
            </select>
            <select x-model="filterProjectId" @change="currentPage = 1"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="">All Projects</option>
                <template x-for="p in projects" :key="p.id">
                    <option :value="p.id" x-text="p.name"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 mb-3">
        <div class="relative max-w-xs w-full">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" @input="currentPage = 1"
                placeholder="Search planning..."
                class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="table-header-branded">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Sr. No</th>
                        <th class="px-4 py-3 font-semibold">Worker</th>
                        <th class="px-4 py-3 font-semibold">Project</th>
                        <th class="px-4 py-3 font-semibold">Job</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Days</th>
                        <th class="px-4 py-3 font-semibold text-center">Action</th>
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
                    <template x-if="!loading && paginatedPlans.length === 0">
                        <tr><td colspan="7" class="px-6 py-20 text-center">
                            <div class="max-w-sm mx-auto">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <h3 class="text-base font-semibold text-gray-400 mb-1">No Worker Plans Found</h3>
                                <p class="text-sm text-gray-400">Select filters to view worker assignments</p>
                            </div>
                        </td></tr>
                    </template>
                    {{-- Data rows --}}
                    <template x-for="(item, index) in paginatedPlans" :key="item.id">
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-600" x-text="(currentPage - 1) * pageSize + index + 1"></td>
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="item.worker?.name || ''"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="item.project?.name || ''"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="item.job?.name || ''"></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                    :class="{
                                        'bg-blue-100 text-blue-700': item.job_type === 'daily',
                                        'bg-green-100 text-green-700': item.job_type === 'weekly',
                                        'bg-purple-100 text-purple-700': item.job_type === 'onetime',
                                        'bg-orange-100 text-orange-700': item.job_type === 'extra'
                                    }" x-text="item.job_type || ''"></span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <span x-text="Array.isArray(item.days) ? item.days.join(', ') : (item.days || '-')"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(item)" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(item)" class="text-red-600 hover:text-red-800 p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 border-t border-gray-100 gap-3" x-show="totalPages > 1">
            <span class="text-sm text-gray-600" x-text="'Showing ' + ((currentPage - 1) * pageSize + 1) + '-' + Math.min(currentPage * pageSize, filteredPlans.length) + ' of ' + filteredPlans.length"></span>
            <div class="flex items-center gap-1">
                <button @click="currentPage = 1" :disabled="currentPage === 1"
                    class="px-3 py-1.5 text-sm rounded-md border border-gray-300 disabled:opacity-50 hover:bg-gray-100 transition-colors">First</button>
                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="px-3 py-1.5 text-sm rounded-md border border-gray-300 disabled:opacity-50 hover:bg-gray-100 transition-colors">Prev</button>
                <template x-for="page in visiblePages" :key="page">
                    <button @click="currentPage = page" :class="currentPage === page ? 'bg-primary text-white' : 'border border-gray-300 hover:bg-gray-100'"
                        class="px-3 py-1.5 text-sm rounded-md transition-colors" x-text="page"></button>
                </template>
                <button @click="currentPage++" :disabled="currentPage === totalPages"
                    class="px-3 py-1.5 text-sm rounded-md border border-gray-300 disabled:opacity-50 hover:bg-gray-100 transition-colors">Next</button>
                <button @click="currentPage = totalPages" :disabled="currentPage === totalPages"
                    class="px-3 py-1.5 text-sm rounded-md border border-gray-300 disabled:opacity-50 hover:bg-gray-100 transition-colors">Last</button>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="bg-primary text-white px-6 py-4 rounded-t-2xl flex justify-between items-center sticky top-0 z-10">
                <h2 class="text-lg font-semibold">Edit Work Plan</h2>
                <button @click="closeModal()" class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveWorkPlan()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project *</label>
                    <select x-model="form.projectId" required @change="fetchJobs(form.projectId)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select project</option>
                        <template x-for="p in projects" :key="p.id">
                            <option :value="p.id" x-text="p.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job *</label>
                    <select x-model="form.jobId" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select job</option>
                        <template x-for="j in jobs" :key="j.id">
                            <option :value="j.id" x-text="j.name || j.job_def?.name || ''"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Worker *</label>
                    <select x-model="form.workerId" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select worker</option>
                        <template x-for="w in workers" :key="w.id">
                            <option :value="w.id" x-text="w.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Type *</label>
                    <select x-model="form.jobType" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select type</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="onetime">One-time</option>
                        <option value="extra">Extra</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" x-model="form.date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                {{-- Days checkboxes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Days</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="day in dayOptions" :key="day.value">
                            <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 cursor-pointer hover:bg-gray-50 transition-colors"
                                :class="{ 'bg-primary text-white border-primary': form.days.includes(day.value) }">
                                <input type="checkbox" :value="day.value" x-model="form.days" class="sr-only">
                                <span x-text="day.label"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weeks (comma separated)</label>
                    <input type="text" x-model="form.weeks" placeholder="e.g. 1,2,3,4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select status</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" :disabled="saving" class="bg-primary text-white px-5 py-2.5 rounded-lg hover:bg-primary/90 transition-colors shadow-md disabled:opacity-50" x-text="saving ? 'Saving...' : 'Save'"></button>
                </div>
                <div x-show="errorMsg" x-text="errorMsg" class="text-red-600 text-sm text-center"></div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="deleteModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this work plan?</p>
            <div class="flex justify-center gap-3">
                <button @click="deleteModalOpen = false" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                <button @click="deleteWorkPlan()" :disabled="saving" class="bg-red-600 text-white px-5 py-2.5 rounded-lg hover:bg-red-700 transition-colors shadow-md disabled:opacity-50" x-text="saving ? 'Deleting...' : 'Delete'"></button>
            </div>
            <div x-show="errorMsg" x-text="errorMsg" class="text-red-600 text-sm mt-3"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function workerPlanningData() {
    return {
        workPlans: [],
        projects: [],
        workers: [],
        jobs: [],
        search: '',
        filterWorkerId: '',
        filterProjectId: '',
        loading: false,
        saving: false,
        errorMsg: '',
        modalOpen: false,
        deleteModalOpen: false,
        editId: null,
        deleteTarget: null,
        currentPage: 1,
        pageSize: 10,
        dayOptions: [
            { value: 'mon', label: 'Mon' },
            { value: 'tue', label: 'Tue' },
            { value: 'wed', label: 'Wed' },
            { value: 'thu', label: 'Thu' },
            { value: 'fri', label: 'Fri' },
            { value: 'sat', label: 'Sat' },
            { value: 'sun', label: 'Sun' }
        ],
        form: { projectId: '', jobId: '', workerId: '', jobType: '', days: [], weeks: '', date: '', status: '' },



        get filteredPlans() {
            let list = this.workPlans;
            if (this.filterWorkerId) {
                list = list.filter(w => w.worker_id == this.filterWorkerId || w.workerId == this.filterWorkerId);
            }
            if (this.filterProjectId) {
                list = list.filter(w => w.project_id == this.filterProjectId || w.projectId == this.filterProjectId);
            }
            const q = this.search.toLowerCase();
            if (!q) return list;
            return list.filter(w =>
                (w.worker?.name || '').toLowerCase().includes(q) ||
                (w.project?.name || '').toLowerCase().includes(q) ||
                (w.job?.name || '').toLowerCase().includes(q) ||
                (w.job_type || '').toLowerCase().includes(q)
            );
        },

        get totalPages() {
            return Math.ceil(this.filteredPlans.length / this.pageSize) || 1;
        },

        get paginatedPlans() {
            const start = (this.currentPage - 1) * this.pageSize;
            return this.filteredPlans.slice(start, start + this.pageSize);
        },

        get visiblePages() {
            const total = this.totalPages;
            const current = this.currentPage;
            const pages = [];
            let start = Math.max(1, current - 2);
            let end = Math.min(total, current + 2);
            if (end - start < 4) {
                if (start === 1) end = Math.min(total, start + 4);
                else start = Math.max(1, end - 4);
            }
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },

        async fetchDependencies() {
            try {
                const [projData, staffData] = await Promise.all([$store.api.get('/api/v1/project'), $store.api.get('/api/v1/staff')]);
                if (projData.status) this.projects = Array.isArray(projData.data) ? projData.data : [];
                if (staffData.status) this.workers = Array.isArray(staffData.data) ? staffData.data : [];
            } catch (e) { console.error('Failed to load dependencies:', e); }
        },
        async fetchJobs(projectId) { this.jobs = []; if (!projectId) return; try { let data = await $store.api.get('/api/v1/job/project/'+projectId); if (data.status) this.jobs = Array.isArray(data.data)?data.data:[]; } catch(e){} },
        async fetchWorkPlans() {
            this.loading = true; this.errorMsg = '';
            try { let data = await $store.api.get('/api/v1/work'); if (data.status) this.workPlans = Array.isArray(data.data)?data.data:[]; else this.errorMsg = data.message||'Failed to load'; }
            catch(e) { this.errorMsg = 'Network error: '+e.message; } this.loading = false;
        },

        openEditModal(item) {
            this.editId = item.id;
            this.errorMsg = '';
            this.jobs = [];
            this.form = {
                projectId: item.project_id || item.projectId || '',
                jobId: item.job_id || item.jobId || '',
                workerId: item.worker_id || item.workerId || '',
                jobType: item.job_type || '',
                days: Array.isArray(item.days) ? item.days : (item.days ? item.days.split(',') : []),
                weeks: Array.isArray(item.weeks) ? item.weeks.join(',') : (item.weeks || ''),
                date: item.date || '',
                status: item.status || ''
            };
            if (this.form.projectId) this.fetchJobs(this.form.projectId);
            this.modalOpen = true;
        },

        openDeleteModal(item) {
            this.deleteTarget = item;
            this.errorMsg = '';
            this.deleteModalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
            this.editId = null;
            this.errorMsg = '';
        },

        async saveWorkPlan() {
            this.saving = true; this.errorMsg = '';
            const body = { ...this.form, weeks: this.form.weeks ? this.form.weeks.split(',').map(s=>s.trim()).filter(Boolean) : [] };
            try { await $store.api.put('/api/v1/work/'+this.editId, body); this.closeModal(); $store.toast.success('Work plan updated'); this.fetchWorkPlans(); }
            catch(e) { this.errorMsg = e.message||'Save failed'; } this.saving = false;
        },
        async deleteWorkPlan() {
            if (!this.deleteTarget) return;
            this.saving = true; this.errorMsg = '';
            try { await $store.api.delete('/api/v1/work/'+this.deleteTarget.id); this.deleteModalOpen = false; this.deleteTarget = null; $store.toast.success('Work plan deleted'); this.fetchWorkPlans(); }
            catch(e) { this.errorMsg = e.message||'Delete failed'; } this.saving = false;
        }
    };
}
</script>
@endpush
