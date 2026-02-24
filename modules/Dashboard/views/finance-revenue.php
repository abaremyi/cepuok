<?php
/**
 * Finance Revenue
 * File: modules/Dashboard/views/finance-revenue.php
 */
$pageTitle          = 'Revenue Management';
$requiredPermission = 'finance.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canRecord = hasPermission($userPermissions, 'finance.record_revenue');
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
<script>(function(){var el=document.getElementById('sessionLockOverlay');if(el)el.dataset.email=<?=json_encode($currentUser->email??'')?>;})();</script>
<script src="<?=admin_js_url('hs.theme-appearance.js')?>"></script>
<script src="<?=admin_vendor_url('hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js')?>"></script>
<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title">Revenue</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
                    <li class="breadcrumb-item active">Revenue</li>
                </ol></nav>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-ghost-secondary" onclick="exportCSV()"><i class="bi bi-download me-1"></i>Export CSV</button>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- LEFT: Record Form -->
        <?php if($canRecord): ?>
        <div class="col-xl-4">
            <div class="card sticky-top" style="top:80px">
                <div class="card-header"><h4 class="card-header-title">Record Revenue</h4></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                        <select id="fSession" class="form-select">
                            <?php if($isSuperAdmin??false): ?>
                            <option value="">Select session…</option>
                            <option value="day">☀️ Day CEP</option>
                            <option value="weekend">🌙 Weekend CEP</option>
                            <?php else: ?>
                            <option value="<?=htmlspecialchars($currentUser->session_type??'day')?>"><?=$currentUser->session_type==='weekend'?'🌙 Weekend CEP':'☀️ Day CEP'?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Revenue Type <span class="text-danger">*</span></label>
                        <select id="fType" class="form-select">
                            <option value="offering">💰 Offering</option>
                            <option value="tithe">🙏 Tithe</option>
                            <option value="donation">❤️ Donation</option>
                            <option value="project">📋 Project</option>
                            <option value="fundraising">🎪 Fundraising</option>
                            <option value="other">📦 Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (RWF) <span class="text-danger">*</span></label>
                        <input type="number" id="fAmount" class="form-control" placeholder="0" min="0" step="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" id="fDate" class="form-control" value="<?=date('Y-m-d')?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reference No</label>
                        <input type="text" id="fRef" class="form-control" placeholder="e.g. RCP-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea id="fDesc" class="form-control" rows="2" placeholder="Optional notes…"></textarea>
                    </div>

                    <!-- Daily Total -->
                    <div class="alert alert-soft-primary py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="small">Today's total:</span>
                        <strong id="dailyTotal">RWF —</strong>
                    </div>

                    <button id="btnRecord" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i> Record Revenue
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- RIGHT: History Table -->
        <div class="col-xl-<?=$canRecord?'8':'12'?>">
            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-center">
                        <?php if($isSuperAdmin??false): ?>
                        <div class="col-auto">
                            <select id="fltSession" class="form-select form-select-sm" style="width:140px">
                                <option value="">All Sessions</option>
                                <option value="day">☀️ Day CEP</option>
                                <option value="weekend">🌙 Weekend CEP</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-auto">
                            <select id="fltType" class="form-select form-select-sm" style="width:130px">
                                <option value="">All Types</option>
                                <option value="offering">Offering</option>
                                <option value="tithe">Tithe</option>
                                <option value="donation">Donation</option>
                                <option value="project">Project</option>
                                <option value="fundraising">Fundraising</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="month" id="fltMonth" class="form-control form-control-sm" value="<?=date('Y-m')?>">
                        </div>
                        <div class="col"><span class="text-muted small" id="resultCount">Loading…</span></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr><th>Date</th><th>Session</th><th>Type</th><th>Amount</th><th>Reference</th><th>Description</th><th>Recorded By</th></tr>
                        </thead>
                        <tbody id="revTbody">
                            <tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center py-2" id="paginator"></div>
            </div>
        </div>
    </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const BASE   = '<?=BASE_URL?>';
    const API    = BASE + '/api/finance';
    const IS_SA  = <?=json_encode($isSuperAdmin??false)?>;
    const MY_SES = <?=json_encode($currentUser->session_type??null)?>;
    let currentPage = 1;

    function getFilter(id){ return document.getElementById(id)?.value||null; }
    function sessionParam() { return IS_SA ? (getFilter('fltSession')||'') : (MY_SES||''); }

    async function loadRevenue(page=1){
        currentPage = page;
        const tbody = document.getElementById('revTbody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';
        const params = new URLSearchParams({action:'revenue_list',page,per_page:20});
        if(sessionParam()) params.set('session', sessionParam());
        if(getFilter('fltType'))  params.set('type',  getFilter('fltType'));
        if(getFilter('fltMonth')) params.set('month', getFilter('fltMonth'));
        const res  = await fetch(`${API}?${params}`, {credentials:'include'});
        const data = await res.json();
        document.getElementById('resultCount').textContent = `${data.total||0} record(s)`;
        if(!data.data?.length){ tbody.innerHTML='<tr><td colspan="7" class="text-center text-muted">No revenue records found.</td></tr>'; renderPager(0,1); return; }
        const typeColors = {offering:'success',tithe:'primary',donation:'info',project:'warning',fundraising:'secondary',other:'dark'};
        tbody.innerHTML = data.data.map(r=>`<tr>
          <td>${r.revenue_date}</td>
          <td><span class="badge bg-soft-${r.cep_session==='day'?'warning':'primary'} text-${r.cep_session==='day'?'warning':'primary'}">${esc(r.cep_session)}</span></td>
          <td><span class="badge bg-soft-${typeColors[r.revenue_type]||'dark'} text-${typeColors[r.revenue_type]||'dark'} text-capitalize">${esc(r.revenue_type)}</span></td>
          <td class="fw-bold text-success">RWF ${Number(r.amount||0).toLocaleString()}</td>
          <td>${esc(r.reference_no||'—')}</td>
          <td class="text-muted">${esc(r.description||'—')}</td>
          <td>${esc(r.recorded_by_name||'System')}</td>
        </tr>`).join('');
        renderPager(data.total, data.pages);
    }

    function renderPager(total, pages){
        const el = document.getElementById('paginator');
        if(!el) return;
        el.innerHTML = `<span class="text-muted small">${total} records</span>
          <nav><ul class="pagination pagination-sm mb-0">
            <li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadRev(${currentPage-1});return false;">‹</a></li>
            ${Array.from({length:pages},(_, i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadRev(${i+1});return false;">${i+1}</a></li>`).join('')}
            <li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadRev(${currentPage+1});return false;">›</a></li>
          </ul></nav>`;
    }
    window.loadRev = loadRevenue;

    async function loadDailyTotal(){
        const session = IS_SA ? (document.getElementById('fSession')?.value||MY_SES) : MY_SES;
        if(!session) return;
        const res  = await fetch(`${API}?action=daily_total&session=${session}`,{credentials:'include'});
        const data = await res.json();
        document.getElementById('dailyTotal').textContent = 'RWF ' + Number(data.total||0).toLocaleString();
    }

    async function recordRevenue(){
        const btn = document.getElementById('btnRecord');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        const payload = {
            cep_session  : document.getElementById('fSession').value,
            revenue_type : document.getElementById('fType').value,
            amount       : document.getElementById('fAmount').value,
            revenue_date : document.getElementById('fDate').value,
            reference_no : document.getElementById('fRef').value,
            description  : document.getElementById('fDesc').value,
        };
        const res  = await fetch(`${API}?action=revenue_record`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data = await res.json();
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Record Revenue';
        if(data.success){
            document.getElementById('fAmount').value='';
            document.getElementById('fRef').value='';
            document.getElementById('fDesc').value='';
            loadRevenue(); loadDailyTotal();
            showToast('Revenue recorded successfully!','success');
        } else {
            showToast(data.message||'Failed to record revenue','danger');
        }
    }

    function showToast(msg,type='success'){
        const t = document.createElement('div');
        t.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;
        t.style.zIndex = 9999; t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(()=>t.remove(),3000);
    }

    function exportCSV(){
        const params = new URLSearchParams({action:'revenue_export'});
        if(sessionParam()) params.set('session', sessionParam());
        if(getFilter('fltType'))  params.set('type',  getFilter('fltType'));
        if(getFilter('fltMonth')) params.set('month', getFilter('fltMonth'));
        window.location.href = `${API}?${params}`;
    }
    window.exportCSV = exportCSV;

    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    document.addEventListener('DOMContentLoaded',()=>{
        loadRevenue();
        loadDailyTotal();
        document.getElementById('btnRecord')?.addEventListener('click', recordRevenue);
        ['fltSession','fltType','fltMonth'].forEach(id=>document.getElementById(id)?.addEventListener('change',()=>loadRevenue(1)));
        document.getElementById('fSession')?.addEventListener('change', loadDailyTotal);
    });
})();
</script>
</body></html>