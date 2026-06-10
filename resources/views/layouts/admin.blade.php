{{-- resources/views/layouts/admin.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100" x-data="{ sidebarOpen: false }">
    {{-- Mobile sidebar toggle --}}
    <button @click="sidebarOpen = !sidebarOpen"
        class="fixed top-4 left-4 z-50 lg:hidden p-2 rounded-md bg-white shadow-md">
        <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg x-show="sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    {{-- Navbar (fixed top) --}}
    <div class="fixed top-0 left-0 right-0 z-40 lg:ml-[19rem]">
        @include('components.navbar')
    </div>

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden" x-transition.opacity></div>

    {{-- Main Content --}}
    <main class="lg:ml-[19rem] min-h-screen transition-all duration-300 p-4 pt-20 bg-cover bg-center"
        style="background-image: url('/common/Cardbg.png')">
        <div class="mx-auto">
            @yield('page-content')
        </div>
    </main>
</div>
@endsection
