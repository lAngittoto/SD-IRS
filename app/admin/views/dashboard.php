<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-chart-pie text-[#f8c922] text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-600 mt-1">Overview of students, faculty, and incident reports</p>
            </div>
        </div>
    </div>

    <!-- ── Stats Cards ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">

        <!-- Total Students -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-user-graduate text-blue-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Students</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($totalStudents) ?></p>
            </div>
        </div>

        <!-- Total Faculty -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-chalkboard-user text-indigo-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Faculty</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($totalFaculty) ?></p>
            </div>
        </div>

        <!-- Total Incidents -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-triangle-exclamation text-red-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Incidents</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($totalIncidents) ?></p>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-clock text-amber-500 text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Pending</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($pendingReports) ?></p>
            </div>
        </div>

        <!-- Reviewed -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-eye text-blue-500 text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Reviewed</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($reviewedReports) ?></p>
            </div>
        </div>

        <!-- Resolved -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-circle-check text-emerald-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Resolved</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($resolvedCases) ?></p>
            </div>
        </div>

    </div>

    <!-- ── Charts Row ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">

        <!-- Monthly Trend Line Chart -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-[#043915] text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Monthly Incident Trends</h2>
                    <p class="text-xs text-gray-500">Last 6 months</p>
                </div>
            </div>
            <?php if (!empty($monthlyTrends)): ?>
            <div class="flex-1 relative" style="min-height:220px;">
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
            <?php else: ?>
            <div class="flex-1 flex flex-col items-center justify-center py-12 text-center">
                <i class="fas fa-chart-line text-gray-200 text-5xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">No trend data available yet.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Status Doughnut Chart -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-pie text-[#043915] text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Incident Status Breakdown</h2>
                    <p class="text-xs text-gray-500">All time</p>
                </div>
            </div>
            <?php $hasStatusData = ($statusBreakdown['pending'] + $statusBreakdown['reviewed'] + $statusBreakdown['resolved']) > 0; ?>
            <?php if ($hasStatusData): ?>
            <div class="flex-1 flex items-center justify-center" style="min-height:220px;">
                <div class="w-56 h-56">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="flex items-center justify-center gap-5 mt-4 flex-wrap">
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                    <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span> Pending (<?= $statusBreakdown['pending'] ?>)
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                    <span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span> Reviewed (<?= $statusBreakdown['reviewed'] ?>)
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                    <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span> Resolved (<?= $statusBreakdown['resolved'] ?>)
                </div>
            </div>
            <?php else: ?>
            <div class="flex-1 flex flex-col items-center justify-center py-12 text-center">
                <i class="fas fa-chart-pie text-gray-200 text-5xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">No incident data yet.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ── Bottom Row: Recent Incidents + Top Violations + Policy List ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Recent Incidents -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center gap-3">
                <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-[#043915]"></i>
                </div>
                <h2 class="text-sm font-bold text-gray-900">Recent Incidents</h2>
            </div>
            <?php if (!empty($recentIncidents)): ?>
            <div class="divide-y divide-gray-50">
                <?php foreach ($recentIncidents as $inc):
                    $sc = match($inc['status']) {
                        'pending'  => 'bg-amber-100 text-amber-700',
                        'reviewed' => 'bg-blue-100 text-blue-700',
                        'resolved' => 'bg-emerald-100 text-emerald-700',
                        default    => 'bg-gray-100 text-gray-500',
                    };
                    $targetBg = match($inc['report_target']) {
                        'student' => 'bg-blue-600',
                        'teacher' => 'bg-violet-600',
                        default   => 'bg-gray-400',
                    };
                ?>
                <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                    <div class="w-9 h-9 <?= $targetBg ?> rounded-xl flex items-center justify-center text-white font-bold text-sm shrink-0">
                        <?= strtoupper(substr($inc['reported_name'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($inc['reported_name']) ?></p>
                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($inc['violation_display']) ?></p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase <?= $sc ?>"><?= ucfirst($inc['status']) ?></span>
                        <span class="text-[10px] text-gray-400"><?= date('M d, Y', strtotime($inc['created_at'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <i class="fas fa-clipboard-list text-gray-200 text-5xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">No incidents recorded yet.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Policy Violations List -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-gavel text-[#043915]"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-900">Policy Violations</h2>
                </div>
                <span class="text-xs font-bold text-gray-400"><?= count($allViolations) ?> total</span>
            </div>
            <div class="flex-1 overflow-y-auto" style="max-height:380px;">
                <?php if (!empty($allViolations)): ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($allViolations as $v):
                        $sev = strtolower($v['severity'] ?? '');
                        $sevCls = str_contains($sev, 'major') || str_contains($sev, 'severe')
                            ? 'bg-red-100 text-red-700'
                            : (str_contains($sev, 'moderate') || str_contains($sev, 'medium')
                                ? 'bg-amber-100 text-amber-700'
                                : 'bg-blue-100 text-blue-700');
                    ?>
                    <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-gray-50 transition-colors">
                        <p class="text-sm text-gray-800 font-medium truncate flex-1"><?= htmlspecialchars($v['violation_name']) ?></p>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg whitespace-nowrap <?= $sevCls ?>"><?= htmlspecialchars($v['severity'] ?? 'N/A') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                    <i class="fas fa-gavel text-gray-200 text-4xl mb-3"></i>
                    <p class="text-sm text-gray-400 font-medium">No violations configured.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ── Top Violations Bar Section ── -->
    <?php if (!empty($topViolations)): ?>
    <div class="mt-5 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-bar text-[#043915] text-lg"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900">Most Common Violations</h2>
                <p class="text-xs text-gray-500">By number of incidents</p>
            </div>
        </div>
        <?php
            $maxCount = max(array_column($topViolations, 'violation_count') ?: [1]);
        ?>
        <div class="space-y-4">
            <?php foreach ($topViolations as $v):
                $pct = $maxCount > 0 ? round(($v['violation_count'] / $maxCount) * 100) : 0;
                $sev = strtolower($v['severity'] ?? '');
                $barCls = str_contains($sev, 'major') || str_contains($sev, 'severe')
                    ? 'bg-red-400'
                    : (str_contains($sev, 'moderate') || str_contains($sev, 'medium')
                        ? 'bg-amber-400'
                        : 'bg-[#043915]');
            ?>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($v['violation_name']) ?></p>
                    <div class="flex items-center gap-2">
                        <?php if (!empty($v['severity'])): ?>
                        <span class="text-xs px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600 font-medium"><?= htmlspecialchars($v['severity']) ?></span>
                        <?php endif; ?>
                        <span class="text-sm font-black text-gray-900 min-w-[2rem] text-right"><?= $v['violation_count'] ?></span>
                    </div>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5">
                    <div class="<?= $barCls ?> h-2.5 rounded-full transition-all duration-500" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($monthlyTrends)): ?>
/* ── Monthly Trend Line ── */
(function () {
    const ctx = document.getElementById('monthlyTrendsChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels:   <?= json_encode(array_column($monthlyTrends, 'month_name')) ?>,
            datasets: [{
                label:           'Incidents',
                data:            <?= json_encode(array_map('intval', array_column($monthlyTrends, 'incident_count'))) ?>,
                borderColor:     '#043915',
                backgroundColor: 'rgba(4,57,21,0.08)',
                pointBackgroundColor: '#f8c922',
                pointBorderColor:     '#043915',
                pointRadius:     5,
                pointHoverRadius:7,
                tension:         0.4,
                fill:            true,
                borderWidth:     2.5,
            }]
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#043915',
                    titleColor:      '#f8c922',
                    bodyColor:       '#fff',
                    padding:         10,
                    cornerRadius:    10,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks:  { stepSize: 1, color: '#9ca3af', font: { size: 11 } },
                    grid:   { color: '#f3f4f6' },
                },
                x: {
                    ticks: { color: '#9ca3af', font: { size: 11 } },
                    grid:  { display: false },
                }
            }
        }
    });
})();
<?php endif; ?>

<?php if (($statusBreakdown['pending'] + $statusBreakdown['reviewed'] + $statusBreakdown['resolved']) > 0): ?>
/* ── Status Doughnut ── */
(function () {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Reviewed', 'Resolved'],
            datasets: [{
                data:            [<?= $statusBreakdown['pending'] ?>, <?= $statusBreakdown['reviewed'] ?>, <?= $statusBreakdown['resolved'] ?>],
                backgroundColor: ['#fbbf24', '#60a5fa', '#34d399'],
                borderColor:     ['#f59e0b', '#3b82f6', '#10b981'],
                borderWidth:     2,
                hoverOffset:     6,
            }]
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            cutout:              '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#043915',
                    titleColor:      '#f8c922',
                    bodyColor:       '#fff',
                    padding:         10,
                    cornerRadius:    10,
                }
            }
        }
    });
})();
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>