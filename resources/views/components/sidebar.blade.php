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
                ['title' => 'Dashboard', 'path' => 'company_admin', 'icon' => 'layout-dashboard', 'isPage' => true],
                [
                    'title' => 'Sales', 'icon' => 'cart',
                    'subLinks' => [
                        ['title' => 'Shops / Retailers', 'path' => 'company_admin/shops'],
                        ['title' => 'Sales Orders', 'path' => 'company_admin/sales_orders'],
                        ['title' => 'Invoices', 'path' => 'company_admin/invoices'],
                        ['title' => 'Payments', 'path' => 'company_admin/payments'],
                        ['title' => 'Deliveries', 'path' => 'company_admin/deliveries'],
                        ['title' => 'Salesmen', 'path' => 'company_admin/salesmen'],
                        ['title' => 'Sales Returns', 'path' => 'company_admin/sales_returns'],
                    ]
                ],
                [
                    'title' => 'Inventory', 'icon' => 'boxes',
                    'subLinks' => [
                        ['title' => 'Products', 'path' => 'company_admin/material'],
                        ['title' => 'Categories', 'path' => 'company_admin/material_category'],
                        ['title' => 'Warehouses', 'path' => 'company_admin/warehouses'],
                        ['title' => 'Stock Overview', 'path' => 'company_admin/stock_overview'],
                    ]
                ],
                [
                    'title' => 'Procurement', 'icon' => 'truck',
                    'subLinks' => [
                        ['title' => 'Suppliers', 'path' => 'company_admin/suppliers'],
                        ['title' => 'Distributors', 'path' => 'company_admin/distributors'],
                        ['title' => 'Purchase Orders', 'path' => 'company_admin/material_order'],
                        ['title' => 'Purchase Returns', 'path' => 'company_admin/purchase_returns'],
                    ]
                ],
                [
                    'title' => 'Customers', 'icon' => 'user',
                    'subLinks' => [
                        ['title' => 'Customer Management', 'path' => 'company_admin/customer_management'],
                    ]
                ],
                [
                    'title' => 'Reports', 'icon' => 'chart',
                    'subLinks' => [
                        ['title' => 'Business Reports', 'path' => 'company_admin/reports'],
                        ['title' => 'Quality Reports', 'path' => 'company_admin/quality_controller'],
                        ['title' => 'Worker Reports', 'path' => 'company_admin/worker_report'],
                        ['title' => 'Project Reports', 'path' => 'company_admin/project_report'],
                    ]
                ],
            ];

            $icons = [
                'layout-dashboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>',
                'cart' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>',
                'boxes' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10m-8-4l8-4"/></svg>',
                'truck' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>',
                'user' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'chart' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
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
        <a href="https://www.softwicks.com/" target="_blank" class="text-gray-700 hover:text-gray-900 text-sm">
            Powered By Digital Stationz
        </a>
    </div>
</aside>
