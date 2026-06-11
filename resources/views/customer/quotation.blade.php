{{-- resources/views/customer/quotation.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quotations - Distributor Portal')

@section('page-content')
<div x-data="quotationData()" x-init="init()" class="max-w-7xl mx-auto px-2 sm:px-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Quotations</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage customer quotations</p>
        </div>
        <button @click="openAddModal()"
            class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Quotation
        </button>
    </div>

    {{-- Filter card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 mb-3">
        <div class="flex flex-col sm:flex-row items-stretch gap-2">
            <div class="relative max-w-xs w-full">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300ms="doSearch()"
                    placeholder="Search quotations..."
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Grand Total</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Actions</th>
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
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-gray-500 font-medium">No quotations found</p>
                            <p class="text-sm text-gray-400 mt-1" x-text="search ? 'Try adjusting your search' : 'Click Add Quotation to create one'"></p>
                        </td></tr>
                    </template>
                    <template x-for="(item, index) in pager.items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap" data-label="#" x-text="(pager.currentPage - 1) * pager.perPage + index + 1"></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap" data-label="Company" x-text="item.companyname"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Date" x-text="item.date"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Contact" x-text="item.contact_person || item.contactPerson || ''"></td>
                            <td class="px-4 py-3 whitespace-nowrap" data-label="Type">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full"
                                    :class="{
                                        'bg-blue-100 text-blue-700': item.type === 'hourly',
                                        'bg-green-100 text-green-700': item.type === 'daily',
                                        'bg-purple-100 text-purple-700': item.type === 'monthly',
                                        'bg-orange-100 text-orange-700': item.type === 'extra_hours'
                                    }" x-text="item.type"></span>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap hidden sm:table-cell" data-label="Grand Total" x-text="'&euro;' + parseFloat(item.grandtotal || 0).toFixed(2)"></td>
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
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.200>
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto z-10">
            <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white rounded-t-2xl z-10">
                <h3 class="text-lg font-semibold text-gray-800" x-text="editId ? 'Edit Quotation' : 'Add Quotation'"></h3>
                <button @click="closeModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveQuotation()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.companyname" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <p x-show="errors.companyname" class="text-red-500 text-xs mt-1" x-text="errors.companyname"></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="form.date" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.date" class="text-red-500 text-xs mt-1" x-text="errors.date"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Validity <span class="text-red-500">*</span></label>
                        <input type="date" x-model="form.validity" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.validity" class="text-red-500 text-xs mt-1" x-text="errors.validity"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.contactPerson" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.contactPerson" class="text-red-500 text-xs mt-1" x-text="errors.contactPerson"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.phone" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.phone" class="text-red-500 text-xs mt-1" x-text="errors.phone"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                        <select x-model="form.type" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="">Select type</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                            <option value="extra_hours">Extra Hours</option>
                        </select>
                        <p x-show="errors.type" class="text-red-500 text-xs mt-1" x-text="errors.type"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Grand Total <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" x-model="form.grandtotal" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <p x-show="errors.grandtotal" class="text-red-500 text-xs mt-1" x-text="errors.grandtotal"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address <span class="text-red-500">*</span></label>
                    <textarea x-model="form.address" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></textarea>
                    <p x-show="errors.address" class="text-red-500 text-xs mt-1" x-text="errors.address"></p>
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
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="deleteModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
            <p class="text-sm text-gray-500 mb-5">Are you sure you want to delete quotation for <span class="font-medium text-gray-700" x-text="deleteTarget?.companyname"></span>?</p>
            <div class="flex justify-center gap-3">
                <button @click="deleteModalOpen = false" class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                <button @click="deleteQuotation()" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50">Delete</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function quotationData(){return{
    pager:{loading:true,items:[],currentPage:1,perPage:10,total:0,totalPages:1,get visiblePages(){return[]},fetchPage(){return Promise.resolve(false)},setSearch(){},refresh(){return Promise.resolve(false)},goToPage(){},changePerPage(){}},search:'',saving:false,modalOpen:false,deleteModalOpen:false,editId:null,deleteTarget:null,form:{companyname:'',date:'',validity:'',contactPerson:'',phone:'',type:'',grandtotal:'',address:''},errors:{},
    async init(){try{this.pager=$store.pager.create({endpoint:'/api/v1/qoutation',perPage:10})}catch(e){console.error('Pager create failed:',e);this.pager.loading=false;return}this.pager.fetchPage()},
    doSearch(){try{this.pager.currentPage=1;this.pager.fetchPage({search:this.search})}catch(e){this.search=''}},
    openAddModal(){this.editId=null;this.errors={};this.form={companyname:'',date:'',validity:'',contactPerson:'',phone:'',type:'',grandtotal:'',address:''};this.modalOpen=true},
    openEditModal(i){this.editId=i.id;this.errors={};this.form={companyname:i.companyname||'',date:i.date||'',validity:i.validity||'',contactPerson:i.contact_person||i.contactPerson||'',phone:i.phone||'',type:i.type||'',grandtotal:i.grandtotal||'',address:i.address||''};this.modalOpen=true},
    openDeleteModal(i){this.deleteTarget=i;this.deleteModalOpen=true},
    closeModal(){this.modalOpen=false;this.editId=null;this.errors={}},
    async saveQuotation(){this.errors={};var cn=this.form.companyname,d=this.form.date,v=this.form.validity,cp=this.form.contactPerson,ph=this.form.phone,t=this.form.type,gt=this.form.grandtotal,ad=this.form.address;if(!cn){this.errors.companyname='Company name is required';return}if(!d){this.errors.date='Date is required';return}if(!v){this.errors.validity='Validity is required';return}if(!cp){this.errors.contactPerson='Contact person is required';return}if(!ph){this.errors.phone='Phone is required';return}if(!t){this.errors.type='Type is required';return}if(!gt){this.errors.grandtotal='Grand total is required';return}if(!ad){this.errors.address='Address is required';return}this.saving=true;try{if(this.editId){await $store.api.put('/api/v1/qoutation/'+this.editId,this.form)}else{await $store.api.post('/api/v1/qoutation',this.form)}this.closeModal();$store.toast.success(this.editId?'Quotation updated':'Quotation created');this.pager.fetchPage()}catch(err){if(err.errors)this.errors=err.errors;else $store.toast.error(err.message||'Save failed');this.saving=false}},
    async deleteQuotation(){if(!this.deleteTarget)return;this.saving=true;try{await $store.api.del('/api/v1/qoutation/'+this.deleteTarget.id);this.deleteModalOpen=false;this.deleteTarget=null;$store.toast.success('Quotation deleted');this.pager.fetchPage()}catch(e){$store.toast.error(e.message||'Delete failed')}this.saving=false}
}}
</script>
@endpush
