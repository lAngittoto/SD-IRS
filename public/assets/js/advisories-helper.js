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

function openStudentModal() {
    const advisorySelect = document.getElementById('modalAdvisoryTeacher');
    const hasAdvisoryTeachers = advisorySelect.options.length > 1; 
    
    if (!hasAdvisoryTeachers) {
        alert('Please assign an advisory teacher first before assigning students.');
        return;
    }
    
    document.getElementById('studentModal').classList.remove('hidden');
    document.getElementById('studentModal').classList.add('flex');
}

function closeStudentModal() {
    document.getElementById('studentModal').classList.add('hidden');
    document.getElementById('studentModal').classList.remove('flex');
    document.getElementById('assignStudentsForm').reset();
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
        alert('Please select an advisory teacher first.');
        return;
    }
    
    const hasStudents = document.querySelectorAll('.student-checkbox').length > 0;
    if (!hasStudents) {
        alert('No unassigned students available. All students are already assigned to advisory classes.');
        return;
    }
    
    if (selectedStudents.length === 0) {
        alert('Please select at least one student.');
        return;
    }
    
    document.getElementById('hiddenAdvisoryId').value = advisoryId;
    document.getElementById('assignStudentsForm').submit();
}

function applyFilters(event) {
    event.preventDefault();
    loadFilteredData();
}

function resetFilters() {
    document.getElementById('filterForm').reset();
    document.getElementById('searchInput').value = '';
    loadFilteredData();
}

let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        loadFilteredData();
    }, 500);
});

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