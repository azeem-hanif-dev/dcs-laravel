{{-- resources/views/report/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Reports - Distributor Portal')
@section('page-content')
<div x-data="reportsData()" x-init="loadData()" class="max-w-7xl mx-auto px-2 sm:px-4">
    <div class="mb-6"><h1 class="text-xl sm:text-2xl font-bold text-gray-800">Reports</h1><p class="text-sm text-gray-500 mt-0.5">Business analytics and summaries</p></div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100"><p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Total Sales (Delivered)</p><p class="text-2xl font-bold text-green-600" x-text="'$'+(summary.totalSales||0).toFixed(2)"></p></div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100"><p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Outstanding Payments</p><p class="text-2xl font-bold text-red-600" x-text="'$'+(summary.outstanding||0).toFixed(2)"></p></div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100"><p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Total Purchase Orders</p><p class="text-2xl font-bold text-blue-600" x-text="summary.totalPO||0"></p></div>
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100"><p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Total Stock Value</p><p class="text-2xl font-bold text-purple-600" x-text="'$'+(summary.stockValue||0).toFixed(2)"></p></div>
    </div>

    {{-- Top Shops --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-base font-semibold text-gray-800">Top Shops by Orders</h3></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-xs text-gray-500 uppercase"><tr><th class="px-4 py-2.5 text-left">Shop</th><th class="px-4 py-2.5 text-right">Orders</th><th class="px-4 py-2.5 text-right">Total</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                <template x-if="!loading && topShops.length === 0"><tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">No data yet</td></tr></template>
                <template x-for="s in topShops" :key="s.id"><tr class="hover:bg-gray-50"><td class="px-4 py-2.5 font-medium text-gray-800" x-text="s.name"></td><td class="px-4 py-2.5 text-right" x-text="s.orderCount||0"></td><td class="px-4 py-2.5 text-right font-semibold" x-text="'$'+(+s.totalAmount||0).toFixed(2)"></td></tr></template>
            </tbody></table></div>
        </div>

        {{-- Outstanding Invoices --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-base font-semibold text-gray-800">Outstanding Invoices</h3></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-xs text-gray-500 uppercase"><tr><th class="px-4 py-2.5 text-left">Shop</th><th class="px-4 py-2.5 text-right">Invoice Total</th><th class="px-4 py-2.5 text-right">Balance Due</th><th class="px-4 py-2.5 text-left">Due Date</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                <template x-if="!loading && outstandingInvoices.length === 0"><tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No outstanding invoices</td></tr></template>
                <template x-for="inv in outstandingInvoices" :key="inv.id"><tr class="hover:bg-gray-50"><td class="px-4 py-2.5 font-medium text-gray-800" x-text="inv.shop?.name||''"></td><td class="px-4 py-2.5 text-right" x-text="'$'+(+inv.total_amount||0).toFixed(2)"></td><td class="px-4 py-2.5 text-right font-semibold text-red-600" x-text="'$'+(+inv.balance_due||0).toFixed(2)"></td><td class="px-4 py-2.5" :class="new Date(inv.due_date) < new Date() ? 'text-red-600' : 'text-gray-600'" x-text="inv.due_date"></td></tr></template>
            </tbody></table></div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
        <a href="{{ url('company_admin/sales_orders') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition-shadow"><span class="text-lg font-semibold text-primary">Sales Orders</span><p class="text-xs text-gray-500 mt-1">View all orders</p></a>
        <a href="{{ url('company_admin/invoices') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition-shadow"><span class="text-lg font-semibold text-primary">Invoices</span><p class="text-xs text-gray-500 mt-1">View all invoices</p></a>
        <a href="{{ url('company_admin/stock_overview') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition-shadow"><span class="text-lg font-semibold text-primary">Stock</span><p class="text-xs text-gray-500 mt-1">Inventory levels</p></a>
        <a href="{{ url('company_admin/sales_returns') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition-shadow"><span class="text-lg font-semibold text-primary">Returns</span><p class="text-xs text-gray-500 mt-1">Sales returns</p></a>
    </div>
</div>
@endsection
@push('scripts')<script>function reportsData(){return{summary:{totalSales:0,outstanding:0,totalPO:0,stockValue:0},topShops:[],outstandingInvoices:[],loading:true,async loadData(){this.loading=true;try{var d=await Alpine.store('api').get('/api/v1/dashboard/counts');if(d&&d.status)this.summary=d.data;var so=await Alpine.store('api').get('/api/v1/sales-order',{per_page:100,status:'Delivered'});if(so&&so.status){var orders=Array.isArray(so.data)?so.data:(so.data?.data||[]);var shopMap={};orders.forEach(function(o){var sid=o.shop_id;if(!shopMap[sid])shopMap[sid]={id:sid,name:o.shop?.name||'',orderCount:0,totalAmount:0};shopMap[sid].orderCount++;shopMap[sid].totalAmount+=parseFloat(o.grand_total||0)});this.topShops=Object.values(shopMap).sort(function(a,b){return b.totalAmount-a.totalAmount}).slice(0,5)}var inv=await Alpine.store('api').get('/api/v1/invoice',{per_page:50,status:'Unpaid'});if(inv&&inv.status)this.outstandingInvoices=(Array.isArray(inv.data)?inv.data:(inv.data?.data||[])).filter(function(i){return(i.balance_due||0)>0}).sort(function(a,b){return new Date(a.due_date)-new Date(b.due_date)})}catch(e){console.error(e)}this.loading=false}}}</script>@endpush
