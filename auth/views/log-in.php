<?php
ob_start();
?>

<main class="min-h-screen relative flex items-center justify-center bg-[#f8fafc] px-4">

    <a href="home-page" class="absolute top-6 left-10 text-[#043915] text-2xl hover:scale-110 transition-all duration-300">
        <i class="fa-solid fa-circle-arrow-left"></i>
    </a>

    <section class="w-full max-w-md bg-[#ffffff] rounded-3xl shadow-2xl p-8 border border-gray-100">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-[#043915] text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-[#043915]">Welcome Back!</h2>
            <p class="text-sm text-gray-500 mt-2">Enter your credentials to access your account.</p>
        </div>

        <form class="space-y-6" method="post" action="index.php?page=authenticate">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                <div class="relative group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#043915] transition-colors">
                        <i class="fa-solid fa-user-circle text-lg"></i>
                    </div>
                    <input type="text" name="username" placeholder="Enter your username" required
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#043915] focus:border-transparent transition-all duration-200">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                <div class="relative group">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#043915] transition-colors">
                        <i class="fa-solid fa-lock text-lg"></i>
                    </div>
                    <input id="password" type="password" name="password" placeholder="••••••••" required
                        class="w-full pl-12 pr-12 py-3 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#043915] focus:border-transparent transition-all duration-200">
                    
                    <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#043915] transition-colors" id="togglePassword">
                        <i class="fa-solid fa-eye cursor-pointer" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="#" class="text-xs font-bold text-[#043915] hover:underline underline-offset-4 uppercase tracking-tighter">Forgot your password?</a>
            </div>

            <button type="submit"
                class="w-full py-4 rounded-xl bg-[#f8c922] text-[#043915] font-black text-xs uppercase tracking-widest hover:bg-yellow-400 hover:shadow-lg hover:shadow-yellow-200 active:scale-[0.98] transition-all duration-300 cursor-pointer">
                Sign In to System
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-50 text-center">
            <p class="text-xs text-gray-500">
                Don’t have an account? <br>
                <span class="font-bold text-[#043915] uppercase tracking-tighter">Contact your System Administrator</span>
            </p>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-100 flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                <p class="text-red-700 text-[11px] font-bold uppercase tracking-tight">
                    <?= $_SESSION['error']; ?>
                </p>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

    </section>
</main>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/eye-icon.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/structure.php';
?>