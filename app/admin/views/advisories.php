<?php
ob_start();

$teachers         = $advisoriesController->getAllTeachers();
$advisoryTeachers = $advisoriesController->getAdvisoryTeachers();
$allStudents      = $advisoriesController->getAllStudents();
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
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Advisory Class Management</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Manage advisory teachers and student assignments</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="openTeacherModal()"
                    class="inline-flex items-center gap-2 bg-[#043915] text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#055020] transition-colors shadow-sm">
                    Assign Teacher
                </button>
                <button onclick="openStudentModal()" id="assignStudentBtn"
                    class="inline-flex items-center gap-2 bg-[#f8c922] text-[#043915] px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-yellow-300 transition-colors shadow-sm">
                    Assign Students
                </button>
                <p id="noAdvisoryMessage" class="hidden text-xs text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2.5 rounded-xl font-medium">
                    Assign a teacher first
                </p>
            </div>
        </div>
    </div>

    <!-- Layout -->
    <div class="flex flex-col xl:flex-row gap-5 items-start h-[calc(100vh-200px)]">

        <!-- ── SIDEBAR FILTERS ──────────────────────────────────── -->
        <aside class="w-full xl:w-64 shrink-0">

            <!-- Filter card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-bold text-black flex items-center gap-2">
                        Filters
                    </span>
                    <button onclick="resetFilters()"
                        class="text-xs text-red-600 hover:text-red-700 font-bold transition-colors flex items-center gap-1">
                        Reset
                    </button>
                </div>
                <div class="p-4 space-y-4 max-h-[calc(100vh-300px)] overflow-y-auto">

                    <!-- Search -->
                    <div>
                        <label class="block text-xs font-bold text-black mb-2">Search</label>
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Student, LRN, teacher…"
                                class="w-full pl-3 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black placeholder-gray-500">
                        </div>
                    </div>

                    <!-- Teacher Type -->
                    <div>
                        <label class="block text-xs font-bold text-black mb-2">Teacher Type</label>
                        <select id="filterTeacherRole"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                            <option value="">All Types</option>
                            <option value="advisory">Advisory Teacher</option>
                            <option value="subject">Subject Teacher</option>
                        </select>
                    </div>

                    <!-- Grade Level -->
                    <div>
                        <label class="block text-xs font-bold text-black mb-2">Grade Level</label>
                        <select id="filterGrade"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                            <option value="">All Grades</option>
                            <option value="7">Grade 7</option>
                            <option value="8">Grade 8</option>
                            <option value="9">Grade 9</option>
                            <option value="10">Grade 10</option>
                            <option value="11">Grade 11</option>
                            <option value="12">Grade 12</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-xs font-bold text-black mb-2">Sort by Name</label>
                        <select id="sortName"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                            <option value="ASC">A to Z</option>
                            <option value="DESC">Z to A</option>
                        </select>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-xs font-bold text-black mb-2">Assignment Date</label>
                        <input type="date" id="filterDate"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black">
                    </div>
                </div>
            </div>

        </aside>

        <!-- ── MAIN TABLE ───────────────────────────────────────── -->
        <section class="flex-1 min-w-0 flex flex-col">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-full">

                <!-- Toolbar -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3 shrink-0">
                    <p id="resultCount" class="text-sm font-bold text-black">Loading records…</p>
                </div>

                <!-- Table -->
                <div class="flex-1 overflow-x-auto overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-[#043915] text-white">
                                <th class="py-4 px-6 text-left text-base font-bold uppercase tracking-wide">Student</th>
                                <th class="py-4 px-6 text-left text-base font-bold uppercase tracking-wide">LRN</th>
                                <th class="py-4 px-6 text-left text-base font-bold uppercase tracking-wide">Grade</th>
                                <th class="py-4 px-6 text-left text-base font-bold uppercase tracking-wide">Advisory Teacher</th>
                                <th class="py-4 px-6 text-left text-base font-bold uppercase tracking-wide">Advisory Class</th>
                                <th class="py-4 px-6 text-left text-base font-bold uppercase tracking-wide">Assigned Date</th>
                                <th class="py-4 px-6 text-center text-base font-bold uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="7" class="py-20 text-center">
                                    <p class="text-base text-gray-700">Loading data…</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
                <p id="paginationInfo" class="text-base font-semibold text-gray-700"></p>
                <div id="paginationContainer" class="flex items-center gap-2"></div>
            </div>
        </section>

    </div>
</main>


<!-- ================================================================ -->
<!-- STUDENT PROFILE MODAL                                             -->
<!-- ================================================================ -->
<div id="studentProfileModal" class="fixed inset-0 z-[150] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-2">
    <div class="bg-white rounded-3xl shadow-2xl w-full h-auto max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="px-8 pt-8 pb-6 border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-black">Edit Student Record</h2>
                <p class="text-base text-gray-800 mt-1">Update information below, then click Save Changes.</p>
            </div>
            <button onclick="closeStudentProfileModal()"
                class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition shrink-0 text-xl">
                ✕
            </button>
        </div>

        <!-- Loading -->
        <div id="profileLoadingState" class="hidden flex-1 flex items-center justify-center">
            <div class="text-center py-12">
                <p class="text-lg text-gray-700">Loading student info…</p>
            </div>
        </div>

        <!-- Form -->
        <div id="profileFormContent" class="flex-1 overflow-y-auto px-8 py-6 space-y-6">
            <input type="hidden" id="profileStudentId">

            <!-- Profile Picture Section -->
            <div class="flex justify-center">
                <div class="w-56 h-32 bg-gray-100 rounded-2xl border-2 border-gray-200 flex items-center justify-center overflow-hidden relative cursor-pointer group hover:shadow-lg transition"
                    onclick="document.getElementById('profilePictureInput').click()">
                    <i class="fa-solid fa-user-graduate text-6xl text-gray-300" id="profileAvatarIcon"></i>
                    <img id="profileAvatarImg" src="" alt="" class="hidden w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        Camera
                    </div>
                </div>
            </div>
            <input type="file" id="profilePictureInput" class="hidden" accept="image/jpeg,image/png,image/webp" onchange="handleProfilePictureChange(event)">
            <input type="hidden" id="originalProfilePix" value="">

            <!-- Name fields -->
            <div>
                <label class="block text-sm font-bold text-black uppercase tracking-wider mb-3">Full Name</label>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-base font-bold text-black mb-2">First Name <span class="text-red-600">*</span></label>
                        <input type="text" id="profileFirstName" placeholder="e.g. Juan"
                            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black bg-white outline-none focus:border-[#043915] focus:ring-2 focus:ring-[#043915]/20 transition placeholder-gray-500">
                    </div>
                    <div>
                        <label class="block text-base font-bold text-black mb-2">Last Name <span class="text-red-600">*</span></label>
                        <input type="text" id="profileLastName" placeholder="e.g. Santos"
                            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black bg-white outline-none focus:border-[#043915] focus:ring-2 focus:ring-[#043915]/20 transition placeholder-gray-500">
                    </div>
                    <div>
                        <label class="block text-base font-bold text-black mb-2">M.I.</label>
                        <input type="text" id="profileMI" maxlength="3" placeholder="e.g. A."
                            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black bg-white outline-none focus:border-[#043915] focus:ring-2 focus:ring-[#043915]/20 transition placeholder-gray-500">
                    </div>
                </div>
            </div>

            <!-- LRN -->
            <div>
                <label class="block text-base font-bold text-black mb-2">
                    LRN — Learner Reference Number
                    <span class="text-gray-700 font-medium">(12 digits)</span>
                </label>
                <input type="text" id="profileLrn"
                    maxlength="12"
                    inputmode="numeric"
                    oninput="this.value=this.value.replace(/\D/g,'')"
                    placeholder="e.g. 100123456789"
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black bg-white outline-none focus:border-[#043915] focus:ring-2 focus:ring-[#043915]/20 transition placeholder-gray-500 font-mono tracking-wider">
                <p class="text-sm text-gray-700 mt-1 font-medium">Numbers only · exactly 12 digits</p>
            </div>

            <!-- Contact + Address -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-base font-bold text-black mb-2">
                        Contact Number
                        <span class="text-gray-700 font-medium">(11 digits)</span>
                    </label>
                    <input type="text" id="profileContact"
                        maxlength="11"
                        inputmode="numeric"
                        oninput="this.value=this.value.replace(/\D/g,'')"
                        placeholder="e.g. 09171234567"
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black bg-white outline-none focus:border-[#043915] focus:ring-2 focus:ring-[#043915]/20 transition placeholder-gray-500 font-mono tracking-wider">
                </div>
                <div>
                    <label class="block text-base font-bold text-black mb-2">
                        Home Address
                    </label>
                    <input type="text" id="profileAddress" placeholder="Street, Barangay, City"
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black bg-white outline-none focus:border-[#043915] focus:ring-2 focus:ring-[#043915]/20 transition placeholder-gray-500">
                </div>
            </div>

            <!-- Read-only class info -->
            <div class="bg-gray-100 border-2 border-gray-300 rounded-2xl p-7">
                <p class="text-sm font-bold text-black uppercase tracking-wider mb-5">
                    Class Information — Read Only
                </p>
                <div class="grid grid-cols-3 gap-8">
                    <div>
                        <p class="text-sm font-bold text-black mb-2">Year Level</p>
                        <p id="profileYearLevel" class="text-lg font-bold text-black">—</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-black mb-2">Section / Advisory</p>
                        <p id="profileSection" class="text-lg font-bold text-black">—</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-black mb-2">Adviser</p>
                        <p id="profileAdviser" class="text-lg font-bold text-black">—</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-6 border-t border-gray-200 flex gap-4 shrink-0">
            <button onclick="saveStudentProfile()"
                class="flex-1 bg-[#043915] hover:bg-[#055020] text-white font-bold py-4 rounded-xl transition-colors shadow-lg text-lg flex items-center justify-center gap-3">
                Save Changes
            </button>
            <button onclick="closeStudentProfileModal()"
                class="flex-1 bg-gray-200 hover:bg-gray-300 text-black font-bold py-4 rounded-xl transition-colors text-lg">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- TEACHER PROFILE MODAL                                             -->
<!-- ================================================================ -->
<div id="teacherProfileModal" class="fixed inset-0 z-[150] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-2">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="px-8 pt-8 pb-6 border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-black">Edit Faculty Record</h2>
                <p class="text-base text-gray-800 mt-1">Manage teacher credentials and contact information.</p>
            </div>
            <button onclick="closeTeacherProfileModal()"
                class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition shrink-0 text-xl">
                ✕
            </button>
        </div>

        <!-- Loading -->
        <div id="teacherLoadingState" class="hidden flex-1 flex items-center justify-center">
            <div class="text-center py-12">
                <p class="text-lg text-gray-700">Loading teacher info…</p>
            </div>
        </div>

        <!-- Form -->
        <div id="teacherFormContent" class="flex-1 overflow-y-auto px-8 py-6 space-y-6">
            <input type="hidden" id="teacherRecordId">

            <!-- Profile Picture Section -->
            <div class="flex justify-center">
                <div class="w-56 h-32 bg-gray-100 rounded-2xl border-2 border-gray-200 flex items-center justify-center overflow-hidden relative cursor-pointer group hover:shadow-lg transition"
                    onclick="document.getElementById('teacherPictureInput').click()">
                    <i class="fa-solid fa-user-tie text-6xl text-gray-300" id="teacherAvatarIcon"></i>
                    <img id="teacherAvatarImg" src="" alt="" class="hidden w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        Camera
                    </div>
                </div>
            </div>
            <input type="file" id="teacherPictureInput" class="hidden" accept="image/jpeg,image/png,image/webp" onchange="handleTeacherPictureChange(event)">
            <input type="hidden" id="originalTeacherPix" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-base font-bold text-black mb-2">
                        Teacher Number
                    </label>
                    <input type="text" id="teacherIdField" placeholder="T-2024-001"
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black bg-white outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 transition placeholder-gray-500 font-mono">
                </div>
                <div>
                    <label class="block text-base font-bold text-black mb-2">
                        Department
                    </label>
                    <select id="teacherDeptField" class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black bg-white outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 transition">
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
                <label class="block text-sm font-bold text-black uppercase tracking-wider mb-3">Professional Name</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-base font-bold text-black mb-2">First Name</label>
                        <input type="text" id="teacherFirstName" placeholder="First Name"
                            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 transition">
                    </div>
                    <div>
                        <label class="block text-base font-bold text-black mb-2">Last Name</label>
                        <input type="text" id="teacherLastName" placeholder="Last Name"
                            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 transition">
                    </div>
                    <div>
                        <label class="block text-base font-bold text-black mb-2">Suffix / Title</label>
                        <input type="text" id="teacherSuffix" placeholder="e.g. LPT, PhD"
                            class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 transition">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-base font-bold text-black mb-2">
                        Email Address
                    </label>
                    <input type="email" id="teacherEmail" placeholder="teacher@school.edu.ph"
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 transition">
                </div>
                <div>
                    <label class="block text-base font-bold text-black mb-2">
                        Contact Number
                    </label>
                    <input type="text" id="teacherContact" maxlength="11" placeholder="09XXXXXXXXX"
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 transition font-mono">
                </div>
            </div>

            <div>
                <label class="block text-base font-bold text-black mb-2">
                    Specialization / Subjects Taught
                </label>
                <textarea id="teacherSpecialization" rows="2" placeholder="e.g. Advanced Algebra, Physics, Robotics"
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base text-black outline-none focus:border-[#1e1b4b] focus:ring-2 focus:ring-[#1e1b4b]/20 transition"></textarea>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-6 border-t border-gray-200 flex gap-4 shrink-0">
            <button onclick="saveTeacherProfile()"
                class="flex-1 bg-[#1e1b4b] hover:bg-[#2e2a75] text-white font-bold py-4 rounded-xl transition-colors shadow-lg text-lg flex items-center justify-center gap-3">
                Update Faculty Profile
            </button>
            <button onclick="closeTeacherProfileModal()"
                class="flex-1 bg-gray-200 hover:bg-gray-300 text-black font-bold py-4 rounded-xl transition-colors text-lg">
                Cancel
            </button>
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
                <h2 class="text-2xl font-bold text-black">Assign Students</h2>
                <p class="text-lg text-gray-800 mt-1">Select students and assign them to an advisory class</p>
            </div>
            <button onclick="closeStudentModal()"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition-colors text-xl">
                ✕
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5">

            <!-- Advisory teacher select -->
            <div>
                <label class="block text-sm font-bold text-black uppercase tracking-wider mb-2">Advisory Teacher</label>
                <select id="modalAdvisoryTeacher"
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                    <option value="">— Choose an advisory teacher —</option>
                    <?php foreach ($advisoryTeachers as $teacher): ?>
                        <option value="<?= $teacher['advisory_id'] ?>"
                            data-current-count="<?= $teacher['student_count'] ?? 0 ?>"
                            data-grade-level="<?= $teacher['grade_level'] ?>">
                            <?= htmlspecialchars($teacher['teacher_name']) ?> — <?= htmlspecialchars($teacher['advisory_name']) ?>
                            (Grade <?= $teacher['grade_level'] ?>, <?= $teacher['student_count'] ?? 0 ?>/40 students)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Capacity banner -->
            <div id="advisoryCapacityInfo" class="hidden">
                <div class="bg-sky-100 border-2 border-sky-400 rounded-xl px-5 py-4 flex items-center gap-3">
                    <p class="text-base text-sky-900 font-medium">
                        Grade <span id="advisoryGradeLevel" class="font-bold text-lg">—</span> advisory ·
                        <span id="currentStudentCount" class="font-bold text-lg">0</span> students assigned ·
                        <span id="remainingSlots" class="font-bold text-lg">40</span> slots remaining
                    </p>
                </div>
            </div>

            <!-- Grade filter pills -->
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-bold text-black">Filter:</span>
                <button onclick="filterModalStudents('all')" id="gradeTab_all"
                    class="px-4 py-2 rounded-lg text-sm font-bold bg-gray-200 text-black hover:bg-gray-300 transition-colors">All</button>
                <button onclick="filterModalStudents('7')" id="gradeTab_7"
                    class="px-4 py-2 rounded-lg text-sm font-bold bg-blue-100 text-blue-900 hover:bg-blue-200 transition-colors">Grade 7</button>
                <button onclick="filterModalStudents('8')" id="gradeTab_8"
                    class="px-4 py-2 rounded-lg text-sm font-bold bg-orange-100 text-orange-900 hover:bg-orange-200 transition-colors">Grade 8</button>
                <button onclick="filterModalStudents('9')" id="gradeTab_9"
                    class="px-4 py-2 rounded-lg text-sm font-bold bg-purple-100 text-purple-900 hover:bg-purple-200 transition-colors">Grade 9</button>
                <button onclick="filterModalStudents('10')" id="gradeTab_10"
                    class="px-4 py-2 rounded-lg text-sm font-bold bg-green-100 text-green-900 hover:bg-green-200 transition-colors">Grade 10</button>
                <button onclick="filterModalStudents('11')" id="gradeTab_11"
                    class="px-4 py-2 rounded-lg text-sm font-bold bg-pink-100 text-pink-900 hover:bg-pink-200 transition-colors">Grade 11</button>
                <button onclick="filterModalStudents('12')" id="gradeTab_12"
                    class="px-4 py-2 rounded-lg text-sm font-bold bg-red-100 text-red-900 hover:bg-red-200 transition-colors">Grade 12</button>
                <div class="ml-auto flex items-center gap-2">
                    <button onclick="toggleAllVisibleStudents(true)"
                        class="px-4 py-2 bg-[#043915] text-white text-sm font-bold rounded-lg hover:bg-[#055020] transition-colors">Select All</button>
                    <button onclick="toggleAllVisibleStudents(false)"
                        class="px-4 py-2 border-2 border-red-500 text-red-600 text-sm font-bold rounded-lg hover:bg-red-50 transition-colors">Clear</button>
                </div>
            </div>

            <!-- Student table -->
            <div class="rounded-xl border-2 border-gray-300 overflow-hidden" style="max-height: 360px; overflow-y: auto;">
                <form id="assignStudentsForm" method="POST">
                    <input type="hidden" name="action" value="assign_students">
                    <input type="hidden" name="advisory_id" id="hiddenAdvisoryId" value="">
                    <table class="w-full text-base">
                        <thead class="sticky top-0 bg-gray-100 z-10">
                            <tr class="text-sm text-black font-bold uppercase">
                                <th class="py-3 px-5 border-b-2 border-gray-300 text-left">Select</th>
                                <th class="py-3 px-5 border-b-2 border-gray-300 text-left">Student Name</th>
                                <th class="py-3 px-5 border-b-2 border-gray-300 text-left">LRN</th>
                                <th class="py-3 px-5 border-b-2 border-gray-300 text-left">Current Grade</th>
                                <th class="py-3 px-5 border-b-2 border-gray-300 text-left">Change Grade</th>
                            </tr>
                        </thead>
                        <tbody id="modalStudentTable" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <span class="text-base text-gray-700 font-medium">Loading students…</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <div class="px-8 py-6 border-t border-gray-200 flex gap-4 shrink-0">
            <button onclick="closeStudentModal()"
                class="flex-1 border-2 border-gray-300 text-black font-bold py-4 rounded-xl hover:bg-gray-50 transition-colors text-lg">
                Cancel
            </button>
            <button onclick="confirmStudentAssignment()"
                class="flex-1 bg-[#f8c922] text-[#043915] font-bold py-4 rounded-xl hover:bg-yellow-300 transition-colors shadow-lg text-lg flex items-center justify-center gap-2">
                Confirm Assignment
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
            <h2 class="text-xl font-bold text-white">Assign Teacher Role</h2>
            <p class="text-sm text-green-300 mt-1">Add a teacher as advisory or subject teacher</p>
        </div>

        <form id="assignTeacherForm" onsubmit="submitTeacherAssignment(event)" class="p-7 space-y-5">
            <input type="hidden" name="action" value="assign_teacher">

            <div>
                <label class="block text-sm font-bold text-black mb-2">Teacher</label>
                <select name="teacher_id" id="teacherSelect" required
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                    <option value="">Select a teacher…</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= $teacher['user_id'] ?>">
                            <?= htmlspecialchars($teacher['name']) ?> — <?= htmlspecialchars($teacher['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-black mb-2">Role</label>
                <select name="role_type" id="teacherRoleType" required
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium"
                    onchange="toggleAdvisoryFields()">
                    <option value="">Select role…</option>
                    <option value="subject">Subject Teacher</option>
                    <option value="advisory">Advisory Teacher</option>
                </select>
            </div>

            <div id="advisoryFields" class="hidden bg-emerald-50 border-2 border-emerald-400 rounded-2xl p-5 space-y-4">
                <p class="text-sm font-bold text-emerald-900 uppercase tracking-wider">Advisory Class Details</p>
                <div>
                    <label class="block text-sm font-bold text-black mb-2">Class Name</label>
                    <input type="text" name="advisory_name" id="advisoryNameInput"
                        placeholder="e.g. Diamond 7-A, Emerald 8-B"
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black">
                </div>
                <div>
                    <label class="block text-sm font-bold text-black mb-2">Grade Level</label>
                    <select name="grade_level" id="advisoryGradeLevel"
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                        <option value="">Select grade…</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTeacherModal()"
                    class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 bg-[#f8c922] text-[#043915] font-bold py-3 rounded-xl hover:bg-yellow-300 transition-colors shadow-lg text-base">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- REASSIGN MODAL                                                     -->
<!-- ================================================================ -->
<div id="reassignModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">

        <div class="px-8 py-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-black">Reassign Student</h2>
            <p class="text-lg text-gray-800 mt-1">
                Moving <span id="reassignStudentName" class="font-bold text-[#043915]"></span> to a new advisory class
            </p>
        </div>

        <form id="reassignForm" onsubmit="submitReassignment(event)" class="p-7 space-y-5">
            <input type="hidden" name="action" value="reassign_student">
            <input type="hidden" name="assignment_id" id="reassignAssignmentId">
            <input type="hidden" name="current_grade" id="reassignCurrentGrade">
            <div>
                <label class="block text-sm font-bold text-black mb-2">New Advisory Teacher</label>
                <select name="new_advisory_id" id="reassignAdvisorySelect" required
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-base bg-white focus:outline-none focus:ring-2 focus:ring-[#043915]/30 focus:border-[#043915] transition-all text-black font-medium">
                    <option value="">Select advisory class…</option>
                    <?php foreach ($advisoryTeachers as $teacher): ?>
                        <option value="<?= $teacher['advisory_id'] ?>" data-grade="<?= $teacher['grade_level'] ?>">
                            <?= htmlspecialchars($teacher['teacher_name']) ?> — <?= htmlspecialchars($teacher['advisory_name']) ?> (Grade <?= $teacher['grade_level'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeReassignModal()"
                    class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors text-base">
                    Reassign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- REMOVE FROM ADVISORY MODAL                                        -->
<!-- ================================================================ -->
<div id="removeAdvisoryModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-8 text-center">
        <h2 class="text-xl font-bold text-black mb-3">Remove from Advisory</h2>
        <p class="text-lg text-gray-800 mb-7 leading-relaxed font-medium">
            Are you sure you want to remove <strong id="removeStudentName" class="text-[#043915]"></strong> from their advisory class?
            They will become unassigned.
        </p>
        <form id="removeAdvisoryForm" onsubmit="submitRemoval(event)">
            <input type="hidden" name="action" value="remove_from_advisory">
            <input type="hidden" name="assignment_id" id="removeAssignmentId">
            <div class="flex gap-3">
                <button type="button" onclick="closeRemoveAdvisoryModal()"
                    class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 bg-red-600 text-white font-bold py-3 rounded-xl hover:bg-red-700 transition-colors text-base">
                    Remove
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- CONVERT TEACHER MODAL                                             -->
<!-- ================================================================ -->
<div id="convertTeacherModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-8 text-center">
        <h2 class="text-xl font-bold text-black mb-3">Convert to Subject Teacher</h2>
        <p class="text-lg text-gray-800 mb-7 leading-relaxed font-medium">
            <strong id="convertTeacherName" class="text-[#043915]"></strong> will be converted to a Subject Teacher.
            All their advisory students will be <strong>unassigned</strong>. This cannot be undone.
        </p>
        <form id="convertTeacherForm" onsubmit="submitConversion(event)">
            <input type="hidden" name="action" value="convert_to_subject">
            <input type="hidden" name="advisory_id" id="convertAdvisoryId">
            <div class="flex gap-3">
                <button type="button" onclick="closeConvertTeacherModal()"
                    class="flex-1 border-2 border-gray-300 text-black font-bold py-3 rounded-xl hover:bg-gray-50 transition-colors text-base">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 bg-orange-600 text-white font-bold py-3 rounded-xl hover:bg-orange-700 transition-colors text-base">
                    Convert
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- VIEW ADVISORY DETAILS MODAL                                       -->
<!-- ================================================================ -->
<div id="viewAdvisoryModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

        <div class="px-8 py-6 border-b border-gray-200 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-black" id="advisoryDetailTitle">Advisory Students</h2>
                <p class="text-lg text-gray-800 mt-1" id="advisoryDetailSubtitle"></p>
            </div>
            <button onclick="closeViewAdvisoryModal()"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition-colors shrink-0 text-xl">
                ✕
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8" id="advisoryStudentsList"></div>

        <div id="gradePromotionControls" class="hidden px-8 py-6 border-t border-gray-200 shrink-0 bg-gray-50">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-bold text-black mb-1">Grade Promotion</p>
                    <p id="selectedCount" class="text-xs text-gray-500">0 students selected</p>
                </div>
                <div class="flex gap-2">
                    <select id="bulkGradeSelect" class="border-2 border-gray-300 rounded-lg px-3 py-2 text-sm font-bold bg-white text-gray-700 focus:ring-2 focus:ring-[#043915] focus:border-[#043915] transition">
                        <option value="">Select Grade Level</option>
                    </select>
                    <button onclick="confirmBulkGradePromotion()" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition text-sm">
                        Promote
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" id="selectAllStudentsPromotion" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" onchange="toggleAllStudentsPromotion(this.checked)">
                <label for="selectAllStudentsPromotion" class="text-sm font-medium text-gray-700">Select All Students</label>
            </div>
        </div>
            <button onclick="closeViewAdvisoryModal()"
                class="w-full bg-[#043915] text-white font-bold py-4 rounded-2xl hover:bg-[#055020] transition-colors text-lg">
                Close
            </button>
        </div>
    </div>
</div>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/advisories-helper.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>