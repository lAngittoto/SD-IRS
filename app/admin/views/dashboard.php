<?php
ob_start();
?>

<main class="ml-64 min-h-screen bg-gray-100 p-6 max-w-full overflow-x-hidden">
    <?php 
    include __DIR__.'/../../../includes/admin-sidebar.php';
    ?>
    <?php 
    include __DIR__.'/../../../includes/admin-header.php';
    ?>

    <!-- TOP SUMMARY CARDS -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-user-graduate text-[#0B3C5D] text-xl"></i>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">
                    Total Enrolled Students
                </h1>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-chalkboard-user text-[#0B3C5D] text-xl"></i>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">
                    Total Faculty Members
                </h1>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-[#0B3C5D] text-xl"></i>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">
                    Total Recorded Incidents
                </h1>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-clock text-[#0B3C5D] text-xl"></i>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">
                    Pending Incident Reports
                </h1>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-check-circle text-[#0B3C5D] text-xl"></i>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">
                    Resolved Incident Cases
                </h1>
            </div>
        </div>

    </section>

    <!-- ANALYTICS SECTION -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-chart-line text-[#0B3C5D] text-xl"></i>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">
                    Monthly Incident Trends
                </h1>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-list-check text-[#0B3C5D] text-xl"></i>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">
                    Most Common Policy Violations
                </h1>
            </div>
        </div>

    </section>

</main>



<?php
$content =ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>