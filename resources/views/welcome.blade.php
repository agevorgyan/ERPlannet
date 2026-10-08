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
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Phase 0 & 1 Ready</span>
                    </div>
                    <p class="text-xs text-slate-400">Multi-Tenant SaaS ERP/CRM Platform</p>
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
                    Chrome, Safari և Firefox բրաուզերները <code class="text-amber-300">*.localhost</code>-ը ավտոմատ ուղղում են դեպի <code class="text-amber-300">127.0.0.1</code>՝ առանց <code class="text-slate-300">/etc/hosts</code>-ը փոխելու։
                </p>
                <div class="bg-slate-950/60 rounded-xl p-3 border border-slate-800/80 text-xs text-slate-400">
                    <p>Postman/cURL-ի դեպքում կարող եք պարզապես ուղարկել header՝</p>
                    <code class="text-emerald-400 font-mono text-[11px] block mt-1">X-Tenant-Slug: gourmet</code>
                </div>
            </div>
        </div>

        <!-- Live Platform Stats -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="glass-panel p-4 rounded-xl text-center">
                <span class="text-2xl font-bold text-white">{{ $stats['tenants_count'] }}</span>
                <span class="block text-xs text-slate-400 mt-1">Tenants</span>
            </div>
            <div class="glass-panel p-4 rounded-xl text-center">
                <span class="text-2xl font-bold text-indigo-400">{{ $stats['plans_count'] }}</span>
                <span class="block text-xs text-slate-400 mt-1">Plans</span>
            </div>
            <div class="glass-panel p-4 rounded-xl text-center">
                <span class="text-2xl font-bold text-emerald-400">{{ $stats['branches_count'] }}</span>
                <span class="block text-xs text-slate-400 mt-1">Branches</span>
            </div>
            <div class="glass-panel p-4 rounded-xl text-center">
                <span class="text-2xl font-bold text-purple-400">{{ $stats['products_count'] }}</span>
                <span class="block text-xs text-slate-400 mt-1">Products</span>
            </div>
            <div class="glass-panel p-4 rounded-xl text-center">
                <span class="text-2xl font-bold text-amber-400">{{ $stats['orders_count'] }}</span>
                <span class="block text-xs text-slate-400 mt-1">Orders</span>
            </div>
            <div class="glass-panel p-4 rounded-xl text-center">
                <span class="text-2xl font-bold text-teal-400">{{ number_format($stats['total_revenue'], 0) }} ֏</span>
                <span class="block text-xs text-slate-400 mt-1">Revenue</span>
            </div>
        </div>

        <!-- Live Seeded Data Showcase (Gourmet Tenant) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Products Catalog -->
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-white flex items-center space-x-2">
                            <span>🥩</span>
                            <span>Ապրանքների Կատալոգ (Gourmet Tenant)</span>
                        </h3>
                        <p class="text-xs text-slate-400">Multi-lingual JSONB անվանումներ, չափման միավորներ և ինքնարժեք/վաճառք</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 font-mono">{{ $products->count() }} items</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-medium">
                                <th class="pb-2">Ապրանք</th>
                                <th class="pb-2">SKU</th>
                                <th class="pb-2">Միավոր</th>
                                <th class="pb-2">Ինքնարժեք</th>
                                <th class="pb-2 text-right">Գին</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-mono">
                            @foreach ($products as $p)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-2.5 font-sans font-medium text-slate-200">
                                        {{ is_array($p->name) ? ($p->name['hy'] ?? reset($p->name)) : $p->name }}
                                        @if($p->is_produced)
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 font-sans">Արտադրություն</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 text-slate-400">{{ $p->sku }}</td>
                                    <td class="py-2.5 text-slate-300">{{ $p->unit?->code ?? '-' }}</td>
                                    <td class="py-2.5 text-slate-400">{{ number_format($p->cost_price, 0) }} ֏</td>
                                    <td class="py-2.5 text-right font-bold text-emerald-400">{{ number_format($p->sale_price, 0) }} ֏</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Orders & Branches -->
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-white flex items-center space-x-2">
                            <span>📦</span>
                            <span>Պատվերներ & Մասնաճյուղեր</span>
                        </h3>
                        <p class="text-xs text-slate-400">Sequential Order Numbering & State Machine Transitions</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 font-mono">{{ $orders->count() }} orders</span>
                </div>

                <div class="space-y-3">
                    @foreach ($orders as $order)
                        <div class="p-3.5 rounded-xl bg-slate-950/50 border border-slate-800/80 flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-brand-400">{{ $order->order_number }}</span>
                                    <span class="text-slate-400 font-sans">({{ $order->branch?->name ?? 'Main' }})</span>
                                    @if ($order->status === 'delivered')
                                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-medium">Delivered</span>
                                    @elseif ($order->status === 'processing')
                                        <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-medium">Processing</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-medium">{{ $order->status }}</span>
                                    @endif
                                </div>
                                <div class="text-slate-400">
                                    Հաճախորդ՝ <span class="text-slate-300">{{ $order->customer?->first_name }} {{ $order->customer?->last_name }}</span> &bull;
                                    Տողեր՝ {{ $order->items->count() }} ապրանք
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-emerald-400 text-sm">
                                    {{ number_format($order->total, 0) }} ֏
                                </div>
                                <span class="text-[11px] text-slate-400 uppercase font-mono">{{ $order->payment_status }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Branches List -->
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                    <span>Մասնաճյուղեր ({{ $branches->count() }}):</span>
                    <div class="flex space-x-2">
                        @foreach ($branches as $b)
                            <span class="px-2 py-1 rounded bg-slate-800/80 text-slate-300 font-mono">
                                {{ $b->name }} ({{ $b->code }})
                            </span>
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
                        Կտտացրեք կոճակներին՝ կենդանի API հարցումներ կատարելու և պատասխանները տեսնելու համար։
                    </p>
                </div>
                <div class="flex items-center space-x-2" id="token-status-badge">
                    <span class="text-xs font-mono px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 border border-slate-700">
                        Token: <span id="active-token-label">None</span>
                    </span>
                </div>
            </div>

            <!-- Action Buttons Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <button onclick="loginPlatformAdmin()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-indigo-500/30 hover:border-indigo-500 transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-indigo-300">1. Platform Admin Login</span>
                        <span class="text-[10px] font-mono text-slate-400">POST</span>
                    </div>
                    <p class="text-[11px] text-slate-400">admin@erplannet.com</p>
                </button>

                <button onclick="loginTenantOwner()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-emerald-500/30 hover:border-emerald-500 transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-emerald-300">2. Tenant Owner Login</span>
                        <span class="text-[10px] font-mono text-slate-400">POST</span>
                    </div>
                    <p class="text-[11px] text-slate-400">aram@gourmet.am (gourmet)</p>
                </button>

                <button onclick="fetchTenantProducts()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-slate-200">3. Get Products</span>
                        <span class="text-[10px] font-mono text-slate-400">GET</span>
                    </div>
                    <p class="text-[11px] text-slate-400">/api/v1/products</p>
                </button>

                <button onclick="fetchTenantOrders()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-slate-200">4. Get Orders</span>
                        <span class="text-[10px] font-mono text-slate-400">GET</span>
                    </div>
                    <p class="text-[11px] text-slate-400">/api/v1/orders</p>
                </button>

                <button onclick="fetchSubscription()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-slate-200">5. Entitlement Quotas</span>
                        <span class="text-[10px] font-mono text-slate-400">GET</span>
                    </div>
                    <p class="text-[11px] text-slate-400">/api/v1/subscription</p>
                </button>

                <button onclick="fetchPlatformTenants()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-slate-200">6. Platform Tenants</span>
                        <span class="text-[10px] font-mono text-slate-400">GET</span>
                    </div>
                    <p class="text-[11px] text-slate-400">/api/v1/platform/tenants</p>
                </button>

                <button onclick="fetchTranslations('hy')" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-slate-200">7. Հայերեն Թարգմանություններ</span>
                        <span class="text-[10px] font-mono text-slate-400">GET</span>
                    </div>
                    <p class="text-[11px] text-slate-400">/api/v1/translations/hy</p>
                </button>

                <button onclick="testHealthCheck()" class="p-3 text-left rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-slate-500 transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-slate-200">8. Health Check</span>
                        <span class="text-[10px] font-mono text-slate-400">GET</span>
                    </div>
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
                    <pre id="response-output" class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono text-emerald-300 max-h-96 overflow-y-auto whitespace-pre-wrap">{ "info": "ERPlannet Multi-Tenant API Ready. Click any button above." }</pre>
                </div>
            </div>
        </section>

        <!-- Command Line / Developers Cheatsheet -->
        <section class="glass-panel rounded-2xl p-6 space-y-4">
            <h3 class="text-base font-semibold text-white flex items-center space-x-2">
                <span>💻</span>
                <span>Հրամաններ և Postman/cURL ուղեցույց</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 space-y-2">
                    <span class="text-slate-400 font-sans font-medium block">1. Լոկալ սերվերի գործարկում (Terminal)</span>
                    <p class="text-slate-300">php artisan serve --port=8000</p>
                    <p class="text-slate-500 font-sans text-[11px]">կամ Docker Compose-ով՝ docker compose up -d</p>
                </div>

                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 space-y-2">
                    <span class="text-slate-400 font-sans font-medium block">2. Բոլոր 40 թեստերի գործարկում</span>
                    <p class="text-emerald-400">php artisan test</p>
                    <p class="text-slate-500 font-sans text-[11px]">100% ծածկույթ PostgreSQL-ի վրա</p>
                </div>

                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 space-y-2 md:col-span-2">
                    <span class="text-slate-400 font-sans font-medium block">3. cURL հարցում Tenant-ի տվյալները ստանալու համար</span>
                    <p class="text-slate-300 whitespace-pre-wrap">curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "X-Tenant-Slug: gourmet" \
  -H "Content-Type: application/json" \
  -d '{"email":"aram@gourmet.am","password":"password123"}'</p>
                </div>
            </div>
        </section>

    </main>

    <footer class="border-t border-slate-800/60 mt-12 py-6 text-center text-xs text-slate-500">
        ERPlannet Multi-Tenant SaaS Platform &bull; Built with Laravel 13, PostgreSQL 18 & Redis &bull; Yerevan, Armenia
    </footer>

    <!-- JavaScript for Live Interactive API Testing -->
    <script>
        let currentToken = null;
        let currentAuthType = null; // 'platform' or 'tenant'

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

        async function fetchTenantProducts() {
            if (!currentToken || currentAuthType !== 'tenant') {
                await loginTenantOwner();
            }
            await makeRequest('GET', '/api/v1/products', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchTenantOrders() {
            if (!currentToken || currentAuthType !== 'tenant') {
                await loginTenantOwner();
            }
            await makeRequest('GET', '/api/v1/orders', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchSubscription() {
            if (!currentToken || currentAuthType !== 'tenant') {
                await loginTenantOwner();
            }
            await makeRequest('GET', '/api/v1/subscription', {
                'X-Tenant-Slug': 'gourmet',
                'Authorization': 'Bearer ' + currentToken
            });
        }

        async function fetchPlatformTenants() {
            if (!currentToken || currentAuthType !== 'platform') {
                await loginPlatformAdmin();
            }
            await makeRequest('GET', '/api/v1/platform/tenants', {
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
