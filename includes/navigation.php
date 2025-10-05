<?php
/**
 * Sidebar Navigation Component
 * Professional sidebar navigation with modern color palette
 */

// Get current page for active menu highlighting
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<!-- Mobile Sidebar Toggle -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar Navigation -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a class="sidebar-brand" href="index.php">
            <div class="logo-img"></div>
            <div class="brand-text">
                <div class="school-name">Holy Family High School</div>
                <small class="system-name">Gate Security System</small>
            </div>
        </a>
    </div>
    
    <ul class="sidebar-menu">
        <li class="sidebar-item">
            <a href="index.php?page=dashboard" 
               class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="index.php?page=rfid" 
               class="<?php echo ($currentPage == 'rfid') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>User Management</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="index.php?page=reports" 
               class="<?php echo ($currentPage == 'reports') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="rfid_scanner.php" target="_blank">
                <i class="fas fa-wifi"></i>
                <span>RFID Scanner</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <div class="mb-3">
            <div class="d-flex">
                <div class="user-avatar mb-2">
                    <?php echo strtoupper(substr($currentAdmin['full_name'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="ml-2">
                    <small class="text-light d-block"><?php echo htmlspecialchars($currentAdmin['full_name'] ?? 'Admin'); ?></small>
                    <small class="text-light"><?php echo htmlspecialchars($currentAdmin['email'] ?? ''); ?></small>
                </div>
            </div>
        </div>
        <a href="index.php?action=logout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('mobile-visible');
    overlay.classList.toggle('show');
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.remove('mobile-visible');
    overlay.classList.remove('show');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.sidebar-toggle');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(event.target) && 
        !toggle.contains(event.target) && 
        sidebar.classList.contains('mobile-visible')) {
        closeSidebar();
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-visible');
        overlay.classList.remove('show');
    }
});

// Handle escape key to close sidebar
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSidebar();
    }
});
</script>