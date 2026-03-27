<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- Toggle Button -->
<button id="toggleSidebar"
    class="fixed top-5 left-5 z-[60] w-10 h-10 flex items-center justify-center
           rounded-xl bg-[#f8c922] shadow-lg hover:scale-105 
           transition-all duration-300 xl:hidden">
    <i id="toggleIcon" class="fa-solid fa-bars text-[#043915] text-lg"></i>
</button>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed top-0 left-0 h-screen w-64 bg-[#043915] flex flex-col shadow-xl
           -translate-x-full xl:translate-x-0
           transition-transform duration-300 ease-in-out
           z-50">

    <!-- Profile -->
    <div class="h-24 flex flex-col items-center justify-center text-[#f8c922]
                border-b border-white/10 mb-4">
        <i class="fa-solid fa-user-graduate text-3xl mb-1"></i>
        <span class="text-sm font-semibold">Student Panel</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 flex flex-col gap-2 text-white overflow-y-auto">

        <a href="student-dashboard"
           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition-all duration-300">
            <i class="fa-solid fa-gauge-high w-5"></i>
            Student Dashboard
        </a>

        <a href="code-of-conduct"
           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition-all duration-300">
            <i class="fa-solid fa-book w-5"></i>
            Code of Conduct
        </a>

        <a href="student-report"
           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition-all duration-300">
            <i class="fa-solid fa-file-circle-exclamation w-5"></i>
            Student Report
        </a>

    </nav>

    <!-- Logout -->
    <div class="px-4 pb-6 mt-auto">
        <a href="/student-discipline-and-incident-reporting-system/auth/controllers/log-out.php"
           class="flex items-center justify-center gap-3 px-4 py-3 rounded-xl font-bold
           bg-[#f8c922] text-[#043915]
           hover:bg-yellow-400 hover:scale-[1.04]
           transition-all duration-300 shadow-md">
            <i class="fa-solid fa-right-from-bracket"></i>
            Log out
        </a>
    </div>
</aside>

<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-40 xl:hidden"></div>

<!-- Script -->
<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const toggleIcon = document.getElementById('toggleIcon');
    const overlay = document.getElementById('overlay');
    const links = document.querySelectorAll('.sidebar-link');

    function toggleSidebar() {
        const isOpen = !sidebar.classList.contains('-translate-x-full');

        if (isOpen) {
            closeSidebar();
        } else {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            toggleIcon.classList.replace('fa-bars', 'fa-xmark');
        }
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        toggleIcon.classList.replace('fa-xmark', 'fa-bars');
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', closeSidebar);

    // Active Page Highlight
    const currentPath = window.location.pathname.split('/').pop();

    links.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add(
                'bg-white/20',
                'backdrop-blur-lg',
                'border-l-4',
                'border-[#f8c922]'
            );
        }

        link.addEventListener('click', () => {
            if (window.innerWidth < 1280) closeSidebar();
        });
    });
</script>