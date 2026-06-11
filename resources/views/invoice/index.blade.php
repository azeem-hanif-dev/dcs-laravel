{{-- resources/views/invoice/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Invoices - Distributor Portal')
@section('page-content')
<div x-data="invoiceData()" x-init="init()" class="max-w-7xl mx-auto px-2 sm:px-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
        <div><h1 class="text-xl sm:text-2xl font-bold text-gray-800">Invoices</h1><p class="text-sm text-gray-500 mt-0.5">Billing & payment tracking</p></div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 mb-3">
        <div class="flex flex-col sm:flex-row items-stretch gap-2">
            <div class="relative max-w-xs w-full"><svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input type="text" x-model="search" @input.debounce.300ms="doSearch()" placeholder="Search invoice..." class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
            <select x-model="filterStatus" @change="doSearch()" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                <option value="">All Statuses</option><option value="Unpaid">Unpaid</option><option value="Partially Paid">Partially Paid</option><option value="Paid">Paid</option><option value="Overdue">Overdue</option>
            </select>
            <button @click="search='';filterStatus='';doSearch()" class="text-xs text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition-colors whitespace-nowrap">Clear</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"><div class="table-responsive"><table class="table-card-sm min-w-full divide-y divide-gray-200">
        <thead class="table-header-branded"><tr><th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Invoice #</th><th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">Shop</th><th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">Date</th><th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider">Total</th><th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider hidden sm:table-cell">Paid</th><th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider hidden md:table-cell">Balance</th><th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider">Status</th><th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider w-28">Pay</th></tr></thead>
        <tbody class="bg-white divide-y divide-gray-100">
            <template x-if="pager.loading"><tr><td colspan="8" class="px-6 py-20 text-center"><div class="flex flex-col items-center gap-3"><svg class="spinner w-10 h-10 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><div><p class="text-sm font-medium text-gray-400">Loading data...</p><p class="text-xs text-gray-400 mt-0.5">Please wait a moment</p></div></div></td></tr></template>
            <template x-if="!pager.loading && pager.items.length === 0"><tr><td colspan="8" class="px-6 py-20 text-center"><div class="max-w-sm mx-auto"><svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg><h3 class="text-base font-semibold text-gray-400 mb-1">No Invoices Found</h3><p class="text-sm text-gray-400">Invoices appear when sales orders are confirmed</p></div></td></tr></template>
            <template x-for="(item, index) in pager.items" :key="item.id"><tr class="hover:bg-gray-50 transition-colors">
                <td class="px-3 py-3 text-sm font-mono font-medium text-primary whitespace-nowrap" data-label="Invoice #" x-text="item.invoice_number"></td>
                <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Shop" x-text="item.shop?.name||''"></td>
                <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap hidden sm:table-cell" data-label="Date" x-text="item.invoice_date"></td>
                <td class="px-3 py-3 text-sm font-semibold text-gray-700 text-right whitespace-nowrap" data-label="Total" x-text="'$'+parseFloat(item.total_amount||0).toFixed(2)"></td>
                <td class="px-3 py-3 text-sm text-green-600 text-right whitespace-nowrap hidden sm:table-cell" data-label="Paid" x-text="'$'+parseFloat(item.paid_amount||0).toFixed(2)"></td>
                <td class="px-3 py-3 text-sm text-right whitespace-nowrap hidden md:table-cell" data-label="Balance" :class="(item.balance_due||0)>0?'text-red-600 font-semibold':'text-gray-400'" x-text="'$'+parseFloat(item.balance_due||0).toFixed(2)"></td>
                <td class="px-3 py-3 text-center whitespace-nowrap" data-label="Status"><span class="px-2 py-0.5 text-xs rounded-full font-medium" :class="{'bg-red-100 text-red-700':item.status==='Unpaid'||item.status==='Overdue','bg-yellow-100 text-yellow-700':item.status==='Partially Paid','bg-green-100 text-green-700':item.status==='Paid','bg-gray-200 text-gray-600':item.status==='Cancelled'}" x-text="item.status"></span></td>
                <td class="px-3 py-3 text-center whitespace-nowrap" data-label="Pay">
                    <button @click="openPayModal(item)" :disabled="(item.balance_due||0) <= 0" class="px-3 py-1 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed">Pay</button>
                </td>
            </tr></template>
        </tbody>
    </table></div>@include('components.pagination-footer', ['prefix' => 'pager.'])</div>

    {{-- Record Payment Modal --}}
    <div x-show="payModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity @keydown.escape.window="payModalOpen=false">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="payModalOpen=false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10" @click.outside="payModalOpen=false">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Record Payment</h3>
                <button @click="payModalOpen=false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-gray-50 rounded-xl p-3 space-y-1.5 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Invoice:</span><span class="font-mono font-semibold text-gray-700" x-text="payForm.invoice_number"></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Shop:</span><span class="text-gray-700" x-text="payForm.shop_name"></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Remaining:</span><span class="font-bold text-red-600" x-text="'$'+(+payForm.balance||0).toFixed(2)"></span></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Amount <span class="text-red-500">*</span></label><input type="number" min="0.01" :max="payForm.balance" step="0.01" x-model="payForm.amount" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"><p x-show="payErrors.amount" class="text-red-500 text-xs mt-1" x-text="payErrors.amount"></p></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Method</label><select x-model="payForm.method" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"><option value="Cash">Cash</option><option value="Bank Transfer">Bank Transfer</option><option value="Cheque">Cheque</option><option value="Mobile Money">Mobile Money</option><option value="Credit Card">Credit Card</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Reference #</label><input type="text" x-model="payForm.reference" placeholder="Optional" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Date</label><input type="date" x-model="payForm.date" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></div>
                <div class="flex justify-end gap-3 pt-2"><button @click="payModalOpen=false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button><button @click="submitPayment()" :disabled="paySaving" class="px-5 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-xl transition-colors disabled:opacity-50 flex items-center gap-2"><svg x-show="paySaving" class="spinner w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span x-text="paySaving?'Processing...':'Record Payment'"></span></button></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')<script>function invoiceData(){return{pager:{loading:true,items:[],currentPage:1,perPage:10,total:0,totalPages:1,get visiblePages(){return[]},fetchPage(){return Promise.resolve(false)},setSearch(){},refresh(){return Promise.resolve(false)},goToPage(){},changePerPage(){}},search:'',filterStatus:'',payModalOpen:false,paySaving:false,payTarget:null,payForm:{invoice_number:'',shop_name:'',balance:0,amount:0,method:'Cash',reference:'',date:new Date().toISOString().split('T')[0]},payErrors:{},async init(){try{this.pager=$store.pager.create({endpoint:'/api/v1/invoice',perPage:10})}catch(e){console.error('Pager create failed:',e);this.pager.loading=false;return}this.pager.fetchPage()},doSearch(){try{var params={search:this.search||undefined};if(this.filterStatus)params.status=this.filterStatus;this.pager.setSearch(params)}catch(e){this.search=''}},openPayModal(inv){this.payTarget=inv;this.payForm={invoice_number:inv.invoice_number||'',shop_name:inv.shop?.name||'',balance:inv.balance_due||0,amount:Math.min(inv.balance_due||0,inv.total_amount||0),method:'Cash',reference:'',date:new Date().toISOString().split('T')[0]};this.payErrors={};this.payModalOpen=true},async submitPayment(){this.payErrors={};var amt=parseFloat(this.payForm.amount);if(!amt||amt<=0){this.payErrors.amount='Enter valid amount';return}if(amt>parseFloat(this.payForm.balance)){this.payErrors.amount='Amount exceeds balance due';return}this.paySaving=true;try{var d=await $store.api.post('/api/v1/payment',{invoice_id:this.payTarget.id,shop_id:this.payTarget.shop_id,amount:amt,payment_method:this.payForm.method,reference_number:this.payForm.reference,payment_date:this.payForm.date});if(d.status){$store.toast.success('Payment recorded!');this.payModalOpen=false;this.pager.refresh()}else{$store.toast.error(d.message||'Failed')}}catch(e){$store.toast.error(e.message||'Payment failed')}finally{this.paySaving=false}}}}</script>@endpush
