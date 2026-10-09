<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $currentTenant ? $currentTenant->name . ' — ERPlannet SaaS' : 'ERPlannet — Modern ERP Dashboard' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6.5.1 Pro-grade Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SoftFire / EduNova Pure Vanilla CSS Design System -->
    <link rel="stylesheet" href="/css/app.css">

    <!-- Server Initial State Hydration -->
    <script>
        window.SERVER_INITIAL_DATA = {
            token: @json($apiToken),
            currentTenant: @json($currentTenant),
            tenants: @json($tenants),
            plans: @json($plans),
            stats: @json($stats),
            demoTenant: @json($demoTenant),
            branches: @json($branches),
            warehouses: @json($warehouses),
            suppliers: @json($suppliers),
            ingredients: @json($ingredients),
            products: @json($products),
            categories: @json($categories),
            units: @json($units),
            customers: @json($customers),
            orders: @json($orders),
            batches: @json($batches),
            purchaseOrders: @json($purchaseOrders),
            recipes: @json($recipes),
            productionOrders: @json($productionOrders),
            qualityInspections: @json($qualityInspections),
            posTerminals: @json($posTerminals),
            posSessions: @json($posSessions),
            deliveryDrivers: @json($deliveryDrivers),
            deliveryShipments: @json($deliveryShipments),
            users: @json($users),
            roles: @json($roles),
            currentUser: @json($currentUser),
            activeView: @json($activeView)
        };
    </script>
</head>
<body>

    <div class="app-wrapper">

        <!-- ==================================================================
             Sidebar Navigation (Matching Image 1: SoftFire ERP)
             ================================================================== -->
        <aside class="app-sidebar" id="app-sidebar">
            <div class="sidebar-header">
                <div class="brand-logo-wrap">
                    <div class="brand-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                            <path d="M7 7h10"/>
                            <path d="M7 12h10"/>
                            <path d="M7 17h10"/>
                        </svg>
                    </div>
                    <div class="brand-text">
                        <h1>ERPlannet</h1>
                        <span class="brand-badge">SaaS Live</span>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <!-- Main Navigation Items -->
                <ul class="nav-menu">
                    <li>
                        <a href="#dashboard" class="nav-item-link active" data-view="dashboard" onclick="ERP.navigateTo('dashboard')">
                            <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                            <span data-i18n="dashboard">Overview</span>
                        </a>
                    </li>
                    <li>
                        <a href="#catalog" class="nav-item-link" data-view="catalog" onclick="ERP.navigateTo('catalog')">
                            <span class="nav-icon"><i class="fa-solid fa-tags"></i></span>
                            <span>Catalog &amp; Items</span>
                            <span class="nav-pill">{{ count($products) }}</span>
                        </a>
                    </li>
                    <!-- Directory Section (Տեղեկագիր) -->
                    <li style="margin-top: 0.5rem; padding: 0.35rem 0.85rem 0.15rem; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-subtle);">
                        <i class="fa-solid fa-book-bookmark" style="margin-right: 4px; color: var(--color-primary);"></i> Տեղեկագիր
                    </li>
                    <li>
                        <a href="#directory-suppliers" class="nav-item-link" data-view="directory-suppliers" onclick="ERP.navigateTo('directory-suppliers')">
                            <span class="nav-icon"><i class="fa-solid fa-truck-field"></i></span>
                            <span>Մատակարարներ</span>
                            <span class="nav-pill" id="sidebar-suppliers-count">{{ count($suppliers) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#directory-ingredients" class="nav-item-link" data-view="directory-ingredients" onclick="ERP.navigateTo('directory-ingredients')">
                            <span class="nav-icon"><i class="fa-solid fa-mortar-pestle"></i></span>
                            <span>Բաղադրիչներ</span>
                            <span class="nav-pill" id="sidebar-ingredients-count">{{ count($ingredients) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#procurement" class="nav-item-link" data-view="procurement" onclick="ERP.navigateTo('procurement')">
                            <span class="nav-icon"><i class="fa-solid fa-file-invoice"></i></span>
                            <span>Procurement</span>
                            <span class="nav-pill">{{ count($purchaseOrders) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#pos" class="nav-item-link" data-view="pos" onclick="ERP.navigateTo('pos')">
                            <span class="nav-icon"><i class="fa-solid fa-cash-register"></i></span>
                            <span data-i18n="pos">POS Դրամարկղ</span>
                            <span class="nav-pill">Live</span>
                        </a>
                    </li>
                    <li>
                        <a href="#inventory" class="nav-item-link" data-view="inventory" onclick="ERP.navigateTo('inventory')">
                            <span class="nav-icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <span data-i18n="inventory">Inventory</span>
                            <span class="nav-pill">{{ count($batches) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#manufacturing" class="nav-item-link" data-view="manufacturing" onclick="ERP.navigateTo('manufacturing')">
                            <span class="nav-icon"><i class="fa-solid fa-industry"></i></span>
                            <span data-i18n="manufacturing">Operations &amp; BOM</span>
                            <span class="nav-pill">{{ count($recipes) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#quality" class="nav-item-link" data-view="quality" onclick="ERP.navigateTo('quality')">
                            <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                            <span data-i18n="quality">Quality (QA)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#delivery" class="nav-item-link" data-view="delivery" onclick="ERP.navigateTo('delivery')">
                            <span class="nav-icon"><i class="fa-solid fa-truck-fast"></i></span>
                            <span data-i18n="delivery">Fleet Dispatch</span>
                        </a>
                    </li>
                    <li>
                        <a href="#users" class="nav-item-link" data-view="users" onclick="ERP.navigateTo('users')">
                            <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
                            <span data-i18n="users">HR &amp; Team</span>
                        </a>
                    </li>
                    <li>
                        <a href="#roles" class="nav-item-link" data-view="roles" onclick="ERP.navigateTo('roles')">
                            <span class="nav-icon"><i class="fa-solid fa-user-shield"></i></span>
                            <span data-i18n="roles">Roles &amp; RBAC</span>
                        </a>
                    </li>
                    <li>
                        <a href="#billing" class="nav-item-link" data-view="billing" onclick="ERP.navigateTo('billing')">
                            <span class="nav-icon"><i class="fa-solid fa-credit-card"></i></span>
                            <span data-i18n="billing">Finance &amp; Plans</span>
                        </a>
                    </li>
                    <li>
                        <a href="#settings" class="nav-item-link" data-view="settings" onclick="ERP.navigateTo('settings')">
                            <span class="nav-icon"><i class="fa-solid fa-sliders"></i></span>
                            <span data-i18n="settings">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="#api-console" class="nav-item-link" data-view="api-console" onclick="ERP.navigateTo('api-console')">
                            <span class="nav-icon"><i class="fa-solid fa-bolt"></i></span>
                            <span data-i18n="api_console">API Console</span>
                        </a>
                    </li>
                </ul>

                <!-- Quick Actions Sidebar Box (Exact Image 1) -->
                <div class="sidebar-quick-actions">
                    <div class="quick-actions-title">Quick Actions</div>
                    <div class="quick-actions-list">
                        <button class="quick-action-btn" onclick="ERP.procurement.openCreatePurchaseModal()">
                            <span style="width: 16px; text-align: center; color: var(--color-primary);"><i class="fa-solid fa-file-invoice-dollar"></i></span> <span>Create Purchase</span>
                        </button>
                        <button class="quick-action-btn" onclick="ERP.catalog.openCreateProductModal()">
                            <span style="width: 16px; text-align: center; color: var(--color-primary);"><i class="fa-solid fa-circle-plus"></i></span> <span>Add Product</span>
                        </button>
                        <button class="quick-action-btn" onclick="ERP.inventory.openAdjustStockModal()">
                            <span style="width: 16px; text-align: center; color: var(--color-primary);"><i class="fa-solid fa-scale-balanced"></i></span> <span>Adjust / Scrap</span>
                        </button>
                        <button class="quick-action-btn" onclick="ERP.manufacturing.openCreateRecipeModal()">
                            <span style="width: 16px; text-align: center; color: var(--color-primary);"><i class="fa-solid fa-scroll"></i></span> <span>New BOM Recipe</span>
                        </button>
                    </div>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user-card">
                    <div class="user-avatar" id="sidebar-user-avatar">EJ</div>
                    <div class="user-info">
                        <div class="name" id="sidebar-user-name">Emily Johnson</div>
                        <div class="role" id="sidebar-user-role">Admin</div>
                    </div>
                    <button onclick="ERP.auth.logout()" title="Logout" style="color: var(--text-muted); margin-left: auto; padding: 4px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- ==================================================================
             Main Content Area
             ================================================================== -->
        <div class="app-main">

            <!-- Top Header Bar (Matching Image 1 & 2) -->
            <header class="app-header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobile-menu-toggle"><i class="fa-solid fa-bars"></i></button>

                    <!-- Active Tenant Selector -->
                    <div class="header-tenant-selector" onclick="ERP.toast('Tenant context: {{ request()->getHost() }}', 'info')">
                        <span class="tenant-dot"></span>
                        <span>{{ $currentTenant ? $currentTenant->name : 'Armenia Gourmet Food' }}</span>
                        <span style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono);">({{ request()->getHost() }})</span>
                    </div>

                    <!-- Global Search Bar -->
                    <div class="header-search">
                        <input type="text" placeholder="Search operations, teams, and workflows..." data-i18n-placeholder="search_placeholder" onkeyup="if(event.key === 'Enter') ERP.toast('Search: ' + this.value, 'info')">
                        <span class="search-shortcut">⌘K</span>
                    </div>
                </div>

                <div class="header-right">
                    <!-- Environment Pill -->
                    <div style="font-size: 0.72rem; font-family: var(--font-mono); font-weight: 700; color: var(--color-primary); background: var(--color-primary-light); padding: 5px 10px; border-radius: var(--radius-full); border: 1px solid var(--color-primary-border);">
                        PostgreSQL 18 &bull; Port 8000
                    </div>

                    <!-- Date Range Pill (Image 1 style) -->
                    <div class="header-date-pill">
                        <span style="color: var(--color-primary);"><i class="fa-regular fa-calendar-days"></i></span>
                        <span>May 1 – May 31, 2026</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; color: var(--text-muted);"></i>
                    </div>

                    <!-- Notification Bell -->
                    <button class="header-icon-btn" onclick="ERP.toast('3 unread system alerts', 'info')">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge-dot"></span>
                    </button>

                    <!-- Language Switcher -->
                    <div style="position: relative;">
                        <button class="lang-selector-btn" onclick="document.getElementById('lang-dropdown').classList.toggle('active')">
                            <span id="current-lang-label">🇦🇲 Հայ</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; color: var(--text-muted);"></i>
                        </button>
                        <div id="lang-dropdown" style="display: none; position: absolute; right: 0; top: 110%; background: #ffffff; border: 1px solid var(--border-card); border-radius: var(--radius-md); box-shadow: var(--shadow-lg); min-width: 140px; z-index: 50; padding: 4px;">
                            <div onclick="ERP.setLocale('hy'); document.getElementById('lang-dropdown').classList.remove('active');" style="padding: 7px 12px; cursor: pointer; border-radius: 6px; font-size: 0.82rem; font-weight: 700;">🇦🇲 Հայերեն</div>
                            <div onclick="ERP.setLocale('en'); document.getElementById('lang-dropdown').classList.remove('active');" style="padding: 7px 12px; cursor: pointer; border-radius: 6px; font-size: 0.82rem; font-weight: 700;">🇺🇸 English</div>
                            <div onclick="ERP.setLocale('ru'); document.getElementById('lang-dropdown').classList.remove('active');" style="padding: 7px 12px; cursor: pointer; border-radius: 6px; font-size: 0.82rem; font-weight: 700;">🇷🇺 Русский</div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown Pill -->
                    <div style="position: relative;">
                        <div class="header-user-profile" onclick="document.getElementById('user-menu-dropdown').classList.toggle('active')" style="cursor: pointer;">
                            <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.72rem;">{{ auth()->check() ? mb_substr(auth()->user()->name, 0, 2) : 'EJ' }}</div>
                            <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-heading);" id="header-user-name">{{ auth()->check() ? auth()->user()->name : 'Emily Johnson' }}</span>
                            <span style="font-size: 0.65rem; color: var(--text-muted);">{{ auth()->check() && auth()->user()->is_owner ? 'Owner' : 'Admin' }} <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem;"></i></span>
                        </div>
                        <div id="user-menu-dropdown" style="display: none; position: absolute; right: 0; top: 115%; background: #ffffff; border: 1px solid var(--border-card); border-radius: var(--radius-md); box-shadow: var(--shadow-lg); min-width: 220px; z-index: 60; padding: 6px;">
                            <div style="padding: 8px 12px; border-bottom: 1px solid #F1F5F9; font-size: 0.78rem; color: #64748B;">
                                <strong style="color: #1E293B; display: block;">{{ auth()->check() ? auth()->user()->email : 'aram@gourmet.am' }}</strong>
                                <span>{{ $currentTenant ? $currentTenant->name : 'Armenia Gourmet Food' }}</span>
                            </div>
                            <a href="/login" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 0.82rem; font-weight: 600; color: #334155; text-decoration: none; border-radius: 6px;">
                                <i class="fa-solid fa-arrow-right-to-bracket" style="width: 16px; color: var(--color-primary);"></i> <span>Մուտք / Փոխել հաշիվը</span>
                            </a>
                            <a href="/register" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 0.82rem; font-weight: 600; color: #2563EB; text-decoration: none; border-radius: 6px;">
                                <i class="fa-solid fa-rocket" style="width: 16px;"></i> <span>Գրանցել կազմակերպություն</span>
                            </a>
                            <a href="/password-reset" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 0.82rem; font-weight: 600; color: #334155; text-decoration: none; border-radius: 6px;">
                                <i class="fa-solid fa-key" style="width: 16px; color: var(--color-warning);"></i> <span>Գաղտնաբառի վերականգնում</span>
                            </a>
                            <div style="border-top: 1px solid #F1F5F9; margin-top: 4px; padding-top: 4px;">
                                <button onclick="ERP.auth.logout()" style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 0.82rem; font-weight: 600; color: #DC2626; background: none; border: none; cursor: pointer; border-radius: 6px; text-align: left;">
                                    <i class="fa-solid fa-arrow-right-from-bracket" style="width: 16px;"></i> <span>Դուրս գալ (Logout)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Viewport Content -->
            <main class="app-content">

                <!-- ==============================================================
                     VIEW 1: DASHBOARD (Matching Image 1: SoftFire ERP)
                     =======                    <!-- Welcome Greeting Header -->
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">
                                <span>Welcome back, <span id="welcome-user-name">Emily</span>!</span>
                                <span class="animate-wave"><i class="fa-solid fa-hand" style="color: #f59e0b; font-size: 1.2rem;"></i></span>
                            </h2>
                            <p class="welcome-subtitle">Here's what's happening in your business today.</p>
                        </div>
                        <div style="display: flex; gap: 0.6rem;">
                            <button class="btn btn-secondary btn-sm" onclick="ERP.toast('Dashboard data refreshed', 'success')">
                                <i class="fa-solid fa-arrows-rotate"></i> Refresh
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="ERP.navigateTo('pos')">
                                <i class="fa-solid fa-cash-register"></i> Open POS Cashier
                            </button>
                        </div>
                    </div>

                    <!-- 5 Top KPI Cards (Matching Image 1) -->
                    <div class="kpi-grid-5">
                        <!-- 1. Total Revenue -->
                        <div class="kpi-card">
                            <div class="kpi-card-header">
                                <div class="kpi-icon-badge kpi-icon-green"><i class="fa-solid fa-sack-dollar"></i></div>
                                <span class="kpi-title" data-i18n="revenue">Total Revenue</span>
                            </div>
                            <div class="kpi-value" style="color: var(--color-success);">
                                ${{ number_format($stats['total_revenue'] > 0 ? $stats['total_revenue'] : 1246800, 0) }}
                            </div>
                            <div class="kpi-trend trend-green">
                                ↑ 12.6% <span style="color: var(--text-muted); font-weight: 500;">vs last month</span>
                            </div>
                        </div>

                        <!-- 2. Total Expenses -->
                        <div class="kpi-card">
                            <div class="kpi-card-header">
                                <div class="kpi-icon-badge kpi-icon-orange"><i class="fa-solid fa-boxes-packing"></i></div>
                                <span class="kpi-title">Total Expenses</span>
                            </div>
                            <div class="kpi-value">$834,250</div>
                            <div class="kpi-trend trend-green">
                                ↑ 8.4% <span style="color: var(--text-muted); font-weight: 500;">vs last month</span>
                            </div>
                        </div>

                        <!-- 3. Net Profit -->
                        <div class="kpi-card">
                            <div class="kpi-card-header">
                                <div class="kpi-icon-badge kpi-icon-blue"><i class="fa-solid fa-chart-line"></i></div>
                                <span class="kpi-title">Net Profit</span>
                            </div>
                            <div class="kpi-value" style="color: var(--color-primary);">$412,550</div>
                            <div class="kpi-trend trend-green">
                                ↑ 15.3% <span style="color: var(--text-muted); font-weight: 500;">vs last month</span>
                            </div>
                        </div>

                        <!-- 4. Open Projects / Orders -->
                        <div class="kpi-card">
                            <div class="kpi-card-header">
                                <div class="kpi-icon-badge kpi-icon-purple"><i class="fa-solid fa-clipboard-list"></i></div>
                                <span class="kpi-title">Open Projects</span>
                            </div>
                            <div class="kpi-value">{{ $stats['orders_count'] > 0 ? $stats['orders_count'] : 24 }}</div>
                            <div class="kpi-trend trend-green">
                                ↑ 9% <span style="color: var(--text-muted); font-weight: 500;">vs last month</span>
                            </div>
                        </div>

                        <!-- 5. Active Employees -->
                        <div class="kpi-card">
                            <div class="kpi-card-header">
                                <div class="kpi-icon-badge kpi-icon-cyan"><i class="fa-solid fa-users"></i></div>
                                <span class="kpi-title">Active Employees</span>
                            </div>
                            <div class="kpi-value">128</div>
                            <div class="kpi-trend trend-green">
                                ↑ 6% <span style="color: var(--text-muted); font-weight: 500;">vs last month</span>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Charts & Visuals (Matching Image 1) -->
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                        <!-- Revenue vs Expenses Curve -->
                        <div class="chart-card">
                            <div class="card-header" style="margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Revenue vs Expenses</h3>
                                </div>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="chart-legend">
                                        <span><span class="legend-dot" style="background: var(--color-primary);"></span> Revenue</span>
                                        <span><span class="legend-dot" style="background: var(--color-orange);"></span> Expenses</span>
                                    </div>
                                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); border: 1px solid var(--border-card); padding: 4px 10px; border-radius: var(--radius-sm); cursor: pointer;">
                                        This Month <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- SVG Smooth Bezier Curve Chart -->
                            <div class="chart-container">
                                <svg class="chart-svg" viewBox="0 0 600 200" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="blueGradient" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#2563eb" stop-opacity="0.25"/>
                                            <stop offset="100%" stop-color="#2563eb" stop-opacity="0.0"/>
                                        </linearGradient>
                                    </defs>
                                    <!-- Horizontal Grid Lines -->
                                    <line x1="0" y1="40" x2="600" y2="40" stroke="#f1f5f9" stroke-width="1.5"/>
                                    <line x1="0" y1="90" x2="600" y2="90" stroke="#f1f5f9" stroke-width="1.5"/>
                                    <line x1="0" y1="140" x2="600" y2="140" stroke="#f1f5f9" stroke-width="1.5"/>
                                    <line x1="0" y1="190" x2="600" y2="190" stroke="#f1f5f9" stroke-width="1.5"/>

                                    <!-- Blue Revenue Curve Fill & Line -->
                                    <path d="M 0 140 C 60 130, 90 90, 150 90 C 210 90, 240 120, 300 110 C 360 100, 400 40, 480 50 C 530 60, 560 45, 600 40 L 600 200 L 0 200 Z" fill="url(#blueGradient)"/>
                                    <path d="M 0 140 C 60 130, 90 90, 150 90 C 210 90, 240 120, 300 110 C 360 100, 400 40, 480 50 C 530 60, 560 45, 600 40" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/>

                                    <!-- Orange Expenses Curve Line -->
                                    <path d="M 0 160 C 60 150, 110 130, 180 125 C 240 120, 310 140, 370 135 C 440 130, 490 100, 550 110 C 570 115, 590 115, 600 110" fill="none" stroke="#f97316" stroke-width="2.5" stroke-linecap="round"/>

                                    <!-- Dots on points -->
                                    <circle cx="150" cy="90" r="4.5" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                                    <circle cx="300" cy="110" r="4.5" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                                    <circle cx="480" cy="50" r="5" fill="#2563eb" stroke="#fff" stroke-width="2.5"/>

                                    <circle cx="180" cy="125" r="4" fill="#f97316" stroke="#fff" stroke-width="2"/>
                                    <circle cx="370" cy="135" r="4" fill="#f97316" stroke="#fff" stroke-width="2"/>
                                </svg>

                                <!-- X-Axis Labels -->
                                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-subtle); margin-top: 0.5rem; font-family: var(--font-mono);">
                                    <span>May 1</span>
                                    <span>May 8</span>
                                    <span>May 15</span>
                                    <span>May 22</span>
                                    <span>May 31</span>
                                </div>
                            </div>
                        </div>

                        <!-- Workflow Status Donut Chart (Image 1) -->
                        <div class="chart-card">
                            <div class="card-header" style="margin-bottom: 0.5rem;">
                                <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Workflow Status</h3>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">All Workflows <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i></div>
                            </div>

                            <div class="donut-wrap">
                                <div class="donut-svg-box">
                                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#10b981" stroke-width="14" stroke-dasharray="85 240"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#2563eb" stroke-width="14" stroke-dasharray="76 240" stroke-dashoffset="-85"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#f59e0b" stroke-width="14" stroke-dasharray="43 240" stroke-dashoffset="-161"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#ef4444" stroke-width="14" stroke-dasharray="34 240" stroke-dashoffset="-204"/>
                                    </svg>
                                    <div class="donut-center-text">
                                        <span class="donut-center-label">Total</span>
                                        <span class="donut-center-num">56</span>
                                    </div>
                                </div>
                                <div class="donut-legend-list">
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #10b981;"></span> Completed</div>
                                        <div class="right">20 (35.7%)</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #2563eb;"></span> In Progress</div>
                                        <div class="right">18 (32.1%)</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #f59e0b;"></span> Pending</div>
                                        <div class="right">10 (17.9%)</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #ef4444;"></span> Blocked</div>
                                        <div class="right">8 (14.3%)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Inventory Overview, Pending Procurement & Projects (Image 1) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                        <!-- Inventory Overview -->
                        <div class="card">
                            <div class="card-header" style="margin-bottom: 0.5rem;">
                                <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--text-heading);">Inventory Overview</h3>
                                <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">All Warehouses <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i></span>
                            </div>
                            <div class="donut-wrap">
                                <div class="donut-svg-box" style="width: 120px; height: 120px;">
                                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#10b981" stroke-width="14" stroke-dasharray="144 240"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#f59e0b" stroke-width="14" stroke-dasharray="53 240" stroke-dashoffset="-144"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#ef4444" stroke-width="14" stroke-dasharray="31 240" stroke-dashoffset="-197"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#2563eb" stroke-width="14" stroke-dasharray="12 240" stroke-dashoffset="-228"/>
                                    </svg>
                                    <div class="donut-center-text">
                                        <span class="donut-center-label">Items</span>
                                        <span class="donut-center-num">2,350</span>
                                    </div>
                                </div>
                                <div class="donut-legend-list">
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #10b981;"></span> In Stock</div>
                                        <div class="right">1,420</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #f59e0b;"></span> Low Stock</div>
                                        <div class="right">520</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #ef4444;"></span> Out of Stock</div>
                                        <div class="right">310</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #2563eb;"></span> On Order</div>
                                        <div class="right">100</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Procurement (Image 1) -->
                        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                            <div class="card-header" style="margin-bottom: 0.5rem;">
                                <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--text-heading);">Pending Procurement</h3>
                            </div>
                            <div style="display: flex; align-items: center; gap: 1rem; margin: 1rem 0;">
                                <div style="width: 52px; height: 52px; border-radius: var(--radius-md); background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </div>
                                <div>
                                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-heading); font-family: var(--font-mono); line-height: 1;">18</div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">Purchase Orders Pending</div>
                                </div>
                            </div>
                            <div style="border-top: 1px solid var(--border-card); padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">Total Value</div>
                                    <div style="font-size: 1.1rem; font-weight: 800; color: var(--text-heading); font-family: var(--font-mono);">$245,760</div>
                                </div>
                                <button class="btn btn-sm btn-primary" onclick="ERP.toast('Viewing all purchase orders', 'info')">View All POs</button>
                            </div>
                        </div>

                        <!-- Projects Overview (Image 1) -->
                        <div class="card">
                            <div class="card-header" style="margin-bottom: 0.5rem;">
                                <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--text-heading);">Projects Overview</h3>
                                <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">All Projects <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i></span>
                            </div>
                            <div class="donut-wrap">
                                <div class="donut-svg-box" style="width: 120px; height: 120px;">
                                    <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#10b981" stroke-width="14" stroke-dasharray="100 240"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#f59e0b" stroke-width="14" stroke-dasharray="70 240" stroke-dashoffset="-100"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#ef4444" stroke-width="14" stroke-dasharray="40 240" stroke-dashoffset="-170"/>
                                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#2563eb" stroke-width="14" stroke-dasharray="30 240" stroke-dashoffset="-210"/>
                                    </svg>
                                    <div class="donut-center-text">
                                        <span class="donut-center-label">Total</span>
                                        <span class="donut-center-num">24</span>
                                    </div>
                                </div>
                                <div class="donut-legend-list">
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #10b981;"></span> On Track</div>
                                        <div class="right">10 (41.7%)</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #f59e0b;"></span> At Risk</div>
                                        <div class="right">7 (29.2%)</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #ef4444;"></span> Delayed</div>
                                        <div class="right">4 (16.7%)</div>
                                    </div>
                                    <div class="donut-legend-item">
                                        <div class="left"><span class="legend-dot" style="background: #2563eb;"></span> Completed</div>
                                        <div class="right">3 (12.4%)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Team Tasks & Recent Activities (Image 1) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                        <!-- Team Tasks -->
                        <div class="card">
                            <div class="card-header">
                                <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Team Tasks</h3>
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">My Tasks <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i></span>
                            </div>
                            <div>
                                <div class="task-item">
                                    <div class="task-left">
                                        <input type="checkbox" class="task-checkbox">
                                        <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.7rem; background: #60a5fa;">RB</div>
                                        <div>
                                            <div class="task-title">Review Q2 Budget &amp; Allocations</div>
                                            <div class="task-dept">Finance</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span class="task-date">May 25</span>
                                        <span class="badge-priority priority-high">High</span>
                                    </div>
                                </div>

                                <div class="task-item">
                                    <div class="task-left">
                                        <input type="checkbox" class="task-checkbox" checked>
                                        <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.7rem; background: #34d399;">IA</div>
                                        <div>
                                            <div class="task-title">Inventory Batch Expiry Audit</div>
                                            <div class="task-dept">Operations</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span class="task-date">May 27</span>
                                        <span class="badge-priority priority-medium">Medium</span>
                                    </div>
                                </div>

                                <div class="task-item">
                                    <div class="task-left">
                                        <input type="checkbox" class="task-checkbox">
                                        <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.7rem; background: #f472b6;">ON</div>
                                        <div>
                                            <div class="task-title">Onboard 3 New Factory Dispatchers</div>
                                            <div class="task-dept">HR &amp; Team</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span class="task-date">May 28</span>
                                        <span class="badge-priority priority-high">High</span>
                                    </div>
                                </div>

                                <div class="task-item">
                                    <div class="task-left">
                                        <input type="checkbox" class="task-checkbox">
                                        <div class="user-avatar" style="width: 28px; height: 28px; font-size: 0.7rem; background: #a78bfa;">SE</div>
                                        <div>
                                            <div class="task-title">Supplier Contract Price Evaluation</div>
                                            <div class="task-dept">Procurement</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span class="task-date">May 30</span>
                                        <span class="badge-priority priority-medium">Medium</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="card">
                            <div class="card-header">
                                <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Recent Activities</h3>
                                <a href="javascript:void(0)" onclick="ERP.toast('Showing all activities', 'info')" style="font-size: 0.78rem; color: var(--color-primary); font-weight: 700;">View All</a>
                            </div>
                            <div>
                                <div class="activity-item">
                                    <div class="activity-left">
                                        <div class="activity-icon-badge" style="background: var(--color-success-bg); color: var(--color-success);"><i class="fa-solid fa-check"></i></div>
                                        <div>
                                            <div class="activity-title">PO #PO-1245 approved</div>
                                            <div class="activity-meta">by David Lee</div>
                                        </div>
                                    </div>
                                    <span class="activity-time">1h ago</span>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-left">
                                        <div class="activity-icon-badge" style="background: var(--color-primary-light); color: var(--color-primary);"><i class="fa-solid fa-user-plus"></i></div>
                                        <div>
                                            <div class="activity-title">New employee Sarah Johnson joined</div>
                                            <div class="activity-meta">HR Team</div>
                                        </div>
                                    </div>
                                    <span class="activity-time">3h ago</span>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-left">
                                        <div class="activity-icon-badge" style="background: var(--color-purple-bg); color: var(--color-purple);"><i class="fa-solid fa-file-pen"></i></div>
                                        <div>
                                            <div class="activity-title">BOM Recipe #RCP-2026-001 updated</div>
                                            <div class="activity-meta">by Aram Petrosyan</div>
                                        </div>
                                    </div>
                                    <span class="activity-time">5h ago</span>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-left">
                                        <div class="activity-icon-badge" style="background: var(--color-warning-bg); color: var(--color-warning);"><i class="fa-solid fa-credit-card"></i></div>
                                        <div>
                                            <div class="activity-title">Invoice INV-0987 paid</div>
                                            <div class="activity-meta">by Finance Team</div>
                                        </div>
                                    </div>
                                    <span class="activity-time">6h ago</span>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-left">
                                        <div class="activity-icon-badge" style="background: var(--color-cyan-bg); color: var(--color-cyan);"><i class="fa-solid fa-warehouse"></i></div>
                                        <div>
                                            <div class="activity-title">Inventory stock updated</div>
                                            <div class="activity-meta">Main Warehouse</div>
                                        </div>
                                    </div>
                                    <span class="activity-time">1d ago</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 5: Top Suppliers Table (Matching Image 1) -->
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Top Suppliers</h3>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">This Month <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Category</th>
                                        <th>Total Spend</th>
                                        <th>Orders</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--text-heading);">Global Tech Solutions</td>
                                        <td>IT Equipment</td>
                                        <td class="font-mono" style="font-weight: 700;">$78,650</td>
                                        <td class="font-mono">12</td>
                                        <td><span style="color: #f59e0b;"><i class="fa-solid fa-star"></i> 4.8</span></td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--text-heading);">Office Supplies Co.</td>
                                        <td>Office Supplies</td>
                                        <td class="font-mono" style="font-weight: 700;">$45,230</td>
                                        <td class="font-mono">18</td>
                                        <td><span style="color: #f59e0b;"><i class="fa-solid fa-star"></i> 4.6</span></td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--text-heading);">BuildRight Materials</td>
                                        <td>Construction &amp; Raw</td>
                                        <td class="font-mono" style="font-weight: 700;">$36,890</td>
                                        <td class="font-mono">8</td>
                                        <td><span style="color: #f59e0b;"><i class="fa-solid fa-star"></i> 4.5</span></td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--text-heading);">Logistics Express</td>
                                        <td>Logistics &amp; Fleet</td>
                                        <td class="font-mono" style="font-weight: 700;">$28,740</td>
                                        <td class="font-mono">15</td>
                                        <td><span style="color: #f59e0b;"><i class="fa-solid fa-star"></i> 4.7</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: CATALOG & ITEM MASTER (7 Types)
                     ============================================================== -->
                <section class="view-panel" id="view-catalog" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Product Catalog &amp; Item Master (7 Item Types)</h2>
                            <p class="welcome-subtitle">Finished goods, semi-finished, raw materials, ingredients, packaging, services, and modifiers.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="ERP.catalog.openCreateProductModal()">
                            <i class="fa-solid fa-plus"></i> Add New Product
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Master Product Directory</h3>
                            <span class="badge badge-indigo">{{ count($products) }} Products</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Unit</th>
                                        <th>Cost Price</th>
                                        <th>Sale Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $p)
                                        @php
                                            $typeBadges = [
                                                'finished_product' => 'badge-emerald',
                                                'semi_finished' => 'badge-indigo',
                                                'ingredient' => 'badge-amber',
                                                'raw_material' => 'badge-cyan',
                                                'packaging' => 'badge-slate',
                                                'service' => 'badge-violet',
                                                'modifier' => 'badge-amber',
                                            ];
                                            $badgeClass = $typeBadges[$p->type ?? 'finished_product'] ?? 'badge-slate';
                                        @endphp
                                        <tr>
                                            <td class="font-mono" style="font-weight: 700; color: var(--color-primary);">{{ $p->sku }}</td>
                                            <td style="font-weight: 700; color: var(--text-heading);">
                                                {{ is_array($p->name) ? ($p->name['hy'] ?? reset($p->name)) : $p->name }}
                                                @if(is_array($p->name) && isset($p->name['en']) && $p->name['en'] !== ($p->name['hy'] ?? ''))
                                                    <span style="font-size: 0.72rem; color: var(--text-muted); display: block;">{{ $p->name['en'] }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ strtoupper(str_replace('_', ' ', $p->type ?? 'finished_product')) }}
                                                </span>
                                            </td>
                                            <td>{{ $p->category?->name ? (is_array($p->category->name) ? ($p->category->name['hy'] ?? reset($p->category->name)) : $p->category->name) : 'General' }}</td>
                                            <td class="font-mono">{{ $p->unit?->symbol ?? 'pcs' }}</td>
                                            <td class="font-mono">{{ number_format($p->cost_price ?? 0, 0) }} ֏</td>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-success);">{{ number_format($p->sale_price ?? 0, 0) }} ֏</td>
                                            <td>
                                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.inventory.openAdjustStockModal(); const sel = document.getElementById('adj-product-id'); if(sel) sel.value='{{ $p->id }}';" style="font-size: 0.72rem; padding: 3px 8px;">
                                                    <i class="fa-solid fa-scale-balanced"></i> Stock
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">No products registered yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: DIRECTORY - SUPPLIERS (Տեղեկագիր: Մատակարարներ)
                     ============================================================== -->
                <section class="view-panel" id="view-directory-suppliers" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title"><i class="fa-solid fa-truck-field" style="color: var(--color-primary); margin-right: 8px;"></i> Տեղեկագիր: Մատակարարներ</h2>
                            <p class="welcome-subtitle">Մատակարարների ռեեստր, ՀՎՀՀ, կոնտակտներ, առաքման հասցեներ, առաքիչների պարկ, կցված ապրանքներ և զամբյուղ:</p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button class="btn btn-primary btn-sm" onclick="ERP.directory.suppliers.openCreateModal()">
                                <i class="fa-solid fa-plus"></i> Ավելացնել Մատակարար
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" style="flex-wrap: wrap; gap: 0.75rem;">
                            <!-- Segmented Tabs: All / Active / Suspended / Trash -->
                            <div class="directory-tabs">
                                <button type="button" class="directory-tab-btn active" id="tab-sup-all" onclick="ERP.directory.suppliers.switchTab('all')">
                                    <i class="fa-solid fa-list"></i> Բոլորը <span class="badge badge-slate" id="sup-count-all">{{ count($suppliers) }}</span>
                                </button>
                                <button type="button" class="directory-tab-btn" id="tab-sup-active" onclick="ERP.directory.suppliers.switchTab('active')">
                                    <i class="fa-solid fa-circle-check" style="color: var(--color-success);"></i> Ակտիվ <span class="badge badge-emerald" id="sup-count-active">{{ $suppliers->where('is_active', true)->count() }}</span>
                                </button>
                                <button type="button" class="directory-tab-btn" id="tab-sup-suspended" onclick="ERP.directory.suppliers.switchTab('suspended')">
                                    <i class="fa-solid fa-circle-pause" style="color: var(--color-warning);"></i> Կասեցված <span class="badge badge-amber" id="sup-count-suspended">{{ $suppliers->where('is_active', false)->count() }}</span>
                                </button>
                                <button type="button" class="directory-tab-btn tab-trash" id="tab-sup-trash" onclick="ERP.directory.suppliers.switchTab('trash')">
                                    <i class="fa-solid fa-trash-can" style="color: var(--color-danger);"></i> Զամբյուղ <span class="badge badge-trash" id="sup-count-trash">0</span>
                                </button>
                            </div>

                            <div style="display: flex; gap: 0.5rem; align-items: center; margin-left: auto;">
                                <div style="position: relative;">
                                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.8rem;"></i>
                                    <input type="text" class="form-control form-control-sm" id="sup-search-input" placeholder="Որոնել (անվանում, ՀՎՀՀ, հեռախոս)..." style="padding-left: 30px; width: 260px;" oninput="ERP.directory.suppliers.handleSearch(this.value)">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Անվանում / ID</th>
                                        <th>ՀՎՀՀ</th>
                                        <th>Կոնտակտներ</th>
                                        <th>Հասցեներ</th>
                                        <th>Առաքիչներ</th>
                                        <th>Մատակարարվող Ապրանքներ</th>
                                        <th>Կարգավիճակ</th>
                                        <th style="text-align: right;">Գործողություններ</th>
                                    </tr>
                                </thead>
                                <tbody id="directory-suppliers-table-body">
                                    @forelse($suppliers as $sup)
                                        <tr id="sup-row-{{ $sup->id }}">
                                            <td>
                                                <div style="font-weight: 800; color: var(--text-heading);">{{ $sup->company_name }}</div>
                                                @if($sup->legal_name && $sup->legal_name !== $sup->company_name)
                                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $sup->legal_name }}</div>
                                                @endif
                                                <div class="font-mono" style="font-size: 0.68rem; color: #94A3B8;">ID: {{ substr($sup->id, 0, 8) }}...</div>
                                            </td>
                                            <td class="font-mono" style="font-weight: 700; color: var(--color-primary);">
                                                {{ $sup->tax_id ?: '—' }}
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 2px; font-size: 0.78rem;">
                                                    <div><i class="fa-solid fa-phone" style="width: 14px; color: var(--text-muted);"></i> {{ $sup->phone }}</div>
                                                    @if($sup->email)
                                                        <div><i class="fa-solid fa-envelope" style="width: 14px; color: var(--text-muted);"></i> <a href="mailto:{{ $sup->email }}" style="color: var(--color-primary);">{{ $sup->email }}</a></div>
                                                    @endif
                                                    @if($sup->website)
                                                        <div><i class="fa-solid fa-globe" style="width: 14px; color: var(--text-muted);"></i> <a href="{{ Str::startsWith($sup->website, 'http') ? $sup->website : 'https://' . $sup->website }}" target="_blank" style="color: var(--color-primary); text-decoration: underline;">{{ $sup->website }}</a></div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="max-width: 200px;">
                                                <div style="font-size: 0.76rem; display: flex; flex-direction: column; gap: 3px;">
                                                    @if($sup->legal_address)
                                                        <div><strong>Իրավ․:</strong> {{ $sup->legal_address }}</div>
                                                    @endif
                                                    @if($sup->shipping_address)
                                                        <div style="color: #0284C7;"><strong>Առաքում:</strong> {{ $sup->shipping_address }}</div>
                                                    @elseif($sup->address)
                                                        <div>{{ $sup->address }}</div>
                                                    @else
                                                        <span style="color: var(--text-muted);">—</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @php $couriers = $sup->couriers ?? collect(); @endphp
                                                @if($couriers->count() > 0)
                                                    <button class="btn btn-xs btn-outline-secondary" onclick="ERP.directory.suppliers.showCouriers('{{ $sup->id }}')" style="font-size: 0.72rem; padding: 3px 8px; border-radius: 6px;">
                                                        <i class="fa-solid fa-truck"></i> {{ $couriers->count() }} Առաքիչ
                                                    </button>
                                                @else
                                                    <span style="color: var(--text-muted); font-size: 0.75rem;">—</span>
                                                @endif
                                            </td>
                                            <td style="max-width: 220px;">
                                                @php $supProds = $sup->products ?? collect(); @endphp
                                                @if($supProds->count() > 0)
                                                    <div style="display: flex; flex-wrap: wrap; gap: 3px;">
                                                        @foreach($supProds->take(3) as $sp)
                                                            <span class="item-chip">{{ $sp->getLocalizedName() }}</span>
                                                        @endforeach
                                                        @if($supProds->count() > 3)
                                                            <span class="badge badge-slate">+{{ $supProds->count() - 3 }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span style="color: var(--text-muted); font-size: 0.75rem;">Կցված չէ</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($sup->is_active)
                                                    <span class="badge badge-emerald"><i class="fa-solid fa-circle-check"></i> Ակտիվ</span>
                                                @else
                                                    <span class="badge badge-suspended"><i class="fa-solid fa-circle-pause"></i> Կասեցված</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display: inline-flex; gap: 4px;">
                                                    <button class="btn btn-xs btn-outline-secondary" title="Խմբագրել" onclick="ERP.directory.suppliers.openEditModal('{{ $sup->id }}')">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-xs {{ $sup->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $sup->is_active ? 'Կասեցնել' : 'Ակտիվացնել' }}" onclick="ERP.directory.suppliers.toggleSuspend('{{ $sup->id }}')">
                                                        <i class="fa-solid {{ $sup->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-danger" title="Տեղափոխել Զամբյուղ" onclick="ERP.directory.suppliers.deleteSupplier('{{ $sup->id }}')">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">Մատակարարներ գրանցված չեն: Սեղմեք «Ավելացնել Մատակարար» ստեղծելու համար:</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: DIRECTORY - INGREDIENTS (Տեղեկագիր: Բաղադրիչներ)
                     ============================================================== -->
                <section class="view-panel" id="view-directory-ingredients" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title"><i class="fa-solid fa-mortar-pestle" style="color: var(--color-primary); margin-right: 8px;"></i> Տեղեկագիր: Բաղադրիչներ և Հումք</h2>
                            <p class="welcome-subtitle">Բաղադրիչների մուտքագրում (ձեռքով կամ ինվոյսների ֆայլային ներմուծմամբ), պահեստի մնացորդներ, նվազագույն քանակներ և բաղադրատոմսերի կապ:</p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                            <button class="btn btn-secondary btn-sm" onclick="ERP.directory.ingredients.openImportModal()">
                                <i class="fa-solid fa-file-arrow-up"></i> Ներմուծել Ինվոյս (.xml, .xls, .csv)
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="ERP.directory.ingredients.openCreateModal()">
                                <i class="fa-solid fa-plus"></i> Ձեռքով Մուտքագրել
                            </button>
                        </div>
                    </div>

                    <!-- Ingredients Summary Metric Cards -->
                    <div class="metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 1.25rem;">
                        <div class="metric-card">
                            <div class="metric-icon" style="background: #EEF2FF; color: #4F46E5;"><i class="fa-solid fa-cubes-stacked"></i></div>
                            <div class="metric-details">
                                <div class="metric-label">Ընդհանուր Բաղադրիչներ</div>
                                <div class="metric-val" id="ing-stat-total">{{ count($ingredients) }}</div>
                            </div>
                        </div>

                        @php
                            $lowStockCount = $ingredients->filter(function($i) {
                                $s = (float)($i->current_stock ?? 0);
                                $m = (float)($i->min_stock_level ?? 0);
                                return $m > 0 && $s <= $m;
                            })->count();
                            $totalValuation = $ingredients->sum(function($i) {
                                return (float)($i->cost_price ?? 0) * (float)($i->current_stock ?? 0);
                            });
                        @endphp

                        <div class="metric-card" style="cursor: pointer;" onclick="ERP.directory.ingredients.toggleLowStockFilter()" title="Սեղմեք ֆիլտրելու համար">
                            <div class="metric-icon" style="background: #FEF2F2; color: #DC2626;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div class="metric-details">
                                <div class="metric-label">Նվազագույնից Ցածր Պաշար</div>
                                <div class="metric-val" style="color: #DC2626;" id="ing-stat-low">{{ $lowStockCount }}</div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-icon" style="background: #ECFDF5; color: #059669;"><i class="fa-solid fa-coins"></i></div>
                            <div class="metric-details">
                                <div class="metric-label">Պաշարների Ընդհանուր Արժեք</div>
                                <div class="metric-val" style="color: #059669;" id="ing-stat-val">{{ number_format($totalValuation, 0) }} ֏</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" style="flex-wrap: wrap; gap: 0.75rem;">
                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; flex: 1;">
                                <div style="position: relative; min-width: 240px;">
                                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.8rem;"></i>
                                    <input type="text" class="form-control form-control-sm" id="ing-search-input" placeholder="Որոնել (անվանում, SKU, EAN-13, ԱՏԳ ԱԱ)..." style="padding-left: 30px;" oninput="ERP.directory.ingredients.handleSearch(this.value)">
                                </div>

                                <select class="select select-sm" id="ing-category-filter" style="width: 170px;" onchange="ERP.directory.ingredients.handleCategoryFilter(this.value)">
                                    <option value="">Բոլոր Կատեգորիաները</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ is_array($cat->name) ? ($cat->name['hy'] ?? reset($cat->name)) : $cat->name }}</option>
                                    @endforeach
                                </select>

                                <select class="select select-sm" id="ing-supplier-filter" style="width: 170px;" onchange="ERP.directory.ingredients.handleSupplierFilter(this.value)">
                                    <option value="">Բոլոր Մատակարարները</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}">{{ $s->company_name }}</option>
                                    @endforeach
                                </select>

                                <button type="button" class="btn btn-sm btn-outline-danger" id="ing-low-stock-btn" onclick="ERP.directory.ingredients.toggleLowStockFilter()">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Միայն Նվազագույն Քանակով
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Կատեգորիա / Ենթակատեգորիա</th>
                                        <th>ID / SKU / EAN-13</th>
                                        <th>ԱՏԳ ԱԱ</th>
                                        <th>Ապրանքի Անվանում &amp; Նկարագրություն</th>
                                        <th>Չ/Մ</th>
                                        <th>Քանակ</th>
                                        <th>Միավորի Գին</th>
                                        <th>Զեղչ (%)</th>
                                        <th>Արժեք</th>
                                        <th>Զեղչված Արժեք</th>
                                        <th>Տարա</th>
                                        <th>ԱԱՀ (%) &amp; Գումար</th>
                                        <th>Գործարքի Տեսակ</th>
                                        <th>Մատակարարներ</th>
                                        <th>Պահեստի Մնացորդ</th>
                                        <th>Նվազագույն Քանակ</th>
                                        <th>Օգտագործվում Է</th>
                                        <th style="text-align: right;">Գործողություններ</th>
                                    </tr>
                                </thead>
                                <tbody id="directory-ingredients-table-body">
                                    @forelse($ingredients as $ing)
                                        @php
                                            $stock = (float)($ing->current_stock ?? 0);
                                            $minStock = (float)($ing->min_stock_level ?? 0);
                                            $isLow = ($minStock > 0 && $stock <= $minStock);
                                            $costPrice = (float)($ing->cost_price ?? 0);
                                            $discPercent = (float)($ing->discount_percent ?? 0);
                                            $subtotal = round($costPrice * max(1, $stock), 2);
                                            $discounted = round($subtotal * (1 - ($discPercent / 100)), 2);
                                            $vatRate = (float)($ing->vat_rate ?? 20.00);
                                            $vatAmount = round($discounted * ($vatRate / 100), 2);
                                            $whereUsedCount = $ing->recipesWhereUsed ? $ing->recipesWhereUsed->count() : 0;
                                        @endphp
                                        <tr id="ing-row-{{ $ing->id }}" class="{{ $isLow ? 'low-stock-alert' : '' }}">
                                            <td>
                                                <div style="font-weight: 700; color: var(--text-heading);">
                                                    {{ $ing->category ? (is_array($ing->category->name) ? ($ing->category->name['hy'] ?? reset($ing->category->name)) : $ing->category->name) : '—' }}
                                                </div>
                                                @if($ing->subcategory)
                                                    <div style="font-size: 0.72rem; color: var(--color-primary);">
                                                        ↳ {{ is_array($ing->subcategory->name) ? ($ing->subcategory->name['hy'] ?? reset($ing->subcategory->name)) : $ing->subcategory->name }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="font-mono" style="font-weight: 800; color: var(--color-primary);">{{ $ing->sku }}</div>
                                                @if($ing->barcode)
                                                    <div class="font-mono" style="font-size: 0.72rem; color: var(--text-muted);"><i class="fa-solid fa-barcode"></i> {{ $ing->barcode }}</div>
                                                @endif
                                                <div class="font-mono" style="font-size: 0.65rem; color: #94A3B8;">ID: {{ substr($ing->id, 0, 8) }}...</div>
                                            </td>
                                            <td class="font-mono" style="font-size: 0.75rem;">
                                                {{ $ing->hs_code ?: '—' }}
                                            </td>
                                            <td>
                                                <div style="font-weight: 800; color: var(--text-heading);">
                                                    {{ $ing->getLocalizedName() }}
                                                </div>
                                                @php $desc = is_array($ing->description) ? ($ing->description['hy'] ?? '') : ($ing->description ?? ''); @endphp
                                                @if($desc)
                                                    <div style="font-size: 0.72rem; color: var(--text-muted); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $desc }}">
                                                        {{ $desc }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="font-mono">
                                                {{ $ing->unit ? (is_array($ing->unit->name) ? ($ing->unit->name['hy'] ?? reset($ing->unit->name)) : $ing->unit->name) : 'կգ' }}
                                            </td>
                                            <td class="font-mono" style="font-weight: 700;">
                                                {{ number_format($stock, 2) }}
                                            </td>
                                            <td class="font-mono">
                                                {{ number_format($costPrice, 2) }} ֏
                                            </td>
                                            <td class="font-mono">
                                                {{ $discPercent > 0 ? $discPercent . '%' : '0%' }}
                                            </td>
                                            <td class="font-mono" style="font-weight: 700;">
                                                {{ number_format($subtotal, 2) }} ֏
                                            </td>
                                            <td class="font-mono" style="font-weight: 700; color: #0284C7;">
                                                {{ number_format($discounted, 2) }} ֏
                                            </td>
                                            <td>
                                                <span class="badge badge-slate" style="font-size: 0.7rem;">{{ $ing->packaging ?: 'Առանց տարայի' }}</span>
                                            </td>
                                            <td class="font-mono" style="font-size: 0.75rem;">
                                                <div>{{ $vatRate }}%</div>
                                                <div style="color: var(--text-muted);">{{ number_format($vatAmount, 2) }} ֏</div>
                                            </td>
                                            <td style="font-size: 0.75rem;">
                                                @if($ing->transaction_type === 'import_eaec')
                                                    <span class="badge badge-indigo">ԵԱՏՄ Ներմուծում</span>
                                                @elseif($ing->transaction_type === 'import_third')
                                                    <span class="badge badge-violet">Երրորդ երկրներ</span>
                                                @elseif($ing->transaction_type === 'service')
                                                    <span class="badge badge-amber">Ծառայություն</span>
                                                @else
                                                    <span class="badge badge-emerald">Տեղական ձեռքբերում</span>
                                                @endif
                                            </td>
                                            <td style="max-width: 160px;">
                                                @php $ingSups = $ing->suppliers ?? collect(); @endphp
                                                @if($ingSups->count() > 0)
                                                    <div style="display: flex; flex-wrap: wrap; gap: 3px;">
                                                        @foreach($ingSups as $isup)
                                                            <span class="item-chip" title="ՀՎՀՀ: {{ $isup->tax_id }}">{{ $isup->company_name }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span style="color: var(--text-muted); font-size: 0.75rem;">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="font-mono" style="font-weight: 800; font-size: 0.95rem; color: {{ $isLow ? '#DC2626' : 'var(--text-heading)' }};">
                                                    {{ number_format($stock, 2) }}
                                                </div>
                                                <div style="font-size: 0.65rem; color: var(--text-muted);">{{ date('d.m.Y') }}</div>
                                            </td>
                                            <td>
                                                <div class="font-mono" style="font-weight: 700;">{{ number_format($minStock, 2) }}</div>
                                                @if($isLow)
                                                    <span class="low-stock-badge"><i class="fa-solid fa-bell"></i> Լրացնել!</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($whereUsedCount > 0)
                                                    <button class="btn btn-xs btn-outline-primary" onclick="ERP.directory.ingredients.showWhereUsed('{{ $ing->id }}')" style="font-size: 0.72rem; padding: 3px 7px;">
                                                        <i class="fa-solid fa-diagram-project"></i> {{ $whereUsedCount }} Պրոդուկտ
                                                    </button>
                                                @else
                                                    <span style="color: var(--text-muted); font-size: 0.72rem;">Բաղադրատոմս չկա</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display: inline-flex; gap: 4px;">
                                                    <button class="btn btn-xs btn-outline-secondary" title="Խմբագրել" onclick="ERP.directory.ingredients.openEditModal('{{ $ing->id }}')">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-primary" title="Ճշգրտել Պահեստ" onclick="ERP.inventory.openAdjustStockModal(); const sel = document.getElementById('adj-product-id'); if(sel) sel.value='{{ $ing->id }}';">
                                                        <i class="fa-solid fa-scale-balanced"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-danger" title="Հեռացնել" onclick="ERP.directory.ingredients.deleteIngredient('{{ $ing->id }}')">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="18" style="text-align: center; color: var(--text-muted); padding: 2rem;">Բաղադրիչներ գրանցված չեն: Օգտվեք «Ձեռքով Մուտքագրել» կամ «Ներմուծել Ինվոյս (.xml, .xls, .csv)» կոճակներից:</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: PROCUREMENT & PURCHASE ORDERS
                     ============================================================== -->
                <section class="view-panel" id="view-procurement" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Procurement &amp; Inbound Goods Receipt</h2>
                            <p class="welcome-subtitle">Purchase orders, supplier contracts, moving weighted average (MWA) valuation, and warehouse delivery receipts.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="ERP.procurement.openCreatePurchaseModal()">
                            <i class="fa-solid fa-plus"></i> Create Purchase Order
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Purchase Orders</h3>
                            <span class="badge badge-amber">{{ count($purchaseOrders) }} Orders</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>PO Number</th>
                                        <th>Supplier</th>
                                        <th>Warehouse</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchaseOrders as $po)
                                        <tr>
                                            <td class="font-mono" style="font-weight: 700; color: var(--color-primary);">{{ $po->order_number }}</td>
                                            <td style="font-weight: 700; color: var(--text-heading);">{{ $po->supplier?->name ?? 'Direct Vendor' }}</td>
                                            <td class="font-mono">{{ $po->warehouse?->name ?? 'Main WH' }}</td>
                                            <td>
                                                <span class="badge badge-slate font-mono">{{ count($po->items) }} items</span>
                                            </td>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-primary);">{{ number_format($po->total_amount, 0) }} ֏</td>
                                            <td>
                                                <span class="badge {{ $po->status === 'received' ? 'badge-emerald' : ($po->status === 'cancelled' ? 'badge-rose' : 'badge-amber') }}">
                                                    {{ strtoupper($po->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($po->status !== 'received' && $po->status !== 'cancelled')
                                                    <button class="btn btn-xs btn-primary" onclick="ERP.procurement.receivePurchase('{{ $po->id }}')" style="font-size: 0.75rem; padding: 4px 10px;">
                                                        <i class="fa-solid fa-dolly"></i> Receive Goods
                                                    </button>
                                                @else
                                                    <span style="font-size: 0.75rem; color: var(--color-success); font-weight: 700;"><i class="fa-solid fa-check"></i> In Stock</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No purchase orders yet. Click "+ Create Purchase Order" to buy materials.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 2: POS POINT OF SALE
                     ============================================================== -->
                <section class="view-panel" id="view-pos" style="display: none;">
                    <div class="pos-layout">
                        <!-- Left Catalog -->
                        <div class="pos-catalog">
                            <div style="display: flex; gap: 0.75rem;">
                                <input type="text" class="input" id="pos-search-input" placeholder="Search products or scan barcode..." oninput="
                                    const val = this.value.toLowerCase();
                                    document.querySelectorAll('.pos-product-card').forEach(c => {
                                        const text = c.innerText.toLowerCase();
                                        c.style.display = text.includes(val) ? 'flex' : 'none';
                                    });
                                ">
                                <button class="btn btn-secondary" onclick="document.getElementById('pos-search-input').value=''; ERP.pos.render();">Clear</button>
                            </div>
                            <div class="pos-products-grid" id="pos-product-grid">
                                <!-- Populated dynamically by ERP.pos.render() -->
                            </div>
                        </div>

                        <!-- Right Cart Panel -->
                        <div class="pos-cart-panel">
                            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Shopping Cart</h3>
                                    <span style="font-size: 0.72rem; color: var(--text-muted);">Terminal #POS-01</span>
                                </div>
                                <button class="btn btn-sm btn-secondary" onclick="ERP.pos.clearCart()">Clear</button>
                            </div>

                            <div class="cart-items-list" id="pos-cart-list">
                                <!-- Rendered dynamically by ERP.pos.renderCart() -->
                            </div>

                            <div class="cart-totals">
                                <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: var(--text-muted);">
                                    <span>Subtotal:</span>
                                    <span id="pos-subtotal" class="font-mono">0 ֏</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: var(--text-muted);">
                                    <span>Tax (20% VAT):</span>
                                    <span id="pos-tax" class="font-mono">0 ֏</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 800; color: var(--text-heading); border-top: 1px solid var(--border-card); padding-top: 0.6rem;">
                                    <span>Total:</span>
                                    <span id="pos-total" class="font-mono" style="color: var(--color-primary);">0 ֏</span>
                                </div>
                                <button class="btn btn-primary btn-lg" id="pos-checkout-btn" disabled onclick="ERP.pos.openCheckoutModal()" style="margin-top: 0.5rem;">
                                    <i class="fa-solid fa-credit-card"></i> Checkout &amp; Pay
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 3: INVENTORY & WAREHOUSES
                     ============================================================== -->
                <section class="view-panel" id="view-inventory" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Warehouses &amp; Inventory Management</h2>
                            <p class="welcome-subtitle">Lot numbering, batch expiry dates, and real-time stock levels.</p>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-secondary btn-sm" onclick="ERP.inventory.openAdjustStockModal(); const sel = document.getElementById('adj-type'); if(sel) { sel.value='scrap'; ERP.inventory.toggleReason('scrap'); }">
                                <i class="fa-solid fa-trash-can"></i> Log Scrap / Waste
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="ERP.inventory.openAdjustStockModal()">
                                <i class="fa-solid fa-plus"></i> Stock Adjustment
                            </button>
                        </div>
                    </div>

                    <!-- Warehouses Cards -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header">
                            <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Warehouses</h3>
                            <span class="badge badge-cyan">{{ count($warehouses) }} Warehouses</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Address</th>
                                        <th>Branch</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($warehouses as $wh)
                                        <tr>
                                            <td class="font-mono" style="font-weight: 700; color: var(--color-primary);">{{ $wh->code }}</td>
                                            <td style="font-weight: 700; color: var(--text-heading);">{{ $wh->name }}</td>
                                            <td><span class="badge {{ $wh->type === 'cold_storage' ? 'badge-cyan' : 'badge-indigo' }}">{{ $wh->type }}</span></td>
                                            <td>{{ $wh->address ?? 'N/A' }}</td>
                                            <td>{{ $wh->branch?->name ?? 'Main Branch' }}</td>
                                            <td><span class="badge badge-emerald">Active</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No warehouses registered yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Batches Table -->
                    <div class="card">
                        <div class="card-header">
                            <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Stock Batches &amp; Expiry Dates</h3>
                            <span class="badge badge-emerald">{{ count($batches) }} Batches</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Batch Number</th>
                                        <th>Product</th>
                                        <th>Warehouse</th>
                                        <th>Quantity On Hand</th>
                                        <th>Cost Price</th>
                                        <th>Expiry Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batches as $b)
                                        <tr>
                                            <td class="font-mono" style="font-weight: 700; color: var(--color-primary);">{{ $b->batch_number }}</td>
                                            <td style="font-weight: 700; color: var(--text-heading);">
                                                {{ is_array($b->product->name) ? ($b->product->name['hy'] ?? reset($b->product->name)) : $b->product->name }}
                                            </td>
                                            <td class="font-mono">{{ $b->warehouse?->code }}</td>
                                            <td class="font-mono" style="font-weight: 800;">{{ number_format($b->quantity_on_hand, 2) }} kg</td>
                                            <td class="font-mono">{{ number_format($b->cost_price, 0) }} ֏</td>
                                            <td>
                                                <span class="badge badge-amber font-mono">{{ $b->expiry_date }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.inventory.openAdjustStockModal(); const sel = document.getElementById('adj-product-id'); if(sel) sel.value='{{ $b->product_id }}'; const w = document.getElementById('adj-warehouse-id'); if(w) w.value='{{ $b->warehouse_id }}';" style="font-size: 0.72rem; padding: 2px 7px;">
                                                    <i class="fa-solid fa-scale-balanced"></i> Adjust / Scrap
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No batches registered.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 4: MANUFACTURING & BOM RECIPES
                     ============================================================== -->
                <section class="view-panel" id="view-manufacturing" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Manufacturing &amp; BOM Technological Recipes</h2>
                            <p class="welcome-subtitle">Bill of Materials, ingredient scaling, and production orders lifecycle.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="ERP.manufacturing.openCreateRecipeModal()">
                            <i class="fa-solid fa-plus"></i> New BOM Recipe
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                        @foreach($recipes as $rcp)
                            <div class="card">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                    <div>
                                        <span class="font-mono" style="font-size: 0.75rem; font-weight: 700; color: var(--color-warning);">{{ $rcp->code }}</span>
                                        <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--text-heading); margin-top: 2px;">{{ $rcp->name }}</h4>
                                    </div>
                                    <span class="badge badge-amber font-mono">Yield: {{ number_format($rcp->yield_quantity, 0) }} {{ $rcp->yieldUnit?->symbol ?? 'units' }}</span>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                    Labor / Overhead: <strong style="color: var(--text-heading); font-family: var(--font-mono);">{{ number_format($rcp->labor_cost, 0) }} / {{ number_format($rcp->overhead_cost, 0) }} ֏</strong>
                                </div>
                                <div style="border-top: 1px solid var(--border-card); padding-top: 0.75rem;">
                                    <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Raw Materials / BOM:</div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        @foreach($rcp->items as $item)
                                            <span style="font-size: 0.72rem; font-weight: 600; padding: 3px 7px; border-radius: var(--radius-sm); background: var(--bg-surface-subtle); border: 1px solid var(--border-card);">
                                                {{ is_array($item->product?->name) ? ($item->product->name['hy'] ?? '') : $item->product?->name }}: {{ $item->quantity }}kg
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div style="margin-top: 0.75rem; display: flex; justify-content: flex-end;">
                                    <button class="btn btn-xs btn-primary" onclick="ERP.manufacturing.openProductionModal('{{ $rcp->id }}')" style="font-size: 0.75rem; padding: 4px 10px;">
                                        <i class="fa-solid fa-play"></i> Produce Batch
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Production Orders Table -->
                    <div class="card">
                        <div class="card-header">
                            <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Production Orders</h3>
                            <span class="badge badge-indigo">{{ count($productionOrders) }} Orders</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Product</th>
                                        <th>Planned / Actual</th>
                                        <th>Batch #</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productionOrders as $po)
                                        <tr>
                                            <td class="font-mono" style="font-weight: 700; color: var(--color-primary);">{{ $po->order_number }}</td>
                                            <td style="font-weight: 700; color: var(--text-heading);">{{ is_array($po->product?->name) ? ($po->product->name['hy'] ?? '') : $po->product?->name }}</td>
                                            <td class="font-mono">{{ number_format($po->planned_quantity, 0) }} / {{ number_format($po->actual_quantity, 0) }} pcs</td>
                                            <td class="font-mono" style="color: var(--color-success); font-weight: 700;">{{ $po->batch?->batch_number ?? 'In Progress' }}</td>
                                            <td>
                                                <span class="badge {{ $po->status === 'completed' ? 'badge-emerald' : ($po->status === 'in_progress' ? 'badge-indigo' : 'badge-amber') }}">{{ strtoupper($po->status) }}</span>
                                            </td>
                                            <td>
                                                @if($po->status === 'draft')
                                                    <button class="btn btn-xs btn-primary" onclick="ERP.manufacturing.startProduction('{{ $po->id }}')" style="font-size: 0.72rem; padding: 3px 8px;">
                                                        <i class="fa-solid fa-play"></i> Start
                                                    </button>
                                                @elseif($po->status === 'in_progress')
                                                    <button class="btn btn-xs btn-success" onclick="ERP.manufacturing.completeProduction('{{ $po->id }}')" style="font-size: 0.72rem; padding: 3px 8px; background: var(--color-success); color: white;">
                                                        <i class="fa-solid fa-check-double"></i> Complete &amp; Yield
                                                    </button>
                                                @else
                                                    <span style="font-size: 0.75rem; color: var(--color-success); font-weight: 700;"><i class="fa-solid fa-check"></i> Done</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 5: ISO 22000 QUALITY ASSURANCE
                     ============================================================== -->
                <section class="view-panel" id="view-quality" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">ISO 22000 / HACCP Quality Assurance</h2>
                            <p class="welcome-subtitle">Critical Control Points (CCP) parameter validation and inspections.</p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1.25rem;">
                        @foreach($qualityInspections as $qa)
                            <div class="card" style="border-color: var(--color-success-border);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                    <span class="font-mono" style="font-weight: 700; color: var(--color-success);">{{ $qa->inspection_number }}</span>
                                    <span class="badge badge-emerald font-mono font-bold">{{ $qa->overall_score }}% PASSED</span>
                                </div>
                                <div style="font-size: 0.82rem; margin-bottom: 0.75rem;">
                                    Standard: <strong>{{ $qa->standard_applied }}</strong> &bull;
                                    Order: <span class="font-mono" style="color: var(--color-primary);">{{ $qa->productionOrder?->order_number }}</span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    @foreach($qa->items as $item)
                                        <div style="display: flex; justify-content: space-between; padding: 6px 10px; border-radius: var(--radius-sm); background: var(--bg-surface-subtle); font-size: 0.78rem;">
                                            <div>
                                                <strong style="color: var(--color-warning);" class="font-mono">{{ $item->critical_control_point }}:</strong>
                                                <span>{{ $item->parameter_name }}</span>
                                            </div>
                                            <div class="font-mono" style="color: var(--color-success); font-weight: 700;">
                                                {{ $item->actual_value }}{{ $item->unit }} <i class="fa-solid fa-check"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 6: DELIVERY FLEET
                     ============================================================== -->
                <section class="view-panel" id="view-delivery" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Delivery Fleet &amp; Dispatch</h2>
                            <p class="welcome-subtitle">Active couriers, route tracking, and cash collection (COD).</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">Active Shipments</h3>
                            <span class="badge badge-cyan">{{ count($deliveryShipments) }} Shipments</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Shipment #</th>
                                        <th>Driver</th>
                                        <th>Destination</th>
                                        <th>COD Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deliveryShipments as $s)
                                        <tr>
                                            <td class="font-mono" style="font-weight: 700; color: var(--color-primary);">{{ $s->shipment_number }}</td>
                                            <td style="font-weight: 700; color: var(--text-heading);">{{ $s->driver?->user?->name ?? 'Courier #1' }}</td>
                                            <td>{{ $s->order?->delivery_address ?? 'Yerevan, Center' }}</td>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-success);">{{ number_format($s->cod_amount ?? 0, 0) }} ֏</td>
                                            <td>
                                                <span class="badge badge-emerald">{{ strtoupper($s->status) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 7: USERS & TEAM MANAGEMENT
                     ============================================================== -->
                <section class="view-panel" id="view-users" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Users &amp; Team Management</h2>
                            <p class="welcome-subtitle">Manage organization teammates, access permissions, and roles.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="ERP.users.openInviteModal()">
                            <i class="fa-solid fa-user-plus"></i> Add Employee
                        </button>
                    </div>

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined Date</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-table-body">
                                    <!-- Rendered dynamically via ERP.users.load() -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 8: ROLES & RBAC
                     ============================================================== -->
                <section class="view-panel" id="view-roles" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Roles &amp; Access Policies (RBAC)</h2>
                            <p class="welcome-subtitle">Define module privileges and authorization policies.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="ERP.roles.savePolicies()">
                            <i class="fa-solid fa-check"></i> Save Access Policies
                        </button>
                    </div>

                    <div class="card">
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <!-- Sales -->
                            <div style="border-bottom: 1px solid var(--border-card); padding-bottom: 1rem;">
                                <h4 style="font-size: 1rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.75rem;"><i class="fa-solid fa-cash-register"></i> Orders &amp; POS</h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" checked> <span>View Orders</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" checked> <span>Create POS Checkout</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" checked> <span>Apply Discounts</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Inventory -->
                            <div style="border-bottom: 1px solid var(--border-card); padding-bottom: 1rem;">
                                <h4 style="font-size: 1rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.75rem;"><i class="fa-solid fa-boxes-stacked"></i> Warehouses &amp; Stock</h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" checked> <span>View Batches &amp; Stock</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" checked> <span>Stock Transfer &amp; Movements</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Operations -->
                            <div>
                                <h4 style="font-size: 1rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.75rem;"><i class="fa-solid fa-industry"></i> Manufacturing &amp; Quality</h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" checked> <span>Edit BOM Recipes</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" checked> <span>Approve ISO 22000 Inspections</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 9: BILLING & SUBSCRIPTIONS
                     ============================================================== -->
                <section class="view-panel" id="view-billing" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Subscription Plans &amp; Billing</h2>
                            <p class="welcome-subtitle">Manage your SaaS tier, entitlements, and payment invoices.</p>
                        </div>
                    </div>

                    <!-- Active Plan Banner -->
                    <div class="card" style="margin-bottom: 2rem; border-color: var(--color-primary-border); background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span class="badge badge-emerald">Active Subscription</span>
                                <h3 style="font-size: 1.4rem; font-weight: 800; color: var(--text-heading); margin: 0.5rem 0 0.25rem 0;">Professional Plan ($79 / month)</h3>
                                <p style="font-size: 0.82rem; color: var(--text-muted);">Next billing date: June 1, 2026 &bull; Primary Card: Visa •••• 4242</p>
                            </div>
                            <button class="btn btn-primary" onclick="ERP.billing.openUpgradeModal('enterprise')">
                                <i class="fa-solid fa-bolt"></i> Upgrade to Enterprise
                            </button>
                        </div>
                    </div>

                    <!-- 3 Pricing Cards -->
                    <div class="pricing-grid">
                        <div class="pricing-card">
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-heading);">Starter Plan</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Essential features for small business workflows.</p>
                            <div class="pricing-price">$29 <span style="font-size: 0.85rem; color: var(--text-muted);">/ month</span></div>
                            <ul class="pricing-features">
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> Up to 5 Employees</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> 1 Warehouse</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> POS &amp; Receipts</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> Standard Financial Reporting</li>
                            </ul>
                            <button class="btn btn-secondary" onclick="ERP.toast('Currently on higher plan', 'info')">Current plan is higher</button>
                        </div>

                        <div class="pricing-card popular">
                            <div class="popular-badge">Most Popular</div>
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-heading);">Professional Plan</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Automated supply chain, BOM recipes, and fleet dispatch.</p>
                            <div class="pricing-price" style="color: var(--color-primary);">$79 <span style="font-size: 0.85rem; color: var(--text-muted);">/ month</span></div>
                            <ul class="pricing-features">
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> Up to 25 Employees</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> 5 Warehouses &amp; Cold Storage</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> Delivery Fleet &amp; GPS</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> BOM Recipes &amp; Production Orders</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> Custom Role Policies (RBAC)</li>
                            </ul>
                            <button class="btn btn-primary" style="opacity: 0.75; cursor: default;"><i class="fa-solid fa-check"></i> Current Active Plan</button>
                        </div>

                        <div class="pricing-card">
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-heading);">Enterprise Plan</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">High-volume factory operations with dedicated database.</p>
                            <div class="pricing-price">$199 <span style="font-size: 0.85rem; color: var(--text-muted);">/ month</span></div>
                            <ul class="pricing-features">
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> Unlimited Employees</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> Unlimited Warehouses &amp; POS</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> ISO 22000 &amp; HACCP Certified</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> 1C &amp; AS Software Integration</li>
                                <li><i class="fa-solid fa-check" style="color: var(--color-success); margin-right: 4px;"></i> Dedicated Database &amp; 99.99% SLA</li>
                            </ul>
                            <button class="btn btn-secondary" onclick="ERP.billing.openUpgradeModal('enterprise')">Switch to Enterprise</button>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 10: TENANT SETTINGS
                     ============================================================== -->
                <section class="view-panel" id="view-settings" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Organization Settings</h2>
                            <p class="welcome-subtitle">Company profile, tax identification (ՀՎՀՀ), currency, and regional timezone.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="ERP.settings.save()">
                            <i class="fa-solid fa-check"></i> Save Changes
                        </button>
                    </div>

                    <div class="card" style="max-width: 800px;">
                        <form onsubmit="ERP.settings.save(event)">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Organization Name</label>
                                    <input type="text" class="form-control" value="{{ $currentTenant ? $currentTenant->name : 'Armenia Gourmet Food' }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tenant Slug</label>
                                    <input type="text" class="form-control font-mono" value="{{ $currentTenant ? $currentTenant->slug : 'gourmet' }}" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tax ID (ՀՎՀՀ)</label>
                                    <input type="text" class="form-control font-mono" value="{{ $currentTenant->tax_number ?? '02548963' }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Currency</label>
                                    <select class="select">
                                        <option value="AMD" selected>AMD — Armenian Dram (֏)</option>
                                        <option value="USD">USD — US Dollar ($)</option>
                                        <option value="EUR">EUR — Euro (€)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control font-mono" value="+374 10 55-44-33">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Timezone</label>
                                    <select class="select">
                                        <option value="Asia/Yerevan" selected>Asia/Yerevan (GMT+4)</option>
                                        <option value="Europe/Moscow">Europe/Moscow (GMT+3)</option>
                                        <option value="UTC">UTC (GMT+0)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Legal Address</label>
                                <input type="text" class="form-control" value="Sayat-Nova Ave 12, Yerevan, Armenia">
                            </div>

                            <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                                <button type="submit" class="btn btn-primary">Save Organization Profile</button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW 11: API CONSOLE RUNNER
                     ============================================================== -->
                <section class="view-panel" id="view-api-console" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title">Interactive API Console (Same Port 8000)</h2>
                            <p class="welcome-subtitle">Direct browser requests to Laravel API endpoints without CORS.</p>
                        </div>
                        <span id="api-console-status" class="badge badge-emerald">Ready</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 1.25rem;">
                        <div class="card" style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.25rem;">ENDPOINTS</div>
                            <button class="btn btn-sm btn-secondary" style="justify-content: flex-start;" onclick="ERP.apiConsole.run('GET', '/health')">
                                GET /health
                            </button>
                            <button class="btn btn-sm btn-secondary" style="justify-content: flex-start;" onclick="ERP.apiConsole.run('GET', '/warehouses')">
                                GET /warehouses
                            </button>
                            <button class="btn btn-sm btn-secondary" style="justify-content: flex-start;" onclick="ERP.apiConsole.run('GET', '/inventory/batches')">
                                GET /inventory/batches
                            </button>
                            <button class="btn btn-sm btn-secondary" style="justify-content: flex-start;" onclick="ERP.apiConsole.run('GET', '/recipes')">
                                GET /recipes
                            </button>
                            <button class="btn btn-sm btn-secondary" style="justify-content: flex-start;" onclick="ERP.apiConsole.run('GET', '/production-orders')">
                                GET /production-orders
                            </button>
                            <button class="btn btn-sm btn-secondary" style="justify-content: flex-start;" onclick="ERP.apiConsole.run('GET', '/quality-inspections')">
                                GET /quality-inspections
                            </button>
                            <button class="btn btn-sm btn-secondary" style="justify-content: flex-start;" onclick="ERP.apiConsole.run('GET', '/users')">
                                GET /users
                            </button>
                            <button class="btn btn-sm btn-secondary" style="justify-content: flex-start;" onclick="ERP.apiConsole.run('GET', '/roles')">
                                GET /roles
                            </button>
                        </div>

                        <div class="card" style="padding: 1rem;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem; font-family: var(--font-mono);">RESPONSE VIEWER:</div>
                            <pre class="api-runner-box" id="api-console-output">// Select an endpoint on the left to execute API request...</pre>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <!-- ======================================================================
         MODALS & DIALOGS
         ====================================================================== -->

    <!-- Modal 1: POS Checkout Payment Method Modal -->
    <div class="modal-backdrop" id="pos-checkout-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Select Payment Method</h3>
                <button onclick="ERP.pos.closeCheckoutModal()" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                    Select the preferred gateway to complete transaction and generate fiscal receipt:
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <button class="btn btn-secondary" onclick="ERP.pos.submitCheckout('cash')" style="padding: 1rem; justify-content: flex-start; gap: 0.75rem;">
                        <span style="font-size: 1.4rem; color: var(--color-success);"><i class="fa-solid fa-money-bill-wave"></i></span>
                        <div style="text-align: left;">
                            <div style="font-weight: 700;">Cash Drawer</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted);">Standard Cashier</div>
                        </div>
                    </button>

                    <button class="btn btn-secondary" onclick="ERP.pos.submitCheckout('telcell')" style="padding: 1rem; justify-content: flex-start; gap: 0.75rem;">
                        <span style="font-size: 1.4rem; color: #f97316;"><i class="fa-solid fa-qrcode"></i></span>
                        <div style="text-align: left;">
                            <div style="font-weight: 700;">Telcell QR</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted);">Instant Scan &amp; Pay</div>
                        </div>
                    </button>

                    <button class="btn btn-secondary" onclick="ERP.pos.submitCheckout('idram')" style="padding: 1rem; justify-content: flex-start; gap: 0.75rem;">
                        <span style="font-size: 1.4rem; color: #0284c7;"><i class="fa-solid fa-wallet"></i></span>
                        <div style="text-align: left;">
                            <div style="font-weight: 700;">Idram &amp; IDBank</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted);">Digital Wallet</div>
                        </div>
                    </button>

                    <button class="btn btn-secondary" onclick="ERP.pos.submitCheckout('ameria')" style="padding: 1rem; justify-content: flex-start; gap: 0.75rem;">
                        <span style="font-size: 1.4rem; color: var(--color-primary);"><i class="fa-solid fa-credit-card"></i></span>
                        <div style="text-align: left;">
                            <div style="font-weight: 700;">Ameria Bank POS</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted);">Visa / Mastercard</div>
                        </div>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="ERP.pos.closeCheckoutModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Fiscal Receipt Modal -->
    <div class="modal-backdrop" id="receipt-modal">
        <div class="modal-container" style="max-width: 420px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-receipt"></i> Fiscal Receipt</h3>
                <button onclick="document.getElementById('receipt-modal').classList.remove('active')" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <div class="modal-body" id="receipt-content">
                <!-- Populated dynamically by ERP.pos.showReceiptModal() -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="document.getElementById('receipt-modal').classList.remove('active')">Close</button>
                <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Receipt</button>
            </div>
        </div>
    </div>

    <!-- Modal 3: Invite User Modal -->
    <div class="modal-backdrop" id="invite-user-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Add New Employee</h3>
                <button onclick="ERP.users.closeInviteModal()" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <form onsubmit="ERP.users.submitInvite(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="invite-name" required placeholder="e.g. Sarah Johnson">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control font-mono" id="invite-email" required placeholder="sarah@company.am">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select class="select" id="invite-role">
                            <option value="admin">Administrator (Full Access)</option>
                            <option value="manager">Operations Manager</option>
                            <option value="accountant">Accountant</option>
                            <option value="member" selected>Operator / Cashier</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.users.closeInviteModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Invite Teammate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Upgrade Plan Modal -->
    <div class="modal-backdrop" id="upgrade-plan-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Upgrade Subscription Plan</h3>
                <button onclick="ERP.billing.closeUpgradeModal()" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                    Upgrade to Enterprise for unlimited seats, dedicated database, and full 1C/AS integrations.
                </p>
                <div class="card" style="padding: 1.25rem; margin-bottom: 1rem; border-color: var(--color-primary-border); background: var(--color-primary-light);">
                    <div style="font-weight: 800; color: var(--text-heading);">Enterprise Cloud Tier</div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--color-primary); font-family: var(--font-mono); margin: 0.25rem 0;">$199 / month</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Charged to primary billing card (Visa •••• 4242)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 5: Create Product / Item Master -->
    <div class="modal-backdrop" id="create-product-modal">
        <div class="modal-container" style="max-width: 620px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-tag"></i> Add New Item / Product</h3>
                <button onclick="ERP.catalog.closeCreateProductModal()" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <form onsubmit="ERP.catalog.submitProduct(event)">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Անվանում (Հայերեն) *</label>
                            <input type="text" class="form-control" id="prod-name-hy" required placeholder="օր․ Լոլիկ, Պանիր Չանախ, Փաթեթավորման տուփ">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Name (English)</label>
                            <input type="text" class="form-control" id="prod-name-en" placeholder="e.g. Tomato, Fresh Cheese">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Ապրանքի Տիպ (Item Type) *</label>
                            <select class="select" id="prod-type" required>
                                <option value="finished_product">Finished Product (Պատրաստի արտադրանք)</option>
                                <option value="semi_finished">Semi-Finished (Կիսաֆաբրիկատ)</option>
                                <option value="ingredient">Ingredient (Բաղադրիչ)</option>
                                <option value="raw_material">Raw Material (Հումք)</option>
                                <option value="packaging">Packaging (Փաթեթավորում)</option>
                                <option value="service">Service (Ծառայություն)</option>
                                <option value="modifier">Modifier (Մոդիֆիկատոր)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Կոդ / SKU *</label>
                            <input type="text" class="form-control font-mono" id="prod-sku" required placeholder="օր․ RAW-TOM-01">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Կատեգորիա</label>
                            <select class="select" id="prod-category-id">
                                <option value="">-- Առանց կատեգորիայի --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ is_array($cat->name) ? ($cat->name['hy'] ?? reset($cat->name)) : $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Չափման Միավոր (Unit) *</label>
                            <select class="select" id="prod-unit-id" required>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ is_array($u->name) ? ($u->name['hy'] ?? reset($u->name)) : $u->name }} ({{ $u->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Ինքնարժեք (Cost Price ֏)</label>
                            <input type="number" step="any" class="form-control font-mono" id="prod-cost-price" value="0" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Վաճառքի Գին (Sale Price ֏)</label>
                            <input type="number" step="any" class="form-control font-mono" id="prod-sale-price" value="0" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.catalog.closeCreateProductModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Create Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 6: Create Purchase Order -->
    <div class="modal-backdrop" id="create-purchase-modal">
        <div class="modal-container" style="max-width: 680px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-file-invoice-dollar"></i> Create Purchase Order</h3>
                <button onclick="ERP.procurement.closeCreatePurchaseModal()" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <form onsubmit="ERP.procurement.submitPurchase(event)">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Մատակարար (Supplier) *</label>
                            <select class="select" id="po-supplier-id" required>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }} ({{ $sup->code ?? 'SUP' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ընդունող Պահեստ (Warehouse) *</label>
                            <select class="select" id="po-warehouse-id" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Նշումներ (Notes / Ref)</label>
                        <input type="text" class="form-control" id="po-notes" placeholder="օր․ Հաշիվ-ապրանքագիր #88241">
                    </div>

                    <div style="margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label" style="margin: 0; font-weight: 800;">Գնվող Ապրանքներ (Order Lines)</label>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="ERP.procurement.addPurchaseItemRow()" style="font-size: 0.75rem; padding: 3px 8px;"><i class="fa-solid fa-plus"></i> Add Line</button>
                    </div>

                    <div id="po-items-container" style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="po-item-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 8px; align-items: center; background: var(--bg-surface-subtle); padding: 8px; border-radius: 6px; border: 1px solid var(--border-card);">
                            <div>
                                <select class="select po-product-select" required style="width: 100%;">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">
                                            {{ is_array($p->name) ? ($p->name['hy'] ?? reset($p->name)) : $p->name }} ({{ $p->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="number" step="any" class="form-control font-mono po-qty-input" required placeholder="Qty" value="10">
                            </div>
                            <div>
                                <input type="number" step="any" class="form-control font-mono po-cost-input" required placeholder="Cost ֏" value="1500">
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="ERP.procurement.removePurchaseItemRow(this)" style="padding: 4px 8px; color: var(--color-danger); border: 1px solid var(--color-danger-border);"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.procurement.closeCreatePurchaseModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Purchase Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 7: Create Recipe (BOM) -->
    <div class="modal-backdrop" id="create-recipe-modal">
        <div class="modal-container" style="max-width: 680px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-scroll"></i> New Bill of Materials (BOM) Recipe</h3>
                <button onclick="ERP.manufacturing.closeCreateRecipeModal()" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <form onsubmit="ERP.manufacturing.submitRecipe(event)">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Արտադրվող Ապրանք (Finished Product) *</label>
                            <select class="select" id="rcp-product-id" required>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">
                                        {{ is_array($p->name) ? ($p->name['hy'] ?? reset($p->name)) : $p->name }} ({{ $p->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Բաղադրատոմսի Անվանում *</label>
                            <input type="text" class="form-control" id="rcp-name" required placeholder="օր․ Պիցցա Մարգարիտա 30սմ Ստանդարտ">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Կոդ *</label>
                            <input type="text" class="form-control font-mono" id="rcp-code" required placeholder="BOM-001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Տարբերակ</label>
                            <input type="text" class="form-control font-mono" id="rcp-version" value="1.0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ելք (Yield)</label>
                            <input type="number" step="any" class="form-control font-mono" id="rcp-yield" value="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Խոտան % (Scrap)</label>
                            <input type="number" step="any" class="form-control font-mono" id="rcp-scrap" value="2.5">
                        </div>
                    </div>

                    <div style="margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label" style="margin: 0; font-weight: 800;">Բաղադրիչներ (Raw Materials &amp; Ingredients)</label>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="ERP.manufacturing.addRecipeComponentRow()" style="font-size: 0.75rem; padding: 3px 8px;"><i class="fa-solid fa-plus"></i> Add Ingredient</button>
                    </div>

                    <div id="rcp-items-container" style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="rcp-item-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 8px; align-items: center; background: var(--bg-surface-subtle); padding: 8px; border-radius: 6px; border: 1px solid var(--border-card);">
                            <div>
                                <select class="select rcp-product-select" required style="width: 100%;">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">
                                            {{ is_array($p->name) ? ($p->name['hy'] ?? reset($p->name)) : $p->name }} ({{ $p->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="number" step="any" class="form-control font-mono rcp-qty-input" required placeholder="Qty" value="0.25">
                            </div>
                            <div>
                                <input type="number" step="any" class="form-control font-mono rcp-waste-input" placeholder="Waste %" value="0">
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="ERP.manufacturing.removeRecipeComponentRow(this)" style="padding: 4px 8px; color: var(--color-danger); border: 1px solid var(--color-danger-border);"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.manufacturing.closeCreateRecipeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save BOM Recipe</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 8: Produce Batch (Production Order) -->
    <div class="modal-backdrop" id="create-production-modal">
        <div class="modal-container" style="max-width: 520px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-industry"></i> Launch Production Order</h3>
                <button onclick="ERP.manufacturing.closeProductionModal()" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <form onsubmit="ERP.manufacturing.submitProductionOrder(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Ընտրեք Բաղադրատոմսը (BOM Recipe) *</label>
                        <select class="select" id="prod-recipe-id" required>
                            @foreach($recipes as $rcp)
                                <option value="{{ $rcp->id }}">{{ $rcp->name }} ({{ $rcp->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Արտադրվող Քանակ (Batch Quantity) *</label>
                        <input type="number" step="any" class="form-control font-mono" id="prod-order-qty" required value="20">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Հումքի Պահեստ (Source)</label>
                            <select class="select" id="prod-source-wh" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Արտադրանքի Պահեստ (Target)</label>
                            <select class="select" id="prod-target-wh" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.manufacturing.closeProductionModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play"></i> Create &amp; Plan Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 9: Stock Adjustment & Scrap Logging -->
    <div class="modal-backdrop" id="adjust-stock-modal">
        <div class="modal-container" style="max-width: 540px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-scale-balanced"></i> Stock Adjustment &amp; Waste Scrap</h3>
                <button onclick="ERP.inventory.closeAdjustStockModal()" style="font-size: 1.25rem; color: var(--text-muted);">&times;</button>
            </div>
            <form onsubmit="ERP.inventory.submitAdjustment(event)">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Պահեստ (Warehouse) *</label>
                            <select class="select" id="adj-warehouse-id" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Գործողության Տեսակ *</label>
                            <select class="select" id="adj-type" onchange="ERP.inventory.toggleReason(this.value)">
                                <option value="adjust">Գույքագրման Ճշգրտում (Physical Count)</option>
                                <option value="scrap">Խոտան / Կորուստ (Scrap &amp; Waste)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ապրանք (Product) *</label>
                        <select class="select" id="adj-product-id" required>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">
                                    {{ is_array($p->name) ? ($p->name['hy'] ?? reset($p->name)) : $p->name }} ({{ $p->sku }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Քանակ (Quantity) *</label>
                        <input type="number" step="any" class="form-control font-mono" id="adj-quantity" required placeholder="օր․ 5 կամ փաստացի մնացորդ">
                        <small style="color: var(--text-muted); font-size: 0.72rem;">Ճշգրտման դեպքում՝ փաստացի հաշվառված քանակը։ Խոտանի դեպքում՝ դուրս գրվող քանակը։</small>
                    </div>

                    <div class="form-group" id="adj-reason-group" style="display: none;">
                        <label class="form-label">Խոտանի Պատճառ (Scrap Reason Taxonomy) *</label>
                        <select class="select" id="adj-reason">
                            <option value="spoilage">Փչացում (Spoilage)</option>
                            <option value="expired">Ժամկետանց (Expired)</option>
                            <option value="production_loss">Արտադրական կորուստ (Production Loss)</option>
                            <option value="kitchen_waste">Խոհանոցային թափոն (Kitchen Waste)</option>
                            <option value="damaged">Վնասված (Damaged)</option>
                            <option value="unknown_loss">Անհայտ կորուստ (Unknown Loss)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Նշումներ / Հիմք (Notes)</label>
                        <input type="text" class="form-control" id="adj-notes" placeholder="օր․ Ակտ #12, ջերմաստիճանի խախտում">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.inventory.closeAdjustStockModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check-double"></i> Post to Ledger</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==============================================================
         DIRECTORY MODALS (Suppliers, Ingredients, Invoices, Where-Used)
         ============================================================== -->

    <!-- Directory Modal 1: Supplier Create / Edit -->
    <div class="modal-backdrop" id="directory-supplier-modal">
        <div class="modal-container" style="max-width: 760px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="directory-supplier-modal-title"><i class="fa-solid fa-truck-field"></i> Ավելացնել Մատակարար</h3>
                <button type="button" onclick="ERP.directory.suppliers.closeModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <form onsubmit="ERP.directory.suppliers.submitForm(event)">
                <input type="hidden" id="sup-id">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Անվանում (Company Name) *</label>
                            <input type="text" class="form-control" id="sup-company-name" required placeholder="օր․ Արարատ Միս Ֆարմ ՍՊԸ">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Իրավաբանական Անվանում</label>
                            <input type="text" class="form-control" id="sup-legal-name" placeholder="օր․ «Արարատ Միս Ֆարմ» ՍՊԸ">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">ՀՎՀՀ (Tax ID)</label>
                            <input type="text" class="form-control font-mono" id="sup-tax-id" placeholder="օր․ 01548234">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Հեռախոսահամար *</label>
                            <input type="text" class="form-control font-mono" id="sup-phone" required placeholder="+374 93 112233">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Էլ. փոստ (Email)</label>
                            <input type="email" class="form-control" id="sup-email" placeholder="supplier@example.am">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Կայք (Website)</label>
                            <input type="text" class="form-control" id="sup-website" placeholder="https://supplier.am">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Կարգավիճակ</label>
                            <select class="select" id="sup-is-active">
                                <option value="1">Ակտիվ (Active)</option>
                                <option value="0">Կասեցված (Suspended)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Իրավաբանական Հասցե</label>
                            <input type="text" class="form-control" id="sup-legal-address" placeholder="օր․ ք․ Երևան, Տիգրան Մեծի 12">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Առաքման Հասցե (Որտեղից առաքվում են ապրանքները)</label>
                            <input type="text" class="form-control" id="sup-shipping-address" placeholder="օր․ ք․ Արտաշատ, Պահեստ 2">
                        </div>
                    </div>

                    <!-- Couriers Section (Առաքիչներ) -->
                    <div style="margin-top: 1rem; border-top: 1px solid var(--border-subtle); padding-top: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label class="form-label" style="margin-bottom: 0; font-weight: 800; color: var(--text-heading);">
                                <i class="fa-solid fa-truck"></i> Առաքիչներ (ID, Անուն Ազգանուն, Հեռախոս, Մակնիշ, Պետհամարանիշ)
                            </label>
                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="ERP.directory.suppliers.addCourierRow()">
                                <i class="fa-solid fa-plus"></i> Ավելացնել Առաքիչ
                            </button>
                        </div>
                        <div id="sup-couriers-list" style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <!-- Dynamically populated rows -->
                        </div>
                    </div>

                    <!-- Attached Products / Ingredients Section -->
                    <div style="margin-top: 1rem; border-top: 1px solid var(--border-subtle); padding-top: 0.75rem;">
                        <label class="form-label" style="font-weight: 800; color: var(--text-heading); margin-bottom: 0.35rem;">
                            <i class="fa-solid fa-box-open"></i> Մատակարարվող Ապրանքներ կամ Բաղադրիչներ
                        </label>
                        <div style="max-height: 140px; overflow-y: auto; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem;" id="sup-products-selector">
                            @foreach($products as $prod)
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;">
                                    <input type="checkbox" name="sup_product_ids[]" value="{{ $prod->id }}" class="sup-prod-chk">
                                    <span><strong>{{ $prod->sku }}</strong> — {{ $prod->getLocalizedName() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.directory.suppliers.closeModal()">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Պահպանել Մատակարարին</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Directory Modal 2: Ingredient Create / Edit (Ձեռքով Մուտքագրում) -->
    <div class="modal-backdrop" id="directory-ingredient-modal">
        <div class="modal-container" style="max-width: 820px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="directory-ingredient-modal-title"><i class="fa-solid fa-mortar-pestle"></i> Բաղադրիչի Մուտքագրում (Ձեռքով)</h3>
                <button type="button" onclick="ERP.directory.ingredients.closeModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <form onsubmit="ERP.directory.ingredients.submitForm(event)">
                <input type="hidden" id="ing-id">
                <div class="modal-body">
                    <!-- Categorization -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Կատեգորիա /խումբ/ *</label>
                            <select class="select" id="ing-category-id" onchange="ERP.directory.ingredients.updateSubcategories(this.value)">
                                <option value="">-- Ընտրեք Կատեգորիան --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ is_array($cat->name) ? ($cat->name['hy'] ?? reset($cat->name)) : $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ենթակատեգորիա /ենթախումբ/</label>
                            <select class="select" id="ing-subcategory-id">
                                <option value="">-- Ընտրեք Ենթակատեգորիան --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Identification -->
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Ապրանքի Անվանում *</label>
                            <input type="text" class="form-control" id="ing-name-hy" required placeholder="օր․ Ցորենի ալյուր բարձր տեսակի">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SKU / Ծածկագիր</label>
                            <div style="display: flex; gap: 4px;">
                                <input type="text" class="form-control font-mono" id="ing-sku" placeholder="ING-FLOUR-01">
                                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="ERP.directory.ingredients.generateSku()" title="Գեներացնել">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">EAN-13 Շտրիխկոդ</label>
                            <input type="text" class="form-control font-mono" id="ing-barcode" placeholder="օր․ 485000100201">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">ԱՏԳ ԱԱ ծածկագիր (HS Code)</label>
                            <input type="text" class="form-control font-mono" id="ing-hs-code" placeholder="օր․ 1101 00 150 0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Տարա (Packaging)</label>
                            <input type="text" class="form-control" id="ing-packaging" placeholder="օր․ Պարկ 25կգ, Տուփ, Շիշ">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Գործարքի Տեսակ</label>
                            <select class="select" id="ing-transaction-type">
                                <option value="local_purchase">Տեղական ձեռքբերում</option>
                                <option value="import_eaec">Ներմուծում ԵԱՏՄ</option>
                                <option value="import_third">Ներմուծում Երրորդ երկրներ</option>
                                <option value="service">Ծառայություն</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Նկարագրություն</label>
                        <input type="text" class="form-control" id="ing-description" placeholder="Հատկանիշներ, խոնավություն, որակական ցուցանիշներ...">
                    </div>

                    <!-- Pricing, Quantities & Live Calculations -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <div class="form-group">
                            <label class="form-label">Չափման Միավոր *</label>
                            <select class="select" id="ing-unit-id" required>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ is_array($u->name) ? ($u->name['hy'] ?? reset($u->name)) : $u->name }} ({{ $u->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Քանակ (Մուտք) *</label>
                            <input type="number" step="any" class="form-control font-mono" id="ing-quantity" value="1" required oninput="ERP.directory.ingredients.recalculate()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Միավորի Գին (֏) *</label>
                            <input type="number" step="any" class="form-control font-mono" id="ing-cost-price" value="0" required oninput="ERP.directory.ingredients.recalculate()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Զեղչ (%)</label>
                            <input type="number" step="any" class="form-control font-mono" id="ing-discount-percent" value="0" min="0" max="100" oninput="ERP.directory.ingredients.recalculate()">
                        </div>
                    </div>

                    <!-- Live Dynamic Math Box -->
                    <div class="calc-summary-box">
                        <div class="calc-summary-item">
                            <span class="calc-summary-label">Արժեք (Գին × Քանակ)</span>
                            <span class="calc-summary-val" id="calc-subtotal">0.00 ֏</span>
                        </div>
                        <div class="calc-summary-item">
                            <span class="calc-summary-label">Զեղչված Արժեք</span>
                            <span class="calc-summary-val" style="color: #0284C7;" id="calc-discounted">0.00 ֏</span>
                        </div>
                        <div class="calc-summary-item">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                <span class="calc-summary-label" style="margin-bottom: 0;">ԱԱՀ %</span>
                                <input type="number" id="ing-vat-rate" value="20" min="0" max="100" step="any" class="font-mono" style="width: 45px; font-size: 0.72rem; padding: 1px 3px; border: 1px solid #CBD5E1; border-radius: 4px;" oninput="ERP.directory.ingredients.recalculate()">
                            </div>
                            <span class="calc-summary-val" style="color: #475569;" id="calc-vat-amount">0.00 ֏</span>
                        </div>
                        <div class="calc-summary-item">
                            <span class="calc-summary-label">Ընդամենը (Ներառյալ ԱԱՀ)</span>
                            <span class="calc-summary-val" style="color: #059669;" id="calc-total-inc-vat">0.00 ֏</span>
                        </div>
                    </div>

                    <!-- Warehouse, Min Stock and Multiple Suppliers -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <div class="form-group">
                            <label class="form-label">Պահեստում Նվազագույն Քանակ (Հիշեցման համար)</label>
                            <input type="number" step="any" class="form-control font-mono" id="ing-min-stock-level" value="10" placeholder="օր․ 10">
                            <small style="color: var(--text-muted); font-size: 0.72rem;">Երբ մնացորդը հասնի կամ իջնի այս շեմից, համակարգը ցույց կտա ազդանշան:</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Մուտքի Պահեստ</label>
                            <select class="select" id="ing-warehouse-id">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.5rem;">
                        <label class="form-label" style="font-weight: 800; color: var(--text-heading);">
                            <i class="fa-solid fa-truck-field"></i> Մատակարարներ (Կարող են լինել մի քանիսը)
                        </label>
                        <div style="max-height: 120px; overflow-y: auto; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem;">
                            @foreach($suppliers as $sup)
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;">
                                    <input type="checkbox" name="ing_supplier_ids[]" value="{{ $sup->id }}" class="ing-sup-chk">
                                    <span><strong>{{ $sup->company_name }}</strong> (ՀՎՀՀ: {{ $sup->tax_id ?: '—' }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.directory.ingredients.closeModal()">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Պահպանել Բաղադրիչը</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Directory Modal 3: Invoice File Import (.xml, .xls, .csv) -->
    <div class="modal-backdrop" id="directory-invoice-import-modal">
        <div class="modal-container" style="max-width: 680px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-file-arrow-up"></i> Ինվոյսների Ներմուծում (.xml, .xls, .csv)</h3>
                <button type="button" onclick="ERP.directory.ingredients.closeImportModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <form onsubmit="ERP.directory.ingredients.submitImport(event)">
                <div class="modal-body">
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                        Վերբեռնեք մատակարարի հաշիվ-ապրանքագրի կամ ներմուծման ֆայլը (ՀՀ ՀՏ e-invoicing XML, Excel .xlsx/.xls կամ CSV ֆորմատով)՝ բաղադրիչները ավտոմատ ճանաչելու և պահեստ մուտքագրելու համար:
                    </p>

                    <!-- Dropzone -->
                    <div class="import-dropzone" id="invoice-dropzone" onclick="document.getElementById('invoice-file-input').click()">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0.5rem; display: block;"></i>
                        <div style="font-weight: 700; color: var(--text-heading); font-size: 0.95rem;">
                            Քաշեք և գցեք ֆայլը այստեղ կամ <span style="color: var(--color-primary); text-decoration: underline;">ընտրեք համակարգչից</span>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                            Աջակցվող ֆորմատներ՝ <strong>.XML</strong> (e-Invoicing / UBL), <strong>.XLSX / .XLS</strong>, <strong>.CSV</strong> (առավելագույնը 10MB)
                        </div>
                        <input type="file" id="invoice-file-input" accept=".xml,.csv,.txt,.xls,.xlsx" style="display: none;" onchange="ERP.directory.ingredients.handleFileSelect(this)">
                    </div>

                    <!-- Selected file info pill -->
                    <div id="invoice-file-preview" style="display: none; margin-top: 0.75rem; background: #F1F5F9; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid #CBD5E1; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-file-invoice" style="font-size: 1.5rem; color: var(--color-primary);"></i>
                            <div>
                                <div style="font-weight: 700; font-size: 0.85rem;" id="invoice-file-name">invoice.xml</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);" id="invoice-file-size">0 KB</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="ERP.directory.ingredients.clearFile()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Կանխադրված Մատակարար (Ըստ ցանկության)</label>
                            <select class="select" id="import-supplier-id">
                                <option value="">-- Ավտոմատ որոշել ֆայլից --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Մուտքի Պահեստ *</label>
                            <select class="select" id="import-warehouse-id" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.directory.ingredients.closeImportModal()">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary" id="import-submit-btn" disabled>
                        <i class="fa-solid fa-cloud-arrow-up"></i> Սկսել Ներմուծումը
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Directory Modal 4: Couriers List Modal -->
    <div class="modal-backdrop" id="directory-couriers-modal">
        <div class="modal-container" style="max-width: 620px;">
            <div class="modal-header">
                <h3 id="couriers-modal-title"><i class="fa-solid fa-truck"></i> Մատակարարի Առաքիչներ</h3>
                <button type="button" onclick="document.getElementById('directory-couriers-modal').classList.remove('active')" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body" id="couriers-modal-body">
                <!-- Dynamically populated -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('directory-couriers-modal').classList.remove('active')">Փակել</button>
            </div>
        </div>
    </div>

    <!-- Directory Modal 5: Where-Used Products Modal -->
    <div class="modal-backdrop" id="directory-where-used-modal">
        <div class="modal-container" style="max-width: 680px;">
            <div class="modal-header">
                <h3 id="where-used-modal-title"><i class="fa-solid fa-diagram-project"></i> Որտեղ է օգտագործվում բաղադրիչը</h3>
                <button type="button" onclick="document.getElementById('directory-where-used-modal').classList.remove('active')" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body" id="where-used-modal-body">
                <!-- Dynamically populated -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('directory-where-used-modal').classList.remove('active')">Փակել</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Pure Vanilla JS Engine -->
    <script src="/js/app.js"></script>

</body>
</html>
