<?php
/**
 * Admin Scripts Layout
 * File: layouts/admin-scripts.php
 */
?>
<!-- JS Implementing Plugins -->
<script src="<?= BASE_URL ?>/dashboard-assets/js/vendor.min.js"></script>

<!-- JS Front -->
<script src="<?= BASE_URL ?>/dashboard-assets/js/theme.min.js"></script>
<script src="<?= admin_js_url('toastr.min.js')?>"></script>

<script>
    // Base URL for API calls
    const BASE_URL = '<?= BASE_URL ?>';
    const userPhoto = '<?= $userPhoto ?>';
    const userInitials = '<?= $userInitials ?>';
    const userFullName = '<?= $userFullName ?>';

    // Session timeout configuration
    const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes in milliseconds
    const WARNING_TIME = 5 * 60 * 1000; // 5 minutes warning
    const HEARTBEAT_INTERVAL = 5 * 60 * 1000; // 5 minutes heartbeat

    let timeoutTimer;
    let warningTimer;
    let countdownTimer;
    let warningModal;

    // Toastr configuration
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 5000
    };

    $(document).ready(function () {
        // Initialize warning modal
        warningModal = new bootstrap.Modal(document.getElementById('sessionWarningModal'));

        // Initialize charts
        initializeCharts();

        // Load dashboard data
        loadDashboardStats();
        loadRecentUsers();
        loadRecentMembers();

        // Start session timeout monitoring
        resetSessionTimers();

        // Start heartbeat
        startHeartbeat();

        // Update current time every second
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);

        // Reset timers on user activity
        const events = ['click', 'keypress', 'scroll', 'mousemove', 'touchstart'];
        events.forEach(event => {
            document.addEventListener(event, resetSessionTimers);
        });
    });

    // Update current time
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('currentTime').textContent = timeString;
    }

    // Initialize charts
    function initializeCharts() {
        // Activity Chart
        const ctx1 = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
                datasets: [{
                    label: 'User Logins',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // User Distribution Chart
        const ctx2 = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Pending', 'Inactive'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Load dashboard statistics
    function loadDashboardStats() {
        $.ajax({
            url: BASE_URL + '/api/dashboard?action=getStats',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getCookie('auth_token')
            },
            success: function (response) {
                if (response.success) {
                    $('#totalUsers').text(response.data.total_users || 0);
                    $('#totalMembers').text(response.data.total_members || 0);
                    $('#pendingMembers').text(response.data.pending_members || 0);
                    $('#todayVisitors').text(response.data.today_visitors || 0);

                    // Update growth indicators
                    $('#userGrowth').text('+' + (response.data.new_users_30_days || 0) + ' this month');
                    $('#membersGrowth').text('+' + (response.data.new_members_30_days || 0) + ' this month');
                    $('#pendingGrowth').text(response.data.pending_increase || '0% increase');
                    $('#visitorGrowth').text('+' + (response.data.visitor_increase || 0) + '% today');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading stats:', error);
                if (xhr.status === 401) {
                    // Unauthorized - token expired
                    forceLogout();
                }
            }
        });
    }

    // Load recent users
    function loadRecentUsers() {
        $.ajax({
            url: BASE_URL + '/api/users?action=list&limit=5',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getCookie('auth_token')
            },
            success: function (response) {
                if (response.success && response.data && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function (user) {
                        const initials = (user.firstname ? user.firstname.charAt(0) : '') +
                            (user.lastname ? user.lastname.charAt(0) : '');
                        const userPhoto = user.photo || '';

                        html += `
                                <tr>
                                    <td>
                                        <a class="d-flex align-items-center" href="${BASE_URL}/admin/users-view?id=${user.id}">
                                            <div class="flex-shrink-0">
                                                ${userPhoto ?
                                `<div class="avatar avatar-sm avatar-circle">
                                                        <img class="avatar-img" src="${BASE_URL}/uploads/${userPhoto}" alt="${user.firstname}">
                                                    </div>` :
                                `<div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                                        <span class="avatar-initials">${initials || 'U'}</span>
                                                    </div>`
                            }
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="text-inherit mb-0">${user.firstname || ''} ${user.lastname || ''}</h5>
                                                <small class="text-muted">${user.username || ''}</small>
                                            </div>
                                        </a>
                                    </td>
                                    <td>${user.email || 'N/A'}</td>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary">${user.role_name || 'N/A'}</span>
                                    </td>
                                    <td>
                                        <span class="badge ${user.status === 'active' ? 'bg-success' : user.status === 'pending' ? 'bg-warning' : 'bg-secondary'}">
                                            ${user.status || 'unknown'}
                                        </span>
                                    </td>
                                    <td>${user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="${BASE_URL}/admin/users-view?id=${user.id}" class="btn btn-sm btn-white" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="${BASE_URL}/admin/users-add-user?id=${user.id}" class="btn btn-sm btn-white" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            `;
                    });
                    $('#recentUsersTable').html(html);
                } else {
                    $('#recentUsersTable').html('<tr><td colspan="6" class="text-center">No users found</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading users:', error);
                $('#recentUsersTable').html('<tr><td colspan="6" class="text-center text-danger">Error loading users</td></tr>');
                if (xhr.status === 401) {
                    forceLogout();
                }
            }
        });
    }

    // Load recent members
    function loadRecentMembers() {
        $.ajax({
            url: BASE_URL + '/api/membership?action=recent&limit=5',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getCookie('auth_token')
            },
            success: function (response) {
                if (response.success && response.data && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function (member) {
                        html += `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                                    <span class="avatar-initials">${member.firstname ? member.firstname.charAt(0) : ''}${member.lastname ? member.lastname.charAt(0) : ''}</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0">${member.firstname || ''} ${member.lastname || ''}</h5>
                                                <small class="text-muted">${member.membership_number || 'No number'}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${member.membership_type || 'N/A'}</td>
                                    <td>
                                        <span class="badge ${member.cep_session === 'day' ? 'bg-soft-warning text-warning' : 'bg-soft-info text-info'}">
                                            ${member.cep_session === 'day' ? 'Day CEP' : 'Weekend CEP'}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge ${member.status === 'active' ? 'bg-success' : member.status === 'pending' ? 'bg-warning' : 'bg-secondary'}">
                                            ${member.status || 'unknown'}
                                        </span>
                                    </td>
                                    <td>${member.created_at ? new Date(member.created_at).toLocaleDateString() : 'N/A'}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="${BASE_URL}/admin/membership-management?id=${member.id}" class="btn btn-sm btn-white" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            ${member.status === 'pending' ?
                                `<button class="btn btn-sm btn-success" onclick="approveMember(${member.id})" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>` : ''
                            }
                                        </div>
                                    </td>
                                </tr>
                            `;
                    });
                    $('#recentMembersTable').html(html);
                } else {
                    $('#recentMembersTable').html('<tr><td colspan="6" class="text-center">No members found</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading members:', error);
                $('#recentMembersTable').html('<tr><td colspan="6" class="text-center text-danger">Error loading members</td></tr>');
                if (xhr.status === 401) {
                    forceLogout();
                }
            }
        });
    }

    // ============================================================
    // SESSION TIMEOUT HANDLING
    // ============================================================

    function resetSessionTimers() {
        // Clear existing timers
        clearTimeout(timeoutTimer);
        clearTimeout(warningTimer);
        clearInterval(countdownTimer);

        // Hide warning modal if open
        if (warningModal) {
            warningModal.hide();
        }

        // Set new timers
        warningTimer = setTimeout(showSessionWarning, SESSION_TIMEOUT - WARNING_TIME);
        timeoutTimer = setTimeout(forceLogout, SESSION_TIMEOUT);
    }

    function showSessionWarning() {
        let timeLeft = 300; // 5 minutes in seconds

        // Show modal
        warningModal.show();

        // Update timer every second
        countdownTimer = setInterval(function () {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('sessionTimer').textContent =
                `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
            }
            timeLeft--;
        }, 1000);
    }

    function extendSession() {
        // Show loading spinner
        document.getElementById('loadingSpinner').classList.add('show');

        $.ajax({
            url: BASE_URL + '/api/auth?action=extend-session',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + getCookie('auth_token'),
                'Content-Type': 'application/json'
            },
            success: function (response) {
                document.getElementById('loadingSpinner').classList.remove('show');

                if (response.success) {
                    // Hide modal
                    warningModal.hide();

                    // Clear countdown timer
                    clearInterval(countdownTimer);

                    // Reset session timers
                    resetSessionTimers();

                    // Show success message
                    toastr.success('Session extended successfully');

                    // Reload stats to verify session
                    loadDashboardStats();
                } else {
                    toastr.error(response.message || 'Failed to extend session');
                }
            },
            error: function (xhr, status, error) {
                document.getElementById('loadingSpinner').classList.remove('show');
                console.error('Error extending session:', error);
                toastr.error('Failed to extend session. You will be logged out.');

                if (xhr.status === 401) {
                    forceLogout();
                }
            }
        });
    }

    function forceLogout() {
        // Clear all timers
        clearTimeout(timeoutTimer);
        clearTimeout(warningTimer);
        clearInterval(countdownTimer);

        // Hide modal if open
        if (warningModal) {
            warningModal.hide();
        }

        // Show logout message
        toastr.warning('Your session has expired. Redirecting to login page...');

        // Redirect to logout
        setTimeout(function () {
            window.location.href = BASE_URL + '/logout';
        }, 2000);
    }

    function startHeartbeat() {
        setInterval(function () {
            $.ajax({
                url: BASE_URL + '/api/auth?action=heartbeat',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + getCookie('auth_token')
                },
                success: function (response) {
                    if (!response.success) {
                        console.warn('Heartbeat failed:', response.message);
                    }
                },
                error: function (xhr, status, error) {
                    if (xhr.status === 401) {
                        forceLogout();
                    }
                }
            });
        }, HEARTBEAT_INTERVAL);
    }

    // ============================================================
    // HELPER FUNCTIONS
    // ============================================================

    // Get cookie value
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return '';
    }

    // Approve member
    function approveMember(memberId) {
        if (!confirm('Are you sure you want to approve this member?')) {
            return;
        }

        document.getElementById('loadingSpinner').classList.add('show');

        $.ajax({
            url: BASE_URL + '/api/membership?action=approve',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + getCookie('auth_token'),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ member_id: memberId }),
            success: function (response) {
                document.getElementById('loadingSpinner').classList.remove('show');

                if (response.success) {
                    toastr.success('Member approved successfully');
                    loadRecentMembers(); // Reload the list
                } else {
                    toastr.error(response.message || 'Failed to approve member');
                }
            },
            error: function (xhr, status, error) {
                document.getElementById('loadingSpinner').classList.remove('show');
                console.error('Error approving member:', error);
                toastr.error('Failed to approve member');
            }
        });
    }

    // Check authentication status periodically
    setInterval(function () {
        $.ajax({
            url: BASE_URL + '/api/auth?action=validate',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getCookie('auth_token')
            },
            success: function (response) {
                if (!response.success) {
                    forceLogout();
                }
            },
            error: function (xhr, status, error) {
                if (xhr.status === 401) {
                    forceLogout();
                }
            }
        });
    }, 60000); // Check every minute

    // Handle visibility change
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            // Page became visible again, check authentication
            $.ajax({
                url: BASE_URL + '/api/auth?action=validate',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + getCookie('auth_token')
                },
                success: function (response) {
                    if (!response.success) {
                        forceLogout();
                    } else {
                        // Reset timers
                        resetSessionTimers();
                    }
                },
                error: function (xhr, status, error) {
                    if (xhr.status === 401) {
                        forceLogout();
                    }
                }
            });
        }
    });

    // Prevent back button after logout
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function () {
        window.history.go(1);
    };
</script>


<script src="<?= js_url('admin/session-lock.js') ?>"></script>


</body>

</html>