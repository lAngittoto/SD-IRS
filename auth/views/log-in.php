<?php
ob_start();
?>
<main class="min-h-screen flex items-center justify-center bg-[#0B3C5D] px-4">
    <section class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 md:p-8">
        

        <p class="text-center text-gray-600 mb-6">
            Enter your credentials to access your account.
        </p>

        <!-- Form -->
        <form class="space-y-5">
            <!-- Username -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Username
                </label>
                <input
                    type="text"
                    name="username"
                    placeholder="Enter your username"
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0B3C5D] focus:border-transparent"
                >
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password
                </label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0B3C5D] focus:border-transparent"
                >
            </div>

            <!-- Forgot password -->
            <div class="flex justify-end">
                <a href="#" class="text-sm text-[#0B3C5D] hover:underline">
                    Forgot your password?
                </a>
            </div>

            <!-- Button -->
            <button
                type="submit"
                class="w-full py-3 rounded-xl bg-[#f8c922] text-[#0B3C5D] font-semibold text-lg
                       hover:bg-yellow-400 hover:scale-[1.02] transition-all duration-300 shadow-md"
            >
                Sign In
            </button>
        </form>

        <!-- Footer text -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Don’t have an account?
            <span class="font-medium text-[#0B3C5D]">
                Contact an administrator.
            </span>
        </p>
    </section>
</main>

<?php
$content = ob_get_clean();
include __DIR__ .'/../../includes/structure.php';
?>