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