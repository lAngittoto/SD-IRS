<?php
// Include controller
require_once __DIR__ . '/../controllers/users-controller.php';

// Initialize controller with PDO (assuming $pdo is available from routing)
$userController = new UserController($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController->createUser();
}

// Get all users
$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

$totalUsers = $userController->getTotalUsers();
$totalPages = ceil($totalUsers / $limit);

if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;

$users = $userController->getUsersPaginated($limit, $offset);

ob_start();
?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-[#f8fafc] p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>

    <!-- SUCCESS/ERROR MESSAGES -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div id="successAlert" class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-green-500 text-xl"></i>
                <p class="text-green-700 font-medium"><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div id="errorAlert" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i>
                <p class="text-red-700 font-medium"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- PAGE HEADER + SEARCH -->
    <section class="mb-8 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-6">
        <div class="text-center lg:text-left">
            <h1 class="text-2xl font-bold text-[#043915] flex items-center justify-center lg:justify-start gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-users text-green-600 text-lg"></i>
                </div>
                Manage Users
            </h1>
            <p class="text-sm text-gray-600 mt-1 lg:ml-13">
                View and manage system users
            </p>
        </div>

        <div class="w-full lg:w-80 relative">
            <div class="absolute left-0 top-0 bottom-0 w-12 bg-white rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
            </div>
            <input type="text" placeholder="Search users..."
                class="w-full pl-14 pr-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm shadow-sm bg-white">
        </div>
    </section>

    <div class="flex flex-col xl:flex-row gap-8 items-start">

        <!-- SIDEBAR -->
        <aside class="w-full xl:w-72 space-y-6 h-fit">
            <button onclick="openModal('createUserModal')" class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-[#f8c922] text-[#043915] rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-yellow-300 transition-all shadow-lg shadow-green-900/10 cursor-pointer">
                <i class="fa-solid fa-user-plus text-base"></i>
                Create New User
            </button>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-[#043915]">Filters</h2>
                    <button type="button" onclick="resetFilters()" class="text-[10px] font-bold text-red-500 hover:text-red-700 uppercase tracking-wider transition-all">
                        Reset Filters
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">User Role</label>
                        <div class="relative">
                            <div class="absolute left-0 top-0 bottom-0 w-10 bg-blue-50 rounded-l-lg flex items-center justify-center border-y border-l border-gray-100">
                                <i class="fa-solid fa-user-tag text-blue-500 text-xs"></i>
                            </div>
                            <select class="w-full pl-12 pr-3 py-2.5 rounded-lg border border-gray-100 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50" id="userRole" name="role">
                                <option value="">All Roles</option>
                                <option value="Student">Student</option>
                                <option value="Teacher">Teacher</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Sort By Name</label>
                        <div class="relative">
                            <div class="absolute left-0 top-0 bottom-0 w-10 bg-purple-50 rounded-l-lg flex items-center justify-center border-y border-l border-gray-100">
                                <i class="fa-solid fa-arrow-up-wide-short text-purple-500 text-xs"></i>
                            </div>
                            <select class="w-full pl-12 pr-3 py-2.5 rounded-lg border border-gray-100 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
                                <option value="asc">A → Z</option>
                                <option value="desc">Z → A</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN TABLE -->
        <section class="flex-1 w-full overflow-hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-green-100 flex flex-col min-h-[60vh] overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-[#043915]">
                                <th class="px-6 py-4 text-center text-white text-[11px] font-bold uppercase tracking-widest rounded-tl-2xl">Name</th>
                                <th class="px-6 py-4 text-center text-white text-[11px] font-bold uppercase tracking-widest">Email/LRN</th>
                                <th class="px-6 py-4 text-center text-white text-[11px] font-bold uppercase tracking-widest rounded-tr-2xl">Role</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user):
                                    // Initials Generator (e.g., Allan Aranda -> AA)
                                    $nameParts = explode(' ', trim($user['name']));
                                    $initials = strtoupper(substr($nameParts[0], 0, 1) . (count($nameParts) > 1 ? substr(end($nameParts), 0, 1) : ''));

                                    $isStudent = ($user['role'] === 'Student');
                                    $roleColor = $isStudent ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-green-100 text-green-800 border-green-200';
                                    $avatarColor = $isStudent ? 'bg-emerald-100 text-emerald-600' : 'bg-green-200 text-green-800';

                                    $identifier = $isStudent ? $user['lrn'] : ($user['email'] ?? 'N/A');
                                ?>
                                    <tr class="hover:bg-green-50/40 transition-all duration-200 border-b border-gray-100">

                                        <td class="px-4 py-4 md:px-6">
                                            <div class="flex items-center justify-center gap-3">
                                                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-[11px] shrink-0 <?php echo $avatarColor; ?> shadow-sm border border-white/50">
                                                    <?php echo $initials; ?>
                                                </div>
                                                <span class="text-sm md:text-base font-bold text-gray-700 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($user['name']); ?>
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 md:px-6 text-center">
                                            <span class="text-xs md:text-sm font-medium text-gray-500 font-mono bg-gray-50/80 px-3 py-1.5 rounded-lg border border-gray-100">
                                                <?php echo htmlspecialchars($identifier); ?>
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 md:px-6 text-center">
                                            <span class="px-4 py-1.5 text-[10px] md:text-xs font-black rounded-full uppercase tracking-widest border <?php echo $roleColor; ?> shadow-sm">
                                                <?php echo htmlspecialchars($user['role']); ?>
                                            </span>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-32 text-gray-300 text-sm italic">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION + RESULTS INFO -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-6 px-2 gap-4">
                <p class="text-[11px] text-gray-400 uppercase font-bold tracking-widest">Showing  Results</p>
                <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0">
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition-all shadow-sm">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>

                 <?php include __DIR__ . '/../../../helpers/user-pignation.php'; ?>


        
                    </button>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- MODAL -->
<div id="createUserModal" class="fixed inset-0 z-999 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="bg-[#043915] p-6 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold">New System User</h3>
                    <p class="text-[10px] text-green-200 uppercase tracking-widest">Add student or faculty access</p>
                </div>
            </div>

            <form id="createUserForm" method="POST" class="p-8 space-y-5">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Full Name</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                        <input type="text" name="name" required placeholder="Juan Dela Cruz"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">LRN / ID Number</label>
                    <div class="relative">
                        <i class="fa-solid fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                        <input type="text" id="lrnInput" name="lrn" placeholder="123456789012" maxlength="12" inputmode="numeric" pattern="\d{12}" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50 disabled:bg-gray-200 disabled:cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">
                        Email Address
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                        <input type="email" id="emailInput" name="email" placeholder="example@email.com" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50 disabled:bg-gray-200 disabled:cursor-not-allowed">
                    </div>
                </div>


                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Access Role</label>
                    <select id="modalRole" name="role" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50">
                        <option value="Student">Student</option>
                        <option value="Teacher">Teacher</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">System Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                        <input type="password" id="userPassword" name="password" autocomplete="new-password" placeholder="••••••••" required
                            class="w-full pl-11 pr-12 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50">
                        <button type="button" onclick="togglePasswordVisibility('userPassword', 'eyeIcon')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#043915]">
                            <i id="eyeIcon" class="fa-solid fa-eye text-sm cursor-pointer"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('createUserModal')" class="flex-1 py-3 border border-gray-200 text-gray-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-[#f8c922] text-[#043915] rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-yellow-400 transition-all shadow-md shadow-yellow-200 cursor-pointer">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/user-helper.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>