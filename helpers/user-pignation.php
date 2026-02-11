<?php if ($totalPages > 1): 
    $maxVisible = 5;
    $half = floor($maxVisible / 2);
    $start = max(1, $page - $half);
    $end = min($totalPages, $page + $half);

    if ($end - $start + 1 < $maxVisible) {
        if ($start == 1) {
            $end = min($totalPages, $start + $maxVisible - 1);
        } else {
            $start = max(1, $end - $maxVisible + 1);
        }
    }
?>
<div class="flex items-center justify-center gap-2 flex-nowrap py-1">
    
    <button type="button" 
        <?= ($page > 1) ? 'onclick="loadUserPage('.($page - 1).')"' : 'disabled' ?>
        class="w-10 h-10 min-w-[40px] flex items-center justify-center rounded-xl transition-all duration-300 shadow-sm
        <?= ($page > 1) 
            ? 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 cursor-pointer hover:scale-105 active:scale-95' 
            : 'bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed' ?>">
        <i class="fa-solid fa-chevron-left text-[10px]"></i>
    </button>

    <?php for ($i=$start; $i<=$end; $i++): ?>
        <button type="button" onclick="loadUserPage(<?= $i ?>)"
            class="w-10 h-10 min-w-[40px] flex items-center justify-center rounded-xl cursor-pointer transition-all duration-300
                <?= ($i==$page) 
                    ? 'bg-[#f8c922] text-[#043915] font-bold shadow-md scale-105' 
                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:scale-105' ?>">
            <span class="text-sm"><?= $i ?></span>
        </button>
    <?php endfor; ?>

    <button type="button" 
        <?= ($page < $totalPages) ? 'onclick="loadUserPage('.($page + 1).')"' : 'disabled' ?>
        class="w-10 h-10 min-w-[40px] flex items-center justify-center rounded-xl transition-all duration-300 shadow-sm
        <?= ($page < $totalPages) 
            ? 'bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 cursor-pointer hover:scale-105 active:scale-95' 
            : 'bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed' ?>">
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
    </button>
    
</div>
<?php endif; ?>