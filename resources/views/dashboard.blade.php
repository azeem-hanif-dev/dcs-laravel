{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.admin')
@section('title', 'Dashboard - Distributor Portal')
@section('page-content')
<div class="max-w-7xl mx-auto px-2 sm:px-4" x-data="dashboardData()" x-init="loadCounts()">
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">Welcome back, {{ session('username', 'User') }}</p>
    </div>
    {{-- Loading --}}
    <div x-show="loading" class="grid gap-5 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        <template x-for="i in 8" :key="i">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 animate-pulse">
                <div class="flex flex-col items-center gap-3">
                    <div class="skeleton w-10 h-10 rounded-full"></div>
                    <div class="skeleton w-16 h-6 rounded"></div>
                    <div class="skeleton w-24 h-4 rounded"></div>
                </div>
            </div>
        </template>
    </div>
    {{-- Cards --}}
    <div x-show="!loading" class="grid gap-5 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        <template x-for="(card, index) in cards" :key="index">
            <a :href="card.link" :target="card.target||'_self'"
                class="relative overflow-hidden bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 p-5 flex flex-col items-center justify-center text-center group cursor-pointer border border-gray-100 hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br opacity-8 group-hover:opacity-15 transition-opacity" :class="card.bgColor"></div>
                <div class="relative z-10 mb-2" :class="card.textColor" x-html="card.iconSvg"></div>
                <div class="relative z-10">
                    <p class="text-2xl sm:text-3xl font-bold text-gray-800" x-text="card.count||0"></p>
                    <p class="text-xs sm:text-sm font-medium text-gray-500 mt-0.5" x-text="card.title"></p>
                </div>
            </a>
        </template>
    </div>
    {{-- Error --}}
    <div x-show="!loading && error" class="text-center py-12">
        <svg class="w-12 h-12 text-red-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <p class="text-gray-500" x-text="error"></p>
        <button @click="loadCounts()" class="mt-3 text-primary text-sm font-medium hover:underline">Retry</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardData(){return{
    counts:{},loading:true,error:'',
    colorThemes:[
        {bgColor:'from-blue-400/20 to-blue-500/10',textColor:'text-blue-500'},
        {bgColor:'from-green-400/20 to-green-500/10',textColor:'text-green-500'},
        {bgColor:'from-yellow-400/20 to-yellow-500/10',textColor:'text-yellow-500'},
        {bgColor:'from-red-400/20 to-red-500/10',textColor:'text-red-500'}
    ],
    iconSvgMap:{
        factory:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        users:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
        file:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        user:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        userlock:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
        warehouse:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
        store:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        receipt:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        credit:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        euro:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.121 15.536c-1.171 1.952-3.07 1.952-4.242 0-1.172-1.953-1.172-5.119 0-7.072 1.171-1.952 3.07-1.952 4.242 0M8 10.5h4m-4 3h4m9-1.5a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        truck:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>',
        globe:'<svg class="w-9 h-9 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>',
    },
    get cards(){
        let c=this.counts;
        return [
            {key:'totalProjects',title:'Projects',link:'/company_admin/project_management',icon:'factory'},
            {key:'totalWorkers',title:'Workers',link:'/company_admin/staff_management',icon:'users'},
            {key:'totalInspections',title:'Inspections',link:'/company_admin/quality_controller',icon:'file'},
            {key:'totalCustomers',title:'Customers',link:'/company_admin/customer_management',icon:'user'},
            {key:'totalQuotations',title:'Quotations',link:'/company_admin/qoutation_management',icon:'receipt'},
            {key:'assignedWorkers',title:'Assigned',link:'/company_admin/staff_management',icon:'userlock'},
            {key:'totalStock',title:'Stock Items',link:'/company_admin/material',icon:'warehouse'},
            {key:'totalSuppliers',title:'Suppliers',link:'/company_admin/suppliers',icon:'globe'},
            {key:'totalDistributors',title:'Distributors',link:'/company_admin/distributors',icon:'truck'},
            {key:'',title:'Digital Store',link:'https://www.greenwayproducts.eu/',icon:'store',target:'_blank'},
            {key:'totalPayrolls',title:'Payrolls',link:'/company_admin/under_development',icon:'credit'},
            {key:'totalInvoices',title:'Invoices',link:'/company_admin/under_development',icon:'euro'},
        ].map((d,i)=>({...d,count:c[d.key]||0,bgColor:this.colorThemes[i%4].bgColor,textColor:this.colorThemes[i%4].textColor,iconSvg:this.iconSvgMap[d.icon]||''}));
    },
    async loadCounts(){
        this.loading=true;this.error='';
        try{let d=await $store.api.get('/api/v1/dashboard/counts');if(d.status)this.counts=d.data;else this.error=d.message||'Failed to load';}
        catch(e){this.error='Could not load dashboard data';}
        this.loading=false;
    }
}}
</script>
@endpush
