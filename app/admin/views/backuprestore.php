<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}
?>
<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-database text-[#f8c922] text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-gray-900">Backup &amp; Restore</h1>
                <p class="text-sm text-gray-600 mt-1">Download a full database backup or restore from a previously saved <code class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded text-xs font-mono">.sql</code> file.</p>
            </div>
        </div>
    </div>

    <!-- Feedback Alert -->
    <?php if (!empty($feedback)): ?>
        <?php
            $isSuccess  = $feedback['type'] === 'success';
            $alertBg    = $isSuccess ? 'bg-emerald-50 border-emerald-400 text-emerald-800' : 'bg-red-50 border-red-400 text-red-800';
            $alertIcon  = $isSuccess ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-red-500';
        ?>
        <div class="flex items-start gap-3 px-5 py-4 rounded-2xl border-l-4 mb-6 <?= $alertBg ?>">
            <i class="fas <?= $alertIcon ?> mt-0.5 text-lg shrink-0"></i>
            <span class="text-sm font-semibold"><?= htmlspecialchars($feedback['message']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Top Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

        <!-- ── Database Overview ── -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex flex-col gap-4">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 bg-[#043915]/10 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-server text-[#043915] text-lg"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Database Overview</h2>
                    <p class="text-xs text-gray-500">Current database stats</p>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-gray-500">Total Tables</span>
                    <span class="bg-[#043915]/10 text-[#043915] text-xs font-bold px-3 py-1 rounded-full"><?= $totalTables ?></span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-gray-500">Total Rows</span>
                    <span class="bg-[#043915]/10 text-[#043915] text-xs font-bold px-3 py-1 rounded-full"><?= number_format($totalRows) ?></span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-gray-500">Last Backup</span>
                    <span class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1 rounded-full">—</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-gray-500">Engine</span>
                    <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">MySQL / InnoDB</span>
                </div>
            </div>
        </div>

        <!-- ── Download Backup ── -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-download text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Download Backup</h2>
                    <p class="text-xs text-gray-500">Export full .sql dump</p>
                </div>
            </div>

            <p class="text-sm text-gray-500 leading-relaxed mb-6 flex-1">
                Export a complete <strong class="text-gray-700">.sql</strong> dump of all tables and data.
                Store this file somewhere safe — it can be used to fully restore your database at any time.
            </p>

            <a href="?action=download"
               class="flex items-center justify-center gap-2 w-full bg-[#043915] hover:bg-[#032a0f] text-[#f8c922] font-bold text-sm py-3 px-5 rounded-xl transition-colors duration-200 shadow-sm">
                <i class="fas fa-file-arrow-down"></i>
                Download .sql Backup
            </a>
        </div>

        <!-- ── Restore Backup ── -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-rotate-left text-red-500 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Restore Backup</h2>
                    <p class="text-xs text-gray-500">Upload a .sql file to restore</p>
                </div>
            </div>

            <!-- Warning -->
            <div class="flex items-start gap-2.5 bg-amber-50 border-l-4 border-amber-400 rounded-r-xl px-4 py-3 mb-4">
                <i class="fas fa-triangle-exclamation text-amber-500 mt-0.5 shrink-0 text-sm"></i>
                <p class="text-xs text-amber-800 leading-relaxed">
                    <strong class="block mb-0.5">Warning!</strong>
                    Restoring will <u>overwrite all current data</u>. This cannot be undone. Ensure you have a fresh backup first.
                </p>
            </div>

            <!-- Hidden real form submitted after modal confirm -->
            <form id="restore-form"
                  action="?action=restore"
                  method="POST"
                  enctype="multipart/form-data"
                  class="hidden">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="file" id="hidden-file-input" name="sql_file" accept=".sql">
            </form>

            <!-- Drop Zone -->
            <div id="drop-zone"
                 class="relative border-2 border-dashed border-gray-200 hover:border-[#043915] bg-gray-50 hover:bg-[#043915]/5 rounded-xl p-5 text-center cursor-pointer transition-all duration-200 mb-3">
                <input type="file" id="visible-file-input" accept=".sql"
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <i class="fas fa-cloud-arrow-up text-3xl text-gray-300 mb-2 block"></i>
                <p class="text-xs text-gray-500">Drag &amp; drop your <strong>.sql</strong> file here<br>or <span class="text-[#043915] font-bold">browse to upload</span></p>
            </div>

            <p id="file-name-display" class="text-xs text-gray-400 italic mb-4">No file selected.</p>

            <button type="button" id="open-confirm-btn" disabled
                    class="flex items-center justify-center gap-2 w-full bg-red-500 hover:bg-red-600 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white font-bold text-sm py-3 px-5 rounded-xl transition-colors duration-200 shadow-sm mt-auto">
                <i class="fas fa-upload"></i>
                Restore Database
            </button>
        </div>

    </div><!-- /top cards -->

    <!-- Table Details Card -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-table-list text-[#043915]"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Table Details</h2>
                    <p class="text-xs text-gray-500">Row counts per table</p>
                </div>
            </div>
            <span class="text-xs font-bold text-gray-400"><?= $totalTables ?> tables</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-6 py-3 w-12">#</th>
                        <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-6 py-3">Table Name</th>
                        <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-6 py-3">Row Count</th>
                        <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $i = 1; foreach ($tableRowCounts as $table => $count): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3.5 text-gray-400 font-medium text-xs"><?= $i++ ?></td>
                        <td class="px-6 py-3.5">
                            <code class="bg-[#043915]/8 text-[#043915] px-2.5 py-1 rounded-lg text-xs font-mono font-semibold">
                                <?= htmlspecialchars($table) ?>
                            </code>
                        </td>
                        <td class="px-6 py-3.5 text-gray-700 font-bold"><?= number_format($count) ?></td>
                        <td class="px-6 py-3.5">
                            <?php if ($count > 0): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 inline-block"></span> Has Data
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-bold rounded-lg">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block"></span> Empty
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ── Confirm Restore Modal ── -->
<div id="confirm-modal"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] opacity-0 pointer-events-none transition-opacity duration-200">
    <div id="modal-box"
         class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-[90%] text-center transform scale-95 transition-transform duration-200">

        <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-triangle-exclamation text-red-500 text-2xl"></i>
        </div>

        <h3 class="text-lg font-black text-gray-900 mb-2">Confirm Restore</h3>
        <p class="text-sm text-gray-500 leading-relaxed mb-1">
            You are about to restore the database from:
        </p>
        <p class="text-sm font-bold text-[#043915] bg-[#043915]/8 px-3 py-2 rounded-xl mb-5 break-all" id="modal-filename">—</p>
        <p class="text-xs text-red-500 font-semibold mb-6">All existing data will be permanently replaced. Are you absolutely sure?</p>

        <div class="flex gap-3">
            <button id="cancel-restore-btn"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm py-3 rounded-xl transition-colors">
                Cancel
            </button>
            <button id="confirm-restore-btn"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold text-sm py-3 rounded-xl transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-rotate-left"></i> Yes, Restore
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const visibleInput  = document.getElementById('visible-file-input');
    const hiddenInput   = document.getElementById('hidden-file-input');
    const dropZone      = document.getElementById('drop-zone');
    const fileDisplay   = document.getElementById('file-name-display');
    const openBtn       = document.getElementById('open-confirm-btn');
    const modal         = document.getElementById('confirm-modal');
    const modalBox      = document.getElementById('modal-box');
    const cancelBtn     = document.getElementById('cancel-restore-btn');
    const confirmBtn    = document.getElementById('confirm-restore-btn');
    const modalFilename = document.getElementById('modal-filename');
    const restoreForm   = document.getElementById('restore-form');

    let selectedFile = null;

    function openModal() {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modalBox.classList.remove('scale-95');
        modalBox.classList.add('scale-100');
    }
    function closeModal() {
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalBox.classList.remove('scale-100');
        modalBox.classList.add('scale-95');
    }

    function setFile(file) {
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.sql')) {
            alert('Only .sql files are accepted.');
            return;
        }
        selectedFile = file;
        fileDisplay.textContent = '📄 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        fileDisplay.classList.remove('text-gray-400');
        fileDisplay.classList.add('text-[#043915]', 'font-semibold', 'not-italic');
        openBtn.disabled = false;
    }

    visibleInput.addEventListener('change', function () {
        setFile(this.files[0] || null);
    });

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.classList.add('border-[#043915]', 'bg-[#043915]/5');
    });
    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('border-[#043915]', 'bg-[#043915]/5');
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('border-[#043915]', 'bg-[#043915]/5');
        setFile(e.dataTransfer.files[0] || null);
    });

    openBtn.addEventListener('click', function () {
        if (!selectedFile) return;
        modalFilename.textContent = selectedFile.name;
        openModal();
    });

    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    confirmBtn.addEventListener('click', function () {
        if (!selectedFile) return;
        const dt = new DataTransfer();
        dt.items.add(selectedFile);
        hiddenInput.files = dt.files;
        closeModal();
        restoreForm.submit();
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>