<?php
/**
 * Finance Revenue
 * File: modules/Dashboard/views/finance-revenue.php
 */
$pageTitle          = 'Revenue Management';
$requiredPermission = 'finance.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canRecord = hasPermission($userPermissions, 'finance.record_revenue');
$canEdit = hasPermission($userPermissions, 'finance.edit_revenue') || $isSuperAdmin;
$canDelete = hasPermission($userPermissions, 'finance.delete_revenue') || $isSuperAdmin;

// Debug current user
error_log("Current User ID: " . ($currentUser->id ?? 'N/A'));
error_log("Current User Email: " . ($currentUser->email ?? 'N/A'));
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
<script>(function(){var el=document.getElementById('sessionLockOverlay');if(el)el.dataset.email=<?=json_encode($currentUser->email??'')?>;})();</script>

<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title">Revenue</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Dashboard / Finance</a></li>
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
                <div class="card-header">
                    <h4 class="card-header-title" id="formTitle">Record Revenue</h4>
                </div>
                <div class="card-body">
                    <input type="hidden" id="editId" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                        <select id="fSession" class="form-select" required>
                            <?php if($isSuperAdmin ?? false): ?>
                                <option value="">Select session…</option>
                                <option value="day">☀️ Day CEP</option>
                                <option value="weekend">🌙 Weekend CEP</option>
                            <?php else: 
                                // Try multiple sources for session type
                                $userSession = $currentUser->session_type ?? $_SESSION['user_session'] ?? $currentUser->cep_session ?? 'day';
                                // If still empty, you might want to show a dropdown anyway
                                if (empty($userSession)): ?>
                                    <option value="">Select session…</option>
                                    <option value="day">☀️ Day CEP</option>
                                    <option value="weekend">🌙 Weekend CEP</option>
                                <?php else: ?>
                                    <option value="<?= htmlspecialchars($userSession) ?>" selected>
                                        <?= $userSession === 'weekend' ? '🌙 Weekend CEP' : '☀️ Day CEP' ?>
                                    </option>
                                <?php endif; 
                            endif; ?>
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

                    <div class="d-grid gap-2">
                        <button id="btnRecord" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Record Revenue
                        </button>
                        <button id="btnUpdate" class="btn btn-warning" style="display: none;">
                            <i class="bi bi-pencil-square me-1"></i> Update Revenue
                        </button>
                        <button id="btnCancelEdit" class="btn btn-secondary" style="display: none;">
                            <i class="bi bi-x-lg me-1"></i> Cancel Edit
                        </button>
                    </div>
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
                            <tr>
                                <th>Date</th>
                                <th>Session</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th>Recorded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="revTbody">
                            <tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>
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
(function() {
    'use strict';
    
    const BASE_URL = '<?= BASE_URL ?>';
    const API_URL = BASE_URL + '/api/finance';
    const IS_SUPER_ADMIN = <?= json_encode($isSuperAdmin ?? false) ?>;
    const CURRENT_USER_SESSION = <?= json_encode($currentUser->session_type ?? null) ?>;
    const CURRENT_USER_ID = <?= json_encode($currentUser->user_id ?? $currentUser->id ?? null) ?>;
    const CAN_EDIT = <?= json_encode($canEdit ?? false) ?>;
    const CAN_DELETE = <?= json_encode($canDelete ?? false) ?>;
    
    console.log('Current User ID for recording:', CURRENT_USER_ID);
    console.log('Can Edit:', CAN_EDIT);
    console.log('Can Delete:', CAN_DELETE);
    
    let currentPage = 1;
    let totalPages = 1;
    let totalRecords = 0;

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Get filter values
    function getFilterValue(id) {
        const el = document.getElementById(id);
        return el ? el.value || null : null;
    }

    // Get session for filtering
    function getFilterSession() {
        if (IS_SUPER_ADMIN) {
            return getFilterValue('fltSession') || '';
        }
        return CURRENT_USER_SESSION || '';
    }

    // Load revenue data
    async function loadRevenue(page = 1) {
        currentPage = page;
        
        const tbody = document.getElementById('revTbody');
        if (!tbody) return;
        
        // Show loading
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
        
        try {
            // Build query parameters
            const params = new URLSearchParams({
                action: 'revenue_list',
                page: page,
                per_page: 20
            });
            
            const session = getFilterSession();
            if (session) {
                params.append('session', session);
            }
            
            const type = getFilterValue('fltType');
            if (type) {
                params.append('type', type);
            }
            
            const month = getFilterValue('fltMonth');
            if (month) {
                params.append('month', month);
            }
            
            console.log('Fetching revenue with params:', params.toString());
            
            const response = await fetch(`${API_URL}?${params.toString()}`, {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Revenue API response:', result);
            
            // Update result count
            const resultCount = document.getElementById('resultCount');
            if (resultCount) {
                if (result.success === false) {
                    resultCount.textContent = 'Error loading data';
                } else {
                    totalRecords = result.total || 0;
                    resultCount.textContent = `${totalRecords} record(s)`;
                }
            }
            
            // Check if we have data
            if (result.success === false || !result.data) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>${escapeHtml(result.message || 'Failed to load data')}
                </td></tr>`;
                renderPagination(0, 1);
                return;
            }
            
            if (!result.data.length) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted"><i class="bi bi-inbox me-2"></i>No revenue records found</td></tr>';
                renderPagination(result.total || 0, result.pages || 1);
                return;
            }
            
            // Render table rows
            let html = '';
            result.data.forEach(item => {
                const sessionClass = item.cep_session === 'day' ? 'warning' : 'primary';
                
                let typeColor = 'secondary';
                if (item.revenue_type === 'offering') typeColor = 'success';
                else if (item.revenue_type === 'tithe') typeColor = 'primary';
                else if (item.revenue_type === 'donation') typeColor = 'info';
                else if (item.revenue_type === 'project') typeColor = 'warning';
                else if (item.revenue_type === 'fundraising') typeColor = 'secondary';
                
                html += '<tr>';
                html += `<td>${escapeHtml(item.revenue_date)}</td>`;
                html += `<td><span class="badge bg-soft-${sessionClass} text-${sessionClass}">${escapeHtml(item.cep_session)}</span></td>`;
                html += `<td><span class="badge bg-soft-${typeColor} text-${typeColor} text-capitalize">${escapeHtml(item.revenue_type)}</span></td>`;
                html += `<td class="fw-bold text-success">RWF ${Number(item.amount || 0).toLocaleString()}</td>`;
                html += `<td>${escapeHtml(item.reference_no || '—')}</td>`;
                html += `<td class="text-muted">${escapeHtml(item.description || '—')}</td>`;
                html += `<td>${escapeHtml(item.recorded_by_name || 'System')}</td>`;
                
                // Actions column
                html += '<td>';
                if (CAN_EDIT) {
                    html += `<button class="btn btn-sm btn-ghost-secondary me-1" onclick="editRevenue(${item.id})" title="Edit"><i class="bi-pencil"></i></button>`;
                }
                if (CAN_DELETE) {
                    html += `<button class="btn btn-sm btn-ghost-danger" onclick="deleteRevenue(${item.id})" title="Delete"><i class="bi-trash"></i></button>`;
                }
                html += '</td>';
                
                html += '</tr>';
            });
            
            tbody.innerHTML = html;
            
            // Update pagination
            totalPages = result.pages || 1;
            renderPagination(result.total || 0, result.pages || 1);
            
        } catch (error) {
            console.error('Error loading revenue:', error);
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>Error: ${escapeHtml(error.message)}
            </td></tr>`;
            renderPagination(0, 1);
        }
    }

    // Render pagination
    function renderPagination(total, pages) {
        const paginator = document.getElementById('paginator');
        if (!paginator) return;
        
        if (!total || pages <= 1) {
            paginator.innerHTML = `<span class="text-muted small">${total || 0} records</span><nav></nav>`;
            return;
        }
        
        let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';
        
        // Previous button
        paginationHtml += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="window.loadRev(${currentPage - 1}); return false;" aria-label="Previous">
                <span aria-hidden="true">‹</span>
            </a>
        </li>`;
        
        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(pages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        if (startPage > 1) {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="window.loadRev(${i}); return false;">${i}</a>
            </li>`;
        }
        
        if (endPage < pages) {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        
        // Next button
        paginationHtml += `<li class="page-item ${currentPage >= pages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="window.loadRev(${currentPage + 1}); return false;" aria-label="Next">
                <span aria-hidden="true">›</span>
            </a>
        </li>`;
        
        paginationHtml += '</ul></nav>';
        
        paginator.innerHTML = `<span class="text-muted small">${total} records</span>${paginationHtml}`;
    }
    
    // Make loadRev available globally
    window.loadRev = loadRevenue;

    // Load daily total
    async function loadDailyTotal() {
        try {
            const sessionSelect = document.getElementById('fSession');
            let session = null;
            
            if (sessionSelect) {
                session = sessionSelect.value;
            }
            
            // If session is empty and not super admin, try CURRENT_USER_SESSION
            if (!session && !IS_SUPER_ADMIN) {
                session = CURRENT_USER_SESSION;
            }
            
            console.log('Loading daily total for session:', session);
            
            if (!session) {
                document.getElementById('dailyTotal').textContent = 'RWF —';
                return;
            }
            
            const response = await fetch(`${API_URL}?action=daily_total&session=${encodeURIComponent(session)}`, {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Daily total response:', result);
            
            const dailyTotal = document.getElementById('dailyTotal');
            if (dailyTotal) {
                if (result.success === false) {
                    dailyTotal.textContent = 'RWF —';
                } else {
                    dailyTotal.textContent = 'RWF ' + Number(result.total || 0).toLocaleString();
                }
            }
        } catch (error) {
            console.error('Error loading daily total:', error);
            document.getElementById('dailyTotal').textContent = 'RWF —';
        }
    }

    // Edit revenue
    window.editRevenue = async function(id) {
        try {
            // Fetch the revenue record details
            const response = await fetch(`${API_URL}?action=revenue_get&id=${id}`, {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Edit revenue response:', result);
            
            if (result.success && result.data) {
                const revenue = result.data;
                
                // Fill the form with the revenue data
                document.getElementById('editId').value = revenue.id;
                document.getElementById('fSession').value = revenue.cep_session;
                document.getElementById('fType').value = revenue.revenue_type;
                document.getElementById('fAmount').value = revenue.amount;
                document.getElementById('fDate').value = revenue.revenue_date;
                document.getElementById('fRef').value = revenue.reference_no || '';
                document.getElementById('fDesc').value = revenue.description || '';
                
                // Show update button, hide record button
                document.getElementById('btnRecord').style.display = 'none';
                document.getElementById('btnUpdate').style.display = 'block';
                document.getElementById('btnCancelEdit').style.display = 'block';
                document.getElementById('formTitle').textContent = 'Edit Revenue';
                
                // Scroll to form
                document.querySelector('.card.sticky-top').scrollIntoView({ behavior: 'smooth' });
            } else {
                throw new Error(result.message || 'Failed to load revenue details');
            }
        } catch (error) {
            console.error('Error loading revenue for edit:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to load revenue details',
                confirmButtonColor: '#5537ff'
            });
        }
    };

    // Update revenue
    async function updateRevenue() {
        const id = document.getElementById('editId').value;
        if (!id) return;
        
        // Get form values
        const session = document.getElementById('fSession').value;
        const type = document.getElementById('fType').value;
        const amount = document.getElementById('fAmount').value;
        const date = document.getElementById('fDate').value;
        const ref = document.getElementById('fRef').value;
        const desc = document.getElementById('fDesc').value;
        
        // Validate
        if (!session) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a session',
                confirmButtonColor: '#ff377a'
            });
            return;
        }
        
        if (!type) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a revenue type',
                confirmButtonColor: '#ff377a'
            });
            return;
        }
        
        if (!amount || amount <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please enter a valid amount greater than 0',
                confirmButtonColor: '#ff377a'
            });
            return;
        }
        
        if (!date) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a date',
                confirmButtonColor: '#ff377a'
            });
            return;
        }
        
        const payload = {
            id: id,
            cep_session: session,
            revenue_type: type,
            amount: amount,
            revenue_date: date,
            reference_no: ref || '',
            description: desc || ''
        };
        
        const btn = document.getElementById('btnUpdate');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
        
        try {
            const response = await fetch(`${API_URL}?action=revenue_update`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            
            const responseText = await response.text();
            console.log('Raw update response:', responseText.substring(0, 200));
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Invalid server response');
            }
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Revenue updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Cancel edit mode
                cancelEdit();
                
                // Refresh data
                await loadRevenue();
                await loadDailyTotal();
            } else {
                throw new Error(result.message || 'Failed to update revenue');
            }
        } catch (error) {
            console.error('Error updating revenue:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'An error occurred while updating revenue',
                confirmButtonColor: '#ff3752'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    // Delete revenue
    window.deleteRevenue = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`${API_URL}?action=revenue_delete`, {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ id: id })
                    });
                    
                    const responseText = await response.text();
                    console.log('Raw delete response:', responseText.substring(0, 200));
                    
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Invalid server response');
                    }
                    
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Revenue record has been deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Refresh data
                        await loadRevenue();
                        await loadDailyTotal();
                    } else {
                        throw new Error(result.message || 'Failed to delete revenue');
                    }
                } catch (error) {
                    console.error('Error deleting revenue:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'An error occurred while deleting revenue',
                        confirmButtonColor: '#ff3752'
                    });
                }
            }
        });
    };

    // Cancel edit mode
    function cancelEdit() {
        document.getElementById('editId').value = '';
        document.getElementById('fSession').value = IS_SUPER_ADMIN ? '' : (CURRENT_USER_SESSION || 'day');
        document.getElementById('fType').value = 'offering';
        document.getElementById('fAmount').value = '';
        document.getElementById('fDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('fRef').value = '';
        document.getElementById('fDesc').value = '';
        
        document.getElementById('btnRecord').style.display = 'block';
        document.getElementById('btnUpdate').style.display = 'none';
        document.getElementById('btnCancelEdit').style.display = 'none';
        document.getElementById('formTitle').textContent = 'Record Revenue';
    }

    // Record revenue
    async function recordRevenue() {
        console.log('Record revenue function called');
        
        // Get form values
        const sessionSelect = document.getElementById('fSession');
        let session = sessionSelect ? sessionSelect.value : null;
        const type = document.getElementById('fType')?.value;
        const amount = document.getElementById('fAmount')?.value;
        const date = document.getElementById('fDate')?.value;
        const ref = document.getElementById('fRef')?.value;
        const desc = document.getElementById('fDesc')?.value;
        
        console.log('Form values:', { session, type, amount, date, ref, desc, userId: CURRENT_USER_ID });
        
        // Get button reference for later use
        const btn = document.getElementById('btnRecord');
        const originalText = btn ? btn.innerHTML : '';
        
        // Validate session
        if (!session) {
            // If not super admin and session is empty, try to use CURRENT_USER_SESSION
            if (!IS_SUPER_ADMIN && CURRENT_USER_SESSION) {
                session = CURRENT_USER_SESSION;
                console.log('Using CURRENT_USER_SESSION:', session);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a session',
                    confirmButtonColor: '#ff3752'
                });
                return;
            }
        }
        
        if (!type) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a revenue type',
                confirmButtonColor: '#ff3752'
            });
            return;
        }
        
        if (!amount || amount <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please enter a valid amount greater than 0',
                confirmButtonColor: '#ff3752'
            });
            return;
        }
        
        if (!date) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a date',
                confirmButtonColor: '#ff3752'
            });
            return;
        }
        
        if (!CURRENT_USER_ID) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'User ID not found. Please try logging in again.',
                confirmButtonColor: '#ff3752'
            });
            return;
        }
        
        // Prepare payload
        const payload = {
            cep_session: session,
            revenue_type: type,
            amount: amount,
            revenue_date: date,
            reference_no: ref || '',
            description: desc || '',
            recorded_by: CURRENT_USER_ID
        };
        
        console.log('Sending payload:', payload);
        
        // Disable button
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        }
        
        try {
            const response = await fetch(`${API_URL}?action=revenue_record`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            
            // Get response text first
            const responseText = await response.text();
            console.log('Raw response:', responseText.substring(0, 200));
            
            // Try to parse as JSON
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                // Check if it's a PHP error message
                if (responseText.includes('<br />') || responseText.includes('<b>')) {
                    throw new Error('PHP Error occurred. Check server logs.');
                }
                throw new Error('Invalid server response');
            }
            
            console.log('Parsed response:', result);
            
            if (result.success) {
                // Clear form
                document.getElementById('fAmount').value = '';
                document.getElementById('fRef').value = '';
                document.getElementById('fDesc').value = '';
                
                // Show success
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Revenue recorded successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Refresh data
                await loadRevenue();
                await loadDailyTotal();
            } else {
                throw new Error(result.message || 'Failed to record revenue');
            }
        } catch (error) {
            console.error('Error recording revenue:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'An error occurred while recording revenue',
                confirmButtonColor: '#ff3752'
            });
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    // Export CSV
    function exportCSV() {
        const params = new URLSearchParams({
            action: 'revenue_export'
        });
        
        const session = getFilterSession();
        if (session) {
            params.append('session', session);
        }
        
        const type = getFilterValue('fltType');
        if (type) {
            params.append('type', type);
        }
        
        const month = getFilterValue('fltMonth');
        if (month) {
            params.append('month', month);
        }
        
        window.location.href = `${API_URL}?${params.toString()}`;
    }
    
    window.exportCSV = exportCSV;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded, initializing...');
        console.log('Current User ID:', CURRENT_USER_ID);
        
        // Load initial data
        loadRevenue();
        loadDailyTotal();
        
        // Add event listeners
        const recordBtn = document.getElementById('btnRecord');
        if (recordBtn) {
            recordBtn.addEventListener('click', recordRevenue);
        }
        
        const updateBtn = document.getElementById('btnUpdate');
        if (updateBtn) {
            updateBtn.addEventListener('click', updateRevenue);
        }
        
        const cancelBtn = document.getElementById('btnCancelEdit');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', cancelEdit);
        }
        
        // Filter change events
        const filterIds = ['fltSession', 'fltType', 'fltMonth'];
        filterIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => loadRevenue(1));
            }
        });
        
        // Session change for daily total
        const sessionSelect = document.getElementById('fSession');
        if (sessionSelect) {
            sessionSelect.addEventListener('change', loadDailyTotal);
        }
    });
})();
</script>
</body>
</html>