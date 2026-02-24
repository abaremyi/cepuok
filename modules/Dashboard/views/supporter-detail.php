<?php
/**
 * Supporter Detail
 * File: modules/Dashboard/views/supporter-detail.php
 */
$pageTitle          = 'Supporter Profile';
$requiredPermission = 'supporters.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canEdit    = hasPermission($userPermissions, 'supporters.edit');
$canContrib = hasPermission($userPermissions, 'supporters.contributions');
$supporterId = (int)($_GET['id'] ?? 0);
if (!$supporterId) { header('Location: '.url('admin/supporters-management')); exit; }
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

    <div id="supporterProfile" class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div id="suppAvatar" class="avatar avatar-xxl avatar-soft-primary avatar-circle" style="width:72px;height:72px;font-size:1.5rem">
                    <span class="avatar-initials" id="suppInitials">—</span>
                </div>
            </div>
            <div class="col">
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter mb-1">
                    <li class="breadcrumb-item"><a href="<?=url('admin/supporters-management')?>">Supporters</a></li>
                    <li class="breadcrumb-item active" id="breadName">Loading…</li>
                </ol></nav>
                <h1 class="page-header-title mb-1" id="suppName">Loading…</h1>
                <div id="suppMeta" class="d-flex flex-wrap gap-2"></div>
            </div>
            <div class="col-auto">
                <div class="text-center">
                    <div class="fs-1 fw-bold text-success" id="totalContrib">—</div>
                    <small class="text-muted">Total Contributed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <!-- Contact Info -->
            <div class="card mb-3"><div class="card-body">
                <h5 class="card-header-title mb-3">Contact Information</h5>
                <div id="contactPanel"><p class="text-muted">Loading…</p></div>
                <?php if($canEdit): ?><a href="<?=url('admin/supporters-management')?>" class="btn btn-outline-secondary btn-sm mt-2 w-100"><i class="bi bi-pencil me-1"></i>Edit in Supporters List</a><?php endif; ?>
            </div></div>
            <!-- Quick Add Contribution -->
            <?php if($canContrib): ?>
            <div class="card"><div class="card-body">
                <h5 class="card-header-title mb-3">Add Contribution</h5>
                <div class="row g-2">
                    <div class="col-12"><select id="cType" class="form-select form-select-sm"><option value="financial">💰 Financial</option><option value="material">📦 Material</option><option value="service">🙌 Service</option><option value="prayer">🙏 Prayer</option><option value="mentorship">📚 Mentorship</option></select></div>
                    <div class="col-6"><input type="number" id="cAmount" class="form-control form-control-sm" placeholder="Amount (RWF)"></div>
                    <div class="col-6"><input type="date" id="cDate" class="form-control form-control-sm" value="<?=date('Y-m-d')?>"></div>
                    <div class="col-12"><input type="text" id="cDesc" class="form-control form-control-sm" placeholder="Description…"></div>
                    <div class="col-12"><button class="btn btn-success btn-sm w-100" onclick="addContrib()"><i class="bi bi-plus me-1"></i>Add</button></div>
                </div>
            </div></div>
            <?php endif; ?>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Contribution History</h4></div>
                <div class="table-responsive">
                    <table class="table table-borderless table-align-middle card-table">
                        <thead class="thead-light"><tr><th>Date</th><th>Type</th><th>Amount</th><th>Description</th><th>Recorded By</th></tr></thead>
                        <tbody id="contribTbody"><tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>
<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    const SUPP_ID=<?=json_encode($supporterId)?>;
    const BASE=`<?=BASE_URL?>`,API=BASE+'/api/supporters';
    const CAN_CONTRIB=<?=json_encode($canContrib)?>;
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
    const typeColors={financial:'success',material:'info',service:'primary',prayer:'warning',mentorship:'secondary'};
    const tierIcons={bronze:'🥉',silver:'🥈',gold:'🥇',platinum:'💎'};
    const typeColors2={alumni:'success',external:'info',choir:'warning',organization:'primary'};

    async function loadSupporter(){
        const res=await fetch(`${API}?action=get&id=${SUPP_ID}`,{credentials:'include'});
        const data=await res.json(); const s=data.data; if(!s) return;
        const name=s.supporter_type==='organization'&&s.organization_name?s.organization_name:`${s.firstname} ${s.lastname}`;
        const initials=((s.firstname?.[0]??'')+(s.lastname?.[0]??'')).toUpperCase()||'??';
        document.getElementById('suppInitials').textContent=initials;
        document.getElementById('suppName').textContent=name;
        document.getElementById('breadName').textContent=name;
        document.getElementById('totalContrib').textContent='RWF '+Number(s.total_contributed||0).toLocaleString();
        const stCls=s.status==='active'?'success':'secondary';
        const tc=typeColors2[s.supporter_type]||'primary';
        document.getElementById('suppMeta').innerHTML=`
          <span class="badge bg-soft-${tc} text-${tc} text-capitalize">${esc(s.supporter_type)}</span>
          <span class="badge bg-soft-${stCls} text-${stCls} text-capitalize">${esc(s.status)}</span>
          <span class="fs-5">${tierIcons[s.tier]||'🥉'}</span>
          ${s.is_alumni?'<span class="badge bg-soft-info text-info">Alumni</span>':''}
        `;
        document.getElementById('contactPanel').innerHTML=`<dl class="row mb-0">
          ${s.email?`<dt class="col-4 text-muted small">Email</dt><dd class="col-8 small">${esc(s.email)}</dd>`:''}
          ${s.phone?`<dt class="col-4 text-muted small">Phone</dt><dd class="col-8 small">${esc(s.phone)}</dd>`:''}
          ${s.address?`<dt class="col-4 text-muted small">Address</dt><dd class="col-8 small">${esc(s.address)}</dd>`:''}
          <dt class="col-4 text-muted small">Session</dt><dd class="col-8 small text-capitalize">${esc(s.cep_session||'—')}</dd>
          ${s.graduation_year?`<dt class="col-4 text-muted small">Grad Year</dt><dd class="col-8 small">${s.graduation_year}</dd>`:''}
          ${s.notes?`<dt class="col-4 text-muted small">Notes</dt><dd class="col-8 small">${esc(s.notes)}</dd>`:''}
        </dl>`;
        const list=s.contributions||[];
        document.getElementById('contribTbody').innerHTML=list.length?list.map(c=>`<tr>
          <td class="text-muted">${esc(c.contribution_date)}</td>
          <td><span class="badge bg-soft-${typeColors[c.contribution_type]||'secondary'} text-${typeColors[c.contribution_type]||'secondary'} text-capitalize">${esc(c.contribution_type)}</span></td>
          <td class="fw-semibold text-success">${c.amount?'RWF '+Number(c.amount).toLocaleString():'—'}</td>
          <td class="text-muted">${esc(c.description||'—')}</td>
          <td class="text-muted">${esc(c.recorded_by_name||'—')}</td>
        </tr>`).join(''):'<tr><td colspan="5" class="text-center text-muted py-4">No contributions yet.</td></tr>';
    }

    window.addContrib = async function(){
        const payload={supporter_id:SUPP_ID,contribution_type:document.getElementById('cType').value,amount:document.getElementById('cAmount').value||null,description:document.getElementById('cDesc').value,contribution_date:document.getElementById('cDate').value};
        const res=await fetch(`${API}?action=add_contribution`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){document.getElementById('cAmount').value='';document.getElementById('cDesc').value='';loadSupporter();showToast('Added!','success');}
        else showToast(data.message||'Failed','danger');
    };

    function showToast(msg,type='success'){const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;document.body.appendChild(t);setTimeout(()=>t.remove(),3000);}
    document.addEventListener('DOMContentLoaded',loadSupporter);
})();
</script>
</body></html>