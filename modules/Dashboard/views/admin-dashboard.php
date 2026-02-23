<?php
/**
 * Admin Dashboard
 * File: modules/Dashboard/views/admin-dashboard.php
 *
 * ─── PAGE CONTRACT ────────────────────────────────────────────────────────────
 * 1. Set $pageTitle and $requiredPermission
 * 2. Include admin-base.php  →  handles ALL auth/redirect logic
 * 3. Render HTML (layouts + page content)
 * 4. ONE inline <script> block at the bottom — page-specific JS only
 *
 * Everything else (session timeout, lock screen, sidebar active state, user
 * variables) is provided automatically by the base/layout files.
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── 1. Page config ────────────────────────────────────────────────────────────
$pageTitle          = 'Dashboard';
$requiredPermission = 'dashboard.view';   // string or array or '' for any user

// ── 2. Auth guard (sets $currentUser, $userPermissions, $userFullName …) ──────
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

// ── 3. Page-specific data fetching (keep it lightweight here) ─────────────────
// Heavy data is loaded client-side via AJAX in the script block below.
// Only include PHP-side data that is needed before the HTML is sent.

// ─────────────────────────────────────────────────────────────────────────────
// HTML OUTPUT
// ─────────────────────────────────────────────────────────────────────────────
?>
<?php include get_layout('admin-header'); ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">

<?php
/**
 * Include the lock screen overlay once per page.
 * It stores the user's email in a data attribute so session-lock.js
 * can re-authenticate without a full page reload.
 */
?>
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>

    <?php /* Patch the overlay with the user email for the unlock flow */ ?>
    <script>
        (function(){
            var el = document.getElementById('sessionLockOverlay');
            if (el) el.dataset.email = <?= json_encode($currentUser->email ?? '') ?>;
        })();
    </script>

  <script src="<?= admin_js_url('hs.theme-appearance.js') ?>"></script>
  <script src="<?= admin_vendor_url('hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js') ?>"></script>

<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
    <div class="content container-fluid">

        <!-- ── Page Header ────────────────────────────────────────────── -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="page-header-title">Dashboard</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item active" aria-current="page">Overview</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- ── Stats Row ──────────────────────────────────────────────── -->
        <div class="row" id="statsRow">
            <?php
            $stats = [
                ['id' => 'totalUsers',    'label' => 'Total Users',     'icon' => 'bi-people',          'color' => 'primary'],
                ['id' => 'totalMembers',  'label' => 'Total Members',   'icon' => 'bi-person-check',    'color' => 'success'],
                ['id' => 'pendingMembers','label' => 'Pending Members', 'icon' => 'bi-person-exclamation','color'=> 'warning'],
                ['id' => 'todayVisitors', 'label' => "Today's Visitors",'icon' => 'bi-eye',             'color' => 'info'],
            ];
            foreach ($stats as $s): ?>
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle"><?= htmlspecialchars($s['label']) ?></h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-inherit" id="<?= $s['id'] ?>">
                                    <span class="spinner-border spinner-border-sm text-muted" role="status"></span>
                                </h2>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-<?= $s['color'] ?> fs-3"><i class="bi <?= $s['icon'] ?>"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- /Stats Row -->

        <!-- ── Recent Users ───────────────────────────────────────────── -->
        <div class="card mb-3 mb-lg-5">
            <div class="card-header">
                <h4 class="card-header-title">Recent Users</h4>
                <a href="<?= url('admin/users-management') ?>" class="btn btn-sm btn-ghost-secondary ms-auto">
                    View all <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody id="recentUsersTable">
                        <tr><td colspan="5" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /Recent Users -->

    </div><!-- /content -->

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>


<!-- ── Page-specific JS ────────────────────────────────────────────────────
     Keep this block ONLY for logic that belongs exclusively to this page.
     Shared behaviour (session lock, sidebar toggle, etc.) lives in layouts.
─────────────────────────────────────────────────────────────────────────── -->
<script>
(function () {
    'use strict';

    
    const BASE_URL = '<?= BASE_URL ?>';
    const API = BASE_URL + '/api/dashboard';

    // ── Stats ────────────────────────────────────────────────────────────
    async function loadStats() {
        try {
            const res  = await fetch(`${API}?action=getStats`, { credentials: 'include' });
            const data = await res.json();
            if (!data.success) return;

            const map = {
                totalUsers:     data.data.total_users     ?? 0,
                totalMembers:   data.data.total_members   ?? 0,
                pendingMembers: data.data.pending_members ?? 0,
                todayVisitors:  data.data.today_visitors  ?? 0,
            };
            Object.entries(map).forEach(([id, val]) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val.toLocaleString();
            });
        } catch (e) {
            ['totalUsers','totalMembers','pendingMembers','todayVisitors'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '<span class="text-danger">—</span>';
            });
        }
    }

    // ── Recent users ─────────────────────────────────────────────────────
    async function loadRecentUsers() {
        const tbody = document.getElementById('recentUsersTable');
        try {
            const res  = await fetch(`${API}?action=getRecentUsers`, { credentials: 'include' });
            const data = await res.json();

            if (!data.success || !data.data?.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No users found</td></tr>';
                return;
            }

            tbody.innerHTML = data.data.map(user => {
                const initials = ((user.firstname?.[0] ?? '') + (user.lastname?.[0] ?? '')).toUpperCase() || 'U';
                const avatar   = user.photo
                    ? `<img class="avatar-img" src="${BASE_URL}/uploads/${escHtml(user.photo)}" alt="${escHtml(user.firstname)}">`
                    : `<span class="avatar-initials">${escHtml(initials)}</span>`;
                const statusClass = { active:'success', pending:'warning' }[user.status] ?? 'secondary';
                const joined = user.created_at
                    ? new Date(user.created_at).toLocaleDateString()
                    : 'N/A';

                return `
                <tr>
                    <td>
                        <a class="d-flex align-items-center" href="${BASE_URL}/admin/users-view?id=${escHtml(user.id)}">
                            <div class="avatar avatar-sm avatar-soft-primary avatar-circle flex-shrink-0">${avatar}</div>
                            <div class="ms-3">
                                <h5 class="text-inherit mb-0">${escHtml(user.firstname ?? '')} ${escHtml(user.lastname ?? '')}</h5>
                            </div>
                        </a>
                    </td>
                    <td>${escHtml(user.email ?? 'N/A')}</td>
                    <td>${escHtml(user.role_name ?? 'N/A')}</td>
                    <td><span class="badge bg-soft-${statusClass} text-${statusClass}">${escHtml(user.status ?? 'unknown')}</span></td>
                    <td>${escHtml(joined)}</td>
                </tr>`;
            }).join('');
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load users.</td></tr>';
        }
    }

    // ── Utility ──────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Init ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadStats();
        loadRecentUsers();
    });
})();
</script>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

</body>
</html>