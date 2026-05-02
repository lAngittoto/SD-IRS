<?php
$incidents   = $incidentController->getInitialReports();
$violations  = $incidentController->getViolationFilters();
ob_start();
?>

<style>
@media print {
    /* Hide everything except the print area */
    body > *,
    main > * { display: none !important; }

    #printArea {
        display: block !important;
        position: fixed !important;
        inset: 0 !important;
        z-index: 9999 !important;
        background: white !important;
        padding: 32px !important;
        font-family: sans-serif !important;
    }

    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    @page {
        size: portrait;
        margin: 1.5cm;
    }
}
</style>

<!-- Hidden Print Area (populated dynamically) -->
<div id="printArea" style="display:none;"></div>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__.'/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__.'/../../../includes/admin-header.php'; ?>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[300] flex flex-col gap-2 pointer-events-none"></div>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-clipboard-list text-[#f8c922] text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900">Incident Records</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage and review reported incidents</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-[220px] flex-1">
                <label class="block text-xs font-bold text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search incidents…"
                        class="w-full pl-9 pr-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                </div>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Reported Individual</label>
                <select id="filterRole" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">All Roles</option>
                    <option value="student">Student</option>
                    <option value="teacher">Faculty / Teacher</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Status</label>
                <select id="filterStatus" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
            <div class="min-w-[170px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Violation Type</label>
                <select id="filterViolation" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">All Violations</option>
                    <?php foreach ($violations as $v): ?>
                        <option value="<?= $v['discipline_id'] ?>"><?= htmlspecialchars($v['violation_name']) ?></option>
                    <?php endforeach; ?>
                    <option value="custom">Custom / Other</option>
                </select>
            </div>
            <button id="resetFiltersBtn" class="px-4 py-2.5 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] rounded-lg text-sm font-bold transition-all">
                Reset
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col" style="height:600px;">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3 shrink-0 bg-gradient-to-r from-gray-50 to-white">
            <p id="resultCount" class="text-sm font-bold text-gray-700">
                Showing <?= count($incidents) ?> result(s)
            </p>
        </div>
        <div class="overflow-x-auto overflow-y-auto flex-1">
            <table class="w-full text-sm" id="incidentTable">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white">
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap w-12">#</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Reported Individual</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Reported By</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Violation</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Status</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Date</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="incidentTbody" class="divide-y divide-gray-100">
                    <?php if (empty($incidents)): ?>
                    <tr id="emptyRow">
                        <td colspan="7" class="py-20 text-center">
                            <i class="fa-solid fa-clipboard-list text-gray-200 text-5xl mb-4 block"></i>
                            <p class="text-sm text-gray-400 font-medium">No incident reports found.</p>
                        </td>
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
                        <td class="py-3 px-6 text-center text-xs text-gray-400 font-mono"><?= $i['report_id'] ?></td>
                        <td class="py-3 px-6">
                            <div class="font-semibold text-sm text-gray-900"><?= htmlspecialchars($i['reported_name']) ?></div>
                            <div class="mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <?= $targetBadge ?>
                                <?php if ($i['grade_level']): ?>
                                    <span class="text-[9px] text-gray-400">Grade <?= $i['grade_level'] ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="py-3 px-6 text-sm text-gray-600"><?= htmlspecialchars($i['reporter_name'] ?? '—') ?></td>
                        <td class="py-3 px-6 max-w-[180px]">
                            <span class="text-sm text-gray-600 block truncate" title="<?= htmlspecialchars($i['violation_display']) ?>"><?= htmlspecialchars($i['violation_display']) ?></span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <span class="status-badge px-3 py-1 text-xs font-bold rounded-lg uppercase <?= $sc ?>"><?= ucfirst($i['status']) ?></span>
                        </td>
                        <td class="py-3 px-6 text-center text-xs text-gray-400 whitespace-nowrap"><?= date('M d, Y', strtotime($i['created_at'])) ?></td>
                        <td class="py-3 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openDetailModal(<?= $i['report_id'] ?>)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#043915] hover:bg-[#032a0f] text-white rounded-lg text-xs font-bold transition-all">
                                    <i class="fa-regular fa-eye text-xs"></i> View
                                </button>
                                <button onclick="confirmDelete(<?= $i['report_id'] ?>)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-bold transition-all">
                                    <i class="fa-regular fa-trash-can text-xs"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ══════════════════════════════════════════
     SLIDE-IN DETAIL PANEL
══════════════════════════════════════════ -->
<div id="detailModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDetailModal()"></div>

    <!-- Panel -->
    <div id="modalPanel"
         class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <!-- Header -->
        <div class="bg-gradient-to-r from-[#043915] to-[#032a0f] px-8 py-6 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-white/10 rounded-2xl flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-list text-[#f8c922] text-lg"></i>
                </div>
                <div>
                    <p class="text-white font-bold text-base">Incident Report</p>
                    <p class="text-[#f8c922]/70 text-xs mt-0.5" id="modalReportId">—</p>
                </div>
            </div>
            <!-- Header buttons -->
            <div class="flex items-center gap-2">
                <!-- ✅ PRINT BUTTON SA MODAL -->
                <button id="modalPrintBtn" onclick="printReport()" title="Print this report"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-[#f8c922]/20 hover:bg-[#f8c922]/40 text-[#f8c922] transition-all"
                        style="opacity:0.4;" disabled>
                    <i class="fa-solid fa-print text-sm"></i>
                </button>
                <button onclick="closeDetailModal()"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable body -->
        <div class="flex-1 overflow-y-auto p-6 bg-white" id="modalBody">
            <div id="modalLoading" class="flex flex-col items-center justify-center py-24 gap-3">
                <i class="fa-solid fa-spinner fa-spin text-[#043915] text-3xl"></i>
                <p class="text-sm text-gray-400">Loading report…</p>
            </div>
            <div id="modalContent" class="hidden space-y-5"></div>
        </div>

        <!-- Footer actions -->
        <div class="border-t border-gray-100 px-6 py-5 shrink-0 bg-gray-50 space-y-3">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Update Status</p>
            <div class="flex gap-2 flex-wrap" id="statusButtons"></div>
            <div id="adminNotesWrap" class="hidden">
                <textarea id="adminNotesInput" rows="2" placeholder="Admin notes (optional)…"
                          class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 resize-none"></textarea>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     DELETE CONFIRM MODAL
══════════════════════════════════════════ -->
<div id="deleteModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm mx-4 z-10 text-center scale-95 transition-transform duration-200" id="deletePanel">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-trash text-red-600 text-2xl"></i>
        </div>
        <h3 class="font-bold text-gray-900 text-xl mb-2">Delete Report</h3>
        <p class="text-sm text-gray-500 mb-7">This action is permanent and cannot be undone.</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()"
                    class="flex-1 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold transition-all">
                Cancel
            </button>
            <button id="confirmDeleteBtn"
                    class="flex-1 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition-all">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     STATUS CONFIRM MODAL
══════════════════════════════════════════ -->
<div id="statusConfirmModal" class="fixed inset-0 z-[150] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeStatusConfirm()"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm mx-4 z-10 text-center scale-95 transition-transform duration-200" id="statusConfirmPanel">
        <div id="statusConfirmIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
        <h3 class="font-bold text-gray-900 text-xl mb-2" id="statusConfirmTitle"></h3>
        <p class="text-sm text-gray-500 mb-7" id="statusConfirmDesc"></p>
        <div class="flex gap-3">
            <button onclick="closeStatusConfirm()"
                    class="flex-1 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold transition-all">
                Cancel
            </button>
            <button id="statusConfirmOkBtn"
                    class="flex-1 py-3 rounded-xl text-white text-sm font-bold transition-all">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     TOAST
══════════════════════════════════════════ -->
<div id="toast" class="fixed bottom-6 right-6 z-[200] hidden pointer-events-none">
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
    let currentReportData = null; // stores the loaded report for printing

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

    /* ─────────── PRINT REPORT ─────────── */
    window.printReport = function () {
        if (!currentReportData) return;
        const d = currentReportData;

        const statusColors = {
            pending:   '#D97706',
            reviewed:  '#2563EB',
            resolved:  '#16A34A',
            dismissed: '#6B7280',
        };
        const statusColor = statusColors[d.status] || '#6B7280';

        const roleLabel = {
            student: 'Student',
            teacher: 'Faculty / Teacher',
            other:   'Other',
        }[d.report_target] || d.report_target;

        const fmtDate = (str) => {
            if (!str) return '—';
            return new Date(str).toLocaleDateString('en-US', {
                month: 'long', day: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        };

        const escP = (str) => {
            if (!str) return '—';
            const el = document.createElement('div');
            el.appendChild(document.createTextNode(str));
            return el.innerHTML;
        };

        document.getElementById('printArea').innerHTML = `
            <div style="font-family:sans-serif;max-width:680px;margin:0 auto;color:#111;">

                <!-- Report Header -->
                <div style="background:#043915;color:white;padding:24px 32px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:20px;font-weight:900;letter-spacing:-0.5px;">Incident Report</div>
                        <div style="font-size:13px;color:#f8c922;margin-top:4px;">Report #${d.report_id} &bull; SY ${escP(d.school_year)}</div>
                    </div>
                    <div style="text-align:right;font-size:12px;color:#ccc;">
                        <div>Printed: ${new Date().toLocaleDateString('en-US',{month:'long',day:'2-digit',year:'numeric'})}</div>
                        <div style="margin-top:6px;display:inline-block;background:${statusColor};color:white;padding:3px 12px;border-radius:6px;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;">${d.status}</div>
                    </div>
                </div>

                <!-- Reported Individual -->
                <div style="background:#f8f8f8;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:16px;">
                    <div style="font-size:10px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">Reported Individual</div>
                    <div style="font-size:18px;font-weight:800;color:#111;">${escP(d.reported_name)}</div>
                    <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <span style="background:#EFF6FF;color:#1D4ED8;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;text-transform:uppercase;">${roleLabel}</span>
                        ${d.grade_level ? `<span style="font-size:11px;color:#6B7280;">Grade ${d.grade_level}</span>` : ''}
                        ${d.student_lrn ? `<span style="font-size:11px;color:#6B7280;">LRN: ${escP(d.student_lrn)}</span>` : ''}
                    </div>
                    ${d.advisory_name ? `
                    <div style="margin-top:10px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12px;">
                        <span style="font-weight:700;color:#15803D;">Advisory Class:</span>
                        <span style="color:#166534;margin-left:6px;">${escP(d.advisory_name)}</span>
                        ${d.advisory_teacher_name ? `<span style="color:#6B7280;margin-left:4px;">— ${escP(d.advisory_teacher_name)}</span>` : ''}
                    </div>` : ''}
                </div>

                <!-- Info Grid -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                    <div style="background:#f8f8f8;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;">
                        <div style="font-size:10px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Reported By</div>
                        <div style="font-size:14px;font-weight:600;color:#111;">${escP(d.reporter_name)}</div>
                    </div>
                    <div style="background:#f8f8f8;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;">
                        <div style="font-size:10px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Location</div>
                        <div style="font-size:14px;font-weight:600;color:#111;">${escP(d.location)}</div>
                    </div>
                    <div style="background:#f8f8f8;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;">
                        <div style="font-size:10px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Violation</div>
                        <div style="font-size:14px;font-weight:600;color:#111;">${escP(d.violation_display)}</div>
                    </div>
                    <div style="background:#f8f8f8;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;">
                        <div style="font-size:10px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Date Filed</div>
                        <div style="font-size:14px;font-weight:600;color:#111;">${fmtDate(d.created_at)}</div>
                    </div>
                </div>

                <!-- Description -->
                <div style="border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:16px;">
                    <div style="font-size:10px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px;">Incident Description</div>
                    <div style="font-size:13px;color:#374151;line-height:1.75;white-space:pre-wrap;">${escP(d.description)}</div>
                </div>

                <!-- Admin Notes -->
                ${d.admin_notes ? `
                <div style="background:#FFFBEB;border:1px solid #FCD34D;border-radius:10px;padding:16px 20px;margin-bottom:16px;">
                    <div style="font-size:10px;font-weight:700;color:#B45309;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;">Admin Notes</div>
                    <div style="font-size:13px;color:#92400E;line-height:1.65;">${escP(d.admin_notes)}</div>
                </div>` : ''}

                <!-- Reviewed By -->
                ${d.reviewed_by_name ? `
                <div style="font-size:12px;color:#6B7280;margin-bottom:16px;">
                    <span style="font-weight:700;">Reviewed by:</span> ${escP(d.reviewed_by_name)} &bull; ${fmtDate(d.reviewed_at)}
                </div>` : ''}

                <!-- Signature Lines -->
                <div style="margin-top:56px;display:grid;grid-template-columns:1fr 1fr;gap:48px;">
                    <div>
                        <div style="border-top:1.5px solid #111;padding-top:6px;font-size:11px;color:#6B7280;text-align:center;">Reported By / Signature over Printed Name</div>
                    </div>
                    <div>
                        <div style="border-top:1.5px solid #111;padding-top:6px;font-size:11px;color:#6B7280;text-align:center;">Noted By / Admin Signature over Printed Name</div>
                    </div>
                </div>

            </div>
        `;

        window.print();
    };

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
                emptyRow.innerHTML = '<td colspan="7" class="py-20 text-center"><i class="fa-solid fa-clipboard-list text-gray-200 text-5xl mb-4 block"></i><p class="text-sm text-gray-400 font-medium">No incidents match your filters.</p></td>';
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
        currentReportId   = reportId;
        currentReportData = null;

        // disable print btn while loading
        const printBtn = document.getElementById('modalPrintBtn');
        printBtn.style.opacity = '0.4';
        printBtn.disabled = true;

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
                currentReportData = res.data;
                // enable print btn once data is loaded
                printBtn.style.opacity = '1';
                printBtn.disabled = false;
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
            ? `<div class="bg-[#043915]/5 border border-[#043915]/10 rounded-2xl px-4 py-3 flex items-center gap-3">
                   <div class="w-9 h-9 bg-[#043915] rounded-xl flex items-center justify-center shrink-0">
                       <i class="fa-solid fa-chalkboard-teacher text-[#f8c922] text-sm"></i>
                   </div>
                   <div>
                       <p class="text-[10px] font-bold text-[#043915] uppercase tracking-wider">Advisory Class</p>
                       <p class="text-sm font-semibold text-gray-800">${esc(d.advisory_name)}</p>
                       ${d.advisory_teacher_name ? `<p class="text-xs text-gray-500">${esc(d.advisory_teacher_name)}</p>` : ''}
                   </div>
               </div>`
            : '';

        const notesHTML = d.admin_notes
            ? `<div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
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
                <span class="px-3 py-1.5 text-xs font-bold rounded-lg uppercase ${statusColor}">${d.status}</span>
                <span class="text-xs text-gray-400 font-mono">SY ${d.school_year || '—'}</span>
            </div>

            <div class="bg-gray-50 rounded-2xl p-4 space-y-3 border border-gray-100">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reported Individual</p>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-md">
                        ${esc(d.reported_name).charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-sm truncate">${esc(d.reported_name)}</p>
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

        if (currentStatus === 'resolved') {
            wrap.innerHTML = `
                <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-bold w-full">
                    <i class="fa-solid fa-lock text-xs"></i>
                    <span>Case Resolved — No further changes allowed</span>
                </div>`;
            document.getElementById('adminNotesWrap').classList.add('hidden');
            return;
        }

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
                cls:     'bg-[#043915] hover:bg-[#032a0f] text-white',
                icon:    'fa-circle-check',
                title:   'Mark as Resolved?',
                desc:    'This will permanently close the case. This action cannot be undone.',
                btnCls:  'bg-[#043915] hover:bg-[#032a0f]',
                iconBg:  'bg-green-100',
                iconClr: 'text-green-600',
            },
        ].filter(a => a.status !== currentStatus);

        document.getElementById('adminNotesWrap').classList.remove('hidden');

        actions.forEach(a => {
            const btn = document.createElement('button');
            btn.className = `inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all ${a.cls}`;
            btn.innerHTML = `<i class="fa-solid ${a.icon} text-xs"></i> ${a.label}`;
            btn.addEventListener('click', () => openStatusConfirm(
                currentReportId, a.status, a.title, a.desc, a.btnCls, a.icon, a.iconBg, a.iconClr
            ));
            wrap.appendChild(btn);
        });
    }

    /* ─────────── STATUS CONFIRM ─────────── */
    const statusConfirmModal = document.getElementById('statusConfirmModal');
    const statusConfirmPanel = document.getElementById('statusConfirmPanel');

    function openStatusConfirm(reportId, status, title, desc, btnCls, icon, iconBg, iconClr) {
        pendingStatusData = { reportId, status };

        document.getElementById('statusConfirmIcon').className =
            `w-16 h-16 ${iconBg} rounded-full flex items-center justify-center mx-auto mb-4`;
        document.getElementById('statusConfirmIcon').innerHTML =
            `<i class="fa-solid ${icon} ${iconClr} text-2xl"></i>`;
        document.getElementById('statusConfirmTitle').textContent = title;
        document.getElementById('statusConfirmDesc').textContent  = desc;

        const okBtn = document.getElementById('statusConfirmOkBtn');
        okBtn.className = `flex-1 py-3 rounded-xl text-white text-sm font-bold transition-all ${btnCls}`;
        okBtn.onclick = function () {
            closeStatusConfirm();
            if (pendingStatusData) updateStatus(pendingStatusData.reportId, pendingStatusData.status);
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
                        badge.className = `status-badge px-3 py-1 text-xs font-bold rounded-lg uppercase ${scMap[status] || ''}`;
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