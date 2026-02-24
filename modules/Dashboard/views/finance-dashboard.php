<?php
/**
 * Finance Dashboard
 * File: modules/Dashboard/views/finance-dashboard.php
 */
$pageTitle          = 'Finance Dashboard';
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title">Finance Dashboard</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Finance</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto d-flex gap-2">
                <?php if($isSuperAdmin??false): ?>
                <select id="sessionFilter" class="form-select form-select-sm" style="width:160px">
                    <option value="">All Sessions</option>
                    <option value="day">☀️ Day CEP</option>
                    <option value="weekend">🌙 Weekend CEP</option>
                </select>
                <?php endif; ?>
                <a href="<?=url('admin/finance-revenue')?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Record Revenue
                </a>
                <a href="<?=url('admin/finance-fund-requests')?>" class="btn btn-outline-primary btn-sm">
                    Fund Requests
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4" id="kpiCards">
        <?php
        $kpis = [
            ['id'=>'totalRevenue',  'label'=>'Total Revenue',       'icon'=>'bi-graph-up-arrow',   'color'=>'success'],
            ['id'=>'totalExpenses', 'label'=>'Total Expenses',       'icon'=>'bi-graph-down-arrow', 'color'=>'danger'],
            ['id'=>'balance',       'label'=>'Current Balance',      'icon'=>'bi-wallet2',          'color'=>'primary'],
            ['id'=>'reservePool',   'label'=>'Reserve Pool',         'icon'=>'bi-safe2',            'color'=>'warning'],
            ['id'=>'pendingReqs',   'label'=>'Pending Requests',     'icon'=>'bi-hourglass-split',  'color'=>'info'],
            ['id'=>'thisMonth',     'label'=>'This Month Revenue',   'icon'=>'bi-calendar-check',   'color'=>'secondary'],
        ];
        foreach ($kpis as $k): ?>
        <div class="col-sm-6 col-xl-2">
            <div class="card card-hover-shadow h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted"><?=$k['label']?></h6>
                    <div class="d-flex align-items-center">
                        <span class="display-6 fw-bold text-<?=$k['color']?> me-2" id="<?=$k['id']?>">—</span>
                    </div>
                    <div class="badge bg-soft-<?=$k['color']?> text-<?=$k['color']?> mt-1">
                        <i class="bi <?=$k['icon']?> me-1"></i>Loading…
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Monthly Trend -->
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-header-title">Monthly Revenue Trend</h4>
                    <select id="trendYear" class="form-select form-select-sm" style="width:100px">
                        <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
                        <option value="<?=$y?>" <?=$y==date('Y')?'selected':''?>><?=$y?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="card-body"><canvas id="trendChart" height="100"></canvas></div>
            </div>
        </div>
        <!-- Revenue by Type Doughnut -->
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h4 class="card-header-title">Revenue by Type</h4></div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="typeChart" style="max-height:220px"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Utilisation + Session Split -->
    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Budget Utilisation</h4></div>
                <div class="card-body" id="budgetBars">
                    <p class="text-muted text-center py-3">Loading…</p>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Session Revenue Split</h4></div>
                <div class="card-body" id="sessionSplit">
                    <p class="text-muted text-center py-3">Loading…</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-header-title">Recent Transactions</h4>
            <a href="<?=url('admin/finance-revenue')?>" class="btn btn-sm btn-ghost-secondary">View all <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr><th>Date</th><th>Session</th><th>Type</th><th>Amount</th><th>Description</th><th>Recorded By</th></tr>
                </thead>
                <tbody id="recentTbody">
                    <tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function(){
    'use strict';
    const BASE = '<?=BASE_URL?>';
    const API  = BASE + '/api/finance';
    const IS_SA = <?=json_encode($isSuperAdmin??false)?>;
    const USER_SESSION = <?=json_encode($currentUser->session_type??null)?>;

    let trendChart, typeChart;

    function getSession() {
        if (!IS_SA) return USER_SESSION;
        return document.getElementById('sessionFilter')?.value || null;
    }

    function fmtMoney(v) { return 'RWF ' + Number(v||0).toLocaleString('en',{minimumFractionDigits:0}); }

    async function loadDashboard() {
        try {
            const s   = getSession();
            const url = `${API}?action=dashboard` + (s?`&session=${s}`:'');
            const res = await fetch(url, {credentials:'include'});
            const d   = await res.json();
            if (!d.success) return;
            const { stats, revenue_by_type, monthly_trend, session_split, budgets, recent } = d.data;

            // KPIs
            document.getElementById('totalRevenue') .textContent = fmtMoney(stats.total_revenue);
            document.getElementById('totalExpenses').textContent = fmtMoney(stats.total_expenses);
            document.getElementById('balance')      .textContent = fmtMoney(stats.balance);
            document.getElementById('reservePool')  .textContent = fmtMoney(stats.reserve_pool);
            document.getElementById('pendingReqs')  .textContent = stats.pending_requests;
            document.getElementById('thisMonth')    .textContent = fmtMoney(stats.this_month);

            renderTrend(monthly_trend);
            renderTypeChart(revenue_by_type);
            renderBudgetBars(budgets);
            renderSessionSplit(session_split, stats);
            renderRecent(recent);
        } catch(e) { console.error(e); }
    }

    function renderTrend(data) {
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const amounts = Array(12).fill(0);
        (data||[]).forEach(r => amounts[r.month-1] = parseFloat(r.total||0));
        const ctx = document.getElementById('trendChart').getContext('2d');
        if (trendChart) trendChart.destroy();
        trendChart = new Chart(ctx, {
            type:'line',
            data:{ labels:months, datasets:[{label:'Revenue (RWF)',data:amounts,borderColor:'#377dff',backgroundColor:'rgba(55,125,255,.1)',fill:true,tension:.4,pointRadius:4}]},
            options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{ticks:{callback:v=>'RWF '+v.toLocaleString()}}}}
        });
    }

    function renderTypeChart(data) {
        const labels  = (data||[]).map(r=>r.revenue_type.charAt(0).toUpperCase()+r.revenue_type.slice(1));
        const values  = (data||[]).map(r=>parseFloat(r.total||0));
        const colors  = ['#377dff','#00c9a7','#e8a838','#ed4c78','#6f42c1','#20c997'];
        const ctx = document.getElementById('typeChart').getContext('2d');
        if (typeChart) typeChart.destroy();
        typeChart = new Chart(ctx,{
            type:'doughnut',
            data:{labels, datasets:[{data:values, backgroundColor:colors, hoverOffset:4}]},
            options:{responsive:true, plugins:{legend:{position:'bottom'}}}
        });
    }

    function renderBudgetBars(budgets) {
        const el = document.getElementById('budgetBars');
        if (!budgets||!budgets.length){el.innerHTML='<p class="text-muted text-center py-3">No approved budgets found.</p>';return;}
        el.innerHTML = budgets.map(b => {
            const pct = b.total_amount>0 ? Math.min(100,Math.round(b.spent/b.total_amount*100)) : 0;
            const cls = pct>=90?'bg-danger':pct>=60?'bg-warning':'bg-success';
            return `<div class="mb-3">
              <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold">${esc(b.budget_name)} <small class="text-muted">(${esc(b.cep_session)})</small></span>
                <span class="text-muted small">${pct}%</span>
              </div>
              <div class="progress" style="height:8px">
                <div class="progress-bar ${cls}" role="progressbar" style="width:${pct}%"></div>
              </div>
              <div class="d-flex justify-content-between mt-1">
                <span class="text-muted small">Spent: RWF ${Number(b.spent||0).toLocaleString()}</span>
                <span class="text-muted small">Budget: RWF ${Number(b.total_amount||0).toLocaleString()}</span>
              </div>
            </div>`;
        }).join('');
    }

    function renderSessionSplit(split, stats) {
        const el = document.getElementById('sessionSplit');
        const day     = (split||[]).find(r=>r.cep_session==='day');
        const weekend = (split||[]).find(r=>r.cep_session==='weekend');
        const total   = parseFloat(stats.total_revenue||0);
        const render  = (label, data, color) => {
            const pct = total>0 ? Math.round(parseFloat(data?.total||0)/total*100) : 0;
            return `<div class="mb-3">
              <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold">${label}</span>
                <span>RWF ${Number(data?.total||0).toLocaleString()} <span class="badge bg-soft-${color} text-${color}">${pct}%</span></span>
              </div>
              <div class="progress" style="height:6px">
                <div class="progress-bar bg-${color}" style="width:${pct}%"></div>
              </div>
            </div>`;
        };
        el.innerHTML = render('☀️ Day CEP', day, 'warning') + render('🌙 Weekend CEP', weekend, 'primary');
    }

    function renderRecent(data) {
        const tbody = document.getElementById('recentTbody');
        if (!data||!data.length){tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted">No transactions yet.</td></tr>';return;}
        const typeColors = {offering:'success',tithe:'primary',donation:'info',project:'warning',fundraising:'secondary',other:'dark'};
        tbody.innerHTML = data.map(r=>`<tr>
          <td>${r.revenue_date}</td>
          <td><span class="badge bg-soft-${r.cep_session==='day'?'warning':'primary'} text-${r.cep_session==='day'?'warning':'primary'}">${esc(r.cep_session)}</span></td>
          <td><span class="badge bg-soft-${typeColors[r.revenue_type]||'dark'} text-${typeColors[r.revenue_type]||'dark'}">${esc(r.revenue_type)}</span></td>
          <td class="fw-bold">RWF ${Number(r.amount||0).toLocaleString()}</td>
          <td class="text-muted">${esc(r.description||'—')}</td>
          <td>${esc(r.recorded_by_name||'—')}</td>
        </tr>`).join('');
    }

    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    document.addEventListener('DOMContentLoaded',()=>{
        loadDashboard();
        document.getElementById('sessionFilter')?.addEventListener('change', loadDashboard);
        document.getElementById('trendYear')?.addEventListener('change', loadDashboard);
    });
})();
</script>
</body>
</html>