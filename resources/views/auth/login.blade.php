<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Մուտք — ERPlannet SaaS ERP</title>

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

    <div class="auth-container">
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
                <h1>Բարի վերադարձ</h1>
                <p>Մուտք գործեք Ձեր կազմակերպության ERP աշխատանքային տարածք</p>
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

            <form action="/login" method="POST" class="auth-form" id="loginForm">
                @csrf

                <!-- Workspace / Tenant -->
                <div class="auth-group">
                    <label class="auth-label" for="tenant_slug">
                        <span>Կազմակերպություն / Տարածք (Workspace)</span>
                        <span style="font-weight: 400; color: #94A3B8; font-size: 0.76rem;">(ըստ ցանկության)</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon"><i class="fa-solid fa-building"></i></span>
                        <input type="text" name="tenant_slug" id="tenant_slug" class="auth-input" 
                               value="{{ request('tenant', $currentTenant ? $currentTenant->slug : 'gourmet') }}" 
                               placeholder="օր. gourmet կամ դատարկ">
                    </div>
                </div>

                <!-- Email -->
                <div class="auth-group">
                    <label class="auth-label" for="email">Աշխատանքային էլ. փոստ</label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="auth-input" 
                               value="{{ old('email', 'aram@gourmet.am') }}" 
                               required autofocus placeholder="name@company.am">
                    </div>
                </div>

                <!-- Password -->
                <div class="auth-group">
                    <label class="auth-label" for="password">Գաղտնաբառ</label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="auth-input" 
                               value="password123" required placeholder="••••••••">
                        <button type="button" class="auth-pw-toggle" onclick="togglePasswordVisibility('password', this)" title="Ցուցադրել">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="auth-links-row">
                    <label class="auth-checkbox-label">
                        <input type="checkbox" name="remember" value="1" checked style="accent-color: var(--color-primary); width: 15px; height: 15px;">
                        <span>Հիշել ինձ</span>
                    </label>
                    <a href="/password-reset" class="auth-link">Մոռացե՞լ եք գաղտնաբառը կամ էլ. փոստը</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-btn-primary" id="loginSubmitBtn">
                    <span>Մուտք գործել</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </button>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="demo-credentials-card">
                <div class="title">
                    <span><i class="fa-solid fa-bolt" style="color: #f59e0b; margin-right: 4px;"></i> Արագ փորձարկման օգտահաշիվներ</span>
                    <span>1-Click Fill</span>
                </div>
                <div class="demo-account-pills">
                    <button type="button" class="demo-pill" onclick="fillCredentials('gourmet', 'aram@gourmet.am', 'password123')">
                        <i class="fa-solid fa-building" style="color: var(--color-primary); margin-right: 2px;"></i> <strong>Gourmet Owner</strong> (aram@gourmet.am)
                    </button>
                    <button type="button" class="demo-pill" onclick="fillCredentials('', 'admin@erplannet.com', 'SecretPass123!')">
                        <i class="fa-solid fa-shield-halved" style="color: #7c3aed; margin-right: 2px;"></i> <strong>Superadmin</strong> (admin@erplannet.com)
                    </button>
                </div>
            </div>

            <!-- Footer link to Register -->
            <div class="auth-footer">
                Գործընկեր չե՞ք: 
                <a href="/register" class="auth-link" style="margin-left: 4px;">Գրանցեք Ձեր կազմակերպությունը →</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="/" style="font-size: 0.84rem; color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                ← Վերադառնալ գլխավոր Dashboard
            </a>
        </div>
    </div>

    <script>
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

        function fillCredentials(tenant, email, password) {
            document.getElementById('tenant_slug').value = tenant;
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
