function openPopup(id) {
        const popup = document.getElementById(id);
        popup.classList.remove('hidden');
        popup.classList.add('flex');
    }

    function closePopup(id) {
        const popup = document.getElementById(id);
        popup.classList.remove('flex');
        popup.classList.add('hidden');
    }

// ============================================
// DISCIPLINE MANAGEMENT - PURE JAVASCRIPT
// ============================================

// Global variables from PHP
let currentPage = PAGE_DATA.currentPage;
let totalPages = PAGE_DATA.totalPages;
let totalRecords = PAGE_DATA.totalRecords;
let deleteTargetRow = null;
let deleteOptionType = null;
let deleteOptionId = null;

// ============================================
// EDIT DISCIPLINE
// ============================================
function editDiscipline(button) {
    const row = button.closest('tr.discipline-row');
    if (!row) return;
    
    const id = row.getAttribute('data-id');
    if (!id || id === '0') {
        showMessage('Invalid record ID', 'error');
        return;
    }
    
    document.getElementById('edit-discipline-id').value = id;
    document.getElementById('violation_name').value = row.getAttribute('data-violation') || '';
    document.getElementById('sanction-select').value = row.getAttribute('data-sanction-id') || '';
    document.getElementById('severity-select').value = row.getAttribute('data-warning-id') || '';
    document.getElementById('description').value = row.getAttribute('data-description') || '';
    document.getElementById('submit-btn-text').textContent = 'Update Configuration';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
    showMessage('Record loaded for editing', 'success');
}

// ============================================
// DELETE DISCIPLINE
// ============================================
function deleteDiscipline(button) {
    const row = button.closest('tr.discipline-row');
    if (!row) return;
    
    const id = row.getAttribute('data-id');
    if (!id || id === '0') {
        showMessage('Invalid record ID', 'error');
        return;
    }
    
    deleteTargetRow = row;
    document.getElementById('delete-confirm-modal').classList.remove('hidden');
    document.getElementById('delete-confirm-modal').classList.add('flex');
}

function closeDeleteConfirm() {
    deleteTargetRow = null;
    const modal = document.getElementById('delete-confirm-modal');
    if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
}

function confirmDelete() {
    if (!deleteTargetRow) {
        closeDeleteConfirm();
        return;
    }
    
    const id = deleteTargetRow.getAttribute('data-id');
    if (!id || id === '0') {
        showMessage('Invalid ID', 'error');
        closeDeleteConfirm();
        return;
    }
    
    closeDeleteConfirm();
    
    fetch(window.location.pathname, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'ajax_action=delete&id=' + encodeURIComponent(id)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showMessage(data.message, 'success');
            const tbody = document.getElementById('table-body');
            const rowCount = tbody.querySelectorAll('tr.discipline-row').length;
            
            if (rowCount === 1 && currentPage > 1) {
                loadPage(currentPage - 1);
            } else {
                loadPage(currentPage);
            }
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(() => showMessage('Error deleting record', 'error'));
}

// ============================================
// PAGINATION
// ============================================
function loadPage(page) {
    if (page < 1 || page > totalPages) return;
    
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10"><i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400"></i></td></tr>';
    
    const formData = new FormData();
    formData.append('ajax_action', 'get-data');
    formData.append('page', page);
    
    fetch(window.location.pathname, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateTable(data.disciplines);
            currentPage = parseInt(data.page);
            totalPages = parseInt(data.totalPages);
            totalRecords = parseInt(data.totalRecords);
            
            renderPagination();
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-red-500">' + data.message + '</td></tr>';
        }
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-red-500">Error loading page</td></tr>';
    });
}

function updateTable(disciplines) {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '';
    
    if (!disciplines || disciplines.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-gray-400 italic text-sm">No records found.</td></tr>';
        return;
    }
    
    disciplines.forEach(d => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 transition-colors discipline-row';
        row.setAttribute('data-id', d.discipline_id || 0);
        row.setAttribute('data-violation', d.violation_name || '');
        row.setAttribute('data-sanction-id', d.id_sanctions || 0);
        row.setAttribute('data-warning-id', d.id_warning || 0);
        row.setAttribute('data-description', d.description || '');
        
        row.innerHTML = `
            <td class="px-6 py-4 text-sm font-bold text-gray-700">${escapeHtml(d.violation_name || 'N/A')}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(d.sanction || 'N/A')}</td>
            <td class="px-6 py-4"><span class="px-3 py-1 text-[10px] font-bold rounded-full bg-purple-100 text-purple-700 uppercase">${escapeHtml(d.severity || 'N/A')}</span></td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">${escapeHtml(d.description || 'N/A')}</td>
            <td class="px-6 py-4 text-right text-xs text-gray-400 font-mono">${d.date_created ? formatDate(d.date_created) : 'N/A'}</td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                    <button type="button" onclick="editDiscipline(this)" class="w-9 h-9 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition flex items-center justify-center">
                        <i class="fa-solid fa-edit text-sm"></i>
                    </button>
                    <button type="button" onclick="deleteDiscipline(this)" class="w-9 h-9 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex items-center justify-center">
                        <i class="fa-solid fa-trash text-sm"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function renderPagination() {
    const container = document.getElementById('pagination-buttons');
    if (!container) return;
    
    container.innerHTML = '';
    if (totalPages <= 1) return;
    
    // Previous button - only show if NOT on first page
    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.onclick = () => loadPage(currentPage - 1);
        prevBtn.className = 'w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-center shadow-sm';
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-sm"></i>';
        container.appendChild(prevBtn);
    }
    
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    // First page + ellipsis
    if (startPage > 1) {
        addPageButton(1, container);
        if (startPage > 2) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'px-2 text-gray-400 flex items-center';
            ellipsis.textContent = '...';
            container.appendChild(ellipsis);
        }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        addPageButton(i, container);
    }
    
    // Last page + ellipsis
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'px-2 text-gray-400 flex items-center';
            ellipsis.textContent = '...';
            container.appendChild(ellipsis);
        }
        addPageButton(totalPages, container);
    }
    
    // Next button - only show if NOT on last page
    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.onclick = () => loadPage(currentPage + 1);
        nextBtn.className = 'w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 hover:border-gray-300 transition flex items-center justify-center shadow-sm';
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right text-sm"></i>';
        container.appendChild(nextBtn);
    }
}

function addPageButton(pageNum, container) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.onclick = () => loadPage(pageNum);
    
    if (pageNum === currentPage) {
        // Active page - yellow
        btn.className = 'w-10 h-10 rounded-lg bg-[#f8c922] text-[#043915] font-bold shadow-md flex items-center justify-center border-2 border-[#f8c922]';
    } else {
        // Inactive pages - white with better hover
        btn.className = 'w-10 h-10 rounded-lg bg-white text-gray-700 font-bold hover:bg-gray-100 hover:text-[#043915] transition shadow-sm border border-gray-200 hover:border-gray-300 flex items-center justify-center';
    }
    
    btn.textContent = pageNum;
    container.appendChild(btn);
}

// ============================================
// FORM FUNCTIONS
// ============================================
function resetForm() {
    const form = document.getElementById('discipline-form');
    if (form) form.reset();
    
    const idField = document.getElementById('edit-discipline-id');
    if (idField) idField.value = '';
    
    const submitText = document.getElementById('submit-btn-text');
    if (submitText) submitText.textContent = 'Save Configuration';
}

// ============================================
// MANAGE POPUPS
// ============================================
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
    const formData = new FormData();
    formData.append('ajax_action', 'get-options');
    formData.append('type', type);
    
    fetch(window.location.pathname, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            renderOptionsList(type, data.options);
        }
    })
    .catch(() => {});
}

function renderOptionsList(type, options) {
    const container = document.getElementById(type + '-list');
    container.innerHTML = '';
    
    if (!options || options.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-400 py-4">No items found</p>';
        return;
    }
    
    options.forEach(option => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition';
        div.innerHTML = `
            <span class="font-medium text-gray-700">${escapeHtml(option.name)}</span>
            <div class="flex gap-2">
                <button type="button" onclick='openEditPopup("${type}", ${option.id}, "${escapeHtml(option.name).replace(/"/g, '&quot;')}")' class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition flex items-center justify-center">
                    <i class="fa-solid fa-edit text-sm"></i>
                </button>
                <button type="button" onclick="deleteOption('${type}', ${option.id})" class="w-8 h-8 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex items-center justify-center">
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
    
    fetch(window.location.pathname, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `ajax_action=manage-options&type=${type}&action=add&name=${encodeURIComponent(name)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('new-' + type + '-name').value = '';
            loadOptionsList(type);
            showMessage('Added successfully!', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(() => showMessage('Error adding item', 'error'));
}

function openEditPopup(type, id, name) {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = name;
    
    document.getElementById('edit-option-id').value = id;
    document.getElementById('edit-option-type').value = type;
    document.getElementById('edit-option-name').value = textarea.value;
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
    
    fetch(window.location.pathname, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `ajax_action=manage-options&type=${type}&action=edit&id=${id}&name=${encodeURIComponent(name)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeEditPopup();
            loadOptionsList(type);
            showMessage('Updated successfully!', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(() => showMessage('Error updating item', 'error'));
}

function deleteOption(type, id) {
    deleteOptionType = type;
    deleteOptionId = id;
    
    const itemType = type === 'sanction' ? 'sanction' : 'severity level';
    document.getElementById('delete-option-message').textContent = `Are you sure you want to delete this ${itemType}?`;
    
    document.getElementById('delete-option-modal').classList.remove('hidden');
    document.getElementById('delete-option-modal').classList.add('flex');
}

function closeDeleteOptionModal() {
    deleteOptionType = null;
    deleteOptionId = null;
    document.getElementById('delete-option-modal').classList.remove('flex');
    document.getElementById('delete-option-modal').classList.add('hidden');
}

function confirmDeleteOption() {
    if (!deleteOptionType || !deleteOptionId) {
        closeDeleteOptionModal();
        return;
    }
    
    const type = deleteOptionType;
    const id = deleteOptionId;
    
    closeDeleteOptionModal();
    
    fetch(window.location.pathname, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `ajax_action=manage-options&type=${type}&action=delete&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadOptionsList(type);
            showMessage('Deleted successfully!', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(() => showMessage('Error deleting item', 'error'));
}

function reloadDropdowns() {
    const formData = new FormData();
    formData.append('ajax_action', 'get-dropdowns');
    
    fetch(window.location.pathname, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateDropdown('sanction-select', data.sanctions, 'id_sanctions');
            updateDropdown('severity-select', data.warnings, 'id_warning');
        }
    })
    .catch(() => {});
}

function updateDropdown(selectId, options, idField) {
    const select = document.getElementById(selectId);
    const currentValue = select.value;
    
    const placeholder = selectId.includes('sanction') ? 'Select default sanction' : 'Select Severity Level';
    select.innerHTML = `<option value="">${placeholder}</option>`;
    
    options.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option[idField];
        opt.textContent = option.name;
        select.appendChild(opt);
    });
    
    if (currentValue) select.value = currentValue;
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch (e) {
        return 'N/A';
    }
}

function showMessage(message, type) {
    const existingMessages = document.querySelectorAll('.toast-message');
    existingMessages.forEach(msg => msg.remove());
    
    const div = document.createElement('div');
    div.className = `toast-message fixed top-4 right-4 z-[90] p-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'} animate-fade-in-down`;
    div.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span class="font-semibold">${escapeHtml(message)}</span>
        </div>
    `;
    document.body.appendChild(div);
    
    setTimeout(() => {
        div.style.opacity = '0';
        div.style.transform = 'translateY(-10px)';
        setTimeout(() => div.remove(), 300);
    }, 3000);
}

// ============================================
// FORM SUBMIT HANDLER
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Form submit
    document.getElementById('discipline-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';
        
        fetch(window.location.pathname, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showMessage(data.message, 'success');
                resetForm();
                setTimeout(() => loadPage(1), 300);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(() => showMessage('Error saving record', 'error'))
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        });
    });
    
    // Initialize pagination
    renderPagination();
    
    // Auto-hide flash messages
    setTimeout(() => {
        const msg = document.getElementById('status-message');
        if (msg) {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }
    }, 3000);
});