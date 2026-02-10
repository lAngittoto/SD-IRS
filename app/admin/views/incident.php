<?php
ob_start();
?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gray-100 p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__.'/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__.'/../../../includes/admin-header.php'; ?>

    <section class="mb-8 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
        <div class="text-center lg:text-left">
            <h1 class="text-xl sm:text-2xl font-bold text-[#043915] flex items-center justify-center lg:justify-start gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clipboard-list text-green-600 text-lg"></i>
                </div>
                Incident Records
            </h1>
            <p class="text-sm text-gray-600 mt-1 lg:ml-13">Manage and review reported incidents</p>
        </div>

        <div class="w-full lg:w-80 relative">
            <div class="absolute left-0 top-0 bottom-0 w-12 bg-white rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" placeholder="Search incidents..." 
                class="w-full pl-14 pr-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm shadow-sm bg-white">
        </div>
    </section>

    <div class="flex flex-col xl:flex-row gap-6">

        <aside class="w-full xl:w-72 bg-white rounded-2xl p-6 shadow-lg h-fit border border-gray-50">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-[#043915]">Filters</h2>
                <button type="button" onclick="resetFilters()" class="text-[10px] font-bold text-red-500 hover:text-red-700 uppercase tracking-wider transition-all">
                    Reset Filters
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-1 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Reported By</label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-blue-100 rounded-l-lg flex items-center justify-center border-y border-l border-gray-200">
                            <i class="fa-solid fa-user-tie text-blue-600 text-xs"></i>
                        </div>
                        <select id="filterRole" class="filter-select w-full pl-12 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                            <option value="">All Roles</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Other">Other</option>
                            <option value="Unknown">Unknown</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Incident Status</label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-orange-100 rounded-l-lg flex items-center justify-center border-y border-l border-gray-200">
                            <i class="fa-solid fa-circle-info text-orange-600 text-xs"></i>
                        </div>
                        <select id="filterStatus" class="filter-select w-full pl-12 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Under Review">Under Review</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Violation Type</label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-purple-100 rounded-l-lg flex items-center justify-center border-y border-l border-gray-200">
                            <i class="fa-solid fa-triangle-exclamation text-purple-600 text-xs"></i>
                        </div>
                        <select id="filterViolation" class="filter-select w-full pl-12 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                            <option value="">All Violations</option>
                        </select>
                    </div>
                </div>
            </div>
        </aside>

        <section class="flex-1 flex flex-col gap-4 min-w-0">
            <div class="bg-white rounded-2xl shadow-md overflow-x-auto border border-gray-100" style="min-height:60vh;">
                <table class="w-full border-collapse text-left min-w-[700px]">
                    <thead>
                        <tr class="bg-[#043915]">
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Reported Individual</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Violation</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Status</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Actions</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Date Reported</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <?php
                        if (!empty($incidents)) {
                            foreach($incidents as $incident):
                                $statusColor = '';
                                switch ($incident['status']) {
                                    case 'Pending': $statusColor = 'bg-yellow-100 text-yellow-700'; break;
                                    case 'Under Review': $statusColor = 'bg-orange-100 text-orange-700'; break;
                                    case 'Resolved': $statusColor = 'bg-green-100 text-green-700'; break;
                                }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 text-center text-sm text-gray-700 font-medium"><?php echo $incident['name']; ?></td>
                            <td class="px-4 py-4 text-center text-sm text-gray-600"><?php echo $incident['violation']; ?></td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase <?php echo $statusColor; ?>">
                                    <?php echo $incident['status']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center relative">
                                <button class="action-btn text-gray-400 hover:text-[#043915] transition-colors p-1">
                                    <i class="fa-solid fa-ellipsis-vertical text-lg"></i>
                                </button>
                                
                                <div class="action-menu hidden absolute left-1/2 -translate-x-1/2 mt-1 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 z-50">
                                    <div class="flex flex-col py-2">
                                        <button class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-gray-50 text-xs text-gray-700">
                                            <i class="fa-regular fa-eye text-gray-400"></i> View Details
                                        </button>
                                        <div class="h-[1px] bg-gray-100 my-1"></div>
                                        <button class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-green-50 text-xs text-green-600 font-bold">
                                            <i class="fa-solid fa-circle-check"></i> Resolved
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center text-xs text-gray-500 font-mono"><?php echo $incident['date']; ?></td>
                        </tr>
                        <?php endforeach; } else { ?>
                            <tr>
                                <td colspan="5" class="text-center text-gray-400 py-10 text-sm">No incidents found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-center mt-2 gap-4 px-2">
                <p class="text-[11px] text-gray-400 uppercase font-bold tracking-widest">Showing Results</p>
                <div class="flex items-center gap-2">
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#f8c922] text-[#043915] font-bold shadow-md">1</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold">2</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold">3</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold">4</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold">5</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </section>
    </div>
</main>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/actions.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>