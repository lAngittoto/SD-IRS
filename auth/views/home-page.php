<?php
ob_start();
?>

<main class="w-screen h-screen flex flex-col overflow-hidden bg-[#FFFFFF]">
    <header class="flex items-center justify-between px-4 md:px-8 py-4 shrink-0">
        <div class="flex items-center gap-3">
            <img src="/student-discipline-and-incident-reporting-system/public/assets/images/SD&IRS.png" alt="Logo" class="w-[7vw] ">
            <h1 class="text-[#043915] text-xl md:text-4xl font-mono">
                Student Discipline & Incident Reporting System
            </h1>
        </div>
    </header>

    <div
        class="flex-1 bg-[url('/student-discipline-and-incident-reporting-system/public/assets/images/senior-high-school-students.jpg')]
         bg-cover bg-center bg-no-repeat flex flex-col items-center justify-center px-4 gap-6">

        <p class="text-[#043915] text-base md:text-2xl leading-relaxed text-center 
            bg-white/50 backdrop-blur-md p-6 md:p-10 rounded-2xl max-w-3xl shadow-lg">
            A secure online platform designed to promote accountability, transparency,
            and student welfare. This system allows students and school personnel to
            report incidents responsibly, manage disciplinary cases, and coordinate
            with the Guidance Office for proper review and resolution.
            <br><br>
            All reports are handled with confidentiality and fairness.
        </p>

        <a href="log-in"
            class="mt-6 px-10 py-3 rounded-2xl bg-[#f8c922] backdrop-blur-md border border-white/30 
            text-[#043915] font-semibold text-lg shadow-lg
            hover:scale-110 hover:shadow-xl transition-all duration-300">
            Sign In to Your Account
        </a>
    </div>

    <footer class="bg-white/30 backdrop-blur-md border-t border-white/30 py-6 px-4">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">

            <p class="text-[#043915] text-center md:text-left text-sm md:text-base">
                © 2026 Student Discipline & Incident Reporting System. All rights reserved.
            </p>

            <div class="text-[#043915] text-center md:text-left text-sm md:text-base">
                <p>BSIT 3 BLOCK 2:</p>
                <p>Allan Aranda III, Fernando Junio, John Vence Bernal</p>
                <p>Clark Junsen, Miggy Salindog</p>
            </div>

        </div>
    </footer>

</main>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/structure.php';
?>
