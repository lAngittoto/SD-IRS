<?php
ob_start();
?>

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
            <input type="text" placeholder="Search assignments..." 
                class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#043915] shadow-sm">
        </div>
    </section>

    <div class="flex flex-col xl:flex-row gap-8 items-start">
        
        <aside class="w-full xl:w-72 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-[#043915]">Filters</h2>
                <button class="text-[10px] font-bold text-red-500 uppercase tracking-tighter hover:underline">Reset Filters</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-6">
                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <div class="w-6 h-6 bg-blue-50 rounded flex items-center justify-center">
                            <i class="fa-solid fa-chalkboard-user text-blue-500 text-[10px]"></i>
                        </div>
                        Teacher
                    </label>
                    <select class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#043915]">
                        <option value="">All Teachers</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <div class="w-6 h-6 bg-orange-50 rounded flex items-center justify-center">
                            <i class="fa-solid fa-user-graduate text-orange-500 text-[10px]"></i>
                        </div>
                        Student Name
                    </label>
                    <select class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#043915]">
                        <option value="">All Students</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <div class="w-6 h-6 bg-green-50 rounded flex items-center justify-center">
                            <i class="fa-solid fa-circle-check text-green-500 text-[10px]"></i>
                        </div>
                        Status
                    </label>
                    <select class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#043915]">
                        <option value="">All Statuses</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <div class="w-6 h-6 bg-purple-50 rounded flex items-center justify-center">
                            <i class="fa-solid fa-calendar-days text-purple-500 text-[10px]"></i>
                        </div>
                        Assignment Date
                    </label>
                    <input type="date" class="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#043915]">
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-100">
                <button class="w-full flex items-center justify-center gap-2 bg-[#f8c922] text-[#043915] px-4 py-3 rounded-xl text-xs font-bold hover:bg-yellow-300 transition shadow-md">
                    <i class="fa-solid fa-user-plus"></i> Assign Student
                </button>
            </div>
        </aside>

        <section class="flex-1 w-full overflow-hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col min-h-[60vh] overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="bg-[#043915]">
                            <tr>
                                <th class="px-6 py-4 text-white text-[11px] font-bold uppercase tracking-wider text-center rounded-tl-2xl">Student</th>
                                <th class="px-6 py-4 text-white text-[11px] font-bold uppercase tracking-wider text-center">Advisory Teacher</th>
                                <th class="px-6 py-4 text-white text-[11px] font-bold uppercase tracking-wider text-center">Status</th>
                                <th class="px-6 py-4 text-white text-[11px] font-bold uppercase tracking-wider text-center">Actions</th>
                                <th class="px-6 py-4 text-white text-[11px] font-bold uppercase tracking-wider text-center rounded-tr-2xl">Date Assigned</th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-gray-50">
                            <?php if(!empty($assignments)): ?>
                                <?php foreach($assignments as $assign): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-700 font-medium text-center border-b border-gray-50">
                                            <?php echo $assign['student_name']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 text-center border-b border-gray-50">
                                            <?php echo $assign['teacher_name']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center border-b border-gray-50">
                                            <span class="px-3 py-1 bg-green-50 text-green-600 rounded-full text-[10px] font-bold uppercase">Active</span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-400 text-center border-b border-gray-50">
                                            <button class="hover:text-[#043915]"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500 font-mono text-center border-b border-gray-50">
                                            <?php echo $assign['date_assigned']; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-32 text-gray-300 text-sm italic">
                                        No assignments found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-center mt-6 px-2 gap-4">
                <p class="text-[11px] text-gray-400 uppercase font-bold tracking-widest">Showing Results</p>
                <div class="flex items-center gap-2 overflow-x-auto max-w-full pb-2">
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 shadow-sm hover:bg-gray-50">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#f8c922] text-[#043915] font-bold shadow-md">1</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50">2</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50">3</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50">4</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50">5</button>
                    
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 shadow-sm hover:bg-gray-50">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>