<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[300] flex flex-col gap-3 pointer-events-none"></div>

    <!-- Page Header -->
    <div class="mb-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-red-100 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fas fa-gavel text-red-700 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Disciplinary Actions</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Configure violations, sanctions, and severity levels</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <section class="w-full bg-white rounded-2xl border border-gray-200 shadow-sm p-8 mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-3">
            <i class="fas fa-file-circle-plus text-blue-600 text-xl"></i>Add New Violation
        </h2>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6" id="discipline-form">
            <input type="hidden" name="ajax_action" value="save">
            <input type="hidden" name="id_discipline" id="edit-discipline-id" value="">

            <!-- Column 1 -->
            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                        <i class="fas fa-triangle-exclamation mr-2 text-yellow-600"></i>Violation Name
                    </label>
                    <input type="text" name="violation_name" id="violation_name" required placeholder="e.g. Unauthorized Absence" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 bg-gray-50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                        <i class="fas fa-gavel mr-2 text-green-600"></i>Default Sanction
                    </label>
                    <div class="flex items-center gap-2">
                        <select name="id_sanctions" id="sanction-select" required 
                            class="flex-1 px-4 py-3 rounded-xl border border-gray-300 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500">
                            <option value="">Select sanction...</option>
                            <?php foreach($sanctions as $s): ?>
                                <option value="<?= htmlspecialchars($s['id_sanctions']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-100 transition border border-blue-200 shrink-0" 
                            onclick="openManagePopup('sanction')">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                        <i class="fas fa-layer-group mr-2 text-purple-600"></i>Severity Level
                    </label>
                    <div class="flex items-center gap-2">
                        <select name="id_warning" id="severity-select" required 
                            class="flex-1 px-4 py-3 rounded-xl border border-gray-300 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500">
                            <option value="">Select severity...</option>
                            <?php foreach($warnings as $w): ?>
                                <option value="<?= htmlspecialchars($w['id_warning']) ?>"><?= htmlspecialchars($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center hover:bg-purple-100 transition border border-purple-200 shrink-0" 
                            onclick="openManagePopup('severity')">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Column 2 -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                    <i class="fas fa-file-alt mr-2 text-indigo-600"></i>Description
                </label>
                <textarea name="description" id="description" rows="7" placeholder="Provide detailed notes about this violation..." 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 bg-gray-50 resize-none"></textarea>
            </div>

            <!-- Buttons -->
            <div class="md:col-span-2 flex justify-end gap-3 pt-6 border-t border-gray-100">
                <button type="button" onclick="resetForm()" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 transition flex items-center gap-2">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-[#043915] bg-[#f8c922] hover:bg-yellow-300 transition flex items-center gap-2">
                    <i class="fas fa-floppy-disk"></i> <span id="submit-btn-text">Save</span>
                </button>
            </div>
        </form>
    </section>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col" style="height: 600px;">
        
        <!-- Table Header -->


        <!-- Table -->
        <div class="overflow-x-auto overflow-y-auto flex-1">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-[#043915] text-white">
                    <tr class="text-xs font-bold uppercase tracking-wide">
                        <th class="py-4 px-6 text-left"><i class="fas fa-triangle-exclamation mr-2"></i>Violation</th>
                        <th class="py-4 px-6 text-left"><i class="fas fa-gavel mr-2"></i>Sanction</th>
                        <th class="py-4 px-6 text-left"><i class="fas fa-layer-group mr-2"></i>Severity</th>
                        <th class="py-4 px-6 text-left"><i class="fas fa-file-alt mr-2"></i>Description</th>
                        <th class="py-4 px-6 text-left"><i class="fas fa-calendar mr-2"></i>Date Created</th>
                        <th class="py-4 px-6 text-center"><i class="fas fa-cogs mr-2"></i>Actions</th>
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
                        <td class="py-3 px-6">
                            <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($d['violation_name'] ?? 'N/A') ?></span>
                        </td>
                        <td class="py-3 px-6">
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($d['sanction'] ?? 'N/A') ?></span>
                        </td>
                        <td class="py-3 px-6">
                            <span class="px-3 py-1 text-xs font-bold rounded-lg bg-purple-100 text-purple-800"><?= htmlspecialchars($d['severity'] ?? 'N/A') ?></span>
                        </td>
                        <td class="py-3 px-6">
                            <span class="text-sm text-gray-500 truncate max-w-xs" title="<?= htmlspecialchars($d['description'] ?? '') ?>"><?= htmlspecialchars(substr($d['description'] ?? '', 0, 40)) ?></span>
                        </td>
                        <td class="py-3 px-6">
                            <span class="text-xs text-gray-400"><?= isset($d['date_created']) ? date('M d, Y', strtotime($d['date_created'])) : 'N/A' ?></span>
                        </td>
                        <td class="py-3 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" onclick='editDiscipline(this)' class="w-9 h-9 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition flex items-center justify-center" title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button type="button" onclick='deleteDiscipline(this)' class="w-9 h-9 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex items-center justify-center" title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center py-10 text-gray-400 text-sm">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manage Sanction Popup -->
    <div id="popup-manage-sanction" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[60] p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-[#043915] text-lg flex items-center gap-2">
                    <i class="fas fa-gavel text-green-600"></i>Manage Sanctions
                </h3>
                <button type="button" onclick="closeManagePopup('sanction')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="mb-6 p-4 bg-gray-50 rounded-xl flex gap-2">
                <input type="text" id="new-sanction-name" class="flex-1 border border-gray-300 p-3 rounded-xl text-sm bg-white focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Enter sanction name">
                <button type="button" onclick="addNewOption('sanction')" class="bg-[#f8c922] text-[#043915] px-6 py-2 rounded-xl text-sm font-bold hover:bg-yellow-300 transition"><i class="fas fa-plus mr-1"></i> Add</button>
            </div>
            <div id="sanction-list" class="space-y-2"></div>
        </div>
    </div>

    <!-- Manage Severity Popup -->
    <div id="popup-manage-severity" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[60] p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-2xl max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-[#043915] text-lg flex items-center gap-2">
                    <i class="fas fa-layer-group text-purple-600"></i>Manage Severity Levels
                </h3>
                <button type="button" onclick="closeManagePopup('severity')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="mb-6 p-4 bg-gray-50 rounded-xl flex gap-2">
                <input type="text" id="new-severity-name" class="flex-1 border border-gray-300 p-3 rounded-xl text-sm bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none" placeholder="Enter severity level">
                <button type="button" onclick="addNewOption('severity')" class="bg-[#f8c922] text-[#043915] px-6 py-2 rounded-xl text-sm font-bold hover:bg-yellow-300 transition"><i class="fas fa-plus mr-1"></i> Add</button>
            </div>
            <div id="severity-list" class="space-y-2"></div>
        </div>
    </div>

    <!-- Edit Option Popup -->
    <div id="popup-edit-option" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[70] p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <h3 class="font-bold text-[#043915] mb-4 text-lg">Edit <span id="edit-option-type-text">Item</span></h3>
            <input type="hidden" id="edit-option-id">
            <input type="hidden" id="edit-option-type">
            <input type="text" id="edit-option-name" class="w-full border border-gray-300 p-3 rounded-xl text-sm bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none mb-4" placeholder="Enter name">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEditPopup()" class="text-sm font-bold text-gray-600 px-4 py-2 hover:bg-gray-50 transition rounded-lg">Cancel</button>
                <button type="button" onclick="saveEditOption()" class="bg-[#f8c922] text-[#043915] px-6 py-2 rounded-xl text-sm font-bold hover:bg-yellow-300 transition"><i class="fas fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-confirm-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[80] p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Confirm Delete</h3>
                    <p class="text-xs text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-600 mb-6 text-sm">Are you sure you want to delete this record?</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteConfirm()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 transition">Cancel</button>
                <button type="button" onclick="confirmDelete()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition"><i class="fas fa-trash mr-1"></i> Delete</button>
            </div>
        </div>
    </div>

    <!-- Delete Option Modal -->
    <div id="delete-option-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[80] p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Confirm Delete</h3>
                    <p class="text-xs text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-600 mb-6 text-sm" id="delete-option-message">Are you sure?</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteOptionModal()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 transition">Cancel</button>
                <button type="button" onclick="confirmDeleteOption()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition"><i class="fas fa-trash mr-1"></i> Delete</button>
            </div>
        </div>
    </div>

</main>

<script>
const PAGE_DATA = {
    currentPage: <?= $page ?>,
    totalPages: <?= $totalPages ?>,
    totalRecords: <?= $totalRecords ?>
};
</script>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/discipline-pop-up.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>