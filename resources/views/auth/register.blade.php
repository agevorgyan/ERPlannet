<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Գործընկերների գրանցում — ERPlannet SaaS ERP</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- CSS -->
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="auth-page-wrapper">

    <div class="auth-container wide">
        <!-- Brand Header -->
        <a href="/" class="auth-brand">
            <div class="brand-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                    <path d="M7 7h10"/>
                    <path d="M7 12h10"/>
                    <path d="M7 17h10"/>
                </svg>
            </div>
            <div class="brand-title">
                ERPlannet <span class="brand-badge" style="font-size: 0.65rem; padding: 2px 7px;">SaaS</span>
            </div>
        </a>

        <!-- Main Auth Card -->
        <div class="auth-card">
            <div class="auth-header">
                <h1>Գրանցեք Ձեր կազմակերպությունը <i class="fa-solid fa-rocket" style="color: var(--color-primary); font-size: 1.5rem; margin-left: 4px;"></i></h1>
                <p>Ստեղծեք նոր ERP աշխատանքային տարածք 14-օրյա անվճար փորձաշրջանով (Free Trial)</p>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="auth-alert-box auth-alert-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="auth-alert-box auth-alert-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="/register" method="POST" class="auth-form" id="registerForm">
                @csrf

                <!-- Company Name & Subdomain -->
                <div class="form-row-2">
                    <div class="auth-group">
                        <label class="auth-label" for="company_name">Կազմակերպության անվանում</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-building"></i></span>
                            <input type="text" name="company_name" id="company_name" class="auth-input" 
                                   value="{{ old('company_name', 'Ararat Premium Foods') }}" required 
                                   placeholder="օր. Ararat Foods LLC" oninput="updateSubdomain(this.value)">
                        </div>
                    </div>

                    <div class="auth-group">
                        <label class="auth-label" for="subdomain">Համակարգային դոմեն (Workspace URL)</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-globe"></i></span>
                            <input type="text" name="subdomain" id="subdomain" class="auth-input" 
                                   value="{{ old('subdomain', 'ararat-foods') }}" required 
                                   placeholder="ararat-foods">
                        </div>
                        <div class="auth-subdomain-preview" id="subdomainPreview">https://ararat-foods.erplannet.am</div>
                    </div>
                </div>

                <!-- Owner Name & Phone -->
                <div class="form-row-2">
                    <div class="auth-group">
                        <label class="auth-label" for="owner_name">Գործընկերոջ անուն ազգանուն</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="owner_name" id="owner_name" class="auth-input" 
                                   value="{{ old('owner_name', 'Karen Petrosyan') }}" required placeholder="օր. Karen Petrosyan">
                        </div>
                    </div>

                    <div class="auth-group">
                        <label class="auth-label" for="owner_phone">Հեռախոսահամար</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" name="owner_phone" id="owner_phone" class="auth-input" 
                                   value="{{ old('owner_phone', '+374 91 223344') }}" placeholder="+374 91 000000">
                        </div>
                    </div>
                </div>

                <!-- Email & Currency -->
                <div class="form-row-2">
                    <div class="auth-group">
                        <label class="auth-label" for="owner_email">Աշխատանքային էլ. փոստ</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="owner_email" id="owner_email" class="auth-input" 
                                   value="{{ old('owner_email', 'karen@araratfoods.am') }}" required placeholder="owner@company.am">
                        </div>
                    </div>

                    <div class="auth-group">
                        <label class="auth-label" for="currency">Հիմնական տարադրամ</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-money-bill-transfer"></i></span>
                            <select name="currency" id="currency" class="auth-input" style="padding-left: 2.65rem;">
                                <option value="AMD" selected>AMD — Հայկական դրամ (֏)</option>
                                <option value="USD">USD — ԱՄՆ դոլար ($)</option>
                                <option value="EUR">EUR — Եվրո (€)</option>
                                <option value="RUB">RUB — Ռուսական ռուբլի (₽)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Password & Confirm Password -->
                <div class="form-row-2">
                    <div class="auth-group">
                        <label class="auth-label" for="password">Գաղտնաբառ (առնվազն 8 նիշ)</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" id="password" class="auth-input" 
                                   value="password123" required placeholder="••••••••">
                            <button type="button" class="auth-pw-toggle" onclick="togglePasswordVisibility('password', this)"><i class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="auth-group">
                        <label class="auth-label" for="password_confirmation">Կրկնել գաղտնաբառը</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="auth-input" 
                                   value="password123" required placeholder="••••••••">
                            <button type="button" class="auth-pw-toggle" onclick="togglePasswordVisibility('password_confirmation', this)"><i class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Plan Selection -->
                <div class="auth-group">
                    <label class="auth-label">Ընտրեք սակագնային պլանը</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.75rem; margin-top: 0.25rem;">
                        <label style="border: 2px solid var(--color-primary); border-radius: 10px; padding: 0.85rem; background: #EFF6FF; cursor: pointer; display: flex; flex-direction: column; gap: 0.2rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-weight: 700; font-size: 0.9rem; color: #1E293B;">Starter Plan</span>
                                <input type="radio" name="plan_code" value="starter" checked style="accent-color: var(--color-primary);">
                            </div>
                            <span style="font-size: 0.76rem; color: #059669; font-weight: 700;">14 օր անվճար փորձաշրջան</span>
                            <span style="font-size: 0.75rem; color: #64748B;">Հարմար է փոքր բիզնեսների համար</span>
                        </label>

                        <label style="border: 1px solid #CBD5E1; border-radius: 10px; padding: 0.85rem; background: #FFFFFF; cursor: pointer; display: flex; flex-direction: column; gap: 0.2rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-weight: 700; font-size: 0.9rem; color: #1E293B;">Professional</span>
                                <input type="radio" name="plan_code" value="professional" style="accent-color: var(--color-primary);">
                            </div>
                            <span style="font-size: 0.76rem; color: #2563EB; font-weight: 700;">Արտադրություն + POS</span>
                            <span style="font-size: 0.75rem; color: #64748B;">Բազմաթիվ պահեստներ</span>
                        </label>

                        <label style="border: 1px solid #CBD5E1; border-radius: 10px; padding: 0.85rem; background: #FFFFFF; cursor: pointer; display: flex; flex-direction: column; gap: 0.2rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-weight: 700; font-size: 0.9rem; color: #1E293B;">Enterprise</span>
                                <input type="radio" name="plan_code" value="enterprise" style="accent-color: var(--color-primary);">
                            </div>
                            <span style="font-size: 0.76rem; color: #7C3AED; font-weight: 700;">Անսահմանափակ</span>
                            <span style="font-size: 0.75rem; color: #64748B;">ERP + CRM + Առաքում</span>
                        </label>
                    </div>
                </div>

                <!-- Terms -->
                <label class="auth-checkbox-label" style="margin-top: 0.25rem;">
                    <input type="checkbox" required checked style="accent-color: var(--color-primary); width: 16px; height: 16px;">
                    <span style="font-size: 0.82rem;">Ես ընդունում եմ ERPlannet-ի <a href="#" class="auth-link">օգտագործման պայմանները</a> և <a href="#" class="auth-link">գաղտնիության քաղաքականությունը</a>:</span>
                </label>

                <!-- Submit Button -->
                <button type="submit" class="auth-btn-primary" id="registerSubmitBtn">
                    <span>Ստեղծել կազմակերպության հաշիվ</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </button>
            </form>

            <!-- Footer link to Login -->
            <div class="auth-footer">
                Արդեն ունե՞ք գրանցված հաշիվ: 
                <a href="/login" class="auth-link" style="margin-left: 4px;">Մուտք գործել →</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="/" style="font-size: 0.84rem; color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                ← Վերադառնալ գլխավոր Dashboard
            </a>
        </div>
    </div>

    <script>
        function updateSubdomain(val) {
            const clean = val.toLowerCase()
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            const input = document.getElementById('subdomain');
            input.value = clean || 'workspace';
            document.getElementById('subdomainPreview').textContent = `https://${input.value}.erplannet.am`;
        }

        document.getElementById('subdomain').addEventListener('input', function(e) {
            document.getElementById('subdomainPreview').textContent = `https://${this.value}.erplannet.am`;
        });

        function togglePasswordVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i class="fa-regular fa-eye"></i>';
            }
        }
    </script>
</body>
</html>
