<?php
/**
 * Choir Attendance Management
 * File: modules/Dashboard/views/choir-attendance.php
 */
$pageTitle          = 'Choir Attendance';
$requiredPermission = 'choir.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canManage = hasPermission($userPermissions, 'choir.manage_attendance');
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
            <div class="col-sm"><h1 class="page-header-title">Choir Attendance</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/choir-members')?>">Choir</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol></nav>
            </div>
            <?php if($canManage): ?>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rehearsalModal">
                    <i class="bi bi-calendar-plus me-1"></i> New Rehearsal
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Rehearsals List -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-header-title">Rehearsals</h4>
                    <?php if($isSuperAdmin??false): ?>
                    <select id="fltSession" class="form-select form-select-sm" style="width:140px">
                        <option value="">All Sessions</option><option value="day">☀️ Day</option><option value="weekend">🌙 Weekend</option>
                    </select>
                    <?php endif; ?>
                </div>
                <div class="list-group list-group-flush" id="rehearsalList" style="max-height:70vh;overflow-y:auto">
                    <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
                </div>
                <div id="rPager" class="card-footer d-flex justify-content-center"></div>
            </div>
        </div>

        <!-- Right: Attendance Sheet -->
        <div class="col-xl-8">
            <div class="card" id="attendanceCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-header-title" id="attendanceTitle">Select a Rehearsal</h4>
                    <?php if($canManage): ?>
                    <button id="btnSaveAttendance" class="btn btn-success btn-sm d-none" onclick="saveAttendance()">
                        <i class="bi bi-check-lg me-1"></i> Save Attendance
                    </button>
                    <?php endif; ?>
                </div>
                <div id="attendanceBody" class="card-body">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-calendar-check fs-1 d-block mb-3 text-light"></i>
                        <p>Click a rehearsal on the left to view or record attendance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- New Rehearsal Modal -->
<div class="modal fade" id="rehearsalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Schedule Rehearsal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" id="rDate" class="form-control" value="<?=date('Y-m-d')?>">
                    </div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                        <select id="rSession" class="form-select"><option value="day">Day CEP</option><option value="weekend">Weekend CEP</option></select>
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Location</label>
                        <input type="text" id="rLocation" class="form-control" placeholder="e.g. Main Auditorium" value="Main Auditorium">
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Notes</label>
                        <textarea id="rNotes" class="form-control" rows="2" placeholder="Rehearsal notes or songs practiced…"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnSaveRehearsal" class="btn btn-primary">Schedule</button></div>
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
    let rPage=1,selectedRehearsalId=null;
    function sess(){return IS_SA?(document.getElementById('fltSession')?.value||null):MY_SES;}
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    async function loadRehearsals(page=1){
        rPage=page;
        const params=new URLSearchParams({action:'rehearsals',page,per_page:10});
        const s=sess(); if(s) params.set('session',s);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        const list=data.data||[];
        const el=document.getElementById('rehearsalList');
        if(!list.length){el.innerHTML='<div class="text-center text-muted py-4">No rehearsals yet.</div>';renderRPager(0,1);return;}
        el.innerHTML=list.map(r=>{
            const sesBadge=r.session==='day'?'badge bg-soft-warning text-warning':'badge bg-soft-primary text-primary';
            const pct=r.total_active?Math.round(r.present_count/r.total_active*100):0;
            const pcCls=pct>=80?'success':pct>=60?'warning':'danger';
            return `<button class="list-group-item list-group-item-action px-3 py-3 ${selectedRehearsalId===r.id?'active':''}" onclick="selectRehearsal(${r.id})">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <strong class="small">${esc(r.rehearsal_date)}</strong>
                <span class="${sesBadge} small">${esc(r.session)}</span>
              </div>
              <div class="text-muted smaller mb-1"><i class="bi bi-geo-alt me-1"></i>${esc(r.location||'—')}</div>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-${pcCls}" style="width:${pct}%"></div></div>
                <small class="text-${pcCls}">${r.present_count||0}/${r.total_active||0}</small>
              </div>
            </button>`;
        }).join('');
        renderRPager(data.total,data.pages);
    }

    function renderRPager(total,pages){
        const el=document.getElementById('rPager');
        if(!el||pages<=1){el&&(el.innerHTML='');return;}
        el.innerHTML=`<ul class="pagination pagination-sm mb-0">
          <li class="page-item ${rPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadReh(${rPage-1});return false;">‹</a></li>
          ${Array.from({length:pages},(_,i)=>`<li class="page-item ${rPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadReh(${i+1});return false;">${i+1}</a></li>`).join('')}
          <li class="page-item ${rPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadReh(${rPage+1});return false;">›</a></li>
        </ul>`;
    }
    window.loadReh=loadRehearsals;

    window.selectRehearsal = async function(id){
        selectedRehearsalId=id;
        document.getElementById('attendanceBody').innerHTML='<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        document.getElementById('btnSaveAttendance')?.classList.add('d-none');
        // Re-render list to show active state
        loadRehearsals(rPage);
        const res=await fetch(`${API}?action=attendance&rehearsal_id=${id}`,{credentials:'include'});
        const data=await res.json();
        const members=data.data||[];
        document.getElementById('attendanceTitle').textContent=data.rehearsal_date||'Attendance Sheet';
        if(!members.length){document.getElementById('attendanceBody').innerHTML='<p class="text-muted text-center py-3">No active choir members found.</p>';return;}
        const stColors={present:'success',absent:'danger',excused:'warning',late:'info'};
        document.getElementById('attendanceBody').innerHTML=`
          <div class="row g-2 mb-3">
            ${['present','late','excused','absent'].map(st=>`<div class="col-auto">
              <button class="btn btn-sm btn-outline-${stColors[st]}" onclick="markAll('${st}')">All ${st.charAt(0).toUpperCase()+st.slice(1)}</button>
            </div>`).join('')}
          </div>
          <div class="table-responsive"><table class="table table-sm table-borderless">
            <thead class="thead-light"><tr><th>Member</th><th>Voice</th><th style="width:220px">Status</th></tr></thead>
            <tbody>${members.map(m=>{
              const voiceColors={soprano:'danger',alto:'warning',tenor:'info',bass:'dark'};
              const vc=voiceColors[m.voice_part]||'secondary';
              const initials=((m.full_name||'').split(' ').map(w=>w[0]).join('').slice(0,2)||'??').toUpperCase();
              return `<tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-xs avatar-soft-${vc} avatar-circle"><span class="avatar-initials">${esc(initials)}</span></div>
                    <span class="fw-semibold small">${esc(m.full_name)}</span>
                  </div>
                </td>
                <td><span class="badge bg-soft-${vc} text-${vc} small text-capitalize">${esc(m.voice_part)}</span></td>
                <td>
                  <div class="btn-group btn-group-sm" role="group" id="att-${m.id}">
                    ${['present','late','excused','absent'].map(st=>`
                    <button type="button" class="btn btn-outline-${stColors[st]} ${m.status===st?'active':''}" onclick="setAtt(${m.id},'${st}',this)">${st.charAt(0).toUpperCase()+st.slice(1)}</button>`).join('')}
                  </div>
                </td>
              </tr>`;
            }).join('')}</tbody>
          </table></div>`;
        if(CAN_MANAGE) document.getElementById('btnSaveAttendance')?.classList.remove('d-none');
    };

    window.setAtt = function(memberId, status, btn){
        const grp=document.getElementById(`att-${memberId}`);
        grp.querySelectorAll('button').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
    };

    window.markAll = function(status){
        document.querySelectorAll('[id^="att-"]').forEach(grp=>{
            grp.querySelectorAll('button').forEach(b=>b.classList.remove('active'));
            const target=[...grp.querySelectorAll('button')].find(b=>b.textContent.trim().toLowerCase()===status);
            if(target) target.classList.add('active');
        });
    };

    window.saveAttendance = async function(){
        if(!selectedRehearsalId) return;
        const attendance=[];
        document.querySelectorAll('[id^="att-"]').forEach(grp=>{
            const memberId=parseInt(grp.id.replace('att-',''));
            const active=grp.querySelector('button.active');
            if(active) attendance.push({choir_member_id:memberId,status:active.textContent.trim().toLowerCase()});
        });
        const btn=document.getElementById('btnSaveAttendance');
        btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
        const res=await fetch(`${API}?action=save_attendance`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({rehearsal_id:selectedRehearsalId,attendance})});
        const data=await res.json();
        btn.disabled=false;btn.innerHTML='<i class="bi bi-check-lg me-1"></i>Save Attendance';
        if(data.success){loadRehearsals(rPage);showToast('Attendance saved!','success');}
        else showToast(data.message||'Failed','danger');
    };

    async function createRehearsal(){
        const payload={rehearsal_date:document.getElementById('rDate').value,session:document.getElementById('rSession').value,location:document.getElementById('rLocation').value,notes:document.getElementById('rNotes').value};
        const res=await fetch(`${API}?action=create_rehearsal`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('rehearsalModal'))?.hide();loadRehearsals(1);showToast('Rehearsal scheduled!','success');}
        else showToast(data.message||'Failed','danger');
    }

    function showToast(msg,type='success'){
        const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;
        document.body.appendChild(t);setTimeout(()=>t.remove(),3000);
    }

    document.addEventListener('DOMContentLoaded',()=>{
        loadRehearsals();
        document.getElementById('fltSession')?.addEventListener('change',()=>loadRehearsals(1));
        document.getElementById('btnSaveRehearsal')?.addEventListener('click',createRehearsal);
    });
})();
</script>
</body></html>