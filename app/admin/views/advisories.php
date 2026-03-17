<?php
ob_start();

$teachers         = $advisoriesController->getAllTeachers();
$advisoryTeachers = $advisoriesController->getAdvisoryTeachers();
$allStudents      = $advisoriesController->getAllStudents();
$activeSchoolYear = $advisoriesController->getActiveSchoolYear();
?>

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-5 right-5 z-[300] flex flex-col gap-2 pointer-events-none"></div>

<!-- PRINT REPORT MODAL -->
<div id="printReportModal" class="fixed inset-0 z-[500] hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[95vh] overflow-hidden flex flex-col">
        <div class="px-8 py-6 border-b border-gray-200 flex items-center justify-between shrink-0 bg-white">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-[#043915] rounded-2xl flex items-center justify-center">
                    <i class="fas fa-file-pdf text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900" id="printReportTitle">Student Report</h2>
                    <p class="text-sm text-gray-500 mt-0.5" id="printReportSubtitle">Academic history & incident records</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-6 py-2.5 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold rounded-xl text-sm transition-all shadow-md">
                    <i class="fas fa-print mr-2"></i>Print / Save PDF
                </button>
                <button onclick="closePrintModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-8 bg-gray-50" id="printReportBody">
            <div class="flex items-center justify-center py-20 text-gray-400">
                <i class="fas fa-spinner fa-spin text-4xl mr-4"></i>
                <span class="text-lg">Loading…</span>
            </div>
        </div>
    </div>
</div>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-5 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-[#043915] to-[#032a0f] rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-chalkboard-user text-[#f8c922] text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900" id="pageTitle">Advisory Class Management</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage teachers and student assignments</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button onclick="openTeacherModal()" class="inline-flex items-center gap-2 bg-[#043915] hover:bg-[#032a0f] text-white px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-user-plus"></i> Assign Teacher
                </button>
                <button onclick="openStudentModal()" id="assignStudentBtn" class="inline-flex items-center gap-2 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-users"></i> Assign Students
                </button>
                <p id="noAdvisoryMessage" class="hidden text-sm text-amber-600 bg-amber-50 px-4 py-3 rounded-xl font-medium">
                    <i class="fas fa-info-circle mr-2"></i>Assign a teacher first
                </p>
            </div>
        </div>
    </div>

    <!-- School Year Info Bar -->
    <?php if ($activeSchoolYear): ?>
    <div class="mb-6 rounded-2xl shadow-sm p-5 flex items-center justify-between gap-4 bg-white hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-[#043915]/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-check text-[#043915] text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-black text-gray-900">S.Y. <?= $activeSchoolYear['start_year'] ?> – <?= $activeSchoolYear['end_year'] ?></p>
                <p class="text-xs text-gray-500 mt-0.5">Active School Year</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end">
            <span class="px-3 py-1.5 bg-[#f8c922] text-[#043915] rounded-lg text-xs font-black">ACTIVE</span>
            <button onclick="openSchoolYearModal(<?= $activeSchoolYear['school_year_id'] ?>, <?= $activeSchoolYear['start_year'] ?>, <?= $activeSchoolYear['end_year'] ?>)" class="px-4 py-1.5 bg-[#043915] hover:bg-[#032a0f] text-white font-bold rounded-lg text-xs transition-colors">Edit</button>
            <button onclick="toggleFilters()" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs transition-colors"><i class="fas fa-sliders-h mr-1"></i>Filter</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters Bar -->
    <div id="filterBars" class="mb-6 bg-white rounded-2xl shadow-sm p-6 overflow-x-auto hidden hover:shadow-md transition-shadow duration-300">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-[180px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Search</label>
                <input type="text" id="searchInput" placeholder="Student, LRN, teacher…" class="w-full px-4 py-2.5 bg-gray-50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Category</label>
                <select id="filterTeacherRole" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">All Students</option>
                    <option value="advisory">Advisory Teacher</option>
                    <option value="subject">Subject Teacher</option>
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Grade</label>
                <select id="filterGrade" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">All</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                </select>
            </div>
            <div class="min-w-[110px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Sort</label>
                <select id="sortName" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="ASC">A to Z</option>
                    <option value="DESC">Z to A</option>
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-bold text-gray-700 mb-2">Date</label>
                <input type="date" id="filterDate" class="w-full bg-gray-50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
            </div>
            <button onclick="resetFilters()" class="px-4 py-2.5 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] rounded-lg text-sm font-bold transition-all">Reset</button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col" style="height:600px;">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3 shrink-0 bg-gradient-to-r from-gray-50 to-white">
            <p id="resultCount" class="text-sm font-bold text-gray-700">Loading records…</p>
        </div>
        <div class="overflow-x-auto overflow-y-auto flex-1">
            <table class="w-full text-sm" id="mainTable">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white">
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Student</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">LRN</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Grade</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Teacher</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Class</th>
                        <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Date</th>
                        <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="7" class="py-20 text-center"><i class="fas fa-spinner fa-spin mr-2 text-gray-300"></i><span class="text-sm text-gray-500">Loading…</span></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-5 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white rounded-2xl shadow-sm px-6 py-4 hover:shadow-md transition-shadow duration-300">
        <p id="paginationInfo" class="text-sm font-semibold text-gray-600">Loading…</p>
        <div id="paginationContainer" class="flex items-center gap-2"></div>
    </div>
</main>

<!-- ============================================================ -->
<!-- SCHOOL YEAR MODAL -->
<!-- ============================================================ -->
<div id="schoolYearModal" class="fixed inset-0 z-[150] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent">
            <h2 class="text-2xl font-bold text-gray-900">Edit School Year</h2>
            <p class="text-sm text-gray-600 mt-1">Update the active school year</p>
        </div>
        <form id="schoolYearForm" onsubmit="submitSchoolYearUpdate(event)" class="p-8 space-y-6">
            <input type="hidden" name="action" value="update_school_year">
            <input type="hidden" id="schoolYearId" name="school_year_id">
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-3">Start Year</label>
                <input type="number" id="startYearInput" name="start_year" min="2000" max="2100" required class="w-full bg-gray-50 rounded-lg px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-3">End Year</label>
                <input type="number" id="endYearInput" name="end_year" min="2000" max="2100" required class="w-full bg-gray-50 rounded-lg px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeSchoolYearModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-3 rounded-lg transition-colors">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- STUDENT HISTORY MODAL -->
<!-- ============================================================ -->
<div id="studentHistoryModal" class="fixed inset-0 z-[200] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-3">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Academic History</h2>
                <p class="text-sm text-gray-600 mt-2" id="historyModalStudentName">—</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="printStudentHistoryAndProfile()" class="px-5 py-2.5 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold rounded-lg text-sm transition-all shadow-md">
                    <i class="fas fa-download mr-2"></i>Export PDF
                </button>
                <button onclick="closeStudentHistoryModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6 bg-white" id="historyModalBody">
            <div id="historyLoadingState" class="flex flex-col items-center justify-center py-20 gap-3">
                <i class="fas fa-spinner fa-spin text-[#043915] text-3xl"></i>
                <p class="text-sm text-gray-500">Loading history…</p>
            </div>
            <div id="historyContent" class="hidden space-y-5"></div>
        </div>
        <div class="px-8 py-4 border-t border-gray-200 shrink-0 bg-gray-50 flex justify-end">
            <button onclick="closeStudentHistoryModal()" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-lg text-sm transition-colors">Close</button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- STUDENT PROFILE MODAL -->
<!-- ============================================================ -->
<div id="studentProfileModal" class="fixed inset-0 z-[150] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-3">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-hidden flex flex-col">
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-900" id="profileModalTitle">Student Profile</h2>
                <p class="text-sm text-gray-600 mt-2" id="profileModalSubtitle">View detailed information</p>
            </div>
            <button onclick="closeStudentProfileModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div id="profileLoadingState" class="hidden flex-1 flex items-center justify-center py-16">
            <i class="fas fa-spinner fa-spin text-[#043915] text-3xl"></i>
        </div>
        <div id="profileFormContent" class="flex-1 overflow-y-auto px-8 py-6 bg-white">
            <input type="hidden" id="profileStudentId">
            <input type="hidden" id="originalProfilePix" value="">
            <input type="hidden" id="profileEditMode" value="0">

            <div class="mb-8 flex justify-center">
                <div class="relative cursor-pointer" onclick="document.getElementById('profilePictureInput').click()">
                    <div class="w-32 h-32 bg-gradient-to-br from-[#043915]/5 to-[#f8c922]/5 rounded-2xl flex items-center justify-center overflow-hidden shadow-md">
                        <i id="profileAvatarIcon" class="fas fa-user text-[#043915] text-5xl"></i>
                        <img id="profileAvatarImg" src="" alt="" class="w-full h-full object-cover hidden">
                    </div>
                    <div id="profilePicOverlay" class="hidden absolute inset-0 bg-black/50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-camera text-white text-3xl"></i>
                    </div>
                </div>
            </div>
            <input type="file" id="profilePictureInput" class="hidden" accept="image/jpeg,image/png,image/webp" onchange="handleProfilePictureChange(event)">

            <div class="mb-6">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Personal Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" id="profileFirstName" placeholder="First Name" readonly class="profile-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Last Name</label>
                        <input type="text" id="profileLastName" placeholder="Last Name" readonly class="profile-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 mt-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Middle Initial</label>
                    <input type="text" id="profileMI" maxlength="3" placeholder="M.I." readonly class="profile-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Academic Details</h3>
                <div class="grid grid-cols-3 gap-3 bg-gradient-to-br from-[#043915]/5 to-transparent rounded-lg p-5">
                    <div><p class="text-xs font-bold text-gray-600 uppercase mb-2">Grade</p><p id="profileYearLevel" class="text-lg font-bold text-gray-900">—</p></div>
                    <div><p class="text-xs font-bold text-gray-600 uppercase mb-2">Section</p><p id="profileSection" class="text-sm font-bold text-gray-900 truncate">—</p></div>
                    <div><p class="text-xs font-bold text-gray-600 uppercase mb-2">Adviser</p><p id="profileAdviser" class="text-sm font-bold text-gray-900 truncate">—</p></div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Contact Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">LRN</label>
                        <input type="text" id="profileLrn" maxlength="12" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="100123456789" readonly class="profile-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition font-mono py-1">
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Phone</label>
                        <input type="text" id="profileContact" maxlength="11" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="09XXXXXXXXX" readonly class="profile-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition font-mono py-1">
                    </div>
                </div>
            </div>
            <div class="mb-6">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Address</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Home Address</label>
                    <input type="text" id="profileAddress" placeholder="Street, Barangay, City" readonly class="profile-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                </div>
            </div>
            <div class="mb-8">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Guardian</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Guardian Name</label>
                        <input type="text" id="profileGuardianName" placeholder="Guardian Name" readonly class="profile-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Guardian Contact</label>
                        <input type="text" id="profileGuardianContact" maxlength="11" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="09XXXXXXXXX" readonly class="profile-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition font-mono py-1">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-8 py-5 border-t border-gray-200 flex gap-3 shrink-0 bg-gray-50">
            <div id="profileViewButtons" class="flex gap-3 w-full">
                <button onclick="enableProfileEditMode()" class="flex-1 bg-[#043915] hover:bg-[#032a0f] text-white font-bold py-2.5 rounded-lg text-sm transition-colors">Edit</button>
                <button onclick="closeStudentProfileModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2.5 rounded-lg text-sm transition-colors">Close</button>
            </div>
            <div id="profileEditButtons" class="hidden flex gap-3 w-full">
                <button onclick="saveStudentProfile()" class="flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-2.5 rounded-lg text-sm transition-colors">Save</button>
                <button onclick="cancelProfileEdit()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2.5 rounded-lg text-sm transition-colors">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TEACHER PROFILE MODAL -->
<!-- ============================================================ -->
<div id="teacherProfileModal" class="fixed inset-0 z-[150] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-2">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-hidden flex flex-col">
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-900" id="teacherModalTitle">Faculty Profile</h2>
                <p class="text-sm text-gray-600 mt-2" id="teacherModalSubtitle">View teacher information</p>
            </div>
            <button onclick="closeTeacherProfileModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div id="teacherLoadingState" class="hidden flex-1 flex items-center justify-center py-20">
            <i class="fas fa-spinner fa-spin text-[#043915] text-3xl"></i>
        </div>
        <div id="teacherFormContent" class="flex-1 overflow-y-auto px-8 py-6 bg-white">
            <input type="hidden" id="teacherRecordId">
            <input type="hidden" id="originalTeacherPix" value="">
            <input type="hidden" id="teacherEditMode" value="0">

            <div class="mb-8 flex justify-center">
                <div class="relative cursor-pointer" onclick="document.getElementById('teacherPictureInput').click()">
                    <div class="w-32 h-32 bg-gradient-to-br from-[#043915]/5 to-[#f8c922]/5 rounded-2xl flex items-center justify-center overflow-hidden shadow-md">
                        <i id="teacherAvatarIcon" class="fas fa-user-tie text-[#043915] text-4xl"></i>
                        <img id="teacherAvatarImg" src="" alt="" class="w-full h-full object-cover hidden">
                    </div>
                    <div id="teacherPicOverlay" class="hidden absolute inset-0 bg-black/50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-camera text-white text-3xl"></i>
                    </div>
                </div>
            </div>
            <input type="file" id="teacherPictureInput" class="hidden" accept="image/jpeg,image/png,image/webp" onchange="handleTeacherPictureChange(event)">

            <div class="mb-6">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Professional Details</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Teacher ID</label>
                        <input type="text" id="teacherIdField" placeholder="T-2024-001" readonly class="teacher-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition font-mono py-1">
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Department</label>
                        <select id="teacherDeptField" disabled class="teacher-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
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
            </div>
            <div class="mb-6">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Full Name</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" id="teacherFirstName" placeholder="First Name" readonly class="teacher-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Last Name</label>
                        <input type="text" id="teacherLastName" placeholder="Last Name" readonly class="teacher-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Suffix</label>
                        <input type="text" id="teacherSuffix" placeholder="e.g. LPT" readonly class="teacher-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                    </div>
                </div>
            </div>
            <div class="mb-6">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Contact</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Email</label>
                        <input type="email" id="teacherEmail" placeholder="teacher@school.edu.ph" readonly class="teacher-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition py-1">
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Phone</label>
                        <input type="text" id="teacherContact" maxlength="11" placeholder="09XXXXXXXXX" readonly class="teacher-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition font-mono py-1">
                    </div>
                </div>
            </div>
            <div class="mb-8">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Specialization</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <textarea id="teacherSpecialization" rows="3" placeholder="e.g. Advanced Algebra, Physics" readonly class="teacher-field w-full bg-white text-gray-900 text-sm font-medium outline-none border-b border-transparent focus:border-[#f8c922] transition resize-none py-1"></textarea>
                </div>
            </div>
        </div>
        <div class="px-8 py-5 border-t border-gray-200 flex gap-3 shrink-0 bg-gray-50">
            <div id="teacherViewButtons" class="flex gap-3 w-full">
                <button onclick="enableTeacherEditMode()" class="flex-1 bg-[#043915] hover:bg-[#032a0f] text-white font-bold py-2.5 rounded-lg text-sm transition-colors">Edit</button>
                <button onclick="closeTeacherProfileModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2.5 rounded-lg text-sm transition-colors">Close</button>
            </div>
            <div id="teacherEditButtons" class="hidden flex gap-3 w-full">
                <button onclick="saveTeacherProfile()" class="flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-2.5 rounded-lg text-sm transition-colors">Save</button>
                <button onclick="cancelTeacherEdit()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2.5 rounded-lg text-sm transition-colors">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ASSIGN TEACHER MODAL -->
<!-- ============================================================ -->
<div id="teacherModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-[#043915] to-[#032a0f] px-8 py-8">
            <h2 class="text-xl font-bold text-white">Assign Teacher Role</h2>
            <p class="text-sm text-[#f8c922] mt-2">Add a teacher to the system</p>
        </div>
        <form id="assignTeacherForm" onsubmit="submitTeacherAssignment(event)" class="p-8 space-y-6">
            <input type="hidden" name="action" value="assign_teacher">
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-3">Teacher</label>
                <select name="teacher_id" id="teacherSelect" required class="w-full bg-gray-50 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">Select a teacher…</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= $teacher['user_id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-3">Role</label>
                <select name="role_type" id="teacherRoleType" required class="w-full bg-gray-50 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all" onchange="toggleAdvisoryFields()">
                    <option value="">Select role…</option>
                    <option value="subject">Subject Teacher</option>
                    <option value="advisory">Advisory Teacher</option>
                </select>
            </div>
            <div id="advisoryFields" class="hidden bg-[#043915]/5 rounded-lg p-5 space-y-4">
                <p class="text-sm font-bold text-gray-900 uppercase tracking-wider">Advisory Class Details</p>
                <input type="text" name="advisory_name" id="advisoryNameInput" placeholder="e.g. Diamond 7-A" class="w-full bg-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/20 transition-all">
                <select name="grade_level" id="advisoryGradeLevelInput" class="w-full bg-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/20 transition-all">
                    <option value="">Select grade…</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTeacherModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-3 rounded-lg transition-colors">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- ASSIGN STUDENTS MODAL -->
<!-- ============================================================ -->
<div id="studentModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-6xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="px-8 py-8 border-b border-gray-200 flex items-center justify-between shrink-0 bg-gradient-to-r from-[#043915]/5 to-transparent">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Assign Students</h2>
                <p class="text-sm text-gray-600 mt-2">Select students to add to an advisory class</p>
            </div>
            <button onclick="closeStudentModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5">
            <?php if ($activeSchoolYear): ?>
            <div class="bg-[#043915]/5 rounded-lg px-5 py-3 flex items-center gap-3">
                <i class="fas fa-calendar-alt text-[#043915] text-lg"></i>
                <p class="text-sm text-gray-800 font-semibold">School Year: <span class="font-black"><?= $activeSchoolYear['start_year'] ?> – <?= $activeSchoolYear['end_year'] ?></span><span class="ml-2 px-3 py-1 bg-[#f8c922] text-[#043915] text-xs rounded-full font-bold">ACTIVE</span></p>
            </div>
            <?php endif; ?>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-3">Advisory Teacher</label>
                <select id="modalAdvisoryTeacher" class="w-full bg-gray-50 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                    <option value="">Choose an advisory teacher</option>
                    <?php foreach ($advisoryTeachers as $teacher): ?>
                        <option value="<?= $teacher['advisory_id'] ?>" data-current-count="<?= $teacher['student_count']??0 ?>" data-grade-level="<?= $teacher['grade_level'] ?>"><?= htmlspecialchars($teacher['teacher_name']) ?> — <?= htmlspecialchars($teacher['advisory_name']) ?> (Grade <?= $teacher['grade_level'] ?>, <?= $teacher['student_count']??0 ?>/40)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="advisoryCapacityInfo" class="hidden bg-[#043915]/5 rounded-lg px-5 py-3 flex items-center gap-3">
                <i class="fas fa-check-circle text-[#043915] text-lg"></i>
                <p class="text-sm text-gray-800 font-medium">Grade <span id="advisoryGradeLevel" class="font-bold">—</span> · <span id="currentStudentCount" class="font-bold">0</span> assigned · <span id="remainingSlots" class="font-bold">40</span> available</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-gray-700">Filter:</span>
                <button onclick="filterModalStudents('all')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#043915] text-white hover:bg-[#032a0f] transition-colors">All</button>
                <button onclick="filterModalStudents('7')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">Grade 7</button>
                <button onclick="filterModalStudents('8')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">Grade 8</button>
                <button onclick="filterModalStudents('9')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">Grade 9</button>
                <button onclick="filterModalStudents('10')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">Grade 10</button>
                <div class="ml-auto flex items-center gap-2">
                    <button onclick="toggleAllVisibleStudents(true)" class="px-3 py-1.5 bg-[#043915] text-white text-xs font-bold rounded-lg hover:bg-[#032a0f] transition-colors">Select All</button>
                    <button onclick="toggleAllVisibleStudents(false)" class="px-3 py-1.5 border border-red-300 text-red-600 text-xs font-bold rounded-lg hover:bg-red-50 transition-colors">Clear</button>
                </div>
            </div>

            <div class="rounded-lg overflow-hidden" style="max-height:340px;overflow-y:auto;">
                <form id="assignStudentsForm" method="POST">
                    <input type="hidden" name="action" value="assign_students">
                    <input type="hidden" name="advisory_id" id="hiddenAdvisoryId" value="">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-[#043915] z-10">
                            <tr class="text-xs text-white font-bold uppercase">
                                <th class="py-3 px-4 text-left">Select</th>
                                <th class="py-3 px-4 text-left">Student Name</th>
                                <th class="py-3 px-4 text-left">LRN</th>
                                <th class="py-3 px-4 text-left">Grade</th>
                                <th class="py-3 px-4 text-left">Change Grade</th>
                            </tr>
                        </thead>
                        <tbody id="modalStudentTable" class="divide-y divide-gray-100">
                            <tr><td colspan="5" class="py-12 text-center"><i class="fas fa-spinner fa-spin text-2xl text-[#043915]"></i><span class="text-sm text-gray-600 block mt-2">Loading…</span></td></tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        <div class="px-8 py-5 border-t border-gray-200 flex gap-4 shrink-0 bg-gray-50">
            <button onclick="closeStudentModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-lg transition-colors text-sm">Cancel</button>
            <button onclick="confirmStudentAssignment()" class="flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-3 rounded-lg transition-colors text-sm">Confirm</button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- REASSIGN MODAL -->
<!-- ============================================================ -->
<div id="reassignModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-8 py-8 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Reassign Student</h2>
            <p class="text-sm text-gray-600 mt-2">Moving <span id="reassignStudentName" class="font-bold text-[#043915]"></span></p>
        </div>
        <form id="reassignForm" onsubmit="submitReassignment(event)" class="p-8 space-y-6">
            <input type="hidden" name="action" value="reassign_student">
            <input type="hidden" name="assignment_id" id="reassignAssignmentId">
            <input type="hidden" name="current_grade" id="reassignCurrentGrade">
            <select name="new_advisory_id" id="reassignAdvisorySelect" required class="w-full bg-gray-50 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                <option value="">Select advisory class…</option>
                <?php foreach ($advisoryTeachers as $teacher): ?>
                    <option value="<?= $teacher['advisory_id'] ?>" data-grade="<?= $teacher['grade_level'] ?>"><?= htmlspecialchars($teacher['teacher_name']) ?> — <?= htmlspecialchars($teacher['advisory_name']) ?> (Grade <?= $teacher['grade_level'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <div class="flex gap-3">
                <button type="button" onclick="closeReassignModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-3 rounded-lg transition-colors">Reassign</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- REMOVE FROM ADVISORY MODAL -->
<!-- ============================================================ -->
<div id="removeAdvisoryModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-8 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">Remove from Advisory</h2>
        <p class="text-sm text-gray-600 mb-7">Are you sure you want to remove <strong id="removeStudentName"></strong>?</p>
        <form id="removeAdvisoryForm" onsubmit="submitRemoval(event)">
            <input type="hidden" name="action" value="remove_from_advisory">
            <input type="hidden" name="assignment_id" id="removeAssignmentId">
            <div class="flex gap-3">
                <button type="button" onclick="closeRemoveAdvisoryModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition-colors">Remove</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- SINGLE PROMOTE STUDENT MODAL -->
<!-- ============================================================ -->
<div id="promoteModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-8 py-8 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Promote Student</h2>
            <p class="text-sm text-gray-600 mt-2" id="promoteStudentInfo">—</p>
        </div>
        <div id="promoteLoadingState" class="hidden p-8 text-center">
            <i class="fas fa-spinner fa-spin text-[#043915] text-2xl"></i>
            <p class="text-sm text-gray-500 mt-3">Checking incidents…</p>
        </div>
        <div id="promoteContent" class="p-8 space-y-6">
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 hidden" id="promoteUnresolvedWarning">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 text-lg shrink-0"></i>
                    <div>
                        <p class="text-sm font-bold text-red-800">Cannot Promote</p>
                        <p class="text-sm text-red-700 mt-1">This student has <span id="unresolvedCount" class="font-black">0</span> unresolved incident(s). Resolve all incidents before promoting.</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 hidden" id="promoteReadyMessage">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500 text-lg shrink-0"></i>
                    <p class="text-sm font-bold text-green-800">All incidents resolved — ready to promote!</p>
                </div>
            </div>
            <form id="promoteForm" onsubmit="submitPromotion(event)" class="space-y-4">
                <input type="hidden" name="action" value="promote_student">
                <input type="hidden" name="assignment_id" id="promoteAssignmentId">
                <input type="hidden" name="student_id" id="promoteStudentId">
                <input type="hidden" name="current_grade" id="promoteCurrentGrade">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Promote to Grade</label>
                    <p class="text-xs text-gray-500 mb-3">Grade is automatically set to the next level.</p>
                    <select name="new_grade" id="promoteNewGrade" required class="w-full bg-gray-50 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/20 focus:bg-white transition-all">
                        <!-- Options populated by JS based on current grade -->
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closePromoteModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" id="promoteSubmitBtn" class="flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-3 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-level-up-alt mr-2"></i>Promote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- BULK PROMOTE MODAL -->
<!-- ============================================================ -->
<div id="bulkPromoteModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[200] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

        <!-- Header -->
        <div class="px-8 py-7 bg-gradient-to-r from-[#043915] to-[#032a0f] flex items-center justify-between shrink-0 rounded-t-3xl">
            <div>
                <h2 class="text-xl font-black text-white flex items-center gap-3">
                    <i class="fas fa-level-up-alt text-[#f8c922]"></i>
                    Bulk Promote Students
                </h2>
                <p class="text-sm text-[#f8c922]/80 mt-1">Each student promotes to the next grade only</p>
            </div>
            <button onclick="closeBulkPromoteModal()" class="w-9 h-9 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Loading state -->
        <div id="bulkPromoteLoading" class="flex-1 flex flex-col items-center justify-center py-16 gap-4">
            <i class="fas fa-spinner fa-spin text-[#043915] text-3xl"></i>
            <p class="text-sm text-gray-500 font-medium">Checking incidents for all students…</p>
        </div>

        <!-- Content -->
        <div id="bulkPromoteContent" class="hidden flex-1 overflow-y-auto px-8 py-5">

            <!-- Legend -->
            <div class="flex items-center gap-5 mb-5 flex-wrap p-3 bg-gray-50 rounded-xl">
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                    <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>
                    Ready to promote
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                    Blocked — has unresolved incidents
                </div>
            </div>

            <!-- Select all bar -->
            <div class="flex items-center justify-between mb-4 gap-3">
                <button id="bulkPromoteSelectAll" onclick="toggleBulkPromoteSelectAll()"
                    class="flex items-center gap-2 px-4 py-2 bg-[#043915] hover:bg-[#032a0f] text-white text-xs font-bold rounded-lg transition shadow-sm">
                    <i class="fas fa-check-double"></i>
                    <span>Select All</span>
                </button>
                <p class="text-xs text-gray-500 font-medium">
                    <span id="bulkPromoteCheckedCount">0</span> selected
                </p>
            </div>

            <!-- Student list rendered by JS -->
            <div id="bulkPromoteList" class="space-y-5"></div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-5 border-t border-gray-200 flex gap-3 shrink-0 bg-gray-50 rounded-b-3xl">
            <button onclick="closeBulkPromoteModal()"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-xl transition text-sm">
                Cancel
            </button>
            <button id="bulkPromoteSubmitBtn" onclick="submitBulkPromotion()"
                class="flex-1 bg-[#f8c922] hover:bg-[#e6b70f] text-[#043915] font-bold py-3 rounded-xl transition text-sm shadow-sm">
                <i class="fas fa-level-up-alt mr-2"></i>Promote Selected
            </button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- VIEW ADVISORY MODAL -->
<!-- ============================================================ -->
<div id="viewAdvisoryModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="px-8 py-8 bg-gradient-to-r from-[#043915]/5 to-transparent border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-900" id="advisoryDetailTitle">Advisory Students</h2>
                <p class="text-sm text-gray-600 mt-2" id="advisoryDetailSubtitle"></p>
            </div>
            <button onclick="closeViewAdvisoryModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-8 bg-white" id="advisoryStudentsList"></div>
        <div class="px-8 py-5 border-t border-gray-200 shrink-0 bg-gray-50 flex justify-end">
            <button onclick="closeViewAdvisoryModal()" class="px-8 py-2.5 bg-[#043915] hover:bg-[#032a0f] text-white font-bold rounded-xl text-sm transition">Close</button>
        </div>
    </div>
</div>

<style>
@media print {
    body > *:not(#printFrame) { display: none !important; }
    #printFrame { display: block !important; }
}
</style>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/advisories-helper.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>