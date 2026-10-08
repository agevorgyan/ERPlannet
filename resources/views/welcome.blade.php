<!DOCTYPE html>
<html lang="hy" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $currentTenant ? $currentTenant->name . ' — ERPlannet' : 'ERPlannet SaaS — Multi-Tenant ERP/CRM Platform' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre { font-family: 'JetBrains Mono', monospace; }
        .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-indigo {
            box-shadow: 0 0 35px -8px rgba(99, 102, 241, 0.35);
        }
    </style>
</head>
<body class="bg-[#0B0F19] text-slate-100 min-h-screen selection:bg-brand-500 selection:text-white antialiased">

    <!-- Top Navigation Bar -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-brand-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-lg tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">ERPlannet</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Phase 0, 1, 2 & 3 Complete (65 Tests Passed)</span>
                    </div>
                    <p class="text-xs text-slate-400">Multi-Tenant SaaS ERP/CRM &bull; Manufacturing &bull; Recipes (BOM) &bull; ISO 22000 QA</p>
                </div>
            </div>

            <!-- Environment Badge -->
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex items-center space-x-2 text-xs font-mono bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700/60">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-slate-400">PHP 8.5</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-400">PGSQL 18</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-400">Redis 7</span>
                </div>
                <a href="#api-tester" class="text-xs font-medium px-3.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white transition-all shadow-md shadow-brand-600/30">
                    API Console ↓
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Active Context Banner -->
        @if ($currentTenant)
            <div class="rounded-2xl p-5 bg-gradient-to-r from-emerald-950/60 via-slate-900 to-slate-900 border border-emerald-500/30 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-xl font-bold">
                        🏢
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-lg font-bold text-white">{{ $currentTenant->name }}</h2>
                            <span class="text-xs px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono">Tenant Subdomain Active</span>
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5">
                            Domain: <span class="text-emerald-400 font-mono">{{ request()->getHost() }}</span> &bull;
                            Tax ID: <span class="font-mono">{{ $currentTenant->tax_number ?? 'N/A' }}</span> &bull;
                            Currency: <span class="font-mono">{{ $currentTenant->currency }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="http://localhost:8000" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700">
                        ← Վերադառնալ Platform Console (localhost:8000)
                    </a>
                </div>
            </div>
        @else
            <div class="rounded-2xl p-5 bg-gradient-to-r from-brand-950/40 via-slate-900 to-slate-900 border border-brand-500/30 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/20 border border-brand-500/30 flex items-center justify-center text-brand-400 text-xl font-bold">
                        🌐
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-lg font-bold text-white">SaaS Platform Root & Admin Portal</h2>
                            <span class="text-xs px-2 py-0.5 rounded bg-brand-500/20 text-brand-300 font-mono">http://localhost:8000</span>
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5">
                            Այստեղից կառավարվում են բոլոր ընկերությունները (Tenants), սակագնային պլանները (Plans) և վճարումները։
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="http://gourmet.localhost:8000" class="text-xs px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition shadow-md shadow-emerald-600/30">
                        Մուտք Demo Ընկերություն (gourmet.localhost) →
                    </a>
                </div>
            </div>
        @endif

        <!-- Quick Access & Domain Routing Instructions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-200 flex items-center space-x-2">
                        <span>🌐</span>
                        <span>1. Platform SuperAdmin</span>
                    </h3>
                    <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300">Root Guard</span>
                </div>
                <p class="text-xs text-slate-400 mb-3">
                    Կառավարման հիմնական մուտքը՝ առանց ընկերության սահմանափակման։
                </p>
                <div class="bg-slate-950/60 rounded-xl p-3 border border-slate-800/80 space-y-1.5 text-xs font-mono">
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="text-slate-500">URL:</span>
                        <a href="http://localhost:8000" class="text-brand-400 hover:underline">http://localhost:8000</a>
                    </div>
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="text-slate-500">Email:</span>
                        <span class="text-slate-200">admin@erplannet.com</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="text-slate-500">Pass:</span>
                        <span class="text-slate-200">SuperSecurePass123!</span>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-200 flex items-center space-x-2">
                        <span>🏢</span>
                        <span>2. Demo Tenant (Gourmet)</span>
                    </h3>
                    <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300">Subdomain</span>
                </div>
                <p class="text-xs text-slate-400 mb-3">
                    Սննդի արտադրության «Armenia Gourmet Food» ընկերություն։
                </p>
                <div class="bg-slate-950/60 rounded-xl p-3 border border-slate-800/80 space-y-1.5 text-xs font-mono">
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="text-slate-500">Subdomain:</span>
                        <a href="http://gourmet.localhost:8000" class="text-emerald-400 hover:underline">gourmet.localhost:8000</a>
                    </div>
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="text-slate-500">Owner:</span>
                        <span class="text-slate-200">aram@gourmet.am</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="text-slate-500">Pass:</span>
                        <span class="text-slate-200">password123</span>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-200 flex items-center space-x-2">
                        <span>⚡</span>
                        <span>3. Subdomain Routing</span>
                    </h3>
                    <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-amber-500/20 text-amber-300">Auto RFC 6761</span>
                </div>
                <p class="text-xs text-slate-400 mb-3">
                    Chrome, Safari և Firefox բրաուզերները <code class="text-amber-300">*.localhost</code>-ը ավտոմատ ուղղում են դեպի <code class="text-amber-300">127.0.0.1</code>։
                </p>
                <div class="bg-slate-950/60 rounded-xl p-3 border border-slate-800/80 text-xs text-slate-400">
                    <p>Postman/cURL-ի դեպքում կարող եք պարզապես ուղարկել header՝</p>
                    <code class="text-emerald-400 font-mono text-[11px] block mt-1">X-Tenant-Slug: gourmet</code>
                </div>
            </div>
        </div>

        <!-- Live Platform Stats (Phase 0, 1, 2 & 3) -->
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-3">
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-white">{{ $stats['tenants_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">Tenants</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-indigo-400">{{ $stats['branches_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">Branches</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-cyan-400">{{ $stats['warehouses_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">Warehouses</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-teal-400">{{ $stats['suppliers_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">Suppliers</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-purple-400">{{ $stats['products_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">Products</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-amber-400">{{ $stats['recipes_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">Recipes (BOM)</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-blue-400">{{ $stats['production_orders_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">Production</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-emerald-400">{{ $stats['quality_inspections_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">ISO 22000 QA</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-orange-400">{{ $stats['orders_count'] }}</span>
                <span class="block text-[11px] text-slate-400 mt-1">Sales Orders</span>
            </div>
            <div class="glass-panel p-3.5 rounded-xl text-center">
                <span class="text-xl font-bold text-rose-400">{{ number_format($stats['total_revenue'], 0) }} ֏</span>
                <span class="block text-[11px] text-slate-400 mt-1">Revenue</span>
            </div>
        </div>

        <!-- Phase 2: Warehouses & Stock Batches Showcase -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Warehouses Showcase -->
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-white flex items-center space-x-2">
                            <span>🏭</span>
                            <span>Պահեստներ (Warehouses — Phase 2)</span>
                        </h3>
                        <p class="text-xs text-slate-400">Կենտրոնական և Սառնարանային պահեստներ</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 font-mono">{{ $warehouses->count() }} warehouses</span>
                </div>

                <div class="space-y-3">
                    @foreach ($warehouses as $wh)
                        <div class="p-3.5 rounded-xl bg-slate-950/50 border border-slate-800/80 flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-cyan-400">{{ $wh->code }}</span>
                                    <span class="text-slate-200 font-medium">{{ $wh->name }}</span>
                                    @if ($wh->is_default)
                                        <span class="px-2 py-0.5 rounded bg-brand-500/20 text-brand-300 text-[10px]">Default</span>
                                    @endif
                                    @if ($wh->type === 'cold_storage')
                                        <span class="px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 text-[10px]">Cold Storage</span>
                                    @endif
                                </div>
                                <div class="text-slate-400">
                                    Հասցե՝ <span class="text-slate-300">{{ $wh->address ?? 'N/A' }}</span> &bull;
                                    Մասնաճյուղ՝ <span class="text-slate-300">{{ $wh->branch?->name }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-1 rounded bg-emerald-500/10 text-emerald-400 font-mono text-[11px]">Active</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Stock Batches & Lots -->
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-white flex items-center space-x-2">
                            <span>🏷️</span>
                            <span>Խմբաքանակներ & Ժամկետներ (Batches & Expiry)</span>
                        </h3>
                        <p class="text-xs text-slate-400">Lot Numbering, Manufacturing & Expiry Tracking</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 font-mono">{{ $batches->count() }} batches</span>
                </div>

                <div class="space-y-3">
                    @foreach ($batches as $batch)
                        <div class="p-3.5 rounded-xl bg-slate-950/50 border border-slate-800/80 flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-emerald-400">{{ $batch->batch_number }}</span>
                                    <span class="text-slate-200">{{ is_array($batch->product->name) ? ($batch->product->name['hy'] ?? reset($batch->product->name)) : $batch->product->name }}</span>
                                </div>
                                <div class="text-slate-400 text-[11px]">
                                    Պիտանի է մինչև՝ <span class="text-amber-400 font-mono">{{ $batch->expiry_date }}</span> &bull;
                                    Պահեստ՝ <span class="text-slate-300">{{ $batch->warehouse?->code }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-mono font-bold text-white block">{{ number_format($batch->quantity_on_hand, 2) }} կգ</span>
                                <span class="text-[10px] text-slate-400 font-mono">Ինքնարժեք: {{ number_format($batch->cost_price, 0) }} ֏</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Phase 2: Suppliers & Purchase Orders Showcase -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Suppliers -->
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-white flex items-center space-x-2">
                            <span>🤝</span>
                            <span>Մատակարարներ (Suppliers — Phase 2)</span>
                        </h3>
                        <p class="text-xs text-slate-400">B2B Vendors, ՀՎՀՀ և վճարման պայմաններ</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 font-mono">{{ $suppliers->count() }} suppliers</span>
                </div>

                <div class="space-y-3">
                    @foreach ($suppliers as $sup)
                        <div class="p-3.5 rounded-xl bg-slate-950/50 border border-slate-800/80 flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-white">{{ $sup->company_name }}</span>
                                    <span class="font-mono text-slate-400 text-[11px]">(ՀՎՀՀ: {{ $sup->tax_id }})</span>
                                </div>
                                <div class="text-slate-400 text-[11px]">
                                    Կոնտակտ՝ <span class="text-slate-300">{{ $sup->contact_person }}</span> &bull;
                                    Հեռ՝ <span class="text-slate-300">{{ $sup->phone }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono text-[10px]">{{ $sup->payment_terms_days }} օր վճ. ժամկետ</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Purchase Orders -->
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-white flex items-center space-x-2">
                            <span>📑</span>
                            <span>Գնումների Պատվերներ (Purchase Orders)</span>
                        </h3>
                        <p class="text-xs text-slate-400">PO Lifecycle: Draft &rarr; Ordered &rarr; Goods Received</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 font-mono">{{ $purchaseOrders->count() }} POs</span>
                </div>

                <div class="space-y-3">
                    @foreach ($purchaseOrders as $po)
                        <div class="p-3.5 rounded-xl bg-slate-950/50 border border-slate-800/80 flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-brand-400">{{ $po->po_number }}</span>
                                    <span class="text-slate-300">{{ $po->supplier?->company_name }}</span>
                                    @if ($po->status === 'received')
                                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-medium">Received</span>
                                    @elseif ($po->status === 'ordered')
                                        <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-medium">Ordered</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-medium">{{ $po->status }}</span>
                                    @endif
                                </div>
                                <div class="text-slate-400 text-[11px]">
                                    Պահեստ՝ <span class="text-slate-300">{{ $po->warehouse?->name }}</span> &bull;
                                    Տողեր՝ {{ $po->items->count() }} ապրանք
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-mono font-bold text-emerald-400 text-sm">{{ number_format($po->total, 0) }} ֏</span>
                                <span class="text-[10px] text-slate-400 uppercase font-mono block">{{ $po->payment_status }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Phase 3: Manufacturing, Recipes (BOM) & ISO 22000 Quality Assurance Showcase -->
        <div class="space-y-6">
            <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                        <span>🏭</span>
                        <span>Phase 3: Արտադրություն, Բաղադրատոմսեր (BOM) & ISO 22000 Որակ</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Տեխնոլոգիական քարտեր, բաղադրիչների մասշտաբավորում, արտադրության ցիկլ և HACCP / CCP հսկողություն
                    </p>
                </div>
                <span class="text-xs font-mono px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    Manufacturing &amp; QA Live
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- 1. Recipes & BOM -->
                <div class="glass-panel rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-white flex items-center space-x-2">
                                <span>📜</span>
                                <span>Բաղադրատոմսեր (BOM)</span>
                            </h4>
                            <p class="text-xs text-slate-400">Տեխնոլոգիական քարտեր ({{ $recipes->count() }})</p>
                        </div>
                        <span class="text-xs font-mono px-2 py-0.5 rounded bg-amber-500/10 text-amber-300 border border-amber-500/20">Recipes</span>
                    </div>

                    <div class="space-y-3">
                        @foreach ($recipes as $rcp)
                            <div class="p-3.5 rounded-xl bg-slate-900/70 border border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs font-bold text-amber-400">{{ $rcp->code }}</span>
                                    <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                                        Ելք՝ {{ number_format($rcp->yield_quantity, 0) }} {{ $rcp->yieldUnit?->symbol ?? 'միավոր' }}
                                    </span>
                                </div>
                                <h5 class="text-xs font-semibold text-white">{{ $rcp->name }}</h5>
                                <div class="text-[11px] text-slate-400 space-y-1">
                                    <div class="flex justify-between text-slate-400">
                                        <span>Աշխատավարձ / Վերադիր՝</span>
                                        <span class="font-mono text-slate-300">{{ number_format($rcp->labor_cost, 0) }} / {{ number_format($rcp->overhead_cost, 0) }} ֏</span>
                                    </div>
                                    <div class="pt-1 border-t border-slate-800/80 text-[10px] text-slate-400">
                                        <span class="text-amber-300/80 font-medium">Հումք՝</span>
                                        @foreach ($rcp->items as $item)
                                            <span class="inline-block px-1.5 py-0.5 bg-slate-800 rounded mr-1 mb-1">
                                                {{ is_array($item->product?->name) ? ($item->product->name['hy'] ?? '') : $item->product?->name }}: {{ $item->quantity }}կգ
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Production Orders -->
                <div class="glass-panel rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-white flex items-center space-x-2">
                                <span>⚙️</span>
                                <span>Արտադրական Պատվերներ</span>
                            </h4>
                            <p class="text-xs text-slate-400">Production Orders ({{ $productionOrders->count() }})</p>
                        </div>
                        <span class="text-xs font-mono px-2 py-0.5 rounded bg-blue-500/10 text-blue-300 border border-blue-500/20">Lifecycle</span>
                    </div>

                    <div class="space-y-3">
                        @foreach ($productionOrders as $po)
                            <div class="p-3.5 rounded-xl bg-slate-900/70 border border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs font-bold text-blue-400">{{ $po->order_number }}</span>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $po->status === 'completed' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                        {{ strtoupper($po->status) }}
                                    </span>
                                </div>
                                <h5 class="text-xs font-medium text-white">
                                    {{ is_array($po->product?->name) ? ($po->product->name['hy'] ?? '') : $po->product?->name }}
                                </h5>
                                <div class="text-[11px] text-slate-400 space-y-1">
                                    <div class="flex justify-between">
                                        <span>Պլանավորված / Փաստացի՝</span>
                                        <span class="font-mono text-slate-200">
                                            {{ number_format($po->planned_quantity, 0) }} / {{ number_format($po->actual_quantity, 0) }} հատ
                                        </span>
                                    </div>
                                    @if ($po->batch)
                                        <div class="flex justify-between text-emerald-400 font-mono text-[10px]">
                                            <span>Թողարկված Խմբաքանակ՝</span>
                                            <span>{{ $po->batch->batch_number }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Quality Assurance & ISO 22000 Inspections -->
                <div class="glass-panel rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-white flex items-center space-x-2">
                                <span>🛡️</span>
                                <span>ISO 22000 / HACCP Որակ</span>
                            </h4>
                            <p class="text-xs text-slate-400">Quality Inspections ({{ $qualityInspections->count() }})</p>
                        </div>
                        <span class="text-xs font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">Certified</span>
                    </div>

                    <div class="space-y-3">
                        @foreach ($qualityInspections as $qa)
                            <div class="p-3.5 rounded-xl bg-slate-900/70 border border-emerald-500/30 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs font-bold text-emerald-400">{{ $qa->inspection_number }}</span>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold">
                                        {{ $qa->overall_score }}% PASSED
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-300 font-medium">
                                    Ստանդարտ՝ <span class="text-white">{{ $qa->standard_applied }}</span> &bull;
                                    Պատվեր՝ <span class="text-blue-300 font-mono">{{ $qa->productionOrder?->order_number }}</span>
                                </div>

                                <!-- CCP Points breakdown -->
                                <div class="space-y-1.5 pt-1 border-t border-slate-800 text-[10px]">
                                    @foreach ($qa->items as $item)
                                        <div class="p-1.5 rounded bg-slate-800/80 flex items-center justify-between">
                                            <div>
                                                <span class="font-mono font-bold text-amber-400 mr-1">{{ $item->critical_control_point }}</span>
                                                <span class="text-slate-300">{{ $item->parameter_name }}</span>
                                            </div>
                                            <div class="flex items-center space-x-1.5 font-mono">
                                                <span class="text-slate-200">{{ $item->actual_value }}{{ $item->unit }}</span>
                                                <span class="text-emerald-400 font-bold">✓</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <!-- Interactive API Console (Live In-Browser Testing) -->
        <section id="api-tester" class="glass-panel rounded-2xl p-6 lg:p-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                        <span>🧪</span>
                        <span>Interactive In-Browser API Console</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Phase 0, 1, 2 & 3 API-ների կենդանի թեստավորում հենց բրաուզերում։
                    </p>
                </div>
                <div class="flex items-center space-x-2" id="token-status-badge">
                    <span class="text-xs font-mono px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 border border-slate-700">
                        Token: <span id="active-token-label">None</span>
                    </span>
                </div>
            </div>

            <!-- Action Buttons Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <button onclick="loginPlatformAdmin()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-indigo-500/30 hover:border-indigo-500 transition group">
                    <span class="text-xs font-semibold text-indigo-300 block mb-1">1. Admin Login</span>
                    <p class="text-[11px] text-slate-400">admin@erplannet</p>
                </button>

                <button onclick="loginTenantOwner()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-emerald-500/30 hover:border-emerald-500 transition group">
                    <span class="text-xs font-semibold text-emerald-300 block mb-1">2. Tenant Login</span>
                    <p class="text-[11px] text-slate-400">aram@gourmet.am</p>
                </button>

                <button onclick="fetchWarehouses()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-cyan-300 block mb-1">3. Warehouses</span>
                    <p class="text-[11px] text-slate-400">/api/v1/warehouses</p>
                </button>

                <button onclick="fetchStockLevels()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-slate-200 block mb-1">4. Stock Levels</span>
                    <p class="text-[11px] text-slate-400">/inventory/levels</p>
                </button>

                <button onclick="fetchBatches()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-slate-200 block mb-1">5. Stock Batches</span>
                    <p class="text-[11px] text-slate-400">/inventory/batches</p>
                </button>

                <button onclick="fetchSuppliers()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-teal-300 block mb-1">6. Suppliers</span>
                    <p class="text-[11px] text-slate-400">/api/v1/suppliers</p>
                </button>

                <button onclick="fetchPurchaseOrders()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-emerald-300 block mb-1">7. Purchase Orders</span>
                    <p class="text-[11px] text-slate-400">/purchase-orders</p>
                </button>

                <button onclick="fetchRecipes()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-amber-500/30 hover:border-amber-500 transition group">
                    <span class="text-xs font-semibold text-amber-300 block mb-1">8. Recipes (BOM)</span>
                    <p class="text-[11px] text-slate-400">/api/v1/recipes</p>
                </button>

                <button onclick="fetchProductionOrders()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-blue-500/30 hover:border-blue-500 transition group">
                    <span class="text-xs font-semibold text-blue-300 block mb-1">9. Production Orders</span>
                    <p class="text-[11px] text-slate-400">/production-orders</p>
                </button>

                <button onclick="fetchQualityInspections()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-emerald-500/30 hover:border-emerald-500 transition group">
                    <span class="text-xs font-semibold text-emerald-300 block mb-1">10. ISO 22000 QA</span>
                    <p class="text-[11px] text-slate-400">/quality-inspections</p>
                </button>

                <button onclick="fetchTenantProducts()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-slate-200 block mb-1">11. Products</span>
                    <p class="text-[11px] text-slate-400">/api/v1/products</p>
                </button>

                <button onclick="fetchTenantOrders()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-slate-200 block mb-1">12. Sales Orders</span>
                    <p class="text-[11px] text-slate-400">/api/v1/orders</p>
                </button>

                <button onclick="fetchSubscription()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-slate-200 block mb-1">13. Entitlements</span>
                    <p class="text-[11px] text-slate-400">/api/v1/subscription</p>
                </button>

                <button onclick="fetchTranslations('hy')" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-slate-200 block mb-1">14. Հայերեն UI</span>
                    <p class="text-[11px] text-slate-400">/translations/hy</p>
                </button>

                <button onclick="testHealthCheck()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <span class="text-xs font-semibold text-slate-200 block mb-1">15. Health Check</span>
                    <p class="text-[11px] text-slate-400">/api/v1/health</p>
                </button>
            </div>

            <!-- Response Console Output -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <div class="flex items-center space-x-2 font-mono">
                        <span id="response-method" class="text-indigo-400 font-bold">READY</span>
                        <span id="response-url" class="text-slate-300">Click any action above to execute live request</span>
                    </div>
                    <div class="flex items-center space-x-3 font-mono">
                        <span id="response-status" class="text-slate-500">-</span>
                        <span id="response-time" class="text-slate-500">- ms</span>
                    </div>
                </div>
                <div class="relative">
                    <pre id="response-output" class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono text-emerald-300 max-h-96 overflow-y-auto whitespace-pre-wrap">{ "info": "ERPlannet Multi-Tenant API (Phase 0, 1 & 2) Ready. Click any button above." }</pre>
                </div>
            </div>
        </section>

    </main>

    <footer class="border-t border-slate-800/60 mt-12 py-6 text-center text-xs text-slate-500">
        ERPlannet Multi-Tenant SaaS Platform &bull; Phase 0 (Foundation), Phase 1 (Business Core) & Phase 2 (Warehouse & Procurement) &bull; Yerevan, Armenia
    </footer>

    <!-- JavaScript for Live Interactive API Testing -->
    <script>
        let currentToken = null;
        let currentAuthType = null;

        function setConsoleStatus(method, url, status, time, data) {
            document.getElementById('response-method').innerText = method;
            document.getElementById('response-url').innerText = url;
            document.getElementById('response-status').innerText = 'HTTP ' + status;
            document.getElementById('response-status').className = status >= 200 && status < 300 ? 'text-emerald-400 font-bold' : 'text-rose-400 font-bold';
            document.getElementById('response-time').innerText = time + ' ms';
            document.getElementById('response-output').innerText = JSON.stringify(data, null, 2);
        }

        async function makeRequest(method, url, headers = {}, body = null) {
            const start = performance.now();
            try {
                const options = {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        ...headers
                    }
                };
                if (body) {
                    options.body = JSON.stringify(body);
                }
                const res = await fetch(url, options);
                const time = Math.round(performance.now() - start);
                const data = await res.json();
                setConsoleStatus(method, url, res.status, time, data);
                return { status: res.status, data };
            } catch (err) {
                const time = Math.round(performance.now() - start);
                setConsoleStatus(method, url, 500, time, { error: err.message });
            }
        }

        async function loginPlatformAdmin() {
            const res = await makeRequest('POST', '/api/v1/platform/auth/login', {}, {
                email: 'admin@erplannet.com',
                password: 'SuperSecurePass123!'
            });
            if (res && res.data && res.data.token) {
                currentToken = res.data.token;
                currentAuthType = 'platform';
                document.getElementById('active-token-label').innerText = 'Platform Admin (admin@erplannet.com)';
                document.getElementById('active-token-label').className = 'text-indigo-400 font-semibold';
            }
        }

        async function loginTenantOwner() {
            const res = await makeRequest('POST', '/api/v1/auth/login', {
                'X-Tenant-Slug': 'gourmet'
            }, {
                email: 'aram@gourmet.am',
                password: 'password123'
            });
            if (res && res.data && res.data.token) {
                currentToken = res.data.token;
                currentAuthType = 'tenant';
                document.getElementById('active-token-label').innerText = 'Tenant Owner (aram@gourmet.am)';
                document.getElementById('active-token-label').className = 'text-emerald-400 font-semibold';
            }
        }

        async function ensureTenantAuth() {
            if (!currentToken || currentAuthType !== 'tenant') {
                await loginTenantOwner();
            }
        }

        async function fetchWarehouses() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/warehouses', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchStockLevels() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/inventory/levels', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchBatches() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/inventory/batches', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchSuppliers() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/suppliers', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchPurchaseOrders() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/purchase-orders', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchRecipes() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/recipes', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchProductionOrders() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/production-orders', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchQualityInspections() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/quality-inspections', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchTenantProducts() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/products', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchTenantOrders() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/orders', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchSubscription() {
            await ensureTenantAuth();
            await makeRequest('GET', '/api/v1/subscription', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchTranslations(locale) {
            await makeRequest('GET', '/api/v1/translations/' + locale);
        }

        async function testHealthCheck() {
            await makeRequest('GET', '/api/v1/health');
        }
    </script>
</body>
</html>
