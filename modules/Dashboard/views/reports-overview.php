<?php
/**
 * Reports Overview
 * File: modules/Dashboard/views/reports-overview.php
 */
$pageTitle          = 'Reports Overview';
$requiredPermission = 'reports.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canExport = hasPermission($userPermissions, 'reports.export');
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
            <div class="col-sm"><h1 class="page-header-title">Reports</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol></nav>
            </div>
            <div class="col-auto d-flex gap-2">
                <?php if($isSuperAdmin??false): ?>
                <select id="fltSession" class="form-select form-select-sm" style="width:150px">
                    <option value="">All Sessions</option><option value="day">☀️ Day CEP</option><option value="weekend">🌙 Weekend CEP</option>
                </select>
                <?php endif; ?>
                <select id="fltYear" class="form-select form-select-sm" style="width:100px">
                    <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?><option value="<?=$y?>" <?=$y==date('Y')?'selected':''?>><?=$y?></option><?php endfor; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link active" href="#" onclick="switchTab('overview',this);return false;"><i class="bi bi-grid me-1"></i>Overview</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="switchTab('members',this);return false;"><i class="bi bi-people me-1"></i>Members</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="switchTab('finance',this);return false;"><i class="bi bi-cash me-1"></i>Finance</a></li>
    </ul>

    <!-- Overview Tab -->
    <div id="tab-overview">
        <div class="row g-3 mb-4">
            <?php foreach([
                ['id'=>'ovMembers', 'label'=>'Total Members',   'icon'=>'bi-people-fill',    'color'=>'primary'],
                ['id'=>'ovActive',  'label'=>'Active Members',  'icon'=>'bi-check-circle',   'color'=>'success'],
                ['id'=>'ovFamilies','label'=>'Families',        'icon'=>'bi-house-heart',    'color'=>'info'],
                ['id'=>'ovRevenue', 'label'=>'This Year Revenue','icon'=>'bi-cash-coin',     'color'=>'warning'],
                ['id'=>'ovProjects','label'=>'Active Projects', 'icon'=>'bi-kanban',         'color'=>'danger'],
            ] as $c): ?>
            <div class="col"><div class="card"><div class="card-body text-center">
                <i class="bi <?=$c['icon']?> fs-3 text-<?=$c['color']?> mb-2 d-block"></i>
                <div class="fs-2 fw-bold text-<?=$c['color']?>" id="<?=$c['id']?>">—</div>
                <small class="text-muted"><?=$c['label']?></small>
            </div></div></div>
            <?php endforeach; ?>
        </div>

        <div class="row g-3">
            <div class="col-xl-6"><div class="card"><div class="card-header"><h4 class="card-header-title">Members by Faculty</h4></div>
                <div class="card-body"><canvas id="facultyChart" height="220"></canvas></div></div></div>
            <div class="col-xl-6"><div class="card"><div class="card-header"><h4 class="card-header-title">Members Joined by Year</h4></div>
                <div class="card-body"><canvas id="joinedChart" height="220"></canvas></div></div></div>
        </div>
    </div>

    <!-- Members Tab -->
    <div id="tab-members" style="display:none">
        <div class="row g-3 mb-3">
            <div class="col-sm-2"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-primary" id="mTotal">—</div><small class="text-muted">Total</small></div></div></div>
            <div class="col-sm-2"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-success" id="mActive">—</div><small class="text-muted">Active</small></div></div></div>
            <div class="col-sm-2"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-warning" id="mPending">—</div><small class="text-muted">Pending</small></div></div></div>
            <div class="col-sm-2"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-primary" id="mMale">—</div><small class="text-muted">Male</small></div></div></div>
            <div class="col-sm-2"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-danger" id="mFemale">—</div><small class="text-muted">Female</small></div></div></div>
            <div class="col-sm-2"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-info" id="mInFamilies">—</div><small class="text-muted">In Families</small></div></div></div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Member List</h5>
            <?php if($canExport): ?>
            <button class="btn btn-outline-success btn-sm" onclick="exportMembers()"><i class="bi bi-download me-1"></i>Export CSV</button>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light"><tr><th>Name</th><th>Email</th><th>Faculty</th><th>Session</th><th>Status</th><th>Family</th><th>Joined</th></tr></thead>
                    <tbody id="memberTbody"><tr><td colspan="7" class="text-center py-3 text-muted">Loading…</td></tr></tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <span class="text-muted small" id="mListInfo"></span>
                <div id="mPager"></div>
            </div>
        </div>
    </div>

    <!-- Finance Tab -->
    <div id="tab-finance" style="display:none">
        <div class="row g-3 mb-3">
            <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-success" id="fRevenue">—</div><small class="text-muted">Total Revenue</small></div></div></div>
            <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-danger" id="fExpenses">—</div><small class="text-muted">Total Expenses</small></div></div></div>
            <div class="col-sm-4"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-primary" id="fBalance">—</div><small class="text-muted">Net Balance</small></div></div></div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-xl-8"><div class="card"><div class="card-header"><h4 class="card-header-title">Monthly Revenue</h4></div>
                <div class="card-body"><canvas id="monthlyRevChart" height="100"></canvas></div></div></div>
            <div class="col-xl-4"><div class="card"><div class="card-header"><h4 class="card-header-title">Fund Requests</h4></div>
                <div class="card-body" id="frPanel"></div></div></div>
        </div>
        <?php if($canExport): ?>
        <div class="text-end mb-3"><button class="btn btn-outline-success btn-sm" onclick="exportRevenue()"><i class="bi bi-download me-1"></i>Export Revenue CSV</button></div>
        <?php endif; ?>
    </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function(){
    'use strict';
    const BASE=`<?=BASE_URL?>`, RAPI=BASE+'/api/reports';
    const IS_SA=<?=json_encode($isSuperAdmin??false)?>, MY_SES=<?=json_encode($currentUser->session_type??null)?>;
    let charts={}, mPage=1, activeTab='overview';
    function sess(){return IS_SA?(document.getElementById('fltSession')?.value||null):MY_SES;}
    function year(){return document.getElementById('fltYear').value;}
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
    function fmtMoney(v){return'RWF '+Number(v||0).toLocaleString();}
    const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    window.switchTab=function(tab,link){
        activeTab=tab;
        document.querySelectorAll('.nav-tabs .nav-link').forEach(l=>l.classList.remove('active'));
        link.classList.add('active');
        ['overview','members','finance'].forEach(t=>document.getElementById('tab-'+t).style.display=t===tab?'':'none');
        if(tab==='overview') loadOverview();
        if(tab==='members') loadMembers(1);
        if(tab==='finance') loadFinance();
    };

    async function loadOverview(){
        const s=sess();
        const rParams=new URLSearchParams({action:'member_overview'}); if(s) rParams.set('session',s);
        const mRes=await fetch(`${RAPI}?${rParams}`,{credentials:'include'});
        const mData=(await mRes.json()).data||{};
        document.getElementById('ovMembers').textContent  = mData.summary?.total||0;
        document.getElementById('ovActive').textContent   = mData.summary?.active||0;
        document.getElementById('ovFamilies').textContent = mData.summary?.in_families||0;
        document.getElementById('ovRevenue').textContent  = fmtMoney(0);
        document.getElementById('ovProjects').textContent = '—';

        // Faculty chart
        const facData=mData.by_faculty||[];
        const ctx1=document.getElementById('facultyChart').getContext('2d');
        if(charts.fac) charts.fac.destroy();
        charts.fac=new Chart(ctx1,{type:'doughnut',data:{labels:facData.map(r=>r.faculty||'Unknown'),datasets:[{data:facData.map(r=>r.count),backgroundColor:['#377dff','#00c9a7','#ffc107','#de4437','#00dffc','#71869d','#e96f2d']}]},options:{responsive:true,plugins:{legend:{position:'right'}}}});

        // Joined by year chart
        const joinData=mData.joined_by_year||[];
        const ctx2=document.getElementById('joinedChart').getContext('2d');
        if(charts.join) charts.join.destroy();
        charts.join=new Chart(ctx2,{type:'bar',data:{labels:joinData.map(r=>r.year),datasets:[{label:'New Members',data:joinData.map(r=>r.count),backgroundColor:'rgba(55,125,255,.6)',borderColor:'#377dff',borderWidth:1}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
    }

    async function loadMembers(page=1){
        mPage=page;
        const params=new URLSearchParams({action:'member_list',page,per_page:15});
        const s=sess(); if(s) params.set('session',s);
        const res=await fetch(`${RAPI}?${params}`,{credentials:'include'});
        const data=await res.json();
        const overview=new URLSearchParams({action:'member_overview'}); if(s) overview.set('session',s);
        const ovRes=await fetch(`${RAPI}?${overview}`,{credentials:'include'});
        const ovData=(await ovRes.json()).data?.summary||{};
        document.getElementById('mTotal').textContent     = ovData.total||0;
        document.getElementById('mActive').textContent    = ovData.active||0;
        document.getElementById('mPending').textContent   = ovData.pending||0;
        document.getElementById('mMale').textContent      = ovData.male||0;
        document.getElementById('mFemale').textContent    = ovData.female||0;
        document.getElementById('mInFamilies').textContent= ovData.in_families||0;
        const list=data.data||[];
        document.getElementById('mListInfo').textContent=`Page ${page} of ${data.pages||1} (${data.total||0} total)`;
        const stCls=s=>s==='active'?'success':s==='pending'?'warning':'secondary';
        const sesCls=s=>s==='day'?'warning':'primary';
        document.getElementById('memberTbody').innerHTML=list.length?list.map(m=>`<tr>
          <td><div class="fw-semibold">${esc(m.firstname)} ${esc(m.lastname)}</div></td>
          <td class="text-muted">${esc(m.email||'—')}</td>
          <td class="text-muted">${esc(m.faculty||'—')}</td>
          <td><span class="badge bg-soft-${sesCls(m.cep_session)} text-${sesCls(m.cep_session)}">${esc(m.cep_session)}</span></td>
          <td><span class="badge bg-soft-${stCls(m.status)} text-${stCls(m.status)} text-capitalize">${esc(m.status)}</span></td>
          <td class="text-muted">${esc(m.family_name||'—')}</td>
          <td class="text-muted">${esc(m.created_at?.split('T')[0]||m.created_at||'—')}</td>
        </tr>`).join(''):'<tr><td colspan="7" class="text-center text-muted py-3">No members found.</td></tr>';
        renderMPager(data.total,data.pages);
    }

    function renderMPager(total,pages){
        const el=document.getElementById('mPager');
        if(!el||pages<=1){el&&(el.innerHTML='');return;}
        el.innerHTML=`<ul class="pagination pagination-sm mb-0"><li class="page-item ${mPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadM(${mPage-1});return false;">‹</a></li>
          ${Array.from({length:Math.min(pages,7)},(_,i)=>`<li class="page-item ${mPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadM(${i+1});return false;">${i+1}</a></li>`).join('')}
          ${pages>7?`<li class="page-item disabled"><a class="page-link">…</a></li><li class="page-item ${mPage===pages?'active':''}"><a class="page-link" href="#" onclick="loadM(${pages});return false;">${pages}</a></li>`:''}
          <li class="page-item ${mPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadM(${mPage+1});return false;">›</a></li></ul>`;
    }
    window.loadM=loadMembers;

    async function loadFinance(){
        const s=sess();const y=year();
        const params=new URLSearchParams({action:'finance_overview'}); if(s) params.set('session',s); if(y) params.set('year',y);
        const res=await fetch(`${RAPI}?${params}`,{credentials:'include'});
        const data=(await res.json()).data||{};
        const {summary,monthly,fund_requests}=data;
        document.getElementById('fRevenue').textContent  = fmtMoney(summary?.total_revenue);
        document.getElementById('fExpenses').textContent = fmtMoney(summary?.total_expenses);
        const bal=parseFloat(summary?.total_revenue||0)-parseFloat(summary?.total_expenses||0);
        document.getElementById('fBalance').textContent  = fmtMoney(bal);
        const revArr=Array(12).fill(0);
        (monthly||[]).forEach(r=>{revArr[r.month-1]=parseFloat(r.revenue||0);});
        const ctx=document.getElementById('monthlyRevChart').getContext('2d');
        if(charts.rev) charts.rev.destroy();
        charts.rev=new Chart(ctx,{type:'bar',data:{labels:months,datasets:[{label:'Revenue',data:revArr,backgroundColor:'rgba(0,201,167,.6)',borderColor:'#00c9a7',borderWidth:1}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{ticks:{callback:v=>'RWF '+v.toLocaleString()}}}}});
        const stColors={pending:'warning',reviewing:'info',approved:'success',rejected:'danger',disbursed:'primary'};
        document.getElementById('frPanel').innerHTML=(fund_requests||[]).map(r=>`<div class="d-flex justify-content-between align-items-center mb-2">
          <span class="badge bg-soft-${stColors[r.stage]||'secondary'} text-${stColors[r.stage]||'secondary'} text-capitalize">${esc(r.stage)}</span>
          <div class="text-end"><strong>${r.count}</strong><br><small class="text-muted">RWF ${Number(r.requested||0).toLocaleString()}</small></div>
        </div>`).join('')||'<p class="text-muted text-center">No data.</p>';
    }

    window.exportMembers=function(){
        const s=sess();const y=year();
        const params=new URLSearchParams({action:'member_export'}); if(s) params.set('session',s); if(y) params.set('year',y);
        window.open(`${RAPI}?${params}`,'_blank');
    };
    window.exportRevenue=function(){
        const s=sess();const y=year();
        const params=new URLSearchParams({action:'revenue_export'}); if(s) params.set('session',s); if(y) params.set('year',y);
        window.open(`${BASE}/api/finance?${params}`,'_blank');
    };

    function reloadActive(){
        if(activeTab==='overview') loadOverview();
        else if(activeTab==='members') loadMembers(mPage);
        else if(activeTab==='finance') loadFinance();
    }

    document.addEventListener('DOMContentLoaded',()=>{
        loadOverview();
        ['fltSession','fltYear'].forEach(id=>document.getElementById(id)?.addEventListener('change',reloadActive));
    });
})();
</script>
</body></html>