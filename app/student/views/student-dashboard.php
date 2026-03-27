<?php
$esc = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');

$statusBadge = [
    'pending'   => ['label' => 'Pending',      'class' => 'badge-pending'],
    'reviewed'  => ['label' => 'Under Review',  'class' => 'badge-reviewed'],
    'resolved'  => ['label' => 'Resolved',     'class' => 'badge-resolved'],
    'dismissed' => ['label' => 'Dismissed',    'class' => 'badge-dismissed'],
];

ob_start(); ?>
<?php include __DIR__ . '/../../../includes/student-sidebar.php'; ?>

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blue:#2563eb;--blue2:#1d4ed8;--blue-lt:#eff6ff;
  --bg:#f1f5f9;--card:#ffffff;--border:#e2e8f0;--border2:#f8fafc;
  --t1:#0f172a;--t2:#475569;--t3:#94a3b8;
  --green:#16a34a;--green-lt:#dcfce7;
  --yellow:#d97706;--yellow-lt:#fef9c3;
  --red:#dc2626;--red-lt:#fee2e2;
  --purple:#7c3aed;--purple-lt:#ede9fe;
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
  display:flex;
  flex-direction:column;
  gap:20px;
  animation:fadeIn .3s ease
}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}

/* Flash */
.alert{padding:12px 18px;border-radius:var(--r2);font-size:13.5px;font-weight:500;display:flex;align-items:flex-start;gap:10px}
.alert.success{background:#dcfce7;color:#166534;border:1px solid #86efac}
.alert.error  {background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}

/* Profile card */
.profile-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:24px;box-shadow:var(--sh);display:flex;align-items:center;gap:20px}
.profile-avatar{width:68px;height:68px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0}
.profile-avatar-placeholder{width:68px;height:68px;border-radius:50%;background:var(--blue-lt);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid var(--border)}
.profile-avatar-placeholder i{font-size:28px;color:var(--blue)}
.profile-info{flex:1;min-width:0}
.profile-name{font-size:18px;font-weight:700;color:var(--t1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.profile-meta{font-size:13px;color:var(--t2);margin-top:3px}
.profile-badges{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap}
.profile-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:var(--blue-lt);color:var(--blue);border:1px solid #bfdbfe}

/* Summary cards */
.summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.sum-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:18px 20px;box-shadow:var(--sh);display:flex;align-items:center;gap:14px}
.sum-icon{width:44px;height:44px;border-radius:var(--r2);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:17px}
.sum-icon.total   {background:#f0f9ff;color:#0284c7}
.sum-icon.pending {background:var(--yellow-lt);color:var(--yellow)}
.sum-icon.review  {background:var(--purple-lt);color:var(--purple)}
.sum-icon.resolved{background:var(--green-lt);color:var(--green)}
.sum-label{font-size:12px;color:var(--t3);font-weight:500}
.sum-value{font-size:22px;font-weight:700;color:var(--t1);line-height:1.1}

/* Table card */
.tbl-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden}
.tbl-header{padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border-bottom:1px solid var(--border)}
.tbl-title{font-size:16px;font-weight:700;color:var(--t1)}
.tbl-filters{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.filter-select{
  padding:7px 32px 7px 12px;border:1px solid var(--border);border-radius:var(--r2);
  font-family:'Outfit',sans-serif;font-size:12.5px;color:var(--t1);background:var(--bg);
  cursor:pointer;appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236b7280' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 8px center
}
.filter-select:focus{outline:none;border-color:var(--blue)}

/* Table */
.tbl-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse;font-size:13.5px}
.data-table th{background:var(--bg);padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);white-space:nowrap}
.data-table td{padding:13px 16px;border-bottom:1px solid var(--border2);vertical-align:middle;color:var(--t1)}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:#f8faff}

/* Badges */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-pending  {background:var(--yellow-lt);color:#92400e}
.badge-reviewed {background:var(--purple-lt);color:#5b21b6}
.badge-resolved {background:var(--green-lt); color:#166534}
.badge-dismissed{background:#f1f5f9;         color:var(--t2)}

/* Violation chip */
.vio-chip{display:inline-block;padding:2px 9px;border-radius:20px;font-size:12px;font-weight:500;background:var(--blue-lt);color:var(--blue);white-space:nowrap;max-width:200px;overflow:hidden;text-overflow:ellipsis}

/* Detail button */
.btn-detail{padding:5px 12px;border-radius:var(--r2);font-family:'Outfit',sans-serif;font-size:12px;font-weight:600;cursor:pointer;border:1px solid var(--border);background:var(--bg);color:var(--t2);transition:all var(--tr)}
.btn-detail:hover{background:var(--blue);color:#fff;border-color:var(--blue)}

/* Empty */
.empty{text-align:center;padding:52px 20px;color:var(--t3)}
.empty i{font-size:40px;opacity:.35;display:block;margin-bottom:12px}

/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:500;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;pointer-events:none;transition:opacity .2s ease}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal{background:#fff;border-radius:var(--r);box-shadow:0 24px 80px rgba(0,0,0,.18);width:100%;max-width:540px;max-height:88vh;overflow-y:auto;transform:translateY(16px) scale(.98);transition:transform .25s cubic-bezier(.34,1.56,.64,1),opacity .2s ease;opacity:0}
.modal-overlay.open .modal{transform:none;opacity:1}
.modal-head{padding:22px 24px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;position:sticky;top:0;background:#fff;z-index:1;border-bottom:1px solid var(--border)}
.modal-head h3{font-size:16px;font-weight:700}
.modal-close{width:30px;height:30px;border-radius:50%;border:1px solid var(--border);background:var(--bg);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--t2);transition:all var(--tr)}
.modal-close:hover{background:var(--red-lt);color:var(--red);border-color:#fca5a5}
.modal-body{padding:20px 24px 24px}
.detail-row{display:flex;flex-direction:column;gap:3px;padding:12px 0;border-bottom:1px solid var(--border2)}
.detail-row:last-child{border-bottom:none}
.detail-label{font-size:11px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px}
.detail-value{font-size:13.5px;color:var(--t1);line-height:1.6}
.detail-notes{background:var(--blue-lt);border-radius:var(--r2);padding:10px 14px;font-size:13px;color:#1e40af;line-height:1.6;margin-top:2px}
.modal-loader{text-align:center;padding:40px;color:var(--t3)}

@media(max-width:1024px){.summary-grid{grid-template-columns:1fr 1fr}}
@media(max-width:768px){
  .page{margin-left:0;padding:14px}
  .summary-grid{grid-template-columns:1fr 1fr}
  .profile-card{flex-direction:column;align-items:flex-start}
  .tbl-header{flex-direction:column;align-items:flex-start}
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

  <!-- Profile Card -->
  <div class="profile-card">
    <?php if (!empty($student['profile_pix'])): ?>
      <img src="<?= $esc($student['profile_pix']) ?>" class="profile-avatar" alt="Profile Photo">
    <?php else: ?>
      <div class="profile-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
    <?php endif; ?>
    <div class="profile-info">
      <div class="profile-name"><?= $esc($student['name'] ?? '—') ?></div>
      <div class="profile-meta">LRN: <?= $esc($student['lrn'] ?: '—') ?></div>
      <div class="profile-badges">
        <?php if (!empty($student['grade_level'])): ?>
          <span class="profile-badge">
            <i class="fa-solid fa-graduation-cap" style="font-size:10px"></i>
            Grade <?= $esc($student['grade_level']) ?>
          </span>
        <?php endif; ?>
        <?php if (!empty($student['advisory_name'])): ?>
          <span class="profile-badge">
            <i class="fa-solid fa-users" style="font-size:10px"></i>
            <?= $esc($student['advisory_name']) ?>
          </span>
        <?php endif; ?>
        <?php if (!empty($student['adviser_name'])): ?>
          <span class="profile-badge">
            <i class="fa-solid fa-chalkboard-teacher" style="font-size:10px"></i>
            <?= $esc($student['adviser_name']) ?>
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="summary-grid">
    <div class="sum-card">
      <div class="sum-icon total"><i class="fa-solid fa-folder-open"></i></div>
      <div>
        <div class="sum-label">Total Reports</div>
        <div class="sum-value"><?= (int) ($summary['total'] ?? 0) ?></div>
      </div>
    </div>
    <div class="sum-card">
      <div class="sum-icon pending"><i class="fa-solid fa-clock"></i></div>
      <div>
        <div class="sum-label">Pending</div>
        <div class="sum-value"><?= (int) ($summary['pending'] ?? 0) ?></div>
      </div>
    </div>
    <div class="sum-card">
      <div class="sum-icon review"><i class="fa-solid fa-magnifying-glass"></i></div>
      <div>
        <div class="sum-label">Under Review</div>
        <div class="sum-value"><?= (int) ($summary['under_review'] ?? 0) ?></div>
      </div>
    </div>
    <div class="sum-card">
      <div class="sum-icon resolved"><i class="fa-solid fa-circle-check"></i></div>
      <div>
        <div class="sum-label">Resolved</div>
        <div class="sum-value"><?= (int) ($summary['resolved'] ?? 0) ?></div>
      </div>
    </div>
  </div>

  <!-- Incident Table -->
  <div class="tbl-card">
    <div class="tbl-header">
      <div class="tbl-title">My Incident Records</div>
      <form method="GET" action="" class="tbl-filters" id="filterForm">
        <input type="hidden" name="page" value="student-dashboard">
        <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
          <option value="">All Statuses</option>
          <option value="pending"   <?= $filterStatus === 'pending'   ? 'selected' : '' ?>>Pending</option>
          <option value="reviewed"  <?= $filterStatus === 'reviewed'  ? 'selected' : '' ?>>Under Review</option>
          <option value="resolved"  <?= $filterStatus === 'resolved'  ? 'selected' : '' ?>>Resolved</option>
          <option value="dismissed" <?= $filterStatus === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
        </select>
        <select name="violation" class="filter-select" onchange="document.getElementById('filterForm').submit()">
          <option value="">All Violations</option>
          <?php foreach ($violations as $v): ?>
            <option value="<?= (int) $v['discipline_id'] ?>"
              <?= $filterViolation == $v['discipline_id'] ? 'selected' : '' ?>>
              <?= $esc($v['violation_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <select name="sort" class="filter-select" onchange="document.getElementById('filterForm').submit()">
          <option value="desc" <?= $filterSort === 'desc' ? 'selected' : '' ?>>Newest First</option>
          <option value="asc"  <?= $filterSort === 'asc'  ? 'selected' : '' ?>>Oldest First</option>
        </select>
      </form>
    </div>

    <div class="tbl-wrap">
      <?php if (!empty($incidents)): ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Violation</th>
            <th>Location</th>
            <th>Reported By</th>
            <th>Date</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($incidents as $i => $inc): ?>
        <tr>
          <td style="color:var(--t3);font-weight:700"><?= $i + 1 ?></td>
          <td>
            <span class="vio-chip" title="<?= $esc($inc['violation_display'] ?? 'Custom') ?>">
              <?= $esc($inc['violation_display'] ?: 'Custom') ?>
            </span>
          </td>
          <td style="color:var(--t2)"><?= $esc($inc['location']) ?></td>
          <td style="color:var(--t2)"><?= $esc($inc['reported_by'] ?? '—') ?></td>
          <td style="color:var(--t3);white-space:nowrap;font-size:12.5px">
            <?= $inc['created_at'] ? date('M j, Y', strtotime($inc['created_at'])) : '—' ?>
          </td>
          <td>
            <?php $b = $statusBadge[$inc['status']] ?? ['label' => ucfirst($inc['status']), 'class' => 'badge-dismissed']; ?>
            <span class="badge <?= $b['class'] ?>"><?= $b['label'] ?></span>
          </td>
          <td>
            <button class="btn-detail" onclick="openDetail(<?= (int) $inc['report_id'] ?>)">
              <i class="fa-solid fa-eye" style="font-size:11px;margin-right:3px"></i>View
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="empty">
        <i class="fa-solid fa-folder-open"></i>
        <p>No incident records found.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div><!-- /.page -->

<!-- Detail Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
  <div class="modal" id="modalBox" role="dialog" aria-modal="true">
    <div class="modal-head">
      <h3><i class="fa-solid fa-file-lines" style="color:var(--blue);margin-right:7px"></i>Incident Detail</h3>
      <button class="modal-close" onclick="closeDetailModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="modalBody">
      <div class="modal-loader"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>
    </div>
  </div>
</div>

<script>
const overlay = document.getElementById('modalOverlay');
const body    = document.getElementById('modalBody');
const esc     = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

function openDetail(reportId) {
  overlay.classList.add('open');
  body.innerHTML = '<div class="modal-loader"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</div>';

  fetch(`?page=student-report&action=detail&report_id=${reportId}`)
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        body.innerHTML = `<p style="color:var(--red);padding:20px">${esc(res.message)}</p>`;
        return;
      }
      const d = res.data;
      const statusMap = { pending:'Pending', reviewed:'Under Review', resolved:'Resolved', dismissed:'Dismissed' };
      const badgeCls  = { pending:'badge-pending', reviewed:'badge-reviewed', resolved:'badge-resolved', dismissed:'badge-dismissed' };
      const status    = d.status ?? '';

      body.innerHTML = `
        <div class="detail-row">
          <div class="detail-label">Violation</div>
          <div class="detail-value">${esc(d.violation_display || 'Not specified')}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Severity / Sanction</div>
          <div class="detail-value">${esc(d.warning_level || '—')} &nbsp;·&nbsp; ${esc(d.sanction_name || '—')}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Location</div>
          <div class="detail-value">${esc(d.location)}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Description</div>
          <div class="detail-value">${esc(d.description)}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Reported By</div>
          <div class="detail-value">${esc(d.reported_by || '—')}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Date Filed</div>
          <div class="detail-value">${esc(d.date_display || '—')}</div>
        </div>
        <div class="detail-row">
          <div class="detail-label">Status</div>
          <div class="detail-value"><span class="badge ${badgeCls[status] ?? 'badge-dismissed'}">${esc(statusMap[status] ?? status)}</span></div>
        </div>
        ${d.reviewed_by_name ? `
        <div class="detail-row">
          <div class="detail-label">Reviewed By</div>
          <div class="detail-value">${esc(d.reviewed_by_name)} &nbsp;·&nbsp; ${esc(d.reviewed_display || '—')}</div>
        </div>` : ''}
        ${d.admin_notes ? `
        <div class="detail-row">
          <div class="detail-label">Admin Notes</div>
          <div class="detail-notes">${esc(d.admin_notes)}</div>
        </div>` : ''}
      `;
    })
    .catch(() => {
      body.innerHTML = '<p style="color:var(--red);padding:20px">Failed to load details.</p>';
    });
}

function closeDetailModal() { overlay.classList.remove('open'); }
function closeModal(e)      { if (e.target === overlay) closeDetailModal(); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetailModal(); });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>