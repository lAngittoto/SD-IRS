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

        <form action="/student-discipline-and-incident-reporting-system/public/discipline-records?action=save" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8" id="discipline-form">
            <input type="hidden" name="id_discipline" id="edit-discipline-id">

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
                    <?php if(!empty($disciplines)): foreach($disciplines as $d): 
                        $id = isset($d['id_discipline']) ? $d['id_discipline'] : 0;
                        $violation = isset($d['violation_name']) ? htmlspecialchars($d['violation_name']) : 'N/A';
                        $sanction = isset($d['sanction']) ? htmlspecialchars($d['sanction']) : 'N/A';
                        $severity = isset($d['severity']) ? htmlspecialchars($d['severity']) : 'N/A';
                        $desc = isset($d['description']) ? htmlspecialchars($d['description']) : 'N/A';
                        $date = isset($d['date_created']) ? date('M d, Y', strtotime($d['date_created'])) : 'N/A';
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors" data-id="<?php echo $id; ?>">
                        <td class="px-6 py-4 text-sm font-bold text-gray-700"><?php echo $violation; ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo $sanction; ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-purple-100 text-purple-700 uppercase"><?php echo $severity; ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?php echo $desc; ?>"><?php echo $desc; ?></td>
                        <td class="px-6 py-4 text-right text-xs text-gray-400 font-mono"><?php echo $date; ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" onclick="editDiscipline(<?php echo $id; ?>)" class="w-9 h-9 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition flex items-center justify-center" title="Edit">
                                    <i class="fa-solid fa-edit text-sm"></i>
                                </button>
                                <button type="button" onclick="showDeleteConfirm(<?php echo $id; ?>)" class="w-9 h-9 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex items-center justify-center" title="Delete">
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
                <div class="text-sm text-gray-600">
                    Showing page <span class="font-bold text-[#043915]" id="current-page"><?php echo $page; ?></span> of 
                    <span class="font-bold text-[#043915]" id="total-pages"><?php echo $totalPages; ?></span> 
                    (<span class="font-bold text-[#043915]" id="total-records"><?php echo $totalRecords; ?></span> total records)
                </div>
                <div id="pagination-buttons" class="flex items-center gap-2"></div>
            </div>
        </div>
    </section>

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

    <!-- Custom Delete Confirmation Modal -->
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

</main>

<script>
let currentPage = <?php echo $page; ?>;
let totalPages = <?php echo $totalPages; ?>;
let totalRecords = <?php echo $totalRecords; ?>;
let deleteTargetId = null;

// Get base URL for AJAX requests
function getBaseUrl() {
    return window.location.pathname.split('?')[0];
}

// Initialize pagination on page load
function initPagination() {
    renderPagination();
}

// Render pagination buttons
function renderPagination() {
    const container = document.getElementById('pagination-buttons');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.onclick = () => loadPage(currentPage - 1);
        prevBtn.className = 'w-10 h-10 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition flex items-center justify-center';
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-sm"></i>';
        container.appendChild(prevBtn);
    }
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    // First page + ellipsis
    if (startPage > 1) {
        addPageButton(1);
        if (startPage > 2) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'px-2 text-gray-400 flex items-center';
            ellipsis.textContent = '...';
            container.appendChild(ellipsis);
        }
    }
    
    // Page numbers range
    for (let i = startPage; i <= endPage; i++) {
        addPageButton(i);
    }
    
    // Ellipsis + last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'px-2 text-gray-400 flex items-center';
            ellipsis.textContent = '...';
            container.appendChild(ellipsis);
        }
        addPageButton(totalPages);
    }
    
    // Next button
    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.onclick = () => loadPage(currentPage + 1);
        nextBtn.className = 'w-10 h-10 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition flex items-center justify-center';
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right text-sm"></i>';
        container.appendChild(nextBtn);
    }
}

// Add individual page button
function addPageButton(pageNum) {
    const container = document.getElementById('pagination-buttons');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.onclick = () => loadPage(pageNum);
    btn.className = pageNum === currentPage 
        ? 'w-10 h-10 rounded-lg bg-[#043915] text-white font-bold shadow-md flex items-center justify-center'
        : 'w-10 h-10 rounded-lg bg-[#f8c922] text-[#043915] font-bold hover:bg-yellow-300 transition shadow-sm flex items-center justify-center';
    btn.textContent = pageNum;
    container.appendChild(btn);
}

// Load page via AJAX
function loadPage(page) {
    const baseUrl = getBaseUrl();
    const url = baseUrl + '?action=get-data&page=' + page;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    updateTable(data.disciplines);
                    currentPage = data.page;
                    totalPages = data.totalPages;
                    totalRecords = data.totalRecords;
                    
                    document.getElementById('current-page').textContent = currentPage;
                    document.getElementById('total-pages').textContent = totalPages;
                    document.getElementById('total-records').textContent = totalRecords;
                    
                    renderPagination();
                } else {
                    showMessage(data.message || 'Failed to load page', 'error');
                }
            } catch (e) {
                console.error('JSON Parse Error:', e);
                console.error('Response text:', text);
                showMessage('Error loading page. Please refresh.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Failed to load page', 'error');
        });
}

// Update table with new data
function updateTable(disciplines) {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '';
    
    if (!disciplines || disciplines.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-gray-400 italic text-sm">No records found.</td></tr>';
        return;
    }
    
    disciplines.forEach(d => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 transition-colors';
        row.setAttribute('data-id', d.id_discipline);
        row.innerHTML = `
            <td class="px-6 py-4 text-sm font-bold text-gray-700">${escapeHtml(d.violation_name)}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(d.sanction)}</td>
            <td class="px-6 py-4"><span class="px-3 py-1 text-[10px] font-bold rounded-full bg-purple-100 text-purple-700 uppercase">${escapeHtml(d.severity)}</span></td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="${escapeHtml(d.description || '')}">${escapeHtml(d.description || 'N/A')}</td>
            <td class="px-6 py-4 text-right text-xs text-gray-400 font-mono">${formatDate(d.date_created)}</td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                    <button type="button" onclick="editDiscipline(${d.id_discipline})" class="w-9 h-9 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition flex items-center justify-center" title="Edit">
                        <i class="fa-solid fa-edit text-sm"></i>
                    </button>
                    <button type="button" onclick="showDeleteConfirm(${d.id_discipline})" class="w-9 h-9 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex items-center justify-center" title="Delete">
                        <i class="fa-solid fa-trash text-sm"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Edit discipline record - FIXED
function editDiscipline(id) {
    if (!id || id === 'undefined' || id === 0) {
        showMessage('Invalid record ID', 'error');
        return;
    }
    
    const baseUrl = getBaseUrl();
    const url = baseUrl + '?action=get-discipline&id=' + id;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.status === 'success' && data.discipline) {
                    // Populate form fields
                    document.getElementById('edit-discipline-id').value = data.discipline.id_discipline || '';
                    document.getElementById('violation_name').value = data.discipline.violation_name || '';
                    document.getElementById('sanction-select').value = data.discipline.id_sanctions || '';
                    document.getElementById('severity-select').value = data.discipline.id_warning || '';
                    document.getElementById('description').value = data.discipline.description || '';
                    document.getElementById('submit-btn-text').textContent = 'Update Configuration';
                    
                    // Scroll to top smoothly
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    // Add visual feedback
                    const form = document.getElementById('discipline-form');
                    form.classList.add('ring-2', 'ring-blue-500');
                    setTimeout(() => {
                        form.classList.remove('ring-2', 'ring-blue-500');
                    }, 2000);
                    
                    showMessage('Record loaded for editing', 'success');
                } else {
                    showMessage(data.message || 'Failed to load record', 'error');
                }
            } catch (e) {
                console.error('JSON Parse Error:', e);
                console.error('Response text:', text);
                showMessage('Error loading record', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Failed to load record', 'error');
        });
}

// Show delete confirmation modal - FIXED
function showDeleteConfirm(id) {
    if (!id || id === 'undefined' || id === 0) {
        showMessage('Invalid record ID', 'error');
        return;
    }
    
    deleteTargetId = id;
    const modal = document.getElementById('delete-confirm-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Close delete confirmation modal
function closeDeleteConfirm() {
    deleteTargetId = null;
    const modal = document.getElementById('delete-confirm-modal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// Confirm delete action - FIXED
function confirmDelete() {
    if (!deleteTargetId) {
        closeDeleteConfirm();
        return;
    }
    
    const id = deleteTargetId;
    closeDeleteConfirm();
    
    const baseUrl = getBaseUrl();
    const url = baseUrl + '?action=delete-discipline';
    
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                showMessage('Deleted successfully!', 'success');
                // Reload current page
                loadPage(currentPage);
            } else {
                showMessage(data.message || 'Failed to delete', 'error');
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text);
            showMessage('Error deleting record', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
}

// Reset form
function resetForm() {
    document.getElementById('discipline-form').reset();
    document.getElementById('edit-discipline-id').value = '';
    document.getElementById('submit-btn-text').textContent = 'Save Configuration';
}

// Manage popup functions
function openManagePopup(type) {
    document.getElementById('popup-manage-' + type).classList.remove('hidden');
    document.getElementById('popup-manage-' + type).classList.add('flex');
    loadOptionsList(type);
}

function closeManagePopup(type) {
    document.getElementById('popup-manage-' + type).classList.remove('flex');
    document.getElementById('popup-manage-' + type).classList.add('hidden');
    document.getElementById('new-' + type + '-name').value = '';
    reloadDropdowns();
}

function loadOptionsList(type) {
    const baseUrl = getBaseUrl();
    const url = baseUrl + '?action=get-options&type=' + type;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderOptionsList(type, data.options);
            }
        })
        .catch(error => console.error('Error:', error));
}

function renderOptionsList(type, options) {
    const container = document.getElementById(type + '-list');
    container.innerHTML = '';
    
    if (options.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-400 py-4">No items found</p>';
        return;
    }
    
    options.forEach(option => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition';
        div.innerHTML = `
            <span class="font-medium text-gray-700">${escapeHtml(option.name)}</span>
            <div class="flex gap-2">
                <button type="button" onclick="openEditPopup('${type}', ${option.id}, '${escapeHtml(option.name).replace(/'/g, "\\'")}', event)" class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition flex items-center justify-center">
                    <i class="fa-solid fa-edit text-sm"></i>
                </button>
                <button type="button" onclick="deleteOption('${type}', ${option.id}, event)" class="w-8 h-8 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex items-center justify-center">
                    <i class="fa-solid fa-trash text-sm"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });
}

function addNewOption(type) {
    const name = document.getElementById('new-' + type + '-name').value.trim();
    
    if (!name) {
        showMessage('Please enter a name', 'error');
        return;
    }
    
    const baseUrl = getBaseUrl();
    const url = baseUrl + '?action=manage-options';
    
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `type=${type}&action=add&name=${encodeURIComponent(name)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('new-' + type + '-name').value = '';
            loadOptionsList(type);
            showMessage('Added successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to add', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
}

function openEditPopup(type, id, name, event) {
    if (event) event.stopPropagation();
    
    document.getElementById('edit-option-id').value = id;
    document.getElementById('edit-option-type').value = type;
    document.getElementById('edit-option-name').value = name;
    document.getElementById('edit-option-type-text').textContent = type === 'sanction' ? 'Sanction' : 'Severity Level';
    document.getElementById('popup-edit-option').classList.remove('hidden');
    document.getElementById('popup-edit-option').classList.add('flex');
}

function closeEditPopup() {
    document.getElementById('popup-edit-option').classList.remove('flex');
    document.getElementById('popup-edit-option').classList.add('hidden');
}

function saveEditOption() {
    const id = document.getElementById('edit-option-id').value;
    const type = document.getElementById('edit-option-type').value;
    const name = document.getElementById('edit-option-name').value.trim();
    
    if (!name) {
        showMessage('Please enter a name', 'error');
        return;
    }
    
    const baseUrl = getBaseUrl();
    const url = baseUrl + '?action=manage-options';
    
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `type=${type}&action=edit&id=${id}&name=${encodeURIComponent(name)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeEditPopup();
            loadOptionsList(type);
            showMessage('Updated successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to update', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
}

function deleteOption(type, id, event) {
    if (event) event.stopPropagation();
    
    if (!confirm('Are you sure you want to delete this item?')) return;
    
    const baseUrl = getBaseUrl();
    const url = baseUrl + '?action=manage-options';
    
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `type=${type}&action=delete&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadOptionsList(type);
            showMessage('Deleted successfully!', 'success');
        } else {
            showMessage(data.message || 'Cannot delete. Item is being used.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
}

function reloadDropdowns() {
    const baseUrl = getBaseUrl();
    const url = baseUrl + '?action=get-dropdowns';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateDropdown('sanction-select', data.sanctions, 'id_sanctions');
                updateDropdown('severity-select', data.warnings, 'id_warning');
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateDropdown(selectId, options, idField) {
    const select = document.getElementById(selectId);
    const currentValue = select.value;
    
    select.innerHTML = '<option value="">Select ' + (selectId.includes('sanction') ? 'default sanction' : 'Severity Level') + '</option>';
    
    options.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option[idField];
        opt.textContent = option.name;
        select.appendChild(opt);
    });
    
    select.value = currentValue;
}

// Utility functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function showMessage(message, type) {
    const div = document.createElement('div');
    div.className = `fixed top-4 right-4 z-[90] p-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'} animate-fade-in-down`;
    div.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span class="font-semibold">${escapeHtml(message)}</span>
        </div>
    `;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}

// Form submit handler - FIXED
document.getElementById('discipline-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                showMessage(data.message, 'success');
                resetForm();
                loadPage(1); // Reload first page after save
            } else {
                showMessage(data.message || 'Failed to save', 'error');
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text);
            showMessage('Error saving record', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
});

// Auto-hide flash messages
setTimeout(() => {
    const msg = document.getElementById('status-message');
    if (msg) {
        msg.style.transition = 'opacity 0.5s';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 500);
    }
}, 3000);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initPagination();
});
</script>

<style>
@keyframes fade-in-down {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-down {
    animation: fade-in-down 0.3s ease-out;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>