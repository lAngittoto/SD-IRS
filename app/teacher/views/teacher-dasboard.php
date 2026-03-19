<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/teacher-sidebar.php'; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-gauge-high text-[#f8c922] text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900">
                        Welcome, <?= htmlspecialchars(explode(' ', $teacher['name'])[0]) ?>!
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        <?php if ($schoolYear): ?>
                            S.Y. <?= $schoolYear['start_year'] ?> – <?= $schoolYear['end_year'] ?> &nbsp;·&nbsp;
                        <?php endif; ?>
                        <?= date('l, F j, Y') ?>
                    </p>
                </div>
            </div>
            <?php if ($stats['advisory_name']): ?>
            <div class="flex items-center gap-3 bg-white rounded-2xl shadow-sm px-5 py-3 border border-gray-100">
                <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-chalkboard-user text-[#043915]"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Advisory Class</p>
                    <p class="text-sm font-black text-gray-900"><?= htmlspecialchars($stats['advisory_name']) ?> · Grade <?= $stats['advisory_grade'] ?></p>
                </div>
                <span class="ml-2 px-2.5 py-1 bg-[#f8c922] text-[#043915] text-xs font-black rounded-lg"><?= $stats['advisory_students'] ?> students</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Stats Cards ── -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <?php
        $cards = [
            ['icon' => 'fa-clipboard-list',      'label' => 'Total Reports',   'value' => $stats['total_reports'],    'bg' => 'bg-[#043915]/10', 'clr' => 'text-[#043915]'],
            ['icon' => 'fa-clock',               'label' => 'Pending',         'value' => $stats['pending_reports'],  'bg' => 'bg-amber-50',     'clr' => 'text-amber-500'],
            ['icon' => 'fa-eye',                 'label' => 'Reviewed',        'value' => $stats['reviewed_reports'], 'bg' => 'bg-blue-50',      'clr' => 'text-blue-600'],
            ['icon' => 'fa-circle-check',        'label' => 'Resolved',        'value' => $stats['resolved_reports'], 'bg' => 'bg-emerald-50',   'clr' => 'text-emerald-600'],
        ];
        foreach ($cards as $c):
        ?>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-5 flex items-center gap-4">
            <div class="w-12 h-12 <?= $c['bg'] ?> rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas <?= $c['icon'] ?> <?= $c['clr'] ?> text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5"><?= $c['label'] ?></p>
                <p class="text-2xl font-black text-gray-900"><?= $c['value'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Main Content Row ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

        <!-- Monthly Trend Chart (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-[#043915] text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">My Report Trends</h2>
                    <p class="text-xs text-gray-400">Incidents I submitted — last 6 months</p>
                </div>
            </div>
            <?php if (!empty($monthlyTrend)): ?>
            <div class="flex-1 relative" style="min-height:200px;">
                <canvas id="trendChart"></canvas>
            </div>
            <?php else: ?>
            <div class="flex-1 flex flex-col items-center justify-center py-12">
                <i class="fas fa-chart-line text-gray-200 text-5xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">No reports submitted in the last 6 months.</p>
                <a href="report-incident" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#043915] hover:underline">
                    <i class="fas fa-plus"></i> File your first report
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Advisory Flagged Students -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-[#043915]"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-900">Advisory Students</h2>
                </div>
                <?php if ($stats['flagged_students'] > 0): ?>
                <span class="px-2.5 py-1 bg-red-50 text-red-600 text-xs font-black rounded-lg">
                    <?= $stats['flagged_students'] ?> flagged
                </span>
                <?php endif; ?>
            </div>

            <?php if (empty($advisoryStudents) && !$stats['advisory_id']): ?>
            <div class="flex-1 flex flex-col items-center justify-center py-12 px-6 text-center">
                <i class="fas fa-chalkboard text-gray-200 text-4xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">No advisory class assigned.</p>
                <p class="text-xs text-gray-300 mt-1">Contact admin to set up your advisory.</p>
            </div>

            <?php elseif (empty($advisoryStudents)): ?>
            <div class="flex-1 flex flex-col items-center justify-center py-12 px-6 text-center">
                <i class="fas fa-users text-gray-200 text-4xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">No students assigned yet.</p>
            </div>

            <?php else: ?>
            <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                <?php foreach ($advisoryStudents as $s):
                    $hasUnresolved = $s['unresolved_count'] > 0;
                    $hasIncidents  = $s['incident_count']  > 0;
                    $dotCls = $hasUnresolved ? 'bg-red-500' : ($hasIncidents ? 'bg-amber-400' : 'bg-emerald-400');
                    $initial = strtoupper(substr($s['name'], 0, 1));
                ?>
                <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                    <?php if (!empty($s['profile_pix'])): ?>
                    <img src="<?= htmlspecialchars($s['profile_pix']) ?>" class="w-9 h-9 rounded-xl object-cover shrink-0">
                    <?php else: ?>
                    <div class="w-9 h-9 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-xl flex items-center justify-center text-white font-bold text-sm shrink-0">
                        <?= $initial ?>
                    </div>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($s['name']) ?></p>
                        <p class="text-xs text-gray-400">Grade <?= $s['grade_level'] ?></p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <?php if ($s['incident_count'] > 0): ?>
                        <span class="text-xs font-black <?= $hasUnresolved ? 'text-red-600' : 'text-amber-500' ?>">
                            <?= $s['incident_count'] ?> incident<?= $s['incident_count'] > 1 ? 's' : '' ?>
                        </span>
                        <?php endif; ?>
                        <span class="w-2.5 h-2.5 rounded-full <?= $dotCls ?> shrink-0"></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($stats['advisory_students'] > 5): ?>
            <div class="px-5 py-3 border-t border-gray-50 shrink-0">
                <a href="advisory" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#043915] hover:underline">
                    <i class="fas fa-arrow-right"></i> View all <?= $stats['advisory_students'] ?> students
                </a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- ── Recent Reports Table ── -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3 shrink-0 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-[#043915]"></i>
                </div>
                <p class="text-sm font-bold text-gray-900">My Recent Reports</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white">
                        <th class="py-3 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">#</th>
                        <th class="py-3 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Reported Individual</th>
                        <th class="py-3 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Violation</th>
                        <th class="py-3 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Location</th>
                        <th class="py-3 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Status</th>
                        <th class="py-3 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($recentReports)): ?>
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <i class="fas fa-clipboard-list text-gray-200 text-5xl mb-3 block"></i>
                            <p class="text-sm text-gray-400 font-medium">You haven't submitted any reports yet.</p>
                            <a href="report-incident" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#043915] hover:underline">
                                <i class="fas fa-flag"></i> File a report
                            </a>
                        </td>
                    </tr>
                    <?php else: foreach ($recentReports as $r):
                        $sc = match($r['status']) {
                            'pending'  => 'bg-amber-100 text-amber-700',
                            'reviewed' => 'bg-blue-100 text-blue-700',
                            'resolved' => 'bg-emerald-100 text-emerald-700',
                            default    => 'bg-gray-100 text-gray-500',
                        };
                        $targetBadge = match($r['report_target']) {
                            'student' => '<span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-600 rounded uppercase">Student</span>',
                            'teacher' => '<span class="px-1.5 py-0.5 text-[9px] font-bold bg-violet-50 text-violet-600 rounded uppercase">Teacher</span>',
                            default   => '<span class="px-1.5 py-0.5 text-[9px] font-bold bg-gray-100 text-gray-500 rounded uppercase">Other</span>',
                        };
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-6 text-xs text-gray-400 font-mono"><?= $r['report_id'] ?></td>
                        <td class="py-3 px-6">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <?= $targetBadge ?>
                                <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($r['reported_name']) ?></span>
                            </div>
                        </td>
                        <td class="py-3 px-6 max-w-[160px]">
                            <span class="text-sm text-gray-600 truncate block" title="<?= htmlspecialchars($r['violation_display']) ?>">
                                <?= htmlspecialchars($r['violation_display']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-6">
                            <span class="text-sm text-gray-500"><?= htmlspecialchars($r['location']) ?></span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase <?= $sc ?>"><?= ucfirst($r['status']) ?></span>
                        </td>
                        <td class="py-3 px-6 text-center text-xs text-gray-400 whitespace-nowrap">
                            <?= date('M d, Y', strtotime($r['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($recentReports) && $stats['total_reports'] > 6): ?>
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex justify-end">
            <a href="report-incident" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#043915] hover:underline">
                <i class="fas fa-arrow-right"></i> View all <?= $stats['total_reports'] ?> reports
            </a>
        </div>
        <?php endif; ?>
    </div>

</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($monthlyTrend)): ?>
(function () {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($monthlyTrend, 'month_name')) ?>,
            datasets: [{
                label: 'Reports',
                data: <?= json_encode(array_map('intval', array_column($monthlyTrend, 'report_count'))) ?>,
                backgroundColor: 'rgba(4,57,21,0.12)',
                borderColor:     '#043915',
                borderWidth:     2,
                borderRadius:    8,
                borderSkipped:   false,
                hoverBackgroundColor: '#f8c922',
                hoverBorderColor:    '#043915',
            }]
        },
        options: {
            responsive: true,
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
                    ticks: { stepSize: 1, color: '#9ca3af', font: { size: 11 } },
                    grid: { color: '#f3f4f6' },
                },
                x: {
                    ticks: { color: '#9ca3af', font: { size: 11 } },
                    grid: { display: false },
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