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
            productCategories: @json($productCategories ?? []),
            ingredientCategories: @json($ingredientCategories ?? []),
            units: @json($units),
            customerSources: @json($customerSources ?? []),
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
            printers: @json($printers ?? []),
            documentTemplates: @json($documentTemplates ?? []),
            deliveryNotes: @json($deliveryNotes ?? []),
            printJobs: @json($printJobs ?? []),
            xmlImports: @json($xmlImports ?? []),
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
                    <!-- Directory Section (Տեղեկագիր) -->
                    <li style="margin-top: 0.5rem; padding: 0.35rem 0.85rem 0.15rem; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-subtle);">
                        <i class="fa-solid fa-book-bookmark" style="margin-right: 4px; color: var(--color-primary);"></i> Տեղեկագիր
                    </li>
                    <li>
                        <a href="#directory-customers" class="nav-item-link" data-view="directory-customers" onclick="ERP.navigateTo('directory-customers')">
                            <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
                            <span>Հաճախորդներ</span>
                            <span class="nav-pill" id="sidebar-customers-count">{{ count($customers) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#catalog" class="nav-item-link" data-view="catalog" onclick="ERP.navigateTo('catalog')">
                            <span class="nav-icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <span>Ապրանքներ</span>
                            <span class="nav-pill" id="sidebar-products-count">{{ count($products) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#directory-categories" class="nav-item-link" data-view="directory-categories" onclick="ERP.navigateTo('directory-categories')">
                            <span class="nav-icon"><i class="fa-solid fa-folder-tree"></i></span>
                            <span>Կատեգորիաներ</span>
                            <span class="nav-pill" id="sidebar-categories-count">{{ count($categories) }}</span>
                        </a>
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
                    <!-- Sales & Orders Section -->
                    <li style="margin-top: 0.5rem; padding: 0.35rem 0.85rem 0.15rem; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-subtle);">
                        <i class="fa-solid fa-cart-shopping" style="margin-right: 4px; color: var(--color-primary);"></i> Վաճառք & Պատվերներ
                    </li>
                    <li>
                        <a href="#orders" class="nav-item-link" data-view="orders" onclick="ERP.navigateTo('orders')">
                            <span class="nav-icon"><i class="fa-solid fa-cart-flatbed"></i></span>
                            <span>Պատվերներ</span>
                            <span class="nav-pill" id="sidebar-orders-count">{{ count($orders) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#delivery-notes" class="nav-item-link" data-view="delivery-notes" onclick="ERP.navigateTo('delivery-notes')">
                            <span class="nav-icon"><i class="fa-solid fa-file-signature"></i></span>
                            <span>Բեռնագրեր (B2B)</span>
                            <span class="nav-pill">{{ count($deliveryNotes) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#xml-import" class="nav-item-link" data-view="xml-import" onclick="ERP.navigateTo('xml-import')">
                            <span class="nav-icon"><i class="fa-solid fa-file-code"></i></span>
                            <span>XML Ներմուծում</span>
                            <span class="nav-pill">Center</span>
                        </a>
                    </li>
                    <!-- Printing & Hardware Section -->
                    <li style="margin-top: 0.5rem; padding: 0.35rem 0.85rem 0.15rem; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-subtle);">
                        <i class="fa-solid fa-print" style="margin-right: 4px; color: var(--color-primary);"></i> Տպում & Դիզայներ
                    </li>
                    <li>
                        <a href="#print-management" class="nav-item-link" data-view="print-management" onclick="ERP.navigateTo('print-management')">
                            <span class="nav-icon"><i class="fa-solid fa-print"></i></span>
                            <span>Տպիչներ & Հերթ</span>
                            <span class="nav-pill">{{ count($printers) }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="#document-designer" class="nav-item-link" data-view="document-designer" onclick="ERP.navigateTo('document-designer')">
                            <span class="nav-icon"><i class="fa-solid fa-palette"></i></span>
                            <span>Կտրոնի Դիզայներ</span>
                            <span class="nav-pill">Visual</span>
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
                     ============================================================== -->
                <section class="view-panel" id="view-dashboard">
                    <!-- Welcome Greeting Header -->
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
                     VIEW: DIRECTORY - PRODUCTS & ITEM MASTER (Տեղեկագիր: Ապրանքներ)
                     ============================================================== -->
                <section class="view-panel" id="view-catalog" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title"><i class="fa-solid fa-boxes-stacked" style="color: var(--color-primary); margin-right: 8px;"></i> Տեղեկագիր: Ապրանքներ &amp; Պրոդուկտներ</h2>
                            <p class="welcome-subtitle">Պատրաստի արտադրանք, կիսաֆաբրիկատներ, բաղադրիչներ, տեխնիկական քարտեր (BOM), ինքնարժեք և պահեստի մնացորդներ:</p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <button class="btn btn-secondary btn-sm" onclick="ERP.catalog.load()">
                                <i class="fa-solid fa-arrows-rotate"></i> Թարմացնել
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="ERP.catalog.openCreateProductModal()">
                                <i class="fa-solid fa-plus"></i> Ավելացնել Ապրանք
                            </button>
                        </div>
                    </div>

                    <!-- Summary KPI Cards -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ընդհանուր ապրանքներ</div>
                                    <div class="font-mono" id="prod-kpi-total" style="font-size: 1.5rem; font-weight: 800; color: var(--text-heading); margin-top: 4px;">{{ count($products) }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-box-archive"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Տեխ. Քարտ ունեցող</div>
                                    <div class="font-mono" id="prod-kpi-recipes" style="font-size: 1.5rem; font-weight: 800; color: #059669; margin-top: 4px;">{{ $products->filter(fn($p) => $p->recipes->isNotEmpty())->count() }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-scroll"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ստոպ-Լիստում</div>
                                    <div class="font-mono" id="prod-kpi-stoplist" style="font-size: 1.5rem; font-weight: 800; color: #DC2626; margin-top: 4px;">{{ $products->where('is_stop_list', true)->count() }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-ban"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Սակավ Մնացորդ</div>
                                    <div class="font-mono" id="prod-kpi-lowstock" style="font-size: 1.5rem; font-weight: 800; color: #D97706; margin-top: 4px;">{{ $products->filter(fn($p) => $p->track_stock && ($p->current_stock ?? 0) <= ($p->min_stock_level ?? 0))->count() }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Tabs & Controls -->
                    <div class="card" style="margin-bottom: 1.25rem; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                            <!-- Type Segmented Tabs -->
                            <div class="directory-tabs" id="catalog-type-tabs" style="margin-bottom: 0;">
                                <button class="directory-tab-btn active" data-type="all" onclick="ERP.catalog.filterType('all')">
                                    <i class="fa-solid fa-list"></i> Բոլորը
                                </button>
                                <button class="directory-tab-btn" data-type="finished_product" onclick="ERP.catalog.filterType('finished_product')">
                                    <i class="fa-solid fa-cubes"></i> Պատրաստի արտադրանք
                                </button>
                                <button class="directory-tab-btn" data-type="semi_finished" onclick="ERP.catalog.filterType('semi_finished')">
                                    <i class="fa-solid fa-puzzle-piece"></i> Կիսաֆաբրիկատներ
                                </button>
                                <button class="directory-tab-btn" data-type="modifier" onclick="ERP.catalog.filterType('modifier')">
                                    <i class="fa-solid fa-sliders"></i> Մոդիֆիկատորներ
                                </button>
                                <button class="directory-tab-btn" data-type="stop_list" onclick="ERP.catalog.filterType('stop_list')">
                                    <i class="fa-solid fa-ban"></i> Ստոպ-ցուցակ
                                </button>
                                <button class="directory-tab-btn" data-type="low_stock" onclick="ERP.catalog.filterType('low_stock')">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Սակավ մնացորդ
                                </button>
                            </div>

                            <!-- Search & Category Filters -->
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <select class="select" id="catalog-category-filter" onchange="ERP.catalog.load()" style="min-width: 170px; font-size: 0.8rem; padding: 6px 10px;">
                                    <option value="">Բոլոր կատեգորիաները</option>
                                    @foreach($productCategories ?? $categories as $cat)
                                        <option value="{{ $cat->id }}">{{ is_array($cat->name) ? ($cat->name['hy'] ?? \Illuminate\Support\Arr::first($cat->name)) : $cat->name }}</option>
                                    @endforeach
                                </select>
                                <div style="position: relative; width: 240px;">
                                    <input type="text" id="catalog-search" class="form-control" placeholder="Որոնել անվանում, SKU, EAN..." oninput="ERP.catalog.debouncedSearch()" style="padding-left: 30px; font-size: 0.82rem;">
                                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: var(--text-muted);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Table Card -->
                    <div class="card">
                        <div class="card-header" style="margin-bottom: 0.75rem;">
                            <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">
                                <i class="fa-solid fa-boxes-stacked" style="color: var(--color-primary); margin-right: 6px;"></i> Ապրանքների և Պրոդուկտների Ցանկ
                            </h3>
                            <span class="badge badge-indigo" id="catalog-count-badge">{{ count($products) }} Ապրանք</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="catalog-table">
                                <thead>
                                    <tr>
                                        <th style="width: 48px;">Պատկեր</th>
                                        <th>Անվանում &amp; Կատեգորիա</th>
                                        <th>Կոդեր (SKU / EAN / ԱՏԳ)</th>
                                        <th>Տեսակ</th>
                                        <th>Քանակ / Չափ</th>
                                        <th>Ինքնարժեք</th>
                                        <th>Վաճառքի Գին</th>
                                        <th>Կարգավիճակ</th>
                                        <th>Պահեստի Մնացորդ</th>
                                        <th style="text-align: right;">Գործողություններ</th>
                                    </tr>
                                </thead>
                                <tbody id="catalog-table-body">
                                    @forelse($products as $p)
                                        @php
                                            $typeBadges = [
                                                'finished_product' => ['cls' => 'badge-emerald', 'lbl' => 'Պատրաստի'],
                                                'semi_finished' => ['cls' => 'badge-indigo', 'lbl' => 'Կիսաֆաբրիկատ'],
                                                'ingredient' => ['cls' => 'badge-amber', 'lbl' => 'Բաղադրիչ'],
                                                'raw_material' => ['cls' => 'badge-cyan', 'lbl' => 'Հումք'],
                                                'packaging' => ['cls' => 'badge-slate', 'lbl' => 'Փաթեթավորում'],
                                                'service' => ['cls' => 'badge-violet', 'lbl' => 'Ծառայություն'],
                                                'modifier' => ['cls' => 'badge-orange', 'lbl' => 'Մոդիֆիկատոր'],
                                            ];
                                            $tInfo = $typeBadges[$p->type ?? 'finished_product'] ?? ['cls' => 'badge-slate', 'lbl' => $p->type];
                                            $imgUrl = !empty($p->images) && is_array($p->images) ? \Illuminate\Support\Arr::first($p->images) : null;
                                            $stock = (float) ($p->current_stock ?? 0);
                                            $isLowStock = $p->track_stock && $stock <= (float) ($p->min_stock_level ?? 0);
                                            $hasRecipe = $p->recipes->isNotEmpty();
                                        @endphp
                                        <tr data-id="{{ $p->id }}" data-type="{{ $p->type }}" data-stoplist="{{ $p->is_stop_list ? '1' : '0' }}">
                                            <td>
                                                @if($imgUrl)
                                                    <img src="{{ $imgUrl }}" class="product-thumb-sm" alt="Thumbnail">
                                                @else
                                                    <div class="product-thumb-sm"><i class="fa-solid fa-cube"></i></div>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="font-weight: 700; color: var(--text-heading); font-size: 0.88rem;">
                                                    {{ is_array($p->name) ? ($p->name['hy'] ?? \Illuminate\Support\Arr::first($p->name)) : $p->name }}
                                                </div>
                                                <div style="display: flex; gap: 4px; align-items: center; margin-top: 2px;">
                                                    <span style="font-size: 0.72rem; color: var(--text-muted);">
                                                        {{ $p->category?->name ? (is_array($p->category->name) ? ($p->category->name['hy'] ?? \Illuminate\Support\Arr::first($p->category->name)) : $p->category->name) : 'Ընդհանուր' }}
                                                    </span>
                                                    @if($p->packaging)
                                                        <span class="item-chip" style="font-size: 0.65rem; padding: 1px 4px;">{{ $p->packaging }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="font-mono" style="font-weight: 700; color: var(--color-primary); font-size: 0.78rem;">{{ $p->sku }}</div>
                                                @if($p->barcode)
                                                    <div class="font-mono" style="font-size: 0.7rem; color: var(--text-muted);"><i class="fa-solid fa-barcode"></i> {{ $p->barcode }}</div>
                                                @endif
                                                @if($p->hs_code)
                                                    <div style="font-size: 0.68rem; color: #64748B;">ԱՏԳ: {{ $p->hs_code }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $tInfo['cls'] }}">{{ $tInfo['lbl'] }}</span>
                                            </td>
                                            <td>
                                                @if($p->net_quantity)
                                                    <div style="font-weight: 600; font-size: 0.8rem; color: var(--text-heading);">{{ $p->net_quantity }}</div>
                                                @endif
                                                <span class="font-mono" style="font-size: 0.72rem; color: var(--text-muted);">{{ $p->unit?->name ? (is_array($p->unit->name) ? ($p->unit->name['hy'] ?? \Illuminate\Support\Arr::first($p->unit->name)) : $p->unit->name) : ($p->unit?->code ?? 'հատ') }}</span>
                                            </td>
                                            <td>
                                                <div class="font-mono" style="font-weight: 700; color: #334155;">{{ number_format($p->cost_price ?? 0, 0) }} ֏</div>
                                                @if($hasRecipe)
                                                    <span class="item-chip" style="background: #ECFDF5; color: #059669; border-color: #A7F3D0; font-size: 0.65rem;">
                                                        <i class="fa-solid fa-scroll"></i> BOM ինքնարժեք
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="font-mono" style="font-weight: 800; color: var(--color-success); font-size: 0.9rem;">
                                                    {{ number_format($p->sale_price ?? 0, 0) }} ֏
                                                </div>
                                                @if($p->special_price && $p->special_price > 0)
                                                    <div class="font-mono" style="font-size: 0.72rem; color: #D97706; text-decoration: line-through;">
                                                        Ակցիա՝ {{ number_format($p->special_price ?? 0, 0) }} ֏
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                                    @if($p->is_stop_list)
                                                        <span class="badge-stop-list"><i class="fa-solid fa-ban"></i> Ստոպ-լիստ</span>
                                                    @endif
                                                    @if($p->allow_modifiers)
                                                        <span class="badge badge-orange" style="font-size: 0.65rem;"><i class="fa-solid fa-sliders"></i> Մոդիֆիկատորներ</span>
                                                    @endif
                                                    @if($p->has_vat)
                                                        <span style="font-size: 0.68rem; color: #64748B;">ԱԱՀ {{ $p->vat_rate ?? 20 }}%</span>
                                                    @else
                                                        <span style="font-size: 0.68rem; color: #059669; font-weight: 700;">Առանց ԱԱՀ</span>
                                                    @endif
                                                    @if($p->is_excise)
                                                        <span class="badge badge-slate" style="font-size: 0.65rem;">Ակցիզ</span>
                                                    @endif
                                                    @if($p->is_marked)
                                                        <span class="badge badge-cyan" style="font-size: 0.65rem;">DataMatrix</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($p->track_stock)
                                                    <div class="font-mono" style="font-weight: 800; font-size: 0.88rem; color: {{ $isLowStock ? '#DC2626' : 'var(--text-heading)' }};">
                                                        {{ number_format($stock, 2) }}
                                                    </div>
                                                    @if($isLowStock)
                                                        <span class="low-stock-badge" style="font-size: 0.65rem; padding: 1px 5px;">
                                                            <i class="fa-solid fa-triangle-exclamation"></i> Սակավ
                                                        </span>
                                                    @endif
                                                @else
                                                    <span style="font-size: 0.72rem; color: var(--text-muted);">Անսահմանափակ</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right; white-space: nowrap;">
                                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.catalog.openTechnicalCard('{{ $p->id }}')" title="Տեխնիկական քարտ / BOM" style="padding: 4px 7px; color: #059669; border-color: #A7F3D0; background: #ECFDF5;">
                                                    <i class="fa-solid fa-scroll"></i> Տեխ. Քարտ
                                                </button>
                                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.catalog.openQuickProduce('{{ $p->id }}')" title="Արտադրել խմբաքանակ" style="padding: 4px 7px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF;">
                                                    <i class="fa-solid fa-industry"></i> Արտադրել
                                                </button>
                                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.catalog.openEditProductModal('{{ $p->id }}')" title="Խմբագրել" style="padding: 4px 7px;">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.catalog.deleteProduct('{{ $p->id }}')" title="Հեռացնել" style="padding: 4px 7px; color: #DC2626;">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" style="text-align: center; padding: 2rem; color: var(--text-muted);">Գրանցված ապրանքներ չկան:</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: DIRECTORY - CATEGORIES (Տեղեկագիր: Կատեգորիաներ)
                     ============================================================== -->
                <section class="view-panel" id="view-directory-categories" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title"><i class="fa-solid fa-folder-tree" style="color: var(--color-primary); margin-right: 8px;"></i> Տեղեկագիր: Կատեգորիաներ &amp; Բաժիններ</h2>
                            <p class="welcome-subtitle">Ապրանքային և հումքային խմբեր, ենթակատեգորիաներ, լուսանկարներ, դասավորություն և ակտիվ կարգավիճակ:</p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button class="btn btn-secondary btn-sm" onclick="ERP.directory.categories.load()">
                                <i class="fa-solid fa-arrows-rotate"></i> Թարմացնել
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="ERP.directory.categories.openCreateModal()">
                                <i class="fa-solid fa-plus"></i> Ավելացնել Կատեգորիա
                            </button>
                        </div>
                    </div>

                    <!-- Category KPI Cards -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ընդհանուր Խմբեր</div>
                                    <div class="font-mono" id="cat-kpi-total" style="font-size: 1.5rem; font-weight: 800; color: var(--text-heading); margin-top: 4px;">{{ count($categories) }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ապրանքային Խմբեր</div>
                                    <div class="font-mono" id="cat-kpi-product" style="font-size: 1.5rem; font-weight: 800; color: #2563EB; margin-top: 4px;">{{ $categories->filter(fn($c) => ($c->type ?? 'product') === 'product')->count() }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Բաղադրիչների Խմբեր</div>
                                    <div class="font-mono" id="cat-kpi-ingredient" style="font-size: 1.5rem; font-weight: 800; color: #D97706; margin-top: 4px;">{{ $categories->filter(fn($c) => ($c->type ?? '') === 'ingredient')->count() }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-mortar-pestle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ենթակատեգորիաներ</div>
                                    <div class="font-mono" id="cat-kpi-sub" style="font-size: 1.5rem; font-weight: 800; color: #7C3AED; margin-top: 4px;">{{ $categories->whereNotNull('parent_id')->count() }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #F5F3FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Filter & Search Bar -->
                    <div class="card" style="margin-bottom: 1.25rem; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                            <div class="directory-tabs" id="category-filter-tabs" style="margin-bottom: 0;">
                                <button type="button" class="directory-tab-btn active" data-type="all" onclick="ERP.directory.categories.filterType('all')">
                                    <i class="fa-solid fa-list"></i> Բոլորը (<span id="cat-tab-count-all">{{ count($categories) }}</span>)
                                </button>
                                <button type="button" class="directory-tab-btn" data-type="product" onclick="ERP.directory.categories.filterType('product')">
                                    <i class="fa-solid fa-boxes-stacked" style="color: #2563EB;"></i> Ապրանքային (<span id="cat-tab-count-product">{{ $categories->filter(fn($c) => ($c->type ?? 'product') === 'product')->count() }}</span>)
                                </button>
                                <button type="button" class="directory-tab-btn" data-type="ingredient" onclick="ERP.directory.categories.filterType('ingredient')">
                                    <i class="fa-solid fa-mortar-pestle" style="color: #D97706;"></i> Բաղադրիչների (<span id="cat-tab-count-ingredient">{{ $categories->filter(fn($c) => ($c->type ?? '') === 'ingredient')->count() }}</span>)
                                </button>
                                <button type="button" class="directory-tab-btn" data-type="root" onclick="ERP.directory.categories.filterType('root')">
                                    <i class="fa-solid fa-folder"></i> Գլխավոր (<span id="cat-tab-count-root">{{ $categories->whereNull('parent_id')->count() }}</span>)
                                </button>
                                <button type="button" class="directory-tab-btn" data-type="sub" onclick="ERP.directory.categories.filterType('sub')">
                                    <i class="fa-solid fa-folder-open"></i> Ենթախմբեր (<span id="cat-tab-count-sub">{{ $categories->whereNotNull('parent_id')->count() }}</span>)
                                </button>
                            </div>

                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <div style="position: relative; width: 260px;">
                                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.75rem;"></i>
                                    <input type="text" id="cat-search-input" class="form-control form-control-sm" placeholder="Որոնել կատեգորիա, slug..." oninput="ERP.directory.categories.handleSearch(this.value)" style="padding-left: 30px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Table Card -->
                    <div class="card">
                        <div class="card-header" style="margin-bottom: 0.75rem;">
                            <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">
                                <i class="fa-solid fa-folder-tree" style="color: var(--color-primary); margin-right: 6px;"></i> Կատեգորիաների Ցանկ
                            </h3>
                            <span class="badge badge-indigo" id="cat-count-badge">{{ count($categories) }} Խումբ</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="directory-categories-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Պատկեր</th>
                                        <th>Անվանում (Հայերեն / English)</th>
                                        <th>Տեսակ</th>
                                        <th>Slug / Կոդ</th>
                                        <th>Գլխավոր Խումբ</th>
                                        <th>Ապրանքներ</th>
                                        <th>Դասավորություն</th>
                                        <th>Կարգավիճակ</th>
                                        <th style="text-align: right;">Գործողություններ</th>
                                    </tr>
                                </thead>
                                <tbody id="directory-categories-table-body">
                                    @forelse($categories as $cat)
                                        @php
                                            $nameHy = is_array($cat->name) ? ($cat->name['hy'] ?? \Illuminate\Support\Arr::first($cat->name)) : $cat->name;
                                            $nameEn = is_array($cat->name) ? ($cat->name['en'] ?? '') : '';
                                            $parentName = $cat->parent ? (is_array($cat->parent->name) ? ($cat->parent->name['hy'] ?? \Illuminate\Support\Arr::first($cat->parent->name)) : $cat->parent->name) : null;
                                            $catType = $cat->type ?? 'product';
                                        @endphp
                                        <tr data-id="{{ $cat->id }}" data-type="{{ $catType }}" data-parent="{{ $cat->parent_id ? '1' : '0' }}">
                                            <td>
                                                @if($cat->image_url)
                                                    <img src="{{ $cat->image_url }}" class="category-thumb-sm" alt="Thumbnail">
                                                @else
                                                    <div class="category-thumb-sm"><i class="fa-solid {{ $catType === 'ingredient' ? 'fa-mortar-pestle' : 'fa-folder' }}"></i></div>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="font-weight: 700; color: var(--text-heading); font-size: 0.88rem;">{{ $nameHy }}</div>
                                                @if($nameEn)
                                                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $nameEn }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($catType === 'ingredient')
                                                    <span class="badge badge-amber" style="font-size: 0.72rem; font-weight: 700;">
                                                        <i class="fa-solid fa-mortar-pestle"></i> Բաղադրիչների
                                                    </span>
                                                @else
                                                    <span class="badge badge-indigo" style="font-size: 0.72rem; font-weight: 700;">
                                                        <i class="fa-solid fa-boxes-stacked"></i> Ապրանքային
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="font-mono" style="font-size: 0.78rem; font-weight: 700; color: var(--color-primary);">{{ $cat->slug }}</span>
                                            </td>
                                            <td>
                                                @if($parentName)
                                                    <span class="item-chip" style="background: #F5F3FF; color: #7C3AED; border-color: #DDD6FE;">
                                                        <i class="fa-solid fa-folder-open"></i> {{ $parentName }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-emerald" style="font-size: 0.68rem;">Գլխավոր Խումբ</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-slate" style="font-weight: 700; font-size: 0.75rem;">
                                                    <i class="fa-solid fa-box"></i> {{ $cat->products_count ?? 0 }} ապրանք
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-mono" style="font-size: 0.78rem; color: #64748B;">{{ $cat->sort_order }}</span>
                                            </td>
                                            <td>
                                                @if($cat->is_active)
                                                    <span class="badge badge-emerald"><i class="fa-solid fa-circle-check"></i> Ակտիվ</span>
                                                @else
                                                    <span class="badge badge-amber"><i class="fa-solid fa-circle-pause"></i> Պասիվ</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right; white-space: nowrap;">
                                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.directory.categories.openEditModal('{{ $cat->id }}')" title="Խմբագրել" style="padding: 4px 7px;">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.directory.categories.deleteCategory('{{ $cat->id }}')" title="Հեռացնել" style="padding: 4px 7px; color: #DC2626;">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-muted);">Գրանցված կատեգորիաներ չկան:</td></tr>
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
                                    @foreach($ingredientCategories ?? $categories as $cat)
                                        <option value="{{ $cat->id }}">{{ is_array($cat->name) ? ($cat->name['hy'] ?? \Illuminate\Support\Arr::first($cat->name)) : $cat->name }}</option>
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
                                                    {{ $ing->category ? (is_array($ing->category->name) ? ($ing->category->name['hy'] ?? \Illuminate\Support\Arr::first($ing->category->name)) : $ing->category->name) : '—' }}
                                                </div>
                                                @if($ing->subcategory)
                                                    <div style="font-size: 0.72rem; color: var(--color-primary);">
                                                        ↳ {{ is_array($ing->subcategory->name) ? ($ing->subcategory->name['hy'] ?? \Illuminate\Support\Arr::first($ing->subcategory->name)) : $ing->subcategory->name }}
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
                                                {{ $ing->unit ? (is_array($ing->unit->name) ? ($ing->unit->name['hy'] ?? \Illuminate\Support\Arr::first($ing->unit->name)) : $ing->unit->name) : 'կգ' }}
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
                     VIEW: DIRECTORY - CUSTOMERS (Տեղեկագիր: Հաճախորդներ & CRM 360°)
                     ============================================================== -->
                <section class="view-panel" id="view-directory-customers" style="display: none;">
                    <div class="welcome-banner">
                        <div>
                            <h2 class="welcome-title"><i class="fa-solid fa-users" style="color: var(--color-primary); margin-right: 8px;"></i> Տեղեկագիր: Հաճախորդներ (CRM 360°)</h2>
                            <p class="welcome-subtitle">Ֆիզիկական և իրավաբանական անձինք, հասցեներ, բազմակի կոնտակտներ, պատվերների վիճակագրություն, լոյալության հաշիվ և Timeline:</p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                            <button class="btn btn-secondary btn-sm" onclick="ERP.directory.customers.load()">
                                <i class="fa-solid fa-arrows-rotate"></i> Թարմացնել
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="ERP.directory.customers.exportCSV()">
                                <i class="fa-solid fa-file-csv"></i> Արտահանել CSV
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="ERP.directory.customers.openMergeModal()">
                                <i class="fa-solid fa-code-merge"></i> Միավորել
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="ERP.directory.customers.openCreateModal('individual')">
                                <i class="fa-solid fa-user-plus"></i> + Ֆիզիկական
                            </button>
                            <button class="btn btn-primary btn-sm" style="background: #7C3AED; border-color: #6D28D9;" onclick="ERP.directory.customers.openCreateModal('company')">
                                <i class="fa-solid fa-building"></i> + Իրավաբանական
                            </button>
                        </div>
                    </div>

                    <!-- Customer KPI Cards -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ընդհանուր Հաճախորդներ</div>
                                    <div class="font-mono" id="cust-kpi-total" style="font-size: 1.5rem; font-weight: 800; color: var(--text-heading); margin-top: 4px;">{{ count($customers) }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ֆիզիկական Անձինք (B2C)</div>
                                    <div class="font-mono" id="cust-kpi-individual" style="font-size: 1.5rem; font-weight: 800; color: #2563EB; margin-top: 4px;">{{ $customers->where('type', 'individual')->count() }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Իրավաբանական (B2B)</div>
                                    <div class="font-mono" id="cust-kpi-company" style="font-size: 1.5rem; font-weight: 800; color: #7C3AED; margin-top: 4px;">{{ $customers->where('type', 'company')->count() }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #F5F3FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Լոյալության Միավորներ</div>
                                    <div class="font-mono" id="cust-kpi-points" style="font-size: 1.5rem; font-weight: 800; color: #D97706; margin-top: 4px;">{{ number_format($customers->sum(fn($c) => $c->loyaltyAccount?->points_balance ?? 0), 0) }}</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                    <i class="fa-solid fa-award"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ընդհանուր Վաճառք (LTV)</div>
                                    <div class="font-mono" id="cust-kpi-revenue" style="font-size: 1.5rem; font-weight: 800; color: #059669; margin-top: 4px;">{{ number_format($customers->sum('total_spent'), 0) }} ֏</div>
                                </div>
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Filters & Search -->
                    <div class="card" style="margin-bottom: 1.25rem; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                            <div class="directory-tabs" id="customer-type-tabs" style="margin-bottom: 0;">
                                <button type="button" class="directory-tab-btn active" data-type="all" onclick="ERP.directory.customers.filterType('all')">
                                    <i class="fa-solid fa-list"></i> Բոլորը (<span id="cust-tab-count-all">{{ count($customers) }}</span>)
                                </button>
                                <button type="button" class="directory-tab-btn" data-type="individual" onclick="ERP.directory.customers.filterType('individual')">
                                    <i class="fa-solid fa-user" style="color: #2563EB;"></i> Ֆիզիկական (<span id="cust-tab-count-individual">{{ $customers->where('type', 'individual')->count() }}</span>)
                                </button>
                                <button type="button" class="directory-tab-btn" data-type="company" onclick="ERP.directory.customers.filterType('company')">
                                    <i class="fa-solid fa-building" style="color: #7C3AED;"></i> Իրավաբանական (<span id="cust-tab-count-company">{{ $customers->where('type', 'company')->count() }}</span>)
                                </button>
                            </div>

                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                <div style="position: relative; min-width: 240px;">
                                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 0.85rem;"></i>
                                    <input type="text" id="cust-search-input" class="form-control" placeholder="Որոնել (անուն, ՀՎՀՀ, հեռախոս, կոդ)..." style="padding-left: 30px; font-size: 0.82rem;" oninput="ERP.directory.customers.onSearch(this.value)">
                                </div>

                                <select id="cust-filter-status" class="form-control" style="width: 140px; font-size: 0.82rem;" onchange="ERP.directory.customers.filterStatus(this.value)">
                                    <option value="all">Բոլոր կարգավիճակները</option>
                                    <option value="active" selected>Ակտիվ</option>
                                    <option value="inactive">Ոչ ակտիվ</option>
                                    <option value="blocked">Արգելափակված</option>
                                    <option value="archived">Արխիվացված</option>
                                </select>

                                <select id="cust-filter-branch" class="form-control" style="width: 140px; font-size: 0.82rem;" onchange="ERP.directory.customers.filterBranch(this.value)">
                                    <option value="all">Բոլոր մասնաճյուղերը</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>

                                <select id="cust-filter-source" class="form-control" style="width: 130px; font-size: 0.82rem;" onchange="ERP.directory.customers.filterSource(this.value)">
                                    <option value="all">Բոլոր աղբյուրները</option>
                                    @foreach($customerSources as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>

                                <select id="cust-filter-tier" class="form-control" style="width: 120px; font-size: 0.82rem;" onchange="ERP.directory.customers.filterTier(this.value)">
                                    <option value="all">Բոլոր Tier-երը</option>
                                    <option value="basic">Basic</option>
                                    <option value="bronze">Bronze</option>
                                    <option value="silver">Silver</option>
                                    <option value="gold">Gold</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Customers Table Card -->
                    <div class="card">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-heading); margin: 0;">Հաճախորդների Ռեգիստր</h3>
                                <span class="badge badge-primary font-mono" id="cust-table-badge-count">{{ count($customers) }}</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="directory-customers-table">
                                <thead>
                                    <tr>
                                        <th>Կոդ</th>
                                        <th>Հաճախորդ / Ընկերություն</th>
                                        <th>Կոնտակտներ</th>
                                        <th>Առաքման Հասցե</th>
                                        <th>Լոյալություն &amp; Զեղչ</th>
                                        <th>Գնումներ (Քանակ / LTV)</th>
                                        <th>Score</th>
                                        <th>Կարգավիճակ</th>
                                        <th style="text-align: right;">Գործողություններ</th>
                                    </tr>
                                </thead>
                                <tbody id="directory-customers-table-body">
                                    <!-- Rendered dynamically by ERP.directory.customers -->
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
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-primary);">{{ number_format($po->total_amount ?? 0, 0) }} ֏</td>
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
                     VIEW: ORDERS MANAGEMENT (ՊԱՏՎԵՐՆԵՐ)
                     ============================================================== -->
                <section class="view-panel" id="view-orders" style="display: none;">
                    <div class="view-header">
                        <div>
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-heading); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-cart-flatbed" style="color: var(--color-primary);"></i>
                                <span>Պատվերների Կառավարում</span>
                            </h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem;">
                                Բոլոր ալիքների պատվերներ՝ POS, Առցանց, Մանրածախ, B2B, XML ներմուծում
                            </p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button class="btn btn-secondary" onclick="ERP.orders.load()">
                                <i class="fa-solid fa-rotate"></i> <span>Թարմացնել</span>
                            </button>
                            <button class="btn btn-primary" onclick="ERP.orders.openCreateModal()">
                                <i class="fa-solid fa-plus"></i> <span>Նոր Պատվեր</span>
                            </button>
                        </div>
                    </div>

                    <!-- Metrics Summary Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                        <div class="card" style="padding: 1rem; border-left: 4px solid var(--color-primary);">
                            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Ընդհանուր Պատվերներ</div>
                            <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-heading); margin-top: 0.25rem;" id="orders-kpi-total">{{ count($orders) }}</div>
                            <div style="font-size: 0.75rem; color: #16a34a; margin-top: 0.2rem;"><i class="fa-solid fa-arrow-trend-up"></i> Ակտիվ համակարգում</div>
                        </div>
                        <div class="card" style="padding: 1rem; border-left: 4px solid #16a34a;">
                            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Ընդհանուր Հասույթ</div>
                            <div style="font-size: 1.6rem; font-weight: 800; color: #16a34a; margin-top: 0.25rem;" id="orders-kpi-revenue">{{ number_format($orders->sum('total'), 0) }} ֏</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">Հաշվարկված գումար</div>
                        </div>
                        <div class="card" style="padding: 1rem; border-left: 4px solid #f59e0b;">
                            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Ընթացքի Մեջ / Սպասող</div>
                            <div style="font-size: 1.6rem; font-weight: 800; color: #f59e0b; margin-top: 0.25rem;" id="orders-kpi-pending">{{ $orders->whereIn('status', ['new', 'confirmed', 'processing'])->count() }}</div>
                            <div style="font-size: 0.75rem; color: #f59e0b; margin-top: 0.2rem;">Պահանջում է գործողություն</div>
                        </div>
                        <div class="card" style="padding: 1rem; border-left: 4px solid #6366f1;">
                            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Վճարված Պատվերներ</div>
                            <div style="font-size: 1.6rem; font-weight: 800; color: #6366f1; margin-top: 0.25rem;" id="orders-kpi-paid">{{ $orders->where('payment_status', 'paid')->count() }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">Լրիվ մարված հաշիվներ</div>
                        </div>
                    </div>

                    <!-- Filter Toolbar -->
                    <div class="card" style="padding: 1rem; margin-bottom: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 0.75rem; align-items: center;">
                            <div style="position: relative;">
                                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                <input type="text" class="input" id="orders-search-input" placeholder="Որոնել պատվերի #, հաճախորդ, հեռախոս, ՀՎՀՀ..." style="padding-left: 2.2rem;" onkeyup="ERP.orders.handleFilterChange()">
                            </div>
                            <select class="input" id="orders-status-filter" onchange="ERP.orders.handleFilterChange()">
                                <option value="">Բոլոր Կարգավիճակները</option>
                                <option value="new">Նոր (New)</option>
                                <option value="confirmed">Հաստատված (Confirmed)</option>
                                <option value="processing">Պատրաստվում է (Processing)</option>
                                <option value="ready">Պատրաստ է (Ready)</option>
                                <option value="delivery">Առաքման մեջ (Delivery)</option>
                                <option value="delivered">Առաքված (Delivered)</option>
                                <option value="completed">Ավարտված (Completed)</option>
                                <option value="cancelled">Չեղարկված (Cancelled)</option>
                            </select>
                            <select class="input" id="orders-payment-filter" onchange="ERP.orders.handleFilterChange()">
                                <option value="">Բոլոր Վճարումները</option>
                                <option value="paid">Վճարված (Paid)</option>
                                <option value="partially_paid">Մասնակի (Partially Paid)</option>
                                <option value="unpaid">Չվճարված (Unpaid)</option>
                                <option value="refunded">Վերադարձված (Refunded)</option>
                            </select>
                            <select class="input" id="orders-source-filter" onchange="ERP.orders.handleFilterChange()">
                                <option value="">Բոլոր Աղբյուրները</option>
                                <option value="direct">Direct</option>
                                <option value="pos">POS Terminal</option>
                                <option value="web">Storefront / Web</option>
                                <option value="xml_import">XML Import</option>
                                <option value="manual">Manual Back-office</option>
                            </select>
                            <button class="btn btn-secondary" onclick="ERP.orders.resetFilters()" title="Մաքրել ֆիլտրերը">
                                <i class="fa-solid fa-filter-circle-xmark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Orders Table Card -->
                    <div class="card" style="overflow: hidden;">
                        <div class="table-responsive">
                            <table class="table" id="orders-table">
                                <thead>
                                    <tr>
                                        <th>Պատվերի #</th>
                                        <th>Ամսաթիվ / Ժամ</th>
                                        <th>Հաճախորդ</th>
                                        <th>Մասնաճյուղ</th>
                                        <th>Աղբյուր</th>
                                        <th>Տողեր</th>
                                        <th>Գումար (AMD)</th>
                                        <th>Վճարում</th>
                                        <th>Կարգավիճակ</th>
                                        <th style="text-align: right;">Գործողություններ</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-table-body">
                                    @forelse($orders as $ord)
                                        <tr>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-primary); cursor: pointer;" onclick="ERP.orders.viewDetails('{{ $ord->id }}')">
                                                {{ $ord->order_number }}
                                            </td>
                                            <td style="font-size: 0.8rem;">
                                                <div>{{ $ord->placed_at ? $ord->placed_at->format('d.m.Y H:i') : '' }}</div>
                                                @if($ord->scheduled_for)
                                                    <div style="color: #6366f1; font-size: 0.75rem;"><i class="fa-regular fa-calendar-check"></i> {{ $ord->scheduled_for->format('d.m.Y H:i') }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="font-weight: 700; color: var(--text-heading);">
                                                    {{ $ord->customer_snapshot['name'] ?? ($ord->customer ? ($ord->customer->first_name . ' ' . $ord->customer->last_name) : 'Retail Customer') }}
                                                </div>
                                                @if(!empty($ord->customer_snapshot['tax_id']) || !empty($ord->customer?->tax_id))
                                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace;">ՀՎՀՀ: {{ $ord->customer_snapshot['tax_id'] ?? $ord->customer?->tax_id }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-slate">{{ $ord->branch?->name ?? 'Main Branch' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-slate font-mono">{{ strtoupper($ord->source) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-slate font-mono">{{ count($ord->items) }} տող</span>
                                            </td>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-primary);">
                                                {{ number_format($ord->total, 0) }} ֏
                                            </td>
                                            <td>
                                                @if($ord->payment_status === 'paid')
                                                    <span class="badge badge-green"><i class="fa-solid fa-check"></i> Վճարված</span>
                                                @elseif($ord->payment_status === 'partially_paid')
                                                    <span class="badge badge-amber"><i class="fa-solid fa-circle-half-stroke"></i> Մասնակի</span>
                                                @elseif($ord->payment_status === 'refunded')
                                                    <span class="badge badge-purple"><i class="fa-solid fa-rotate-left"></i> Վերադարձ</span>
                                                @else
                                                    <span class="badge badge-rose"><i class="fa-solid fa-xmark"></i> Չվճարված</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ord->status === 'new')
                                                    <span class="badge badge-amber">Նոր (New)</span>
                                                @elseif($ord->status === 'confirmed')
                                                    <span class="badge badge-blue">Հաստատված</span>
                                                @elseif($ord->status === 'processing')
                                                    <span class="badge badge-indigo">Պատրաստվում է</span>
                                                @elseif($ord->status === 'ready')
                                                    <span class="badge badge-teal">Պատրաստ է</span>
                                                @elseif($ord->status === 'delivery')
                                                    <span class="badge badge-purple">Առաքվում է</span>
                                                @elseif($ord->status === 'completed' || $ord->status === 'delivered')
                                                    <span class="badge badge-green">Ավարտված</span>
                                                @elseif($ord->status === 'cancelled')
                                                    <span class="badge badge-slate" style="text-decoration: line-through;">Չեղարկված</span>
                                                @else
                                                    <span class="badge badge-slate">{{ $ord->status }}</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display: flex; gap: 0.35rem; justify-content: flex-end;">
                                                    <button class="btn btn-sm btn-secondary" onclick="ERP.orders.viewDetails('{{ $ord->id }}')" title="Մանրամասն">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-secondary" onclick="ERP.orders.printOrder('{{ $ord->id }}', 'pos_receipt')" title="Տպել Կտրոն">
                                                        <i class="fa-solid fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                                <i class="fa-solid fa-cart-flatbed" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.5rem; display: block;"></i>
                                                Պատվերներ չեն գտնվել:
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: B2B DELIVERY NOTES (ԲԵՌՆԱԳՐԵՐ)
                     ============================================================== -->
                <section class="view-panel" id="view-delivery-notes" style="display: none;">
                    <div class="view-header">
                        <div>
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-heading); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-file-signature" style="color: var(--color-primary);"></i>
                                <span>Բեռնագրեր / B2B Накладная</span>
                            </h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem;">
                                Պաշտոնական առաքման և հանձնման-ընդունման փաստաթղթեր (A4 ձևաչափ, վերատպման աուդիտ)
                            </p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button class="btn btn-secondary" onclick="ERP.deliveryNotes.load()">
                                <i class="fa-solid fa-rotate"></i> <span>Թարմացնել</span>
                            </button>
                            <button class="btn btn-primary" onclick="ERP.deliveryNotes.openGenerateModal()">
                                <i class="fa-solid fa-file-circle-plus"></i> <span>Ստեղծել Բեռնագիր</span>
                            </button>
                        </div>
                    </div>

                    <div class="card" style="overflow: hidden;">
                        <div class="table-responsive">
                            <table class="table" id="delivery-notes-table">
                                <thead>
                                    <tr>
                                        <th>Բեռնագրի #</th>
                                        <th>Ամսաթիվ</th>
                                        <th>Գնորդ / Հաճախորդ</th>
                                        <th>ՀՎՀՀ / TIN</th>
                                        <th>Պատվերի #</th>
                                        <th>Ընդհանուր Գումար</th>
                                        <th>Վերատպումներ</th>
                                        <th>Կարգավիճակ</th>
                                        <th style="text-align: right;">Գործողություններ</th>
                                    </tr>
                                </thead>
                                <tbody id="delivery-notes-table-body">
                                    @forelse($deliveryNotes as $dn)
                                        <tr>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-primary); cursor: pointer;" onclick="ERP.deliveryNotes.viewAndPrint('{{ $dn->id }}')">
                                                {{ $dn->document_number }}
                                            </td>
                                            <td class="font-mono">{{ $dn->document_date ? $dn->document_date->format('d.m.Y') : '' }}</td>
                                            <td style="font-weight: 700;">{{ $dn->customer_name }}</td>
                                            <td class="font-mono">{{ $dn->customer_tax_id ?? 'N/A' }}</td>
                                            <td class="font-mono" style="color: #6366f1;">{{ $dn->order?->order_number ?? 'Manual' }}</td>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-primary);">{{ number_format($dn->total_amount, 0) }} ֏</td>
                                            <td>
                                                @if($dn->reprint_count > 0)
                                                    <span class="badge badge-amber"><i class="fa-solid fa-copy"></i> {{ $dn->reprint_count }} անգամ</span>
                                                @else
                                                    <span class="badge badge-slate">Բնօրինակ</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($dn->status === 'issued')
                                                    <span class="badge badge-green">Գործող</span>
                                                @else
                                                    <span class="badge badge-slate" style="text-decoration: line-through;">Չեղարկված</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display: flex; gap: 0.35rem; justify-content: flex-end;">
                                                    <button class="btn btn-sm btn-primary" onclick="ERP.deliveryNotes.viewAndPrint('{{ $dn->id }}')" title="Տպել / Նախադիտել A4">
                                                        <i class="fa-solid fa-print"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-secondary" onclick="ERP.deliveryNotes.reprintNote('{{ $dn->id }}')" title="Գրանցել Վերատպում">
                                                        <i class="fa-solid fa-arrows-rotate"></i>
                                                    </button>
                                                    @if($dn->status === 'issued')
                                                        <button class="btn btn-sm btn-danger" onclick="ERP.deliveryNotes.cancelNote('{{ $dn->id }}')" title="Չեղարկել">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                                <i class="fa-solid fa-file-signature" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.5rem; display: block;"></i>
                                                Բեռնագրեր չեն գտնվել: Ընտրեք պատվեր և ստեղծեք նոր բեռնագիր:
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: XML IMPORT CENTER (XML ՆԵՐՄՈՒԾՈՒՄ)
                     ============================================================== -->
                <section class="view-panel" id="view-xml-import" style="display: none;">
                    <div class="view-header">
                        <div>
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-heading); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-file-code" style="color: var(--color-primary);"></i>
                                <span>XML Ներմուծման Կենտրոն</span>
                            </h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem;">
                                Պատվերների և էլեկտրոնային հաշիվների ապահով ներմուծում (XXE պաշտպանություն, չոր փորձարկում և աուդիտ)
                            </p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <!-- Upload Card -->
                        <div class="card" style="padding: 1.5rem;">
                            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-heading);">
                                <i class="fa-solid fa-cloud-arrow-up" style="color: var(--color-primary); margin-right: 0.5rem;"></i>
                                Բեռնել XML Ֆայլ
                            </h3>

                            <div style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 1.75rem; text-align: center; background: #f8fafc; margin-bottom: 1rem; cursor: pointer;" onclick="document.getElementById('xml-file-input').click()">
                                <i class="fa-solid fa-file-code" style="font-size: 2.2rem; color: var(--color-primary); margin-bottom: 0.5rem;"></i>
                                <div style="font-weight: 700; color: var(--text-heading);" id="xml-file-label">Ընտրեք կամ քաշեք XML ֆայլը այստեղ</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Աջակցվող ձևաչափեր՝ ERPlannet XML, e-Invoicing (Հարկային), Ընդհանուր XML (մինչև 10MB)</div>
                                <input type="file" id="xml-file-input" accept=".xml,text/xml" style="display: none;" onchange="ERP.xmlImport.onFileSelected(this)">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                                <div>
                                    <label class="label">Մասնաճյուղ</label>
                                    <select class="input" id="xml-branch-select">
                                        @foreach($branches as $br)
                                            <option value="{{ $br->id }}">{{ $br->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="label">Պահեստ</label>
                                    <select class="input" id="xml-warehouse-select">
                                        <option value="">Համակարգային լռելյայն</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <button class="btn btn-primary" style="width: 100%;" id="xml-preview-btn" onclick="ERP.xmlImport.previewFile()">
                                <i class="fa-solid fa-magnifying-glass-chart"></i> <span>Նախադիտել &amp; Ստուգել (Dry Run)</span>
                            </button>
                        </div>

                        <!-- Live Validation & Preview Container -->
                        <div class="card" style="padding: 1.5rem;" id="xml-preview-card">
                            <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-heading);">
                                <i class="fa-solid fa-shield-halved" style="color: #16a34a; margin-right: 0.5rem;"></i>
                                Ստուգման Արդյունքներ
                            </h3>

                            <div id="xml-preview-placeholder" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                                <i class="fa-solid fa-clipboard-check" style="font-size: 2.2rem; color: #cbd5e1; margin-bottom: 0.5rem; display: block;"></i>
                                Բեռնեք XML ֆայլը և սեղմեք «Նախադիտել»՝ ստուգման և արտապատկերման համար:
                            </div>

                            <div id="xml-preview-content" style="display: none;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                                    <div>
                                        <span class="badge badge-blue" id="xml-format-badge">ERPLANNET XML</span>
                                        <span class="badge badge-slate" id="xml-doctype-badge">Customer Order</span>
                                    </div>
                                    <div class="font-mono" style="font-size: 0.75rem; color: var(--text-muted);" id="xml-checksum-preview"></div>
                                </div>

                                <div id="xml-errors-container" style="margin-bottom: 1rem; display: none;"></div>

                                <div style="max-height: 220px; overflow-y: auto; margin-bottom: 1rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                                    <table class="table" style="font-size: 0.8rem;">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Ապրանք</th>
                                                <th>Քանակ</th>
                                                <th>Գին</th>
                                                <th>Ընդամենը</th>
                                            </tr>
                                        </thead>
                                        <tbody id="xml-preview-items-body"></tbody>
                                    </table>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 6px; margin-bottom: 1rem;">
                                    <span style="font-weight: 700;">Հաշվարկված Ընդհանուր Գումար:</span>
                                    <span class="font-mono" style="font-size: 1.2rem; font-weight: 800; color: var(--color-primary);" id="xml-preview-total">0 ֏</span>
                                </div>

                                <button class="btn btn-primary" style="width: 100%; background: #16a34a;" id="xml-confirm-btn" onclick="ERP.xmlImport.confirmImport()">
                                    <i class="fa-solid fa-check"></i> <span>Հաստատել &amp; Գրանցել Պատվերը</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Import History Card -->
                    <div class="card" style="padding: 1.25rem;">
                        <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-heading);">
                            <i class="fa-solid fa-clock-rotate-left" style="color: var(--color-primary); margin-right: 0.5rem;"></i>
                            Ներմուծումների Պատմություն
                        </h3>
                        <div class="table-responsive">
                            <table class="table" id="xml-history-table">
                                <thead>
                                    <tr>
                                        <th>Ամսաթիվ</th>
                                        <th>Ֆայլի Անուն</th>
                                        <th>Ձևաչափ</th>
                                        <th>Մասնաճյուղ</th>
                                        <th>Տողեր</th>
                                        <th>Կարգավիճակ</th>
                                        <th>Արդյունք</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($xmlImports as $xi)
                                        <tr>
                                            <td class="font-mono" style="font-size: 0.8rem;">{{ $xi->created_at ? $xi->created_at->format('d.m.Y H:i') : '' }}</td>
                                            <td style="font-weight: 700;">{{ $xi->file_name }}</td>
                                            <td><span class="badge badge-slate font-mono">{{ $xi->format_detected }}</span></td>
                                            <td>{{ $xi->branch?->name ?? 'Branch' }}</td>
                                            <td class="font-mono">{{ $xi->successful_records }} / {{ $xi->total_records }}</td>
                                            <td>
                                                @if($xi->status === 'imported')
                                                    <span class="badge badge-green"><i class="fa-solid fa-check"></i> Ներմուծված</span>
                                                @elseif($xi->status === 'failed')
                                                    <span class="badge badge-rose"><i class="fa-solid fa-triangle-exclamation"></i> Սխալ</span>
                                                @else
                                                    <span class="badge badge-amber">{{ $xi->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($xi->created_orders_ids))
                                                    <span class="badge badge-blue font-mono">{{ count($xi->created_orders_ids) }} պատվեր</span>
                                                @else
                                                    <span style="color: var(--text-muted); font-size: 0.8rem;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                                Ներմուծումների պատմությունը դատարկ է:
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: PRINT MANAGEMENT (ՏՊՄԱՆ ԿԱՌԱՎԱՐՈՒՄ)
                     ============================================================== -->
                <section class="view-panel" id="view-print-management" style="display: none;">
                    <div class="view-header">
                        <div>
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-heading); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-print" style="color: var(--color-primary);"></i>
                                <span>Տպման Կառավարում &amp; Տպիչներ</span>
                            </h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem;">
                                Ջերմային POS կտրոնների (58mm, 80mm ESC/POS), գրասենյակային տպիչների և տպման հերթի կառավարում
                            </p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button class="btn btn-secondary" onclick="ERP.printing.load()">
                                <i class="fa-solid fa-rotate"></i> <span>Թարմացնել</span>
                            </button>
                            <button class="btn btn-primary" onclick="ERP.printing.openAddPrinterModal()">
                                <i class="fa-solid fa-plus"></i> <span>Ավելացնել Տպիչ</span>
                            </button>
                        </div>
                    </div>

                    <!-- Printer Cards Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem;" id="printers-grid">
                        @forelse($printers as $prn)
                            <div class="card" style="padding: 1.25rem; border-top: 4px solid var(--color-primary);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                    <div>
                                        <h4 style="font-weight: 800; color: var(--text-heading); font-size: 1.05rem;">{{ $prn->name }}</h4>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $prn->branch?->name ?? 'Default Branch' }}</div>
                                    </div>
                                    <div>
                                        @if($prn->is_default)
                                            <span class="badge badge-green"><i class="fa-solid fa-star"></i> Լռելյայն</span>
                                        @endif
                                        <span class="badge badge-slate font-mono">{{ $prn->paper_width }}</span>
                                    </div>
                                </div>

                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1rem;">
                                    <div><strong>Տեսակ:</strong> {{ ucfirst($prn->printer_type) }}</div>
                                    <div><strong>Միացում:</strong> {{ str_replace('_', ' ', strtoupper($prn->connection_type)) }}</div>
                                    @if($prn->ip_address)
                                        <div class="font-mono"><strong>IP/Port:</strong> {{ $prn->ip_address }}:{{ $prn->port }}</div>
                                    @endif
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                                    <span class="badge badge-teal"><i class="fa-solid fa-circle" style="font-size: 0.5rem; margin-right: 3px;"></i> Online</span>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <button class="btn btn-sm btn-secondary" onclick="ERP.printing.testPrint('{{ $prn->id }}')">
                                            <i class="fa-solid fa-vial"></i> Փորձարկել
                                        </button>
                                        <button class="btn btn-sm btn-outline" onclick="ERP.printing.deletePrinter('{{ $prn->id }}')" title="Հեռացնել">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card" style="padding: 2.5rem; text-align: center; grid-column: 1 / -1; color: var(--text-muted);">
                                <i class="fa-solid fa-print" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 0.5rem; display: block;"></i>
                                Գրանցված տպիչներ չկան: Սեղմեք «Ավելացնել Տպիչ»՝ POS կամ ցանցային տպիչ կցելու համար:
                            </div>
                        @endforelse
                    </div>

                    <!-- Print Jobs Queue Monitor -->
                    <div class="card" style="padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-heading);">
                                <i class="fa-solid fa-list-check" style="color: var(--color-primary); margin-right: 0.5rem;"></i>
                                Տպման Առաջադրանքների Հերթ (Print Jobs Monitor)
                            </h3>
                            <span class="badge badge-slate" id="print-jobs-count-badge">{{ count($printJobs) }} առաջադրանք</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="print-jobs-table">
                                <thead>
                                    <tr>
                                        <th>Job ID</th>
                                        <th>Ժամանակ</th>
                                        <th>Փաստաթուղթ</th>
                                        <th>Տպիչ</th>
                                        <th>Պատվերի #</th>
                                        <th>Կրկնօրինակ</th>
                                        <th>Կարգավիճակ</th>
                                        <th style="text-align: right;">Գործողություն</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($printJobs as $pj)
                                        <tr>
                                            <td class="font-mono" style="font-size: 0.75rem;">{{ substr($pj->id, 0, 8) }}...</td>
                                            <td style="font-size: 0.8rem;">{{ $pj->created_at ? $pj->created_at->format('d.m.Y H:i') : '' }}</td>
                                            <td><span class="badge badge-slate">{{ str_replace('_', ' ', strtoupper($pj->document_type)) }}</span></td>
                                            <td>{{ $pj->printer?->name ?? 'Browser Print' }}</td>
                                            <td class="font-mono" style="color: var(--color-primary);">{{ $pj->order?->order_number ?? '—' }}</td>
                                            <td>
                                                @if($pj->is_reprint)
                                                    <span class="badge badge-amber"><i class="fa-solid fa-rotate"></i> Reprint #{{ $pj->reprint_count }}</span>
                                                @else
                                                    <span class="badge badge-slate">{{ $pj->copies }} օրինակ</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pj->status === 'completed')
                                                    <span class="badge badge-green"><i class="fa-solid fa-check"></i> Completed</span>
                                                @elseif($pj->status === 'failed')
                                                    <span class="badge badge-rose"><i class="fa-solid fa-triangle-exclamation"></i> Failed</span>
                                                @else
                                                    <span class="badge badge-blue">{{ $pj->status }}</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                <button class="btn btn-sm btn-secondary" onclick="ERP.printing.reprintJob('{{ $pj->id }}')">
                                                    <i class="fa-solid fa-arrows-rotate"></i> Վերատպել
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                                Տպման առաջադրանքների հերթը դատարկ է:
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ==============================================================
                     VIEW: VISUAL RECEIPT & DOCUMENT DESIGNER (ԴԻԶԱՅՆԵՐ)
                     ============================================================== -->
                <section class="view-panel" id="view-document-designer" style="display: none;">
                    <div class="view-header">
                        <div>
                            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-heading); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-palette" style="color: var(--color-primary);"></i>
                                <span>Կտրոնների &amp; Փաստաթղթերի Դիզայներ</span>
                            </h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem;">
                                Ձևանմուշների վիզուալ խմբագրիչ՝ իրական նախադիտմամբ (58mm, 80mm, A4, A5, հայերեն յունիկոդ)
                            </p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button class="btn btn-secondary" onclick="ERP.designer.duplicateTemplate()">
                                <i class="fa-solid fa-copy"></i> <span>Կրկնօրինակել</span>
                            </button>
                            <button class="btn btn-secondary" onclick="ERP.designer.saveDraft()">
                                <i class="fa-solid fa-floppy-disk"></i> <span>Պահպանել</span>
                            </button>
                            <button class="btn btn-primary" onclick="ERP.designer.publishVersion()">
                                <i class="fa-solid fa-upload"></i> <span>Հրապարակել</span>
                            </button>
                        </div>
                    </div>

                    <!-- Designer Top Toolbar -->
                    <div class="card" style="padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <label style="font-size: 0.85rem; font-weight: 700; color: var(--text-heading);">Ձևանմուշ:</label>
                            <select class="input" id="designer-template-select" style="min-width: 240px;" onchange="ERP.designer.selectTemplate(this.value)">
                                @foreach($documentTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->paper_size }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Paper Size Selector -->
                        <div style="display: flex; gap: 0.35rem; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-heading); margin-right: 0.35rem;">Թղթի չափ:</span>
                            <button class="btn btn-sm btn-secondary active" id="btn-size-80mm" onclick="ERP.designer.changePaperSize('80mm')">80mm</button>
                            <button class="btn btn-sm btn-secondary" id="btn-size-58mm" onclick="ERP.designer.changePaperSize('58mm')">58mm</button>
                            <button class="btn btn-sm btn-secondary" id="btn-size-a4" onclick="ERP.designer.changePaperSize('a4')">A4</button>
                            <button class="btn btn-sm btn-secondary" id="btn-size-a5" onclick="ERP.designer.changePaperSize('a5')">A5</button>
                        </div>

                        <div>
                            <button class="btn btn-sm btn-outline" onclick="ERP.designer.openPlaceholdersModal()">
                                <i class="fa-solid fa-code"></i> Փոխարինիչներ (Placeholders)
                            </button>
                        </div>
                    </div>

                    <!-- 2-Column Visual Designer Workspace -->
                    <div style="display: grid; grid-template-columns: 380px 1fr; gap: 1.5rem; align-items: flex-start;">
                        <!-- Controls Sidebar -->
                        <div class="card" style="padding: 1.25rem; max-height: 750px; overflow-y: auto;">
                            <h4 style="font-weight: 800; font-size: 0.95rem; margin-bottom: 1rem; color: var(--text-heading);">
                                <i class="fa-solid fa-sliders" style="color: var(--color-primary); margin-right: 0.35rem;"></i>
                                Փաստաթղթի Պարամետրեր
                            </h4>

                            <!-- Header Section -->
                            <div style="margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                                <label class="label">Վերնագիր / Title</label>
                                <input type="text" class="input" id="designer-title-input" value="ՎԱՃԱՌՔԻ ԿՏՐՈՆ / RECEIPT" oninput="ERP.designer.onConfigChange()">

                                <label class="label" style="margin-top: 0.5rem;">Ենթավերնագիր</label>
                                <input type="text" class="input" id="designer-subtitle-input" value="@{{ organization.name }}" oninput="ERP.designer.onConfigChange()">

                                <div style="margin-top: 0.5rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" id="designer-show-logo" onchange="ERP.designer.onConfigChange()">
                                        <span>Ցուցադրել Ընկերության Լոգոն</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Sections Toggles -->
                            <div style="margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                                <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-heading); margin-bottom: 0.5rem;">Բաժինների Տեսանելիություն</div>
                                <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.82rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="designer-sec-branch" checked onchange="ERP.designer.onConfigChange()">
                                        <span>Մասնաճյուղի տվյալներ (Branch info)</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="designer-sec-customer" checked onchange="ERP.designer.onConfigChange()">
                                        <span>Հաճախորդի տվյալներ &amp; ՀՎՀՀ</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="designer-sec-items" checked onchange="ERP.designer.onConfigChange()">
                                        <span>Ապրանքների ցանկ (Items table)</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="designer-sec-totals" checked onchange="ERP.designer.onConfigChange()">
                                        <span>Հանրագումար &amp; Զեղչեր (Totals)</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="designer-sec-payments" checked onchange="ERP.designer.onConfigChange()">
                                        <span>Վճարման եղանակ (Payments)</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" id="designer-sec-signatures" onchange="ERP.designer.onConfigChange()">
                                        <span>Ստորագրությունների դաշտ (Signatures)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Typography & Footer -->
                            <div>
                                <label class="label">Տառատեսակի չափ</label>
                                <select class="input" id="designer-font-size" onchange="ERP.designer.onConfigChange()">
                                    <option value="11px">Կոմպակտ (11px)</option>
                                    <option value="12px" selected>Սովորական (12px)</option>
                                    <option value="14px">Խոշոր (14px)</option>
                                </select>

                                <label class="label" style="margin-top: 0.5rem;">Ստորոտի տեքստ (Footer)</label>
                                <textarea class="input" id="designer-footer-text" rows="2" oninput="ERP.designer.onConfigChange()">Շնորհակալություն գնումների համար: Ապրանքները ենթակա են վերադարձի 14 օրում:</textarea>
                            </div>
                        </div>

                        <!-- Live WYSIWYG Preview Paper -->
                        <div class="card" style="padding: 1.5rem; min-height: 600px; display: flex; flex-direction: column; align-items: center; background: #e2e8f0;">
                            <div style="font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-eye" style="margin-right: 0.35rem;"></i> Իրական Տպման Նախադիտում
                            </div>

                            <div id="designer-preview-wrapper" style="background: #ffffff; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); border-radius: 4px; overflow: hidden; width: 80mm; min-height: 480px; transition: width 0.2s ease;">
                                <iframe id="designer-preview-frame" style="width: 100%; height: 580px; border: none; display: block;" src="about:blank"></iframe>
                            </div>
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
                                                {{ is_array($b->product->name) ? ($b->product->name['hy'] ?? \Illuminate\Support\Arr::first($b->product->name)) : $b->product->name }}
                                            </td>
                                            <td class="font-mono">{{ $b->warehouse?->code }}</td>
                                            <td class="font-mono" style="font-weight: 800;">{{ number_format($b->quantity_on_hand ?? 0, 2) }} kg</td>
                                            <td class="font-mono">{{ number_format($b->cost_price ?? 0, 0) }} ֏</td>
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
                                    <span class="badge badge-amber font-mono">Yield: {{ number_format($rcp->yield_quantity ?? 0, 0) }} {{ $rcp->yieldUnit?->code ?? 'units' }}</span>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                    Labor / Overhead: <strong style="color: var(--text-heading); font-family: var(--font-mono);">{{ number_format($rcp->labor_cost ?? 0, 0) }} / {{ number_format($rcp->overhead_cost ?? 0, 0) }} ֏</strong>
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
                                            <td class="font-mono">{{ number_format($po->planned_quantity ?? 0, 0) }} / {{ number_format($po->actual_quantity ?? 0, 0) }} pcs</td>
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

    <!-- Modal 5: Comprehensive Product / Item Master (7 Tabs with Hints) -->
    <div class="modal-backdrop" id="create-product-modal">
        <div class="modal-container" style="max-width: 860px; max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <div>
                    <h3 id="product-modal-title"><i class="fa-solid fa-boxes-stacked" style="color: var(--color-primary); margin-right: 6px;"></i> Ապրանքի Քարտ (Product / Item Master)</h3>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Պարամետրեր, գնագոյացում, մոդիֆիկատորներ, ստոպ-լիստ, ալերգեններ և հասանելիություն:</p>
                </div>
                <button onclick="ERP.catalog.closeCreateProductModal()" style="font-size: 1.25rem; color: var(--text-muted); background: none; border: none; cursor: pointer;">&times;</button>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="product-modal-tabs" style="padding: 0 1.5rem; background: #F8FAFC;">
                <button type="button" class="product-tab-btn active" data-tab="tab-prod-general" onclick="ERP.catalog.switchModalTab('tab-prod-general')">
                    <i class="fa-solid fa-info-circle"></i> Հիմնական
                </button>
                <button type="button" class="product-tab-btn" data-tab="tab-prod-codes" onclick="ERP.catalog.switchModalTab('tab-prod-codes')">
                    <i class="fa-solid fa-barcode"></i> Կոդեր &amp; ԻԴ
                </button>
                <button type="button" class="product-tab-btn" data-tab="tab-prod-pricing" onclick="ERP.catalog.switchModalTab('tab-prod-pricing')">
                    <i class="fa-solid fa-coins"></i> Գներ &amp; ԱԱՀ
                </button>
                <button type="button" class="product-tab-btn" data-tab="tab-prod-flags" onclick="ERP.catalog.switchModalTab('tab-prod-flags')">
                    <i class="fa-solid fa-sliders"></i> Մոդիֆիկատոր &amp; Պահեստ
                </button>
                <button type="button" class="product-tab-btn" data-tab="tab-prod-variants" onclick="ERP.catalog.switchModalTab('tab-prod-variants')">
                    <i class="fa-solid fa-layer-group"></i> Վարիացիաներ
                </button>
                <button type="button" class="product-tab-btn" data-tab="tab-prod-nutrition" onclick="ERP.catalog.switchModalTab('tab-prod-nutrition')">
                    <i class="fa-solid fa-apple-whole"></i> Ալերգեններ &amp; Դիետա
                </button>
                <button type="button" class="product-tab-btn" data-tab="tab-prod-availability" onclick="ERP.catalog.switchModalTab('tab-prod-availability')">
                    <i class="fa-solid fa-clock"></i> Հասանելիություն
                </button>
            </div>

            <form id="product-master-form" onsubmit="ERP.catalog.submitProduct(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                <input type="hidden" id="prod-edit-id" value="">

                <div class="modal-body" style="overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1;">
                    <!-- TAB 1: General Info -->
                    <div class="product-tab-pane" id="tab-prod-general">
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Անվանում (Հայերեն) *</label>
                                <input type="text" class="form-control" id="prod-name-hy" required placeholder="օր․ Լոլիկով և Պանրով Պիցցա, Մատնաքաշ, Խմոր Կիսաֆաբրիկատ">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Պրոդուկտի պաշտոնական անվանումը հայերենով: Ցուցադրվում է POS-ում, չեկերում և մենյուում:</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ապրանքի Տեսակ (Type) *</label>
                                <select class="select" id="prod-type" required onchange="ERP.catalog.onTypeChange()">
                                    <option value="finished_product">Finished Product (Պատրաստի արտադրանք)</option>
                                    <option value="semi_finished">Semi-Finished (Կիսաֆաբրիկատ)</option>
                                    <option value="ingredient">Ingredient (Բաղադրիչ / Հումք)</option>
                                    <option value="raw_material">Raw Material (Հումք)</option>
                                    <option value="packaging">Packaging (Փաթեթավորում)</option>
                                    <option value="service">Service (Ծառայություն)</option>
                                    <option value="modifier">Modifier (Մոդիֆիկատոր)</option>
                                </select>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Պրոդուկտի դերը արտադրության և առևտրի շղթայում: Կիսաֆաբրիկատները կարող են մտնել այլ ապրանքների բաղադրատոմսի մեջ:</div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Name (English)</label>
                                <input type="text" class="form-control" id="prod-name-en" placeholder="e.g. Tomato & Cheese Pizza">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Անգլերեն անվանում միջազգային հաճախորդների և QR Menu-ի համար:</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Название (Русский)</label>
                                <input type="text" class="form-control" id="prod-name-ru" placeholder="напр. Пицца с томатами и сыром">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Ռուսերեն անվանում մենյուի և հաշվետվությունների համար:</div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div class="form-group">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <label class="form-label" style="margin-bottom: 0;">Կատեգորիա</label>
                                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="ERP.directory.categories.openCreateModal(null, 'product')" style="font-size: 0.7rem; padding: 2px 6px;">
                                        <i class="fa-solid fa-plus"></i> Նոր Խումբ
                                    </button>
                                </div>
                                <select class="select" id="prod-category-id" onchange="ERP.catalog.onCategoryChange(this.value)">
                                    <option value="">-- Առանց կատեգորիայի --</option>
                                    @foreach($productCategories ?? $categories as $cat)
                                        <option value="{{ $cat->id }}">{{ is_array($cat->name) ? ($cat->name['hy'] ?? \Illuminate\Support\Arr::first($cat->name)) : $cat->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Հիմնական ապրանքային խումբը (օր․ Հացաբուլկեղեն, Տաք ուտեստներ, Խմիչքներ):</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ենթակատեգորիա (Ենթախումբ)</label>
                                <select class="select" id="prod-subcategory-id">
                                    <option value="">-- Առանց ենթախմբի --</option>
                                    @foreach($productCategories ?? $categories as $cat)
                                        <option value="{{ $cat->id }}">{{ is_array($cat->name) ? ($cat->name['hy'] ?? \Illuminate\Support\Arr::first($cat->name)) : $cat->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Ենթախումբ ավելի նեղ դասակարգման և զտման համար:</div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Չափման Միավոր (Unit) *</label>
                                <select class="select" id="prod-unit-id" required>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ is_array($u->name) ? ($u->name['hy'] ?? \Illuminate\Support\Arr::first($u->name)) : $u->name }} ({{ $u->code }})</option>
                                    @endforeach
                                </select>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Պահեստային հաշվառման հիմնական միավորը (հատ, կգ, գրամ, լիտր):</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Քանակ չափման միավոր</label>
                                <input type="text" class="form-control" id="prod-net-quantity" placeholder="օր․ 450 գրամ, 0.5 լ, 1 հատ">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Մեկ միավորի կամ պորցիայի զուտ ծավալ/քաշ (Net quantity):</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Տարա / Փաթեթավորում</label>
                                <input type="text" class="form-control" id="prod-packaging" placeholder="օր․ Տուփ, Շիշ, Պարկ, Առանց տարայի">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Տարայի կամ փաթեթավորման տեսակը:</div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label class="form-label">Ապրանքի Պատկեր (Image)</label>
                            <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                                <div id="prod-image-preview-box" style="width: 76px; height: 76px; border-radius: 8px; border: 2px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                                    <img id="prod-image-preview" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                    <i id="prod-image-placeholder-icon" class="fa-solid fa-cloud-arrow-up" style="color: #94A3B8; font-size: 1.5rem;"></i>
                                    <button type="button" id="prod-image-remove-btn" onclick="ERP.media.clearProductImage()" style="display: none; position: absolute; top: 3px; right: 3px; background: rgba(220, 38, 38, 0.9); color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 11px; align-items: center; justify-content: center;">&times;</button>
                                </div>
                                <div style="flex: 1;">
                                    <div style="display: flex; gap: 0.5rem; margin-bottom: 6px;">
                                        <input type="text" class="form-control" id="prod-image-url" placeholder="https://... կամ վերբեռնեք ֆայլը" oninput="ERP.media.onProductUrlChange(this.value)">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('prod-image-file').click()" style="white-space: nowrap;">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i> Վերբեռնել
                                        </button>
                                        <input type="file" id="prod-image-file" accept="image/*" style="display: none;" onchange="ERP.media.handleFileUpload(this, 'products', 'prod-image-url', 'prod-image-preview', 'prod-image-preview-box', 'prod-image-remove-btn', 'prod-image-placeholder-icon')">
                                    </div>
                                    <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Կարող եք ընտրել պատկեր համակարգչից (PNG, JPG, WebP մինչև 10MB) կամ տեղադրել արտաքին URL:</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Նկարագրություն</label>
                            <textarea class="form-control" id="prod-description" rows="2" placeholder="Ապրանքի բաղադրություն, պատրաստման կամ մատուցման առանձնահատկություններ..."></textarea>
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Մանրամասն նկարագրություն հաճախորդների և անձնակազմի համար:</div>
                        </div>
                    </div>

                    <!-- TAB 2: Codes & Identifiers -->
                    <div class="product-tab-pane" id="tab-prod-codes" style="display: none;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Համակարգային ID (UUID)</label>
                                <input type="text" class="form-control font-mono" id="prod-id-display" readonly style="background: #F1F5F9; color: #64748B;">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Համակարգի եզակի ներքին իդենտիֆիկատոր: Գեներացվում է ավտոմատ:</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Արտիկուլ / SKU *</label>
                                <div style="display: flex; gap: 4px;">
                                    <input type="text" class="form-control font-mono" id="prod-sku" required placeholder="օր․ PRD-PIZZA-01">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.catalog.generateSku()" title="Գեներացնել SKU">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    </button>
                                </div>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Stock Keeping Unit: Եզակի ապրանքային կոդ հաշվառման համար:</div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Շտրիխկոդ (EAN-13 / Barcode)</label>
                                <input type="text" class="form-control font-mono" id="prod-barcode" placeholder="4850001234567">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> 13-նիշ EAN կամ ներքին շտրիխ կոդ՝ սկաներով POS-ում ակնթարթային ճանաչման համար:</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">ԱՏԳ ԱԱ Ծածկագիր (HS Code / FEACN)</label>
                                <input type="text" class="form-control font-mono" id="prod-hs-code" placeholder="1905 90 900 0">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> ԵԱՏՄ ԱՏԳ ԱԱ ծածկագիր հարկային էլեկտրոնային հաշիվ-ապրանքագրերի և մաքսային ձևակերպման համար:</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Pricing, VAT & Discounts -->
                    <div class="product-tab-pane" id="tab-prod-pricing" style="display: none;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Վաճառքի Գին (Sale Price ֏) *</label>
                                <input type="number" step="any" class="form-control font-mono" id="prod-sale-price" required value="0" placeholder="0">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Հիմնական վաճառքի մանրածախ գինը դրամով (ներառյալ հարկերը):</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Զեղչված Արժեք (Special / Promo ֏)</label>
                                <input type="number" step="any" class="form-control font-mono" id="prod-special-price" placeholder="օր․ 2200">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Ակցիոն կամ ժամանակավոր իջեցված գին: Եթե լրացված է, վաճառվում է այս գնով:</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ինքնարժեք (Cost Price ֏)</label>
                                <input type="number" step="any" class="form-control font-mono" id="prod-cost-price" value="0" placeholder="0">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Փաստացի կամ տեխնիկական քարտից (BOM) հաշվարկված միջին կշռված ինքնարժեքը:</div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem; background: #F8FAFC; padding: 0.85rem; border-radius: 8px; border: 1px solid #E2E8F0;">
                            <div class="form-group">
                                <label class="form-label">ԱԱՀ Կարգավիճակ</label>
                                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 6px;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.82rem; cursor: pointer;">
                                        <input type="radio" name="prod_vat_status" id="prod-vat-yes" value="1" checked onchange="ERP.catalog.onVatToggle()">
                                        <span>ԱԱՀ-ով (Հարկվող)</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.82rem; cursor: pointer;">
                                        <input type="radio" name="prod_vat_status" id="prod-vat-no" value="0" onchange="ERP.catalog.onVatToggle()">
                                        <span style="color: #059669; font-weight: 700;">Առանց ԱԱՀ (Ազատված)</span>
                                    </label>
                                </div>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Նշեք «Առանց ԱԱՀ», եթե ապրանքն ազատված է ԱԱՀ-ից կամ գործում է հատուկ հարկային ռեժիմ:</div>
                            </div>
                            <div class="form-group" id="prod-vat-rate-group">
                                <label class="form-label">ԱԱՀ Դրույքաչափ (%)</label>
                                <input type="number" step="any" class="form-control font-mono" id="prod-vat-rate" value="20" placeholder="20">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Օրենսդրությամբ սահմանված ԱԱՀ դրույքաչափը (ստանդարտ 20% կամ 0%):</div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <div class="card" style="padding: 0.75rem; background: #FFFFFF;">
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                    <input type="checkbox" id="prod-allow-discount" checked>
                                    <span>Զեղչի հնարավորություն (Այո/Ոչ)</span>
                                </label>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Թույլատրե՞լ գանձապահին կամ ավտոմատ ակցիաներին զեղչ կիրառել այս ապրանքի վրա: Եթե «Ոչ», ապրանքը միշտ վաճառվում է առանց զեղչի:</div>
                            </div>
                            <div class="card" style="padding: 0.75rem; background: #FFFFFF;">
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                    <input type="checkbox" id="prod-allow-price-edit">
                                    <span>Գնի փոփոխության հնարավորություն (Այո/Ոչ)</span>
                                </label>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Թույլատրե՞լ գանձապահին POS-ում ազատ խմբագրել վաճառքի միավոր գինը (Open Price): Օգտակար է կշռով կամ փոփոխական ծառայությունների համար:</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Modifiers, Stock & Regulatory Flags -->
                    <div class="product-tab-pane" id="tab-prod-flags" style="display: none;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <!-- Modifiers Switch & Ungroup -->
                            <div class="card" style="padding: 0.85rem; border-left: 3px solid #F59E0B;">
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 0.85rem; color: #B45309; cursor: pointer;">
                                    <input type="checkbox" id="prod-allow-modifiers" onchange="ERP.catalog.onModifierToggle()">
                                    <span>Модификаторы (Մոդիֆիկատորներ)</span>
                                </label>
                                <div class="form-hint" style="margin-top: 4px;">
                                    <i class="fa-solid fa-circle-info"></i> Позволяет добавлять ингредиенты (модификаторы) в товар при создании заказа: Եթե ակտիվ է, այս ապրանքը <strong>չի խմբավորվում պատվերում</strong>:
                                </div>
                                <div style="margin-top: 8px; padding-top: 6px; border-top: 1px dashed #E2E8F0;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <input type="checkbox" id="prod-is-ungrouped">
                                        <span>Չխմբավորել պատվերում (Не группируется в заказе)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Stop List Switch -->
                            <div class="card" style="padding: 0.85rem; border-left: 3px solid #DC2626;">
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 0.85rem; color: #DC2626; cursor: pointer;">
                                    <input type="checkbox" id="prod-is-stop-list">
                                    <span>Стоп-лист (Ստոպ-ցուցակ)</span>
                                </label>
                                <div class="form-hint" style="margin-top: 4px;">
                                    <i class="fa-solid fa-circle-info"></i> Ապրանքը կասեցվում է վաճառքից (օր․ բաղադրիչը սպառվել է): POS-ում և QR մենյուում այն դառնում է անհասանելի:
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <!-- Excise Goods -->
                            <div class="card" style="padding: 0.85rem;">
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                    <input type="checkbox" id="prod-is-excise">
                                    <span>Подакцизный товар (Ակցիզային ապրանք)</span>
                                </label>
                                <div class="form-hint">
                                    <i class="fa-solid fa-circle-info"></i> Նշվում է, եթե ապրանքը ենթակա է ակցիզային հարկման (օր․ ալկոհոլ, ծխախոտային արտադրանք): Փոխանցվում է ՀԴՄ և հարկային հաշիվ:
                                </div>
                            </div>

                            <!-- Marked Goods DataMatrix -->
                            <div class="card" style="padding: 0.85rem;">
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                    <input type="checkbox" id="prod-is-marked">
                                    <span>Маркированный товар / Դատամատրիքս</span>
                                </label>
                                <div class="form-hint">
                                    <i class="fa-solid fa-circle-info"></i> Պարտադիր մակնշման ենթակա ապրանք: Վաճառքի ժամանակ պահանջվում է DataMatrix 2D կոդի սկանավորում:
                                </div>
                            </div>
                        </div>

                        <!-- Track Stock & Min threshold -->
                        <div style="background: #F8FAFC; padding: 0.85rem; border-radius: 8px; border: 1px solid #E2E8F0;">
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; margin-bottom: 6px;">
                                <input type="checkbox" id="prod-track-stock" checked onchange="ERP.catalog.onTrackStockToggle()">
                                <span>Имеет остаток на складе (Վարել պահեստային հաշվառում)</span>
                            </label>
                            <div class="form-hint" style="margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-circle-info"></i> Եթե ակտիվ է, ապրանքի յուրաքանչյուր մուտք, վաճառք կամ արտադրություն փոխում է պահեստի ֆիզիկական մնացորդը:
                            </div>

                            <div id="prod-stock-fields" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Նվազագույն Քանակ (Min Stock Threshold)</label>
                                    <input type="number" step="any" class="form-control font-mono" id="prod-min-stock" value="0" placeholder="0">
                                    <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Պահեստում նվազագույն քանակ, որի դեպքում ցուցադրվում է կարմիր «Սակավ մնացորդ» ահազանգը:</div>
                                </div>
                                <div class="form-group" id="prod-initial-stock-group">
                                    <label class="form-label">Նախնական Մնացորդ (միայն նոր ստեղծելիս)</label>
                                    <input type="number" step="any" class="form-control font-mono" id="prod-initial-stock" value="0" placeholder="0">
                                    <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Գլխավոր պահեստում փաստացի առկա մնացորդի քանակը:</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: Variations -->
                    <div class="product-tab-pane" id="tab-prod-variants" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <div>
                                <label class="form-label" style="margin: 0; font-size: 0.88rem; font-weight: 700;">Ապրանքի Տարբերակներ / Վարիացիաներ</label>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Չափսեր (S, M, L), տարողություն (0.5լ, 1լ), քաշ կամ գույներ՝ յուրաքանչյուրն իր առանձին SKU-ով և գնով:</div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-xs" onclick="ERP.catalog.addVariantRow()">
                                <i class="fa-solid fa-plus"></i> Ավելացնել Վարիացիա
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table" style="font-size: 0.78rem;">
                                <thead>
                                    <tr>
                                        <th>Տարբերակ (Անվանում)</th>
                                        <th>SKU</th>
                                        <th>Շտրիխկոդ</th>
                                        <th>Վաճառքի Գին ֏</th>
                                        <th>Ինքնարժեք ֏</th>
                                        <th style="width: 30px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="prod-variants-tbody">
                                    <!-- Dynamic Variant Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 6: Nutrition, Allergens & Dietary -->
                    <div class="product-tab-pane" id="tab-prod-nutrition" style="display: none;">
                        <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Կալորիականություն (Calories kcal)</label>
                                <input type="number" step="any" class="form-control font-mono" id="prod-calories" placeholder="օր․ 245">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Էներգետիկ արժեքը (կկալ) 100գ-ի կամ 1 պորցիայի համար:</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Սպիտակուցներ (Proteins գր)</label>
                                <input type="number" step="any" class="form-control font-mono" id="prod-protein" placeholder="0">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Սպիտակուցների քանակը գրամներով:</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ճարպեր (Fats գր)</label>
                                <input type="number" step="any" class="form-control font-mono" id="prod-fat" placeholder="0">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Ճարպերի քանակը գրամներով:</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ածխաջրեր (Carbs գր)</label>
                                <input type="number" step="any" class="form-control font-mono" id="prod-carbs" placeholder="0">
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Ածխաջրերի քանակը գրամներով:</div>
                            </div>
                        </div>

                        <!-- 14 EU Allergens Tagging -->
                        <div class="card" style="padding: 0.85rem; margin-bottom: 1rem;">
                            <label class="form-label" style="font-weight: 800; color: #DC2626;"><i class="fa-solid fa-triangle-exclamation"></i> EU Allergens Tagging (ԵՄ 14 Պարտադիր Ալերգեններ)</label>
                            <div class="form-hint" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-info"></i> Նշեք բոլոր ալերգենները հաճախորդների անվտանգության և տեխ. քարտի մակնշման համար:</div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 6px;" id="prod-allergens-grid">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="gluten" class="allergen-chk"> Գլյուտեն (Gluten)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="milk" class="allergen-chk"> Կաթ / Լակտոզ (Milk)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="eggs" class="allergen-chk"> Ձու (Eggs)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="fish" class="allergen-chk"> Ձուկ (Fish)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="peanuts" class="allergen-chk"> Գետնանուշ (Peanuts)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="soybeans" class="allergen-chk"> Սոյա (Soybeans)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="nuts" class="allergen-chk"> Ընկույզներ (Nuts)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="celery" class="allergen-chk"> Նեխուր (Celery)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="mustard" class="allergen-chk"> Մանանեխ (Mustard)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="sesame" class="allergen-chk"> Քունջութ (Sesame)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="sulphites" class="allergen-chk"> Սուլֆիտներ (Sulphites)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="lupin" class="allergen-chk"> Լուպին (Lupin)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="molluscs" class="allergen-chk"> Փափկամարմիններ (Molluscs)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="crustaceans" class="allergen-chk"> Խեցգետնակերպեր</label>
                            </div>
                        </div>

                        <!-- Dietary Tags -->
                        <div class="card" style="padding: 0.85rem; margin-bottom: 1rem;">
                            <label class="form-label" style="font-weight: 800; color: #059669;"><i class="fa-solid fa-leaf"></i> Dietary Tags (Դիետիկ և Սննդակարգային նշումներ)</label>
                            <div class="form-hint" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-info"></i> Օգնում է հաճախորդներին արագ գտնել համապատասխան ուտեստները:</div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 6px;" id="prod-dietary-grid">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="vegetarian" class="dietary-chk"> Վեգետարիանական</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="vegan" class="dietary-chk"> Վեգան (Vegan)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="halal" class="dietary-chk"> Հալալ (Halal)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="kosher" class="dietary-chk"> Կոշեր (Kosher)</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="gluten_free" class="dietary-chk"> Առանց գլյուտենի</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="sugar_free" class="dietary-chk"> Առանց շաքարի</label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78rem; cursor: pointer;"><input type="checkbox" value="keto" class="dietary-chk"> Կետո (Keto)</label>
                            </div>
                        </div>

                        <!-- Shelf Life & Storage Conditions -->
                        <div class="form-group">
                            <label class="form-label">Պահպանման Ժամկետ &amp; Պայմաններ</label>
                            <input type="text" class="form-control" id="prod-shelf-life-info" placeholder="օր․ 72 ժամ, +2°C-ից +6°C ջերմաստիճանում">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Պահպանման պայմաններն ու ջերմաստիճանը ըստ սանիտարական և տեխնիկական պայմանների:</div>
                        </div>
                    </div>

                    <!-- TAB 7: Availability & Hours -->
                    <div class="product-tab-pane" id="tab-prod-availability" style="display: none;">
                        <!-- Branch Availability -->
                        <div class="card" style="padding: 0.85rem; margin-bottom: 1rem;">
                            <label class="form-label" style="font-weight: 800;"><i class="fa-solid fa-shop"></i> Մասնաճյուղերում Հասանելիություն</label>
                            <div class="form-hint" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-info"></i> Ընտրեք այն մասնաճյուղերը, որտեղ տվյալ ապրանքը հասանելի է վաճառքի համար: Եթե ոչինչ նշված չէ, հասանելի է բոլորում:</div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px;" id="prod-branches-grid">
                                @foreach($branches as $b)
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                                        <input type="checkbox" value="{{ $b->id }}" class="branch-avail-chk">
                                        <span>{{ $b->name }} ({{ $b->code }})</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Time Availability (Meals) -->
                        <div class="card" style="padding: 0.85rem; margin-bottom: 1rem;">
                            <label class="form-label" style="font-weight: 800;"><i class="fa-solid fa-clock"></i> Ժամային Հասանելիություն (Meal Times)</label>
                            <div class="form-hint" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-info"></i> Օրինակ՝ Նախաճաշի ուտեստները հասանելի են միայն 08:30 - 11:30 ժամերին:</div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Սկիզբ (Time From)</label>
                                    <input type="time" class="form-control" id="prod-avail-from">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Ավարտ (Time To)</label>
                                    <input type="time" class="form-control" id="prod-avail-to">
                                </div>
                            </div>
                        </div>

                        <!-- Happy Hours / Discount Hours -->
                        <div class="card" style="padding: 0.85rem;">
                            <label class="form-label" style="font-weight: 800; color: #D97706;"><i class="fa-solid fa-tag"></i> Զեղչի Ժամեր (Happy Hours)</label>
                            <div class="form-hint" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-info"></i> Օրինակ՝ Երեկոյան զեղչ թարմ թխվածքի համար 19:00 - 22:00:</div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Զեղչի Սկիզբ</label>
                                    <input type="time" class="form-control" id="prod-discount-from">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Զեղչի Ավարտ</label>
                                    <input type="time" class="form-control" id="prod-discount-to">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn btn-secondary" onclick="ERP.catalog.closeCreateProductModal()">Չեղարկել</button>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-secondary btn-sm" id="btn-modal-tech-card" onclick="ERP.catalog.openTechCardFromEdit()" style="display: none;">
                            <i class="fa-solid fa-scroll"></i> Տեխնիկական Քարտ
                        </button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Պահպանել Ապրանքը</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Technical Card / BOM (Բաղադրություն, Տեխնիկական քարտ) -->
    <div class="modal-backdrop" id="product-technical-card-modal">
        <div class="modal-container" style="max-width: 950px; max-height: 92vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <div>
                    <h3 id="tc-product-title"><i class="fa-solid fa-scroll" style="color: #059669; margin-right: 6px;"></i> Բաղադրություն &amp; Տեխնիկական Քարտ (BOM)</h3>
                    <p id="tc-product-subtitle" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Բաղադրիչների և կիսաֆաբրիկատների նորմաներ, կորուստ %, միջին կշռված ինքնարժեք:</p>
                </div>
                <button onclick="ERP.catalog.closeTechnicalCardModal()" style="font-size: 1.25rem; color: var(--text-muted); background: none; border: none; cursor: pointer;">&times;</button>
            </div>

            <form id="tech-card-form" onsubmit="ERP.catalog.submitTechnicalCard(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                <input type="hidden" id="tc-product-id" value="">

                <div class="modal-body" style="overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1;">
                    <!-- Recipe Master Parameters -->
                    <div style="display: grid; grid-template-columns: 1.2fr 1.5fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; background: #F8FAFC; padding: 0.85rem; border-radius: 8px; border: 1px solid #E2E8F0;">
                        <div class="form-group">
                            <label class="form-label">Տեխ. Քարտի Կոդ *</label>
                            <input type="text" class="form-control font-mono" id="tc-code" required placeholder="RCP-PROD-V1">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Բաղադրատոմսի եզակի ծածկագիր:</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Տեխ. Քարտի Անվանում *</label>
                            <input type="text" class="form-control" id="tc-name" required placeholder="օր․ Պիցցա Տեխնիկական Քարտ">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ելքի Քանակ (Yield) *</label>
                            <input type="number" step="any" class="form-control font-mono" id="tc-yield-qty" required value="1" oninput="ERP.catalog.recalculateTechCard()">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Պատրաստի ելքը տվյալ բաղադրատոմսով:</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ելքի Միավոր *</label>
                            <select class="select" id="tc-yield-unit-id" required>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ is_array($u->name) ? ($u->name['hy'] ?? \Illuminate\Support\Arr::first($u->name)) : $u->name }} ({{ $u->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Components Table -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <div>
                            <h4 style="font-size: 0.92rem; font-weight: 800; color: var(--text-heading); margin: 0;">
                                <i class="fa-solid fa-cubes-stacked" style="color: var(--color-primary); margin-right: 6px;"></i> Բաղադրիչների &amp; Կիսաֆաբրիկատների Ցանկ
                            </h4>
                            <div class="form-hint">Կարող եք ավելացնել ինչպես հումք/բաղադրիչներ, այնպես էլ այլ պրոդուկտներ (օր․ խմոր, սոուս):</div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-xs" onclick="ERP.catalog.addRecipeComponentRow()">
                            <i class="fa-solid fa-plus"></i> Ավելացնել Բաղադրիչ
                        </button>
                    </div>

                    <div class="table-responsive" style="margin-bottom: 1rem; border: 1px solid #E2E8F0; border-radius: 6px;">
                        <table class="table" style="font-size: 0.78rem; margin: 0;">
                            <thead>
                                <tr style="background: #F1F5F9;">
                                    <th style="min-width: 220px;">Բաղադրիչ / Կիսաֆաբրիկատ</th>
                                    <th style="width: 100px;">Նետտո Քանակ</th>
                                    <th style="width: 80px;">Միավոր</th>
                                    <th style="width: 90px;" title="Խոհարարական կամ արտադրական մշակման կորուստ">Կորուստ %</th>
                                    <th style="width: 100px;" title="Հաշվարկված համախառն քաշ (Բրուտտո)">Բրուտտո</th>
                                    <th style="width: 110px;">Միավորի Գին ֏</th>
                                    <th style="width: 110px;">Ընդհանուր ֏</th>
                                    <th style="width: 32px;"></th>
                                </tr>
                            </thead>
                            <tbody id="tc-components-tbody">
                                <!-- Dynamic Component Rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Additional Costs & Instructions -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Ընդհանուր Խոտան / Կորուստ (%)</label>
                            <input type="number" step="any" class="form-control font-mono" id="tc-scrap-pct" value="0" placeholder="0" oninput="ERP.catalog.recalculateTechCard()">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Եփման, թխման կամ արտադրական վերջնական կորստի տոկոս (Scrap %):</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Աշխատուժի Ծախս (Labor Cost ֏)</label>
                            <input type="number" step="any" class="form-control font-mono" id="tc-labor-cost" value="0" placeholder="0" oninput="ERP.catalog.recalculateTechCard()">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Արտադրական աշխատավարձի բաժինը տվյալ խմբաքանակի վրա:</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Վերադիր Ծախսեր (Overhead ֏)</label>
                            <input type="number" step="any" class="form-control font-mono" id="tc-overhead-cost" value="0" placeholder="0" oninput="ERP.catalog.recalculateTechCard()">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Կոմունալ, սարքավորումների մաշվածք և այլ վերադիր ծախսեր:</div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Պատրաստման Տեխնոլոգիական Հրահանգներ (Instructions)</label>
                        <textarea class="form-control" id="tc-instructions" rows="2" placeholder="Խառնել ալյուրը խմորիչի հետ, թողնել հասունանա 45 րոպե, թխել 220°C ջերմաստիճանում 20 րոպե..."></textarea>
                    </div>

                    <!-- Cost Calculation Summary Box (Կենդանի հաշվարկ) -->
                    <div class="tech-summary-box">
                        <div class="tech-kpi">
                            <div class="tech-kpi-lbl">Հումքի Արժեք</div>
                            <div class="tech-kpi-val" id="tc-calc-materials">0 ֏</div>
                        </div>
                        <div class="tech-kpi">
                            <div class="tech-kpi-lbl">Լրացուցիչ Ծախսեր</div>
                            <div class="tech-kpi-val" id="tc-calc-extra">0 ֏</div>
                        </div>
                        <div class="tech-kpi" style="border-color: #A7F3D0; background: #ECFDF5;">
                            <div class="tech-kpi-lbl" style="color: #059669;">Միավորի Ինքնարժեք</div>
                            <div class="tech-kpi-val" style="color: #059669;" id="tc-calc-unit-cost">0 ֏</div>
                        </div>
                        <div class="tech-kpi">
                            <div class="tech-kpi-lbl">Վաճառքի Գին</div>
                            <div class="tech-kpi-val" id="tc-calc-sale-price">0 ֏</div>
                        </div>
                        <div class="tech-kpi">
                            <div class="tech-kpi-lbl">Մարժա %</div>
                            <div class="tech-kpi-val" style="color: #2563EB;" id="tc-calc-margin">0%</div>
                        </div>
                        <div class="tech-kpi">
                            <div class="tech-kpi-lbl">Վերադիր % (Markup)</div>
                            <div class="tech-kpi-val" style="color: #7C3AED;" id="tc-calc-markup">0%</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-secondary" onclick="ERP.catalog.closeTechnicalCardModal()">Փակել</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.catalog.printTechnicalCard()" title="Տպել Տեխ. Քարտը">
                            <i class="fa-solid fa-print"></i> Տպել Տեխ. Քարտ
                        </button>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.catalog.quickProduceFromTechCard()" style="background: #EFF6FF; color: #2563EB; border-color: #BFDBFE;">
                            <i class="fa-solid fa-industry"></i> Արտադրել Խմբաքանակ
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Պահպանել Տեխնիկական Քարտը
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Quick Produce Batch (Արտադրություն և Պահեստից Ավտոմատ Դուրսգրում) -->
    <div class="modal-backdrop" id="product-quick-produce-modal">
        <div class="modal-container" style="max-width: 580px;">
            <div class="modal-header">
                <div>
                    <h3><i class="fa-solid fa-industry" style="color: #2563EB; margin-right: 6px;"></i> Արտադրության Ձևակերպում</h3>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Բաղադրիչները ավտոմատ դուրս են գրվում պահեստից, իսկ պատրաստի արտադրանքը՝ մուտքագրվում:</p>
                </div>
                <button onclick="ERP.catalog.closeQuickProduceModal()" style="font-size: 1.25rem; color: var(--text-muted); background: none; border: none; cursor: pointer;">&times;</button>
            </div>
            <form onsubmit="ERP.catalog.submitProduce(event)">
                <input type="hidden" id="qp-product-id" value="">
                <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Արտադրվող Ապրանք</div>
                        <div style="font-size: 1.1rem; font-weight: 800; color: var(--text-heading); margin-top: 2px;" id="qp-product-name">-</div>
                        <div style="font-size: 0.75rem; color: var(--color-primary); font-family: var(--font-mono); margin-top: 2px;" id="qp-product-sku">-</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Արտադրվող Քանակ *</label>
                            <input type="number" step="any" class="form-control font-mono" id="qp-quantity" required value="1" placeholder="1">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Պատրաստի արտադրանքի ելքային քանակը:</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Մուտքագրվող Պահեստ *</label>
                            <select class="select" id="qp-target-warehouse" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Պահեստ, որտեղ մուտքագրվելու է պատրաստի արտադրանքը:</div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Բաղադրիչների Դուրսգրման Պահեստ *</label>
                        <select class="select" id="qp-source-warehouse" required>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                        <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Հումքի պահեստ, որտեղից կատարվելու է բաղադրիչների ավտոմատ դուրսգրումը:</div>
                    </div>

                    <div class="card" style="padding: 0.85rem; background: #ECFDF5; border-color: #A7F3D0;">
                        <div style="display: flex; gap: 8px; align-items: flex-start;">
                            <i class="fa-solid fa-circle-check" style="color: #059669; font-size: 1rem; margin-top: 2px;"></i>
                            <div style="font-size: 0.78rem; color: #065F46; line-height: 1.4;">
                                <strong>Ավտոմատացում՝</strong> Հաստատելուց հետո համակարգը ըստ տեխնիկական քարտի համամասնորեն կնվազեցնի բոլոր բաղադրիչների պահեստային մնացորդները և կավելացնի տվյալ պատրաստի ապրանքի քանակը:
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-secondary" onclick="ERP.catalog.closeQuickProduceModal()">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #059669;">
                        <i class="fa-solid fa-industry"></i> Հաստատել Արտադրությունը
                    </button>
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
                                            {{ is_array($p->name) ? ($p->name['hy'] ?? \Illuminate\Support\Arr::first($p->name)) : $p->name }} ({{ $p->sku }})
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
                                        {{ is_array($p->name) ? ($p->name['hy'] ?? \Illuminate\Support\Arr::first($p->name)) : $p->name }} ({{ $p->sku }})
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
                                            {{ is_array($p->name) ? ($p->name['hy'] ?? \Illuminate\Support\Arr::first($p->name)) : $p->name }} ({{ $p->sku }})
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
                                    {{ is_array($p->name) ? ($p->name['hy'] ?? \Illuminate\Support\Arr::first($p->name)) : $p->name }} ({{ $p->sku }})
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
         DIRECTORY MODALS (Categories, Suppliers, Ingredients, Invoices, Where-Used)
         ============================================================== -->

    <!-- Directory Modal 0: Category Create / Edit (Կատեգորիաների ավելացում / փոփոխում) -->
    <div class="modal-backdrop" id="directory-category-modal">
        <div class="modal-container" style="max-width: 650px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="category-modal-title"><i class="fa-solid fa-folder-plus"></i> Ավելացնել Կատեգորիա</h3>
                <button type="button" onclick="ERP.directory.categories.closeModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer; border: none; background: none;">&times;</button>
            </div>
            <form id="category-master-form" onsubmit="ERP.directory.categories.submitForm(event)">
                <input type="hidden" id="cat-id" value="">
                <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                    <!-- Category Type Selector: Product vs Ingredient -->
                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label class="form-label">Կատեգորիայի Տեսակ *</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 2px solid #3B82F6; border-radius: 8px; cursor: pointer; background: #EFF6FF; transition: all 0.2s;" id="cat-type-product-label">
                                <input type="radio" name="cat_type" id="cat-type-product" value="product" checked onchange="ERP.directory.categories.onModalTypeChange('product')" style="accent-color: var(--color-primary); width: 18px; height: 18px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 0.88rem; color: #1E40AF;"><i class="fa-solid fa-boxes-stacked" style="color: #2563EB;"></i> Ապրանքային Խումբ</div>
                                    <div style="font-size: 0.75rem; color: #64748B;">Վաճառքի ապրանքներ, ճաշացանկ, ըմպելիքներ</div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 2px solid #E2E8F0; border-radius: 8px; cursor: pointer; background: #fff; transition: all 0.2s;" id="cat-type-ingredient-label">
                                <input type="radio" name="cat_type" id="cat-type-ingredient" value="ingredient" onchange="ERP.directory.categories.onModalTypeChange('ingredient')" style="accent-color: var(--color-primary); width: 18px; height: 18px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 0.88rem; color: #92400E;"><i class="fa-solid fa-mortar-pestle" style="color: #D97706;"></i> Բաղադրիչների Խումբ</div>
                                    <div style="font-size: 0.75rem; color: #64748B;">Հումք, բաղադրամասեր, կիսաֆաբրիկատներ</div>
                                </div>
                            </label>
                        </div>
                        <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Ապրանքների և բաղադրիչների խմբերը առանձնացված են՝ շփոթությունից խուսափելու համար:</div>
                    </div>

                    <!-- Category Name Fields -->
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Անվանում (Հայերեն) *</label>
                        <input type="text" class="form-control" id="cat-name-hy" required placeholder="օր․ Տաք Ուտեստներ, Խմիչքներ, Կիսաֆաբրիկատներ" oninput="ERP.directory.categories.onNameInput(this.value)">
                        <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Կատեգորիայի պաշտոնական անվանումը հայերենով: Ցուցադրվում է համակարգի բոլոր բաժիններում և մենյուում:</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Name (English)</label>
                            <input type="text" class="form-control" id="cat-name-en" placeholder="e.g. Hot Dishes, Beverages">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Անգլերեն անվանում QR մենյուի և միջազգային հաշվետվությունների համար:</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Название (Русский)</label>
                            <input type="text" class="form-control" id="cat-name-ru" placeholder="напр. Горячие блюда, Напитки">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Ռուսերեն անվանում մենյուի և հաշվետվությունների համար:</div>
                        </div>
                    </div>

                    <!-- Hierarchy & Slug -->
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Գլխավոր Խումբ (Ծնող Կատեգորիա)</label>
                            <select class="select" id="cat-parent-id">
                                <option value="">-- Գլխավոր Կատեգորիա (Առանց ծնողի) --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" data-type="{{ $c->type ?? 'product' }}">
                                        {{ is_array($c->name) ? ($c->name['hy'] ?? \Illuminate\Support\Arr::first($c->name)) : $c->name }}
                                        [{{ ($c->type ?? 'product') === 'ingredient' ? 'Բաղադրիչ' : 'Ապրանք' }}]
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Եթե սա ենթակատեգորիա է, ընտրեք այն գլխավոր խումբը, որին այն պատկանում է:</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug / Հղում</label>
                            <div style="display: flex; gap: 4px;">
                                <input type="text" class="form-control font-mono" id="cat-slug" placeholder="hot-dishes">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.directory.categories.generateSlug()" title="Գեներացնել Slug">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </button>
                            </div>
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> URL-բարեկամ նույնացուցիչ (թողեք դատարկ ավտոմատ գեներացման համար):</div>
                        </div>
                    </div>

                    <!-- Image Upload with Drag & Drop, URL and Live Thumbnail -->
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Կատեգորիայի Պատկեր (Image)</label>
                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <div id="cat-image-preview-box" style="width: 76px; height: 76px; border-radius: 8px; border: 2px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                                <img id="cat-image-preview" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                <i id="cat-image-placeholder-icon" class="fa-solid fa-cloud-arrow-up" style="color: #94A3B8; font-size: 1.5rem;"></i>
                                <button type="button" id="cat-image-remove-btn" onclick="ERP.media.clearCategoryImage()" style="display: none; position: absolute; top: 3px; right: 3px; background: rgba(220, 38, 38, 0.9); color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 11px; align-items: center; justify-content: center;">&times;</button>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; gap: 0.5rem; margin-bottom: 6px;">
                                    <input type="text" class="form-control" id="cat-image-url" placeholder="https://... կամ վերբեռնեք ֆայլը" oninput="ERP.media.onCategoryUrlChange(this.value)">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('cat-image-file').click()" style="white-space: nowrap;">
                                        <i class="fa-solid fa-arrow-up-from-bracket"></i> Վերբեռնել
                                    </button>
                                    <input type="file" id="cat-image-file" accept="image/*" style="display: none;" onchange="ERP.media.handleFileUpload(this, 'categories', 'cat-image-url', 'cat-image-preview', 'cat-image-preview-box', 'cat-image-remove-btn', 'cat-image-placeholder-icon')">
                                </div>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Կարող եք ընտրել պատկեր համակարգչից (PNG, JPG, WebP մինչև 10MB) կամ տեղադրել արտաքին URL:</div>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Order & Active Status -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Դասավորության Հերթականություն (Sort Order)</label>
                            <input type="number" class="form-control font-mono" id="cat-sort-order" value="0" min="0" placeholder="0">
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Փոքր թվերը կցուցադրվեն առաջինը մենյուում և ցանկերում:</div>
                        </div>
                        <div class="form-group" style="display: flex; flex-direction: column; justify-content: center;">
                            <label class="form-label">Կարգավիճակ</label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 4px;">
                                <input type="checkbox" id="cat-is-active" checked style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                                <span style="font-weight: 600; font-size: 0.88rem; color: var(--text-heading);">Ակտիվ (Ցուցադրել մենյուում և POS-ում)</span>
                            </label>
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Ապաակտիվացված խմբերը թաքցվում են պատվերների ընդունման ժամանակ:</div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">Նկարագրություն</label>
                        <textarea class="form-control" id="cat-description" rows="2" placeholder="Կատեգորիայի բնութագիր, նշանակություն..."></textarea>
                        <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Լրացուցիչ պարզաբանումներ և նկարագրություն:</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ERP.directory.categories.closeModal()">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary" id="cat-submit-btn">
                        <i class="fa-solid fa-floppy-disk"></i> Պահպանել Կատեգորիան
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <label class="form-label" style="margin-bottom: 0;">Կատեգորիա /խումբ/ *</label>
                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="ERP.directory.categories.openCreateModal(null, 'ingredient')" style="font-size: 0.7rem; padding: 2px 6px;">
                                    <i class="fa-solid fa-plus"></i> Նոր Խումբ
                                </button>
                            </div>
                            <select class="select" id="ing-category-id" onchange="ERP.directory.ingredients.updateSubcategories(this.value)">
                                <option value="">-- Ընտրեք Բաղադրիչների Կատեգորիան --</option>
                                @foreach($ingredientCategories ?? $categories as $cat)
                                    <option value="{{ $cat->id }}">{{ is_array($cat->name) ? ($cat->name['hy'] ?? \Illuminate\Support\Arr::first($cat->name)) : $cat->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Բաղադրիչների և հումքի խումբ (օր․ Կաթնամթերք, Մսամթերք, Համեմունքներ):</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ենթակատեգորիա /ենթախումբ/</label>
                            <select class="select" id="ing-subcategory-id">
                                <option value="">-- Ընտրեք Ենթակատեգորիան --</option>
                            </select>
                            <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Բաղադրիչի ենթախումբ ավելի նեղ դասակարգման համար:</div>
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

                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Նկարագրություն</label>
                        <input type="text" class="form-control" id="ing-description" placeholder="Հատկանիշներ, խոնավություն, որակական ցուցանիշներ...">
                    </div>

                    <!-- Ingredient Image Upload -->
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Բաղադրիչի Պատկեր (Image)</label>
                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <div id="ing-image-preview-box" style="width: 64px; height: 64px; border-radius: 8px; border: 2px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                                <img id="ing-image-preview" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                <i id="ing-image-placeholder-icon" class="fa-solid fa-cloud-arrow-up" style="color: #94A3B8; font-size: 1.25rem;"></i>
                                <button type="button" id="ing-image-remove-btn" onclick="ERP.media.clearIngredientImage()" style="display: none; position: absolute; top: 2px; right: 2px; background: rgba(220, 38, 38, 0.9); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; cursor: pointer; font-size: 10px; align-items: center; justify-content: center;">&times;</button>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; gap: 0.5rem; margin-bottom: 4px;">
                                    <input type="text" class="form-control" id="ing-image-url" placeholder="https://... կամ վերբեռնեք ֆայլը" oninput="ERP.media.onIngredientUrlChange(this.value)">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('ing-image-file').click()" style="white-space: nowrap;">
                                        <i class="fa-solid fa-arrow-up-from-bracket"></i> Վերբեռնել
                                    </button>
                                    <input type="file" id="ing-image-file" accept="image/*" style="display: none;" onchange="ERP.media.handleFileUpload(this, 'ingredients', 'ing-image-url', 'ing-image-preview', 'ing-image-preview-box', 'ing-image-remove-btn', 'ing-image-placeholder-icon')">
                                </div>
                                <div class="form-hint"><i class="fa-solid fa-circle-info"></i> Բաղադրիչի լուսանկարը պահեստում տեսողական նույնականացման համար:</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing, Quantities & Live Calculations -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <div class="form-group">
                            <label class="form-label">Չափման Միավոր *</label>
                            <select class="select" id="ing-unit-id" required>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ is_array($u->name) ? ($u->name['hy'] ?? \Illuminate\Support\Arr::first($u->name)) : $u->name }} ({{ $u->code }})</option>
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

    <!-- ==============================================================
         DIRECTORY MODAL: CREATE / EDIT CUSTOMER
         ============================================================== -->
    <div class="modal-backdrop" id="directory-customer-modal">
        <div class="modal-container" style="max-width: 820px;">
            <div class="modal-header">
                <h3 id="customer-modal-title"><i class="fa-solid fa-user-plus" style="color: var(--color-primary);"></i> Նոր Հաճախորդ</h3>
                <button type="button" onclick="document.getElementById('directory-customer-modal').classList.remove('active')" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <form id="directory-customer-form" onsubmit="ERP.directory.customers.saveCustomer(event)">
                <input type="hidden" id="cust-form-id" value="">
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <!-- Type Selector Buttons -->
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem;">
                        <button type="button" class="btn btn-sm" id="cust-type-btn-indiv" style="flex: 1; border: 2px solid var(--color-primary); background: #EFF6FF; color: var(--color-primary); font-weight: 700;" onclick="ERP.directory.customers.setFormType('individual')">
                            <i class="fa-solid fa-user"></i> Ֆիզիկական Անձ (B2C)
                        </button>
                        <button type="button" class="btn btn-sm" id="cust-type-btn-comp" style="flex: 1; border: 1px solid #CBD5E1; background: #FFFFFF; color: var(--text-main); font-weight: 700;" onclick="ERP.directory.customers.setFormType('company')">
                            <i class="fa-solid fa-building"></i> Իրավաբանական Անձ (B2B)
                        </button>
                    </div>
                    <input type="hidden" id="cust-form-type" value="individual">

                    <!-- Duplicate Warning Banner -->
                    <div id="cust-dup-warning-banner" class="duplicate-warning-banner">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem; color: #D97706;"></i>
                        <span id="cust-dup-warning-text">Ուշադրություն. գտնվել է նույն հեռախոսով/էլ.փոստով հաճախորդ:</span>
                    </div>

                    <!-- Individual Fields (B2C) -->
                    <div id="cust-fields-individual">
                        <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Անձնական Տվյալներ</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                            <div>
                                <label class="form-label">Անուն <span style="color: #EF4444;">*</span></label>
                                <input type="text" id="cust-first-name" class="form-control" placeholder="Օր.՝ Գևորգ">
                            </div>
                            <div>
                                <label class="form-label">Ազգանուն</label>
                                <input type="text" id="cust-last-name" class="form-control" placeholder="Օր.՝ Հակոբյան">
                            </div>
                            <div>
                                <label class="form-label">Ծննդյան Ամսաթիվ</label>
                                <input type="date" id="cust-birth-date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Company Fields (B2B) -->
                    <div id="cust-fields-company" style="display: none;">
                        <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #7C3AED; margin-bottom: 0.5rem;">Կազմակերպության Տվյալներ</div>
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="form-label">Կազմակերպության Անվանում <span style="color: #EF4444;">*</span></label>
                                <input type="text" id="cust-company-name" class="form-control" placeholder="Օր.՝ «Անի Ռեստորանային Համալիր» ՍՊԸ">
                            </div>
                            <div>
                                <label class="form-label">ՀՎՀՀ (Tax ID)</label>
                                <input type="text" id="cust-tax-id" class="form-control font-mono" placeholder="02511448" onblur="ERP.directory.customers.onTaxIdBlur()">
                            </div>
                            <div>
                                <label class="form-label">Երկիր</label>
                                <select id="cust-registration-country" class="form-control">
                                    <option value="AM" selected>Հայաստան (AM)</option>
                                    <option value="RU">Ռուսաստան (RU)</option>
                                    <option value="GE">Վրաստան (GE)</option>
                                    <option value="US">ԱՄՆ (US)</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label class="form-label">Իրավաբանական Հասցե</label>
                                <input type="text" id="cust-legal-address" class="form-control" placeholder="ք. Երևան, Մաշտոցի պող. 15">
                            </div>
                            <div>
                                <label class="form-label">Փաստացի Հասցե</label>
                                <input type="text" id="cust-physical-address" class="form-control" placeholder="ք. Երևան, Պռոշյան 1-ին նրբ. 25">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                            <div>
                                <label class="form-label">Տնօրեն</label>
                                <input type="text" id="cust-director-name" class="form-control" placeholder="Կարեն Պետրոսյան">
                            </div>
                            <div>
                                <label class="form-label">Գնումների Պատասխանատու</label>
                                <input type="text" id="cust-purchasing-manager" class="form-control" placeholder="Արմեն Դավթյան">
                            </div>
                            <div>
                                <label class="form-label">Վարկային Սահմանաչափ (֏)</label>
                                <input type="number" id="cust-credit-limit" class="form-control font-mono" placeholder="0" min="0" step="1000">
                            </div>
                            <div>
                                <label class="form-label">Վճարման Պայման (օր)</label>
                                <input type="number" id="cust-payment-terms" class="form-control font-mono" placeholder="0" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details (Common) -->
                    <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Կապի Տվյալներ</div>
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label">Հեռախոսահամար <span style="color: #EF4444;">*</span></label>
                            <input type="text" id="cust-phone" class="form-control font-mono" placeholder="+374 91 123456" onblur="ERP.directory.customers.onPhoneBlur()">
                            <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">Աջակցում է ՀՀ (+374), ՌԴ (+7) և E.164 ձևաչափ:</div>
                        </div>
                        <div>
                            <label class="form-label">Էլ. Փոստ</label>
                            <input type="email" id="cust-email" class="form-control" placeholder="example@mail.am" onblur="ERP.directory.customers.onEmailBlur()">
                        </div>
                        <div>
                            <label class="form-label">Վեբ Կայք</label>
                            <input type="text" id="cust-website" class="form-control" placeholder="https://example.am">
                        </div>
                    </div>

                    <!-- Primary Address Section -->
                    <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Առաքման Հիմնական Հասցե</div>
                    <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label class="form-label">Քաղաք</label>
                            <input type="text" id="cust-addr-city" class="form-control" value="Երևան">
                        </div>
                        <div>
                            <label class="form-label">Փողոց / Շենք</label>
                            <input type="text" id="cust-addr-street" class="form-control" placeholder="Սայաթ-Նովա պող. 10">
                        </div>
                        <div>
                            <label class="form-label">Բնակարան / Սենյակ</label>
                            <input type="text" id="cust-addr-apartment" class="form-control" placeholder="18">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label">Հարկ</label>
                            <input type="text" id="cust-addr-floor" class="form-control" placeholder="4">
                        </div>
                        <div>
                            <label class="form-label">Դռան / Դոմոֆոնի Կոդ</label>
                            <input type="text" id="cust-addr-door-code" class="form-control" placeholder="45K">
                        </div>
                        <div>
                            <label class="form-label">Առաքման Հրահանգներ</label>
                            <input type="text" id="cust-addr-instructions" class="form-control" placeholder="Մուտքը բակի կողմից, զանգահարել ժամանելիս">
                        </div>
                    </div>

                    <!-- CRM & Loyalty Settings -->
                    <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">CRM, Լոյալություն &amp; Կարգավիճակ</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label">Մասնաճյուղ</label>
                            <select id="cust-branch-id" class="form-control">
                                <option value="">— Ընտրել —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Աղբյուր (Source)</label>
                            <select id="cust-source-id" class="form-control">
                                <option value="">— Ընտրել —</option>
                                @foreach($customerSources as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Լոյալության Tier</label>
                            <select id="cust-loyalty-tier" class="form-control">
                                <option value="basic">Basic (0%)</option>
                                <option value="bronze">Bronze (2%)</option>
                                <option value="silver">Silver (5%)</option>
                                <option value="gold">Gold (7%)</option>
                                <option value="vip">VIP (10%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Անհատական Զեղչ (%)</label>
                            <input type="number" id="cust-custom-discount" class="form-control font-mono" placeholder="0" min="0" max="100" step="0.5">
                        </div>
                        <div>
                            <label class="form-label">Կարգավիճակ</label>
                            <select id="cust-status" class="form-control">
                                <option value="active" selected>Ակտիվ</option>
                                <option value="inactive">Ոչ ակտիվ</option>
                                <option value="blocked">Արգելափակված</option>
                                <option value="archived">Արխիվացված</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Նշումներ (CRM Notes)</label>
                        <textarea id="cust-notes" class="form-control" rows="2" placeholder="Հաճախորդի հետ կապված հատուկ նշումներ, նախասիրություններ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('directory-customer-modal').classList.remove('active')">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary" id="cust-submit-btn">
                        <i class="fa-solid fa-check"></i> Պահպանել Հաճախորդին
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==============================================================
         DIRECTORY MODAL: CUSTOMER 360° PROFILE MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="customer-profile-modal">
        <div class="modal-container" style="max-width: 1050px; max-height: 90vh; display: flex; flex-direction: column;">
            <!-- Modal Header with Customer Quick Card -->
            <div class="modal-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #E2E8F0; background: #FAFAFA;">
                <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                    <div id="cprof-avatar" class="crm-avatar" style="width: 48px; height: 48px; font-size: 1.1rem;">ԳՀ</div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h3 id="cprof-name" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--text-heading);">Գևորգ Հակոբյան</h3>
                            <span id="cprof-type-badge" class="badge badge-primary">B2C</span>
                            <span id="cprof-code" class="badge badge-slate font-mono">CUST-000001</span>
                            <span id="cprof-status-badge" class="badge badge-emerald">Ակտիվ</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                            <span><i class="fa-solid fa-phone"></i> <span id="cprof-phone">+374 91 223344</span></span>
                            <span><i class="fa-solid fa-envelope"></i> <span id="cprof-email">gevorg@example.am</span></span>
                            <span><i class="fa-solid fa-location-dot"></i> <span id="cprof-address">Սայաթ-Նովա պող. 10</span></span>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.directory.customers.openEditCurrent()">
                        <i class="fa-solid fa-pen-to-square"></i> Խմբագրել
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.directory.customers.openLoyaltyAdjustModal()">
                        <i class="fa-solid fa-coins"></i> Ճշգրտել Միավորներ
                    </button>
                    <button type="button" onclick="document.getElementById('customer-profile-modal').classList.remove('active')" style="font-size: 1.5rem; color: var(--text-muted); cursor: pointer; border: none; background: transparent;">&times;</button>
                </div>
            </div>

            <!-- Profile Tabs Navigation -->
            <div style="background: #FFFFFF; border-bottom: 1px solid #E2E8F0; padding: 0 1.5rem; display: flex; gap: 1rem; overflow-x: auto;">
                <button type="button" class="directory-tab-btn active" id="cptab-btn-overview" onclick="ERP.directory.customers.switchProfileTab('overview')">
                    <i class="fa-solid fa-chart-pie"></i> Ընդհանուր
                </button>
                <button type="button" class="directory-tab-btn" id="cptab-btn-data" onclick="ERP.directory.customers.switchProfileTab('data')">
                    <i class="fa-solid fa-id-card"></i> Տվյալներ
                </button>
                <button type="button" class="directory-tab-btn" id="cptab-btn-addresses" onclick="ERP.directory.customers.switchProfileTab('addresses')">
                    <i class="fa-solid fa-location-dot"></i> Հասցեներ (<span id="cprof-addr-count">1</span>)
                </button>
                <button type="button" class="directory-tab-btn" id="cptab-btn-orders" onclick="ERP.directory.customers.switchProfileTab('orders')">
                    <i class="fa-solid fa-cart-shopping"></i> Պատվերներ (<span id="cprof-orders-count">0</span>)
                </button>
                <button type="button" class="directory-tab-btn" id="cptab-btn-loyalty" onclick="ERP.directory.customers.switchProfileTab('loyalty')">
                    <i class="fa-solid fa-award"></i> Լոյալություն
                </button>
                <button type="button" class="directory-tab-btn" id="cptab-btn-analytics" onclick="ERP.directory.customers.switchProfileTab('analytics')">
                    <i class="fa-solid fa-chart-line"></i> Վերլուծություն
                </button>
                <button type="button" class="directory-tab-btn" id="cptab-btn-timeline" onclick="ERP.directory.customers.switchProfileTab('timeline')">
                    <i class="fa-solid fa-clock-rotate-left"></i> Timeline
                </button>
                <button type="button" class="directory-tab-btn" id="cptab-btn-contacts" onclick="ERP.directory.customers.switchProfileTab('contacts')">
                    <i class="fa-solid fa-address-book"></i> Կոնտակտներ
                </button>
                <button type="button" class="directory-tab-btn" id="cptab-btn-notes" onclick="ERP.directory.customers.switchProfileTab('notes')">
                    <i class="fa-solid fa-note-sticky"></i> Նշումներ
                </button>
            </div>

            <!-- Profile Tab Contents -->
            <div class="modal-body" style="padding: 1.5rem; flex: 1; overflow-y: auto;">
                <!-- TAB 1: OVERVIEW -->
                <div id="cptab-content-overview">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="card" style="padding: 1rem;">
                            <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ընդհանուր LTV (Վաճառք)</div>
                            <div class="font-mono" id="cprof-kpi-spent" style="font-size: 1.35rem; font-weight: 800; color: #059669; margin-top: 4px;">0 ֏</div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Միջին Չեք (AOV)</div>
                            <div class="font-mono" id="cprof-kpi-aov" style="font-size: 1.35rem; font-weight: 800; color: #2563EB; margin-top: 4px;">0 ֏</div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ավարտված Պատվերներ</div>
                            <div class="font-mono" id="cprof-kpi-orders" style="font-size: 1.35rem; font-weight: 800; color: var(--text-heading); margin-top: 4px;">0</div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Լոյալության Միավորներ</div>
                            <div class="font-mono" id="cprof-kpi-points" style="font-size: 1.35rem; font-weight: 800; color: #D97706; margin-top: 4px;">0</div>
                        </div>
                        <div class="card" style="padding: 1rem;">
                            <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Customer Score</div>
                            <div id="cprof-kpi-score" style="margin-top: 4px;">
                                <span class="score-badge score-high">88 / 100</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="card" style="padding: 1.25rem;">
                            <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 0.75rem;"><i class="fa-solid fa-circle-info" style="color: var(--color-primary);"></i> Հիմնական Ամփոփում</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Հաճախորդի տեսակ:</span> <span id="cprof-ov-type" style="font-weight: 700;">Ֆիզիկական</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Հիմնական մասնաճյուղ:</span> <span id="cprof-ov-branch" style="font-weight: 700;">Գլխավոր</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Գրանցման աղբյուր:</span> <span id="cprof-ov-source" style="font-weight: 700;">Instagram</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Առաջին գնում:</span> <span id="cprof-ov-first-order" style="font-weight: 700;">—</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Վերջին գնում:</span> <span id="cprof-ov-last-order" style="font-weight: 700;">—</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Պատվերների միջին պարբերություն:</span> <span id="cprof-ov-frequency" style="font-weight: 700;">— օր</span></div>
                            </div>
                        </div>

                        <div class="card" style="padding: 1.25rem;">
                            <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 0.75rem;"><i class="fa-solid fa-award" style="color: #D97706;"></i> Լոյալության &amp; Զեղչի Կարգավիճակ</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Քարտի համար:</span> <span id="cprof-ov-card" class="font-mono" style="font-weight: 700;">LOY-000001</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Ընթացիկ մակարդակ:</span> <span id="cprof-ov-tier" class="badge badge-amber" style="text-transform: uppercase;">GOLD</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Գործող զեղչ:</span> <span id="cprof-ov-discount" class="font-mono" style="font-weight: 700; color: #2563EB;">5.0%</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Կուտակված ընդհանուր:</span> <span id="cprof-ov-earned-lifetime" class="font-mono">500 միավոր</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Օգտագործված ընդհանուր:</span> <span id="cprof-ov-spent-lifetime" class="font-mono">50 միավոր</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: PROFILE DATA -->
                <div id="cptab-content-data" style="display: none;">
                    <div class="card" style="padding: 1.25rem;">
                        <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 1rem;"><i class="fa-solid fa-id-card-clip"></i> Ամբողջական Անկետա</h4>
                        <div id="cprof-data-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.85rem;">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>

                <!-- TAB 3: ADDRESSES -->
                <div id="cptab-content-addresses" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 style="font-size: 0.95rem; font-weight: 800; margin: 0;"><i class="fa-solid fa-map-location-dot"></i> Գրանցված Հասցեներ</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="ERP.directory.customers.toggleAddAddressForm()">
                            <i class="fa-solid fa-plus"></i> Ավելացնել Հասցե
                        </button>
                    </div>

                    <!-- Add Address Quick Form -->
                    <div id="cprof-add-address-form" class="card" style="display: none; padding: 1rem; margin-bottom: 1rem; background: #F8FAFC; border: 1px dashed var(--color-primary);">
                        <div style="font-weight: 800; font-size: 0.85rem; margin-bottom: 0.5rem;">Նոր Առաքման Հասցե</div>
                        <div style="display: grid; grid-template-columns: 1fr 2fr 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <input type="text" id="caddr-title" class="form-control" placeholder="Անվանում (օր.՝ Գրասենյակ)">
                            <input type="text" id="caddr-street" class="form-control" placeholder="Փողոց / Շենք *">
                            <input type="text" id="caddr-city" class="form-control" placeholder="Քաղաք" value="Երևան">
                            <input type="text" id="caddr-apartment" class="form-control" placeholder="Բնակարան">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <input type="text" id="caddr-floor" class="form-control" placeholder="Հարկ">
                            <input type="text" id="caddr-door-code" class="form-control" placeholder="Դռան կոդ">
                            <input type="text" id="caddr-instructions" class="form-control" placeholder="Հրահանգներ (օր.՝ 2-րդ մուտք)">
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label style="font-size: 0.8rem; display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" id="caddr-is-default"> Դարձնել հիմնական հասցե
                            </label>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" class="btn btn-secondary btn-xs" onclick="ERP.directory.customers.toggleAddAddressForm()">Չեղարկել</button>
                                <button type="button" class="btn btn-primary btn-xs" onclick="ERP.directory.customers.submitAddress()">Պահպանել Հասցեն</button>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses List Container -->
                    <div id="cprof-addresses-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <!-- Dynamically populated -->
                    </div>
                </div>

                <!-- TAB 4: ORDERS -->
                <div id="cptab-content-orders" style="display: none;">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Պատվերի #</th>
                                    <th>Ամսաթիվ</th>
                                    <th>Տեսակ</th>
                                    <th>Հասցե</th>
                                    <th>Գումար</th>
                                    <th>Վճարում</th>
                                    <th>Կարգավիճակ</th>
                                </tr>
                            </thead>
                            <tbody id="cprof-orders-table-body">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 5: LOYALTY & POINTS -->
                <div id="cptab-content-loyalty" style="display: none;">
                    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div id="cprof-loyalty-card-visual" class="loyalty-card-visual">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                <div style="font-size: 0.8rem; font-weight: 700; opacity: 0.8; letter-spacing: 0.05em;">ERPLANNET LOYALTY</div>
                                <span id="cprof-card-tier-badge" class="badge" style="background: rgba(255,255,255,0.2); color: #FFF; text-transform: uppercase;">GOLD</span>
                            </div>
                            <div id="cprof-card-number" class="font-mono" style="font-size: 1.25rem; font-weight: 800; letter-spacing: 0.1em; margin-bottom: 1.5rem;">LOY-000001</div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                                <div>
                                    <div style="font-size: 0.68rem; opacity: 0.7;">ՀԱՃԱԽՈՐԴ</div>
                                    <div id="cprof-card-holder" style="font-weight: 700; font-size: 0.95rem;">Գևորգ Հակոբյան</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.68rem; opacity: 0.7;">ՄՆԱՑՈՐԴ</div>
                                    <div id="cprof-card-balance" class="font-mono" style="font-size: 1.25rem; font-weight: 800; color: #FCD34D;">450 մ.</div>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="padding: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin: 0;"><i class="fa-solid fa-calculator"></i> Միավորների Կառավարում</h4>
                                <button type="button" class="btn btn-outline-primary btn-xs" onclick="ERP.directory.customers.openLoyaltyAdjustModal()">
                                    <i class="fa-solid fa-plus-minus"></i> Ձեռքով Ճշգրտում
                                </button>
                            </div>
                            <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                Միավորների կուտակումը և ծախսը գրանցվում են անհերքելի transaction ledger-ում: Յուրաքանչյուր պատվերի համար կուտակումը կատարվում է idempotent սկզբունքով:
                            </p>
                            <div style="display: flex; gap: 1rem; font-size: 0.85rem;">
                                <div><span style="color: var(--text-muted);">Քարտի կարգավիճակ:</span> <span id="cprof-loyalty-frozen" class="badge badge-emerald">Ակտիվ</span></div>
                                <div><span style="color: var(--text-muted);">Զեղչի տոկոս:</span> <span id="cprof-loyalty-discount" class="font-mono" style="font-weight: 700;">5.0%</span></div>
                            </div>
                        </div>
                    </div>

                    <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 0.75rem;"><i class="fa-solid fa-list-check"></i> Լոյալության Գործարքների Պատմություն (Ledger)</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Տեսակ</th>
                                    <th>Միավորներ (+/-)</th>
                                    <th>Մնացորդ հետո</th>
                                    <th>Պատճառ / Մեկնաբանություն</th>
                                    <th>Ամսաթիվ</th>
                                    <th>Աշխատակից</th>
                                </tr>
                            </thead>
                            <tbody id="cprof-loyalty-tx-table-body">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 6: ANALYTICS -->
                <div id="cptab-content-analytics" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="card" style="padding: 1.25rem;">
                            <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 0.75rem;"><i class="fa-solid fa-fire" style="color: #EF4444;"></i> Ամենահաճախ Գնվող Ապրանքներ</h4>
                            <div class="table-responsive">
                                <table class="table" style="font-size: 0.82rem;">
                                    <thead>
                                        <tr>
                                            <th>Ապրանք</th>
                                            <th style="text-align: right;">Քանակ</th>
                                            <th style="text-align: right;">Ընդհանուր (֏)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cprof-analytics-top-products">
                                        <!-- Dynamically populated -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card" style="padding: 1.25rem;">
                            <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 0.75rem;"><i class="fa-solid fa-chart-column" style="color: #2563EB;"></i> RFM &amp; Պահվածքի Վերլուծություն</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.85rem;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <span style="color: var(--text-muted);">Customer Score (RFM)</span>
                                        <span id="cprof-rfm-score-val" style="font-weight: 800;">88 / 100</span>
                                    </div>
                                    <div style="height: 8px; border-radius: 4px; background: #E2E8F0; overflow: hidden;">
                                        <div id="cprof-rfm-score-bar" style="height: 100%; width: 88%; background: #10B981;"></div>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Գնումների ռիթմ (հաճախականություն):</span> <span id="cprof-an-freq" style="font-weight: 700;">2 օրը մեկ</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Չեղարկված պատվերներ:</span> <span id="cprof-an-canceled" class="badge badge-slate">0</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Վերադարձներ:</span> <span id="cprof-an-returns" class="badge badge-slate">0</span></div>
                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Նախընտրելի մասնաճյուղ:</span> <span id="cprof-an-branch" style="font-weight: 700;">Գլխավոր Մասնաճյուղ</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: TIMELINE -->
                <div id="cptab-content-timeline" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <h4 style="font-size: 0.95rem; font-weight: 800; margin: 0;"><i class="fa-solid fa-timeline"></i> Իրադարձությունների Ժամանակագրություն (CRM Timeline)</h4>
                        <button type="button" class="btn btn-secondary btn-xs" onclick="ERP.directory.customers.loadTimeline()">
                            <i class="fa-solid fa-arrows-rotate"></i> Թարմացնել Timeline
                        </button>
                    </div>
                    <div id="cprof-timeline-container" class="crm-timeline-list">
                        <!-- Dynamically populated from /v1/customers/{id}/timeline -->
                    </div>
                </div>

                <!-- TAB 8: CONTACTS (B2B Multi-contacts) -->
                <div id="cptab-content-contacts" style="display: none;">
                    <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 1rem;"><i class="fa-solid fa-address-book"></i> Կոնտակտային Անձինք</h4>
                    <div id="cprof-contacts-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <!-- Dynamically populated -->
                    </div>
                </div>

                <!-- TAB 9: NOTES -->
                <div id="cptab-content-notes" style="display: none;">
                    <!-- Add Note Quick Form -->
                    <div class="card" style="padding: 1rem; margin-bottom: 1.25rem; background: #F8FAFC;">
                        <div style="font-weight: 800; font-size: 0.85rem; margin-bottom: 0.5rem;"><i class="fa-solid fa-plus"></i> Ավելացնել Նոր Գրառում</div>
                        <div style="margin-bottom: 0.5rem;">
                            <textarea id="cnote-content" class="form-control" rows="2" placeholder="Գրեք նշում հաճախորդի, հանդիպման կամ պահանջի մասին..."></textarea>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                <select id="cnote-category" class="form-control" style="width: 150px; font-size: 0.8rem;">
                                    <option value="general">Ընդհանուր</option>
                                    <option value="preference">Նախասիրություն</option>
                                    <option value="complaint">Բողոք / Դիտողություն</option>
                                    <option value="financial">Ֆինանսական</option>
                                </select>
                                <label style="font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
                                    <input type="checkbox" id="cnote-pinned"> Ամրացնել (Pin)
                                </label>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="ERP.directory.customers.submitNote()">
                                <i class="fa-solid fa-paper-plane"></i> Պահպանել Նշումը
                            </button>
                        </div>
                    </div>

                    <!-- Notes Feed -->
                    <div id="cprof-notes-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <!-- Dynamically populated -->
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: space-between;">
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="ERP.directory.customers.archiveCurrent()">
                        <i class="fa-solid fa-box-archive"></i> Արխիվացնել
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('customer-profile-modal').classList.remove('active')">Փակել</button>
            </div>
        </div>
    </div>

    <!-- ==============================================================
         DIRECTORY MODAL: MERGE CUSTOMERS MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="customer-merge-modal">
        <div class="modal-container" style="max-width: 580px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-code-merge" style="color: var(--color-primary);"></i> Միավորել Կրկնվող Հաճախորդներին</h3>
                <button type="button" onclick="document.getElementById('customer-merge-modal').classList.remove('active')" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                    Միավորման արդյունքում աղբյուր հանդիսացող հաճախորդի բոլոր պատվերները, հասցեները, լոյալության միավորները և պատմությունը կտեղափոխվեն հիմնական հաճախորդի քարտ:
                </p>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Հիմնական (Target) Հաճախորդ <span style="color: #EF4444;">*</span></label>
                    <select id="merge-target-id" class="form-control">
                        <option value="">— Ընտրել հիմնական հաճախորդին —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_code }} — {{ $c->display_name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Կլանվող (Source) Հաճախորդ <span style="color: #EF4444;">*</span></label>
                    <select id="merge-source-id" class="form-control">
                        <option value="">— Ընտրել կլանվող հաճախորդին —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_code }} — {{ $c->display_name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Միավորման Պատճառ / Նշումներ</label>
                    <input type="text" id="merge-notes" class="form-control" placeholder="Օր.՝ Կրկնվող գրանցում նույն հեռախոսահամարով">
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('customer-merge-modal').classList.remove('active')">Չեղարկել</button>
                <button type="button" class="btn btn-primary" onclick="ERP.directory.customers.submitMerge()">
                    <i class="fa-solid fa-code-merge"></i> Հաստատել Միավորումը
                </button>
            </div>
        </div>
    </div>

    <!-- ==============================================================
         DIRECTORY MODAL: ADJUST LOYALTY POINTS MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="customer-loyalty-adjust-modal">
        <div class="modal-container" style="max-width: 480px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-coins" style="color: #D97706;"></i> Լոյալության Միավորների Ճշգրտում</h3>
                <button type="button" onclick="document.getElementById('customer-loyalty-adjust-modal').classList.remove('active')" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Միավորների Փոփոխություն (+ / -) <span style="color: #EF4444;">*</span></label>
                    <input type="number" id="loyalty-adj-delta" class="form-control font-mono" placeholder="Օր.՝ 100 կամ -50" step="1">
                    <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">Դրական թիվը ավելացնում է, բացասականը՝ դուրս գրում:</div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Տեսակ</label>
                    <select id="loyalty-adj-type" class="form-control">
                        <option value="manual_adj">Ձեռքով ճշգրտում (Manual Adjustment)</option>
                        <option value="birthday_gift">Ծննդյան նվեր (Birthday Gift)</option>
                        <option value="earn">Կուտակում (Earn)</option>
                        <option value="redeem">Օգտագործում (Redeem)</option>
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label">Պատճառ / Հիմնավորում <span style="color: #EF4444;">*</span></label>
                    <input type="text" id="loyalty-adj-reason" class="form-control" placeholder="Օր.՝ Ակցիայի բոնուս կամ սխալի ուղղում">
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('customer-loyalty-adjust-modal').classList.remove('active')">Չեղարկել</button>
                <button type="button" class="btn btn-primary" onclick="ERP.directory.customers.submitLoyaltyAdjustment()">
                    <i class="fa-solid fa-check"></i> Կատարել Գործարքը
                </button>
            </div>
        </div>
    </div>

    <!-- ==============================================================
         PHASE 5 MODAL: CREATE ORDER MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="create-order-modal">
        <div class="modal-container" style="max-width: 950px; max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <div>
                    <h3><i class="fa-solid fa-cart-shopping" style="color: var(--color-primary); margin-right: 6px;"></i> Նոր Պատվեր (New Order)</h3>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Բոլոր աղբյուրների (POS, Storefront, Back-Office) միասնական պատվերի ստեղծում:</p>
                </div>
                <button type="button" onclick="ERP.orders.closeCreateModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer; background: none; border: none;">&times;</button>
            </div>
            <form id="create-order-form" onsubmit="ERP.orders.submitCreate(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                <div class="modal-body" style="overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1;">
                    <!-- Order Header Meta -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; background: #F8FAFC; padding: 0.85rem; border-radius: 8px; border: 1px solid #E2E8F0;">
                        <div class="form-group">
                            <label class="form-label">Մասնաճյուղ *</label>
                            <select class="select" id="order-branch-id" required>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ելքագրման Պահեստ *</label>
                            <select class="select" id="order-warehouse-id" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Հաճախորդ</label>
                            <select class="select" id="order-customer-id" onchange="ERP.orders.onCustomerChange()">
                                <option value="">Անանուն Հաճախորդ (Anonymous)</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'Հեռ․ չկա' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Պատվերի Աղբյուր *</label>
                            <select class="select" id="order-source" required>
                                <option value="manual_backoffice">Manual Back-Office</option>
                                <option value="pos">POS Terminal</option>
                                <option value="online_store">Online Storefront</option>
                                <option value="xml_import">XML Import</option>
                                <option value="external_integration">External Integration</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1.2fr 1.5fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Առաքման / Տրամադրման Տեսակ *</label>
                            <select class="select" id="order-fulfillment-method" required>
                                <option value="pickup">Ինքնարտահանում (Pickup)</option>
                                <option value="delivery">Առաքում (Delivery)</option>
                                <option value="dine_in">Տեղում / Սրահում (Dine In)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Պատվերի Տիպ *</label>
                            <select class="select" id="order-type" required>
                                <option value="standard">Ստանդարտ (Standard)</option>
                                <option value="preliminary">Նախնական / Ապառիկ (Preliminary)</option>
                                <option value="b2b_wholesale">B2B Մեծածախ (B2B Wholesale)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Պլանավորված Ժամկետ (Scheduled)</label>
                            <input type="datetime-local" class="form-control" id="order-scheduled-at">
                            <div class="form-hint">Նախնական պատվերների կատարման ժամ:</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Պատասխանատու Աշխատակից</label>
                            <select class="select" id="order-employee-id">
                                <option value="">Ավտոմատ (Ընթացիկ օգտատեր)</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Order Line Items -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <h4 style="font-size: 0.92rem; font-weight: 800; color: var(--text-heading); margin: 0;">
                            <i class="fa-solid fa-boxes-stacked" style="color: var(--color-primary); margin-right: 6px;"></i> Պատվերի Տողեր (Order Items)
                        </h4>
                        <button type="button" class="btn btn-secondary btn-xs" onclick="ERP.orders.addItemRow()">
                            <i class="fa-solid fa-plus"></i> Ավելացնել Տող
                        </button>
                    </div>

                    <div class="table-responsive" style="margin-bottom: 1rem; border: 1px solid #E2E8F0; border-radius: 6px;">
                        <table class="table" style="font-size: 0.78rem; margin: 0;">
                            <thead>
                                <tr style="background: #F1F5F9;">
                                    <th style="min-width: 220px;">Ապրանք</th>
                                    <th style="width: 100px;">Քանակ</th>
                                    <th style="width: 120px;">Գին (֏)</th>
                                    <th style="width: 90px;">Զեղչ %</th>
                                    <th style="width: 80px;">ԱԱՀ %</th>
                                    <th style="width: 120px;">Տողի Գումար (֏)</th>
                                    <th style="width: 32px;"></th>
                                </tr>
                            </thead>
                            <tbody id="order-items-tbody">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Discount, Promo, Delivery & Totals -->
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1rem; align-items: start;">
                        <div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Պատվերի Զեղչ (Ընդհանուր)</label>
                                    <div style="display: flex; gap: 4px;">
                                        <input type="number" step="any" class="form-control font-mono" id="order-discount-value" value="0" placeholder="0" oninput="ERP.orders.recalculateLivePricing()">
                                        <select class="select" id="order-discount-type" style="width: 75px;" onchange="ERP.orders.recalculateLivePricing()">
                                            <option value="fixed">֏</option>
                                            <option value="percent">%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Առաքման Վճար (֏)</label>
                                    <input type="number" step="any" class="form-control font-mono" id="order-delivery-fee" value="0" placeholder="0" oninput="ERP.orders.recalculateLivePricing()">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1.5fr auto; gap: 0.5rem; align-items: flex-end; margin-bottom: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Պրոմո Կոդ (Promo Code)</label>
                                    <input type="text" class="form-control font-mono" id="order-promo-code" placeholder="Օր. SAVE10 կամ PROMO500">
                                </div>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.orders.applyPromoCode()" style="margin-bottom: 2px;">Կիրառել</button>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Նշումներ Պատվերի Վերաբերյալ</label>
                                <textarea class="form-control" id="order-notes" rows="2" placeholder="Հաճախորդի կամ ներքին ծառայողական նշումներ..."></textarea>
                            </div>
                        </div>

                        <!-- Live Calculation Summary Box -->
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem;">
                            <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.75rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.35rem;">
                                Հաշվարկված Գումարներ (Exact Precision)
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px;">
                                <span style="color: var(--text-muted);">Ապրանքների Ենթագումար՝</span>
                                <span class="font-mono" id="order-sum-subtotal">0.00 ֏</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px;">
                                <span style="color: var(--text-muted);">Տողային Զեղչեր՝</span>
                                <span class="font-mono" style="color: #EF4444;" id="order-sum-item-discounts">-0.00 ֏</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px;">
                                <span style="color: var(--text-muted);">Պատվերի Զեղչ՝</span>
                                <span class="font-mono" style="color: #EF4444;" id="order-sum-order-discount">-0.00 ֏</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px;">
                                <span style="color: var(--text-muted);">Պրոմո Զեղչ՝</span>
                                <span class="font-mono" style="color: #EF4444;" id="order-sum-promo-discount">-0.00 ֏</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px;">
                                <span style="color: var(--text-muted);">Առաքման Վճար՝</span>
                                <span class="font-mono" id="order-sum-delivery">0.00 ֏</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 8px;">
                                <span style="color: var(--text-muted);">Հաշվարկված ԱԱՀ (Tax)՝</span>
                                <span class="font-mono" id="order-sum-tax">0.00 ֏</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 1.05rem; font-weight: 800; border-top: 2px solid #CBD5E1; padding-top: 8px; color: var(--text-heading);">
                                <span>Վերջնական Վճարման՝</span>
                                <span class="font-mono" style="color: var(--color-primary);" id="order-sum-grand-total">0.00 ֏</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn btn-secondary" onclick="ERP.orders.closeCreateModal()">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Ստեղծել Պատվերը</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==============================================================
         PHASE 5 MODAL: ORDER DETAILS & LIFECYCLE MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="order-details-modal">
        <div class="modal-container" style="max-width: 950px; max-height: 92vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h3 id="od-title" style="margin: 0;">Պատվեր #<span id="od-order-number">-</span></h3>
                        <span class="badge" id="od-badge-status">Draft</span>
                        <span class="badge" id="od-badge-payment">Pending</span>
                        <span class="badge" id="od-badge-source">Manual</span>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                        Ստեղծվել է՝ <span id="od-placed-at" class="font-mono">-</span> | Կատարման ժամկետ՝ <span id="od-scheduled-at" class="font-mono">-</span>
                    </p>
                </div>
                <button type="button" onclick="ERP.orders.closeDetailsModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer; background: none; border: none;">&times;</button>
            </div>

            <!-- Tabs Nav -->
            <div style="display: flex; gap: 4px; padding: 0.5rem 1.5rem 0; border-bottom: 1px solid #E2E8F0; background: #F8FAFC;">
                <button type="button" class="btn btn-sm btn-outline tab-btn active" id="od-tab-btn-overview" onclick="ERP.orders.switchDetailsTab('overview')"><i class="fa-solid fa-circle-info"></i> Ընդհանուր</button>
                <button type="button" class="btn btn-sm btn-outline tab-btn" id="od-tab-btn-items" onclick="ERP.orders.switchDetailsTab('items')"><i class="fa-solid fa-boxes-stacked"></i> Ապրանքներ</button>
                <button type="button" class="btn btn-sm btn-outline tab-btn" id="od-tab-btn-payments" onclick="ERP.orders.switchDetailsTab('payments')"><i class="fa-solid fa-credit-card"></i> Վճարումներ & Split</button>
                <button type="button" class="btn btn-sm btn-outline tab-btn" id="od-tab-btn-docs" onclick="ERP.orders.switchDetailsTab('docs')"><i class="fa-solid fa-file-invoice"></i> Փաստաթղթեր & Տպագրություն</button>
                <button type="button" class="btn btn-sm btn-outline tab-btn" id="od-tab-btn-audit" onclick="ERP.orders.switchDetailsTab('audit')"><i class="fa-solid fa-clock-rotate-left"></i> Աուդիտ</button>
            </div>

            <div class="modal-body" style="overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1;">
                <!-- Tab 1: Overview & Lifecycle -->
                <div id="od-pane-overview">
                    <!-- Lifecycle Actions Bar -->
                    <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: #1E40AF; font-weight: 700; text-transform: uppercase;">Կարգավիճակի Փոփոխություն</div>
                            <div style="font-size: 0.85rem; color: #1E3A8A;">Անցումները ավտոմատ վերահսկում են պահեստի ռեզերվացումն ու դուրսգրումը:</div>
                        </div>
                        <div id="od-status-actions" style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <!-- Populated dynamically based on allowed transitions -->
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <!-- Customer Snapshot Card -->
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem;">
                            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.5rem;">
                                <i class="fa-solid fa-user" style="color: var(--color-primary); margin-right: 6px;"></i> Հաճախորդի Պատմական Snapshot
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.6;">
                                <div><strong>Անուն՝</strong> <span id="od-cust-name">-</span></div>
                                <div><strong>Հեռախոս՝</strong> <span id="od-cust-phone" class="font-mono">-</span></div>
                                <div><strong>ՀՎՀՀ (TIN)՝</strong> <span id="od-cust-tax-id" class="font-mono">-</span></div>
                                <div><strong>Հասցե՝</strong> <span id="od-cust-address">-</span></div>
                            </div>
                        </div>

                        <!-- Logistics & Branch Card -->
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-heading);">
                                    <i class="fa-solid fa-truck" style="color: #059669; margin-right: 6px;"></i> Լոգիստիկա & Մասնաճյուղ
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="ERP.orders.openRescheduleModal()" id="btn-open-reschedule">
                                    <i class="fa-solid fa-calendar-days"></i> Փոխել Ժամկետը
                                </button>
                            </div>
                            <div style="font-size: 0.85rem; line-height: 1.6;">
                                <div><strong>Մասնաճյուղ՝</strong> <span id="od-branch-name">-</span></div>
                                <div><strong>Պահեստ՝</strong> <span id="od-warehouse-name">-</span></div>
                                <div><strong>Ֆուլֆիլմենթ՝</strong> <span id="od-fulfillment-method">-</span></div>
                                <div><strong>Պատասխանատու՝</strong> <span id="od-employee-name">-</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary Tile -->
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="card" style="padding: 0.75rem; text-align: center;">
                            <div style="font-size: 0.72rem; color: var(--text-muted);">Ընդհանուր Գումար</div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--text-heading);" class="font-mono" id="od-fin-total">0 ֏</div>
                        </div>
                        <div class="card" style="padding: 0.75rem; text-align: center; background: #ECFDF5; border-color: #A7F3D0;">
                            <div style="font-size: 0.72rem; color: #065F46;">Վճարված Գումար</div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: #059669;" class="font-mono" id="od-fin-paid">0 ֏</div>
                        </div>
                        <div class="card" style="padding: 0.75rem; text-align: center; background: #FEF2F2; border-color: #FECACA;">
                            <div style="font-size: 0.72rem; color: #991B1B;">Մնացորդ Վճարման</div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: #DC2626;" class="font-mono" id="od-fin-balance">0 ֏</div>
                        </div>
                        <div class="card" style="padding: 0.75rem; text-align: center;">
                            <div style="font-size: 0.72rem; color: var(--text-muted);">Զեղչեր Ընդամենը</div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: #7C3AED;" class="font-mono" id="od-fin-discounts">0 ֏</div>
                        </div>
                    </div>

                    <!-- Notes & Cancellation -->
                    <div id="od-cancellation-box" style="display: none; background: #FEF2F2; border: 1px solid #F87171; border-radius: 6px; padding: 0.75rem; margin-bottom: 1rem;">
                        <strong style="color: #991B1B;"><i class="fa-solid fa-ban"></i> Չեղարկման Պատճառ՝</strong>
                        <span id="od-cancellation-reason" style="color: #7F1D1D;">-</span>
                    </div>
                </div>

                <!-- Tab 2: Order Items -->
                <div id="od-pane-items" style="display: none;">
                    <div class="table-responsive" style="border: 1px solid #E2E8F0; border-radius: 6px;">
                        <table class="table" style="font-size: 0.8rem; margin: 0;">
                            <thead>
                                <tr style="background: #F1F5F9;">
                                    <th>Ապրանք / Կոդ</th>
                                    <th>Քանակ & Միավոր</th>
                                    <th>Միավորի Գին</th>
                                    <th>Զեղչ</th>
                                    <th>ԱԱՀ</th>
                                    <th>Տողի Գումար</th>
                                    <th>Ինքնարժեք</th>
                                </tr>
                            </thead>
                            <tbody id="od-items-tbody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Payments & Split Checkout -->
                <div id="od-pane-payments" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1rem; align-items: start;">
                        <!-- Payment List -->
                        <div>
                            <h4 style="font-size: 0.9rem; font-weight: 800; margin-bottom: 0.5rem;">Գրանցված Վճարումներ & Վերադարձներ</h4>
                            <div id="od-payments-list" style="display: flex; flex-direction: column; gap: 8px;">
                                <!-- Dynamic payment cards -->
                            </div>
                        </div>

                        <!-- Record Payment Form -->
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem;">
                            <h4 style="font-size: 0.88rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-plus-circle" style="color: #059669; margin-right: 6px;"></i> Գրանցել Նոր Վճարում (Split Payment)
                            </h4>
                            <form onsubmit="ERP.orders.submitRecordPayment(event)">
                                <div class="form-group" style="margin-bottom: 0.5rem;">
                                    <label class="form-label">Վճարման Եղանակ *</label>
                                    <select class="select" id="od-pay-method" required>
                                        <option value="cash">Կանխիկ (Cash)</option>
                                        <option value="card">Բանկային Քարտ (Card POS)</option>
                                        <option value="arca">ArCa Պրոցեսինգ</option>
                                        <option value="idram">Idram QR</option>
                                        <option value="telcell">Telcell Wallet</option>
                                        <option value="ameria">Ameria vPOS</option>
                                        <option value="stripe">Stripe</option>
                                        <option value="bank_transfer">Բանկային Փոխանցում</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 0.5rem;">
                                    <label class="form-label">Գումար (֏) *</label>
                                    <input type="number" step="any" class="form-control font-mono" id="od-pay-amount" required placeholder="0">
                                </div>
                                <div class="form-group" style="margin-bottom: 0.75rem;">
                                    <label class="form-label">Գործարքի / Չեկի Ref ID</label>
                                    <input type="text" class="form-control font-mono" id="od-pay-ref" placeholder="Օր. TXN-998241">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                                    <i class="fa-solid fa-check"></i> Կատարել Վճարում
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Documents & Print -->
                <div id="od-pane-docs" style="display: none;">
                    <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="ERP.orders.printCurrentOrderReceipt()">
                            <i class="fa-solid fa-receipt"></i> Տպել Չեկ (Receipt 80mm/58mm)
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.orders.printCurrentOrderA4()">
                            <i class="fa-solid fa-print"></i> Տպել A4 / A5 Պատվեր
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" style="background: #7C3AED; color: white; border-color: #6D28D9;" onclick="ERP.orders.openGenerateDeliveryNoteForCurrent()">
                            <i class="fa-solid fa-file-invoice"></i> Ստեղծել B2B Բեռնագիր (Накладная)
                        </button>
                    </div>

                    <h4 style="font-size: 0.88rem; font-weight: 800; margin-bottom: 0.5rem;">Կից B2B Բեռնագրեր</h4>
                    <div id="od-attached-delivery-notes" style="margin-bottom: 1rem;">
                        <!-- Dynamic delivery notes -->
                    </div>

                    <h4 style="font-size: 0.88rem; font-weight: 800; margin-bottom: 0.5rem;">Տպագրության Աշխատանքներ (Print Jobs Queue)</h4>
                    <div id="od-attached-print-jobs">
                        <!-- Dynamic print jobs -->
                    </div>
                </div>

                <!-- Tab 5: Audit Log -->
                <div id="od-pane-audit" style="display: none;">
                    <div id="od-audit-timeline" style="display: flex; flex-direction: column; gap: 8px;">
                        <!-- Dynamic audit events -->
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-secondary" onclick="ERP.orders.closeDetailsModal()">Փակել</button>
                <div style="display: flex; gap: 6px;">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-cancel-order" onclick="ERP.orders.promptCancelOrder()" style="color: #DC2626; border-color: #F87171;">
                        <i class="fa-solid fa-ban"></i> Չեղարկել Պատվերը
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==============================================================
         PHASE 5 MODAL: RESCHEDULE ORDER MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="reschedule-order-modal">
        <div class="modal-container" style="max-width: 500px;">
            <div class="modal-header">
                <div>
                    <h3><i class="fa-solid fa-calendar-days" style="color: var(--color-primary); margin-right: 6px;"></i> Վերապլանավորել Պատվերը</h3>
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                        Պատվերի սկզբնական ստեղծման ժամկետը (placed_at) երբեք չի փոփոխվում:
                    </p>
                </div>
                <button type="button" onclick="document.getElementById('reschedule-order-modal').classList.remove('active')" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer; background: none; border: none;">&times;</button>
            </div>
            <form onsubmit="ERP.orders.submitReschedule(event)">
                <div class="modal-body">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Նոր Կատարման Ամսաթիվ & Ժամ *</label>
                        <input type="datetime-local" class="form-control" id="reschedule-target-date" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Պատճառ / Հիմնավորում</label>
                        <input type="text" class="form-control" id="reschedule-note" placeholder="Օր.՝ Հաճախորդի խնդրանքով տեղափոխվել է">
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('reschedule-order-modal').classList.remove('active')">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Հաստատել Փոփոխությունը</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==============================================================
         PHASE 5 MODAL: GENERATE B2B DELIVERY NOTE MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="generate-delivery-note-modal">
        <div class="modal-container" style="max-width: 650px;">
            <div class="modal-header">
                <div>
                    <h3><i class="fa-solid fa-file-invoice" style="color: #7C3AED; margin-right: 6px;"></i> Ձևավորել B2B Բեռնագիր (Накладная)</h3>
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">Պաշտոնական ապրանքային բեռնագիր հաջորդական համարակալմամբ:</p>
                </div>
                <button type="button" onclick="ERP.deliveryNotes.closeGenerateModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer; background: none; border: none;">&times;</button>
            </div>
            <form onsubmit="ERP.deliveryNotes.submitGenerate(event)">
                <input type="hidden" id="dn-gen-order-id" value="">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Ստացող Ընկերություն (Legal Name) *</label>
                            <input type="text" class="form-control" id="dn-recipient-name" required placeholder="«ՍՊԸ» կամ «ԱՁ»">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ստացողի ՀՎՀՀ (TIN / Tax ID)</label>
                            <input type="text" class="form-control font-mono" id="dn-recipient-tax-id" placeholder="8-նիշ ՀՎՀՀ">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Առաքման / Նշանակման Հասցե</label>
                        <input type="text" class="form-control" id="dn-delivery-address" placeholder="Երևան, ...">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Հանձնեց (Delivered By)</label>
                            <input type="text" class="form-control" id="dn-delivered-by" placeholder="Առաքիչ / Պատասխանատու">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ընդունեց (Received By)</label>
                            <input type="text" class="form-control" id="dn-received-by" placeholder="Ստացող անձ">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Նշումներ / Ծանոթագրություն</label>
                        <textarea class="form-control" id="dn-notes" rows="2" placeholder="Լրացուցիչ պայմաններ կամ պայմանագրի համար..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-secondary" onclick="ERP.deliveryNotes.closeGenerateModal()">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary" style="background: #7C3AED; border-color: #6D28D9;">
                        <i class="fa-solid fa-check"></i> Ստեղծել Բեռնագիր
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==============================================================
         PHASE 5 MODAL: VIEW & PRINT B2B DELIVERY NOTE MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="view-delivery-note-modal">
        <div class="modal-container" style="max-width: 900px; max-height: 92vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <div>
                    <h3 id="vdn-title"><i class="fa-solid fa-file-invoice" style="color: #7C3AED; margin-right: 6px;"></i> Բեռնագիր #<span id="vdn-doc-number">-</span></h3>
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">Պաշտոնական B2B ապրանքագիր</p>
                </div>
                <button type="button" onclick="document.getElementById('view-delivery-note-modal').classList.remove('active')" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer; background: none; border: none;">&times;</button>
            </div>
            <div class="modal-body" style="overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1; background: #E2E8F0;">
                <div id="vdn-preview-sheet" style="background: white; padding: 2rem; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.07); min-height: 700px; font-family: 'Inter', -apple-system, sans-serif; color: #1E293B;">
                    <!-- Rendered A4 Document with watermark if reprint -->
                </div>
            </div>
            <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('view-delivery-note-modal').classList.remove('active')">Փակել</button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="ERP.deliveryNotes.printWindow()">
                        <i class="fa-solid fa-print"></i> Տպել (A4 Browser)
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="ERP.deliveryNotes.triggerReprintJob()">
                        <i class="fa-solid fa-repeat"></i> Ուղարկել Տպիչին (Reprint)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==============================================================
         PHASE 5 MODAL: ADD PRINTER MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="add-printer-modal">
        <div class="modal-container" style="max-width: 580px;">
            <div class="modal-header">
                <div>
                    <h3><i class="fa-solid fa-print" style="color: var(--color-primary); margin-right: 6px;"></i> Գրանցել Նոր Տպիչ</h3>
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">Թերմալ չեկային (ESC/POS) կամ գրասենյակային (A4/A5) տպիչ:</p>
                </div>
                <button type="button" onclick="ERP.printing.closeAddPrinterModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer; background: none; border: none;">&times;</button>
            </div>
            <form onsubmit="ERP.printing.submitPrinter(event)">
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Տպիչի Անվանում *</label>
                            <input type="text" class="form-control" id="ptr-name" required placeholder="Օր. Cash Desk 1 - Thermal 80mm">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Մասնաճյուղ *</label>
                            <select class="select" id="ptr-branch-id" required>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Միացման Ինտերֆեյս *</label>
                            <select class="select" id="ptr-interface" required onchange="ERP.printing.onInterfaceChange()">
                                <option value="network">Network (TCP/IP RAW)</option>
                                <option value="usb">USB Bridge</option>
                                <option value="browser">Browser Native Print</option>
                                <option value="system_driver">OS System Driver / CUPS</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Թղթի Լայնություն / Չափս *</label>
                            <select class="select" id="ptr-paper-width" required>
                                <option value="80mm">80 mm (Standard Thermal)</option>
                                <option value="58mm">58 mm (Compact Thermal)</option>
                                <option value="A4">A4 (Office Sheet)</option>
                                <option value="A5">A5 (Half Sheet)</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">IP Հասցե / Սարքի Ուղի</label>
                            <input type="text" class="form-control font-mono" id="ptr-ip" placeholder="192.168.1.200 կամ /dev/usb/lp0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Port</label>
                            <input type="number" class="form-control font-mono" id="ptr-port" value="9100">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Պրոտոկոլ</label>
                        <select class="select" id="ptr-protocol">
                            <option value="esc_pos">ESC/POS (Epson/Star/Xprinter)</option>
                            <option value="html">HTML / CSS Layout</option>
                            <option value="postscript">PostScript / PDF</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" id="ptr-cut-paper" checked> Ավտոմատ Թղթի Կտրում (Auto Cut)
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" id="ptr-cash-drawer" checked> Բացել Դրամարկղը (Kick Drawer)
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" id="ptr-is-default"> Լռելյայն Տպիչ
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-secondary" onclick="ERP.printing.closeAddPrinterModal()">Չեղարկել</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Պահպանել Տպիչը</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==============================================================
         PHASE 5 MODAL: TEMPLATE PLACEHOLDERS GUIDE MODAL
         ============================================================== -->
    <div class="modal-backdrop" id="designer-placeholders-modal">
        <div class="modal-container" style="max-width: 750px; max-height: 85vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <div>
                    <h3><i class="fa-solid fa-code" style="color: var(--color-primary); margin-right: 6px;"></i> Հասանելի Փոխարինիչներ (Placeholders)</h3>
                    <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                        Անվտանգ տիպիզացված փոխարինիչներ, որոնք ավտոմատ լրացվում են տպագրության պահին:
                    </p>
                </div>
                <button type="button" onclick="ERP.designer.closePlaceholdersModal()" style="font-size: 1.25rem; color: var(--text-muted); cursor: pointer; background: none; border: none;">&times;</button>
            </div>
            <div class="modal-body" style="overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1;">
                <div class="table-responsive">
                    <table class="table" style="font-size: 0.8rem;">
                        <thead>
                            <tr style="background: #F1F5F9;">
                                <th style="width: 240px;">Փոխարինիչ (Placeholder)</th>
                                <th>Նկարագրություն</th>
                                <th style="width: 70px;">Գործողություն</th>
                            </tr>
                        </thead>
                        <tbody id="designer-placeholders-tbody">
                            <!-- Populated dynamically via API / placeholder registry -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="ERP.designer.closePlaceholdersModal()">Փակել</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Pure Vanilla JS Engine -->
    <script src="/js/app.js?v={{ filemtime(public_path('js/app.js')) }}"></script>

</body>
</html>
