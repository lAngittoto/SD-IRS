<?php ob_start(); ?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gray-100 p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>

    <!-- Success/Error Messages -->
    <?php if(isset($_GET['status'])): ?>
        <div id="status-message" class="mb-6 p-4 rounded-xl <?= $_GET['status'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
            <div class="flex items-center gap-3">
                <i class="fa-solid <?= $_GET['status'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <span class="font-semibold">
                    <?= $_GET['status'] === 'success' ? 'Discipline record added successfully!' : 'Failed to add discipline record.' ?>
                </span>
            </div>
        </div>
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

            <div class="space-y-6">
                <div class="relative">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Violation Name</label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-12 bg-yellow-100 rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                            <i class="fa-solid fa-triangle-exclamation text-yellow-600 text-lg"></i>
                        </div>
                        <input type="text" name="violation_name" id="violation_name" required placeholder="e.g. Unauthorized Absence"
                            class="w-full pl-16 pr-4 py-3.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915] bg-gray-50">
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
                                    <option value="<?= $s['id_sanctions'] ?>" data-id="<?= $s['id_sanctions'] ?>">
                                        <?= htmlspecialchars($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="shrink-0 w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-100 transition border border-blue-200" onclick="openPopup('popup-sanction')">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div id="sanction-tags" class="mt-3 flex flex-wrap gap-2"></div>
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
                                    <option value="<?= $w['id_warning'] ?>" data-id="<?= $w['id_warning'] ?>">
                                        <?= htmlspecialchars($w['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="shrink-0 w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center hover:bg-purple-100 transition border border-purple-200" onclick="openPopup('popup-severity')">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div id="severity-tags" class="mt-3 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Description / Notes</label>
                    <textarea name="description" rows="10" placeholder="Provide any notes..."
                        class="w-full p-4 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#043915] bg-gray-50 resize-none min-h-[150px]"></textarea>
                </div>
            </div>

            <div class="md:col-span-2 flex justify-end gap-3 pt-6 border-t border-gray-100">
                <button type="submit" class="px-8 py-3.5 rounded-xl text-sm font-bold text-[#043915] bg-[#f8c922] hover:bg-yellow-300 transition shadow-md flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk"></i> Save Configuration
                </button>
            </div>
        </form>
    </section>

    <!-- Discipline Records Table -->
    <section class="w-full bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#043915] text-white text-[11px] uppercase tracking-widest">
                        <th class="px-6 py-4">Violation Name</th>
                        <th class="px-6 py-4">Sanction</th>
                        <th class="px-6 py-4">Severity</th>
                        <th class="px-6 py-4">Description</th>
                        <th class="px-6 py-4 text-right">Date Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(!empty($disciplines)): foreach($disciplines as $d): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-bold text-gray-700"><?= htmlspecialchars($d['violation_name']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($d['sanction']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-purple-100 text-purple-700 uppercase">
                                    <?= htmlspecialchars($d['severity']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($d['description'] ?? '') ?>">
                                <?= htmlspecialchars($d['description'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-gray-400 font-mono"><?= date('M d, Y', strtotime($d['date_created'])) ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center py-10 text-gray-400 italic text-sm">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if(isset($totalPages) && $totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-500">
                    Showing page <?= $page ?> of <?= $totalPages ?> (<?= $totalRecords ?> total records)
                </div>
                <div class="flex items-center gap-2">
                    <?php if($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">
                            <i class="fa-solid fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if($startPage > 1): ?>
                        <a href="?page=1" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">1</a>
                        <?php if($startPage > 2): ?>
                            <span class="px-2 text-gray-400">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $i === $page ? 'bg-[#043915] text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($endPage < $totalPages): ?>
                        <?php if($endPage < $totalPages - 1): ?>
                            <span class="px-2 text-gray-400">...</span>
                        <?php endif; ?>
                        <a href="?page=<?= $totalPages ?>" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <?php if($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Add Sanction Popup -->
    <div id="popup-sanction" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <h3 class="font-bold text-[#043915] mb-4 text-lg">Add Sanction Option</h3>
            <input type="text" id="new-sanction-name" class="w-full border border-gray-300 p-3 rounded-xl text-sm bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter sanction name">
            <div class="flex justify-end mt-4 gap-2">
                <button onclick="closePopup('popup-sanction')" class="text-sm font-bold text-gray-400 px-4 py-2 hover:text-gray-600 transition">Cancel</button>
                <button onclick="saveOption('sanction')" class="bg-blue-600 text-white px-6 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition">
                    <i class="fa-solid fa-plus mr-1"></i> Add
                </button>
            </div>
        </div>
    </div>

    <!-- Add Severity Popup -->
    <div id="popup-severity" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <h3 class="font-bold text-[#043915] mb-4 text-lg">Add Severity Level</h3>
            <input type="text" id="new-severity-name" class="w-full border border-gray-300 p-3 rounded-xl text-sm bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:outline-none" placeholder="Enter severity level">
            <div class="flex justify-end mt-4 gap-2">
                <button onclick="closePopup('popup-severity')" class="text-sm font-bold text-gray-400 px-4 py-2 hover:text-gray-600 transition">Cancel</button>
                <button onclick="saveOption('severity')" class="bg-purple-600 text-white px-6 py-2 rounded-xl text-sm font-bold hover:bg-purple-700 transition">
                    <i class="fa-solid fa-plus mr-1"></i> Add
                </button>
            </div>
        </div>
    </div>

</main>

<script>
// Popup functions
function openPopup(id) { 
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function closePopup(id) { 
    document.getElementById(id).classList.remove('flex');
    document.getElementById(id).classList.add('hidden');
    // Clear input
    const inputId = id === 'popup-sanction' ? 'new-sanction-name' : 'new-severity-name';
    document.getElementById(inputId).value = '';
}

// Save new option via AJAX
function saveOption(type) {
    const inputId = type === 'sanction' ? 'new-sanction-name' : 'new-severity-name';
    const selectId = type === 'sanction' ? 'sanction-select' : 'severity-select';
    const name = document.getElementById(inputId).value.trim();
    
    if(!name) {
        showMessage('Please enter a name', 'error');
        return;
    }
    
    // Send AJAX request
    fetch('/student-discipline-and-incident-reporting-system/public/discipline-records?action=manage-options', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=${type}&action=add&name=${encodeURIComponent(name)}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            // Add to select dropdown
            const select = document.getElementById(selectId);
            const option = document.createElement('option');
            option.value = data.id;
            option.textContent = data.name;
            option.setAttribute('data-id', data.id);
            select.appendChild(option);
            
            // Add tag with delete button
            addTag(type, data.id, data.name);
            
            // Close popup and clear input
            closePopup('popup-' + type);
            
            // Show success message
            showMessage('Added successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to add option', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
}

// Delete option via AJAX
function deleteOption(type, id, tagElement) {
    if(!confirm('Are you sure you want to delete this option?')) {
        return;
    }
    
    fetch('/student-discipline-and-incident-reporting-system/public/discipline-records?action=manage-options', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=${type}&action=delete&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            // Remove from dropdown
            const selectId = type === 'sanction' ? 'sanction-select' : 'severity-select';
            const select = document.getElementById(selectId);
            const option = select.querySelector(`option[data-id="${id}"]`);
            if(option) {
                option.remove();
            }
            
            // Remove tag
            tagElement.remove();
            
            showMessage('Deleted successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to delete option', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred', 'error');
    });
}

// Add tag with delete button
function addTag(type, id, name) {
    const containerId = type === 'sanction' ? 'sanction-tags' : 'severity-tags';
    const container = document.getElementById(containerId);
    
    // Check if tag already exists
    if(container.querySelector(`[data-tag-id="${id}"]`)) {
        return;
    }
    
    const tag = document.createElement('div');
    tag.setAttribute('data-tag-id', id);
    tag.className = `inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold ${type === 'sanction' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-purple-100 text-purple-700 border border-purple-200'}`;
    tag.innerHTML = `
        <span>${name}</span>
        <button type="button" onclick="deleteOption('${type}', ${id}, this.parentElement)" class="hover:bg-red-100 rounded-full p-0.5 transition">
            <i class="fa-solid fa-xmark text-red-600"></i>
        </button>
    `;
    
    container.appendChild(tag);
}

// Load existing tags on page load
function loadExistingTags() {
    // Load sanction tags
    const sanctionSelect = document.getElementById('sanction-select');
    Array.from(sanctionSelect.options).forEach(option => {
        if(option.value) {
            addTag('sanction', option.getAttribute('data-id'), option.textContent.trim());
        }
    });
    
    // Load severity tags
    const severitySelect = document.getElementById('severity-select');
    Array.from(severitySelect.options).forEach(option => {
        if(option.value) {
            addTag('severity', option.getAttribute('data-id'), option.textContent.trim());
        }
    });
}

// Show success/error message
function showMessage(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-[70] p-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'} animate-fade-in-down`;
    alertDiv.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span class="font-semibold">${message}</span>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Form validation before submit
document.getElementById('discipline-form')?.addEventListener('submit', function(e) {
    const violationName = document.getElementById('violation_name').value.trim();
    const sanction = document.getElementById('sanction-select').value;
    const severity = document.getElementById('severity-select').value;
    
    if(!violationName) {
        e.preventDefault();
        showMessage('Please enter a violation name', 'error');
        return false;
    }
    
    if(!sanction) {
        e.preventDefault();
        showMessage('Please select a sanction', 'error');
        return false;
    }
    
    if(!severity) {
        e.preventDefault();
        showMessage('Please select a severity level', 'error');
        return false;
    }
});

// Auto-hide status message
setTimeout(() => {
    const statusMsg = document.getElementById('status-message');
    if(statusMsg) {
        statusMsg.style.transition = 'opacity 0.5s';
        statusMsg.style.opacity = '0';
        setTimeout(() => statusMsg.remove(), 500);
    }
}, 3000);

// Load tags on page load
document.addEventListener('DOMContentLoaded', loadExistingTags);
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