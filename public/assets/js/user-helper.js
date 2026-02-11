// Pinagsamang Load Function - NO BLINK
function loadUsers(page = 1) {
    const tableBody = document.querySelector('#userTableSection tbody');
    const paginationWrapper = document.getElementById('paginationWrapper');
    
    // Kunin ang mga values ng filters
    // Ginagamitan ng ?. para hindi mag-error kung wala ang element
    const role = document.getElementById('userRoleFilter')?.value || '';
    const sort = document.getElementById('userSortFilter')?.value || 'latest';
    const search = document.getElementById('userSearchBar')?.value || '';

    const params = new URLSearchParams({ 
        p: page, 
        role: role, 
        sort: sort,
        search: search 
    });

    // ALIS: Inalis natin ang opacity = '0.5' dito para hindi mag-blink

    fetch(window.location.pathname + '?' + params.toString())
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTableBody = doc.querySelector('#userTableSection tbody');
            const newPagination = doc.getElementById('paginationWrapper');

            // Instant Swap ng data (hindi mahahalata ng user ang blink)
            if (newTableBody && tableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }
            if (newPagination && paginationWrapper) {
                paginationWrapper.innerHTML = newPagination.innerHTML;
            }

            // I-update ang URL bar nang hindi nagre-refresh ang page
            window.history.replaceState({}, document.title, window.location.pathname);
        })
        .catch(err => console.error('Error loading users:', err));
}

// Reset Filter - Tatawag na sa loadUsers
function resetFilters() {
    const role = document.getElementById('userRoleFilter');
    const sort = document.getElementById('userSortFilter');
    const search = document.getElementById('userSearchBar');

    if (role) role.value = '';
    if (sort) sort.value = 'latest';
    if (search) search.value = '';

    loadUsers(1); 
}

// Para sa Pagination Buttons: 
// Siguraduhin na sa PHP mo, "loadUsers" na ang tinatawag imbes na "loadUserPage"
function loadUserPage(pageNum) {
    loadUsers(pageNum);
}

// Modal and Input States (Nanatiling pareho pero nilinis ang logic)
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('hidden');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        const form = modal.querySelector('form');
        if (form) form.reset();
        resetInputState();
        modal.classList.add('hidden');
    }
}

function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input && icon) {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }
}

function resetInputState() {
    const roleSelect = document.getElementById('modalRole');
    const lrnInput = document.getElementById('lrnInput');
    const emailInput = document.getElementById('emailInput');

    if (!roleSelect || !lrnInput || !emailInput) return;

    const isTeacher = roleSelect.value === 'Teacher';
    
    // Teacher logic
    emailInput.disabled = !isTeacher;
    if (isTeacher) emailInput.setAttribute('required', 'required');
    else emailInput.removeAttribute('required');
    emailInput.classList.toggle('bg-gray-200', !isTeacher);
    emailInput.classList.toggle('cursor-not-allowed', !isTeacher);

    // Student logic
    lrnInput.disabled = isTeacher;
    if (!isTeacher) lrnInput.setAttribute('required', 'required');
    else lrnInput.removeAttribute('required');
    lrnInput.classList.toggle('bg-gray-200', isTeacher);
    lrnInput.classList.toggle('cursor-not-allowed', isTeacher);
    if (isTeacher) lrnInput.value = '';
}

// Auto-hide alerts
setTimeout(() => {
    document.getElementById('successAlert')?.remove();
    document.getElementById('errorAlert')?.remove();
}, 5000);