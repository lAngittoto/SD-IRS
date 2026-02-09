<?php
ob_start();
?>

<main class="ml-64 min-h-screen bg-gray-100 p-8 w-[calc(100%-16rem)] overflow-x-hidden">
    <?php 
    include __DIR__.'/../../../includes/admin-sidebar.php';
    ?>
    <?php 
    include __DIR__.'/../../../includes/admin-header.php';
    ?>

    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-50">
                    <i class="fa-solid fa-user-graduate text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Enrolled Students</h1>
                    <p class="text-2xl font-bold text-[#0B3C5D]">
                        <?php echo $totalStudents ?? '-'; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-indigo-50">
                    <i class="fa-solid fa-chalkboard-user text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Faculty Members</h1>
                    <p class="text-2xl font-bold text-[#0B3C5D]">
                        <?php echo $totalFaculty ?? '-'; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-red-50">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Recorded Incidents</h1>
                    <p class="text-2xl font-bold text-[#0B3C5D]">
                        <?php echo $totalIncidents ?? '-'; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-amber-50">
                    <i class="fa-solid fa-clock text-amber-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Reports</h1>
                    <p class="text-2xl font-bold text-[#0B3C5D]">
                        <?php echo $pendingReports ?? '-'; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-emerald-50">
                    <i class="fa-solid fa-check-circle text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Resolved Cases</h1>
                    <p class="text-2xl font-bold text-[#0B3C5D]">
                        <?php echo $resolvedCases ?? '-'; ?>
                    </p>
                </div>
            </div>
        </div>

    </section>

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-100">
                    <i class="fa-solid fa-chart-line text-blue-700 text-lg"></i>
                </div>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">Monthly Incident Trends</h1>
            </div>
            <div class="h-48 bg-gray-50 rounded-xl border border-dashed border-gray-200 flex items-center justify-center">
                <span class="text-gray-400 text-sm">Chart Placeholder</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-purple-100">
                    <i class="fa-solid fa-list-check text-purple-700 text-lg"></i>
                </div>
                <h1 class="text-lg font-semibold text-[#0B3C5D]">Policy Violations</h1>
            </div>
            <div class="h-48 bg-gray-50 rounded-xl border border-dashed border-gray-200 flex items-center justify-center">
                <span class="text-gray-400 text-sm">Data Breakdown Placeholder</span>
            </div>
        </div>

    </section>

</main>

<?php
$content = ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>
