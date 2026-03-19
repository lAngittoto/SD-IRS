<?php
$violations  = $teacherReportController->getViolations();
$myReports   = $teacherReportController->getMyReports();
$teacher     = $_SESSION['user'];
?>
<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">
    <?php include __DIR__ . '/../../../includes/teacher-sidebar.php'; ?>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[300] flex flex-col gap-2 pointer-events-none"></div>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-flag text-[#f8c922] text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-gray-900">Report an Incident</h1>
                <p class="text-sm text-gray-600 mt-1">Submit a formal incident report. All reports are confidential and reviewed by the admin.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- ══════════════════════════════════════
             FORM CARD
        ══════════════════════════════════════ -->
        <div class="xl:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 md:p-8">

                <!-- Form header -->
                <div class="flex items-center gap-3 mb-7">
                    <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-circle-plus text-[#043915] text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900 text-base">New Incident Report</h2>
                        <p class="text-xs text-gray-400">Fill in all required fields</p>
                    </div>
                </div>

                <!-- Alert -->
                <div id="formAlert" class="hidden mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium">
                    <i id="formAlertIcon" class="fas fa-circle-exclamation shrink-0"></i>
                    <span id="formAlertText"></span>
                </div>

                <form id="reportForm" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="action" value="submit_report">

                    <!-- Step 1: Who are you reporting? -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                            Who are you reporting? <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3" id="targetSelector">
                            <?php
                            $targets = [
                                ['student', 'Student',  'fa-user-graduate'],
                                ['teacher', 'Teacher',  'fa-chalkboard-user'],
                                ['other',   'Other',    'fa-circle-question'],
                            ];
                            foreach ($targets as [$val, $label, $icon]):
                            ?>
                            <label class="target-btn cursor-pointer border-2 rounded-xl p-4 flex flex-col items-center gap-2 transition-all
                                          border-gray-200 hover:border-[#043915]/40 hover:bg-[#043915]/5
                                          <?= $val === 'student' ? 'border-[#043915] bg-[#043915]/5' : '' ?>"
                                   data-value="<?= $val ?>">
                                <input type="radio" name="report_target" value="<?= $val ?>" class="hidden" <?= $val === 'student' ? 'checked' : '' ?>>
                                <i class="fas <?= $icon ?> text-xl target-icon <?= $val === 'student' ? 'text-[#043915]' : 'text-gray-400' ?>"></i>
                                <span class="text-xs font-bold target-label <?= $val === 'student' ? 'text-[#043915]' : 'text-gray-500' ?>"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Dynamic: Student fields -->
                    <div id="studentFields" class="mb-5 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">
                                Grade Level <span class="text-red-500">*</span>
                            </label>
                            <select name="grade_level" id="gradeSelect"
                                    class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                                <option value=""> Select Grade Level </option>
                                <?php foreach (['7','8','9','10','11','12'] as $g): ?>
                                    <option value="<?= $g ?>">Grade <?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="studentDropdownWrap" class="hidden">
                            <label class="block text-xs font-bold text-gray-700 mb-2">
                                Student Involved <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="student_id" id="studentSelect"
                                        class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                                    <option value="">— Select Student —</option>
                                </select>
                                <div id="studentLoading" class="hidden absolute right-3 top-3">
                                    <i class="fas fa-spinner fa-spin text-[#043915] text-sm"></i>
                                </div>
                            </div>
                            <p id="studentEmpty" class="hidden text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                                <i class="fas fa-triangle-exclamation"></i> No students found for this grade level.
                            </p>
                        </div>
                    </div>

                    <!-- Dynamic: Teacher fields -->
                    <div id="teacherFields" class="mb-5 hidden">
                        <label class="block text-xs font-bold text-gray-700 mb-2">
                            Teacher Involved <span class="text-red-500">*</span>
                        </label>
                        <select name="teacher_involved_id" id="teacherSelect"
                                class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                            <option value="">— Select Teacher —</option>
                        </select>
                    </div>

                    <!-- Dynamic: Other fields -->
                    <div id="otherFields" class="mb-5 hidden">
                        <label class="block text-xs font-bold text-gray-700 mb-2">
                            Name of Person Involved <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="other_name" id="otherName" placeholder="Enter full name"
                               class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all placeholder-gray-300">
                    </div>

                    <!-- Location -->
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-gray-700 mb-2">
                            Location <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="location"
                               placeholder="e.g. Classroom 3B, Corridor 2nd Floor, Canteen…"
                               class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all placeholder-gray-300">
                    </div>

                    <!-- Violation Type -->
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-gray-700 mb-2">
                            Violation Type <span class="text-red-500">*</span>
                        </label>
                        <select name="violation_id" id="violationSelect"
                                class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                            <option value=""> Select Violation </option>
                            <?php foreach ($violations as $v): ?>
                                <option value="<?= $v['discipline_id'] ?>"><?= htmlspecialchars($v['violation_name']) ?></option>
                            <?php endforeach; ?>
                            <option value="other">Other (specify below)</option>
                        </select>
                        <div id="customViolationWrap" class="mt-2 hidden">
                            <input type="text" name="custom_violation" id="customViolation"
                                   placeholder="Describe the violation…"
                                   class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all placeholder-gray-300">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-gray-700 mb-2">
                            Description of Incident <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="5"
                                  placeholder="Provide a clear, factual account of what happened, when it occurred, and who was involved…"
                                  class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all placeholder-gray-300 resize-none"></textarea>
                        <p class="text-xs text-gray-400 mt-1">Be specific and objective. Avoid opinions — stick to observable facts.</p>
                    </div>

                    <!-- Evidence Upload -->
                    <div class="mb-7">
                        <label class="block text-xs font-bold text-gray-700 mb-2">
                            Upload Evidence <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <div id="dropZone"
                             class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-[#043915]/40 hover:bg-[#043915]/5 transition-all">
                            <input type="file" name="evidence" id="evidenceInput" accept="image/*,video/*,audio/*" class="hidden">
                            <i class="fas fa-cloud-arrow-up text-gray-300 text-4xl mb-3 block"></i>
                            <p class="text-sm font-semibold text-gray-500">Click or drag & drop to upload</p>
                            <p class="text-xs text-gray-400 mt-1">Image, Video, or Audio · Max 20MB</p>
                        </div>
                        <div id="filePreview" class="hidden mt-3 flex items-center gap-3 bg-[#043915]/5 rounded-xl px-4 py-3 border border-[#043915]/10">
                            <i class="fas fa-paperclip text-[#043915] shrink-0"></i>
                            <span id="fileName" class="text-sm text-gray-700 flex-1 truncate font-medium"></span>
                            <button type="button" id="removeFile" class="inline-flex items-center gap-1 text-xs font-bold text-red-500 hover:text-red-700 transition-colors">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">If you have no evidence now, be prepared to present it at the office when called.</p>
                    </div>

                    <!-- Submit -->
                    <div class="flex gap-3 pt-4 border-t border-gray-100">
                        <button type="button" onclick="resetForm()"
                                class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all">
                            <i class="fas fa-times"></i> Clear
                        </button>
                        <button type="submit" id="submitBtn"
                                class="flex-1 inline-flex items-center justify-center gap-2 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold rounded-xl py-3 px-6 transition-all shadow-md hover:shadow-lg">
                            <i class="fas fa-paper-plane"></i>
                            <span id="submitText">Submit Report</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             SIDEBAR: My Reports + Guidelines
        ══════════════════════════════════════ -->
        <div class="xl:col-span-1 space-y-5">

            <!-- My Reports Card -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center gap-3">
                    <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-[#043915]"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm flex-1">My Reports</h3>
                    <span class="px-2.5 py-1 bg-[#043915] text-white text-xs font-black rounded-lg"><?= count($myReports) ?></span>
                </div>

                <?php if (empty($myReports)): ?>
                <div class="flex flex-col items-center justify-center py-14 px-6 text-center">
                    <i class="fas fa-clipboard-list text-gray-200 text-4xl mb-3"></i>
                    <p class="text-sm text-gray-400 font-medium">No reports submitted yet.</p>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-50 max-h-[480px] overflow-y-auto" id="reportsList">
                    <?php foreach ($myReports as $rpt):
                        $statusCls = match($rpt['status']) {
                            'pending'   => 'bg-amber-100 text-amber-700',
                            'reviewed'  => 'bg-blue-100 text-blue-700',
                            'resolved'  => 'bg-emerald-100 text-emerald-700',
                            'dismissed' => 'bg-gray-100 text-gray-500',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                        $person = match($rpt['report_target']) {
                            'student' => $rpt['student_name']          ?? 'Unknown Student',
                            'teacher' => $rpt['teacher_involved_name'] ?? 'Unknown Teacher',
                            default   => $rpt['other_name']            ?? 'Unknown',
                        };
                        $violation = $rpt['violation_name'] ?? $rpt['custom_violation'] ?? 'N/A';
                        $targetBadge = match($rpt['report_target']) {
                            'student' => '<span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-600 rounded uppercase">Student</span>',
                            'teacher' => '<span class="px-1.5 py-0.5 text-[9px] font-bold bg-violet-50 text-violet-600 rounded uppercase">Teacher</span>',
                            default   => '<span class="px-1.5 py-0.5 text-[9px] font-bold bg-gray-100 text-gray-500 rounded uppercase">Other</span>',
                        };
                    ?>
                    <div class="px-5 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                <?= $targetBadge ?>
                                <p class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($person) ?></p>
                            </div>
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg capitalize shrink-0 <?= $statusCls ?>"><?= $rpt['status'] ?></span>
                        </div>
                        <p class="text-xs text-gray-500 truncate mb-1"><?= htmlspecialchars($violation) ?></p>
                        <p class="text-[10px] text-gray-400"><i class="fas fa-calendar mr-1"></i><?= date('M d, Y', strtotime($rpt['created_at'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Reporting Guidelines -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-[#043915]/5 to-transparent flex items-center gap-3">
                    <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-circle-info text-[#043915]"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">Reporting Guidelines</h3>
                </div>
                <div class="px-6 py-5 space-y-3">
                    <?php
                    $guidelines = [
                        ['fa-check-circle',      'text-emerald-500', 'Be factual and objective in your description.'],
                        ['fa-lock',              'text-[#043915]',   'Reports are confidential and reviewed by admin.'],
                        ['fa-triangle-exclamation','text-amber-500', 'False reports may result in disciplinary action.'],
                        ['fa-paperclip',         'text-blue-500',    'Preserve any evidence and present it when asked.'],
                    ];
                    foreach ($guidelines as [$ico, $clr, $txt]):
                    ?>
                    <div class="flex items-start gap-3">
                        <i class="fas <?= $ico ?> <?= $clr ?> text-sm mt-0.5 shrink-0"></i>
                        <p class="text-xs text-gray-600 leading-relaxed"><?= $txt ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Quick Stats -->
                <?php
                $pendingCount  = count(array_filter($myReports, fn($r) => $r['status'] === 'pending'));
                $resolvedCount = count(array_filter($myReports, fn($r) => $r['status'] === 'resolved'));
                ?>
                <div class="mx-6 mb-6 grid grid-cols-3 gap-2 p-4 bg-gradient-to-br from-[#043915]/5 to-transparent rounded-xl">
                    <div class="text-center">
                        <p class="text-lg font-black text-gray-900"><?= count($myReports) ?></p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Total</p>
                    </div>
                    <div class="text-center border-x border-[#043915]/10">
                        <p class="text-lg font-black text-amber-500"><?= $pendingCount ?></p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Pending</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-black text-emerald-600"><?= $resolvedCount ?></p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Resolved</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
(function () {
    'use strict';

    /* ── Target selector ── */
    const targetBtns    = document.querySelectorAll('.target-btn');
    const studentFields = document.getElementById('studentFields');
    const teacherFields = document.getElementById('teacherFields');
    const otherFields   = document.getElementById('otherFields');

    function setTarget(val) {
        targetBtns.forEach(btn => {
            const active = btn.dataset.value === val;
            btn.classList.toggle('border-[#043915]',        active);
            btn.classList.toggle('bg-[#043915]/5',          active);
            btn.classList.toggle('border-gray-200',        !active);
            btn.querySelector('.target-icon').classList.toggle('text-[#043915]', active);
            btn.querySelector('.target-icon').classList.toggle('text-gray-400',  !active);
            btn.querySelector('.target-label').classList.toggle('text-[#043915]', active);
            btn.querySelector('.target-label').classList.toggle('text-gray-500',  !active);
            if (active) btn.querySelector('input[type=radio]').checked = true;
        });
        studentFields.classList.toggle('hidden', val !== 'student');
        teacherFields.classList.toggle('hidden', val !== 'teacher');
        otherFields.classList.toggle('hidden',   val !== 'other');
        if (val === 'teacher' && document.getElementById('teacherSelect').options.length <= 1) {
            loadTeachers();
        }
    }

    targetBtns.forEach(btn => btn.addEventListener('click', () => setTarget(btn.dataset.value)));
    setTarget('student');

    /* ── Grade → Student dropdown ── */
    const gradeSelect     = document.getElementById('gradeSelect');
    const studentDropWrap = document.getElementById('studentDropdownWrap');
    const studentSelect   = document.getElementById('studentSelect');
    const studentLoading  = document.getElementById('studentLoading');
    const studentEmpty    = document.getElementById('studentEmpty');

    gradeSelect.addEventListener('change', function () {
        const grade = this.value;
        if (!grade) { studentDropWrap.classList.add('hidden'); return; }
        studentLoading.classList.remove('hidden');
        studentSelect.innerHTML = '<option value="">Loading…</option>';
        studentDropWrap.classList.remove('hidden');
        studentEmpty.classList.add('hidden');

        fetch(window.location.pathname, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=get_students_by_grade&grade_level=${encodeURIComponent(grade)}`
        })
        .then(r => r.json())
        .then(res => {
            studentLoading.classList.add('hidden');
            studentSelect.innerHTML = '<option value="">— Select Student —</option>';
            if (res.success && res.data.length > 0) {
                res.data.forEach(s => {
                    studentSelect.appendChild(new Option(
                        `${s.name}${s.lrn ? ' (LRN: ' + s.lrn + ')' : ''}`, s.user_id
                    ));
                });
            } else {
                studentEmpty.classList.remove('hidden');
            }
        })
        .catch(() => {
            studentLoading.classList.add('hidden');
            studentSelect.innerHTML = '<option value="">Error loading students</option>';
        });
    });

    /* ── Load teachers ── */
    function loadTeachers() {
        const sel = document.getElementById('teacherSelect');
        sel.innerHTML = '<option value="">Loading…</option>';
        fetch(window.location.pathname, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_teachers'
        })
        .then(r => r.json())
        .then(res => {
            sel.innerHTML = '<option value="">— Select Teacher —</option>';
            if (res.success) {
                res.data.forEach(t => sel.appendChild(new Option(t.name, t.user_id)));
            }
        });
    }

    /* ── Custom violation ── */
    document.getElementById('violationSelect').addEventListener('change', function () {
        document.getElementById('customViolationWrap').classList.toggle('hidden', this.value !== 'other');
    });

    /* ── File upload drag & drop ── */
    const dropZone      = document.getElementById('dropZone');
    const evidenceInput = document.getElementById('evidenceInput');
    const filePreview   = document.getElementById('filePreview');
    const fileNameEl    = document.getElementById('fileName');
    const removeFile    = document.getElementById('removeFile');

    dropZone.addEventListener('click',    () => evidenceInput.click());
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-[#043915]/40','bg-[#043915]/5'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-[#043915]/40','bg-[#043915]/5'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-[#043915]/40','bg-[#043915]/5');
        if (e.dataTransfer.files[0]) { evidenceInput.files = e.dataTransfer.files; showFile(e.dataTransfer.files[0]); }
    });
    evidenceInput.addEventListener('change', function () { if (this.files[0]) showFile(this.files[0]); });

    function showFile(file) {
        fileNameEl.textContent = file.name + ' (' + (file.size / 1048576).toFixed(2) + ' MB)';
        filePreview.classList.remove('hidden');
        dropZone.classList.add('hidden');
    }
    removeFile.addEventListener('click', () => {
        evidenceInput.value = '';
        filePreview.classList.add('hidden');
        dropZone.classList.remove('hidden');
    });

    /* ── Alert helper ── */
    const alertBox  = document.getElementById('formAlert');
    const alertIcon = document.getElementById('formAlertIcon');
    const alertText = document.getElementById('formAlertText');

    function showAlert(msg, success) {
        alertIcon.className = 'fas shrink-0 ' + (success ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600');
        alertText.textContent = msg;
        alertBox.className = 'mb-5 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium border ' +
            (success ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200');
        alertBox.classList.remove('hidden');
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ── Clear form ── */
    window.resetForm = function () {
        document.getElementById('reportForm').reset();
        setTarget('student');
        document.getElementById('studentDropdownWrap').classList.add('hidden');
        document.getElementById('filePreview').classList.add('hidden');
        document.getElementById('dropZone').classList.remove('hidden');
        document.getElementById('customViolationWrap').classList.add('hidden');
        alertBox.classList.add('hidden');
    };

    /* ── Form submission ── */
    const form      = document.getElementById('reportForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitTxt = document.getElementById('submitText');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        alertBox.classList.add('hidden');
        submitBtn.disabled = true;
        submitTxt.textContent = 'Submitting…';
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');

        fetch(window.location.pathname, { method: 'POST', body: new FormData(form) })
        .then(r => r.json())
        .then(res => {
            submitBtn.disabled = false;
            submitTxt.textContent = 'Submit Report';
            submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            if (res.success) {
                showAlert(res.message, true);
                window.resetForm();
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert(res.message, false);
            }
        })
        .catch(() => {
            submitBtn.disabled = false;
            submitTxt.textContent = 'Submit Report';
            submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            showAlert('Network error. Please try again.', false);
        });
    });

})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>