/**
 * user-helper.js - COMPLETE FILE
 */

// ============================================
// 1. LOAD USERS - NO BLINK
// ============================================

function loadUsers(page = 1) {
  const tableBody = document.querySelector("#userTableSection tbody");
  const paginationWrapper = document.getElementById("paginationWrapper");

  const role = document.getElementById("userRoleFilter")?.value || "";
  const sort = document.getElementById("userSortFilter")?.value || "latest";
  const search = document.getElementById("userSearchBar")?.value || "";

  const params = new URLSearchParams({
    p: page,
    role: role,
    sort: sort,
    search: search,
  });

  fetch(window.location.pathname + "?" + params.toString())
    .then((res) => res.text())
    .then((html) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");

      const newTableBody = doc.querySelector("#userTableSection tbody");
      const newPagination = doc.getElementById("paginationWrapper");

      if (newTableBody && tableBody) {
        tableBody.innerHTML = newTableBody.innerHTML;
      }
      if (newPagination && paginationWrapper) {
        paginationWrapper.innerHTML = newPagination.innerHTML;
      }

      window.history.replaceState({}, document.title, window.location.pathname);
    })
    .catch((err) => console.error("Error loading users:", err));
}

// ============================================
// 2. PAGINATION
// ============================================

function loadUserPage(pageNum) {
  loadUsers(pageNum);
}

// ============================================
// 3. RESET FILTERS
// ============================================

function resetFilters() {
  const role = document.getElementById("userRoleFilter");
  const sort = document.getElementById("userSortFilter");
  const search = document.getElementById("userSearchBar");

  if (role) role.value = "";
  if (sort) sort.value = "latest";
  if (search) search.value = "";

  loadUsers(1);
}

// ============================================
// 4. MODAL MANAGEMENT
// ============================================

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.remove("hidden");
    handleRoleSwitch();
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    const form = modal.querySelector("form");
    if (form) form.reset();
    resetInputState();
    modal.classList.add("hidden");
  }
}

// ============================================
// 5. PASSWORD VISIBILITY
// ============================================

function togglePasswordVisibility(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (input && icon) {
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");
  }
}

// ============================================
// 6. INPUT STATE MANAGEMENT (LRN vs Email)
// ============================================

function resetInputState() {
  const roleSelect = document.getElementById("modalRole");
  const lrnInput = document.getElementById("lrnInput");
  const emailInput = document.getElementById("emailInput");

  if (!roleSelect || !lrnInput || !emailInput) return;

  const isTeacher = roleSelect.value === "Teacher" || roleSelect.value === "Admin";

  emailInput.disabled = !isTeacher;
  if (isTeacher) emailInput.setAttribute("required", "required");
  else emailInput.removeAttribute("required");
  emailInput.classList.toggle("bg-gray-200", !isTeacher);
  emailInput.classList.toggle("cursor-not-allowed", !isTeacher);

  lrnInput.disabled = isTeacher;
  if (!isTeacher) lrnInput.setAttribute("required", "required");
  else lrnInput.removeAttribute("required");
  lrnInput.classList.toggle("bg-gray-200", isTeacher);
  lrnInput.classList.toggle("cursor-not-allowed", isTeacher);
  if (isTeacher) lrnInput.value = "";
}

// ============================================
// 7. AUTO-HIDE ALERTS
// ============================================

setTimeout(() => {
  document.getElementById("successAlert")?.remove();
  document.getElementById("errorAlert")?.remove();
}, 5000);

// ============================================
// 8. ROLE SWITCH LOGIC
// ============================================

function handleRoleSwitch() {
  const roleSelect = document.getElementById("modalRole");
  const lrnInput = document.getElementById("lrnInput");
  const emailInput = document.getElementById("emailInput");

  if (!roleSelect || !lrnInput || !emailInput) return;

  if (roleSelect.value === "Teacher" || roleSelect.value === "Admin") {
    emailInput.disabled = false;
    emailInput.required = true;
    emailInput.classList.remove("bg-gray-200", "cursor-not-allowed");
    emailInput.classList.add("bg-gray-50");

    lrnInput.disabled = true;
    lrnInput.required = false;
    lrnInput.value = "";
    lrnInput.classList.add("bg-gray-200", "cursor-not-allowed");
    lrnInput.classList.remove("bg-gray-50");
  } else {
    lrnInput.disabled = false;
    lrnInput.required = true;
    lrnInput.classList.remove("bg-gray-200", "cursor-not-allowed");
    lrnInput.classList.add("bg-gray-50");

    emailInput.disabled = true;
    emailInput.required = false;
    emailInput.value = "";
    emailInput.classList.add("bg-gray-200", "cursor-not-allowed");
    emailInput.classList.remove("bg-gray-50");
  }
}

// ============================================
// 9. PROFILE MODAL - OPEN
// ============================================

function openProfileModal(user) {
  // Only students can edit
  if (user.role !== 'Student') {
    showProfileReadOnly(user);
    return;
  }

  // Parse name: "Clark Junsen A. Tolentino"
  const nameParts = user.name.trim().split(/\s+/);
  let firstName = "";
  let lastName = "";
  let mi = "";

  const initialIndex = nameParts.findIndex(p => p.length <= 2 || p.includes('.'));

  if (initialIndex !== -1) {
    firstName = nameParts.slice(0, initialIndex).join(' ');
    mi = nameParts[initialIndex].replace('.', '');
    lastName = nameParts.slice(initialIndex + 1).join(' ');
  } else {
    firstName = nameParts[0];
    lastName = nameParts.slice(1).join(' ');
  }

  // Store user_id
  document.getElementById('profileModal').dataset.userId = user.user_id;

  // Fill inputs
  document.getElementById('profileName').textContent = user.name;
  document.getElementById('inputFirstName').value = firstName;
  document.getElementById('inputLastName').value = lastName;
  document.getElementById('inputMI').value = mi;
  document.getElementById('inputLrn').value = user.lrn || '';
  document.getElementById('inputContact').value = user.contact_no || '';
  document.getElementById('inputAddress').value = user.address || '';
  document.getElementById('inputSection').value = user.section || '';
  document.getElementById('inputAdvisory').value = user.advisory || '';
  if(user.year_level) document.getElementById('inputYearLevel').value = user.year_level;

  // Case History
  const historyBox = document.getElementById('historyContainer');
  historyBox.innerHTML = ''; 
  for (let g = 7; g <= 12; g++) {
    historyBox.innerHTML += `
        <div class="p-3 bg-green-50 rounded-xl border border-green-100/50 flex items-center gap-3">
            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-green-600 shadow-sm border border-green-100 text-[10px] font-bold">G${g}</div>
            <p class="text-[11px] text-green-800 font-medium">Grade ${g}: <span class="font-bold text-gray-600">No Record</span></p>
        </div>`;
  }

  // Role badge
  const badge = document.getElementById('profileRoleBadge');
  badge.textContent = user.role;
  badge.className = "px-5 py-1.5 text-[10px] font-black bg-emerald-500 text-white rounded-full uppercase tracking-widest shadow-lg";

  // Enable editing
  enableStudentEditing();

  openModal('profileModal');
}

// ============================================
// 10. PROFILE READ-ONLY (Teacher/Admin)
// ============================================

function showProfileReadOnly(user) {
  document.getElementById('profileModal').dataset.userId = user.user_id;
  document.getElementById('profileName').textContent = user.name;
  
  const nameParts = user.name.trim().split(/\s+/);
  const initialIndex = nameParts.findIndex(p => p.length <= 2 || p.includes('.'));
  
  if (initialIndex !== -1) {
    document.getElementById('inputFirstName').value = nameParts.slice(0, initialIndex).join(' ');
    document.getElementById('inputMI').value = nameParts[initialIndex].replace('.', '');
    document.getElementById('inputLastName').value = nameParts.slice(initialIndex + 1).join(' ');
  } else {
    document.getElementById('inputFirstName').value = nameParts[0];
    document.getElementById('inputLastName').value = nameParts.slice(1).join(' ');
  }

  document.getElementById('inputLrn').value = user.lrn || '';
  document.getElementById('inputContact').value = user.contact_no || '';
  document.getElementById('inputAddress').value = user.address || '';
  document.getElementById('inputSection').value = user.section || '';
  document.getElementById('inputAdvisory').value = user.advisory || '';
  if(user.year_level) document.getElementById('inputYearLevel').value = user.year_level;

  // Case History
  const historyBox = document.getElementById('historyContainer');
  historyBox.innerHTML = ''; 
  for (let g = 7; g <= 12; g++) {
    historyBox.innerHTML += `
        <div class="p-3 bg-green-50 rounded-xl border border-green-100/50 flex items-center gap-3">
            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-green-600 shadow-sm border border-green-100 text-[10px] font-bold">G${g}</div>
            <p class="text-[11px] text-green-800 font-medium">Grade ${g}: <span class="font-bold text-gray-600">No Record</span></p>
        </div>`;
  }

  // Role badge
  const badge = document.getElementById('profileRoleBadge');
  badge.textContent = user.role;
  badge.className = user.role === 'Teacher'
    ? "px-5 py-1.5 text-[10px] font-black bg-green-500 text-white rounded-full uppercase tracking-widest shadow-lg"
    : "px-5 py-1.5 text-[10px] font-black bg-red-600 text-white rounded-full uppercase tracking-widest shadow-lg";

  disableAllFields();
  openModal('profileModal');
}

// ============================================
// 11. DISABLE ALL FIELDS
// ============================================

function disableAllFields() {
  const inputs = document.querySelectorAll('#profileModal input, #profileModal select');
  inputs.forEach(input => {
    input.disabled = true;
    input.classList.add('bg-gray-200', 'cursor-not-allowed');
  });
  
  // Hide buttons
  const updateBtn = document.querySelector('[onclick="updateProfileData()"]');
  const deleteBtn = document.querySelector('[onclick="deleteStudent()"]');
  if (updateBtn) updateBtn.style.display = 'none';
  if (deleteBtn) deleteBtn.style.display = 'none';
}

// ============================================
// 12. ENABLE STUDENT EDITING
// ============================================

function enableStudentEditing() {
  const inputs = document.querySelectorAll('#profileModal input, #profileModal select');
  inputs.forEach(input => {
    if (input.id !== 'inputLrn') {
      input.disabled = false;
      input.classList.remove('bg-gray-200', 'cursor-not-allowed');
      input.classList.add('bg-gray-50');
    } else {
      input.disabled = true;
    }
  });
  
  // Show buttons
  const updateBtn = document.querySelector('[onclick="updateProfileData()"]');
  const deleteBtn = document.querySelector('[onclick="deleteStudent()"]');
  if (updateBtn) updateBtn.style.display = 'block';
  if (deleteBtn) deleteBtn.style.display = 'block';
}

// ============================================
// 13. UPDATE PROFILE
// ============================================

function updateProfileData() {
  const firstName = document.getElementById('inputFirstName').value.trim();
  const lastName = document.getElementById('inputLastName').value.trim();
  const mi = document.getElementById('inputMI').value.trim();

  // Validate
  if (!firstName || !lastName) {
    alert('First name and last name required!');
    return;
  }

  const nameRegex = /^[a-zA-Z\s\-']+$/;
  if (!nameRegex.test(firstName) || !nameRegex.test(lastName)) {
    alert('Name can only have letters, spaces, hyphens, apostrophes');
    return;
  }

  if (mi && !(/^[a-zA-Z]\.?$/).test(mi)) {
    alert('M.I. must be single letter');
    return;
  }

  const userId = document.getElementById('profileModal').dataset.userId;
  
  // Format name: FirstName M. LastName
  let fullName = firstName;
  if (mi) {
    fullName += ' ' + (mi.includes('.') ? mi : mi + '.');
  }
  fullName += ' ' + lastName;

  // Send data
  const formData = new FormData();
  formData.append('action', 'update_profile');
  formData.append('user_id', userId);
  formData.append('name', fullName);
  formData.append('contact_no', document.getElementById('inputContact').value.trim());
  formData.append('address', document.getElementById('inputAddress').value.trim());
  formData.append('year_level', document.getElementById('inputYearLevel').value);
  formData.append('section', document.getElementById('inputSection').value.trim());
  formData.append('advisory', document.getElementById('inputAdvisory').value.trim());

  fetch(window.location.pathname, {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    alert('✓ Profile updated!');
    closeModal('profileModal');
    location.reload();
  })
  .catch(error => {
    alert('✗ Error: ' + error);
  });
}

// ============================================
// 14. DELETE STUDENT
// ============================================

function deleteStudent() {
  const userId = document.getElementById('profileModal').dataset.userId;
  
  if (!confirm('Delete this student? This cannot be undone!')) {
    return;
  }

  const formData = new FormData();
  formData.append('action', 'delete_student');
  formData.append('user_id', userId);

  fetch(window.location.pathname, {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    alert('✓ Student deleted!');
    closeModal('profileModal');
    location.reload();
  })
  .catch(error => {
    alert('✗ Error: ' + error);
  });
}

// ============================================
// 15. DOCUMENT READY
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  const roleSelect = document.getElementById("modalRole");
  if (roleSelect) {
    roleSelect.addEventListener("change", handleRoleSwitch);
  }

  handleRoleSwitch();
});