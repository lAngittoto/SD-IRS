<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gray-100 p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>

    <?php if(isset($_SESSION['flash_message'])): ?>
        <div id="status-message" class="mb-6 p-4 rounded-xl <?= $_SESSION['flash_message']['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
            <div class="flex items-center gap-3">
                <i class="fa-solid <?= $_SESSION['flash_message']['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <span class="font-semibold"><?= htmlspecialchars($_SESSION['flash_message']['text']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <section class="mb-8 text-center md:text-left">
        <h1 class="text-2xl font-bold text-[#043915] flex items-center justify-center md:justify-start gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-scale-balanced text-green-600 text-lg"></i>
            </div>
            Add Disciplinary Action
        </h1>
    </section>

    <section class="w-full bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-10 relative mb-10">
        <h2 class="text-lg font-semibold text-[#043915] mb-8 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-file-circle-plus text-blue-600 text-lg"></i>
            </div>
            Disciplinary Action Configuration
        </h2>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8" id="discipline-form">
            <input type="hidden" name="ajax_action" value="save">
            <input type="hidden" name="id_discipline" id="edit-discipline-id" value="">

            <div class="space-y-6">
                <div class="relative">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Violation Name</label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-12 bg-yellow-100 rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                            <i class="fa-solid fa-triangle-exclamation text-yellow-600 text-lg"></i>
                        </div>
                        <input type="text" name="violation_name" id="violation_name" required placeholder="e.g. Unauthorized Absence" class="w-full pl-16 pr-4 py-3.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915] bg-gray-50">
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Default Sanction</label>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full">
                            <div class="absolute left-0 top-0 bottom-0 w-12 bg-green-100 rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                                <i class="fa-solid fa-gavel text-green-600 text-lg"></i>
                            </div>
                            <select name="id_sanctions" id="sanction-select" required class="w-full pl-16 pr-10 py-3.5 rounded-xl border border-gray-300 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#043915]">
                                <option value="">Select default sanction</option>
                                <?php foreach($sanctions as $s): ?>
                                    <option value="<?= htmlspecialchars($s['id_sanctions']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="shrink-0 w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-100 transition border border-blue-200" onclick="openManagePopup('sanction')">
                            <i class="fa-solid fa-list"></i>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Severity Level</label>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full">
                            <div class="absolute left-0 top-0 bottom-0 w-12 bg-purple-100 rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                                <i class="fa-solid fa-layer-group text-purple-600 text-lg"></i>
                            </div>
                            <select name="id_warning" id="severity-select" required class="w-full pl-16 pr-10 py-3.5 rounded-xl border border-gray-300 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#043915]">
                                <option value="">Select Severity Level</option>
                                <?php foreach($warnings as $w): ?>
                                    <option value="<?= htmlspecialchars($w['id_warning']) ?>"><?= htmlspecialchars($w['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="shrink-0 w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center hover:bg-purple-100 transition border border-purple-200" onclick="openManagePopup('severity')">
                            <i class="fa-solid fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Description / Notes</label>
                    <textarea name="description" id="description" rows="10" placeholder="Provide any notes..." class="w-full p-4 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#043915] bg-gray-50 resize-none min-h-[150px]"></textarea>
                </div>
            </div>

            <div class="md:col-span-2 flex justify-end gap-3 pt-6 border-t border-gray-100">
                <button type="button" onclick="resetForm()" class="px-8 py-3.5 rounded-xl text-sm font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 transition shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-times"></i> Cancel
                </button>
                <button type="submit" class="px-8 py-3.5 rounded-xl text-sm font-bold text-[#043915] bg-[#f8c922] hover:bg-yellow-300 transition shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> <span id="submit-btn-text">Save Configuration</span>
                </button>
            </div>
        </form>
    </section>

    <section class="w-full bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead class="bg-[#043915]">
                    <tr class="text-white text-[11px] uppercase tracking-widest">
                        <th class="px-6 py-4">Violation Name</th>
                        <th class="px-6 py-4">Sanction</th>
                        <th class="px-6 py-4">Severity</th>
                        <th class="px-6 py-4">Description</th>
                        <th class="px-6 py-4 text-right">Date Created</th>
                        <th class="px-6 py-4 text-center w-32">Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-gray-100">
                    <?php if(!empty($disciplines)): foreach($disciplines as $d): ?>
                    <tr class="hover:bg-gray-50 transition-colors discipline-row" 
                        data-id="<?= isset($d['discipline_id']) ? intval($d['discipline_id']) : 0 ?>"
                        data-violation="<?= isset($d['violation_name']) ? htmlspecialchars($d['violation_name'], ENT_QUOTES, 'UTF-8') : '' ?>"
                        data-sanction-id="<?= isset($d['id_sanctions']) ? intval($d['id_sanctions']) : 0 ?>"
                        data-warning-id="<?= isset($d['id_warning']) ? intval($d['id_warning']) : 0 ?>"
                        data-description="<?= isset($d['description']) ? htmlspecialchars($d['description'], ENT_QUOTES, 'UTF-8') : '' ?>">
                        <td class="px-6 py-4 text-sm font-bold text-gray-700"><?= htmlspecialchars($d['violation_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($d['sanction'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-purple-100 text-purple-700 uppercase"><?= htmlspecialchars($d['severity'] ?? 'N/A') ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($d['description'] ?? '') ?>"><?= htmlspecialchars($d['description'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 text-right text-xs text-gray-400 font-mono"><?= isset($d['date_created']) ? date('M d, Y', strtotime($d['date_created'])) : 'N/A' ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" onclick='editDiscipline(this)' class="w-9 h-9 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition flex items-center justify-center" title="Edit">
                                    <i class="fa-solid fa-edit text-sm"></i>
                                </button>
                                <button type="button" onclick='deleteDiscipline(this)' class="w-9 h-9 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex items-center justify-center" title="Delete">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center py-10 text-gray-400 italic text-sm">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 font-semibold">
                    Showing Results
                </div>
                <div id="pagination-buttons" class="flex items-center gap-2"></div>
            </div>
        </div>
    </section>

    <!-- Manage Sanction Popup -->
    <div id="popup-manage-sanction" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-[#043915] text-lg">Manage Sanctions</h3>
                <button type="button" onclick="closeManagePopup('sanction')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                <div class="flex gap-2">
                    <input type="text" id="new-sanction-name" class="flex-1 border border-gray-300 p-3 rounded-xl text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter new sanction name">
                    <button type="button" onclick="addNewOption('sanction')" class="bg-[#f8c922] text-[#043915] px-6 py-2 rounded-xl text-sm font-bold hover:bg-yellow-300 transition"><i class="fa-solid fa-plus mr-1"></i> Add</button>
                </div>
            </div>
            <div id="sanction-list" class="space-y-2"></div>
        </div>
    </div>

    <!-- Manage Severity Popup -->
    <div id="popup-manage-severity" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-[#043915] text-lg">Manage Severity Levels</h3>
                <button type="button" onclick="closeManagePopup('severity')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                <div class="flex gap-2">
                    <input type="text" id="new-severity-name" class="flex-1 border border-gray-300 p-3 rounded-xl text-sm bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none" placeholder="Enter new severity level">
                    <button type="button" onclick="addNewOption('severity')" class="bg-[#f8c922] text-[#043915] px-6 py-2 rounded-xl text-sm font-bold hover:bg-yellow-300 transition"><i class="fa-solid fa-plus mr-1"></i> Add</button>
                </div>
            </div>
            <div id="severity-list" class="space-y-2"></div>
        </div>
    </div>

    <!-- Edit Option Popup -->
    <div id="popup-edit-option" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[70] p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <h3 class="font-bold text-[#043915] mb-4 text-lg">Edit <span id="edit-option-type-text">Item</span></h3>
            <input type="hidden" id="edit-option-id">
            <input type="hidden" id="edit-option-type">
            <input type="text" id="edit-option-name" class="w-full border border-gray-300 p-3 rounded-xl text-sm bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter name">
            <div class="flex justify-end mt-4 gap-2">
                <button type="button" onclick="closeEditPopup()" class="text-sm font-bold text-gray-400 px-4 py-2 hover:text-gray-600 transition">Cancel</button>
                <button type="button" onclick="saveEditOption()" class="bg-[#f8c922] text-[#043915] px-6 py-2 rounded-xl text-sm font-bold hover:bg-yellow-300 transition"><i class="fa-solid fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-confirm-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[80] p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Confirm Delete</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this discipline record?</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteConfirm()" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="button" onclick="confirmDelete()" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition">
                    <i class="fa-solid fa-trash mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Option Modal -->
    <div id="delete-option-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[80] p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Confirm Delete</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-600 mb-6" id="delete-option-message">Are you sure you want to delete this item?</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteOptionModal()" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="button" onclick="confirmDeleteOption()" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition">
                    <i class="fa-solid fa-trash mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>

</main>

<script>
// Pass PHP variables to JavaScript
const PAGE_DATA = {
    currentPage: <?= $page ?>,
    totalPages: <?= $totalPages ?>,
    totalRecords: <?= $totalRecords ?>
};
</script>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/discipline-pop-up.js"></script>
<style>
@keyframes fade-in-down {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-down {
    animation: fade-in-down 0.3s ease-out;
}

.toast-message {
    transition: opacity 0.3s ease-out, transform 0.3s ease-out;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>