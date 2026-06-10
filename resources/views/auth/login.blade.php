{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Login - Distributor Portal')

@section('content')
<div class="flex h-screen overflow-hidden bg-[#0b1a2e] relative" x-data="{
    step: 1, showPassword: false, loading: false, errorMsg: '',
    companyName: '', companyData: null, username: '', password: '',
    modalOpen: false, forgotEmail: '', forgotCompanyId: '', forgotMsg: ''
}">
    {{-- Animated background orbs --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-[#2EA8FA] rounded-full opacity-[0.06] blur-3xl animate-logo-float"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-[#1E8ED8] rounded-full opacity-[0.05] blur-3xl animate-logo-float" style="animation-delay: 3s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-[#2EA8FA] rounded-full opacity-[0.03] blur-3xl animate-logo-pulse"></div>
    </div>

    {{-- Centered Login Card --}}
    <div class="relative z-10 w-full flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            {{-- Card --}}
            <div class="bg-[#0f2744]/80 backdrop-blur-sm rounded-3xl shadow-2xl p-10 border border-[#2EA8FA]/20 ring-1 ring-white/5">
                {{-- Logo --}}
                <div class="flex justify-center mb-8">
                    <img src="/common/distributor-logo.svg" alt="Distributor Logo"
                        class="h-20 w-auto logo-white animate-logo-pulse drop-shadow-[0_0_24px_rgba(46,168,250,0.6)]" />
                </div>

                {{-- Step 1: Company Verification --}}
                <div x-show="step === 1" x-transition>
                    <h2 class="text-2xl font-bold text-center text-white mb-1">Welcome Back</h2>
                    <p class="text-center text-gray-400 text-sm mb-8">Sign in to your distributor portal</p>
                    <form @submit.prevent="async () => {
                        loading = true; errorMsg = '';
                        try {
                            let res = await fetch('/api/auth/verify-company', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({companyName}) });
                            let data = await res.json();
                            if (data.status) { companyData = data.data; step = 2; }
                            else { errorMsg = data.message || 'Company not found'; }
                        } catch(e) { errorMsg = 'Network error'; }
                        loading = false;
                    }">
                        <div class="mb-6">
                            <label class="block text-gray-400 text-xs font-medium uppercase tracking-wider mb-2">Company Name</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </span>
                                <input type="text" x-model="companyName" required
                                    class="w-full pl-12 pr-4 bg-[#0a1e36] text-white py-3.5 border border-[#2EA8FA]/20 rounded-xl focus:outline-none focus:border-[#2EA8FA] focus:ring-2 focus:ring-[#2EA8FA]/30 transition-all duration-300 placeholder-gray-500 text-sm"
                                    placeholder="Enter your company name" autofocus />
                            </div>
                        </div>
                        <button type="submit" :disabled="loading"
                            class="w-full text-white bg-gradient-to-r from-[#2EA8FA] to-[#1E8ED8] py-3.5 px-4 rounded-xl hover:from-[#3DB5FF] hover:to-[#2EA8FA] hover:shadow-lg hover:shadow-[#2EA8FA]/25 hover:-translate-y-0.5 font-semibold text-sm tracking-wide transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                            <span x-show="!loading">Continue</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="spinner w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Verifying...
                            </span>
                        </button>
                        <p x-show="errorMsg" class="mt-4 text-center text-red-400 text-sm font-medium" x-text="errorMsg"></p>
                    </form>
                </div>

                {{-- Step 2: Login --}}
                <div x-show="step === 2" x-transition>
                    <div class="flex items-center mb-6">
                        <button @click="step = 1; errorMsg = ''" class="text-gray-500 hover:text-white mr-3 p-1.5 rounded-lg hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div>
                            <h2 class="text-xl font-bold text-white">Sign In</h2>
                            <p class="text-sm text-gray-400" x-text="companyData?.companyName"></p>
                        </div>
                    </div>
                    <form @submit.prevent="async () => {
                        loading = true; errorMsg = '';
                        try {
                            let res = await fetch('/api/auth/login', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({username, password, companyId: companyData?.companyId}) });
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
                        <div class="mb-5">
                            <label class="block text-gray-400 text-xs font-medium uppercase tracking-wider mb-2">Username</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input type="text" x-model="username" required
                                    class="w-full pl-12 pr-4 bg-[#0a1e36] text-white py-3.5 border border-[#2EA8FA]/20 rounded-xl focus:outline-none focus:border-[#2EA8FA] focus:ring-2 focus:ring-[#2EA8FA]/30 transition-all duration-300 placeholder-gray-500 text-sm"
                                    placeholder="Enter your username" autofocus />
                            </div>
                        </div>
                        <div class="mb-5">
                            <label class="block text-gray-400 text-xs font-medium uppercase tracking-wider mb-2">Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input :type="showPassword ? 'text' : 'password'" x-model="password" required
                                    class="w-full pl-12 pr-12 bg-[#0a1e36] text-white py-3.5 border border-[#2EA8FA]/20 rounded-xl focus:outline-none focus:border-[#2EA8FA] focus:ring-2 focus:ring-[#2EA8FA]/30 transition-all duration-300 placeholder-gray-500 text-sm"
                                    placeholder="Enter your password" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 p-1 transition-colors">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-end mb-6">
                            <button type="button" @click="modalOpen = true; forgotCompanyId = companyData?.companyId"
                                class="text-sm text-primary hover:text-primary-light font-medium transition-colors">Forgot Password?</button>
                        </div>
                        <button type="submit" :disabled="loading"
                            class="w-full text-white bg-gradient-to-r from-[#2EA8FA] to-[#1E8ED8] py-3.5 px-4 rounded-xl hover:from-[#3DB5FF] hover:to-[#2EA8FA] hover:shadow-lg hover:shadow-[#2EA8FA]/25 hover:-translate-y-0.5 font-semibold text-sm tracking-wide transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                            <span x-show="!loading">Sign In</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="spinner w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Signing in...
                            </span>
                        </button>
                        <p x-show="errorMsg" class="mt-4 text-center text-red-400 text-sm font-medium" x-text="errorMsg"></p>
                    </form>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex mt-8 flex-col items-center space-y-1">
                <a href="https://www.digitalstationz.com/" target="_blank">
                    <p class="text-center text-xs text-gray-500 font-light hover:text-gray-400 transition-colors">Powered By Digital Stationz</p>
                </a>
            </div>
        </div>
    </div>

    {{-- Forgot Password Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" x-transition @click.outside="modalOpen = false">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-[90%] max-w-md">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-800">Forgot Password</h3>
                <button @click="modalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="async () => {
                loading = true; forgotMsg = '';
                try {
                    let res = await fetch('/api/auth/forgot-password', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({email: forgotEmail, companyId: forgotCompanyId}) });
                    let data = await res.json();
                    forgotMsg = data.message || 'Reset link sent';
                } catch(e) { forgotMsg = 'Error sending reset link'; }
                loading = false;
            }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" x-model="forgotEmail" required placeholder="you@example.com"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-colors mb-5" />
                <div class="flex gap-3">
                    <button type="button" @click="modalOpen = false"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" :disabled="loading"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl transition-colors disabled:opacity-50">Send Reset Link</button>
                </div>
                <p x-show="forgotMsg" class="mt-4 text-sm text-center text-green-600 font-medium" x-text="forgotMsg"></p>
            </form>
        </div>
    </div>
</div>
@endsection
