<?php
ob_start();

// Get data from controller
$teachers = $advisoriesController->getAllTeachers();
$advisoryTeachers = $advisoriesController->getAdvisoryTeachers();
$allStudents = $advisoriesController->getAllStudents();

?>

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-[200] space-y-3"></div>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-[#f8fafc] p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>

    <section class="mb-8 flex flex-col lg:flex-row lg:justify-between lg:items-end gap-6">
        <div class="text-center lg:text-left">
            <h1 class="text-2xl font-bold text-[#043915] flex items-center justify-center lg:justify-start gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-people-group text-[#043915] text-lg"></i>
                </div>
                Advisory Class Management
            </h1>
            <p class="text-sm text-gray-500 mt-1 lg:ml-13">
                Assign students to advisory teachers efficiently and track assignment dates
            </p>
        </div>

        <div class="relative w-full lg:w-72">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="searchInput" placeholder="Search assignments..."
                class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#043915] shadow-sm">
        </div>
    </section>

    <div class="flex flex-col xl:flex-row gap-8 items-start">

        <!-- Filters Sidebar -->
        <aside class="w-full xl:w-72 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-[#043915]">Filters</h2>
                <button type="button" onclick="resetFilters()" class="text-[10px] font-bold text-red-500 hover:text-red-700 uppercase tracking-wider transition-all">
                    Reset Filters
                </button>
            </div>

            <form id="filterForm" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-6" onsubmit="applyFilters(event)">
                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <div class="w-6 h-6 bg-blue-50 rounded flex items-center justify-center">
                            <i class="fa-solid fa-chalkboard-user text-blue-500 text-[10px]"></i>
                        </div>
                        Teacher Type
                    </label>
                    <select name="teacher_role" id="filterTeacherRole" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:ring-2 focus:ring-[#043915]">
                        <option value="">All Types</option>
                        <option value="advisory">Advisory Teacher</option>
                        <option value="subject">Subject Teacher</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <div class="w-6 h-6 bg-purple-50 rounded flex items-center justify-center">
                            <i class="fa-solid fa-arrow-down-a-z text-purple-500 text-[10px]"></i>
                        </div>
                        Sort Name
                    </label>
                    <select name="sort_name" id="sortName" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:ring-2 focus:ring-[#043915]">
                        <option value="ASC">A to Z</option>
                        <option value="DESC">Z to A</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <div class="w-6 h-6 bg-orange-50 rounded flex items-center justify-center">
                            <i class="fa-solid fa-user-graduate text-orange-500 text-[10px]"></i>
                        </div>
                        Grade Level
                    </label>
                    <select name="filter_grade" id="filterGrade" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:ring-2 focus:ring-[#043915]">
                        <option value="">All Grades</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <div class="w-6 h-6 bg-purple-50 rounded flex items-center justify-center">
                            <i class="fa-solid fa-calendar-days text-purple-500 text-[10px]"></i>
                        </div>
                        Assignment Date
                    </label>
                    <input type="date" name="filter_date" id="filterDate" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:ring-2 focus:ring-[#043915]">
                </div>

                <button type="submit" class="w-full bg-[#f8c922] text-[#043915] py-3 rounded-xl text-xs font-bold hover:bg-opacity-90 transition shadow-md">
                    Apply Filters
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-gray-100 space-y-4">
                <button onclick="openTeacherModal()" class="w-full flex items-center justify-center gap-2 bg-[#043915] text-white px-4 py-3 rounded-xl text-xs font-bold hover:bg-opacity-90 transition shadow-md">
                    <i class="fa-solid fa-chalkboard-user"></i> Assign Advisory Teacher
                </button>
                <button onclick="openStudentModal()" id="assignStudentBtn" class="w-full flex items-center justify-center gap-2 bg-[#f8c922] text-[#043915] px-4 py-3 rounded-xl text-xs font-bold hover:bg-yellow-300 transition shadow-md">
                    <i class="fa-solid fa-user-plus"></i> Assign Students
                </button>
                <div id="noAdvisoryMessage" class="hidden w-full bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl text-xs text-center">
                    <i class="fa-solid fa-info-circle mb-1"></i>
                    <p class="font-bold">No Advisory Teachers Available</p>
                    <p class="text-[10px] mt-1">Please assign an advisory teacher first before assigning students.</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Table -->
        <section class="flex-1 w-full overflow-hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col min-h-[60vh] overflow-hidden">
                <!-- Fixed height table container with scrollbar -->
                <div class="overflow-x-auto w-full flex-1" style="max-height: 600px; overflow-y: auto;">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-[#043915]">
                                <th class="py-4 px-6 text-left text-[10px] font-bold text-white uppercase tracking-widest border-b border-green-800">Student Name</th>
                                <th class="py-4 px-6 text-left text-[10px] font-bold text-white uppercase tracking-widest border-b border-green-800">LRN</th>
                                <th class="py-4 px-6 text-left text-[10px] font-bold text-white uppercase tracking-widest border-b border-green-800">Grade Level</th>
                                <th class="py-4 px-6 text-left text-[10px] font-bold text-white uppercase tracking-widest border-b border-green-800">Advisory Teacher</th>
                                <th class="py-4 px-6 text-left text-[10px] font-bold text-white uppercase tracking-widest border-b border-green-800">Advisory Class</th>
                                <th class="py-4 px-6 text-left text-[10px] font-bold text-white uppercase tracking-widest border-b border-green-800">Assigned Date</th>
                                <th class="py-4 px-6 text-center text-[10px] font-bold text-white uppercase tracking-widest border-b border-green-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fa-solid fa-filter text-4xl mb-4 opacity-20"></i>
                                        <p class="text-sm font-medium text-gray-600">No Data to Display</p>
                                        <p class="text-xs text-gray-500 mt-1">Apply filters above to view student assignments</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Section -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-6 px-2 gap-4">
                <p id="resultCount" class="text-[11px] text-gray-400 uppercase font-bold tracking-widest">Showing Results</p>

                <div id="paginationContainer" class="flex items-center gap-2">
                    <!-- Pagination buttons will be generated here -->
                </div>
            </div>

        </section>

    </div>
</main>

<!-- Student Assignment Modal -->
<div id="studentModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-8 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-[#043915]">Assign Students</h2>
                <p class="text-xs text-gray-500 uppercase tracking-widest font-bold">Select students and assign to an Advisory Teacher</p>
            </div>
            <button onclick="closeStudentModal()" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-all md:ml-auto">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <div class="p-8 overflow-hidden flex flex-col flex-1">
            <!-- Grade Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 mb-6 bg-white p-3 rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-[10px] font-bold text-gray-400 uppercase mr-2">Filter by Grade:</span>
                <button type="button" onclick="filterModalStudents('all')" id="gradeTab_all" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold uppercase transition hover:bg-gray-200">
                    All
                </button>
                <button type="button" onclick="filterModalStudents('7')" id="gradeTab_7" class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold uppercase transition hover:bg-blue-100">
                    Grade 7
                </button>
                <button type="button" onclick="filterModalStudents('8')" id="gradeTab_8" class="px-4 py-2 bg-orange-50 text-orange-600 rounded-lg text-xs font-bold uppercase transition hover:bg-orange-100">
                    Grade 8
                </button>
                <button type="button" onclick="filterModalStudents('9')" id="gradeTab_9" class="px-4 py-2 bg-purple-50 text-purple-600 rounded-lg text-xs font-bold uppercase transition hover:bg-purple-100">
                    Grade 9
                </button>
                <button type="button" onclick="filterModalStudents('10')" id="gradeTab_10" class="px-4 py-2 bg-green-50 text-green-600 rounded-lg text-xs font-bold uppercase transition hover:bg-green-100">
                    Grade 10
                </button>
            </div>

            <div id="loadingStudents" class="hidden py-12 text-center">
                <i class="fa-solid fa-spinner fa-spin text-4xl text-[#043915] mb-3"></i>
                <p class="text-sm text-gray-600">Loading students...</p>
            </div>

            <div id="studentListContainer" class="flex flex-col h-full">
                <!-- Advisory Selection -->
                <div class="mb-6">
                    <label class="text-[10px] font-bold text-gray-400 uppercase block mb-2">Select Advisory Teacher</label>
                    <select id="modalAdvisoryTeacher" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#043915] bg-white">
                        <option value="">Choose Teacher...</option>
                        <?php foreach ($advisoryTeachers as $teacher): ?>
                            <option value="<?= $teacher['advisory_id'] ?>"
                                data-current-count="<?= $teacher['student_count'] ?? 0 ?>"
                                data-grade-level="<?= $teacher['grade_level'] ?>">
                                <?= htmlspecialchars($teacher['teacher_name']) ?> - <?= htmlspecialchars($teacher['advisory_name']) ?> (Grade <?= $teacher['grade_level'] ?>) - <?= $teacher['student_count'] ?? 0 ?>/40
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Advisory Capacity Info -->
                <div id="advisoryCapacityInfo" class="hidden mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center gap-3">
                        <i class="fa-solid fa-info-circle text-blue-500 text-xl"></i>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-blue-900">Advisory Capacity - Grade <span id="advisoryGradeLevel">7</span></p>
                            <p class="text-xs text-blue-700 mt-1">
                                <span id="currentStudentCount">0</span> students assigned.
                                You can assign up to <span id="remainingSlots">40</span> more students (Maximum: 40)
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 mb-6 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <span class="text-[10px] font-bold text-gray-400 uppercase mr-2">Quick Select:</span>
                    <button type="button" onclick="toggleAllVisibleStudents(true)" class="px-4 py-2 bg-[#043915] text-white rounded-lg text-[10px] font-bold uppercase transition hover:bg-opacity-90">Select All Visible</button>
                    <button type="button" onclick="toggleAllVisibleStudents(false)" class="px-4 py-2 border border-red-200 text-red-500 rounded-lg text-[10px] font-bold uppercase hover:bg-red-50 transition ml-auto">Clear Selection</button>
                </div>

                <div class="flex-1 overflow-y-auto rounded-xl border border-gray-100" style="max-height: 400px;">
                    <form id="assignStudentsForm" method="POST">
                        <input type="hidden" name="action" value="assign_students">
                        <input type="hidden" name="advisory_id" id="hiddenAdvisoryId" value="">

                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-gray-50 z-10">
                                <tr class="text-[10px] text-gray-400 uppercase font-bold">
                                    <th class="py-4 px-6 border-b">Select</th>
                                    <th class="py-4 px-6 border-b">Student Name</th>
                                    <th class="py-4 px-6 border-b">LRN</th>
                                    <th class="py-4 px-6 border-b">Current Grade</th>
                                </tr>
                            </thead>
                            <tbody id="modalStudentTable" class="divide-y divide-gray-50">
                                <tr>
                                    <td colspan="4" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="fa-solid fa-users text-4xl mb-3 opacity-20"></i>
                                            <p class="text-sm font-medium text-gray-600">Loading Available Students...</p>
                                            <p class="text-xs text-gray-500 mt-1">Please wait while we fetch the student list</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>

        <div class="p-5 bg-gray-50 border-t border-gray-100 flex gap-4">
            <button type="button" onclick="closeStudentModal()" class="flex-1 px-6 py-4 border border-gray-200 text-gray-600 rounded-2xl font-bold text-sm hover:bg-white transition">Cancel</button>
            <button type="button" onclick="confirmStudentAssignment()" class="flex-1 px-6 py-4 bg-[#f8c922] text-[#043915] rounded-2xl font-bold text-sm hover:bg-opacity-90 transition shadow-xl">Confirm Assignment</button>
        </div>

    </div>
</div>

<!-- Teacher Assignment Modal -->
<div id="teacherModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl relative">
        <button onclick="closeTeacherModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        <h2 class="text-xl font-bold text-[#043915] mb-6">Assign Teacher Role</h2>

        <form id="assignTeacherForm" onsubmit="submitTeacherAssignment(event)">
            <input type="hidden" name="action" value="assign_teacher">

            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Teacher</label>
                    <select name="teacher_id" id="teacherSelect" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#043915] bg-white">
                        <option value="">Select Teacher...</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher['user_id'] ?>">
                                <?= htmlspecialchars($teacher['name']) ?> - <?= htmlspecialchars($teacher['email']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Role Type</label>
                    <select name="role_type" id="teacherRoleType" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#043915] bg-gray-50" onchange="toggleAdvisoryFields()">
                        <option value="">Select Role...</option>
                        <option value="subject">Subject Teacher</option>
                        <option value="advisory">Advisory Teacher</option>
                    </select>
                </div>

                <div id="advisoryFields" class="hidden space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Advisory Class Name</label>
                        <input type="text" name="advisory_name" id="advisoryNameInput" placeholder="e.g. Diamond-7, Emerald-8" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#043915]">
                        <p class="text-xs text-gray-500 mt-1">Enter a unique name for the advisory class</p>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Grade Level to Teach</label>
                        <select name="grade_level" id="advisoryGradeLevel" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#043915] bg-white">
                            <option value="">Select Grade Level...</option>
                            <option value="7">Grade 7</option>
                            <option value="8">Grade 8</option>
                            <option value="9">Grade 9</option>
                            <option value="10">Grade 10</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#f8c922] text-[#043915] py-4 rounded-xl font-bold text-sm shadow-lg mt-4 hover:bg-opacity-90 transition">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Reassign Student Modal -->
<div id="reassignModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl relative">
        <button onclick="closeReassignModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        <h2 class="text-xl font-bold text-[#043915] mb-2">Reassign Student</h2>
        <p class="text-xs text-gray-500 mb-6">Change advisory assignment for <span id="reassignStudentName" class="font-bold text-[#043915]"></span></p>

        <form id="reassignForm" onsubmit="submitReassignment(event)">
            <input type="hidden" name="action" value="reassign_student">
            <input type="hidden" name="assignment_id" id="reassignAssignmentId">
            <input type="hidden" name="current_grade" id="reassignCurrentGrade">

            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">New Advisory Teacher</label>
                    <select name="new_advisory_id" id="reassignAdvisorySelect" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#043915] bg-white">
                        <option value="">Select New Advisory...</option>
                        <?php foreach ($advisoryTeachers as $teacher): ?>
                            <option value="<?= $teacher['advisory_id'] ?>" data-grade="<?= $teacher['grade_level'] ?>">
                                <?= htmlspecialchars($teacher['teacher_name']) ?> - <?= htmlspecialchars($teacher['advisory_name']) ?> (Grade <?= $teacher['grade_level'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="w-full bg-[#f8c922] text-[#043915] py-4 rounded-xl font-bold text-sm shadow-lg mt-4 hover:bg-opacity-90 transition">Reassign Student</button>
            </div>
        </form>
    </div>
</div>

<!-- Remove from Advisory Modal -->
<div id="removeAdvisoryModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl relative">
        <button onclick="closeRemoveAdvisoryModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-user-slash text-red-500 text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-[#043915] mb-2">Remove from Advisory</h2>
            <p class="text-sm text-gray-600 mb-6">Are you sure you want to remove <span id="removeStudentName" class="font-bold text-[#043915]"></span> from their advisory class?</p>

            <form id="removeAdvisoryForm" onsubmit="submitRemoval(event)">
                <input type="hidden" name="action" value="remove_from_advisory">
                <input type="hidden" name="assignment_id" id="removeAssignmentId">

                <div class="flex gap-3">
                    <button type="button" onclick="closeRemoveAdvisoryModal()" class="flex-1 px-6 py-3 border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-red-500 text-white rounded-xl font-bold text-sm hover:bg-red-600 transition">Remove</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Convert Teacher Role Modal -->
<div id="convertTeacherModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl relative">
        <button onclick="closeConvertTeacherModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        <div class="text-center">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-repeat text-orange-500 text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-[#043915] mb-2">Convert to Subject Teacher</h2>
            <p class="text-sm text-gray-600 mb-6">Converting <span id="convertTeacherName" class="font-bold text-[#043915]"></span> to a Subject Teacher will remove all their advisory students. This action cannot be undone.</p>

            <form id="convertTeacherForm" onsubmit="submitConversion(event)">
                <input type="hidden" name="action" value="convert_to_subject">
                <input type="hidden" name="advisory_id" id="convertAdvisoryId">

                <div class="flex gap-3">
                    <button type="button" onclick="closeConvertTeacherModal()" class="flex-1 px-6 py-3 border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-orange-500 text-white rounded-xl font-bold text-sm hover:bg-orange-600 transition">Convert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Advisory Details Modal with Grade Promotion -->
<div id="viewAdvisoryModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-8 border-b border-gray-100 bg-gray-50/50 flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-black text-[#043915]" id="advisoryDetailTitle">Advisory Details</h2>
                <p class="text-xs text-gray-500 uppercase tracking-widest font-bold mt-1" id="advisoryDetailSubtitle"></p>
            </div>
            <button onclick="closeViewAdvisoryModal()" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-all">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <div class="p-8 overflow-y-auto flex-1">
            <!-- Grade Promotion Controls -->
            <div id="gradePromotionControls" class="hidden mb-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-6 border border-blue-200">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Grade Promotion</h3>
                            <p class="text-xs text-gray-600">Select students and promote to higher grade</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" id="selectAllStudentsPromotion" onchange="toggleAllStudentsPromotion(this.checked)" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm font-bold text-gray-700">Select All</span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <label class="text-sm font-bold text-gray-700">Promote to:</label>
                    <select id="bulkGradeSelect" class="px-4 py-2 border-2 border-gray-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Select Grade Level</option>
                    </select>
                    <button type="button" onclick="confirmBulkGradePromotion()" class="px-6 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition shadow-md">
                        <i class="fa-solid fa-arrow-up mr-2"></i>Confirm Promotion
                    </button>
                    <span id="selectedCount" class="text-xs font-bold text-gray-600 ml-auto">0 students selected</span>
                </div>
            </div>

            <div id="advisoryStudentsList">
                <!-- Student list will be loaded here -->
            </div>
        </div>

        <div class="p-8 bg-gray-50 border-t border-gray-100">
            <button type="button" onclick="closeViewAdvisoryModal()" class="w-full px-6 py-4 bg-[#043915] text-white rounded-2xl font-bold text-sm hover:bg-opacity-90 transition">Close</button>
        </div>
    </div>
</div>


<script src="/student-discipline-and-incident-reporting-system/public/assets/js/advisories-helper.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>