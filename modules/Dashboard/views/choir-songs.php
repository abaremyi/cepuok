<?php
/**
 * Choir Songs Management
 * File: modules/Dashboard/views/choir-songs.php
 */
$pageTitle          = 'Choir Songs';
$requiredPermission = 'choir.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canManage = hasPermission($userPermissions, 'choir.manage_songs');
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
            <div class="col-sm"><h1 class="page-header-title">Song Repertoire</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/choir-members')?>">Choir</a></li>
                    <li class="breadcrumb-item active">Songs</li>
                </ol></nav>
            </div>
            <?php if($canManage): ?>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#songModal">
                    <i class="bi bi-music-note-plus me-1"></i> Add Song
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php foreach([
            ['id'=>'stTotal',   'label'=>'Total Songs',   'color'=>'primary'],
            ['id'=>'stActive',  'label'=>'Active',         'color'=>'success'],
            ['id'=>'stLearning','label'=>'Learning',       'color'=>'warning'],
            ['id'=>'stWorship', 'label'=>'Worship',        'color'=>'info'],
            ['id'=>'stPraise',  'label'=>'Praise',         'color'=>'danger'],
        ] as $c): ?>
        <div class="col"><div class="card"><div class="card-body text-center">
            <div class="fs-2 fw-bold text-<?=$c['color']?>" id="<?=$c['id']?>">—</div>
            <small class="text-muted"><?=$c['label']?></small>
        </div></div></div>
        <?php endforeach; ?>
    </div>

    <!-- Filters + View Toggle -->
    <div class="card mb-3"><div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-auto"><select id="fltCategory" class="form-select form-select-sm" style="width:130px">
                <option value="">All Categories</option><option value="worship">Worship</option><option value="praise">Praise</option>
                <option value="anthem">Anthem</option><option value="christmas">Christmas</option><option value="easter">Easter</option>
            </select></div>
            <div class="col-auto"><select id="fltStatus" class="form-select form-select-sm" style="width:120px">
                <option value="">All Status</option><option value="active">Active</option><option value="learning">Learning</option><option value="archived">Archived</option>
            </select></div>
            <div class="col-auto"><select id="fltLang" class="form-select form-select-sm" style="width:120px">
                <option value="">All Languages</option><option value="English">English</option><option value="Kinyarwanda">Kinyarwanda</option><option value="French">French</option><option value="Swahili">Swahili</option>
            </select></div>
            <div class="col"><div class="input-group input-group-sm"><span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" id="searchBox" class="form-control" placeholder="Search title, composer…">
            </div></div>
            <div class="col-auto">
                <div class="btn-group btn-group-sm">
                    <button id="btnGrid" class="btn btn-outline-primary active" onclick="setView('grid')"><i class="bi bi-grid"></i></button>
                    <button id="btnList" class="btn btn-outline-primary" onclick="setView('list')"><i class="bi bi-list"></i></button>
                </div>
            </div>
        </div>
    </div></div>

    <!-- Songs Grid (default) -->
    <div id="songsGrid" class="row g-3"></div>

    <!-- Songs List (alternate) -->
    <div id="songsList" class="card" style="display:none">
        <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-align-middle card-table">
                <thead class="thead-light"><tr><th>Title</th><th>Composer</th><th>Category</th><th>Language</th><th>Key</th><th>Times Performed</th><th>Status</th><?php if($canManage): ?><th></th><?php endif; ?></tr></thead>
                <tbody id="songsTbody"></tbody>
            </table>
        </div>
    </div>

    <div id="paginator" class="d-flex justify-content-center mt-3"></div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Add/Edit Song Modal -->
<div class="modal fade" id="songModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="sModalTitle">Add Song</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="sId">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label fw-semibold">Title <span class="text-danger">*</span></label><input type="text" id="sTitle" class="form-control" placeholder="Song title"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Status</label>
                        <select id="sStatus" class="form-select"><option value="active">Active</option><option value="learning">Learning</option><option value="archived">Archived</option></select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Composer</label><input type="text" id="sComposer" class="form-control" placeholder="Composer name"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Arranger</label><input type="text" id="sArranger" class="form-control" placeholder="Arranger name"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Category</label>
                        <select id="sCategory" class="form-select"><option value="worship">Worship</option><option value="praise">Praise</option><option value="anthem">Anthem</option><option value="christmas">Christmas</option><option value="easter">Easter</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Language</label>
                        <select id="sLang" class="form-select"><option value="English">English</option><option value="Kinyarwanda">Kinyarwanda</option><option value="French">French</option><option value="Swahili">Swahili</option><option value="Latin">Latin</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Key Signature</label><input type="text" id="sKey" class="form-control" placeholder="e.g. G Major"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Tempo (BPM)</label><input type="number" id="sTempo" class="form-control" placeholder="e.g. 120"></div>
                    <div class="col-md-8"><label class="form-label fw-semibold">YouTube URL</label><input type="url" id="sYoutube" class="form-control" placeholder="https://youtube.com/watch?v=…"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Lyrics / Notes</label><textarea id="sLyrics" class="form-control" rows="3" placeholder="Lyrics or rehearsal notes…"></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnSaveSong" class="btn btn-primary">Save Song</button></div>
        </div>
    </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const BASE=`<?=BASE_URL?>`,API=BASE+'/api/choir';
    const CAN_MANAGE=<?=json_encode($canManage)?>;
    let currentPage=1,viewMode='grid';
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    const catColors={worship:'primary',praise:'success',anthem:'info',christmas:'danger',easter:'warning'};
    const catIcons={worship:'🙏',praise:'🎉',anthem:'🏛️',christmas:'🎄',easter:'✝️'};

    async function loadStats(){
        const res=await fetch(`${API}?action=song_stats`,{credentials:'include'});
        const d=(await res.json()).data||{};
        document.getElementById('stTotal').textContent   = d.total||0;
        document.getElementById('stActive').textContent  = d.active||0;
        document.getElementById('stLearning').textContent= d.learning||0;
        document.getElementById('stWorship').textContent = d.worship||0;
        document.getElementById('stPraise').textContent  = d.praise||0;
    }

    async function loadSongs(page=1){
        currentPage=page;
        const params=new URLSearchParams({action:'songs',page,per_page:12});
        const c=document.getElementById('fltCategory').value; if(c) params.set('category',c);
        const s=document.getElementById('fltStatus').value; if(s) params.set('status',s);
        const l=document.getElementById('fltLang').value; if(l) params.set('language',l);
        const q=document.getElementById('searchBox').value; if(q) params.set('search',q);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        const list=data.data||[];
        if(viewMode==='grid') renderGrid(list);
        else renderList(list);
        renderPager(data.total,data.pages);
    }

    function renderGrid(list){
        const el=document.getElementById('songsGrid');
        if(!list.length){el.innerHTML='<div class="col-12 text-center text-muted py-5">No songs in repertoire.</div>';return;}
        el.innerHTML=list.map(s=>{
            const cc=catColors[s.category]||'secondary';
            const stCls=s.status==='active'?'success':s.status==='learning'?'warning':'secondary';
            const ytBtn=s.youtube_url?`<a href="${esc(s.youtube_url)}" target="_blank" class="btn btn-xs btn-ghost-danger"><i class="bi bi-youtube"></i></a>`:'';
            return `<div class="col-md-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="fs-2">${catIcons[s.category]||'🎵'}</span>
                    <span class="badge bg-soft-${stCls} text-${stCls} text-capitalize">${esc(s.status)}</span>
                  </div>
                  <h6 class="fw-bold mb-1">${esc(s.title)}</h6>
                  <p class="text-muted small mb-2">${esc(s.composer||'Unknown composer')}</p>
                  <div class="d-flex flex-wrap gap-1 mb-3">
                    <span class="badge bg-soft-${cc} text-${cc} text-capitalize">${esc(s.category)}</span>
                    <span class="badge bg-soft-secondary text-secondary">${esc(s.language||'')}</span>
                    ${s.key_signature?`<span class="badge bg-soft-dark text-dark">${esc(s.key_signature)}</span>`:''}
                  </div>
                  <div class="d-flex align-items-center justify-content-between">
                    <small class="text-muted"><i class="bi bi-mic me-1"></i>${s.times_performed||0}× performed</small>
                    <div class="d-flex gap-1">
                      ${ytBtn}
                      ${CAN_MANAGE?`<button class="btn btn-xs btn-ghost-secondary" onclick="editSong(${s.id})"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-xs btn-ghost-danger" onclick="deleteSong(${s.id})"><i class="bi bi-trash"></i></button>`:''}
                    </div>
                  </div>
                </div>
              </div>
            </div>`;
        }).join('');
    }

    function renderList(list){
        const tb=document.getElementById('songsTbody');
        if(!list.length){tb.innerHTML='<tr><td colspan="8" class="text-center text-muted py-4">No songs found.</td></tr>';return;}
        const cc=s=>catColors[s]||'secondary';
        const stCls=s=>s==='active'?'success':s==='learning'?'warning':'secondary';
        tb.innerHTML=list.map(s=>`<tr>
          <td><div class="fw-semibold">${esc(s.title)}</div>${s.youtube_url?`<a href="${esc(s.youtube_url)}" target="_blank" class="text-danger small"><i class="bi bi-youtube me-1"></i>YouTube</a>`:''}</td>
          <td class="text-muted">${esc(s.composer||'—')}</td>
          <td><span class="badge bg-soft-${cc(s.category)} text-${cc(s.category)} text-capitalize">${esc(s.category)}</span></td>
          <td>${esc(s.language||'—')}</td>
          <td class="text-muted">${esc(s.key_signature||'—')}</td>
          <td class="text-muted text-center">${s.times_performed||0}</td>
          <td><span class="badge bg-soft-${stCls(s.status)} text-${stCls(s.status)} text-capitalize">${esc(s.status)}</span></td>
          ${CAN_MANAGE?`<td><div class="d-flex gap-1">
            <button class="btn btn-xs btn-ghost-secondary" onclick="editSong(${s.id})"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-xs btn-ghost-danger" onclick="deleteSong(${s.id})"><i class="bi bi-trash"></i></button>
          </div></td>`:''}
        </tr>`).join('');
    }

    function renderPager(total,pages){
        const el=document.getElementById('paginator');
        if(!el||pages<=1){el&&(el.innerHTML='');return;}
        el.innerHTML=`<ul class="pagination pagination-sm"><li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadS(${currentPage-1});return false;">‹</a></li>
          ${Array.from({length:pages},(_,i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadS(${i+1});return false;">${i+1}</a></li>`).join('')}
          <li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadS(${currentPage+1});return false;">›</a></li></ul>`;
    }
    window.loadS=loadSongs;

    window.setView=function(mode){
        viewMode=mode;
        document.getElementById('songsGrid').style.display=mode==='grid'?'':'none';
        document.getElementById('songsList').style.display=mode==='list'?'':'none';
        document.getElementById('btnGrid').classList.toggle('active',mode==='grid');
        document.getElementById('btnList').classList.toggle('active',mode==='list');
        loadSongs(1);
    };

    window.editSong = async function(id){
        // Re-load from API to populate form
        const params=new URLSearchParams({action:'songs',page:1,per_page:200});
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        const s=(data.data||[]).find(x=>x.id===id); if(!s) return;
        document.getElementById('sId').value      = s.id;
        document.getElementById('sTitle').value   = s.title;
        document.getElementById('sStatus').value  = s.status;
        document.getElementById('sComposer').value= s.composer||'';
        document.getElementById('sArranger').value= s.arranger||'';
        document.getElementById('sCategory').value= s.category;
        document.getElementById('sLang').value    = s.language||'English';
        document.getElementById('sKey').value     = s.key_signature||'';
        document.getElementById('sTempo').value   = s.tempo||'';
        document.getElementById('sYoutube').value = s.youtube_url||'';
        document.getElementById('sLyrics').value  = s.lyrics||'';
        document.getElementById('sModalTitle').textContent='Edit Song';
        new bootstrap.Modal(document.getElementById('songModal')).show();
    };

    window.deleteSong = async function(id){
        if(!confirm('Delete this song from repertoire?')) return;
        const res=await fetch(`${API}?action=delete_song`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
        const data=await res.json();
        if(data.success){loadSongs(currentPage);loadStats();showToast('Song deleted','success');}
        else showToast(data.message||'Failed','danger');
    };

    async function saveSong(){
        const id=document.getElementById('sId').value;
        const payload={
            id:id||undefined, title:document.getElementById('sTitle').value,
            status:document.getElementById('sStatus').value, composer:document.getElementById('sComposer').value,
            arranger:document.getElementById('sArranger').value, category:document.getElementById('sCategory').value,
            language:document.getElementById('sLang').value, key_signature:document.getElementById('sKey').value,
            tempo:document.getElementById('sTempo').value||null, youtube_url:document.getElementById('sYoutube').value,
            lyrics:document.getElementById('sLyrics').value,
        };
        const action=id?'update_song':'add_song';
        const res=await fetch(`${API}?action=${action}`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('songModal'))?.hide();loadSongs(currentPage);loadStats();showToast('Saved!','success');}
        else showToast(data.message||'Failed','danger');
    }

    function showToast(msg,type='success'){
        const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;
        document.body.appendChild(t);setTimeout(()=>t.remove(),3000);
    }

    let timer;
    document.addEventListener('DOMContentLoaded',()=>{
        loadStats();loadSongs();
        ['fltCategory','fltStatus','fltLang'].forEach(id=>document.getElementById(id)?.addEventListener('change',()=>loadSongs(1)));
        document.getElementById('searchBox')?.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>loadSongs(1),350);});
        document.getElementById('btnSaveSong')?.addEventListener('click',saveSong);
        document.getElementById('songModal')?.addEventListener('hidden.bs.modal',()=>{document.getElementById('sId').value='';document.getElementById('sModalTitle').textContent='Add Song';});
    });
})();
</script>
</body></html>