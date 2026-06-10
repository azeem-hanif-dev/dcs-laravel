{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard - Digital Clean Solution')

@section('page-content')
<div class="max-w-7xl mx-auto px-4" x-data="dashboardData()" x-init="loadCounts()">
    <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        <template x-for="(card, index) in cards" :key="index">
            <a :href="card.link" :target="card.target || '_self'"
                class="relative overflow-hidden bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-6 flex flex-col items-center justify-center text-center group cursor-pointer border border-gray-100">
                <div class="absolute inset-0 bg-gradient-to-b opacity-10 group-hover:opacity-20 transition-opacity"
                    :class="card.bgColor"></div>
                <div class="relative z-10 mb-3" :class="card.textColor" x-html="card.iconSvg"></div>
                <div class="relative z-10">
                    <p class="text-3xl font-bold text-gray-800" x-text="card.count"></p>
                    <p class="text-sm font-medium text-gray-600 mt-1" x-text="card.title"></p>
                </div>
            </a>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardData() {
    return {
        counts: { totalProjects:0, totalWorkers:0, totalCustomers:0, totalQuotations:0, totalInspections:0, assignedWorkers:0, totalStock:0, totalSuppliers:0, totalDistributors:0, totalPayrolls:0, totalInvoices:0 },
        colorThemes: [
            { bgColor: 'from-blue-300', textColor: 'text-blue-500' },
            { bgColor: 'from-green-300', textColor: 'text-green-500' },
            { bgColor: 'from-yellow-200', textColor: 'text-yellow-500' },
            { bgColor: 'from-red-200', textColor: 'text-red-500' }
        ],
        iconSvgMap: {
            factory: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
            users: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
            file: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
            user: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
            userlock: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
            warehouse: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
            store: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
            receipt: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
            credit: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
            euro: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.121 15.536c-1.171 1.952-3.07 1.952-4.242 0-1.172-1.953-1.172-5.119 0-7.072 1.171-1.952 3.07-1.952 4.242 0M8 10.5h4m-4 3h4m9-1.5a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            truck: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>',
            globe: '<svg class="w-[42px] h-[42px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>',
        },
        get cards() {
            let c = this.counts;
            let cardDefs = [
                { key: 'totalProjects', title: 'Total Projects', link: '/company_admin/project_management', icon: 'factory' },
                { key: 'totalWorkers', title: 'Total Workers', link: '/company_admin/staff_management', icon: 'users' },
                { key: 'totalInspections', title: 'Total Inspections', link: '/company_admin/quality_controller', icon: 'file' },
                { key: 'totalCustomers', title: 'Total Customers', link: '/company_admin/customer_management', icon: 'user' },
                { key: 'totalQuotations', title: 'Total Quotations', link: '/company_admin/qoutation_management', icon: 'receipt' },
                { key: 'assignedWorkers', title: 'Assigned Workers', link: '/company_admin/staff_management', icon: 'userlock' },
                { key: 'totalStock', title: 'Stock Management', link: '/company_admin/material', icon: 'warehouse' },
                { key: 'totalSuppliers', title: 'Total Suppliers', link: '/company_admin/suppliers', icon: 'globe' },
                { key: 'totalDistributors', title: 'Total Distributors', link: '/company_admin/distributors', icon: 'truck' },
                { key: '', title: 'Digital Store', link: 'https://www.greenwayproducts.eu/', icon: 'store', target: '_blank' },
                { key: 'totalPayrolls', title: 'Total Payrolls', link: '/company_admin/under_development', icon: 'credit' },
                { key: 'totalInvoices', title: 'Total Invoices', link: '/company_admin/under_development', icon: 'euro' },
            ];
            return cardDefs.map((def, i) => ({
                ...def,
                count: c[def.key] || '',
                bgColor: this.colorThemes[i % 4].bgColor,
                textColor: this.colorThemes[i % 4].textColor,
                iconSvg: this.iconSvgMap[def.icon] || ''
            }));
        },
        async loadCounts() {
            try {
                let token = localStorage.getItem('S_S_Token');
                let res = await fetch('/api/v1/dashboard/counts', {
                    headers: {'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json'}
                });
                let data = await res.json();
                if (data.status) this.counts = data.data;
            } catch(e) { console.error('Dashboard error:', e); }
        }
    }
}
</script>
@endpush
