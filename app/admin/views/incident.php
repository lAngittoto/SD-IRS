<?php
ob_start();
?>

<main class="ml-64 min-h-screen bg-gray-100 p-6 max-w-full overflow-x-hidden">

    <?php include __DIR__.'/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__.'/../../../includes/admin-header.php'; ?>

    <section class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0B3C5D]">Incident Records</h1>
            <p class="text-sm text-gray-600">Manage and review reported incidents</p>
        </div>

        <div class="w-full md:w-64">
            <input type="text" id="searchInput" placeholder="Search incidents..." 
                class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0B3C5D] text-sm shadow-sm">
        </div>
    </section>

    <div class="flex gap-6">

        <aside class="w-72 bg-white rounded-2xl p-6 shadow-lg h-fit border border-gray-50">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-[#0B3C5D]">Filters</h2>
                <button type="button" onclick="resetFilters()" class="text-[10px] font-bold text-red-500 hover:text-red-700 uppercase tracking-wider transition-all">
                    Reset Filters
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Reported By Dropdown -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-widest">Reported By</label>
                    <select id="filterRole" class="filter-select w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#0B3C5D] text-sm bg-gray-50">
                        <option value="">All Roles</option>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Other">Other</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>

                <!-- Incident Status Dropdown -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-widest">Incident Status</label>
                    <select id="filterStatus" class="filter-select w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#0B3C5D] text-sm bg-gray-50">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Under Review">Under Review</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>

                <!-- Violation Type Dropdown (Empty / Dynamic) -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-widest">Violation Type</label>
                    <select id="filterViolation" class="filter-select w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#0B3C5D] text-sm bg-gray-50">
                        <option value="">All Violations</option>
                        <!-- Options will come dynamically from the database -->
                    </select>
                </div>
            </div>
        </aside>

        <section class="flex-1 flex flex-col gap-4">

            <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100" style="min-height:60vh;">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-[#0B3C5D]">
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Reported Individual</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Violation</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Status</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Actions</th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">Date Reported</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <?php
                        // Fetch incidents dynamically from your database
                        // Example: $incidents = fetchIncidentsFromDB();

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
                                <button class="action-btn text-gray-400 hover:text-[#0B3C5D] transition-colors p-1">
                                    <i class="fa-solid fa-ellipsis-vertical text-lg"></i>
                                </button>
                                
                                <div class="action-menu hidden absolute left-1/2 -translate-x-1/2 mt-1 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 z-50">
                                    <div class="flex flex-col py-2">
                                        <button class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-gray-50 text-xs text-gray-700">
                                            <i class="fa-regular fa-eye text-gray-400"></i> View Details
                                        </button>
                                        <button class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-gray-50 text-xs text-gray-700">
                                            <i class="fa-regular fa-comment-dots text-gray-400"></i> Send Message
                                        </button>
                                        <div class="h-[1px] bg-gray-100 my-1"></div>
                                        <p class="px-4 py-1 text-[9px] text-gray-400 font-bold uppercase tracking-widest">Update Status</p>
                                        
                                        <button class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-yellow-50 text-xs text-yellow-600">
                                            <i class="fa-solid fa-clock-rotate-left"></i> Pending
                                        </button>
                                        <button class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-orange-50 text-xs text-orange-600">
                                            <i class="fa-solid fa-magnifying-glass"></i> Under Review
                                        </button>
                                        <button class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-green-50 text-xs text-green-600 font-bold">
                                            <i class="fa-solid fa-circle-check"></i> Resolved
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center text-xs text-gray-500 font-mono"><?php echo $incident['date']; ?></td>
                        </tr>
                        <?php 
                            endforeach; 
                        } else {
                            echo '<tr><td colspan="5" class="text-center text-gray-400 py-4 text-sm">No incidents found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-2 px-2">
                <p class="text-[11px] text-gray-400 uppercase font-bold tracking-widest">Showing Results</p>
                <div class="flex items-center gap-2">
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#0B3C5D] text-white font-bold shadow-md">1</button>
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

<script>
function resetFilters() {
    document.querySelectorAll('.filter-select').forEach(select => select.selectedIndex = 0);
    document.getElementById('searchInput').value = '';
    console.log("All filters have been reset.");
}

document.addEventListener("DOMContentLoaded", () => {
    const actionButtons = document.querySelectorAll(".action-btn");

    actionButtons.forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            const menu = btn.nextElementSibling;
            document.querySelectorAll(".action-menu").forEach(m => {
                if (m !== menu) m.classList.add("hidden");
            });
            menu.classList.toggle("hidden");
        });
    });

    document.addEventListener("click", () => {
        document.querySelectorAll(".action-menu").forEach(menu => menu.classList.add("hidden"));
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>
