{{-- resources/views/profile.blade.php --}}
@extends('layouts.admin')
@section('title', 'Profile')
@section('page-content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-8" x-data="profileData()" x-init="loadProfile()">
    <h1 class="text-2xl font-semibold mb-6">User Profile</h1>
    <form @submit.prevent="updateProfile">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label><input type="text" x-model="form.name" class="w-full px-3 py-2 border rounded-md" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" x-model="form.email" class="w-full px-3 py-2 border rounded-md" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Username</label><input type="text" x-model="form.username" disabled class="w-full px-3 py-2 border rounded-md bg-gray-50" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label><input type="text" x-model="form.phone" class="w-full px-3 py-2 border rounded-md" /></div>
        </div>
        <div class="mt-6">
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary/80">Update Profile</button>
        </div>
    </form>
    <div x-show="toast" x-text="toast" class="mt-4 p-3 rounded-md" :class="toastType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"></div>
</div>
@endsection
@push('scripts')
<script>
function profileData() {
    return {
        form: { name: '', email: '', username: '', phone: '' },
        toast: '', toastType: 'success',
        async loadProfile() {
            let user = JSON.parse(localStorage.getItem('user') || '{}');
            this.form.name = user.name || ''; this.form.email = user.email || '';
            this.form.username = user.username || ''; this.form.phone = user.phone || '';
        },
        async updateProfile() {
            try {
                let token = localStorage.getItem('S_S_Token');
                let res = await fetch('/api/v1/admin/update/' + (JSON.parse(localStorage.getItem('user')||'{}').id), {
                    method: 'PUT', headers: {'Content-Type':'application/json','Authorization':'Bearer '+token},
                    body: JSON.stringify(this.form)
                });
                let data = await res.json();
                this.toast = data.message || 'Profile updated';
                this.toastType = data.status ? 'success' : 'error';
            } catch(e) { this.toast = 'Error updating profile'; this.toastType = 'error'; }
        }
    }
}
</script>
@endpush
