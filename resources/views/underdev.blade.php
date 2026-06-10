{{-- resources/views/underdev.blade.php --}}
@extends('layouts.admin')
@section('title', 'Under Development')
@section('page-content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
        <div class="text-6xl mb-4">🚧</div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Under Development</h1>
        <p class="text-gray-600">This feature is coming soon. Stay tuned!</p>
        <a href="/company_admin" class="mt-6 inline-block px-6 py-2 bg-primary text-white rounded-md hover:bg-primary/80">Back to Dashboard</a>
    </div>
</div>
@endsection
