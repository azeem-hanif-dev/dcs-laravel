{{-- resources/views/sales-order/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Create Sales Order - Distributor Portal')
@section('page-content')
<div x-data="soCreateData()" x-init="init()" class="max-w-5xl mx-auto px-2 sm:px-4">
    <div class="mb-4">
        <a href="{{ url('company_admin/sales_orders') }}" class="text-sm text-primary hover:underline inline-flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>Back to Sales Orders</a>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mt-1">Create Sales Order</h1>
        <p class="text-sm text-gray-500 mt-0.5">Create a new order for a shop</p>
    </div>

    {{-- Order Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Shop <span class="text-red-500">*</span></label>
                <select x-model="form.shop_id" @change="onShopChange()" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">Select shop</option>
                    <template x-for="s in shops" :key="s.id">
                        <option :value="s.id" x-text="s.name + (s.city ? ' ('+s.city+')' : '')"></option>
                    </template>
                </select>
                <p x-show="errors.shop_id" class="text-red-500 text-xs mt-1" x-text="errors.shop_id"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Salesman</label>
                <select x-model="form.salesman_id" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">Auto (from shop)</option>
                    <template x-for="s in salesmen" :key="s.id">
                        <option :value="s.id" x-text="s.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Warehouse</label>
                <select x-model="form.warehouse_id" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">Select warehouse</option>
                    <template x-for="w in warehouses" :key="w.id">
                        <option :value="w.id" x-text="w.name"></option>
                    </template>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Order Date</label><input type="date" x-model="form.order_date" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label><input type="text" x-model="form.notes" placeholder="Optional notes..." class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
        </div>
    </div>

    {{-- Add Product Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Products</h3>
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Product</label>
                <select x-model="newItem.product_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">Select product</option>
                    <template x-for="p in products" :key="p.id">
                        <option :value="p.id" x-text="p.material_name + ' (Stock: '+(p.total_quantity - p.assigned_quantity)+')'"></option>
                    </template>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label>
                <input type="number" min="1" x-model="newItem.quantity" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Unit Price</label>
                <input type="number" min="0" step="0.01" x-model="newItem.unit_price" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Line Total</label>
                <div class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-700" x-text="'$' + ((newItem.quantity||0) * (newItem.unit_price||0)).toFixed(2)"></div>
            </div>
            <div class="sm:col-span-2">
                <button @click="addItem()" :disabled="!newItem.product_id || !newItem.quantity"
                    class="w-full bg-primary hover:bg-primary-dark text-white py-2 rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Add Item</button>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4" x-show="items.length > 0">
        <div class="table-responsive">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase">Qty</th>
                        <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Unit Price</th>
                        <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase w-16"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-for="(item, index) in items" :key="index">
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2.5 text-sm text-gray-500" x-text="index+1"></td>
                            <td class="px-3 py-2.5 text-sm font-medium text-gray-800" x-text="item.product_name"></td>
                            <td class="px-3 py-2.5 text-sm text-center text-gray-700" x-text="item.quantity"></td>
                            <td class="px-3 py-2.5 text-sm text-right text-gray-600" x-text="'$'+parseFloat(item.unit_price||0).toFixed(2)"></td>
                            <td class="px-3 py-2.5 text-sm text-right font-semibold text-gray-800" x-text="'$'+parseFloat(item.total||0).toFixed(2)"></td>
                            <td class="px-3 py-2.5 text-center">
                                <button @click="items.splice(index,1); calcTotals()" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Totals --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4" x-show="items.length > 0">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Subtotal</span><span class="font-semibold text-gray-700" x-text="'$'+subtotal.toFixed(2)"></span></div>
                <div class="flex justify-between text-sm items-center"><span class="text-gray-500">Tax (%)</span><input type="number" min="0" max="100" x-model.number="form.tax_percent" @input="calcTotals()" class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Tax Amount</span><span class="font-semibold text-gray-700" x-text="'$'+form.tax_amount.toFixed(2)"></span></div>
                <div class="flex justify-between text-sm items-center"><span class="text-gray-500">Discount</span><input type="number" min="0" step="0.01" x-model.number="form.discount" @input="calcTotals()" class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
            </div>
            <div class="flex items-end justify-end">
                <div class="text-right">
                    <p class="text-sm text-gray-500">Grand Total</p>
                    <p class="text-3xl font-bold text-primary" x-text="'$'+form.grand_total.toFixed(2)"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end gap-3" x-show="items.length > 0">
        <button @click="saveOrder('Draft')" :disabled="saving"
            class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors disabled:opacity-50">Save as Draft</button>
        <button @click="saveOrder('Confirmed')" :disabled="saving"
            class="px-5 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl transition-colors disabled:opacity-50 flex items-center gap-2">
            <svg x-show="saving" class="spinner w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <span x-text="saving ? 'Saving...' : 'Confirm Order'"></span>
        </button>
    </div>
</div>
@endsection
@push('scripts')
<script>
function soCreateData(){return{
    shops:[],salesmen:[],warehouses:[],products:[],items:[],saving:false,
    form:{shop_id:'',salesman_id:'',warehouse_id:'',order_date:new Date().toISOString().split('T')[0],notes:'',tax_percent:0,tax_amount:0,discount:0,grand_total:0},
    newItem:{product_id:'',quantity:1,unit_price:0},
    errors:{},
    get subtotal(){return this.items.reduce(function(s,i){return s+(parseFloat(i.total)||0)},0)},
    async init(){await Promise.all([this.fetchShops(),this.fetchSalesmen(),this.fetchWarehouses(),this.fetchProducts()])},
    async fetchShops(){try{var d=await Alpine.store('api').get('/api/v1/shop',{per_page:500});if(d&&d.status)this.shops=Array.isArray(d.data)?d.data:(d.data?.data||[])}catch(e){console.error(e)}},
    async fetchSalesmen(){try{var d=await Alpine.store('api').get('/api/v1/salesman',{per_page:500});if(d&&d.status)this.salesmen=Array.isArray(d.data)?d.data:(d.data?.data||[])}catch(e){console.error(e)}},
    async fetchWarehouses(){try{var d=await Alpine.store('api').get('/api/v1/warehouse',{per_page:500});if(d&&d.status)this.warehouses=Array.isArray(d.data)?d.data:(d.data?.data||[])}catch(e){console.error(e)}},
    async fetchProducts(){try{var d=await Alpine.store('api').get('/api/v1/material',{per_page:500,status:'Active'});if(d&&d.status)this.products=Array.isArray(d.data)?d.data:(d.data?.data||[])}catch(e){console.error(e)}},
    onShopChange(){var shop=this.shops.find(function(s){return s.id==this.form.shop_id},this);if(shop&&shop.salesman_id)this.form.salesman_id=shop.salesman_id},
    addItem(){if(!this.newItem.product_id||!this.newItem.quantity)return;var p=this.products.find(function(p){return p.id==this.newItem.product_id},this);var qty=parseInt(this.newItem.quantity)||1;var price=parseFloat(this.newItem.unit_price)||parseFloat(p?p.price:0)||0;this.items.push({product_id:this.newItem.product_id,product_name:p?p.material_name:'Product',quantity:qty,unit_price:price,total:qty*price});this.newItem={product_id:'',quantity:1,unit_price:0};this.calcTotals()},
    calcTotals(){this.form.tax_amount=this.subtotal*(parseFloat(this.form.tax_percent)||0)/100;this.form.grand_total=Math.max(0,this.subtotal+this.form.tax_amount-(parseFloat(this.form.discount)||0))},
    async saveOrder(status){this.errors={};if(!this.form.shop_id){this.errors.shop_id='Please select a shop';return}if(this.items.length===0){Alpine.store('toast').error('Add at least one product');return}this.calcTotals();this.saving=true;var payload={...this.form,status:status,items:this.items.map(function(i){return{product_id:i.product_id,quantity:i.quantity,unit_price:i.unit_price}})};try{var d=await Alpine.store('api').post('/api/v1/sales-order',payload);if(d.status){Alpine.store('toast').success(status==='Confirmed'?'Order confirmed!':'Draft saved!');window.location.href='/company_admin/sales_orders'}else{Alpine.store('toast').error(d.message||'Failed to save')}}catch(e){Alpine.store('toast').error(e.message||'Save failed')}finally{this.saving=false}}
}}
</script>
@endpush
