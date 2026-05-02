<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<style>
    #student-toggle-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 60;
        width: 40px;
        height: 40px;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background-color: #f8c922;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        border: none;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    #student-toggle-btn:hover { transform: scale(1.05); }
    #student-toggle-btn i { color: #043915; font-size: 18px; }

    @media (max-width: 1279px) {
        #student-toggle-btn { display: flex; }
    }

    #student-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 222px;
        background-color: #043915;
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 24px rgba(0,0,0,0.3);
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        z-index: 50;
    }

    @media (min-width: 1280px) {
        #student-sidebar { transform: translateX(0); }
    }

    #student-sidebar .sidebar-header {
        height: 96px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #f8c922;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 16px;
    }
    #student-sidebar .sidebar-header i { font-size: 30px; margin-bottom: 4px; }

    #student-sidebar nav {
        flex: 1;
        padding: 0 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        color: white;
        overflow-y: auto;
    }

    #student-sidebar .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 12px;
        color: white;
        text-decoration: none;
        font-size: 14px;
        transition: background 0.3s ease;
    }
    #student-sidebar .sidebar-link:hover { background: rgba(255,255,255,0.1); }
    #student-sidebar .sidebar-link.active {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(8px);
        border-left: 4px solid #f8c922;
    }
    #student-sidebar .sidebar-link i { width: 20px; }

    #student-sidebar .sidebar-logout {
        padding: 0 16px 24px;
        margin-top: auto;
    }
    #student-sidebar .sidebar-logout a {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 12px;
        font-weight: 700;
        background-color: #f8c922;
        color: #043915;
        text-decoration: none;
        transition: background 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    #student-sidebar .sidebar-logout a:hover {
        background-color: #facc15;
        transform: scale(1.04);
    }

    #student-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        z-index: 40;
    }
    #student-overlay.show { display: block; }
</style>

<!-- Toggle Button -->
<button id="student-toggle-btn">
    <i id="student-toggle-icon" class="fa-solid fa-bars"></i>
</button>

<!-- Sidebar -->
<aside id="student-sidebar">

    <!-- Header -->
    <div class="sidebar-header">
        <i class="fa-solid fa-user-graduate"></i>
    </div>

    <!-- Navigation -->
    <nav>
        <a href="student-dashboard" class="sidebar-link">
            <i class="fa-solid fa-gauge-high"></i>
            Student Dashboard
        </a>
        <a href="code-of-conduct" class="sidebar-link">
            <i class="fa-solid fa-book"></i>
            Code of Conduct
        </a>
        <a href="student-report" class="sidebar-link">
            <i class="fa-solid fa-file-circle-exclamation"></i>
            Student Report
        </a>
    </nav>

    <!-- Logout -->
    <div class="sidebar-logout">
        <a href="/student-discipline-and-incident-reporting-system/auth/controllers/log-out.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Log out
        </a>
    </div>

</aside>

<!-- Overlay -->
<div id="student-overlay"></div>

<script>
{
    const sidebar    = document.getElementById('student-sidebar');
    const toggleBtn  = document.getElementById('student-toggle-btn');
    const toggleIcon = document.getElementById('student-toggle-icon');
    const overlay    = document.getElementById('student-overlay');
    const links      = document.querySelectorAll('#student-sidebar .sidebar-link');

    function openSidebar() {
        sidebar.style.transform = 'translateX(0)';
        overlay.classList.add('show');
        toggleIcon.classList.replace('fa-bars', 'fa-xmark');
    }

    function closeSidebar() {
        sidebar.style.transform = 'translateX(-100%)';
        overlay.classList.remove('show');
        toggleIcon.classList.replace('fa-xmark', 'fa-bars');
    }

    function toggleSidebar() {
        const isOpen = sidebar.style.transform === 'translateX(0px)' || sidebar.style.transform === 'translateX(0)';
        isOpen ? closeSidebar() : openSidebar();
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', closeSidebar);

    // Active Page Highlight
    const currentPath = window.location.pathname.split('/').pop();
    links.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
        link.addEventListener('click', () => {
            if (window.innerWidth < 1280) closeSidebar();
        });
    });
}
</script>