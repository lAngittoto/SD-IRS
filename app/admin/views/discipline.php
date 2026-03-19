<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[300] flex flex-col gap-2 pointer-events-none"></div>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-gavel text-[#f8c922] text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900">Disciplinary Actions</h1>
                    <p class="text-sm text-gray-600 mt-1">Configure violations, sanctions, and severity levels</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button onclick="openAddViolationPanel()" class="inline-flex items-center gap-2 bg-[#043915] hover:bg-[#032a0f] text-white px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-file-circle-plus"></i> Add Violation
                </button>
                <button onclick="openManagePopup('sanction')" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-gavel text-green-600"></i> Manage Sanctions
                </button>
                <button onclick="openManagePopup('severity')" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-layer-group text-purple-600"></i> Manage Severity
                </button>
            </div>
        </div>
    </div>

    <!-- Add / Edit Violation Panel -->
    <div id="violationFormPanel" class="mb-6 bg-white rounded-2xl shadow-sm p-6 hidden hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-circle-plus text-[#043915]"></i>
                </div>
                <h2 class="text-base font-bold text-gray-900" id="formPanelTitle">Add New Violation</h2>
            </div>
            <button onclick="closeAddViolationPanel()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors text-xs font-bold">
                <i class="fas fa-times"></i> Close
            </button>
        </div>

        <!-- Form error banner -->
        <div id="form-error-banner" class="hidden mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            <i class="fas fa-circle-exclamation text-red-500 shrink-0"></i>
            <span id="form-error-text">Something went wrong.</span>
        </div>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5" id="discipline-form">
            <input type="hidden" name="ajax_action" value="save">
            <input type="hidden" name="id_discipline" id="edit-discipline-id" value="">

            <!-- Column 1 -->
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">
                        <i class="fas fa-triangle-exclamation mr-1 text-yellow-600"></i> Violation Name
                    </label>
                    <input type="text" name="violation_name" id="violation_name" required
                        placeholder="e.g. Unauthorized Absence"
                        class="w-full px-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all border-0">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">
                        <i class="fas fa-gavel mr-1 text-green-600"></i> Default Sanction
                    </label>
                    <div class="flex items-center gap-2">
                        <select name="id_sanctions" id="sanction-select" required
                            class="flex-1 px-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                            <option value="">Select sanction…</option>
                            <?php foreach($sanctions as $s): ?>
                                <option value="<?= htmlspecialchars($s['id_sanctions']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="openManagePopup('sanction')"
                            class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition shrink-0 text-xs font-bold whitespace-nowrap">
                            <i class="fas fa-list"></i> Manage
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">
                        <i class="fas fa-layer-group mr-1 text-purple-600"></i> Severity Level
                    </label>
                    <div class="flex items-center gap-2">
                        <select name="id_warning" id="severity-select" required
                            class="flex-1 px-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                            <option value="">Select severity…</option>
                            <?php foreach($warnings as $w): ?>
                                <option value="<?= htmlspecialchars($w['id_warning']) ?>"><?= htmlspecialchars($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="openManagePopup('severity')"
                            class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition shrink-0 text-xs font-bold whitespace-nowrap">
                            <i class="fas fa-list"></i> Manage
                        </button>
                    </div>
                </div>
            </div>

            <!-- Column 2 -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">
                    <i class="fas fa-file-alt mr-1 text-indigo-600"></i> Description
                </label>
                <textarea name="description" id="description" rows="7"
                    placeholder="Provide detailed notes about this violation…"
                    class="w-full px-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all resize-none h-full"></textarea>
            </div>

            <!-- Buttons -->
            <div class="md:col-span-2 flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="resetForm()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-[#043915] bg-[#f8c922] hover:bg-[#e6b70f] transition shadow-md">
                    <i class="fas fa-floppy-disk"></i> <span id="submit-btn-text">Save</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col" style="height:600px;">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3 shrink-0 bg-gradient-to-r from-gray-50 to-white">
            <p class="text-sm font-bold text-gray-700">
                <?= $totalRecords ?> violation<?= $totalRecords !== 1 ? 's' : '' ?> found
            </p>
        </div>
        <div class="overflow-x-auto overflow-y-auto flex-1">
            <table class="w-full text-sm" id="mainTable">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white">
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Violation</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Sanction</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Severity</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Description</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Date Created</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-gray-100">
                    <?php if (!empty($disciplines)): foreach ($disciplines as $d): ?>
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
                        <td class="py-3 px-6 max-w-xs">
                            <span class="text-sm text-gray-500 truncate block" title="<?= htmlspecialchars($d['description'] ?? '') ?>">
                                <?= htmlspecialchars(substr($d['description'] ?? '', 0, 50)) ?><?= strlen($d['description'] ?? '') > 50 ? '…' : '' ?>
                            </span>
                        </td>
                        <td class="py-3 px-6 whitespace-nowrap">
                            <span class="text-xs text-gray-400"><?= isset($d['date_created']) ? date('M d, Y', strtotime($d['date_created'])) : 'N/A' ?></span>
                        </td>
                        <td class="py-3 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" onclick='editDiscipline(this)'
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-xs font-bold">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" onclick='deleteDiscipline(this)'
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-xs font-bold">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" class="py-20 text-center">
                            <i class="fas fa-gavel text-gray-200 text-5xl mb-4 block"></i>
                            <p class="text-sm text-gray-400 font-medium">No violations found.</p>
                            <button onclick="openAddViolationPanel()" class="mt-4 inline-flex items-center gap-2 text-[#043915] text-sm font-bold hover:underline">
                                <i class="fas fa-plus"></i> Add your first violation
                            </button>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ============================================================ -->
<!-- MANAGE SANCTIONS MODAL -->
<!-- ============================================================ -->
<div id="popup-manage-sanction" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-gavel text-green-700 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Manage Sanctions</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Add or remove sanction options</p>
                </div>
            </div>
            <button onclick="closeManagePopup('sanction')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors text-xs font-bold">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        <!-- Error banner -->
        <div id="sanction-error-banner" class="hidden mx-8 mt-5 shrink-0 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            <i class="fas fa-circle-exclamation text-red-500 shrink-0"></i>
            <span id="sanction-error-text">Something went wrong.</span>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <div class="flex gap-2 mb-5">
                <input type="text" id="new-sanction-name"
                    class="flex-1 px-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all"
                    placeholder="Enter sanction name…">
                <button type="button" onclick="addNewOption('sanction')"
                    class="inline-flex items-center gap-2 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] px-5 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
            <div id="sanction-list" class="space-y-2"></div>
        </div>
        <div class="px-8 py-4 border-t border-gray-200 shrink-0 bg-gray-50 flex justify-end">
            <button onclick="closeManagePopup('sanction')" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#043915] hover:bg-[#032a0f] text-white font-bold rounded-lg text-sm transition-colors">
                <i class="fas fa-check"></i> Done
            </button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MANAGE SEVERITY MODAL -->
<!-- ============================================================ -->
<div id="popup-manage-severity" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-layer-group text-purple-700 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Manage Severity Levels</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Add or remove severity options</p>
                </div>
            </div>
            <button onclick="closeManagePopup('severity')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors text-xs font-bold">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        <!-- Error banner -->
        <div id="severity-error-banner" class="hidden mx-8 mt-5 shrink-0 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            <i class="fas fa-circle-exclamation text-red-500 shrink-0"></i>
            <span id="severity-error-text">Something went wrong.</span>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <div class="flex gap-2 mb-5">
                <input type="text" id="new-severity-name"
                    class="flex-1 px-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all"
                    placeholder="Enter severity level…">
                <button type="button" onclick="addNewOption('severity')"
                    class="inline-flex items-center gap-2 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] px-5 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
            <div id="severity-list" class="space-y-2"></div>
        </div>
        <div class="px-8 py-4 border-t border-gray-200 shrink-0 bg-gray-50 flex justify-end">
            <button onclick="closeManagePopup('severity')" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#043915] hover:bg-[#032a0f] text-white font-bold rounded-lg text-sm transition-colors">
                <i class="fas fa-check"></i> Done
            </button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- EDIT OPTION MODAL -->
<!-- ============================================================ -->
<div id="popup-edit-option" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[150] backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
        <!-- Header -->
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Edit <span id="edit-option-type-text">Item</span></h2>
                <p class="text-sm text-gray-500 mt-1">Update the name below</p>
            </div>
            <button onclick="closeEditPopup()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors text-xs font-bold">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        <!-- Error banner -->
        <div id="edit-option-error-banner" class="hidden mx-8 mt-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            <i class="fas fa-circle-exclamation text-red-500 shrink-0"></i>
            <span id="edit-option-error-text">Something went wrong.</span>
        </div>
        <div class="p-8 space-y-5">
            <input type="hidden" id="edit-option-id">
            <input type="hidden" id="edit-option-type">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">Name</label>
                <input type="text" id="edit-option-name"
                    class="w-full px-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all"
                    placeholder="Enter name…">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditPopup()"
                    class="inline-flex items-center justify-center gap-2 flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-lg transition-colors text-sm">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" onclick="saveEditOption()"
                    class="inline-flex items-center justify-center gap-2 flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-2.5 rounded-lg transition-colors text-sm">
                    <i class="fas fa-floppy-disk"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- DELETE VIOLATION CONFIRMATION MODAL -->
<!-- ============================================================ -->
<div id="delete-confirm-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[150] backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
        <!-- Error banner -->
        <div id="delete-confirm-error-banner" class="hidden mx-8 mt-8 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            <i class="fas fa-circle-exclamation text-red-500 shrink-0"></i>
            <span id="delete-confirm-error-text">Something went wrong.</span>
        </div>
        <div class="p-8 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-3">Delete Violation</h2>
            <p class="text-sm text-gray-600 mb-7">Are you sure you want to delete this violation? This action cannot be undone.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteConfirm()"
                    class="inline-flex items-center justify-center gap-2 flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-lg transition-colors text-sm">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" onclick="confirmDelete()"
                    class="inline-flex items-center justify-center gap-2 flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition-colors text-sm">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- DELETE OPTION (SANCTION/SEVERITY) CONFIRMATION MODAL -->
<!-- ============================================================ -->
<div id="delete-option-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[200] backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
        <!-- Error banner -->
        <div id="delete-option-error-banner" class="hidden mx-8 mt-8 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            <i class="fas fa-circle-exclamation text-red-500 shrink-0"></i>
            <span id="delete-option-error-text-inner">Something went wrong.</span>
        </div>
        <div class="p-8 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-3">Confirm Delete</h2>
            <p class="text-sm text-gray-600 mb-7" id="delete-option-message">Are you sure?</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteOptionModal()"
                    class="inline-flex items-center justify-center gap-2 flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-lg transition-colors text-sm">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" onclick="confirmDeleteOption()"
                    class="inline-flex items-center justify-center gap-2 flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition-colors text-sm">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const PAGE_DATA = {
    currentPage: <?= $page ?>,
    totalPages: <?= $totalPages ?>,
    totalRecords: <?= $totalRecords ?>
};

function openAddViolationPanel() {
    const panel = document.getElementById('violationFormPanel');
    panel.classList.remove('hidden');
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function closeAddViolationPanel() {
    hideBanner('form-error-banner');
    document.getElementById('violationFormPanel').classList.add('hidden');
}

/* ── Banner helpers ── */
function showBanner(bannerId, textId, msg) {
    const banner = document.getElementById(bannerId);
    const span   = document.getElementById(textId);
    if (!banner || !span) return;
    span.textContent = msg;
    banner.classList.remove('hidden');
    banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
function hideBanner(bannerId) {
    const el = document.getElementById(bannerId);
    if (el) el.classList.add('hidden');
}

/* Map each modal to its error banner */
const MODAL_BANNER_MAP = [
    { modal: 'popup-manage-sanction', banner: 'sanction-error-banner',      text: 'sanction-error-text' },
    { modal: 'popup-manage-severity', banner: 'severity-error-banner',      text: 'severity-error-text' },
    { modal: 'popup-edit-option',     banner: 'edit-option-error-banner',   text: 'edit-option-error-text' },
    { modal: 'delete-confirm-modal',  banner: 'delete-confirm-error-banner',text: 'delete-confirm-error-text' },
    { modal: 'delete-option-modal',   banner: 'delete-option-error-banner', text: 'delete-option-error-text-inner' },
    { modal: 'violationFormPanel',    banner: 'form-error-banner',          text: 'form-error-text' },
];

/* Show error in whichever modal is currently visible */
function showErrorInActiveModal(msg) {
    for (const entry of MODAL_BANNER_MAP) {
        const el = document.getElementById(entry.modal);
        if (el && !el.classList.contains('hidden')) {
            showBanner(entry.banner, entry.text, msg);
            return;
        }
    }
}
</script>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/discipline-pop-up.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── renderOptionList — icon + text buttons ── */
    window.renderOptionList = function (type, items) {
        const listEl = document.getElementById(type === 'sanction' ? 'sanction-list' : 'severity-list');
        if (!listEl) return;
        if (!items || items.length === 0) {
            listEl.innerHTML = '<p class="text-sm text-gray-400 text-center py-6">No items yet.</p>';
            return;
        }
        listEl.innerHTML = items.map(item => `
            <div class="flex items-center justify-between gap-3 px-4 py-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                <span class="text-sm font-semibold text-gray-800">${item.name}</span>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="openEditPopup('${type}', ${item.id}, '${item.name.replace(/'/g, "\\'")}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-xs font-bold">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button type="button" onclick="openDeleteOptionModal('${type}', ${item.id}, '${item.name.replace(/'/g, "\\'")}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-xs font-bold">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `).join('');
    };

    /* ── Intercept alert() so errors appear in banners instead ── */
    const nativeAlert = window.alert;
    window.alert = function (msg) {
        const shown = showErrorInActiveModal(msg);
        if (shown === undefined) {
            /* showErrorInActiveModal always runs; if no modal found, fallback */
        }
    };

    /* ── Clear banners when modals close ── */
    const patchClose = (fnName, bannerIds) => {
        const orig = window[fnName];
        if (typeof orig !== 'function') return;
        window[fnName] = function (...args) {
            bannerIds.forEach(id => hideBanner(id));
            return orig.apply(this, args);
        };
    };

    patchClose('closeManagePopup',      ['sanction-error-banner', 'severity-error-banner']);
    patchClose('closeEditPopup',        ['edit-option-error-banner']);
    patchClose('closeDeleteConfirm',    ['delete-confirm-error-banner']);
    patchClose('closeDeleteOptionModal',['delete-option-error-banner']);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>