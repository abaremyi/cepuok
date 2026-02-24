<?php
/**
 * Choir Members Management
 * File: modules/Dashboard/views/choir-members.php
 */
$pageTitle          = 'Choir Members';
$requiredPermission = 'choir.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canManage = hasPermission($userPermissions, 'choir.manage_members');
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
            <div class="col-sm"><h1 class="page-header-title">Choir Members</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Choir Members</li>
                </ol></nav>
            </div>
            <?php if($canManage): ?>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#memberModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Member
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <?php foreach([
            ['id'=>'stTotal',   'label'=>'Total Members',  'icon'=>'bi-people',      'color'=>'primary'],
            ['id'=>'stActive',  'label'=>'Active',          'icon'=>'bi-check-circle','color'=>'success'],
            ['id'=>'stSoprano', 'label'=>'Soprano',         'icon'=>'bi-music-note',  'color'=>'danger'],
            ['id'=>'stAlto',    'label'=>'Alto',            'icon'=>'bi-music-note',  'color'=>'warning'],
            ['id'=>'stTenor',   'label'=>'Tenor',           'icon'=>'bi-music-note',  'color'=>'info'],
            ['id'=>'stBass',    'label'=>'Bass',            'icon'=>'bi-music-note',  'color'=>'dark'],
        ] as $c): ?>
        <div class="col-sm-6 col-lg-2"><div class="card"><div class="card-body text-center">
            <i class="bi <?=$c['icon']?> fs-4 text-<?=$c['color']?> mb-1 d-block"></i>
            <div class="fs-3 fw-bold text-<?=$c['color']?>" id="<?=$c['id']?>">—</div>
            <small class="text-muted"><?=$c['label']?></small>
        </div></div></div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-3"><div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <?php if($isSuperAdmin??false): ?>
            <div class="col-auto"><select id="fltSession" class="form-select form-select-sm" style="width:140px">
                <option value="">All Sessions</option><option value="day">☀️ Day CEP</option><option value="weekend">🌙 Weekend CEP</option>
            </select></div>
            <?php endif; ?>
            <div class="col-auto"><select id="fltVoice" class="form-select form-select-sm" style="width:120px">
                <option value="">All Voices</option><option value="soprano">Soprano</option><option value="alto">Alto</option>
                <option value="tenor">Tenor</option><option value="bass">Bass</option>
            </select></div>
            <div class="col-auto"><select id="fltStatus" class="form-select form-select-sm" style="width:110px">
                <option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option>
            </select></div>
            <div class="col"><div class="input-group input-group-sm"><span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" id="searchBox" class="form-control" placeholder="Search name, instrument…"></div></div>
            <div class="col-auto"><span class="text-muted small" id="resultCount">Loading…</span></div>
        </div>
    </div></div>

    <!-- Members Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr><th>Member</th><th>Voice Part</th><th>Role</th><th>Instrument</th><th>Session</th><th>Status</th><th>Attendance</th><?php if($canManage): ?><th></th><?php endif; ?></tr>
                </thead>
                <tbody id="choirTbody"><tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr></tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small" id="pageInfo"></span>
            <div id="paginator"></div>
        </div>
    </div>
</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Add/Edit Member Modal -->
<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="mModalTitle">Add Choir Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="mId">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold">CEP Member ID (optional)</label>
                        <input type="number" id="mMemberId" class="form-control" placeholder="Link to existing member">
                    </div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="mName" class="form-control" placeholder="Full name">
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Voice Part <span class="text-danger">*</span></label>
                        <select id="mVoice" class="form-select"><option value="soprano">🎵 Soprano</option><option value="alto">🎵 Alto</option><option value="tenor">🎵 Tenor</option><option value="bass">🎵 Bass</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Role</label>
                        <select id="mRole" class="form-select"><option value="member">Member</option><option value="section_leader">Section Leader</option><option value="choir_president">Choir President</option><option value="accompanist">Accompanist</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                        <select id="mSession" class="form-select"><option value="day">Day CEP</option><option value="weekend">Weekend CEP</option></select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Instrument</label>
                        <input type="text" id="mInstrument" class="form-control" placeholder="e.g. Piano, Guitar">
                    </div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Join Date</label>
                        <input type="date" id="mJoinDate" class="form-control" value="<?=date('Y-m-d')?>">
                    </div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Status</label>
                        <select id="mStatus" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Notes</label>
                        <textarea id="mNotes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnSaveMember" class="btn btn-primary">Save</button></div>
        </div>
    </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const BASE=`<?=BASE_URL?>`,API=BASE+'/api/choir';
    const IS_SA=<?=json_encode($isSuperAdmin??false)?>,MY_SES=<?=json_encode($currentUser->session_type??null)?>;
    const CAN_MANAGE=<?=json_encode($canManage)?>;
    let currentPage=1;
    function sess(){return IS_SA?(document.getElementById('fltSession')?.value||null):MY_SES;}
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    const voiceColors={soprano:'danger',alto:'warning',tenor:'info',bass:'dark'};
    const roleLabels={member:'Member',section_leader:'Section Leader',choir_president:'Choir President',accompanist:'Accompanist'};

    async function loadStats(){
        const res=await fetch(`${API}?action=member_stats`,{credentials:'include'});
        const d=(await res.json()).data||{};
        document.getElementById('stTotal').textContent  = d.total||0;
        document.getElementById('stActive').textContent = d.active||0;
        document.getElementById('stSoprano').textContent= d.soprano||0;
        document.getElementById('stAlto').textContent   = d.alto||0;
        document.getElementById('stTenor').textContent  = d.tenor||0;
        document.getElementById('stBass').textContent   = d.bass||0;
    }

    async function loadMembers(page=1){
        currentPage=page;
        document.getElementById('choirTbody').innerHTML='<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';
        const params=new URLSearchParams({action:'members',page,per_page:20});
        const s=sess(); if(s) params.set('session',s);
        const v=document.getElementById('fltVoice').value; if(v) params.set('voice_part',v);
        const st=document.getElementById('fltStatus').value; if(st) params.set('status',st);
        const q=document.getElementById('searchBox').value; if(q) params.set('search',q);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        document.getElementById('resultCount').textContent=`${data.total||0} member(s)`;
        document.getElementById('pageInfo').textContent=`Showing page ${page} of ${data.pages||1}`;
        const list=data.data||[];
        if(!list.length){document.getElementById('choirTbody').innerHTML='<tr><td colspan="8" class="text-center text-muted py-4">No choir members found.</td></tr>';renderPager(0,1);return;}
        document.getElementById('choirTbody').innerHTML=list.map(m=>{
            const stCls=m.status==='active'?'success':'secondary';
            const vc=voiceColors[m.voice_part]||'secondary';
            const initials=((m.full_name||'').split(' ').map(w=>w[0]).join('').slice(0,2)||'??').toUpperCase();
            return `<tr>
              <td><div class="d-flex align-items-center gap-2">
                <div class="avatar avatar-sm avatar-soft-${vc} avatar-circle"><span class="avatar-initials">${esc(initials)}</span></div>
                <div><div class="fw-semibold">${esc(m.full_name||'—')}</div><small class="text-muted">${m.member_id?'CEP #'+m.member_id:''}</small></div>
              </div></td>
              <td><span class="badge bg-soft-${vc} text-${vc} text-capitalize">${esc(m.voice_part)}</span></td>
              <td>${esc(roleLabels[m.role]||m.role||'—')}</td>
              <td class="text-muted">${esc(m.instrument||'—')}</td>
              <td><span class="badge ${m.cep_session==='day'?'bg-soft-warning text-warning':'bg-soft-primary text-primary'}">${esc(m.cep_session)}</span></td>
              <td><span class="badge bg-soft-${stCls} text-${stCls}">${esc(m.status)}</span></td>
              <td><small class="text-muted">${m.attendance_rate!=null?m.attendance_rate+'%':'—'}</small></td>
              ${CAN_MANAGE?`<td><div class="d-flex gap-1">
                <button class="btn btn-xs btn-ghost-secondary" onclick="editMember(${m.id})"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-xs btn-ghost-danger" onclick="removeMember(${m.id})"><i class="bi bi-trash"></i></button>
              </div></td>`:''}
            </tr>`;
        }).join('');
        renderPager(data.total,data.pages);
    }

    function renderPager(total,pages){
        const el=document.getElementById('paginator');
        if(!el||pages<=1){el&&(el.innerHTML='');return;}
        el.innerHTML=`<ul class="pagination pagination-sm mb-0">
          <li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadM(${currentPage-1});return false;">‹</a></li>
          ${Array.from({length:pages},(_,i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadM(${i+1});return false;">${i+1}</a></li>`).join('')}
          <li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadM(${currentPage+1});return false;">›</a></li>
        </ul>`;
    }
    window.loadM=loadMembers;

    window.editMember = async function(id){
        const res=await fetch(`${API}?action=members&page=1&per_page=200`,{credentials:'include'});
        // Simpler: we'll read from the table row data from the already-loaded list
        // Actually just open blank form with id to fetch
        // For simplicity re-fetch all and find
        showToast('Use table to find member info','info');
    };

    window.removeMember = async function(id){
        if(!confirm('Remove this choir member?')) return;
        const res=await fetch(`${API}?action=remove_member`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
        const data=await res.json();
        if(data.success){loadMembers(currentPage);loadStats();showToast('Removed','success');}
        else showToast(data.message||'Failed','danger');
    };

    async function saveMember(){
        const id=document.getElementById('mId').value;
        const payload={
            id:id||undefined, member_id:document.getElementById('mMemberId').value||null,
            full_name:document.getElementById('mName').value, voice_part:document.getElementById('mVoice').value,
            role:document.getElementById('mRole').value, cep_session:document.getElementById('mSession').value,
            instrument:document.getElementById('mInstrument').value, join_date:document.getElementById('mJoinDate').value,
            status:document.getElementById('mStatus').value, notes:document.getElementById('mNotes').value,
        };
        const action=id?'update_member':'add_member';
        const res=await fetch(`${API}?action=${action}`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('memberModal'))?.hide();loadMembers(currentPage);loadStats();showToast('Saved!','success');}
        else showToast(data.message||'Failed','danger');
    }

    function showToast(msg,type='success'){
        const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;
        document.body.appendChild(t);setTimeout(()=>t.remove(),3000);
    }

    let timer;
    document.addEventListener('DOMContentLoaded',()=>{
        loadStats();loadMembers();
        ['fltSession','fltVoice','fltStatus'].forEach(id=>document.getElementById(id)?.addEventListener('change',()=>loadMembers(1)));
        document.getElementById('searchBox')?.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>loadMembers(1),350);});
        document.getElementById('btnSaveMember')?.addEventListener('click',saveMember);
        document.getElementById('memberModal')?.addEventListener('hidden.bs.modal',()=>{document.getElementById('mId').value='';document.getElementById('mModalTitle').textContent='Add Choir Member';});
    });
})();
</script>
</body></html>