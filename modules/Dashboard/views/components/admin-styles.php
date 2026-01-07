<style>
    :root {
        --primary-color: #667eea;
        --secondary-color: #764ba2;
        --success-color: #10b981;
        --info-color: #3b86f6ff;
        --warning-color: #f59e0b;
        --danger-color: #ff5555e0;
        --light-color: #f8f9fa;
        --dark-color: #1f2937;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8fafc;
        overflow-x: hidden;
    }

    /* Add these to your existing admin-styles.php */

    .bg-purple {
        background-color: #8b5cf6 !important;
    }
    .bg-orange {
        background-color: #f97316 !important;
    }
    .bg-teal {
        background-color: #14b8a6 !important;
    }
    .bg-pink {
        background-color: #ec4899 !important;
    }

    .text-purple {
        color: #8b5cf6 !important;
    }
    .text-orange {
        color: #f97316 !important;
    }
    .text-teal {
        color: #14b8a6 !important;
    }
    .text-pink {
        color: #ec4899 !important;
    }

    .bg-purple.bg-opacity-10 {
        background-color: rgba(139, 92, 246, 0.1) !important;
    }
    .bg-orange.bg-opacity-10 {
        background-color: rgba(249, 115, 22, 0.1) !important;
    }
    .bg-teal.bg-opacity-10 {
        background-color: rgba(20, 184, 166, 0.1) !important;
    }
    .bg-pink.bg-opacity-10 {
        background-color: rgba(236, 72, 153, 0.1) !important;
    }
    
    .sidebar {
        background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        height: 100vh;
        box-shadow: 0 0 30px rgba(0,0,0,0.1);
        position: fixed;
        width: 250px;
        transition: all 0.3s;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }
    
    .sidebar .logo {
        padding: 20px;
        text-align: center;
        color: white;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }
    
    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        padding: 0 10px 20px 10px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.3) transparent;
    }
    
    /* Custom scrollbar for sidebar */
    .sidebar-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-content::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .sidebar-content::-webkit-scrollbar-thumb {
        background-color: rgba(255,255,255,0.3);
        border-radius: 10px;
    }
    
    .sidebar-content::-webkit-scrollbar-thumb:hover {
        background-color: rgba(255,255,255,0.5);
    }
    
    .sidebar .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 12px 15px;
        margin: 2px 5px;
        border-radius: 8px;
        transition: all 0.3s;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .sidebar .nav-link:hover {
        color: white;
        background: rgba(255,255,255,0.1);
    }
    
    .sidebar .nav-link.active {
        color: white;
        background: rgba(255,255,255,0.2);
        font-weight: 500;
    }
    
    .sidebar .nav-item.dropdown .nav-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .sidebar .nav-item.dropdown .nav-link::after {
        content: '\f078';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 12px;
        transition: transform 0.3s;
    }
    
    .sidebar .nav-item.dropdown.show .nav-link::after {
        transform: rotate(180deg);
    }
    
    .sidebar .dropdown-menu {
        background: rgba(255,255,255,0.95);
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin-left: 10px;
        margin-top: -5px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .sidebar .dropdown-item {
        padding: 10px 15px;
        border-radius: 4px;
        margin: 2px 5px;
        font-size: 14px;
        color: var(--dark-color);
    }
    
    .sidebar .dropdown-item:hover {
        background: rgba(102, 126, 234, 0.1);
        color: var(--primary-color);
    }
    
    .sidebar-footer {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 15px;
        background: rgba(0,0,0,0.1);
        flex-shrink: 0;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .user-avatar-small {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        overflow: hidden;
    }
    
    .user-avatar-small img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .user-details {
        flex: 1;
        min-width: 0;
    }
    
    .user-name {
        color: white;
        font-weight: 500;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .user-role {
        color: rgba(255,255,255,0.7);
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .main-content {
        margin-left: 250px;
        padding: 20px;
        transition: all 0.3s;
        min-height: 100vh;
    }
    
    .navbar {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 15px 20px;
        position: sticky;
        top: 0;
        z-index: 999;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.3s;
        border: 1px solid #e5e7eb;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0,0,0,0.1);
    }
    
    .stat-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 15px;
    }
    
    .stat-card .count {
        font-size: 28px;
        font-weight: 700;
        color: var(--dark-color);
    }
    
    .stat-card .label {
        color: #6b7280;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        color: #6b7280;
        border-bottom-width: 1px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .badge {
        padding: 5px 10px;
        font-weight: 500;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border: none;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
    }
    
    .card {
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border-radius: 12px;
    }
    
    .card-header {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 20px;
        border-radius: 12px 12px 0 0 !important;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .form-control, .form-select {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 15px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .action-btn {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        background: white;
        transition: all 0.3s;
    }
    
    .action-btn:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }
    
    .action-btn.edit {
        color: var(--info-color);
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .action-btn.delete {
        color: var(--danger-color);
        border-color: rgba(239, 68, 68, 0.2);
    }
    
    .action-btn.view {
        color: var(--success-color);
        border-color: rgba(16, 185, 129, 0.2);
    }
    
    .image-preview {
        width: 100px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid #e5e7eb;
    }
    
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-active {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }
    
    .status-inactive {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger-color);
    }
    
    .status-draft {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
    }
    
    .status-published {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }
    
    .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 20px;
    }
    
    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 20px;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .sidebar {
            margin-left: -250px;
            width: 250px;
        }
        
        .sidebar.active {
            margin-left: 0;
        }
        
        .main-content {
            margin-left: 0;
            padding: 15px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        /* Mobile toggle button */
        .sidebar-toggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
    }
    
    /* Larger screens */
    @media (min-width: 1200px) {
        .sidebar {
            width: 280px;
        }
        
        .main-content {
            margin-left: 280px;
        }
        
        .sidebar .nav-link {
            padding: 12px 20px;
            margin: 2px 10px;
        }
    }
    
    /* Animation for dropdowns */
    .dropdown-menu {
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Menu item icons */
    .sidebar .nav-link i {
        width: 20px;
        text-align: center;
    }
    
    /* Active dropdown styling */
    .sidebar .nav-item.dropdown.show .nav-link {
        background: rgba(255,255,255,0.15);
    }
    
    /* Separator for menu sections */
    .sidebar .nav-item:not(:first-child) {
        margin-top: 2px;
    }
    
    /* Special styling for logout */
    .sidebar .nav-link.text-danger:hover {
        background: rgba(239, 68, 68, 0.2);
    }
</style>