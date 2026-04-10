<?php
/**
 * Membership Management
 * File: modules/Dashboard/views/membership-management.php
 *
 */

// ── 1. Page config ────────────────────────────────────────────────────────────
$pageTitle = 'Membership Management';
$requiredPermission = 'membership.view';
$currentPage = 'membership-management.php';

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
$stats = $statsResult['data'] ?? [];

// Families for assignment dropdown
$familiesResult = $mc->getFamilies($sessionCtx);
$families = $familiesResult['data'] ?? [];

// Active membership types
$typesResult = $mc->getMembershipTypes();
$membershipTypes = $typesResult['data'] ?? [];

// ─────────────────────────────────────────────────────────────────────────────
// HTML OUTPUT
// ─────────────────────────────────────────────────────────────────────────────
?>
<?php include get_layout('admin-header'); ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">

    <?php include get_layout('admin-lock-screen'); ?>

    <script>
        (function () {
            var el = document.getElementById('sessionLockOverlay');
            if (el) el.dataset.email = <?= json_encode($currentUser->email ?? '') ?>;
        })();
    </script>

    <!-- <script src="<?= admin_js_url('hs.theme-appearance.js') ?>"></script>
    <script src="<?= admin_vendor_url('hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js') ?>"></script> -->

    <?php include get_layout('admin-navbar'); ?>
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
                                <span
                                    class="badge <?= $sessionCtx === 'day' ? 'bg-warning text-dark' : 'bg-primary' ?> ms-2"
                                    style="font-size:14px;">
                                    <?= $sessionCtx === 'day' ? '&#9728;&#65039; Day CEP' : '&#127761; Weekend CEP' ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-2" style="font-size:14px;">All Sessions</span>
                            <?php endif; ?>
                        </h1>

                        <!-- Stats badges -->
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <span class="badge bg-soft-secondary text-secondary fs-6 px-3 py-2">
                                <i class="bi-people me-1"></i> Total:
                                <strong><?= number_format($stats['total'] ?? 0) ?></strong>
                            </span>
                            <span class="badge bg-soft-success text-success fs-6 px-3 py-2">
                                <i class="bi-check-circle me-1"></i> Active:
                                <strong><?= number_format($stats['active'] ?? 0) ?></strong>
                            </span>
                            <span class="badge bg-soft-warning text-warning fs-6 px-3 py-2">
                                <i class="bi-clock me-1"></i> Pending:
                                <strong><?= number_format($stats['pending'] ?? 0) ?></strong>
                            </span>
                            <span class="badge bg-soft-info text-info fs-6 px-3 py-2">
                                <i class="bi-person me-1"></i> Male: <?= number_format($stats['male'] ?? 0) ?> /
                                <i class="bi-person me-1 ms-1"></i>Female: <?= number_format($stats['female'] ?? 0) ?>
                            </span>
                            <?php if (!$sessionCtx && $isSuperAdmin): ?>
                                <span class="badge bg-soft-warning text-dark fs-6 px-3 py-2">&#9728;&#65039; Day:
                                    <?= $stats['day_session'] ?? 0 ?></span>
                                <span class="badge bg-soft-primary text-primary fs-6 px-3 py-2">&#127761; Weekend:
                                    <?= $stats['weekend_session'] ?? 0 ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-sm-auto d-flex gap-2">
                        <!-- Session switcher (Super Admin only) -->
                        <?php if ($isSuperAdmin): ?>
                            <div class="btn-group">
                                <a href="?session="
                                    class="btn btn-sm <?= !$sessionCtx ? 'btn-secondary' : 'btn-outline-secondary' ?>">All</a>
                                <a href="?session=day"
                                    class="btn btn-sm <?= $sessionCtx === 'day' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>">&#9728;&#65039;
                                    Day</a>
                                <a href="?session=weekend"
                                    class="btn btn-sm <?= $sessionCtx === 'weekend' ? 'btn-primary' : 'btn-outline-primary' ?>">&#127761;
                                    Weekend</a>
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
                    <span><i class="bi-clock-history me-2"></i><strong><?= $stats['pending'] ?></strong> member
                        application(s) pending approval.</span>
                    <a href="<?= url('admin/membership-applications') ?><?= $sessionCtx ? '?session=' . $sessionCtx : '' ?>"
                        class="btn btn-warning btn-sm">
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
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Name, email, phone or membership number…"
                                    autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch"
                                        title="Clear search" style="display:none;">
                                    <i class="bi-x-lg" style="font-size:12px;"></i>
                                </button>
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
                        <div class="col-sm-2">
                            <label class="form-label form-label-sm mb-1">Member Type</label>
                            <select id="filterType" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <?php foreach ($membershipTypes as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['type_name']) ?></option>
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
                            <button class="btn btn-success btn-xs d-none" id="bulkApproveBtn"
                                onclick="bulkAction('approve')">
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
                    <!-- <div id="tableLoader"
                        class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-white"
                        style="z-index:10; display:none!important;">
                        <div class="spinner-border text-primary"></div>
                    </div> -->
                    <table
                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                        id="membersTable">
                        <thead class="thead-light">
                            <tr>
                                <th class="table-check">
                                    <div class="form-check"><input class="form-check-input" type="checkbox"
                                            id="checkAll"></div>
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
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="spinner-border text-primary mb-3 d-block mx-auto"></div>
                                    Loading members...
                                </td>
                            </tr>
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

    <!-- ── View Member Modal (Full Interactive) ─────────────────────────────────── -->
    <div class="modal fade" id="viewMemberModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#377dff15,#28a74515);">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div id="vmHeaderAvatar" style="width:56px;height:56px;flex-shrink:0;"></div>
                        <div class="flex-grow-1">
                            <h5 class="modal-title mb-0 fw-bold" id="viewModalTitle">Member Profile</h5>
                            <small class="text-muted" id="vmSubtitle"></small>
                        </div>
                        <div id="vmStatusBadge"></div>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <!-- Nav tabs -->
                <div class="border-bottom px-4 pt-3" id="vmNavWrapper">
                    <ul class="nav nav-tabs nav-tabs-sm" id="vmTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vmProfile"><i class="bi-person me-1"></i>Profile</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vmAcademic"><i class="bi-mortarboard me-1"></i>Academic</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vmChurch"><i class="bi-heart me-1"></i>Faith & Church</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vmTalents"><i class="bi-stars me-1"></i>Talents</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vmMembership"><i class="bi-card-checklist me-1"></i>Membership</a></li>
                    </ul>
                </div>

                <div class="modal-body" id="viewModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between border-top" id="vmFooter" style="display:none!important;">
                    <div class="d-flex gap-2" id="vmActionBtns"></div>
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Upload Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi-camera me-2"></i>Update Profile Photo</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="photoMemberId">
                    <div class="text-center mb-3" id="photoPreviewWrap">
                        <div id="photoPreview" style="width:100px;height:100px;border-radius:50%;background:#e0e7ff;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:36px;color:#6366f1;overflow:hidden;">
                            <i class="bi-person"></i>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Choose Photo</label>
                        <input type="file" class="form-control form-control-sm" id="photoFile" accept="image/jpeg,image/png,image/gif">
                        <small class="text-muted">JPG, PNG or GIF. Max 2MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary btn-sm" onclick="uploadMemberPhoto()"><i class="bi-upload me-1"></i>Upload</button>
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
                    <button class="btn btn-success" onclick="saveFamily()">
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
                        <label class="form-label fw-semibold">Reason for Rejection <span
                                class="text-danger">*</span></label>
                        <textarea id="rejectReason" class="form-control" rows="4"
                            placeholder="Please provide a reason that will be communicated to the applicant..."></textarea>
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
    
    <!-- ── Page-specific JS ─────────────────────────────────────────────────────  -->
    <?php include get_layout('admin-scripts'); ?>

    <script>
        // Global avatar error handler — called via onerror="imgErr(this)"
        // Replaces a broken img with an initials div using data-ini attribute.
        function imgErr(img) {
            var ini  = img.getAttribute('data-ini') || '??';
            var size = img.getAttribute('data-size') === 'modal' ? '56px' : '32px';
            var fs   = img.getAttribute('data-size') === 'modal' ? '20px' : '13px';
            var d    = document.createElement('div');
            d.className   = 'avatar avatar-sm avatar-soft-primary avatar-circle';
            d.style.cssText = 'width:' + size + ';height:' + size + ';font-size:' + fs + ';display:flex;align-items:center;justify-content:center;border-radius:50%;';
            d.innerHTML   = '<span class="avatar-initials">' + ini + '</span>';
            img.parentNode.replaceChild(d, img);
        }

        (function () {
            'use strict';

            const BASE_URL = '<?= BASE_URL ?>';
            const API = BASE_URL + '/api/membership';
            const SESSION_CTX = '<?= $sessionCtx ?>';
            const CAN_APPROVE = <?= ($isSuperAdmin || hasPermission($userPermissions, 'membership.approve')) ? 'true' : 'false' ?>;
            const CAN_DELETE = <?= ($isSuperAdmin || hasPermission($userPermissions, 'membership.delete')) ? 'true' : 'false' ?>;
            const CAN_EDIT = <?= ($isSuperAdmin || hasPermission($userPermissions, 'membership.edit')) ? 'true' : 'false' ?>;

            let currentPage = 1;
            let totalPages = 1;
            let selectedIds = new Set();
            let loadTimer;

            // Helper: Select one or many
            const $ = (sel) => document.querySelector(sel);
            const $$ = (sel) => document.querySelectorAll(sel);

            // ── Load Members ─────────────────────────────────────────────────────
            async function loadMembers(page) {
                page = page || 1;
                currentPage = page;
            
                const loader = $('#tableLoader');
                if (loader) loader.style.display = 'flex'; // Use flex, not block
            
                try {
                    const params = new URLSearchParams({
                        action: 'list',
                        page: page,
                        per_page: 20,
                        search: $('#searchInput')?.value || '',
                        status: $('#filterStatus')?.value || '',
                        faculty: $('#filterFaculty')?.value || '',
                        family_id: $('#filterFamily')?.value || '',
                        membership_type_id: $('#filterType')?.value || '',
                    });
            
                    if (SESSION_CTX) {
                        params.append('cep_session', SESSION_CTX);
                    } else if ($('#filterSession')) {
                        params.append('cep_session', $('#filterSession').value);
                    }
            
                    const response = await fetch(`${API}?${params.toString()}`, { 
                        credentials: 'include' 
                    });
                    
                    const res = await response.json();
            
                    // Hide loader first
                    if (loader) loader.style.display = 'none';
            
                    if (!res.success) { 
                        renderEmpty('Failed to load members.'); 
                        return; 
                    }
            
                    const members = res.data || [];
                    const meta = res.meta || {};
                    totalPages = meta.total_pages || 1;
            
                    if (!members.length) {
                        renderEmpty('No members found matching your filters.');
                        if ($('#paginationInfo')) $('#paginationInfo').textContent = 'No members found';
                        if ($('#pagination')) $('#pagination').innerHTML = '';
                        return;
                    }
            
                    renderMembers(members);
                    renderPagination(meta);
                } catch (error) {
                    console.error('Error loading members:', error);
                    if (loader) loader.style.display = 'none';
                    renderEmpty('Server error. Please try again.');
                }
            }
                
            function renderEmpty(msg) {
                const body = $('#membersBody');
                if (!body) return;
                body.innerHTML = `
            <tr><td colspan="10" class="text-center py-5">
            <div style="font-size:48px;color:#ddd;margin-bottom:12px;"><i class="bi-inbox"></i></div>
            <p class="text-muted mb-0">${msg}</p>
            </td></tr>`;
            }

            // ── Render members rows ───────────────────────────────────────────────
            function renderMembers(members) {
                const rows = members.map(function (m) {
                    const initials = ((m.firstname || '')[0] + (m.lastname || '')[0]).toUpperCase();
                    const sessLabel = m.cep_session === 'day'
                        ? '<span class="badge bg-soft-warning text-warning">☀️ Day</span>'
                        : '<span class="badge bg-soft-primary text-primary">🌘 Weekend</span>';

                    const statusClass = { active: 'success', pending: 'warning', inactive: 'secondary', suspended: 'danger' }[m.status] || 'secondary';
                    const familyHtml = m.family_name
                        ? `<span style="color:${m.family_color || '#00ff95'}">${escHtml(m.family_name)}</span>`
                        : '<span class="text-muted">—</span>';

                    const actions = [];
                    actions.push(`<button class="btn btn-xs btn-outline-secondary" onclick="viewMember(${m.id})" title="View"><i class="bi-eye"></i></button>`);
                    if (CAN_EDIT) actions.push(`<a class="btn btn-xs btn-outline-primary" href="${BASE_URL}/admin/member-edit?id=${m.id}" title="Edit"><i class="bi-pencil"></i></a>`);
                    if (CAN_APPROVE && m.status === 'pending') {
                        actions.push(`<button class="btn btn-xs btn-outline-success" onclick="approveMember(${m.id},'${escHtml(m.firstname + ' ' + m.lastname)}')" title="Approve"><i class="bi-check-circle"></i></button>`);
                        actions.push(`<button class="btn btn-xs btn-outline-danger" onclick="rejectMember(${m.id})" title="Reject"><i class="bi-x-circle"></i></button>`);
                    }
                    actions.push(`<button class="btn btn-xs btn-outline-info" onclick="assignFamily(${m.id},'${escHtml(m.firstname + ' ' + m.lastname)}',${m.family_id || 'null'})" title="Assign Family"><i class="bi-diagram-3"></i></button>`);
                    if (CAN_DELETE) actions.push(`<button class="btn btn-xs btn-outline-danger" onclick="deleteMember(${m.id})" title="Delete"><i class="bi-trash"></i></button>`);


                    // Build avatar — photo if available, initials div otherwise.
                    // onerror calls global imgErr(this) — no inline string escaping.
                    const _ini = escHtml(initials);
                    const avatarHtml = m.profile_photo
                        ? '<img src="' + BASE_URL + '/uploads/' + escHtml(m.profile_photo) + '"'
                          + ' data-ini="' + _ini + '"'
                          + ' class="avatar avatar-sm avatar-circle"'
                          + ' style="width:32px;height:32px;object-fit:cover;border-radius:50%;"'
                          + ' onerror="imgErr(this)">'
                        : '<div class="avatar avatar-sm avatar-soft-primary avatar-circle">'
                          + '<span class="avatar-initials">' + _ini + '</span></div>';

                    return `<tr>
                <td class="table-check"><div class="form-check"><input class="form-check-input row-check" type="checkbox" value="${m.id}" ${selectedIds.has(m.id) ? 'checked' : ''}></div></td>
                <td><div class="d-flex align-items-center gap-2">
                    ${avatarHtml}
                    <div><h5 class="mb-0 text-inherit">${escHtml(m.firstname)} ${escHtml(m.lastname)}</h5>
                    <small class="text-muted">${escHtml(m.email)}</small></div>
                </div></td>
                <td>${sessLabel}</td>
                <td><small>${escHtml(m.faculty || '—')}<br>${escHtml(m.program || '')}</small></td>
                <td>${familyHtml}</td>
                <td>${escHtml(m.membership_type || '—')}</td>
                <td>${escHtml(m.church_name || '—')}</td>
                <td><span class="badge bg-soft-${statusClass} text-${statusClass}">${escHtml(m.status)}</span></td>
                <td>${m.year_joined_cep || '—'}</td>
                <td class="text-end"><div class="btn-group btn-group-sm">${actions.join('')}</div></td>
            </tr>`;
                });

                const body = $('#membersBody');
                if (body) body.innerHTML = rows.join('');
                bindRowChecks();
            }

            // ── Pagination ────────────────────────────────────────────────────────
            function renderPagination(meta) {
                const total = meta.total || 0;
                const page = meta.page || meta.current_page || 1;
                const perPage = meta.per_page || 20;
                const from = Math.min((page - 1) * perPage + 1, total);
                const to = Math.min(page * perPage, total);

                if ($('#paginationInfo')) $('#paginationInfo').textContent = `Showing ${from} – ${to} of ${total} members`;

                const pages = [];
                pages.push(`<li class="page-item ${page <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadMembers(${page - 1});return false;">&laquo;</a></li>`);
                for (let i = Math.max(1, page - 2); i <= Math.min(totalPages, page + 2); i++) {
                    pages.push(`<li class="page-item ${i === page ? 'active' : ''}"><a class="page-link" href="#" onclick="loadMembers(${i});return false;">${i}</a></li>`);
                }
                pages.push(`<li class="page-item ${page >= totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadMembers(${page + 1});return false;">&raquo;</a></li>`);

                if ($('#pagination')) $('#pagination').innerHTML = pages.join('');
            }

            // ── Checkbox management ───────────────────────────────────────────────
            function bindRowChecks() {
                $$('.row-check').forEach(el => {
                    el.addEventListener('change', function () {
                        const id = parseInt(this.value);
                        this.checked ? selectedIds.add(id) : selectedIds.delete(id);
                        updateBulkBar();
                    });
                });
            }

            function updateBulkBar() {
                const count = selectedIds.size;
                const countLabel = $('#selectedCount');
                if (countLabel) countLabel.textContent = count + ' selected';

                const bulkBtns = $$('#bulkApproveBtn, #bulkDeleteBtn');
                bulkBtns.forEach(btn => {
                    count > 0 ? btn.classList.remove('d-none') : btn.classList.add('d-none');
                });
            }

            // ── View Member ───────────────────────────────────────────────────────
            window.viewMember = async function (id) {
                const modalBody = $('#viewModalBody');
                const vmFooter  = $('#vmFooter');
                const vmHeader  = $('#vmHeaderAvatar');
                const vmSubtitle = $('#vmSubtitle');
                const vmStatus  = $('#vmStatusBadge');
                const vmActions = $('#vmActionBtns');

                // Reset
                modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
                if (vmFooter)  vmFooter.style.display  = 'none';
                if (vmHeader)  vmHeader.innerHTML = '';
                if (vmSubtitle) vmSubtitle.textContent = '';
                if (vmStatus)  vmStatus.innerHTML = '';
                if (vmActions) vmActions.innerHTML = '';

                const modalEl = document.getElementById('viewMemberModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();

                try {
                    const response = await fetch(`${API}?action=get&id=${id}`, { credentials: 'include' });
                    const res = await response.json();
                    if (!res.success) {
                        modalBody.innerHTML = '<p class="text-danger text-center py-4">Failed to load member.</p>';
                        return;
                    }
                    const m = res.data;

                    // ── Build header avatar ─────────────────────────────
                    const initials = ((m.firstname||'')[0]+(m.lastname||'')[0]).toUpperCase();
                    if (vmHeader) {
                        const _hIni = ((m.firstname||'')[0]+(m.lastname||'')[0]).toUpperCase();
                        const _hFallback = '<div style="width:56px;height:56px;border-radius:50%;background:#377dff20;font-size:20px;font-weight:700;color:#377dff;display:flex;align-items:center;justify-content:center;">' + _hIni + '</div>';
                        if (m.profile_photo) {
                            vmHeader.innerHTML = '<img src="' + BASE_URL + '/uploads/' + escHtml(m.profile_photo) + '"'
                                + ' data-ini="' + _hIni + '"'
                                + ' data-size="modal"'
                                + ' style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.15);"'
                                + ' onerror="imgErr(this)">';
                        } else {
                            vmHeader.innerHTML = _hFallback;
                        }
                    }
                    if (vmSubtitle) {
                        vmSubtitle.innerHTML = `<span class="me-2">${escHtml(m.membership_number || 'No # yet')}</span>
                            ${m.cep_session === 'day'
                                ? '<span class="badge bg-soft-warning text-warning">☀️ Day CEP</span>'
                                : '<span class="badge bg-soft-primary text-primary">🌘 Weekend CEP</span>'}`;
                    }

                    // ── Status badge ─────────────────────────────────────
                    const sColors = { active:'success', pending:'warning', inactive:'secondary', suspended:'danger' };
                    const sc = sColors[m.status] || 'secondary';
                    if (vmStatus) vmStatus.innerHTML = `<span class="badge bg-soft-${sc} text-${sc} px-3 py-2 fs-6">${escHtml(m.status?.toUpperCase())}</span>`;

                    // ── Title ─────────────────────────────────────────────
                    document.getElementById('viewModalTitle').textContent = `${m.firstname} ${m.lastname}`;

                    // ── Talents ────────────────────────────────────────────
                    const talents = (m.talents || []).map(t => {
                        const name = typeof t === 'object' ? (t.talent_name || t.name || '') : t;
                        const level = typeof t === 'object' ? t.proficiency_level : '';
                        return `<span class="badge bg-soft-primary text-primary me-1 mb-1 py-2 px-3">
                            <i class="bi-star-fill me-1 opacity-50"></i>${escHtml(name)}${level ? ` <span class="opacity-75 small">(${escHtml(level)})</span>` : ''}
                        </span>`;
                    }).join('') || '<span class="text-muted fst-italic">No talents listed yet.</span>';

                    // ── Helper: info row ─────────────────────────────────────
                    const irow = (label, value, icon='') =>
                        `<div class="col-sm-6 mb-3">
                            <div class="d-flex gap-2 align-items-start">
                                ${icon ? `<i class="bi-${icon} text-muted mt-1" style="width:18px;"></i>` : '<span style="width:18px;"></span>'}
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase" style="font-size:10px;letter-spacing:.5px;">${label}</div>
                                    <div class="fw-medium">${value || '<span class="text-muted">—</span>'}</div>
                                </div>
                            </div>
                        </div>`;

                    // ── Membership type badge ─────────────────────────────────
                    const memTypeColor = { 'Current Member':'success', 'Post CEPian':'primary', 'Honorary':'warning' };
                    const mtName = escHtml(m.membership_type_name || m.membership_type || '—');
                    const mtColor = memTypeColor[m.membership_type_name] || 'secondary';
                    const mtBadge = mtName !== '—' ? `<span class="badge bg-soft-${mtColor} text-${mtColor} px-3 py-2">${mtName}</span>` : '<span class="text-muted">—</span>';

                    // ── Family badge ────────────────────────────────────────
                    const familyBadge = m.family_name
                        ? `<span class="badge py-2 px-3" style="background:${m.family_color||'#6366f1'}22;color:${m.family_color||'#6366f1'};border:1px solid ${m.family_color||'#6366f1'}44;">
                                <i class="bi-people-fill me-1"></i>${escHtml(m.family_name)}</span>`
                        : '<span class="text-muted">Not assigned</span>';

                    // ── Approved by ──────────────────────────────────────────
                    const approvedBy = (m.approved_by_firstname && m.approved_by_lastname)
                        ? escHtml(m.approved_by_firstname + ' ' + m.approved_by_lastname)
                        : '';

                    // ── Date formatting ─────────────────────────────────────
                    const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'}) : '—';

                    // ── Build tab content ──────────────────────────────────────
                    modalBody.innerHTML = `
                    <div class="tab-content px-1" id="vmTabContent">

                        <!-- PROFILE TAB -->
                        <div class="tab-pane fade show active" id="vmProfile" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        ${m.profile_photo
                                            ? `<img src="${BASE_URL}/uploads/${escHtml(m.profile_photo)}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #e0e7ff;box-shadow:0 4px 16px rgba(0,0,0,.1);" id="vmProfileImg" onerror="this.style.display='none';document.getElementById('vmProfileFallback').style.display='flex';">
                                               <div id="vmProfileFallback" style="display:none;width:120px;height:120px;border-radius:50%;background:#377dff20;font-size:42px;font-weight:700;color:#377dff;align-items:center;justify-content:center;border:3px solid #e0e7ff;margin:0 auto;">${initials}</div>`
                                            : `<div style="width:120px;height:120px;border-radius:50%;background:#377dff20;font-size:42px;font-weight:700;color:#377dff;display:flex;align-items:center;justify-content:center;border:3px solid #e0e7ff;margin:0 auto;">${initials}</div>`
                                        }
                                        ${(CAN_EDIT) ? `<button class="btn btn-xs btn-white border shadow-sm position-absolute bottom-0 end-0 rounded-circle p-1" onclick="openPhotoUpload(${m.id})" title="Change photo" style="width:30px;height:30px;"><i class="bi-camera-fill text-primary" style="font-size:13px;"></i></button>` : ''}
                                    </div>
                                    <div class="mt-3">${mtBadge}</div>
                                    <div class="mt-2">${familyBadge}</div>
                                    ${m.bio ? `<p class="text-muted small mt-3 fst-italic">"${escHtml(m.bio)}"</p>` : ''}
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        ${irow('First Name', escHtml(m.firstname), 'person')}
                                        ${irow('Last Name',  escHtml(m.lastname),  'person')}
                                        ${irow('Email',      `<a href="mailto:${escHtml(m.email)}">${escHtml(m.email)}</a>`, 'envelope')}
                                        ${irow('Phone',      m.phone ? `<a href="tel:${escHtml(m.phone)}">${escHtml(m.phone)}</a>` : '', 'telephone')}
                                        ${irow('Gender',     escHtml(m.gender), 'gender-ambiguous')}
                                        ${irow('Date of Birth', fmtDate(m.date_of_birth), 'calendar-date')}
                                        ${irow('Nationality', escHtml(m.nationality), 'globe')}
                                        ${irow('Home Town',  escHtml(m.home_town), 'geo-alt')}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ACADEMIC TAB -->
                        <div class="tab-pane fade" id="vmAcademic" role="tabpanel">
                            <div class="row mt-3">
                                ${irow('Faculty / School', escHtml(m.faculty), 'building')}
                                ${irow('Program / Course', escHtml(m.program), 'journal-bookmark')}
                                ${irow('Academic Year',    escHtml(m.academic_year), 'calendar3')}
                                ${irow('Student ID',       escHtml(m.student_id), 'card-text')}
                                ${irow('Year of Study',    escHtml(m.year_of_study), '123')}
                                ${irow('Graduation Year',  escHtml(m.graduation_year), 'award')}
                            </div>
                        </div>

                        <!-- CHURCH/FAITH TAB -->
                        <div class="tab-pane fade" id="vmChurch" role="tabpanel">
                            <div class="row mt-3">
                                ${irow('Church Name',  escHtml(m.church_name), 'building')}
                                ${irow('Born Again',   m.is_born_again === 'yes' ? '<span class="badge bg-soft-success text-success">Yes</span>' : '<span class="badge bg-soft-secondary text-secondary">No</span>', 'heart')}
                                ${irow('Baptized',     m.is_baptized   === 'yes' ? '<span class="badge bg-soft-success text-success">Yes</span>' : '<span class="badge bg-soft-secondary text-secondary">No</span>', 'droplet')}
                                ${irow('CEP Session',  m.cep_session === 'day' ? '☀️ Day Session' : '🌘 Weekend Session', 'clock')}
                                ${irow('Year Joined CEP', escHtml(m.year_joined_cep), 'calendar-event')}
                            </div>
                        </div>

                        <!-- TALENTS TAB -->
                        <div class="tab-pane fade" id="vmTalents" role="tabpanel">
                            <div class="mt-3">
                                <p class="text-muted small mb-3"><i class="bi-info-circle me-1"></i>Gifts and talents registered for this member.</p>
                                <div class="d-flex flex-wrap gap-1">${talents}</div>
                            </div>
                        </div>

                        <!-- MEMBERSHIP TAB -->
                        <div class="tab-pane fade" id="vmMembership" role="tabpanel">
                            <div class="row mt-3">
                                ${irow('Membership #',    escHtml(m.membership_number), 'hash')}
                                ${irow('Member Type',     mtBadge, 'person-badge')}
                                ${irow('Status',          `<span class="badge bg-soft-${sc} text-${sc}">${escHtml(m.status?.toUpperCase())}</span>`, 'check-circle')}
                                ${irow('Family',          familyBadge, 'people')}
                                ${irow('Year Joined',     escHtml(m.year_joined_cep), 'calendar-plus')}
                                ${irow('Applied',         fmtDate(m.created_at), 'clock-history')}
                                ${approvedBy ? irow('Approved By', approvedBy, 'person-check') : ''}
                                ${m.approved_at ? irow('Approved On', fmtDate(m.approved_at), 'calendar-check') : ''}
                                ${m.rejection_reason ? `<div class="col-12"><div class="alert alert-danger py-2 small"><i class="bi-x-circle me-2"></i><strong>Rejection Reason:</strong> ${escHtml(m.rejection_reason)}</div></div>` : ''}
                            </div>
                        </div>
                    </div>`;

                    // ── Show footer with action buttons ───────────────────────
                    if (vmFooter) {
                        vmFooter.style.removeProperty('display');
                        vmFooter.style.display = 'flex';
                        if (vmActions) {
                            const btns = [];
                            if (CAN_EDIT) {
                                btns.push(`<a href="${BASE_URL}/admin/member-edit?id=${m.id}" class="btn btn-primary btn-sm"><i class="bi-pencil me-1"></i>Edit Member</a>`);
                                btns.push(`<button class="btn btn-outline-secondary btn-sm" onclick="openPhotoUpload(${m.id})"><i class="bi-camera me-1"></i>Update Photo</button>`);
                            }
                            if (CAN_APPROVE && m.status === 'pending') {
                                btns.push(`<button class="btn btn-success btn-sm" onclick="bootstrap.Modal.getInstance(document.getElementById('viewMemberModal')).hide();approveMember(${m.id},'${escHtml(m.firstname+' '+m.lastname)}')"><i class="bi-check-circle me-1"></i>Approve</button>`);
                                btns.push(`<button class="btn btn-danger btn-sm" onclick="bootstrap.Modal.getInstance(document.getElementById('viewMemberModal')).hide();rejectMember(${m.id})"><i class="bi-x-circle me-1"></i>Reject</button>`);
                            }
                            vmActions.innerHTML = btns.join('');
                        }
                    }

                } catch (err) {
                    console.error('Error viewing member:', err);
                    modalBody.innerHTML = '<p class="text-danger text-center py-4">Error fetching data.</p>';
                }
            };

            // ── Photo upload ───────────────────────────────────────────────────────
            window.openPhotoUpload = function(id) {
                document.getElementById('photoMemberId').value = id;
                document.getElementById('photoFile').value = '';
                document.getElementById('photoPreview').innerHTML = '<i class="bi-person"></i>';
                document.getElementById('photoPreview').style.backgroundImage = '';
                const modal = new bootstrap.Modal(document.getElementById('photoModal'));
                modal.show();
            };

            document.getElementById('photoFile')?.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const prev = document.getElementById('photoPreview');
                    prev.innerHTML = '';
                    prev.style.cssText = `width:100px;height:100px;border-radius:50%;background:center/cover no-repeat url('${e.target.result}');margin:0 auto;overflow:hidden;`;
                };
                reader.readAsDataURL(file);
            });

            window.uploadMemberPhoto = async function() {
                const id   = document.getElementById('photoMemberId').value;
                const file = document.getElementById('photoFile').files[0];
                if (!file) { Swal.fire('No file', 'Please choose a photo first.', 'warning'); return; }

                const fd = new FormData();
                fd.append('photo', file);

                Swal.fire({ title: 'Uploading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                try {
                    const res = await (await fetch(`${API}?action=updatePhoto&id=${id}`, {
                        method: 'POST',
                        credentials: 'include',
                        body: fd
                    })).json();

                    Swal.close();
                    bootstrap.Modal.getInstance(document.getElementById('photoModal'))?.hide();

                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Photo Updated!', text: 'Profile photo has been updated.', timer: 2000, showConfirmButton: false });
                        loadMembers(currentPage);
                    } else {
                        Swal.fire('Error', res.message || 'Upload failed.', 'error');
                    }
                } catch(e) {
                    Swal.close();
                    Swal.fire('Error', 'Network error during upload.', 'error');
                }
            };
                
            // ── Member Actions (Assign Family)
            window.assignFamily = function(id, name, currentFamilyId) {
                document.getElementById('familyMemberId').value = id;
                document.getElementById('familyMemberName').textContent = name;
                
                // Pre-select current family if any
                const select = document.getElementById('familySelect');
                if (currentFamilyId) {
                    select.value = currentFamilyId;
                } else {
                    select.value = '';
                }
                
                const modal = new bootstrap.Modal(document.getElementById('familyModal'));
                modal.show();
            };
            
            window.saveFamily = async function() {
                const id = document.getElementById('familyMemberId').value;
                const familyId = document.getElementById('familySelect').value;
                
                if (!id) {
                    showAlert('danger', 'Member ID is missing');
                    return;
                }
                
                // Show loading state
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                try {
                    const response = await fetch(`${API}?action=assignFamily&id=${id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify({ family_id: familyId || null })
                    });
                    
                    const res = await response.json();
                    
                    bootstrap.Modal.getInstance(document.getElementById('familyModal')).hide();
                    Swal.close();
                    
                    if (res.success) { 
                        showAlert('success', 'Family assigned successfully!'); 
                        loadMembers(currentPage); 
                    } else {
                        showAlert('danger', res.message || 'Failed to assign family');
                    }
                } catch (error) {
                    Swal.close();
                    showAlert('danger', 'Network error. Please try again.');
                }
            };

            // ── Member Actions (Approve, Reject, Delete, etc.) ──────────────────────
            window.approveMember = function (id, name) {
                Swal.fire({
                    title: 'Approve Member?',
                    html: `Approve <strong>${name}</strong> as a CEP member?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, Approve'
                }).then(async (r) => {
                    if (!r.isConfirmed) return;
                    const res = await (await fetch(`${API}?action=approve&id=${id}`, { credentials: 'include' })).json();
                    if (res.success) { showAlert('success', name + ' approved successfully!'); loadMembers(currentPage); }
                    else showAlert('danger', res.message);
                });
            };

            // Opens the reject modal for a pending member
            window.rejectMember = function (id) {
                document.getElementById('rejectMemberId').value = id;
                document.getElementById('rejectReason').value = '';
                const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
                modal.show();
            };

            window.confirmReject = async function () {
                const id = document.getElementById('rejectMemberId').value;
                const reason = document.getElementById('rejectReason').value.trim();
                if (!reason) { alert('Please provide a rejection reason.'); return; }

                const res = await (await fetch(`${API}?action=reject&id=${id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ reason: reason })
                })).json();

                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                if (res.success) { showAlert('success', 'Application rejected.'); loadMembers(currentPage); }
                else showAlert('danger', res.message);
            };

            window.saveFamily = async function () {
                const id = document.getElementById('familyMemberId').value;
                const familyId = document.getElementById('familySelect').value;

                const res = await (await fetch(`${API}?action=assignFamily&id=${id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ family_id: familyId || null })
                })).json();

                bootstrap.Modal.getInstance(document.getElementById('familyModal')).hide();
                if (res.success) { showAlert('success', 'Family assigned!'); loadMembers(currentPage); }
                else showAlert('danger', res.message);
            };

            window.deleteMember = function (id) {
                Swal.fire({
                    title: 'Delete Member?',
                    text: 'This action cannot be undone!',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Delete'
                }).then(async (r) => {
                    if (!r.isConfirmed) return;
                    const res = await (await fetch(`${API}?action=delete&id=${id}`, { method: 'DELETE', credentials: 'include' })).json();
                    if (res.success) { showAlert('success', 'Member deleted.'); loadMembers(currentPage); }
                    else showAlert('danger', res.message);
                });
            };

            window.bulkAction = function (action) {
                const ids = Array.from(selectedIds);
                if (!ids.length) return;

                Swal.fire({
                    title: action === 'approve' ? 'Bulk Approve' : 'Bulk Delete',
                    text: `${action === 'approve' ? 'Approve' : 'Delete'} ${ids.length} member(s)?`,
                    icon: action === 'approve' ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
                    confirmButtonText: 'Confirm'
                }).then(async (r) => {
                    if (!r.isConfirmed) return;
                    const actionKey = 'bulk' + action.charAt(0).toUpperCase() + action.slice(1);
                    const res = await (await fetch(`${API}?action=${actionKey}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify({ ids: ids })
                    })).json();

                    if (res.success) {
                        showAlert('success', `Bulk ${action} completed!`);
                        selectedIds.clear();
                        loadMembers(currentPage);
                        updateBulkBar();
                    } else showAlert('danger', res.message);
                });
            };

            // ── Filters & Export ──────────────────────────────────────────────────
            function bindFilters() {
                const searchEl   = $('#searchInput');
                const clearSearch = $('#clearSearch');

                // Show/hide clear button; debounce search
                searchEl?.addEventListener('input', function () {
                    clearSearch.style.display = this.value ? '' : 'none';
                    clearTimeout(loadTimer);
                    loadTimer = setTimeout(() => { loadMembers(1); }, 350);
                });

                // Trigger immediately on Enter
                searchEl?.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        clearTimeout(loadTimer);
                        loadMembers(1);
                    }
                });

                // Clear button
                clearSearch?.addEventListener('click', function () {
                    searchEl.value = '';
                    this.style.display = 'none';
                    searchEl.focus();
                    loadMembers(1);
                });

                $$('#filterStatus, #filterFaculty, #filterFamily, #filterSession, #filterType').forEach(el => {
                    el.addEventListener('change', () => loadMembers(1));
                });

                $('#checkAll')?.addEventListener('change', function () {
                    const checked = this.checked;
                    $$('.row-check').forEach(box => {
                        box.checked = checked;
                        const id = parseInt(box.value);
                        checked ? selectedIds.add(id) : selectedIds.delete(id);
                    });
                    updateBulkBar();
                });

                $('#clearFilters')?.addEventListener('click', () => {
                    $('#searchInput').value = '';
                    const cs = $('#clearSearch'); if(cs) cs.style.display = 'none';
                    $$('#filterStatus, #filterFaculty, #filterFamily, #filterSession, #filterType').forEach(el => el.value = '');
                    loadMembers(1);
                });

                $('#exportBtn')?.addEventListener('click', function () {
                    const params = new URLSearchParams({
                        action: 'export',
                        cep_session: SESSION_CTX || '',
                        status: $('#filterStatus')?.value || '',
                        search: $('#searchInput')?.value || '',
                    });
                    window.location.href = `${API}?${params.toString()}`;
                });
            }

            // ── Utility ───────────────────────────────────────────────────────────
            function showAlert(type, msg) {
                const el = document.getElementById('pageAlert');
                if (!el) return;
                el.className = `alert alert-${type} d-flex align-items-center gap-2`;
                el.innerHTML = `<i class="bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill"></i><span>${msg}</span>`;
                el.style.display = 'flex';
                setTimeout(() => { el.style.display = 'none'; }, 5000);
            }

            function escHtml(str) {
                return String(str === null || str === undefined ? '' : str)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            window.loadMembers = loadMembers;

            document.addEventListener('DOMContentLoaded', function () {
                bindFilters();
                loadMembers(1);
            });
        })();
    </script>
</body>

</html>