@extends('layouts.admin')

@section('title', 'Task Management')

@section('page-content')
<div class="p-6" x-data="{
    allData: [],
    searchTerm: '',
    currentPage: 1,
    itemsPerPage: 10,
    loading: false,
    error: null,

    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    editItem: null,
    deleteId: null,
    newName: '',
    editName: '',

    toastMessage: '',
    toastType: 'success',
    showToast: false,

    get filteredData() {
        return this.allData.filter(item =>
            item.name.toLowerCase().includes(this.searchTerm.toLowerCase())
        );
    },
    get totalPages() {
        return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1;
    },
    get paginatedData() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        return this.filteredData.slice(start, start + this.itemsPerPage);
    },

    showNotification(message, type = 'success') {
        this.toastMessage = message;
        this.toastType = type;
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 3000);
    },

    async fetchData() {
        this.loading = true;
        this.error = null;
        try {
            const token = localStorage.getItem('token');
            const response = await fetch('/api/v1/task', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (data.status) {
                this.allData = data.data || [];
            } else {
                throw new Error(data.message || 'Failed to fetch tasks');
            }
        } catch (err) {
            this.error = err.message;
            this.showNotification(err.message, 'error');
        } finally {
            this.loading = false;
        }
    },

    openAddModal() {
        this.newName = '';
        this.showAddModal = true;
    },

    async addItem() {
        if (!this.newName.trim()) {
            this.showNotification('Name is required', 'error');
            return;
        }
        try {
            const token = localStorage.getItem('token');
            const response = await fetch('/api/v1/task', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: this.newName.trim() })
            });
            const data = await response.json();
            if (data.status) {
                this.showAddModal = false;
                this.newName = '';
                await this.fetchData();
                this.currentPage = this.totalPages > 0 ? this.totalPages : 1;
                this.showNotification(data.message || 'Task added successfully!');
            } else {
                throw new Error(data.message || 'Failed to add task');
            }
        } catch (err) {
            this.showNotification(err.message, 'error');
        }
    },

    openEditModal(item) {
        this.editItem = item;
        this.editName = item.name;
        this.showEditModal = true;
    },

    async updateItem() {
        if (!this.editName.trim()) {
            this.showNotification('Name is required', 'error');
            return;
        }
        try {
            const token = localStorage.getItem('token');
            const response = await fetch('/api/v1/task/' + this.editItem.id, {
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: this.editName.trim() })
            });
            const data = await response.json();
            if (data.status) {
                this.showEditModal = false;
                this.editItem = null;
                this.editName = '';
                await this.fetchData();
                this.showNotification(data.message || 'Task updated successfully!');
            } else {
                throw new Error(data.message || 'Failed to update task');
            }
        } catch (err) {
            this.showNotification(err.message, 'error');
        }
    },

    openDeleteModal(id) {
        this.deleteId = id;
        this.showDeleteModal = true;
    },

    async deleteItem() {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch('/api/v1/task/' + this.deleteId, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.status) {
                this.showDeleteModal = false;
                this.deleteId = null;
                await this.fetchData();
                this.showNotification(data.message || 'Task deleted successfully!');
            } else {
                throw new Error(data.message || 'Failed to delete task');
            }
        } catch (err) {
            this.showNotification(err.message, 'error');
        }
    },

    handleSearch() {
        this.currentPage = 1;
    },

    goToPage(page) {
        this.currentPage = page;
    }
}" x-init="fetchData()">
    {{-- Toast Notification --}}
    <div x-show="showToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed top-20 right-6 z-[100] px-6 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
        :class="toastType === 'success' ? 'bg-green-500' : toastType === 'error' ? 'bg-red-500' : 'bg-yellow-500'"
        x-text="toastMessage"
        style="display: none;">
    </div>

    {{-- Header --}}
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Task Management</h1>
        <div class="flex items-center gap-2">
            <button @click="openAddModal()"
                class="bg-primary text-white whitespace-nowrap px-4 h-10 rounded hover:bg-primary/80">
                Add Task
            </button>
            <input type="text" x-model="searchTerm" @input="handleSearch()"
                placeholder="Search by task name..."
                class="h-10 px-3 border rounded w-full max-w-md">
        </div>
    </div>

    {{-- Loading State --}}
    <div x-show="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
    </div>

    {{-- Error State --}}
    <div x-show="error && !loading" x-text="error" class="p-6 text-center text-red-500"></div>

    {{-- Table --}}
    <div x-show="!loading" class="overflow-x-auto rounded-xl shadow">
        <table class="table-fixed min-w-full bg-white border border-gray-200 text-sm">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="w-20 px-4 py-3 text-left border-b">Sr. No</th>
                    <th class="w-1/2 px-4 py-3 text-left border-b">Name</th>
                    <th class="w-1/3 px-4 py-3 text-left border-b">Action</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="paginatedData.length === 0">
                    <tr>
                        <td colspan="3" class="text-center py-6 text-gray-500">No tasks found.</td>
                    </tr>
                </template>
                <template x-for="(item, index) in paginatedData" :key="item.id">
                    <tr class="hover:bg-gray-50">
                        <td class="w-20 px-4 py-3 border-b" x-text="(currentPage - 1) * itemsPerPage + index + 1"></td>
                        <td class="w-1/2 px-4 py-3 border-b" x-text="item.name"></td>
                        <td class="w-1/3 px-4 py-3 border-b space-x-2">
                            <button @click="openEditModal(item)" class="text-blue-600 hover:underline">Edit</button>
                            <button @click="openDeleteModal(item.id)" class="text-red-600 hover:underline">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div x-show="totalPages > 1 && !loading" class="flex justify-end mt-4 space-x-2">
        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="disabled:opacity-50 px-2 py-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <template x-for="page in totalPages" :key="page">
            <button @click="goToPage(page)"
                class="px-3 py-1 border rounded"
                :class="currentPage === page ? 'bg-primary text-white' : ''"
                x-text="page"></button>
        </template>
        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="disabled:opacity-50 px-2 py-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>

    {{-- Add Modal --}}
    <div x-show="showAddModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white/30 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white border border-gray-200 rounded-xl shadow-xl p-6 w-[90%] max-w-md animate-fade-in" @click.outside="showAddModal = false">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Add Task</h2>
            <form @submit.prevent="addItem()">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" x-model="newName"
                        class="w-full h-10 px-3 border rounded focus:outline-none focus:ring-2 focus:ring-primary"
                        placeholder="Enter task name" required>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showAddModal = false"
                        class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 rounded bg-primary text-white hover:bg-primary/80">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white/30 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white border border-gray-200 rounded-xl shadow-xl p-6 w-[90%] max-w-md animate-fade-in" @click.outside="showEditModal = false">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Edit Task</h2>
            <form @submit.prevent="updateItem()">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" x-model="editName"
                        class="w-full h-10 px-3 border rounded focus:outline-none focus:ring-2 focus:ring-primary"
                        placeholder="Enter task name" required>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showEditModal = false"
                        class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 rounded bg-primary text-white hover:bg-primary/80">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white/30 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white border border-gray-200 rounded-xl shadow-xl p-6 w-[90%] max-w-sm text-center animate-fade-in" @click.outside="showDeleteModal = false">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Confirm Deletion</h2>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this task? This action cannot be undone.</p>
            <div class="flex justify-center gap-4">
                <button @click="showDeleteModal = false"
                    class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Cancel</button>
                <button @click="deleteItem()"
                    class="px-4 py-2 rounded bg-red-500 hover:bg-red-600 text-white">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection
