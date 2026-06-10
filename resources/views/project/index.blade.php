{{-- resources/views/project/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Project Management - Digital Clean Solution')

@section('page-content')
<div x-data="projectData()" x-init="fetchProjects()" class="max-w-7xl mx-auto px-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Project Management</h1>
        <button @click="openAddModal()"
            class="bg-primary text-white px-5 py-2.5 rounded-lg hover:bg-primary/90 transition-colors shadow-md flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Project
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" @input="currentPage = 1"
                placeholder="Search projects..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Sr. No</th>
                        <th class="px-4 py-3 font-semibold">Name</th>
                        <th class="px-4 py-3 font-semibold">Customer</th>
                        <th class="px-4 py-3 font-semibold">Start</th>
                        <th class="px-4 py-3 font-semibold">End</th>
                        <th class="px-4 py-3 font-semibold">Location</th>
                        <th class="px-4 py-3 font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(project, index) in paginatedProjects" :key="project.id">
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-600" x-text="(currentPage - 1) * pageSize + index + 1"></td>
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="project.name"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="project.customer?.name || ''"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="project.start_date || project.startDate || ''"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="project.end_date || project.endDate || ''"></td>
                            <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate" x-text="project.location_url || project.locationUrl || ''"></td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(project)" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(project)" class="text-red-600 hover:text-red-800 p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="paginatedProjects.length === 0 && !loading">
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">No projects found.</td>
                    </tr>
                    <tr x-show="loading">
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center px-4 py-3 border-t border-gray-100 gap-3" x-show="totalPages > 1">
            <span class="text-sm text-gray-600" x-text="'Showing ' + ((currentPage - 1) * pageSize + 1) + '-' + Math.min(currentPage * pageSize, filteredProjects.length) + ' of ' + filteredProjects.length"></span>
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

    {{-- Add/Edit Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="bg-primary text-white px-6 py-4 rounded-t-2xl flex justify-between items-center sticky top-0 z-10">
                <h2 class="text-lg font-semibold" x-text="editId ? 'Edit Project' : 'Add Project'"></h2>
                <button @click="closeModal()" class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveProject()" class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" x-model="form.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                        <select x-model="form.customerId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select customer</option>
                            <template x-for="c in customers" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supervisor</label>
                        <select x-model="form.supervisorId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select supervisor</option>
                            <template x-for="s in supervisors" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project Code</label>
                        <input type="text" x-model="form.projectCode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country Code</label>
                        <input type="text" x-model="form.countryCode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" x-model="form.phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" x-model="form.startDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" x-model="form.endDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Breaktime (min)</label>
                        <input type="number" min="0" x-model="form.breaktime" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location URL</label>
                        <input type="text" x-model="form.locationUrl" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
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
            <p class="text-gray-600 mb-6">Are you sure you want to delete project <span class="font-semibold" x-text="deleteTarget?.name"></span>?</p>
            <div class="flex justify-center gap-3">
                <button @click="deleteModalOpen = false" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                <button @click="deleteProject()" :disabled="saving" class="bg-red-600 text-white px-5 py-2.5 rounded-lg hover:bg-red-700 transition-colors shadow-md disabled:opacity-50" x-text="saving ? 'Deleting...' : 'Delete'"></button>
            </div>
            <div x-show="errorMsg" x-text="errorMsg" class="text-red-600 text-sm mt-3"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function projectData() {
    return {
        projects: [],
        customers: [],
        supervisors: [],
        search: '',
        loading: false,
        saving: false,
        errorMsg: '',
        modalOpen: false,
        deleteModalOpen: false,
        editId: null,
        deleteTarget: null,
        currentPage: 1,
        pageSize: 10,
        form: { name: '', description: '', customerId: '', countryCode: '', phone: '', supervisorId: '', startDate: '', endDate: '', projectCode: '', breaktime: '', locationUrl: '' },

        get token() { return localStorage.getItem('S_S_Token'); },
        get authHeaders() { return { 'Authorization': 'Bearer ' + this.token, 'Content-Type': 'application/json', 'Accept': 'application/json' }; },

        get filteredProjects() {
            const q = this.search.toLowerCase();
            if (!q) return this.projects;
            return this.projects.filter(p =>
                (p.name || '').toLowerCase().includes(q) ||
                (p.customer?.name || '').toLowerCase().includes(q) ||
                (p.location_url || p.locationUrl || '').toLowerCase().includes(q)
            );
        },

        get totalPages() {
            return Math.ceil(this.filteredProjects.length / this.pageSize) || 1;
        },

        get paginatedProjects() {
            const start = (this.currentPage - 1) * this.pageSize;
            return this.filteredProjects.slice(start, start + this.pageSize);
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

        async fetchProjects() {
            this.loading = true;
            this.errorMsg = '';
            try {
                const res = await fetch('/api/v1/project', { headers: this.authHeaders });
                const data = await res.json();
                if (data.status) {
                    this.projects = Array.isArray(data.data) ? data.data : [];
                } else {
                    this.errorMsg = data.message || 'Failed to load projects';
                }
            } catch (e) {
                this.errorMsg = 'Network error: ' + e.message;
            }
            this.loading = false;
        },

        async fetchDependencies() {
            try {
                const [custRes, staffRes] = await Promise.all([
                    fetch('/api/v1/customer', { headers: this.authHeaders }),
                    fetch('/api/v1/staff', { headers: this.authHeaders })
                ]);
                const custData = await custRes.json();
                const staffData = await staffRes.json();
                if (custData.status) this.customers = Array.isArray(custData.data) ? custData.data : [];
                if (staffData.status) this.supervisors = Array.isArray(staffData.data) ? staffData.data : [];
            } catch (e) {
                console.error('Failed to load dependencies:', e);
            }
        },

        openAddModal() {
            this.editId = null;
            this.errorMsg = '';
            this.form = { name: '', description: '', customerId: '', countryCode: '', phone: '', supervisorId: '', startDate: '', endDate: '', projectCode: '', breaktime: '', locationUrl: '' };
            this.modalOpen = true;
            if (this.customers.length === 0) this.fetchDependencies();
        },

        openEditModal(project) {
            this.editId = project.id;
            this.errorMsg = '';
            this.form = {
                name: project.name || '',
                description: project.description || '',
                customerId: project.customer_id || project.customerId || '',
                countryCode: project.country_code || project.countryCode || '',
                phone: project.phone || '',
                supervisorId: project.supervisor_id || project.supervisorId || '',
                startDate: project.start_date || project.startDate || '',
                endDate: project.end_date || project.endDate || '',
                projectCode: project.project_code || project.projectCode || '',
                breaktime: project.breaktime || '',
                locationUrl: project.location_url || project.locationUrl || ''
            };
            this.modalOpen = true;
            if (this.customers.length === 0) this.fetchDependencies();
        },

        openDeleteModal(project) {
            this.deleteTarget = project;
            this.errorMsg = '';
            this.deleteModalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
            this.editId = null;
            this.errorMsg = '';
        },

        async saveProject() {
            this.saving = true;
            this.errorMsg = '';
            const method = this.editId ? 'PUT' : 'POST';
            const url = this.editId ? '/api/v1/project/' + this.editId : '/api/v1/project';
            try {
                const res = await fetch(url, { method, headers: this.authHeaders, body: JSON.stringify(this.form) });
                const data = await res.json();
                if (data.status) {
                    this.closeModal();
                    await this.fetchProjects();
                } else {
                    this.errorMsg = data.message || 'Save failed';
                }
            } catch (e) {
                this.errorMsg = 'Network error: ' + e.message;
            }
            this.saving = false;
        },

        async deleteProject() {
            if (!this.deleteTarget) return;
            this.saving = true;
            this.errorMsg = '';
            try {
                const res = await fetch('/api/v1/project/' + this.deleteTarget.id, { method: 'DELETE', headers: this.authHeaders });
                const data = await res.json();
                if (data.status) {
                    this.deleteModalOpen = false;
                    this.deleteTarget = null;
                    await this.fetchProjects();
                } else {
                    this.errorMsg = data.message || 'Delete failed';
                }
            } catch (e) {
                this.errorMsg = 'Network error: ' + e.message;
            }
            this.saving = false;
        }
    };
}
</script>
@endpush
