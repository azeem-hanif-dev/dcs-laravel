{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Login - Digital Clean Solution')

@section('content')
<div class="flex h-screen overflow-hidden bg-[#0f172a]" x-data="{ 
    step: 1, 
    showPassword: false, 
    loading: false, 
    errorMsg: '', 
    companyName: '', 
    companyData: null,
    username: '', 
    password: '',
    modalOpen: false,
    forgotEmail: '',
    forgotCompanyId: '',
    forgotMsg: ''
}">
    {{-- Left Side - Form --}}
    <div class="w-full lg:w-1/2 bg-[#1e293b] flex flex-col h-full">
        <div class="flex-1 flex items-center justify-center">
            <div class="max-w-md w-full p-8 rounded-xl bg-[#1e3a5f] shadow-lg">
                <div class="flex justify-center mb-6">
                    <img src="/common/Logo.svg" alt="Logo" class="h-16 w-auto" />
                </div>

                {{-- Step 1: Company Verification --}}
                <div x-show="step === 1" x-transition>
                    <h2 class="text-3xl font-bold text-center font-sans text-white mb-2">Welcome</h2>
                    <p class="text-center text-gray-400 mb-8">Enter your company name to continue</p>
                    <form @submit.prevent="async () => {
                        loading = true; errorMsg = '';
                        try {
                            let res = await fetch('/api/auth/verify-company', {
                                method: 'POST', headers: {'Content-Type':'application/json'},
                                body: JSON.stringify({companyName: companyName})
                            });
                            let data = await res.json();
                            if (data.status) { companyData = data.data; step = 2; }
                            else { errorMsg = data.message || 'Company not found'; }
                        } catch(e) { errorMsg = 'Network error'; }
                        loading = false;
                    }">
                        <div class="mb-6">
                            <label class="block text-gray-300 text-sm font-sans mb-2">Company Name</label>
                            <input type="text" x-model="companyName" required
                                class="w-full px-3 bg-[#1e293b] text-white py-3 border border-[#334155] rounded-md focus:outline-none focus:ring-1 focus:ring-[#3B82F6]"
                                placeholder="Enter your company name" autofocus />
                        </div>
                        <button type="submit" :disabled="loading"
                            class="w-full text-white bg-primary py-3 px-4 rounded-md hover:bg-primary/80 font-medium">
                            <span x-show="!loading">Continue</span>
                            <span x-show="loading">Verifying...</span>
                        </button>
                        <p x-show="errorMsg" class="mt-4 text-center text-red-500 text-sm" x-text="errorMsg"></p>
                    </form>
                </div>

                {{-- Step 2: Login --}}
                <div x-show="step === 2" x-transition>
                    <div class="flex items-center mb-4">
                        <button @click="step = 1; errorMsg = ''"
                            class="text-gray-400 hover:text-white mr-3 p-1 rounded-md hover:bg-[#1e293b]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div>
                            <h2 class="text-2xl font-bold font-sans text-white">LOGIN</h2>
                            <p class="text-sm text-gray-400" x-text="companyData?.companyName"></p>
                        </div>
                    </div>
                    <form @submit.prevent="async () => {
                        loading = true; errorMsg = '';
                        try {
                            let res = await fetch('/api/auth/login', {
                                method: 'POST', headers: {'Content-Type':'application/json'},
                                body: JSON.stringify({username, password, companyId: companyData?.companyId})
                            });
                            let data = await res.json();
                            if (data.status) {
                                localStorage.setItem('S_S_Token', data.S_S_Token);
                                localStorage.setItem('user', JSON.stringify(data.data));
                                localStorage.setItem('company', JSON.stringify(companyData));
                                window.location.href = '/company_admin';
                            } else { errorMsg = data.message || 'Login failed'; }
                        } catch(e) { errorMsg = 'Network error'; }
                        loading = false;
                    }">
                        <div class="mb-4">
                            <label class="block text-gray-300 text-sm font-sans mb-2">Username</label>
                            <input type="text" x-model="username" required
                                class="w-full px-3 bg-[#1e293b] text-white py-3 border border-[#334155] rounded-md focus:outline-none focus:ring-1 focus:ring-[#3B82F6]"
                                placeholder="Username" autofocus />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-300 text-sm font-sans mb-2">Password</label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" x-model="password" required
                                    class="w-full px-3 bg-[#1e293b] text-white py-3 border border-[#334155] rounded-md focus:outline-none focus:ring-1 focus:ring-[#3B82F6]"
                                    placeholder="Password" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-end mb-6">
                            <button type="button" @click="modalOpen = true; forgotCompanyId = companyData?.companyId"
                                class="text-sm text-primary font-sans hover:underline">Forget Password?</button>
                        </div>
                        <button type="submit" :disabled="loading"
                            class="w-full text-white bg-primary py-3 px-4 rounded-md hover:bg-primary/80 font-medium">
                            <span x-show="!loading">Login</span>
                            <span x-show="loading">Logging in...</span>
                        </button>
                        <p x-show="errorMsg" class="mt-4 text-center text-red-500 text-sm" x-text="errorMsg"></p>
                    </form>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex mb-10 flex-col items-center space-y-0.5">
            <p class="text-center text-sm text-primary">DS.DCS.AP.1.00</p>
            <a href="https://www.digitalstationz.com/" target="_blank">
                <p class="text-center text-sm text-gray-400 font-light">Powered By Digital Stationz</p>
            </a>
        </div>
    </div>

    {{-- Right Side - Image --}}
    <div class="hidden lg:flex lg:w-1/2 items-center justify-center"
        style="background-image: url('/common/Login.png'); background-size: cover; background-repeat: no-repeat;">
    </div>

    {{-- Forgot Password Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-transition>
        <div class="bg-white rounded-xl shadow-xl p-6 w-[90%] max-w-md" @click.outside="modalOpen = false">
            <h3 class="text-lg font-semibold mb-4">Forgot Password</h3>
            <form @submit.prevent="async () => {
                loading = true; forgotMsg = '';
                try {
                    let res = await fetch('/api/auth/forgot-password', {
                        method: 'POST', headers: {'Content-Type':'application/json'},
                        body: JSON.stringify({email: forgotEmail, companyId: forgotCompanyId})
                    });
                    let data = await res.json();
                    forgotMsg = data.message || 'Reset link sent';
                } catch(e) { forgotMsg = 'Error sending reset link'; }
                loading = false;
            }">
                <input type="email" x-model="forgotEmail" required placeholder="Enter your email"
                    class="w-full px-3 py-2 border rounded-md mb-4" />
                <div class="flex gap-3">
                    <button type="button" @click="modalOpen = false"
                        class="flex-1 px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" :disabled="loading"
                        class="flex-1 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/80">Send Reset Link</button>
                </div>
                <p x-show="forgotMsg" class="mt-3 text-sm text-center text-green-600" x-text="forgotMsg"></p>
            </form>
        </div>
    </div>
</div>
@endsection
