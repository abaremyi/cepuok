<?php
/**
 * Financial Reports
 * File: modules/Dashboard/views/finance-reports.php
 */
$pageTitle          = 'Financial Reports';
$requiredPermission='finance.reports';
require_once dirname(__DIR__,3).'/helpers/admin-base.php';
?>
<?php include LAYOUTS_PATH.'/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH.'/admin-lock-screen.php'; ?>
<script>(function(){var el=document.getElementById('sessionLockOverlay');if(el)el.dataset.email=<?=json_encode($currentUser->email??'')?>;})();</script>
<script src="<?=admin_js_url('hs.theme-appearance.js')?>"></script>
<script src="<?=admin_vendor_url('hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js')?>"></script>
<?php include LAYOUTS_PATH.'/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH.'/admin-sidebar.php'; ?>
<main id="content" role="main" class="main">
<div class="content container-fluid">
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-sm"><h1 class="page-header-title">Financial Reports</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
          <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
          <li class="breadcrumb-item active">Reports</li>
        </ol></nav>
      </div>
      <div class="col-auto d-flex gap-2">
        <?php if($isSuperAdmin??false): ?><select id="fltSession" class="form-select form-select-sm" style="width:150px"><option value="">All Sessions</option><option value="day">☀️ Day CEP</option><option value="weekend">🌙 Weekend CEP</option></select><?php endif; ?>
        <select id="fltYear" class="form-select form-select-sm" style="width:100px">
          <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?><option value="<?=$y?>" <?=$y==date('Y')?'selected':''?>><?=$y?></option><?php endfor; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Summary KPIs -->
  <div class="row g-3 mb-4">
    <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-success" id="rTotalRev">—</div><small class="text-muted">Total Revenue</small></div></div></div>
    <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-danger" id="rTotalExp">—</div><small class="text-muted">Total Expenses</small></div></div></div>
    <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-primary" id="rBalance">—</div><small class="text-muted">Net Balance</small></div></div></div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-xl-8"><div class="card"><div class="card-header"><h4 class="card-header-title">Monthly Revenue vs Expenses</h4></div>
      <div class="card-body"><canvas id="monthlyChart" height="100"></canvas></div></div></div>
    <div class="col-xl-4"><div class="card"><div class="card-header"><h4 class="card-header-title">Fund Requests by Stage</h4></div>
      <div class="card-body" id="frSummary"><p class="text-muted text-center py-3">Loading…</p></div></div></div>
  </div>

  <!-- Monthly Table -->
  <div class="card">
    <div class="card-header"><h4 class="card-header-title">Monthly Breakdown</h4></div>
    <div class="table-responsive">
      <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light"><tr><th>Month</th><th>Revenue</th><th>Offerings</th><th>Tithes</th><th>Donations</th></tr></thead>
        <tbody id="monthlyTbody"><tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr></tbody>
      </table>
    </div>
  </div>
</div>
<?php include LAYOUTS_PATH.'/admin-footer.php'; ?>
</main>
<?php include LAYOUTS_PATH.'/admin-scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function(){
  const BASE='<?=BASE_URL?>';const API=BASE+'/api/reports';
  const IS_SA=<?=json_encode($isSuperAdmin??false)?>;const MY_S=<?=json_encode($currentUser->session_type??null)?>;
  let chart;
  function sess(){return IS_SA?(document.getElementById('fltSession')?.value||null):MY_S;}
  function fmtMoney(v){return'RWF '+Number(v||0).toLocaleString();}
  function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;');}
  const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

  async function load(){
    const s=sess();const y=document.getElementById('fltYear').value;
    const params=new URLSearchParams({action:'finance_overview'});
    if(s)params.set('session',s);if(y)params.set('year',y);
    const res=await fetch(`${API}?${params}`,{credentials:'include'});
    const data=await res.json();
    if(!data.success)return;
    const {summary,monthly,fund_requests}=data.data;
    document.getElementById('rTotalRev').textContent=fmtMoney(summary.total_revenue);
    document.getElementById('rTotalExp').textContent=fmtMoney(summary.total_expenses);
    const bal=parseFloat(summary.total_revenue||0)-parseFloat(summary.total_expenses||0);
    document.getElementById('rBalance').textContent=fmtMoney(bal);
    renderMonthly(monthly);
    renderFRSummary(fund_requests);
  }

  function renderMonthly(data){
    const revArr=Array(12).fill(0);
    (data||[]).forEach(r=>{revArr[r.month-1]=parseFloat(r.revenue||0);});
    const tbody=document.getElementById('monthlyTbody');
    tbody.innerHTML=(data||[]).map(r=>`<tr>
      <td>${months[r.month-1]}</td>
      <td class="fw-bold text-success">RWF ${Number(r.revenue||0).toLocaleString()}</td>
      <td>RWF ${Number(r.offerings||0).toLocaleString()}</td>
      <td>RWF ${Number(r.tithes||0).toLocaleString()}</td>
      <td>RWF ${Number(r.donations||0).toLocaleString()}</td>
    </tr>`).join('')||'<tr><td colspan="5" class="text-center text-muted">No data for this period.</td></tr>';
    const ctx=document.getElementById('monthlyChart').getContext('2d');
    if(chart)chart.destroy();
    chart=new Chart(ctx,{type:'bar',data:{labels:months,datasets:[{label:'Revenue',data:revArr,backgroundColor:'rgba(0,201,167,.6)',borderColor:'#00c9a7',borderWidth:1}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{ticks:{callback:v=>'RWF '+v.toLocaleString()}}}}});
  }

  function renderFRSummary(data){
    const el=document.getElementById('frSummary');
    if(!data?.length){el.innerHTML='<p class="text-muted text-center">No fund request data.</p>';return;}
    const stColors={pending:'warning',reviewing:'info',approved:'success',rejected:'danger',disbursed:'primary'};
    el.innerHTML=data.map(r=>`<div class="d-flex justify-content-between align-items-center mb-2">
      <span class="badge bg-soft-${stColors[r.stage]||'secondary'} text-${stColors[r.stage]||'secondary'} text-capitalize">${esc(r.stage)}</span>
      <div class="text-end"><strong>${r.count}</strong> request(s)<br><small class="text-muted">RWF ${Number(r.requested||0).toLocaleString()}</small></div>
    </div>`).join('');
  }

  document.addEventListener('DOMContentLoaded',()=>{
    load();
    ['fltSession','fltYear'].forEach(id=>document.getElementById(id)?.addEventListener('change',load));
  });
})();
</script>
</body></html>
