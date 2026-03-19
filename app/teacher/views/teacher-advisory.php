<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/teacher-sidebar.php'; ?>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[300] flex flex-col gap-2 pointer-events-none"></div>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-chalkboard-user text-[#f8c922] text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-gray-900">My Advisory Class</h1>
                <p class="text-sm text-gray-600 mt-1">View your assigned students and their incident records</p>
            </div>
        </div>
    </div>

    <?php if (!$advisoryClass): ?>
    <!-- No Advisory Assigned -->
    <div class="bg-white rounded-2xl shadow-sm p-16 text-center hover:shadow-md transition-shadow duration-300">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-chalkboard text-gray-300 text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-700 mb-2">No Advisory Class Assigned</h2>
        <p class="text-sm text-gray-400 max-w-md mx-auto">You have not been assigned as an advisory teacher yet. Please contact the admin to set up your advisory class.</p>
    </div>

    <?php else: ?>

    <!-- Advisory Info Bar -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm p-5 flex flex-wrap items-center justify-between gap-4 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-[#043915] text-xl"></i>
            </div>
            <div>
                <p class="text-lg font-black text-gray-900"><?= htmlspecialchars($advisoryClass['advisory_name']) ?></p>
                <p class="text-xs text-gray-500 mt-0.5">
                    Grade <?= htmlspecialchars($advisoryClass['grade_level']) ?>
                    &nbsp;·&nbsp;
                    S.Y. <?= $advisoryClass['start_year'] ?> – <?= $advisoryClass['end_year'] ?>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <span class="px-4 py-1.5 bg-[#f8c922] text-[#043915] rounded-lg text-xs font-black uppercase">
                <?= $advisoryClass['sy_status'] ?>
            </span>
            <span class="px-4 py-1.5 bg-[#043915]/10 text-[#043915] rounded-lg text-xs font-black">
                <?= count($assignedStudents) ?> / 40 Students
            </span>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm p-5 hover:shadow-md transition-shadow duration-300">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Search Student</label>
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Name or LRN…"
                        value="<?= htmlspecialchars($searchQuery) ?>"
                        class="w-full pl-9 pr-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                </div>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Incidents</label>
                <select id="filterIncidents" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">All Students</option>
                    <option value="with">With Incidents</option>
                    <option value="without">No Incidents</option>
                    <option value="unresolved">Has Unresolved</option>
                </select>
            </div>
            <button onclick="resetFilters()" class="px-4 py-2.5 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] rounded-lg text-sm font-bold transition-all">
                <i class="fas fa-rotate mr-1"></i> Reset
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
        $totalStudents   = count($assignedStudents);
        $withIncidents   = count(array_filter($assignedStudents, fn($s) => $s['incident_count'] > 0));
        $withUnresolved  = count(array_filter($assignedStudents, fn($s) => $s['unresolved_count'] > 0));
        $cleanStudents   = $totalStudents - $withIncidents;
        $statCards = [
            ['icon' => 'fa-users',               'label' => 'Total Students',  'value' => $totalStudents,  'bg' => 'bg-[#043915]/10', 'clr' => 'text-[#043915]'],
            ['icon' => 'fa-circle-check',         'label' => 'No Incidents',    'value' => $cleanStudents,  'bg' => 'bg-emerald-50',   'clr' => 'text-emerald-600'],
            ['icon' => 'fa-triangle-exclamation', 'label' => 'With Incidents',  'value' => $withIncidents,  'bg' => 'bg-amber-50',     'clr' => 'text-amber-500'],
            ['icon' => 'fa-clock',                'label' => 'Unresolved',      'value' => $withUnresolved, 'bg' => 'bg-red-50',       'clr' => 'text-red-600'],
        ];
        foreach ($statCards as $c):
        ?>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-5 flex items-center gap-4">
            <div class="w-11 h-11 <?= $c['bg'] ?> rounded-xl flex items-center justify-center shrink-0">
                <i class="fas <?= $c['icon'] ?> <?= $c['clr'] ?> text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5"><?= $c['label'] ?></p>
                <p class="text-2xl font-black text-gray-900"><?= $c['value'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col" style="height:600px;">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3 shrink-0 bg-gradient-to-r from-gray-50 to-white">
            <p id="resultCount" class="text-sm font-bold text-gray-700">
                <?= count($assignedStudents) ?> student<?= count($assignedStudents) !== 1 ? 's' : '' ?> in your advisory
            </p>
        </div>
        <div class="overflow-x-auto overflow-y-auto flex-1">
            <table class="w-full text-sm" id="studentsTable">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white">
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Student</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">LRN</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Grade</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Contact</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Incidents</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Status</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <?php if (empty($assignedStudents)): ?>
                    <tr id="emptyRow">
                        <td colspan="7" class="py-20 text-center">
                            <i class="fas fa-users text-gray-200 text-5xl mb-4 block"></i>
                            <p class="text-sm text-gray-400 font-medium">No students assigned to your advisory yet.</p>
                            <p class="text-xs text-gray-300 mt-1">Ask the admin to assign students to your class.</p>
                        </td>
                    </tr>
                    <?php else: foreach ($assignedStudents as $s):
                        $hasIncidents  = $s['incident_count'] > 0;
                        $hasUnresolved = $s['unresolved_count'] > 0;
                        $statusCls = $hasUnresolved
                            ? 'bg-red-100 text-red-700'
                            : ($hasIncidents ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                        $statusLabel = $hasUnresolved ? 'Unresolved' : ($hasIncidents ? 'Has Record' : 'Clean');
                        $initial = strtoupper(substr($s['name'], 0, 1));
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors student-row"
                        data-name="<?= strtolower(htmlspecialchars($s['name'])) ?>"
                        data-lrn="<?= htmlspecialchars($s['lrn'] ?? '') ?>"
                        data-incidents="<?= $s['incident_count'] ?>"
                        data-unresolved="<?= $s['unresolved_count'] ?>">
                        <td class="py-3 px-6">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($s['profile_pix'])): ?>
                                <img src="<?= htmlspecialchars($s['profile_pix']) ?>" class="w-9 h-9 rounded-xl object-cover shrink-0" alt="">
                                <?php else: ?>
                                <div class="w-9 h-9 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-xl flex items-center justify-center text-white font-bold text-sm shrink-0">
                                    <?= $initial ?>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($s['name']) ?></p>
                                    <?php if (!empty($s['guardian_name'])): ?>
                                    <p class="text-xs text-gray-400">Guardian: <?= htmlspecialchars($s['guardian_name']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-6">
                            <span class="text-sm font-mono text-gray-600"><?= htmlspecialchars($s['lrn'] ?? '—') ?></span>
                        </td>
                        <td class="py-3 px-6">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#043915]/10 text-[#043915]">
                                Grade <?= htmlspecialchars($s['grade_level']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-6">
                            <span class="text-sm font-mono text-gray-500"><?= htmlspecialchars($s['contact_no'] ?? '—') ?></span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <span class="text-sm font-black <?= $s['incident_count'] > 0 ? 'text-red-600' : 'text-gray-400' ?>">
                                <?= $s['incident_count'] ?>
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <span class="px-3 py-1 text-xs font-bold rounded-lg uppercase <?= $statusCls ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td class="py-3 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openStudentModal(<?= $s['user_id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#043915] hover:bg-[#032a0f] text-white rounded-lg text-xs font-bold transition-all">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if ($s['incident_count'] > 0): ?>
                                <button onclick="openIncidentModal(<?= $s['user_id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-bold transition-all">
                                    <i class="fas fa-clipboard-list"></i> Incidents
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>
</main>

<!-- ══════════════════════════════════════════
     STUDENT PROFILE MODAL
══════════════════════════════════════════ -->
<div id="studentProfileModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-xl font-bold text-gray-900" id="profileModalName">Student Profile</h2>
                <p class="text-sm text-gray-500 mt-1" id="profileModalSub">—</p>
            </div>
            <button onclick="closeStudentModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5" id="profileModalBody">
            <div class="flex items-center justify-center py-16">
                <i class="fas fa-spinner fa-spin text-[#043915] text-3xl"></i>
            </div>
        </div>
        <div class="px-8 py-4 border-t border-gray-200 shrink-0 bg-gray-50 flex justify-end">
            <button onclick="closeStudentModal()" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#043915] hover:bg-[#032a0f] text-white font-bold rounded-lg text-sm transition-colors">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     INCIDENT HISTORY MODAL
══════════════════════════════════════════ -->
<div id="incidentModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Incident History</h2>
                <p class="text-sm text-gray-500 mt-1" id="incidentModalSub">—</p>
            </div>
            <button onclick="closeIncidentModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6" id="incidentModalBody">
            <div class="flex items-center justify-center py-16">
                <i class="fas fa-spinner fa-spin text-[#043915] text-3xl"></i>
            </div>
        </div>
        <div class="px-8 py-4 border-t border-gray-200 shrink-0 bg-gray-50 flex justify-end">
            <button onclick="closeIncidentModal()" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#043915] hover:bg-[#032a0f] text-white font-bold rounded-lg text-sm transition-colors">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const BASE_URL = window.location.pathname;

    /* ── Filter logic ── */
    function applyFilters() {
        const search     = document.getElementById('searchInput').value.toLowerCase().trim();
        const filterInc  = document.getElementById('filterIncidents').value;
        let visible = 0;

        document.querySelectorAll('.student-row').forEach(row => {
            const name       = row.dataset.name      || '';
            const lrn        = row.dataset.lrn       || '';
            const incidents  = parseInt(row.dataset.incidents  || '0');
            const unresolved = parseInt(row.dataset.unresolved || '0');

            const matchSearch = !search || name.includes(search) || lrn.includes(search);
            let matchFilter   = true;
            if (filterInc === 'with')       matchFilter = incidents  > 0;
            if (filterInc === 'without')    matchFilter = incidents === 0;
            if (filterInc === 'unresolved') matchFilter = unresolved > 0;

            const show = matchSearch && matchFilter;
            row.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        document.getElementById('resultCount').textContent =
            visible + ' student' + (visible !== 1 ? 's' : '') + ' shown';

        // Empty state
        let emptyRow = document.getElementById('emptyRow');
        if (visible === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'emptyRow';
                emptyRow.innerHTML = `<td colspan="7" class="py-20 text-center">
                    <i class="fas fa-magnifying-glass text-gray-200 text-4xl mb-3 block"></i>
                    <p class="text-sm text-gray-400 font-medium">No students match your filter.</p>
                </td>`;
                document.getElementById('tableBody').appendChild(emptyRow);
            } else {
                emptyRow.classList.remove('hidden');
            }
        } else if (emptyRow) {
            emptyRow.classList.add('hidden');
        }
    }

    document.getElementById('searchInput')?.addEventListener('input', applyFilters);
    document.getElementById('filterIncidents')?.addEventListener('change', applyFilters);

    window.resetFilters = function () {
        document.getElementById('searchInput').value       = '';
        document.getElementById('filterIncidents').value  = '';
        applyFilters();
    };

    /* ── Student profile modal (view from PHP data directly) ── */
    const STUDENTS = <?php
        $map = [];
        foreach ($assignedStudents as $s) {
            $map[$s['user_id']] = [
                'name'             => $s['name'],
                'lrn'              => $s['lrn'] ?? '—',
                'grade_level'      => $s['grade_level'],
                'contact_no'       => $s['contact_no'] ?? '—',
                'home_address'     => $s['home_address'] ?? '—',
                'guardian_name'    => $s['guardian_name'] ?? '—',
                'guardian_contact' => $s['guardian_contact'] ?? '—',
                'profile_pix'      => $s['profile_pix'] ?? null,
                'incident_count'   => (int)$s['incident_count'],
                'unresolved_count' => (int)$s['unresolved_count'],
            ];
        }
        echo json_encode($map);
    ?>;

    window.openStudentModal = function (studentId, name) {
        const modal = document.getElementById('studentProfileModal');
        const body  = document.getElementById('profileModalBody');
        modal.classList.remove('hidden');

        const s = STUDENTS[studentId];
        if (!s) {
            body.innerHTML = '<p class="text-sm text-red-500 text-center py-10">Student not found.</p>';
            return;
        }

        document.getElementById('profileModalName').textContent = s.name;
        document.getElementById('profileModalSub').textContent  = 'Grade ' + s.grade_level + ' · LRN: ' + (s.lrn || '—');

        const avatarHtml = s.profile_pix
            ? `<img src="${s.profile_pix}" class="w-24 h-24 rounded-2xl object-cover shadow-md mx-auto block">`
            : `<div class="w-24 h-24 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center text-white font-bold text-4xl shadow-md mx-auto">${s.name.charAt(0).toUpperCase()}</div>`;

        const badgeCls = s.unresolved_count > 0
            ? 'bg-red-100 text-red-700'
            : (s.incident_count > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
        const badgeLabel = s.unresolved_count > 0 ? 'Has Unresolved' : (s.incident_count > 0 ? 'Has Record' : 'Clean');

        body.innerHTML = `
            <div class="flex justify-center mb-6">${avatarHtml}</div>

            <div class="bg-gradient-to-br from-[#043915]/5 to-transparent rounded-2xl p-5 grid grid-cols-3 gap-3 mb-5">
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Grade</p>
                    <p class="text-lg font-black text-gray-900">${s.grade_level}</p>
                </div>
                <div class="text-center border-x border-[#043915]/10">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Incidents</p>
                    <p class="text-lg font-black ${s.incident_count > 0 ? 'text-red-600' : 'text-gray-400'}">${s.incident_count}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                    <span class="px-2 py-0.5 text-xs font-bold rounded-lg ${badgeCls}">${badgeLabel}</span>
                </div>
            </div>

            <div class="space-y-3">
                ${infoRow('LRN', s.lrn)}
                ${infoRow('Contact No.', s.contact_no)}
                ${infoRow('Home Address', s.home_address)}
                ${infoRow('Guardian', s.guardian_name)}
                ${infoRow('Guardian Contact', s.guardian_contact)}
            </div>
        `;
    };

    window.closeStudentModal = function () {
        document.getElementById('studentProfileModal').classList.add('hidden');
    };

    /* ── Incident History Modal ── */
    window.openIncidentModal = function (studentId, name) {
        const modal = document.getElementById('incidentModal');
        const body  = document.getElementById('incidentModalBody');
        document.getElementById('incidentModalSub').textContent = name;
        modal.classList.remove('hidden');

        body.innerHTML = `<div class="flex items-center justify-center py-16">
            <i class="fas fa-spinner fa-spin text-[#043915] text-3xl"></i>
        </div>`;

        fetch(BASE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=get_student_incidents&student_id=${studentId}`
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success || !res.data.length) {
                body.innerHTML = `<div class="flex flex-col items-center justify-center py-16">
                    <i class="fas fa-clipboard-list text-gray-200 text-5xl mb-3"></i>
                    <p class="text-sm text-gray-400 font-medium">No incidents recorded.</p>
                </div>`;
                return;
            }

            body.innerHTML = `<div class="space-y-4">${res.data.map(inc => {
                const sc = { pending: 'bg-amber-100 text-amber-700', reviewed: 'bg-blue-100 text-blue-700', resolved: 'bg-emerald-100 text-emerald-700' }[inc.status] || 'bg-gray-100 text-gray-500';
                const sevCls = (inc.severity || '').toLowerCase().includes('grave') ? 'bg-red-100 text-red-700' : 'bg-purple-100 text-purple-700';
                return `
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 hover:border-[#043915]/20 transition-colors">
                    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase ${sc}">${inc.status}</span>
                            ${inc.severity ? `<span class="px-2.5 py-1 text-xs font-bold rounded-lg ${sevCls}">${esc(inc.severity)}</span>` : ''}
                        </div>
                        <span class="text-xs text-gray-400">${formatDate(inc.created_at)}</span>
                    </div>
                    <p class="text-sm font-bold text-gray-800 mb-1">${esc(inc.violation_display)}</p>
                    <p class="text-xs text-gray-500 mb-2"><i class="fas fa-location-dot mr-1"></i>${esc(inc.location)}</p>
                    <p class="text-sm text-gray-600 leading-relaxed">${esc(inc.description)}</p>
                    ${inc.admin_notes ? `<div class="mt-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
                        <p class="text-xs font-bold text-amber-600 mb-0.5">Admin Notes</p>
                        <p class="text-xs text-amber-800">${esc(inc.admin_notes)}</p>
                    </div>` : ''}
                    <p class="text-xs text-gray-400 mt-2">Reported by: ${esc(inc.reported_by || '—')}</p>
                </div>`;
            }).join('')}</div>`;
        })
        .catch(() => {
            body.innerHTML = '<p class="text-sm text-red-500 text-center py-10">Failed to load incidents.</p>';
        });
    };

    window.closeIncidentModal = function () {
        document.getElementById('incidentModal').classList.add('hidden');
    };

    /* ── Helpers ── */
    function infoRow(label, value) {
        return `<div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">${label}</p>
            <p class="text-sm font-medium text-gray-800 text-right max-w-[60%]">${esc(value || '—')}</p>
        </div>`;
    }
    function esc(str) {
        if (!str) return '—';
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(String(str)));
        return d.innerHTML;
    }
    function formatDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    }

    /* ── Close modals on backdrop click ── */
    document.getElementById('studentProfileModal').addEventListener('click', function (e) {
        if (e.target === this) closeStudentModal();
    });
    document.getElementById('incidentModal').addEventListener('click', function (e) {
        if (e.target === this) closeIncidentModal();
    });

})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>