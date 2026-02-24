<?php
/**
 * Family Detail
 * File: modules/Dashboard/views/family-detail.php
 */
$pageTitle          = 'Family Detail';
$requiredPermission = 'families.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canEdit   = hasPermission($userPermissions, 'families.edit');
$canAssign = hasPermission($userPermissions, 'families.assign');
$familyId  = (int)($_GET['id'] ?? 0);
if (!$familyId) { header('Location: '.url('admin/member-families')); exit; }
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

    <!-- Header (filled by JS) -->
    <div id="familyHeader" class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div id="familyAvatar" class="avatar avatar-xxl avatar-circle d-flex align-items-center justify-content-center text-white fw-bold fs-3" style="width:72px;height:72px;background:#007bff">—</div>
            </div>
            <div class="col">
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter mb-1">
                    <li class="breadcrumb-item"><a href="<?=url('admin/member-families')?>">Families</a></li>
                    <li class="breadcrumb-item active" id="breadName">Loading…</li>
                </ol></nav>
                <h1 class="page-header-title mb-1" id="familyName">Loading…</h1>
                <p class="text-muted fst-italic mb-0" id="familyMotto"></p>
            </div>
            <div class="col-auto" id="familyBadges"></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Info Panel -->
        <div class="col-xl-4">
            <!-- Stats Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-header-title mb-3">Family Stats</h5>
                    <div class="row text-center g-2" id="familyStats">
                        <div class="col-4"><div class="fs-2 fw-bold text-primary" id="stTotal">—</div><small class="text-muted">Members</small></div>
                        <div class="col-4"><div class="fs-2 fw-bold text-info" id="stMale">—</div><small class="text-muted">Male</small></div>
                        <div class="col-4"><div class="fs-2 fw-bold text-danger" id="stFemale">—</div><small class="text-muted">Female</small></div>
                        <div class="col-6"><div class="fs-3 fw-bold text-success mt-2" id="stActive">—</div><small class="text-muted">Active</small></div>
                        <div class="col-6"><div class="fs-3 fw-bold text-warning mt-2" id="stPending">—</div><small class="text-muted">Pending</small></div>
                    </div>
                </div>
            </div>

            <!-- Leadership Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-header-title mb-3">Leadership</h5>
                    <div id="leadershipPanel"><p class="text-muted">Loading…</p></div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-header-title mb-3">Details</h5>
                    <div id="infoPanel"><p class="text-muted">Loading…</p></div>
                    <?php if($canEdit): ?>
                    <button class="btn btn-outline-primary btn-sm mt-3 w-100" onclick="openEditModal()"><i class="bi bi-pencil me-1"></i>Edit Family Info</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Members -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-header-title">Members</h4>
                    <div class="d-flex gap-2">
                        <input type="search" id="memberSearch" class="form-control form-control-sm" style="width:200px" placeholder="Search…">
                        <?php if($canAssign): ?>
                        <button class="btn btn-primary btn-sm" onclick="openAssign()"><i class="bi bi-person-plus me-1"></i>Add</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless table-align-middle card-table">
                        <thead class="thead-light"><tr><th>Member</th><th>Faculty</th><th>Session</th><th>Status</th><?php if($canAssign): ?><th></th><?php endif; ?></tr></thead>
                        <tbody id="membersTbody"><tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Assign Members Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Members to Family</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="input-group mb-3"><input type="search" id="asSearch" class="form-control" placeholder="Search unassigned members…">
                    <button class="btn btn-outline-secondary" onclick="searchUnassigned()"><i class="bi bi-search"></i></button>
                </div>
                <div id="unassignedList" class="row g-2" style="max-height:350px;overflow-y:auto"></div>
                <div class="mt-3"><span class="text-muted small"><span id="selCount">0</span> selected</span></div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" onclick="doAssign()">Add Selected</button></div>
        </div>
    </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const FAMILY_ID = <?=json_encode($familyId)?>;
    const BASE=`<?=BASE_URL?>`, API=BASE+'/api/families';
    const CAN_ASSIGN=<?=json_encode($canAssign)?>;

    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    async function loadFamily(){
        const res=await fetch(`${API}?action=get&id=${FAMILY_ID}`,{credentials:'include'});
        const data=await res.json(); const f=data.data; if(!f) return;
        const color=f.color_code||'#007bff';
        const initials=f.family_name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2);
        document.getElementById('familyAvatar').textContent=initials;
        document.getElementById('familyAvatar').style.background=color;
        document.getElementById('familyName').textContent=f.family_name;
        document.getElementById('breadName').textContent=f.family_name;
        document.getElementById('familyMotto').textContent=f.motto?'"'+f.motto+'"':'';
        const sessBadge=f.cep_session==='day'?'bg-soft-warning text-warning':'bg-soft-primary text-primary';
        document.getElementById('familyBadges').innerHTML=`
          <span class="badge ${sessBadge} fs-6">${f.cep_session==='day'?'☀️ Day CEP':'🌙 Weekend CEP'}</span>
          ${f.family_code?`<span class="badge bg-soft-secondary text-secondary ms-2 fs-6">${esc(f.family_code)}</span>`:''}
        `;
        document.getElementById('leadershipPanel').innerHTML=f.parent_name||f.co_parent_name?`
          ${f.parent_name?`<div class="d-flex align-items-center gap-2 mb-2">
            <div class="avatar avatar-sm avatar-soft-primary avatar-circle"><span class="avatar-initials">${f.parent_name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase()}</span></div>
            <div><div class="fw-semibold">${esc(f.parent_name)}</div><small class="text-muted">Family Parent</small></div>
          </div>`:''}
          ${f.co_parent_name?`<div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-sm avatar-soft-danger avatar-circle"><span class="avatar-initials">${f.co_parent_name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase()}</span></div>
            <div><div class="fw-semibold">${esc(f.co_parent_name)}</div><small class="text-muted">Co-Parent</small></div>
          </div>`:''}
        `:'<p class="text-muted">No leadership assigned.</p>';
        document.getElementById('infoPanel').innerHTML=`
          <dl class="row mb-0">
            <dt class="col-5 text-muted small">Session</dt><dd class="col-7 small text-capitalize">${esc(f.cep_session)}</dd>
            ${f.description?`<dt class="col-5 text-muted small">Description</dt><dd class="col-7 small">${esc(f.description)}</dd>`:''}
            <dt class="col-5 text-muted small">Created</dt><dd class="col-7 small">${esc(f.created_at?.split('T')[0]||'—')}</dd>
          </dl>
        `;
    }

    async function loadMembers(search=''){
        const params=new URLSearchParams({action:'members',family_id:FAMILY_ID}); if(search) params.set('search',search);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json(); const list=data.data||[];
        const stCls=s=>s==='active'?'success':s==='pending'?'warning':'secondary';
        const sesCls=s=>s==='day'?'warning':'primary';
        let active=0,pending=0,male=0,female=0;
        list.forEach(m=>{if(m.status==='active')active++;if(m.status==='pending')pending++;if(m.gender==='male')male++;if(m.gender==='female')female++;});
        document.getElementById('stTotal').textContent  = list.length;
        document.getElementById('stMale').textContent   = male;
        document.getElementById('stFemale').textContent = female;
        document.getElementById('stActive').textContent = active;
        document.getElementById('stPending').textContent= pending;
        document.getElementById('membersTbody').innerHTML=list.length?list.map(m=>{
            const initials=((m.firstname?.[0]??'')+(m.lastname?.[0]??'')).toUpperCase();
            return `<tr>
              <td><div class="d-flex align-items-center gap-2">
                <div class="avatar avatar-sm avatar-soft-primary avatar-circle"><span class="avatar-initials">${esc(initials)}</span></div>
                <div><div class="fw-semibold">${esc(m.firstname)} ${esc(m.lastname)}</div><small class="text-muted">${esc(m.email||'')}</small></div>
              </div></td>
              <td class="text-muted">${esc(m.faculty||'—')}</td>
              <td><span class="badge bg-soft-${sesCls(m.cep_session)} text-${sesCls(m.cep_session)}">${esc(m.cep_session)}</span></td>
              <td><span class="badge bg-soft-${stCls(m.status)} text-${stCls(m.status)} text-capitalize">${esc(m.status)}</span></td>
              ${CAN_ASSIGN?`<td><button class="btn btn-xs btn-ghost-danger" onclick="removeMember(${m.id})" title="Remove"><i class="bi bi-x"></i></button></td>`:''}
            </tr>`;
        }).join(''):'<tr><td colspan="5" class="text-center text-muted py-4">No members yet.</td></tr>';
    }

    window.removeMember = async function(memberId){
        if(!confirm('Remove from family?')) return;
        const res=await fetch(`${API}?action=remove_member`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({member_id:memberId})});
        const data=await res.json();
        if(data.success){loadMembers();showToast('Removed','success');}
        else showToast(data.message||'Failed','danger');
    };

    window.openAssign = async function(){
        new bootstrap.Modal(document.getElementById('assignModal')).show();
        searchUnassigned();
    };
    window.searchUnassigned = async function(){
        const s=document.getElementById('asSearch').value;
        const params=new URLSearchParams({action:'unassigned'}); if(s) params.set('search',s);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json(); const list=data.data||[];
        const el=document.getElementById('unassignedList');
        el.innerHTML=list.length?list.map(m=>`<div class="col-md-4">
          <label class="d-flex align-items-center gap-2 card card-sm p-2" style="cursor:pointer">
            <input type="checkbox" class="assign-chk" value="${m.id}" onchange="document.getElementById('selCount').textContent=document.querySelectorAll('.assign-chk:checked').length">
            <div><div class="small fw-semibold">${esc(m.firstname)} ${esc(m.lastname)}</div><div class="text-muted" style="font-size:.7rem">${esc(m.cep_session)} • ${esc(m.faculty||'—')}</div></div>
          </label>
        </div>`).join(''):'<div class="col-12 text-center text-muted py-3">No unassigned members found.</div>';
    };
    window.doAssign = async function(){
        const ids=Array.from(document.querySelectorAll('.assign-chk:checked')).map(c=>parseInt(c.value));
        if(!ids.length){showToast('Select at least one member','warning');return;}
        const res=await fetch(`${API}?action=assign`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({family_id:FAMILY_ID,member_ids:ids})});
        const data=await res.json();
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('assignModal'))?.hide();loadMembers();showToast(`${data.assigned||ids.length} added!`,'success');}
        else showToast(data.message||'Failed','danger');
    };

    function showToast(msg,type='success'){
        const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;
        document.body.appendChild(t);setTimeout(()=>t.remove(),3000);
    }

    let st;
    document.addEventListener('DOMContentLoaded',()=>{
        loadFamily(); loadMembers();
        document.getElementById('memberSearch')?.addEventListener('input',e=>{clearTimeout(st);st=setTimeout(()=>loadMembers(e.target.value),350);});
    });
})();
</script>
</body></html>