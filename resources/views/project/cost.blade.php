{{-- resources/views/project/cost.blade.php --}}
@extends('layouts.admin')
@section('title', 'Project Cost Estimate')
@section('page-content')
<div class="max-w-7xl mx-auto bg-white rounded-xl shadow p-8" x-data="costData()" x-init="loadData()">
    <h1 class="text-2xl font-semibold mb-6">Project Cost Estimate</h1>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
        <select x-model="selectedProject" @change="loadEstimate()" class="px-3 py-2 border rounded-md w-64">
            <option value="">-- Select Project --</option>
            <template x-for="p in projects" :key="p.id"><option :value="p.id" x-text="p.name"></option></template>
        </select>
    </div>
    <div x-show="loading" class="text-center py-8 text-gray-500">Loading...</div>
    <div x-show="!loading && estimate" class="mt-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-blue-50 rounded-lg"><p class="text-sm text-blue-600">Total Materials Cost</p><p class="text-2xl font-bold" x-text="'$'+estimate.materialCost"></p></div>
            <div class="p-4 bg-green-50 rounded-lg"><p class="text-sm text-green-600">Total Labor Cost</p><p class="text-2xl font-bold" x-text="'$'+estimate.laborCost"></p></div>
            <div class="p-4 bg-yellow-50 rounded-lg"><p class="text-sm text-yellow-600">Other Costs</p><p class="text-2xl font-bold" x-text="'$'+estimate.otherCost"></p></div>
            <div class="p-4 bg-primary/10 rounded-lg"><p class="text-sm text-primary">Grand Total</p><p class="text-2xl font-bold" x-text="'$'+estimate.grandTotal"></p></div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function costData() {
    return {
        projects: [], selectedProject: '', estimate: null, loading: false,
        async loadData() {
            let token = localStorage.getItem('S_S_Token');
            let res = await fetch('/api/v1/project', { headers: { 'Authorization': 'Bearer ' + token } });
            let data = await res.json();
            if (data.status) this.projects = data.data;
        },
        async loadEstimate() {
            if (!this.selectedProject) return;
            this.loading = true;
            this.estimate = { materialCost: 0, laborCost: 0, otherCost: 0, grandTotal: 0 };
            this.loading = false;
        }
    }
}
</script>
@endpush
