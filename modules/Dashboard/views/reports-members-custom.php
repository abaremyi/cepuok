<?php
/**
 * Custom Members Report Builder
 * File: modules/Dashboard/views/reports-members-custom.php
 */

$pageTitle          = 'Custom Members Report';
$requiredPermission = 'membership.view';
$currentPage        = 'reports-members-custom.php';

require_once get_helper('admin-base');
require_once ROOT_PATH . '/modules/Membership/controllers/MembershipController.php';

$mc              = new MembershipController();
$isSuperAdmin    = !empty($currentUser->is_super_admin);
$sessionCtx      = $isSuperAdmin ? null : ($currentUser->session_type ?? null);
$families        = $mc->getFamilies(null)['data']    ?? [];
$membershipTypes = $mc->getMembershipTypes()['data'] ?? [];

$ALL_COLUMNS = [
    'membership_number' => 'Membership #',
    'firstname'         => 'First Name',
    'lastname'          => 'Last Name',
    'email'             => 'Email',
    'phone'             => 'Phone',
    'gender'            => 'Gender',
    'date_of_birth'     => 'Date of Birth',
    'cep_session'       => 'Session',
    'year_joined_cep'   => 'Year Joined',
    'faculty'           => 'Faculty',
    'program'           => 'Program',
    'church_name'       => 'Church',
    'family_name'       => 'Family',
    'membership_type'   => 'Member Type',
    'status'            => 'Status',
    'created_at'        => 'Registered',
    'is_born_again'     => 'Born Again',
    'is_baptized'       => 'Baptized',
];

$DEFAULT_COLS = ['membership_number','firstname','lastname','email','cep_session','faculty','membership_type','status','year_joined_cep'];
?>
<?php include get_layout('admin-header'); ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include get_layout('admin-lock-screen'); ?>
<script>(function(){ var el=document.getElementById('sessionLockOverlay'); if(el) el.dataset.email=<?= json_encode($currentUser->email??'') ?>; })();</script>
<?php include get_layout('admin-navbar'); ?>
<?php include get_layout('admin-sidebar'); ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

  <div class="page-header">
    <div class="row align-items-center">
      <div class="col">
        <h1 class="page-header-title"><i class="bi-file-person me-2"></i>Custom Members Report</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
          <li class="breadcrumb-item"><a href="<?= url('admin/reports-members') ?>">Members Report</a></li>
          <li class="breadcrumb-item active">Custom Builder</li>
        </ol></nav>
      </div>
    </div>
  </div>

  <div class="card">
    <!-- ── Filter Bar ─────────────────────────────────────────────── -->
    <div class="card-header border-bottom-0">
      <div class="row g-2 align-items-end">

        <div class="col-sm-3">
          <label class="form-label form-label-sm mb-1">Search</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi-search"></i></span>
            <input type="text" id="fSearch" class="form-control" placeholder="Name, email, number…">
          </div>
        </div>

        <div class="col-sm-2">
          <label class="form-label form-label-sm mb-1">Status</label>
          <select id="fStatus" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>

        <div class="col-sm-2">
          <label class="form-label form-label-sm mb-1">Member Type</label>
          <select id="fType" class="form-select form-select-sm">
            <option value="">All Types</option>
            <?php foreach($membershipTypes as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['type_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if($isSuperAdmin): ?>
        <div class="col-sm-2">
          <label class="form-label form-label-sm mb-1">Session</label>
          <select id="fSession" class="form-select form-select-sm">
            <option value="">All Sessions</option>
            <option value="day">&#9728;&#65039; Day</option>
            <option value="weekend">&#127761; Weekend</option>
          </select>
        </div>
        <?php endif; ?>

        <!-- More Filters toggle -->
        <div class="col-sm-auto">
          <label class="form-label form-label-sm mb-1 d-block">&nbsp;</label>
          <button class="btn btn-outline-secondary btn-sm" type="button"
                  data-bs-toggle="collapse" data-bs-target="#moreFilters" aria-expanded="false">
            <i class="bi-sliders me-1"></i>More
          </button>
        </div>

        <!-- Column picker -->
        <div class="col-sm-auto">
          <label class="form-label form-label-sm mb-1 d-block">&nbsp;</label>
          <div class="dropdown">
            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside">
              <i class="bi-layout-three-columns me-1"></i>Columns
            </button>
            <div class="dropdown-menu p-2" style="min-width:200px;max-height:320px;overflow-y:auto;">
              <div class="d-flex justify-content-between mb-2 px-1">
                <small class="text-muted fw-semibold">Choose Columns</small>
                <a href="#" class="small text-primary" id="toggleAllCols">Select All</a>
              </div>
              <?php foreach($ALL_COLUMNS as $key => $label): ?>
                <div class="form-check form-check-sm py-1">
                  <input class="form-check-input col-check" type="checkbox"
                         id="col_<?= $key ?>" value="<?= $key ?>"
                         <?= in_array($key, $DEFAULT_COLS) ? 'checked' : '' ?>>
                  <label class="form-check-label small" for="col_<?= $key ?>"><?= $label ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Clear -->
        <div class="col-sm-auto">
          <label class="form-label form-label-sm mb-1 d-block">&nbsp;</label>
          <button class="btn btn-light btn-sm" id="clearBtn"><i class="bi-x-lg me-1"></i>Clear</button>
        </div>

        <!-- Export -->
        <div class="col-sm-auto ms-auto">
          <label class="form-label form-label-sm mb-1 d-block">&nbsp;</label>
          <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#" id="printBtn"><i class="bi-printer me-2"></i>Print / Save as PDF</a></li>
              <li><a class="dropdown-item" href="#" id="csvBtn"><i class="bi-file-earmark-spreadsheet me-2"></i>Download CSV</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" id="waBtn"><i class="bi-whatsapp text-success me-2"></i>Share on WhatsApp</a></li>
              <li><a class="dropdown-item" href="#" id="tgBtn"><i class="bi-telegram text-primary me-2"></i>Share on Telegram</a></li>
            </ul>
          </div>
        </div>

      </div>

      <!-- Collapsible extra filters -->
      <div class="collapse" id="moreFilters">
        <div class="row g-2 pt-3 pb-1 border-top mt-3">
          <div class="col-sm-2">
            <label class="form-label form-label-sm mb-1">Gender</label>
            <select id="fGender" class="form-select form-select-sm">
              <option value="">All Genders</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="col-sm-2">
            <label class="form-label form-label-sm mb-1">Family</label>
            <select id="fFamily" class="form-select form-select-sm">
              <option value="">All Families</option>
              <option value="unassigned">Unassigned</option>
              <?php foreach($families as $f): ?>
                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['family_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-2">
            <label class="form-label form-label-sm mb-1">Faculty</label>
            <select id="fFaculty" class="form-select form-select-sm">
              <option value="">All Faculties</option>
              <option>Information Technology</option><option>Law</option>
              <option>Finance</option><option>Accounting</option>
              <option>Procurement</option><option>Education</option>
              <option>Economics</option><option>Graduate School</option>
            </select>
          </div>
          <div class="col-sm-2">
            <label class="form-label form-label-sm mb-1">Year Joined (from – to)</label>
            <div class="d-flex gap-1">
              <input type="number" id="fYearFrom" class="form-control form-control-sm" placeholder="<?= date('Y')-4 ?>" min="2016" max="<?= date('Y') ?>">
              <input type="number" id="fYearTo"   class="form-control form-control-sm" placeholder="<?= date('Y') ?>"   min="2016" max="<?= date('Y') ?>">
            </div>
          </div>
          <div class="col-sm-3">
            <label class="form-label form-label-sm mb-1">Registration Date (from – to)</label>
            <div class="d-flex gap-1">
              <input type="date" id="fDateFrom" class="form-control form-control-sm">
              <input type="date" id="fDateTo"   class="form-control form-control-sm">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Info strip -->
    <div class="px-4 py-2 border-top border-bottom bg-light d-flex align-items-center justify-content-between">
      <span class="text-muted small">
        Showing <strong id="resultCount">—</strong> members
        <span id="filterBadges" class="ms-2"></span>
      </span>
      <span id="loadingSpinner" style="display:none;" class="text-muted small">
        <span class="spinner-border spinner-border-sm me-1"></span>Loading…
      </span>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light" id="reportHead"></thead>
        <tbody id="reportBody">
          <tr><td class="text-center py-5">
            <div class="spinner-border text-primary mb-2 d-block mx-auto"></div>Loading…
          </td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="card-footer d-flex justify-content-between align-items-center">
      <span class="text-muted small" id="pageInfo"></span>
      <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
    </div>
  </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include get_layout('admin-scripts'); ?>

<script>
(function () {
    'use strict';

    const BASE_URL    = '<?= BASE_URL ?>';
    const API         = BASE_URL + '/api/membership';
    const IS_SUPER    = <?= $isSuperAdmin ? 'true' : 'false' ?>;
    const SESSION_CTX = '<?= $sessionCtx ?? '' ?>';
    const ALL_COLUMNS = <?= json_encode($ALL_COLUMNS) ?>;

    let allData     = [];
    let currentPage = 1;
    let totalPages  = 1;
    let loadTimer;

    const $  = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);
    const val = id => (document.getElementById(id) || {}).value || '';

    // ── Active columns ──────────────────────────────────────────────
    function getActiveCols() {
        return Array.from($$('.col-check:checked')).map(el => el.value);
    }

    // ── Build URL params ────────────────────────────────────────────
    function getParams(page) {
        const p = new URLSearchParams({ action: 'list', page: page || 1, per_page: 50 });
        if (val('fSearch'))  p.set('search',             val('fSearch'));
        if (val('fStatus'))  p.set('status',             val('fStatus'));
        if (val('fType'))    p.set('membership_type_id', val('fType'));
        if (val('fGender'))  p.set('gender',             val('fGender'));
        if (val('fFamily'))  p.set('family_id',          val('fFamily'));
        if (val('fFaculty')) p.set('faculty',            val('fFaculty'));
        const sess = IS_SUPER ? val('fSession') : SESSION_CTX;
        if (sess) p.set('cep_session', sess);
        return p;
    }

    // ── Load ────────────────────────────────────────────────────────
    async function load(page) {
        page = page || 1;
        currentPage = page;
        $('#loadingSpinner').style.display = '';

        try {
            const res = await (await fetch(API + '?' + getParams(page), { credentials: 'include' })).json();
            if (!res.success) { renderEmpty('Failed to load members.'); return; }

            let members = res.data || [];

            // Optional client-side year/date narrowing
            const yf = parseInt(val('fYearFrom'));
            const yt = parseInt(val('fYearTo'));
            const df = val('fDateFrom');
            const dt = val('fDateTo');
            if (yf) members = members.filter(m => parseInt(m.year_joined_cep) >= yf);
            if (yt) members = members.filter(m => parseInt(m.year_joined_cep) <= yt);
            if (df) members = members.filter(m => m.created_at && m.created_at >= df);
            if (dt) members = members.filter(m => m.created_at && m.created_at <= dt + ' 23:59:59');

            allData    = members;
            totalPages = res.meta?.total_pages || 1;

            $('#resultCount').textContent = (res.meta?.total || members.length).toLocaleString();
            updateBadges();
            renderHeader(getActiveCols());
            renderRows(members, getActiveCols());
            renderPagination(res.meta);
        } catch (e) {
            renderEmpty('Network error. Please retry.');
        }
        $('#loadingSpinner').style.display = 'none';
    }

    // ── Header ──────────────────────────────────────────────────────
    function renderHeader(cols) {
        $('#reportHead').innerHTML = '<tr><th style="width:36px;">#</th>'
            + cols.map(c => '<th>' + esc(ALL_COLUMNS[c] || c) + '</th>').join('') + '</tr>';
    }

    // ── Rows ────────────────────────────────────────────────────────
    function renderRows(members, cols) {
        if (!members.length) { renderEmpty('No members found with the selected filters.'); return; }

        const scMap = { active: 'success', pending: 'warning', inactive: 'secondary', suspended: 'danger' };
        const fmtD  = d => d ? new Date(d).toLocaleDateString('en-GB') : '—';

        const fmtV = function (m, col) {
            var v = m[col];
            if (col === 'created_at' || col === 'date_of_birth') return fmtD(v);
            if (col === 'cep_session') return v === 'day'
                ? '<span class="badge bg-soft-warning text-warning">&#9728;&#65039; Day</span>'
                : '<span class="badge bg-soft-primary text-primary">&#127761; Weekend</span>';
            if (col === 'status') {
                var sc = scMap[v] || 'secondary';
                return '<span class="badge bg-soft-' + sc + ' text-' + sc + '">' + esc((v||'').toUpperCase()) + '</span>';
            }
            if (col === 'is_born_again' || col === 'is_baptized')
                return v === 'yes'
                    ? '<span class="badge bg-soft-success text-success">Yes</span>'
                    : '<span class="text-muted small">No</span>';
            if (col === 'membership_type') return esc(m.membership_type_name || m.membership_type || '—');
            if (col === 'family_name') return m.family_name
                ? '<span style="color:' + (m.family_color || '#377dff') + '">' + esc(m.family_name) + '</span>'
                : '<span class="text-muted">—</span>';
            return (v !== null && v !== undefined && v !== '') ? esc(String(v)) : '<span class="text-muted">—</span>';
        };

        var rows = members.map(function (m, i) {
            var ini = ((m.firstname || '')[0] + (m.lastname || '')[0]).toUpperCase();
            // Safe avatar — no nested template-literal interpolation
            var fb  = '<div class="avatar avatar-xs avatar-soft-primary avatar-circle" style="display:none">'
                    + '<span class="avatar-initials">' + esc(ini) + '</span></div>';
            var avatar = m.profile_photo
                ? '<img src="' + BASE_URL + '/uploads/' + esc(m.profile_photo)
                  + '" style="width:28px;height:28px;border-radius:50%;object-fit:cover;"'
                  + ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\';">' + fb
                : '<div class="avatar avatar-xs avatar-soft-primary avatar-circle">'
                  + '<span class="avatar-initials">' + esc(ini) + '</span></div>';

            var tds = cols.map(function (col) {
                if (col === 'firstname') {
                    return '<td><div class="d-flex align-items-center gap-2">'
                        + avatar + '<div><div class="fw-semibold" style="font-size:13px;">'
                        + esc(m.firstname || '') + '</div></div></div></td>';
                }
                if (col === 'lastname' && !cols.includes('firstname')) {
                    return '<td><div class="d-flex align-items-center gap-2">'
                        + avatar + '<span>' + esc(m.lastname || '') + '</span></div></td>';
                }
                return '<td>' + fmtV(m, col) + '</td>';
            });

            return '<tr><td class="text-muted" style="font-size:11px;">'
                + ((currentPage - 1) * 50 + i + 1)
                + '</td>' + tds.join('') + '</tr>';
        });

        $('#reportBody').innerHTML = rows.join('');
    }

    function renderEmpty(msg) {
        var span = getActiveCols().length + 1;
        $('#reportBody').innerHTML = '<tr><td colspan="' + span + '" class="text-center py-5 text-muted">'
            + '<i class="bi-inbox" style="font-size:40px;opacity:.25;display:block;margin-bottom:8px;"></i>'
            + msg + '</td></tr>';
    }

    // ── Filter badges ───────────────────────────────────────────────
    function updateBadges() {
        var badges = [];
        ['fStatus', 'fType', 'fGender', 'fFaculty', 'fFamily', 'fSession'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && el.value) {
                var txt = el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : el.value;
                badges.push('<span class="badge bg-soft-secondary text-secondary me-1">' + esc(txt) + '</span>');
            }
        });
        var yf = val('fYearFrom'), yt = val('fYearTo');
        if (yf || yt) badges.push('<span class="badge bg-soft-secondary text-secondary me-1">Year: ' + (yf||'…') + '–' + (yt||'…') + '</span>');
        $('#filterBadges').innerHTML = badges.join('');
    }

    // ── Pagination ──────────────────────────────────────────────────
    function renderPagination(meta) {
        var total = meta && meta.total ? meta.total : 0;
        var page  = meta && meta.page  ? meta.page  : 1;
        var pp    = meta && meta.per_page ? meta.per_page : 50;
        var from  = Math.min((page - 1) * pp + 1, total);
        var to    = Math.min(page * pp, total);
        $('#pageInfo').textContent = total ? 'Showing ' + from + '–' + to + ' of ' + total : '';

        var pages = [];
        pages.push('<li class="page-item ' + (page <= 1 ? 'disabled' : '') + '">'
            + '<a class="page-link" href="#" onclick="window._cr(' + (page-1) + ');return false;">&laquo;</a></li>');
        for (var i = Math.max(1, page - 2); i <= Math.min(totalPages, page + 2); i++) {
            pages.push('<li class="page-item ' + (i === page ? 'active' : '') + '">'
                + '<a class="page-link" href="#" onclick="window._cr(' + i + ');return false;">' + i + '</a></li>');
        }
        pages.push('<li class="page-item ' + (page >= totalPages ? 'disabled' : '') + '">'
            + '<a class="page-link" href="#" onclick="window._cr(' + (page+1) + ');return false;">&raquo;</a></li>');
        $('#pagination').innerHTML = pages.join('');
    }
    window._cr = load;

    // ── Fetch all for export ────────────────────────────────────────
    async function fetchAll() {
        var p = getParams(1);
        p.set('per_page', '1000');
        var members = ((await (await fetch(API + '?' + p, { credentials: 'include' })).json()).data) || [];
        var yf = parseInt(val('fYearFrom')), yt = parseInt(val('fYearTo'));
        var df = val('fDateFrom'), dt = val('fDateTo');
        if (yf) members = members.filter(function(m){ return parseInt(m.year_joined_cep) >= yf; });
        if (yt) members = members.filter(function(m){ return parseInt(m.year_joined_cep) <= yt; });
        if (df) members = members.filter(function(m){ return m.created_at && m.created_at >= df; });
        if (dt) members = members.filter(function(m){ return m.created_at && m.created_at <= dt + ' 23:59:59'; });
        return members;
    }

    // ── Print HTML ──────────────────────────────────────────────────
    function buildPrint(members, cols) {
        var labels = cols.map(function(c){ return ALL_COLUMNS[c] || c; });
        var fmtD   = function(d){ return d ? new Date(d).toLocaleDateString('en-GB') : '—'; };
        var fmtP   = function(m, col) {
            var v = m[col];
            if (col === 'created_at' || col === 'date_of_birth') return fmtD(v);
            if (col === 'cep_session')   return v === 'day' ? 'Day' : (v === 'weekend' ? 'Weekend' : (v||'—'));
            if (col === 'status')        return (v||'').toUpperCase();
            if (col === 'is_born_again' || col === 'is_baptized') return v === 'yes' ? 'Yes' : (v === 'no' ? 'No' : (v||'—'));
            if (col === 'membership_type') return m.membership_type_name || m.membership_type || '—';
            if (col === 'family_name')     return m.family_name || '—';
            return (v !== null && v !== undefined) ? String(v) : '—';
        };
        var filterSummary = [];
        ['fStatus','fType','fGender','fFaculty','fFamily'].forEach(function(id){
            var el = document.getElementById(id);
            if (el && el.value) filterSummary.push(el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : el.value);
        });
        var rows = members.map(function(m, i){
            return '<tr><td style="text-align:center;color:#999;font-size:10px;">' + (i+1) + '</td>'
                + cols.map(function(c){ return '<td>' + fmtP(m,c) + '</td>'; }).join('') + '</tr>';
        }).join('');

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Members Report</title>'
            + '<style>*{box-sizing:border-box;margin:0;padding:0;font-family:\'Segoe UI\',Arial,sans-serif;}'
            + 'body{background:#fff;}.w{padding:20px;}'
            + '.hdr{background:linear-gradient(135deg,#2c3e6b,#377dff);color:#fff;padding:18px 22px;border-radius:6px 6px 0 0;}'
            + '.hdr h1{font-size:18px;font-weight:700;}.hdr p{font-size:11px;opacity:.85;margin-top:3px;}'
            + '.meta{background:#f8faff;border:1px solid #e0e7ff;padding:7px 14px;margin-bottom:12px;'
            + 'display:flex;gap:16px;flex-wrap:wrap;border-radius:0 0 6px 6px;}'
            + '.meta span{font-size:10px;color:#555;}'
            + 'table{width:100%;border-collapse:collapse;}'
            + 'th{background:#2c3e6b;color:#fff;font-size:10px;text-transform:uppercase;letter-spacing:.4px;padding:7px;text-align:left;}'
            + 'td{font-size:10px;padding:5px 7px;border-bottom:1px solid #f0f0f0;}'
            + 'tr:nth-child(even) td{background:#fafbff;}'
            + '.ft{text-align:center;margin-top:12px;font-size:9px;color:#aaa;}'
            + '@media print{@page{margin:10mm;size:landscape;}.w{padding:0;}}</style>'
            + '</head><body><div class="w">'
            + '<div class="hdr"><h1>CEP UoK &mdash; Members Report</h1>'
            + '<p>Generated ' + new Date().toLocaleDateString('en-GB',{weekday:'long',year:'numeric',month:'long',day:'numeric'})
            + ' &bull; ' + members.length + ' members</p></div>'
            + '<div class="meta">'
            + '<span><b>Total:</b> ' + members.length + '</span>'
            + filterSummary.map(function(f){ return '<span>' + f + '</span>'; }).join('')
            + '<span><b>Columns:</b> ' + labels.join(', ') + '</span>'
            + '<span><b>By:</b> <?= htmlspecialchars($userFullName) ?></span></div>'
            + '<table><thead><tr><th>#</th>'
            + labels.map(function(l){ return '<th>' + l + '</th>'; }).join('')
            + '</tr></thead><tbody>' + rows + '</tbody></table>'
            + '<div class="ft">CEP UoK &bull; Confidential &bull; <?= date('Y') ?></div>'
            + '</div></body></html>';
    }

    // ── CSV ─────────────────────────────────────────────────────────
    function downloadCSV(members, cols) {
        var labels = cols.map(function(c){ return ALL_COLUMNS[c]||c; });
        var q = function(v){ return '"' + String(v||'').replace(/"/g,'""') + '"'; };
        var fmtV = function(m,col){
            if (col==='membership_type') return m.membership_type_name||m.membership_type||'';
            if (col==='family_name')     return m.family_name||'';
            var v = m[col]; return v!==null&&v!==undefined ? String(v) : '';
        };
        var csv = [['#'].concat(labels).map(q).join(',')].concat(
            members.map(function(m,i){
                return [i+1].concat(cols.map(function(c){ return fmtV(m,c); })).map(q).join(',');
            })
        ).join('\r\n');
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
        a.download = 'CEP_Custom_Report_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    }

    // ── Share text ──────────────────────────────────────────────────
    function shareText(count) {
        var parts = ['*CEP Members Report*',
            new Date().toLocaleDateString('en-GB'),
            'Total: ' + count + ' members'];
        ['fStatus','fType','fGender'].forEach(function(id){
            var el = document.getElementById(id);
            if (el && el.value) parts.push(el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : el.value);
        });
        return encodeURIComponent(parts.join('\n'));
    }

    // ── HTML escape ─────────────────────────────────────────────────
    function esc(s) {
        return String(s === null || s === undefined ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Bindings ────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

        renderHeader(getActiveCols());

        document.getElementById('fSearch').addEventListener('input', function(){
            clearTimeout(loadTimer);
            loadTimer = setTimeout(function(){ load(1); }, 350);
        });

        ['fStatus','fType','fSession','fFamily','fGender','fFaculty'].forEach(function(id){
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', function(){ load(1); });
        });

        ['fYearFrom','fYearTo','fDateFrom','fDateTo'].forEach(function(id){
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', function(){ load(1); });
        });

        $$('.col-check').forEach(function(el){
            el.addEventListener('change', function(){
                renderHeader(getActiveCols());
                if (allData.length) renderRows(allData, getActiveCols());
            });
        });

        document.getElementById('toggleAllCols').addEventListener('click', function(e){
            e.preventDefault();
            var all = $$('.col-check');
            var anyOff = Array.from(all).some(function(el){ return !el.checked; });
            all.forEach(function(el){ el.checked = anyOff; });
            this.textContent = anyOff ? 'Clear All' : 'Select All';
            renderHeader(getActiveCols());
            if (allData.length) renderRows(allData, getActiveCols());
        });

        document.getElementById('clearBtn').addEventListener('click', function(){
            ['fSearch','fStatus','fType','fSession','fFamily','fGender','fFaculty',
             'fYearFrom','fYearTo','fDateFrom','fDateTo'].forEach(function(id){
                var el = document.getElementById(id); if (el) el.value = '';
            });
            load(1);
        });

        document.getElementById('printBtn').addEventListener('click', async function(e){
            e.preventDefault();
            Swal.fire({ title:'Preparing report…', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
            var all = await fetchAll();
            Swal.close();
            var win = window.open('','_blank','width=1200,height=800');
            win.document.write(buildPrint(all, getActiveCols()));
            win.document.close();
            setTimeout(function(){ win.print(); }, 700);
        });

        document.getElementById('csvBtn').addEventListener('click', async function(e){
            e.preventDefault();
            Swal.fire({ title:'Preparing CSV…', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
            var all = await fetchAll();
            Swal.close();
            downloadCSV(all, getActiveCols());
        });

        document.getElementById('waBtn').addEventListener('click', async function(e){
            e.preventDefault();
            var all = await fetchAll();
            window.open('https://wa.me/?text=' + shareText(all.length), '_blank');
        });

        document.getElementById('tgBtn').addEventListener('click', async function(e){
            e.preventDefault();
            var all = await fetchAll();
            window.open('https://t.me/share/url?url=' + encodeURIComponent(location.href)
                + '&text=' + shareText(all.length), '_blank');
        });

        load(1);
    });
})();
</script>
</body>