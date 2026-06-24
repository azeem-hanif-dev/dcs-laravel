{{-- resources/views/material/supplier.blade.php --}}
@extends('layouts.admin')
@section('title', 'Suppliers - Distributor Portal')
@section('page-content')
<div x-data="supplierData()" x-init="initPage()" class="max-w-7xl mx-auto px-2 sm:px-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div><h1 class="text-xl sm:text-2xl font-bold text-gray-800">Suppliers</h1><p class="text-sm text-gray-500 mt-0.5">Manage your suppliers</p></div>
        <button @click="openAddModal()" x-show="isAdmin" class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Add Supplier
        </button>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-2.5 mb-4 border border-gray-100">
        <div class="flex items-center gap-2">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300ms="searchChanged()" placeholder="Search..."
                    class="w-full pl-8 pr-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <button @click="search='';searchChanged()" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1.5 rounded-lg hover:bg-gray-100 whitespace-nowrap">Clear</button>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-responsive">
            <table class="min-w-full divide-y divide-primary/20">
                <thead class="table-header-branded">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">#</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Name</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider hidden md:table-cell">Phone</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-if="loading">
                        <tr><td colspan="5" class="px-6 py-16 text-center"><div class="flex flex-col items-center gap-2"><svg class="spinner w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="text-sm text-gray-400">Loading...</span></div></td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="5" class="px-6 py-16 text-center"><svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg><p class="text-gray-500 font-medium">No suppliers found</p><p class="text-sm text-gray-400 mt-1">Click Add Supplier to create one</p></td></tr>
                    </template>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-3 text-sm text-gray-500 whitespace-nowrap" data-label="#" x-text="index+1"></td>
                            <td class="px-3 py-3 text-sm font-medium text-gray-800 whitespace-nowrap" data-label="Name" x-text="item.name"></td>
                            <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Email" x-text="item.email"></td>
                            <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap hidden md:table-cell" data-label="Phone" x-text="item.contact_number||item.phone||''"></td>
                            <td class="px-3 py-3 text-center whitespace-nowrap" data-label="Actions">
                                <div class="flex justify-center gap-1" x-show="isAdmin">
                                    <button @click="openEditModal(item)" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                    <button @click="confirmDelete(item)" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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

    {{-- Modal --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
            <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold text-gray-800" x-text="isEditing?'Edit Supplier':'Add Supplier'"></h3>
                <button @click="closeModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form @submit.prevent="saveItem()" class="p-6 space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label><input type="text" x-model="form.name" placeholder="Supplier name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"><p x-show="errors.name" class="text-red-500 text-xs mt-1" x-text="errors.name"></p></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label><input type="email" x-model="form.email" placeholder="supplier@example.com" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"><p x-show="errors.email" class="text-red-500 text-xs mt-1" x-text="errors.email"></p></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person</label><input type="text" x-model="form.contactPerson" placeholder="Name" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label><input type="text" x-model="form.contactNumber" placeholder="Phone" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label><textarea x-model="form.address" rows="2" placeholder="Address..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none resize-none"></textarea></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl disabled:opacity-50">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete --}}
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40" @click="deleteModalOpen=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg></div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Supplier?</h3>
            <p class="text-sm text-gray-500 mb-5">This action cannot be undone.</p>
            <div class="flex justify-center gap-3">
                <button @click="deleteModalOpen=false" class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                <button @click="deleteItem()" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl disabled:opacity-50">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function supplierData(){return{
    items:[],search:'',isAdmin:false,currentPage:1,perPage:10,total:0,totalPages:1,loading:false,saving:false,modalOpen:false,deleteModalOpen:false,isEditing:false,form:{id:null,name:'',email:'',contactPerson:'',contactNumber:'',address:''},errors:{},deleteId:null,
    get visiblePages(){var p=[],s=Math.max(1,this.currentPage-2),e=Math.min(this.totalPages,this.currentPage+2);for(var i=s;i<=e;i++)p.push(i);return p},
    initPage(){try{var u=JSON.parse(localStorage.getItem('user')||'{}');this.isAdmin=!u.role||u.role==='superadmin'||u.role==='admin'}catch(e){}this.fetchItems()},
    searchChanged(){this.currentPage=1;this.fetchItems()},
    async fetchItems(){this.loading=true;try{var d=await Alpine.store('api').get('/api/v1/supplier',{page:this.currentPage,per_page:this.perPage,search:this.search||''});if(d&&d.status){var arr=d.data;this.items=Array.isArray(arr)?arr:(arr&&arr.data?arr.data:[]);this.total=arr&&arr.total?arr.total:this.items.length;this.totalPages=arr&&arr.last_page?arr.last_page:Math.max(1,Math.ceil(this.total/this.perPage))}else{this.items=[];this.total=0;this.totalPages=1}}catch(e){console.error(e);this.items=[];Alpine.store('toast').error('Failed to load suppliers')}finally{this.loading=false}},
    openAddModal(){this.isEditing=false;this.form={id:null,name:'',email:'',contactPerson:'',contactNumber:'',address:''};this.errors={};this.modalOpen=true},
    openEditModal(item){this.isEditing=true;this.form={id:item.id,name:item.name||'',email:item.email||'',contactPerson:item.contact_person||'',contactNumber:item.contact_number||'',address:item.address||''};this.errors={};this.modalOpen=true},
    closeModal(){this.modalOpen=false;this.errors={}},
    async saveItem(){this.errors={};if(!this.form.name){this.errors.name='Name is required';return}if(!this.form.email){this.errors.email='Email is required';return}this.saving=true;try{if(this.isEditing){await Alpine.store('api').put('/api/v1/supplier/'+this.form.id,{name:this.form.name,email:this.form.email,contactPerson:this.form.contactPerson,contactNumber:this.form.contactNumber,address:this.form.address})}else{await Alpine.store('api').post('/api/v1/supplier',{name:this.form.name,email:this.form.email,contactPerson:this.form.contactPerson,contactNumber:this.form.contactNumber,address:this.form.address})}this.closeModal();Alpine.store('toast').success(this.isEditing?'Supplier updated':'Supplier created');this.fetchItems()}catch(e){if(e.errors)this.errors=e.errors;else Alpine.store('toast').error(e.message||'Save failed')}finally{this.saving=false}},
    confirmDelete(item){this.deleteId=item.id;this.deleteModalOpen=true},
    async deleteItem(){this.saving=true;try{await Alpine.store('api').del('/api/v1/supplier/'+this.deleteId);this.deleteModalOpen=false;Alpine.store('toast').success('Supplier deleted');this.fetchItems()}catch(e){Alpine.store('toast').error(e.message||'Delete failed')}finally{this.saving=false}},
    changePage(p){if(p>=1&&p<=this.totalPages){this.currentPage=p;this.fetchItems()}},
    changePerPage(n){n=parseInt(n);if(!n||n===this.perPage)return;this.perPage=n;this.currentPage=1;this.fetchItems()}
}}
</script>
@endpush
