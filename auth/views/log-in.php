<?php
ob_start();
?>
<main class="min-h-screen relative flex items-center justify-center bg-[#ffffff] px-4">

    <a href="home-page" class="absolute top-6 left-10 text-[#0B3C5D] text-2xl hover:scale-110 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <section class="w-full max-w-md bg-[#ffffff] rounded-2xl shadow-2xl p-6 md:p-8 text-[#0B3C5D]">

        <p class="text-center mb-6">Enter your credentials to access your account.</p>

        <form class="space-y-5" method="post" action="index.php?page=authenticate">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" placeholder="Enter your username"
                    class="w-full px-4 py-2 rounded-xl border border-[#0B3C5D]/40 focus:outline-none focus:ring-2 focus:ring-[#0B3C5D] focus:border-[#0B3C5D]">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" placeholder="Enter your password"
                    class="w-full px-4 py-2 rounded-xl border border-[#0B3C5D]/40 focus:outline-none focus:ring-2 focus:ring-[#0B3C5D] focus:border-[#0B3C5D]">
            </div>

            <div class="flex justify-end">
                <a href="#" class="text-sm hover:underline">Forgot your password?</a>
            </div>

            <button type="submit"
                class="w-full py-3 rounded-xl bg-[#f8c922] text-[#0B3C5D] font-semibold text-lg hover:bg-yellow-400 hover:scale-[1.02] transition-all duration-300 shadow-md">
                Sign In
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            Don’t have an account? <span class="font-medium">Contact an administrator.</span>
        </p>

        <?php if (!empty($_SESSION['error'])): ?>
            <p style="color:#8b2d2d; font-size:14px; margin-bottom:15px;">
                <?= $_SESSION['error']; ?>
            </p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

    </section>
</main>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/structure.php';

?>