<?php ob_start();
$currentYear = date('Y');
?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-chart-pie text-[#f8c922] text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900">Reports & Analytics</h1>
                    <p class="text-sm text-gray-600 mt-1">Comprehensive overview of incidents, advisories, and violations</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[150px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Month</label>
                <select name="month" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">All Months</option>
                    <?php for ($m = 1; $m <= 12; $m++):
                        $sel = ($filterMonth !== null && $filterMonth === $m) ? 'selected' : '';
                    ?>
                    <option value="<?= $m ?>" <?= $sel ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Year</label>
                <select name="year" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">All Years</option>
                    <?php for ($y = 2025; $y <= 2030; $y++):
                        $sel = ($filterYear !== null && $filterYear === $y) ? 'selected' : '';
                    ?>
                    <option value="<?= $y ?>" <?= $sel ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#043915] hover:bg-[#032a0f] text-white rounded-xl text-sm font-bold transition-all shadow-md">
                <i class="fas fa-filter"></i> Apply
            </button>
            <a href="?" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] rounded-xl text-sm font-bold transition-all shadow-sm">
                <i class="fas fa-rotate"></i> Reset
            </a>
            <?php if ($filterYear !== null || $filterMonth !== null): ?>
            <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-xs font-bold">
                <i class="fas fa-circle-info"></i>
                Filtered:
                <?= $filterMonth !== null ? date('F', mktime(0, 0, 0, $filterMonth, 1)) . ' ' : '' ?>
                <?= $filterYear !== null ? $filterYear : '' ?>
            </div>
            <?php endif; ?>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-sm font-bold transition-all shadow-sm ml-auto">
                <i class="fas fa-print"></i> Print Report
            </button>
        </form>
    </div>

    <!-- ── Stats Cards ── -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">

        <?php
        $cards = [
            ['icon' => 'fa-user-graduate',        'label' => 'Total Students',  'value' => $totalStudents,      'bg' => 'bg-blue-50',    'clr' => 'text-blue-600'],
            ['icon' => 'fa-chalkboard-user',       'label' => 'Faculty',         'value' => $totalFaculty,       'bg' => 'bg-indigo-50',  'clr' => 'text-indigo-600'],
            ['icon' => 'fa-triangle-exclamation',  'label' => 'Total Incidents', 'value' => $totalIncidents,     'bg' => 'bg-red-50',     'clr' => 'text-red-600'],
            ['icon' => 'fa-clock',                 'label' => 'Pending',         'value' => $pendingReports,     'bg' => 'bg-amber-50',   'clr' => 'text-amber-500'],
            ['icon' => 'fa-eye',                   'label' => 'Reviewed',        'value' => $reviewedReports,    'bg' => 'bg-sky-50',     'clr' => 'text-sky-600'],
            ['icon' => 'fa-circle-check',          'label' => 'Resolved',        'value' => $resolvedCases,      'bg' => 'bg-emerald-50', 'clr' => 'text-emerald-600'],
            ['icon' => 'fa-users',                 'label' => 'Assigned',        'value' => $assignedStudents,   'bg' => 'bg-green-50',   'clr' => 'text-green-700'],
            ['icon' => 'fa-user-xmark',            'label' => 'Unassigned',      'value' => $unassignedStudents, 'bg' => 'bg-rose-50',    'clr' => 'text-rose-600'],
        ];
        foreach ($cards as $c):
        ?>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-5 flex items-center gap-4">
            <div class="w-12 h-12 <?= $c['bg'] ?> rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas <?= $c['icon'] ?> <?= $c['clr'] ?> text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5"><?= $c['label'] ?></p>
                <p class="text-2xl font-black text-gray-900"><?= number_format($c['value']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- ── Charts Row ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

        <!-- Monthly Trend -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-[#043915] text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Monthly Incident Trends</h2>
                    <p class="text-xs text-gray-400">
                        <?php
                        if ($filterYear !== null && $filterMonth !== null) {
                            echo date('F', mktime(0,0,0,$filterMonth,1)) . ' ' . $filterYear;
                        } elseif ($filterYear !== null) {
                            echo 'Year ' . $filterYear;
                        } else {
                            echo 'Last 6 months';
                        }
                        ?>
                    </p>
                </div>
            </div>
            <?php if (!empty($monthlyTrends)): ?>
            <div class="flex-1 relative" style="min-height:220px;">
                <canvas id="monthlyChart"></canvas>
            </div>
            <?php else: ?>
            <div class="flex-1 flex flex-col items-center justify-center py-12">
                <i class="fas fa-chart-line text-gray-200 text-5xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">
                    <?php if ($filterYear !== null || $filterMonth !== null): ?>
                        No incidents recorded for
                        <?= $filterMonth !== null ? date('F', mktime(0,0,0,$filterMonth,1)) . ' ' : '' ?>
                        <?= $filterYear ?? '' ?>.
                    <?php else: ?>
                        No incidents in the last 6 months.
                    <?php endif; ?>
                </p>
                <?php if ($filterYear !== null || $filterMonth !== null): ?>
                <a href="?" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#043915] hover:underline">
                    <i class="fas fa-rotate"></i> Clear filter
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Incidents by Grade -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-layer-group text-[#043915] text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">By Grade Level</h2>
                    <p class="text-xs text-gray-400">Student incidents</p>
                </div>
            </div>
            <?php if (!empty($incidentsByGrade)): ?>
            <div class="flex-1 flex items-center justify-center" style="min-height:220px;">
                <canvas id="gradeChart"></canvas>
            </div>
            <?php else: ?>
            <div class="flex-1 flex flex-col items-center justify-center py-12">
                <i class="fas fa-layer-group text-gray-200 text-5xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">No student incidents yet.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ── Bottom Row ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

        <!-- Recent Resolutions Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-circle-check text-[#043915]"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-900">Recent Resolutions</h2>
                </div>
                <span class="text-xs font-bold px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg">
                    <?= count($recentResolutions) ?> resolved
                </span>
            </div>
            <div class="overflow-x-auto overflow-y-auto flex-1" style="max-height:340px;">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white">
                            <th class="py-3 px-5 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Reported Individual</th>
                            <th class="py-3 px-5 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Violation</th>
                            <th class="py-3 px-5 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Severity</th>
                            <th class="py-3 px-5 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Resolved By</th>
                            <th class="py-3 px-5 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (!empty($recentResolutions)): foreach ($recentResolutions as $r):
                            $sev = strtolower($r['severity'] ?? '');
                            $sevCls = str_contains($sev, 'extreme') || str_contains($sev, 'grave')
                                ? 'bg-red-100 text-red-700'
                                : (str_contains($sev, 'less grave') || str_contains($sev, 'moderate')
                                    ? 'bg-amber-100 text-amber-700'
                                    : 'bg-blue-100 text-blue-700');
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-5 text-sm font-semibold text-gray-800 whitespace-nowrap"><?= htmlspecialchars($r['reported_name']) ?></td>
                            <td class="py-3 px-5 text-sm text-gray-600 max-w-[160px]">
                                <span class="block truncate" title="<?= htmlspecialchars($r['violation_display']) ?>"><?= htmlspecialchars($r['violation_display']) ?></span>
                            </td>
                            <td class="py-3 px-5">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg <?= $sevCls ?>"><?= htmlspecialchars($r['severity'] ?? 'N/A') ?></span>
                            </td>
                            <td class="py-3 px-5 text-sm text-gray-500 whitespace-nowrap"><?= htmlspecialchars($r['reviewed_by_name'] ?? '—') ?></td>
                            <td class="py-3 px-5 text-xs text-gray-400 whitespace-nowrap">
                                <?= $r['reviewed_at'] ? date('M d, Y', strtotime($r['reviewed_at'])) : '—' ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <i class="fas fa-circle-check text-gray-200 text-4xl mb-3 block"></i>
                                <p class="text-sm text-gray-400 font-medium">No resolved incidents yet.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Advisory Summary -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-people-group text-[#043915]"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-900">Advisory Summary</h2>
                </div>
                <span class="text-xs font-bold px-3 py-1 bg-[#043915]/10 text-[#043915] rounded-lg"><?= count($advisorySummary) ?> classes</span>
            </div>

            <!-- Quick stats -->
            <div class="px-5 py-4 grid grid-cols-3 gap-3 border-b border-gray-50 shrink-0">
                <div class="text-center">
                    <p class="text-lg font-black text-gray-900"><?= $assignedStudents ?></p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Assigned</p>
                </div>
                <div class="text-center border-x border-gray-100">
                    <p class="text-lg font-black text-rose-600"><?= $unassignedStudents ?></p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Unassigned</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-black text-[#043915]"><?= $activeAdvisories ?></p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Classes</p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto" style="max-height:240px;">
                <?php if (!empty($advisorySummary)): ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($advisorySummary as $a): ?>
                    <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-gray-50 transition-colors">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($a['advisory_name']) ?></p>
                            <p class="text-xs text-gray-400 truncate">Grade <?= $a['grade_level'] ?> · <?= htmlspecialchars($a['teacher_name'] ?? '—') ?></p>
                        </div>
                        <span class="text-xs font-black text-[#043915] bg-[#043915]/10 px-2.5 py-1 rounded-lg shrink-0"><?= $a['student_count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="flex flex-col items-center justify-center py-12 text-center px-5">
                    <i class="fas fa-people-group text-gray-200 text-4xl mb-3"></i>
                    <p class="text-sm text-gray-400 font-medium">No advisory classes yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ── Top Violations + All Violations Table ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Top 5 Violations Bar -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-bar text-[#043915] text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Top Violations</h2>
                    <p class="text-xs text-gray-400">By incident count</p>
                </div>
            </div>
            <?php
            $maxCount = !empty($topViolations) ? max(array_column($topViolations, 'violation_count') ?: [1]) : 1;
            if (!empty($topViolations)):
            ?>
            <div class="space-y-4">
                <?php foreach ($topViolations as $v):
                    $pct    = $maxCount > 0 ? round(($v['violation_count'] / $maxCount) * 100) : 0;
                    $sev    = strtolower($v['severity'] ?? '');
                    $barCls = str_contains($sev, 'extreme') || str_contains($sev, 'grave')
                        ? 'bg-red-400'
                        : (str_contains($sev, 'less grave') || str_contains($sev, 'moderate')
                            ? 'bg-amber-400'
                            : 'bg-[#043915]');
                ?>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-sm font-semibold text-gray-800 truncate max-w-[200px]"><?= htmlspecialchars($v['violation_name']) ?></p>
                        <div class="flex items-center gap-2 shrink-0">
                            <?php if (!empty($v['severity'])): ?>
                            <span class="text-xs px-2 py-0.5 rounded-lg bg-gray-100 text-gray-500 font-medium"><?= htmlspecialchars($v['severity']) ?></span>
                            <?php endif; ?>
                            <span class="text-sm font-black text-gray-900 min-w-[1.5rem] text-right"><?= $v['violation_count'] ?></span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="<?= $barCls ?> h-2.5 rounded-full transition-all duration-500" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="flex flex-col items-center justify-center py-12">
                <i class="fas fa-chart-bar text-gray-200 text-4xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">No violation data yet.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- All Violations Table -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-gavel text-[#043915]"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-900">All Policy Violations</h2>
                </div>
                <span class="text-xs font-bold text-gray-400"><?= count($allViolations) ?> total</span>
            </div>
            <div class="overflow-x-auto overflow-y-auto flex-1" style="max-height:360px;">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white">
                            <th class="py-3 px-5 text-left text-xs font-bold uppercase tracking-wide">Violation</th>
                            <th class="py-3 px-5 text-left text-xs font-bold uppercase tracking-wide">Severity</th>
                            <th class="py-3 px-5 text-center text-xs font-bold uppercase tracking-wide">Incidents</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (!empty($allViolations)): foreach ($allViolations as $v):
                            $sev    = strtolower($v['severity'] ?? '');
                            $sevCls = str_contains($sev, 'extreme') || str_contains($sev, 'grave')
                                ? 'bg-red-100 text-red-700'
                                : (str_contains($sev, 'less grave') || str_contains($sev, 'moderate')
                                    ? 'bg-amber-100 text-amber-700'
                                    : 'bg-blue-100 text-blue-700');
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-5 text-sm font-medium text-gray-800"><?= htmlspecialchars($v['violation_name']) ?></td>
                            <td class="py-3 px-5">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg <?= $sevCls ?>"><?= htmlspecialchars($v['severity'] ?? 'N/A') ?></span>
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="text-sm font-black text-gray-900"><?= $v['incident_count'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="3" class="py-12 text-center">
                                <i class="fas fa-gavel text-gray-200 text-4xl mb-3 block"></i>
                                <p class="text-sm text-gray-400">No violations configured.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($monthlyTrends)): ?>
/* ── Monthly Trend Line ── */
(function () {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($monthlyTrends, 'month_name')) ?>,
            datasets: [{
                label: 'Incidents',
                data: <?= json_encode(array_map('intval', array_column($monthlyTrends, 'incident_count'))) ?>,
                borderColor:          '#043915',
                backgroundColor:      'rgba(4,57,21,0.07)',
                pointBackgroundColor: '#f8c922',
                pointBorderColor:     '#043915',
                pointRadius: 5, pointHoverRadius: 7,
                tension: 0.4, fill: true, borderWidth: 2.5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#043915', titleColor: '#f8c922', bodyColor: '#fff', padding: 10, cornerRadius: 10 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, color: '#9ca3af', font: { size: 11 } }, grid: { color: '#f3f4f6' } },
                x: { ticks: { color: '#9ca3af', font: { size: 11 } }, grid: { display: false } }
            }
        }
    });
})();
<?php endif; ?>

<?php if (!empty($incidentsByGrade)): ?>
/* ── Incidents by Grade Doughnut ── */
(function () {
    const ctx = document.getElementById('gradeChart');
    if (!ctx) return;
    const palette = ['#043915', '#f8c922', '#10b981', '#3b82f6', '#8b5cf6', '#ef4444'];
    const labels  = <?= json_encode(array_map(fn($r) => 'Grade ' . $r['grade_level'], $incidentsByGrade)) ?>;
    const data    = <?= json_encode(array_map(fn($r) => (int)$r['total'], $incidentsByGrade)) ?>;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: palette.slice(0, data.length),
                borderWidth: 2,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } },
                tooltip: { backgroundColor: '#043915', titleColor: '#f8c922', bodyColor: '#fff', padding: 10, cornerRadius: 10 }
            }
        }
    });
})();
<?php endif; ?>
</script>

<style>
@media print {
    aside, header, form, button, a[href] { display: none !important; }
    main { margin-left: 0 !important; width: 100% !important; padding: 0.5rem !important; background: white !important; }
    .shadow-sm, .shadow-md, .shadow-lg { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>