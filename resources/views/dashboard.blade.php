{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.admin')
@section('title', 'Dashboard - Distributor Portal')
@section('page-content')
<div x-data="dashboardData()" x-init="loadData()" class="max-w-7xl mx-auto px-2 sm:px-4">
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">Distributor overview — {{ date('F d, Y') }}</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 mb-6">
        {{-- Total Products --}}
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div></div>
            <div class="text-2xl font-bold text-gray-800" x-text="counts.products||0"></div><p class="text-xs text-gray-500 mt-1">Total Products</p>
        </div>
        {{-- Total Shops --}}
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div></div>
            <div class="text-2xl font-bold text-gray-800" x-text="counts.shops||0"></div><p class="text-xs text-gray-500 mt-1">Total Shops</p>
        </div>
        {{-- Total Suppliers --}}
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center"><svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg></div></div>
            <div class="text-2xl font-bold text-gray-800" x-text="counts.suppliers||0"></div><p class="text-xs text-gray-500 mt-1">Suppliers</p>
        </div>
        {{-- Pending Orders --}}
        <a href="{{ url('company_admin/sales_orders') }}" class="bg-white rounded-2xl shadow-sm p-5 border border-yellow-100 hover:shadow-md transition-shadow block">
            <div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center"><svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div></div>
            <div class="text-2xl font-bold text-yellow-600" x-text="counts.pendingOrders||0"></div><p class="text-xs text-gray-500 mt-1">Pending Orders</p>
        </a>
        {{-- Unpaid Invoices --}}
        <a href="{{ url('company_admin/invoices') }}" class="bg-white rounded-2xl shadow-sm p-5 border border-red-100 hover:shadow-md transition-shadow block">
            <div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center"><svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></div></div>
            <div class="text-2xl font-bold text-red-600" x-text="counts.unpaidInvoices||0"></div><p class="text-xs text-gray-500 mt-1">Unpaid Invoices</p>
        </a>
        {{-- Low Stock --}}
        <a href="{{ url('company_admin/stock_overview') }}" class="bg-white rounded-2xl shadow-sm p-5 border border-orange-100 hover:shadow-md transition-shadow block">
            <div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center"><svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg></div></div>
            <div class="text-2xl font-bold text-orange-600" x-text="counts.lowStock||0"></div><p class="text-xs text-gray-500 mt-1">Low Stock Alerts</p>
        </a>
    </div>

    {{-- Tables Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Sales Orders --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-base font-semibold text-gray-800">Recent Sales Orders</h3>
                <a href="{{ url('company_admin/sales_orders') }}" class="text-xs text-primary font-medium hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase"><tr><th class="px-4 py-2.5 text-left">SO #</th><th class="px-4 py-2.5 text-left">Shop</th><th class="px-4 py-2.5 text-right">Total</th><th class="px-4 py-2.5 text-center">Status</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="!loading && recentOrders.length === 0"><tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">No orders yet</td></tr></template>
                        <template x-for="o in recentOrders" :key="o.id"><tr class="hover:bg-gray-50"><td class="px-4 py-2.5 font-mono text-xs text-primary" x-text="o.so_number"></td><td class="px-4 py-2.5 font-medium text-gray-800" x-text="o.shop?.name||''"></td><td class="px-4 py-2.5 text-right font-semibold text-gray-700" x-text="'$'+(+o.grand_total||0).toFixed(2)"></td><td class="px-4 py-2.5 text-center"><span class="px-2 py-0.5 text-xs rounded-full font-medium" :class="{'bg-gray-200 text-gray-600':o.status==='Draft','bg-blue-100 text-blue-700':o.status==='Confirmed','bg-yellow-100 text-yellow-700':o.status==='Processing','bg-green-100 text-green-700':o.status==='Delivered','bg-red-100 text-red-700':o.status==='Cancelled'}" x-text="o.status"></span></td></tr></template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-base font-semibold text-gray-800">Recent Payments</h3>
                <a href="{{ url('company_admin/payments') }}" class="text-xs text-primary font-medium hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase"><tr><th class="px-4 py-2.5 text-left">Invoice</th><th class="px-4 py-2.5 text-left">Shop</th><th class="px-4 py-2.5 text-right">Amount</th><th class="px-4 py-2.5 text-left">Date</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="!loading && recentPayments.length === 0"><tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">No payments yet</td></tr></template>
                        <template x-for="p in recentPayments" :key="p.id"><tr class="hover:bg-gray-50"><td class="px-4 py-2.5 font-mono text-xs text-primary" x-text="p.invoice?.invoice_number||''"></td><td class="px-4 py-2.5 font-medium text-gray-800" x-text="p.shop?.name||''"></td><td class="px-4 py-2.5 text-right font-semibold text-green-600" x-text="'$'+(+p.amount||0).toFixed(2)"></td><td class="px-4 py-2.5 text-gray-600" x-text="p.payment_date"></td></tr></template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function dashboardData(){return{
    counts:{products:0,shops:0,suppliers:0,pendingOrders:0,unpaidInvoices:0,lowStock:0},recentOrders:[],recentPayments:[],loading:true,
    async loadData(){this.loading=true;try{var d=await Alpine.store('api').get('/api/v1/dashboard/counts');if(d&&d.status)this.counts=d.data;var ro=await Alpine.store('api').get('/api/v1/sales-order',{per_page:5});if(ro&&ro.status)this.recentOrders=Array.isArray(ro.data)?ro.data:(ro.data?.data||[]);var rp=await Alpine.store('api').get('/api/v1/payment',{per_page:5});if(rp&&rp.status)this.recentPayments=Array.isArray(rp.data)?rp.data:(rp.data?.data||[])}catch(e){console.error('Dashboard load error:',e)}this.loading=false}
}}
</script>
@endpush
