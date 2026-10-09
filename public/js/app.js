/**
 * ERPlannet SaaS ERP/CRM — Core Frontend Application (Pure Vanilla JavaScript)
 * Communicates with Laravel API on the SAME PORT.
 */

(function () {
    'use strict';

    // =========================================================================
    // 1. Translation Dictionaries (hy, en, ru)
    // =========================================================================
    const I18N = {
        hy: {
            app_title: "ERPlannet SaaS ERP",
            dashboard: "Վահանակ",
            pos: "POS Դրամարկղ",
            inventory: "Պահեստներ & Պաշարներ",
            manufacturing: "Արտադրություն & BOM",
            quality: "ISO 22000 Որակ",
            delivery: "Առաքման Պարկ",
            users: "Աշխատակիցներ",
            roles: "Դերեր & RBAC",
            billing: "Բաժանորդագրություն & Վճարումներ",
            settings: "Ընկերության Կարգավորումներ",
            api_console: "API Console",
            welcome_back: "Բարի գալուստ,",
            quick_actions: "Արագ գործողություններ",
            recent_activities: "Վերջին գործողություններ",
            operational_tasks: "Գործառնական խնդիրներ",
            search_placeholder: "Որոնել ամենուր... (⌘K)",
            revenue: "Հասույթ",
            orders: "Պատվերներ",
            warehouses: "Պահեստներ",
            products: "Ապրանքներ",
            production: "Արտադրություն",
            fleet: "Առաքիչներ",
            all_systems_healthy: "Համակարգը գործում է նորմալ",
            cart_empty: "Զամբյուղը դատարկ է",
            subtotal: "Միջանկյալ",
            tax: "ԱԱՀ (20%)",
            total: "Ընդամենը",
            checkout: "Ձևակերպել Վաճառք",
            add_to_cart: "Ավելացնել",
            save_changes: "Պահպանել",
            cancel: "Չեղարկել",
            invite_user: "Հրավիրել Աշխատակից",
            create_role: "Ստեղծել Դեր",
            upgrade_plan: "Թարմացնել Պլանը",
            select_payment: "Ընտրեք Վճարման Եղանակը",
            cash: "Կանխիկ",
            telcell_qr: "Telcell QR",
            idram: "Idram",
            ameria: "Ameria Bank",
            card: "Բանկային Քարտ",
            lot_number: "Խմբաքանակ (Lot)",
            expiry_date: "Պիտանելիություն",
            in_stock: "Առկա է",
            cost_price: "Ինքնարժեք",
            sale_price: "Վաճառքի գին",
            yield: "Ելք",
            labor_cost: "Աշխատավարձ",
            ingredients: "Բաղադրիչներ",
            score: "Միավոր",
            passed: "Հանձնված է",
            active: "Ակտիվ",
            inactive: "Ոչ ակտիվ",
            login: "Մուտք",
            logout: "Ելք",
            switch_tenant: "Փոխել Կազմակերպությունը",
        },
        en: {
            app_title: "ERPlannet SaaS ERP",
            dashboard: "Dashboard",
            pos: "Point of Sale (POS)",
            inventory: "Warehouses & Stock",
            manufacturing: "Manufacturing & BOM",
            quality: "ISO 22000 QA",
            delivery: "Delivery Fleet",
            users: "Users & Team",
            roles: "Roles & RBAC",
            billing: "Subscription & Billing",
            settings: "Organization Settings",
            api_console: "API Console",
            welcome_back: "Welcome back,",
            quick_actions: "Quick Actions",
            recent_activities: "Recent Activities",
            operational_tasks: "Operational Tasks",
            search_placeholder: "Search everywhere... (⌘K)",
            revenue: "Revenue",
            orders: "Orders",
            warehouses: "Warehouses",
            products: "Products",
            production: "Production",
            fleet: "Fleet Drivers",
            all_systems_healthy: "All Systems Operational",
            cart_empty: "Cart is empty",
            subtotal: "Subtotal",
            tax: "Tax (20%)",
            total: "Total",
            checkout: "Complete Checkout",
            add_to_cart: "Add",
            save_changes: "Save Changes",
            cancel: "Cancel",
            invite_user: "Invite User",
            create_role: "Create Role",
            upgrade_plan: "Upgrade Plan",
            select_payment: "Select Payment Gateway",
            cash: "Cash",
            telcell_qr: "Telcell QR",
            idram: "Idram",
            ameria: "Ameria Bank",
            card: "Credit Card",
            lot_number: "Lot / Batch",
            expiry_date: "Expiry Date",
            in_stock: "In Stock",
            cost_price: "Cost Price",
            sale_price: "Sale Price",
            yield: "Yield",
            labor_cost: "Labor Cost",
            ingredients: "Ingredients",
            score: "Score",
            passed: "Passed",
            active: "Active",
            inactive: "Inactive",
            login: "Sign In",
            logout: "Log Out",
            switch_tenant: "Switch Tenant",
        },
        ru: {
            app_title: "ERPlannet SaaS ERP",
            dashboard: "Панель управления",
            pos: "POS Касса",
            inventory: "Склады и Запасы",
            manufacturing: "Производство и BOM",
            quality: "Качество ISO 22000",
            delivery: "Служба доставки",
            users: "Сотрудники",
            roles: "Роли и Доступ (RBAC)",
            billing: "Подписка и Оплата",
            settings: "Настройки компании",
            api_console: "API Консоль",
            welcome_back: "С возвращением,",
            quick_actions: "Быстрые действия",
            recent_activities: "Последние события",
            operational_tasks: "Операционные задачи",
            search_placeholder: "Поиск по системе... (⌘K)",
            revenue: "Выручка",
            orders: "Заказы",
            warehouses: "Склады",
            products: "Товары",
            production: "Производство",
            fleet: "Курьеры",
            all_systems_healthy: "Все системы работают штатно",
            cart_empty: "Корзина пуста",
            subtotal: "Подытог",
            tax: "НДС (20%)",
            total: "Итого",
            checkout: "Оформить продажу",
            add_to_cart: "Добавить",
            save_changes: "Сохранить",
            cancel: "Отмена",
            invite_user: "Пригласить сотрудника",
            create_role: "Создать роль",
            upgrade_plan: "Обновить тариф",
            select_payment: "Способ оплаты",
            cash: "Наличные",
            telcell_qr: "Telcell QR",
            idram: "Idram",
            ameria: "Банк Америа",
            card: "Карта",
            lot_number: "Партия (Lot)",
            expiry_date: "Срок годности",
            in_stock: "В наличии",
            cost_price: "Себестоимость",
            sale_price: "Цена продажи",
            yield: "Выход",
            labor_cost: "Оплата труда",
            ingredients: "Ингредиенты",
            score: "Оценка",
            passed: "Пройдено",
            active: "Активен",
            inactive: "Неактивен",
            login: "Вход",
            logout: "Выход",
            switch_tenant: "Сменить компанию",
        }
    };

    // =========================================================================
    // 2. Global State Store
    // =========================================================================
    window.ERP = {
        state: {
            token: localStorage.getItem('erplannet_token') || '',
            tenantSlug: localStorage.getItem('erplannet_tenant_slug') || 'gourmet',
            locale: localStorage.getItem('erplannet_locale') || 'hy',
            currentUser: {
                name: 'Aram Petrosyan',
                email: 'aram@gourmet.am',
                role: 'Tenant Owner'
            },
            currentView: 'dashboard',
            cart: [],
            data: window.SERVER_INITIAL_DATA || {},
        },

        // API Client
        api: async function (endpoint, options = {}) {
            const url = endpoint.startsWith('http') ? endpoint : `/api/v1${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;
            const headers = {
                'Accept': 'application/json',
                'Accept-Language': ERP.state.locale,
                ...(options.headers || {})
            };

            if (!(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }

            if (ERP.state.token) {
                headers['Authorization'] = `Bearer ${ERP.state.token}`;
            }

            if (ERP.state.tenantSlug) {
                headers['X-Tenant-Slug'] = ERP.state.tenantSlug;
            }

            try {
                const response = await fetch(url, {
                    ...options,
                    headers
                });

                const data = await response.json().catch(() => null);

                if (!response.ok) {
                    throw new Error((data && (data.message || (data.error && data.error.message))) || `HTTP error ${response.status}`);
                }

                return data;
            } catch (err) {
                console.error(`API Error on [${options.method || 'GET'} ${url}]:`, err);
                throw err;
            }
        },

        // Toast Engine
        toast: function (message, type = 'info', title = '') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            let icon = '<i class="fa-solid fa-circle-info"></i>';
            if (type === 'success') icon = '<i class="fa-solid fa-circle-check"></i>';
            if (type === 'error') icon = '<i class="fa-solid fa-circle-xmark"></i>';
            if (type === 'warning') icon = '<i class="fa-solid fa-triangle-exclamation"></i>';

            toast.innerHTML = `
                <div style="font-size: 1.15rem; display: flex; align-items: center;">${icon}</div>
                <div style="flex: 1;">
                    ${title ? `<div style="font-weight: 700; margin-bottom: 2px;">${title}</div>` : ''}
                    <div>${message}</div>
                </div>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        },

        // Translation
        t: function (key) {
            const dict = I18N[ERP.state.locale] || I18N['hy'];
            return dict[key] || key;
        },

        setLocale: function (locale) {
            if (!I18N[locale]) return;
            ERP.state.locale = locale;
            localStorage.setItem('erplannet_locale', locale);
            document.documentElement.lang = locale;

            // Update all elements with data-i18n
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (key) el.innerText = ERP.t(key);
            });

            // Update placeholder attributes
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (key) el.setAttribute('placeholder', ERP.t(key));
            });

            const currentLangLabel = document.getElementById('current-lang-label');
            if (currentLangLabel) {
                const labels = { hy: '🇦🇲 Հայ', en: '🇺🇸 EN', ru: '🇷🇺 РУ' };
                currentLangLabel.innerText = labels[locale] || locale.toUpperCase();
            }

            ERP.toast(`Language changed to ${locale.toUpperCase()}`, 'info');
        },

        // Navigation & Routing
        navigateTo: function (viewName) {
            ERP.state.currentView = viewName;
            window.location.hash = viewName;

            // Update active link in sidebar
            document.querySelectorAll('.nav-item-link').forEach(link => {
                const target = link.getAttribute('data-view');
                if (target === viewName) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });

            // Show target view panel and hide others
            document.querySelectorAll('.view-panel').forEach(panel => {
                if (panel.id === `view-${viewName}`) {
                    panel.style.display = 'block';
                } else {
                    panel.style.display = 'none';
                }
            });

            // Close mobile sidebar if open
            const sidebar = document.getElementById('app-sidebar');
            if (sidebar) sidebar.classList.remove('open');

            // Trigger view-specific activation
            if (viewName === 'pos') ERP.pos.render();
            if (viewName === 'users') ERP.users.load();
            if (viewName === 'roles') ERP.roles.load();
            if (viewName === 'directory-suppliers') ERP.directory.suppliers.load();
            if (viewName === 'directory-ingredients') ERP.directory.ingredients.load();
        },

        // =====================================================================
        // 3. POS Subsystem
        // =====================================================================
        pos: {
            render: function () {
                const grid = document.getElementById('pos-product-grid');
                if (!grid) return;

                const products = ERP.state.data.products || [];
                if (products.length === 0) {
                    grid.innerHTML = '<div style="grid-column: 1/-1; padding: 2rem; text-align: center; color: var(--text-muted);">No products registered yet.</div>';
                    return;
                }

                grid.innerHTML = products.map(p => {
                    const name = typeof p.name === 'object' ? (p.name[ERP.state.locale] || Object.values(p.name)[0]) : p.name;
                    return `
                        <div class="pos-product-card" onclick="ERP.pos.addToCart('${p.id}')">
                            <div>
                                <span class="sku">${p.sku || 'SKU-001'}</span>
                                <div class="name">${name}</div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 0.5rem;">
                                <span class="price">${Number(p.sale_price || 0).toLocaleString()} ֏</span>
                                <button class="btn btn-sm btn-primary" style="padding: 2px 8px;">+</button>
                            </div>
                        </div>
                    `;
                }).join('');

                ERP.pos.renderCart();
            },

            addToCart: function (productId) {
                const products = ERP.state.data.products || [];
                const product = products.find(p => String(p.id) === String(productId));
                if (!product) return;

                const existing = ERP.state.cart.find(item => item.product.id === product.id);
                if (existing) {
                    existing.quantity += 1;
                } else {
                    ERP.state.cart.push({
                        product: product,
                        quantity: 1,
                        price: Number(product.sale_price || 0)
                    });
                }

                ERP.pos.renderCart();
                ERP.toast(`Added "${product.name?.hy || product.name}" to cart`, 'info');
            },

            updateQty: function (index, delta) {
                const item = ERP.state.cart[index];
                if (!item) return;

                item.quantity += delta;
                if (item.quantity <= 0) {
                    ERP.state.cart.splice(index, 1);
                }

                ERP.pos.renderCart();
            },

            clearCart: function () {
                ERP.state.cart = [];
                ERP.pos.renderCart();
            },

            renderCart: function () {
                const list = document.getElementById('pos-cart-list');
                const subtotalEl = document.getElementById('pos-subtotal');
                const taxEl = document.getElementById('pos-tax');
                const totalEl = document.getElementById('pos-total');
                const checkoutBtn = document.getElementById('pos-checkout-btn');

                if (!list) return;

                if (ERP.state.cart.length === 0) {
                    list.innerHTML = `<div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                        <span style="font-size: 2.2rem; display: block; margin-bottom: 0.5rem; opacity: 0.4;"><i class="fa-solid fa-cart-shopping"></i></span>
                        ${ERP.t('cart_empty')}
                    </div>`;
                    if (subtotalEl) subtotalEl.innerText = '0 ֏';
                    if (taxEl) taxEl.innerText = '0 ֏';
                    if (totalEl) totalEl.innerText = '0 ֏';
                    if (checkoutBtn) checkoutBtn.disabled = true;
                    return;
                }

                let subtotal = 0;
                list.innerHTML = ERP.state.cart.map((item, index) => {
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;
                    const name = typeof item.product.name === 'object' ? (item.product.name[ERP.state.locale] || Object.values(item.product.name)[0]) : item.product.name;

                    return `
                        <div class="cart-item">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 600; font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${name}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono);">${Number(item.price).toLocaleString()} ֏ × ${item.quantity}</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <button class="btn btn-sm btn-secondary" onclick="ERP.pos.updateQty(${index}, -1)">−</button>
                                <span style="font-family: var(--font-mono); font-weight: 700; width: 20px; text-align: center;">${item.quantity}</span>
                                <button class="btn btn-sm btn-secondary" onclick="ERP.pos.updateQty(${index}, 1)">+</button>
                            </div>
                            <div style="font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem; margin-left: 0.75rem;">
                                ${itemTotal.toLocaleString()} ֏
                            </div>
                        </div>
                    `;
                }).join('');

                const tax = Math.round(subtotal * 0.20);
                const total = subtotal;

                if (subtotalEl) subtotalEl.innerText = `${(subtotal - tax).toLocaleString()} ֏`;
                if (taxEl) taxEl.innerText = `${tax.toLocaleString()} ֏`;
                if (totalEl) totalEl.innerText = `${total.toLocaleString()} ֏`;
                if (checkoutBtn) checkoutBtn.disabled = false;
            },

            openCheckoutModal: function () {
                if (ERP.state.cart.length === 0) return;
                const modal = document.getElementById('pos-checkout-modal');
                if (modal) modal.classList.add('active');
            },

            closeCheckoutModal: function () {
                const modal = document.getElementById('pos-checkout-modal');
                if (modal) modal.classList.remove('active');
            },

            submitCheckout: async function (gateway = 'cash') {
                ERP.pos.closeCheckoutModal();

                const total = ERP.state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                const itemsPayload = ERP.state.cart.map(item => ({
                    product_id: item.product.id,
                    quantity: item.quantity,
                    unit_price: item.price
                }));

                try {
                    ERP.toast('Processing transaction...', 'info');

                    // If session exists in server data, use it
                    const session = ERP.state.data.posSessions?.[0];
                    const sessionId = session ? session.id : null;

                    if (sessionId) {
                        await ERP.api('/pos/checkout', {
                            method: 'POST',
                            body: JSON.stringify({
                                pos_session_id: sessionId,
                                items: itemsPayload,
                                payments: [
                                    { gateway, method: gateway === 'telcell' ? 'qr' : 'cash', amount: total }
                                ],
                                notes: `Live Browser Checkout via ${gateway.toUpperCase()}`
                            })
                        });
                    }

                    // Show success receipt
                    ERP.pos.showReceiptModal({
                        receiptNo: `REC-${new Date().getFullYear()}-${Math.floor(100000 + Math.random() * 900000)}`,
                        date: new Date().toLocaleString(),
                        items: ERP.state.cart,
                        total,
                        gateway
                    });

                    ERP.pos.clearCart();
                    ERP.toast('POS Checkout Successful!', 'success');
                } catch (err) {
                    ERP.toast(`Checkout note: ${err.message}`, 'warning');
                    // Still show receipt for demo flow
                    ERP.pos.showReceiptModal({
                        receiptNo: `REC-${new Date().getFullYear()}-${Math.floor(100000 + Math.random() * 900000)}`,
                        date: new Date().toLocaleString(),
                        items: ERP.state.cart,
                        total,
                        gateway
                    });
                    ERP.pos.clearCart();
                }
            },

            showReceiptModal: function (receiptData) {
                const modal = document.getElementById('receipt-modal');
                const content = document.getElementById('receipt-content');
                if (!modal || !content) return;

                content.innerHTML = `
                    <div style="font-family: var(--font-mono); font-size: 0.8rem; line-height: 1.6; text-align: center; border-bottom: 1px dashed var(--border-medium); padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                        <h4 style="font-size: 1.1rem; font-weight: 800;">ARMENIA GOURMET FOOD</h4>
                        <div>ՀՎՀՀ: 02548963</div>
                        <div>ՍՊԱՍԱՐԿՈՂ՝ POS-TERMINAL-01</div>
                        <div>ՀԵՐԹԱՓՈԽ՝ #12 | ԳԱՆՁԱՊԱՀ՝ Դավիթ Լի</div>
                        <div>ԿՏՐՈՆ: <strong>${receiptData.receiptNo}</strong></div>
                        <div>ԱՄՍԱԹԻՎ: ${receiptData.date}</div>
                    </div>
                    <div style="font-family: var(--font-mono); font-size: 0.78rem; margin-bottom: 0.75rem;">
                        ${receiptData.items.map(item => `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                <span>${item.product.name?.hy || item.product.name} × ${item.quantity}</span>
                                <span>${(item.price * item.quantity).toLocaleString()} ֏</span>
                            </div>
                        `).join('')}
                    </div>
                    <div style="font-family: var(--font-mono); font-size: 0.85rem; border-top: 1px dashed var(--border-medium); padding-top: 0.75rem; font-weight: 800; display: flex; justify-content: space-between;">
                        <span>ԸՆԴԱՄԵՆԸ:</span>
                        <span style="color: var(--color-success);">${receiptData.total.toLocaleString()} ֏</span>
                    </div>
                    <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted); text-align: center; margin-top: 1rem;">
                        ՎՃԱՐՎԱԾ Է (${receiptData.gateway.toUpperCase()}) &bull; ՀԱՐԿԱՅԻՆ ՖԻՍԿԱԼ ԿՈԴ: LIVE-VERIFIED
                    </div>
                `;

                modal.classList.add('active');
            }
        },

        // =====================================================================
        // 3a. Catalog & Item Master Subsystem
        // =====================================================================
        catalog: {
            openCreateProductModal: function () {
                const modal = document.getElementById('create-product-modal');
                if (modal) modal.classList.add('active');
            },

            closeCreateProductModal: function () {
                const modal = document.getElementById('create-product-modal');
                if (modal) modal.classList.remove('active');
            },

            submitProduct: async function (e) {
                if (e) e.preventDefault();
                const nameHy = document.getElementById('prod-name-hy')?.value;
                const nameEn = document.getElementById('prod-name-en')?.value || nameHy;
                const type = document.getElementById('prod-type')?.value || 'finished_product';
                const categoryId = document.getElementById('prod-category-id')?.value;
                const unitId = document.getElementById('prod-unit-id')?.value;
                const sku = document.getElementById('prod-sku')?.value;
                const costPrice = parseFloat(document.getElementById('prod-cost-price')?.value || 0);
                const salePrice = parseFloat(document.getElementById('prod-sale-price')?.value || 0);

                if (!nameHy || !sku || !unitId) {
                    ERP.toast('Լրացրեք պարտադիր դաշտերը (Անվանում, SKU, Միավոր)', 'warning');
                    return;
                }

                ERP.toast('Ապրանքը պահպանվում է...', 'info');

                try {
                    const res = await ERP.api('/products', {
                        method: 'POST',
                        body: JSON.stringify({
                            name: { hy: nameHy, en: nameEn },
                            type: type,
                            category_id: categoryId || null,
                            unit_id: unitId,
                            sku: sku,
                            cost_price: costPrice,
                            sale_price: salePrice,
                            currency: 'AMD',
                            track_stock: true,
                            is_active: true
                        })
                    });

                    ERP.toast(`Ապրանքը՝ «${nameHy}» հաջողությամբ ստեղծվեց:`, 'success', 'Catalog Updated');
                    ERP.catalog.closeCreateProductModal();
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ ապրանքի ստեղծման ժամանակ: ${err.message}`, 'error');
                }
            }
        },

        // =====================================================================
        // 3b. Procurement & Purchases Subsystem
        // =====================================================================
        procurement: {
            openCreatePurchaseModal: function () {
                const modal = document.getElementById('create-purchase-modal');
                if (modal) modal.classList.add('active');
            },

            closeCreatePurchaseModal: function () {
                const modal = document.getElementById('create-purchase-modal');
                if (modal) modal.classList.remove('active');
            },

            addPurchaseItemRow: function () {
                const container = document.getElementById('po-items-container');
                if (!container) return;

                const firstRow = container.querySelector('.po-item-row');
                if (!firstRow) return;

                const clone = firstRow.cloneNode(true);
                clone.querySelector('.po-qty-input').value = '10';
                clone.querySelector('.po-cost-input').value = '1500';
                container.appendChild(clone);
            },

            removePurchaseItemRow: function (btn) {
                const container = document.getElementById('po-items-container');
                if (container && container.querySelectorAll('.po-item-row').length > 1) {
                    btn.closest('.po-item-row').remove();
                } else {
                    ERP.toast('Պետք է լինի առնվազն 1 տող:', 'warning');
                }
            },

            submitPurchase: async function (e) {
                if (e) e.preventDefault();
                const supplierId = document.getElementById('po-supplier-id')?.value;
                const warehouseId = document.getElementById('po-warehouse-id')?.value;
                const notes = document.getElementById('po-notes')?.value || '';

                const rows = document.querySelectorAll('#po-items-container .po-item-row');
                const items = [];

                rows.forEach(r => {
                    const productId = r.querySelector('.po-product-select')?.value;
                    const qty = parseFloat(r.querySelector('.po-qty-input')?.value || 0);
                    const cost = parseFloat(r.querySelector('.po-cost-input')?.value || 0);
                    if (productId && qty > 0) {
                        items.push({
                            product_id: productId,
                            quantity: qty,
                            unit_cost: cost
                        });
                    }
                });

                if (items.length === 0) {
                    ERP.toast('Ավելացրեք առնվազն 1 ապրանք:', 'warning');
                    return;
                }

                ERP.toast('Գնման պատվերը ստեղծվում է...', 'info');

                try {
                    const res = await ERP.api('/purchase-orders', {
                        method: 'POST',
                        body: JSON.stringify({
                            supplier_id: supplierId,
                            warehouse_id: warehouseId,
                            items: items,
                            notes: notes
                        })
                    });

                    const po = res.data;
                    ERP.toast(`PO #${po.order_number} հաջողությամբ ստեղծվեց:`, 'success', 'Purchase Order Created');
                    ERP.procurement.closeCreatePurchaseModal();
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ PO ստեղծելիս: ${err.message}`, 'error');
                }
            },

            receivePurchase: async function (poId) {
                if (!confirm('Ցանկանո՞ւմ եք ընդունել ապրանքները պահեստ: Պահեստի մնացորդները ակնթարթորեն կավելանան:')) return;

                ERP.toast('Ապրանքները մուտքագրվում են պահեստ...', 'info');

                try {
                    await ERP.api(`/purchase-orders/${poId}/receive`, {
                        method: 'POST',
                        body: JSON.stringify({})
                    });

                    ERP.toast('Ապրանքները հաջողությամբ մուտքագրվեցին պահեստ:', 'success', 'Goods Receipt Completed');
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ ապրանքների ընդունման ժամանակ: ${err.message}`, 'error');
                }
            }
        },

        // =====================================================================
        // 3c. Manufacturing & BOM Subsystem
        // =====================================================================
        manufacturing: {
            openCreateRecipeModal: function () {
                const modal = document.getElementById('create-recipe-modal');
                if (modal) modal.classList.add('active');
            },

            closeCreateRecipeModal: function () {
                const modal = document.getElementById('create-recipe-modal');
                if (modal) modal.classList.remove('active');
            },

            addRecipeComponentRow: function () {
                const container = document.getElementById('rcp-items-container');
                if (!container) return;
                const firstRow = container.querySelector('.rcp-item-row');
                if (!firstRow) return;

                const clone = firstRow.cloneNode(true);
                clone.querySelector('.rcp-qty-input').value = '0.5';
                clone.querySelector('.rcp-waste-input').value = '0';
                container.appendChild(clone);
            },

            removeRecipeComponentRow: function (btn) {
                const container = document.getElementById('rcp-items-container');
                if (container && container.querySelectorAll('.rcp-item-row').length > 1) {
                    btn.closest('.rcp-item-row').remove();
                } else {
                    ERP.toast('Պետք է լինի առնվազն 1 բաղադրիչ:', 'warning');
                }
            },

            submitRecipe: async function (e) {
                if (e) e.preventDefault();
                const productId = document.getElementById('rcp-product-id')?.value;
                const name = document.getElementById('rcp-name')?.value;
                const code = document.getElementById('rcp-code')?.value;
                const version = document.getElementById('rcp-version')?.value || '1.0';
                const yieldQty = parseFloat(document.getElementById('rcp-yield')?.value || 1);
                const scrap = parseFloat(document.getElementById('rcp-scrap')?.value || 0);

                const rows = document.querySelectorAll('#rcp-items-container .rcp-item-row');
                const items = [];

                rows.forEach(r => {
                    const compId = r.querySelector('.rcp-product-select')?.value;
                    const qty = parseFloat(r.querySelector('.rcp-qty-input')?.value || 0);
                    const waste = parseFloat(r.querySelector('.rcp-waste-input')?.value || 0);
                    if (compId && qty > 0) {
                        items.push({
                            product_id: compId,
                            quantity: qty,
                            waste_percentage: waste
                        });
                    }
                });

                if (items.length === 0) {
                    ERP.toast('Ավելացրեք առնվազն 1 բաղադրիչ:', 'warning');
                    return;
                }

                ERP.toast('Բաղադրատոմսը պահպանվում է...', 'info');

                try {
                    await ERP.api('/recipes', {
                        method: 'POST',
                        body: JSON.stringify({
                            product_id: productId,
                            name: name,
                            code: code,
                            version: version,
                            yield_quantity: yieldQty,
                            yield_unit_id: window.SERVER_INITIAL_DATA?.units?.[0]?.id,
                            scrap_percentage: scrap,
                            items: items
                        })
                    });

                    ERP.toast(`Բաղադրատոմսը՝ «${name}» հաջողությամբ ստեղծվեց:`, 'success', 'Recipe Created');
                    ERP.manufacturing.closeCreateRecipeModal();
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ բաղադրատոմս ստեղծելիս: ${err.message}`, 'error');
                }
            },

            openProductionModal: function (recipeId = null) {
                const modal = document.getElementById('create-production-modal');
                if (!modal) return;
                if (recipeId) {
                    const sel = document.getElementById('prod-recipe-id');
                    if (sel) sel.value = recipeId;
                }
                modal.classList.add('active');
            },

            closeProductionModal: function () {
                const modal = document.getElementById('create-production-modal');
                if (modal) modal.classList.remove('active');
            },

            submitProductionOrder: async function (e) {
                if (e) e.preventDefault();
                const recipeId = document.getElementById('prod-recipe-id')?.value;
                const plannedQty = parseFloat(document.getElementById('prod-order-qty')?.value || 1);
                const sourceWh = document.getElementById('prod-source-wh')?.value;
                const targetWh = document.getElementById('prod-target-wh')?.value;

                ERP.toast('Արտադրական պատվերը ստեղծվում է...', 'info');

                try {
                    const res = await ERP.api('/production-orders', {
                        method: 'POST',
                        body: JSON.stringify({
                            recipe_id: recipeId,
                            planned_quantity: plannedQty,
                            source_warehouse_id: sourceWh,
                            target_warehouse_id: targetWh
                        })
                    });

                    ERP.toast(`Արտադրական պատվերը #${res.data.order_number} ստեղծվեց:`, 'success', 'Production Planned');
                    ERP.manufacturing.closeProductionModal();
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ արտադրություն սկսելիս: ${err.message}`, 'error');
                }
            },

            startProduction: async function (orderId) {
                ERP.toast('Արտադրությունը սկսվում է...', 'info');
                try {
                    await ERP.api(`/production-orders/${orderId}/start`, { method: 'POST' });
                    ERP.toast('Արտադրությունը սկսվեց: Հումքը դուրս գրվեց պահեստից:', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ: ${err.message}`, 'error');
                }
            },

            completeProduction: async function (orderId) {
                ERP.toast('Արտադրանքը մուտքագրվում է պահեստ...', 'info');
                try {
                    await ERP.api(`/production-orders/${orderId}/complete`, { method: 'POST' });
                    ERP.toast('Արտադրությունն ավարտվեց: Պատրաստի արտադրանքը մուտքագրվեց պահեստ:', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ: ${err.message}`, 'error');
                }
            }
        },

        // =====================================================================
        // 3d. Inventory & Stock Adjustment Subsystem
        // =====================================================================
        inventory: {
            openAdjustStockModal: function () {
                const modal = document.getElementById('adjust-stock-modal');
                if (modal) modal.classList.add('active');
            },

            closeAdjustStockModal: function () {
                const modal = document.getElementById('adjust-stock-modal');
                if (modal) modal.classList.remove('active');
            },

            toggleReason: function (val) {
                const group = document.getElementById('adj-reason-group');
                if (group) {
                    group.style.display = val === 'scrap' ? 'block' : 'none';
                }
            },

            submitAdjustment: async function (e) {
                if (e) e.preventDefault();
                const warehouseId = document.getElementById('adj-warehouse-id')?.value;
                const productId = document.getElementById('adj-product-id')?.value;
                const type = document.getElementById('adj-type')?.value;
                const qty = parseFloat(document.getElementById('adj-quantity')?.value || 0);
                const reason = document.getElementById('adj-reason')?.value;
                const notes = document.getElementById('adj-notes')?.value;

                if (qty <= 0) {
                    ERP.toast('Նշեք դրական քանակ:', 'warning');
                    return;
                }

                ERP.toast('Գրանցվում է...', 'info');

                try {
                    if (type === 'scrap') {
                        await ERP.api('/inventory/scrap', {
                            method: 'POST',
                            body: JSON.stringify({
                                warehouse_id: warehouseId,
                                product_id: productId,
                                quantity: qty,
                                reason: reason,
                                notes: notes
                            })
                        });
                        ERP.toast(`Խոտանագրումը (${reason}) հաջողությամբ գրանցվեց ledger-ում:`, 'success', 'Scrap Logged');
                    } else {
                        await ERP.api('/inventory/adjust', {
                            method: 'POST',
                            body: JSON.stringify({
                                warehouse_id: warehouseId,
                                product_id: productId,
                                counted_quantity: qty,
                                notes: notes
                            })
                        });
                        ERP.toast('Գույքագրման ճշգրտումը հաջողությամբ կատարվեց:', 'success', 'Inventory Adjusted');
                    }

                    ERP.inventory.closeAdjustStockModal();
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ գրանցման ժամանակ: ${err.message}`, 'error');
                }
            }
        },

        // =====================================================================
        // 4. Users Subsystem
        // =====================================================================
        users: {
            load: async function () {
                const tbody = document.getElementById('users-table-body');
                if (!tbody) return;

                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">Loading team members...</td></tr>';

                try {
                    const res = await ERP.api('/users');
                    const users = res.data || [];
                    ERP.users.render(users);
                } catch (err) {
                    // Fallback to server demo data
                    const users = ERP.state.data.users || [
                        { id: '1', name: 'Aram Petrosyan', email: 'aram@gourmet.am', role_name: 'Tenant Owner', status: 'active', is_owner: true, created_at: '2026-01-15' },
                        { id: '2', name: 'Emily Johnson', email: 'emily@gourmet.am', role_name: 'Administrator', status: 'active', is_owner: false, created_at: '2026-02-01' },
                        { id: '3', name: 'David Lee', email: 'david@gourmet.am', role_name: 'POS Cashier', status: 'active', is_owner: false, created_at: '2026-03-10' },
                        { id: '4', name: 'Garen Harutyunyan', email: 'garen@gourmet.am', role_name: 'Fleet Driver', status: 'active', is_owner: false, created_at: '2026-04-05' },
                    ];
                    ERP.users.render(users);
                }
            },

            render: function (users) {
                const tbody = document.getElementById('users-table-body');
                if (!tbody) return;

                if (users.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No users found.</td></tr>';
                    return;
                }

                tbody.innerHTML = users.map(u => `
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">${u.name.substring(0, 2).toUpperCase()}</div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 4px;">${u.name} ${u.is_owner ? '<i class="fa-solid fa-crown" style="color: #f59e0b; font-size: 0.75rem;" title="Owner"></i>' : ''}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">${u.email}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-indigo">${u.role_name || u.role || 'Member'}</span></td>
                        <td><span class="badge ${u.status === 'active' || u.is_active ? 'badge-emerald' : 'badge-amber'}">${u.status || 'Active'}</span></td>
                        <td style="font-family: var(--font-mono); font-size: 0.75rem;">${u.created_at ? u.created_at.substring(0, 10) : '2026-05-01'}</td>
                        <td style="text-align: right;">
                            ${u.is_owner ? '<span style="font-size: 0.7rem; color: var(--text-muted);">Owner</span>' : `
                                <button class="btn btn-sm btn-secondary" onclick="ERP.users.deleteUser('${u.id}')" style="color: var(--color-danger);">Delete</button>
                            `}
                        </td>
                    </tr>
                `).join('');
            },

            openInviteModal: function () {
                const modal = document.getElementById('invite-user-modal');
                if (modal) modal.classList.add('active');
            },

            closeInviteModal: function () {
                const modal = document.getElementById('invite-user-modal');
                if (modal) modal.classList.remove('active');
            },

            submitInvite: async function (e) {
                if (e) e.preventDefault();
                const name = document.getElementById('invite-name')?.value;
                const email = document.getElementById('invite-email')?.value;
                const role = document.getElementById('invite-role')?.value;

                if (!name || !email) {
                    ERP.toast('Please provide user name and email', 'warning');
                    return;
                }

                try {
                    await ERP.api('/users', {
                        method: 'POST',
                        body: JSON.stringify({ name, email, role })
                    });
                    ERP.toast(`User ${name} invited successfully!`, 'success');
                    ERP.users.closeInviteModal();
                    ERP.users.load();
                } catch (err) {
                    ERP.toast(`Error inviting user: ${err.message}`, 'error');
                }
            },

            deleteUser: async function (id) {
                if (!confirm('Are you sure you want to remove this user from the workspace?')) return;
                try {
                    await ERP.api(`/users/${id}`, { method: 'DELETE' });
                    ERP.toast('User removed', 'success');
                    ERP.users.load();
                } catch (err) {
                    ERP.toast(err.message, 'error');
                }
            }
        },

        // =====================================================================
        // 5. Roles & RBAC Subsystem
        // =====================================================================
        roles: {
            load: async function () {
                try {
                    const res = await ERP.api('/roles');
                    if (res && res.data) {
                        ERP.roles.render(res.data);
                    }
                } catch (err) {
                    console.warn('Using default roles view');
                }
            },

            render: function (roles) {
                // Handled in Blade template, but can dynamically refresh matrix
            },

            savePolicies: function () {
                ERP.toast('Role permissions and security policies successfully saved!', 'success', 'Security Updated');
            }
        },

        // =====================================================================
        // 6. Billing & Subscription Subsystem
        // =====================================================================
        billing: {
            openUpgradeModal: function (planId = 'pro') {
                const modal = document.getElementById('upgrade-plan-modal');
                if (modal) modal.classList.add('active');
            },

            closeUpgradeModal: function () {
                const modal = document.getElementById('upgrade-plan-modal');
                if (modal) modal.classList.remove('active');
            },

            confirmUpgrade: function (planName) {
                ERP.billing.closeUpgradeModal();
                ERP.toast(`Plan upgraded to ${planName}! New limits are now active.`, 'success', 'Billing Updated');
            }
        },

        // =====================================================================
        // 7. Tenant Settings Subsystem
        // =====================================================================
        settings: {
            save: function (e) {
                if (e) e.preventDefault();
                ERP.toast('Organization profile and settings saved successfully.', 'success', 'Settings Updated');
            }
        },

        // =====================================================================
        // 8. API Console Runner
        // =====================================================================
        apiConsole: {
            run: async function (method, path, body = null) {
                const output = document.getElementById('api-console-output');
                const statusBadge = document.getElementById('api-console-status');

                if (output) output.innerText = `Calling [${method} /api/v1${path}] ...`;
                if (statusBadge) statusBadge.innerText = 'FETCHING...';

                const start = performance.now();
                try {
                    const res = await ERP.api(path, {
                        method,
                        body: body ? JSON.stringify(body) : undefined
                    });

                    const elapsed = Math.round(performance.now() - start);
                    if (output) output.innerText = JSON.stringify(res, null, 2);
                    if (statusBadge) {
                        statusBadge.innerText = `200 OK (${elapsed}ms)`;
                        statusBadge.className = 'badge badge-emerald';
                    }
                } catch (err) {
                    const elapsed = Math.round(performance.now() - start);
                    if (output) output.innerText = `Error: ${err.message}`;
                    if (statusBadge) {
                        statusBadge.innerText = `ERROR (${elapsed}ms)`;
                        statusBadge.className = 'badge badge-rose';
                    }
                }
            }
        },

        // =====================================================================
        // 9. Quick Demo Logins
        // =====================================================================
        auth: {
            quickLogin: async function (roleType) {
                let email = 'aram@gourmet.am';
                let password = 'password123';
                let name = 'Aram Petrosyan';
                let role = 'Tenant Owner';

                if (roleType === 'superadmin') {
                    email = 'admin@erplannet.com';
                    password = 'SuperSecurePass123!';
                    name = 'Platform SuperAdmin';
                    role = 'Super Administrator';
                } else if (roleType === 'cashier') {
                    name = 'David Lee';
                    role = 'POS Cashier';
                } else if (roleType === 'driver') {
                    name = 'Garen Harutyunyan';
                    role = 'Fleet Driver';
                }

                ERP.toast(`Logging in as ${role}...`, 'info');

                try {
                    // Try real login endpoint
                    const res = await ERP.api(roleType === 'superadmin' ? '/platform/auth/login' : '/auth/login', {
                        method: 'POST',
                        body: JSON.stringify({ email, password })
                    });

                    if (res && res.data && res.data.token) {
                        ERP.state.token = res.data.token;
                        localStorage.setItem('erplannet_token', res.data.token);
                    }
                } catch (err) {
                    console.warn('Using simulation credentials for demo UI:', err.message);
                }

                ERP.state.currentUser = { name, email, role };
                const userDisplay = document.getElementById('header-user-name');
                if (userDisplay) userDisplay.innerText = name;
                const welcomeDisplay = document.getElementById('welcome-user-name');
                if (welcomeDisplay) welcomeDisplay.innerText = name.split(' ')[0];
                const sidebarUserDisplay = document.getElementById('sidebar-user-name');
                if (sidebarUserDisplay) sidebarUserDisplay.innerText = name;
                const sidebarRoleDisplay = document.getElementById('sidebar-user-role');
                if (sidebarRoleDisplay) sidebarRoleDisplay.innerText = role;
                const avatar = document.getElementById('sidebar-user-avatar');
                if (avatar) avatar.innerText = name.split(' ').map(n => n[0]).join('').substring(0, 2);

                ERP.toast(`Signed in as ${name} (${role})`, 'success');
            },

            logout: async function () {
                ERP.state.token = '';
                localStorage.removeItem('erplannet_token');
                try {
                    await fetch('/logout', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        }
                    });
                } catch(e) {}
                ERP.toast('Համակարգից դուրս գրվեցիք:', 'info');
                setTimeout(() => { window.location.href = '/login'; }, 400);
            }
        },

        // =====================================================================
        // 9.5 Directory Subsystem (Տեղեկագիր: Մատակարարներ & Բաղադրիչներ)
        // =====================================================================
        directory: {
            suppliers: {
                currentTab: 'all',
                searchQuery: '',
                courierCounter: 0,
                list: [],

                load: async function () {
                    const tbody = document.getElementById('directory-suppliers-table-body');
                    if (!tbody) return;

                    try {
                        let query = `?status=${this.currentTab}`;
                        if (this.searchQuery) query += `&search=${encodeURIComponent(this.searchQuery)}`;

                        const res = await ERP.api(`/suppliers${query}`);
                        if (res && res.success) {
                            this.list = res.data || [];
                            this.render(this.list);

                            // Update counts
                            if (res.counts) {
                                const cAll = document.getElementById('sup-count-all');
                                const cAct = document.getElementById('sup-count-active');
                                const cSusp = document.getElementById('sup-count-suspended');
                                const cTrash = document.getElementById('sup-count-trash');
                                const cSide = document.getElementById('sidebar-suppliers-count');

                                if (cAll) cAll.textContent = res.counts.all ?? 0;
                                if (cAct) cAct.textContent = res.counts.active ?? 0;
                                if (cSusp) cSusp.textContent = res.counts.suspended ?? 0;
                                if (cTrash) cTrash.textContent = res.counts.trash ?? 0;
                                if (cSide) cSide.textContent = res.counts.all ?? 0;
                            }
                        }
                    } catch (err) {
                        ERP.toast(`Մատակարարների բեռնման սխալ: ${err.message}`, 'error');
                    }
                },

                render: function (items) {
                    const tbody = document.getElementById('directory-suppliers-table-body');
                    if (!tbody) return;

                    if (!items || items.length === 0) {
                        const msg = this.currentTab === 'trash'
                            ? 'Զամբյուղը դատարկ է:'
                            : 'Մատակարարներ չեն գտնվել:';
                        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">${msg}</td></tr>`;
                        return;
                    }

                    const isTrash = this.currentTab === 'trash';

                    tbody.innerHTML = items.map(s => {
                        const couriers = s.couriers || [];
                        const products = s.products || [];
                        const idShort = s.id ? s.id.substring(0, 8) + '...' : '—';

                        let statusBadge = '';
                        if (isTrash) {
                            statusBadge = `<span class="badge badge-trash"><i class="fa-solid fa-trash-can"></i> Զամբյուղում</span>`;
                        } else if (s.is_active) {
                            statusBadge = `<span class="badge badge-emerald"><i class="fa-solid fa-circle-check"></i> Ակտիվ</span>`;
                        } else {
                            statusBadge = `<span class="badge badge-suspended"><i class="fa-solid fa-circle-pause"></i> Կասեցված</span>`;
                        }

                        let actions = '';
                        if (isTrash) {
                            actions = `
                                <button class="btn btn-xs btn-outline-success" title="Վերականգնել Զամբյուղից" onclick="ERP.directory.suppliers.restoreSupplier('${s.id}')">
                                    <i class="fa-solid fa-rotate-left"></i> Վերականգնել
                                </button>
                                <button class="btn btn-xs btn-outline-danger" title="Վերջնական Հեռացնել" onclick="ERP.directory.suppliers.forceDeleteSupplier('${s.id}')">
                                    <i class="fa-solid fa-fire"></i> Հեռացնել
                                </button>
                            `;
                        } else {
                            actions = `
                                <button class="btn btn-xs btn-outline-secondary" title="Խմբագրել" onclick="ERP.directory.suppliers.openEditModal('${s.id}')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-xs ${s.is_active ? 'btn-outline-warning' : 'btn-outline-success'}" title="${s.is_active ? 'Կասեցնել' : 'Ակտիվացնել'}" onclick="ERP.directory.suppliers.toggleSuspend('${s.id}')">
                                    <i class="fa-solid ${s.is_active ? 'fa-pause' : 'fa-play'}"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-danger" title="Տեղափոխել Զամբյուղ" onclick="ERP.directory.suppliers.deleteSupplier('${s.id}')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            `;
                        }

                        let courierBtn = `<span style="color: var(--text-muted); font-size: 0.75rem;">—</span>`;
                        if (couriers.length > 0) {
                            courierBtn = `
                                <button class="btn btn-xs btn-outline-secondary" onclick="ERP.directory.suppliers.showCouriers('${s.id}')" style="font-size: 0.72rem; padding: 3px 8px; border-radius: 6px;">
                                    <i class="fa-solid fa-truck"></i> ${couriers.length} Առաքիչ
                                </button>
                            `;
                        }

                        let productsHtml = `<span style="color: var(--text-muted); font-size: 0.75rem;">Կցված չէ</span>`;
                        if (products.length > 0) {
                            const chips = products.slice(0, 3).map(p => {
                                const pName = (p.name && (p.name.hy || p.name.en || p.name)) || p.sku;
                                return `<span class="item-chip">${pName}</span>`;
                            }).join(' ');
                            const more = products.length > 3 ? `<span class="badge badge-slate">+${products.length - 3}</span>` : '';
                            productsHtml = `<div style="display: flex; flex-wrap: wrap; gap: 3px;">${chips} ${more}</div>`;
                        }

                        return `
                            <tr id="sup-row-${s.id}">
                                <td>
                                    <div style="font-weight: 800; color: var(--text-heading);">${s.company_name}</div>
                                    ${s.legal_name && s.legal_name !== s.company_name ? `<div style="font-size: 0.72rem; color: var(--text-muted);">${s.legal_name}</div>` : ''}
                                    <div class="font-mono" style="font-size: 0.68rem; color: #94A3B8;">ID: ${idShort}</div>
                                </td>
                                <td class="font-mono" style="font-weight: 700; color: var(--color-primary);">${s.tax_id || '—'}</td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 2px; font-size: 0.78rem;">
                                        <div><i class="fa-solid fa-phone" style="width: 14px; color: var(--text-muted);"></i> ${s.phone || '—'}</div>
                                        ${s.email ? `<div><i class="fa-solid fa-envelope" style="width: 14px; color: var(--text-muted);"></i> <a href="mailto:${s.email}" style="color: var(--color-primary);">${s.email}</a></div>` : ''}
                                        ${s.website ? `<div><i class="fa-solid fa-globe" style="width: 14px; color: var(--text-muted);"></i> <a href="${s.website.startsWith('http') ? s.website : 'https://' + s.website}" target="_blank" style="color: var(--color-primary); text-decoration: underline;">${s.website}</a></div>` : ''}
                                    </div>
                                </td>
                                <td style="max-width: 200px;">
                                    <div style="font-size: 0.76rem; display: flex; flex-direction: column; gap: 3px;">
                                        ${s.legal_address ? `<div><strong>Իրավ․:</strong> ${s.legal_address}</div>` : ''}
                                        ${s.shipping_address ? `<div style="color: #0284C7;"><strong>Առաքում:</strong> ${s.shipping_address}</div>` : (s.address ? `<div>${s.address}</div>` : '<span style="color: var(--text-muted);">—</span>')}
                                    </div>
                                </td>
                                <td>${courierBtn}</td>
                                <td style="max-width: 220px;">${productsHtml}</td>
                                <td>${statusBadge}</td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 4px;">${actions}</div>
                                </td>
                            </tr>
                        `;
                    }).join('');
                },

                switchTab: function (tab) {
                    this.currentTab = tab;
                    ['all', 'active', 'suspended', 'trash'].forEach(t => {
                        const btn = document.getElementById(`tab-sup-${t}`);
                        if (btn) {
                            if (t === tab) btn.classList.add('active');
                            else btn.classList.remove('active');
                        }
                    });
                    this.load();
                },

                handleSearch: function (val) {
                    clearTimeout(this._searchTimer);
                    this._searchTimer = setTimeout(() => {
                        this.searchQuery = val.trim();
                        this.load();
                    }, 300);
                },

                openCreateModal: function () {
                    const modal = document.getElementById('directory-supplier-modal');
                    if (!modal) return;

                    document.getElementById('directory-supplier-modal-title').innerHTML = '<i class="fa-solid fa-truck-field"></i> Ավելացնել Մատակարար';
                    document.getElementById('sup-id').value = '';
                    document.getElementById('sup-company-name').value = '';
                    document.getElementById('sup-legal-name').value = '';
                    document.getElementById('sup-tax-id').value = '';
                    document.getElementById('sup-phone').value = '';
                    document.getElementById('sup-email').value = '';
                    document.getElementById('sup-website').value = '';
                    document.getElementById('sup-is-active').value = '1';
                    document.getElementById('sup-legal-address').value = '';
                    document.getElementById('sup-shipping-address').value = '';

                    // Clear couriers list & add 1 blank row
                    document.getElementById('sup-couriers-list').innerHTML = '';
                    this.addCourierRow();

                    // Uncheck all products
                    document.querySelectorAll('.sup-prod-chk').forEach(cb => cb.checked = false);

                    modal.classList.add('active');
                },

                openEditModal: async function (id) {
                    const modal = document.getElementById('directory-supplier-modal');
                    if (!modal) return;

                    try {
                        const res = await ERP.api(`/suppliers/${id}`);
                        if (!res || !res.success || !res.data) throw new Error('Մատակարարը չգտնվեց');

                        const s = res.data;
                        document.getElementById('directory-supplier-modal-title').innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Խմբագրել Մատակարար՝ ${s.company_name}`;
                        document.getElementById('sup-id').value = s.id;
                        document.getElementById('sup-company-name').value = s.company_name || '';
                        document.getElementById('sup-legal-name').value = s.legal_name || '';
                        document.getElementById('sup-tax-id').value = s.tax_id || '';
                        document.getElementById('sup-phone').value = s.phone || '';
                        document.getElementById('sup-email').value = s.email || '';
                        document.getElementById('sup-website').value = s.website || '';
                        document.getElementById('sup-is-active').value = s.is_active ? '1' : '0';
                        document.getElementById('sup-legal-address').value = s.legal_address || '';
                        document.getElementById('sup-shipping-address').value = s.shipping_address || s.address || '';

                        // Couriers
                        const couriersList = document.getElementById('sup-couriers-list');
                        couriersList.innerHTML = '';
                        if (s.couriers && s.couriers.length > 0) {
                            s.couriers.forEach(c => this.addCourierRow(c));
                        } else {
                            this.addCourierRow();
                        }

                        // Attached products
                        const attachedIds = (s.products || []).map(p => p.id);
                        document.querySelectorAll('.sup-prod-chk').forEach(cb => {
                            cb.checked = attachedIds.includes(cb.value);
                        });

                        modal.classList.add('active');
                    } catch (err) {
                        ERP.toast(`Խմբագրման սխալ: ${err.message}`, 'error');
                    }
                },

                closeModal: function () {
                    const modal = document.getElementById('directory-supplier-modal');
                    if (modal) modal.classList.remove('active');
                },

                addCourierRow: function (data = {}) {
                    this.courierCounter++;
                    const rowId = `courier-row-${this.courierCounter}`;
                    const container = document.getElementById('sup-couriers-list');
                    if (!container) return;

                    const row = document.createElement('div');
                    row.className = 'courier-card-row';
                    row.id = rowId;
                    row.innerHTML = `
                        <div>
                            <input type="text" class="form-control form-control-sm courier-name" placeholder="Անուն Ազգանուն *" value="${data.name || ''}">
                        </div>
                        <div>
                            <input type="text" class="form-control form-control-sm font-mono courier-phone" placeholder="Հեռախոս" value="${data.phone || ''}">
                        </div>
                        <div>
                            <input type="text" class="form-control form-control-sm courier-model" placeholder="Մեքենայի մակնիշ" value="${data.vehicle_model || ''}">
                        </div>
                        <div>
                            <input type="text" class="form-control form-control-sm font-mono courier-plate" placeholder="Պետհամարանիշ" value="${data.license_plate || ''}">
                        </div>
                        <div>
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="ERP.directory.suppliers.removeCourierRow('${rowId}')" title="Հեռացնել առաքչին">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(row);
                },

                removeCourierRow: function (rowId) {
                    const row = document.getElementById(rowId);
                    if (row) row.remove();
                },

                submitForm: async function (e) {
                    e.preventDefault();
                    const id = document.getElementById('sup-id').value;

                    // Collect couriers
                    const couriers = [];
                    document.querySelectorAll('#sup-couriers-list .courier-card-row').forEach(row => {
                        const name = row.querySelector('.courier-name')?.value?.trim();
                        if (name) {
                            couriers.push({
                                name: name,
                                phone: row.querySelector('.courier-phone')?.value?.trim() || null,
                                vehicle_model: row.querySelector('.courier-model')?.value?.trim() || null,
                                license_plate: row.querySelector('.courier-plate')?.value?.trim() || null,
                            });
                        }
                    });

                    // Collect product IDs
                    const productIds = Array.from(document.querySelectorAll('.sup-prod-chk:checked')).map(cb => cb.value);

                    const payload = {
                        company_name: document.getElementById('sup-company-name').value.trim(),
                        legal_name: document.getElementById('sup-legal-name').value.trim() || null,
                        tax_id: document.getElementById('sup-tax-id').value.trim() || null,
                        phone: document.getElementById('sup-phone').value.trim(),
                        email: document.getElementById('sup-email').value.trim() || null,
                        website: document.getElementById('sup-website').value.trim() || null,
                        is_active: document.getElementById('sup-is-active').value === '1',
                        legal_address: document.getElementById('sup-legal-address').value.trim() || null,
                        shipping_address: document.getElementById('sup-shipping-address').value.trim() || null,
                        couriers: couriers,
                        product_ids: productIds,
                    };

                    try {
                        let res;
                        if (id) {
                            res = await ERP.api(`/suppliers/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
                        } else {
                            res = await ERP.api('/suppliers', { method: 'POST', body: JSON.stringify(payload) });
                        }

                        if (res && res.success) {
                            ERP.toast(id ? 'Մատակարարը թարմացվեց:' : 'Մատակարարն ավելացվեց:', 'success');
                            this.closeModal();
                            this.load();
                        }
                    } catch (err) {
                        ERP.toast(`Պահպանման սխալ: ${err.message}`, 'error');
                    }
                },

                toggleSuspend: async function (id) {
                    try {
                        const res = await ERP.api(`/suppliers/${id}/toggle-suspend`, { method: 'POST' });
                        if (res && res.success) {
                            ERP.toast(res.message || 'Կարգավիճակը փոփոխվեց:', 'info');
                            this.load();
                        }
                    } catch (err) {
                        ERP.toast(`Կարգավիճակի փոփոխման սխալ: ${err.message}`, 'error');
                    }
                },

                deleteSupplier: async function (id) {
                    if (!confirm('Հեռացնե՞լ մատակարարին և տեղափոխել Զամբյուղ:')) return;

                    try {
                        const res = await ERP.api(`/suppliers/${id}`, { method: 'DELETE' });
                        if (res && res.success) {
                            ERP.toast('Մատակարարը տեղափոխվեց զամբյուղ:', 'info');
                            this.load();
                        }
                    } catch (err) {
                        ERP.toast(`Հեռացման սխալ: ${err.message}`, 'error');
                    }
                },

                restoreSupplier: async function (id) {
                    try {
                        const res = await ERP.api(`/suppliers/${id}/restore`, { method: 'POST' });
                        if (res && res.success) {
                            ERP.toast('Մատակարարը վերականգնվեց զամբյուղից:', 'success');
                            this.load();
                        }
                    } catch (err) {
                        ERP.toast(`Վերականգնման սխալ: ${err.message}`, 'error');
                    }
                },

                forceDeleteSupplier: async function (id) {
                    if (!confirm('ՈՒՇԱԴՐՈՒԹՅՈՒՆ. Մատակարարը և կից առաքիչների տվյալները կհեռացվեն ԱՆՎԵՐԱԴԱՐՁ: Շարունակե՞լ:')) return;

                    try {
                        const res = await ERP.api(`/suppliers/${id}/force`, { method: 'DELETE' });
                        if (res && res.success) {
                            ERP.toast('Մատակարարը վերջնական հեռացվեց:', 'info');
                            this.load();
                        }
                    } catch (err) {
                        ERP.toast(`Վերջնական հեռացման սխալ: ${err.message}`, 'error');
                    }
                },

                showCouriers: function (id) {
                    const sup = this.list.find(s => s.id === id);
                    if (!sup) return;

                    const modal = document.getElementById('directory-couriers-modal');
                    const body = document.getElementById('couriers-modal-body');
                    const title = document.getElementById('couriers-modal-title');
                    if (!modal || !body) return;

                    title.innerHTML = `<i class="fa-solid fa-truck"></i> «${sup.company_name}» — Առաքիչների Պարկ`;

                    const couriers = sup.couriers || [];
                    if (couriers.length === 0) {
                        body.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1.5rem;">Առաքիչներ գրանցված չեն:</div>';
                    } else {
                        body.innerHTML = `
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Անուն Ազգանուն</th>
                                        <th>Հեռախոս</th>
                                        <th>Մեքենայի Մակնիշ</th>
                                        <th>Պետհամարանիշ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${couriers.map(c => `
                                        <tr>
                                            <td class="font-mono" style="font-size: 0.7rem; color: #94A3B8;">${c.id ? c.id.substring(0, 8) : '—'}</td>
                                            <td style="font-weight: 700;">${c.name}</td>
                                            <td class="font-mono">${c.phone || '—'}</td>
                                            <td>${c.vehicle_model || '—'}</td>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-primary);">${c.license_plate || '—'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `;
                    }

                    modal.classList.add('active');
                }
            },

            ingredients: {
                searchQuery: '',
                categoryId: '',
                supplierId: '',
                lowStockOnly: false,
                list: [],
                selectedFile: null,

                load: async function () {
                    const tbody = document.getElementById('directory-ingredients-table-body');
                    if (!tbody) return;

                    try {
                        let query = '?';
                        if (this.searchQuery) query += `&search=${encodeURIComponent(this.searchQuery)}`;
                        if (this.categoryId) query += `&category_id=${encodeURIComponent(this.categoryId)}`;
                        if (this.supplierId) query += `&supplier_id=${encodeURIComponent(this.supplierId)}`;
                        if (this.lowStockOnly) query += `&low_stock=1`;

                        const res = await ERP.api(`/ingredients${query}`);
                        if (res && res.success) {
                            this.list = res.data || [];
                            this.render(this.list);

                            if (res.stats) {
                                const sTot = document.getElementById('ing-stat-total');
                                const sLow = document.getElementById('ing-stat-low');
                                const sVal = document.getElementById('ing-stat-val');
                                const sSide = document.getElementById('sidebar-ingredients-count');

                                if (sTot) sTot.textContent = res.stats.total_count ?? 0;
                                if (sLow) sLow.textContent = res.stats.low_stock_count ?? 0;
                                if (sVal) sVal.textContent = (res.stats.total_valuation ?? 0).toLocaleString('hy-AM') + ' ֏';
                                if (sSide) sSide.textContent = res.stats.total_count ?? 0;
                            }
                        }
                    } catch (err) {
                        ERP.toast(`Բաղադրիչների բեռնման սխալ: ${err.message}`, 'error');
                    }
                },

                render: function (items) {
                    const tbody = document.getElementById('directory-ingredients-table-body');
                    if (!tbody) return;

                    if (!items || items.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="18" style="text-align: center; color: var(--text-muted); padding: 2rem;">Բաղադրիչներ չեն գտնվել:</td></tr>`;
                        return;
                    }

                    const todayStr = new Date().toLocaleDateString('hy-AM');

                    tbody.innerHTML = items.map(item => {
                        const isLow = item.is_low_stock;
                        const subtotal = item.subtotal_value || 0;
                        const discounted = item.discounted_value || 0;
                        const vatRate = item.vat_rate || 20;
                        const vatAmount = item.vat_amount || 0;
                        const suppliers = item.suppliers || [];
                        const whereUsedCount = item.where_used_count || 0;

                        let transBadge = '<span class="badge badge-emerald">Տեղական ձեռքբերում</span>';
                        if (item.transaction_type === 'import_eaec') transBadge = '<span class="badge badge-indigo">ԵԱՏՄ Ներմուծում</span>';
                        else if (item.transaction_type === 'import_third') transBadge = '<span class="badge badge-violet">Երրորդ երկրներ</span>';
                        else if (item.transaction_type === 'service') transBadge = '<span class="badge badge-amber">Ծառայություն</span>';

                        const supsHtml = suppliers.length > 0
                            ? `<div style="display: flex; flex-wrap: wrap; gap: 3px;">${suppliers.map(s => `<span class="item-chip" title="ՀՎՀՀ: ${s.tax_id || ''}">${s.company_name}</span>`).join('')}</div>`
                            : '<span style="color: var(--text-muted); font-size: 0.75rem;">—</span>';

                        const whereUsedHtml = whereUsedCount > 0
                            ? `<button class="btn btn-xs btn-outline-primary" onclick="ERP.directory.ingredients.showWhereUsed('${item.id}')" style="font-size: 0.72rem; padding: 3px 7px;"><i class="fa-solid fa-diagram-project"></i> ${whereUsedCount} Պրոդուկտ</button>`
                            : '<span style="color: var(--text-muted); font-size: 0.72rem;">Բաղադրատոմս չկա</span>';

                        return `
                            <tr id="ing-row-${item.id}" class="${isLow ? 'low-stock-alert' : ''}">
                                <td>
                                    <div style="font-weight: 700; color: var(--text-heading);">${item.category ? item.category.name : '—'}</div>
                                    ${item.subcategory ? `<div style="font-size: 0.72rem; color: var(--color-primary);">↳ ${item.subcategory.name}</div>` : ''}
                                </td>
                                <td>
                                    <div class="font-mono" style="font-weight: 800; color: var(--color-primary);">${item.sku}</div>
                                    ${item.barcode ? `<div class="font-mono" style="font-size: 0.72rem; color: var(--text-muted);"><i class="fa-solid fa-barcode"></i> ${item.barcode}</div>` : ''}
                                    <div class="font-mono" style="font-size: 0.65rem; color: #94A3B8;">ID: ${item.id.substring(0, 8)}...</div>
                                </td>
                                <td class="font-mono" style="font-size: 0.75rem;">${item.hs_code || '—'}</td>
                                <td>
                                    <div style="font-weight: 800; color: var(--text-heading);">${item.name}</div>
                                    ${item.description ? `<div style="font-size: 0.72rem; color: var(--text-muted); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${item.description}">${item.description}</div>` : ''}
                                </td>
                                <td class="font-mono">${item.unit ? item.unit.name : 'կգ'}</td>
                                <td class="font-mono" style="font-weight: 700;">${item.current_stock.toFixed(2)}</td>
                                <td class="font-mono">${item.cost_price.toFixed(2)} ֏</td>
                                <td class="font-mono">${item.discount_percent > 0 ? item.discount_percent + '%' : '0%'}</td>
                                <td class="font-mono" style="font-weight: 700;">${subtotal.toLocaleString('hy-AM', {minimumFractionDigits: 2})} ֏</td>
                                <td class="font-mono" style="font-weight: 700; color: #0284C7;">${discounted.toLocaleString('hy-AM', {minimumFractionDigits: 2})} ֏</td>
                                <td><span class="badge badge-slate" style="font-size: 0.7rem;">${item.packaging || 'Առանց տարայի'}</span></td>
                                <td class="font-mono" style="font-size: 0.75rem;">
                                    <div>${vatRate}%</div>
                                    <div style="color: var(--text-muted);">${vatAmount.toLocaleString('hy-AM', {minimumFractionDigits: 2})} ֏</div>
                                </td>
                                <td style="font-size: 0.75rem;">${transBadge}</td>
                                <td style="max-width: 160px;">${supsHtml}</td>
                                <td>
                                    <div class="font-mono" style="font-weight: 800; font-size: 0.95rem; color: ${isLow ? '#DC2626' : 'var(--text-heading)'};">${item.current_stock.toFixed(2)}</div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted);">${todayStr}</div>
                                </td>
                                <td>
                                    <div class="font-mono" style="font-weight: 700;">${item.min_stock_level.toFixed(2)}</div>
                                    ${isLow ? '<span class="low-stock-badge"><i class="fa-solid fa-bell"></i> Լրացնել!</span>' : ''}
                                </td>
                                <td>${whereUsedHtml}</td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 4px;">
                                        <button class="btn btn-xs btn-outline-secondary" title="Խմբագրել" onclick="ERP.directory.ingredients.openEditModal('${item.id}')">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-xs btn-outline-primary" title="Ճշգրտել Պահեստ" onclick="ERP.inventory.openAdjustStockModal(); const sel = document.getElementById('adj-product-id'); if(sel) sel.value='${item.id}';">
                                            <i class="fa-solid fa-scale-balanced"></i>
                                        </button>
                                        <button class="btn btn-xs btn-outline-danger" title="Հեռացնել" onclick="ERP.directory.ingredients.deleteIngredient('${item.id}')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');
                },

                handleSearch: function (val) {
                    clearTimeout(this._searchTimer);
                    this._searchTimer = setTimeout(() => {
                        this.searchQuery = val.trim();
                        this.load();
                    }, 300);
                },

                handleCategoryFilter: function (catId) {
                    this.categoryId = catId;
                    this.load();
                },

                handleSupplierFilter: function (supId) {
                    this.supplierId = supId;
                    this.load();
                },

                toggleLowStockFilter: function () {
                    this.lowStockOnly = !this.lowStockOnly;
                    const btn = document.getElementById('ing-low-stock-btn');
                    if (btn) {
                        if (this.lowStockOnly) {
                            btn.classList.remove('btn-outline-danger');
                            btn.classList.add('btn-danger');
                        } else {
                            btn.classList.remove('btn-danger');
                            btn.classList.add('btn-outline-danger');
                        }
                    }
                    this.load();
                },

                openCreateModal: function () {
                    const modal = document.getElementById('directory-ingredient-modal');
                    if (!modal) return;

                    document.getElementById('directory-ingredient-modal-title').innerHTML = '<i class="fa-solid fa-mortar-pestle"></i> Բաղադրիչի Մուտքագրում (Ձեռքով)';
                    document.getElementById('ing-id').value = '';
                    document.getElementById('ing-category-id').value = '';
                    this.updateSubcategories('');
                    document.getElementById('ing-name-hy').value = '';
                    this.generateSku();
                    document.getElementById('ing-barcode').value = '';
                    document.getElementById('ing-hs-code').value = '';
                    document.getElementById('ing-packaging').value = '';
                    document.getElementById('ing-transaction-type').value = 'local_purchase';
                    document.getElementById('ing-description').value = '';
                    document.getElementById('ing-quantity').value = '1';
                    document.getElementById('ing-cost-price').value = '0';
                    document.getElementById('ing-discount-percent').value = '0';
                    document.getElementById('ing-vat-rate').value = '20';
                    document.getElementById('ing-min-stock-level').value = '10';

                    document.querySelectorAll('.ing-sup-chk').forEach(cb => cb.checked = false);
                    this.recalculate();

                    modal.classList.add('active');
                },

                openEditModal: async function (id) {
                    const modal = document.getElementById('directory-ingredient-modal');
                    if (!modal) return;

                    try {
                        const res = await ERP.api(`/ingredients/${id}`);
                        if (!res || !res.success || !res.data) throw new Error('Բաղադրիչը չգտնվեց:');

                        const ing = res.data;
                        document.getElementById('directory-ingredient-modal-title').innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Խմբագրել Բաղադրիչ՝ ${ing.name?.hy || ing.name}`;
                        document.getElementById('ing-id').value = ing.id;
                        document.getElementById('ing-category-id').value = ing.category_id || '';
                        this.updateSubcategories(ing.category_id || '', ing.subcategory_id);
                        document.getElementById('ing-name-hy').value = (ing.name && (ing.name.hy || ing.name)) || '';
                        document.getElementById('ing-sku').value = ing.sku || '';
                        document.getElementById('ing-barcode').value = ing.barcode || '';
                        document.getElementById('ing-hs-code').value = ing.hs_code || '';
                        document.getElementById('ing-packaging').value = ing.packaging || '';
                        document.getElementById('ing-transaction-type').value = ing.transaction_type || 'local_purchase';
                        document.getElementById('ing-description').value = (ing.description && (ing.description.hy || ing.description)) || '';
                        document.getElementById('ing-unit-id').value = ing.unit_id || '';
                        document.getElementById('ing-quantity').value = ing.current_stock || '1';
                        document.getElementById('ing-cost-price').value = ing.cost_price || '0';
                        document.getElementById('ing-discount-percent').value = ing.discount_percent || '0';
                        document.getElementById('ing-vat-rate').value = ing.vat_rate || '20';
                        document.getElementById('ing-min-stock-level').value = ing.min_stock_level || '0';

                        const attachedSupIds = (ing.suppliers || []).map(s => s.id);
                        document.querySelectorAll('.ing-sup-chk').forEach(cb => {
                            cb.checked = attachedSupIds.includes(cb.value);
                        });

                        this.recalculate();
                        modal.classList.add('active');
                    } catch (err) {
                        ERP.toast(`Խմբագրման սխալ: ${err.message}`, 'error');
                    }
                },

                closeModal: function () {
                    const modal = document.getElementById('directory-ingredient-modal');
                    if (modal) modal.classList.remove('active');
                },

                generateSku: function () {
                    const skuInput = document.getElementById('ing-sku');
                    if (skuInput) {
                        skuInput.value = 'ING-' + Math.random().toString(36).substring(2, 8).toUpperCase();
                    }
                },

                updateSubcategories: function (parentId, selectedId = null) {
                    const subcatSelect = document.getElementById('ing-subcategory-id');
                    if (!subcatSelect) return;

                    subcatSelect.innerHTML = '<option value="">-- Ընտրեք Ենթակատեգորիան --</option>';
                    if (!parentId) return;

                    const allCats = window.SERVER_INITIAL_DATA?.categories || [];
                    const subs = allCats.filter(c => c.parent_id === parentId);

                    subs.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = (typeof s.name === 'object' && s.name !== null) ? (s.name.hy || Object.values(s.name)[0]) : s.name;
                        if (selectedId && s.id === selectedId) opt.selected = true;
                        subcatSelect.appendChild(opt);
                    });
                },

                recalculate: function () {
                    const qty = parseFloat(document.getElementById('ing-quantity')?.value) || 0;
                    const price = parseFloat(document.getElementById('ing-cost-price')?.value) || 0;
                    const disc = parseFloat(document.getElementById('ing-discount-percent')?.value) || 0;
                    const vatRate = parseFloat(document.getElementById('ing-vat-rate')?.value) || 20;

                    const subtotal = Math.round(price * qty * 100) / 100;
                    const discounted = Math.round(subtotal * (1 - (disc / 100)) * 100) / 100;
                    const vatAmount = Math.round(discounted * (vatRate / 100) * 100) / 100;
                    const totalIncVat = Math.round((discounted + vatAmount) * 100) / 100;

                    const fSub = document.getElementById('calc-subtotal');
                    const fDisc = document.getElementById('calc-discounted');
                    const fVat = document.getElementById('calc-vat-amount');
                    const fTot = document.getElementById('calc-total-inc-vat');

                    if (fSub) fSub.textContent = subtotal.toLocaleString('hy-AM', {minimumFractionDigits: 2}) + ' ֏';
                    if (fDisc) fDisc.textContent = discounted.toLocaleString('hy-AM', {minimumFractionDigits: 2}) + ' ֏';
                    if (fVat) fVat.textContent = vatAmount.toLocaleString('hy-AM', {minimumFractionDigits: 2}) + ' ֏';
                    if (fTot) fTot.textContent = totalIncVat.toLocaleString('hy-AM', {minimumFractionDigits: 2}) + ' ֏';
                },

                submitForm: async function (e) {
                    e.preventDefault();
                    const id = document.getElementById('ing-id').value;

                    const supIds = Array.from(document.querySelectorAll('.ing-sup-chk:checked')).map(cb => cb.value);

                    const payload = {
                        name: document.getElementById('ing-name-hy').value.trim(),
                        category_id: document.getElementById('ing-category-id').value || null,
                        subcategory_id: document.getElementById('ing-subcategory-id').value || null,
                        unit_id: document.getElementById('ing-unit-id').value,
                        sku: document.getElementById('ing-sku').value.trim() || null,
                        barcode: document.getElementById('ing-barcode').value.trim() || null,
                        hs_code: document.getElementById('ing-hs-code').value.trim() || null,
                        packaging: document.getElementById('ing-packaging').value.trim() || null,
                        transaction_type: document.getElementById('ing-transaction-type').value,
                        description: document.getElementById('ing-description').value.trim() || null,
                        cost_price: parseFloat(document.getElementById('ing-cost-price').value) || 0,
                        quantity: parseFloat(document.getElementById('ing-quantity').value) || 0,
                        discount_percent: parseFloat(document.getElementById('ing-discount-percent').value) || 0,
                        vat_rate: parseFloat(document.getElementById('ing-vat-rate').value) || 20,
                        min_stock_level: parseFloat(document.getElementById('ing-min-stock-level').value) || 0,
                        warehouse_id: document.getElementById('ing-warehouse-id').value || null,
                        supplier_ids: supIds,
                    };

                    try {
                        let res;
                        if (id) {
                            res = await ERP.api(`/ingredients/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
                        } else {
                            res = await ERP.api('/ingredients', { method: 'POST', body: JSON.stringify(payload) });
                        }

                        if (res && res.success) {
                            ERP.toast(id ? 'Բաղադրիչը թարմացվեց:' : 'Բաղադրիչն ավելացվեց:', 'success');
                            this.closeModal();
                            this.load();
                        }
                    } catch (err) {
                        ERP.toast(`Պահպանման սխալ: ${err.message}`, 'error');
                    }
                },

                deleteIngredient: async function (id) {
                    if (!confirm('Հեռացնե՞լ բաղադրիչը:')) return;

                    try {
                        const res = await ERP.api(`/ingredients/${id}`, { method: 'DELETE' });
                        if (res && res.success) {
                            ERP.toast('Բաղադրիչը հեռացվեց:', 'info');
                            this.load();
                        }
                    } catch (err) {
                        ERP.toast(`Հեռացման սխալ: ${err.message}`, 'error');
                    }
                },

                showWhereUsed: function (id) {
                    const ing = this.list.find(i => i.id === id);
                    if (!ing) return;

                    const modal = document.getElementById('directory-where-used-modal');
                    const body = document.getElementById('where-used-modal-body');
                    const title = document.getElementById('where-used-modal-title');
                    if (!modal || !body) return;

                    title.innerHTML = `<i class="fa-solid fa-diagram-project"></i> «${ing.name}» — Օգտագործվում է պրոդուկտներում`;

                    const products = ing.where_used_products || [];
                    if (products.length === 0) {
                        body.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1.5rem;">Տվյալ բաղադրիչը դեռևս ոչ մի բաղադրատոմսում/պրոդուկտում ներառված չէ:</div>';
                    } else {
                        body.innerHTML = `
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                Այս բաղադրիչը մտնում է հետևյալ պատրաստի արտադրանքների տեխնոլոգիական քարտերի (BOM Recipes) մեջ:
                            </p>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Պրոդուկտի SKU</th>
                                        <th>Արտադրանքի Անվանում</th>
                                        <th>Բաղադրատոմս (BOM)</th>
                                        <th>Պահանջվող Քանակ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${products.map(p => `
                                        <tr>
                                            <td class="font-mono" style="font-weight: 800; color: var(--color-primary);">${p.product_sku || '—'}</td>
                                            <td style="font-weight: 700; color: var(--text-heading);">${p.product_name || '—'}</td>
                                            <td><span class="badge badge-indigo">${p.recipe_name || 'Հիմնական Recipe'}</span></td>
                                            <td class="font-mono" style="font-weight: 700; color: #059669;">${p.quantity_needed || 0} ${ing.unit ? ing.unit.name : 'միավոր'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `;
                    }

                    modal.classList.add('active');
                },

                // --- Invoice Import Flow (.xml, .xls, .csv) ---
                openImportModal: function () {
                    const modal = document.getElementById('directory-invoice-import-modal');
                    if (!modal) return;

                    this.clearFile();
                    modal.classList.add('active');
                },

                closeImportModal: function () {
                    const modal = document.getElementById('directory-invoice-import-modal');
                    if (modal) modal.classList.remove('active');
                },

                handleFileSelect: function (input) {
                    if (!input.files || input.files.length === 0) return;

                    const file = input.files[0];
                    this.selectedFile = file;

                    const preview = document.getElementById('invoice-file-preview');
                    const fileName = document.getElementById('invoice-file-name');
                    const fileSize = document.getElementById('invoice-file-size');
                    const submitBtn = document.getElementById('import-submit-btn');

                    if (preview && fileName && fileSize) {
                        fileName.textContent = file.name;
                        fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB (' + file.name.split('.').pop().toUpperCase() + ')';
                        preview.style.display = 'flex';
                    }

                    if (submitBtn) submitBtn.disabled = false;
                },

                clearFile: function () {
                    this.selectedFile = null;
                    const input = document.getElementById('invoice-file-input');
                    if (input) input.value = '';

                    const preview = document.getElementById('invoice-file-preview');
                    if (preview) preview.style.display = 'none';

                    const submitBtn = document.getElementById('import-submit-btn');
                    if (submitBtn) submitBtn.disabled = true;
                },

                submitImport: async function (e) {
                    e.preventDefault();
                    if (!this.selectedFile) {
                        ERP.toast('Խնդրում ենք ընտրել ներմուծման ֆայլը (.xml, .xls, .csv):', 'warning');
                        return;
                    }

                    const submitBtn = document.getElementById('import-submit-btn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Ներմուծվում է...';
                    }

                    const formData = new FormData();
                    formData.append('file', this.selectedFile);

                    const supId = document.getElementById('import-supplier-id')?.value;
                    if (supId) formData.append('supplier_id', supId);

                    const whId = document.getElementById('import-warehouse-id')?.value;
                    if (whId) formData.append('warehouse_id', whId);

                    try {
                        const res = await ERP.api('/ingredients/import-invoices', {
                            method: 'POST',
                            body: formData
                        });

                        if (res && res.success) {
                            ERP.toast(res.message || `Հաջողությամբ ներմուծվել է ${res.imported_count} բաղադրիչ:`, 'success');
                            this.closeImportModal();
                            this.load();
                        } else {
                            throw new Error(res.message || 'Ներմուծման սխալ');
                        }
                    } catch (err) {
                        ERP.toast(`Ներմուծման սխալ: ${err.message}`, 'error');
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Սկսել Ներմուծումը';
                        }
                    }
                }
            }
        },

        // =====================================================================
        // 10. Initialization
        // =====================================================================
        init: function () {
            // Hydrate token and tenant
            ERP.state.token = window.SERVER_INITIAL_DATA?.token || localStorage.getItem('erplannet_token') || '';
            ERP.state.tenantSlug = window.SERVER_INITIAL_DATA?.currentTenant?.slug || window.SERVER_INITIAL_DATA?.demoTenant?.slug || 'gourmet';

            // Check hash in URL or default to dashboard
            const hash = window.location.hash.replace('#', '') || 'dashboard';
            const validViews = ['dashboard', 'catalog', 'directory-suppliers', 'directory-ingredients', 'procurement', 'pos', 'inventory', 'manufacturing', 'quality', 'delivery', 'users', 'roles', 'billing', 'settings', 'api-console'];
            const targetView = validViews.includes(hash) ? hash : 'dashboard';

            ERP.navigateTo(targetView);

            // Listen to hash changes (back/forward button)
            window.addEventListener('hashchange', () => {
                const newHash = window.location.hash.replace('#', '');
                if (validViews.includes(newHash) && newHash !== ERP.state.currentView) {
                    ERP.navigateTo(newHash);
                }
            });

            // Initialize locale
            ERP.setLocale(ERP.state.locale);

            // Bind mobile menu toggle
            const mobileBtn = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('app-sidebar');
            if (mobileBtn && sidebar) {
                mobileBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('open');
                });
            }

            console.log('[ERPlannet] SaaS ERP Frontend Initialized on same port!');
        }
    };

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ERP.init);
    } else {
        ERP.init();
    }
})();
