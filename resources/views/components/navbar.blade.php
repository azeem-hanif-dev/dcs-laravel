{{-- resources/views/components/navbar.blade.php --}}
<div class="bg-primary text-white h-full flex justify-between items-center rounded-b-lg px-4 sm:px-6 py-3 sm:py-4 shadow-md font-urbanist" 
    x-data="{ 
        profileOpen: false,
        userName: '',
        userRole: '',
        companyName: '',
        
        loadUserInfo() {
            try {
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                const company = JSON.parse(localStorage.getItem('company') || '{}');
                this.userName = user.name || user.full_name || user.username || 'User';
                this.userRole = user.role || '';
                this.companyName = company.companyName || user.company?.name || '{{ session('company_name', '') }}';
            } catch(e) {
                this.userName = '{{ session('username', 'User') }}';
                this.companyName = '{{ session('company_name', '') }}';
            }
        }
    }" x-init="loadUserInfo()">
    <div>
        <h2 class="text-base sm:text-xl font-semibold truncate mr-2">
            Welcome <span x-text="userName"></span>
        </h2>
        <p class="text-xs text-white/70 mt-0.5">
            <span x-text="companyName"></span>
            <template x-if="userRole">
                <span class="ml-1 px-1.5 py-0.5 bg-white/15 rounded text-[10px] uppercase tracking-wide" 
                    x-text="userRole"></span>
            </template>
        </p>
    </div>

    <div class="flex items-center gap-2 sm:gap-4">
        {{-- Language Selector --}}
        <div class="relative hidden sm:flex items-center space-x-2">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
            <select id="langSelect" onchange="document.cookie='lang='+this.value+';path=/';location.reload()"
                class="appearance-none pl-3 pr-8 py-1.5 sm:py-2 bg-white text-black rounded-lg border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                <option value="EN" {{ session('lang', 'EN') === 'EN' ? 'selected' : '' }}>English</option>
                <option value="NL" {{ session('lang') === 'NL' ? 'selected' : '' }}>Dutch</option>
            </select>
        </div>

        {{-- Profile Menu --}}
        <div class="relative" @click.outside="profileOpen = false">
            <button @click="profileOpen = !profileOpen"
                class="flex items-center gap-2 bg-primary-dark text-white px-3 py-2 rounded-lg hover:bg-[#1E8ED8]/80 transition-all duration-200 shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="hidden md:inline max-w-[15ch] truncate capitalize pr-2" x-text="userName"></span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="profileOpen" x-transition
                class="absolute right-0 mt-2 w-52 bg-white text-gray-800 rounded-lg shadow-lg py-2 z-50">
                <a href="{{ url('company_admin/profile') }}"
                    class="flex items-center gap-3 px-4 py-2 w-full text-left hover:bg-primary hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                <a href="{{ url('logout') }}" onclick="localStorage.clear(); sessionStorage.clear();"
                    class="flex items-center gap-3 px-4 py-2 w-full text-left hover:bg-primary hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>
