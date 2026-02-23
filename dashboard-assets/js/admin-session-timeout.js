// Session timeout handling
const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes in milliseconds
const WARNING_TIME = 5 * 60 * 1000; // 5 minutes warning

let timeoutTimer;
let warningTimer;

function resetSessionTimers() {
    clearTimeout(timeoutTimer);
    clearTimeout(warningTimer);
    
    // Show warning 5 minutes before timeout
    warningTimer = setTimeout(showSessionWarning, SESSION_TIMEOUT - WARNING_TIME);
    
    // Logout when session expires
    timeoutTimer = setTimeout(forceLogout, SESSION_TIMEOUT);
}

function showSessionWarning() {
    // Create modal if it doesn't exist
    if (!document.getElementById('sessionWarningModal')) {
        const modalHtml = `
            <div class="modal fade" id="sessionWarningModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Session About to Expire</h5>
                        </div>
                        <div class="modal-body">
                            <p>Your session will expire in 5 minutes due to inactivity.</p>
                            <p>Would you like to stay logged in?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="extendSession()">Stay Logged In</button>
                            <button type="button" class="btn btn-danger" onclick="forceLogout()">Logout Now</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    const modal = new bootstrap.Modal(document.getElementById('sessionWarningModal'));
    modal.show();
}

function extendSession() {
    // Send heartbeat to server to extend session
    fetch(BASE_URL + '/api/auth?action=extend-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('sessionWarningModal'));
            modal.hide();
            
            // Reset timers
            resetSessionTimers();
            
            // Show success message
            toastr.success('Session extended successfully');
        }
    })
    .catch(error => {
        console.error('Error extending session:', error);
    });
}

function forceLogout() {
    // Clear any open modals
    const modal = document.getElementById('sessionWarningModal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    }
    
    // Show logout message
    toastr.warning('Your session has expired. Redirecting to login page...');
    
    // Redirect to logout
    setTimeout(() => {
        window.location.href = BASE_URL + '/logout';
    }, 2000);
}

// Reset timers on user activity
['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
    document.addEventListener(event, resetSessionTimers);
});

// Initialize timers when page loads
document.addEventListener('DOMContentLoaded', function() {
    resetSessionTimers();
});

// Check token validity periodically
setInterval(checkTokenValidity, 60000); // Check every minute

function checkTokenValidity() {
    fetch(BASE_URL + '/api/auth?action=validate', {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            forceLogout();
        }
    })
    .catch(error => {
        console.error('Token validation error:', error);
    });
}