<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Գաղտնաբառի և Էլ. փոստի վերականգնում — ERPlannet SaaS ERP</title>

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
                <h1>Հաշվի վերականգնում <i class="fa-solid fa-key" style="color: var(--color-primary); font-size: 1.5rem; margin-left: 4px;"></i></h1>
                <p>Վերականգնեք մուտքի գաղտնաբառը կամ գտեք Ձեր գրանցված էլ. փոստը</p>
            </div>

            <!-- Two Mode Tabs -->
            <div class="auth-tab-buttons">
                <button type="button" class="auth-tab-btn active" id="tabResetBtn" onclick="switchRecoveryTab('reset')">
                    <i class="fa-solid fa-key" style="margin-right: 4px;"></i> Գաղտնաբառի փոփոխում
                </button>
                <button type="button" class="auth-tab-btn" id="tabLookupBtn" onclick="switchRecoveryTab('lookup')">
                    <i class="fa-solid fa-magnifying-glass" style="margin-right: 4px;"></i> Էլ. փոստի որոնում
                </button>
            </div>

            <!-- Dynamic Alert Box -->
            <div id="dynamicAlert" class="auth-alert-box" style="display: none;"></div>

            <!-- TAB 1: Password Reset Flow -->
            <div id="recoveryResetSection">
                <!-- STEP 1: Request Code -->
                <form id="requestCodeForm" onsubmit="handleRequestCode(event)" class="auth-form">
                    @csrf
                    <div class="auth-group">
                        <label class="auth-label" for="reset_email">Մուտքագրեք Ձեր աշխատանքային էլ. փոստը</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="reset_email" name="email" class="auth-input" 
                                   value="aram@gourmet.am" required placeholder="name@company.am">
                        </div>
                    </div>

                    <button type="submit" class="auth-btn-primary" id="sendCodeBtn">
                        <span>Ստանալ վերականգնման կոդ</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                        </svg>
                    </button>
                </form>

                <!-- STEP 2: Confirm Token & Reset Password -->
                <form id="confirmResetForm" onsubmit="handleConfirmReset(event)" class="auth-form" style="display: none; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px dashed #E2E8F0;">
                    @csrf
                    <input type="hidden" id="confirm_email" name="email">

                    <div class="auth-group">
                        <label class="auth-label" for="reset_token">
                            <span>Վերականգնման կոդ (Verification Code)</span>
                            <span style="color: #059669; font-weight: 700; font-size: 0.76rem;" id="codeAutoHint"></span>
                        </label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" id="reset_token" name="token" class="auth-input" 
                                   required placeholder="6-նիշ կոդ">
                        </div>
                    </div>

                    <div class="auth-group">
                        <label class="auth-label" for="new_password">Նոր գաղտնաբառ (առնվազն 8 նիշ)</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="new_password" name="password" class="auth-input" 
                                   required placeholder="••••••••">
                            <button type="button" class="auth-pw-toggle" onclick="togglePasswordVisibility('new_password', this)"><i class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="auth-group">
                        <label class="auth-label" for="new_password_confirmation">Կրկնել նոր գաղտնաբառը</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="new_password_confirmation" name="password_confirmation" class="auth-input" 
                                   required placeholder="••••••••">
                            <button type="button" class="auth-pw-toggle" onclick="togglePasswordVisibility('new_password_confirmation', this)"><i class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>

                    <button type="submit" class="auth-btn-primary" id="savePasswordBtn">
                        <span>Հաստատել նոր գաղտնաբառը</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- TAB 2: Recover / Lookup Email -->
            <div id="recoveryLookupSection" style="display: none;">
                <form id="lookupForm" onsubmit="handleLookupEmail(event)" class="auth-form">
                    @csrf
                    <p style="font-size: 0.85rem; color: #64748B; margin-bottom: 0.5rem;">
                        Մոռացե՞լ եք Ձեր գրանցված էլ. փոստը: Որոնեք ըստ Ձեր կազմակերպության դոմենի (Workspace) կամ հեռախոսահամարի:
                    </p>

                    <div class="auth-group">
                        <label class="auth-label" for="lookup_subdomain">Կազմակերպության Subdomain / Դոմեն</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-building"></i></span>
                            <input type="text" id="lookup_subdomain" name="subdomain" class="auth-input" 
                                   value="gourmet" placeholder="օր. gourmet">
                        </div>
                    </div>

                    <div style="text-align: center; font-size: 0.78rem; font-weight: 700; color: #94A3B8;">— ԿԱՄ —</div>

                    <div class="auth-group">
                        <label class="auth-label" for="lookup_phone">Գրանցված հեռախոսահամար</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" id="lookup_phone" name="phone" class="auth-input" 
                                   placeholder="+374 91 000000">
                        </div>
                    </div>

                    <button type="submit" class="auth-btn-primary" id="lookupSubmitBtn">
                        <span>Գտնել էլ. փոստը</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </button>
                </form>

                <!-- Lookup Result Box -->
                <div id="lookupResultCard" style="display: none; margin-top: 1.25rem; padding: 1.25rem; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 12px;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #059669; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                        <span><i class="fa-solid fa-circle-check"></i></span> Գտնված կազմակերպություն
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.86rem; color: #1E293B;">
                        <div><strong>Կազմակերպություն:</strong> <span id="resTenantName"></span></div>
                        <div><strong>Սեփականատեր:</strong> <span id="resOwnerName"></span></div>
                        <div><strong>Գրանցված էլ. փոստ:</strong> <span id="resMaskedEmail" style="font-family: var(--font-mono); font-weight: 700; color: #2563EB;"></span></div>
                    </div>
                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                        <button type="button" class="auth-btn-secondary" onclick="useFoundEmailForReset()" style="font-size: 0.8rem; height: 38px;">
                            <i class="fa-solid fa-key" style="margin-right: 4px;"></i> Փոխել գաղտնաբառը
                        </button>
                        <button type="button" class="auth-btn-primary" onclick="proceedToLoginWithFound()" style="font-size: 0.8rem; height: 38px; margin-top: 0;">
                            <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 4px;"></i> Մուտք գործել
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer links -->
            <div class="auth-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <a href="/login" class="auth-link">← Վերադառնալ մուտքի էջ</a>
                <a href="/register" class="auth-link">Գրանցել նոր կազմակերպություն →</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="/" style="font-size: 0.84rem; color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                ← Վերադառնալ գլխավոր Dashboard
            </a>
        </div>
    </div>

    <script>
        let foundData = null;

        function showAlert(msg, type = 'info') {
            const el = document.getElementById('dynamicAlert');
            el.className = `auth-alert-box auth-alert-${type}`;
            el.innerHTML = `<span>${msg}</span>`;
            el.style.display = 'flex';
        }

        function hideAlert() {
            document.getElementById('dynamicAlert').style.display = 'none';
        }

        function switchRecoveryTab(tab) {
            hideAlert();
            if (tab === 'reset') {
                document.getElementById('tabResetBtn').classList.add('active');
                document.getElementById('tabLookupBtn').classList.remove('active');
                document.getElementById('recoveryResetSection').style.display = 'block';
                document.getElementById('recoveryLookupSection').style.display = 'none';
            } else {
                document.getElementById('tabLookupBtn').classList.add('active');
                document.getElementById('tabResetBtn').classList.remove('active');
                document.getElementById('recoveryLookupSection').style.display = 'block';
                document.getElementById('recoveryResetSection').style.display = 'none';
            }
        }

        async function handleRequestCode(e) {
            e.preventDefault();
            hideAlert();
            const email = document.getElementById('reset_email').value;
            const btn = document.getElementById('sendCodeBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>Ուղարկվում է...</span>';

            try {
                const res = await fetch('/password-reset/request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showAlert(data.message || 'Վերականգնման կոդը պատրաստ է:', 'success');
                    document.getElementById('confirmResetForm').style.display = 'block';
                    document.getElementById('confirm_email').value = email;
                    if (data.data && data.data.token) {
                        document.getElementById('reset_token').value = data.data.token;
                        document.getElementById('codeAutoHint').textContent = `(Կոդ՝ ${data.data.token})`;
                    }
                } else {
                    showAlert(data.error?.message || data.message || 'Սխալ տեղի ունեցավ:', 'error');
                }
            } catch (err) {
                showAlert('Սերվերի հետ կապի խնդիր:', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Ստանալ վերականգնման կոդ</span>';
            }
        }

        async function handleConfirmReset(e) {
            e.preventDefault();
            hideAlert();
            const email = document.getElementById('confirm_email').value || document.getElementById('reset_email').value;
            const token = document.getElementById('reset_token').value;
            const password = document.getElementById('new_password').value;
            const password_confirmation = document.getElementById('new_password_confirmation').value;

            const btn = document.getElementById('savePasswordBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>Պահպանվում է...</span>';

            try {
                const res = await fetch('/password-reset/confirm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        token: token,
                        password: password,
                        password_confirmation: password_confirmation
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showAlert('Գաղտնաբառը հաջողությամբ թարմացվեց: Վերահասցեավորում...', 'success');
                    setTimeout(() => {
                        window.location.href = data.data?.redirect || '/login';
                    }, 1500);
                } else {
                    showAlert(data.error?.message || data.message || 'Սխալ վերականգնման կոդ կամ գաղտնաբառ:', 'error');
                }
            } catch (err) {
                showAlert('Սերվերի հետ կապի խնդիր:', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Հաստատել նոր գաղտնաբառը</span>';
            }
        }

        async function handleLookupEmail(e) {
            e.preventDefault();
            hideAlert();
            const subdomain = document.getElementById('lookup_subdomain').value;
            const phone = document.getElementById('lookup_phone').value;

            const btn = document.getElementById('lookupSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>Որոնվում է...</span>';

            try {
                const res = await fetch('/password-reset/lookup-account', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ subdomain: subdomain, phone: phone })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    foundData = data.data;
                    document.getElementById('resTenantName').textContent = data.data.tenant_name;
                    document.getElementById('resOwnerName').textContent = data.data.owner_name;
                    document.getElementById('resMaskedEmail').textContent = data.data.masked_email;
                    document.getElementById('lookupResultCard').style.display = 'block';
                    showAlert('Կազմակերպության հաշիվը գտնվել է:', 'success');
                } else {
                    document.getElementById('lookupResultCard').style.display = 'none';
                    showAlert(data.message || 'Հաշիվը չգտնվեց:', 'error');
                }
            } catch (err) {
                showAlert('Սերվերի հետ կապի խնդիր:', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Գտնել էլ. փոստը</span>';
            }
        }

        function useFoundEmailForReset() {
            if (foundData && foundData.full_email) {
                document.getElementById('reset_email').value = foundData.full_email;
            }
            switchRecoveryTab('reset');
        }

        function proceedToLoginWithFound() {
            if (foundData) {
                window.location.href = `/login?tenant=${encodeURIComponent(foundData.subdomain)}&email=${encodeURIComponent(foundData.full_email)}`;
            } else {
                window.location.href = '/login';
            }
        }

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
