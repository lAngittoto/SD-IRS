<?php
ob_start();
?>

<main class="ml-64 min-h-screen bg-gray-100 p-8 w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__.'/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__.'/../../../includes/admin-header.php'; ?>

    <!-- PAGE HEADER -->
    <section class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#043915] flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-users text-green-600 text-lg"></i>
                </div>
                Manage Users
            </h1>
            <p class="text-sm text-gray-600 mt-1 ml-13">
                View and manage system users
            </p>
        </div>

        <!-- SEARCH -->
        <div class="w-full md:w-80 relative">
            <div class="absolute left-0 top-0 bottom-0 w-12 bg-white rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
            </div>
            <input type="text" placeholder="Search users..."
                class="w-full pl-14 pr-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm shadow-sm bg-white">
        </div>
    </section>

    <div class="flex gap-6">

        <!-- FILTER SIDEBAR -->
        <aside class="w-72 bg-white rounded-2xl p-6 shadow-lg h-fit border border-gray-50">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-[#043915]">Filters</h2>
                <button class="text-[10px] font-bold text-red-500 uppercase tracking-wider">
                    Reset
                </button>
            </div>

            <div class="space-y-4">
                <!-- ROLE FILTER -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">
                        User Role
                    </label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-blue-100 rounded-l-lg flex items-center justify-center border-y border-l border-gray-200">
                            <i class="fa-solid fa-user-tag text-blue-600 text-xs"></i>
                        </div>
                        <select
                            class="w-full pl-12 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                            <option value="">All Roles</option>
                            <option value="Student">Student</option>
                            <option value="Teacher">Teacher</option>
                        </select>
                    </div>
                </div>

                <!-- A→Z / Z→A FILTER -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">
                        Sort By Name
                    </label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-purple-100 rounded-l-lg flex items-center justify-center border-y border-l border-gray-200">
                            <i class="fa-solid fa-arrow-up-wide-short text-purple-600 text-xs"></i>
                        </div>
                        <select
                            class="w-full pl-12 pr-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                            <option value="asc">A → Z</option>
                            <option value="desc">Z → A</option>
                        </select>
                    </div>
                </div>
            </div>
        </aside>

        <!-- TABLE SECTION -->
        <section class="flex-1 flex flex-col gap-4">

            <!-- TABLE CARD -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100"
                 style="min-height:60vh;"> <!-- SAME HEIGHT AS INCIDENT -->
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-[#043915]">
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <?php if(!empty($users)): ?>
                            <?php foreach($users as $user): 
                                // Status color
                                $statusColor = $user['status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                                $roleColor = $user['role'] === 'Student' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700';
                            ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-4 text-center text-sm font-medium text-gray-700"><?php echo $user['name']; ?></td>
                                    <td class="px-4 py-4 text-center text-sm text-gray-600"><?php echo $user['email']; ?></td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase <?php echo $roleColor; ?>">
                                            <?php echo $user['role']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase <?php echo $statusColor; ?>">
                                            <?php echo $user['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center relative">
                                        <button class="action-btn text-gray-400 hover:text-[#043915] p-1">
                                            <i class="fa-solid fa-ellipsis-vertical text-lg"></i>
                                        </button>
                                        <!-- dropdown action menu can be added here -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-20 text-gray-300 text-sm">
                                    No users found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION SAME DESIGN AS INCIDENT -->
      
            <div class="flex justify-between items-center mt-2 px-2">
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
