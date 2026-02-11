<?php
if ($totalPages > 1):

    $maxVisible = 5;

    $start = max(1, $page - 2);
    $end = min($totalPages, $start + $maxVisible - 1);

    if ($end - $start < $maxVisible - 1) {
        $start = max(1, $end - $maxVisible + 1);
    }
?>

<div class="flex items-center gap-2">

    <!-- PREVIOUS -->
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>"
           class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm">
            <i class="fa-solid fa-chevron-left text-xs"></i>
        </a>
    <?php endif; ?>

    <!-- PAGE NUMBERS -->
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?page=<?php echo $i; ?>"
           class="w-10 h-10 flex items-center justify-center rounded-xl 
           <?php echo ($i == $page)
               ? 'bg-[#f8c922] text-[#043915] font-bold shadow-md'
               : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <!-- NEXT -->
    <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>"
           class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm">
            <i class="fa-solid fa-chevron-right text-xs"></i>
        </a>
    <?php endif; ?>

</div>

<?php endif; ?>
