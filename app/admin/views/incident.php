<?php
$incidents   = $incidentController->getInitialReports();
$violations  = $incidentController->getViolationFilters();
ob_start();
?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gray-100 p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__.'/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__.'/../../../includes/admin-header.php'; ?>

    <!-- ── Page Header ── -->
    <section class="mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
        <div class="text-center lg:text-left">
            <h1 class="text-xl sm:text-2xl font-bold text-[#043915] flex items-center justify-center lg:justify-start gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clipboard-list text-green-600 text-lg"></i>
                </div>
                Incident Records
            </h1>
            <p class="text-sm text-gray-600 mt-1 ml-0 lg:ml-[52px]">Manage and review reported incidents</p>
        </div>
        <div class="w-full lg:w-80 relative">
            <div class="absolute left-0 top-0 bottom-0 w-12 bg-white rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" placeholder="Search incidents…"
                class="w-full pl-14 pr-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm shadow-sm bg-white">
        </div>
    </section>

    <div class="flex flex-col xl:flex-row gap-6">

        <!-- ── Filters Sidebar ── -->
        <aside class="w-full xl:w-72 bg-white rounded-2xl p-6 shadow-lg h-fit border border-gray-50">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-[#043915]">Filters</h2>
                <button type="button" id="resetFiltersBtn" class="text-[10px] font-bold text-red-500 hover:text-red-700 uppercase tracking-wider transition-all">Reset Filters</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-1 gap-4">
                <!-- Role -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Reported Individual</label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-blue-100 rounded-l-lg flex items-center justify-center border-y border-l border-gray-200">
                            <i class="fa-solid fa-user-tie text-blue-600 text-xs"></i>
                        </div>
                        <select id="filterRole" class="filter-select w-full pl-12 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                            <option value="">All Roles</option>
                            <option value="student">Student</option>
                            <option value="teacher">Faculty / Teacher</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Incident Status</label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-orange-100 rounded-l-lg flex items-center justify-center border-y border-l border-gray-200">
                            <i class="fa-solid fa-circle-info text-orange-600 text-xs"></i>
                        </div>
                        <select id="filterStatus" class="filter-select w-full pl-12 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                <!-- Violation -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Violation Type</label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-purple-100 rounded-l-lg flex items-center justify-center border-y border-l border-gray-200">
                            <i class="fa-solid fa-triangle-exclamation text-purple-600 text-xs"></i>
                        </div>
                        <select id="filterViolation" class="filter-select w-full pl-12 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                            <option value="">All Violations</option>
                            <?php foreach ($violations as $v): ?>
                                <option value="<?= $v['discipline_id'] ?>"><?= htmlspecialchars($v['violation_name']) ?></option>
                            <?php endforeach; ?>
                            <option value="custom">Custom / Other</option>
                        </select>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ── Table ── -->
        <section class="flex-1 flex flex-col gap-4 min-w-0">
            <div class="bg-white rounded-2xl shadow-md overflow-x-auto border border-gray-100" style="min-height:55vh;">
                <table class="w-full border-collapse text-left min-w-[800px]" id="incidentTable">
                    <thead>
                        <tr class="bg-[#043915]">
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider w-12">#</th>
                            <th class="px-4 py-4 text-left   text-white text-[11px] font-bold uppercase tracking-wider">Reported Individual</th>
                            <th class="px-4 py-4 text-left   text-white text-[11px] font-bold uppercase tracking-wider">Reported By</th>
                            <th class="px-4 py-4 text-left   text-white text-[11px] font-bold uppercase tracking-wider">Violation</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider w-28">Status</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider w-28">Date</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider w-36">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="incidentTbody" class="divide-y divide-gray-100">
                        <?php if (empty($incidents)): ?>
                        <tr id="emptyRow">
                            <td colspan="7" class="text-center text-gray-400 py-16 text-sm">No incident reports found.</td>
                        </tr>
                        <?php else: foreach ($incidents as $i): ?>
                        <?php
                            $sc = match($i['status']) {
                                'pending'   => 'bg-amber-100 text-amber-700',
                                'reviewed'  => 'bg-blue-100 text-blue-700',
                                'resolved'  => 'bg-green-100 text-green-700',
                                'dismissed' => 'bg-gray-100 text-gray-500',
                                default     => 'bg-gray-100 text-gray-500',
                            };
                            $targetBadge = match($i['report_target']) {
                                'student' => '<span class="px-2 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-600 rounded-full uppercase">Student</span>',
                                'teacher' => '<span class="px-2 py-0.5 text-[9px] font-bold bg-violet-50 text-violet-600 rounded-full uppercase">Teacher</span>',
                                default   => '<span class="px-2 py-0.5 text-[9px] font-bold bg-gray-100 text-gray-500 rounded-full uppercase">Other</span>',
                            };
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors incident-row"
                            data-id="<?= $i['report_id'] ?>"
                            data-role="<?= htmlspecialchars($i['report_target']) ?>"
                            data-status="<?= htmlspecialchars($i['status']) ?>"
                            data-violation="<?= $i['violation_id'] ?? '' ?>"
                            data-search="<?= strtolower(htmlspecialchars(
                                $i['reported_name'].' '.
                                ($i['reporter_name'] ?? '').' '.
                                $i['violation_display'].' '.
                                $i['location']
                            )) ?>">
                            <td class="px-4 py-4 text-center text-xs text-gray-400 font-mono"><?= $i['report_id'] ?></td>
                            <td class="px-4 py-4">
                                <div class="font-medium text-sm text-gray-800"><?= htmlspecialchars($i['reported_name']) ?></div>
                                <div class="mt-0.5 flex items-center gap-1.5 flex-wrap">
                                    <?= $targetBadge ?>
                                    <?php if ($i['grade_level']): ?>
                                        <span class="text-[9px] text-gray-400">Grade <?= $i['grade_level'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600"><?= htmlspecialchars($i['reporter_name'] ?? '—') ?></td>
                            <td class="px-4 py-4 text-sm text-gray-600 max-w-[180px]">
                                <span class="block truncate" title="<?= htmlspecialchars($i['violation_display']) ?>"><?= htmlspecialchars($i['violation_display']) ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="status-badge px-2.5 py-1 text-[10px] font-bold rounded-full uppercase <?= $sc ?>"><?= ucfirst($i['status']) ?></span>
                            </td>
                            <td class="px-4 py-4 text-center text-xs text-gray-500 whitespace-nowrap"><?= date('M d, Y', strtotime($i['created_at'])) ?></td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openDetailModal(<?= $i['report_id'] ?>)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#043915] hover:bg-green-800 text-white text-xs font-semibold transition-all shadow-sm">
                                        <i class="fa-regular fa-eye text-[11px]"></i>
                                        <span>View</span>
                                    </button>
                                    <button onclick="confirmDelete(<?= $i['report_id'] ?>)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold border border-red-100 transition-all">
                                        <i class="fa-regular fa-trash-can text-[11px]"></i>
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between px-2">
                <p class="text-[11px] text-gray-400 uppercase font-bold tracking-widest" id="resultCount">
                    Showing <?= count($incidents) ?> result(s)
                </p>
            </div>
        </section>
    </div>
</main>

<!-- ══════════════════════════════════════════
     SLIDE-IN DETAIL PANEL
══════════════════════════════════════════ -->
<div id="detailModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetailModal()"></div>

    <!-- Panel -->
    <div id="modalPanel"
         class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <!-- Header -->
        <div class="bg-[#043915] px-6 py-5 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-list text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white font-bold text-sm">Incident Report</p>
                    <p class="text-green-300 text-[11px]" id="modalReportId">—</p>
                </div>
            </div>
            <button onclick="closeDetailModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/15 hover:bg-white/30 text-white transition-all"
                    title="Close">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Scrollable body -->
        <div class="flex-1 overflow-y-auto p-6" id="modalBody">
            <div id="modalLoading" class="flex flex-col items-center justify-center py-24 gap-3">
                <div class="w-9 h-9 border-4 border-[#043915] border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm text-gray-400">Loading report…</p>
            </div>
            <div id="modalContent" class="hidden space-y-5"></div>
        </div>

        <!-- Footer actions -->
        <div class="border-t border-gray-100 px-6 py-4 shrink-0 bg-gray-50 space-y-3">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Update Status</p>
            <div class="flex gap-2 flex-wrap" id="statusButtons"></div>
            <div id="adminNotesWrap" class="hidden">
                <textarea id="adminNotesInput" rows="2" placeholder="Admin notes (optional)…"
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915] resize-none"></textarea>
            </div>
        </div>
    </div>
</div>

<!-- ── Delete Confirm Modal ── -->
<div id="deleteModal" class="fixed inset-0 z-[60] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 z-10 scale-95 transition-transform duration-200" id="deletePanel">
        <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-trash text-red-500 text-xl"></i>
        </div>
        <h3 class="text-center font-bold text-gray-800 text-lg mb-1">Delete Report</h3>
        <p class="text-center text-sm text-gray-500 mb-6">This action is permanent and cannot be undone.</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition-all">
                Cancel
            </button>
            <button id="confirmDeleteBtn"
                    class="flex-1 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-bold transition-all">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- ── Status Confirmation Modal ── -->
<div id="statusConfirmModal" class="fixed inset-0 z-[80] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeStatusConfirm()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 z-10 scale-95 transition-transform duration-200" id="statusConfirmPanel">
        <div id="statusConfirmIcon" class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"></div>
        <h3 class="text-center font-bold text-gray-800 text-lg mb-1" id="statusConfirmTitle"></h3>
        <p class="text-center text-sm text-gray-500 mb-6" id="statusConfirmDesc"></p>
        <div class="flex gap-3">
            <button onclick="closeStatusConfirm()"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition-all">
                Cancel
            </button>
            <button id="statusConfirmOkBtn"
                    class="flex-1 py-2.5 rounded-xl text-white text-sm font-bold transition-all">
                OK
            </button>
        </div>
    </div>
</div>

<!-- ── Toast ── -->
<div id="toast" class="fixed bottom-6 right-6 z-[90] hidden pointer-events-none">
    <div class="flex items-center gap-3 bg-gray-900 text-white px-5 py-3 rounded-2xl shadow-2xl text-sm font-medium">
        <i id="toastIcon" class="fa-solid fa-circle-check text-green-400"></i>
        <span id="toastMsg"></span>
    </div>
</div>

<script>
(function () {
    'use strict';

    const BASE_URL = window.location.pathname;
    let currentReportId   = null;
    let deleteTargetId    = null;
    let pendingStatusData = null;
    let toastTimer        = null;

    /* ─────────── Toast ─────────── */
    function showToast(msg, success) {
        clearTimeout(toastTimer);
        document.getElementById('toastMsg').textContent = msg;
        document.getElementById('toastIcon').className  =
            'fa-solid ' + (success !== false ? 'fa-circle-check text-green-400' : 'fa-circle-xmark text-red-400');
        const el = document.getElementById('toast');
        el.classList.remove('hidden');
        toastTimer = setTimeout(() => el.classList.add('hidden'), 3500);
    }

    /* ─────────── FILTERS ─────────── */
    function applyFilters() {
        const role      = document.getElementById('filterRole').value;
        const status    = document.getElementById('filterStatus').value;
        const violation = document.getElementById('filterViolation').value;
        const search    = document.getElementById('searchInput').value.toLowerCase().trim();

        let visible = 0;
        document.querySelectorAll('.incident-row').forEach(row => {
            const matchRole      = !role      || row.dataset.role    === role;
            const matchStatus    = !status    || row.dataset.status  === status;
            const matchViolation = !violation ||
                                   (violation === 'custom'
                                       ? row.dataset.violation === ''
                                       : row.dataset.violation === violation);
            const matchSearch    = !search    || row.dataset.search.includes(search);

            const show = matchRole && matchStatus && matchViolation && matchSearch;
            row.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        document.getElementById('resultCount').textContent = 'Showing ' + visible + ' result(s)';

        let emptyRow = document.getElementById('emptyRow');
        if (visible === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'emptyRow';
                emptyRow.innerHTML = '<td colspan="7" class="text-center text-gray-400 py-16 text-sm">No incidents match your filters.</td>';
                document.getElementById('incidentTbody').appendChild(emptyRow);
            } else {
                emptyRow.classList.remove('hidden');
            }
        } else if (emptyRow) {
            emptyRow.classList.add('hidden');
        }
    }

    document.getElementById('filterRole').addEventListener('change', applyFilters);
    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('filterViolation').addEventListener('change', applyFilters);
    document.getElementById('searchInput').addEventListener('input', applyFilters);

    document.getElementById('resetFiltersBtn').addEventListener('click', function () {
        ['filterRole','filterStatus','filterViolation'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('searchInput').value = '';
        applyFilters();
    });

    /* ─────────── DETAIL MODAL ─────────── */
    const modalEl    = document.getElementById('detailModal');
    const modalPanel = document.getElementById('modalPanel');

    window.openDetailModal = function (reportId) {
        currentReportId = reportId;
        document.getElementById('modalReportId').textContent = '#' + reportId;
        document.getElementById('modalLoading').classList.remove('hidden');
        document.getElementById('modalContent').classList.add('hidden');
        document.getElementById('modalContent').innerHTML = '';
        document.getElementById('statusButtons').innerHTML = '';
        document.getElementById('adminNotesInput').value   = '';
        document.getElementById('adminNotesWrap').classList.add('hidden');

        modalEl.classList.remove('hidden');
        requestAnimationFrame(() => modalPanel.classList.remove('translate-x-full'));

        fetch(BASE_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'action=get_report_detail&report_id=' + reportId
        })
        .then(r => r.json())
        .then(res => {
            document.getElementById('modalLoading').classList.add('hidden');
            if (res.success) {
                renderModalContent(res.data);
                renderStatusButtons(res.data.status);
            } else {
                document.getElementById('modalContent').innerHTML =
                    `<p class="text-red-500 text-sm text-center py-10">${res.message}</p>`;
                document.getElementById('modalContent').classList.remove('hidden');
            }
        })
        .catch(() => {
            document.getElementById('modalLoading').classList.add('hidden');
            document.getElementById('modalContent').innerHTML =
                '<p class="text-red-500 text-sm text-center py-10">Failed to load report.</p>';
            document.getElementById('modalContent').classList.remove('hidden');
        });
    };

    window.closeDetailModal = function () {
        modalPanel.classList.add('translate-x-full');
        setTimeout(() => modalEl.classList.add('hidden'), 300);
    };

    function renderModalContent(d) {
        const targetBadge = {
            student: '<span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded-full uppercase">Student</span>',
            teacher: '<span class="px-2 py-0.5 text-[10px] font-bold bg-violet-100 text-violet-700 rounded-full uppercase">Teacher</span>',
            other:   '<span class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 text-gray-600 rounded-full uppercase">Other</span>',
        }[d.report_target] || '';

        const statusColor = {
            pending:   'bg-amber-100 text-amber-700',
            reviewed:  'bg-blue-100 text-blue-700',
            resolved:  'bg-green-100 text-green-700',
            dismissed: 'bg-gray-100 text-gray-500',
        }[d.status] || 'bg-gray-100 text-gray-500';

        // Evidence
        let evidenceHTML = '';
        if (d.evidence_path) {
            const url = d.evidence_path;
            if (d.evidence_type === 'image') {
                evidenceHTML = `<a href="${url}" target="_blank">
                    <img src="${url}" class="w-full rounded-xl border border-gray-200 max-h-56 object-cover hover:opacity-90 transition-all cursor-zoom-in">
                </a>`;
            } else if (d.evidence_type === 'video') {
                evidenceHTML = `<video controls class="w-full rounded-xl border border-gray-200 max-h-52"><source src="${url}"></video>`;
            } else if (d.evidence_type === 'audio') {
                evidenceHTML = `<audio controls class="w-full mt-1"><source src="${url}"></audio>`;
            }
        }

        const infoRow = (label, value) =>
            `<div class="flex flex-col gap-0.5">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">${label}</p>
                <p class="text-sm text-gray-800 font-medium leading-snug">${value || '—'}</p>
             </div>`;

        const advisoryBlock = (d.report_target === 'student' && d.advisory_name)
            ? `<div class="bg-green-50 border border-green-100 rounded-xl px-4 py-3 flex items-center gap-3">
                   <div class="w-8 h-8 bg-[#043915] rounded-lg flex items-center justify-center shrink-0">
                       <i class="fa-solid fa-chalkboard-teacher text-white text-xs"></i>
                   </div>
                   <div>
                       <p class="text-[10px] font-bold text-green-700 uppercase tracking-wider">Advisory Class</p>
                       <p class="text-sm font-semibold text-gray-800">${esc(d.advisory_name)}</p>
                       ${d.advisory_teacher_name ? `<p class="text-xs text-gray-500">${esc(d.advisory_teacher_name)}</p>` : ''}
                   </div>
               </div>`
            : '';

        const notesHTML = d.admin_notes
            ? `<div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                   <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider mb-1">Admin Notes</p>
                   <p class="text-sm text-amber-800">${esc(d.admin_notes)}</p>
               </div>`
            : '';

        const reviewedHTML = d.reviewed_by_name
            ? `<div class="flex flex-col gap-0.5">
                   <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reviewed By</p>
                   <p class="text-sm text-gray-800 font-medium">${esc(d.reviewed_by_name)} &bull; <span class="text-gray-500 font-normal">${formatDate(d.reviewed_at)}</span></p>
               </div>`
            : '';

        document.getElementById('modalContent').innerHTML = `
            <div class="flex items-center justify-between">
                <span class="px-3 py-1 text-xs font-bold rounded-full uppercase ${statusColor}">${d.status}</span>
                <span class="text-xs text-gray-400 font-mono">SY ${d.school_year || '—'}</span>
            </div>

            <div class="bg-gray-50 rounded-2xl p-4 space-y-3 border border-gray-100">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reported Individual</p>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-[#043915] rounded-xl flex items-center justify-center text-white font-bold text-base shrink-0">
                        ${esc(d.reported_name).charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate">${esc(d.reported_name)}</p>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            ${targetBadge}
                            ${d.grade_level ? `<span class="text-[10px] text-gray-400">Grade ${d.grade_level}</span>` : ''}
                            ${d.student_lrn ? `<span class="text-[10px] text-gray-400">LRN: ${esc(d.student_lrn)}</span>` : ''}
                        </div>
                    </div>
                </div>
            </div>

            ${advisoryBlock}

            <div class="grid grid-cols-2 gap-3">
                ${infoRow('Reported By', esc(d.reporter_name))}
                ${infoRow('Location', esc(d.location))}
                ${infoRow('Violation', esc(d.violation_display))}
                ${infoRow('Date Filed', formatDate(d.created_at))}
            </div>

            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Incident Description</p>
                <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap border border-gray-100">${esc(d.description)}</div>
            </div>

            ${d.evidence_path ? `
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Evidence</p>
                ${evidenceHTML}
            </div>` : ''}

            ${notesHTML}
            ${reviewedHTML ? `<div>${reviewedHTML}</div>` : ''}
        `;

        document.getElementById('adminNotesInput').value = d.admin_notes || '';
        document.getElementById('modalContent').classList.remove('hidden');
    }

    /* ─────────── STATUS BUTTONS ─────────── */
    function renderStatusButtons(currentStatus) {
        const wrap = document.getElementById('statusButtons');
        wrap.innerHTML = '';

        // If already resolved — lock it, case is done
        if (currentStatus === 'resolved') {
            wrap.innerHTML = `
                <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-bold w-full">
                    <i class="fa-solid fa-lock text-xs"></i>
                    <span>Case Resolved — No further changes allowed</span>
                </div>`;
            document.getElementById('adminNotesWrap').classList.add('hidden');
            return;
        }

        // Only Mark Reviewed and Mark Resolved — no Dismiss, no Reset to Pending
        const actions = [
            {
                status:  'reviewed',
                label:   'Mark Reviewed',
                cls:     'bg-blue-600 hover:bg-blue-700 text-white',
                icon:    'fa-eye',
                title:   'Mark as Reviewed?',
                desc:    'This will mark the incident as reviewed. You can still resolve it after.',
                btnCls:  'bg-blue-600 hover:bg-blue-700',
                iconBg:  'bg-blue-100',
                iconClr: 'text-blue-600',
            },
            {
                status:  'resolved',
                label:   'Mark Resolved',
                cls:     'bg-green-600 hover:bg-green-700 text-white',
                icon:    'fa-circle-check',
                title:   'Mark as Resolved?',
                desc:    'This will permanently close the case. This action cannot be undone.',
                btnCls:  'bg-green-600 hover:bg-green-700',
                iconBg:  'bg-green-100',
                iconClr: 'text-green-600',
            },
        ].filter(a => a.status !== currentStatus);

        document.getElementById('adminNotesWrap').classList.remove('hidden');

        actions.forEach(a => {
            const btn = document.createElement('button');
            btn.className = `flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition-all ${a.cls}`;
            btn.innerHTML = `<i class="fa-solid ${a.icon} text-xs"></i> ${a.label}`;
            btn.addEventListener('click', () => openStatusConfirm(
                currentReportId, a.status, a.title, a.desc, a.btnCls, a.icon, a.iconBg, a.iconClr
            ));
            wrap.appendChild(btn);
        });
    }

    /* ─────────── STATUS CONFIRM MODAL ─────────── */
    const statusConfirmModal = document.getElementById('statusConfirmModal');
    const statusConfirmPanel = document.getElementById('statusConfirmPanel');

    function openStatusConfirm(reportId, status, title, desc, btnCls, icon, iconBg, iconClr) {
        pendingStatusData = { reportId, status };

        document.getElementById('statusConfirmIcon').className =
            `w-14 h-14 ${iconBg} rounded-2xl flex items-center justify-center mx-auto mb-4`;
        document.getElementById('statusConfirmIcon').innerHTML =
            `<i class="fa-solid ${icon} ${iconClr} text-xl"></i>`;
        document.getElementById('statusConfirmTitle').textContent = title;
        document.getElementById('statusConfirmDesc').textContent  = desc;

        const okBtn = document.getElementById('statusConfirmOkBtn');
        okBtn.className = `flex-1 py-2.5 rounded-xl text-white text-sm font-bold transition-all ${btnCls}`;
        okBtn.onclick = function () {
            closeStatusConfirm();
            if (pendingStatusData) {
                updateStatus(pendingStatusData.reportId, pendingStatusData.status);
            }
        };

        statusConfirmModal.classList.remove('hidden');
        statusConfirmModal.classList.add('flex');
        requestAnimationFrame(() => statusConfirmPanel.classList.remove('scale-95'));
    }

    window.closeStatusConfirm = function () {
        statusConfirmPanel.classList.add('scale-95');
        setTimeout(() => {
            statusConfirmModal.classList.add('hidden');
            statusConfirmModal.classList.remove('flex');
        }, 150);
    };

    /* ─────────── UPDATE STATUS ─────────── */
    function updateStatus(reportId, status) {
        const notes = document.getElementById('adminNotesInput').value.trim();

        fetch(BASE_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `action=update_status&report_id=${reportId}&status=${encodeURIComponent(status)}&admin_notes=${encodeURIComponent(notes)}`
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, true);

                // Update badge in table without reload
                const row = document.querySelector(`.incident-row[data-id="${reportId}"]`);
                if (row) {
                    row.dataset.status = status;
                    const scMap = {
                        pending:   'bg-amber-100 text-amber-700',
                        reviewed:  'bg-blue-100 text-blue-700',
                        resolved:  'bg-green-100 text-green-700',
                        dismissed: 'bg-gray-100 text-gray-500',
                    };
                    const badge = row.querySelector('.status-badge');
                    if (badge) {
                        badge.className = `status-badge px-2.5 py-1 text-[10px] font-bold rounded-full uppercase ${scMap[status] || ''}`;
                        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    }
                }
                closeDetailModal();
            } else {
                showToast(res.message, false);
            }
        })
        .catch(() => showToast('Network error.', false));
    }

    /* ─────────── DELETE ─────────── */
    const deleteModal = document.getElementById('deleteModal');
    const deletePanel = document.getElementById('deletePanel');

    window.confirmDelete = function (reportId) {
        deleteTargetId = reportId;
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
        requestAnimationFrame(() => deletePanel.classList.remove('scale-95'));
    };

    window.closeDeleteModal = function () {
        deletePanel.classList.add('scale-95');
        setTimeout(() => {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        }, 150);
    };

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (!deleteTargetId) return;
        this.disabled    = true;
        this.textContent = 'Deleting…';

        fetch(BASE_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `action=delete_report&report_id=${deleteTargetId}`
        })
        .then(r => r.json())
        .then(res => {
            closeDeleteModal();
            this.disabled    = false;
            this.textContent = 'Delete';
            if (res.success) {
                const row = document.querySelector(`.incident-row[data-id="${deleteTargetId}"]`);
                if (row) row.remove();
                showToast('Report deleted.', true);
                applyFilters();
            } else {
                showToast(res.message, false);
            }
        })
        .catch(() => {
            closeDeleteModal();
            this.disabled    = false;
            this.textContent = 'Delete';
            showToast('Network error.', false);
        });
    });

    /* ─────────── Helpers ─────────── */
    function esc(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }
    function formatDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
    }
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>