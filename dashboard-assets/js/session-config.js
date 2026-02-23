// Session configuration
const SESSION_CONFIG = {
    timeout: 30 * 60 * 1000, // 30 minutes
    warningTime: 5 * 60 * 1000, // 5 minutes before timeout
    checkInterval: 60000, // Check every minute
    heartbeatInterval: 300000 // Send heartbeat every 5 minutes
};

// Heartbeat function to keep session alive
function sendHeartbeat() {
    fetch(BASE_URL + '/api/auth?action=heartbeat', {
        method: 'POST',
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.warn('Heartbeat failed:', data.message);
        }
    })
    .catch(error => {
        console.error('Heartbeat error:', error);
    });
}

// Start heartbeat
setInterval(sendHeartbeat, SESSION_CONFIG.heartbeatInterval);

// Before unload, clear sensitive data
window.addEventListener('beforeunload', function() {
    // Clear any sensitive data from sessionStorage
    sessionStorage.clear();
});