<?php
/**
 * Membership Management
 * File: modules/Dashboard/views/membership-management.php
 *
 * ─── PAGE CONTRACT ────────────────────────────────────────────────────────────
 * 1. Set $pageTitle and $requiredPermission
 * 2. Include admin-base.php  →  handles ALL auth/redirect logic
 * 3. Render HTML (layouts + page content)
 * 4. ONE inline <script> block at the bottom — page-specific JS only
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── 1. Page config ────────────────────────────────────────────────────────────
$pageTitle          = 'Membership Management';
$requiredPermission = 'membership.view';
$currentPage        = 'membership-management.php';

// ── 2. Auth guard ─────────────────────────────────────────────────────────────
require_once get_helper('admin-base');

// ── 3. Page-specific data fetching ────────────────────────────────────────────
require_once ROOT_PATH . '/modules/Membership/controllers/MembershipController.php';

$isSuperAdmin = !empty($currentUser->is_super_admin);

// Session context — leaders see their own session; super admin sees all
$sessionCtx = $currentUser->session_type ?? null;
if ($isSuperAdmin) {
    $sessionCtx = $_GET['session'] ?? null;
}

$mc = new MembershipController();

// Stats
$statsResult = $mc->getStatistics($sessionCtx);
$stats       = $statsResult['data'] ?? [];

// Families for assignment dropdown
$familiesResult = $mc->getFamilies($sessionCtx);
$families       = $familiesResult['data'] ?? [];

// Active membership types
$typesResult     = $mc->getMembershipTypes();
$membershipTypes = $typesResult['data'] ?? [];

// ─────────────────────────────────────────────────────────────────────────────
// HTML OUTPUT
// ─────────────────────────────────────────────────────────────────────────────
?>
<?php include get_layout('admin-header'); ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">

    <?php include get_layout('admin-lock-screen'); ?>

    <script>
        (function(){
            var el = document.getElementById('sessionLockOverlay');
            if (el) el.dataset.email = <?= json_encode($currentUser->email ?? '') ?>;
        })();
    </script>

    <script src="<?= admin_js_url('hs.theme-appearance.js') ?>"></script>
    <script src="<?= admin_vendor_url('hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js') ?>"></script>

    <?php include  get_layout('admin-navbar'); ?>
    <?php include get_layout('admin-sidebar'); ?>

<main id="content" role="main" class="main">
    <div class="content container-fluid">

        <!-- ── Page Header ────────────────────────────────────────────── -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="bi-person-badge me-2"></i>Membership Management
                        <?php if ($sessionCtx): ?>
                            <span class="badge <?= $sessionCtx === 'day' ? 'bg-warning text-dark' : 'bg-primary' ?> ms-2" style="font-size:14px;">
                                <?= $sessionCtx === 'day' ? '&#9728;&#65039; Day CEP' : '&#127761; Weekend CEP' ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary ms-2" style="font-size:14px;">All Sessions</span>
                        <?php endif; ?>
                    </h1>

                    <!-- Stats badges -->
                    <div class="mt-2 d-flex flex-wrap gap-2">
                        <span class="badge bg-soft-secondary text-secondary fs-6 px-3 py-2">
                            <i class="bi-people me-1"></i> Total: <strong><?= number_format($stats['total'] ?? 0) ?></strong>
                        </span>
                        <span class="badge bg-soft-success text-success fs-6 px-3 py-2">
                            <i class="bi-check-circle me-1"></i> Active: <strong><?= number_format($stats['active'] ?? 0) ?></strong>
                        </span>
                        <span class="badge bg-soft-warning text-warning fs-6 px-3 py-2">
                            <i class="bi-clock me-1"></i> Pending: <strong><?= number_format($stats['pending'] ?? 0) ?></strong>
                        </span>
                        <span class="badge bg-soft-info text-info fs-6 px-3 py-2">
                            <i class="bi-person me-1"></i> Male: <?= number_format($stats['male'] ?? 0) ?> /
                            <i class="bi-person me-1 ms-1"></i>Female: <?= number_format($stats['female'] ?? 0) ?>
                        </span>
                        <?php if (!$sessionCtx && $isSuperAdmin): ?>
                            <span class="badge bg-soft-warning text-dark fs-6 px-3 py-2">&#9728;&#65039; Day: <?= $stats['day_session'] ?? 0 ?></span>
                            <span class="badge bg-soft-primary text-primary fs-6 px-3 py-2">&#127761; Weekend: <?= $stats['weekend_session'] ?? 0 ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-sm-auto d-flex gap-2">
                    <!-- Session switcher (Super Admin only) -->
                    <?php if ($isSuperAdmin): ?>
                        <div class="btn-group">
                            <a href="?session=" class="btn btn-sm <?= !$sessionCtx ? 'btn-secondary' : 'btn-outline-secondary' ?>">All</a>
                            <a href="?session=day" class="btn btn-sm <?= $sessionCtx === 'day' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>">&#9728;&#65039; Day</a>
                            <a href="?session=weekend" class="btn btn-sm <?= $sessionCtx === 'weekend' ? 'btn-primary' : 'btn-outline-primary' ?>">&#127761; Weekend</a>
                        </div>
                    <?php endif; ?>
                    <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.create')): ?>
                        <a href="<?= url('admin/member-add') ?>" class="btn btn-primary btn-sm">
                            <i class="bi-plus me-1"></i>Add Member
                        </a>
                    <?php endif; ?>
                    <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.export')): ?>
                        <button class="btn btn-outline-secondary btn-sm" id="exportBtn">
                            <i class="bi-download me-1"></i>Export
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Alert container -->
        <div id="pageAlert" class="alert" style="display:none;" role="alert"></div>

        <!-- Pending approval banner -->
        <?php if (($stats['pending'] ?? 0) > 0 && ($isSuperAdmin || hasPermission($userPermissions, 'membership.approve'))): ?>
            <div class="alert alert-warning d-flex align-items-center justify-content-between">
                <span><i class="bi-clock-history me-2"></i><strong><?= $stats['pending'] ?></strong> member application(s) pending approval.</span>
                <a href="<?= url('admin/membership-applications') ?><?= $sessionCtx ? '?session=' . $sessionCtx : '' ?>" class="btn btn-warning btn-sm">
                    Review Now
                </a>
            </div>
        <?php endif; ?>

        <!-- ── Filters Card ────────────────────────────────────────────── -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label form-label-sm mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Name, email, phone, number...">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label form-label-sm mb-1">Status</label>
                        <select id="filterStatus" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <?php if ($isSuperAdmin && !$sessionCtx): ?>
                        <div class="col-sm-2">
                            <label class="form-label form-label-sm mb-1">Session</label>
                            <select id="filterSession" class="form-select form-select-sm">
                                <option value="">All Sessions</option>
                                <option value="day">&#9728;&#65039; Day</option>
                                <option value="weekend">&#127761; Weekend</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="col-sm-2">
                        <label class="form-label form-label-sm mb-1">Faculty</label>
                        <select id="filterFaculty" class="form-select form-select-sm">
                            <option value="">All Faculties</option>
                            <option>Information Technology</option>
                            <option>Law</option>
                            <option>Finance</option>
                            <option>Accounting</option>
                            <option>Procurement</option>
                            <option>Education</option>
                            <option>Economics</option>
                            <option>Graduate School</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label form-label-sm mb-1">Family</label>
                        <select id="filterFamily" class="form-select form-select-sm">
                            <option value="">All Families</option>
                            <option value="unassigned">Unassigned</option>
                            <?php foreach ($families as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['family_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-1">
                        <button class="btn btn-outline-secondary btn-sm w-100" id="clearFilters">
                            <i class="bi-x-lg"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Filters Card -->

        <!-- ── Members Table ───────────────────────────────────────────── -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-header-title">Members</h4>
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted small" id="selectedCount">0 selected</span>
                    <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.approve')): ?>
                        <button class="btn btn-success btn-xs d-none" id="bulkApproveBtn" onclick="bulkAction('approve')">
                            <i class="bi-check-all me-1"></i>Approve
                        </button>
                    <?php endif; ?>
                    <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.delete')): ?>
                        <button class="btn btn-danger btn-xs d-none" id="bulkDeleteBtn" onclick="bulkAction('delete')">
                            <i class="bi-trash me-1"></i>Delete
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive position-relative">
                <div id="tableLoader" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-white" style="z-index:10; display:none!important;">
                    <div class="spinner-border text-primary"></div>
                </div>
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" id="membersTable">
                    <thead class="thead-light">
                        <tr>
                            <th class="table-check">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="checkAll"></div>
                            </th>
                            <th>Member</th>
                            <th>Session</th>
                            <th>Faculty / Program</th>
                            <th>Family</th>
                            <th>Type</th>
                            <th>Church</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="membersBody">
                        <tr><td colspan="10" class="text-center py-5">
                            <div class="spinner-border text-primary mb-3 d-block mx-auto"></div>
                            Loading members...
                        </td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small" id="paginationInfo">Showing 0 members</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </nav>
            </div>
        </div>
        <!-- /Members Table -->

    </div><!-- /content -->

    <?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- ── View Member Modal ────────────────────────────────────────────────────── -->
<div class="modal fade" id="viewMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalTitle">Member Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- ── Assign Family Modal ──────────────────────────────────────────────────── -->
<div class="modal fade" id="familyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi-diagram-3 me-2"></i>Assign Spiritual Family</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="familyMemberId">
                <p class="text-muted">Assigning family for: <strong id="familyMemberName"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Family</label>
                    <select id="familySelect" class="form-select">
                        <option value="">No family (remove assignment)</option>
                        <?php foreach ($families as $f): ?>
                            <option value="<?= $f['id'] ?>" data-session="<?= $f['cep_session'] ?>">
                                <?= htmlspecialchars($f['family_name']) ?>
                                <?php if ($f['cep_session'] !== 'both'): ?>
                                    (<?= ucfirst($f['cep_session']) ?> Session)
                                <?php endif; ?>
                                — <?= $f['member_count'] ?> members
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveFamily()">
                    <i class="bi-save me-1"></i>Save Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Reject Member Modal ──────────────────────────────────────────────────── -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi-x-circle me-2 text-danger"></i>Reject Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectMemberId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea id="rejectReason" class="form-control" rows="4" placeholder="Please provide a reason that will be communicated to the applicant..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" onclick="confirmReject()">
                    <i class="bi-x-circle me-1"></i>Reject Application
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Page-specific JS ─────────────────────────────────────────────────────
     Keep this block ONLY for logic that belongs exclusively to this page.
     Shared behaviour (session lock, sidebar toggle, etc.) lives in layouts.
──────────────────────────────────────────────────────────────────────────── -->
<script>
(function () {
    'use strict';

    const BASE_URL    = '<?= BASE_URL ?>';
    const API         = BASE_URL + '/api/membership';
    const SESSION_CTX = '<?= $sessionCtx ?>';
    const CAN_APPROVE = <?= ($isSuperAdmin || hasPermission($userPermissions, 'membership.approve')) ? 'true' : 'false' ?>;
    const CAN_DELETE  = <?= ($isSuperAdmin || hasPermission($userPermissions, 'membership.delete')) ? 'true' : 'false' ?>;
    const CAN_EDIT    = <?= ($isSuperAdmin || hasPermission($userPermissions, 'membership.edit')) ? 'true' : 'false' ?>;

    let currentPage  = 1;
    let totalPages   = 1;
    let selectedIds  = new Set();
    let loadTimer;

    // ── Load Members ─────────────────────────────────────────────────────
    function loadMembers(page) {
        page = page || 1;
        currentPage = page;
        $('#tableLoader').show();

        const params = {
            action:   'list',
            page:     page,
            per_page: 20,
            search:   $('#searchInput').val(),
            status:   $('#filterStatus').val(),
            faculty:  $('#filterFaculty').val(),
            family_id: $('#filterFamily').val(),
        };

        if (SESSION_CTX) {
            params.cep_session = SESSION_CTX;
        } else if ($('#filterSession').length) {
            params.cep_session = $('#filterSession').val();
        }

        $.get(API, params, function (res) {
            $('#tableLoader').hide();
            if (!res.success) { renderEmpty('Failed to load members.'); return; }

            const members = res.data   || [];
            const meta    = res.meta   || {};
            totalPages = meta.total_pages || 1;

            if (!members.length) {
                renderEmpty('No members found matching your filters.');
                $('#paginationInfo').text('No members found');
                $('#pagination').html('');
                return;
            }

            renderMembers(members);
            renderPagination(meta);
        }).fail(function () {
            $('#tableLoader').hide();
            renderEmpty('Server error. Please try again.');
        });
    }

    function renderEmpty(msg) {
        $('#membersBody').html(
            '<tr><td colspan="10" class="text-center py-5">' +
            '<div style="font-size:48px;color:#ddd;margin-bottom:12px;"><i class="bi-inbox"></i></div>' +
            '<p class="text-muted mb-0">' + msg + '</p>' +
            '</td></tr>'
        );
    }

    // ── Render members rows ───────────────────────────────────────────────
    function renderMembers(members) {
        const rows = members.map(function (m) {
            const initials  = ((m.firstname || '')[0] + (m.lastname || '')[0]).toUpperCase();
            const sessLabel = m.cep_session === 'day'
                ? '<span class="badge bg-soft-warning text-warning">&#9728;&#65039; Day</span>'
                : '<span class="badge bg-soft-primary text-primary">&#127761; Weekend</span>';
            const statusClass = { active: 'success', pending: 'warning', inactive: 'secondary', suspended: 'danger' }[m.status] || 'secondary';
            const familyHtml  = m.family_name
                ? '<span style="color:' + escHtml(m.family_color) + '">' + escHtml(m.family_name) + '</span>'
                : '<span class="text-muted">—</span>';

            const actions = [];
            actions.push('<a class="btn btn-xs btn-outline-secondary" onclick="viewMember(' + m.id + ')" title="View"><i class="bi-eye"></i></a>');
            if (CAN_EDIT)    actions.push('<a class="btn btn-xs btn-outline-primary" href="' + BASE_URL + '/admin/member-edit?id=' + m.id + '" title="Edit"><i class="bi-pencil"></i></a>');
            if (CAN_APPROVE && m.status === 'pending') actions.push('<button class="btn btn-xs btn-outline-success" onclick="approveMember(' + m.id + ',\'' + escHtml(m.firstname + ' ' + m.lastname) + '\')" title="Approve"><i class="bi-check-circle"></i></button>');
            if (CAN_APPROVE && m.status === 'pending') actions.push('<button class="btn btn-xs btn-outline-danger" onclick="rejectMember(' + m.id + ')" title="Reject"><i class="bi-x-circle"></i></button>');
            actions.push('<button class="btn btn-xs btn-outline-info" onclick="assignFamily(' + m.id + ',\'' + escHtml(m.firstname + ' ' + m.lastname) + '\',' + (m.family_id || 'null') + ')" title="Assign Family"><i class="bi-diagram-3"></i></button>');
            if (CAN_DELETE)  actions.push('<button class="btn btn-xs btn-outline-danger" onclick="deleteMember(' + m.id + ')" title="Delete"><i class="bi-trash"></i></button>');

            return '<tr>' +
                '<td class="table-check"><div class="form-check"><input class="form-check-input row-check" type="checkbox" value="' + m.id + '"></div></td>' +
                '<td><div class="d-flex align-items-center gap-2">' +
                    '<div class="avatar avatar-sm avatar-soft-primary avatar-circle"><span class="avatar-initials">' + escHtml(initials) + '</span></div>' +
                    '<div><h5 class="mb-0 text-inherit">' + escHtml(m.firstname) + ' ' + escHtml(m.lastname) + '</h5>' +
                    '<small class="text-muted">' + escHtml(m.email) + '</small></div>' +
                '</div></td>' +
                '<td>' + sessLabel + '</td>' +
                '<td><small>' + escHtml(m.faculty || '—') + '<br>' + escHtml(m.program || '') + '</small></td>' +
                '<td>' + familyHtml + '</td>' +
                '<td>' + escHtml(m.membership_type || '—') + '</td>' +
                '<td>' + escHtml(m.church_name || '—') + '</td>' +
                '<td><span class="badge bg-soft-' + statusClass + ' text-' + statusClass + '">' + escHtml(m.status) + '</span></td>' +
                '<td>' + (m.year_joined_cep || '—') + '</td>' +
                '<td class="text-end"><div class="btn-group btn-group-sm">' + actions.join('') + '</div></td>' +
                '</tr>';
        });

        $('#membersBody').html(rows.join(''));
        bindRowChecks();
    }

    // ── Pagination ────────────────────────────────────────────────────────
    function renderPagination(meta) {
        const total   = meta.total       || 0;
        const page    = meta.current_page || 1;
        const perPage = meta.per_page    || 20;
        const from    = Math.min((page - 1) * perPage + 1, total);
        const to      = Math.min(page * perPage, total);

        $('#paginationInfo').text('Showing ' + from + ' – ' + to + ' of ' + total + ' members');

        const pages = [];
        pages.push('<li class="page-item ' + (page <= 1 ? 'disabled' : '') + '"><a class="page-link" href="#" onclick="loadMembers(' + (page - 1) + ');return false;">&laquo;</a></li>');
        for (let i = Math.max(1, page - 2); i <= Math.min(totalPages, page + 2); i++) {
            pages.push('<li class="page-item ' + (i === page ? 'active' : '') + '"><a class="page-link" href="#" onclick="loadMembers(' + i + ');return false;">' + i + '</a></li>');
        }
        pages.push('<li class="page-item ' + (page >= totalPages ? 'disabled' : '') + '"><a class="page-link" href="#" onclick="loadMembers(' + (page + 1) + ');return false;">&raquo;</a></li>');
        $('#pagination').html(pages.join(''));
    }

    // ── Checkbox management ───────────────────────────────────────────────
    function bindRowChecks() {
        $('.row-check').on('change', function () {
            const id = parseInt(this.value);
            this.checked ? selectedIds.add(id) : selectedIds.delete(id);
            updateBulkBar();
        });
    }

    function updateBulkBar() {
        const count = selectedIds.size;
        $('#selectedCount').text(count + ' selected');
        if (count > 0) {
            $('#bulkApproveBtn, #bulkDeleteBtn').removeClass('d-none');
        } else {
            $('#bulkApproveBtn, #bulkDeleteBtn').addClass('d-none');
        }
    }

    // ── View Member ───────────────────────────────────────────────────────
    window.viewMember = function (id) {
        $('#viewModalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        new bootstrap.Modal(document.getElementById('viewMemberModal')).show();

        $.get(API, { action: 'get', id: id }, function (res) {
            if (!res.success) { $('#viewModalBody').html('<p class="text-danger text-center">Failed to load member.</p>'); return; }
            const m = res.data;
            const talents = (m.talents || []).map(function (t) {
                return '<span class="badge bg-soft-primary text-primary me-1">' + escHtml(t) + '</span>';
            }).join('') || '<span class="text-muted">None listed</span>';

            $('#viewModalTitle').text(m.firstname + ' ' + m.lastname);
            $('#viewModalBody').html(
                '<div class="row">' +
                '<div class="col-md-3 text-center">' +
                    (m.photo
                        ? '<img src="' + BASE_URL + '/uploads/' + escHtml(m.photo) + '" class="avatar avatar-xl avatar-circle mb-2">'
                        : '<div class="avatar avatar-xl avatar-soft-primary avatar-circle mx-auto mb-2"><span class="avatar-initials fs-3">' + escHtml((m.firstname[0] + m.lastname[0]).toUpperCase()) + '</span></div>') +
                    (m.membership_number ? '<small class="text-muted d-block mt-1">#' + m.membership_number + '</small>' : '') +
                '</div>' +
                '<div class="col-md-9">' +
                    '<h5 class="fw-bold">' + escHtml(m.firstname) + ' ' + escHtml(m.lastname) + '</h5>' +
                    '<table class="table table-sm table-borderless mb-3">' +
                        '<tr><td class="text-muted" style="width:140px;">Email:</td><td>' + escHtml(m.email) + '</td></tr>' +
                        '<tr><td class="text-muted">Phone:</td><td>' + escHtml(m.phone || '—') + '</td></tr>' +
                        '<tr><td class="text-muted">Gender:</td><td>' + escHtml(m.gender || '—') + '</td></tr>' +
                        '<tr><td class="text-muted">Faculty:</td><td>' + escHtml(m.faculty || '—') + '</td></tr>' +
                        '<tr><td class="text-muted">Program:</td><td>' + escHtml(m.program || '—') + (m.academic_year ? ' (' + escHtml(m.academic_year) + ')' : '') + '</td></tr>' +
                        '<tr><td class="text-muted">Church:</td><td>' + escHtml(m.church_name || '—') + '</td></tr>' +
                        '<tr><td class="text-muted">Family:</td><td>' + (m.family_name ? '<span style="color:' + m.family_color + '">' + escHtml(m.family_name) + '</span>' : '—') + '</td></tr>' +
                        '<tr><td class="text-muted">Joined CEP:</td><td>' + (m.year_joined_cep || '—') + '</td></tr>' +
                        '<tr><td class="text-muted">Born Again:</td><td>' + escHtml(m.is_born_again || '—') + '</td></tr>' +
                        '<tr><td class="text-muted">Baptized:</td><td>' + escHtml(m.is_baptized || '—') + '</td></tr>' +
                        (m.bio ? '<tr><td class="text-muted">Bio:</td><td class="fst-italic">' + escHtml(m.bio) + '</td></tr>' : '') +
                    '</table>' +
                    '<div><strong class="text-muted small d-block mb-1">GIFTS & TALENTS:</strong>' + talents + '</div>' +
                '</div></div>'
            );
        });
    };

    // ── Approve Member ────────────────────────────────────────────────────
    window.approveMember = function (id, name) {
        Swal.fire({
            title: 'Approve Member?',
            html: 'Approve <strong>' + name + '</strong> as a CEP member?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, Approve'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.get(API + '?action=approve&id=' + id, function (res) {
                if (res.success) { showAlert('success', name + ' approved successfully!'); loadMembers(currentPage); }
                else showAlert('danger', res.message);
            });
        });
    };

    // ── Reject Member ─────────────────────────────────────────────────────
    window.rejectMember = function (id) {
        document.getElementById('rejectMemberId').value = id;
        document.getElementById('rejectReason').value   = '';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    };

    window.confirmReject = function () {
        const id     = document.getElementById('rejectMemberId').value;
        const reason = document.getElementById('rejectReason').value.trim();
        if (!reason) { alert('Please provide a rejection reason.'); return; }

        $.ajax({
            url:         API + '?action=reject&id=' + id,
            type:        'POST',
            contentType: 'application/json',
            data:        JSON.stringify({ reason: reason }),
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                if (res.success) { showAlert('success', 'Application rejected.'); loadMembers(currentPage); }
                else showAlert('danger', res.message);
            }
        });
    };

    // ── Assign Family ─────────────────────────────────────────────────────
    window.assignFamily = function (id, name, currentFamily) {
        document.getElementById('familyMemberId').value       = id;
        document.getElementById('familyMemberName').textContent = name;
        document.getElementById('familySelect').value         = currentFamily || '';
        new bootstrap.Modal(document.getElementById('familyModal')).show();
    };

    window.saveFamily = function () {
        const id       = document.getElementById('familyMemberId').value;
        const familyId = document.getElementById('familySelect').value;

        $.ajax({
            url:         API + '?action=assignFamily&id=' + id,
            type:        'POST',
            contentType: 'application/json',
            data:        JSON.stringify({ family_id: familyId || null }),
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('familyModal')).hide();
                if (res.success) { showAlert('success', 'Family assigned!'); loadMembers(currentPage); }
                else showAlert('danger', res.message);
            }
        });
    };

    // ── Delete Member ─────────────────────────────────────────────────────
    window.deleteMember = function (id) {
        Swal.fire({
            title: 'Delete Member?',
            text:  'This action cannot be undone!',
            icon:  'error',
            showCancelButton:    true,
            confirmButtonColor:  '#dc3545',
            confirmButtonText:   'Delete'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url:  API + '?action=delete&id=' + id,
                type: 'DELETE',
                success: function (res) {
                    if (res.success) { showAlert('success', 'Member deleted.'); loadMembers(currentPage); }
                    else showAlert('danger', res.message);
                }
            });
        });
    };

    // ── Bulk Actions ──────────────────────────────────────────────────────
    window.bulkAction = function (action) {
        const ids = Array.from(selectedIds);
        if (!ids.length) return;

        const msg = action === 'approve'
            ? 'Approve ' + ids.length + ' member(s)?'
            : 'Delete ' + ids.length + ' member(s)? This cannot be undone!';

        Swal.fire({
            title:              action === 'approve' ? 'Bulk Approve' : 'Bulk Delete',
            text:               msg,
            icon:               action === 'approve' ? 'question' : 'warning',
            showCancelButton:    true,
            confirmButtonColor:  action === 'approve' ? '#28a745' : '#dc3545',
            confirmButtonText:   action === 'approve' ? 'Yes, Approve All' : 'Yes, Delete All'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            const actionKey = 'bulk' + action.charAt(0).toUpperCase() + action.slice(1);
            $.ajax({
                url:         API + '?action=' + actionKey,
                type:        'POST',
                contentType: 'application/json',
                data:        JSON.stringify({ ids: ids }),
                success: function (res) {
                    if (res.success) {
                        showAlert('success', 'Bulk ' + action + ' completed!');
                        selectedIds.clear();
                        loadMembers(currentPage);
                    } else showAlert('danger', res.message);
                }
            });
        });
    };

    // ── Export ────────────────────────────────────────────────────────────
    function bindExport() {
        $('#exportBtn').on('click', function () {
            const params = new URLSearchParams({
                action:      'export',
                cep_session: SESSION_CTX || '',
                status:      $('#filterStatus').val(),
                search:      $('#searchInput').val(),
            });
            window.location.href = API + '?' + params.toString();
        });
    }

    // ── Filters ───────────────────────────────────────────────────────────
    function bindFilters() {
        $('#searchInput').on('input', function () {
            clearTimeout(loadTimer);
            loadTimer = setTimeout(function () { loadMembers(1); }, 350);
        });
        $('#filterStatus, #filterFaculty, #filterFamily, #filterSession').on('change', function () {
            loadMembers(1);
        });
        $('#checkAll').on('change', function () {
            const checked = this.checked;
            $('.row-check').prop('checked', checked).each(function () {
                const id = parseInt(this.value);
                checked ? selectedIds.add(id) : selectedIds.delete(id);
            });
            updateBulkBar();
        });
        $('#clearFilters').on('click', function () {
            $('#searchInput').val('');
            $('#filterStatus, #filterFaculty, #filterFamily, #filterSession').val('');
            loadMembers(1);
        });
    }

    // ── Utility ───────────────────────────────────────────────────────────
    function showAlert(type, msg) {
        const el = document.getElementById('pageAlert');
        el.className  = 'alert alert-' + type + ' d-flex align-items-center gap-2';
        el.innerHTML  = '<i class="bi-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + '-fill"></i><span>' + msg + '</span>';
        el.style.display = 'flex';
        setTimeout(function () { el.style.display = 'none'; }, 5000);
    }

    function escHtml(str) {
        return String(str === null || str === undefined ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // expose loadMembers for pagination links
    window.loadMembers = loadMembers;

    // ── Init ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        bindFilters();
        bindExport();
        loadMembers(1);
    });
})();
</script>

<?php include get_layout('admin-scripts'); ?>

</body>
</html>