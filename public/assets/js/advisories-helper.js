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
}

function closeStudentModal() {
    document.getElementById('studentModal').classList.add('hidden');
    document.getElementById('studentModal').classList.remove('flex');
    document.getElementById('assignStudentsForm').reset();
    document.getElementById('selectAdvisoryMessage').classList.remove('hidden');
    document.getElementById('studentListContainer').classList.add('hidden');
    document.getElementById('advisoryCapacityInfo').classList.add('hidden');
}

function updateStudentList() {
    const advisoryId = document.getElementById('modalAdvisoryTeacher').value;
    const selectMessage = document.getElementById('selectAdvisoryMessage');
    const loadingDiv = document.getElementById('loadingStudents');
    const listContainer = document.getElementById('studentListContainer');
    const capacityInfo = document.getElementById('advisoryCapacityInfo');
    
    if (!advisoryId) {
        selectMessage.classList.remove('hidden');
        listContainer.classList.add('hidden');
        loadingDiv.classList.add('hidden');
        capacityInfo.classList.add('hidden');
        return;
    }
    
    // Get current student count from selected option
    const selectedOption = document.getElementById('modalAdvisoryTeacher').selectedOptions[0];
    const currentCount = parseInt(selectedOption.getAttribute('data-current-count')) || 0;
    
    // Update capacity info
    document.getElementById('currentStudentCount').textContent = currentCount;
    document.getElementById('remainingSlots').textContent = (40 - currentCount);
    capacityInfo.classList.remove('hidden');
    
    selectMessage.classList.add('hidden');
    loadingDiv.classList.remove('hidden');
    listContainer.classList.add('hidden');
    
    const formData = new FormData();
    formData.append('action', 'get_unassigned_students');
    formData.append('advisory_id', advisoryId);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        loadingDiv.classList.add('hidden');
        
        if (data.success) {
            const tbody = document.getElementById('modalStudentTable');
            
            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fa-solid fa-user-check text-4xl mb-3 opacity-20"></i>
                                <p class="text-sm font-medium text-gray-600">No Unassigned Students Available</p>
                                <p class="text-xs text-gray-500 mt-1">All students are already assigned to advisory classes.</p>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                let html = '';
                data.data.forEach(student => {
                    html += `
                        <tr class="student-row hover:bg-gray-50 transition" data-grade="${student.grade_level}" data-student-id="${student.user_id}">
                            <td class="py-3 px-6">
                                <input type="checkbox" name="student_ids[]" value="${student.user_id}" class="student-checkbox w-4 h-4 text-[#043915] border-gray-300 rounded focus:ring-[#043915]">
                            </td>
                            <td class="py-3 px-6">
                                <div class="text-sm font-medium text-gray-900">${escapeHtml(student.name)}</div>
                                <div class="text-xs text-gray-500">Grade ${student.grade_level}</div>
                            </td>
                            <td class="py-3 px-6 text-sm text-gray-600">${escapeHtml(student.lrn)}</td>
                            <td class="py-3 px-6 text-sm grade-display">Grade ${student.grade_level}</td>
                            <td class="py-3 px-6 text-center">
                                <select name="grade_levels[${student.user_id}]" class="grade-select border border-gray-200 rounded-lg px-3 py-1 text-xs focus:ring-2 focus:ring-[#043915]" onchange="updateGradeDisplay(this)">
                                    <option value="7" ${student.grade_level == '7' ? 'selected' : ''}>Grade 7</option>
                                    <option value="8" ${student.grade_level == '8' ? 'selected' : ''}>Grade 8</option>
                                    <option value="9" ${student.grade_level == '9' ? 'selected' : ''}>Grade 9</option>
                                    <option value="10" ${student.grade_level == '10' ? 'selected' : ''}>Grade 10</option>
                                </select>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }
            
            listContainer.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loadingDiv.classList.add('hidden');
        showToast('Error loading students', 'error');
    });
}

function openReassignModal(assignmentId, studentName, currentGrade) {
    document.getElementById('reassignAssignmentId').value = assignmentId;
    document.getElementById('reassignStudentName').textContent = studentName;
    document.getElementById('reassignGradeSelect').value = currentGrade;
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

function toggleAllStudents(checked) {
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.checked = checked;
    });
}

function selectByGrade(grade) {
    toggleAllStudents(false);
    document.querySelectorAll('.student-row').forEach(row => {
        if(row.getAttribute('data-grade') === grade) {
            row.querySelector('.student-checkbox').checked = true;
        }
    });
}

function updateGradeDisplay(selectElement) {
    const row = selectElement.closest('tr');
    const gradeDisplay = row.querySelector('.grade-display');
    const newGrade = selectElement.value;
    
    gradeDisplay.textContent = 'Grade ' + newGrade;
    row.setAttribute('data-grade', newGrade);
}

function confirmStudentAssignment() {
    const advisoryId = document.getElementById('modalAdvisoryTeacher').value;
    const selectedStudents = document.querySelectorAll('.student-checkbox:checked');
    
    if (!advisoryId) {
        showToast('Please select an advisory teacher first.', 'error');
        return;
    }
    
    const studentTable = document.getElementById('modalStudentTable');
    const hasStudents = studentTable.querySelector('tr:not([colspan])') !== null;
    
    if (!hasStudents) {
        showToast('No unassigned students available. All students are already assigned to advisory classes.', 'error');
        return;
    }
    
    if (selectedStudents.length === 0) {
        showToast('Please select at least one student.', 'error');
        return;
    }
    
    // Check advisory capacity
    const selectedOption = document.getElementById('modalAdvisoryTeacher').selectedOptions[0];
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
            loadFilteredData();
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
    
    const teacherRole = document.getElementById('filterTeacherRole').value;
    
    if (teacherRole === 'advisory') {
        loadAdvisoryList();
    } else {
        loadFilteredData();
    }
}

function resetFilters() {
    document.getElementById('filterForm').reset();
    document.getElementById('searchInput').value = '';
    
    showToast('Filters have been reset', 'success');
    
    // Reset to default empty state
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
}

let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        const teacherRole = document.getElementById('filterTeacherRole').value;
        if (teacherRole === 'advisory') {
            loadAdvisoryList();
        } else {
            loadFilteredData();
        }
    }, 500);
});

function loadAdvisoryList() {
    const search = document.getElementById('searchInput').value;
    
    const formData = new FormData();
    formData.append('action', 'get_advisory_list');
    
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
        return;
    }
    
    let html = '';
    advisories.forEach(advisory => {
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
                    <button onclick="viewAdvisoryDetails(${advisory.advisory_id})" 
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
    resultCount.textContent = `Showing ${advisories.length} Advisory Class${advisories.length !== 1 ? 'es' : ''}`;
}

function viewAdvisoryDetails(advisoryId) {
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
            displayAdvisoryStudents(data.data, advisoryId);
        }
    })
    .catch(error => console.error('Error:', error));
}

function displayAdvisoryStudents(students, advisoryId) {
    const modal = document.getElementById('viewAdvisoryModal');
    const studentsList = document.getElementById('advisoryStudentsList');
    
    // Update title
    if (students.length > 0) {
        document.getElementById('advisoryDetailTitle').textContent = 'Advisory Students';
        document.getElementById('advisoryDetailSubtitle').textContent = `${students.length}/40 Students`;
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
                    <div class="flex justify-between items-start mb-2">
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

function closeViewAdvisoryModal() {
    document.getElementById('viewAdvisoryModal').classList.add('hidden');
    document.getElementById('viewAdvisoryModal').classList.remove('flex');
}

function loadFilteredData() {
    const teacherRole = document.getElementById('filterTeacherRole').value;
    const gradeLevel = document.getElementById('filterGrade').value;
    const dateFilter = document.getElementById('filterDate').value;
    const search = document.getElementById('searchInput').value;
    
    const formData = new FormData();
    formData.append('action', 'get_filtered_data');
    formData.append('teacher_role', teacherRole);
    formData.append('grade_level', gradeLevel);
    formData.append('date_filter', dateFilter);
    formData.append('search', search);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
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
        return;
    }
    
    let html = '';
    data.forEach(row => {
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
    resultCount.textContent = `Showing ${data.length} Result${data.length !== 1 ? 's' : ''}`;
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