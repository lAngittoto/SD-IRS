<?php
ob_start();

// Safety fallbacks
$logs       = $logs       ?? [];
$totalLogs  = $totalLogs  ?? 0;
$totalPages = $totalPages ?? 1;
$page       = $page       ?? 1;
$stats      = $stats      ?? [];
$filters    = $filters    ?? [];
$modules    = $modules    ?? [];
$actions    = $actions    ?? [];

$actionColors = [
    'CREATE'     => 'bg-green-100 text-green-700',
    'UPDATE'     => 'bg-blue-100 text-blue-700',
    'DELETE'     => 'bg-red-100 text-red-700',
    'LOGIN'      => 'bg-yellow-100 text-yellow-700',
    'LOGOUT'     => 'bg-gray-100 text-gray-600',
    'VIEW'       => 'bg-purple-100 text-purple-700',
    'UPLOAD'     => 'bg-indigo-100 text-indigo-700',
    'CLEAR_LOGS' => 'bg-orange-100 text-orange-700',
];

function actionBadge($action, $colors) {
    $class = $colors[$action] ?? 'bg-gray-100 text-gray-600';
    return "<span class=\"inline-block px-2 py-0.5 rounded-full text-xs font-semibold $class\">"
         . htmlspecialchars($action) . "</span>";
}
?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-clock-rotate-left text-[#f8c922] text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-gray-900">Audit Log</h1>
                <p class="text-sm text-gray-600 mt-1">Track all admin actions across the system.</p>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-list text-gray-500 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Logs</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($stats['total_logs'] ?? 0) ?></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-day text-emerald-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Today</p>
                <p class="text-3xl font-black text-[#043915]"><?= number_format($stats['today_logs'] ?? 0) ?></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Unique Users</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($stats['unique_users'] ?? 0) ?></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex items-center gap-5">
            <div class="w-12 h-12 bg-violet-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-cubes text-violet-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Modules Touched</p>
                <p class="text-3xl font-black text-gray-900"><?= number_format($stats['modules_touched'] ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <input id="f-search" type="text" placeholder="Search..."
                value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                class="col-span-2 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">

            <select id="f-module" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">
                <option value="">All Modules</option>
                <?php foreach ($modules as $m): ?>
                    <option value="<?= htmlspecialchars($m) ?>" <?= ($filters['module'] ?? '') === $m ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="f-action" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">
                <option value="">All Actions</option>
                <?php foreach ($actions as $a): ?>
                    <option value="<?= htmlspecialchars($a) ?>" <?= ($filters['action'] ?? '') === $a ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input id="f-date-from" type="date" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">

            <input id="f-date-to" type="date" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">
        </div>

        <div class="flex items-center justify-between mt-4">
            <div class="flex gap-2">
                <button onclick="loadLogs(1)"
                    class="bg-[#043915] text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-[#065a20] transition-colors flex items-center gap-2">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <button onclick="resetFilters()"
                    class="text-gray-500 border border-gray-200 hover:bg-gray-50 px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                    <i class="fas fa-rotate-left"></i> Reset
                </button>
            </div>
            <button onclick="confirmClear()"
                class="text-red-500 border border-red-200 hover:bg-red-50 px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                <i class="fas fa-trash"></i> Clear Logs
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Date & Time</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Action</th>
                        <th class="px-4 py-3 text-left">Module</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-left">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-700">
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-gray-400 py-16">
                            <i class="fas fa-clock-rotate-left text-4xl mb-3 block text-gray-200"></i>
                            No audit logs found.
                        </td>
                    </tr>
                    <?php else: foreach ($logs as $i => $log): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-400 text-xs"><?= ($page - 1) * 20 + $i + 1 ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                            <?= date('M d, Y h:i A', strtotime($log['created_at'])) ?>
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800"><?= htmlspecialchars($log['user_name'] ?? '—') ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#043915]/10 text-[#043915]">
                                <?= htmlspecialchars($log['user_role'] ?? '—') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3"><?= actionBadge($log['action'], $actionColors) ?></td>
                        <td class="px-4 py-3 text-gray-600 text-xs font-medium"><?= htmlspecialchars($log['module']) ?></td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate"
                            title="<?= htmlspecialchars($log['description'] ?? '') ?>">
                            <?= htmlspecialchars($log['description'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400 font-mono"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination — only show when there are logs -->
        <?php if ($totalLogs > 0): ?>
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 text-sm text-gray-500">
            <span>
                Showing <strong><?= ($page - 1) * 20 + 1 ?>–<?= min($page * 20, $totalLogs) ?></strong>
                of <strong><?= number_format($totalLogs) ?></strong> logs
            </span>
            <?php if ($totalPages > 1): ?>
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                <button onclick="loadLogs(<?= $page - 1 ?>)"
                    class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs font-semibold transition-colors">
                    <i class="fas fa-chevron-left mr-1"></i> Prev
                </button>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end   = min($totalPages, $page + 2);
                for ($p = $start; $p <= $end; $p++):
                    $active = $p === $page
                        ? 'bg-[#043915] text-white border-[#043915]'
                        : 'border-gray-200 hover:bg-gray-50';
                ?>
                <button onclick="loadLogs(<?= $p ?>)"
                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-colors <?= $active ?>">
                    <?= $p ?>
                </button>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <button onclick="loadLogs(<?= $page + 1 ?>)"
                    class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs font-semibold transition-colors">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</main>

<!-- Clear Logs Modal -->
<div id="clear-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="flex flex-col items-center text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-trash text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-black text-gray-900 mb-2">Clear All Logs?</h3>
            <p class="text-sm text-gray-500 mb-6">
                This will permanently delete <strong>all audit logs</strong>. This action cannot be undone.
            </p>
            <div class="flex gap-3 w-full">
                <button onclick="closeModal()"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="clearLogs()"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition-colors">
                    Yes, Clear All
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function loadLogs(p) {
    const params = new URLSearchParams({
        page:      'audit-log',   // router param
        p:         p,             // pagination param (matches $_GET['p'] in controller)
        search:    document.getElementById('f-search').value.trim(),
        module:    document.getElementById('f-module').value,
        f_action:  document.getElementById('f-action').value,
        date_from: document.getElementById('f-date-from').value,
        date_to:   document.getElementById('f-date-to').value,
    });
    window.location.href = `?${params.toString()}`;
}

function resetFilters() {
    window.location.href = '?page=audit-log';
}

function confirmClear() {
    document.getElementById('clear-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('clear-modal').classList.add('hidden');
}

function clearLogs() {
    const fd = new FormData();
    fd.append('ajax_action', 'clear_logs');

    fetch('?page=audit-log', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = '?page=audit-log';
            } else {
                alert(data.message ?? 'Failed to clear logs.');
                closeModal();
            }
        })
        .catch(() => {
            alert('An error occurred. Please try again.');
            closeModal();
        });
}

document.getElementById('clear-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>