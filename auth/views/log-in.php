<?php
ob_start();
?>

<main class="min-h-screen relative flex items-center justify-center bg-[#ffffff] px-4">

    <a href="home-page" class="absolute top-6 left-10 text-[#043915] text-2xl hover:scale-110 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <section class="w-full max-w-md bg-[#ffffff] rounded-2xl shadow-2xl p-6 md:p-8 text-[#043915]">

        <p class="text-center mb-6">Enter your credentials to access your account.</p>

        <form class="space-y-5" method="post" action="index.php?page=authenticate">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" placeholder="Enter your username" required
                    class="w-full px-4 py-2 rounded-xl border border-[#043915]/40 focus:outline-none focus:ring-2 focus:ring-[#043915] focus:border-[#043915]">
            </div>

            <div class="relative">
                <label class="block text-sm font-medium mb-1">Password</label>

                <input id="password" type="password" name="password" placeholder="Enter your password" required
                    class="w-full px-4 py-2 pr-10 rounded-xl border border-[#043915]/40 focus:outline-none focus:ring-2 focus:ring-[#043915] focus:border-[#043915]">

                <!-- Eye icon positioned inside input -->
                <span class="absolute inset-y-0 right-3 top-6 flex items-center cursor-pointer text-[#043915]" id="togglePassword">
                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                </span>
            </div>

            <div class="flex justify-end">
                <a href="#" class="text-sm hover:underline">Forgot your password?</a>
            </div>

            <button type="submit"
                class="w-full py-3 rounded-xl bg-[#f8c922] text-[#043915] font-semibold text-lg hover:bg-yellow-400 hover:scale-[1.02] transition-all duration-300 shadow-md cursor-pointer">
                Sign In
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            Don’t have an account? <span class="font-medium">Contact an administrator.</span>
        </p>

        <?php if (!empty($_SESSION['error'])): ?>
            <p class="text-center text-red-700 text-sm mt-4">
                <?= $_SESSION['error']; ?>
            </p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

    </section>
</main>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/eye-icon.js"></script>



<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/structure.php';
?>

