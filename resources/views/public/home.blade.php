{{-- resources/views/public/home.blade.php --}}
@extends('layouts.auth')
@section('title', 'Distributor Portal')
@section('content')
<div class="min-h-screen bg-white">
    {{-- Navbar --}}
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <img src="/common/distributor-logo.svg" alt="Logo" class="h-10" />
            <div class="flex gap-4">
                <a href="/login" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/80">Login</a>
            </div>
        </div>
    </nav>
    {{-- Hero --}}
    <div class="max-w-7xl mx-auto px-4 py-20 text-center">
        <h1 class="text-5xl font-bold text-gray-900 mb-6">Professional Cleaning Solutions</h1>
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">Comprehensive cleaning management platform for modern businesses. Streamline operations, manage staff, and deliver quality service.</p>
        <a href="/login" class="px-8 py-3 bg-primary text-white rounded-lg text-lg font-medium hover:bg-primary/80 inline-block">Get Started</a>
    </div>
    {{-- Features --}}
    <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-3 gap-8">
            <div class="text-center p-6"><div class="text-4xl mb-4">&#128736;</div><h3 class="text-xl font-semibold mb-2">Project Management</h3><p class="text-gray-600">Manage cleaning projects efficiently with real-time tracking.</p></div>
            <div class="text-center p-6"><div class="text-4xl mb-4">&#128101;</div><h3 class="text-xl font-semibold mb-2">Staff Management</h3><p class="text-gray-600">Handle workforce scheduling, shifts, and attendance.</p></div>
            <div class="text-center p-6"><div class="text-4xl mb-4">&#128202;</div><h3 class="text-xl font-semibold mb-2">Quality Reports</h3><p class="text-gray-600">Generate detailed quality and inspection reports.</p></div>
        </div>
    </div>
    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-8 text-center text-sm">
        <p>Powered By Digital Stationz &copy; {{ date('Y') }}</p>
    </footer>
</div>
@endsection
