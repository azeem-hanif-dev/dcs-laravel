{{-- resources/views/material/order.blade.php --}}
@extends('layouts.admin')

@section('title', 'Material Orders - Distributor Portal')

@section('page-content')
<div x-data="orderData()" x-init="init()" class="max-w-7xl mx-auto px-2 sm:px-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Material Orders</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage purchase orders for materials</p>
        </div>
        <button @click="openAddModal()"
            class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Order
        </button>
    </div>

    {{-- Filter card --}}
    <div class="bg-white rounded-xl shadow-sm p-2.5 mb-3 border border-gray-100">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative max-w-xs w-full">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300ms="currentPage=1; fetchItems()"
                    placeholder="Search orders..."
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
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Notes</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-44">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-if="loading">
                        <tr><td colspan="5" class="px-6 py-16 text-center">
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
                        <tr><td colspan="5" class="px-6 py-16 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            <p class="text-gray-500 font-medium">No orders found</p>
                            <p class="text-sm text-gray-400 mt-1">Create your first material order</p>
                        </td></tr>
                    </template>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-3 text-sm text-gray-500 whitespace-nowrap" data-label="#" x-text="(currentPage-1)*perPage + index + 1"></td>
                            <td class="px-3 py-3 text-sm text-gray-900 whitespace-nowrap" data-label="Date" x-text="item.orderDate||''"></td>
                            <td class="px-3 py-3 text-sm whitespace-nowrap" data-label="Status">
                                <span :class="statusBadgeClass(item.status)" class="px-2.5 py-1 rounded-full text-xs font-medium" x-text="item.status"></span>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-600 max-w-[200px] truncate hidden sm:table-cell" data-label="Notes" x-text="item.notes||''"></td>
                            <td class="px-3 py-3 text-center whitespace-nowrap" data-label="Actions">
                                <div class="flex justify-center gap-1 flex-wrap">
                                    <button @click="openStatusModal(item)" class="p-1.5 rounded-lg text-yellow-600 hover:bg-yellow-50 transition-colors" title="Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
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
        @include('components.pagination-footer', ['prefix' => ''])
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.200>
        <div class="fixed inset-0 bg-black/40" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto z-10">
            <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white rounded-t-2xl z-10">
                <h3 class="text-lg font-semibold text-gray-800" x-text="isEditing?'Edit Order':'Add Order'"></h3>
                <button @click="closeModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveItem()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Ordered By</label>
                    <input type="text" x-model="form.ordered_by" placeholder="Ordered by" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Order Date</label>
                    <input type="date" x-model="form.orderDate" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select x-model="form.status" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <option value="">Select Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Fulfilled">Fulfilled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea x-model="form.notes" rows="2" placeholder="Order notes..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Items</label>
                    <div class="space-y-3 mb-3">
                        <template x-for="(oi, idx) in form.items" :key="idx">
                            <div class="flex items-end gap-3 bg-gray-50 p-3 rounded-xl">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">Material</label>
                                    <select x-model="oi.material_id" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                        <option value="">Select Material</option>
                                        <template x-for="mat in materials" :key="mat.id"><option :value="mat.id" x-text="mat.materialName"></option></template>
                                    </select>
                                </div>
                                <div class="w-20">
                                    <label class="block text-xs text-gray-500 mb-1">Qty</label>
                                    <input type="number" x-model="oi.quantity" placeholder="1" min="1" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs text-gray-500 mb-1">Price</label>
                                    <input type="number" step="0.01" x-model="oi.price" placeholder="0.00" min="0" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                </div>
                                <button type="button" @click="removeOrderItem(idx)" class="text-red-500 hover:text-red-700 mb-0.5 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addOrderItem()" class="text-sm text-primary hover:text-primary-dark font-medium flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Add Item
                    </button>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl transition-colors disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="saving" class="spinner w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="saving?'Saving...':(isEditing?'Update':'Save')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Status Update Modal --}}
    <div x-show="statusModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40" @click="statusModalOpen=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Update Status</h3>
                <button @click="statusModalOpen=false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="updateStatus()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select x-model="statusForm.status" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Fulfilled">Fulfilled</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="statusModalOpen=false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-5 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl transition-colors disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="saving" class="spinner w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="saving?'Updating...':'Update Status'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="fixed inset-0 bg-black/40" @click="deleteModalOpen=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Order?</h3>
            <p class="text-sm text-gray-500 mb-5">Are you sure? This action cannot be undone.</p>
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
function orderData(){return{
    items:[],materials:[],search:'',currentPage:1,perPage:10,total:0,totalPages:1,loading:false,modalOpen:false,statusModalOpen:false,deleteModalOpen:false,isEditing:false,saving:false,form:{id:null,ordered_by:'',orderDate:'',status:'',notes:'',items:[]},statusForm:{id:null,status:'Pending'},errors:{},deleteId:null,
    get visiblePages(){var p=[],s=Math.max(1,this.currentPage-2),e=Math.min(this.totalPages,this.currentPage+2);for(var i=s;i<=e;i++)p.push(i);return p},
    async init(){await this.fetchMaterials();this.fetchItems()},
    async fetchItems(){this.loading=true;try{var d=await $store.api.get('/api/v1/material-order',{page:this.currentPage,per_page:this.perPage,search:this.search||undefined});if(d&&d.status){this.items=d.data?.data||d.data||[];this.total=d.data?.total||d.total||this.items.length;this.totalPages=d.data?.last_page||d.last_page||Math.ceil(this.total/this.perPage)||1}else{this.items=[];this.total=0;this.totalPages=1}}catch(e){console.error(e);this.items=[];$store.toast.error('Failed to load orders')}finally{this.loading=false}},
    async fetchMaterials(){try{var d=await $store.api.get('/api/v1/material',{per_page:100});this.materials=d.data?.data||d.data||[]}catch(e){}},
    statusBadgeClass(s){switch(s){case'Approved':return'bg-blue-100 text-blue-800';case'Pending':return'bg-yellow-100 text-yellow-800';case'Rejected':return'bg-red-100 text-red-800';case'Fulfilled':return'bg-green-100 text-green-800';default:return'bg-gray-100 text-gray-800'}},
    addOrderItem(){this.form.items.push({material_id:'',quantity:1,price:0})},
    removeOrderItem(idx){this.form.items.splice(idx,1)},
    openAddModal(){this.isEditing=false;this.form={id:null,ordered_by:'',orderDate:'',status:'',notes:'',items:[]};this.errors={};this.modalOpen=true},
    openEditModal(item){this.isEditing=true;this.form={id:item.id,ordered_by:item.ordered_by||'',orderDate:item.orderDate||'',status:item.status||'',notes:item.notes||'',items:(item.items||[]).map(function(i){return{material_id:i.material_id||i.materialId||'',quantity:i.quantity||1,price:i.price||0}})};this.errors={};this.modalOpen=true},
    closeModal(){this.modalOpen=false;this.form={id:null,ordered_by:'',orderDate:'',status:'',notes:'',items:[]};this.errors={}},
    async saveItem(){this.errors={};this.saving=true;try{if(this.isEditing){await $store.api.put('/api/v1/material-order/'+this.form.id,this.form)}else{await $store.api.post('/api/v1/material-order',this.form)};this.closeModal();$store.toast.success(this.isEditing?'Order updated':'Order created');this.fetchItems()}catch(e){if(e.errors)this.errors=e.errors;else $store.toast.error(e.message||'Save failed')}this.saving=false},
    openStatusModal(item){this.statusForm={id:item.id,status:item.status||'Pending'};this.statusModalOpen=true},
    async updateStatus(){this.saving=true;try{await $store.api.put('/api/v1/material-order/'+this.statusForm.id+'/status',{status:this.statusForm.status});this.statusModalOpen=false;$store.toast.success('Status updated');this.fetchItems()}catch(e){$store.toast.error(e.message||'Status update failed')}this.saving=false},
    confirmDelete(item){this.deleteId=item.id;this.deleteModalOpen=true},
    async deleteItem(){this.saving=true;try{await $store.api.del('/api/v1/material-order/'+this.deleteId);this.deleteModalOpen=false;$store.toast.success('Order deleted');this.fetchItems()}catch(e){$store.toast.error(e.message||'Delete failed')}this.saving=false},
    changePage(p){if(p>=1&&p<=this.totalPages){this.currentPage=p;this.fetchItems()}},
    changePerPage(n){n=parseInt(n);if(!n||n===this.perPage)return;this.perPage=n;this.currentPage=1;this.fetchItems()}
}}
</script>
@endpush
