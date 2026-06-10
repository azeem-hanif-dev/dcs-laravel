{{-- resources/views/work/plan.blade.php --}}
@extends('layouts.admin')

@section('title', 'Work Plans - Distributor Portal')

@section('page-content')
<div x-data="workPlanData()" x-init="init()" class="max-w-7xl mx-auto px-2 sm:px-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Work Plans</h1>
            <p class="text-sm text-gray-500 mt-0.5">Assign workers to project jobs</p>
        </div>
        <button @click="openAddModal()"
            class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Work Plan
        </button>
    </div>

    {{-- Filter card --}}
    <div class="bg-white rounded-xl shadow-sm p-3 mb-4 border border-gray-100">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300ms="doSearch()"
                    placeholder="Search work plans..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none text-sm">
            </div>
            <button @click="search=''; doSearch()" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors whitespace-nowrap">Clear</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-responsive">
            <table class="table-card-sm min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Job</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Worker</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Type</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-if="pager.loading">
                        <tr><td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="spinner w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span class="text-sm text-gray-400">Loading...</span>
                            </div>
                        </td></tr>
                    </template>
                    <template x-if="!pager.loading && pager.items.length === 0">
                        <tr><td colspan="7" class="px-6 py-16 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-gray-500 font-medium">No work plans found</p>
                            <p class="text-sm text-gray-400 mt-1">Assign workers to projects</p>
                        </td></tr>
                    </template>
                    <template x-for="(item, index) in pager.items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-3 text-sm text-gray-500 whitespace-nowrap" data-label="#" x-text="(pager.currentPage - 1) * pager.perPage + index + 1"></td>
                            <td class="px-3 py-3 text-sm font-medium text-gray-800 whitespace-nowrap" data-label="Project" x-text="item.project?.name || ''"></td>
                            <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Job" x-text="item.job?.name || ''"></td>
                            <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap" data-label="Worker" x-text="item.worker?.name || ''"></td>
                            <td class="px-3 py-3 whitespace-nowrap hidden md:table-cell" data-label="Type">
                                <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                                    :class="{
                                        'bg-blue-100 text-blue-700': item.job_type === 'daily',
                                        'bg-green-100 text-green-700': item.job_type === 'weekly',
                                        'bg-purple-100 text-purple-700': item.job_type === 'onetime',
                                        'bg-orange-100 text-orange-700': item.job_type === 'extra'
                                    }" x-text="item.job_type || ''"></span>
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap" data-label="Status">
                                <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                                    :class="{
                                        'bg-green-100 text-green-700': (item.status || '').toLowerCase() === 'active',
                                        'bg-yellow-100 text-yellow-700': (item.status || '').toLowerCase() === 'pending',
                                        'bg-gray-200 text-gray-600': (item.status || '').toLowerCase() === 'completed',
                                        'bg-blue-100 text-blue-700': !['active', 'pending', 'completed'].includes((item.status || '').toLowerCase())
                                    }" x-text="item.status || 'N/A'"></span>
                            </td>
                            <td class="px-3 py-3 text-center whitespace-nowrap" data-label="Actions">
                                <div class="flex justify-center gap-1">
                                    <button @click="openEditModal(item)" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="confirmDelete(item)" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Delete">
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
        <div class="px-4 py-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3" x-show="pager.totalPages > 1">
            <span class="text-xs text-gray-500" x-text="'Showing ' + ((pager.currentPage - 1) * pager.perPage + 1) + '-' + Math.min(pager.currentPage * pager.perPage, pager.total) + ' of ' + pager.total"></span>
            <div class="flex gap-1">
                <button @click="pager.goToPage(1)" :disabled="pager.currentPage === 1" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50">First</button>
                <button @click="pager.goToPage(pager.currentPage - 1)" :disabled="pager.currentPage === 1" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50">Prev</button>
                <template x-for="page in pager.visiblePages()" :key="page">
                    <button @click="pager.goToPage(page)" :class="pager.currentPage === page ? 'bg-primary text-white border-primary' : 'border-gray-200 hover:bg-gray-50'" class="px-2.5 py-1.5 text-xs rounded-lg border" x-text="page"></button>
                </template>
                <button @click="pager.goToPage(pager.currentPage + 1)" :disabled="pager.currentPage === pager.totalPages" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50">Next</button>
                <button @click="pager.goToPage(pager.totalPages)" :disabled="pager.currentPage === pager.totalPages" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50">Last</button>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto z-10">
            <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white rounded-t-2xl z-10">
                <h3 class="text-lg font-semibold text-gray-800" x-text="editId ? 'Edit Work Plan' : 'Add Work Plan'"></h3>
                <button @click="closeModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveItem()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Project <span class="text-red-500">*</span></label>
                    <select x-model="form.projectId" @change="fetchJobs(form.projectId)" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <option value="">Select project</option>
                        <template x-for="p in projects" :key="p.id">
                            <option :value="p.id" x-text="p.name"></option>
                        </template>
                    </select>
                    <p x-show="errors.projectId" class="text-red-500 text-xs mt-1" x-text="errors.projectId"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Job <span class="text-red-500">*</span></label>
                    <select x-model="form.jobId" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <option value="">Select job</option>
                        <template x-for="j in jobs" :key="j.id">
                            <option :value="j.id" x-text="j.name || j.job_def?.name || ''"></option>
                        </template>
                    </select>
                    <p x-show="errors.jobId" class="text-red-500 text-xs mt-1" x-text="errors.jobId"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Worker <span class="text-red-500">*</span></label>
                    <select x-model="form.workerId" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <option value="">Select worker</option>
                        <template x-for="w in workers" :key="w.id">
                            <option :value="w.id" x-text="w.name"></option>
                        </template>
                    </select>
                    <p x-show="errors.workerId" class="text-red-500 text-xs mt-1" x-text="errors.workerId"></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Job Type <span class="text-red-500">*</span></label>
                        <select x-model="form.jobType" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="">Select type</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="onetime">One-time</option>
                            <option value="extra">Extra</option>
                        </select>
                        <p x-show="errors.jobType" class="text-red-500 text-xs mt-1" x-text="errors.jobType"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="form.date" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.date" class="text-red-500 text-xs mt-1" x-text="errors.date"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Days</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="day in dayOptions" :key="day.value">
                            <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer transition-colors text-sm"
                                :class="form.days.includes(day.value) ? 'bg-primary text-white border-primary' : 'border-gray-300 hover:bg-gray-50'">
                                <input type="checkbox" :value="day.value" x-model="form.days" class="sr-only">
                                <span x-text="day.label"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Weeks (comma separated)</label>
                    <input type="text" x-model="form.weeks" placeholder="e.g. 1,2,3,4" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select x-model="form.status" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <option value="">Select status</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
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
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="deleteModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
            <p class="text-sm text-gray-500 mb-5">Are you sure you want to delete this work plan?</p>
            <div class="flex justify-center gap-3">
                <button @click="deleteModalOpen = false" class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                <button @click="deleteItem()" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50">Delete</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function workPlanData(){return{
    pager:$store.pager.create({endpoint:'/api/v1/work',perPage:10}),projects:[],jobs:[],workers:[],search:'',saving:false,modalOpen:false,deleteModalOpen:false,editId:null,deleteTarget:null,dayOptions:[{value:'mon',label:'Mon'},{value:'tue',label:'Tue'},{value:'wed',label:'Wed'},{value:'thu',label:'Thu'},{value:'fri',label:'Fri'},{value:'sat',label:'Sat'},{value:'sun',label:'Sun'}],form:{projectId:'',jobId:'',workerId:'',jobType:'',days:[],weeks:'',date:'',status:''},errors:{},
    async init(){await this.fetchDependencies();await this.pager.fetchPage()},
    doSearch(){this.pager.currentPage=1;this.pager.fetchPage({search:this.search})},
    async fetchDependencies(){try{var pd=await $store.api.get('/api/v1/project',{per_page:200});var sd=await $store.api.get('/api/v1/staff',{per_page:200});if(pd.status)this.projects=Array.isArray(pd.data)?pd.data:(pd.data?.data||pd.data||[]);if(sd.status)this.workers=Array.isArray(sd.data)?sd.data:(sd.data?.data||sd.data||[])}catch(e){$store.toast.error('Failed to load dependencies')}},
    async fetchJobs(pid){this.jobs=[];if(!pid)return;try{var d=await $store.api.get('/api/v1/job/project/'+pid);if(d.status)this.jobs=Array.isArray(d.data)?d.data:(d.data?.data||d.data||[])}catch(e){$store.toast.error('Failed to load jobs')}},
    openAddModal(){this.editId=null;this.errors={};this.jobs=[];this.form={projectId:'',jobId:'',workerId:'',jobType:'',days:[],weeks:'',date:'',status:''};this.modalOpen=true},
    openEditModal(item){this.editId=item.id;this.errors={};this.jobs=[];this.form={projectId:item.project_id||item.projectId||'',jobId:item.job_id||item.jobId||'',workerId:item.worker_id||item.workerId||'',jobType:item.job_type||'',days:Array.isArray(item.days)?item.days:(item.days?item.days.split(','):[]),weeks:Array.isArray(item.weeks)?item.weeks.join(','):(item.weeks||''),date:item.date||'',status:item.status||''};if(this.form.projectId)this.fetchJobs(this.form.projectId);this.modalOpen=true},
    closeModal(){this.modalOpen=false;this.editId=null;this.errors={}},
    async saveItem(){this.errors={};var p=this.form.projectId,j=this.form.jobId,w=this.form.workerId,jt=this.form.jobType,dt=this.form.date;if(!p){this.errors.projectId='Project is required';return}if(!j){this.errors.jobId='Job is required';return}if(!w){this.errors.workerId='Worker is required';return}if(!jt){this.errors.jobType='Job type is required';return}if(!dt){this.errors.date='Date is required';return}this.saving=true;var body={...this.form,weeks:this.form.weeks?this.form.weeks.split(',').map(function(s){return s.trim()}).filter(Boolean):[]};try{var d=this.editId?await $store.api.put('/api/v1/work/'+this.editId,body):await $store.api.post('/api/v1/work',body);if(d.status){this.closeModal();$store.toast.success(this.editId?'Work plan updated':'Work plan created');this.pager.fetchPage()}else{$store.toast.error(d.message||'Save failed')}}catch(e){$store.toast.error(e.message||'Save failed')}this.saving=false},
    confirmDelete(item){this.deleteTarget=item;this.deleteModalOpen=true},
    async deleteItem(){if(!this.deleteTarget)return;this.saving=true;try{await $store.api.del('/api/v1/work/'+this.deleteTarget.id);this.deleteModalOpen=false;this.deleteTarget=null;$store.toast.success('Work plan deleted');this.pager.fetchPage()}catch(e){$store.toast.error(e.message||'Delete failed')}this.saving=false}
}}
</script>
@endpush
