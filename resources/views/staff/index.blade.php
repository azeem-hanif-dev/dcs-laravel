{{-- resources/views/staff/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Staff Management - Digital Clean Solution')

@section('page-content')
<div x-data="staffData()" x-init="init()" class="max-w-7xl mx-auto px-4">

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="relative w-full sm:w-72">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" @input.debounce.300ms="fetchItems()" placeholder="Search staff..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
        </div>
        <button @click="openAddModal()"
            class="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all duration-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Staff
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-primary">
                    <tr>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Sr. No</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Name</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Username</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Email</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Designation</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Phone</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-if="items.length === 0">
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No staff found.</td></tr>
                    </template>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 text-sm text-gray-700 whitespace-nowrap" x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td class="px-4 py-4 text-sm text-gray-900 font-medium whitespace-nowrap" x-text="item.name"></td>
                            <td class="px-4 py-4 text-sm text-gray-600 whitespace-nowrap" x-text="item.username"></td>
                            <td class="px-4 py-4 text-sm text-gray-600 whitespace-nowrap" x-text="item.email"></td>
                            <td class="px-4 py-4 text-sm text-gray-700 whitespace-nowrap" x-text="item.designation || ''"></td>
                            <td class="px-4 py-4 text-sm text-gray-700 whitespace-nowrap" x-text="item.phone || ''"></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
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
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6 z-10 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-800" x-text="isEditing ? 'Edit Staff' : 'Add Staff'"></h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveItem()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" x-model="form.name" placeholder="Full name"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <p x-show="errors.name" class="text-red-500 text-xs mt-1" x-text="errors.name"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" x-model="form.username" placeholder="Username"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <p x-show="errors.username" class="text-red-500 text-xs mt-1" x-text="errors.username"></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" x-model="form.email" placeholder="email@example.com"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        <p x-show="errors.email" class="text-red-500 text-xs mt-1" x-text="errors.email"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" x-model="form.phone" placeholder="Phone number"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee Code</label>
                        <input type="text" x-model="form.employee_code" placeholder="Employee code"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select x-model="form.gender"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                        <select x-model="form.designation"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                            <option value="">Select Designation</option>
                            <template x-for="d in designations" :key="d">
                                <option :value="d" x-text="d"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Agency</label>
                        <select x-model="form.agency_id"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                            <option value="">Select Agency</option>
                            <template x-for="ag in agencies" :key="ag.id">
                                <option :value="ag.id" x-text="ag.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                        <select x-model="form.job_type_id"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                            <option value="">Select Job Type</option>
                            <template x-for="jt in jobTypes" :key="jt.id">
                                <option :value="jt.id" x-text="jt.name || jt.title || ''"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                        <select x-model="form.shift_id"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                            <option value="">Select Shift</option>
                            <template x-for="sh in shifts" :key="sh.id">
                                <option :value="sh.id" x-text="sh.title"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div x-show="!isEditing">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" x-model="form.password" placeholder="Password"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    <p x-show="errors.password" class="text-red-500 text-xs mt-1" x-text="errors.password"></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Visa Expiry</label>
                        <input type="date" x-model="form.visa_expiry"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Health Expiry</label>
                        <input type="date" x-model="form.health_expiry"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Passport Expiry</label>
                        <input type="date" x-model="form.passport_expiry"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    </div>
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

    {{-- Delete Confirmation Modal --}}
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="deleteModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <svg class="w-12 h-12 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Staff?</h3>
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
function staffData() {
    return {
        items: [],
        agencies: [],
        jobTypes: [],
        shifts: [],
        designations: ['Admin','Manager','Supervisor','Worker','Operator','Technician','Cleaner','Driver','Security','Other'],
        search: '',
        currentPage: 1,
        perPage: 10,
        total: 0,
        totalPages: 1,
        modalOpen: false,
        deleteModalOpen: false,
        isEditing: false,
        saving: false,
        form: { id: null, name: '', username: '', email: '', employee_code: '', phone: '', designation: '', agency_id: '', job_type_id: '', shift_id: '', gender: '', password: '', visa_expiry: '', health_expiry: '', passport_expiry: '' },
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
            await Promise.all([this.fetchAgencies(), this.fetchShifts()]);
            this.fetchItems();
        },

        async fetchItems() {
            try {
                let token = localStorage.getItem('S_S_Token');
                let params = new URLSearchParams({ page: this.currentPage, per_page: this.perPage });
                if (this.search) params.append('search', this.search);
                let res = await fetch('/api/v1/staff?' + params.toString(), {
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

        async fetchAgencies() {
            try {
                let token = localStorage.getItem('S_S_Token');
                let res = await fetch('/api/v1/employment-agencies?per_page=100', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' }
                });
                let data = await res.json();
                this.agencies = data.data?.data || data.data || [];
            } catch (e) { console.error('Agencies fetch error:', e); }
        },

        async fetchShifts() {
            try {
                let token = localStorage.getItem('S_S_Token');
                let res = await fetch('/api/v1/shift?per_page=100', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' }
                });
                let data = await res.json();
                this.shifts = data.data?.data || data.data || [];
            } catch (e) { console.error('Shifts fetch error:', e); }
        },

        openAddModal() {
            this.isEditing = false;
            this.form = { id: null, name: '', username: '', email: '', employee_code: '', phone: '', designation: '', agency_id: '', job_type_id: '', shift_id: '', gender: '', password: '', visa_expiry: '', health_expiry: '', passport_expiry: '' };
            this.errors = {};
            this.modalOpen = true;
        },

        openEditModal(item) {
            this.isEditing = true;
            this.form = {
                id: item.id,
                name: item.name || '',
                username: item.username || '',
                email: item.email || '',
                employee_code: item.employee_code || '',
                phone: item.phone || '',
                designation: item.designation || '',
                agency_id: item.agency_id || '',
                job_type_id: item.job_type_id || '',
                shift_id: item.shift_id || '',
                gender: item.gender || '',
                password: '',
                visa_expiry: item.visa_expiry || '',
                health_expiry: item.health_expiry || '',
                passport_expiry: item.passport_expiry || ''
            };
            this.errors = {};
            this.modalOpen = true;
        },

        async saveItem() {
            this.errors = {};
            if (!this.form.name) { this.errors.name = 'Name is required'; return; }
            if (!this.form.email) { this.errors.email = 'Email is required'; return; }
            if (!this.isEditing && !this.form.password) { this.errors.password = 'Password is required'; return; }
            this.saving = true;
            try {
                let token = localStorage.getItem('S_S_Token');
                let url = '/api/v1/staff';
                let method = 'POST';
                let body = { ...this.form };
                if (this.isEditing) { url += '/' + this.form.id; method = 'PUT'; if (!body.password) delete body.password; }
                else { url += '/store'; }
                let res = await fetch(url, {
                    method: method,
                    headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(body)
                });
                let data = await res.json();
                if (res.ok) { this.modalOpen = false; this.fetchItems(); } else {
                    if (data.errors) this.errors = data.errors;
                    else if (data.message) alert(data.message);
                }
            } catch (e) { console.error('Save error:', e); }
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
                let res = await fetch('/api/v1/staff/' + this.deleteId, {
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
