
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
    }

    // Close Modal + reset form
    function closeModal(id) {
        const modal = document.getElementById(id);
        const form = modal.querySelector('form');
        if (form) form.reset();
        resetInputState();
        modal.classList.add('hidden');
    }

    // Password Toggle
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Role-based control (Teacher = Email, Student = LRN)
    function resetInputState() {
        const roleSelect = document.getElementById('modalRole');
        const lrnInput = document.getElementById('lrnInput');
        const emailInput = document.getElementById('emailInput');

        if (roleSelect.value === 'Teacher') {
            // Enable Email
            emailInput.disabled = false;
            emailInput.setAttribute('required', 'required');
            emailInput.classList.remove('bg-gray-200', 'cursor-not-allowed');

            // Disable LRN
            lrnInput.value = '';
            lrnInput.disabled = true;
            lrnInput.removeAttribute('required');
            lrnInput.classList.add('bg-gray-200', 'cursor-not-allowed');

        } else if (roleSelect.value === 'Student') {
            // Enable LRN
            lrnInput.disabled = false;
            lrnInput.setAttribute('required', 'required');
            lrnInput.classList.remove('bg-gray-200', 'cursor-not-allowed');

            // Disable Email
            emailInput.value = '';
            emailInput.disabled = true;
            emailInput.removeAttribute('required');
            emailInput.classList.add('bg-gray-200', 'cursor-not-allowed');
        }
    }

    function resetFilters() {
        document.getElementById('userRole').value = '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const roleSelect = document.getElementById('modalRole');
        const lrnInput = document.getElementById('lrnInput');

        // initial setup
        resetInputState();

        // on role change
        roleSelect.addEventListener('change', resetInputState);

        // numeric only for LRN
        lrnInput.addEventListener('input', () => {
            lrnInput.value = lrnInput.value.replace(/\D/g, '');
            if (lrnInput.value.length > 12) {
                lrnInput.value = lrnInput.value.slice(0, 12);
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        if (successAlert) successAlert.remove();
        if (errorAlert) errorAlert.remove();
    }, 5000);
