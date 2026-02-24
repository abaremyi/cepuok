<?php
/**
 * Disbursements
 * File: modules/Dashboard/views/finance-disbursements.php
 */
$pageTitle          = 'Disbursements';
$requiredPermission = 'finance.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
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
                <h1 class="page-header-title">Disbursements</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
                    <li class="breadcrumb-item active">Disbursements</li>
                </ol></nav>
            </div>
            <div class="col-auto d-flex gap-2">
                <?php if($isSuperAdmin??false): ?>
                <select id="fltSession" class="form-select form-select-sm" style="width:150px">
                    <option value="">All Sessions</option>
                    <option value="day">☀️ Day CEP</option>
                    <option value="weekend">🌙 Weekend CEP</option>
                </select>
                <?php endif; ?>
                <select id="fltMethod" class="form-select form-select-sm" style="width:160px">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="display-6 fw-bold text-primary" id="totalDisb">—</div><small class="text-muted">Total Disbursed</small></div></div></div>
        <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="display-6 fw-bold text-success" id="countDisb">—</div><small class="text-muted">Disbursements</small></div></div></div>
        <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="display-6 fw-bold text-warning" id="thisMonthDisb">—</div><small class="text-muted">This Month</small></div></div></div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-header-title">All Disbursements</h4>
            <span class="text-muted small" id="resultCount">Loading…</span>
        </div>
        <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr><th>Date</th><th>Request</th><th>Session</th><th>Amount</th><th>Method</th><th>Reference</th><th>Recipient</th><th>Disbursed By</th></tr>
                </thead>
                <tbody id="disbTbody">
                    <tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center py-2" id="paginator"></div>
    </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const BASE  = '<?=BASE_URL?>';
    const API   = BASE+'/api/finance';
    const IS_SA = <?=json_encode($isSuperAdmin??false)?>;
    const MY_S  = <?=json_encode($currentUser->session_type??null)?>;
    let currentPage=1;
    function sess(){ return IS_SA?(document.getElementById('fltSession')?.value||null):MY_S; }
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

    async function loadDisbursements(page=1){
        currentPage=page;
        const tbody=document.getElementById('disbTbody');
        tbody.innerHTML='<tr><td colspan="8" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';
        const params=new URLSearchParams({action:'disbursements',page,per_page:20});
        const s=sess(); if(s) params.set('session',s);
        const m=document.getElementById('fltMethod')?.value; if(m) params.set('method',m);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        document.getElementById('resultCount').textContent=`${data.total||0} disbursement(s)`;
        const list=data.data||[];
        let totalAmt=0; list.forEach(r=>totalAmt+=parseFloat(r.amount||0));
        document.getElementById('totalDisb').textContent='RWF '+totalAmt.toLocaleString();
        document.getElementById('countDisb').textContent=data.total||0;
        const methColors={cash:'success',mobile_money:'info',bank_transfer:'primary',cheque:'warning'};
        if(!list.length){tbody.innerHTML='<tr><td colspan="8" class="text-center text-muted">No disbursements found.</td></tr>';renderPager(0,1);return;}
        tbody.innerHTML=list.map(r=>`<tr>
          <td>${r.disbursed_at?.substr(0,10)||'—'}</td>
          <td><span class="fw-semibold">${esc(r.request_number||'#'+r.request_id)}</span><br><small class="text-muted">${esc(r.request_title||'')}</small></td>
          <td><span class="badge bg-soft-${r.cep_session==='day'?'warning':'primary'} text-${r.cep_session==='day'?'warning':'primary'}">${esc(r.cep_session||'—')}</span></td>
          <td class="fw-bold text-danger">RWF ${Number(r.amount||0).toLocaleString()}</td>
          <td><span class="badge bg-soft-${methColors[r.payment_method]||'secondary'} text-${methColors[r.payment_method]||'secondary'} text-capitalize">${esc(r.payment_method?.replace('_',' ')||'—')}</span></td>
          <td class="text-muted">${esc(r.reference_no||'—')}</td>
          <td>${esc(r.recipient_name||'—')}</td>
          <td>${esc(r.disbursed_by_name||'System')}</td>
        </tr>`).join('');
        renderPager(data.total,data.pages);
    }

    function renderPager(total,pages){
        const el=document.getElementById('paginator'); if(!el) return;
        el.innerHTML=`<span class="text-muted small">${total} records</span>
          <nav><ul class="pagination pagination-sm mb-0">
            <li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadDisb(${currentPage-1});return false;">‹</a></li>
            ${Array.from({length:pages},(_,i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadDisb(${i+1});return false;">${i+1}</a></li>`).join('')}
            <li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadDisb(${currentPage+1});return false;">›</a></li>
          </ul></nav>`;
    }
    window.loadDisb=loadDisbursements;

    document.addEventListener('DOMContentLoaded',()=>{
        loadDisbursements();
        ['fltSession','fltMethod'].forEach(id=>document.getElementById(id)?.addEventListener('change',()=>loadDisbursements(1)));
    });
})();
</script>
</body></html>