<?php
$violations  = $teacherReportController->getViolations();
$myReports   = $teacherReportController->getMyReports();
$teacher     = $_SESSION['user'];
?>
<?php ob_start(); ?>
<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">
    <?php include __DIR__ . '/../../../includes/teacher-sidebar.php'; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Report an Incident</h1>
        <p class="text-slate-500 mt-1 text-sm">Submit a formal incident report. All reports are confidential and reviewed by the admin.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- ===== FORM CARD ===== -->
        <div class="xl:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-slate-800">New Incident Report</h2>
                        <p class="text-xs text-slate-400">Fill in all required fields</p>
                    </div>
                </div>

                <form id="reportForm" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="action" value="submit_report">

                    <!-- Step 1: Who are you reporting? -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Who are you reporting? <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3" id="targetSelector">
                            <?php foreach ([['student','Student','M17 20h2a2 2 0 002-2V8a2 2 0 00-2-2h-2M9 7H7a2 2 0 00-2 2v10a2 2 0 002 2h2m6-13V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M9 7h6'], ['teacher','Teacher','M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'], ['other','Other','M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z']] as [$val, $label, $icon]): ?>
                            <label class="target-btn cursor-pointer border-2 rounded-xl p-3 flex flex-col items-center gap-2 transition-all
                                          border-slate-200 hover:border-blue-400 hover:bg-blue-50 <?= $val === 'student' ? 'border-blue-500 bg-blue-50' : '' ?>"
                                   data-value="<?= $val ?>">
                                <input type="radio" name="report_target" value="<?= $val ?>" class="hidden" <?= $val === 'student' ? 'checked' : '' ?>>
                                <svg class="w-6 h-6 <?= $val === 'student' ? 'text-blue-600' : 'text-slate-400' ?> target-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="<?= $icon ?>"/>
                                </svg>
                                <span class="text-xs font-medium <?= $val === 'student' ? 'text-blue-700' : 'text-slate-500' ?> target-label"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Dynamic: Student fields -->
                    <div id="studentFields" class="mb-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Grade Level <span class="text-red-500">*</span>
                            </label>
                            <select name="grade_level" id="gradeSelect"
                                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white text-slate-700">
                                <option value="">— Select Grade Level —</option>
                                <?php foreach (['7','8','9','10','11','12'] as $g): ?>
                                    <option value="<?= $g ?>">Grade <?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="studentDropdownWrap" class="hidden">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Student Involved <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="student_id" id="studentSelect"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white text-slate-700 appearance-none">
                                    <option value="">— Select Student —</option>
                                </select>
                                <div id="studentLoading" class="hidden absolute right-3 top-3">
                                    <svg class="animate-spin w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </div>
                            </div>
                            <p id="studentEmpty" class="hidden text-xs text-amber-600 mt-1">No students found for this grade level.</p>
                        </div>
                    </div>

                    <!-- Dynamic: Teacher fields -->
                    <div id="teacherFields" class="mb-5 hidden">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Teacher Involved <span class="text-red-500">*</span>
                        </label>
                        <select name="teacher_involved_id" id="teacherSelect"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white text-slate-700">
                            <option value="">— Select Teacher —</option>
                        </select>
                    </div>

                    <!-- Dynamic: Other fields -->
                    <div id="otherFields" class="mb-5 hidden">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Name of Person Involved <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="other_name" id="otherName" placeholder="Enter full name"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-slate-300">
                    </div>

                    <!-- Location -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Location <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="location" placeholder="e.g. Classroom 3B, Corridor 2nd Floor, Canteen..."
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-slate-300">
                    </div>

                    <!-- Violation Type -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Violation Type <span class="text-red-500">*</span>
                        </label>
                        <select name="violation_id" id="violationSelect"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white text-slate-700">
                            <option value="">— Select Violation —</option>
                            <?php foreach ($violations as $v): ?>
                                <option value="<?= $v['discipline_id'] ?>"><?= htmlspecialchars($v['violation_name']) ?></option>
                            <?php endforeach; ?>
                            <option value="other">Other (specify below)</option>
                        </select>
                        <div id="customViolationWrap" class="mt-2 hidden">
                            <input type="text" name="custom_violation" id="customViolation"
                                   placeholder="Describe the violation..."
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-slate-300">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Description of Incident <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="5"
                                  placeholder="Provide a clear, factual account of what happened, when it occurred, and who was involved..."
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-slate-300 resize-none"></textarea>
                        <p class="text-xs text-slate-400 mt-1">Be specific and objective. Avoid opinions — stick to observable facts.</p>
                    </div>

                    <!-- Evidence Upload -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Upload Evidence <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <div id="dropZone"
                             class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/40 transition-all">
                            <input type="file" name="evidence" id="evidenceInput" accept="image/*,video/*,audio/*" class="hidden">
                            <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <p class="text-sm text-slate-500">Click or drag & drop to upload</p>
                            <p class="text-xs text-slate-400 mt-1">Image, Video, or Audio • Max 20MB</p>
                        </div>
                        <div id="filePreview" class="hidden mt-3 flex items-center gap-3 bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">
                            <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span id="fileName" class="text-sm text-slate-700 flex-1 truncate"></span>
                            <button type="button" id="removeFile" class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">If you have no evidence now, be prepared to present it at the office when called.</p>
                    </div>

                    <!-- Alert -->
                    <div id="formAlert" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium"></div>

                    <!-- Submit -->
                    <button type="submit" id="submitBtn"
                            class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-xl py-3 px-6 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span id="submitText">Submit Report</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- ===== SIDEBAR: My Reports ===== -->
        <div class="xl:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    My Reports
                    <span class="ml-auto bg-slate-100 text-slate-600 text-xs font-semibold px-2 py-0.5 rounded-full"><?= count($myReports) ?></span>
                </h3>

                <?php if (empty($myReports)): ?>
                    <div class="text-center py-8">
                        <svg class="w-10 h-10 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-slate-400">No reports submitted yet.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3 max-h-[540px] overflow-y-auto pr-1" id="reportsList">
                        <?php foreach ($myReports as $rpt):
                            $statusColors = [
                                'pending'   => 'bg-amber-100 text-amber-700',
                                'reviewed'  => 'bg-blue-100 text-blue-700',
                                'resolved'  => 'bg-green-100 text-green-700',
                                'dismissed' => 'bg-slate-100 text-slate-500',
                            ];
                            $sc = $statusColors[$rpt['status']] ?? 'bg-slate-100 text-slate-500';

                            if ($rpt['report_target'] === 'student') {
                                $person = $rpt['student_name'] ?? 'Unknown Student';
                            } elseif ($rpt['report_target'] === 'teacher') {
                                $person = $rpt['teacher_involved_name'] ?? 'Unknown Teacher';
                            } else {
                                $person = $rpt['other_name'] ?? 'Unknown';
                            }

                            $violation = $rpt['violation_name'] ?? $rpt['custom_violation'] ?? 'N/A';
                        ?>
                        <div class="border border-slate-100 rounded-xl p-3 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <p class="text-sm font-medium text-slate-800 leading-tight"><?= htmlspecialchars($person) ?></p>
                                <span class="<?= $sc ?> text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0 capitalize"><?= $rpt['status'] ?></span>
                            </div>
                            <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($violation) ?></p>
                            <p class="text-xs text-slate-400 mt-1"><?= date('M d, Y', strtotime($rpt['created_at'])) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5">
                <h4 class="text-sm font-semibold text-blue-800 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Reporting Guidelines
                </h4>
                <ul class="text-xs text-blue-700 space-y-1.5">
                    <li>• Be factual and objective in your description.</li>
                    <li>• Reports are confidential and reviewed by admin.</li>
                    <li>• False reports may result in disciplinary action.</li>
                    <li>• Preserve any evidence and present it when asked.</li>
                </ul>
            </div>
        </div>

    </div><!-- end grid -->
</main>

<script>
(function () {
    'use strict';

    const BASE = '/student-discipline-and-incident-reporting-system';

    // ---- Target selector ----
    const targetBtns = document.querySelectorAll('.target-btn');
    const studentFields = document.getElementById('studentFields');
    const teacherFields = document.getElementById('teacherFields');
    const otherFields   = document.getElementById('otherFields');

    function setTarget(val) {
        targetBtns.forEach(btn => {
            const active = btn.dataset.value === val;
            btn.classList.toggle('border-blue-500', active);
            btn.classList.toggle('bg-blue-50', active);
            btn.classList.toggle('border-slate-200', !active);
            btn.querySelector('.target-icon').classList.toggle('text-blue-600', active);
            btn.querySelector('.target-icon').classList.toggle('text-slate-400', !active);
            btn.querySelector('.target-label').classList.toggle('text-blue-700', active);
            btn.querySelector('.target-label').classList.toggle('text-slate-500', !active);
            if (active) btn.querySelector('input[type=radio]').checked = true;
        });

        studentFields.classList.toggle('hidden', val !== 'student');
        teacherFields.classList.toggle('hidden', val !== 'teacher');
        otherFields.classList.toggle('hidden',   val !== 'other');

        if (val === 'teacher' && document.getElementById('teacherSelect').options.length <= 1) {
            loadTeachers();
        }
    }

    targetBtns.forEach(btn => {
        btn.addEventListener('click', () => setTarget(btn.dataset.value));
    });

    setTarget('student');

    // ---- Grade → Student dropdown ----
    const gradeSelect       = document.getElementById('gradeSelect');
    const studentDropWrap   = document.getElementById('studentDropdownWrap');
    const studentSelect     = document.getElementById('studentSelect');
    const studentLoading    = document.getElementById('studentLoading');
    const studentEmpty      = document.getElementById('studentEmpty');

    gradeSelect.addEventListener('change', function () {
        const grade = this.value;
        if (!grade) { studentDropWrap.classList.add('hidden'); return; }
        studentLoading.classList.remove('hidden');
        studentSelect.innerHTML = '<option value="">Loading...</option>';
        studentDropWrap.classList.remove('hidden');
        studentEmpty.classList.add('hidden');

        fetch(window.location.pathname, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=get_students_by_grade&grade_level=${encodeURIComponent(grade)}`
        })
        .then(r => r.json())
        .then(res => {
            studentLoading.classList.add('hidden');
            studentSelect.innerHTML = '<option value="">— Select Student —</option>';
            if (res.success && res.data.length > 0) {
                res.data.forEach(s => {
                    const opt = new Option(`${s.name}${s.lrn ? ' (LRN: '+s.lrn+')' : ''}`, s.user_id);
                    studentSelect.appendChild(opt);
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

    // ---- Load teachers ----
    function loadTeachers() {
        const sel = document.getElementById('teacherSelect');
        sel.innerHTML = '<option value="">Loading...</option>';
        fetch(window.location.pathname, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=get_teachers'
        })
        .then(r => r.json())
        .then(res => {
            sel.innerHTML = '<option value="">— Select Teacher —</option>';
            if (res.success) {
                res.data.forEach(t => {
                    sel.appendChild(new Option(t.name, t.user_id));
                });
            }
        });
    }

    // ---- Custom violation ----
    document.getElementById('violationSelect').addEventListener('change', function () {
        document.getElementById('customViolationWrap').classList.toggle('hidden', this.value !== 'other');
    });

    // ---- File upload drag & drop ----
    const dropZone     = document.getElementById('dropZone');
    const evidenceInput = document.getElementById('evidenceInput');
    const filePreview  = document.getElementById('filePreview');
    const fileNameEl   = document.getElementById('fileName');
    const removeFile   = document.getElementById('removeFile');

    dropZone.addEventListener('click', () => evidenceInput.click());

    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-blue-400','bg-blue-50/40'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-blue-400','bg-blue-50/40'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400','bg-blue-50/40');
        if (e.dataTransfer.files[0]) {
            evidenceInput.files = e.dataTransfer.files;
            showFile(e.dataTransfer.files[0]);
        }
    });

    evidenceInput.addEventListener('change', function () {
        if (this.files[0]) showFile(this.files[0]);
    });

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

    // ---- Form submission ----
    const form      = document.getElementById('reportForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitTxt = document.getElementById('submitText');
    const alertBox  = document.getElementById('formAlert');

    function showAlert(msg, success) {
        alertBox.textContent = msg;
        alertBox.className = 'mb-4 px-4 py-3 rounded-xl text-sm font-medium ' +
            (success ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200');
        alertBox.classList.remove('hidden');
        alertBox.scrollIntoView({behavior:'smooth', block:'nearest'});
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        alertBox.classList.add('hidden');

        submitBtn.disabled = true;
        submitTxt.textContent = 'Submitting...';

        fetch(window.location.pathname, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(r => r.json())
        .then(res => {
            submitBtn.disabled = false;
            submitTxt.textContent = 'Submit Report';
            if (res.success) {
                showAlert('✓ ' + res.message, true);
                form.reset();
                setTarget('student');
                studentDropWrap.classList.add('hidden');
                filePreview.classList.add('hidden');
                dropZone.classList.remove('hidden');
                document.getElementById('customViolationWrap').classList.add('hidden');
                // Reload after 2s to update My Reports list
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert(res.message, false);
            }
        })
        .catch(() => {
            submitBtn.disabled = false;
            submitTxt.textContent = 'Submit Report';
            showAlert('Network error. Please try again.', false);
        });
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>