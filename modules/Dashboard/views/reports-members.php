<?php
/**
 * Members Report
 * File: modules/Dashboard/views/reports-members.php
 *
 * Simple, focused member report page with key filters, a clean preview table,
 * and export options (PDF print, CSV, WhatsApp share).
 */

$pageTitle          = 'Members Report';
$requiredPermission = 'membership.view';
$currentPage        = 'reports-members.php';

require_once get_helper('admin-base');
require_once ROOT_PATH . '/modules/Membership/controllers/MembershipController.php';

$isSuperAdmin  = !empty($currentUser->is_super_admin);
$sessionCtx    = $isSuperAdmin ? null : ($currentUser->session_type ?? null);

$mc              = new MembershipController();
$familiesResult  = $mc->getFamilies(null);
$families        = $familiesResult['data'] ?? [];
$typesResult     = $mc->getMembershipTypes();
$membershipTypes = $typesResult['data'] ?? [];
$statsResult     = $mc->getStatistics($sessionCtx);
$stats           = $statsResult['data'] ?? [];
?>
<?php include get_layout('admin-header'); ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include get_layout('admin-lock-screen'); ?>
<script>(function(){ var el=document.getElementById('sessionLockOverlay'); if(el) el.dataset.email=<?= json_encode($currentUser->email ?? '') ?>; })();</script>
<?php include get_layout('admin-navbar'); ?>
<?php include get_layout('admin-sidebar'); ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

  <!-- Page Header -->
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col">
        <h1 class="page-header-title"><i class="bi-people me-2"></i>Members Report</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
          <li class="breadcrumb-item"><a href="<?= url('admin/reports-overview') ?>">Reports</a></li>
          <li class="breadcrumb-item active">Members Report</li>
        </ol></nav>
      </div>
      <div class="col-auto">
        <a href="<?= url('admin/reports-members-custom') ?>" class="btn btn-outline-primary btn-sm">
          <i class="bi-sliders me-1"></i>Advanced Custom Report
        </a>
      </div>
    </div>
  </div>

  <!-- ── Quick Stats Row ──────────────────────────────────────────── -->
  <div class="row g-3 mb-4">
    <?php
    $statCards = [
      ['label'=>'Total Members', 'key'=>'total',    'icon'=>'people-fill',      'color'=>'primary'],
      ['label'=>'Active',        'key'=>'active',   'icon'=>'check-circle-fill','color'=>'success'],
      ['label'=>'Pending',       'key'=>'pending',  'icon'=>'clock-fill',       'color'=>'warning'],
      ['label'=>'Inactive',      'key'=>'inactive', 'icon'=>'pause-circle-fill','color'=>'secondary'],
    ];
    foreach($statCards as $sc): ?>
    <div class="col-6 col-md-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center bg-soft-<?= $sc['color'] ?>"
               style="width:48px;height:48px;">
            <i class="bi-<?= $sc['icon'] ?> text-<?= $sc['color'] ?> fs-5"></i>
          </div>
          <div>
            <div class="fw-bold fs-4 lh-1"><?= number_format($stats[$sc['key']] ?? 0) ?></div>
            <div class="text-muted small"><?= $sc['label'] ?></div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Filters + Table Card ─────────────────────────────────────── -->
  <div class="card">
    <div class="card-header">
      <div class="row align-items-center g-2">

        <!-- Search -->
        <div class="col-sm-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi-search"></i></span>
            <input type="text" id="fSearch" class="form-control" placeholder="Name, email, phone, number…">
          </div>
        </div>

        <!-- Status -->
        <div class="col-sm-2">
          <select id="fStatus" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>

        <!-- Member Type -->
        <div class="col-sm-2">
          <select id="fType" class="form-select form-select-sm">
            <option value="">All Types</option>
            <?php foreach($membershipTypes as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['type_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Session (super admin only) -->
        <?php if ($isSuperAdmin): ?>
        <div class="col-sm-2">
          <select id="fSession" class="form-select form-select-sm">
            <option value="">All Sessions</option>
            <option value="day">☀️ Day</option>
            <option value="weekend">🌘 Weekend</option>
          </select>
        </div>
        <?php endif; ?>

        <!-- Family -->
        <div class="col-sm-2">
          <select id="fFamily" class="form-select form-select-sm">
            <option value="">All Families</option>
            <option value="unassigned">Unassigned</option>
            <?php foreach($families as $f): ?>
              <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['family_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Gender -->
        <div class="col-sm-1">
          <select id="fGender" class="form-select form-select-sm">
            <option value="">Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>

        <!-- Faculty -->
        <div class="col-sm-2">
          <select id="fFaculty" class="form-select form-select-sm">
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

        <!-- Clear + Export -->
        <div class="col-sm-auto ms-auto d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" id="clearBtn" title="Clear filters">
            <i class="bi-x-lg"></i>
          </button>
          <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#" id="printBtn"><i class="bi-printer me-2"></i>Print / PDF</a></li>
              <li><a class="dropdown-item" href="#" id="csvBtn"><i class="bi-file-earmark-spreadsheet me-2"></i>Download CSV</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" id="waBtn"><i class="bi-whatsapp text-success me-2"></i>Share on WhatsApp</a></li>
              <li><a class="dropdown-item" href="#" id="tgBtn"><i class="bi-telegram text-primary me-2"></i>Share on Telegram</a></li>
            </ul>
          </div>
        </div>

      </div>
    </div>

    <!-- Result info bar -->
    <div class="card-body py-2 border-bottom bg-light d-flex align-items-center justify-content-between" id="infoBar">
      <span class="text-muted small">
        <span id="resultCount" class="fw-semibold text-dark">—</span> members found
      </span>
      <span class="text-muted small" id="filterDesc"></span>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" id="reportTable">
        <thead class="thead-light">
          <tr>
            <th style="width:36px;">#</th>
            <th>Member</th>
            <th>Session</th>
            <th>Type</th>
            <th>Faculty / Program</th>
            <th>Family</th>
            <th>Gender</th>
            <th>Year Joined</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="reportBody">
          <tr><td colspan="9" class="text-center py-5">
            <div class="spinner-border text-primary mb-2 d-block mx-auto"></div>
            Loading members…
          </td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination footer -->
    <div class="card-footer d-flex justify-content-between align-items-center">
      <span class="text-muted small" id="pageInfo"></span>
      <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
    </div>
  </div>

</div><!-- /content -->
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Print document (hidden until print) -->
<div id="printDocument" style="display:none;"></div>

<?php include get_layout('admin-scripts'); ?>

<style>
@media print {
  body > *:not(#printDocument) { display:none!important; }
  #printDocument { display:block!important; }
}
.rpt-wrap { font-family:'Segoe UI',Arial,sans-serif; padding:20px; }
.rpt-hdr  { background:linear-gradient(135deg,#2c3e6b,#377dff); color:#fff; padding:20px 24px; border-radius:6px 6px 0 0; }
.rpt-hdr h1 { font-size:20px; font-weight:700; margin:0 0 4px; }
.rpt-hdr p  { font-size:11px; opacity:.85; margin:0; }
.rpt-meta { background:#f8faff; border:1px solid #e0e7ff; border-top:none; padding:8px 16px; display:flex; gap:20px; flex-wrap:wrap; border-radius:0 0 6px 6px; margin-bottom:16px; }
.rpt-meta span { font-size:11px; color:#555; }
.rpt-meta b { color:#2c3e6b; }
.rpt-tbl { width:100%; border-collapse:collapse; }
.rpt-tbl th { background:#2c3e6b; color:#fff; font-size:10px; text-transform:uppercase; letter-spacing:.4px; padding:8px 8px; text-align:left; }
.rpt-tbl td { font-size:11px; padding:6px 8px; border-bottom:1px solid #f0f0f0; }
.rpt-tbl tr:nth-child(even) td { background:#fafbff; }
.rpt-ft  { text-align:center; margin-top:14px; font-size:10px; color:#aaa; }
@media print { @page { margin:12mm; size:landscape; } .rpt-wrap { padding:0; } }
</style>

<script>
(function () {
    'use strict';

    const BASE_URL   = '<?= BASE_URL ?>';
    const API        = BASE_URL + '/api/membership';
    const IS_SUPER   = <?= $isSuperAdmin ? 'true' : 'false' ?>;
    const SESSION_CTX = '<?= $sessionCtx ?? '' ?>';

    let allData    = [];
    let currentPage = 1;
    let totalPages  = 1;
    let loadTimer;

    const $ = sel => document.querySelector(sel);

    // ── Gather filters ─────────────────────────────────────────────────
    function getParams(page) {
        const p = new URLSearchParams({
            action:   'list',
            page:     page || 1,
            per_page: 50,
        });
        const search  = $('#fSearch').value.trim();
        const status  = $('#fStatus').value;
        const type    = $('#fType').value;
        const gender  = $('#fGender').value;
        const family  = $('#fFamily').value;
        const faculty = $('#fFaculty').value;
        const session = IS_SUPER ? ($('#fSession')?.value || '') : SESSION_CTX;

        if (search)  p.set('search',             search);
        if (status)  p.set('status',             status);
        if (type)    p.set('membership_type_id', type);
        if (gender)  p.set('gender',             gender);
        if (family)  p.set('family_id',          family);
        if (faculty) p.set('faculty',            faculty);
        if (session) p.set('cep_session',        session);
        return p;
    }

    // ── Load data ──────────────────────────────────────────────────────
    async function load(page) {
        page = page || 1;
        currentPage = page;

        $('#reportBody').innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const res = await (await fetch(`${API}?${getParams(page).toString()}`, { credentials: 'include' })).json();
            if (!res.success) { renderEmpty('Failed to load.'); return; }

            allData    = res.data || [];
            totalPages = res.meta?.total_pages || 1;

            updateInfoBar(res.meta);
            renderRows(allData);
            renderPagination(res.meta);
        } catch(e) {
            renderEmpty('Network error. Please retry.');
        }
    }

    // ── Render rows ────────────────────────────────────────────────────
    function renderRows(members) {
        if (!members.length) { renderEmpty('No members match the selected filters.'); return; }

        const statusColor = { active:'success', pending:'warning', inactive:'secondary', suspended:'danger' };
        const rows = members.map((m, i) => {
            const ini  = ((m.firstname||'')[0]+(m.lastname||'')[0]).toUpperCase();
            const sc   = statusColor[m.status] || 'secondary';
            const sess = m.cep_session === 'day'
                ? '<span class="badge bg-soft-warning text-warning">☀️ Day</span>'
                : '<span class="badge bg-soft-primary text-primary">🌘 Weekend</span>';
            const fam  = m.family_name
                ? `<span style="color:${m.family_color||'#377dff'}">${esc(m.family_name)}</span>`
                : '<span class="text-muted">—</span>';

            // Safe avatar — no nested interpolation
            const fb  = '<div class="avatar avatar-xs avatar-soft-primary avatar-circle" style="display:none"><span class="avatar-initials">' + esc(ini) + '</span></div>';
            const img = m.profile_photo
                ? '<img src="' + BASE_URL + '/uploads/' + esc(m.profile_photo) + '" style="width:28px;height:28px;border-radius:50%;object-fit:cover;" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\';">' + fb
                : '<div class="avatar avatar-xs avatar-soft-primary avatar-circle"><span class="avatar-initials">' + esc(ini) + '</span></div>';

            return `<tr>
              <td class="text-muted" style="font-size:11px;">${(currentPage-1)*50 + i + 1}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  ${img}
                  <div>
                    <div class="fw-semibold text-dark" style="font-size:13px;">${esc(m.firstname)} ${esc(m.lastname)}</div>
                    <div class="text-muted" style="font-size:11px;">${esc(m.email)}</div>
                  </div>
                </div>
              </td>
              <td>${sess}</td>
              <td>${esc(m.membership_type_name || m.membership_type || '—')}</td>
              <td><span style="font-size:12px;">${esc(m.faculty||'—')}</span><br><span class="text-muted" style="font-size:11px;">${esc(m.program||'')}</span></td>
              <td>${fam}</td>
              <td>${esc(m.gender ? m.gender.charAt(0).toUpperCase()+m.gender.slice(1) : '—')}</td>
              <td>${m.year_joined_cep||'—'}</td>
              <td><span class="badge bg-soft-${sc} text-${sc}">${esc(m.status||'').toUpperCase()}</span></td>
            </tr>`;
        });

        $('#reportBody').innerHTML = rows.join('');
    }

    function renderEmpty(msg) {
        $('#reportBody').innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">
          <i class="bi-inbox" style="font-size:40px;opacity:.25;display:block;margin-bottom:8px;"></i>${msg}
        </td></tr>`;
    }

    // ── Info bar ────────────────────────────────────────────────────────
    function updateInfoBar(meta) {
        const total = meta?.total || 0;
        $('#resultCount').textContent = total.toLocaleString();

        const parts = [];
        if ($('#fStatus').value)  parts.push('Status: ' + $('#fStatus').value);
        if ($('#fType').value)    parts.push($('#fType').options[$('#fType').selectedIndex]?.text);
        if ($('#fGender').value)  parts.push($('#fGender').value);
        if ($('#fFaculty').value) parts.push($('#fFaculty').value);
        if (IS_SUPER && $('#fSession')?.value) parts.push($('#fSession').options[$('#fSession').selectedIndex]?.text);
        $('#filterDesc').textContent = parts.length ? 'Filters: ' + parts.join(' · ') : 'Showing all members';
    }

    // ── Pagination ──────────────────────────────────────────────────────
    function renderPagination(meta) {
        const total  = meta?.total   || 0;
        const page   = meta?.page    || 1;
        const pp     = meta?.per_page || 50;
        const from   = Math.min((page-1)*pp+1, total);
        const to     = Math.min(page*pp, total);
        $('#pageInfo').textContent = total ? `Showing ${from}–${to} of ${total}` : '';

        const pages = [];
        pages.push(`<li class="page-item ${page<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadPage(${page-1});return false;">&laquo;</a></li>`);
        for (let i=Math.max(1,page-2); i<=Math.min(totalPages,page+2); i++) {
            pages.push(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" onclick="loadPage(${i});return false;">${i}</a></li>`);
        }
        pages.push(`<li class="page-item ${page>=totalPages?'disabled':''}"><a class="page-link" href="#" onclick="loadPage(${page+1});return false;">&raquo;</a></li>`);
        $('#pagination').innerHTML = pages.join('');
    }

    window.loadPage = load;

    // ── Build print HTML ────────────────────────────────────────────────
    function buildPrintDoc(members) {
        const fmtD = d => d ? new Date(d).toLocaleDateString('en-GB') : '—';
        const rows = members.map((m,i) => `
          <tr>
            <td style="text-align:center;color:#999;">${i+1}</td>
            <td>${esc(m.firstname)} ${esc(m.lastname)}<br><span style="color:#999;font-size:10px;">${esc(m.email)}</span></td>
            <td>${m.cep_session==='day'?'Day':'Weekend'}</td>
            <td>${esc(m.membership_type_name||m.membership_type||'—')}</td>
            <td>${esc(m.faculty||'—')}</td>
            <td>${esc(m.family_name||'—')}</td>
            <td>${esc(m.gender||'—')}</td>
            <td>${m.year_joined_cep||'—'}</td>
            <td>${esc((m.status||'').toUpperCase())}</td>
          </tr>`).join('');

        const filterParts = [];
        if ($('#fStatus').value)  filterParts.push('Status: ' + $('#fStatus').value);
        if ($('#fType').value)    filterParts.push($('#fType').options[$('#fType').selectedIndex]?.text);
        if ($('#fGender').value)  filterParts.push('Gender: ' + $('#fGender').value);
        if ($('#fFaculty').value) filterParts.push('Faculty: ' + $('#fFaculty').value);

        return `<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Members Report</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Arial,sans-serif;}
body{background:#fff;}
.rpt-wrap{padding:20px;}
.rpt-hdr{background:linear-gradient(135deg,#2c3e6b,#377dff);color:#fff;padding:20px 24px;border-radius:6px 6px 0 0;}
.rpt-hdr h1{font-size:20px;font-weight:700;}
.rpt-hdr p{font-size:11px;opacity:.85;margin-top:4px;}
.rpt-meta{background:#f8faff;border:1px solid #e0e7ff;padding:8px 16px;margin-bottom:14px;display:flex;gap:20px;flex-wrap:wrap;border-radius:0 0 6px 6px;}
.rpt-meta span{font-size:11px;color:#555;}
table{width:100%;border-collapse:collapse;}
th{background:#2c3e6b;color:#fff;font-size:10px;text-transform:uppercase;padding:8px;text-align:left;}
td{font-size:11px;padding:6px 8px;border-bottom:1px solid #f0f0f0;}
tr:nth-child(even) td{background:#fafbff;}
.ft{text-align:center;margin-top:14px;font-size:10px;color:#aaa;}
@media print{@page{margin:12mm;size:landscape;}}
</style></head><body>
<div class="rpt-wrap">
  <div class="rpt-hdr">
    <h1>CEP UoK — Members Report</h1>
    <p>Generated ${new Date().toLocaleDateString('en-GB',{weekday:'long',year:'numeric',month:'long',day:'numeric'})} &bull; ${members.length} members</p>
  </div>
  <div class="rpt-meta">
    <span><b>Total:</b> ${members.length} members</span>
    ${filterParts.map(f=>'<span>'+f+'</span>').join('')}
    <span><b>By:</b> <?= htmlspecialchars($userFullName) ?></span>
  </div>
  <table>
    <thead><tr><th>#</th><th>Name / Email</th><th>Session</th><th>Type</th><th>Faculty</th><th>Family</th><th>Gender</th><th>Year Joined</th><th>Status</th></tr></thead>
    <tbody>${rows}</tbody>
  </table>
  <div class="ft">CEP UoK Members Report &bull; Confidential &bull; <?= date('Y') ?></div>
</div></body></html>`;
    }

    // ── CSV ─────────────────────────────────────────────────────────────
    function downloadCSV(members) {
        const cols = ['#','Name','Email','Phone','Session','Member Type','Faculty','Program','Family','Gender','Year Joined','Status'];
        const q    = v => '"' + String(v||'').replace(/"/g,'""') + '"';
        const rows = members.map((m,i) => [
            i+1, m.firstname+' '+m.lastname, m.email, m.phone||'',
            m.cep_session, m.membership_type_name||m.membership_type||'', m.faculty||'',
            m.program||'', m.family_name||'', m.gender||'',
            m.year_joined_cep||'', m.status||''
        ].map(q).join(','));
        const csv  = [cols.map(q).join(','), ...rows].join('\r\n');
        const a    = document.createElement('a');
        a.href     = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
        a.download = 'CEP_Members_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    }

    // ── Fetch ALL for export (no pagination) ────────────────────────────
    async function fetchAll() {
        const p = getParams(1);
        p.set('per_page', '1000');
        const res = await (await fetch(`${API}?${p.toString()}`, { credentials:'include' })).json();
        return res.data || [];
    }

    // ── Share text ──────────────────────────────────────────────────────
    function shareText(count) {
        const parts = ['📋 *CEP Members Report*', `📅 ${new Date().toLocaleDateString('en-GB')}`, `👥 Total: *${count} members*`];
        if ($('#fStatus').value)  parts.push('📌 Status: ' + $('#fStatus').value);
        if ($('#fGender').value)  parts.push('⚧️ Gender: ' + $('#fGender').value);
        if (IS_SUPER && $('#fSession')?.value) parts.push('🕐 Session: ' + $('#fSession').value);
        parts.push('', '_Generated from CEP Portal_');
        return encodeURIComponent(parts.join('\n'));
    }

    // ── Escape helper ────────────────────────────────────────────────────
    function esc(s) {
        return String(s===null||s===undefined?'':s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Bindings ─────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

        // Auto-search on input
        ['fSearch'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', () => {
                clearTimeout(loadTimer);
                loadTimer = setTimeout(() => load(1), 350);
            });
        });

        // Instant filter on selects
        ['fStatus','fType','fSession','fFamily','fGender','fFaculty'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', () => load(1));
        });

        // Clear
        document.getElementById('clearBtn').addEventListener('click', () => {
            ['fSearch'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
            ['fStatus','fType','fSession','fFamily','fGender','fFaculty'].forEach(id => {
                const el=document.getElementById(id); if(el) el.value='';
            });
            load(1);
        });

        // Print
        document.getElementById('printBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            Swal.fire({ title:'Preparing report…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            const all = await fetchAll();
            Swal.close();
            const win = window.open('','_blank','width=1200,height=800');
            win.document.write(buildPrintDoc(all));
            win.document.close();
            setTimeout(()=>win.print(), 700);
        });

        // CSV
        document.getElementById('csvBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            Swal.fire({ title:'Preparing CSV…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            const all = await fetchAll();
            Swal.close();
            downloadCSV(all);
        });

        // WhatsApp
        document.getElementById('waBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            const all = await fetchAll();
            window.open('https://wa.me/?text=' + shareText(all.length), '_blank');
        });

        // Telegram
        document.getElementById('tgBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            const all = await fetchAll();
            window.open('https://t.me/share/url?url=' + encodeURIComponent(location.href) + '&text=' + shareText(all.length), '_blank');
        });

        // Initial load
        load(1);
    });
})();
</script>
</body>