<aside class="fixed left-0 top-0 h-screen w-64 bg-[#043915] flex flex-col shadow-xl">

    <!-- TOP SPACE / LOGO AREA -->
    <div class="h-20 flex items-center justify-center text-white font-semibold text-lg tracking-wide">
        <i class="fa-solid fa-user"></i>
    </div>

    <!-- MENU -->
    <nav class="flex-1 px-4 flex flex-col gap-2 text-white">

        <a href="admin-dashboard"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
                  hover:bg-white/10 hover:backdrop-blur-md
                  hover:shadow-lg transition-all duration-300">
            <i class="fa-solid fa-gauge-high"></i>
            Dashboard
        </a>

        <a href="incident-reports"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
                  hover:bg-white/10 hover:backdrop-blur-md
                  hover:shadow-lg transition-all duration-300">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Incident Reports
        </a>

        <a href="discipline-records"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
                  hover:bg-white/10 hover:backdrop-blur-md
                  hover:shadow-lg transition-all duration-300">
            <i class="fa-solid fa-scale-balanced"></i>
            Discipline Records
        </a>

        <a href="advisories"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
                  hover:bg-white/10 hover:backdrop-blur-md
                  hover:shadow-lg transition-all duration-300">
            <i class="fa-solid fa-bullhorn"></i>
            Advisories
        </a>

        <a href="#"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
                  hover:bg-white/10 hover:backdrop-blur-md
                  hover:shadow-lg transition-all duration-300">
            <i class="fa-solid fa-users-gear"></i>
            User Management
        </a>

        <a href="#"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
                  hover:bg-white/10 hover:backdrop-blur-md
                  hover:shadow-lg transition-all duration-300">
            <i class="fa-solid fa-chart-column"></i>
            Reports
        </a>

    </nav>

    <!-- LOGOUT -->
    <div class="px-4 pb-6">
        <a href="/student-discipline-and-incident-reporting-system/auth/controllers/log-out.php"
           class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold
                  bg-[#f8c922] text-[#043915]
                  hover:bg-yellow-400 hover:scale-[1.04]
                  transition-all duration-300 shadow-md">
            <i class="fa-solid fa-right-from-bracket"></i>
            Log out
        </a>
    </div>

</aside>
