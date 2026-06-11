{{-- resources/views/report/project.blade.php --}}
@extends('layouts.admin')

@section('title', 'Project Reports - Distributor Portal')

@section('page-content')
<div class="max-w-7xl mx-auto px-2 sm:px-4" x-data="projectReportData()" x-init="init()">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h1 class="text-xl font-semibold text-gray-800">Project Reports</h1>
            <p class="text-sm text-gray-500 mt-1">View project summary and detail reports</p>
        </div>

        <div class="p-4 border-b border-gray-100 bg-gray-50 flex flex-col sm:flex-row gap-3">
            <div class="relative max-w-xs w-full">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300="fetchData()" placeholder="Search projects..." class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            </div>
            <select x-model="projectFilter" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All Projects</option>
                <template x-for="p in projects" :key="p.id">
                    <option :value="p.id" x-text="p.name"></option>
                </template>
            </select>
            <select x-model="statusFilter" @change="fetchData()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On Hold</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="table-header-branded uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Sr. No</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">Details</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loading state --}}
                    <template x-if="loading">
                        <tr><td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="spinner w-10 h-10 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <div><p class="text-sm font-medium text-gray-400">Loading data...</p><p class="text-xs text-gray-400 mt-0.5">Please wait a moment</p></div>
                            </div>
                        </td></tr>
                    </template>
                    {{-- Empty state --}}
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="5" class="px-6 py-20 text-center">
                            <div class="max-w-sm mx-auto">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                <h3 class="text-base font-semibold text-gray-400 mb-1">No Records Found</h3>
                                <p class="text-sm text-gray-400">No project reports available to display</p>
                            </div>
                        </td></tr>
                    </template>
                    {{-- Data rows --}}
                    <template x-for="(item, index) in items" :key="item.id || index">
                        <tr class="border-t border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500" x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800" x-text="item.project?.name || item.name || '-'"></div>
                                <div class="text-xs text-gray-500" x-text="item.project?.location || item.location || ''"></div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <div class="text-sm" x-text="item.details || item.description || '-'"></div>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span class="text-xs text-gray-500" x-show="item.total_tasks || item.totalTasks">
                                        Tasks: <span class="font-medium" x-text="item.total_tasks || item.totalTasks || 0"></span>
                                    </span>
                                    <span class="text-xs text-gray-500" x-show="item.total_workers || item.totalWorkers">
                                        Workers: <span class="font-medium" x-text="item.total_workers || item.totalWorkers || 0"></span>
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="item.status === 'active' ? 'bg-green-100 text-green-700' : item.status === 'completed' ? 'bg-blue-100 text-blue-700' : item.status === 'on-hold' ? 'bg-yellow-100 text-yellow-700' : item.status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'"
                                    x-text="item.status || '-'"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="viewDetails(item)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button @click="downloadPdf(item)" class="p-1.5 text-green-600 hover:bg-green-50 rounded-md transition-colors" title="Download PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
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

    {{-- Details Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Project Details</h2>
                <button @click="closeModal()" class="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Project Name</label>
                        <p class="text-sm text-gray-800" x-text="detailItem.project?.name || detailItem.name || '-'"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Status</label>
                        <p x-text="detailItem.status || '-'" class="text-sm font-semibold"
                            :class="detailItem.status === 'active' ? 'text-green-600' : detailItem.status === 'completed' ? 'text-blue-600' : detailItem.status === 'on-hold' ? 'text-yellow-600' : detailItem.status === 'cancelled' ? 'text-red-600' : 'text-gray-600'"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Location</label>
                        <p class="text-sm text-gray-800" x-text="detailItem.project?.location || detailItem.location || '-'"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Start Date</label>
                        <p class="text-sm text-gray-800" x-text="detailItem.project?.start_date || detailItem.start_date || '-'"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">End Date</label>
                        <p class="text-sm text-gray-800" x-text="detailItem.project?.end_date || detailItem.end_date || '-'"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Total Tasks</label>
                        <p class="text-sm text-gray-800" x-text="detailItem.total_tasks || detailItem.totalTasks || 0"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Total Workers</label>
                        <p class="text-sm text-gray-800" x-text="detailItem.total_workers || detailItem.totalWorkers || 0"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Completion</label>
                        <p class="text-sm text-gray-800" x-text="(detailItem.completion_percentage || detailItem.completionPercentage || 0) + '%'"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Description</label>
                    <p class="text-sm text-gray-800" x-text="detailItem.details || detailItem.description || '-'"></p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <button @click="closeModal()" class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:shadow-md transition-all">Close</button>
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
function projectReportData() {
    return {
        items: [],
        projects: [],
        detailItem: {},
        search: '',
        projectFilter: '',
        statusFilter: '',
        currentPage: 1,
        perPage: 10,
        totalItems: 0,
        totalPages: 1,
        loading: false,
        showModal: false,
        toast: { show: false, message: '', type: 'success' },

        init() {
            this.fetchData();
            this.fetchProjects();
        },

        async fetchData() {
            this.loading = true;
            try {
                let params = { page: this.currentPage, per_page: this.perPage, search: this.search, status: this.statusFilter };
                if (this.projectFilter) params.projectId = this.projectFilter;
                let resp = await Alpine.store('api').get('/api/v1/project-reports/by-project', params);
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
            } catch(e) { console.error(e); Alpine.store('toast').error('Failed to fetch data'); } finally { this.loading = false; }
        },
        async fetchProjects() { try { let data = await Alpine.store('api').get('/api/v1/project', { per_page: 500 }); this.projects = data.data || []; } catch(e) {} },

        viewDetails(item) {
            this.detailItem = item;
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.detailItem = {};
        },

        async downloadPdf(item) {
            try {
                let res = await Alpine.store('api').fetch('/api/v1/project-reports/pdf', {
                    method: 'POST',
                    body: JSON.stringify({ project_id: item.project?.id || item.id, projectId: item.project?.id || item.id })
                });
                if (res.ok) {
                    let blob = await res.blob(); let url = window.URL.createObjectURL(blob); let a = document.createElement('a'); a.href = url; a.download = 'project-report-' + (item.project?.name || item.name || 'report') + '.pdf'; document.body.appendChild(a); a.click(); a.remove(); window.URL.revokeObjectURL(url);
                } else { Alpine.store('toast').error('Failed to download PDF'); }
            } catch(e) { Alpine.store('toast').error('Failed to download PDF'); }
        },

        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.fetchData(); } },
        nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.fetchData(); } },
        goToPage(page) { this.currentPage = page; this.fetchData(); }
    }
}
</script>
@endpush
