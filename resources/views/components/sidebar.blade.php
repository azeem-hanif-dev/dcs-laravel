{{-- resources/views/components/sidebar.blade.php --}}
<aside x-data="{ expandedMenu: '{{ session('expanded_menu', '') }}' }"
    class="fixed top-0 left-0 z-40 h-screen bg-white shadow-xl sidebar-transition w-[17rem] sm:w-[19rem] flex flex-col font-urbanist -translate-x-full lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    @click.outside="sidebarOpen = false">

    {{-- Logo --}}
    <div class="py-6 px-8 flex justify-center">
        <img src="/common/distributor-logo.svg" alt="Distributor Logo" class="w-36 object-contain" />
    </div>

    {{-- Navigation --}}
    <div class="flex-1 overflow-y-auto px-3">
        <nav class="space-y-2">
            @php
            $currentRoute = request()->path();
            $navLinks = [
                ['title' => 'Dashboard', 'path' => 'company_admin', 'icon' => 'layout-dashboard' , 'isPage' => true],
                [
                    'title' => 'Projects', 'icon' => 'factory',
                    'subLinks' => [
                        ['title' => 'Project Management', 'path' => 'company_admin/project_management'],
                        ['title' => 'Work Plan', 'path' => 'company_admin/plan_management'],
                    ]
                ],
                [
                    'title' => 'HR', 'icon' => 'users',
                    'subLinks' => [
                        ['title' => 'Staff Roles', 'path' => 'company_admin/staff_role_management'],
                        ['title' => 'Staff', 'path' => 'company_admin/staff_management'],
                    ]
                ],
                [
                    'title' => 'Customers', 'icon' => 'user',
                    'subLinks' => [
                        ['title' => 'Customer Management', 'path' => 'company_admin/customer_management'],
                        ['title' => 'Quotations', 'path' => 'company_admin/qoutation_management'],
                    ]
                ],
                [
                    'title' => 'Materials', 'icon' => 'boxes',
                    'subLinks' => [
                        ['title' => 'Suppliers', 'path' => 'company_admin/suppliers'],
                        ['title' => 'Distributors', 'path' => 'company_admin/distributors'],
                        ['title' => 'Categories', 'path' => 'company_admin/material_category'],
                        ['title' => 'Materials', 'path' => 'company_admin/material'],
                        ['title' => 'Material Orders', 'path' => 'company_admin/material_order'],
                    ]
                ],
                [
                    'title' => 'Reports', 'icon' => 'clipboard',
                    'subLinks' => [
                        ['title' => 'Quality Controller', 'path' => 'company_admin/quality_controller'],
                        ['title' => 'Worker Report', 'path' => 'company_admin/worker_report'],
                        ['title' => 'Project Report', 'path' => 'company_admin/project_report'],
                    ]
                ],
            ];

            $icons = [
                'layout-dashboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>',
                'factory' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                'users' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                'user' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'boxes' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10m-8-4l8-4"/></svg>',
                'clipboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
            ];
            @endphp

            @foreach($navLinks as $link)
                @php
                    $hasSubLinks = isset($link['subLinks']);
                    $isPage = isset($link['isPage']) && $link['isPage'];
                    $linkPath = $link['path'] ?? '';
                    $isActive = $currentRoute === $linkPath;
                    if ($hasSubLinks) {
                        foreach ($link['subLinks'] as $sub) {
                            if ($currentRoute === $sub['path']) { $isActive = true; break; }
                        }
                    }
                @endphp
                <div>
                    @if($isPage)
                        {{-- Direct page link (Dashboard) --}}
                        <a href="{{ url($linkPath) }}"
                            class="group w-full flex items-center gap-3 p-3 rounded-xl transition-all font-medium
                            {{ $isActive ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                            <span class="flex-shrink-0">{!! $icons[$link['icon']] ?? '' !!}</span>
                            <span>{{ $link['title'] }}</span>
                        </a>
                    @else
                        <button @click="expandedMenu = expandedMenu === '{{ $link['title'] }}' ? '' : '{{ $link['title'] }}'"
                            class="group w-full flex items-center justify-between gap-3 p-3 rounded-xl transition-all font-medium
                            {{ $isActive ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-3 flex-1">
                                <span class="flex-shrink-0">{!! $icons[$link['icon']] ?? '' !!}</span>
                                <span>{{ $link['title'] }}</span>
                            </span>
                            <svg class="w-4 h-4 transition-transform" :class="expandedMenu === '{{ $link['title'] }}' ? 'rotate-90' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="expandedMenu === '{{ $link['title'] }}'" x-transition class="pl-10 mt-1 space-y-1">
                            @foreach($link['subLinks'] as $sub)
                            <a href="{{ url($sub['path']) }}"
                                class="block w-full text-left px-3 py-1.5 rounded-md text-sm transition-colors
                                {{ $currentRoute === $sub['path'] ? 'bg-primary text-white font-semibold' : 'text-gray-600 hover:bg-primary hover:text-white' }}">
                                {{ $sub['title'] }}
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>

    {{-- Footer --}}
    <div class="py-6 px-6 flex flex-col items-center border-t border-gray-200 text-xs font-light text-primary">
        <p class="mb-2">DS.DCS.AP.1.00</p>
        <a href="https://www.softwicks.com/" target="_blank" class="text-gray-700 hover:text-gray-900 text-sm">
            Powered By Digital Stationz
        </a>
    </div>
</aside>
