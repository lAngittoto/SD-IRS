<?php
ob_start();
// Current year para sa filter logic
$currentYear = date('Y'); 
?>

<main class="ml-64 min-h-screen bg-gray-50 p-8 w-[calc(100%-16rem)] transition-all duration-300 ease-in-out">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>

    <section class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-bold text-[#043915] flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-chart-pie text-[#043915] text-lg"></i>
                </div>
                Reports & Analytics Summary
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Comprehensive overview of institutional data, incidents, and advisory assignments.
            </p>
        </div>

        <div class="relative w-full md:w-96">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" placeholder="Search specific report or record..." 
                   class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-[#043915] shadow-sm transition-all duration-200">
        </div>
    </section>

    <section class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-8 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-xl border border-gray-200">
            <i class="fa-solid fa-calendar-week text-[#043915]"></i>
            <select class="bg-transparent text-sm font-bold text-gray-700 focus:outline-none cursor-pointer">
                <option>Select Period</option>
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
            </select>
        </div>

        <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Month</span>
            <select class="bg-transparent text-sm font-bold text-gray-700 focus:outline-none cursor-pointer">
                <?php for($m=1; $m<=12; $m++) echo "<option>".date('F', mktime(0,0,0,$m,1))."</option>"; ?>
            </select>
        </div>

        <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-xl border border-gray-200">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Year</span>
            <select class="bg-transparent text-sm font-bold text-gray-700 focus:outline-none cursor-pointer">
                <?php for($y=2026; $y<=2030; $y++) echo "<option value='$y'>$y</option>"; ?>
            </select>
        </div>

        <button class="ml-auto text-xs font-black text-gray-400 hover:text-red-600 transition-colors uppercase tracking-widest underline decoration-2 underline-offset-4">
            Reset Filters
        </button>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php 
        $cards = [
            ['icon'=>'fa-user-graduate','title'=>'Total Enrolled','value'=>$totalStudents ?? 0,'color'=>'green','tag'=>'ACTIVE YEAR'],
            ['icon'=>'fa-chalkboard-user','title'=>'Faculty Members','value'=>$totalFaculty ?? 0,'color'=>'blue','tag'=>'Teaching Staff'],
            ['icon'=>'fa-triangle-exclamation','title'=>'Total Incidents','value'=>$totalIncidents ?? 0,'color'=>'red','tag'=>'NEEDS ATTENTION'],
            ['icon'=>'fa-clock','title'=>'Pending Case','value'=>$pendingReports ?? 0,'color'=>'orange','tag'=>'For Review']
        ];
        foreach($cards as $card): ?>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden hover:translate-y-[-4px] transition-all duration-300">
            <div class="absolute right-[-10px] top-[-10px] opacity-5 text-[#043915]"><i class="fa-solid <?=$card['icon']?> text-7xl"></i></div>
            <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1"><?=$card['title']?></h2>
            <p class="text-3xl font-bold text-gray-800"><?=number_format($card['value']);?></p>
            <div class="mt-4 flex items-center text-[9px] font-black text-<?=$card['color']?>-600 bg-<?=$card['color']?>-50 w-fit px-2 py-1 rounded-md"><?=$card['tag']?></div>
        </div>
        <?php endforeach; ?>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-chart-line text-green-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-[#043915]">Disciplinary Trends</h3>
                </div>
                <div class="h-72 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center">
                    <i class="fa-solid fa-chart-area text-4xl text-gray-200 mb-2"></i>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Visualizing data trends...</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-[#043915]">Recent Resolutions</h3>
                    <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-1 rounded-md uppercase">Total: 0</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Violation</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Severity</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-center">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3" class="px-6 py-16 text-center text-xs text-gray-400 italic uppercase tracking-widest font-medium">
                                    No recent resolutions recorded.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-people-group text-purple-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-[#043915]">Advisory Summary</h3>
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100 hover:bg-gray-100 transition">
                        <span class="text-xs text-gray-600 font-medium">Assigned Students</span>
                        <span class="text-sm font-bold text-gray-800"><?php echo $assignedStudentsCount ?? '0'; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100 hover:bg-gray-100 transition">
                        <span class="text-xs text-gray-600 font-medium">Unassigned Students</span>
                        <span class="text-sm font-bold text-red-500"><?php echo $unassignedStudentsCount ?? '0'; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100 hover:bg-gray-100 transition">
                        <span class="text-xs text-gray-600 font-medium">Active Advisories</span>
                        <span class="text-sm font-bold text-green-600"><?php echo $activeAdvisoriesCount ?? '0'; ?></span>
                    </div>
                </div>

                <a href="advisory-management.php" class="mt-6 block w-full text-center py-3 rounded-xl border-2 border-dashed border-gray-200 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:border-[#043915] hover:text-[#043915] transition-all">
                    Manage Class Assignments
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-green-100 overflow-hidden">
                <div class="bg-[#f8c922] p-5">
                    <h3 class="text-[#043915] text-sm font-bold flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> AI Generated Analysis
                    </h3>
                </div>
                <div class="p-5 space-y-5">
                    <div class="space-y-4">
                        <?php 
                        $insights = [
                            ['title' => 'System Resolution', 'desc' => 'Current data shows a stable resolution rate.'],
                            ['title' => 'Advisory Gap', 'desc' => 'Noticeable gap in student assignments detected.'],
                            ['title' => 'Incidents', 'desc' => 'High-priority cases flagged for review.']
                        ];
                        foreach($insights as $insight): ?>
                        <div class="border-l-4 border-yellow-500 pl-3">
                            <h5 class="text-[10px] font-black text-green-900 uppercase"><?=$insight['title']?></h5>
                            <p class="text-[11px] text-gray-600 leading-tight mt-1"><?=$insight['desc']?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button class="w-full py-3 bg-[#f8c922] text-[#043915] rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-yellow-300 transition shadow-md flex items-center justify-center gap-2">
                            <i class="fa-solid fa-rotate"></i> Re-generate
                        </button>
                        <button onclick="window.print()" class="w-full py-3 bg-gray-50 text-gray-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-100 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<style>
@media print {
    .ml-64, aside, header, button, select, input, .ml-auto { display: none !important; }
    main { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    .shadow-lg, .shadow-sm { box-shadow: none !important; border: 1px solid #eee !important; }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ .'/../../../includes/structure.php';
?>