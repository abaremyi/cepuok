<?php
/**
 * Finance Maintenance (Manual Trigger)
 * File: modules/Dashboard/views/finance-maintenance.php
 * Route: /admin/finance-maintenance
 * 
 * This page allows Super Admin to manually run maintenance tasks:
 * - Lock expired budget indicators
 * - Suspend expired budget drafts (older than 14 days)
 */
$pageTitle = 'Finance Maintenance';
$requiredPermission = 'finance.manage_budget';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

// Only Super Admin can access this page
if (!$isSuperAdmin) {
    header('Location: ' . BASE_URL . '/admin/finance-dashboard');
    exit;
}

// Get current stats for display
try {
    $db = Database::getInstance();
    
    // Count expired indicators
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM budget_indicators 
        WHERE lock_date IS NOT NULL 
        AND lock_date < CURDATE() 
        AND status != 'locked'
    ");
    $stmt->execute();
    $expiredIndicators = $stmt->fetchColumn();
    
    // Count expired budget drafts
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM budget_quarters 
        WHERE status = 'draft' 
        AND draft_created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
    ");
    $stmt->execute();
    $expiredBudgets = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $expiredIndicators = 0;
    $expiredBudgets = 0;
    error_log("Error fetching maintenance stats: " . $e->getMessage());
}
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>

<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<style>
.maintenance-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
}
.maintenance-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.stat-badge {
    font-size: 0.85rem;
    padding: 5px 12px;
    border-radius: 30px;
    font-weight: 600;
}
.log-container {
    background: #1a1c1e;
    color: #00ff9d;
    padding: 20px;
    border-radius: 12px;
    font-family: 'Courier New', monospace;
    height: 350px;
    overflow-y: auto;
    font-size: 0.9rem;
    line-height: 1.6;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.5);
}
.log-entry {
    margin: 2px 0;
    border-bottom: 1px solid #2a2c2e;
    padding: 4px 0;
}
.log-time {
    color: #888;
    margin-right: 15px;
}
.log-success { color: #00ff9d; }
.log-error { color: #ff6b6b; }
.log-info { color: #5bc0de; }
.log-warning { color: #f0ad4e; }
</style>

<main id="content" role="main" class="main">
<div class="content container-fluid">

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title">
                    <i class="bi bi-tools me-2 text-primary"></i>
                    Finance Maintenance
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
                        <li class="breadcrumb-item active">Maintenance</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary" onclick="clearLog()">
                    <i class="bi bi-eraser me-1"></i> Clear Log
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="card maintenance-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg avatar-soft-primary avatar-circle">
                                <span class="avatar-initials"><i class="bi bi-pie-chart-fill fs-4"></i></span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Expired Indicators</h6>
                            <div class="d-flex align-items-center">
                                <span class="display-6 fw-bold text-primary me-3"><?= $expiredIndicators ?></span>
                                <?php if ($expiredIndicators > 0): ?>
                                    <span class="stat-badge bg-soft-warning text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Need Locking
                                    </span>
                                <?php else: ?>
                                    <span class="stat-badge bg-soft-success text-success">
                                        <i class="bi bi-check-circle me-1"></i> All Good
                                    </span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Indicators past their lock date</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card maintenance-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg avatar-soft-warning avatar-circle">
                                <span class="avatar-initials"><i class="bi bi-calendar3-range fs-4"></i></span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Expired Budget Drafts</h6>
                            <div class="d-flex align-items-center">
                                <span class="display-6 fw-bold text-warning me-3"><?= $expiredBudgets ?></span>
                                <?php if ($expiredBudgets > 0): ?>
                                    <span class="stat-badge bg-soft-warning text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Need Suspension
                                    </span>
                                <?php else: ?>
                                    <span class="stat-badge bg-soft-success text-success">
                                        <i class="bi bi-check-circle me-1"></i> All Good
                                    </span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Budget drafts older than 14 days</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Actions -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card maintenance-card h-100">
                <div class="card-header">
                    <h4 class="card-header-title">
                        <i class="bi bi-lock me-2 text-warning"></i>
                        Lock Indicators
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Lock all budget indicators whose lock date has passed. After locking, only Super Admin can edit them.
                    </p>
                    <div class="d-grid">
                        <button class="btn btn-warning" onclick="runMaintenance('indicators')" 
                                <?= $expiredIndicators == 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-lock me-1"></i> 
                            Lock Expired Indicators (<?= $expiredIndicators ?>)
                        </button>
                    </div>
                </div>
                <div class="card-footer bg-soft-warning border-0">
                    <small class="text-warning">
                        <i class="bi bi-info-circle me-1"></i>
                        This action cannot be undone
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card maintenance-card h-100">
                <div class="card-header">
                    <h4 class="card-header-title">
                        <i class="bi bi-pause-circle me-2 text-danger"></i>
                        Suspend Budgets
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Suspend budget drafts that have been pending for more than 14 days without approval.
                    </p>
                    <div class="d-grid">
                        <button class="btn btn-danger" onclick="runMaintenance('budgets')"
                                <?= $expiredBudgets == 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-pause-circle me-1"></i>
                            Suspend Expired Budgets (<?= $expiredBudgets ?>)
                        </button>
                    </div>
                </div>
                <div class="card-footer bg-soft-danger border-0">
                    <small class="text-danger">
                        <i class="bi bi-info-circle me-1"></i>
                        Suspended budgets can be reactivated
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card maintenance-card h-100 bg-primary text-white">
                <div class="card-header bg-transparent border-white">
                    <h4 class="card-header-title text-white">
                        <i class="bi bi-gear me-2"></i>
                        Run All Tasks
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-white-50 mb-3">
                        Run complete maintenance: lock expired indicators AND suspend expired budget drafts.
                    </p>
                    <div class="d-grid">
                        <button class="btn btn-light" onclick="runMaintenance('all')">
                            <i class="bi bi-play-fill me-1"></i>
                            Run Complete Maintenance
                        </button>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-white">
                    <small class="text-white-50">
                        <i class="bi bi-info-circle me-1"></i>
                        Recommended: Run this monthly
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Output -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-header-title">
                <i class="bi bi-terminal me-2"></i>
                Maintenance Log
            </h4>
            <span class="text-muted small" id="logTimestamp"></span>
        </div>
        <div class="card-body p-0">
            <div class="log-container" id="logOutput">
                <div class="log-entry">
                    <span class="log-time"><?= date('H:i:s') ?></span>
                    <span class="log-info">✦ System ready. Select a maintenance task to begin...</span>
                </div>
                <div class="log-entry">
                    <span class="log-time"><?= date('H:i:s') ?></span>
                    <span class="log-info">✦ Expired indicators: <?= $expiredIndicators ?></span>
                </div>
                <div class="log-entry">
                    <span class="log-time"><?= date('H:i:s') ?></span>
                    <span class="log-info">✦ Expired budget drafts: <?= $expiredBudgets ?></span>
                </div>
            </div>
        </div>
    </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
const API = '<?=BASE_URL?>/api/finance';

// Logging function
function addToLog(message, type = 'info') {
    const log = document.getElementById('logOutput');
    const timestamp = new Date().toLocaleTimeString();
    
    const colors = {
        success: '#00ff9d',
        error: '#ff6b6b',
        warning: '#f0ad4e',
        info: '#5bc0de'
    };
    
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.innerHTML = `<span class="log-time">${timestamp}</span> <span style="color: ${colors[type]}">✦ ${message}</span>`;
    
    log.appendChild(entry);
    log.scrollTop = log.scrollHeight;
    
    // Update timestamp
    document.getElementById('logTimestamp').textContent = `Last update: ${timestamp}`;
}

// Clear log
function clearLog() {
    const log = document.getElementById('logOutput');
    log.innerHTML = '';
    addToLog('Log cleared', 'info');
    addToLog('System ready', 'info');
}

// Run maintenance tasks
async function runMaintenance(task) {
    let taskName = '';
    let confirmMessage = '';
    
    if (task === 'indicators') {
        taskName = 'Lock Expired Indicators';
        confirmMessage = 'Are you sure you want to lock all expired budget indicators?';
    } else if (task === 'budgets') {
        taskName = 'Suspend Expired Budgets';
        confirmMessage = 'Are you sure you want to suspend all expired budget drafts?';
    } else {
        taskName = 'Complete Maintenance';
        confirmMessage = 'Are you sure you want to run complete maintenance (lock indicators + suspend budgets)?';
    }
    
    const result = await Swal.fire({
        title: 'Confirm ' + taskName,
        text: confirmMessage,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: task === 'budgets' ? '#dc3545' : '#ffc107',
        confirmButtonText: 'Yes, run it'
    });
    
    if (!result.isConfirmed) return;
    
    addToLog(`🚀 Starting ${taskName}...`, 'info');
    
    try {
        const response = await fetch(`${API}?action=run_maintenance`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ task: task })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.results) {
                if (data.results.indicators_locked !== undefined) {
                    addToLog(`✅ Locked ${data.results.indicators_locked} expired indicators`, 'success');
                }
                if (data.results.budgets_suspended !== undefined) {
                    addToLog(`✅ Suspended ${data.results.budgets_suspended} expired budgets`, 'success');
                }
            }
            addToLog(`✨ ${taskName} completed successfully!`, 'success');
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: `${taskName} completed successfully`,
                timer: 2000,
                showConfirmButton: false
            });
            
            // Reload page after 2 seconds to update stats
            setTimeout(() => {
                location.reload();
            }, 2000);
            
        } else {
            addToLog(`❌ Error: ${data.message}`, 'error');
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'An error occurred'
            });
        }
    } catch (error) {
        addToLog(`❌ Error: ${error.message}`, 'error');
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message
        });
    }
}

// Export functions to global scope
window.runMaintenance = runMaintenance;
window.clearLog = clearLog;
</script>
</body>
</html>