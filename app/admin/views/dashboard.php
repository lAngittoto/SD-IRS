<?php
ob_start();
?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gray-100 p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__.'/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__.'/../../../includes/admin-header.php'; ?>

    <!-- Statistics Cards Section -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">

        <!-- Total Students Card -->
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-50 shrink-0">
                    <i class="fa-solid fa-user-graduate text-blue-600 text-xl sm:text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">Total Enrolled Students</h1>
                    <p class="text-xl sm:text-2xl font-bold text-[#0B3C5D]">
                        <?php echo isset($totalStudents) ? number_format($totalStudents) : '0'; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Faculty Card -->
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-indigo-50 shrink-0">
                    <i class="fa-solid fa-chalkboard-user text-indigo-600 text-xl sm:text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">Total Faculty Members</h1>
                    <p class="text-xl sm:text-2xl font-bold text-[#0B3C5D]">
                        <?php echo isset($totalFaculty) ? number_format($totalFaculty) : '0'; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Incidents Card -->
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-red-50 shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl sm:text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">Total Recorded Incidents</h1>
                    <p class="text-xl sm:text-2xl font-bold text-[#0B3C5D]">
                        <?php echo isset($totalIncidents) ? number_format($totalIncidents) : '0'; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pending Reports Card -->
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-amber-50 shrink-0">
                    <i class="fa-solid fa-clock text-amber-600 text-xl sm:text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Reports</h1>
                    <p class="text-xl sm:text-2xl font-bold text-[#0B3C5D]">
                        <?php echo isset($pendingReports) ? number_format($pendingReports) : '0'; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Resolved Cases Card -->
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition border border-gray-50">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-emerald-50 shrink-0">
                    <i class="fa-solid fa-check-circle text-emerald-600 text-xl sm:text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">Resolved Cases</h1>
                    <p class="text-xl sm:text-2xl font-bold text-[#0B3C5D]">
                        <?php echo isset($resolvedCases) ? number_format($resolvedCases) : '0'; ?>
                    </p>
                </div>
            </div>
        </div>

    </section>

    <!-- Charts and Policy Violations Section -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
        
        <!-- Monthly Incident Trends Chart -->
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition border border-gray-50 flex flex-col">
            <div class="flex items-center gap-3 mb-3 sm:mb-4">
                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-100 shrink-0">
                    <i class="fa-solid fa-chart-line text-blue-700 text-lg sm:text-xl"></i>
                </div>
                <h1 class="text-base sm:text-lg font-semibold text-[#043915]">Monthly Incident Trends</h1>
            </div>
            <div class="h-48 sm:h-60 bg-gray-50 rounded-xl border border-dashed border-gray-200 flex items-center justify-center">
                <?php if (isset($monthlyTrends) && !empty($monthlyTrends)): ?>
                    <canvas id="monthlyTrendsChart"></canvas>
                <?php else: ?>
                    <span class="text-gray-400 text-sm sm:text-base">No trend data available</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Policy Violations List (Fixed Height with Scroll) -->
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition border border-gray-50 flex flex-col">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-100 shrink-0">
                        <i class="fa-solid fa-gavel text-red-700 text-lg sm:text-xl"></i>
                    </div>
                    <h1 class="text-base sm:text-lg font-semibold text-[#043915]">Policy Violations</h1>
                </div>
                <span class="text-sm text-gray-500">
                    Total: <?php echo isset($allViolations) ? count($allViolations) : 0; ?>
                </span>
            </div>

            <!-- Fixed Height Container with Scrollbar -->
            <div class="h-48 sm:h-60 overflow-y-auto border border-gray-200 rounded-xl">
                <?php if (isset($allViolations) && !empty($allViolations)): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Violation Name
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Severity
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($allViolations as $index => $violation): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $index + 1; ?>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($violation['violation_name']); ?>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            $severity = strtolower($violation['severity'] ?? '');
                                            if (strpos($severity, 'major') !== false || strpos($severity, 'severe') !== false) {
                                                echo 'bg-red-100 text-red-800';
                                            } elseif (strpos($severity, 'moderate') !== false || strpos($severity, 'medium') !== false) {
                                                echo 'bg-yellow-100 text-yellow-800';
                                            } else {
                                                echo 'bg-blue-100 text-blue-800';
                                            }
                                            ?>">
                                            <?php echo htmlspecialchars($violation['severity'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-full text-center py-8">
                        <i class="fa-solid fa-inbox text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500 text-sm">No policy violations configured yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Top Violations Section -->
    <?php if (isset($topViolations) && !empty($topViolations)): ?>
    <section class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg border border-gray-50">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-orange-100 shrink-0">
                <i class="fa-solid fa-chart-bar text-orange-700 text-lg sm:text-xl"></i>
            </div>
            <h1 class="text-base sm:text-lg font-semibold text-[#043915]">Most Common Violations</h1>
        </div>

        <div class="space-y-3">
            <?php foreach (array_slice($topViolations, 0, 5) as $violation): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($violation['violation_name']); ?></h3>
                        <div class="flex gap-2 mt-1">
                            <?php if (!empty($violation['severity'])): ?>
                                <span class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                                    <?php echo htmlspecialchars($violation['severity']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($violation['sanction'])): ?>
                                <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-800">
                                    <?php echo htmlspecialchars($violation['sanction']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-[#0B3C5D]"><?php echo $violation['violation_count']; ?></p>
                        <p class="text-xs text-gray-500">incidents</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Trends Chart
<?php if (isset($monthlyTrends) && !empty($monthlyTrends)): ?>
const monthlyCtx = document.getElementById('monthlyTrendsChart');
if (monthlyCtx) {
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthlyTrends, 'month_name')); ?>,
            datasets: [{
                label: 'Incidents',
                data: <?php echo json_encode(array_column($monthlyTrends, 'incident_count')); ?>,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>