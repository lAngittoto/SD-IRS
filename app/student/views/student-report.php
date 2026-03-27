<?php
$esc = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$old = $old ?? [];

ob_start(); ?>
<?php include __DIR__ . '/../../../includes/student-sidebar.php'; ?>

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blue:#2563eb;--blue2:#1d4ed8;
  --bg:#f1f5f9;--card:#ffffff;--border:#e2e8f0;--border2:#f8fafc;
  --t1:#0f172a;--t2:#475569;--t3:#94a3b8;
  --red:#ef4444;
  --sidebar-w:200px;
  --r:10px;--r2:7px;
  --sh:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);
  --tr:.18s ease
}
body{font-family:'Outfit',sans-serif;font-size:14px;background:var(--bg);color:var(--t1)}

/* ── FIX: push content right so the fixed sidebar doesn't overlap it ── */
.page{
  margin-left:var(--sidebar-w);
  padding:24px;
  min-height:100vh;
  animation:fadeIn .3s ease
}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}

/* Flash */
.alert{padding:12px 18px;border-radius:var(--r2);font-size:13.5px;font-weight:500;display:flex;align-items:flex-start;gap:10px;margin-bottom:16px}
.alert.success{background:#dcfce7;color:#166534;border:1px solid #86efac}
.alert.error  {background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}

/* Card */
.ri-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:28px;box-shadow:var(--sh);display:flex;flex-direction:column;gap:20px}
.ri-card h2{font-size:20px;font-weight:700;color:var(--t1)}
.ri-card > p{font-size:13.5px;color:var(--t2);margin-top:-10px}

/* Section title */
.ri-section-title{font-size:12px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.5px;padding-bottom:8px;border-bottom:1px solid var(--border2)}

/* Grid */
.ri-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}

/* Field group */
.ri-fg{display:flex;flex-direction:column;gap:6px}
.ri-fg label{font-size:13px;font-weight:500;color:var(--t2)}

/* Inputs */
.ri-input{
  padding:10px 13px;border:1px solid var(--border);border-radius:var(--r2);
  font-family:'Outfit',sans-serif;font-size:13.5px;color:var(--t1);
  background:var(--bg);transition:border-color var(--tr);width:100%
}
.ri-input:focus{outline:none;border-color:var(--blue);background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.07)}
select.ri-input{
  appearance:none;cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236b7280' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 12px center;padding-right:32px
}
.ri-textarea{resize:vertical;min-height:120px}
.ri-hint{font-size:12px;color:var(--t3);line-height:1.5}

/* File upload */
.ri-file-label{
  display:flex;align-items:center;border:1px solid var(--border);
  border-radius:var(--r2);overflow:hidden;cursor:pointer;width:100%;
  transition:border-color var(--tr)
}
.ri-file-label:hover{border-color:var(--blue)}
.ri-file-label input{position:absolute;opacity:0;pointer-events:none;width:0;height:0}
.ri-file-label::before{
  content:"Browse…";background:var(--bg);border-right:1px solid var(--border);
  padding:10px 14px;font-size:13px;font-weight:500;color:var(--t1);white-space:nowrap
}
.ri-file-label:hover::before{background:#e5e7eb}
.ri-file-label span{padding:10px 13px;font-size:13px;color:var(--t3);flex:1}

/* Inline field error */
.ferr{font-size:11px;color:var(--red);min-height:14px}

/* Optional label */
.opt{font-size:11px;color:var(--t3);font-weight:400}

/* Actions */
.ri-actions{display:flex;justify-content:flex-end;gap:8px;padding-top:4px}
.btn-s{padding:9px 20px;border-radius:var(--r2);font-family:'Outfit',sans-serif;font-size:13px;font-weight:600;cursor:pointer;background:var(--bg);color:var(--t2);border:1px solid var(--border);transition:all var(--tr)}
.btn-s:hover{background:var(--border);color:var(--t1)}
.btn-p{padding:9px 24px;border-radius:var(--r2);font-family:'Outfit',sans-serif;font-size:13.5px;font-weight:600;cursor:pointer;border:none;background:var(--blue);color:#fff;transition:all var(--tr)}
.btn-p:hover{background:var(--blue2);transform:translateY(-1px)}
.btn-p:disabled{opacity:.6;cursor:not-allowed;transform:none}

/* Toast */
.toast{position:fixed;bottom:24px;right:24px;color:#fff;padding:12px 18px;border-radius:var(--r2);font-size:13.5px;font-weight:500;box-shadow:0 8px 32px rgba(0,0,0,.14);transform:translateY(16px);opacity:0;transition:all .3s cubic-bezier(.34,1.56,.64,1);pointer-events:none;z-index:999}
.toast.on{opacity:1;transform:none}
.toast.err{background:#991b1b}

@media(max-width:768px){
  .page{margin-left:0;padding:14px}
  .ri-card{padding:20px}
  .ri-grid{grid-template-columns:1fr}
}
</style>

<div class="page">

  <?php if ($flash): ?>
  <div class="alert <?= $esc($flash['type']) ?>">
    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
    <div><?php
      if (!empty($flash['message']))      echo $esc($flash['message']);
      elseif (!empty($flash['messages'])) foreach ($flash['messages'] as $m) echo '<div>' . $esc($m) . '</div>';
    ?></div>
  </div>
  <?php endif; ?>

  <div class="ri-card">
    <div>
      <h2>Report an Incident</h2>
      <p>Fill in all required fields accurately. Your report will be reviewed by administration.</p>
    </div>

    <form method="POST" action="?page=student-report" enctype="multipart/form-data" id="reportForm" novalidate>

      <div style="display:flex;flex-direction:column;gap:16px">

        <div class="ri-section-title">Who are you reporting?</div>
        <div class="ri-grid">

          <div class="ri-fg">
            <label>Report Target <span style="color:var(--red)">*</span></label>
            <select id="ri_type" name="report_target" class="ri-input"
                    onchange="toggleTargetFields(this.value)">
              <option value="">Select one</option>
              <option value="student" <?= ($old['report_target'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
              <option value="teacher" <?= ($old['report_target'] ?? '') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
              <option value="other"   <?= ($old['report_target'] ?? '') === 'other'   ? 'selected' : '' ?>>Other</option>
            </select>
            <span class="ferr" id="e_type"></span>
          </div>

          <div class="ri-fg" id="field_student" style="display:none">
            <label>Select Student <span style="color:var(--red)">*</span></label>
            <select id="ri_student" name="target_student_id" class="ri-input">
              <option value="">Select student</option>
              <?php foreach ($students as $s): ?>
                <option value="<?= (int) $s['user_id'] ?>"
                  <?= ($old['target_student_id'] ?? '') == $s['user_id'] ? 'selected' : '' ?>>
                  <?= $esc($s['name']) ?><?= $s['lrn'] ? ' (LRN: ' . $esc($s['lrn']) . ')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="ferr" id="e_student"></span>
          </div>

          <div class="ri-fg" id="field_teacher" style="display:none">
            <label>Select Teacher <span style="color:var(--red)">*</span></label>
            <select id="ri_teacher" name="target_teacher_id" class="ri-input">
              <option value="">Select teacher</option>
              <?php foreach ($teachers as $t): ?>
                <option value="<?= (int) $t['user_id'] ?>"
                  <?= ($old['target_teacher_id'] ?? '') == $t['user_id'] ? 'selected' : '' ?>>
                  <?= $esc($t['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="ferr" id="e_teacher"></span>
          </div>

          <div class="ri-fg" id="field_other" style="display:none">
            <label>Name of Person <span style="color:var(--red)">*</span></label>
            <input type="text" id="ri_other" name="other_name" class="ri-input"
                   placeholder="Full name" value="<?= $esc($old['other_name'] ?? '') ?>"/>
            <span class="ferr" id="e_other"></span>
          </div>

        </div>

        <div class="ri-section-title" style="margin-top:4px">Incident Details</div>
        <div class="ri-grid">

          <div class="ri-fg">
            <label>Location <span style="color:var(--red)">*</span></label>
            <input type="text" id="ri_loc" name="location" class="ri-input"
                   placeholder="e.g., Classroom 3B" value="<?= $esc($old['location'] ?? '') ?>"/>
            <span class="ferr" id="e_loc"></span>
          </div>

          <div class="ri-fg">
            <label>Violation Type <span style="color:var(--red)">*</span></label>
            <select id="ri_vio" name="violation_id" class="ri-input"
                    onchange="toggleCustomViolation(this.value)">
              <option value="">Select a violation</option>
              <?php foreach ($violations as $v): ?>
                <option value="<?= (int) $v['discipline_id'] ?>"
                  <?= ($old['violation_id'] ?? '') == $v['discipline_id'] ? 'selected' : '' ?>>
                  <?= $esc($v['violation_name']) ?>
                </option>
              <?php endforeach; ?>
              <option value="other" <?= ($old['violation_id'] ?? '') === 'other' ? 'selected' : '' ?>>
                Other (specify below)
              </option>
            </select>
            <span class="ferr" id="e_vio"></span>
          </div>

        </div>

        <div class="ri-fg" id="field_custom_vio" style="display:none">
          <label>Describe Violation <span style="color:var(--red)">*</span></label>
          <input type="text" id="ri_custom_vio" name="custom_violation" class="ri-input"
                 placeholder="Briefly describe the violation"
                 value="<?= $esc($old['custom_violation'] ?? '') ?>"/>
        </div>

        <div class="ri-fg">
          <label>Description <span style="color:var(--red)">*</span></label>
          <textarea id="ri_desc" name="description" class="ri-input ri-textarea"
                    placeholder="Provide a detailed and factual account…"><?= $esc($old['description'] ?? '') ?></textarea>
          <p class="ri-hint">Be specific: what happened, when, who was involved, and any witnesses.</p>
          <span class="ferr" id="e_desc"></span>
        </div>

        <div class="ri-fg">
          <label>Upload Evidence <span class="opt">(Optional)</span></label>
          <label class="ri-file-label" for="ri_file">
            <input type="file" id="ri_file" name="evidence"
                   accept="image/*,video/*,audio/*,.pdf,.doc,.docx"/>
            <span id="ri_file_name">No file selected.</span>
          </label>
          <p class="ri-hint">Accepted: images, videos, audio, PDF, Word docs. Max 10 MB.</p>
        </div>

        <div class="ri-actions">
          <button type="button" class="btn-s" onclick="resetForm()">
            <i class="fa-solid fa-rotate-left" style="font-size:11px"></i> Clear
          </button>
          <button type="submit" class="btn-p" id="submitBtn">
            <i class="fa-solid fa-paper-plane" style="font-size:12px;margin-right:5px"></i>Submit Report
          </button>
        </div>

      </div>
    </form>
  </div>
</div><!-- /.page -->

<div class="toast" id="toastEl"></div>

<script>
const $ = id => document.getElementById(id);
const toastEl = $('toastEl');

function toast(msg, type = 'err') {
  toastEl.textContent = msg;
  toastEl.className   = `toast on ${type}`;
  clearTimeout(toastEl._t);
  toastEl._t = setTimeout(() => toastEl.className = 'toast', 3200);
}

function toggleTargetFields(val) {
  ['student', 'teacher', 'other'].forEach(t => {
    const el = document.getElementById('field_' + t);
    if (el) el.style.display = val === t ? '' : 'none';
  });
}

function toggleCustomViolation(val) {
  const el = $('field_custom_vio');
  if (el) el.style.display = val === 'other' ? '' : 'none';
}

// Restore selections from old POST data on validation failure
const st = '<?= $esc($old['report_target'] ?? '') ?>';
if (st) toggleTargetFields(st);

const sv = '<?= $esc((string) ($old['violation_id'] ?? '')) ?>';
if (sv === 'other') toggleCustomViolation('other');

function setErr(id, msg) {
  const el = $(id);
  if (el) el.textContent = msg;
}

const rf = $('reportForm');
if (rf) {
  rf.addEventListener('submit', function (e) {
    let ok = true;

    const rt = $('ri_type').value;
    setErr('e_type', rt ? '' : 'Please select who you are reporting.');
    if (!rt) ok = false;

    if (rt === 'student') {
      const v = $('ri_student').value;
      setErr('e_student', v ? '' : 'Please select the student.');
      if (!v) ok = false;
    }
    if (rt === 'teacher') {
      const v = $('ri_teacher').value;
      setErr('e_teacher', v ? '' : 'Please select the teacher.');
      if (!v) ok = false;
    }
    if (rt === 'other') {
      const v = $('ri_other').value.trim();
      setErr('e_other', v ? '' : 'Please enter the name.');
      if (!v) ok = false;
    }

    const loc = $('ri_loc').value.trim();
    setErr('e_loc', loc ? '' : 'Location is required.');
    if (!loc) ok = false;

    const vio = $('ri_vio').value;
    const cv  = $('ri_custom_vio') ? $('ri_custom_vio').value.trim() : '';
    const vok = (vio && vio !== 'other') || (vio === 'other' && cv);
    setErr('e_vio', vok ? '' : 'Please select or describe a violation.');
    if (!vok) ok = false;

    const desc = $('ri_desc').value.trim();
    setErr('e_desc', desc ? '' : 'Description is required.');
    if (!desc) ok = false;

    if (!ok) {
      e.preventDefault();
      toast('Please fill in all required fields.');
    } else {
      $('submitBtn').disabled  = true;
      $('submitBtn').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting…';
    }
  });
}

const rf2 = $('ri_file');
if (rf2) {
  rf2.addEventListener('change', e => {
    $('ri_file_name').textContent = e.target.files.length
      ? e.target.files[0].name
      : 'No file selected.';
  });
}

function resetForm() {
  if (rf) rf.reset();
  ['student', 'teacher', 'other'].forEach(t => {
    const el = document.getElementById('field_' + t);
    if (el) el.style.display = 'none';
  });
  const cvEl = $('field_custom_vio');
  if (cvEl) cvEl.style.display = 'none';
  $('ri_file_name').textContent = 'No file selected.';
  ['e_type','e_student','e_teacher','e_other','e_loc','e_vio','e_desc']
    .forEach(id => setErr(id, ''));
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>