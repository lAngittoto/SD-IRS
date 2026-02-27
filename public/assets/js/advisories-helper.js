// Pagination Configuration
const ITEMS_PER_PAGE = 40;
let currentPage = 1;
let allData = [];
let currentView = "default";
let currentGradeFilter = "all";
let allAvailableStudents = [];
let currentAdvisoryGrade = null;

// ============================================================
// TOAST NOTIFICATION
// ============================================================
function showToast(message, type = "success") {
  const toastContainer = document.getElementById("toastContainer");
  const toast = document.createElement("div");
  const bgColor = type === "success" ? "bg-emerald-500" : "bg-red-500";

  toast.className = `${bgColor} text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-0 opacity-100`;
  toast.innerHTML = `
    <span class="font-medium">${message}</span>
    <button onclick="this.parentElement.remove()" class="ml-4 hover:bg-white/20 rounded-lg p-1 transition">
      ✕
    </button>
  `;
  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.transform = "translateX(100%)";
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 300);
  }, 5000);
}

// ============================================================
// STUDENT PROFILE MODAL
// ============================================================

function openStudentProfileModal(studentId) {
  console.log("OPENING STUDENT MODAL FOR ID:", studentId);
  
  const modal = document.getElementById("studentProfileModal");
  if (!modal) {
    console.error("Modal element not found!");
    showToast("Error: Modal not found. Please refresh.", "error");
    return;
  }

  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";

  const formContent = document.getElementById("profileFormContent");
  const loadingState = document.getElementById("profileLoadingState");
  
  if (formContent) formContent.classList.add("hidden");
  if (loadingState) loadingState.classList.remove("hidden");

  document.getElementById("profileStudentId").value = studentId;

  const formData = new FormData();
  formData.append("action", "get_student_profile");
  formData.append("student_id", studentId);

  console.log("FETCHING STUDENT PROFILE...");

  fetch(window.location.href, {
    method: "POST",
    body: formData,
  })
    .then((res) => {
      console.log("Response status:", res.status);
      return res.json();
    })
    .then((data) => {
      console.log("Student profile data:", data);
      if (loadingState) loadingState.classList.add("hidden");
      
      if (data && data.success) {
        populateStudentProfile(data.data);
        if (formContent) formContent.classList.remove("hidden");
      } else {
        showToast(data?.message || "Failed to load student profile.", "error");
        closeStudentProfileModal();
      }
    })
    .catch((error) => {
      console.error("Fetch error:", error);
      if (loadingState) loadingState.classList.add("hidden");
      showToast("Error loading profile: " + error.message, "error");
      closeStudentProfileModal();
    });
}

function populateStudentProfile(student) {
  console.log("POPULATING STUDENT PROFILE:", student);
  
  const nameParts = (student.name || "").trim().split(/\s+/);
  let firstName = "";
  let lastName = "";
  let mi = "";

  const miIndex = nameParts.findIndex(
    (p) => p.length <= 2 || p.includes(".")
  );

  if (miIndex !== -1 && miIndex > 0) {
    firstName = nameParts.slice(0, miIndex).join(" ");
    mi = nameParts[miIndex];
    lastName = nameParts.slice(miIndex + 1).join(" ");
  } else if (nameParts.length >= 2) {
    firstName = nameParts[0];
    lastName = nameParts.slice(1).join(" ");
  } else {
    firstName = student.name || "";
  }

  // Handle avatar image
  const avatarIcon = document.getElementById("profileAvatarIcon");
  const avatarImg = document.getElementById("profileAvatarImg");
  if (student.profile_pix && student.profile_pix !== '') {
    if (avatarImg) {
      avatarImg.src = student.profile_pix;
      avatarImg.classList.remove("hidden");
    }
    if (avatarIcon) avatarIcon.classList.add("hidden");
  } else {
    if (avatarImg) avatarImg.classList.add("hidden");
    if (avatarIcon) avatarIcon.classList.remove("hidden");
  }

  // Fill form fields
  const firstNameEl = document.getElementById("profileFirstName");
  const lastNameEl = document.getElementById("profileLastName");
  const miEl = document.getElementById("profileMI");
  const lrnEl = document.getElementById("profileLrn");
  const contactEl = document.getElementById("profileContact");
  const addressEl = document.getElementById("profileAddress");

  if (firstNameEl) firstNameEl.value = firstName;
  if (lastNameEl) lastNameEl.value = lastName;
  if (miEl) miEl.value = mi;
  if (lrnEl) lrnEl.value = student.lrn || "";
  if (contactEl) contactEl.value = student.contact_no || "";
  if (addressEl) addressEl.value = student.home_address || "";

  // Store profile pic path in hidden field
  let originalPicEl = document.getElementById("originalProfilePix");
  if (!originalPicEl) {
    originalPicEl = document.createElement("input");
    originalPicEl.type = "hidden";
    originalPicEl.id = "originalProfilePix";
    originalPicEl.value = student.profile_pix || "";
    document.getElementById("profileFormContent").appendChild(originalPicEl);
  } else {
    originalPicEl.value = student.profile_pix || "";
  }

  // Fill display fields
  const gradeText = student.current_grade ? `Grade ${student.current_grade}` : "Not Assigned";
  const advisoryText = student.advisory_name || "Not Assigned";
  const adviserText = student.teacher_name || "—";

  const yearLevelEl = document.getElementById("profileYearLevel");
  const sectionEl = document.getElementById("profileSection");
  const adviserEl = document.getElementById("profileAdviser");

  if (yearLevelEl) yearLevelEl.textContent = gradeText;
  if (sectionEl) sectionEl.textContent = advisoryText;
  if (adviserEl) adviserEl.textContent = adviserText;
}

function closeStudentProfileModal() {
  const modal = document.getElementById("studentProfileModal");
  if (modal) {
    modal.classList.add("hidden");
  }
  document.body.style.overflow = "auto";
}

function handleProfilePictureChange(event) {
  const file = event.target.files[0];
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  const maxSize = 5 * 1024 * 1024;

  if (!allowedTypes.includes(file.type)) {
    showToast("Only JPG, PNG, and WebP images are allowed.", "error");
    event.target.value = '';
    return;
  }

  if (file.size > maxSize) {
    showToast("File size exceeds 5MB limit.", "error");
    event.target.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    const avatarImg = document.getElementById("profileAvatarImg");
    const avatarIcon = document.getElementById("profileAvatarIcon");
    avatarImg.src = e.target.result;
    avatarImg.classList.remove("hidden");
    if (avatarIcon) avatarIcon.classList.add("hidden");
  };
  reader.readAsDataURL(file);
}

function saveStudentProfile() {
  const studentId   = document.getElementById("profileStudentId").value;
  const firstName   = document.getElementById("profileFirstName").value.trim();
  const lastName    = document.getElementById("profileLastName").value.trim();
  const mi          = document.getElementById("profileMI").value.trim();
  const lrn         = document.getElementById("profileLrn").value.trim();
  const contact     = document.getElementById("profileContact").value.trim();
  const address     = document.getElementById("profileAddress").value.trim();
  const fileInput   = document.getElementById("profilePictureInput");

  if (!firstName || !lastName) {
    showToast("First name and last name are required.", "error");
    return;
  }

  let profilePicPath = document.getElementById("originalProfilePix").value;

  if (fileInput.files.length > 0) {
    const formDataUpload = new FormData();
    formDataUpload.append("action", "upload_profile_pic");
    formDataUpload.append("student_id", studentId);
    formDataUpload.append("profile_pic", fileInput.files[0]);

    fetch(window.location.href, {
      method: "POST",
      body: formDataUpload,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          profilePicPath = data.path;
          saveProfileData(studentId, firstName, lastName, mi, lrn, contact, address, profilePicPath);
        } else {
          showToast(data.message || "Failed to upload image.", "error");
        }
      })
      .catch(() => showToast("Error uploading image.", "error"));
  } else {
    saveProfileData(studentId, firstName, lastName, mi, lrn, contact, address, profilePicPath);
  }
}

function saveProfileData(studentId, firstName, lastName, mi, lrn, contact, address, profilePicPath) {
  const formData = new FormData();
  formData.append("action",       "update_student_info");
  formData.append("student_id",   studentId);
  formData.append("first_name",   firstName);
  formData.append("last_name",    lastName);
  formData.append("mi",           mi);
  formData.append("lrn",          lrn);
  formData.append("contact_no",   contact);
  formData.append("home_address", address);
  formData.append("profile_pix",  profilePicPath);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message, "success");
        closeStudentProfileModal();
        if (allData.length > 0) loadFilteredData();
      } else {
        showToast(data.message || "Update failed.", "error");
      }
    })
    .catch(() => showToast("Error saving profile.", "error"));
}

// ============================================================
// TEACHER PROFILE MODAL
// ============================================================

function openTeacherProfileModal(advisoryId) {
  console.log("OPENING TEACHER MODAL FOR ADVISORY ID:", advisoryId);
  
  const modal = document.getElementById("teacherProfileModal");
  if (!modal) {
    console.error("Teacher modal element not found!");
    showToast("Error: Modal not found. Please refresh.", "error");
    return;
  }

  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";

  const formContent = document.getElementById("teacherFormContent");
  const loadingState = document.getElementById("teacherLoadingState");
  
  if (formContent) formContent.classList.add("hidden");
  if (loadingState) loadingState.classList.remove("hidden");

  const formData = new FormData();
  formData.append("action", "get_teacher_profile");
  formData.append("advisory_id", advisoryId);

  console.log("FETCHING TEACHER PROFILE FOR ADVISORY ID:", advisoryId);

  fetch(window.location.href, {
    method: "POST",
    body: formData,
  })
    .then((res) => {
      console.log("Response status:", res.status);
      return res.json();
    })
    .then((data) => {
      console.log("Teacher profile data:", data);
      if (loadingState) loadingState.classList.add("hidden");
      
      if (data && data.success && data.data) {
        populateTeacherProfile(data.data);
        if (formContent) formContent.classList.remove("hidden");
      } else {
        console.error("Failed response:", data);
        showToast(data?.message || "Failed to load teacher profile.", "error");
        closeTeacherProfileModal();
      }
    })
    .catch((error) => {
      console.error("Fetch error:", error);
      if (loadingState) loadingState.classList.add("hidden");
      showToast("Error loading profile: " + error.message, "error");
      closeTeacherProfileModal();
    });
}

function populateTeacherProfile(teacher) {
  console.log("POPULATING TEACHER PROFILE:", teacher);
  
  const nameParts = (teacher.name || "").trim().split(/\s+/);
  let firstName = "";
  let lastName = "";
  let suffix = "";

  const suffixIndex = nameParts.findIndex((p) => ["LPT", "PhD", "MA", "MS", "BSc", "Jr.", "Sr."].includes(p));

  if (suffixIndex !== -1 && suffixIndex > 0) {
    firstName = nameParts.slice(0, suffixIndex).join(" ");
    lastName = nameParts.slice(suffixIndex, -1).join(" ");
    suffix = nameParts[nameParts.length - 1];
  } else if (nameParts.length >= 2) {
    firstName = nameParts[0];
    lastName = nameParts.slice(1).join(" ");
  } else {
    firstName = teacher.name || "";
  }

  // Handle avatar image
  const avatarIcon = document.getElementById("teacherAvatarIcon");
  const avatarImg = document.getElementById("teacherAvatarImg");
  if (teacher.profile_pix && teacher.profile_pix !== '') {
    if (avatarImg) {
      avatarImg.src = teacher.profile_pix;
      avatarImg.classList.remove("hidden");
    }
    if (avatarIcon) avatarIcon.classList.add("hidden");
  } else {
    if (avatarImg) avatarImg.classList.add("hidden");
    if (avatarIcon) avatarIcon.classList.remove("hidden");
  }

  // Fill form fields
  const recordIdEl = document.getElementById("teacherRecordId");
  const teacherIdEl = document.getElementById("teacherIdField");
  const deptEl = document.getElementById("teacherDeptField");
  const firstNameEl = document.getElementById("teacherFirstName");
  const lastNameEl = document.getElementById("teacherLastName");
  const suffixEl = document.getElementById("teacherSuffix");
  const emailEl = document.getElementById("teacherEmail");
  const contactEl = document.getElementById("teacherContact");
  const specEl = document.getElementById("teacherSpecialization");

  if (recordIdEl) recordIdEl.value = teacher.user_id || "";
  if (teacherIdEl) teacherIdEl.value = teacher.teacher_id_field || "";
  if (deptEl) deptEl.value = teacher.department || "";
  if (firstNameEl) firstNameEl.value = firstName;
  if (lastNameEl) lastNameEl.value = lastName;
  if (suffixEl) suffixEl.value = suffix;
  if (emailEl) emailEl.value = teacher.email || "";
  if (contactEl) contactEl.value = teacher.contact_no || "";
  if (specEl) specEl.value = teacher.specialization || "";

  // Store profile pic path in hidden field
  let originalPicEl = document.getElementById("originalTeacherPix");
  if (!originalPicEl) {
    originalPicEl = document.createElement("input");
    originalPicEl.type = "hidden";
    originalPicEl.id = "originalTeacherPix";
    originalPicEl.value = teacher.profile_pix || "";
    document.getElementById("teacherFormContent").appendChild(originalPicEl);
  } else {
    originalPicEl.value = teacher.profile_pix || "";
  }
}

function closeTeacherProfileModal() {
  const modal = document.getElementById("teacherProfileModal");
  if (modal) {
    modal.classList.add("hidden");
  }
  document.body.style.overflow = "auto";
}

function handleTeacherPictureChange(event) {
  const file = event.target.files[0];
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  const maxSize = 5 * 1024 * 1024;

  if (!allowedTypes.includes(file.type)) {
    showToast("Only JPG, PNG, and WebP images are allowed.", "error");
    event.target.value = '';
    return;
  }

  if (file.size > maxSize) {
    showToast("File size exceeds 5MB limit.", "error");
    event.target.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    const avatarImg = document.getElementById("teacherAvatarImg");
    const avatarIcon = document.getElementById("teacherAvatarIcon");
    avatarImg.src = e.target.result;
    avatarImg.classList.remove("hidden");
    if (avatarIcon) avatarIcon.classList.add("hidden");
  };
  reader.readAsDataURL(file);
}

function saveTeacherProfile() {
  const teacherId = document.getElementById("teacherRecordId").value;
  const teacherIdField = document.getElementById("teacherIdField").value.trim();
  const firstName = document.getElementById("teacherFirstName").value.trim();
  const lastName = document.getElementById("teacherLastName").value.trim();
  const suffix = document.getElementById("teacherSuffix").value.trim();
  const email = document.getElementById("teacherEmail").value.trim();
  const contact = document.getElementById("teacherContact").value.trim();
  const department = document.getElementById("teacherDeptField").value;
  const specialization = document.getElementById("teacherSpecialization").value.trim();
  const fileInput = document.getElementById("teacherPictureInput");

  if (!teacherIdField || !firstName || !lastName) {
    showToast("Teacher ID, First name, and Last name are required.", "error");
    return;
  }

  let profilePicPath = document.getElementById("originalTeacherPix").value;

  if (fileInput.files.length > 0) {
    const formDataUpload = new FormData();
    formDataUpload.append("action", "upload_teacher_profile_pic");
    formDataUpload.append("teacher_id", teacherId);
    formDataUpload.append("profile_pic", fileInput.files[0]);

    fetch(window.location.href, {
      method: "POST",
      body: formDataUpload,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          profilePicPath = data.path;
          saveTeacherProfileData(teacherId, teacherIdField, firstName, lastName, suffix, email, contact, department, specialization, profilePicPath);
        } else {
          showToast(data.message || "Failed to upload image.", "error");
        }
      })
      .catch(() => showToast("Error uploading image.", "error"));
  } else {
    saveTeacherProfileData(teacherId, teacherIdField, firstName, lastName, suffix, email, contact, department, specialization, profilePicPath);
  }
}

function saveTeacherProfileData(teacherId, teacherIdField, firstName, lastName, suffix, email, contact, department, specialization, profilePicPath) {
  const fullName = `${firstName} ${lastName}${suffix ? ' ' + suffix : ''}`.trim();

  const formData = new FormData();
  formData.append("action", "update_teacher_info");
  formData.append("teacher_id", teacherId);
  formData.append("teacher_id_field", teacherIdField);
  formData.append("name", fullName);
  formData.append("email", email);
  formData.append("contact_no", contact);
  formData.append("department", department);
  formData.append("specialization", specialization);
  formData.append("profile_pix", profilePicPath);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message, "success");
        closeTeacherProfileModal();
        if (allData.length > 0) {
          if (currentView === "advisory") {
            loadAdvisoryList();
          } else if (currentView === "subject") {
            loadSubjectTeachersList();
          } else {
            loadFilteredData();
          }
        }
      } else {
        showToast(data.message || "Update failed.", "error");
      }
    })
    .catch(() => showToast("Error saving profile.", "error"));
}

// ============================================================
// MODAL GRADE FILTER
// ============================================================
function filterModalStudents(grade) {
  currentGradeFilter = grade;
  ["all", "7", "8", "9", "10", "11", "12"].forEach((g) => {
    const tab = document.getElementById(`gradeTab_${g}`);
    if (tab) {
      if (g === grade) {
        tab.classList.add("ring-2", "ring-[#043915]", "font-black");
      } else {
        tab.classList.remove("ring-2", "ring-[#043915]", "font-black");
      }
    }
  });
  displayFilteredStudents();
}

function displayFilteredStudents() {
  const tbody = document.getElementById("modalStudentTable");
  const advisorySelect = document.getElementById("modalAdvisoryTeacher");
  const selectedAdvisory = advisorySelect.value
    ? advisorySelect.selectedOptions[0]
    : null;
  const advisoryGrade = selectedAdvisory
    ? selectedAdvisory.getAttribute("data-grade-level")
    : null;

  let filteredStudents = allAvailableStudents;

  if (currentGradeFilter !== "all") {
    filteredStudents = filteredStudents.filter(
      (s) => s.grade_level === currentGradeFilter
    );
  }

  if (advisoryGrade) {
    filteredStudents = filteredStudents.filter(
      (s) => s.grade_level === advisoryGrade
    );
  }

  if (filteredStudents.length === 0) {
    const gradeText =
      currentGradeFilter === "all"
        ? "students"
        : `Grade ${currentGradeFilter} students`;
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="py-12 text-center">
          <div class="flex flex-col items-center justify-center text-gray-400">
            <p class="text-sm font-medium text-gray-600">No ${gradeText} available</p>
            <p class="text-xs text-gray-400 mt-1">All students of this grade may already be assigned.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  const gradeColors = {
    "7":  "bg-blue-100 text-blue-800",
    "8":  "bg-orange-100 text-orange-800",
    "9":  "bg-purple-100 text-purple-800",
    "10": "bg-green-100 text-green-800",
    "11": "bg-pink-100 text-pink-800",
    "12": "bg-red-100 text-red-800",
  };

  let html = "";
  filteredStudents.forEach((student) => {
    const gradeClass = gradeColors[student.grade_level] || "bg-gray-100 text-gray-800";

    let gradeOptions = "";
    for (let g = 7; g <= 12; g++) {
      const sel = String(g) === String(student.grade_level) ? "selected" : "";
      gradeOptions += `<option value="${g}" ${sel}>Grade ${g}</option>`;
    }

    html += `
      <tr class="student-row hover:bg-gray-50 transition" data-grade="${student.grade_level}" data-student-id="${student.user_id}">
        <td class="py-3 px-6">
          <input type="checkbox" name="student_ids[]" value="${student.user_id}" class="student-checkbox w-4 h-4 text-[#043915] border-gray-300 rounded focus:ring-[#043915]">
          <input type="hidden" name="grade_levels[${student.user_id}]" id="gradeHidden_${student.user_id}" value="${student.grade_level}">
        </td>
        <td class="py-3 px-6">
          <div class="text-sm font-semibold text-gray-900">${escapeHtml(student.name)}</div>
          <div class="text-xs text-gray-500 mt-0.5">Grade ${student.grade_level}</div>
        </td>
        <td class="py-3 px-6 text-sm text-gray-700 font-mono">${escapeHtml(student.lrn || "—")}</td>
        <td class="py-3 px-6">
          <span class="px-3 py-1 ${gradeClass} rounded-lg text-xs font-bold">Grade ${student.grade_level}</span>
        </td>
        <td class="py-3 px-6">
          <select onchange="updateStudentGradeInModal(this, ${student.user_id})"
            class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-bold bg-white text-gray-700 focus:ring-2 focus:ring-[#043915] focus:border-[#043915] transition">
            ${gradeOptions}
          </select>
        </td>
      </tr>
    `;
  });
  tbody.innerHTML = html;
}

function updateStudentGradeInModal(selectEl, studentId) {
  const newGrade = selectEl.value;
  const hiddenInput = document.getElementById(`gradeHidden_${studentId}`);
  if (hiddenInput) hiddenInput.value = newGrade;

  const row = selectEl.closest("tr");
  if (row) row.setAttribute("data-grade", newGrade);

  const badge = row ? row.querySelector("td:nth-child(4) span") : null;
  const gradeColors = {
    "7":  "bg-blue-100 text-blue-800",
    "8":  "bg-orange-100 text-orange-800",
    "9":  "bg-purple-100 text-purple-800",
    "10": "bg-green-100 text-green-800",
    "11": "bg-pink-100 text-pink-800",
    "12": "bg-red-100 text-red-800",
  };
  if (badge) {
    badge.className = `px-3 py-1 ${gradeColors[newGrade] || "bg-gray-100 text-gray-800"} rounded-lg text-xs font-bold`;
    badge.innerHTML = `Grade ${newGrade}`;
  }

  const subtitle = row ? row.querySelector("td:nth-child(2) .text-xs") : null;
  if (subtitle) subtitle.innerHTML = `Grade ${newGrade}`;

  const formData = new FormData();
  formData.append("action",     "update_student_grade_by_id");
  formData.append("student_id", studentId);
  formData.append("new_grade",  newGrade);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        const student = allAvailableStudents.find((s) => String(s.user_id) === String(studentId));
        if (student) student.grade_level = newGrade;
        showToast(`Grade updated to Grade ${newGrade}`, "success");
      } else {
        showToast(data.message || "Failed to update grade.", "error");
      }
    })
    .catch(() => showToast("Error updating grade.", "error"));
}

function renderPagination(totalItems) {
  const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));
  const container = document.getElementById("paginationContainer");
  let html = "";

  html += `
    <button onclick="changePage(${currentPage - 1})" 
      ${currentPage === 1 ? "disabled" : ""}
      class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition">
      ◀
    </button>
  `;

  const maxVisiblePages = 5;
  let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
  if (endPage - startPage < maxVisiblePages - 1) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }

  if (startPage > 1) {
    html += `<button onclick="changePage(1)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold transition">«</button>`;
    if (startPage > 2) html += `<span class="px-2 text-gray-400">…</span>`;
  }

  for (let i = startPage; i <= endPage; i++) {
    const isActive = i === currentPage;
    html += `
      <button onclick="changePage(${i})" 
        class="w-10 h-10 flex items-center justify-center rounded-xl ${isActive ? "bg-[#f8c922] text-[#043915] font-bold shadow-md" : "bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold"} transition">
        ${i}
      </button>
    `;
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) html += `<span class="px-2 text-gray-400">…</span>`;
    html += `<button onclick="changePage(${totalPages})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-semibold transition">»</button>`;
  }

  html += `
    <button onclick="changePage(${currentPage + 1})" 
      ${currentPage === totalPages ? "disabled" : ""}
      class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition">
      ▶
    </button>
  `;

  container.innerHTML = html;
}

function changePage(page) {
  const totalPages = Math.max(1, Math.ceil(allData.length / ITEMS_PER_PAGE));
  if (page < 1 || page > totalPages) return;
  currentPage = page;

  if (currentView === "advisory") {
    displayAdvisoryList(allData);
  } else if (currentView === "subject") {
    displaySubjectTeachersList(allData);
  } else {
    displayTableData(allData);
  }
}

function getPaginatedData(data) {
  const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
  return data.slice(startIndex, startIndex + ITEMS_PER_PAGE);
}

// ============================================================
// TEACHER MODAL
// ============================================================
function openTeacherModal() {
  document.getElementById("teacherModal").classList.remove("hidden");
  document.getElementById("teacherModal").classList.add("flex");
}

function closeTeacherModal() {
  document.getElementById("teacherModal").classList.add("hidden");
  document.getElementById("teacherModal").classList.remove("flex");
  document.getElementById("assignTeacherForm").reset();
  document.getElementById("advisoryFields").classList.add("hidden");
}

function toggleAdvisoryFields() {
  const roleType = document.getElementById("teacherRoleType").value;
  const advisoryFields = document.getElementById("advisoryFields");
  const advisoryNameInput = document.getElementById("advisoryNameInput");
  const advisoryGradeLevel = document.getElementById("advisoryGradeLevel");

  if (roleType === "advisory") {
    advisoryFields.classList.remove("hidden");
    advisoryNameInput.required = true;
    advisoryGradeLevel.required = true;
  } else {
    advisoryFields.classList.add("hidden");
    advisoryNameInput.required = false;
    advisoryGradeLevel.required = false;
  }
}

function submitTeacherAssignment(event) {
  event.preventDefault();
  const formData = new FormData(event.target);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message, "success");
        closeTeacherModal();
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast(data.message, "error");
      }
    })
    .catch(() => showToast("An error occurred. Please try again.", "error"));
}

// ============================================================
// STUDENT ASSIGNMENT MODAL
// ============================================================
function openStudentModal() {
  const advisorySelect = document.getElementById("modalAdvisoryTeacher");
  if (advisorySelect.options.length <= 1) {
    showToast("Please assign an advisory teacher first before assigning students.", "error");
    return;
  }

  document.getElementById("studentModal").classList.remove("hidden");
  document.getElementById("studentModal").classList.add("flex");
  loadAllAvailableStudents();
}

function closeStudentModal() {
  document.getElementById("studentModal").classList.add("hidden");
  document.getElementById("studentModal").classList.remove("flex");
  document.getElementById("assignStudentsForm").reset();
  document.getElementById("advisoryCapacityInfo").classList.add("hidden");
  currentGradeFilter = "all";
  allAvailableStudents = [];

  ["all", "7", "8", "9", "10", "11", "12"].forEach((g) => {
    const tab = document.getElementById(`gradeTab_${g}`);
    if (tab) tab.classList.remove("ring-2", "ring-[#043915]", "font-black");
  });
  document.getElementById("gradeTab_all")?.classList.add("ring-2", "ring-[#043915]", "font-black");
}

function loadAllAvailableStudents() {
  const tbody = document.getElementById("modalStudentTable");
  tbody.innerHTML = `
    <tr>
      <td colspan="4" class="py-12 text-center">
        <div class="flex flex-col items-center justify-center text-gray-400">
          <p class="text-sm font-medium text-gray-600">Loading Available Students...</p>
        </div>
      </td>
    </tr>
  `;

  const formData = new FormData();
  formData.append("action", "get_unassigned_students");
  formData.append("advisory_id", 0);
  formData.append("grade_level", "");

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        allAvailableStudents = data.data;
        displayFilteredStudents();
      } else {
        tbody.innerHTML = `<tr><td colspan="4" class="py-12 text-center text-gray-400"><p class="text-sm font-medium text-gray-600">Error Loading Students</p></td></tr>`;
      }
    })
    .catch(() => {
      tbody.innerHTML = `<tr><td colspan="4" class="py-12 text-center text-gray-400"><p class="text-sm font-medium text-gray-600">Error Loading Students</p></td></tr>`;
    });
}

document.getElementById("modalAdvisoryTeacher")?.addEventListener("change", function () {
  const selectedOption = this.selectedOptions[0];
  const advisoryId = this.value;
  const capacityInfo = document.getElementById("advisoryCapacityInfo");

  if (!advisoryId) {
    capacityInfo.classList.add("hidden");
    displayFilteredStudents();
    return;
  }

  const currentCount = parseInt(selectedOption.getAttribute("data-current-count")) || 0;
  const advisoryGrade = selectedOption.getAttribute("data-grade-level");

  document.getElementById("currentStudentCount").textContent = currentCount;
  document.getElementById("remainingSlots").textContent = 40 - currentCount;
  document.getElementById("advisoryGradeLevel").textContent = advisoryGrade;
  capacityInfo.classList.remove("hidden");
  displayFilteredStudents();
});

// ============================================================
// REASSIGN MODAL
// ============================================================
function openReassignModal(assignmentId, studentName, currentGrade) {
  document.getElementById("reassignAssignmentId").value = assignmentId;
  document.getElementById("reassignStudentName").textContent = studentName;
  document.getElementById("reassignCurrentGrade").value = currentGrade;

  const select = document.getElementById("reassignAdvisorySelect");
  Array.from(select.options).forEach((option) => {
    if (option.value) {
      const optionGrade = option.getAttribute("data-grade");
      if (optionGrade !== currentGrade.toString()) {
        option.disabled = true;
        option.classList.add("text-gray-400");
      } else {
        option.disabled = false;
        option.classList.remove("text-gray-400");
      }
    }
  });

  document.getElementById("reassignModal").classList.remove("hidden");
  document.getElementById("reassignModal").classList.add("flex");
}

function closeReassignModal() {
  document.getElementById("reassignModal").classList.add("hidden");
  document.getElementById("reassignModal").classList.remove("flex");
  document.getElementById("reassignForm").reset();
}

function submitReassignment(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  const selectedAdvisory = document.getElementById("reassignAdvisorySelect").selectedOptions[0];
  const advisoryGrade = selectedAdvisory.getAttribute("data-grade");
  const currentGrade = formData.get("current_grade");

  if (advisoryGrade !== currentGrade) {
    showToast(`Cannot reassign. Student is in Grade ${currentGrade} but selected advisory is for Grade ${advisoryGrade}.`, "error");
    return;
  }

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message, "success");
        closeReassignModal();
        loadFilteredData();
      } else {
        showToast(data.message, "error");
      }
    })
    .catch(() => showToast("An error occurred. Please try again.", "error"));
}

// ============================================================
// REMOVE ADVISORY MODAL
// ============================================================
function openRemoveAdvisoryModal(assignmentId, studentName) {
  document.getElementById("removeAssignmentId").value = assignmentId;
  document.getElementById("removeStudentName").textContent = studentName;
  document.getElementById("removeAdvisoryModal").classList.remove("hidden");
  document.getElementById("removeAdvisoryModal").classList.add("flex");
}

function closeRemoveAdvisoryModal() {
  document.getElementById("removeAdvisoryModal").classList.add("hidden");
  document.getElementById("removeAdvisoryModal").classList.remove("flex");
}

function submitRemoval(event) {
  event.preventDefault();
  const formData = new FormData(event.target);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message, "success");
        closeRemoveAdvisoryModal();
        loadFilteredData();
      } else {
        showToast(data.message, "error");
      }
    })
    .catch(() => showToast("An error occurred. Please try again.", "error"));
}

// ============================================================
// CONVERT TEACHER MODAL
// ============================================================
function openConvertTeacherModal(advisoryId, teacherName) {
  document.getElementById("convertAdvisoryId").value = advisoryId;
  document.getElementById("convertTeacherName").textContent = teacherName;
  document.getElementById("convertTeacherModal").classList.remove("hidden");
  document.getElementById("convertTeacherModal").classList.add("flex");
}

function closeConvertTeacherModal() {
  document.getElementById("convertTeacherModal").classList.add("hidden");
  document.getElementById("convertTeacherModal").classList.remove("flex");
}

function submitConversion(event) {
  event.preventDefault();
  const formData = new FormData(event.target);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message, "success");
        closeConvertTeacherModal();
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast(data.message, "error");
      }
    })
    .catch(() => showToast("An error occurred. Please try again.", "error"));
}

// ============================================================
// SELECT ALL / CONFIRM STUDENT ASSIGNMENT
// ============================================================
function toggleAllVisibleStudents(checked) {
  document.querySelectorAll(".student-checkbox").forEach((cb) => {
    const row = cb.closest("tr");
    if (row && row.style.display !== "none") {
      cb.checked = checked;
    }
  });
}

function confirmStudentAssignment() {
  const advisorySelect = document.getElementById("modalAdvisoryTeacher");
  const advisoryId = advisorySelect.value;
  const selectedStudents = document.querySelectorAll(".student-checkbox:checked");

  if (!advisoryId) {
    showToast("Please select an advisory teacher first.", "error");
    return;
  }

  if (selectedStudents.length === 0) {
    showToast("Please select at least one student.", "error");
    return;
  }

  const selectedOption = advisorySelect.selectedOptions[0];
  const currentCount = parseInt(selectedOption.getAttribute("data-current-count")) || 0;
  const newTotal = currentCount + selectedStudents.length;

  if (newTotal > 40) {
    const remaining = 40 - currentCount;
    showToast(`Cannot assign ${selectedStudents.length} students. Only ${remaining} slots available (Maximum: 40).`, "error");
    return;
  }

  document.getElementById("hiddenAdvisoryId").value = advisoryId;
  const formData = new FormData(document.getElementById("assignStudentsForm"));

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message, "success");
        closeStudentModal();
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast(data.message, "error");
      }
    })
    .catch(() => showToast("An error occurred. Please try again.", "error"));
}

// ============================================================
// FILTERS & DYNAMIC TABLE TITLE
// ============================================================

function updateTableTitle() {
  const teacherRole = document.getElementById("filterTeacherRole").value;

  if (teacherRole === "advisory") {
    document.querySelector("h1").innerHTML = 'Advisory Class Management';
  } else if (teacherRole === "subject") {
    document.querySelector("h1").innerHTML = 'Subject Teacher Management';
  } else {
    document.querySelector("h1").innerHTML = 'Advisory Class Management';
  }
}

function applyFilters(event) {
  if (event) event.preventDefault();
  currentPage = 1;
  const teacherRole = document.getElementById("filterTeacherRole").value;

  if (teacherRole === "advisory") {
    currentView = "advisory";
    loadAdvisoryList();
  } else if (teacherRole === "subject") {
    currentView = "subject";
    loadSubjectTeachersList();
  } else {
    currentView = "default";
    loadFilteredData();
  }

  updateTableTitle();
}

function loadSubjectTeachersList() {
  const search = document.getElementById("searchInput").value;
  const formData = new FormData();
  formData.append("action", "get_subject_teachers");

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        let filteredData = data.data;
        if (search.trim() !== "") {
          filteredData = filteredData.filter((t) =>
            t.teacher_name.toLowerCase().includes(search.toLowerCase()) ||
            t.teacher_email.toLowerCase().includes(search.toLowerCase())
          );
        }
        allData = filteredData;
        displaySubjectTeachersList(filteredData);
      }
    })
    .catch((e) => console.error("Error:", e));
}

function displaySubjectTeachersList(teachers) {
  const tbody = document.getElementById("tableBody");
  const countEl = document.getElementById("resultCount");
  countEl.textContent = teachers.length === 0
    ? "No subject teachers found"
    : `Showing ${teachers.length} subject teacher${teachers.length !== 1 ? 's' : ''}`;

  if (teachers.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="px-6 py-20 text-center">
          <div class="flex flex-col items-center justify-center text-gray-400">
            <p class="text-base font-semibold text-gray-600 mb-1">No Subject Teachers Found</p>
            <p class="text-sm text-gray-500">Assign subject teachers to see them here</p>
          </div>
        </td>
      </tr>
    `;
    renderPagination(0);
    return;
  }

  const paginatedTeachers = getPaginatedData(teachers);
  let html = "";
  paginatedTeachers.forEach((teacher) => {
    html += `
      <tr class="hover:bg-gray-50 transition">
        <td class="py-4 px-6" colspan="3">
          <div class="text-sm font-medium text-gray-900">${escapeHtml(teacher.teacher_name)}</div>
          <div class="text-xs text-gray-500">${escapeHtml(teacher.teacher_email)}</div>
        </td>
        <td class="py-4 px-6" colspan="2">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">Subject Teacher</span>
        </td>
        <td class="py-4 px-6" colspan="2">
          <span class="text-xs text-gray-500">${formatDate(teacher.assigned_at)}</span>
        </td>
      </tr>
    `;
  });
  tbody.innerHTML = html;
  renderPagination(teachers.length);
}

function resetFilters() {
  document.getElementById("filterTeacherRole").value = "";
  document.getElementById("filterGrade").value = "";
  document.getElementById("sortName").value = "ASC";
  document.getElementById("filterDate").value = "";
  document.getElementById("searchInput").value = "";
  currentPage = 1;
  currentView = "default";
  loadFilteredData();
  updateTableTitle();
  showToast("Filters cleared", "success");
}


function loadAdvisoryList() {
  const search = document.getElementById("searchInput").value;
  const sortName = document.getElementById("sortName").value;

  const formData = new FormData();
  formData.append("action", "get_advisory_list");
  formData.append("sort_by", "advisory_name");
  formData.append("sort_order", sortName);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        let filteredData = data.data;
        if (search.trim() !== "") {
          filteredData = filteredData.filter((a) =>
            a.advisory_name.toLowerCase().includes(search.toLowerCase()) ||
            a.teacher_name.toLowerCase().includes(search.toLowerCase()) ||
            a.grade_level.includes(search)
          );
        }
        allData = filteredData;
        displayAdvisoryList(filteredData);
      }
    })
    .catch((e) => console.error("Error:", e));
}

function displayAdvisoryList(advisories) {
  const tbody = document.getElementById("tableBody");
  document.getElementById("resultCount").textContent = advisories.length === 0
    ? "No advisory classes found"
    : `Showing ${advisories.length} advisory class${advisories.length !== 1 ? 'es' : ''}`;

  if (advisories.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="px-6 py-20 text-center">
          <div class="flex flex-col items-center justify-center text-gray-400">
            <p class="text-base font-semibold text-gray-600 mb-1">No Advisory Classes Found</p>
            <p class="text-sm text-gray-500">Assign advisory teachers to create classes</p>
          </div>
        </td>
      </tr>
    `;
    renderPagination(0);
    return;
  }

  const paginatedAdvisories = getPaginatedData(advisories);
  let html = "";
  paginatedAdvisories.forEach((advisory) => {
    const gradeColors = {
      7: "bg-blue-100 text-blue-800",
      8: "bg-orange-100 text-orange-800",
      9: "bg-purple-100 text-purple-800",
      10: "bg-green-100 text-green-800",
    };
    const gradeClass = gradeColors[advisory.grade_level] || "bg-gray-100 text-gray-800";
    const capacityClass =
      advisory.student_count >= 40
        ? "bg-red-100 text-red-800"
        : advisory.student_count >= 35
        ? "bg-orange-100 text-orange-800"
        : "bg-green-100 text-green-800";

    html += `
      <tr class="hover:bg-gray-50 transition">
        <td class="py-4 px-6" colspan="2">
          <div class="text-sm font-medium text-gray-900">${escapeHtml(advisory.advisory_name)}</div>
          <div class="text-xs text-gray-500">Teacher: ${escapeHtml(advisory.teacher_name)}</div>
        </td>
        <td class="py-4 px-6">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${gradeClass}">Grade ${advisory.grade_level}</span>
        </td>
        <td class="py-4 px-6" colspan="2">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${capacityClass}">${advisory.student_count}/40 Students</span>
        </td>
        <td class="py-4 px-6">
          <button onclick="viewAdvisoryDetails(${advisory.advisory_id}, '${advisory.grade_level}')" 
            class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-xs font-bold hover:bg-blue-600 transition">
            View
          </button>
        </td>
        <td class="py-4 px-6 text-center">
          <button onclick="openTeacherProfileModal(${advisory.advisory_id})" 
            class="px-3 py-1.5 bg-purple-500 text-white rounded-lg text-xs font-bold hover:bg-purple-600 transition" 
            title="View Teacher Profile">
            Profile
          </button>
        </td>
      </tr>
    `;
  });
  tbody.innerHTML = html;
  renderPagination(advisories.length);
}

// ============================================================
// ADVISORY DETAIL VIEW
// ============================================================
function viewAdvisoryDetails(advisoryId, advisoryGrade) {
  currentAdvisoryGrade = advisoryGrade;

  const formData = new FormData();
  formData.append("action", "get_advisory_students");
  formData.append("advisory_id", advisoryId);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        displayAdvisoryStudents(data.data, advisoryId, advisoryGrade);
      }
    })
    .catch((e) => console.error("Error:", e));
}

function displayAdvisoryStudents(students, advisoryId, advisoryGrade) {
  const modal = document.getElementById("viewAdvisoryModal");
  const studentsList = document.getElementById("advisoryStudentsList");
  const promotionControls = document.getElementById("gradePromotionControls");

  if (students.length > 0) {
    document.getElementById("advisoryDetailTitle").textContent = "Advisory Students";
    document.getElementById("advisoryDetailSubtitle").textContent = `${students.length}/40 Students - Grade ${advisoryGrade}`;
  }

  if (students.length > 0 && promotionControls) {
    promotionControls.classList.remove("hidden");
    setupGradePromotionDropdown(advisoryGrade);
  } else if (promotionControls) {
    promotionControls.classList.add("hidden");
  }

  if (students.length === 0) {
    studentsList.innerHTML = `
      <div class="py-12 text-center">
        <p class="text-sm font-medium text-gray-600">No Students Assigned</p>
        <p class="text-xs text-gray-500 mt-1">This advisory class has no students yet</p>
      </div>
    `;
  } else {
    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[500px] overflow-y-auto pr-2">';
    students.forEach((student) => {
      const gradeColors = {
        7: "bg-blue-100 text-blue-800",
        8: "bg-orange-100 text-orange-800",
        9: "bg-purple-100 text-purple-800",
        10: "bg-green-100 text-green-800",
      };
      const gradeClass = gradeColors[student.grade_level] || "bg-gray-100 text-gray-800";

      html += `
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 hover:shadow-md transition">
          <div class="flex items-start gap-3 mb-2">
            <input type="checkbox" 
              class="student-promotion-checkbox mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
              data-assignment-id="${student.assignment_id}"
              data-student-id="${student.student_id}"
              data-student-name="${escapeHtml(student.student_name)}"
              data-current-grade="${student.grade_level}"
              onchange="updateSelectedCount()">
            <div class="flex-1">
              <p class="font-bold text-gray-900">${escapeHtml(student.student_name)}</p>
              <p class="text-xs text-gray-500">LRN: ${escapeHtml(student.lrn)}</p>
            </div>
            <span class="px-2 py-1 rounded-lg text-xs font-bold ${gradeClass}">Grade ${student.grade_level}</span>
          </div>
          <p class="text-xs text-gray-400">Assigned: ${formatDate(student.assigned_date)}</p>
        </div>
      `;
    });
    html += "</div>";
    studentsList.innerHTML = html;
  }

  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function setupGradePromotionDropdown(currentGrade) {
  const dropdown = document.getElementById("bulkGradeSelect");
  dropdown.innerHTML = '<option value="">Select Grade Level</option>';
  const gradeNum = parseInt(currentGrade);

  if (gradeNum < 10) {
    const nextGrade = gradeNum + 1;
    const option = document.createElement("option");
    option.value = nextGrade;
    option.textContent = `Grade ${nextGrade}`;
    dropdown.appendChild(option);
  }

  if (gradeNum >= 10) {
    dropdown.disabled = true;
    document.getElementById("gradePromotionControls").innerHTML = `
      <div class="bg-gray-100 rounded-2xl p-6 border border-gray-200 text-center">
        <p class="text-sm font-bold text-gray-600">Students are already at the highest grade level (Grade 10)</p>
        <p class="text-xs text-gray-500 mt-1">Grade promotion is not available</p>
      </div>
    `;
  } else {
    dropdown.disabled = false;
  }
}

function toggleAllStudentsPromotion(checked) {
  document.querySelectorAll(".student-promotion-checkbox").forEach((cb) => {
    cb.checked = checked;
  });
  updateSelectedCount();
}

function updateSelectedCount() {
  const selected = document.querySelectorAll(".student-promotion-checkbox:checked");
  const count = selected.length;
  document.getElementById("selectedCount").textContent = `${count} student${count !== 1 ? "s" : ""} selected`;

  const selectAll = document.getElementById("selectAllStudentsPromotion");
  const all = document.querySelectorAll(".student-promotion-checkbox");
  if (selectAll && all.length > 0) {
    selectAll.checked = count === all.length;
  }
}

function confirmBulkGradePromotion() {
  const selectedCheckboxes = document.querySelectorAll(".student-promotion-checkbox:checked");
  const newGrade = document.getElementById("bulkGradeSelect").value;

  if (selectedCheckboxes.length === 0) {
    showToast("Please select at least one student", "error");
    return;
  }

  if (!newGrade) {
    showToast("Please select a grade level", "error");
    return;
  }

  const currentGrade = selectedCheckboxes[0].getAttribute("data-current-grade");
  const allSameGrade = Array.from(selectedCheckboxes).every(
    (cb) => cb.getAttribute("data-current-grade") === currentGrade
  );

  if (!allSameGrade) {
    showToast("All selected students must be in the same grade level", "error");
    return;
  }

  if (parseInt(newGrade) <= parseInt(currentGrade)) {
    showToast("Can only promote students to higher grade levels", "error");
    return;
  }

  showPromotionConfirmation(selectedCheckboxes, currentGrade, newGrade);
}

function showPromotionConfirmation(selectedCheckboxes, currentGrade, newGrade) {
  const studentCount = selectedCheckboxes.length;
  const studentNames = Array.from(selectedCheckboxes)
    .slice(0, 5)
    .map((cb) => cb.getAttribute("data-student-name"));

  const modal = document.createElement("div");
  modal.id = "promotionConfirmModal";
  modal.className = "fixed inset-0 bg-black/60 flex items-center justify-center z-[150] backdrop-blur-sm p-4";
  modal.innerHTML = `
    <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl relative animate-scale-in">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
          
        </div>
        <h2 class="text-xl font-bold text-[#043915] mb-2">Confirm Grade Promotion</h2>
        <p class="text-sm text-gray-600">You are about to promote ${studentCount} student${studentCount > 1 ? "s" : ""} from Grade ${currentGrade} to Grade ${newGrade}</p>
      </div>
      <div class="bg-blue-50 rounded-xl p-4 mb-6 max-h-48 overflow-y-auto">
        <p class="text-xs font-bold text-blue-900 mb-2 uppercase tracking-wider">Selected Students:</p>
        <ul class="space-y-1 text-sm text-blue-800">
          ${studentNames.map((name) => `<li class="flex items-center gap-2">${name}</li>`).join("")}
          ${studentCount > 5 ? `<li class="text-xs text-blue-600 font-bold mt-2">+ ${studentCount - 5} more students</li>` : ""}
        </ul>
      </div>
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
          <div class="flex-1">
            <p class="text-xs font-bold text-amber-900 mb-1">Important Notice</p>
            <p class="text-xs text-amber-800">Students will be removed from their current advisory class and made available for assignment to Grade ${newGrade} advisory classes.</p>
          </div>
        </div>
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="closePromotionConfirm()" class="flex-1 px-6 py-3 border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-50 transition">Cancel</button>
        <button type="button" onclick="executePromotion()" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition shadow-lg">
          Promote Now
        </button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
}

function closePromotionConfirm() {
  const modal = document.getElementById("promotionConfirmModal");
  if (modal) modal.remove();
}

function executePromotion() {
  const selectedCheckboxes = document.querySelectorAll(".student-promotion-checkbox:checked");
  const newGrade = document.getElementById("bulkGradeSelect").value;
  const assignmentIds = Array.from(selectedCheckboxes).map((cb) => cb.getAttribute("data-assignment-id"));

  closePromotionConfirm();
  showToast("Processing updates...", "success");

  const formData = new FormData();
  formData.append("action", "bulk_update_student_grade");
  assignmentIds.forEach((id) => formData.append("assignment_ids[]", id));
  formData.append("new_grade", newGrade);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (typeof closeViewAdvisoryModal === "function") closeViewAdvisoryModal();

      const successModal = document.createElement("div");
      successModal.id = "successPromotionModal";
      successModal.className = "fixed inset-0 bg-slate-900/60 flex items-center justify-center z-[200] backdrop-blur-md p-4 transition-opacity duration-500 opacity-0";
      successModal.innerHTML = `
        <div class="bg-white w-full max-w-sm rounded-[2.5rem] p-10 shadow-2xl text-center">
          <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
          </div>
          <h2 class="text-2xl font-black text-slate-800 mb-2">Update Successful</h2>
          <p class="text-sm text-slate-500 mb-8 leading-relaxed">Student records have been updated. The dashboard will refresh in 3 seconds.</p>
          <button onclick="window.location.reload()" class="w-full py-4 bg-[#043915] text-white font-bold rounded-2xl transition-all shadow-xl hover:bg-opacity-90">Refresh Now</button>
        </div>
      `;
      document.body.appendChild(successModal);
      setTimeout(() => successModal.classList.remove("opacity-0"), 100);

      setTimeout(() => {
        successModal.classList.add("opacity-0");
        setTimeout(() => window.location.reload(), 500);
      }, 3000);
    })
    .catch(() => window.location.reload());
}

function closeViewAdvisoryModal() {
  const modal = document.getElementById("viewAdvisoryModal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
  document.getElementById("selectAllStudentsPromotion").checked = false;
  document.getElementById("bulkGradeSelect").value = "";
  currentAdvisoryGrade = null;
}

// ============================================================
// MAIN TABLE DATA
// ============================================================
function loadFilteredData() {
  const teacherRole = document.getElementById("filterTeacherRole").value;
  const gradeLevel = document.getElementById("filterGrade").value;
  const dateFilter = document.getElementById("filterDate").value;
  const search = document.getElementById("searchInput").value;
  const sortName = document.getElementById("sortName").value;

  const formData = new FormData();
  formData.append("action", "get_filtered_data");
  formData.append("teacher_role", teacherRole);
  formData.append("grade_level", gradeLevel);
  formData.append("date_filter", dateFilter);
  formData.append("search", search);
  formData.append("sort_by", "student_name");
  formData.append("sort_order", sortName);

  fetch(window.location.href, { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        allData = data.data;
        displayTableData(data.data);
      }
    })
    .catch((e) => console.error("Error:", e));
}

function displayTableData(data) {
  const tbody = document.getElementById("tableBody");
  const countEl = document.getElementById("resultCount");
  countEl.textContent = data.length === 0
    ? "No records found"
    : `Showing ${data.length} student assignment${data.length !== 1 ? 's' : ''}`;

  if (data.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="px-6 py-20 text-center">
          <div class="flex flex-col items-center justify-center text-gray-400">
            <p class="text-base font-semibold text-gray-600 mb-1">No Assignments Found</p>
            <p class="text-sm text-gray-500">Try adjusting your filters or assign students to advisory classes</p>
          </div>
        </td>
      </tr>
    `;
    renderPagination(0);
    return;
  }

  const paginatedData = getPaginatedData(data);
  let html = "";

  paginatedData.forEach((row) => {
    const gradeColors = {
      7: "bg-blue-100 text-blue-800",
      8: "bg-orange-100 text-orange-800",
      9: "bg-purple-100 text-purple-800",
      10: "bg-green-100 text-green-800",
    };
    const gradeClass = gradeColors[row.grade_level] || "bg-gray-100 text-gray-800";

    html += `
      <tr class="hover:bg-gray-50 transition">
        <td class="py-4 px-6">
          <div class="text-sm font-medium text-gray-900">${escapeHtml(row.student_name)}</div>
          <div class="text-xs text-gray-500">Grade ${row.grade_level}</div>
        </td>
        <td class="py-4 px-6 text-sm text-gray-600">${escapeHtml(row.lrn || "N/A")}</td>
        <td class="py-4 px-6">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${gradeClass}">Grade ${row.grade_level}</span>
        </td>
        <td class="py-4 px-6 text-sm text-gray-900">${escapeHtml(row.teacher_name)}</td>
        <td class="py-4 px-6">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">${escapeHtml(row.advisory_name)}</span>
        </td>
        <td class="py-4 px-6 text-sm text-gray-600">${formatDate(row.assigned_date)}</td>
        <td class="py-4 px-6">
          <div class="flex items-center justify-center gap-2">
            <button onclick="openReassignModal(${row.assignment_id}, '${escapeHtml(row.student_name)}', ${row.grade_level})" 
              class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-xs font-bold hover:bg-blue-600 transition" 
              title="Reassign to different advisory">
              Reassign
            </button>
            <button onclick="openRemoveAdvisoryModal(${row.assignment_id}, '${escapeHtml(row.student_name)}')" 
              class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-bold hover:bg-red-600 transition" 
              title="Remove from advisory">
              Remove
            </button>
            <button onclick="openStudentProfileModal(${row.student_id})" 
              class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-bold hover:bg-green-700 transition" 
              title="View Student Profile">
              View
            </button>
          </div>
        </td>
      </tr>
    `;
  });

  tbody.innerHTML = html;
  renderPagination(data.length);
}

// ============================================================
// UTILITIES
// ============================================================
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function formatDate(dateString) {
  if (!dateString) return "—";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

// ============================================================
// DOMContentLoaded
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
  checkAdvisoryAvailability();

  loadFilteredData();

  const teacherRoleEl = document.getElementById("filterTeacherRole");
  if (teacherRoleEl) {
    teacherRoleEl.addEventListener("change", () => {
      currentPage = 1;
      const role = teacherRoleEl.value;
      if (role === "advisory") {
        currentView = "advisory";
        loadAdvisoryList();
      } else if (role === "subject") {
        currentView = "subject";
        loadSubjectTeachersList();
      } else {
        currentView = "default";
        loadFilteredData();
      }
      updateTableTitle();
    });
  }

  ["filterGrade", "sortName", "filterDate"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.addEventListener("change", () => { currentPage = 1; applyFilters(); });
  });

  const searchEl = document.getElementById("searchInput");
  if (searchEl) {
    let timer;
    searchEl.addEventListener("input", () => {
      clearTimeout(timer);
      timer = setTimeout(() => { currentPage = 1; applyFilters(); }, 350);
    });
  }
});

function checkAdvisoryAvailability() {
  const advisorySelect = document.getElementById("modalAdvisoryTeacher");
  const hasAdvisoryTeachers = advisorySelect.options.length > 1;
  const assignStudentBtn = document.getElementById("assignStudentBtn");
  const noAdvisoryMessage = document.getElementById("noAdvisoryMessage");

  if (!hasAdvisoryTeachers) {
    assignStudentBtn.classList.add("hidden");
    noAdvisoryMessage.classList.remove("hidden");
  } else {
    assignStudentBtn.classList.remove("hidden");
    noAdvisoryMessage.classList.add("hidden");
  }
}

const style = document.createElement("style");
style.textContent = `
  @keyframes scaleIn {
    from { opacity: 0; transform: scale(0.9); }
    to   { opacity: 1; transform: scale(1); }
  }
  .animate-scale-in { animation: scaleIn 0.3s ease-out; }
`;
document.head.appendChild(style);