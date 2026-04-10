<?php
/**
 * Credentials Wallet — Admin Management Page
 * File: modules/Dashboard/views/credentials-wallet.php
 * Route: /admin/credentials-wallet
 *
 * Super-Admin only (enforced in admin-base and sidebar).
 * Stores encrypted credentials for all CEP digital accounts.
 */
$pageTitle = 'Credentials Wallet';
$requiredPermission = 'wallet.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

$canCreate = hasPermission($userPermissions, 'wallet.create');
$canEdit = hasPermission($userPermissions, 'wallet.edit');
$canDelete = hasPermission($userPermissions, 'wallet.delete');
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
    <?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
    <script>(function () { var el = document.getElementById('sessionLockOverlay'); if (el) el.dataset.email = <?= json_encode($currentUser->email ?? '') ?>; })();</script>

    <?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
    <?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

    <style>
        /* ── Wallet-specific styles ─────────────────────────── */
        .cw-card {
            transition: box-shadow .2s, transform .2s;
            cursor: default;
        }

        .cw-card:hover {
            box-shadow: 0 6px 28px rgba(0, 0, 0, .12);
            transform: translateY(-2px);
        }

        .cw-cat-pill {
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .cw-field-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .cw-field-row:last-child {
            border-bottom: none;
        }

        .cw-field-label {
            color: #8c98a4;
            font-size: .78rem;
            min-width: 110px;
            flex-shrink: 0;
        }

        .cw-field-val {
            font-size: .85rem;
            flex: 1;
            word-break: break-all;
        }

        .pw-mask {
            letter-spacing: .25em;
            color: #aaa;
            user-select: none;
        }

        .copy-btn {
            border: none;
            background: transparent;
            color: #677788;
            padding: 2px 6px;
            border-radius: 4px;
            cursor: pointer;
            font-size: .8rem;
        }

        .copy-btn:hover {
            color: #58da9d;
            background: #e7edf3;
        }

        .reveal-btn {
            border: none;
            background: transparent;
            color: #677788;
            padding: 2px 6px;
            border-radius: 4px;
            cursor: pointer;
            font-size: .8rem;
        }

        .reveal-btn:hover {
            color: #f5a623;
            background: #fdf3e0;
        }

        .expiry-warn {
            color: #fd9139;
            font-weight: 600;
        }

        .expiry-expired {
            color: #de4437;
            font-weight: 600;
        }

        .audit-item {
            font-size: .78rem;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .audit-item:last-child {
            border-bottom: none;
        }

        .audit-action {
            font-weight: 600;
            text-transform: capitalize;
        }

        /* Category tab pills */
        .cat-tab {
            cursor: pointer;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: .82rem;
            border: 1px solid #e7edf3;
            background: #fff;
            transition: all .15s;
        }

        .cat-tab:hover,
        .cat-tab.active {
            background: #58da9d;
            color: #fff;
            border-color: #58da9d;
        }
    </style>

    <main id="content" role="main" class="main">
        <div class="content container-fluid">

            <!-- ── Page Header ──────────────────────────────────────────── -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm">
                        <h1 class="page-header-title"><i class="bi bi-key me-2 text-warning"></i>Credentials Wallet</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-no-gutter">
                                <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Credentials Wallet</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-auto d-flex gap-2 align-items-center">
                        <div class="input-group input-group-sm" style="width:220px">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" id="globalSearch" class="form-control"
                                placeholder="Search platform, label…">
                        </div>
                        <?php if ($canCreate): ?>
                            <button class="btn btn-primary btn-sm" onclick="openAddModal()">
                                <i class="bi bi-plus-lg me-1"></i> Add Credential
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ── Stats Row ──────────────────────────────────────────────── -->
            <div class="row g-3 mb-4" id="statsRow">
                <?php
                $statCards = [
                    ['id' => 'stTotal', 'label' => 'Total Stored', 'icon' => 'bi-safe2', 'color' => 'primary'],
                    ['id' => 'stActive', 'label' => 'Active', 'icon' => 'bi-check-circle', 'color' => 'success'],
                    ['id' => 'stExpiring', 'label' => 'Expiring (30d)', 'icon' => 'bi-clock-history', 'color' => 'warning'],
                    ['id' => 'stExpired', 'label' => 'Expired', 'icon' => 'bi-x-circle', 'color' => 'danger'],
                ];
                foreach ($statCards as $sc): ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="avatar avatar-lg avatar-soft-<?= $sc['color'] ?> avatar-circle">
                                    <span class="avatar-initials"><i class="bi <?= $sc['icon'] ?> fs-5"></i></span>
                                </div>
                                <div>
                                    <div class="fs-2 fw-bold text-<?= $sc['color'] ?>" id="<?= $sc['id'] ?>">—</div>
                                    <small class="text-muted"><?= $sc['label'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ── Category Filter Tabs ────────────────────────────────────── -->
            <div class="d-flex flex-wrap gap-2 mb-4" id="catTabs">
                <span class="cat-tab active" data-cat="" onclick="filterCat('',this)">
                    <i class="bi bi-grid me-1"></i>All
                </span>
                <?php foreach (require_once_categories() as $key => $cfg): ?>
                    <span class="cat-tab" data-cat="<?= $key ?>" onclick="filterCat('<?= $key ?>',this)">
                        <i class="bi <?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
                        <span class="badge bg-soft-<?= $cfg['color'] ?> text-<?= $cfg['color'] ?> ms-1"
                            id="cnt_<?= $key ?>">0</span>
                    </span>
                <?php endforeach; ?>
            </div>

            <!-- ── Cards Grid ──────────────────────────────────────────────── -->
            <div id="walletGrid" class="row g-3">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>

            <!-- ── Empty state ─────────────────────────────────────────────── -->
            <div id="emptyState" class="text-center py-5" style="display:none">
                <i class="bi bi-key fs-1 text-muted"></i>
                <h5 class="text-muted mt-3">No credentials found</h5>
                <p class="text-muted">Add your first credential using the button above.</p>
            </div>

        </div>
        <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
    </main>

    <!-- ════════════════════════════════════════════════════════════
     ADD / EDIT MODAL
════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="credModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="credModalTitle"><i class="bi bi-key me-2"></i>Add Credential</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editId">

                    <div class="row g-3">

                        <!-- ─ Platform & Category ─ -->
                        <div class="col-12">
                            <h6 class="text-muted fw-semibold text-uppercase"
                                style="font-size:.75rem;letter-spacing:.05em;">Platform Details</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select id="fCategory" class="form-select">
                                <?php foreach (require_once_categories() as $key => $cfg): ?>
                                    <option value="<?= $key ?>"><?= $cfg['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Platform / Service <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="fPlatform" class="form-control"
                                placeholder="e.g. Gmail, YouTube, cPanel, Railway">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Account Label <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="fLabel" class="form-control"
                                placeholder="e.g. CEP Photos Gmail, CEP YouTube">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Login URL</label>
                            <input type="url" id="fUrl" class="form-control" placeholder="https://accounts.google.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Purpose / Role</label>
                            <input type="text" id="fPurpose" class="form-control"
                                placeholder="e.g. Storing CEP event photos">
                        </div>

                        <!-- ─ Login Credentials ─ -->
                        <div class="col-12 mt-2">
                            <h6 class="text-muted fw-semibold text-uppercase"
                                style="font-size:.75rem;letter-spacing:.05em;">Login Credentials</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username / Email</label>
                            <input type="text" id="fUsername" class="form-control" placeholder="username or email"
                                autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <input type="password" id="fPassword" class="form-control"
                                    placeholder="Leave blank to keep existing" autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwInput('fPassword',this)" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="generatePassword()"
                                    tabindex="-1" title="Generate strong password">
                                    <i class="bi bi-shuffle"></i>
                                </button>
                            </div>
                            <div id="pwStrength" class="mt-1"></div>
                        </div>

                        <!-- ─ Recovery / 2FA ─ -->
                        <div class="col-12 mt-2">
                            <h6 class="text-muted fw-semibold text-uppercase"
                                style="font-size:.75rem;letter-spacing:.05em;">Recovery &amp; 2FA Details</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Verification / 2FA Phone</label>
                            <input type="tel" id="fVerPhone" class="form-control" placeholder="+250 7XX XXX XXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Verification / Recovery Email</label>
                            <input type="email" id="fVerEmail" class="form-control" placeholder="recovery@example.com">
                        </div>

                        <!-- ─ Account Creator ─ -->
                        <div class="col-12 mt-2">
                            <h6 class="text-muted fw-semibold text-uppercase"
                                style="font-size:.75rem;letter-spacing:.05em;">Account Creator / Owner</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Creator Name</label>
                            <input type="text" id="fCreatorName" class="form-control" placeholder="Full name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Creator Phone</label>
                            <input type="tel" id="fCreatorPhone" class="form-control" placeholder="+250 7XX XXX XXX">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Creator Email</label>
                            <input type="email" id="fCreatorEmail" class="form-control"
                                placeholder="creator@example.com">
                        </div>

                        <!-- ─ Extra ─ -->
                        <div class="col-12 mt-2">
                            <h6 class="text-muted fw-semibold text-uppercase"
                                style="font-size:.75rem;letter-spacing:.05em;">Additional Information</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Expiry Date <span
                                    class="text-muted small">(optional)</span></label>
                            <input type="date" id="fExpiry" class="form-control">
                            <div class="form-text">For subscriptions, domains, API keys with expiry.</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea id="fNotes" class="form-control" rows="2"
                                placeholder="API scopes, plan details, renewal info, who has access…"></textarea>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="btnSaveCred" class="btn btn-primary" onclick="saveCred()">
                        <i class="bi bi-check-lg me-1"></i>Save Credential
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════
     VIEW / DETAIL MODAL
════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" id="viewModalHeader">
                    <h5 class="modal-title" id="viewTitle">Credential Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
                <div class="modal-footer" id="viewFooter"></div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════
     AUDIT LOG MODAL
════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="auditModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Access Audit Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="auditBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════
     DELETE CONFIRM MODAL
════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete Credential</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to permanently delete <strong id="deleteLabel"></strong>?</p>
                    <div class="alert alert-warning py-2"><i class="bi bi-exclamation-triangle me-2"></i>This action
                        cannot be undone. The password will be destroyed.</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="btnConfirmDelete" class="btn btn-danger">Delete Permanently</button>
                </div>
            </div>
        </div>
    </div>

    <?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

    <script>
        (function () {
            'use strict';

            const BASE = '<?= BASE_URL ?>';
            const API = BASE + '/api/wallet';
            const CAN_CREATE = <?= json_encode($canCreate) ?>;
            const CAN_EDIT = <?= json_encode($canEdit) ?>;
            const CAN_DELETE = <?= json_encode($canDelete) ?>;

            const CAT_CONFIG = {
                social_media: { label: 'Social Media', icon: 'bi-share', color: 'primary' },
                email: { label: 'Email', icon: 'bi-envelope', color: 'info' },
                api_key: { label: 'API Key', icon: 'bi-code-slash', color: 'warning' },
                hosting: { label: 'Hosting', icon: 'bi-server', color: 'success' },
                domain: { label: 'Domain', icon: 'bi-globe', color: 'secondary' },
                analytics: { label: 'Analytics', icon: 'bi-bar-chart', color: 'danger' },
                payment: { label: 'Payment', icon: 'bi-credit-card', color: 'success' },
                other: { label: 'Other', icon: 'bi-key', color: 'dark' },
            };

            let allCredentials = [];
            let currentCat = '';
            let searchTerm = '';
            let deletePending = null;

            // ── Escape HTML ──────────────────────────────────────────────
            function esc(s) { return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

            // ── Clipboard ────────────────────────────────────────────────
            function copyText(text, btn) {
                navigator.clipboard.writeText(text).then(() => {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check text-success"></i>';
                    setTimeout(() => btn.innerHTML = orig, 1500);
                });
            }

            // ── Password toggle (input fields) ──────────────────────────
            window.togglePwInput = function (inputId, btn) {
                const inp = document.getElementById(inputId);
                if (!inp) return;
                if (inp.type === 'password') { inp.type = 'text'; btn.innerHTML = '<i class="bi bi-eye-slash"></i>'; }
                else { inp.type = 'password'; btn.innerHTML = '<i class="bi bi-eye"></i>'; }
            };

            // ── Password strength indicator ──────────────────────────────
            document.getElementById('fPassword')?.addEventListener('input', function () {
                const v = this.value;
                const el = document.getElementById('pwStrength');
                if (!v) { el.innerHTML = ''; return; }
                let score = 0;
                if (v.length >= 8) score++;
                if (v.length >= 14) score++;
                if (/[A-Z]/.test(v)) score++;
                if (/[0-9]/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;
                const levels = [
                    { label: 'Weak', color: 'danger', width: '20%' },
                    { label: 'Fair', color: 'warning', width: '40%' },
                    { label: 'Good', color: 'info', width: '60%' },
                    { label: 'Strong', color: 'primary', width: '80%' },
                    { label: 'Excellent', color: 'success', width: '100%' },
                ];
                const l = levels[Math.max(0, score - 1)];
                el.innerHTML = `<div class="progress" style="height:5px"><div class="progress-bar bg-${l.color}" style="width:${l.width}"></div></div>
    <small class="text-${l.color}">${l.label}</small>`;
            });

            // ── Generate random password ─────────────────────────────────
            window.generatePassword = function () {
                const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}';
                let pw = '';
                const arr = new Uint32Array(18);
                crypto.getRandomValues(arr);
                arr.forEach(n => pw += chars[n % chars.length]);
                document.getElementById('fPassword').value = pw;
                document.getElementById('fPassword').type = 'text';
                document.getElementById('fPassword').dispatchEvent(new Event('input'));
            };

            // ── Load stats ───────────────────────────────────────────────
            async function loadStats() {
                const res = await fetch(`${API}?action=stats`, { credentials: 'include' });
                const data = await res.json();
                const tot = data.data?.totals || {};
                const byC = data.data?.by_category || {};
                document.getElementById('stTotal').textContent = tot.total || 0;
                document.getElementById('stActive').textContent = tot.active || 0;
                let expiring = 0, expired = 0;
                Object.values(byC).forEach(c => { expiring += (parseInt(c.expiring_soon) || 0); expired += (parseInt(c.expired) || 0); });
                document.getElementById('stExpiring').textContent = expiring;
                document.getElementById('stExpired').textContent = expired;
                // Update category pill counts
                Object.entries(byC).forEach(([cat, d]) => {
                    const el = document.getElementById('cnt_' + cat); if (el) el.textContent = d.total || 0;
                });
            }

            // ── Load all credentials ─────────────────────────────────────
            async function loadCredentials() {
                document.getElementById('walletGrid').innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
                const res = await fetch(`${API}?action=list`, { credentials: 'include' });
                const data = await res.json();
                allCredentials = data.data || [];
                renderGrid();
            }

            // ── Category filter ──────────────────────────────────────────
            window.filterCat = function (cat, el) {
                currentCat = cat;
                document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
                el.classList.add('active');
                renderGrid();
            };

            // ── Render cards ─────────────────────────────────────────────
            function renderGrid() {
                const term = searchTerm.toLowerCase();
                const list = allCredentials.filter(c => {
                    if (currentCat && c.category !== currentCat) return false;
                    if (term && !`${c.platform} ${c.account_label} ${c.username || ''} ${c.creator_name || ''}`.toLowerCase().includes(term)) return false;
                    return true;
                });

                const grid = document.getElementById('walletGrid');
                const empty = document.getElementById('emptyState');

                if (!list.length) {
                    grid.innerHTML = '';
                    empty.style.display = '';
                    return;
                }
                empty.style.display = 'none';

                const today = new Date(); today.setHours(0, 0, 0, 0);
                const in30 = new Date(today); in30.setDate(in30.getDate() + 30);

                grid.innerHTML = list.map(c => {
                    const cfg = CAT_CONFIG[c.category] || CAT_CONFIG.other;
                    // Expiry badge
                    let expiryHtml = '';
                    if (c.expiry_date) {
                        const exp = new Date(c.expiry_date);
                        if (exp < today) expiryHtml = `<span class="badge bg-danger ms-1" title="Expired"><i class="bi bi-x-circle me-1"></i>Expired ${c.expiry_date}</span>`;
                        else if (exp <= in30) expiryHtml = `<span class="badge bg-warning text-dark ms-1" title="Expiring soon"><i class="bi bi-clock me-1"></i>Expires ${c.expiry_date}</span>`;
                    }
                    // Status
                    const statusBadge = c.is_active == '1' || c.is_active === true
                        ? `<span class="badge bg-soft-success text-success">Active</span>`
                        : `<span class="badge bg-soft-secondary text-secondary">Inactive</span>`;

                    return `<div class="col-sm-6 col-xl-4">
      <div class="card cw-card h-100">
        <div class="card-body">
          <!-- Header -->
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-sm avatar-soft-${cfg.color} avatar-circle">
                <span class="avatar-initials"><i class="bi ${cfg.icon}"></i></span>
              </div>
              <div>
                <div class="fw-bold" style="font-size:.9rem">${esc(c.account_label)}</div>
                <div class="text-muted" style="font-size:.78rem">${esc(c.platform)}</div>
              </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1">
              <span class="cw-cat-pill bg-soft-${cfg.color} text-${cfg.color}">${cfg.label}</span>
              ${statusBadge}
            </div>
          </div>

          <!-- Fields -->
          ${c.username ? `
          <div class="cw-field-row">
            <span class="cw-field-label"><i class="bi bi-person me-1"></i>Username</span>
            <span class="cw-field-val">${esc(c.username)}</span>
            <button class="copy-btn" onclick="copyText('${esc(c.username)}',this)" title="Copy username"><i class="bi bi-clipboard"></i></button>
          </div>` : ''}

          <div class="cw-field-row">
            <span class="cw-field-label"><i class="bi bi-lock me-1"></i>Password</span>
            <span class="cw-field-val pw-mask" id="pw_${c.id}">••••••••••</span>
            <button class="reveal-btn" onclick="revealPw(${c.id},this)" title="Reveal password"><i class="bi bi-eye"></i></button>
            <button class="copy-btn" onclick="copyPw(${c.id},this)" title="Copy password"><i class="bi bi-clipboard"></i></button>
          </div>

          ${c.creator_name ? `
          <div class="cw-field-row">
            <span class="cw-field-label"><i class="bi bi-person-badge me-1"></i>Creator</span>
            <span class="cw-field-val">${esc(c.creator_name)}${c.creator_phone ? `<br><small class="text-muted">${esc(c.creator_phone)}</small>` : ''}</span>
          </div>` : ''}

          ${expiryHtml ? `<div class="mt-2">${expiryHtml}</div>` : ''}

          <!-- Footer actions -->
          <div class="d-flex gap-1 mt-3 flex-wrap">
            <button class="btn btn-xs btn-ghost-primary" onclick="viewCred(${c.id})"><i class="bi bi-eye me-1"></i>Details</button>
            ${CAN_EDIT ? `<button class="btn btn-xs btn-ghost-secondary" onclick="editCred(${c.id})"><i class="bi bi-pencil me-1"></i>Edit</button>` : ''}
            ${CAN_EDIT ? `<button class="btn btn-xs btn-ghost-${c.is_active == '1' ? 'warning' : 'success'}" onclick="toggleCred(${c.id})" title="${c.is_active == '1' ? 'Deactivate' : 'Activate'}"><i class="bi bi-toggle-${c.is_active == '1' ? 'on' : 'off'} me-1"></i>${c.is_active == '1' ? 'Deactivate' : 'Activate'}</button>` : ''}
            ${CAN_DELETE ? `<button class="btn btn-xs btn-ghost-danger" onclick="confirmDelete(${c.id},'${esc(c.account_label)}')"><i class="bi bi-trash me-1"></i>Delete</button>` : ''}
            <button class="btn btn-xs btn-ghost-info ms-auto" onclick="viewAudit(${c.id})" title="Audit log"><i class="bi bi-clock-history"></i></button>
          </div>
        </div>
      </div>
    </div>`;
                }).join('');
            }

            // ── Reveal password (POST → audit logged) ────────────────────
            const pwCache = {};
            window.revealPw = async function (id, btn) {
                const el = document.getElementById('pw_' + id);
                if (!el) return;
                if (el.dataset.revealed) {
                    // Toggle hide
                    el.innerHTML = '••••••••••'; el.classList.add('pw-mask');
                    btn.innerHTML = '<i class="bi bi-eye"></i>'; el.dataset.revealed = '';
                    return;
                }
                if (!pwCache[id]) {
                    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                    const res = await fetch(`${API}?action=reveal`, { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
                    const d = await res.json();
                    if (!d.success) { showToast(d.message || 'Failed', 'danger'); btn.innerHTML = '<i class="bi bi-eye"></i>'; return; }
                    pwCache[id] = d.password;
                }
                el.textContent = pwCache[id];
                el.classList.remove('pw-mask');
                btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
                el.dataset.revealed = '1';
            };

            window.copyPw = async function (id, btn) {
                if (!pwCache[id]) {
                    const res = await fetch(`${API}?action=reveal`, { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
                    const d = await res.json();
                    if (!d.success) { showToast(d.message || 'Failed', 'danger'); return; }
                    pwCache[id] = d.password;
                }
                copyText(pwCache[id], btn);
                showToast('Password copied to clipboard', 'success');
            };

            // ── View details modal ────────────────────────────────────────
            window.viewCred = async function (id) {
                document.getElementById('viewBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
                document.getElementById('viewFooter').innerHTML = '';
                const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                modal.show();

                const res = await fetch(`${API}?action=get&id=${id}`, { credentials: 'include' });
                const data = await res.json();
                if (!data.success) { document.getElementById('viewBody').innerHTML = '<p class="text-danger">Failed to load.</p>'; return; }
                const c = data.data;
                const cfg = CAT_CONFIG[c.category] || CAT_CONFIG.other;

                document.getElementById('viewTitle').innerHTML = `<i class="bi ${cfg.icon} me-2"></i>${esc(c.account_label)}`;
                document.getElementById('viewModalHeader').className = `modal-header bg-soft-${cfg.color}`;

                const today = new Date(); today.setHours(0, 0, 0, 0);
                let expiryDisp = c.expiry_date ? c.expiry_date : '<span class="text-muted">None</span>';
                if (c.expiry_date) {
                    const exp = new Date(c.expiry_date);
                    if (exp < today) expiryDisp = `<span class="expiry-expired"><i class="bi bi-x-circle me-1"></i>${c.expiry_date} (EXPIRED)</span>`;
                    else if (exp <= new Date(today.getTime() + 30 * 86400000)) expiryDisp = `<span class="expiry-warn"><i class="bi bi-clock me-1"></i>${c.expiry_date} (expiring soon)</span>`;
                }

                const field = (label, val, extra = '') => val ? `<div class="cw-field-row">
    <span class="cw-field-label">${label}</span>
    <span class="cw-field-val">${val}</span>${extra}
  </div>`: '';

                const pwField = `<div class="cw-field-row">
    <span class="cw-field-label"><i class="bi bi-lock me-1"></i>Password</span>
    <span class="cw-field-val pw-mask" id="vpw_${c.id}">••••••••••</span>
    <button class="reveal-btn" onclick="revealViewPw(${c.id},'${(c.password || '').replace(/'/g, "\\'")}')" title="Reveal"><i class="bi bi-eye"></i></button>
    <button class="copy-btn" onclick="copyText('${(c.password || '').replace(/'/g, "\\'").replace(/\\/g, '\\\\').replace(/"/g, '\\"')}',this)" title="Copy"><i class="bi bi-clipboard"></i></button>
  </div>`;

                document.getElementById('viewBody').innerHTML = `
    <div class="row g-0">
      <!-- Meta header -->
      <div class="col-12 mb-3 pb-3 border-bottom d-flex align-items-center gap-3">
        <div class="avatar avatar-xl avatar-soft-${cfg.color} avatar-circle">
          <span class="avatar-initials fs-3"><i class="bi ${cfg.icon}"></i></span>
        </div>
        <div>
          <h5 class="mb-1">${esc(c.account_label)}</h5>
          <div class="text-muted">${esc(c.platform)}</div>
          <div class="mt-1">
            <span class="badge bg-soft-${cfg.color} text-${cfg.color}">${cfg.label}</span>
            <span class="badge bg-soft-${c.is_active ? 'success' : 'secondary'} text-${c.is_active ? 'success' : 'secondary'} ms-1">${c.is_active ? 'Active' : 'Inactive'}</span>
          </div>
        </div>
      </div>

      <!-- Left col -->
      <div class="col-md-6 pe-md-3">
        <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.72rem;letter-spacing:.05em;">Login Details</h6>
        ${field('<i class="bi bi-person me-1"></i>Username', c.username ? esc(c.username) : null,
                    c.username ? `<button class="copy-btn" onclick="copyText('${esc(c.username)}',this)"><i class="bi bi-clipboard"></i></button>` : '')}
        ${pwField}
        ${field('<i class="bi bi-link me-1"></i>Login URL',
                        c.account_url ? `<a href="${esc(c.account_url)}" target="_blank" rel="noopener">${esc(c.account_url)}</a>` : null)}
        ${field('<i class="bi bi-info-circle me-1"></i>Purpose', c.purpose ? esc(c.purpose) : null)}
      </div>

      <!-- Right col -->
      <div class="col-md-6 ps-md-3">
        <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.72rem;letter-spacing:.05em;">Recovery &amp; Creator</h6>
        ${field('<i class="bi bi-phone me-1"></i>2FA Phone', c.verification_phone ? esc(c.verification_phone) : null)}
        ${field('<i class="bi bi-envelope me-1"></i>Recovery Email', c.verification_email ? esc(c.verification_email) : null)}
        ${field('<i class="bi bi-person-badge me-1"></i>Created By', c.creator_name ? esc(c.creator_name) : null)}
        ${field('<i class="bi bi-telephone me-1"></i>Creator Phone', c.creator_phone ? esc(c.creator_phone) : null)}
        ${field('<i class="bi bi-at me-1"></i>Creator Email', c.creator_email ? esc(c.creator_email) : null)}
        ${field('<i class="bi bi-calendar-event me-1"></i>Expiry', expiryDisp)}
      </div>

      <!-- Notes -->
      ${c.notes ? `<div class="col-12 mt-3 pt-3 border-top">
        <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.72rem;letter-spacing:.05em;">Notes</h6>
        <p class="text-muted mb-0" style="white-space:pre-line;font-size:.85rem">${esc(c.notes)}</p>
      </div>` : ''}

      <!-- Added / Updated -->
      <div class="col-12 mt-3 pt-3 border-top d-flex gap-4">
        <small class="text-muted"><i class="bi bi-plus-circle me-1"></i>Added by <strong>${esc(c.added_by_name || '—')}</strong> on ${(c.created_at || '').substr(0, 10)}</small>
        ${c.updated_by_name ? `<small class="text-muted"><i class="bi bi-pencil me-1"></i>Updated by <strong>${esc(c.updated_by_name)}</strong> on ${(c.updated_at || '').substr(0, 10)}</small>` : ''}
      </div>
    </div>`;

                document.getElementById('viewFooter').innerHTML = `
    ${CAN_EDIT ? `<button class="btn btn-primary" onclick="editCred(${c.id});bootstrap.Modal.getInstance(document.getElementById('viewModal')).hide()"><i class="bi bi-pencil me-1"></i>Edit</button>` : ''}
    ${CAN_DELETE ? `<button class="btn btn-outline-danger" onclick="confirmDelete(${c.id},'${esc(c.account_label)}');bootstrap.Modal.getInstance(document.getElementById('viewModal')).hide()"><i class="bi bi-trash me-1"></i>Delete</button>` : ''}
    <button class="btn btn-outline-info ms-auto" onclick="viewAudit(${c.id});bootstrap.Modal.getInstance(document.getElementById('viewModal')).hide()"><i class="bi bi-clock-history me-1"></i>Audit Log</button>`;
            };

            window.revealViewPw = function (id, pw) {
                const el = document.getElementById('vpw_' + id); if (!el) return;
                if (el.dataset.revealed) { el.innerHTML = '••••••••••'; el.classList.add('pw-mask'); el.dataset.revealed = ''; return; }
                el.textContent = pw; el.classList.remove('pw-mask'); el.dataset.revealed = '1';
            };

            // ── Audit log modal ──────────────────────────────────────────
            const auditIcons = { viewed: 'bi-eye', copied_password: 'bi-clipboard-check', created: 'bi-plus-circle', updated: 'bi-pencil', deleted: 'bi-trash', toggled_status: 'bi-toggle-on' };
            const auditColors = { viewed: 'secondary', copied_password: 'warning', created: 'success', updated: 'primary', deleted: 'danger', toggled_status: 'info' };

            window.viewAudit = async function (id) {
                document.getElementById('auditBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
                new bootstrap.Modal(document.getElementById('auditModal')).show();
                const res = await fetch(`${API}?action=audit&id=${id}`, { credentials: 'include' });
                const data = await res.json();
                const log = data.data || [];
                document.getElementById('auditBody').innerHTML = log.length
                    ? log.map(a => `<div class="audit-item">
        <span class="badge bg-soft-${auditColors[a.action] || 'secondary'} text-${auditColors[a.action] || 'secondary'}">
          <i class="bi ${auditIcons[a.action] || 'bi-dot'} me-1"></i><span class="audit-action">${a.action.replace('_', ' ')}</span>
        </span>
        <span class="flex-grow-1">${esc(a.user_name || 'Unknown')}</span>
        <span class="text-muted">${(a.created_at || '').replace('T', ' ').substr(0, 16)}</span>
      </div>`).join('')
                    : '<p class="text-muted text-center py-3">No audit records yet.</p>';
            };

            // ── Add credential ────────────────────────────────────────────
            window.openAddModal = function () {
                clearForm();
                document.getElementById('editId').value = '';
                document.getElementById('credModalTitle').innerHTML = '<i class="bi bi-plus-lg me-2"></i>Add Credential';
                new bootstrap.Modal(document.getElementById('credModal')).show();
            };

            // ── Edit credential ───────────────────────────────────────────
            window.editCred = async function (id) {
                clearForm();
                document.getElementById('credModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Credential';
                new bootstrap.Modal(document.getElementById('credModal')).show();

                const res = await fetch(`${API}?action=get&id=${id}`, { credentials: 'include' });
                const data = await res.json();
                if (!data.success) return;
                const c = data.data;

                document.getElementById('editId').value = c.id;
                document.getElementById('fCategory').value = c.category;
                document.getElementById('fPlatform').value = c.platform || '';
                document.getElementById('fLabel').value = c.account_label || '';
                document.getElementById('fUrl').value = c.account_url || '';
                document.getElementById('fPurpose').value = c.purpose || '';
                document.getElementById('fUsername').value = c.username || '';
                // Password left blank — user must re-enter only if changing
                document.getElementById('fVerPhone').value = c.verification_phone || '';
                document.getElementById('fVerEmail').value = c.verification_email || '';
                document.getElementById('fCreatorName').value = c.creator_name || '';
                document.getElementById('fCreatorPhone').value = c.creator_phone || '';
                document.getElementById('fCreatorEmail').value = c.creator_email || '';
                document.getElementById('fExpiry').value = c.expiry_date || '';
                document.getElementById('fNotes').value = c.notes || '';
            };

            // ── Save (create / update) ────────────────────────────────────
            window.saveCred = async function () {
                const btn = document.getElementById('btnSaveCred');
                const id = document.getElementById('editId').value;

                // Validate
                if (!document.getElementById('fCategory').value) { showToast('Category is required', 'warning'); return; }
                if (!document.getElementById('fPlatform').value.trim()) { showToast('Platform is required', 'warning'); return; }
                if (!document.getElementById('fLabel').value.trim()) { showToast('Account label is required', 'warning'); return; }

                const payload = {
                    category: document.getElementById('fCategory').value,
                    platform: document.getElementById('fPlatform').value.trim(),
                    account_label: document.getElementById('fLabel').value.trim(),
                    account_url: document.getElementById('fUrl').value.trim() || null,
                    purpose: document.getElementById('fPurpose').value.trim() || null,
                    username: document.getElementById('fUsername').value.trim() || null,
                    password: document.getElementById('fPassword').value || null,
                    verification_phone: document.getElementById('fVerPhone').value.trim() || null,
                    verification_email: document.getElementById('fVerEmail').value.trim() || null,
                    creator_name: document.getElementById('fCreatorName').value.trim() || null,
                    creator_phone: document.getElementById('fCreatorPhone').value.trim() || null,
                    creator_email: document.getElementById('fCreatorEmail').value.trim() || null,
                    expiry_date: document.getElementById('fExpiry').value || null,
                    notes: document.getElementById('fNotes').value.trim() || null,
                };
                if (id) payload.id = parseInt(id);

                btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

                const action = id ? 'update' : 'create';
                const res = await fetch(`${API}?action=${action}`, { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const data = await res.json();
                btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Credential';

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('credModal'))?.hide();
                    showToast(`Credential ${id ? 'updated' : 'saved'} successfully!`, 'success');
                    delete pwCache[id]; // invalidate cache
                    await loadCredentials();
                    loadStats();
                } else {
                    showToast(data.message || 'Save failed. Please try again.', 'danger');
                }
            };

            // ── Toggle status ─────────────────────────────────────────────
            window.toggleCred = async function (id) {
                const res = await fetch(`${API}?action=toggle`, { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
                const data = await res.json();
                if (data.success) { showToast('Status updated', 'success'); loadCredentials(); loadStats(); }
                else showToast(data.message || 'Failed', 'danger');
            };

            // ── Delete ────────────────────────────────────────────────────
            window.confirmDelete = function (id, label) {
                deletePending = id;
                document.getElementById('deleteLabel').textContent = label;
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            };
            document.getElementById('btnConfirmDelete')?.addEventListener('click', async function () {
                if (!deletePending) return;
                const res = await fetch(`${API}?action=delete`, { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: deletePending }) });
                const data = await res.json();
                bootstrap.Modal.getInstance(document.getElementById('deleteModal'))?.hide();
                if (data.success) { showToast('Credential deleted', 'success'); delete pwCache[deletePending]; loadCredentials(); loadStats(); }
                else showToast(data.message || 'Delete failed', 'danger');
                deletePending = null;
            });

            // ── Clear form ────────────────────────────────────────────────
            function clearForm() {
                ['fCategory', 'fPlatform', 'fLabel', 'fUrl', 'fPurpose', 'fUsername', 'fPassword',
                    'fVerPhone', 'fVerEmail', 'fCreatorName', 'fCreatorPhone', 'fCreatorEmail', 'fExpiry', 'fNotes'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.value = '';
                    });
                document.getElementById('pwStrength').innerHTML = '';
                document.getElementById('fPassword').type = 'password';
            }

            // ── Search ────────────────────────────────────────────────────
            let searchTimer;
            document.getElementById('globalSearch')?.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTerm = this.value;
                searchTimer = setTimeout(renderGrid, 250);
            });

            // ── Boot ─────────────────────────────────────────────────────
            document.addEventListener('DOMContentLoaded', function () {
                loadStats();
                loadCredentials();
            });

        })(); // IIFE
    </script>

    <?php
    /**
     * Helper — called twice (tab bar + modal select).
     * Avoids repeating the array literal.
     */
    function require_once_categories(): array
    {
        return [
            'social_media' => ['label' => 'Social Media', 'icon' => 'bi-share', 'color' => 'primary'],
            'email' => ['label' => 'Email', 'icon' => 'bi-envelope', 'color' => 'info'],
            'api_key' => ['label' => 'API Key', 'icon' => 'bi-code-slash', 'color' => 'warning'],
            'hosting' => ['label' => 'Hosting', 'icon' => 'bi-server', 'color' => 'success'],
            'domain' => ['label' => 'Domain', 'icon' => 'bi-globe', 'color' => 'secondary'],
            'analytics' => ['label' => 'Analytics', 'icon' => 'bi-bar-chart', 'color' => 'danger'],
            'payment' => ['label' => 'Payment', 'icon' => 'bi-credit-card', 'color' => 'success'],
            'other' => ['label' => 'Other', 'icon' => 'bi-key', 'color' => 'dark'],
        ];
    }
    ?>
</body>

</html>