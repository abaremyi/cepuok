<?php
/**
 * Users Management
 * File: modules/Dashboard/views/users-management.php
 *
 * This file demonstrates the STANDARD pattern for ALL admin pages.
 * Compare with admin-dashboard.php — the top 6 lines are identical in every page.
 * That's the whole point: zero boilerplate, one place to change auth logic.
 */

// ── 1. Page config  (change these two lines per page, nothing else) ───────────
$pageTitle          = 'Users Management';
$requiredPermission = 'users.view';

// ── 2. Auth guard  (identical in every admin page) ───────────────────────────
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

// ── 3. Optional: page-level PHP data prep ────────────────────────────────────
// (most data should be loaded via AJAX; only put here what's needed server-side)

?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">

    <?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
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

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="page-header-title">Users</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Users</li>
                        </ol>
                    </nav>
                </div>
                <?php if (hasPermission($userPermissions, 'users.create')): ?>
                <div class="col-auto">
                    <a href="<?= url('admin/users-add-user') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add User
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-header-title">All Users</h4>
                <!-- search / filters would go here -->
            </div>
            <div class="table-responsive">
                <table id="usersTable"
                       class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="7" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /content -->

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

<script>
(function () {
    'use strict';

    const BASE_URL = '<?= BASE_URL ?>';
    const API         = BASE_URL + '/api/users';
    const CAN_EDIT    = <?= json_encode(hasPermission($userPermissions, 'users.edit')) ?>;
    const CAN_DELETE  = <?= json_encode(hasPermission($userPermissions, 'users.delete')) ?>;

    async function loadUsers() {
        const tbody = document.getElementById('usersTableBody');
        try {
            const res  = await fetch(`${API}?action=list`, { credentials: 'include' });
            const data = await res.json();

            if (!data.success || !data.data?.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No users found.</td></tr>';
                return;
            }

            tbody.innerHTML = data.data.map(u => {
                const initials    = ((u.firstname?.[0] ?? '') + (u.lastname?.[0] ?? '')).toUpperCase() || 'U';
                const avatar      = u.photo
                    ? `<img class="avatar-img" src="${BASE_URL}/uploads/${esc(u.photo)}" alt="">`
                    : `<span class="avatar-initials">${esc(initials)}</span>`;
                const statusClass = { active:'success', pending:'warning', inactive:'secondary' }[u.status] ?? 'secondary';
                const actions     = [
                    `<a href="${BASE_URL}/admin/users-view?id=${esc(u.id)}" class="btn btn-xs btn-ghost-secondary" title="View"><i class="bi bi-eye"></i></a>`,
                    CAN_EDIT   ? `<a href="${BASE_URL}/admin/users-management?edit=${esc(u.id)}" class="btn btn-xs btn-ghost-primary" title="Edit"><i class="bi bi-pencil"></i></a>` : '',
                    CAN_DELETE ? `<button class="btn btn-xs btn-ghost-danger" onclick="deleteUser(${esc(u.id)})" title="Delete"><i class="bi bi-trash"></i></button>` : '',
                ].join('');

                return `
                <tr>
                    <td>
                        <a class="d-flex align-items-center" href="${BASE_URL}/admin/users-view?id=${esc(u.id)}">
                            <div class="avatar avatar-sm avatar-soft-primary avatar-circle">${avatar}</div>
                            <div class="ms-3"><h5 class="text-inherit mb-0">${esc(u.firstname ?? '')} ${esc(u.lastname ?? '')}</h5></div>
                        </a>
                    </td>
                    <td>${esc(u.email ?? '—')}</td>
                    <td>${esc(u.phone ?? '—')}</td>
                    <td>${esc(u.role_name ?? '—')}</td>
                    <td><span class="badge bg-soft-${statusClass} text-${statusClass}">${esc(u.status ?? '—')}</span></td>
                    <td>${u.created_at ? new Date(u.created_at).toLocaleDateString() : '—'}</td>
                    <td><div class="d-flex gap-1">${actions}</div></td>
                </tr>`;
            }).join('');
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load users.</td></tr>';
        }
    }

    window.deleteUser = async function (id) {
        if (!confirm('Are you sure you want to delete this user?')) return;
        try {
            const res  = await fetch(`${API}?action=delete`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            });
            const data = await res.json();
            if (data.success) loadUsers();
            else alert(data.message || 'Delete failed.');
        } catch (e) { alert('Network error.'); }
    };

    function esc(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    document.addEventListener('DOMContentLoaded', loadUsers);
})();
</script>

</body>
</html>