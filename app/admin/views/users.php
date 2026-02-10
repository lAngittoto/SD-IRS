<?php
ob_start();
?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-[#f8fafc] p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__.'/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__.'/../../../includes/admin-header.php'; ?>

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

        <aside class="w-full xl:w-72 space-y-6 h-fit">
            <button onclick="openModal('createUserModal')" class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-[#f8c922] text-[#043915] rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-yellow-300 transition-all shadow-lg shadow-green-900/10">
                <i class="fa-solid fa-user-plus text-base"></i>
                Create New User
            </button>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-[#043915]">Filters</h2>
                    <button type="button" class="text-[10px] font-bold text-red-500 hover:text-red-700 uppercase tracking-wider transition-all">
                        Reset
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">User Role</label>
                        <div class="relative">
                            <div class="absolute left-0 top-0 bottom-0 w-10 bg-blue-50 rounded-l-lg flex items-center justify-center border-y border-l border-gray-100">
                                <i class="fa-solid fa-user-tag text-blue-500 text-xs"></i>
                            </div>
                            <select class="w-full pl-12 pr-3 py-2.5 rounded-lg border border-gray-100 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50">
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

        <section class="flex-1 w-full overflow-hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col min-h-[60vh] overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-separate border-spacing-0 text-left">
                        <thead>
                            <tr class="bg-[#043915]">
                                <th class="px-6 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider rounded-tl-2xl border-none">Name</th>
                                <th class="px-6 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider border-none">Email/ID</th>
                                <th class="px-6 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider border-none">Role</th>
                                <th class="px-6 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider border-none">Status</th>
                                <th class="px-6 py-4 text-center text-white text-[11px] font-bold uppercase tracking-wider rounded-tr-2xl border-none">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-50">
                            <?php if(!empty($users)): ?>
                                <?php foreach($users as $user): 
                                    $statusColor = $user['status'] === 'Active' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600';
                                    $roleColor = $user['role'] === 'Student' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600';
                                ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 text-center text-sm font-medium text-gray-700"><?php echo $user['name']; ?></td>
                                        <td class="px-6 py-4 text-center text-sm text-gray-600"><?php echo $user['email']; ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase <?php echo $roleColor; ?>">
                                                <?php echo $user['role']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase <?php echo $statusColor; ?>">
                                                <?php echo $user['status']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-gray-400">
                                            <button class="hover:text-[#043915] transition-colors"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-32 text-gray-300 text-sm italic">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-center mt-6 px-2 gap-4">
                <p class="text-[11px] text-gray-400 uppercase font-bold tracking-widest">Showing Results</p>
                <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0">
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition-all shadow-sm">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#f8c922] text-[#043915] font-bold shadow-md">1</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all">2</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all">3</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all">4</button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all">5</button>

                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition-all shadow-sm">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </section>
    </div>
</main>

<div id="createUserModal" class="fixed inset-0 z-[999] hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>
    
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="bg-[#043915] p-6 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold">New System User</h3>
                    <p class="text-[10px] text-green-200 uppercase tracking-widest">Add student or faculty access</p>
                </div>
                <button onclick="closeModal('createUserModal')" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-red-500 transition-all group">
                    <i class="fa-solid fa-xmark text-sm group-hover:scale-125 transition-transform"></i>
                </button>
            </div>

            <form action="" method="POST" class="p-8 space-y-5">
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
                        <input type="text" name="lrn" required placeholder="123456789012"
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Access Role</label>
                        <select name="role" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50">
                            <option value="Student">Student</option>
                            <option value="Teacher">Teacher</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">Status</label>
                        <select name="status" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-widest">System Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                        <input type="password" id="userPassword" name="password" required placeholder="••••••••"
                               class="w-full pl-11 pr-12 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#043915] text-sm bg-gray-50/50">
                        <button type="button" onclick="togglePasswordVisibility('userPassword', 'eyeIcon')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#043915]">
                            <i id="eyeIcon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('createUserModal')" class="flex-1 py-3 border border-gray-200 text-gray-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-[#f8c922] text-[#043915] rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-yellow-400 transition-all shadow-md shadow-yellow-200">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open Modal
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

// Close Modal (Specifically for X and Cancel buttons)
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

// Password Toggle
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>