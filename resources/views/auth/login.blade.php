{{-- Modern SaaS-style Login Page --}}
@extends('layouts.auth')
@section('title', 'Login - Distributor Portal')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-[#0f1d36] to-slate-900 relative overflow-hidden">
    {{-- Custom login logo animation styles --}}
    <style>
        @keyframes logo-glow {
            0%, 100% { transform: scale(1); filter: brightness(0) saturate(100%) invert(1) drop-shadow(0 0 18px rgba(46,168,250,0.5)) drop-shadow(0 0 40px rgba(46,168,250,0.3)); }
            50%      { transform: scale(1.08); filter: brightness(0) saturate(100%) invert(1) drop-shadow(0 0 28px rgba(46,168,250,0.9)) drop-shadow(0 0 60px rgba(46,168,250,0.5)); }
        }
        .login-logo-animate {
            animation: logo-glow 2.5s ease-in-out infinite;
        }
        @keyframes logo-ring-pulse {
            0%, 100% { transform: scale(0.85); opacity: 0.2; border-color: rgba(46,168,250,0.4); box-shadow: 0 0 15px rgba(46,168,250,0.1); }
            50%      { transform: scale(1.2); opacity: 0.6; border-color: rgba(46,168,250,0.9); box-shadow: 0 0 35px rgba(46,168,250,0.4); }
        }
        .login-logo-ring {
            animation: logo-ring-pulse 2.5s ease-in-out infinite;
        }
        .login-logo-ring-delayed {
            animation: logo-ring-pulse 2.5s ease-in-out infinite 0.7s;
        }
        @keyframes logo-spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .login-logo-spin {
            animation: logo-spin 8s linear infinite;
        }
        .login-logo-spin-reverse {
            animation: logo-spin 10s linear infinite reverse;
        }
        @keyframes logo-blur-breathe {
            0%, 100% { transform: scale(0.9); opacity: 0.3; }
            50%      { transform: scale(1.4); opacity: 0.7; }
        }
        .login-logo-blur {
            animation: logo-blur-breathe 3s ease-in-out infinite;
        }
    </style>

    {{-- Background decorative elements --}}
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-primary/8 rounded-full blur-[100px] translate-y-1/2 -translate-x-1/4"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary/[0.03] rounded-full blur-[150px]"></div>
    </div>

    {{-- Main Content --}}
    <div class="relative z-10 w-full max-w-[480px] mx-auto px-6 py-8" x-data="{
        step: 1, showPassword: false, loading: false, errorMsg: '', successMsg: '',
        companyName: '', companyData: null, username: '', password: '', rememberMe: false,
        modalOpen: false, forgotEmail: '', forgotCompanyId: '', forgotMsg: '', forgotLoading: false,
        // Auto-focus first input on step change
        focusFirst() { $nextTick(() => { const el = this.$el.querySelector('input[autofocus]'); if(el) el.focus(); }); },
        // Slide animation helper
        get slideClass() { return 'transition-all duration-500 ease-out'; }
    }" x-init="focusFirst()" @step-changed.window="focusFirst()">

        {{-- Card with subtle glass effect --}}
        <div class="bg-white/[0.03] backdrop-blur-xl rounded-2xl border border-white/[0.08] shadow-2xl shadow-black/20 animate-fade-in-up">
            <div class="p-10 sm:p-12">

                {{-- Logo with animated glow rings --}}
                <div class="flex justify-center mb-8">
                    <div class="relative flex items-center justify-center">
                        {{-- Spinning gradient arc --}}
                        <div class="absolute w-38 h-38 rounded-full border-[2px] border-transparent border-t-primary border-r-primary/50 login-logo-spin"></div>
                        {{-- Reverse spinning arc --}}
                        <div class="absolute w-32 h-32 rounded-full border-[1.5px] border-transparent border-b-primary/70 border-l-primary/25 login-logo-spin-reverse"></div>
                        {{-- Pulsing ring 1 --}}
                        <div class="absolute w-28 h-28 rounded-full border-2 login-logo-ring"></div>
                        {{-- Pulsing ring 2 (delayed) --}}
                        <div class="absolute w-34 h-34 rounded-full border login-logo-ring-delayed"></div>
                        {{-- Center soft glow --}}
                        <div class="absolute w-24 h-24 bg-primary/40 rounded-full blur-2xl login-logo-blur"></div>
                        {{-- Logo --}}
                        <img src="/common/vilera-logo-03.png" alt="Distributor Logo"
                            class="relative h-12 sm:h-14 w-auto login-logo-animate" />
                    </div>
                </div>

                {{-- Step 1: Company Verification --}}
                <div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-400" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4">
                    <h2 class="text-3xl font-bold text-white text-center mb-2">Welcome back</h2>
                    <p class="text-gray-400 text-base text-center mb-10">Sign in to your company portal</p>

                    <form @submit.prevent="async () => {
                        loading = true; errorMsg = '';
                        try {
                            const res = await fetch('/api/auth/verify-company', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({companyName}) });
                            const data = await res.json();
                            if (data.status) { companyData = data.data; step = 2; errorMsg = ''; }
                            else { errorMsg = data.message || 'Company not found'; }
                        } catch(e) { errorMsg = 'Network error. Check your connection.'; }
                        loading = false;
                    }">
                        <div class="mb-6">
                            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2.5">Company Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <input type="text" x-model="companyName" required autofocus
                                    class="w-full pl-11 pr-4 py-3.5 bg-white/[0.06] border border-white/[0.1] rounded-xl text-white text-base placeholder-gray-500 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200"
                                    placeholder="Enter your company name" />
                            </div>
                            <p x-show="errorMsg" x-transition class="mt-2 text-red-400 text-xs flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="errorMsg"></span>
                            </p>
                        </div>

                        <button type="submit" :disabled="loading" :class="loading ? 'opacity-70 cursor-wait' : 'hover:bg-primary-dark hover:shadow-lg hover:shadow-primary/20'"
                            class="w-full bg-primary text-white py-3.5 rounded-xl font-semibold text-base transition-all duration-200 flex items-center justify-center gap-2 shadow-md shadow-primary/10">
                            <template x-if="!loading"><span>Continue</span></template>
                            <template x-if="loading">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </template>
                        </button>
                    </form>
                </div>

                {{-- Step 2: Login --}}
                <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-400" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4">
                    {{-- Back button + company info --}}
                    <div class="flex items-center gap-3 mb-6 pb-5 border-b border-white/[0.06]">
                        <button @click="step = 1; errorMsg = ''" class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/[0.06] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Sign in</h2>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="companyData?.companyName"></p>
                        </div>
                    </div>

                    <form @submit.prevent="async () => {
                        loading = true; errorMsg = '';
                        try {
                            const res = await fetch('/api/auth/login', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({username, password, companyId: companyData?.companyId}) });
                            const data = await res.json();
                            if (data.status) {
                                localStorage.setItem('S_S_Token', data.S_S_Token);
                                localStorage.setItem('user', JSON.stringify(data.data));
                                localStorage.setItem('company', JSON.stringify(companyData));
                                if (rememberMe) { localStorage.setItem('remembered_company', companyName); localStorage.setItem('remembered_user', username); }
                                else { localStorage.removeItem('remembered_company'); localStorage.removeItem('remembered_user'); }
                                window.location.href = '/company_admin';
                            } else { errorMsg = data.message || 'Invalid credentials'; }
                        } catch(e) { errorMsg = 'Network error. Check your connection.'; }
                        loading = false;
                    }">
                        {{-- Username --}}
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <input type="text" x-model="username" required autofocus
                                    class="w-full pl-11 pr-4 py-3.5 bg-white/[0.06] border border-white/[0.1] rounded-xl text-white text-base placeholder-gray-500 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200"
                                    placeholder="Enter your username" />
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <input :type="showPassword ? 'text' : 'password'" x-model="password" required
                                    class="w-full pl-11 pr-12 py-3.5 bg-white/[0.06] border border-white/[0.1] rounded-xl text-white text-base placeholder-gray-500 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200"
                                    placeholder="Enter your password" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-1 top-1/2 -translate-y-1/2 p-2 rounded-lg text-gray-500 hover:text-gray-300 hover:bg-white/[0.06] transition-colors">
                                    <svg x-show="!showPassword" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPassword" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Remember + Forgot Password --}}
                        <div class="flex items-center justify-between mb-6">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="rememberMe" class="w-4 h-4 rounded border-white/[0.15] bg-white/[0.06] text-primary focus:ring-primary/30 focus:ring-offset-0 cursor-pointer" />
                                <span class="text-xs text-gray-400">Remember me</span>
                            </label>
                            <button type="button" @click="modalOpen = true; forgotCompanyId = companyData?.companyId; forgotEmail = ''; forgotMsg = ''"
                                class="text-xs text-primary hover:text-primary-light transition-colors font-medium">Forgot password?</button>
                        </div>

                        {{-- Error Message --}}
                        <p x-show="errorMsg" x-transition class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="errorMsg"></span>
                        </p>

                        {{-- Login Button --}}
                        <button type="submit" :disabled="loading" :class="loading ? 'opacity-70 cursor-wait' : 'hover:bg-primary-dark hover:shadow-lg hover:shadow-primary/20'"
                            class="w-full bg-primary text-white py-3.5 rounded-xl font-semibold text-base transition-all duration-200 flex items-center justify-center gap-2 shadow-md shadow-primary/10">
                            <template x-if="!loading"><span>Sign in</span></template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">Signing in <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg></span>
                            </template>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-600 mt-6">
            Powered by <a href="https://www.digitalstationz.com/" target="_blank" class="text-gray-500 hover:text-gray-300 transition-colors">Digital Stationz</a>
        </p>
    </div>

    {{-- Forgot Password Modal --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity.duration.200 @keydown.escape.window="modalOpen = false">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden z-10" @click.outside="modalOpen = false">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Reset Password</h3>
                <button @click="modalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="async () => {
                forgotLoading = true; forgotMsg = '';
                try {
                    const res = await fetch('/api/auth/forgot-password', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({email: forgotEmail, companyId: forgotCompanyId}) });
                    const data = await res.json();
                    forgotMsg = data.message || 'Reset email sent successfully';
                } catch(e) { forgotMsg = 'Error. Please try again.'; }
                forgotLoading = false;
            }" class="p-6 space-y-4">
                <p class="text-sm text-gray-500">Enter your email address and we'll send you a link to reset your password.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" x-model="forgotEmail" required placeholder="you@example.com"
                        class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" />
                </div>
                <p x-show="forgotMsg" :class="forgotMsg.includes('error') || forgotMsg.includes('Error') ? 'text-red-500' : 'text-green-600'" class="text-xs" x-text="forgotMsg"></p>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="modalOpen = false" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" :disabled="forgotLoading" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-xl transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                        <template x-if="!forgotLoading"><span>Send Link</span></template>
                        <template x-if="forgotLoading"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg></template>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
