<?php
ob_start();
?>

<main class="w-screen h-screen overflow-y-auto overflow-x-hidden bg-[#FFFFFF] scroll-smooth">
    
    <header class="flex items-center justify-between px-4 md:px-8 py-4 sticky top-0 bg-white/90 backdrop-blur-md z-50">
        <div class="flex items-center gap-3">
            <img src="/student-discipline-and-incident-reporting-system/public/assets/images/SD&IRS.png" alt="Logo" class="w-[50px] md:w-[70px]">
            <h1 class="text-[#043915] text-lg md:text-2xl font-bold tracking-tight">
                SD&IRS
            </h1>
        </div>
    </header>

    <section class="relative min-h-[90vh] flex flex-col items-center justify-center px-4 gap-6 bg-[url('/student-discipline-and-incident-reporting-system/public/assets/images/senior-high-school-students.jpg')] bg-cover bg-center bg-no-repeat">
        <div class="absolute inset-0 bg-black/20"></div>

        <div class="relative z-10 flex flex-col items-center">
            <p class="text-[#043915] text-base md:text-xl leading-relaxed text-center 
                bg-white/80 backdrop-blur-md p-8 md:p-12 rounded-3xl max-w-4xl shadow-2xl border border-white/50">
                <span class="block text-2xl md:text-4xl font-black mb-4">STUDENT DISCIPLINE AND INCIDENT REPORTING SYSTEM</span>
                A secure online platform designed to promote accountability, transparency,
                and student welfare. This system allows students and school personnel to
                report incidents responsibly, manage disciplinary cases, and coordinate
                with the Guidance Office for proper review and resolution.
                <br><br>
                <span class="text-sm font-bold uppercase tracking-widest opacity-70 italic">All reports are handled with confidentiality and fairness.</span>
            </p>

            <a href="log-in"
                class="mt-10 px-12 py-4 rounded-2xl bg-[#f8c922] text-[#043915] font-black text-lg shadow-xl
                hover:scale-105 hover:bg-yellow-400 hover:shadow-yellow-500/20 transition-all duration-300 uppercase tracking-wider">
                Sign In to Your Account
            </a>
        </div>
    </section>

    <section class="py-20 px-4 md:px-8 max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-black text-[#043915] mb-4 text-center">System Core Purpose</h2>
            <div class="w-24 h-2 bg-[#f8c922] mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="group bg-white p-8 rounded-[2rem] border border-gray-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#043915] transition-colors">
                    <i class="fa-solid fa-bullseye text-[#043915] text-2xl group-hover:text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-[#043915] mb-3">System Purpose</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Tracks and records all disciplinary actions to maintain a safe, orderly, and professional learning environment.</p>
            </div>

            <div class="group bg-white p-8 rounded-[2rem] border border-gray-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#043915] transition-colors">
                    <i class="fa-solid fa-file-signature text-blue-600 text-2xl group-hover:text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-[#043915] mb-3">Incident Recording</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Detailed documentation of infractions including student info, date, time, and specific actions taken.</p>
            </div>

            <div class="group bg-white p-8 rounded-[2rem] border border-gray-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#043915] transition-colors">
                    <i class="fa-solid fa-chart-line text-purple-600 text-2xl group-hover:text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-[#043915] mb-3">Real-time Monitoring</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Allows administrators to generate behavior reports for teachers, parents, and school officials instantly.</p>
            </div>

            <div class="group bg-white p-8 rounded-[2rem] border border-gray-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#043915] transition-colors">
                    <i class="fa-solid fa-gavel text-yellow-600 text-2xl group-hover:text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-[#043915] mb-3">Decision Support</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Assists in implementing preventive measures and appropriate actions for future system improvement.</p>
            </div>
        </div>
    </section>

    <footer class="bg-[#043915] py-12 px-4">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 text-white/80 text-sm">
            <div class="text-center md:text-left">
                <p class="font-bold text-white text-lg mb-2">SD&IRS</p>
                <p>© 2026 Student Discipline & Incident Reporting System.</p>
                <p>All rights reserved.</p>
            </div>

            <div class="flex flex-col items-center md:items-end gap-2 border-l-0 md:border-l border-white/20 md:pl-8">
                <p class="font-bold text-[#f8c922] uppercase tracking-widest text-[10px]">Development Team | BSIT 3 BLOCK 2</p>
                <p>Allan Aranda III • Fernando Junio • John Vence Bernal</p>
                <p>Clark Junsen • Miggy Salindog</p>
            </div>
        </div>
    </footer>

</main>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/structure.php';
?>