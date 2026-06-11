{{-- resources/views/project/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Projects - Distributor Portal')

@section('page-content')
<div x-data="projectData()" x-init="init()" class="max-w-7xl mx-auto px-2 sm:px-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Projects</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage projects</p>
        </div>
        <button @click="openAddModal()"
            class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Project
        </button>
    </div>

    {{-- Filter card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 mb-3">
        <div class="flex flex-col sm:flex-row items-stretch gap-2">
            <div class="relative max-w-xs w-full">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300ms="doSearch()"
                    placeholder="Search projects..."
                    class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <button @click="search=''; doSearch()"
                class="text-xs text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition-colors whitespace-nowrap">
                Clear
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-responsive">
            <table class="table-card-sm min-w-full divide-y divide-gray-200">
                <thead class="table-header-branded">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">Start</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">End</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider hidden md:table-cell">Location</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    {{-- Loading state --}}
                    <template x-if="pager.loading">
                        <tr><td colspan="7" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="spinner w-10 h-10 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-400">Loading data...</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Please wait a moment</p>
                                </div>
                            </div>
                        </td></tr>
                    </template>
                    {{-- Empty state --}}
                    <template x-if="!pager.loading && pager.items.length === 0">
                        <tr><td colspan="7" class="px-6 py-20 text-center">
                            <div class="max-w-sm mx-auto">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                <h3 class="text-base font-semibold text-gray-400 mb-1">No Projects Found</h3>
                                <p class="text-sm text-gray-400" x-text="search ? 'Try adjusting your search' : 'Click Add Project to create one'"></p>
                            </div>
                        </td></tr>
                    </template>
                    {{-- Data rows --}}
                    <template x-for="(item, index) in pager.items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap" data-label="#" x-text="(pager.currentPage - 1) * pager.perPage + index + 1"></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap" data-label="Name" x-text="item.name"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap" data-label="Customer" x-text="item.customer?.name || ''"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Start" x-text="item.start_date || item.startDate || ''"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="End" x-text="item.end_date || item.endDate || ''"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate hidden md:table-cell" data-label="Location" x-text="item.location_url || item.locationUrl || ''"></td>
                            <td class="px-4 py-3 text-center whitespace-nowrap" data-label="Actions">
                                <div class="flex justify-center gap-1.5">
                                    <button @click="openEditModal(item)" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(item)" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @include('components.pagination-footer', ['prefix' => 'pager.'])
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.200 @keydown.escape.window="closeModal()">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10" @click.outside="closeModal()">
            <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white rounded-t-2xl z-10">
                <h3 class="text-lg font-semibold text-gray-800" x-text="editId ? 'Edit Project' : 'Add Project'"></h3>
                <button @click="closeModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveProject()" class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.name" class="text-red-500 text-xs mt-1" x-text="errors.name"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Customer</label>
                        <select x-model="form.customerId" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="">Select customer</option>
                            <template x-for="c in customers" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Supervisor</label>
                        <select x-model="form.supervisorId" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="">Select supervisor</option>
                            <template x-for="s in supervisors" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Project Code</label>
                        <input type="text" x-model="form.projectCode" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Country Code</label>
                        <input type="text" x-model="form.countryCode" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                        <input type="text" x-model="form.phone" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Start Date</label>
                        <input type="date" x-model="form.startDate" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">End Date</label>
                        <input type="date" x-model="form.endDate" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Breaktime (min)</label>
                        <input type="number" min="0" x-model="form.breaktime" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Location URL</label>
                        <input type="text" x-model="form.locationUrl" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea x-model="form.description" rows="3" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl transition-colors disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="saving" class="spinner w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="saving ? 'Saving...' : (editId ? 'Update' : 'Save')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @include('components.delete-modal', [
        'show' => 'deleteModalOpen',
        'title' => 'Confirm Delete',
        'itemName' => 'deleteTarget?.name',
        'onConfirm' => 'deleteProject()',
        'onCancel' => 'deleteModalOpen = false',
        'saving' => 'saving'
    ])

</div>
@endsection

@push('scripts')
<script>
function projectData(){return{
    pager:{loading:true,items:[],currentPage:1,perPage:10,total:0,totalPages:1,get visiblePages(){return[]},fetchPage(){return Promise.resolve(false)},setSearch(){},refresh(){return Promise.resolve(false)},goToPage(){},changePerPage(){}},customers:[],supervisors:[],search:'',saving:false,modalOpen:false,deleteModalOpen:false,editId:null,deleteTarget:null,form:{name:'',customerId:'',supervisorId:'',projectCode:'',countryCode:'',phone:'',startDate:'',endDate:'',breaktime:'',locationUrl:'',description:''},errors:{},
    async init(){try{this.pager=$store.pager.create({endpoint:'/api/v1/project',perPage:10})}catch(e){console.error('Pager create failed:',e);this.pager.loading=false;return}this.fetchDependencies();this.pager.fetchPage()},
    doSearch(){try{this.pager.setSearch({search:this.search||undefined})}catch(e){this.search=''}},
    async fetchDependencies(){try{var cr=await $store.api.get('/api/v1/customer',{per_page:200});var sr=await $store.api.get('/api/v1/staff',{per_page:200});if(cr&&cr.status)this.customers=Array.isArray(cr.data)?cr.data:(cr.data?.data||[]);if(sr&&sr.status)this.supervisors=Array.isArray(sr.data)?sr.data:(sr.data?.data||[])}catch(e){console.error('Failed to load project dependencies:',e)}},
    openAddModal(){this.editId=null;this.errors={};this.form={name:'',customerId:'',supervisorId:'',projectCode:'',countryCode:'',phone:'',startDate:'',endDate:'',breaktime:'',locationUrl:'',description:''};this.modalOpen=true;if(this.customers.length===0)this.fetchDependencies()},
    openEditModal(p){this.editId=p.id;this.errors={};this.form={name:p.name||'',customerId:p.customer_id||p.customerId||'',supervisorId:p.supervisor_id||p.supervisorId||'',projectCode:p.project_code||p.projectCode||'',countryCode:p.country_code||p.countryCode||'',phone:p.phone||'',startDate:p.start_date||p.startDate||'',endDate:p.end_date||p.endDate||'',breaktime:p.breaktime||'',locationUrl:p.location_url||p.locationUrl||'',description:p.description||''};this.modalOpen=true;if(this.customers.length===0)this.fetchDependencies()},
    openDeleteModal(p){this.deleteTarget=p;this.deleteModalOpen=true},
    closeModal(){this.modalOpen=false;this.editId=null;this.errors={}},
    async saveProject(){this.errors={};if(!this.form.name){this.errors.name='Name is required';return}this.saving=true;try{if(this.editId){await $store.api.put('/api/v1/project/'+this.editId,this.form)}else{await $store.api.post('/api/v1/project',this.form)}this.closeModal();$store.toast.success(this.editId?'Project updated':'Project created');this.pager.refresh()}catch(e){if(e.errors)this.errors=e.errors;else $store.toast.error(e.message||'Save failed')}finally{this.saving=false}},
    async deleteProject(){if(!this.deleteTarget)return;this.saving=true;try{await $store.api.del('/api/v1/project/'+this.deleteTarget.id);this.deleteModalOpen=false;this.deleteTarget=null;$store.toast.success('Project deleted');this.pager.refresh()}catch(e){$store.toast.error(e.message||'Delete failed')}finally{this.saving=false}}
}}
</script>
@endpush
