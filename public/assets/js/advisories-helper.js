// Pagination Configuration
const ITEMS_PER_PAGE = 40;
let currentPage = 1;
let allData = [];
let currentView = 'default'; // 'default' or 'advisory'
let currentGradeFilter = 'all'; // Track current grade filter in modal
let allAvailableStudents = []; // Store all available students
let currentAdvisoryGrade = null; // Track current advisory grade for promotion

// Toast Notification System
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
    
    toast.className = `${bgColor} text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-0 opacity-100`;
    toast.innerHTML = `
        <i class="fa-solid ${icon} text-xl"></i>
        <span class="font-medium">${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 hover:bg-white/20 rounded-lg p-1 transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Modal Grade Filter Functions
function filterModalStudents(grade) {
    currentGradeFilter = grade;
    
    // Update active tab styling
    ['all', '7', '8', '9', '10'].forEach(g => {
        const tab = document.getElementById(`gradeTab_${g}`);
        if (tab) {
            if (g === grade) {
                tab.classList.add('ring-2', 'ring-[#043915]', 'font-black');
            } else {
                tab.classList.remove('ring-2', 'ring-[#043915]', 'font-black');
            }
        }
    });
    
    // Filter and display students
    displayFilteredStudents();
}

function displayFilteredStudents() {
    const tbody = document.getElementById('modalStudentTable');
    const advisorySelect = document.getElementById('modalAdvisoryTeacher');
    const selectedAdvisory = advisorySelect.value ? advisorySelect.selectedOptions[0] : null;
    const advisoryGrade = selectedAdvisory ? selectedAdvisory.getAttribute('data-grade-level') : null;
    
    let filteredStudents = allAvailableStudents;
    
    // Apply grade filter
    if (currentGradeFilter !== 'all') {
        filteredStudents = filteredStudents.filter(student => student.grade_level === currentGradeFilter);
    }
    
    // If advisory is selected, only show matching grade students
    if (advisoryGrade) {
        filteredStudents = filteredStudents.filter(student => student.grade_level === advisoryGrade);
    }
    
    if (filteredStudents.length === 0) {
        const gradeText = currentGradeFilter === 'all' ? 'students' : `Grade ${currentGradeFilter} students`;
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fa-solid fa-user-check text-4xl mb-3 opacity-20"></i>
                        <p class="text-sm font-medium text-gray-600">No ${gradeText} Available</p>
                        <p class="text-xs text-gray-500 mt-1">All ${gradeText} are already assigned to advisory classes.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    filteredStudents.forEach(student => {
        const gradeColors = {
            '7': 'bg-blue-100 text-blue-800',
            '8': 'bg-orange-100 text-orange-800',
            '9': 'bg-purple-100 text-purple-800',
            '10': 'bg-green-100 text-green-800'
        };
        const gradeClass = gradeColors[student.grade_level] || 'bg-gray-100 text-gray-800';
        
        html += `
            <tr class="student-row hover:bg-gray-50 transition" data-grade="${student.grade_level}" data-student-id="${student.user_id}">
                <td class="py-3 px-6">
                    <input type="checkbox" name="student_ids[]" value="${student.user_id}" class="student-checkbox w-4 h-4 text-[#043915] border-gray-300 rounded focus:ring-[#043915]">
                    <input type="hidden" name="grade_levels[${student.user_id}]" value="${student.grade_level}">
                </td>
                <td class="py-3 px-6">
                    <div class="text-sm font-medium text-gray-900">${escapeHtml(student.name)}</div>
                    <div class="text-xs text-gray-500">Grade ${student.grade_level}</div>
                </td>
                <td class="py-3 px-6 text-sm text-gray-600">${escapeHtml(student.lrn)}</td>
                <td class="py-3 px-6">
                    <span class="px-3 py-1 ${gradeClass} rounded-lg text-xs font-bold">
                        Grade ${student.grade_level}
                    </span>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// Pagination Functions
function renderPagination(totalItems) {
    const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));
    const container = document.getElementById('paginationContainer');
    
    let html = '';
    
    // Previous button
    html += `
        <button onclick="changePage(${currentPage - 1})" 
                ${currentPage === 1 ? 'disabled' : ''}
                class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition">
            <i class="fa-solid fa-chevron-left text-xs"></i>
        </button>
    `;
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page
    if (startPage > 1) {
        html += `
            <button onclick="changePage(1)" 
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold transition">
                1
            </button>
        `;
        if (startPage > 2) {
            html += `<span class="px-2 text-gray-400">...</span>`;
        }
    }
    
    // Page buttons
    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentPage;
        html += `
            <button onclick="changePage(${i})" 
                    class="w-10 h-10 flex items-center justify-center rounded-xl ${isActive ? 'bg-[#f8c922] text-[#043915] font-bold shadow-md' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold'} transition">
                ${i}
            </button>
        `;
    }
    
    // Last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="px-2 text-gray-400">...</span>`;
        }
        html += `
            <button onclick="changePage(${totalPages})" 
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold transition">
                ${totalPages}
            </button>
        `;
    }
    
    // Next button
    html += `
        <button onclick="changePage(${currentPage + 1})" 
                ${currentPage === totalPages ? 'disabled' : ''}
                class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition">
            <i class="fa-solid fa-chevron-right text-xs"></i>
        </button>
    `;
    
    container.innerHTML = html;
}

function changePage(page) {
    const totalPages = Math.max(1, Math.ceil(allData.length / ITEMS_PER_PAGE));
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    
    if (currentView === 'advisory') {
        displayAdvisoryList(allData);
    } else if (currentView === 'subject') {
        displaySubjectTeachersList(allData);
    } else {
        displayTableData(allData);
    }
}

function getPaginatedData(data) {
    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
    const endIndex = startIndex + ITEMS_PER_PAGE;
    return data.slice(startIndex, endIndex);
}

// Modal Functions
function openTeacherModal() {
    document.getElementById('teacherModal').classList.remove('hidden');
    document.getElementById('teacherModal').classList.add('flex');
}

function closeTeacherModal() {
    document.getElementById('teacherModal').classList.add('hidden');
    document.getElementById('teacherModal').classList.remove('flex');
    document.getElementById('assignTeacherForm').reset();
    document.getElementById('advisoryFields').classList.add('hidden');
}

function toggleAdvisoryFields() {
    const roleType = document.getElementById('teacherRoleType').value;
    const advisoryFields = document.getElementById('advisoryFields');
    const advisoryNameInput = document.getElementById('advisoryNameInput');
    const advisoryGradeLevel = document.getElementById('advisoryGradeLevel');
    
    if (roleType === 'advisory') {
        advisoryFields.classList.remove('hidden');
        advisoryNameInput.required = true;
        advisoryGradeLevel.required = true;
    } else {
        advisoryFields.classList.add('hidden');
        advisoryNameInput.required = false;
        advisoryGradeLevel.required = false;
    }
}

function submitTeacherAssignment(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeTeacherModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function openStudentModal() {
    const advisorySelect = document.getElementById('modalAdvisoryTeacher');
    const hasAdvisoryTeachers = advisorySelect.options.length > 1; 
    
    if (!hasAdvisoryTeachers) {
        showToast('Please assign an advisory teacher first before assigning students.', 'error');
        return;
    }
    
    document.getElementById('studentModal').classList.remove('hidden');
    document.getElementById('studentModal').classList.add('flex');
    
    // Load all available students immediately
    loadAllAvailableStudents();
}

function closeStudentModal() {
    document.getElementById('studentModal').classList.add('hidden');
    document.getElementById('studentModal').classList.remove('flex');
    document.getElementById('assignStudentsForm').reset();
    document.getElementById('advisoryCapacityInfo').classList.add('hidden');
    currentGradeFilter = 'all';
    allAvailableStudents = [];
    
    // Reset tab styling
    ['all', '7', '8', '9', '10'].forEach(g => {
        const tab = document.getElementById(`gradeTab_${g}`);
        if (tab) {
            tab.classList.remove('ring-2', 'ring-[#043915]', 'font-black');
        }
    });
    document.getElementById('gradeTab_all')?.classList.add('ring-2', 'ring-[#043915]', 'font-black');
}

function loadAllAvailableStudents() {
    const loadingDiv = document.getElementById('loadingStudents');
    const tbody = document.getElementById('modalStudentTable');
    
    tbody.innerHTML = `
        <tr>
            <td colspan="4" class="py-12 text-center">
                <div class="flex flex-col items-center justify-center text-gray-400">
                    <i class="fa-solid fa-spinner fa-spin text-4xl mb-3 text-[#043915]"></i>
                    <p class="text-sm font-medium text-gray-600">Loading Available Students...</p>
                </div>
            </td>
        </tr>
    `;
    
    const formData = new FormData();
    formData.append('action', 'get_unassigned_students');
    formData.append('advisory_id', 0);
    formData.append('grade_level', '');
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            allAvailableStudents = data.data;
            displayFilteredStudents();
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fa-solid fa-exclamation-triangle text-4xl mb-3 opacity-20"></i>
                            <p class="text-sm font-medium text-gray-600">Error Loading Students</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fa-solid fa-exclamation-triangle text-4xl mb-3 opacity-20"></i>
                        <p class="text-sm font-medium text-gray-600">Error Loading Students</p>
                    </div>
                </td>
            </tr>
        `;
    });
}

// Advisory selection handler - updates capacity info and filters students
document.getElementById('modalAdvisoryTeacher')?.addEventListener('change', function() {
    const selectedOption = this.selectedOptions[0];
    const advisoryId = this.value;
    const capacityInfo = document.getElementById('advisoryCapacityInfo');
    
    if (!advisoryId) {
        capacityInfo.classList.add('hidden');
        displayFilteredStudents();
        return;
    }
    
    const currentCount = parseInt(selectedOption.getAttribute('data-current-count')) || 0;
    const advisoryGrade = selectedOption.getAttribute('data-grade-level');
    
    // Update capacity info
    document.getElementById('currentStudentCount').textContent = currentCount;
    document.getElementById('remainingSlots').textContent = (40 - currentCount);
    document.getElementById('advisoryGradeLevel').textContent = advisoryGrade;
    capacityInfo.classList.remove('hidden');
    
    // Filter students to match advisory grade
    displayFilteredStudents();
});

function openReassignModal(assignmentId, studentName, currentGrade) {
    document.getElementById('reassignAssignmentId').value = assignmentId;
    document.getElementById('reassignStudentName').textContent = studentName;
    document.getElementById('reassignCurrentGrade').value = currentGrade;
    
    // Filter advisory options to show only matching grade
    const select = document.getElementById('reassignAdvisorySelect');
    Array.from(select.options).forEach(option => {
        if (option.value) {
            const optionGrade = option.getAttribute('data-grade');
            if (optionGrade !== currentGrade.toString()) {
                option.disabled = true;
                option.classList.add('text-gray-400');
            } else {
                option.disabled = false;
                option.classList.remove('text-gray-400');
            }
        }
    });
    
    document.getElementById('reassignModal').classList.remove('hidden');
    document.getElementById('reassignModal').classList.add('flex');
}

function closeReassignModal() {
    document.getElementById('reassignModal').classList.add('hidden');
    document.getElementById('reassignModal').classList.remove('flex');
    document.getElementById('reassignForm').reset();
}

function submitReassignment(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const selectedAdvisory = document.getElementById('reassignAdvisorySelect').selectedOptions[0];
    const advisoryGrade = selectedAdvisory.getAttribute('data-grade');
    const currentGrade = formData.get('current_grade');
    
    // Validate grade match
    if (advisoryGrade !== currentGrade) {
        showToast(`Cannot reassign. Student is in Grade ${currentGrade} but selected advisory is for Grade ${advisoryGrade}.`, 'error');
        return;
    }
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeReassignModal();
            loadFilteredData();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function openRemoveAdvisoryModal(assignmentId, studentName) {
    document.getElementById('removeAssignmentId').value = assignmentId;
    document.getElementById('removeStudentName').textContent = studentName;
    document.getElementById('removeAdvisoryModal').classList.remove('hidden');
    document.getElementById('removeAdvisoryModal').classList.add('flex');
}

function closeRemoveAdvisoryModal() {
    document.getElementById('removeAdvisoryModal').classList.add('hidden');
    document.getElementById('removeAdvisoryModal').classList.remove('flex');
}

function submitRemoval(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeRemoveAdvisoryModal();
            loadFilteredData();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function openConvertTeacherModal(advisoryId, teacherName) {
    document.getElementById('convertAdvisoryId').value = advisoryId;
    document.getElementById('convertTeacherName').textContent = teacherName;
    document.getElementById('convertTeacherModal').classList.remove('hidden');
    document.getElementById('convertTeacherModal').classList.add('flex');
}

function closeConvertTeacherModal() {
    document.getElementById('convertTeacherModal').classList.add('hidden');
    document.getElementById('convertTeacherModal').classList.remove('flex');
}

function submitConversion(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeConvertTeacherModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function toggleAllVisibleStudents(checked) {
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        const row = cb.closest('tr');
        if (row && row.style.display !== 'none') {
            cb.checked = checked;
        }
    });
}

function confirmStudentAssignment() {
    const advisorySelect = document.getElementById('modalAdvisoryTeacher');
    const advisoryId = advisorySelect.value;
    const selectedStudents = document.querySelectorAll('.student-checkbox:checked');
    
    if (!advisoryId) {
        showToast('Please select an advisory teacher first.', 'error');
        return;
    }
    
    if (selectedStudents.length === 0) {
        showToast('Please select at least one student.', 'error');
        return;
    }
    
    // Check advisory capacity
    const selectedOption = advisorySelect.selectedOptions[0];
    const currentCount = parseInt(selectedOption.getAttribute('data-current-count')) || 0;
    const newTotal = currentCount + selectedStudents.length;
    
    if (newTotal > 40) {
        const remaining = 40 - currentCount;
        showToast(`Cannot assign ${selectedStudents.length} students. Only ${remaining} slots available (Maximum: 40).`, 'error');
        return;
    }
    
    document.getElementById('hiddenAdvisoryId').value = advisoryId;
    
    const formData = new FormData(document.getElementById('assignStudentsForm'));
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeStudentModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function applyFilters(event) {
    event.preventDefault();
    
    currentPage = 1;
    const teacherRole = document.getElementById('filterTeacherRole').value;
    
    if (teacherRole === 'advisory') {
        currentView = 'advisory';
        loadAdvisoryList();
    } else if (teacherRole === 'subject') {
        currentView = 'subject';
        loadSubjectTeachersList();
    } else {
        currentView = 'default';
        loadFilteredData();
    }
}

function loadSubjectTeachersList() {
    const search = document.getElementById('searchInput').value;
    
    const formData = new FormData();
    formData.append('action', 'get_subject_teachers');
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let filteredData = data.data;
            
            // Apply search filter
            if (search.trim() !== '') {
                filteredData = filteredData.filter(teacher => {
                    return teacher.teacher_name.toLowerCase().includes(search.toLowerCase()) ||
                           teacher.teacher_email.toLowerCase().includes(search.toLowerCase());
                });
            }
            
            allData = filteredData;
            displaySubjectTeachersList(filteredData);
        }
    })
    .catch(error => console.error('Error:', error));
}

function displaySubjectTeachersList(teachers) {
    const tbody = document.getElementById('tableBody');
    const resultCount = document.getElementById('resultCount');
    
    if (teachers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fa-solid fa-chalkboard-user text-5xl mb-4 opacity-20"></i>
                        <p class="text-base font-semibold text-gray-600 mb-1">No Subject Teachers Found</p>
                        <p class="text-sm text-gray-500">Assign subject teachers to see them here</p>
                    </div>
                </td>
            </tr>
        `;
        resultCount.textContent = 'Showing 0 Results';
        renderPagination(0);
        return;
    }
    
    const paginatedTeachers = getPaginatedData(teachers);
    
    let html = '';
    paginatedTeachers.forEach(teacher => {
        html += `
            <tr class="hover:bg-gray-50 transition">
                <td class="py-4 px-6" colspan="3">
                    <div class="text-sm font-medium text-gray-900">${escapeHtml(teacher.teacher_name)}</div>
                    <div class="text-xs text-gray-500">${escapeHtml(teacher.teacher_email)}</div>
                </td>
                <td class="py-4 px-6" colspan="2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                        Subject Teacher
                    </span>
                </td>
                <td class="py-4 px-6" colspan="2">
                    <span class="text-xs text-gray-500">${formatDate(teacher.assigned_at)}</span>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE + 1;
    const endIndex = Math.min(currentPage * ITEMS_PER_PAGE, teachers.length);
    resultCount.textContent = `Showing ${startIndex}-${endIndex} of ${teachers.length} Subject Teacher${teachers.length !== 1 ? 's' : ''}`;
    
    renderPagination(teachers.length);
}

function resetFilters() {
    document.getElementById('filterForm').reset();
    document.getElementById('searchInput').value = '';
    currentPage = 1;
    allData = [];
    currentView = 'default';
    
    showToast('Filters have been reset', 'success');
    
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="px-6 py-20 text-center">
                <div class="flex flex-col items-center justify-center text-gray-400">
                    <i class="fa-solid fa-filter text-4xl mb-4 opacity-20"></i>
                    <p class="text-sm font-medium text-gray-600">No Data to Display</p>
                    <p class="text-xs text-gray-500 mt-1">Apply filters above to view student assignments</p>
                </div>
            </td>
        </tr>
    `;
    document.getElementById('resultCount').textContent = 'Showing 0 Results';
    renderPagination(0);
}

let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        currentPage = 1;
        const teacherRole = document.getElementById('filterTeacherRole').value;
        if (teacherRole === 'advisory') {
            loadAdvisoryList();
        } else if (teacherRole === 'subject') {
            loadSubjectTeachersList();
        } else {
            loadFilteredData();
        }
    }, 500);
});

function loadAdvisoryList() {
    const search = document.getElementById('searchInput').value;
    const sortName = document.getElementById('sortName').value;
    
    const formData = new FormData();
    formData.append('action', 'get_advisory_list');
    formData.append('sort_by', 'advisory_name');
    formData.append('sort_order', sortName);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let filteredData = data.data;
            
            // Apply search filter
            if (search.trim() !== '') {
                filteredData = filteredData.filter(advisory => {
                    return advisory.advisory_name.toLowerCase().includes(search.toLowerCase()) ||
                           advisory.teacher_name.toLowerCase().includes(search.toLowerCase()) ||
                           advisory.grade_level.includes(search);
                });
            }
            
            allData = filteredData;
            displayAdvisoryList(filteredData);
        }
    })
    .catch(error => console.error('Error:', error));
}

function displayAdvisoryList(advisories) {
    const tbody = document.getElementById('tableBody');
    const resultCount = document.getElementById('resultCount');
    
    if (advisories.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fa-solid fa-inbox text-5xl mb-4 opacity-20"></i>
                        <p class="text-base font-semibold text-gray-600 mb-1">No Advisory Classes Found</p>
                        <p class="text-sm text-gray-500">Assign advisory teachers to create classes</p>
                    </div>
                </td>
            </tr>
        `;
        resultCount.textContent = 'Showing 0 Results';
        renderPagination(0);
        return;
    }
    
    const paginatedAdvisories = getPaginatedData(advisories);
    
    let html = '';
    paginatedAdvisories.forEach(advisory => {
        const gradeColors = {
            '7': 'bg-blue-100 text-blue-800',
            '8': 'bg-orange-100 text-orange-800',
            '9': 'bg-purple-100 text-purple-800',
            '10': 'bg-green-100 text-green-800'
        };
        
        const gradeClass = gradeColors[advisory.grade_level] || 'bg-gray-100 text-gray-800';
        const capacityClass = advisory.student_count >= 40 ? 'bg-red-100 text-red-800' : 
                             advisory.student_count >= 35 ? 'bg-orange-100 text-orange-800' : 
                             'bg-green-100 text-green-800';
        
        html += `
            <tr class="hover:bg-gray-50 transition">
                <td class="py-4 px-6" colspan="2">
                    <div class="text-sm font-medium text-gray-900">${escapeHtml(advisory.advisory_name)}</div>
                    <div class="text-xs text-gray-500">Teacher: ${escapeHtml(advisory.teacher_name)}</div>
                </td>
                <td class="py-4 px-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${gradeClass}">
                        Grade ${advisory.grade_level}
                    </span>
                </td>
                <td class="py-4 px-6" colspan="2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${capacityClass}">
                        ${advisory.student_count}/40 Students
                    </span>
                </td>
                <td class="py-4 px-6">
                    <button onclick="viewAdvisoryDetails(${advisory.advisory_id}, '${advisory.grade_level}')" 
                        class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-xs font-bold hover:bg-blue-600 transition inline-flex items-center gap-2" 
                        title="View Details">
                        <i class="fa-solid fa-eye"></i> View
                    </button>
                </td>
                <td class="py-4 px-6 text-center">
                    <button onclick="openConvertTeacherModal(${advisory.advisory_id}, '${escapeHtml(advisory.teacher_name)}')" 
                        class="px-3 py-1.5 bg-orange-500 text-white rounded-lg text-xs font-bold hover:bg-orange-600 transition" 
                        title="Convert to Subject Teacher">
                        <i class="fa-solid fa-repeat"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE + 1;
    const endIndex = Math.min(currentPage * ITEMS_PER_PAGE, advisories.length);
    resultCount.textContent = `Showing ${startIndex}-${endIndex} of ${advisories.length} Advisory Class${advisories.length !== 1 ? 'es' : ''}`;
    
    renderPagination(advisories.length);
}

function viewAdvisoryDetails(advisoryId, advisoryGrade) {
    currentAdvisoryGrade = advisoryGrade;
    
    const formData = new FormData();
    formData.append('action', 'get_advisory_students');
    formData.append('advisory_id', advisoryId);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayAdvisoryStudents(data.data, advisoryId, advisoryGrade);
        }
    })
    .catch(error => console.error('Error:', error));
}

function displayAdvisoryStudents(students, advisoryId, advisoryGrade) {
    const modal = document.getElementById('viewAdvisoryModal');
    const studentsList = document.getElementById('advisoryStudentsList');
    const promotionControls = document.getElementById('gradePromotionControls');
    
    // Update title
    if (students.length > 0) {
        document.getElementById('advisoryDetailTitle').textContent = 'Advisory Students';
        document.getElementById('advisoryDetailSubtitle').textContent = `${students.length}/40 Students - Grade ${advisoryGrade}`;
    }
    
    // Show/setup promotion controls
    if (students.length > 0) {
        promotionControls.classList.remove('hidden');
        setupGradePromotionDropdown(advisoryGrade);
    } else {
        promotionControls.classList.add('hidden');
    }
    
    if (students.length === 0) {
        studentsList.innerHTML = `
            <div class="py-12 text-center">
                <i class="fa-solid fa-users-slash text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm font-medium text-gray-600">No Students Assigned</p>
                <p class="text-xs text-gray-500 mt-1">This advisory class has no students yet</p>
            </div>
        `;
    } else {
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[500px] overflow-y-auto pr-2">';
        students.forEach(student => {
            const gradeColors = {
                '7': 'bg-blue-100 text-blue-800',
                '8': 'bg-orange-100 text-orange-800',
                '9': 'bg-purple-100 text-purple-800',
                '10': 'bg-green-100 text-green-800'
            };
            const gradeClass = gradeColors[student.grade_level] || 'bg-gray-100 text-gray-800';
            
            html += `
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 hover:shadow-md transition">
                    <div class="flex items-start gap-3 mb-2">
                        <input type="checkbox" 
                               class="student-promotion-checkbox mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               data-assignment-id="${student.assignment_id}"
                               data-student-id="${student.student_id}"
                               data-student-name="${escapeHtml(student.student_name)}"
                               data-current-grade="${student.grade_level}"
                               onchange="updateSelectedCount()">
                        <div class="flex-1">
                            <p class="font-bold text-gray-900">${escapeHtml(student.student_name)}</p>
                            <p class="text-xs text-gray-500">LRN: ${escapeHtml(student.lrn)}</p>
                        </div>
                        <span class="px-2 py-1 rounded-lg text-xs font-bold ${gradeClass}">
                            Grade ${student.grade_level}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400">Assigned: ${formatDate(student.assigned_date)}</p>
                </div>
            `;
        });
        html += '</div>';
        studentsList.innerHTML = html;
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function setupGradePromotionDropdown(currentGrade) {
    const dropdown = document.getElementById('bulkGradeSelect');
    dropdown.innerHTML = '<option value="">Select Grade Level</option>';
    
    const gradeNum = parseInt(currentGrade);
    
    // Only show NEXT grade level (Grade 7 -> Grade 8 only)
    if (gradeNum < 10) {
        const nextGrade = gradeNum + 1;
        const option = document.createElement('option');
        option.value = nextGrade;
        option.textContent = `Grade ${nextGrade}`;
        dropdown.appendChild(option);
    }
    
    // Disable if already at Grade 10
    if (gradeNum >= 10) {
        dropdown.disabled = true;
        const controlsDiv = document.getElementById('gradePromotionControls');
        controlsDiv.innerHTML = `
            <div class="bg-gray-100 rounded-2xl p-6 border border-gray-200 text-center">
                <i class="fa-solid fa-graduation-cap text-4xl text-gray-400 mb-3"></i>
                <p class="text-sm font-bold text-gray-600">Students are already at the highest grade level (Grade 10)</p>
                <p class="text-xs text-gray-500 mt-1">Grade promotion is not available</p>
            </div>
        `;
    } else {
        dropdown.disabled = false;
    }
}

function toggleAllStudentsPromotion(checked) {
    document.querySelectorAll('.student-promotion-checkbox').forEach(cb => {
        cb.checked = checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.student-promotion-checkbox:checked');
    const count = selectedCheckboxes.length;
    document.getElementById('selectedCount').textContent = `${count} student${count !== 1 ? 's' : ''} selected`;
    
    // Update select all checkbox state
    const selectAllCheckbox = document.getElementById('selectAllStudentsPromotion');
    const allCheckboxes = document.querySelectorAll('.student-promotion-checkbox');
    if (selectAllCheckbox && allCheckboxes.length > 0) {
        selectAllCheckbox.checked = count === allCheckboxes.length;
    }
}

function confirmBulkGradePromotion() {
    const selectedCheckboxes = document.querySelectorAll('.student-promotion-checkbox:checked');
    const newGrade = document.getElementById('bulkGradeSelect').value;
    
    if (selectedCheckboxes.length === 0) {
        showToast('Please select at least one student', 'error');
        return;
    }
    
    if (!newGrade) {
        showToast('Please select a grade level', 'error');
        return;
    }
    
    // Validate all students have the same current grade
    const currentGrade = selectedCheckboxes[0].getAttribute('data-current-grade');
    const allSameGrade = Array.from(selectedCheckboxes).every(cb => 
        cb.getAttribute('data-current-grade') === currentGrade
    );
    
    if (!allSameGrade) {
        showToast('All selected students must be in the same grade level', 'error');
        return;
    }
    
    // Validate promotion is to higher grade
    if (parseInt(newGrade) <= parseInt(currentGrade)) {
        showToast('Can only promote students to higher grade levels', 'error');
        return;
    }
    
    // Show confirmation modal
    showPromotionConfirmation(selectedCheckboxes, currentGrade, newGrade);
}

function showPromotionConfirmation(selectedCheckboxes, currentGrade, newGrade) {
    const studentCount = selectedCheckboxes.length;
    const studentNames = Array.from(selectedCheckboxes).slice(0, 5).map(cb => 
        cb.getAttribute('data-student-name')
    );
    
    // Create confirmation modal
    const modal = document.createElement('div');
    modal.id = 'promotionConfirmModal';
    modal.className = 'fixed inset-0 bg-black/60 flex items-center justify-center z-[150] backdrop-blur-sm p-4';
    
    modal.innerHTML = `
        <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl relative animate-scale-in">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-graduation-cap text-blue-600 text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-[#043915] mb-2">Confirm Grade Promotion</h2>
                <p class="text-sm text-gray-600">You are about to promote ${studentCount} student${studentCount > 1 ? 's' : ''} from Grade ${currentGrade} to Grade ${newGrade}</p>
            </div>
            
            <div class="bg-blue-50 rounded-xl p-4 mb-6 max-h-48 overflow-y-auto">
                <p class="text-xs font-bold text-blue-900 mb-2 uppercase tracking-wider">Selected Students:</p>
                <ul class="space-y-1 text-sm text-blue-800">
                    ${studentNames.map(name => `<li class="flex items-center gap-2"><i class="fa-solid fa-user text-xs text-blue-600"></i>${name}</li>`).join('')}
                    ${studentCount > 5 ? `<li class="text-xs text-blue-600 font-bold mt-2">+ ${studentCount - 5} more students</li>` : ''}
                </ul>
            </div>
            
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-info-circle text-amber-600 text-lg mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-amber-900 mb-1">Important Notice</p>
                        <p class="text-xs text-amber-800">Students will be removed from their current advisory class and made available for assignment to Grade ${newGrade} advisory classes.</p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closePromotionConfirm()" class="flex-1 px-6 py-3 border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" onclick="executePromotion()" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition shadow-lg">
                    <i class="fa-solid fa-arrow-up mr-2"></i>Promote Now
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Add animation
    setTimeout(() => {
        modal.querySelector('.animate-scale-in').style.animation = 'scaleIn 0.3s ease-out';
    }, 10);
}

function closePromotionConfirm() {
    const modal = document.getElementById('promotionConfirmModal');
    if (modal) {
        modal.remove();
    }
}

function executePromotion() {
    const selectedCheckboxes = document.querySelectorAll('.student-promotion-checkbox:checked');
    const newGrade = document.getElementById('bulkGradeSelect').value;
    
    // Collect assignment IDs
    const assignmentIds = Array.from(selectedCheckboxes).map(cb => 
        cb.getAttribute('data-assignment-id')
    );
    
    // Close confirmation modal
    closePromotionConfirm();
    
    // Show loading toast
    showToast('Processing grade promotion...', 'success');
    
    // Submit bulk promotion
    const formData = new FormData();
    formData.append('action', 'bulk_update_student_grade');
    assignmentIds.forEach(id => {
        formData.append('assignment_ids[]', id);
    });
    formData.append('new_grade', newGrade);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeViewAdvisoryModal();
            
            // Show longer success message with details
            const successModal = document.createElement('div');
            successModal.id = 'successPromotionModal';
            successModal.className = 'fixed inset-0 bg-black/60 flex items-center justify-center z-[150] backdrop-blur-sm p-4';
            
            successModal.innerHTML = `
                <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl relative animate-scale-in">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-check text-green-600 text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-green-600 mb-3">Promotion Successful!</h2>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                            <p class="text-sm text-green-800 leading-relaxed">${data.message}</p>
                        </div>
                        <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            <span>Refreshing page in <span id="countdown">3</span> seconds...</span>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(successModal);
            
            // Countdown timer
            let countdown = 3;
            const countdownEl = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                countdown--;
                if (countdownEl) {
                    countdownEl.textContent = countdown;
                }
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    location.reload();
                }
            }, 1000);
            
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function closeViewAdvisoryModal() {
    const modal = document.getElementById('viewAdvisoryModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    
    // Reset
    document.getElementById('selectAllStudentsPromotion').checked = false;
    document.getElementById('bulkGradeSelect').value = '';
    currentAdvisoryGrade = null;
}

function loadFilteredData() {
    const teacherRole = document.getElementById('filterTeacherRole').value;
    const gradeLevel = document.getElementById('filterGrade').value;
    const dateFilter = document.getElementById('filterDate').value;
    const search = document.getElementById('searchInput').value;
    const sortName = document.getElementById('sortName').value;
    
    const formData = new FormData();
    formData.append('action', 'get_filtered_data');
    formData.append('teacher_role', teacherRole);
    formData.append('grade_level', gradeLevel);
    formData.append('date_filter', dateFilter);
    formData.append('search', search);
    formData.append('sort_by', 'student_name');
    formData.append('sort_order', sortName);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            allData = data.data;
            displayTableData(data.data);
        }
    })
    .catch(error => console.error('Error:', error));
}

function displayTableData(data) {
    const tbody = document.getElementById('tableBody');
    const resultCount = document.getElementById('resultCount');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fa-solid fa-inbox text-5xl mb-4 opacity-20"></i>
                        <p class="text-base font-semibold text-gray-600 mb-1">No Assignments Found</p>
                        <p class="text-sm text-gray-500">Try adjusting your filters or assign students to advisory classes</p>
                    </div>
                </td>
            </tr>
        `;
        resultCount.textContent = 'Showing 0 Results';
        renderPagination(0);
        return;
    }
    
    const paginatedData = getPaginatedData(data);
    
    let html = '';
    paginatedData.forEach(row => {
        const gradeColors = {
            '7': 'bg-blue-100 text-blue-800',
            '8': 'bg-orange-100 text-orange-800',
            '9': 'bg-purple-100 text-purple-800',
            '10': 'bg-green-100 text-green-800'
        };
        
        const gradeClass = gradeColors[row.grade_level] || 'bg-gray-100 text-gray-800';
        
        html += `
            <tr class="hover:bg-gray-50 transition">
                <td class="py-4 px-6">
                    <div class="text-sm font-medium text-gray-900">${escapeHtml(row.student_name)}</div>
                    <div class="text-xs text-gray-500">Grade ${row.grade_level}</div>
                </td>
                <td class="py-4 px-6 text-sm text-gray-600">${escapeHtml(row.lrn || 'N/A')}</td>
                <td class="py-4 px-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${gradeClass}">
                        Grade ${row.grade_level}
                    </span>
                </td>
                <td class="py-4 px-6 text-sm text-gray-900">${escapeHtml(row.teacher_name)}</td>
                <td class="py-4 px-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                        ${escapeHtml(row.advisory_name)}
                    </span>
                </td>
                <td class="py-4 px-6 text-sm text-gray-600">${formatDate(row.assigned_date)}</td>
                <td class="py-4 px-6">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="openReassignModal(${row.assignment_id}, '${escapeHtml(row.student_name)}', ${row.grade_level})" 
                            class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-xs font-bold hover:bg-blue-600 transition" 
                            title="Reassign to different advisory">
                            <i class="fa-solid fa-right-left"></i>
                        </button>
                        <button onclick="openRemoveAdvisoryModal(${row.assignment_id}, '${escapeHtml(row.student_name)}')" 
                            class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-bold hover:bg-red-600 transition" 
                            title="Remove from advisory">
                            <i class="fa-solid fa-user-slash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE + 1;
    const endIndex = Math.min(currentPage * ITEMS_PER_PAGE, data.length);
    resultCount.textContent = `Showing ${startIndex}-${endIndex} of ${data.length} Result${data.length !== 1 ? 's' : ''}`;
    
    renderPagination(data.length);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

window.onclick = function(event) {
    if (event.target.id === 'studentModal') closeStudentModal();
    if (event.target.id === 'teacherModal') closeTeacherModal();
    if (event.target.id === 'reassignModal') closeReassignModal();
    if (event.target.id === 'removeAdvisoryModal') closeRemoveAdvisoryModal();
    if (event.target.id === 'convertTeacherModal') closeConvertTeacherModal();
    if (event.target.id === 'viewAdvisoryModal') closeViewAdvisoryModal();
    if (event.target.id === 'promotionConfirmModal') closePromotionConfirm();
}

document.addEventListener('DOMContentLoaded', function() {
    checkAdvisoryAvailability();
});

function checkAdvisoryAvailability() {
    const advisorySelect = document.getElementById('modalAdvisoryTeacher');
    const hasAdvisoryTeachers = advisorySelect.options.length > 1;
    const assignStudentBtn = document.getElementById('assignStudentBtn');
    const noAdvisoryMessage = document.getElementById('noAdvisoryMessage');
    
    if (!hasAdvisoryTeachers) {
        assignStudentBtn.classList.add('hidden');
        noAdvisoryMessage.classList.remove('hidden');
    } else {
        assignStudentBtn.classList.remove('hidden');
        noAdvisoryMessage.classList.add('hidden');
    }
}

// Add CSS animation for modal
const style = document.createElement('style');
style.textContent = `
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    .animate-scale-in {
        animation: scaleIn 0.3s ease-out;
    }
`;
document.head.appendChild(style);