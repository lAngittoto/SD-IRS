<?php
$esc = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$wlClass = [
    'Extreme Violation'  => 'wl-extreme',
    'Grave Offense'      => 'wl-grave',
    'Less Grave Offense' => 'wl-less',
    'Minor Offense'      => 'wl-minor',
    'Warning'            => 'wl-warning',
];

ob_start(); ?>
<?php include __DIR__ . '/../../../includes/student-sidebar.php'; ?>

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blue:#2563eb;--blue-lt:#eff6ff;
  --bg:#f1f5f9;--card:#ffffff;--border:#e2e8f0;--border2:#f8fafc;
  --t1:#0f172a;--t2:#475569;--t3:#94a3b8;
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

.coc-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:28px 28px 20px;box-shadow:var(--sh)}
.coc-card h2{font-size:20px;font-weight:700;margin-bottom:4px}
.coc-card .sub{font-size:13.5px;color:var(--t3);margin-bottom:20px}

.tbl-wrap{border-radius:var(--r2);overflow:hidden;border:1px solid var(--border)}
.conduct-table{width:100%;border-collapse:collapse;font-size:13.5px}
.conduct-table th{background:var(--bg);padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border)}
.conduct-table td{padding:13px 16px;border-bottom:1px solid var(--border2);vertical-align:top;color:var(--t1)}
.conduct-table tr:last-child td{border-bottom:none}
.conduct-table tr:hover td{background:#f8faff}

.vio-name{font-weight:600;margin-bottom:3px}
.vio-desc{font-size:12px;color:var(--t2);line-height:1.55}

.wl-extreme{color:#dc2626;font-weight:600;font-size:12.5px}
.wl-grave  {color:#ea580c;font-weight:600;font-size:12.5px}
.wl-less   {color:#d97706;font-weight:600;font-size:12.5px}
.wl-minor  {color:#65a30d;font-weight:600;font-size:12.5px}
.wl-warning{color:#0284c7;font-weight:600;font-size:12.5px}
.wl-dot{font-size:7px;vertical-align:middle;margin-right:4px}

.sanction-chip{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:var(--blue-lt);color:var(--blue);white-space:nowrap}

.num-cell{font-weight:700;color:var(--t3);width:36px;text-align:center}

.empty{text-align:center;padding:52px 20px;color:var(--t3)}
.empty i{font-size:40px;opacity:.35;display:block;margin-bottom:12px}

@media(max-width:768px){
  .page{margin-left:0;padding:14px}
  .coc-card{padding:20px}
  .conduct-table thead{display:none}
  .conduct-table td{display:block;padding:8px 16px}
  .conduct-table td.num-cell{display:none}
  .conduct-table td:first-child{padding-top:14px}
  .conduct-table td:last-child{padding-bottom:14px}
}
</style>

<div class="page">
  <div class="coc-card">
    <h2>Code of Conduct</h2>
    <p class="sub">Familiarize yourself with school policies and corresponding sanctions.</p>

    <?php if (!empty($rules)): ?>
    <div class="tbl-wrap">
      <table class="conduct-table">
        <thead>
          <tr>
            <th style="width:42px;text-align:center">#</th>
            <th>Violation</th>
            <th>Severity</th>
            <th>Sanction</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rules as $idx => $rule):
          $wlKey = $rule['warning_level'] ?? '';
          $cls   = $wlClass[$wlKey] ?? '';
        ?>
        <tr>
          <td class="num-cell"><?= $idx + 1 ?></td>
          <td>
            <div class="vio-name"><?= $esc($rule['violation_name']) ?></div>
            <?php if (!empty($rule['description'])): ?>
              <div class="vio-desc"><?= $esc($rule['description']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($wlKey): ?>
              <span class="<?= $esc($cls) ?>">
                <i class="fa-solid fa-circle wl-dot"></i><?= $esc($wlKey) ?>
              </span>
            <?php else: ?>
              <span style="color:var(--t3)">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($rule['sanction_name'])): ?>
              <span class="sanction-chip"><?= $esc($rule['sanction_name']) ?></span>
            <?php else: ?>
              <span style="color:var(--t3)">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty">
      <i class="fa-solid fa-scale-balanced"></i>
      <p>No conduct rules found.</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>