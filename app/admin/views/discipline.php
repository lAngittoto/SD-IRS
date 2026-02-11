<?php
ob_start();
?>

<main class="transition-all duration-300 xl:ml-64 min-h-screen bg-gray-100 p-4 md:p-8 w-full xl:w-[calc(100%-16rem)] overflow-x-hidden">

    <?php include __DIR__ . '/../../../includes/admin-sidebar.php'; ?>
    <?php include __DIR__ . '/../../../includes/admin-header.php'; ?>

    <section class="mb-8 text-center md:text-left">
        <h1 class="text-2xl font-bold text-[#043915] flex items-center justify-center md:justify-start gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-scale-balanced text-green-600 text-lg"></i>
            </div>
            Add Disciplinary Action
        </h1>
        <p class="text-sm text-gray-600 mt-1 md:ml-13">
            Define the disciplinary parameters for school violations.
        </p>
    </section>

    <section class="w-full bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-10 relative">

        <h2 class="text-lg font-semibold text-[#043915] mb-8 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-file-circle-plus text-blue-600 text-lg"></i>
            </div>
            Disciplinary Action Configuration
        </h2>

        <form class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">

            <div class="space-y-6">
                <div class="relative">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">
                        Violation Name
                    </label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 bottom-0 w-12 bg-yellow-100 rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                            <i class="fa-solid fa-triangle-exclamation text-yellow-600 text-lg"></i>
                        </div>
                        <input type="text"
                            placeholder="e.g. Unauthorized Absence"
                            class="w-full pl-16 pr-4 py-3.5 rounded-xl border border-gray-300 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-[#043915] bg-gray-50">
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">
                        Default Sanction
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full">
                            <div class="absolute left-0 top-0 bottom-0 w-12 bg-green-100 rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                                <i class="fa-solid fa-gavel text-green-600 text-lg"></i>
                            </div>
                            <select
                                class="w-full pl-16 pr-4 py-3.5 rounded-xl border border-gray-300 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-[#043915] bg-gray-50">
                                <option value="">Select default sanction</option>
                            </select>
                        </div>
                        <button type="button" class="shrink-0 w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-100 transition border border-blue-200" onclick="openPopup('popup-sanction')">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">
                        Severity Level
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full">
                            <div class="absolute left-0 top-0 bottom-0 w-12 bg-purple-100 rounded-l-xl flex items-center justify-center border-y border-l border-gray-300">
                                <i class="fa-solid fa-layer-group text-purple-600 text-lg"></i>
                            </div>
                            <select
                                class="w-full pl-16 pr-4 py-3.5 rounded-xl border border-gray-300 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-[#043915] bg-gray-50">
                                <option value="">Select Severity Level</option>
                            </select>
                        </div>
                        <button type="button" class="shrink-0 w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center hover:bg-purple-100 transition border border-purple-200" onclick="openPopup('popup-severity')">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">
                        Description / Notes
                    </label>
                    <div class="relative">
                        <div class="absolute left-0 top-0 w-12 h-12 bg-blue-100 rounded-tl-xl flex items-center justify-center border-t border-l border-gray-300">
                            <i class="fa-solid fa-align-left text-blue-600 text-lg"></i>
                        </div>
                        <textarea rows="10"
                            placeholder="Provide any notes or detailed explanation of the disciplinary action..."
                            class="w-full pl-16 pr-4 py-4 rounded-xl border border-gray-300 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-[#043915] bg-gray-50 resize-none min-h-[150px]"></textarea>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-100">
                <button type="reset"
                    class="order-2 sm:order-1 px-8 py-3.5 rounded-xl text-sm font-semibold text-gray-600
                           bg-gray-100 hover:bg-gray-200 transition w-full sm:w-auto text-center cursor-pointer">
                    Cancel
                </button>

                <button type="submit"
                    class="order-1 sm:order-2 px-8 py-3.5 rounded-xl text-sm font-bold text-[#043915]
                           bg-[#f8c922] hover:bg-yellow-300 transition shadow-md flex items-center justify-center gap-2 w-full sm:w-auto cursor-pointer">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Configuration
                </button>
            </div>

        </form>
    </section>

    <div id="popup-sanction" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 relative">
            <button class="absolute top-4 right-4 text-gray-400 hover:text-red-600" onclick="closePopup('popup-sanction')">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <h3 class="text-lg font-bold text-[#043915] mb-4">Edit Default Sanction</h3>
            <select class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#043915] bg-gray-50">
                <option value="">Select default sanction</option>
            </select>
            <div class="flex justify-end mt-6 gap-3">
                <button class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 transition text-sm font-semibold" onclick="closePopup('popup-sanction')">Cancel</button>
                <button class="px-5 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition text-sm font-semibold">Save</button>
            </div>
        </div>
    </div>

    <div id="popup-severity" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 relative">
            <button class="absolute top-4 right-4 text-gray-400 hover:text-red-600" onclick="closePopup('popup-severity')">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <h3 class="text-lg font-bold text-[#043915] mb-4">Edit Severity Level</h3>
            <select class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#043915] bg-gray-50">
                <option value="">Select Severity Level</option>
            </select>
            <div class="flex justify-end mt-6 gap-3">
                <button class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 transition text-sm font-semibold" onclick="closePopup('popup-severity')">Cancel</button>
                <button class="px-5 py-2.5 rounded-xl bg-purple-600 text-white hover:bg-purple-700 transition text-sm font-semibold">Save</button>
            </div>
        </div>
    </div>

</main>

<script src="/student-discipline-and-incident-reporting-system/public/assets/js/discipline-pop-up.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';
?>