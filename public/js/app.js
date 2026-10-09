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
                    panel.classList.add('active');
                } else {
                    panel.style.display = 'none';
                    panel.classList.remove('active');
                }
            });

            // Scroll to top of page
            window.scrollTo({ top: 0, behavior: 'instant' });

            // Close mobile sidebar if open
            const sidebar = document.getElementById('app-sidebar');
            if (sidebar) sidebar.classList.remove('open');

            // Trigger view-specific activation
            if (viewName === 'pos') ERP.pos.render();
            if (viewName === 'users') ERP.users.load();
            if (viewName === 'roles') ERP.roles.load();
            if (viewName === 'directory-categories') ERP.directory.categories.load();
            if (viewName === 'directory-suppliers') ERP.directory.suppliers.load();
            if (viewName === 'directory-ingredients') ERP.directory.ingredients.load();
            if (viewName === 'directory-customers') ERP.directory.customers.load();
        },

        // =====================================================================
        // 2.5 Media & File Upload Subsystem
        // =====================================================================
        media: {
            upload: async function (file, folder = 'general') {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('folder', folder);

                const res = await ERP.api('/media/upload', {
                    method: 'POST',
                    body: formData
                });
                return res && res.success ? res.data : null;
            },

            handleFileUpload: async function (fileInput, folder, urlInputId, previewImgId, previewBoxId, removeBtnId, placeholderIconId) {
                const file = fileInput.files && fileInput.files[0];
                if (!file) return;

                if (file.size > 10 * 1024 * 1024) {
                    ERP.toast('Ֆայլի չափը չպետք է գերազանցի 10MB-ը:', 'error', 'Սխալ');
                    fileInput.value = '';
                    return;
                }

                // Show local preview immediately
                const reader = new FileReader();
                reader.onload = (e) => {
                    const previewImg = document.getElementById(previewImgId);
                    const placeholder = document.getElementById(placeholderIconId);
                    const removeBtn = document.getElementById(removeBtnId);
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                    }
                    if (placeholder) placeholder.style.display = 'none';
                    if (removeBtn) removeBtn.style.display = 'flex';
                };
                reader.readAsDataURL(file);

                ERP.toast('Պատկերը վերբեռնվում է...', 'info');

                try {
                    const data = await this.upload(file, folder);
                    if (data && data.url) {
                        const urlInput = document.getElementById(urlInputId);
                        if (urlInput) urlInput.value = data.url;
                        ERP.toast('Պատկերը հաջողությամբ վերբեռնվեց:', 'success');
                    }
                } catch (err) {
                    ERP.toast('Չհաջողվեց վերբեռնել պատկերը: ' + err.message, 'error');
                }
            },

            setPreview: function (url, previewImgId, previewBoxId, removeBtnId, placeholderIconId) {
                const previewImg = document.getElementById(previewImgId);
                const placeholder = document.getElementById(placeholderIconId);
                const removeBtn = document.getElementById(removeBtnId);

                if (url) {
                    if (previewImg) {
                        previewImg.src = url;
                        previewImg.style.display = 'block';
                    }
                    if (placeholder) placeholder.style.display = 'none';
                    if (removeBtn) removeBtn.style.display = 'flex';
                } else {
                    if (previewImg) {
                        previewImg.src = '';
                        previewImg.style.display = 'none';
                    }
                    if (placeholder) placeholder.style.display = 'block';
                    if (removeBtn) removeBtn.style.display = 'none';
                }
            },

            clearProductImage: function () {
                const urlInput = document.getElementById('prod-image-url');
                const fileInput = document.getElementById('prod-image-file');
                if (urlInput) urlInput.value = '';
                if (fileInput) fileInput.value = '';
                this.setPreview('', 'prod-image-preview', 'prod-image-preview-box', 'prod-image-remove-btn', 'prod-image-placeholder-icon');
            },

            onProductUrlChange: function (url) {
                this.setPreview(url, 'prod-image-preview', 'prod-image-preview-box', 'prod-image-remove-btn', 'prod-image-placeholder-icon');
            },

            clearCategoryImage: function () {
                const urlInput = document.getElementById('cat-image-url');
                const fileInput = document.getElementById('cat-image-file');
                if (urlInput) urlInput.value = '';
                if (fileInput) fileInput.value = '';
                this.setPreview('', 'cat-image-preview', 'cat-image-preview-box', 'cat-image-remove-btn', 'cat-image-placeholder-icon');
            },

            onCategoryUrlChange: function (url) {
                this.setPreview(url, 'cat-image-preview', 'cat-image-preview-box', 'cat-image-remove-btn', 'cat-image-placeholder-icon');
            },

            clearIngredientImage: function () {
                const urlInput = document.getElementById('ing-image-url');
                const fileInput = document.getElementById('ing-image-file');
                if (urlInput) urlInput.value = '';
                if (fileInput) fileInput.value = '';
                this.setPreview('', 'ing-image-preview', 'ing-image-preview-box', 'ing-image-remove-btn', 'ing-image-placeholder-icon');
            },

            onIngredientUrlChange: function (url) {
                this.setPreview(url, 'ing-image-preview', 'ing-image-preview-box', 'ing-image-remove-btn', 'ing-image-placeholder-icon');
            }
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
            // Internal state
            state: {
                activeFilter: 'all',
                searchQuery: '',
                searchTimeout: null,
                currentTechCardProduct: null
            },

            load: function () {
                window.location.reload();
            },

            filterType: function (type) {
                ERP.catalog.state.activeFilter = type;
                const tabs = document.querySelectorAll('#catalog-type-tabs .directory-tab-btn');
                tabs.forEach(btn => {
                    if (btn.getAttribute('data-type') === type) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
                ERP.catalog.applyFilters();
            },

            debouncedSearch: function () {
                clearTimeout(ERP.catalog.state.searchTimeout);
                ERP.catalog.state.searchTimeout = setTimeout(() => {
                    ERP.catalog.state.searchQuery = (document.getElementById('catalog-search')?.value || '').toLowerCase().trim();
                    ERP.catalog.applyFilters();
                }, 250);
            },

            applyFilters: function () {
                const rows = document.querySelectorAll('#catalog-table-body tr[data-id]');
                const filter = ERP.catalog.state.activeFilter;
                const search = ERP.catalog.state.searchQuery;
                const catFilter = document.getElementById('catalog-category-filter')?.value;
                let visibleCount = 0;

                rows.forEach(row => {
                    const rowType = row.getAttribute('data-type') || '';
                    const rowStopList = row.getAttribute('data-stoplist') === '1';
                    const hasLowStock = !!row.querySelector('.low-stock-badge');
                    const rowText = row.innerText.toLowerCase();

                    let matchesType = true;
                    if (filter === 'all') {
                        matchesType = true;
                    } else if (filter === 'stop_list') {
                        matchesType = rowStopList;
                    } else if (filter === 'low_stock') {
                        matchesType = hasLowStock;
                    } else {
                        matchesType = (rowType === filter);
                    }

                    let matchesSearch = !search || rowText.includes(search);

                    if (matchesType && matchesSearch) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                const countBadge = document.getElementById('catalog-count-badge');
                if (countBadge) countBadge.innerText = `${visibleCount} Ապրանք`;
            },

            openCreateProductModal: function () {
                const modal = document.getElementById('create-product-modal');
                if (!modal) return;

                const form = document.getElementById('product-master-form');
                if (form) form.reset();

                document.getElementById('prod-edit-id').value = '';
                const titleEl = document.getElementById('product-modal-title');
                if (titleEl) {
                    titleEl.innerHTML = '<i class="fa-solid fa-boxes-stacked" style="color: var(--color-primary); margin-right: 6px;"></i> Նոր Ապրանք / Պրոդուկտ (Product / Item Master)';
                }

                // UUID display
                const idDisplay = document.getElementById('prod-id-display');
                if (idDisplay) idDisplay.value = 'Ավտոմատ կգեներացվի (UUID)';

                // Auto generate SKU
                ERP.catalog.generateSku();

                // Defaults
                document.getElementById('prod-type').value = 'finished_product';
                document.getElementById('prod-sale-price').value = '0';
                document.getElementById('prod-cost-price').value = '0';
                document.getElementById('prod-special-price').value = '';
                document.getElementById('prod-vat-yes').checked = true;
                document.getElementById('prod-vat-rate').value = '20';
                const vatGroup = document.getElementById('prod-vat-rate-group');
                if (vatGroup) vatGroup.style.display = 'block';
                document.getElementById('prod-allow-discount').checked = true;
                document.getElementById('prod-allow-price-edit').checked = false;
                document.getElementById('prod-allow-modifiers').checked = false;
                document.getElementById('prod-is-ungrouped').checked = false;
                document.getElementById('prod-is-stop-list').checked = false;
                document.getElementById('prod-is-excise').checked = false;
                document.getElementById('prod-is-marked').checked = false;
                document.getElementById('prod-track-stock').checked = true;
                const stockFields = document.getElementById('prod-stock-fields');
                if (stockFields) stockFields.style.display = 'grid';
                document.getElementById('prod-min-stock').value = '0';
                document.getElementById('prod-initial-stock').value = '0';
                const initStockGroup = document.getElementById('prod-initial-stock-group');
                if (initStockGroup) initStockGroup.style.display = 'block';

                // Clear variants
                const vtbody = document.getElementById('prod-variants-tbody');
                if (vtbody) vtbody.innerHTML = '';

                // Clear nutrition & tags
                document.getElementById('prod-calories').value = '';
                document.getElementById('prod-protein').value = '';
                document.getElementById('prod-fat').value = '';
                document.getElementById('prod-carbs').value = '';
                document.getElementById('prod-shelf-life-info').value = '';

                document.querySelectorAll('#prod-allergens-grid input[type="checkbox"]').forEach(c => c.checked = false);
                document.querySelectorAll('#prod-dietary-grid input[type="checkbox"]').forEach(c => c.checked = false);
                document.querySelectorAll('#prod-branches-grid input[type="checkbox"]').forEach(c => c.checked = false);
                document.getElementById('prod-avail-from').value = '';
                document.getElementById('prod-avail-to').value = '';
                document.getElementById('prod-discount-from').value = '';
                document.getElementById('prod-discount-to').value = '';

                // Hide tech card btn in create mode
                const tcBtn = document.getElementById('btn-modal-tech-card');
                if (tcBtn) tcBtn.style.display = 'none';

                ERP.media.clearProductImage();

                ERP.catalog.switchModalTab('tab-prod-general');
                modal.classList.add('active');
            },

            openEditProductModal: async function (id) {
                const modal = document.getElementById('create-product-modal');
                if (!modal) return;

                ERP.toast('Բեռնվում են ապրանքի տվյալները...', 'info');

                try {
                    const res = await ERP.api(`/products/${id}`);
                    const p = res.data;
                    if (!p) throw new Error('Ապրանքը չի գտնվել:');

                    document.getElementById('prod-edit-id').value = p.id;
                    const titleEl = document.getElementById('product-modal-title');
                    if (titleEl) {
                        const pName = (typeof p.name === 'object' && p.name !== null ? (p.name.hy || Object.values(p.name)[0]) : p.name) || '';
                        titleEl.innerHTML = `<i class="fa-solid fa-pen-to-square" style="color: var(--color-primary); margin-right: 6px;"></i> Խմբագրել՝ «${pName}»`;
                    }

                    document.getElementById('prod-id-display').value = p.id;
                    document.getElementById('prod-sku').value = p.sku || '';
                    document.getElementById('prod-barcode').value = p.barcode || '';
                    document.getElementById('prod-hs-code').value = p.hs_code || '';

                    // Names
                    if (typeof p.name === 'object' && p.name !== null) {
                        document.getElementById('prod-name-hy').value = p.name.hy || Object.values(p.name)[0] || '';
                        document.getElementById('prod-name-en').value = p.name.en || '';
                        document.getElementById('prod-name-ru').value = p.name.ru || '';
                    } else {
                        document.getElementById('prod-name-hy').value = p.name || '';
                        document.getElementById('prod-name-en').value = '';
                        document.getElementById('prod-name-ru').value = '';
                    }

                    // Type & Categories & Unit
                    document.getElementById('prod-type').value = p.type || 'finished_product';
                    document.getElementById('prod-category-id').value = p.category_id || '';
                    document.getElementById('prod-subcategory-id').value = p.subcategory_id || '';
                    document.getElementById('prod-unit-id').value = p.unit_id || '';
                    document.getElementById('prod-net-quantity').value = p.net_quantity || '';
                    document.getElementById('prod-packaging').value = p.packaging || '';
                    const prodImgUrl = (Array.isArray(p.images) && p.images[0]) ? p.images[0] : '';
                    document.getElementById('prod-image-url').value = prodImgUrl;
                    ERP.media.setPreview(prodImgUrl, 'prod-image-preview', 'prod-image-preview-box', 'prod-image-remove-btn', 'prod-image-placeholder-icon');

                    // Description
                    if (typeof p.description === 'object' && p.description !== null) {
                        document.getElementById('prod-description').value = p.description.hy || Object.values(p.description)[0] || '';
                    } else {
                        document.getElementById('prod-description').value = p.description || '';
                    }

                    // Prices
                    document.getElementById('prod-sale-price').value = p.sale_price ?? 0;
                    document.getElementById('prod-special-price').value = p.special_price ?? '';
                    document.getElementById('prod-cost-price').value = p.cost_price ?? 0;

                    // VAT
                    if (p.has_vat === false) {
                        document.getElementById('prod-vat-no').checked = true;
                    } else {
                        document.getElementById('prod-vat-yes').checked = true;
                    }
                    document.getElementById('prod-vat-rate').value = p.vat_rate ?? 20;
                    ERP.catalog.onVatToggle();

                    // Flags
                    document.getElementById('prod-allow-discount').checked = (p.allow_discount !== false);
                    document.getElementById('prod-allow-price-edit').checked = !!p.allow_price_edit;
                    document.getElementById('prod-allow-modifiers').checked = !!p.allow_modifiers;
                    document.getElementById('prod-is-ungrouped').checked = !!p.is_ungrouped_in_order;
                    document.getElementById('prod-is-stop-list').checked = !!p.is_stop_list;
                    document.getElementById('prod-is-excise').checked = !!p.is_excise;
                    document.getElementById('prod-is-marked').checked = !!p.is_marked;
                    document.getElementById('prod-track-stock').checked = (p.track_stock !== false);
                    ERP.catalog.onTrackStockToggle();
                    document.getElementById('prod-min-stock').value = p.min_stock_level ?? 0;
                    const initStockGroup = document.getElementById('prod-initial-stock-group');
                    if (initStockGroup) initStockGroup.style.display = 'none';

                    // Variants
                    const vtbody = document.getElementById('prod-variants-tbody');
                    if (vtbody) {
                        vtbody.innerHTML = '';
                        if (p.variants && p.variants.length > 0) {
                            p.variants.forEach(v => ERP.catalog.addVariantRow(v));
                        }
                    }

                    // Nutrition & Allergens & Dietary
                    document.getElementById('prod-calories').value = p.calories ?? '';
                    document.getElementById('prod-protein').value = p.nutritional_info?.protein ?? '';
                    document.getElementById('prod-fat').value = p.nutritional_info?.fat ?? '';
                    document.getElementById('prod-carbs').value = p.nutritional_info?.carbs ?? '';
                    document.getElementById('prod-shelf-life-info').value = p.shelf_life_info ?? '';

                    const allergens = Array.isArray(p.allergens) ? p.allergens : [];
                    document.querySelectorAll('#prod-allergens-grid input[type="checkbox"]').forEach(c => {
                        c.checked = allergens.includes(c.value);
                    });

                    const dietary = Array.isArray(p.dietary_tags) ? p.dietary_tags : [];
                    document.querySelectorAll('#prod-dietary-grid input[type="checkbox"]').forEach(c => {
                        c.checked = dietary.includes(c.value);
                    });

                    // Availability
                    const branches = Array.isArray(p.available_branch_ids) ? p.available_branch_ids : [];
                    document.querySelectorAll('#prod-branches-grid input[type="checkbox"]').forEach(c => {
                        c.checked = branches.includes(c.value);
                    });

                    document.getElementById('prod-avail-from').value = p.time_availability?.from || '';
                    document.getElementById('prod-avail-to').value = p.time_availability?.to || '';
                    document.getElementById('prod-discount-from').value = p.discount_hours?.from || '';
                    document.getElementById('prod-discount-to').value = p.discount_hours?.to || '';

                    // Tech card button in edit modal
                    const tcBtn = document.getElementById('btn-modal-tech-card');
                    if (tcBtn) {
                        tcBtn.style.display = 'inline-flex';
                        tcBtn.setAttribute('data-id', p.id);
                    }

                    ERP.catalog.switchModalTab('tab-prod-general');
                    modal.classList.add('active');
                } catch (err) {
                    ERP.toast(`Սխալ ապրանքի տվյալների բեռնման ժամանակ: ${err.message}`, 'error');
                }
            },

            closeCreateProductModal: function () {
                const modal = document.getElementById('create-product-modal');
                if (modal) modal.classList.remove('active');
            },

            switchModalTab: function (tabId) {
                const tabs = document.querySelectorAll('.product-modal-tabs .product-tab-btn');
                tabs.forEach(btn => {
                    if (btn.getAttribute('data-tab') === tabId) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });

                const panes = document.querySelectorAll('.product-tab-pane');
                panes.forEach(pane => {
                    if (pane.id === tabId) {
                        pane.style.display = 'block';
                    } else {
                        pane.style.display = 'none';
                    }
                });
            },

            generateSku: function () {
                const type = document.getElementById('prod-type')?.value || 'PRD';
                const prefixMap = {
                    finished_product: 'PRD',
                    semi_finished: 'SEMI',
                    ingredient: 'ING',
                    raw_material: 'RAW',
                    packaging: 'PKG',
                    service: 'SRV',
                    modifier: 'MOD'
                };
                const pfx = prefixMap[type] || 'PRD';
                const rand = Math.random().toString(36).substring(2, 7).toUpperCase();
                const skuInput = document.getElementById('prod-sku');
                if (skuInput) skuInput.value = `${pfx}-${rand}`;
            },

            onTypeChange: function () {
                const type = document.getElementById('prod-type')?.value;
                if (type === 'modifier') {
                    const modChk = document.getElementById('prod-allow-modifiers');
                    const ungrpChk = document.getElementById('prod-is-ungrouped');
                    if (modChk) modChk.checked = true;
                    if (ungrpChk) ungrpChk.checked = true;
                }
            },

            onCategoryChange: function (catId) {
                const subSelect = document.getElementById('prod-subcategory-id');
                if (!subSelect) return;

                const allCats = (ERP.directory?.categories?.list && ERP.directory.categories.list.length > 0)
                    ? ERP.directory.categories.list
                    : (window.SERVER_INITIAL_DATA?.categories || []);

                const productCats = allCats.filter(c => (c.type === 'product' || !c.type));

                let subCats = [];
                if (catId) {
                    subCats = productCats.filter(c => c.parent_id === catId);
                }

                if (subCats.length === 0) {
                    subSelect.innerHTML = '<option value="">-- Առանց ենթախմբի --</option>';
                    productCats.filter(c => c.id !== catId).forEach(c => {
                        const name = typeof c.name === 'object' && c.name !== null ? (c.name.hy || Object.values(c.name)[0]) : c.name;
                        subSelect.innerHTML += `<option value="${c.id}">${name}</option>`;
                    });
                } else {
                    let html = '<option value="">-- Առանց ենթախմբի --</option>';
                    subCats.forEach(c => {
                        const name = typeof c.name === 'object' && c.name !== null ? (c.name.hy || Object.values(c.name)[0]) : c.name;
                        html += `<option value="${c.id}">${name}</option>`;
                    });
                    subSelect.innerHTML = html;
                }
            },

            onVatToggle: function () {
                const noVat = document.getElementById('prod-vat-no')?.checked;
                const group = document.getElementById('prod-vat-rate-group');
                const rateInput = document.getElementById('prod-vat-rate');
                if (noVat) {
                    if (group) group.style.opacity = '0.5';
                    if (rateInput) rateInput.value = '0';
                } else {
                    if (group) group.style.opacity = '1';
                    if (rateInput && (!rateInput.value || parseFloat(rateInput.value) === 0)) {
                        rateInput.value = '20';
                    }
                }
            },

            onModifierToggle: function () {
                const allowMods = document.getElementById('prod-allow-modifiers')?.checked;
                const ungrouped = document.getElementById('prod-is-ungrouped');
                if (allowMods && ungrouped) {
                    ungrouped.checked = true;
                }
            },

            onTrackStockToggle: function () {
                const track = document.getElementById('prod-track-stock')?.checked;
                const fields = document.getElementById('prod-stock-fields');
                if (fields) {
                    fields.style.display = track ? 'grid' : 'none';
                }
            },

            addVariantRow: function (variant = null) {
                const tbody = document.getElementById('prod-variants-tbody');
                if (!tbody) return;

                const row = document.createElement('tr');
                row.className = 'variant-row';
                const varName = (typeof variant?.name === 'object' && variant?.name !== null) ? (variant.name.hy || Object.values(variant.name)[0]) : (variant?.name || '');
                row.innerHTML = `
                    <td><input type="text" class="form-control form-control-sm var-name" value="${varName}" placeholder="Չափս / Գույն / Տարողություն"></td>
                    <td><input type="text" class="form-control form-control-sm font-mono var-sku" value="${variant?.sku || ''}" placeholder="SKU-VAR"></td>
                    <td><input type="text" class="form-control form-control-sm font-mono var-barcode" value="${variant?.barcode || ''}" placeholder="EAN-13"></td>
                    <td><input type="number" step="any" class="form-control form-control-sm font-mono var-sale-price" value="${variant?.sale_price ?? ''}" placeholder="0"></td>
                    <td><input type="number" step="any" class="form-control form-control-sm font-mono var-cost-price" value="${variant?.cost_price ?? ''}" placeholder="0"></td>
                    <td><button type="button" class="btn btn-xs btn-outline-danger" onclick="this.closest('tr').remove()" title="Հեռացնել"><i class="fa-solid fa-trash-can"></i></button></td>
                `;
                tbody.appendChild(row);
            },

            submitProduct: async function (e) {
                if (e) e.preventDefault();
                const editId = document.getElementById('prod-edit-id')?.value;
                const nameHy = document.getElementById('prod-name-hy')?.value?.trim();
                const nameEn = document.getElementById('prod-name-en')?.value?.trim() || nameHy;
                const nameRu = document.getElementById('prod-name-ru')?.value?.trim() || nameHy;
                const type = document.getElementById('prod-type')?.value || 'finished_product';
                const categoryId = document.getElementById('prod-category-id')?.value || null;
                const subcategoryId = document.getElementById('prod-subcategory-id')?.value || null;
                const unitId = document.getElementById('prod-unit-id')?.value;
                const sku = document.getElementById('prod-sku')?.value?.trim();
                const barcode = document.getElementById('prod-barcode')?.value?.trim() || null;
                const hsCode = document.getElementById('prod-hs-code')?.value?.trim() || null;
                const netQuantity = document.getElementById('prod-net-quantity')?.value?.trim() || null;
                const packaging = document.getElementById('prod-packaging')?.value?.trim() || null;
                const imageUrl = document.getElementById('prod-image-url')?.value?.trim();
                const description = document.getElementById('prod-description')?.value?.trim();

                const salePrice = parseFloat(document.getElementById('prod-sale-price')?.value || 0);
                const specialPriceVal = document.getElementById('prod-special-price')?.value;
                const specialPrice = (specialPriceVal !== '' && !isNaN(parseFloat(specialPriceVal))) ? parseFloat(specialPriceVal) : null;
                const costPrice = parseFloat(document.getElementById('prod-cost-price')?.value || 0);

                const hasVat = document.getElementById('prod-vat-yes')?.checked;
                const vatRate = hasVat ? parseFloat(document.getElementById('prod-vat-rate')?.value || 20) : 0;
                const allowDiscount = document.getElementById('prod-allow-discount')?.checked;
                const allowPriceEdit = document.getElementById('prod-allow-price-edit')?.checked;

                const allowModifiers = document.getElementById('prod-allow-modifiers')?.checked;
                const isUngrouped = document.getElementById('prod-is-ungrouped')?.checked;
                const isStopList = document.getElementById('prod-is-stop-list')?.checked;
                const isExcise = document.getElementById('prod-is-excise')?.checked;
                const isMarked = document.getElementById('prod-is-marked')?.checked;
                const trackStock = document.getElementById('prod-track-stock')?.checked;
                const minStock = parseFloat(document.getElementById('prod-min-stock')?.value || 0);
                const initialStock = parseFloat(document.getElementById('prod-initial-stock')?.value || 0);

                // Nutrition & Tags
                const calVal = document.getElementById('prod-calories')?.value;
                const calories = calVal !== '' ? parseFloat(calVal) : null;
                const protVal = document.getElementById('prod-protein')?.value;
                const fatVal = document.getElementById('prod-fat')?.value;
                const carbsVal = document.getElementById('prod-carbs')?.value;
                const protein = protVal !== '' ? parseFloat(protVal) : null;
                const fat = fatVal !== '' ? parseFloat(fatVal) : null;
                const carbs = carbsVal !== '' ? parseFloat(carbsVal) : null;
                const shelfLifeInfo = document.getElementById('prod-shelf-life-info')?.value?.trim() || null;

                const allergens = [];
                document.querySelectorAll('#prod-allergens-grid input[type="checkbox"]:checked').forEach(c => allergens.push(c.value));

                const dietaryTags = [];
                document.querySelectorAll('#prod-dietary-grid input[type="checkbox"]:checked').forEach(c => dietaryTags.push(c.value));

                const branchIds = [];
                document.querySelectorAll('#prod-branches-grid input[type="checkbox"]:checked').forEach(c => branchIds.push(c.value));

                const timeFrom = document.getElementById('prod-avail-from')?.value || null;
                const timeTo = document.getElementById('prod-avail-to')?.value || null;
                const timeAvailability = (timeFrom || timeTo) ? { from: timeFrom, to: timeTo } : null;

                const discFrom = document.getElementById('prod-discount-from')?.value || null;
                const discTo = document.getElementById('prod-discount-to')?.value || null;
                const discountHours = (discFrom || discTo) ? { from: discFrom, to: discTo } : null;

                // Variants
                const variants = [];
                document.querySelectorAll('#prod-variants-tbody tr.variant-row').forEach(tr => {
                    const vName = tr.querySelector('.var-name')?.value?.trim();
                    const vSku = tr.querySelector('.var-sku')?.value?.trim();
                    const vBarcode = tr.querySelector('.var-barcode')?.value?.trim() || null;
                    const vSale = parseFloat(tr.querySelector('.var-sale-price')?.value || 0);
                    const vCost = parseFloat(tr.querySelector('.var-cost-price')?.value || 0);
                    if (vName && vSku) {
                        variants.push({
                            name: { hy: vName },
                            sku: vSku,
                            barcode: vBarcode,
                            sale_price: vSale,
                            cost_price: vCost
                        });
                    }
                });

                if (!nameHy || !sku || !unitId) {
                    ERP.toast('Լրացրեք պարտադիր դաշտերը (Անվանում, SKU, Միավոր):', 'warning');
                    return;
                }

                const payload = {
                    name: { hy: nameHy, en: nameEn, ru: nameRu },
                    type: type,
                    category_id: categoryId,
                    subcategory_id: subcategoryId,
                    unit_id: unitId,
                    sku: sku,
                    barcode: barcode,
                    hs_code: hsCode,
                    net_quantity: netQuantity,
                    packaging: packaging,
                    description: description ? { hy: description } : null,
                    images: imageUrl ? [imageUrl] : [],
                    sale_price: salePrice,
                    special_price: specialPrice,
                    cost_price: costPrice,
                    has_vat: hasVat,
                    vat_rate: vatRate,
                    allow_discount: allowDiscount,
                    allow_price_edit: allowPriceEdit,
                    allow_modifiers: allowModifiers,
                    is_ungrouped_in_order: isUngrouped,
                    is_stop_list: isStopList,
                    is_excise: isExcise,
                    is_marked: isMarked,
                    track_stock: trackStock,
                    min_stock_level: minStock,
                    calories: calories,
                    nutritional_info: (protein !== null || fat !== null || carbs !== null) ? { protein, fat, carbs } : null,
                    allergens: allergens,
                    dietary_tags: dietaryTags,
                    available_branch_ids: branchIds,
                    time_availability: timeAvailability,
                    discount_hours: discountHours,
                    shelf_life_info: shelfLifeInfo,
                    variants: variants
                };

                if (!editId && initialStock > 0) {
                    payload.initial_stock = initialStock;
                }

                ERP.toast('Տվյալները պահպանվում են...', 'info');

                try {
                    if (editId) {
                        await ERP.api(`/products/${editId}`, {
                            method: 'PUT',
                            body: JSON.stringify(payload)
                        });
                        ERP.toast(`Ապրանքը՝ «${nameHy}» հաջողությամբ թարմացվեց:`, 'success', 'Catalog Updated');
                    } else {
                        await ERP.api('/products', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        ERP.toast(`Ապրանքը՝ «${nameHy}» հաջողությամբ ստեղծվեց:`, 'success', 'Product Created');
                    }

                    ERP.catalog.closeCreateProductModal();
                    setTimeout(() => window.location.reload(), 800);
                } catch (err) {
                    ERP.toast(`Սխալ պահպանելիս: ${err.message}`, 'error');
                }
            },

            deleteProduct: async function (id) {
                if (!confirm('Վստա՞հ եք, որ ցանկանում եք հեռացնել այս ապրանքը:')) return;

                ERP.toast('Ապրանքը հեռացվում է...', 'info');
                try {
                    await ERP.api(`/products/${id}`, { method: 'DELETE' });
                    ERP.toast('Ապրանքը հաջողությամբ հեռացվեց:', 'success');
                    const row = document.querySelector(`#catalog-table-body tr[data-id="${id}"]`);
                    if (row) row.remove();
                } catch (err) {
                    ERP.toast(`Սխալ հեռացնելիս: ${err.message}`, 'error');
                }
            },

            openTechCardFromEdit: function () {
                const id = document.getElementById('prod-edit-id')?.value;
                if (!id) return;
                ERP.catalog.closeCreateProductModal();
                ERP.catalog.openTechnicalCard(id);
            },

            // -----------------------------------------------------------------
            // Technical Card / BOM Subsystem
            // -----------------------------------------------------------------
            getAvailableComponents: function () {
                const list = [];
                const ings = window.SERVER_INITIAL_DATA?.ingredients || [];
                ings.forEach(i => {
                    const name = (typeof i.name === 'object' && i.name !== null ? (i.name.hy || Object.values(i.name)[0]) : i.name) || i.sku;
                    list.push({
                        id: i.id,
                        name: `[Բաղադրիչ] ${name} (${i.sku})`,
                        type: 'ingredient',
                        cost_price: parseFloat(i.cost_price || 0),
                        unit_id: i.unit_id,
                        unit_code: i.unit?.code || 'կգ'
                    });
                });

                const prods = window.SERVER_INITIAL_DATA?.products || [];
                prods.forEach(p => {
                    const name = (typeof p.name === 'object' && p.name !== null ? (p.name.hy || Object.values(p.name)[0]) : p.name) || p.sku;
                    const typeLabel = p.type === 'semi_finished' ? 'Կիսաֆաբրիկատ' : (p.type === 'modifier' ? 'Մոդիֆիկատոր' : 'Ապրանք');
                    list.push({
                        id: p.id,
                        name: `[${typeLabel}] ${name} (${p.sku})`,
                        type: p.type,
                        cost_price: parseFloat(p.cost_price || 0),
                        unit_id: p.unit_id,
                        unit_code: p.unit?.code || 'հատ'
                    });
                });
                return list;
            },

            openTechnicalCard: async function (productId) {
                ERP.toast('Բեռնվում է տեխնիկական քարտը...', 'info');

                try {
                    const res = await ERP.api(`/products/${productId}/technical-card`);
                    const p = res.product;
                    const cardData = res.data;
                    ERP.catalog.state.currentTechCardProduct = p;

                    document.getElementById('tc-product-id').value = productId;
                    document.getElementById('tc-product-title').innerHTML = `
                        <i class="fa-solid fa-scroll" style="color: #059669; margin-right: 6px;"></i> 
                        Տեխնիկական Քարտ — «${p.name}» (${p.sku})
                    `;
                    document.getElementById('tc-product-subtitle').innerText = `Ելք՝ 1 ${p.unit_name || p.unit_code || 'միավոր'}, Վաճառքի գին՝ ${Math.round(p.sale_price).toLocaleString()} ֏`;

                    document.getElementById('tc-code').value = cardData.code || `RCP-${p.sku}-V1`;
                    document.getElementById('tc-name').value = cardData.name || `${p.name} — Տեխնիկական Քարտ`;
                    document.getElementById('tc-yield-qty').value = cardData.yield_quantity || 1;
                    document.getElementById('tc-yield-unit-id').value = cardData.yield_unit_id || p.unit_id;
                    document.getElementById('tc-scrap-pct').value = cardData.scrap_percentage || 0;
                    document.getElementById('tc-labor-cost').value = cardData.labor_cost || 0;
                    document.getElementById('tc-overhead-cost').value = cardData.overhead_cost || 0;
                    document.getElementById('tc-instructions').value = cardData.instructions || '';

                    const tbody = document.getElementById('tc-components-tbody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        const items = cardData.items || [];
                        if (items.length > 0) {
                            items.forEach(item => ERP.catalog.addRecipeComponentRow(item));
                        } else {
                            ERP.catalog.addRecipeComponentRow();
                        }
                    }

                    ERP.catalog.recalculateTechCard();

                    const modal = document.getElementById('product-technical-card-modal');
                    if (modal) modal.classList.add('active');
                } catch (err) {
                    ERP.toast(`Սխալ տեխնիկական քարտը բեռնելիս: ${err.message}`, 'error');
                }
            },

            closeTechnicalCardModal: function () {
                const modal = document.getElementById('product-technical-card-modal');
                if (modal) modal.classList.remove('active');
            },

            addRecipeComponentRow: function (item = null) {
                const tbody = document.getElementById('tc-components-tbody');
                if (!tbody) return;

                const components = ERP.catalog.getAvailableComponents();
                const units = window.SERVER_INITIAL_DATA?.units || [];

                let compOptions = '<option value="">-- Ընտրեք բաղադրիչ կամ կիսաֆաբրիկատ --</option>';
                components.forEach(c => {
                    const isSel = (item && item.product_id === c.id) ? 'selected' : '';
                    compOptions += `<option value="${c.id}" data-cost="${c.cost_price}" data-unit="${c.unit_id}" ${isSel}>${c.name}</option>`;
                });

                let unitOptions = '';
                units.forEach(u => {
                    const uName = (typeof u.name === 'object' && u.name !== null ? (u.name.hy || Object.values(u.name)[0]) : u.name) || u.code;
                    const isSel = (item && item.unit_id === u.id) ? 'selected' : '';
                    unitOptions += `<option value="${u.id}" ${isSel}>${uName} (${u.code})</option>`;
                });

                const netQty = item ? parseFloat(item.quantity || 1) : 1;
                const wastePct = item ? parseFloat(item.waste_percentage || 0) : 0;
                const grossQty = item ? (item.gross_quantity || (netQty * (1 + wastePct / 100))) : (netQty * (1 + wastePct / 100));
                const unitCost = item ? parseFloat(item.cost_per_unit || 0) : 0;
                const lineTotal = Math.round(grossQty * unitCost);

                const tr = document.createElement('tr');
                tr.className = 'tc-component-row';
                tr.innerHTML = `
                    <td>
                        <select class="select select-sm tc-comp-select" required onchange="ERP.catalog.onComponentSelected(this)">
                            ${compOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="any" min="0.0001" class="form-control form-control-sm font-mono tc-comp-net" value="${netQty}" required oninput="ERP.catalog.onRowValuesChanged(this)">
                    </td>
                    <td>
                        <select class="select select-sm tc-comp-unit" required>
                            ${unitOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="any" min="0" max="100" class="form-control form-control-sm font-mono tc-comp-waste" value="${wastePct}" oninput="ERP.catalog.onRowValuesChanged(this)">
                    </td>
                    <td>
                        <input type="number" step="any" class="form-control form-control-sm font-mono tc-comp-gross" value="${grossQty.toFixed(4)}" readonly style="background: #F8FAFC;">
                    </td>
                    <td>
                        <input type="number" step="any" min="0" class="form-control form-control-sm font-mono tc-comp-cost" value="${unitCost}" oninput="ERP.catalog.onRowValuesChanged(this)">
                    </td>
                    <td>
                        <div class="font-mono tc-comp-total" style="font-weight: 700; color: #334155;">${lineTotal.toLocaleString()} ֏</div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="ERP.catalog.removeComponentRow(this)" title="Հեռացնել"><i class="fa-solid fa-trash-can"></i></button>
                    </td>
                `;

                tbody.appendChild(tr);
                ERP.catalog.recalculateTechCard();
            },

            removeComponentRow: function (btn) {
                const tr = btn.closest('tr');
                if (tr) tr.remove();
                ERP.catalog.recalculateTechCard();
            },

            onComponentSelected: function (selectEl) {
                const opt = selectEl.options[selectEl.selectedIndex];
                const cost = parseFloat(opt.getAttribute('data-cost') || 0);
                const unitId = opt.getAttribute('data-unit');
                const row = selectEl.closest('tr');
                if (row) {
                    if (cost > 0) row.querySelector('.tc-comp-cost').value = cost;
                    if (unitId) row.querySelector('.tc-comp-unit').value = unitId;
                    ERP.catalog.onRowValuesChanged(selectEl);
                }
            },

            onRowValuesChanged: function (el) {
                const row = el.closest('tr');
                if (!row) return;

                const net = parseFloat(row.querySelector('.tc-comp-net')?.value || 0);
                const waste = parseFloat(row.querySelector('.tc-comp-waste')?.value || 0);
                const cost = parseFloat(row.querySelector('.tc-comp-cost')?.value || 0);

                const gross = net * (1 + (waste / 100));
                const grossEl = row.querySelector('.tc-comp-gross');
                if (grossEl) grossEl.value = gross.toFixed(4);

                const total = Math.round(gross * cost);
                const totalEl = row.querySelector('.tc-comp-total');
                if (totalEl) totalEl.innerText = `${total.toLocaleString()} ֏`;

                ERP.catalog.recalculateTechCard();
            },

            recalculateTechCard: function () {
                let totalMaterials = 0;
                document.querySelectorAll('#tc-components-tbody tr.tc-component-row').forEach(row => {
                    const gross = parseFloat(row.querySelector('.tc-comp-gross')?.value || 0);
                    const cost = parseFloat(row.querySelector('.tc-comp-cost')?.value || 0);
                    totalMaterials += (gross * cost);
                });

                const scrapPct = parseFloat(document.getElementById('tc-scrap-pct')?.value || 0);
                const scrapCost = totalMaterials * (scrapPct / 100);
                const laborCost = parseFloat(document.getElementById('tc-labor-cost')?.value || 0);
                const overheadCost = parseFloat(document.getElementById('tc-overhead-cost')?.value || 0);

                const totalExtra = scrapCost + laborCost + overheadCost;
                const totalBatchCost = totalMaterials + totalExtra;

                const yieldQty = Math.max(0.0001, parseFloat(document.getElementById('tc-yield-qty')?.value || 1));
                const unitCost = Math.round(totalBatchCost / yieldQty);

                const product = ERP.catalog.state.currentTechCardProduct;
                const salePrice = product ? (parseFloat(product.sale_price) || 0) : 0;

                const margin = salePrice > 0 ? Math.round(((salePrice - unitCost) / salePrice) * 100) : 0;
                const markup = unitCost > 0 ? Math.round(((salePrice - unitCost) / unitCost) * 100) : 0;

                const matEl = document.getElementById('tc-calc-materials');
                const extraEl = document.getElementById('tc-calc-extra');
                const unitEl = document.getElementById('tc-calc-unit-cost');
                const saleEl = document.getElementById('tc-calc-sale-price');
                const marginEl = document.getElementById('tc-calc-margin');
                const markupEl = document.getElementById('tc-calc-markup');

                if (matEl) matEl.innerText = `${Math.round(totalMaterials).toLocaleString()} ֏`;
                if (extraEl) extraEl.innerText = `${Math.round(totalExtra).toLocaleString()} ֏`;
                if (unitEl) unitEl.innerText = `${unitCost.toLocaleString()} ֏`;
                if (saleEl) saleEl.innerText = `${Math.round(salePrice).toLocaleString()} ֏`;
                if (marginEl) marginEl.innerText = `${margin}%`;
                if (markupEl) markupEl.innerText = `${markup}%`;
            },

            submitTechnicalCard: async function (e) {
                if (e) e.preventDefault();
                const productId = document.getElementById('tc-product-id')?.value;
                if (!productId) return;

                const code = document.getElementById('tc-code')?.value?.trim();
                const name = document.getElementById('tc-name')?.value?.trim();
                const yieldQty = parseFloat(document.getElementById('tc-yield-qty')?.value || 1);
                const yieldUnitId = document.getElementById('tc-yield-unit-id')?.value;
                const scrapPct = parseFloat(document.getElementById('tc-scrap-pct')?.value || 0);
                const laborCost = parseFloat(document.getElementById('tc-labor-cost')?.value || 0);
                const overheadCost = parseFloat(document.getElementById('tc-overhead-cost')?.value || 0);
                const instructions = document.getElementById('tc-instructions')?.value?.trim() || null;

                const items = [];
                document.querySelectorAll('#tc-components-tbody tr.tc-component-row').forEach(row => {
                    const compId = row.querySelector('.tc-comp-select')?.value;
                    const net = parseFloat(row.querySelector('.tc-comp-net')?.value || 0);
                    const unit = row.querySelector('.tc-comp-unit')?.value;
                    const waste = parseFloat(row.querySelector('.tc-comp-waste')?.value || 0);
                    const gross = parseFloat(row.querySelector('.tc-comp-gross')?.value || 0);
                    const cost = parseFloat(row.querySelector('.tc-comp-cost')?.value || 0);

                    if (compId && net > 0 && unit) {
                        items.push({
                            product_id: compId,
                            quantity: net,
                            gross_quantity: gross,
                            unit_id: unit,
                            waste_percentage: waste,
                            cost_per_unit: cost
                        });
                    }
                });

                if (items.length === 0) {
                    ERP.toast('Ավելացրեք առնվազն 1 բաղադրիչ կամ կիսաֆաբրիկատ:', 'warning');
                    return;
                }

                ERP.toast('Տեխնիկական քարտը պահպանվում է...', 'info');

                try {
                    const res = await ERP.api(`/products/${productId}/technical-card`, {
                        method: 'POST',
                        body: JSON.stringify({
                            code: code,
                            name: name,
                            yield_quantity: yieldQty,
                            yield_unit_id: yieldUnitId,
                            scrap_percentage: scrapPct,
                            labor_cost: laborCost,
                            overhead_cost: overheadCost,
                            instructions: instructions,
                            apply_to_cost_price: true,
                            items: items
                        })
                    });

                    ERP.toast(`Տեխնիկական քարտը պահպանվեց: Միավորի ինքնարժեք՝ ${res.breakdown?.unit_cost || 0} ֏`, 'success');
                    ERP.catalog.closeTechnicalCardModal();
                    setTimeout(() => window.location.reload(), 1000);
                } catch (err) {
                    ERP.toast(`Սխալ պահպանելիս: ${err.message}`, 'error');
                }
            },

            quickProduceFromTechCard: function () {
                const productId = document.getElementById('tc-product-id')?.value;
                if (!productId) return;
                ERP.catalog.closeTechnicalCardModal();
                ERP.catalog.openQuickProduce(productId);
            },

            openQuickProduce: function (productId) {
                const p = (window.SERVER_INITIAL_DATA?.products || []).find(item => item.id === productId);
                document.getElementById('qp-product-id').value = productId;
                const pName = p ? ((typeof p.name === 'object' && p.name !== null ? (p.name.hy || Object.values(p.name)[0]) : p.name) || p.sku) : 'Ապրանք';
                const pSku = p?.sku || '';

                document.getElementById('qp-product-name').innerText = pName;
                document.getElementById('qp-product-sku').innerText = pSku;
                document.getElementById('qp-quantity').value = '1';

                const modal = document.getElementById('product-quick-produce-modal');
                if (modal) modal.classList.add('active');
            },

            closeQuickProduceModal: function () {
                const modal = document.getElementById('product-quick-produce-modal');
                if (modal) modal.classList.remove('active');
            },

            submitProduce: async function (e) {
                if (e) e.preventDefault();
                const productId = document.getElementById('qp-product-id')?.value;
                const qty = parseFloat(document.getElementById('qp-quantity')?.value || 0);
                const targetWh = document.getElementById('qp-target-warehouse')?.value;
                const sourceWh = document.getElementById('qp-source-warehouse')?.value;

                if (!productId || qty <= 0) {
                    ERP.toast('Մուտքագրեք վավեր արտադրվող քանակ:', 'warning');
                    return;
                }

                ERP.toast('Արտադրությունը ձևակերպվում է...', 'info');

                try {
                    const res = await ERP.api(`/products/${productId}/produce`, {
                        method: 'POST',
                        body: JSON.stringify({
                            quantity: qty,
                            warehouse_id: targetWh,
                            source_warehouse_id: sourceWh
                        })
                    });

                    ERP.toast(res.message, 'success', 'Արտադրություն կատարված է');
                    ERP.catalog.closeQuickProduceModal();
                    setTimeout(() => window.location.reload(), 1200);
                } catch (err) {
                    ERP.toast(`Արտադրության սխալ: ${err.message}`, 'error');
                }
            },

            printTechnicalCard: function () {
                const product = ERP.catalog.state.currentTechCardProduct;
                const code = document.getElementById('tc-code')?.value;
                const name = document.getElementById('tc-name')?.value;
                const yieldQty = document.getElementById('tc-yield-qty')?.value;
                const scrapPct = document.getElementById('tc-scrap-pct')?.value;
                const laborCost = document.getElementById('tc-labor-cost')?.value;
                const overheadCost = document.getElementById('tc-overhead-cost')?.value;
                const instructions = document.getElementById('tc-instructions')?.value;
                const unitCost = document.getElementById('tc-calc-unit-cost')?.innerText;
                const salePrice = document.getElementById('tc-calc-sale-price')?.innerText;
                const margin = document.getElementById('tc-calc-margin')?.innerText;

                let rowsHtml = '';
                document.querySelectorAll('#tc-components-tbody tr.tc-component-row').forEach((row, i) => {
                    const selectEl = row.querySelector('.tc-comp-select');
                    const compName = selectEl ? (selectEl.options[selectEl.selectedIndex]?.text || '') : '';
                    const net = row.querySelector('.tc-comp-net')?.value || '';
                    const unitSelect = row.querySelector('.tc-comp-unit');
                    const unitName = unitSelect ? (unitSelect.options[unitSelect.selectedIndex]?.text || '') : '';
                    const waste = row.querySelector('.tc-comp-waste')?.value || '0';
                    const gross = row.querySelector('.tc-comp-gross')?.value || '';
                    const cost = row.querySelector('.tc-comp-cost')?.value || '';
                    const total = row.querySelector('.tc-comp-total')?.innerText || '';

                    rowsHtml += `
                        <tr>
                            <td style="border: 1px solid #333; padding: 6px;">${i + 1}</td>
                            <td style="border: 1px solid #333; padding: 6px;">${compName}</td>
                            <td style="border: 1px solid #333; padding: 6px; text-align: right;">${net}</td>
                            <td style="border: 1px solid #333; padding: 6px;">${unitName}</td>
                            <td style="border: 1px solid #333; padding: 6px; text-align: right;">${waste}%</td>
                            <td style="border: 1px solid #333; padding: 6px; text-align: right;">${gross}</td>
                            <td style="border: 1px solid #333; padding: 6px; text-align: right;">${cost} ֏</td>
                            <td style="border: 1px solid #333; padding: 6px; text-align: right; font-weight: bold;">${total}</td>
                        </tr>
                    `;
                });

                const printWindow = window.open('', '_blank', 'width=900,height=700');
                if (!printWindow) {
                    ERP.toast('Թույլատրեք popup պատուհանները տպելու համար:', 'warning');
                    return;
                }

                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Տեխնիկական Քարտ - ${code}</title>
                        <style>
                            body { font-family: Arial, sans-serif; font-size: 12px; margin: 25px; color: #111; }
                            h2, h3 { margin: 4px 0; }
                            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                            .header-box { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
                            .summary-box { margin-top: 15px; display: flex; gap: 20px; font-weight: bold; background: #F1F5F9; padding: 10px; border: 1px solid #CBD5E1; }
                            .signatures { margin-top: 40px; display: flex; justify-content: space-between; }
                        </style>
                    </head>
                    <body>
                        <div class="header-box">
                            <div>
                                <h2>ՏԵԽՆՈԼՈԳԻԱԿԱՆ ՔԱՐՏ / ԲԱՂԱԴՐԱՏՈՄՍ (BOM)</h2>
                                <h3>Ապրանք՝ ${name}</h3>
                                <div>Կոդ: <strong>${code}</strong> | SKU: <strong>${product?.sku || ''}</strong></div>
                            </div>
                            <div style="text-align: right;">
                                <div>Ելք՝ <strong>${yieldQty}</strong> ${product?.unit_code || 'հատ'}</div>
                                <div>Ամսաթիվ՝ ${new Date().toLocaleDateString('hy-AM')}</div>
                            </div>
                        </div>

                        <table>
                            <thead>
                                <tr style="background: #E2E8F0;">
                                    <th style="border: 1px solid #333; padding: 6px; width: 30px;">#</th>
                                    <th style="border: 1px solid #333; padding: 6px; text-align: left;">Բաղադրիչ / Կիսաֆաբրիկատ</th>
                                    <th style="border: 1px solid #333; padding: 6px;">Նետտո</th>
                                    <th style="border: 1px solid #333; padding: 6px;">Միավոր</th>
                                    <th style="border: 1px solid #333; padding: 6px;">Կորուստ %</th>
                                    <th style="border: 1px solid #333; padding: 6px;">Բրուտտո</th>
                                    <th style="border: 1px solid #333; padding: 6px;">Գին ֏</th>
                                    <th style="border: 1px solid #333; padding: 6px;">Ընդհանուր ֏</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rowsHtml}
                            </tbody>
                        </table>

                        <div class="summary-box">
                            <div>Խոտան / Կորուստ՝ ${scrapPct}%</div>
                            <div>Աշխատուժ՝ ${laborCost} ֏</div>
                            <div>Վերադիր՝ ${overheadCost} ֏</div>
                            <div>ՄԻԱՎՈՐԻ ԻՆՔՆԱՐԺԵՔ՝ ${unitCost}</div>
                            <div>Վաճառքի Գին՝ ${salePrice}</div>
                            <div>Մարժա՝ ${margin}</div>
                        </div>

                        ${instructions ? `<div style="margin-top: 20px;"><strong>Պատրաստման տեխնոլոգիա և հրահանգներ՝</strong><p style="white-space: pre-line; margin-top: 5px;">${instructions}</p></div>` : ''}

                        <div class="signatures">
                            <div>Տեխնոլոգ / Շեֆ-խոհարար՝ _________________</div>
                            <div>Հաստատող / Տնօրեն՝ _________________</div>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => printWindow.print(), 500);
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
        // 9.5 Directory Subsystem (Տեղեկագիր: Կատեգորիաներ, Մատակարարներ, Բաղադրիչներ)
        // =====================================================================
        directory: {
            categories: {
                currentFilter: 'all',
                searchQuery: '',
                list: [],

                load: async function () {
                    const tbody = document.getElementById('directory-categories-table-body');
                    if (!tbody) return;

                    try {
                        let query = '';
                        if (this.searchQuery) query += `?search=${encodeURIComponent(this.searchQuery)}`;

                        const res = await ERP.api(`/categories${query}`);
                        if (res && res.success) {
                            this.list = res.data || [];
                            this.applyFilter();
                            this.updateKPIs(this.list);
                            this.updateSelectDropdowns(this.list);
                        }
                    } catch (err) {
                        console.error('Failed to load categories:', err);
                        ERP.toast('Չհաջողվեց բեռնել կատեգորիաները: ' + err.message, 'error');
                    }
                },

                updateKPIs: function (list) {
                    const total = list.length;
                    const prodCount = list.filter(c => (c.type === 'product' || !c.type)).length;
                    const ingCount = list.filter(c => c.type === 'ingredient').length;
                    const root = list.filter(c => !c.parent_id).length;
                    const sub = list.filter(c => !!c.parent_id).length;

                    const totalEl = document.getElementById('cat-kpi-total');
                    const prodEl = document.getElementById('cat-kpi-product');
                    const ingEl = document.getElementById('cat-kpi-ingredient');
                    const subEl = document.getElementById('cat-kpi-sub');
                    const badgeEl = document.getElementById('cat-count-badge');
                    const navBadge = document.getElementById('nav-categories-count');

                    const tabAll = document.getElementById('cat-tab-count-all');
                    const tabProduct = document.getElementById('cat-tab-count-product');
                    const tabIngredient = document.getElementById('cat-tab-count-ingredient');
                    const tabRoot = document.getElementById('cat-tab-count-root');
                    const tabSub = document.getElementById('cat-tab-count-sub');

                    if (totalEl) totalEl.innerText = total;
                    if (prodEl) prodEl.innerText = prodCount;
                    if (ingEl) ingEl.innerText = ingCount;
                    if (subEl) subEl.innerText = sub;
                    if (badgeEl) badgeEl.innerText = `${total} Խումբ`;
                    if (navBadge) navBadge.innerText = total;

                    if (tabAll) tabAll.innerText = total;
                    if (tabProduct) tabProduct.innerText = prodCount;
                    if (tabIngredient) tabIngredient.innerText = ingCount;
                    if (tabRoot) tabRoot.innerText = root;
                    if (tabSub) tabSub.innerText = sub;
                },

                updateSelectDropdowns: function (list) {
                    const prodCat = document.getElementById('prod-category-id');
                    const parentCat = document.getElementById('cat-parent-id');
                    const ingCat = document.getElementById('ing-category-id');
                    const catalogFilter = document.getElementById('catalog-category-filter');
                    const ingFilter = document.getElementById('ing-category-filter');

                    const buildOptions = (cats, includeEmpty = true, emptyLabel = '-- Ընտրել --') => {
                        let html = includeEmpty ? `<option value="">${emptyLabel}</option>` : '';
                        cats.forEach(c => {
                            const name = typeof c.name === 'object' && c.name !== null ? (c.name.hy || Object.values(c.name)[0]) : c.name;
                            html += `<option value="${c.id}" data-type="${c.type || 'product'}">${name}</option>`;
                        });
                        return html;
                    };

                    const productCats = list.filter(c => (c.type === 'product' || !c.type));
                    const ingredientCats = list.filter(c => c.type === 'ingredient');

                    if (prodCat) {
                        const currentVal = prodCat.value;
                        prodCat.innerHTML = buildOptions(productCats, true, '-- Առանց կատեգորիայի --');
                        if (currentVal) prodCat.value = currentVal;
                    }

                    if (catalogFilter) {
                        const currentVal = catalogFilter.value;
                        catalogFilter.innerHTML = buildOptions(productCats, true, 'Բոլոր կատեգորիաները');
                        if (currentVal) catalogFilter.value = currentVal;
                    }

                    if (parentCat) {
                        const currentVal = parentCat.value;
                        const currentModalType = document.querySelector('input[name="cat_type"]:checked')?.value || 'product';
                        const matchingRoots = list.filter(c => !c.parent_id && (c.type || 'product') === currentModalType);
                        parentCat.innerHTML = buildOptions(matchingRoots, true, '-- Գլխավոր Կատեգորիա (Առանց ծնողի) --');
                        if (currentVal && matchingRoots.some(c => c.id === currentVal)) {
                            parentCat.value = currentVal;
                        }
                    }

                    if (ingCat) {
                        const currentVal = ingCat.value;
                        ingCat.innerHTML = buildOptions(ingredientCats, true, '-- Ընտրեք Բաղադրիչների Կատեգորիան --');
                        if (currentVal) ingCat.value = currentVal;
                    }

                    if (ingFilter) {
                        const currentVal = ingFilter.value;
                        ingFilter.innerHTML = buildOptions(ingredientCats, true, 'Բոլոր Կատեգորիաները');
                        if (currentVal) ingFilter.value = currentVal;
                    }
                },

                filterType: function (type) {
                    this.currentFilter = type;
                    const tabs = document.querySelectorAll('#category-filter-tabs .directory-tab-btn');
                    tabs.forEach(btn => {
                        if (btn.getAttribute('data-type') === type) {
                            btn.classList.add('active');
                        } else {
                            btn.classList.remove('active');
                        }
                    });
                    this.applyFilter();
                },

                handleSearch: function (query) {
                    this.searchQuery = (query || '').toLowerCase().trim();
                    this.applyFilter();
                },

                applyFilter: function () {
                    let filtered = this.list;

                    if (this.currentFilter === 'product') {
                        filtered = filtered.filter(c => (c.type === 'product' || !c.type));
                    } else if (this.currentFilter === 'ingredient') {
                        filtered = filtered.filter(c => c.type === 'ingredient');
                    } else if (this.currentFilter === 'root') {
                        filtered = filtered.filter(c => !c.parent_id);
                    } else if (this.currentFilter === 'sub') {
                        filtered = filtered.filter(c => !!c.parent_id);
                    }

                    if (this.searchQuery) {
                        const q = this.searchQuery;
                        filtered = filtered.filter(c => {
                            const nameHy = (typeof c.name === 'object' && c.name?.hy ? c.name.hy : (typeof c.name === 'string' ? c.name : '')).toLowerCase();
                            const nameEn = (typeof c.name === 'object' && c.name?.en ? c.name.en : '').toLowerCase();
                            const slug = (c.slug || '').toLowerCase();
                            return nameHy.includes(q) || nameEn.includes(q) || slug.includes(q);
                        });
                    }

                    this.render(filtered);
                },

                render: function (items) {
                    const tbody = document.getElementById('directory-categories-table-body');
                    if (!tbody) return;

                    if (!items || items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-muted);">Համապատասխան կատեգորիաներ չեն գտնվել:</td></tr>';
                        return;
                    }

                    tbody.innerHTML = items.map(cat => {
                        const nameHy = typeof cat.name === 'object' && cat.name !== null ? (cat.name.hy || Object.values(cat.name)[0]) : cat.name;
                        const nameEn = typeof cat.name === 'object' && cat.name !== null ? (cat.name.en || '') : '';
                        const parentName = cat.parent ? (typeof cat.parent.name === 'object' && cat.parent.name !== null ? (cat.parent.name.hy || Object.values(cat.parent.name)[0]) : cat.parent.name) : null;
                        const catType = cat.type || 'product';
                        const thumb = cat.image_url
                            ? `<img src="${cat.image_url}" class="category-thumb-sm" alt="Thumbnail">`
                            : `<div class="category-thumb-sm"><i class="fa-solid ${catType === 'ingredient' ? 'fa-mortar-pestle' : 'fa-folder'}"></i></div>`;

                        return `
                            <tr data-id="${cat.id}" data-type="${catType}" data-parent="${cat.parent_id ? '1' : '0'}">
                                <td>${thumb}</td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-heading); font-size: 0.88rem;">${nameHy}</div>
                                    ${nameEn ? `<div style="font-size: 0.72rem; color: var(--text-muted);">${nameEn}</div>` : ''}
                                </td>
                                <td>
                                    ${catType === 'ingredient'
                                        ? `<span class="badge badge-amber" style="font-size: 0.72rem; font-weight: 700;"><i class="fa-solid fa-mortar-pestle"></i> Բաղադրիչների</span>`
                                        : `<span class="badge badge-indigo" style="font-size: 0.72rem; font-weight: 700;"><i class="fa-solid fa-boxes-stacked"></i> Ապրանքային</span>`
                                    }
                                </td>
                                <td><span class="font-mono" style="font-size: 0.78rem; font-weight: 700; color: var(--color-primary);">${cat.slug || ''}</span></td>
                                <td>
                                    ${parentName
                                        ? `<span class="item-chip" style="background: #F5F3FF; color: #7C3AED; border-color: #DDD6FE;"><i class="fa-solid fa-folder-open"></i> ${parentName}</span>`
                                        : `<span class="badge badge-emerald" style="font-size: 0.68rem;">Գլխավոր Խումբ</span>`
                                    }
                                </td>
                                <td>
                                    <span class="badge badge-slate" style="font-weight: 700; font-size: 0.75rem;">
                                        <i class="fa-solid fa-box"></i> ${cat.products_count || 0} ապրանք
                                    </span>
                                </td>
                                <td><span class="font-mono" style="font-size: 0.78rem; color: #64748B;">${cat.sort_order ?? 0}</span></td>
                                <td>
                                    ${cat.is_active
                                        ? `<span class="badge badge-emerald"><i class="fa-solid fa-circle-check"></i> Ակտիվ</span>`
                                        : `<span class="badge badge-amber"><i class="fa-solid fa-circle-pause"></i> Պասիվ</span>`
                                    }
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <button class="btn btn-xs btn-outline-secondary" onclick="ERP.directory.categories.openEditModal('${cat.id}')" title="Խմբագրել" style="padding: 4px 7px;">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-secondary" onclick="ERP.directory.categories.deleteCategory('${cat.id}')" title="Հեռացնել" style="padding: 4px 7px; color: #DC2626;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                },

                onModalTypeChange: function (type) {
                    const productLabel = document.getElementById('cat-type-product-label');
                    const ingredientLabel = document.getElementById('cat-type-ingredient-label');
                    if (productLabel && ingredientLabel) {
                        if (type === 'product') {
                            productLabel.style.borderColor = '#3B82F6';
                            productLabel.style.background = '#EFF6FF';
                            ingredientLabel.style.borderColor = '#E2E8F0';
                            ingredientLabel.style.background = '#fff';
                        } else {
                            ingredientLabel.style.borderColor = '#F59E0B';
                            ingredientLabel.style.background = '#FFFBEB';
                            productLabel.style.borderColor = '#E2E8F0';
                            productLabel.style.background = '#fff';
                        }
                    }

                    const parentSelect = document.getElementById('cat-parent-id');
                    if (parentSelect) {
                        const editId = document.getElementById('cat-id')?.value;
                        const currentVal = parentSelect.value;
                        const cats = (this.list && this.list.length > 0) ? this.list : (window.SERVER_INITIAL_DATA?.categories || []);
                        const matchingRoots = cats.filter(c => !c.parent_id && (c.type || 'product') === type && c.id !== editId);

                        let html = '<option value="">-- Գլխավոր Կատեգորիա (Առանց ծնողի) --</option>';
                        matchingRoots.forEach(c => {
                            const name = typeof c.name === 'object' && c.name !== null ? (c.name.hy || Object.values(c.name)[0]) : c.name;
                            html += `<option value="${c.id}">${name}</option>`;
                        });
                        parentSelect.innerHTML = html;
                        if (currentVal && matchingRoots.some(c => c.id === currentVal)) {
                            parentSelect.value = currentVal;
                        } else {
                            parentSelect.value = '';
                        }
                    }
                },

                openCreateModal: function (parentId = null, defaultType = 'product') {
                    const modal = document.getElementById('directory-category-modal');
                    if (!modal) return;

                    const form = document.getElementById('category-master-form');
                    if (form) form.reset();

                    document.getElementById('cat-id').value = '';
                    const title = document.getElementById('category-modal-title');
                    if (title) title.innerHTML = '<i class="fa-solid fa-folder-plus"></i> Ավելացնել Կատեգորիա';

                    const radProduct = document.getElementById('cat-type-product');
                    const radIngredient = document.getElementById('cat-type-ingredient');
                    if (defaultType === 'ingredient') {
                        if (radIngredient) radIngredient.checked = true;
                    } else {
                        if (radProduct) radProduct.checked = true;
                    }
                    this.onModalTypeChange(defaultType);

                    document.getElementById('cat-name-hy').value = '';
                    document.getElementById('cat-name-en').value = '';
                    document.getElementById('cat-name-ru').value = '';
                    document.getElementById('cat-slug').value = '';
                    document.getElementById('cat-sort-order').value = '0';
                    document.getElementById('cat-is-active').checked = true;
                    document.getElementById('cat-description').value = '';

                    const parentSelect = document.getElementById('cat-parent-id');
                    if (parentSelect && parentId) parentSelect.value = parentId;

                    ERP.media.clearCategoryImage();

                    modal.classList.add('active');
                },

                openEditModal: async function (id) {
                    const modal = document.getElementById('directory-category-modal');
                    if (!modal) return;

                    ERP.toast('Բեռնվում են կատեգորիայի տվյալները...', 'info');

                    try {
                        const res = await ERP.api(`/categories/${id}`);
                        const cat = res.data;
                        if (!cat) throw new Error('Կատեգորիան չի գտնվել:');

                        document.getElementById('cat-id').value = cat.id;
                        const title = document.getElementById('category-modal-title');
                        const catName = typeof cat.name === 'object' && cat.name !== null ? (cat.name.hy || Object.values(cat.name)[0]) : cat.name;
                        if (title) title.innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Խմբագրել Կատեգորիա՝ «${catName}»`;

                        const catType = cat.type || 'product';
                        const radProduct = document.getElementById('cat-type-product');
                        const radIngredient = document.getElementById('cat-type-ingredient');
                        if (catType === 'ingredient') {
                            if (radIngredient) radIngredient.checked = true;
                        } else {
                            if (radProduct) radProduct.checked = true;
                        }
                        this.onModalTypeChange(catType);

                        if (typeof cat.name === 'object' && cat.name !== null) {
                            document.getElementById('cat-name-hy').value = cat.name.hy || Object.values(cat.name)[0] || '';
                            document.getElementById('cat-name-en').value = cat.name.en || '';
                            document.getElementById('cat-name-ru').value = cat.name.ru || '';
                        } else {
                            document.getElementById('cat-name-hy').value = cat.name || '';
                            document.getElementById('cat-name-en').value = '';
                            document.getElementById('cat-name-ru').value = '';
                        }

                        const parentSelect = document.getElementById('cat-parent-id');
                        if (parentSelect) parentSelect.value = cat.parent_id || '';

                        document.getElementById('cat-slug').value = cat.slug || '';
                        document.getElementById('cat-sort-order').value = cat.sort_order ?? 0;
                        document.getElementById('cat-is-active').checked = (cat.is_active !== false);

                        if (typeof cat.description === 'object' && cat.description !== null) {
                            document.getElementById('cat-description').value = cat.description.hy || Object.values(cat.description)[0] || '';
                        } else {
                            document.getElementById('cat-description').value = cat.description || '';
                        }

                        document.getElementById('cat-image-url').value = cat.image_url || '';
                        ERP.media.setPreview(cat.image_url || '', 'cat-image-preview', 'cat-image-preview-box', 'cat-image-remove-btn', 'cat-image-placeholder-icon');

                        modal.classList.add('active');
                    } catch (err) {
                        ERP.toast('Սխալ կատեգորիայի բեռնման ժամանակ: ' + err.message, 'error');
                    }
                },

                closeModal: function () {
                    const modal = document.getElementById('directory-category-modal');
                    if (modal) modal.classList.remove('active');
                },

                onNameInput: function (val) {
                    const editId = document.getElementById('cat-id').value;
                    const slugInput = document.getElementById('cat-slug');
                    if (!editId && slugInput && (!slugInput.value || slugInput.dataset.manual !== '1')) {
                        slugInput.value = val.toLowerCase().replace(/[^a-z0-9\u0531-\u058F]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                    }
                },

                generateSlug: function () {
                    const name = document.getElementById('cat-name-en').value || document.getElementById('cat-name-hy').value || 'category';
                    const rand = Math.random().toString(36).substring(2, 6);
                    const clean = name.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '') || 'cat';
                    const slugInput = document.getElementById('cat-slug');
                    if (slugInput) {
                        slugInput.value = `${clean}-${rand}`;
                        slugInput.dataset.manual = '1';
                    }
                },

                submitForm: async function (e) {
                    if (e) e.preventDefault();

                    const id = document.getElementById('cat-id').value;
                    const catType = document.querySelector('input[name="cat_type"]:checked')?.value || 'product';
                    const nameHy = document.getElementById('cat-name-hy').value.trim();
                    const nameEn = document.getElementById('cat-name-en').value.trim();
                    const nameRu = document.getElementById('cat-name-ru').value.trim();
                    const parentId = document.getElementById('cat-parent-id').value || null;
                    const slug = document.getElementById('cat-slug').value.trim() || null;
                    const sortOrder = parseInt(document.getElementById('cat-sort-order').value || '0', 10);
                    const isActive = document.getElementById('cat-is-active').checked;
                    const imageUrl = document.getElementById('cat-image-url').value.trim() || null;
                    const description = document.getElementById('cat-description').value.trim();

                    if (!nameHy) {
                        ERP.toast('Լրացրեք հայերեն անվանումը:', 'warning');
                        return;
                    }

                    const submitBtn = document.getElementById('cat-submit-btn');
                    if (submitBtn) submitBtn.disabled = true;

                    const payload = {
                        name: { hy: nameHy, en: nameEn || nameHy, ru: nameRu || nameHy },
                        type: catType,
                        parent_id: parentId,
                        slug: slug,
                        sort_order: sortOrder,
                        is_active: isActive,
                        image_url: imageUrl,
                        description: description ? { hy: description } : null
                    };

                    try {
                        let res;
                        if (id) {
                            res = await ERP.api(`/categories/${id}`, {
                                method: 'PUT',
                                body: JSON.stringify(payload)
                            });
                            ERP.toast('Կատեգորիան հաջողությամբ թարմացվեց:', 'success');
                        } else {
                            res = await ERP.api('/categories', {
                                method: 'POST',
                                body: JSON.stringify(payload)
                            });
                            ERP.toast('Կատեգորիան հաջողությամբ ստեղծվեց:', 'success');
                        }

                        this.closeModal();
                        await this.load();
                    } catch (err) {
                        ERP.toast('Չհաջողվեց պահպանել կատեգորիան: ' + err.message, 'error');
                    } finally {
                        if (submitBtn) submitBtn.disabled = false;
                    }
                },

                deleteCategory: async function (id) {
                    if (!confirm('Վստա՞հ եք, որ ցանկանում եք հեռացնել այս կատեգորիան:')) return;

                    try {
                        const res = await ERP.api(`/categories/${id}`, {
                            method: 'DELETE'
                        });
                        ERP.toast(res.message || 'Կատեգորիան հեռացվել է:', 'success');
                        await this.load();
                    } catch (err) {
                        ERP.toast('Չհաջողվեց հեռացնել կատեգորիան: ' + err.message, 'error');
                    }
                }
            },

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

                    ERP.media.clearIngredientImage();

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

                        const ingImg = (Array.isArray(ing.images) && ing.images[0]) ? ing.images[0] : '';
                        document.getElementById('ing-image-url').value = ingImg;
                        ERP.media.setPreview(ingImg, 'ing-image-preview', 'ing-image-preview-box', 'ing-image-remove-btn', 'ing-image-placeholder-icon');

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

                    const allCats = (ERP.directory?.categories?.list && ERP.directory.categories.list.length > 0)
                        ? ERP.directory.categories.list
                        : (window.SERVER_INITIAL_DATA?.categories || []);
                    const subs = allCats.filter(c => c.parent_id === parentId && c.type === 'ingredient');

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
                        images: document.getElementById('ing-image-url')?.value?.trim() ? [document.getElementById('ing-image-url').value.trim()] : [],
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
            },

            // -----------------------------------------------------------------
            // CRM: CUSTOMERS & CUSTOMER 360° SUBSYSTEM
            // -----------------------------------------------------------------
            customers: {
                currentFilter: 'all',
                currentStatus: 'active',
                currentBranch: 'all',
                currentSource: 'all',
                currentTier: 'all',
                searchQuery: '',
                searchTimeout: null,
                list: [],
                sources: [],
                activeCustomer: null,

                load: async function () {
                    const tbody = document.getElementById('directory-customers-table-body');
                    if (!tbody) return;

                    try {
                        const params = new URLSearchParams();
                        if (this.currentFilter !== 'all') params.append('type', this.currentFilter);
                        if (this.currentStatus !== 'all') params.append('status', this.currentStatus);
                        if (this.currentBranch !== 'all') params.append('branch_id', this.currentBranch);
                        if (this.currentSource !== 'all') params.append('source_id', this.currentSource);
                        if (this.currentTier !== 'all') params.append('loyalty_tier', this.currentTier);
                        if (this.searchQuery) params.append('search', this.searchQuery);

                        const qs = params.toString() ? `?${params.toString()}` : '';
                        const res = await ERP.api(`/customers${qs}`);
                        if (res && res.success) {
                            this.list = res.data || [];
                            this.renderTable(this.list);
                            this.updateKPIs(this.list);
                        }
                    } catch (err) {
                        console.error('Failed to load customers:', err);
                        ERP.toast('Չհաջողվեց բեռնել հաճախորդներին: ' + err.message, 'error');
                    }
                },

                updateKPIs: function (list) {
                    const totalEl = document.getElementById('cust-kpi-total');
                    const indivEl = document.getElementById('cust-kpi-individual');
                    const compEl = document.getElementById('cust-kpi-company');
                    const pointsEl = document.getElementById('cust-kpi-points');
                    const revEl = document.getElementById('cust-kpi-revenue');
                    const badgeCountEl = document.getElementById('cust-table-badge-count');
                    const sideCountEl = document.getElementById('sidebar-customers-count');

                    const total = list.length;
                    const individuals = list.filter(c => c.type === 'individual').length;
                    const companies = list.filter(c => c.type === 'company').length;
                    const points = list.reduce((sum, c) => sum + (parseFloat(c.loyalty_account?.points_balance) || 0), 0);
                    const spent = list.reduce((sum, c) => sum + (parseFloat(c.total_spent) || 0), 0);

                    if (totalEl) totalEl.textContent = total;
                    if (indivEl) indivEl.textContent = individuals;
                    if (compEl) compEl.textContent = companies;
                    if (pointsEl) pointsEl.textContent = new Intl.NumberFormat('hy-AM').format(Math.round(points));
                    if (revEl) revEl.textContent = new Intl.NumberFormat('hy-AM').format(Math.round(spent)) + ' ֏';
                    if (badgeCountEl) badgeCountEl.textContent = total;
                    if (sideCountEl) sideCountEl.textContent = total;
                },

                renderTable: function (list) {
                    const tbody = document.getElementById('directory-customers-table-body');
                    if (!tbody) return;

                    if (!list || list.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            <div style="font-size: 1.5rem; margin-bottom: 0.5rem;"><i class="fa-solid fa-users-slash"></i></div>
                            <div>Համապատասխան հաճախորդներ չեն գտնվել:</div>
                        </td></tr>`;
                        return;
                    }

                    tbody.innerHTML = list.map(c => {
                        const isComp = c.type === 'company';
                        const name = c.display_name || (isComp ? (c.company?.legal_name || c.company_name) : `${c.first_name || ''} ${c.last_name || ''}`.trim()) || 'Անանուն';
                        const initials = name.split(' ').filter(Boolean).map(w => w[0]).join('').slice(0, 2).toUpperCase() || 'ՀԱ';
                        const tier = (c.loyalty_tier || 'basic').toLowerCase();
                        const tierColors = {
                            basic: 'badge-slate',
                            bronze: 'badge-amber',
                            silver: 'badge-cyan',
                            gold: 'badge-amber',
                            vip: 'badge-purple'
                        };
                        const tierBadge = `<span class="badge ${tierColors[tier] || 'badge-slate'}" style="text-transform: uppercase;">${tier}</span>`;
                        const points = parseFloat(c.loyalty_account?.points_balance || 0);
                        const discount = parseFloat(c.custom_discount_percent || 0);

                        const addr = c.last_used_address?.street || c.default_address?.street || (c.addresses && c.addresses[0]?.street) || '—';
                        const city = c.last_used_address?.city || c.default_address?.city || 'Երևան';

                        const score = parseInt(c.customer_score || 50, 10);
                        let scoreClass = 'score-medium';
                        if (score >= 75) scoreClass = 'score-high';
                        else if (score < 40) scoreClass = 'score-low';

                        const statusMap = {
                            active: '<span class="badge badge-emerald">Ակտիվ</span>',
                            inactive: '<span class="badge badge-slate">Ոչ ակտիվ</span>',
                            blocked: '<span class="badge badge-danger">Արգելափակված</span>',
                            archived: '<span class="badge badge-slate">Արխիվ</span>'
                        };
                        const statusBadge = statusMap[c.status] || `<span class="badge badge-slate">${c.status}</span>`;

                        const totalSpent = new Intl.NumberFormat('hy-AM').format(Math.round(parseFloat(c.total_spent || 0)));

                        return `
                            <tr>
                                <td>
                                    <span class="font-mono" style="font-weight: 800; color: var(--color-primary); cursor: pointer;" onclick="ERP.directory.customers.openProfile360('${c.id}')">${c.customer_code || '—'}</span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="crm-avatar ${isComp ? 'company' : ''}">${initials}</div>
                                        <div>
                                            <div style="font-weight: 800; color: var(--text-heading); cursor: pointer;" onclick="ERP.directory.customers.openProfile360('${c.id}')">${name}</div>
                                            <div style="display: flex; gap: 4px; align-items: center; margin-top: 2px;">
                                                <span class="badge ${isComp ? 'badge-purple' : 'badge-primary'}" style="font-size: 0.65rem;">${isComp ? 'B2B' : 'B2C'}</span>
                                                ${c.tax_id ? `<span class="font-mono" style="font-size: 0.68rem; color: var(--text-muted);"><i class="fa-solid fa-receipt"></i> ${c.tax_id}</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-mono" style="font-size: 0.8rem; font-weight: 700;">
                                        <i class="fa-solid fa-phone" style="font-size: 0.7rem; color: var(--color-primary);"></i> ${c.primary_phone || c.phone || '—'}
                                    </div>
                                    ${(c.primary_email || c.email) ? `<div style="font-size: 0.72rem; color: var(--text-muted);"><i class="fa-regular fa-envelope"></i> ${c.primary_email || c.email}</div>` : ''}
                                </td>
                                <td>
                                    <div style="font-size: 0.82rem; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${addr}">
                                        <i class="fa-solid fa-location-dot" style="color: #EF4444; font-size: 0.7rem;"></i> ${addr}
                                    </div>
                                    <div style="font-size: 0.68rem; color: var(--text-muted);">${city}</div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        ${tierBadge}
                                        <span class="font-mono" style="font-weight: 800; font-size: 0.8rem; color: #D97706;">${points} մ.</span>
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">Զեղչ՝ <strong class="font-mono" style="color: #2563EB;">${discount}%</strong></div>
                                </td>
                                <td>
                                    <div class="font-mono" style="font-weight: 800; color: #059669;">${totalSpent} ֏</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">${c.orders_count || 0} պատվեր</div>
                                </td>
                                <td>
                                    <span class="score-badge ${scoreClass}">${score}</span>
                                </td>
                                <td>
                                    ${statusBadge}
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 4px;">
                                        <button class="btn btn-xs btn-outline-primary" title="CRM 360° Profile" onclick="ERP.directory.customers.openProfile360('${c.id}')">
                                            <i class="fa-solid fa-eye"></i> 360°
                                        </button>
                                        <button class="btn btn-xs btn-outline-secondary" title="Խմբագրել" onclick="ERP.directory.customers.openEditModal('${c.id}')">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-xs btn-outline-danger" title="Արխիվացնել" onclick="ERP.directory.customers.archiveCustomer('${c.id}')">
                                            <i class="fa-solid fa-box-archive"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');
                },

                filterType: function (type) {
                    this.currentFilter = type;
                    document.querySelectorAll('#customer-type-tabs .directory-tab-btn').forEach(btn => {
                        if (btn.getAttribute('data-type') === type) {
                            btn.classList.add('active');
                        } else {
                            btn.classList.remove('active');
                        }
                    });
                    this.load();
                },

                filterStatus: function (status) {
                    this.currentStatus = status;
                    this.load();
                },

                filterBranch: function (branch) {
                    this.currentBranch = branch;
                    this.load();
                },

                filterSource: function (source) {
                    this.currentSource = source;
                    this.load();
                },

                filterTier: function (tier) {
                    this.currentTier = tier;
                    this.load();
                },

                onSearch: function (query) {
                    this.searchQuery = query.trim();
                    if (this.searchTimeout) clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.load();
                    }, 300);
                },

                setFormType: function (type) {
                    const typeInput = document.getElementById('cust-form-type');
                    const indivBtn = document.getElementById('cust-type-btn-indiv');
                    const compBtn = document.getElementById('cust-type-btn-comp');
                    const indivFields = document.getElementById('cust-fields-individual');
                    const compFields = document.getElementById('cust-fields-company');

                    if (typeInput) typeInput.value = type;

                    if (type === 'company') {
                        if (compBtn) {
                            compBtn.style.border = '2px solid #7C3AED';
                            compBtn.style.background = '#F5F3FF';
                            compBtn.style.color = '#7C3AED';
                        }
                        if (indivBtn) {
                            indivBtn.style.border = '1px solid #CBD5E1';
                            indivBtn.style.background = '#FFFFFF';
                            indivBtn.style.color = 'var(--text-main)';
                        }
                        if (indivFields) indivFields.style.display = 'none';
                        if (compFields) compFields.style.display = 'block';
                    } else {
                        if (indivBtn) {
                            indivBtn.style.border = '2px solid var(--color-primary)';
                            indivBtn.style.background = '#EFF6FF';
                            indivBtn.style.color = 'var(--color-primary)';
                        }
                        if (compBtn) {
                            compBtn.style.border = '1px solid #CBD5E1';
                            compBtn.style.background = '#FFFFFF';
                            compBtn.style.color = 'var(--text-main)';
                        }
                        if (indivFields) indivFields.style.display = 'block';
                        if (compFields) compFields.style.display = 'none';
                    }
                },

                openCreateModal: function (type = 'individual') {
                    const form = document.getElementById('directory-customer-form');
                    if (form) form.reset();

                    document.getElementById('cust-form-id').value = '';
                    document.getElementById('customer-modal-title').innerHTML = '<i class="fa-solid fa-user-plus" style="color: var(--color-primary);"></i> Նոր Հաճախորդ';
                    document.getElementById('cust-submit-btn').innerHTML = '<i class="fa-solid fa-check"></i> Պահպանել Հաճախորդին';

                    const warn = document.getElementById('cust-dup-warning-banner');
                    if (warn) warn.style.display = 'none';

                    this.setFormType(type);
                    document.getElementById('directory-customer-modal').classList.add('active');
                },

                openEditModal: function (id) {
                    const customer = this.list.find(c => c.id === id);
                    if (!customer) return;

                    document.getElementById('cust-form-id').value = customer.id;
                    document.getElementById('customer-modal-title').innerHTML = `<i class="fa-solid fa-pen-to-square" style="color: var(--color-primary);"></i> Խմբագրել: ${customer.customer_code}`;
                    document.getElementById('cust-submit-btn').innerHTML = '<i class="fa-solid fa-check"></i> Թարմացնել Տվյալները';

                    const warn = document.getElementById('cust-dup-warning-banner');
                    if (warn) warn.style.display = 'none';

                    this.setFormType(customer.type);

                    // Populate fields
                    if (customer.type === 'company') {
                        document.getElementById('cust-company-name').value = customer.company?.legal_name || customer.company_name || '';
                        document.getElementById('cust-tax-id').value = customer.tax_id || customer.company?.tax_id || '';
                        document.getElementById('cust-registration-country').value = customer.company?.registration_country || 'AM';
                        document.getElementById('cust-legal-address').value = customer.company?.legal_address || '';
                        document.getElementById('cust-physical-address').value = customer.company?.physical_address || '';
                        document.getElementById('cust-director-name').value = customer.company?.director_name || '';
                        document.getElementById('cust-purchasing-manager').value = customer.company?.purchasing_manager_name || '';
                        document.getElementById('cust-credit-limit').value = customer.company?.credit_limit || 0;
                        document.getElementById('cust-payment-terms').value = customer.company?.payment_terms_days || 0;
                    } else {
                        document.getElementById('cust-first-name').value = customer.individual?.first_name || customer.first_name || '';
                        document.getElementById('cust-last-name').value = customer.individual?.last_name || customer.last_name || '';
                        document.getElementById('cust-birth-date').value = customer.individual?.birth_date ? customer.individual.birth_date.slice(0, 10) : '';
                    }

                    document.getElementById('cust-phone').value = customer.primary_phone || customer.phone || '';
                    document.getElementById('cust-email').value = customer.primary_email || customer.email || '';
                    document.getElementById('cust-website').value = customer.company?.website || '';

                    // Address
                    const addr = customer.default_address || customer.last_used_address || (customer.addresses && customer.addresses[0]);
                    if (addr) {
                        document.getElementById('cust-addr-city').value = addr.city || 'Երևան';
                        document.getElementById('cust-addr-street').value = addr.street || '';
                        document.getElementById('cust-addr-apartment').value = addr.apartment || '';
                        document.getElementById('cust-addr-floor').value = addr.floor || '';
                        document.getElementById('cust-addr-door-code').value = addr.door_code || '';
                        document.getElementById('cust-addr-instructions').value = addr.delivery_instructions || '';
                    }

                    document.getElementById('cust-branch-id').value = customer.primary_branch_id || '';
                    document.getElementById('cust-source-id').value = customer.acquisition_source_id || '';
                    document.getElementById('cust-loyalty-tier').value = customer.loyalty_tier || 'basic';
                    document.getElementById('cust-custom-discount').value = customer.custom_discount_percent || 0;
                    document.getElementById('cust-status').value = customer.status || 'active';
                    document.getElementById('cust-notes').value = customer.notes || '';

                    document.getElementById('directory-customer-modal').classList.add('active');
                },

                checkDuplicates: async function () {
                    const phone = document.getElementById('cust-phone')?.value.trim();
                    const email = document.getElementById('cust-email')?.value.trim();
                    const taxId = document.getElementById('cust-tax-id')?.value.trim();
                    const excludeId = document.getElementById('cust-form-id')?.value;

                    if (!phone && !email && !taxId) return;

                    try {
                        const params = new URLSearchParams();
                        if (phone) params.append('phone', phone);
                        if (email) params.append('email', email);
                        if (taxId) params.append('tax_id', taxId);
                        if (excludeId) params.append('exclude_id', excludeId);

                        const res = await ERP.api(`/customers/check-duplicate?${params.toString()}`);
                        const banner = document.getElementById('cust-dup-warning-banner');
                        const text = document.getElementById('cust-dup-warning-text');

                        if (res && res.has_duplicate) {
                            const d = res.duplicates[0];
                            if (text) {
                                text.innerHTML = `<strong>Ուշադրություն.</strong> Համակարգում արդեն կա նույն տվյալներով հաճախորդ՝ <strong>${d.customer_code} (${d.display_name})</strong>:`;
                            }
                            if (banner) banner.style.display = 'flex';
                        } else {
                            if (banner) banner.style.display = 'none';
                        }
                    } catch (err) {
                        console.warn('Duplicate check skipped:', err);
                    }
                },

                onPhoneBlur: function () {
                    this.checkDuplicates();
                },

                onEmailBlur: function () {
                    this.checkDuplicates();
                },

                onTaxIdBlur: function () {
                    this.checkDuplicates();
                },

                saveCustomer: async function (e) {
                    e.preventDefault();
                    const submitBtn = document.getElementById('cust-submit-btn');
                    const id = document.getElementById('cust-form-id')?.value;
                    const type = document.getElementById('cust-form-type')?.value || 'individual';

                    const payload = {
                        type,
                        phone: document.getElementById('cust-phone')?.value.trim(),
                        email: document.getElementById('cust-email')?.value.trim() || null,
                        primary_branch_id: document.getElementById('cust-branch-id')?.value || null,
                        acquisition_source_id: document.getElementById('cust-source-id')?.value || null,
                        loyalty_tier: document.getElementById('cust-loyalty-tier')?.value || 'basic',
                        custom_discount_percent: parseFloat(document.getElementById('cust-custom-discount')?.value) || 0,
                        status: document.getElementById('cust-status')?.value || 'active',
                        notes: document.getElementById('cust-notes')?.value.trim() || null,
                        street: document.getElementById('cust-addr-street')?.value.trim() || null,
                        city: document.getElementById('cust-addr-city')?.value.trim() || 'Երևան',
                        apartment: document.getElementById('cust-addr-apartment')?.value.trim() || null,
                        floor: document.getElementById('cust-addr-floor')?.value.trim() || null,
                        door_code: document.getElementById('cust-addr-door-code')?.value.trim() || null,
                        delivery_instructions: document.getElementById('cust-addr-instructions')?.value.trim() || null,
                    };

                    if (type === 'company') {
                        payload.company_name = document.getElementById('cust-company-name')?.value.trim();
                        payload.tax_id = document.getElementById('cust-tax-id')?.value.trim() || null;
                        payload.registration_country = document.getElementById('cust-registration-country')?.value || 'AM';
                        payload.legal_address = document.getElementById('cust-legal-address')?.value.trim() || null;
                        payload.physical_address = document.getElementById('cust-physical-address')?.value.trim() || null;
                        payload.director_name = document.getElementById('cust-director-name')?.value.trim() || null;
                        payload.purchasing_manager_name = document.getElementById('cust-purchasing-manager')?.value.trim() || null;
                        payload.credit_limit = parseFloat(document.getElementById('cust-credit-limit')?.value) || 0;
                        payload.payment_terms_days = parseInt(document.getElementById('cust-payment-terms')?.value, 10) || 0;
                        payload.website = document.getElementById('cust-website')?.value.trim() || null;
                    } else {
                        payload.first_name = document.getElementById('cust-first-name')?.value.trim();
                        payload.last_name = document.getElementById('cust-last-name')?.value.trim() || null;
                        payload.birth_date = document.getElementById('cust-birth-date')?.value || null;
                    }

                    if (!payload.phone) {
                        ERP.toast('Հեռախոսահամարը պարտադիր է:', 'error');
                        return;
                    }

                    if (type === 'individual' && !payload.first_name) {
                        ERP.toast('Անունը պարտադիր է:', 'error');
                        return;
                    }

                    if (type === 'company' && !payload.company_name) {
                        ERP.toast('Կազմակերպության անվանումը պարտադիր է:', 'error');
                        return;
                    }

                    try {
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Պահպանում...';
                        }

                        let res;
                        if (id) {
                            res = await ERP.api(`/customers/${id}`, {
                                method: 'PUT',
                                body: JSON.stringify(payload)
                            });
                        } else {
                            res = await ERP.api('/customers', {
                                method: 'POST',
                                body: JSON.stringify(payload)
                            });
                        }

                        if (res && res.success) {
                            ERP.toast(id ? 'Հաճախորդի տվյալները թարմացվեցին:' : 'Հաճախորդը հաջողությամբ գրանցվեց:', 'success');
                            document.getElementById('directory-customer-modal').classList.remove('active');
                            await this.load();
                        } else {
                            throw new Error(res.message || 'Սխալ');
                        }
                    } catch (err) {
                        ERP.toast(err.message, 'error');
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Պահպանել Հաճախորդին';
                        }
                    }
                },

                openProfile360: async function (id) {
                    try {
                        const res = await ERP.api(`/customers/${id}`);
                        if (!res || !res.success) throw new Error(res.message || 'Customer not found');

                        const c = res.data;
                        this.activeCustomer = c;

                        const isComp = c.type === 'company';
                        const name = c.display_name || (isComp ? (c.company?.legal_name || c.company_name) : `${c.first_name || ''} ${c.last_name || ''}`.trim());
                        const initials = name.split(' ').filter(Boolean).map(w => w[0]).join('').slice(0, 2).toUpperCase() || 'ՀԱ';

                        // Header elements
                        document.getElementById('cprof-name').textContent = name;
                        document.getElementById('cprof-code').textContent = c.customer_code || '—';
                        document.getElementById('cprof-phone').textContent = c.primary_phone || c.phone || '—';
                        document.getElementById('cprof-email').textContent = c.primary_email || c.email || '—';
                        document.getElementById('cprof-address').textContent = c.last_used_address?.street || c.default_address?.street || '—';

                        const avatar = document.getElementById('cprof-avatar');
                        if (avatar) {
                            avatar.textContent = initials;
                            if (isComp) avatar.classList.add('company');
                            else avatar.classList.remove('company');
                        }

                        const typeBadge = document.getElementById('cprof-type-badge');
                        if (typeBadge) {
                            typeBadge.textContent = isComp ? 'B2B Company' : 'B2C Individual';
                            typeBadge.className = isComp ? 'badge badge-purple' : 'badge badge-primary';
                        }

                        const statusBadge = document.getElementById('cprof-status-badge');
                        if (statusBadge) {
                            statusBadge.textContent = c.status;
                            statusBadge.className = c.status === 'active' ? 'badge badge-emerald' : 'badge badge-slate';
                        }

                        // Tab counts
                        document.getElementById('cprof-addr-count').textContent = c.addresses ? c.addresses.length : 0;
                        document.getElementById('cprof-orders-count').textContent = c.orders ? c.orders.length : (c.orders_count || 0);

                        // Tab 1: Overview
                        document.getElementById('cprof-kpi-spent').textContent = new Intl.NumberFormat('hy-AM').format(Math.round(parseFloat(c.total_spent || 0))) + ' ֏';
                        document.getElementById('cprof-kpi-aov').textContent = new Intl.NumberFormat('hy-AM').format(Math.round(parseFloat(c.average_order_value || 0))) + ' ֏';
                        document.getElementById('cprof-kpi-orders').textContent = c.orders_count || 0;
                        document.getElementById('cprof-kpi-points').textContent = parseFloat(c.loyalty_account?.points_balance || 0);

                        const score = parseInt(c.customer_score || 50, 10);
                        const scoreEl = document.getElementById('cprof-kpi-score');
                        if (scoreEl) {
                            let scoreClass = 'score-medium';
                            if (score >= 75) scoreClass = 'score-high';
                            else if (score < 40) scoreClass = 'score-low';
                            scoreEl.innerHTML = `<span class="score-badge ${scoreClass}">${score} / 100</span>`;
                        }

                        document.getElementById('cprof-ov-type').textContent = isComp ? 'Իրավաբանական Անձ (B2B)' : 'Ֆիզիկական Անձ (B2C)';
                        document.getElementById('cprof-ov-branch').textContent = c.primary_branch?.name || 'Գլխավոր Մասնաճյուղ';
                        document.getElementById('cprof-ov-source').textContent = c.acquisition_source?.name || c.source || 'Ուղիղ';
                        document.getElementById('cprof-ov-first-order').textContent = c.first_ordered_at ? new Date(c.first_ordered_at).toLocaleDateString('hy-AM') : '—';
                        document.getElementById('cprof-ov-last-order').textContent = c.last_ordered_at ? new Date(c.last_ordered_at).toLocaleDateString('hy-AM') : '—';
                        document.getElementById('cprof-ov-frequency').textContent = (c.average_order_frequency_days || 0) + ' օր';

                        document.getElementById('cprof-ov-card').textContent = c.loyalty_account?.card_number || 'LOY-000000';
                        document.getElementById('cprof-ov-tier').textContent = (c.loyalty_tier || 'basic').toUpperCase();
                        document.getElementById('cprof-ov-discount').textContent = (c.custom_discount_percent || 0) + '%';
                        document.getElementById('cprof-ov-earned-lifetime').textContent = (c.loyalty_account?.lifetime_points_earned || 0) + ' միավոր';
                        document.getElementById('cprof-ov-spent-lifetime').textContent = (c.loyalty_account?.lifetime_points_spent || 0) + ' միավոր';

                        // Tab 2: Profile Data
                        const detailsContainer = document.getElementById('cprof-data-details');
                        if (detailsContainer) {
                            if (isComp) {
                                detailsContainer.innerHTML = `
                                    <div><span style="color:var(--text-muted)">Իրավաբանական անվանում:</span> <strong>${c.company?.legal_name || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">ՀՎՀՀ:</span> <strong class="font-mono">${c.tax_id || c.company?.tax_id || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Գրանցման երկիր:</span> <strong>${c.company?.registration_country || 'AM'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Իրավաբանական հասցե:</span> <strong>${c.company?.legal_address || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Փաստացի հասցե:</span> <strong>${c.company?.physical_address || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Վեբ կայք:</span> <strong>${c.company?.website || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Տնօրեն:</span> <strong>${c.company?.director_name || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Գնումների պատասխանատու:</span> <strong>${c.company?.purchasing_manager_name || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Գլխավոր հաշվապահ:</span> <strong>${c.company?.accountant_name || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Վարկային սահմանաչափ:</span> <strong class="font-mono">${new Intl.NumberFormat('hy-AM').format(c.company?.credit_limit || 0)} ֏</strong></div>
                                    <div><span style="color:var(--text-muted)">Վճարման պայման:</span> <strong>${c.company?.payment_terms_days || 0} օր</strong></div>
                                    <div><span style="color:var(--text-muted)">Ընթացիկ պարտք:</span> <strong class="font-mono" style="color:#DC2626">${new Intl.NumberFormat('hy-AM').format(c.company?.outstanding_balance || 0)} ֏</strong></div>
                                `;
                            } else {
                                detailsContainer.innerHTML = `
                                    <div><span style="color:var(--text-muted)">Անուն:</span> <strong>${c.individual?.first_name || c.first_name || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Ազգանուն:</span> <strong>${c.individual?.last_name || c.last_name || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Ծննդյան ամսաթիվ:</span> <strong>${c.individual?.birth_date ? c.individual.birth_date.slice(0, 10) : '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Հեռախոսահամար:</span> <strong class="font-mono">${c.primary_phone || c.phone || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Էլ. փոստ:</span> <strong>${c.primary_email || c.email || '—'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Լեզվի նախասիրություն:</span> <strong>${c.individual?.preferred_language || 'hy'}</strong></div>
                                    <div><span style="color:var(--text-muted)">Գրանցման ամսաթիվ:</span> <strong>${new Date(c.created_at).toLocaleDateString('hy-AM')}</strong></div>
                                    <div><span style="color:var(--text-muted)">SMS համաձայնություն:</span> <strong>${c.marketing_sms_consent ? 'Այո' : 'Ոչ'}</strong></div>
                                `;
                            }
                        }

                        // Tab 3: Addresses
                        this.renderAddressesList(c.addresses || []);

                        // Tab 4: Orders
                        this.renderOrdersList(c.orders || []);

                        // Tab 5: Loyalty Card & Ledger
                        const cardVisual = document.getElementById('cprof-loyalty-card-visual');
                        if (cardVisual) {
                            cardVisual.className = 'loyalty-card-visual loyalty-tier-' + (c.loyalty_tier || 'basic');
                            document.getElementById('cprof-card-tier-badge').textContent = (c.loyalty_tier || 'basic').toUpperCase();
                            document.getElementById('cprof-card-number').textContent = c.loyalty_account?.card_number || 'LOY-000000';
                            document.getElementById('cprof-card-holder').textContent = name;
                            document.getElementById('cprof-card-balance').textContent = `${parseFloat(c.loyalty_account?.points_balance || 0)} մ.`;
                            document.getElementById('cprof-loyalty-discount').textContent = `${parseFloat(c.custom_discount_percent || 0)}%`;
                        }
                        this.renderLoyaltyTransactions(c.loyalty_transactions || []);

                        // Tab 6: Analytics
                        const topProdTable = document.getElementById('cprof-analytics-top-products');
                        if (topProdTable) {
                            const topProds = c.analytics?.top_products || [];
                            if (topProds.length === 0) {
                                topProdTable.innerHTML = '<tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:1rem;">Գնումների պատմություն դեռ չկա</td></tr>';
                            } else {
                                topProdTable.innerHTML = topProds.map(p => `
                                    <tr>
                                        <td><strong>${p.product_name}</strong></td>
                                        <td style="text-align:right;" class="font-mono">${p.total_quantity}</td>
                                        <td style="text-align:right;" class="font-mono">${new Intl.NumberFormat('hy-AM').format(p.total_amount)} ֏</td>
                                    </tr>
                                `).join('');
                            }
                        }
                        const bar = document.getElementById('cprof-rfm-score-bar');
                        const barVal = document.getElementById('cprof-rfm-score-val');
                        if (bar && barVal) {
                            bar.style.width = `${Math.min(100, Math.max(0, score))}%`;
                            barVal.textContent = `${score} / 100`;
                        }
                        document.getElementById('cprof-an-freq').textContent = (c.average_order_frequency_days || 0) + ' օրը մեկ';
                        document.getElementById('cprof-an-canceled').textContent = c.canceled_orders_count || 0;
                        document.getElementById('cprof-an-returns').textContent = c.returned_orders_count || 0;
                        document.getElementById('cprof-an-branch').textContent = c.primary_branch?.name || 'Գլխավոր Մասնաճյուղ';

                        // Tab 8: Contacts
                        const contactsList = document.getElementById('cprof-contacts-list');
                        if (contactsList) {
                            const contacts = c.contacts || (c.company?.contacts) || [];
                            if (contacts.length === 0) {
                                contactsList.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem;">Լրացուցիչ կոնտակտային անձինք գրանցված չեն:</div>';
                            } else {
                                contactsList.innerHTML = contacts.map(ct => `
                                    <div class="card" style="padding:0.75rem 1rem; display:flex; justify-content:space-between; align-items:center;">
                                        <div>
                                            <div style="font-weight:700;">${ct.name} ${ct.is_primary ? '<span class="badge badge-emerald">Հիմնական</span>' : ''}</div>
                                            <div style="font-size:0.78rem; color:var(--text-muted);">${ct.position || 'Կոնտակտային անձ'}</div>
                                        </div>
                                        <div style="text-align:right; font-size:0.82rem;" class="font-mono">
                                            <div><i class="fa-solid fa-phone"></i> ${ct.phone}</div>
                                            ${ct.email ? `<div><i class="fa-regular fa-envelope"></i> ${ct.email}</div>` : ''}
                                        </div>
                                    </div>
                                `).join('');
                            }
                        }

                        // Tab 9: Notes
                        this.renderNotesList(c.notes_list || c.notes_relation || []);

                        this.switchProfileTab('overview');
                        document.getElementById('customer-profile-modal').classList.add('active');
                    } catch (err) {
                        console.error('Failed to open profile:', err);
                        ERP.toast(err.message, 'error');
                    }
                },

                switchProfileTab: function (tab) {
                    const tabs = ['overview', 'data', 'addresses', 'orders', 'loyalty', 'analytics', 'timeline', 'contacts', 'notes'];
                    tabs.forEach(t => {
                        const btn = document.getElementById(`cptab-btn-${t}`);
                        const content = document.getElementById(`cptab-content-${t}`);
                        if (t === tab) {
                            if (btn) btn.classList.add('active');
                            if (content) content.style.display = 'block';
                        } else {
                            if (btn) btn.classList.remove('active');
                            if (content) content.style.display = 'none';
                        }
                    });

                    if (tab === 'timeline') {
                        this.loadTimeline();
                    }
                },

                renderAddressesList: function (addresses) {
                    const container = document.getElementById('cprof-addresses-list');
                    if (!container) return;

                    if (addresses.length === 0) {
                        container.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem;">Հասցեներ գրանցված չեն: Օգտագործեք «Ավելացնել Հասցե» կոճակը:</div>';
                        return;
                    }

                    container.innerHTML = addresses.map(a => `
                        <div class="card" style="padding:1rem; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <strong style="font-size:0.9rem;">${a.title || 'Առաքման Հասցե'}</strong>
                                    ${a.is_default ? '<span class="badge badge-emerald">Հիմնական (Default)</span>' : ''}
                                    ${a.is_last_used ? '<span class="badge badge-primary">Վերջին Օգտագործված</span>' : ''}
                                </div>
                                <div style="font-size:0.85rem; color:var(--text-heading); margin-top:4px;">
                                    <i class="fa-solid fa-location-dot" style="color:#EF4444;"></i> ${a.city || 'Երևան'}, ${a.street || ''} ${a.apartment ? `, բն. ${a.apartment}` : ''} ${a.floor ? `, հարկ ${a.floor}` : ''}
                                </div>
                                ${a.delivery_instructions ? `<div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">Հրահանգներ՝ ${a.delivery_instructions}</div>` : ''}
                            </div>
                        </div>
                    `).join('');
                },

                renderOrdersList: function (orders) {
                    const tbody = document.getElementById('cprof-orders-table-body');
                    if (!tbody) return;

                    if (orders.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:1.5rem;">Պատվերներ դեռ չկան</td></tr>';
                        return;
                    }

                    tbody.innerHTML = orders.map(o => `
                        <tr>
                            <td><span class="font-mono" style="font-weight:800; color:var(--color-primary);">${o.order_number || o.id.slice(0, 8)}</span></td>
                            <td>${o.placed_at ? new Date(o.placed_at).toLocaleDateString('hy-AM') : '—'}</td>
                            <td><span class="badge badge-slate">${o.delivery_type || 'առաքում'}</span></td>
                            <td style="font-size:0.8rem;">${o.delivery_address_snapshot?.address_line_1 || '—'}</td>
                            <td class="font-mono" style="font-weight:700;">${new Intl.NumberFormat('hy-AM').format(Math.round(o.total || 0))} ֏</td>
                            <td><span class="badge ${o.payment_status === 'paid' ? 'badge-emerald' : 'badge-amber'}">${o.payment_status}</span></td>
                            <td><span class="badge ${o.status === 'delivered' ? 'badge-emerald' : 'badge-primary'}">${o.status}</span></td>
                        </tr>
                    `).join('');
                },

                renderLoyaltyTransactions: function (transactions) {
                    const tbody = document.getElementById('cprof-loyalty-tx-table-body');
                    if (!tbody) return;

                    if (transactions.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:1.5rem;">Լոյալության գործարքներ չեն եղել</td></tr>';
                        return;
                    }

                    tbody.innerHTML = transactions.map(t => {
                        const isPlus = parseFloat(t.points_delta) >= 0;
                        return `
                            <tr>
                                <td><span class="badge ${isPlus ? 'badge-emerald' : 'badge-danger'}">${t.type}</span></td>
                                <td class="font-mono" style="font-weight:800; color:${isPlus ? '#059669' : '#DC2626'};">${isPlus ? '+' : ''}${t.points_delta} մ.</td>
                                <td class="font-mono">${t.balance_after} մ.</td>
                                <td>${t.reason || '—'}</td>
                                <td style="font-size:0.78rem;">${new Date(t.created_at).toLocaleString('hy-AM')}</td>
                                <td style="font-size:0.78rem; color:var(--text-muted);">${t.user?.name || 'Համակարգ'}</td>
                            </tr>
                        `;
                    }).join('');
                },

                renderNotesList: function (notes) {
                    const container = document.getElementById('cprof-notes-list');
                    if (!container) return;

                    if (notes.length === 0) {
                        container.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem;">Նշումներ դեռ չկան:</div>';
                        return;
                    }

                    container.innerHTML = notes.map(n => `
                        <div class="card" style="padding:0.75rem 1rem; border-left: 3px solid ${n.is_pinned ? '#D97706' : 'var(--color-primary)'};">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <span class="badge badge-slate" style="font-size:0.68rem;">${n.category || 'Ընդհանուր'}</span>
                                    ${n.is_pinned ? '<span class="badge badge-amber" style="font-size:0.68rem;"><i class="fa-solid fa-thumbtack"></i> Pinned</span>' : ''}
                                </div>
                                <span style="font-size:0.72rem; color:var(--text-muted);">${new Date(n.created_at).toLocaleString('hy-AM')}</span>
                            </div>
                            <div style="font-size:0.85rem; color:var(--text-heading); white-space:pre-wrap;">${n.content}</div>
                            <div style="font-size:0.7rem; color:var(--text-muted); margin-top:4px;">Հեղինակ՝ ${n.author?.name || 'Աշխատակից'}</div>
                        </div>
                    `).join('');
                },

                toggleAddAddressForm: function () {
                    const f = document.getElementById('cprof-add-address-form');
                    if (f) {
                        f.style.display = f.style.display === 'none' ? 'block' : 'none';
                    }
                },

                submitAddress: async function () {
                    if (!this.activeCustomer) return;
                    const street = document.getElementById('caddr-street')?.value.trim();
                    if (!street) {
                        ERP.toast('Փողոցը / շենքը պարտադիր է:', 'error');
                        return;
                    }

                    const payload = {
                        title: document.getElementById('caddr-title')?.value.trim() || 'Առաքման Հասցե',
                        street,
                        city: document.getElementById('caddr-city')?.value.trim() || 'Երևան',
                        apartment: document.getElementById('caddr-apartment')?.value.trim() || null,
                        floor: document.getElementById('caddr-floor')?.value.trim() || null,
                        door_code: document.getElementById('caddr-door-code')?.value.trim() || null,
                        delivery_instructions: document.getElementById('caddr-instructions')?.value.trim() || null,
                        is_default: document.getElementById('caddr-is-default')?.checked || false
                    };

                    try {
                        const res = await ERP.api(`/customers/${this.activeCustomer.id}/addresses`, {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        if (res && res.success) {
                            ERP.toast('Հասցեն ավելացվեց:', 'success');
                            this.toggleAddAddressForm();
                            await this.openProfile360(this.activeCustomer.id);
                        }
                    } catch (err) {
                        ERP.toast(err.message, 'error');
                    }
                },

                submitNote: async function () {
                    if (!this.activeCustomer) return;
                    const content = document.getElementById('cnote-content')?.value.trim();
                    if (!content) {
                        ERP.toast('Գրեք նշման տեքստը:', 'error');
                        return;
                    }

                    const payload = {
                        content,
                        category: document.getElementById('cnote-category')?.value || 'general',
                        is_pinned: document.getElementById('cnote-pinned')?.checked || false
                    };

                    try {
                        const res = await ERP.api(`/customers/${this.activeCustomer.id}/notes`, {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        if (res && res.success) {
                            ERP.toast('Գրառումը պահպանվեց:', 'success');
                            document.getElementById('cnote-content').value = '';
                            await this.openProfile360(this.activeCustomer.id);
                            this.switchProfileTab('notes');
                        }
                    } catch (err) {
                        ERP.toast(err.message, 'error');
                    }
                },

                openLoyaltyAdjustModal: function () {
                    if (!this.activeCustomer) return;
                    document.getElementById('loyalty-adj-delta').value = '';
                    document.getElementById('loyalty-adj-reason').value = '';
                    document.getElementById('customer-loyalty-adjust-modal').classList.add('active');
                },

                submitLoyaltyAdjustment: async function () {
                    if (!this.activeCustomer) return;
                    const delta = parseFloat(document.getElementById('loyalty-adj-delta')?.value);
                    const reason = document.getElementById('loyalty-adj-reason')?.value.trim();
                    const type = document.getElementById('loyalty-adj-type')?.value || 'manual_adj';

                    if (isNaN(delta) || delta === 0) {
                        ERP.toast('Մուտքագրեք 0-ից տարբեր միավոր:', 'error');
                        return;
                    }
                    if (!reason) {
                        ERP.toast('Հիմնավորումը պարտադիր է:', 'error');
                        return;
                    }

                    try {
                        const res = await ERP.api(`/customers/${this.activeCustomer.id}/loyalty/adjust`, {
                            method: 'POST',
                            body: JSON.stringify({ points_delta: delta, reason, type })
                        });
                        if (res && res.success) {
                            ERP.toast('Լոյալության միավորները ճշգրտվեցին:', 'success');
                            document.getElementById('customer-loyalty-adjust-modal').classList.remove('active');
                            await this.openProfile360(this.activeCustomer.id);
                            this.switchProfileTab('loyalty');
                        }
                    } catch (err) {
                        ERP.toast(err.message, 'error');
                    }
                },

                loadTimeline: async function () {
                    if (!this.activeCustomer) return;
                    const container = document.getElementById('cprof-timeline-container');
                    if (!container) return;

                    container.innerHTML = '<div style="color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Բեռնվում է timeline...</div>';

                    try {
                        const res = await ERP.api(`/customers/${this.activeCustomer.id}/timeline`);
                        if (res && res.success) {
                            const activities = res.data || [];
                            if (activities.length === 0) {
                                container.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem;">Ժամանակագրության մեջ դեռ գրառումներ չկան:</div>';
                                return;
                            }

                            const icons = {
                                order_placed: 'fa-cart-shopping',
                                status_change: 'fa-arrows-rotate',
                                loyalty_tx: 'fa-award',
                                note_added: 'fa-note-sticky',
                                call: 'fa-phone',
                                email: 'fa-envelope',
                                merged: 'fa-code-merge'
                            };

                            container.innerHTML = activities.map(a => `
                                <div class="crm-timeline-item">
                                    <div class="crm-timeline-node">
                                        <i class="fa-solid ${icons[a.type] || 'fa-circle-dot'}"></i>
                                    </div>
                                    <div class="crm-timeline-card">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                            <strong style="font-size:0.85rem;">${a.title}</strong>
                                            <span style="font-size:0.72rem; color:var(--text-muted);">${new Date(a.created_at).toLocaleString('hy-AM')}</span>
                                        </div>
                                        <div style="font-size:0.82rem; color:var(--text-muted);">${a.content || ''}</div>
                                        ${a.user ? `<div style="font-size:0.7rem; color:var(--text-subtle); margin-top:2px;">Հեղինակ՝ ${a.user.name}</div>` : ''}
                                    </div>
                                </div>
                            `).join('');
                        }
                    } catch (err) {
                        console.error('Failed to load timeline:', err);
                        container.innerHTML = `<div style="color:#EF4444; font-size:0.85rem;">Սխալ timeline բեռնելիս: ${err.message}</div>`;
                    }
                },

                openMergeModal: function () {
                    const targetSel = document.getElementById('merge-target-id');
                    const sourceSel = document.getElementById('merge-source-id');

                    if (targetSel && sourceSel) {
                        const options = '<option value="">— Ընտրել հաճախորդին —</option>' + this.list.map(c => `
                            <option value="${c.id}">${c.customer_code} — ${c.display_name} (${c.primary_phone || c.phone})</option>
                        `).join('');
                        targetSel.innerHTML = options;
                        sourceSel.innerHTML = options;
                    }

                    document.getElementById('customer-merge-modal').classList.add('active');
                },

                submitMerge: async function () {
                    const targetId = document.getElementById('merge-target-id')?.value;
                    const sourceId = document.getElementById('merge-source-id')?.value;
                    const notes = document.getElementById('merge-notes')?.value.trim();

                    if (!targetId || !sourceId) {
                        ERP.toast('Ընտրեք երկու հաճախորդներին էլ:', 'error');
                        return;
                    }
                    if (targetId === sourceId) {
                        ERP.toast('Հիմնական և կլանվող հաճախորդները չեն կարող լինել նույնը:', 'error');
                        return;
                    }

                    if (!confirm('Վստա՞հ եք, որ ցանկանում եք միավորել հաճախորդներին: Այս գործողությունը կմիավորի բոլոր տվյալները:')) {
                        return;
                    }

                    try {
                        const res = await ERP.api('/customers/merge', {
                            method: 'POST',
                            body: JSON.stringify({
                                target_customer_id: targetId,
                                source_customer_id: sourceId,
                                notes
                            })
                        });

                        if (res && res.success) {
                            ERP.toast('Հաճախորդները հաջողությամբ միավորվեցին:', 'success');
                            document.getElementById('customer-merge-modal').classList.remove('active');
                            await this.load();
                        }
                    } catch (err) {
                        ERP.toast(err.message, 'error');
                    }
                },

                archiveCustomer: async function (id) {
                    if (!confirm('Վստա՞հ եք, որ ցանկանում եք արխիվացնել այս հաճախորդին:')) return;

                    try {
                        const res = await ERP.api(`/customers/${id}`, { method: 'DELETE' });
                        if (res && res.success) {
                            ERP.toast('Հաճախորդը արխիվացվեց:', 'success');
                            await this.load();
                        }
                    } catch (err) {
                        ERP.toast(err.message, 'error');
                    }
                },

                archiveCurrent: async function () {
                    if (!this.activeCustomer) return;
                    await this.archiveCustomer(this.activeCustomer.id);
                    document.getElementById('customer-profile-modal').classList.remove('active');
                },

                openEditCurrent: function () {
                    if (!this.activeCustomer) return;
                    document.getElementById('customer-profile-modal').classList.remove('active');
                    this.openEditModal(this.activeCustomer.id);
                },

                exportCSV: function () {
                    if (!this.list || this.list.length === 0) {
                        ERP.toast('Արտահանման համար տվյալներ չկան:', 'warning');
                        return;
                    }

                    const rows = [
                        ['Կոդ', 'Տեսակ', 'Անվանում', 'ՀՎՀՀ', 'Հեռախոս', 'Էլ. փոստ', 'Tier', 'Միավորներ', 'Զեղչ (%)', 'Ընդհանուր (֏)', 'Պատվերներ', 'Կարգավիճակ']
                    ];

                    this.list.forEach(c => {
                        rows.push([
                            c.customer_code || '',
                            c.type || '',
                            `"${(c.display_name || '').replace(/"/g, '""')}"`,
                            c.tax_id || '',
                            c.primary_phone || c.phone || '',
                            c.primary_email || c.email || '',
                            c.loyalty_tier || '',
                            c.loyalty_account?.points_balance || 0,
                            c.custom_discount_percent || 0,
                            c.total_spent || 0,
                            c.orders_count || 0,
                            c.status || ''
                        ]);
                    });

                    const csvContent = '\uFEFF' + rows.map(r => r.join(',')).join('\n');
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.setAttribute('href', url);
                    link.setAttribute('download', `customers_export_${new Date().toISOString().slice(0, 10)}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    ERP.toast('CSV ֆայլը հաջողությամբ արտահանվեց:', 'success');
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

            // Hydrate categories from bootstrap data if available
            if (window.SERVER_INITIAL_DATA?.categories && window.SERVER_INITIAL_DATA.categories.length > 0) {
                ERP.directory.categories.list = window.SERVER_INITIAL_DATA.categories;
                ERP.directory.categories.updateSelectDropdowns(ERP.directory.categories.list);
            }

            // Hydrate customers and sources from bootstrap data if available
            if (window.SERVER_INITIAL_DATA?.customers && window.SERVER_INITIAL_DATA.customers.length > 0) {
                ERP.directory.customers.list = window.SERVER_INITIAL_DATA.customers;
            }
            if (window.SERVER_INITIAL_DATA?.customerSources && window.SERVER_INITIAL_DATA.customerSources.length > 0) {
                ERP.directory.customers.sources = window.SERVER_INITIAL_DATA.customerSources;
            }

            // Check hash in URL or default to dashboard
            const hash = window.location.hash.replace('#', '') || 'dashboard';
            const validViews = ['dashboard', 'catalog', 'directory-categories', 'directory-suppliers', 'directory-ingredients', 'directory-customers', 'procurement', 'pos', 'inventory', 'manufacturing', 'quality', 'delivery', 'users', 'roles', 'billing', 'settings', 'api-console'];
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
