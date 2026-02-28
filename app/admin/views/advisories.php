<?php
ob_start();

$teachers         = $advisoriesController->getAllTeachers();
$advisoryTeachers = $advisoriesController->getAdvisoryTeachers();
$allStudents      = $advisoriesController->getAllStudents();

// Get active school year for display
$activeSchoolYear = $advisoriesController->getActiveSchoolYear();
?>

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-5 right-5 z-[300] flex flex-col gap-3 pointer-events-none"></div>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>

    <!-- Page Header -->
    <div class="mb-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-emerald-100 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fas fa-chalkboard-user text-emerald-700 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900" id="pageTitle">Advisory Class Management</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Manage advisory teachers and student assignments</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                <button onclick="openTeacherModal()"
                    class="inline-flex items-center gap-2 bg-[#043915] text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#055020] transition-colors shadow-sm">
                    <i class="fas fa-user-plus"></i> Assign Teacher
                </button>
                <button onclick="openStudentModal()" id="assignStudentBtn"
                    class="inline-flex items-center gap-2 bg-[#f8c922] text-[#043915] px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-yellow-300 transition-colors shadow-sm">
                    <i class="fas fa-users"></i> Assign Students
                </button>
                <p id="noAdvisoryMessage" class="hidden text-xs text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2.5 rounded-xl font-medium">
                    <i class="fas fa-info-circle"></i> Assign a teacher first
                </p>
            </div>
        </div>
    </div>

    <!-- School Year Info Bar -->
    <?php if ($activeSchoolYear): ?>
    <div class="mb-5 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border border-blue-200 shadow-sm p-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <i class="fas fa-calendar-check text-blue-600 text-sm"></i>
            <div>
                <p class="text-xs font-bold text-blue-600">S.Y. <?= $activeSchoolYear['start_year'] ?> – <?= $activeSchoolYear['end_year'] ?></p>
            </div>
            <span class="px-2 py-1 bg-emerald-600 text-white text-xs font-bold rounded">ACTIVE</span>
        </div>
        <button onclick="openSchoolYearModal(<?= $activeSchoolYear['school_year_id'] ?>, <?= $activeSchoolYear['start_year'] ?>, <?= $activeSchoolYear['end_year'] ?>)"
            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition-colors flex items-center gap-1">
            <i class="fas fa-edit"></i> Edit
        </button>
    </div>
    <?php endif; ?>

    <!-- Filters Bar -->
    <div class="mb-6 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 overflow-x-auto">
        <div class="flex flex-wrap items-end gap-4 min-w-max md:min-w-0">
            <div class="min-w-[200px]">
                <label class="block text-xs font-bold text-black mb-2"><i class="fas fa-search text-gray-400 mr-2"></i>Search</label>
                <input type="text" id="searchInput" placeholder="Student, LRN, teacher…"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">
            </div>
            
            <div class="min-w-[150px]">
                <label class="block text-xs font-bold text-black mb-2"><i class="fas fa-user-tie text-gray-400 mr-2"></i>Teacher Type</label>
                <select id="filterTeacherRole"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">
                    <option value="">All Types</option>
                    <option value="advisory">Advisory</option>
                    <option value="subject">Subject</option>
                </select>
            </div>

            <div class="min-w-[120px]">
                <label class="block text-xs font-bold text-black mb-2"><i class="fas fa-layer-group text-gray-400 mr-2"></i>Grade</label>
                <select id="filterGrade"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">
                    <option value="">All Grades</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                </select>
            </div>

            <div class="min-w-[110px]">
                <label class="block text-xs font-bold text-black mb-2"><i class="fas fa-arrow-up-down text-gray-400 mr-2"></i>Sort</label>
                <select id="sortName"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">
                    <option value="ASC">A to Z</option>
                    <option value="DESC">Z to A</option>
                </select>
            </div>

            <div class="min-w-[140px]">
                <label class="block text-xs font-bold text-black mb-2"><i class="fas fa-calendar text-gray-400 mr-2"></i>Date</label>
                <input type="date" id="filterDate"
                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/30">
            </div>

            <button onclick="resetFilters()"
                class="px-4 py-2.5 bg-red-500 text-white rounded-xl text-sm font-bold hover:bg-red-600 transition-colors flex items-center gap-2 h-[42px]">
                <i class="fas fa-redo text-sm"></i> Reset
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col" style="height: 600px;">
        
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3 shrink-0">
            <p id="resultCount" class="text-sm font-bold text-black"><i class="fas fa-database text-gray-400 mr-2"></i>Loading records…</p>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto overflow-y-auto flex-1">
            <table class="w-full text-sm" id="mainTable">
                <thead class="sticky top-0 z-10" id="mainTableHead">
                    <tr class="bg-[#043915] text-white" id="defaultHead">
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-graduation-cap mr-2"></i>Student</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-barcode mr-2"></i>LRN</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-book mr-2"></i>Grade</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-person-chalkboard mr-2"></i>Teacher</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-door-open mr-2"></i>Class</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-clock mr-2"></i>Date</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-cogs mr-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="7" class="py-20 text-center">
                            <p class="text-base text-gray-700"><i class="fas fa-spinner fa-spin mr-2"></i>Loading data…</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-5 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-4">
        <p id="paginationInfo" class="text-sm font-semibold text-gray-700"><i class="fas fa-list mr-2"></i>Loading...</p>
        <div id="paginationContainer" class="flex items-center gap-2"></div>
    </div>
</main>

<!-- ================================================================ -->
<!-- SCHOOL YEAR EDIT MODAL                                            -->
<!-- ================================================================ -->
<div id="schoolYearModal" class="fixed inset-0 z-[150] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-8 pt-7 pb-5 border-b border-gray-200">
            <h2 class="text-xl font-bold text-black flex items-center gap-3"><i class="fas fa-calendar-alt text-blue-600"></i>Edit School Year</h2>
            <p class="text-sm text-gray-500 mt-0.5">Update the active school year dates</p>
        </div>
        <form id="schoolYearForm" onsubmit="submitSchoolYearUpdate(event)" class="p-7 space-y-5">
            <input type="hidden" name="action" value="update_school_year">
            <input type="hidden" id="schoolYearId" name="school_year_id">
            
            <div>
                <label class="block text-sm font-bold text-black mb-2"><i class="fas fa-calendar text-blue-600 mr-2"></i>Start Year</label>
                <input type="number" id="startYearInput" name="start_year" min="2000" max="2100" required
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all text-black font-bold">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-black mb-2"><i class="fas fa-calendar text-blue-600 mr-2"></i>End Year</label>
                <input type="number" id="endYearInput" name="end_year" min="2000" max="2100" required
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all text-black font-bold">
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-2"></i>The end year must be greater than the start year.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeSchoolYearModal()" class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">Cancel</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors text-base flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- STUDENT PROFILE MODAL (Read-only view + Edit mode)               -->
<!-- ================================================================ -->
<div id="studentProfileModal" class="fixed inset-0 z-[150] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-3">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="px-7 pt-6 pb-4 border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-lg font-bold text-black flex items-center gap-2"><i class="fas fa-user-graduate text-blue-600"></i><span id="profileModalTitle">Student Profile</span></h2>
                <p class="text-xs text-gray-500 mt-1" id="profileModalSubtitle">Viewing student information</p>
            </div>
            <button onclick="closeStudentProfileModal()"
                class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition shrink-0 text-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Loading -->
        <div id="profileLoadingState" class="hidden flex-1 flex items-center justify-center py-16">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-3"></i>
                <p class="text-sm text-gray-700">Loading…</p>
            </div>
        </div>

        <!-- Form Content -->
        <div id="profileFormContent" class="flex-1 overflow-y-auto px-7 py-5 space-y-4">
            <input type="hidden" id="profileStudentId">
            <input type="hidden" id="originalProfilePix" value="">
            <input type="hidden" id="profileEditMode" value="0">

            <!-- Profile Picture - Bigger -->
            <div class="flex justify-center mb-2">
                <div class="w-32 h-32 bg-gray-100 rounded-full border-3 border-blue-200 flex items-center justify-center overflow-hidden relative shadow-md">
                    <i class="fa-solid fa-user-graduate text-5xl text-gray-300" id="profileAvatarIcon"></i>
                    <img id="profileAvatarImg" src="" alt="" class="hidden w-full h-full object-cover">
                    <div id="profilePicOverlay" class="hidden absolute inset-0 bg-black/40 flex items-center justify-center cursor-pointer rounded-full" onclick="document.getElementById('profilePictureInput').click()">
                        <i class="fas fa-camera text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            <input type="file" id="profilePictureInput" class="hidden" accept="image/jpeg,image/png,image/webp" onchange="handleProfilePictureChange(event)">

            <!-- Name Fields -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">First Name</label>
                    <input type="text" id="profileFirstName" placeholder="First Name" readonly
                        class="profile-field w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-black outline-none focus:border-[#043915] focus:ring-1 focus:ring-[#043915]/30 focus:bg-white transition cursor-default">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Last Name</label>
                    <input type="text" id="profileLastName" placeholder="Last Name" readonly
                        class="profile-field w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-black outline-none focus:border-[#043915] focus:ring-1 focus:ring-[#043915]/30 focus:bg-white transition cursor-default">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">M.I.</label>
                    <input type="text" id="profileMI" maxlength="3" placeholder="M.I." readonly
                        class="profile-field w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-black outline-none focus:border-[#043915] focus:ring-1 focus:ring-[#043915]/30 focus:bg-white transition cursor-default">
                </div>
            </div>

            <!-- LRN + Contact -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">LRN</label>
                    <input type="text" id="profileLrn" maxlength="12" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="100123456789" readonly
                        class="profile-field w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-black outline-none focus:border-[#043915] focus:ring-1 focus:ring-[#043915]/30 focus:bg-white transition font-mono cursor-default">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Contact</label>
                    <input type="text" id="profileContact" maxlength="11" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="09XXXXXXXXX" readonly
                        class="profile-field w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-black outline-none focus:border-[#043915] focus:ring-1 focus:ring-[#043915]/30 focus:bg-white transition font-mono cursor-default">
                </div>
            </div>

            <!-- Address + Guardian -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Home Address</label>
                    <input type="text" id="profileAddress" placeholder="Street, Barangay, City" readonly
                        class="profile-field w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-black outline-none focus:border-[#043915] focus:ring-1 focus:ring-[#043915]/30 focus:bg-white transition cursor-default">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Parent/Guardian</label>
                    <input type="text" id="profileGuardianName" placeholder="Maria Santos" readonly
                        class="profile-field w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-black outline-none focus:border-[#043915] focus:ring-1 focus:ring-[#043915]/30 focus:bg-white transition cursor-default">
                </div>
            </div>

            <!-- Guardian Contact -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Guardian Contact</label>
                <input type="text" id="profileGuardianContact" maxlength="11" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="09XXXXXXXXX" readonly
                    class="profile-field w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm text-black outline-none focus:border-[#043915] focus:ring-1 focus:ring-[#043915]/30 focus:bg-white transition font-mono cursor-default">
            </div>

            <!-- Class Info (Always Read Only) -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 grid grid-cols-3 gap-3">
                <div>
                    <p class="text-xs font-bold text-gray-500 mb-1">Year Level</p>
                    <p id="profileYearLevel" class="text-sm font-bold text-gray-900">—</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 mb-1">Section</p>
                    <p id="profileSection" class="text-sm font-bold text-gray-900">—</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 mb-1">Adviser</p>
                    <p id="profileAdviser" class="text-sm font-bold text-gray-900">—</p>
                </div>
            </div>

            <!-- History Button -->
            <button onclick="openStudentHistoryModal(document.getElementById('profileStudentId').value)" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                <i class="fas fa-history"></i> History
            </button>
        </div>

        <!-- Footer -->
        <div class="px-7 py-4 border-t border-gray-200 flex gap-2 shrink-0">
            <div id="profileViewButtons" class="flex gap-2 w-full">
                <button onclick="enableProfileEditMode()" class="flex-1 bg-[#043915] hover:bg-[#055020] text-white font-bold py-2 rounded-lg transition-colors text-sm">Update</button>
                <button onclick="closeStudentProfileModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-black font-bold py-2 rounded-lg transition-colors text-sm">Close</button>
            </div>
            <div id="profileEditButtons" class="hidden flex gap-2 w-full">
                <button onclick="saveStudentProfile()" class="flex-1 bg-[#043915] hover:bg-[#055020] text-white font-bold py-2 rounded-lg transition-colors text-sm">Save</button>
                <button onclick="cancelProfileEdit()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-black font-bold py-2 rounded-lg transition-colors text-sm">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- TEACHER PROFILE MODAL (Read-only + Edit mode)                    -->
<!-- ================================================================ -->
<div id="teacherProfileModal" class="fixed inset-0 z-[150] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-2">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="px-8 pt-7 pb-5 border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-xl font-bold text-black flex items-center gap-3"><i class="fas fa-user-tie text-purple-600"></i><span id="teacherModalTitle">Faculty Profile</span></h2>
                <p class="text-sm text-gray-500 mt-0.5" id="teacherModalSubtitle">Viewing teacher information</p>
            </div>
            <button onclick="closeTeacherProfileModal()"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition shrink-0 text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Loading -->
        <div id="teacherLoadingState" class="hidden flex-1 flex items-center justify-center py-20">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-base text-gray-700">Loading teacher info…</p>
            </div>
        </div>

        <!-- Form -->
        <div id="teacherFormContent" class="flex-1 overflow-y-auto px-8 py-6 space-y-5">
            <input type="hidden" id="teacherRecordId">
            <input type="hidden" id="originalTeacherPix" value="">
            <input type="hidden" id="teacherEditMode" value="0">

            <!-- Profile Picture -->
            <div class="flex justify-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full border-2 border-gray-200 flex items-center justify-center overflow-hidden relative" id="teacherPicWrapper">
                    <i class="fa-solid fa-user-tie text-4xl text-gray-300" id="teacherAvatarIcon"></i>
                    <img id="teacherAvatarImg" src="" alt="" class="hidden w-full h-full object-cover">
                    <div id="teacherPicOverlay" class="hidden absolute inset-0 bg-black/40 flex items-center justify-center cursor-pointer rounded-full"
                        onclick="document.getElementById('teacherPictureInput').click()">
                        <i class="fas fa-camera text-white text-xl"></i>
                    </div>
                </div>
            </div>
            <input type="file" id="teacherPictureInput" class="hidden" accept="image/jpeg,image/png,image/webp" onchange="handleTeacherPictureChange(event)">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1"><i class="fas fa-id-card mr-1"></i>Teacher Number</label>
                    <input type="text" id="teacherIdField" placeholder="T-2024-001" readonly
                        class="teacher-field w-full border-2 border-gray-200 bg-gray-50 rounded-xl px-3 py-2.5 text-sm text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 focus:bg-white transition font-mono cursor-default">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1"><i class="fas fa-building mr-1"></i>Department</label>
                    <select id="teacherDeptField" disabled
                        class="teacher-field w-full border-2 border-gray-200 bg-gray-50 rounded-xl px-3 py-2.5 text-sm text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 focus:bg-white transition cursor-default">
                        <option value="">Select Department</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Science">Science</option>
                        <option value="English">English</option>
                        <option value="Filipino">Filipino</option>
                        <option value="MAPEH">MAPEH</option>
                        <option value="ICT">ICT</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><i class="fas fa-person-chalkboard mr-1"></i>Professional Name</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="text" id="teacherFirstName" placeholder="First Name" readonly
                        class="teacher-field border-2 border-gray-200 bg-gray-50 rounded-xl px-3 py-2.5 text-sm text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 focus:bg-white transition cursor-default">
                    <input type="text" id="teacherLastName" placeholder="Last Name" readonly
                        class="teacher-field border-2 border-gray-200 bg-gray-50 rounded-xl px-3 py-2.5 text-sm text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 focus:bg-white transition cursor-default">
                    <input type="text" id="teacherSuffix" placeholder="e.g. LPT, PhD" readonly
                        class="teacher-field border-2 border-gray-200 bg-gray-50 rounded-xl px-3 py-2.5 text-sm text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 focus:bg-white transition cursor-default">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1"><i class="fas fa-envelope mr-1"></i>Email</label>
                    <input type="email" id="teacherEmail" placeholder="teacher@school.edu.ph" readonly
                        class="teacher-field w-full border-2 border-gray-200 bg-gray-50 rounded-xl px-3 py-2.5 text-sm text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 focus:bg-white transition cursor-default">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1"><i class="fas fa-phone mr-1"></i>Contact</label>
                    <input type="text" id="teacherContact" maxlength="11" placeholder="09XXXXXXXXX" readonly
                        class="teacher-field w-full border-2 border-gray-200 bg-gray-50 rounded-xl px-3 py-2.5 text-sm text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 focus:bg-white transition font-mono cursor-default">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1"><i class="fas fa-star mr-1"></i>Specialization</label>
                <textarea id="teacherSpecialization" rows="2" placeholder="e.g. Advanced Algebra, Physics, Robotics" readonly
                    class="teacher-field w-full border-2 border-gray-200 bg-gray-50 rounded-xl px-3 py-2.5 text-sm text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 focus:bg-white transition resize-none cursor-default"></textarea>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-5 border-t border-gray-200 flex gap-3 shrink-0">
            <div id="teacherViewButtons" class="flex gap-3 w-full">
                <button onclick="enableTeacherEditMode()"
                    class="flex-1 bg-[#1e1b4b] hover:bg-[#2e2a75] text-white font-bold py-3 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-edit"></i> Update
                </button>
                <button onclick="closeTeacherProfileModal()"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-black font-bold py-3 rounded-xl transition-colors text-sm">
                    Close
                </button>
            </div>
            <div id="teacherEditButtons" class="hidden flex gap-3 w-full">
                <button onclick="saveTeacherProfile()"
                    class="flex-1 bg-[#1e1b4b] hover:bg-[#2e2a75] text-white font-bold py-3 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button onclick="cancelTeacherEdit()"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-black font-bold py-3 rounded-xl transition-colors text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- ASSIGN STUDENTS MODAL                                             -->
<!-- ================================================================ -->
<div id="studentModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-6xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

        <div class="px-8 py-6 border-b border-gray-200 flex items-center justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-black flex items-center gap-3"><i class="fas fa-user-group text-orange-600"></i>Assign Students</h2>
                <p class="text-sm text-gray-600 mt-1">Select students and assign them to an advisory class</p>
            </div>
            <button onclick="closeStudentModal()"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition-colors text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5">

            <!-- School Year Info -->
            <?php if ($activeSchoolYear): ?>
            <div class="bg-emerald-50 border-2 border-emerald-300 rounded-xl px-5 py-3 flex items-center gap-3">
                <i class="fas fa-calendar-alt text-emerald-600 text-lg"></i>
                <p class="text-sm text-emerald-900 font-semibold">
                    School Year: <span class="font-black text-emerald-800"><?= $activeSchoolYear['start_year'] ?> - <?= $activeSchoolYear['end_year'] ?></span>
                    <span class="ml-2 px-2 py-0.5 bg-emerald-600 text-white text-xs rounded-full font-bold">ACTIVE</span>
                </p>
            </div>
            <?php else: ?>
            <div class="bg-amber-50 border-2 border-amber-400 rounded-xl px-5 py-3 flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-amber-600 text-lg"></i>
                <p class="text-sm text-amber-900 font-semibold">No active school year found. Please set an active school year first.</p>
            </div>
            <?php endif; ?>

            <!-- Advisory Teacher -->
            <div>
                <label class="block text-sm font-bold text-black uppercase tracking-wider mb-2"><i class="fas fa-person-chalkboard text-orange-600 mr-2"></i>Advisory Teacher</label>
                <select id="modalAdvisoryTeacher" class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                    <option value="">— Choose an advisory teacher —</option>
                    <?php foreach ($advisoryTeachers as $teacher): ?>
                        <option value="<?= $teacher['advisory_id'] ?>" data-current-count="<?= $teacher['student_count'] ?? 0 ?>" data-grade-level="<?= $teacher['grade_level'] ?>">
                            <?= htmlspecialchars($teacher['teacher_name']) ?> — <?= htmlspecialchars($teacher['advisory_name']) ?> (Grade <?= $teacher['grade_level'] ?>, <?= $teacher['student_count'] ?? 0 ?>/40)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Capacity Info -->
            <div id="advisoryCapacityInfo" class="hidden bg-sky-50 border-2 border-sky-300 rounded-xl px-5 py-3 flex items-center gap-3">
                <i class="fas fa-info-circle text-sky-600 text-lg"></i>
                <p class="text-sm text-sky-900 font-medium">Grade <span id="advisoryGradeLevel" class="font-bold">—</span> · <span id="currentStudentCount" class="font-bold">0</span> assigned · <span id="remainingSlots" class="font-bold">40</span> slots remaining</p>
            </div>

            <!-- Grade Filter -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-black"><i class="fas fa-filter mr-2"></i>Filter:</span>
                <button onclick="filterModalStudents('all')" id="gradeTab_all" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-200 text-black hover:bg-gray-300 transition-colors ring-2 ring-[#043915]"><i class="fas fa-asterisk mr-1"></i>All</button>
                <button onclick="filterModalStudents('7')" id="gradeTab_7" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-100 text-blue-900 hover:bg-blue-200 transition-colors">Grade 7</button>
                <button onclick="filterModalStudents('8')" id="gradeTab_8" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-orange-100 text-orange-900 hover:bg-orange-200 transition-colors">Grade 8</button>
                <button onclick="filterModalStudents('9')" id="gradeTab_9" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-100 text-purple-900 hover:bg-purple-200 transition-colors">Grade 9</button>
                <button onclick="filterModalStudents('10')" id="gradeTab_10" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-green-100 text-green-900 hover:bg-green-200 transition-colors">Grade 10</button>
                <div class="ml-auto flex items-center gap-2">
                    <button onclick="toggleAllVisibleStudents(true)" class="px-3 py-1.5 bg-[#043915] text-white text-xs font-bold rounded-lg hover:bg-[#055020] transition-colors flex items-center gap-1"><i class="fas fa-check-square"></i>Select All</button>
                    <button onclick="toggleAllVisibleStudents(false)" class="px-3 py-1.5 border-2 border-red-500 text-red-600 text-xs font-bold rounded-lg hover:bg-red-50 transition-colors flex items-center gap-1"><i class="fas fa-times-circle"></i>Clear</button>
                </div>
            </div>

            <!-- Student Table -->
            <div class="rounded-xl border-2 border-gray-300 overflow-hidden" style="max-height: 340px; overflow-y: auto;">
                <form id="assignStudentsForm" method="POST">
                    <input type="hidden" name="action" value="assign_students">
                    <input type="hidden" name="advisory_id" id="hiddenAdvisoryId" value="">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-gray-100 z-10">
                            <tr class="text-xs text-black font-bold uppercase">
                                <th class="py-3 px-4 border-b-2 border-gray-300 text-left">Select</th>
                                <th class="py-3 px-4 border-b-2 border-gray-300 text-left">Student Name</th>
                                <th class="py-3 px-4 border-b-2 border-gray-300 text-left">LRN</th>
                                <th class="py-3 px-4 border-b-2 border-gray-300 text-left">Grade</th>
                                <th class="py-3 px-4 border-b-2 border-gray-300 text-left">Change Grade</th>
                            </tr>
                        </thead>
                        <tbody id="modalStudentTable" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-3"></i>
                                    <span class="text-sm text-gray-700 font-medium block mt-2">Loading students…</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <div class="px-8 py-5 border-t border-gray-200 flex gap-4 shrink-0">
            <button onclick="closeStudentModal()" class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-sm flex items-center justify-center gap-2">
                <i class="fas fa-times-circle"></i> Cancel
            </button>
            <button onclick="confirmStudentAssignment()" class="flex-1 bg-[#f8c922] text-[#043915] font-bold py-3 rounded-xl hover:bg-yellow-300 transition-colors shadow-lg text-sm flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> Confirm Assignment
            </button>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- ASSIGN TEACHER MODAL                                              -->
<!-- ================================================================ -->
<div id="teacherModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">

        <div class="bg-[#043915] px-8 py-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2"><i class="fas fa-user-plus"></i>Assign Teacher Role</h2>
            <p class="text-sm text-green-300 mt-1"><i class="fas fa-info-circle mr-1"></i>Add a teacher as advisory or subject teacher</p>
        </div>

        <form id="assignTeacherForm" onsubmit="submitTeacherAssignment(event)" class="p-7 space-y-5">
            <input type="hidden" name="action" value="assign_teacher">

            <div>
                <label class="block text-sm font-bold text-black mb-2"><i class="fas fa-user-tie text-green-600 mr-2"></i>Teacher</label>
                <select name="teacher_id" id="teacherSelect" required class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                    <option value="">Select a teacher…</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= $teacher['user_id'] ?>"><?= htmlspecialchars($teacher['name']) ?> — <?= htmlspecialchars($teacher['email']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-black mb-2"><i class="fas fa-briefcase text-green-600 mr-2"></i>Role</label>
                <select name="role_type" id="teacherRoleType" required class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium" onchange="toggleAdvisoryFields()">
                    <option value="">Select role…</option>
                    <option value="subject">Subject Teacher</option>
                    <option value="advisory">Advisory Teacher</option>
                </select>
            </div>

            <div id="advisoryFields" class="hidden bg-emerald-50 border-2 border-emerald-400 rounded-2xl p-5 space-y-4">
                <p class="text-sm font-bold text-emerald-900 uppercase tracking-wider"><i class="fas fa-door-open mr-2"></i>Advisory Class Details</p>
                <input type="text" name="advisory_name" id="advisoryNameInput" placeholder="e.g. Diamond 7-A, Emerald 8-B"
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black">
                <select name="grade_level" id="advisoryGradeLevelInput" class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                    <option value="">Select grade…</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTeacherModal()" class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">Cancel</button>
                <button type="submit" class="flex-1 bg-[#f8c922] text-[#043915] font-bold py-3 rounded-xl hover:bg-yellow-300 transition-colors shadow-lg text-base flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reassign Modal -->
<div id="reassignModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-black flex items-center gap-2"><i class="fas fa-arrows-alt text-blue-600"></i>Reassign Student</h2>
            <p class="text-sm text-gray-600 mt-1">Moving <span id="reassignStudentName" class="font-bold text-[#043915]"></span></p>
        </div>
        <form id="reassignForm" onsubmit="submitReassignment(event)" class="p-7 space-y-5">
            <input type="hidden" name="action" value="reassign_student">
            <input type="hidden" name="assignment_id" id="reassignAssignmentId">
            <input type="hidden" name="current_grade" id="reassignCurrentGrade">
            <select name="new_advisory_id" id="reassignAdvisorySelect" required class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                <option value="">Select advisory class…</option>
                <?php foreach ($advisoryTeachers as $teacher): ?>
                    <option value="<?= $teacher['advisory_id'] ?>" data-grade="<?= $teacher['grade_level'] ?>"><?= htmlspecialchars($teacher['teacher_name']) ?> — <?= htmlspecialchars($teacher['advisory_name']) ?> (Grade <?= $teacher['grade_level'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <div class="flex gap-3">
                <button type="button" onclick="closeReassignModal()" class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">Cancel</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors text-base flex items-center justify-center gap-2">
                    <i class="fas fa-check"></i> Reassign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Remove Advisory Modal -->
<div id="removeAdvisoryModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-8 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold text-black mb-3">Remove from Advisory</h2>
        <p class="text-sm text-gray-700 mb-7 leading-relaxed font-medium">Are you sure you want to remove <strong id="removeStudentName" class="text-[#043915]"></strong>?</p>
        <form id="removeAdvisoryForm" onsubmit="submitRemoval(event)">
            <input type="hidden" name="action" value="remove_from_advisory">
            <input type="hidden" name="assignment_id" id="removeAssignmentId">
            <div class="flex gap-3">
                <button type="button" onclick="closeRemoveAdvisoryModal()" class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">Cancel</button>
                <button type="submit" class="flex-1 bg-red-600 text-white font-bold py-3 rounded-xl hover:bg-red-700 transition-colors text-base flex items-center justify-center gap-2">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Convert Teacher Modal -->
<div id="convertTeacherModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-8 text-center">
        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-exchange-alt text-orange-600 text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold text-black mb-3">Convert to Subject Teacher</h2>
        <p class="text-sm text-gray-700 mb-7 leading-relaxed font-medium"><strong id="convertTeacherName" class="text-[#043915]"></strong> will be converted to Subject Teacher.</p>
        <form id="convertTeacherForm" onsubmit="submitConversion(event)">
            <input type="hidden" name="action" value="convert_to_subject">
            <input type="hidden" name="advisory_id" id="convertAdvisoryId">
            <div class="flex gap-3">
                <button type="button" onclick="closeConvertTeacherModal()" class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">Cancel</button>
                <button type="submit" class="flex-1 bg-orange-600 text-white font-bold py-3 rounded-xl hover:bg-orange-700 transition-colors text-base flex items-center justify-center gap-2">
                    <i class="fas fa-check"></i> Convert
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Advisory Modal -->
<div id="viewAdvisoryModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-black flex items-center gap-3" id="advisoryDetailTitle"><i class="fas fa-users text-blue-600"></i>Advisory Students</h2>
                <p class="text-sm text-gray-600 mt-1" id="advisoryDetailSubtitle"></p>
            </div>
            <button onclick="closeViewAdvisoryModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition-colors shrink-0 text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8" id="advisoryStudentsList"></div>

        <div id="gradePromotionControls" class="hidden px-8 py-5 border-t border-gray-200 shrink-0 bg-gray-50">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-sm font-bold text-black flex items-center gap-2"><i class="fas fa-chart-line text-blue-600"></i>Grade Promotion</p>
                    <p id="selectedCount" class="text-xs text-gray-500">0 students selected</p>
                </div>
                <div class="flex gap-2">
                    <select id="bulkGradeSelect" class="border-2 border-gray-300 rounded-lg px-3 py-2 text-sm font-bold bg-white text-gray-700 focus:ring-2 focus:ring-[#043915] focus:border-[#043915] transition">
                        <option value="">Select Grade Level</option>
                    </select>
                    <button onclick="confirmBulkGradePromotion()" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition text-sm flex items-center gap-2">
                        <i class="fas fa-arrow-up"></i> Promote
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" id="selectAllStudentsPromotion" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" onchange="toggleAllStudentsPromotion(this.checked)">
                <label for="selectAllStudentsPromotion" class="text-sm font-medium text-gray-700"><i class="fas fa-check-square mr-2"></i>Select All Students</label>
            </div>
        </div>
        <button onclick="closeViewAdvisoryModal()" class="w-full bg-[#043915] text-white font-bold py-4 rounded-b-3xl hover:bg-[#055020] transition-colors text-sm flex items-center justify-center gap-2">
            <i class="fas fa-times-circle"></i> Close
        </button>
    </div>
</div>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/advisories-helper.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>