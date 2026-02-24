
<!doctype html>
<html lang="en">

<?php
/**
 * Membership Applications
 * File: modules/Dashboard/views/membership-applications.php
 *
 * ─── PAGE CONTRACT ────────────────────────────────────────────────────────────
 * 1. Set $pageTitle and $requiredPermission
 * 2. Include admin-base.php  →  handles ALL auth/redirect logic
 * 3. Render HTML (layouts + page content)
 * 4. ONE inline <script> block at the bottom — page-specific JS only
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── 1. Page config ────────────────────────────────────────────────────────────
$pageTitle          = 'Membership Applications';
$requiredPermission = 'membership.approve';
$currentPage        = 'membership-applications.php';

// ── 2. Auth guard ─────────────────────────────────────────────────────────────
require_once get_helper('admin-base');

// ── 3. Page-specific data ─────────────────────────────────────────────────────
$isSuperAdmin = !empty($currentUser->is_super_admin);
$userSession  = $currentUser->session_type ?? 'both';
$sessionCtx   = $isSuperAdmin ? ($_GET['session'] ?? 'all') : $userSession;

// Families for assignment (Day session only)
require_once ROOT_PATH . '/modules/Membership/controllers/MembershipController.php';
$mc             = new MembershipController();
$familiesResult = $mc->getFamilies('day');
$families       = $familiesResult['data'] ?? [];

// ─────────────────────────────────────────────────────────────────────────────
// HTML OUTPUT
// ─────────────────────────────────────────────────────────────────────────────
?>
<?php include get_layout('admin-header'); ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">

  <script src="<?= admin_js_url('hs.theme-appearance.js') ?>"></script>
  <script src="<?= admin_vendor_url('hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js') ?>"></script>
  
  <?php include get_layout('admin-lock-screen'); ?>

    <script>
        (function(){
            var el = document.getElementById('sessionLockOverlay');
            if (el) el.dataset.email = <?= json_encode($currentUser->email ?? '') ?>;
        })();
    </script>

<?php include get_layout('admin-navbar'); ?>
<?php include get_layout('admin-sidebar'); ?>

<main id="content" role="main" class="main">
    <div class="content container-fluid">

        <!-- ── Page Header ────────────────────────────────────────────── -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="page-header-title">
                        <i class="bi-person-check me-2"></i>Membership Applications
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a href="<?= url('admin/membership-management') ?>">Membership</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Applications</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-sm-auto d-flex gap-2">
                    <!-- Session switcher (Super Admin only) -->
                    <?php if ($isSuperAdmin): ?>
                        <select class="form-select form-select-sm" id="sessSwitch">
                            <option value="all"   <?= $sessionCtx === 'all'     ? 'selected' : '' ?>>All Sessions</option>
                            <option value="day"   <?= $sessionCtx === 'day'     ? 'selected' : '' ?>>&#9728;&#65039; Day CEP</option>
                            <option value="weekend" <?= $sessionCtx === 'weekend' ? 'selected' : '' ?>>&#127761; Weekend CEP</option>
                        </select>
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary btn-sm" id="exportBtn">
                        <i class="bi-download me-1"></i>Export CSV
                    </button>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Alert container -->
        <div id="pageAlert" class="alert" style="display:none;" role="alert"></div>

        <!-- ── Pipeline Stages ────────────────────────────────────────── -->
        <div class="row mb-4" id="pipeline">
            <?php
            $stages = [
                ['key' => 'pending',   'label' => 'Pending',   'sub' => 'Awaiting review', 'color' => 'warning'],
                ['key' => 'reviewing', 'label' => 'Reviewing', 'sub' => 'In progress',      'color' => 'info'],
                ['key' => 'approved',  'label' => 'Approved',  'sub' => 'This cycle',       'color' => 'success'],
                ['key' => 'rejected',  'label' => 'Rejected',  'sub' => 'Not approved',     'color' => 'danger'],
            ];
            foreach ($stages as $i => $stage): ?>
                <div class="col-sm-6 col-lg-3 mb-3">
                    <div class="card card-hover-shadow h-100 pipeline-card <?= $i === 0 ? 'border-warning' : '' ?>"
                         data-stage="<?= $stage['key'] ?>" onclick="setStage('<?= $stage['key'] ?>', this)" style="cursor:pointer;">
                        <div class="card-body">
                            <h6 class="card-subtitle text-muted"><?= htmlspecialchars($stage['label']) ?></h6>
                            <div class="row align-items-center gx-2 mb-1">
                                <div class="col-8">
                                    <h2 class="card-title text-<?= $stage['color'] ?>" id="cnt-<?= $stage['key'] ?>">
                                        <span class="spinner-border spinner-border-sm text-muted" role="status"></span>
                                    </h2>
                                    <small class="text-muted"><?= htmlspecialchars($stage['sub']) ?></small>
                                </div>
                                <div class="col-4 text-end">
                                    <span class="text-<?= $stage['color'] ?> fs-3">
                                        <i class="bi-<?= ['clock', 'eye', 'check-circle', 'x-circle'][$i] ?>"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- /Pipeline Stages -->

        <!-- ── Bulk Action Bar ────────────────────────────────────────── -->
        <div class="alert alert-primary d-none" id="bulkBar">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <strong id="bulkCount">0 selected</strong>
                <button class="btn btn-sm btn-success"  onclick="bulkAction('approve')"><i class="bi-check-circle me-1"></i>Approve All</button>
                <button class="btn btn-sm btn-warning"  onclick="bulkAction('reviewing')"><i class="bi-eye me-1"></i>Mark for Review</button>
                <button class="btn btn-sm btn-danger"   onclick="bulkAction('reject')"><i class="bi-x-circle me-1"></i>Reject All</button>
                <button class="btn btn-sm btn-outline-secondary ms-auto" onclick="clearSelection()"><i class="bi-x me-1"></i>Clear</button>
            </div>
        </div>
        <!-- /Bulk Action Bar -->

        <!-- ── Filters Card ───────────────────────────────────────────── -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Name, email, phone, faculty...">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <select id="fFaculty" class="form-select form-select-sm">
                            <option value="">All Faculties</option>
                            <option>IT &amp; Computer Science</option>
                            <option>Law</option>
                            <option>Finance</option>
                            <option>Accounting</option>
                            <option>Procurement</option>
                            <option>Education</option>
                            <option>Economics</option>
                            <option>Graduate Studies</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select id="fGender" class="form-select form-select-sm">
                            <option value="">All Genders</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select id="fSort" class="form-select form-select-sm">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name">Name A–Z</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-check form-check-sm pt-1">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">Select All</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Filters -->

        <!-- ── Applications List ──────────────────────────────────────── -->
        <div id="appList">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small" id="pageInfo"></span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="btnPrev"><i class="bi-chevron-left"></i> Prev</button>
                <button class="btn btn-sm btn-outline-secondary" id="btnNext">Next <i class="bi-chevron-right"></i></button>
            </div>
        </div>
        <!-- /Applications List -->

    </div><!-- /content -->

    <?php include get_layout('admin-footer'); ?>
</main>

<!-- ── Reject Modal ─────────────────────────────────────────────────────────── -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi-x-circle me-2 text-danger"></i>Reject Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectTargetId">
                <p class="text-muted small mb-3">Select a quick reason or write a custom message. The applicant will receive this via email.</p>
                <div class="d-flex flex-wrap gap-2 mb-3" id="reasonChips">
                    <button type="button" class="btn btn-sm btn-outline-secondary reason-chip" data-reason="Incomplete or inaccurate application information.">Incomplete info</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary reason-chip" data-reason="Applicant does not meet student membership criteria.">Not a UoK student</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary reason-chip" data-reason="Application submitted for wrong session — please re-apply for the correct session.">Wrong session</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary reason-chip" data-reason="Duplicate application — a record already exists for this email.">Duplicate</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary reason-chip" data-reason="Membership is currently closed for this cycle. Please apply next semester.">Closed cycle</button>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rejection Message <span class="text-danger">*</span></label>
                    <textarea id="rejectReason" class="form-control" rows="4" placeholder="Write the reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" onclick="confirmReject()"><i class="bi-x-circle me-1"></i>Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Profile Detail Modal ─────────────────────────────────────────────────── -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Application Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="profileBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer" id="profileActions"></div>
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
                <input type="hidden" id="familyTargetId">
                <p class="text-muted small mb-3">Assigning a family is optional and can be done later. Families are for Day CEP members only.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Family</label>
                    <select id="familySelect" class="form-select">
                        <option value="">— No family yet —</option>
                        <?php foreach ($families as $f): ?>
                            <option value="<?= $f['id'] ?>">
                                <?= htmlspecialchars($f['family_name']) ?> (<?= $f['member_count'] ?> members)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Skip</button>
                <button class="btn btn-primary" onclick="confirmFamilyAssign()"><i class="bi-check-circle me-1"></i>Assign Family</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Page-specific JS ─────────────────────────────────────────────────────
     Keep this block ONLY for logic that belongs exclusively to this page.
     Shared behaviour (session lock, sidebar toggle, etc.) lives in layouts.
──────────────────────────────────────────────────────────────────────────── -->

<?php include get_layout('admin-scripts'); ?>


<script>
(function () {
    'use strict';

    const BASE_URL    = '<?= BASE_URL ?>';
    const API         = BASE_URL + '/api/membership';
    const IS_SUPER    = <?= $isSuperAdmin ? 'true' : 'false' ?>;
    const USER_SESSION = '<?= $userSession ?>';

    // ── State ─────────────────────────────────────────────────────────────
    let currentStage = 'pending';
    let currentPage  = 1;
    const perPage    = 8;
    let selected     = new Set();
    let apps         = [];

    // ── Avatar helpers ────────────────────────────────────────────────────
    const avatarColors = ['#4f46e5','#10b981','#f97316','#6366f1','#ec4899','#0ea5e9','#8b5cf6','#14b8a6'];

    function avatarColor(name) {
        let h = 0;
        for (let c of name) h = (h * 31 + c.charCodeAt(0)) % avatarColors.length;
        return avatarColors[Math.abs(h) % avatarColors.length];
    }

    function initials(a) {
        return ((a.firstname || '')[0] + (a.lastname || '')[0]).toUpperCase();
    }

    // ── Load Applications from API ────────────────────────────────────────
    async function loadApplications() {
        const sess = document.getElementById('sessSwitch')?.value || USER_SESSION;
        const params = new URLSearchParams({ action: 'applications', session: sess });

        try {
            const res  = await fetch(API + '?' + params, { credentials: 'include' });
            const data = await res.json();
            if (!data.success) { showAlert('danger', data.message || 'Failed to load applications.'); return; }
            apps = data.data || [];
        } catch (e) {
            showAlert('danger', 'Network error loading applications.');
            apps = [];
        }

        updateCounts();
        renderList();
    }

    // ── Pipeline counts ───────────────────────────────────────────────────
    function updateCounts() {
        ['pending','reviewing','approved','rejected'].forEach(function (s) {
            const el = document.getElementById('cnt-' + s);
            if (el) el.textContent = apps.filter(function (a) { return a.status === s; }).length;
        });
    }

    // ── Set active stage ──────────────────────────────────────────────────
    window.setStage = function (stage, el) {
        currentStage = stage;
        currentPage  = 1;
        document.querySelectorAll('.pipeline-card').forEach(function (c) {
            c.classList.remove('border-warning', 'border-info', 'border-success', 'border-danger');
        });
        const colorMap = { pending: 'warning', reviewing: 'info', approved: 'success', rejected: 'danger' };
        if (el) el.classList.add('border-' + (colorMap[stage] || 'primary'));
        renderList();
    };

    // ── Filter & render ───────────────────────────────────────────────────
    function getFiltered() {
        const search  = (document.getElementById('searchInput').value || '').toLowerCase();
        const faculty = document.getElementById('fFaculty').value;
        const gender  = document.getElementById('fGender').value;
        const sort    = document.getElementById('fSort').value;

        let list = apps.filter(function (a) {
            if (a.status !== currentStage) return false;
            if (faculty && a.faculty !== faculty) return false;
            if (gender  && a.gender  !== gender)  return false;
            if (search) {
                const fullName = ((a.firstname || '') + ' ' + (a.lastname || '')).toLowerCase();
                if (!fullName.includes(search) && !(a.email || '').toLowerCase().includes(search) && !(a.phone || '').includes(search)) return false;
            }
            return true;
        });

        if (sort === 'oldest') list.sort(function (a, b) { return new Date(a.applied) - new Date(b.applied); });
        else if (sort === 'name') list.sort(function (a, b) { return (a.firstname + a.lastname).localeCompare(b.firstname + b.lastname); });
        else list.sort(function (a, b) { return new Date(b.applied) - new Date(a.applied); });

        return list;
    }

    function renderList() {
        const list   = getFiltered();
        const total  = list.length;
        const start  = (currentPage - 1) * perPage;
        const page   = list.slice(start, start + perPage);

        document.getElementById('pageInfo').textContent =
            'Showing ' + Math.min(start + 1, total) + '–' + Math.min(start + perPage, total) + ' of ' + total + ' applications';
        document.getElementById('btnPrev').disabled = currentPage <= 1;
        document.getElementById('btnNext').disabled = start + perPage >= total;

        if (!page.length) {
            document.getElementById('appList').innerHTML =
                '<div class="text-center py-5 text-muted"><i class="bi-inbox fs-1 d-block mb-3 opacity-25"></i>' +
                '<h5>No applications in this stage</h5><p class="small">Try a different filter or stage.</p></div>';
            return;
        }

        document.getElementById('appList').innerHTML = page.map(function (a) {
            const color  = avatarColor(a.firstname + ' ' + a.lastname);
            const ini    = initials(a);
            const sess   = a.cep_session === 'day'
                ? '<span class="badge bg-soft-warning text-warning">&#9728;&#65039; Day</span>'
                : '<span class="badge bg-soft-primary text-primary">&#127761; Weekend</span>';
            const sBadge = {
                pending:   'bg-soft-warning text-warning',
                reviewing: 'bg-soft-info text-info',
                approved:  'bg-soft-success text-success',
                rejected:  'bg-soft-danger text-danger'
            }[a.status] || 'bg-secondary text-white';

            const actions = [];
            if (a.status === 'pending' || a.status === 'reviewing') {
                if (a.status === 'pending') {
                    actions.push('<button class="btn btn-xs btn-outline-info" onclick="markReviewing(' + a.id + ')"><i class="bi-eye me-1"></i>Review</button>');
                }
                actions.push('<button class="btn btn-xs btn-outline-success" onclick="approveApp(' + a.id + ')"><i class="bi-check-circle me-1"></i>Approve</button>');
                actions.push('<button class="btn btn-xs btn-outline-danger"  onclick="openReject(' + a.id + ')"><i class="bi-x-circle me-1"></i>Reject</button>');
            }
            if (a.status === 'approved') {
                actions.push('<button class="btn btn-xs btn-outline-info" onclick="assignFamilyOpen(' + a.id + ')"><i class="bi-diagram-3 me-1"></i>Family</button>');
            }

            const talentTags = (a.talents || []).map(function (t) {
                return '<span class="badge bg-soft-primary text-primary me-1">' + escHtml(t) + '</span>';
            }).join('');

            return '<div class="card mb-3 app-card ' + (selected.has(a.id) ? 'border-primary' : '') + '" id="app-' + a.id + '">' +
                '<div class="card-body">' +
                    '<div class="d-flex align-items-center gap-3">' +
                        '<div class="form-check"><input class="form-check-input app-check" type="checkbox" value="' + a.id + '" ' + (selected.has(a.id) ? 'checked' : '') + '></div>' +
                        '<div class="avatar avatar-md avatar-circle flex-shrink-0" style="background:' + color + ';display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">' + ini + '</div>' +
                        '<div class="flex-grow-1 min-width-0">' +
                            '<h5 class="mb-0">' + escHtml(a.firstname) + ' ' + escHtml(a.lastname) + '</h5>' +
                            '<div class="d-flex gap-2 flex-wrap mt-1">' +
                                '<small class="text-muted"><i class="bi-envelope me-1"></i>' + escHtml(a.email) + '</small>' +
                                '<small class="text-muted"><i class="bi-telephone me-1"></i>' + escHtml(a.phone || '—') + '</small>' +
                                '<small class="text-muted"><i class="bi-building me-1"></i>' + escHtml(a.faculty || '—') + '</small>' +
                            '</div>' +
                        '</div>' +
                        '<div class="d-flex align-items-center gap-2 flex-shrink-0">' +
                            sess +
                            '<span class="badge ' + sBadge + '">' + a.status + '</span>' +
                            '<small class="text-muted">Applied: ' + escHtml(a.applied || '') + '</small>' +
                            '<button class="btn btn-xs btn-outline-secondary" onclick="viewProfile(' + a.id + ')"><i class="bi-person me-1"></i>View</button>' +
                            actions.join('') +
                        '</div>' +
                    '</div>' +
                    (talentTags ? '<div class="mt-2 ps-5">' + talentTags + '</div>' : '') +
                    (a.reject_reason ? '<div class="mt-2 ps-5 text-danger small"><i class="bi-exclamation-circle me-1"></i>' + escHtml(a.reject_reason) + '</div>' : '') +
                '</div></div>';
        }).join('');

        // Bind checkboxes
        document.querySelectorAll('.app-check').forEach(function (chk) {
            chk.addEventListener('change', function () {
                const id = parseInt(this.value);
                this.checked ? selected.add(id) : selected.delete(id);
                updateBulkBar();
            });
        });
    }

    // ── Bulk bar ──────────────────────────────────────────────────────────
    function updateBulkBar() {
        const count = selected.size;
        const bar   = document.getElementById('bulkBar');
        document.getElementById('bulkCount').textContent = count + ' selected';
        bar.classList.toggle('d-none', count === 0);
    }

    window.clearSelection = function () {
        selected.clear();
        document.querySelectorAll('.app-check').forEach(function (c) { c.checked = false; });
        document.getElementById('selectAll').checked = false;
        updateBulkBar();
    };

    // ── Approve / Reviewing / Reject ──────────────────────────────────────
    window.approveApp = function (id) {
        apiPost({ action: 'approve', id: id }).then(function (res) {
            if (res.success) { updateAppStatus(id, 'approved'); showAlert('success', 'Application approved!'); }
            else showAlert('danger', res.message);
        });
    };

    window.markReviewing = function (id) {
        apiPost({ action: 'reviewing', id: id }).then(function (res) {
            if (res.success) { updateAppStatus(id, 'reviewing'); showAlert('success', 'Marked for review.'); }
            else showAlert('danger', res.message);
        });
    };

    window.openReject = function (id) {
        document.getElementById('rejectTargetId').value = id;
        document.getElementById('rejectReason').value   = '';
        document.querySelectorAll('.reason-chip').forEach(function (c) { c.classList.remove('btn-primary'); c.classList.add('btn-outline-secondary'); });
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    };

    window.confirmReject = function () {
        const id     = document.getElementById('rejectTargetId').value;
        const reason = document.getElementById('rejectReason').value.trim();
        if (!reason) { alert('Please provide a rejection reason.'); return; }

        apiPost({ action: 'reject', id: id, reason: reason }).then(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            if (res.success) { updateAppStatus(id, 'rejected', reason); showAlert('success', 'Application rejected.'); }
            else showAlert('danger', res.message);
        });
    };

    function updateAppStatus(id, status, reason) {
        const a = apps.find(function (x) { return x.id === parseInt(id); });
        if (a) { a.status = status; if (reason) a.reject_reason = reason; }
        updateCounts();
        renderList();
    }

    // ── Bulk action ───────────────────────────────────────────────────────
    window.bulkAction = function (action) {
        const ids = Array.from(selected);
        if (!ids.length) return;
        if (!confirm(action.charAt(0).toUpperCase() + action.slice(1) + ' ' + ids.length + ' selected application(s)?')) return;

        if (action === 'reject') {
            const reason = prompt('Rejection reason (applied to all):');
            if (!reason) return;
            apiPost({ action: 'bulkReject', ids: ids, reason: reason }).then(function (res) {
                if (res.success) { ids.forEach(function (id) { updateAppStatus(id, 'rejected', reason); }); showAlert('success', ids.length + ' applications rejected.'); }
                else showAlert('danger', res.message);
            });
        } else {
            const status  = action === 'approve' ? 'approved' : 'reviewing';
            const apiAct  = action === 'approve' ? 'bulkApprove' : 'bulkReviewing';
            apiPost({ action: apiAct, ids: ids }).then(function (res) {
                if (res.success) { ids.forEach(function (id) { updateAppStatus(id, status); }); showAlert('success', ids.length + ' applications updated.'); }
                else showAlert('danger', res.message);
            });
        }
        clearSelection();
    };

    // ── View Profile ──────────────────────────────────────────────────────
    window.viewProfile = function (id) {
        const a = apps.find(function (x) { return x.id === parseInt(id); });
        if (!a) return;

        const color   = avatarColor(a.firstname + ' ' + a.lastname);
        const ini     = initials(a);
        const sessName = a.cep_session === 'day' ? '&#9728;&#65039; Day CEP' : '&#127761; Weekend CEP';
        const sBadge  = { pending:'bg-soft-warning text-warning', reviewing:'bg-soft-info text-info', approved:'bg-soft-success text-success', rejected:'bg-soft-danger text-danger' }[a.status] || '';

        document.getElementById('profileBody').innerHTML =
            '<div class="d-flex align-items-center gap-3 p-3 rounded mb-4" style="background:linear-gradient(135deg,#eef2ff,#e0e7ff);">' +
                '<div class="avatar avatar-xl avatar-circle flex-shrink-0" style="background:' + color + ';display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.3rem;">' + ini + '</div>' +
                '<div>' +
                    '<h4 class="mb-1">' + escHtml(a.firstname) + ' ' + escHtml(a.lastname) + '</h4>' +
                    '<div class="d-flex gap-2 flex-wrap">' +
                        '<span class="badge bg-soft-secondary text-secondary">' + sessName + '</span>' +
                        '<span class="badge ' + sBadge + '">' + a.status + '</span>' +
                        '<small class="text-muted">Applied: ' + escHtml(a.applied || '') + '</small>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<h6 class="text-muted text-uppercase small fw-bold mb-3">Contact Information</h6>' +
            '<div class="row g-3 mb-4">' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Email</div><div class="fw-semibold">' + escHtml(a.email) + '</div></div></div>' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Phone</div><div class="fw-semibold">' + escHtml(a.phone || '—') + '</div></div></div>' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Gender</div><div class="fw-semibold text-capitalize">' + escHtml(a.gender || '—') + '</div></div></div>' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Date of Birth</div><div class="fw-semibold">' + escHtml(a.dob || '—') + '</div></div></div>' +
            '</div>' +
            '<h6 class="text-muted text-uppercase small fw-bold mb-3">CEP Information</h6>' +
            '<div class="row g-3 mb-4">' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Session</div><div class="fw-semibold">' + sessName + '</div></div></div>' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Year Joined</div><div class="fw-semibold">' + escHtml(a.year_joined || '—') + '</div></div></div>' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Home Church</div><div class="fw-semibold">' + escHtml(a.church || '—') + '</div></div></div>' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Faculty</div><div class="fw-semibold">' + escHtml(a.faculty || '—') + '</div></div></div>' +
            '</div>' +
            '<h6 class="text-muted text-uppercase small fw-bold mb-3">Spiritual Background</h6>' +
            '<div class="row g-3 mb-4">' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Born Again</div><div class="fw-semibold ' + (a.born_again ? 'text-success' : 'text-muted') + '">' + (a.born_again ? '&#10003; Yes' : '&#215; No') + '</div></div></div>' +
                '<div class="col-6"><div class="p-3 rounded" style="background:#f8fafc;"><div class="text-muted small text-uppercase">Water Baptism</div><div class="fw-semibold ' + (a.baptized ? 'text-success' : 'text-muted') + '">' + (a.baptized ? '&#10003; Yes' : '&#215; No') + '</div></div></div>' +
            '</div>' +
            '<h6 class="text-muted text-uppercase small fw-bold mb-2">Talents &amp; Gifts</h6>' +
            '<div class="d-flex flex-wrap gap-2 mb-3">' +
                ((a.talents || []).map(function (t) { return '<span class="badge bg-soft-primary text-primary">' + escHtml(t) + '</span>'; }).join('') || '<span class="text-muted small">None listed</span>') +
            '</div>' +
            (a.bio ? '<h6 class="text-muted text-uppercase small fw-bold mb-2">Bio / Introduction</h6><div class="p-3 rounded small fst-italic" style="background:#f8fafc;">' + escHtml(a.bio) + '</div>' : '') +
            (a.reject_reason ? '<div class="alert alert-danger mt-3"><strong>Rejection Reason:</strong> ' + escHtml(a.reject_reason) + '</div>' : '');

        const actionsEl = document.getElementById('profileActions');
        if (a.status === 'pending' || a.status === 'reviewing') {
            actionsEl.innerHTML =
                '<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>' +
                (a.status === 'pending' ? '<button class="btn btn-warning" onclick="markReviewing(' + a.id + ');bootstrap.Modal.getInstance(document.getElementById(\'profileModal\')).hide()"><i class="bi-eye me-1"></i>Mark Reviewing</button>' : '') +
                '<button class="btn btn-danger"  onclick="bootstrap.Modal.getInstance(document.getElementById(\'profileModal\')).hide();openReject(' + a.id + ')"><i class="bi-x-circle me-1"></i>Reject</button>' +
                '<button class="btn btn-success" onclick="approveApp(' + a.id + ');bootstrap.Modal.getInstance(document.getElementById(\'profileModal\')).hide()"><i class="bi-check-circle me-1"></i>Approve</button>';
        } else {
            actionsEl.innerHTML = '<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
        }

        new bootstrap.Modal(document.getElementById('profileModal')).show();
    };

    // ── Family assignment ─────────────────────────────────────────────────
    window.assignFamilyOpen = function (id) {
        document.getElementById('familyTargetId').value = id;
        new bootstrap.Modal(document.getElementById('familyModal')).show();
    };

    window.confirmFamilyAssign = function () {
        const id     = document.getElementById('familyTargetId').value;
        const family = document.getElementById('familySelect').value;

        if (!family) { bootstrap.Modal.getInstance(document.getElementById('familyModal')).hide(); return; }

        apiPost({ action: 'assignFamily', id: id, family_id: family }).then(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('familyModal')).hide();
            if (res.success) showAlert('success', 'Family assigned successfully!');
            else showAlert('danger', res.message);
        });
    };

    // ── Pagination ────────────────────────────────────────────────────────
    document.getElementById('btnPrev').addEventListener('click', function () { currentPage = Math.max(1, currentPage - 1); renderList(); });
    document.getElementById('btnNext').addEventListener('click', function () { currentPage++; renderList(); });

    // ── Filters ───────────────────────────────────────────────────────────
    ['searchInput', 'fFaculty', 'fGender', 'fSort'].forEach(function (id) {
        document.getElementById(id).addEventListener(id === 'searchInput' ? 'input' : 'change', function () {
            currentPage = 1;
            renderList();
        });
    });

    document.getElementById('selectAll').addEventListener('change', function () {
        const checked = this.checked;
        document.querySelectorAll('.app-check').forEach(function (c) {
            c.checked = checked;
            const id = parseInt(c.value);
            checked ? selected.add(id) : selected.delete(id);
        });
        updateBulkBar();
    });

    if (document.getElementById('sessSwitch')) {
        document.getElementById('sessSwitch').addEventListener('change', loadApplications);
    }

    // ── Reason chips ──────────────────────────────────────────────────────
    document.querySelectorAll('.reason-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.reason-chip').forEach(function (c) { c.classList.remove('btn-primary'); c.classList.add('btn-outline-secondary'); });
            chip.classList.add('btn-primary'); chip.classList.remove('btn-outline-secondary');
            document.getElementById('rejectReason').value = chip.dataset.reason;
        });
    });

    // ── Export ────────────────────────────────────────────────────────────
    document.getElementById('exportBtn').addEventListener('click', function () {
        const sess   = document.getElementById('sessSwitch')?.value || USER_SESSION;
        const params = new URLSearchParams({ action: 'exportApplications', session: sess, stage: currentStage });
        window.location.href = API + '?' + params.toString();
    });

    // ── API helpers ───────────────────────────────────────────────────────
    async function apiPost(data) {
        try {
            const res  = await fetch(API, {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await res.json();
        } catch (e) {
            return { success: false, message: 'Network error.' };
        }
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

    // ── Init ──────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadApplications();
    });
})();
</script>

</body>
</html>