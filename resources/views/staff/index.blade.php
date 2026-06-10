{{-- resources/views/staff/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Staff - Distributor Portal')

@section('page-content')
<div x-data="staffData()" x-init="init()" class="max-w-7xl mx-auto px-2 sm:px-4">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Staff</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage staff members</p>
        </div>
        <button @click="openAddModal()"
            class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Staff
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-3 mb-4 border border-gray-100">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300ms="currentPage=1;fetchItems()" placeholder="Search staff..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none text-sm">
            </div>
            <button @click="search=''; currentPage=1; fetchItems()" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors whitespace-nowrap">Clear</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-responsive">
            <table class="table-card-sm min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Username</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Designation</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Phone</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-if="loading">
                        <tr><td colspan="7" class="px-6 py-16 text-center"><div class="flex flex-col items-center gap-2"><svg class="spinner w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="text-sm text-gray-400">Loading...</span></div></td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="7" class="px-6 py-16 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="text-gray-500 font-medium">No staff found</p>
                            <p class="text-sm text-gray-400 mt-1" x-text="search ? 'Try adjusting your search' : 'Click Add Staff to create one'"></p>
                        </td></tr>
                    </template>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-3 text-sm text-gray-500 whitespace-nowrap" data-label="#" x-text="(currentPage-1)*perPage + index + 1"></td>
                            <td class="px-3 py-3 text-sm font-medium text-gray-800 whitespace-nowrap" data-label="Name" x-text="item.name"></td>
                            <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Username" x-text="item.username"></td>
                            <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Email" x-text="item.email"></td>
                            <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap" data-label="Designation" x-text="item.designation || ''"></td>
                            <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap hidden md:table-cell" data-label="Phone" x-text="item.phone || ''"></td>
                            <td class="px-3 py-3 text-center whitespace-nowrap" data-label="Actions">
                                <div class="flex justify-center gap-1.5">
                                    <button @click="openEditModal(item)" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                    <button @click="confirmDelete(item)" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3" x-show="total > perPage">
            <span class="text-xs text-gray-500">Page {{currentPage}} of {{totalPages}} ({{total}} total)</span>
            <div class="flex gap-1">
                <button @click="changePage(1)" :disabled="currentPage===1" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50">First</button>
                <button @click="changePage(currentPage-1)" :disabled="currentPage===1" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50">Prev</button>
                <template x-for="p in visiblePages" :key="p">
                    <button @click="changePage(p)" :class="p===currentPage ? 'bg-primary text-white border-primary' : 'border-gray-200 hover:bg-gray-50'" class="px-2.5 py-1.5 text-xs rounded-lg border"><span x-text="p"></span></button>
                </template>
                <button @click="changePage(currentPage+1)" :disabled="currentPage===totalPages" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50">Next</button>
                <button @click="changePage(totalPages)" :disabled="currentPage===totalPages" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50">Last</button>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10" @click.outside="closeModal()">
            <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white rounded-t-2xl z-10">
                <h3 class="text-lg font-semibold text-gray-800" x-text="editId ? 'Edit Staff' : 'Add Staff'"></h3>
                <button @click="closeModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form @submit.prevent="saveItem()" class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.name" class="text-red-500 text-xs mt-1" x-text="errors.name"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.username" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.username" class="text-red-500 text-xs mt-1" x-text="errors.username"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" x-model="form.email" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.email" class="text-red-500 text-xs mt-1" x-text="errors.email"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                        <input type="text" x-model="form.phone" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Employee Code</label>
                        <input type="text" x-model="form.employeeCode" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                        <select x-model="form.gender" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Designation <span class="text-red-500">*</span></label>
                        <select x-model="form.designation" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="">Select Designation</option>
                            <template x-for="d in designations" :key="d">
                                <option :value="d" x-text="d"></option>
                            </template>
                        </select>
                        <p x-show="errors.designation" class="text-red-500 text-xs mt-1" x-text="errors.designation"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Job Type</label>
                        <select x-model="form.jobTypeId" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="">Select Job Type</option>
                            <template x-for="jt in jobTypes" :key="jt.id">
                                <option :value="jt.id" x-text="jt.name || jt.title || ''"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Visa Expiry</label>
                        <input type="date" x-model="form.visaExpiry" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Health Expiry</label>
                        <input type="date" x-model="form.healthExpiry" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Passport Expiry</label>
                        <input type="date" x-model="form.passportExpiry" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                </div>
                <div x-show="!editId">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" x-model="form.password" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <p x-show="errors.password" class="text-red-500 text-xs mt-1" x-text="errors.password"></p>
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

    {{-- Delete Confirm --}}
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40" @click="deleteModalOpen=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
            <p class="text-sm text-gray-500 mb-5">Are you sure you want to delete <span class="font-medium text-gray-700" x-text="deleteTarget?.name"></span>?</p>
            <div class="flex justify-center gap-3">
                <button @click="deleteModalOpen=false" class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                <button @click="deleteItem()" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function staffData(){return{
    items:[],jobTypes:[],designations:['Admin','Manager','Supervisor','Worker','Operator','Technician','Cleaner','Driver','Security','Other'],search:'',currentPage:1,perPage:10,total:0,totalPages:1,loading:false,saving:false,modalOpen:false,deleteModalOpen:false,editId:null,deleteTarget:null,form:{name:'',username:'',email:'',employeeCode:'',phone:'',designation:'',jobTypeId:'',gender:'',visaExpiry:'',healthExpiry:'',passportExpiry:'',password:''},errors:{},
    get visiblePages(){var p=[],s=Math.max(1,this.currentPage-2),e=Math.min(this.totalPages,this.currentPage+2);for(var i=s;i<=e;i++)p.push(i);return p},
    async init(){await this.fetchJobTypes();this.fetchItems()},
    async fetchJobTypes(){try{var d=await Alpine.store('api').get('/api/v1/staff-role',{per_page:100});this.jobTypes=d.data?.data||d.data||[]}catch(e){}},
    async fetchItems(){this.loading=true;try{var d=await Alpine.store('api').get('/api/v1/staff',{page:this.currentPage,per_page:this.perPage,search:this.search||undefined});if(d.status){this.items=d.data?.data||d.data||[];this.total=d.data?.total||d.total||this.items.length;this.totalPages=d.data?.last_page||d.last_page||Math.ceil(this.total/this.perPage)||1}}catch(e){Alpine.store('toast').error('Failed to load staff')}this.loading=false},
    openAddModal(){this.editId=null;this.errors={};this.form={name:'',username:'',email:'',employeeCode:'',phone:'',designation:'',jobTypeId:'',gender:'',visaExpiry:'',healthExpiry:'',passportExpiry:'',password:''};this.modalOpen=true},
    openEditModal(s){this.editId=s.id;this.errors={};this.form={name:s.name||'',username:s.username||'',email:s.email||'',employeeCode:s.employee_code||s.employeeCode||'',phone:s.phone||'',designation:s.designation||'',jobTypeId:s.job_type_id||s.jobTypeId||'',gender:s.gender||'',visaExpiry:s.visa_expiry||s.visaExpiry||'',healthExpiry:s.health_expiry||s.healthExpiry||'',passportExpiry:s.passport_expiry||s.passportExpiry||'',password:''};this.modalOpen=true},
    closeModal(){this.modalOpen=false;this.editId=null;this.errors={}},
    async saveItem(){this.errors={};if(!this.form.name){this.errors.name='Name is required';return}if(!this.form.username){this.errors.username='Username is required';return}if(!this.form.designation){this.errors.designation='Designation is required';return}if(!this.editId&&!this.form.password){this.errors.password='Password is required';return}this.saving=true;var body={name:this.form.name,username:this.form.username,email:this.form.email||'',employee_code:this.form.employeeCode||'',phone:this.form.phone||'',designation:this.form.designation,job_type_id:this.form.jobTypeId||'',gender:this.form.gender||'',visa_expiry:this.form.visaExpiry||'',health_expiry:this.form.healthExpiry||'',passport_expiry:this.form.passportExpiry||''};if(!this.editId)body.password=this.form.password;try{if(this.editId){await Alpine.store('api').put('/api/v1/staff/'+this.editId,body)}else{await Alpine.store('api').post('/api/v1/staff/store',body)}this.closeModal();Alpine.store('toast').success(this.editId?'Staff updated':'Staff created');this.fetchItems()}catch(e){if(e.errors)this.errors=e.errors;else Alpine.store('toast').error(e.message||'Save failed')}this.saving=false},
    confirmDelete(s){this.deleteTarget=s;this.deleteModalOpen=true},
    async deleteItem(){if(!this.deleteTarget)return;this.saving=true;try{await Alpine.store('api').del('/api/v1/staff/'+this.deleteTarget.id);this.deleteModalOpen=false;this.deleteTarget=null;Alpine.store('toast').success('Staff deleted');this.fetchItems()}catch(e){Alpine.store('toast').error(e.message||'Delete failed')}this.saving=false},
    changePage(p){if(p>=1&&p<=this.totalPages){this.currentPage=p;this.fetchItems()}}
}}
</script>
@endpush
