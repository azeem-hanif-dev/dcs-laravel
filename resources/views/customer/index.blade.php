{{-- resources/views/customer/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Customers - Distributor Portal')

@section('page-content')
<div x-data="customerData()" x-init="initPage()" class="max-w-7xl mx-auto px-2 sm:px-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Customers</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage customer accounts</p>
        </div>
        <button @click="openAddModal()" x-show="isAdmin"
            class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Customer
        </button>
    </div>

    {{-- Filter card --}}
    <div class="bg-white rounded-xl shadow-sm p-2.5 mb-3 border border-gray-100">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative max-w-xs w-full">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300ms="currentPage=1; fetchItems()"
                    placeholder="Search customers..."
                    class="w-full pl-9 pr-4 py-1.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none text-sm">
            </div>
            <button @click="search=''; currentPage=1; fetchItems()"
                class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors whitespace-nowrap">Clear</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-responsive">
            <table class="table-card-sm min-w-full divide-y divide-gray-200">
                <thead class="table-header-branded">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Country</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-if="loading">
                        <tr><td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="spinner w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span class="text-sm text-gray-400">Loading...</span>
                            </div>
                        </td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="6" class="px-6 py-16 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="text-gray-500 font-medium">No customers found</p>
                            <p class="text-sm text-gray-400 mt-1" x-text="search ? 'Try adjusting your search' : 'Click Add Customer to create one'"></p>
                        </td></tr>
                    </template>
                    <template x-for="(customer, index) in items" :key="customer.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap" data-label="#" x-text="(currentPage-1)*perPage + index + 1"></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap" data-label="Name" x-text="customer.name"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Email" x-text="customer.email"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Phone" x-text="customer.phone"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap hidden md:table-cell" data-label="Country" x-text="customer.country"></td>
                            <td class="px-4 py-3 text-center whitespace-nowrap" data-label="Actions">
                                <div class="flex justify-center gap-1.5">
                                    <button @click="openEditModal(customer)" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="openDeleteModal(customer)" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Delete">
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
        @include('components.pagination-footer', ['prefix' => ''])
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.200>
        <div class="fixed inset-0 bg-black/40" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10">
            <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white rounded-t-2xl z-10">
                <h3 class="text-lg font-semibold text-gray-800" x-text="editId ? 'Edit Customer' : 'Add Customer'"></h3>
                <button @click="closeModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveCustomer()" class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.name" class="text-red-500 text-xs mt-1" x-text="errors.name"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" x-model="form.email" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.email" class="text-red-500 text-xs mt-1" x-text="errors.email"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.phone" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.phone" class="text-red-500 text-xs mt-1" x-text="errors.phone"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Country Code <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.countryCode" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.countryCode" class="text-red-500 text-xs mt-1" x-text="errors.countryCode"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.country" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.country" class="text-red-500 text-xs mt-1" x-text="errors.country"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                        <input type="text" x-model="form.city" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person 1</label>
                        <input type="text" x-model="form.contactPerson1" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person 2</label>
                        <input type="text" x-model="form.contactPerson2" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                    <textarea x-model="form.address" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></textarea>
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

    {{-- Delete Confirmation Modal --}}
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40" @click="deleteModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
            <p class="text-sm text-gray-500 mb-5">Are you sure you want to delete <span class="font-medium text-gray-700" x-text="deleteTarget?.name"></span>?</p>
            <div class="flex justify-center gap-3">
                <button @click="deleteModalOpen = false" class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                <button @click="deleteCustomer()" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50">Delete</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function customerData(){return{
    items:[],search:'',isAdmin:false,currentPage:1,perPage:10,total:0,totalPages:1,loading:false,saving:false,modalOpen:false,deleteModalOpen:false,editId:null,deleteTarget:null,form:{name:'',email:'',phone:'',countryCode:'',country:'',city:'',address:'',contactPerson1:'',contactPerson2:'',password:''},errors:{},
    get visiblePages(){var p=[],s=Math.max(1,this.currentPage-2),e=Math.min(this.totalPages,this.currentPage+2);for(var i=s;i<=e;i++)p.push(i);return p},
    initPage(){try{var u=JSON.parse(localStorage.getItem('user')||'{}');this.isAdmin=!u.role||u.role==='superadmin'||u.role==='admin'}catch(e){}this.fetchItems()},
    async fetchItems(){this.loading=true;try{var d=await Alpine.store('api').get('/api/v1/customer',{page:this.currentPage,per_page:this.perPage,search:this.search||undefined});if(d&&d.status){this.items=d.data?.data||d.data||[];this.total=d.data?.total||d.total||this.items.length;this.totalPages=d.data?.last_page||d.last_page||Math.ceil(this.total/this.perPage)||1}else{this.items=[];this.total=0;this.totalPages=1}}catch(e){console.error(e);this.items=[];Alpine.store('toast').error('Failed to load customers')}finally{this.loading=false}},
    openAddModal(){this.editId=null;this.errors={};this.form={name:'',email:'',phone:'',countryCode:'',country:'',city:'',address:'',contactPerson1:'',contactPerson2:'',password:''};this.modalOpen=true},
    openEditModal(c){this.editId=c.id;this.errors={};this.form={name:c.name||'',email:c.email||'',phone:c.phone||'',countryCode:c.country_code||c.countryCode||'',country:c.country||'',city:c.city||'',address:c.address||'',contactPerson1:c.contact_person1||c.contactPerson1||'',contactPerson2:c.contact_person2||c.contactPerson2||'',password:''};this.modalOpen=true},
    openDeleteModal(c){this.deleteTarget=c;this.deleteModalOpen=true},
    closeModal(){this.modalOpen=false;this.editId=null;this.errors={}},
    async saveCustomer(){this.errors={};var n=this.form.name,e=this.form.email,p=this.form.phone,cc=this.form.countryCode,co=this.form.country;if(!n){this.errors.name='Name is required';return}if(!e){this.errors.email='Email is required';return}if(!p){this.errors.phone='Phone is required';return}if(!cc){this.errors.countryCode='Country code is required';return}if(!co){this.errors.country='Country is required';return}if(!this.editId&&!this.form.password){this.errors.password='Password is required';return}this.saving=true;var body={name:n,email:e,phone:p,countryCode:cc,country:co,city:this.form.city||'',address:this.form.address||'',contactPerson1:this.form.contactPerson1||'',contactPerson2:this.form.contactPerson2||''};if(!this.editId)body.password=this.form.password;try{if(this.editId){await Alpine.store('api').put('/api/v1/customer/'+this.editId,body)}else{await Alpine.store('api').post('/api/v1/customer',body)}this.closeModal();Alpine.store('toast').success(this.editId?'Customer updated':'Customer created');this.fetchItems()}catch(err){if(err.errors)this.errors=err.errors;else Alpine.store('toast').error(err.message||'Save failed');this.saving=false}},
    async deleteCustomer(){if(!this.deleteTarget)return;this.saving=true;try{await Alpine.store('api').del('/api/v1/customer/'+this.deleteTarget.id);this.deleteModalOpen=false;this.deleteTarget=null;Alpine.store('toast').success('Customer deleted');this.fetchItems()}catch(e){Alpine.store('toast').error(e.message||'Delete failed')}this.saving=false},
    changePage(p){if(p>=1&&p<=this.totalPages){this.currentPage=p;this.fetchItems()}},
    changePerPage(n){n=parseInt(n);if(!n||n===this.perPage)return;this.perPage=n;this.currentPage=1;this.fetchItems()}
}}
</script>
@endpush
