// ============================================================
// CONFIG
// ============================================================
const ITEMS_PER_PAGE = 100;
let currentPage = 1;
let allData = [];
let currentView = "default";
let currentGradeFilter = "all";
let allAvailableStudents = [];
let currentAdvisoryGrade = null;

// ============================================================
// TOAST
// ============================================================
function showToast(message, type = "success") {
  const tc = document.getElementById("toastContainer");
  const toast = document.createElement("div");
  const bg   = type === "success" ? "bg-emerald-500" : "bg-red-500";
  const icon = type === "success" ? "fas fa-check-circle" : "fas fa-exclamation-circle";
  toast.className = `${bg} text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 pointer-events-auto transform transition-all duration-300`;
  toast.innerHTML = `<i class="${icon}"></i><span class="font-medium text-sm">${message}</span>
    <button onclick="this.parentElement.remove()" class="ml-3 hover:bg-white/20 rounded-lg p-1 transition"><i class="fas fa-times text-xs"></i></button>`;
  tc.appendChild(toast);
  setTimeout(() => { toast.style.opacity = "0"; toast.style.transform = "translateX(100%)"; setTimeout(() => toast.remove(), 300); }, 5000);
}

// ============================================================
// PAGINATION
// ============================================================
function renderPagination(totalItems) {
  const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));
  const container  = document.getElementById("paginationContainer");
  const infoEl     = document.getElementById("paginationInfo");

  const start = totalItems === 0 ? 0 : (currentPage - 1) * ITEMS_PER_PAGE + 1;
  const end   = Math.min(currentPage * ITEMS_PER_PAGE, totalItems);
  infoEl.innerHTML = totalItems === 0
    ? `<i class="fas fa-list mr-2"></i>No records`
    : `<i class="fas fa-list mr-2"></i>Showing ${start}–${end} of ${totalItems} record${totalItems !== 1 ? 's' : ''}`;

  if (totalPages >= 1 && totalItems > 0) {
    const prevDisabled = currentPage === 1;
    const nextDisabled = currentPage === totalPages;

  container.innerHTML = `
    <div class="flex items-center gap-2">
      <button onclick="changePage(${currentPage - 1})"
        ${prevDisabled ? "disabled" : ""}
        class="px-4 py-2 flex items-center gap-1 rounded-lg bg-white border-2 border-gray-300 text-sm font-bold text-gray-700
               hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm">
        <i class="fas fa-chevron-left text-xs"></i> PREV
      </button>

      <div class="flex items-center gap-1 px-4 py-2 bg-[#043915] rounded-lg">
        <span class="text-sm font-black text-white">${currentPage}</span>
        ${totalPages > 1 ? `<span class="text-xs text-green-300 mx-1">/</span><span class="text-sm font-bold text-green-200">${totalPages}</span>` : ''}
      </div>

      <button onclick="changePage(${currentPage + 1})"
        ${nextDisabled ? "disabled" : ""}
        class="px-4 py-2 flex items-center gap-1 rounded-lg bg-white border-2 border-gray-300 text-sm font-bold text-gray-700
               hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm">
        NEXT <i class="fas fa-chevron-right text-xs"></i>
      </button>
    </div>`;
  } else {
    container.innerHTML = "";
  }
}


function changePage(page) {
  const totalPages = Math.max(1, Math.ceil(allData.length / ITEMS_PER_PAGE));
  if (page < 1 || page > totalPages) return;
  currentPage = page;
  if (currentView === "advisory")      displayAdvisoryList(allData);
  else if (currentView === "subject")  displaySubjectTeachersList(allData);
  else                                 displayTableData(allData);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function getPaginatedData(data) {
  return data.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);
}

// ============================================================
// SCHOOL YEAR MODAL
// ============================================================
function openSchoolYearModal(schoolYearId, startYear, endYear) {
  document.getElementById("schoolYearId").value = schoolYearId;
  document.getElementById("startYearInput").value = startYear;
  document.getElementById("endYearInput").value = endYear;
  document.getElementById("schoolYearModal").classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function closeSchoolYearModal() {
  document.getElementById("schoolYearModal").classList.add("hidden");
  document.body.style.overflow = "auto";
  document.getElementById("schoolYearForm").reset();
}

function submitSchoolYearUpdate(event) {
  event.preventDefault();
  const fd = new FormData(event.target);
  const startYear = parseInt(fd.get("start_year"));
  const endYear = parseInt(fd.get("end_year"));
  
  if (isNaN(startYear) || isNaN(endYear)) {
    showToast("Please enter valid years.", "error");
    return;
  }
  if (endYear <= startYear) {
    showToast("End year must be greater than start year.", "error");
    return;
  }
  
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast(data.message, "success");
        closeSchoolYearModal();
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast(data.message || "Update failed.", "error");
      }
    })
    .catch(() => showToast("Error updating school year.", "error"));
}

// ============================================================
// READ-ONLY → EDIT MODE for STUDENT PROFILE
// ============================================================
function enableProfileEditMode() {
  document.querySelectorAll(".profile-field").forEach(el => {
    el.removeAttribute("readonly");
    el.disabled = false;
    el.classList.remove("bg-gray-50", "cursor-default");
    el.classList.add("bg-white", "cursor-text");
  });
  const overlay = document.getElementById("profilePicOverlay");
  if (overlay) overlay.classList.remove("hidden");

  document.getElementById("profileViewButtons").classList.add("hidden");
  document.getElementById("profileEditButtons").classList.remove("hidden");
  document.getElementById("profileEditButtons").classList.add("flex");
  document.getElementById("profileModalTitle").textContent = "Edit Student Record";
  document.getElementById("profileModalSubtitle").textContent = "Make changes, then click Save Changes.";
  document.getElementById("profileEditMode").value = "1";
}

function cancelProfileEdit() {
  const studentId = document.getElementById("profileStudentId").value;
  openStudentProfileModal(studentId);
}

// ============================================================
// READ-ONLY → EDIT MODE for TEACHER PROFILE
// ============================================================
function enableTeacherEditMode() {
  document.querySelectorAll(".teacher-field").forEach(el => {
    el.removeAttribute("readonly");
    el.disabled = false;
    el.classList.remove("bg-gray-50", "cursor-default");
    el.classList.add("bg-white", "cursor-text");
  });
  const overlay = document.getElementById("teacherPicOverlay");
  if (overlay) overlay.classList.remove("hidden");

  document.getElementById("teacherViewButtons").classList.add("hidden");
  document.getElementById("teacherEditButtons").classList.remove("hidden");
  document.getElementById("teacherEditButtons").classList.add("flex");
  document.getElementById("teacherModalTitle").textContent = "Edit Faculty Record";
  document.getElementById("teacherModalSubtitle").textContent = "Make changes, then click Save Changes.";
  document.getElementById("teacherEditMode").value = "1";
}

function cancelTeacherEdit() {
  const advisoryId = document.getElementById("teacherRecordId").dataset.advisoryId;
  closeTeacherProfileModal();
  if (advisoryId) setTimeout(() => openTeacherProfileModal(advisoryId), 100);
}

// ============================================================
// STUDENT PROFILE MODAL
// ============================================================
function openStudentProfileModal(studentId) {
  const modal = document.getElementById("studentProfileModal");
  if (!modal) return;
  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";

  const formContent  = document.getElementById("profileFormContent");
  const loadingState = document.getElementById("profileLoadingState");
  if (formContent)  formContent.classList.add("hidden");
  if (loadingState) loadingState.classList.remove("hidden");

  document.getElementById("profileStudentId").value = studentId;
  document.getElementById("profileEditMode").value  = "0";

  const formData = new FormData();
  formData.append("action", "get_student_profile");
  formData.append("student_id", studentId);

  fetch(window.location.href, { method: "POST", body: formData })
    .then(r => r.json())
    .then(data => {
      if (loadingState) loadingState.classList.add("hidden");
      if (data && data.success && data.data) {
        populateStudentProfile(data.data);
        if (formContent) formContent.classList.remove("hidden");
        setProfileReadOnly();
      } else {
        showToast(data?.message || "Failed to load student profile.", "error");
        closeStudentProfileModal();
      }
    })
    .catch(err => {
      if (loadingState) loadingState.classList.add("hidden");
      showToast("Error: " + err.message, "error");
      closeStudentProfileModal();
    });
}

function setProfileReadOnly() {
  document.querySelectorAll(".profile-field").forEach(el => {
    el.setAttribute("readonly", true);
    el.disabled = false;
    el.classList.add("bg-gray-50", "cursor-default");
    el.classList.remove("bg-white", "cursor-text");
  });
  const overlay = document.getElementById("profilePicOverlay");
  if (overlay) overlay.classList.add("hidden");

  document.getElementById("profileViewButtons").classList.remove("hidden");
  const editBtns = document.getElementById("profileEditButtons");
  editBtns.classList.add("hidden");
  editBtns.classList.remove("flex");
  document.getElementById("profileModalTitle").textContent   = "Student Profile";
  document.getElementById("profileModalSubtitle").textContent = "Viewing student information";
}

function populateStudentProfile(student) {
  const nameParts = (student.name || "").trim().split(/\s+/);
  let firstName = "", lastName = "", mi = "";
  const miIdx = nameParts.findIndex(p => p.endsWith(".") && p.length <= 3);
  if (miIdx !== -1 && miIdx > 0) {
    firstName = nameParts.slice(0, miIdx).join(" ");
    mi        = nameParts[miIdx];
    lastName  = nameParts.slice(miIdx + 1).join(" ");
  } else if (nameParts.length >= 2) {
    firstName = nameParts[0];
    lastName  = nameParts.slice(1).join(" ");
  } else {
    firstName = student.name || "";
  }

  const avatarIcon = document.getElementById("profileAvatarIcon");
  const avatarImg  = document.getElementById("profileAvatarImg");
  if (student.profile_pix && student.profile_pix !== '') {
    if (avatarImg)  { avatarImg.src = student.profile_pix; avatarImg.classList.remove("hidden"); }
    if (avatarIcon) avatarIcon.classList.add("hidden");
  } else {
    if (avatarImg)  avatarImg.classList.add("hidden");
    if (avatarIcon) avatarIcon.classList.remove("hidden");
  }

  document.getElementById("profileFirstName").value       = firstName;
  document.getElementById("profileLastName").value        = lastName;
  document.getElementById("profileMI").value              = mi;
  document.getElementById("profileLrn").value             = student.lrn         || "";
  document.getElementById("profileContact").value         = student.contact_no   || "";
  document.getElementById("profileAddress").value         = student.home_address || "";
  document.getElementById("profileGuardianName").value    = student.guardian_name    || "";
  document.getElementById("profileGuardianContact").value = student.guardian_contact || "";
  document.getElementById("originalProfilePix").value     = student.profile_pix  || "";

  document.getElementById("profileYearLevel").textContent = student.current_grade ? `Grade ${student.current_grade}` : "Not Assigned";
  document.getElementById("profileSection").textContent   = student.advisory_name || "Not Assigned";
  document.getElementById("profileAdviser").textContent   = student.teacher_name  || "—";
}

function closeStudentProfileModal() {
  document.getElementById("studentProfileModal")?.classList.add("hidden");
  document.body.style.overflow = "auto";
  const fi = document.getElementById("profilePictureInput");
  if (fi) fi.value = "";
}

function handleProfilePictureChange(event) {
  const file = event.target.files[0];
  if (!file) return;
  if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { showToast("Only JPG, PNG, WebP allowed.", "error"); event.target.value = ''; return; }
  if (file.size > 5 * 1024 * 1024) { showToast("Max 5MB.", "error"); event.target.value = ''; return; }
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById("profileAvatarImg");
    const ico = document.getElementById("profileAvatarIcon");
    img.src = e.target.result; img.classList.remove("hidden"); if (ico) ico.classList.add("hidden");
  };
  reader.readAsDataURL(file);
}

function saveStudentProfile() {
  const studentId      = document.getElementById("profileStudentId").value;
  const firstName      = document.getElementById("profileFirstName").value.trim();
  const lastName       = document.getElementById("profileLastName").value.trim();
  const mi             = document.getElementById("profileMI").value.trim();
  const lrn            = document.getElementById("profileLrn").value.trim();
  const contact        = document.getElementById("profileContact").value.trim();
  const address        = document.getElementById("profileAddress").value.trim();
  const guardianName   = document.getElementById("profileGuardianName").value.trim();
  const guardianContact= document.getElementById("profileGuardianContact").value.trim();
  const fileInput      = document.getElementById("profilePictureInput");

  if (!firstName || !lastName) { showToast("First name and last name are required.", "error"); return; }
  if (lrn && lrn.length !== 12) { showToast("LRN must be exactly 12 digits.", "error"); return; }
  if (contact && contact.length !== 11) { showToast("Contact must be 11 digits.", "error"); return; }
  if (guardianContact && guardianContact.length !== 11) { showToast("Guardian contact must be 11 digits.", "error"); return; }

  let profilePicPath = document.getElementById("originalProfilePix").value;

  const doSave = (picPath) => {
    const fd = new FormData();
    fd.append("action",           "update_student_info");
    fd.append("student_id",       studentId);
    fd.append("first_name",       firstName);
    fd.append("last_name",        lastName);
    fd.append("mi",               mi);
    fd.append("lrn",              lrn);
    fd.append("contact_no",       contact);
    fd.append("home_address",     address);
    fd.append("guardian_name",    guardianName);
    fd.append("guardian_contact", guardianContact);
    fd.append("profile_pix",      picPath);
    fetch(window.location.href, { method: "POST", body: fd })
      .then(r => r.json())
      .then(data => {
        if (data.success) { showToast(data.message, "success"); closeStudentProfileModal(); loadFilteredData(); }
        else showToast(data.message || "Update failed.", "error");
      })
      .catch(() => showToast("Error saving profile.", "error"));
  };

  if (fileInput.files.length > 0) {
    const uploadFd = new FormData();
    uploadFd.append("action", "upload_profile_pic");
    uploadFd.append("student_id", studentId);
    uploadFd.append("profile_pic", fileInput.files[0]);
    fetch(window.location.href, { method: "POST", body: uploadFd })
      .then(r => r.json())
      .then(data => { if (data.success) doSave(data.path); else showToast(data.message || "Upload failed.", "error"); })
      .catch(() => showToast("Error uploading image.", "error"));
  } else {
    doSave(profilePicPath);
  }
}

// ============================================================
// STUDENT HISTORY MODAL
// ============================================================
function openStudentHistoryModal(studentId) {
  const fd = new FormData();
  fd.append("action", "get_student_history");
  fd.append("student_id", studentId);
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => { if (data && data.success) displayStudentHistory(data.data); else showToast(data?.message || "Failed to load history.", "error"); })
    .catch(err => showToast("Error: " + err.message, "error"));
}

function displayStudentHistory(history) {
  const existing = document.getElementById("studentHistoryModal");
  if (existing) existing.remove();
  const modal = document.createElement("div");
  modal.id        = "studentHistoryModal";
  modal.className = "fixed inset-0 z-[200] bg-black/70 backdrop-blur-sm flex items-center justify-center p-4";
  modal.innerHTML = `
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">
      <div class="px-7 pt-6 pb-5 border-b border-gray-200 flex items-start justify-between shrink-0">
        <div>
          <h2 class="text-lg font-bold text-black flex items-center gap-3"><i class="fas fa-history text-indigo-600"></i>Academic History</h2>
          <p class="text-sm text-gray-500 mt-0.5">Complete record of student assignments</p>
        </div>
        <button onclick="document.getElementById('studentHistoryModal').remove()"
          class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-500 transition shrink-0">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
      <div class="flex-1 overflow-y-auto px-7 py-5">
        ${history.length === 0 ? `<div class="py-16 text-center"><i class="fas fa-inbox text-5xl text-gray-200 mb-4"></i><p class="text-sm text-gray-500">No history records found.</p></div>`
        : `<div class="space-y-3">
            ${history.map((r, i) => `
              <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">${i+1}</div>
                    <div>
                      <p class="font-bold text-black text-sm">S.Y. ${r.start_year} – ${r.end_year}</p>
                      <p class="text-xs text-gray-500">${formatDate(r.assigned_date)}</p>
                    </div>
                  </div>
                  <span class="px-3 py-1 bg-indigo-200 text-indigo-800 rounded-lg text-xs font-bold">Grade ${r.grade_level}</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="bg-white rounded-lg p-3"><p class="text-xs text-gray-400 mb-1">Section</p><p class="text-sm font-bold text-black">${escapeHtml(r.advisory_name)}</p></div>
                  <div class="bg-white rounded-lg p-3"><p class="text-xs text-gray-400 mb-1">Teacher</p><p class="text-sm font-bold text-black">${escapeHtml(r.teacher_name)}</p></div>
                </div>
              </div>`).join("")}
          </div>`}
      </div>
      <div class="px-7 py-5 border-t border-gray-200 shrink-0">
        <button onclick="document.getElementById('studentHistoryModal').remove()"
          class="w-full bg-gray-200 hover:bg-gray-300 text-black font-bold py-3 rounded-xl transition-colors text-sm">Close</button>
      </div>
    </div>`;
  document.body.appendChild(modal);
}

// ============================================================
// TEACHER PROFILE MODAL
// ============================================================
function openTeacherProfileModal(advisoryId) {
  const modal = document.getElementById("teacherProfileModal");
  if (!modal) return;
  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";

  const formContent  = document.getElementById("teacherFormContent");
  const loadingState = document.getElementById("teacherLoadingState");
  if (formContent)  formContent.classList.add("hidden");
  if (loadingState) loadingState.classList.remove("hidden");

  document.getElementById("teacherEditMode").value = "0";

  const fd = new FormData();
  fd.append("action", "get_teacher_profile");
  fd.append("advisory_id", advisoryId);

  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => {
      if (loadingState) loadingState.classList.add("hidden");
      if (data && data.success && data.data) {
        populateTeacherProfile(data.data);
        if (formContent) formContent.classList.remove("hidden");
        setTeacherReadOnly();
      } else {
        showToast(data?.message || "Failed to load teacher profile.", "error");
        closeTeacherProfileModal();
      }
    })
    .catch(err => {
      if (loadingState) loadingState.classList.add("hidden");
      showToast("Error: " + err.message, "error");
      closeTeacherProfileModal();
    });
}

function setTeacherReadOnly() {
  document.querySelectorAll(".teacher-field").forEach(el => {
    if (el.tagName === "SELECT") { el.disabled = true; }
    else { el.setAttribute("readonly", true); }
    el.classList.add("bg-gray-50", "cursor-default");
    el.classList.remove("bg-white", "cursor-text");
  });
  const overlay = document.getElementById("teacherPicOverlay");
  if (overlay) overlay.classList.add("hidden");

  document.getElementById("teacherViewButtons").classList.remove("hidden");
  const editBtns = document.getElementById("teacherEditButtons");
  editBtns.classList.add("hidden");
  editBtns.classList.remove("flex");
  document.getElementById("teacherModalTitle").textContent    = "Faculty Profile";
  document.getElementById("teacherModalSubtitle").textContent  = "Viewing teacher information";
}

function populateTeacherProfile(teacher) {
  const nameParts = (teacher.name || "").trim().split(/\s+/);
  let firstName = "", lastName = "", suffix = "";
  const suffixWords = ["LPT","PhD","MA","MS","BSc","Jr.","Sr.","II","III","IV"];
  const sufIdx = nameParts.findIndex(p => suffixWords.includes(p));
  if (sufIdx !== -1) {
    suffix    = nameParts.slice(sufIdx).join(" ");
    firstName = nameParts[0] || "";
    lastName  = nameParts.slice(1, sufIdx).join(" ");
  } else if (nameParts.length >= 2) {
    firstName = nameParts[0];
    lastName  = nameParts.slice(1).join(" ");
  } else {
    firstName = teacher.name || "";
  }

  const avatarIcon = document.getElementById("teacherAvatarIcon");
  const avatarImg  = document.getElementById("teacherAvatarImg");
  if (teacher.profile_pix && teacher.profile_pix !== '') {
    if (avatarImg)  { avatarImg.src = teacher.profile_pix; avatarImg.classList.remove("hidden"); }
    if (avatarIcon) avatarIcon.classList.add("hidden");
  } else {
    if (avatarImg)  avatarImg.classList.add("hidden");
    if (avatarIcon) avatarIcon.classList.remove("hidden");
  }

  const recId = document.getElementById("teacherRecordId");
  recId.value = teacher.user_id || "";
  recId.dataset.advisoryId = teacher.advisory_id || "";

  document.getElementById("teacherIdField").value        = teacher.teacher_no     || "";
  document.getElementById("teacherDeptField").value      = teacher.department     || "";
  document.getElementById("teacherFirstName").value      = firstName;
  document.getElementById("teacherLastName").value       = lastName;
  document.getElementById("teacherSuffix").value         = suffix;
  document.getElementById("teacherEmail").value          = teacher.email          || "";
  document.getElementById("teacherContact").value        = teacher.contact_no     || "";
  document.getElementById("teacherSpecialization").value = teacher.advisory_section || teacher.specialization || "";
  document.getElementById("originalTeacherPix").value    = teacher.profile_pix    || "";
}

function closeTeacherProfileModal() {
  document.getElementById("teacherProfileModal")?.classList.add("hidden");
  document.body.style.overflow = "auto";
  const fi = document.getElementById("teacherPictureInput");
  if (fi) fi.value = "";
}

function handleTeacherPictureChange(event) {
  const file = event.target.files[0];
  if (!file) return;
  if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { showToast("Only JPG, PNG, WebP allowed.", "error"); event.target.value = ''; return; }
  if (file.size > 5 * 1024 * 1024) { showToast("Max 5MB.", "error"); event.target.value = ''; return; }
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById("teacherAvatarImg");
    const ico = document.getElementById("teacherAvatarIcon");
    img.src = e.target.result; img.classList.remove("hidden"); if (ico) ico.classList.add("hidden");
  };
  reader.readAsDataURL(file);
}

function saveTeacherProfile() {
  const teacherId   = document.getElementById("teacherRecordId").value;
  const idField     = document.getElementById("teacherIdField").value.trim();
  const firstName   = document.getElementById("teacherFirstName").value.trim();
  const lastName    = document.getElementById("teacherLastName").value.trim();
  const suffix      = document.getElementById("teacherSuffix").value.trim();
  const email       = document.getElementById("teacherEmail").value.trim();
  const contact     = document.getElementById("teacherContact").value.trim();
  const department  = document.getElementById("teacherDeptField").value;
  const specialization = document.getElementById("teacherSpecialization").value.trim();
  const fileInput   = document.getElementById("teacherPictureInput");

  if (!firstName || !lastName) { showToast("First and Last name are required.", "error"); return; }
  if (contact && contact.length !== 11) { showToast("Contact must be 11 digits.", "error"); return; }

  const fullName = [firstName, lastName, suffix].filter(Boolean).join(" ").trim();
  const doSave = (picPath) => {
    const fd = new FormData();
    fd.append("action", "update_teacher_info");
    fd.append("teacher_id", teacherId);
    fd.append("teacher_id_field", idField);
    fd.append("name", fullName);
    fd.append("email", email);
    fd.append("contact_no", contact);
    fd.append("department", department);
    fd.append("specialization", specialization);
    fd.append("profile_pix", picPath);
    fetch(window.location.href, { method: "POST", body: fd })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showToast(data.message, "success"); closeTeacherProfileModal();
          if (currentView === "advisory") loadAdvisoryList();
          else if (currentView === "subject") loadSubjectTeachersList();
          else loadFilteredData();
        } else showToast(data.message || "Update failed.", "error");
      })
      .catch(() => showToast("Error saving profile.", "error"));
  };

  let picPath = document.getElementById("originalTeacherPix").value;
  if (fileInput.files.length > 0) {
    const upFd = new FormData();
    upFd.append("action", "upload_teacher_profile_pic");
    upFd.append("teacher_id", teacherId);
    upFd.append("profile_pic", fileInput.files[0]);
    fetch(window.location.href, { method: "POST", body: upFd })
      .then(r => r.json())
      .then(data => { if (data.success) doSave(data.path); else showToast(data.message || "Upload failed.", "error"); })
      .catch(() => showToast("Error uploading image.", "error"));
  } else {
    doSave(picPath);
  }
}

// ============================================================
// TABLE RENDERING
// ============================================================
function displayTableData(data) {
  const tbody  = document.getElementById("tableBody");
  const countEl = document.getElementById("resultCount");

  document.getElementById("mainTableHead").innerHTML = `
    <tr class="bg-[#043915] text-white" id="defaultHead">
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap w-1/6"><i class="fas fa-graduation-cap mr-2"></i>Student</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap w-1/8"><i class="fas fa-barcode mr-2"></i>LRN</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap w-1/10"><i class="fas fa-book mr-2"></i>Grade</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap w-1/6"><i class="fas fa-person-chalkboard mr-2"></i>Teacher</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap w-1/8"><i class="fas fa-door-open mr-2"></i>Class</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap w-1/8"><i class="fas fa-clock mr-2"></i>Date</th>
      <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap w-1/6"><i class="fas fa-cogs mr-2"></i>Actions</th>
    </tr>`;

  countEl.innerHTML = data.length === 0
    ? '<i class="fas fa-database text-gray-400 mr-2"></i>No records found'
    : `<i class="fas fa-database text-gray-400 mr-2"></i>Showing ${data.length} student assignment${data.length !== 1 ? 's' : ''}`;

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-20 text-center">
      <i class="fas fa-inbox text-5xl text-gray-300 mb-4 block"></i>
      <p class="text-base font-semibold text-gray-600">No Assignments Found</p>
      <p class="text-sm text-gray-400 mt-1">Assign students to advisory classes to see them here</p>
    </td></tr>`;
    renderPagination(0); return;
  }

  const pagData = getPaginatedData(data);
  const gradeColors = { 7:"bg-blue-100 text-blue-800", 8:"bg-orange-100 text-orange-800", 9:"bg-purple-100 text-purple-800", 10:"bg-green-100 text-green-800" };

  tbody.innerHTML = pagData.map(row => {
    const gc = gradeColors[row.grade_level] || "bg-gray-100 text-gray-800";
    return `
      <tr class="hover:bg-gray-50 transition border-b border-gray-100">
        <td class="py-3 px-6">
          <div class="text-sm font-semibold text-gray-900">${escapeHtml(row.student_name)}</div>
        </td>
        <td class="py-3 px-6 text-sm text-gray-600 font-mono">${escapeHtml(row.lrn || "—")}</td>
        <td class="py-3 px-6">
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ${gc}">Grade ${row.grade_level}</span>
        </td>
        <td class="py-3 px-6 text-sm text-gray-900">${escapeHtml(row.teacher_name)}</td>
        <td class="py-3 px-6">
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
            <i class="fas fa-door-open mr-1 text-xs"></i>${escapeHtml(row.advisory_name)}
          </span>
        </td>
        <td class="py-3 px-6 text-xs text-gray-500">${formatDate(row.assigned_date)}</td>
        <td class="py-3 px-6">
          <div class="flex items-center justify-center gap-1.5 flex-wrap">
            <button onclick="openReassignModal(${row.assignment_id}, '${escapeJs(row.student_name)}', ${row.grade_level})"
              class="px-2.5 py-1.5 bg-blue-500 text-white rounded-lg text-xs font-bold hover:bg-blue-600 transition" title="Reassign">
              <i class="fas fa-exchange-alt"></i>
            </button>
            <button onclick="openRemoveAdvisoryModal(${row.assignment_id}, '${escapeJs(row.student_name)}')"
              class="px-2.5 py-1.5 bg-red-500 text-white rounded-lg text-xs font-bold hover:bg-red-600 transition" title="Remove">
              <i class="fas fa-trash"></i>
            </button>
            <button onclick="openStudentProfileModal(${row.student_id})"
              class="px-2.5 py-1.5 bg-[#043915] text-white rounded-lg text-xs font-bold hover:bg-[#055020] transition" title="Profile">
              <i class="fas fa-user"></i>
            </button>
          </div>
        </td>
      </tr>`;
  }).join("");

  renderPagination(data.length);
}

// ============================================================
// ADVISORY LIST
// ============================================================
function displayAdvisoryList(advisories) {
  const tbody  = document.getElementById("tableBody");
  const countEl = document.getElementById("resultCount");

  document.getElementById("mainTableHead").innerHTML = `
    <tr class="bg-[#043915] text-white">
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-door-open mr-2"></i>Advisory Class</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-user-tie mr-2"></i>Teacher</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-layer-group mr-2"></i>Grade</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-users mr-2"></i>Capacity</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-calendar mr-2"></i>Created</th>
      <th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap" colspan="2"><i class="fas fa-cogs mr-2"></i>Actions</th>
    </tr>`;

  countEl.innerHTML = advisories.length === 0
    ? '<i class="fas fa-database text-gray-400 mr-2"></i>No advisory classes found'
    : `<i class="fas fa-database text-gray-400 mr-2"></i>Showing ${advisories.length} advisory class${advisories.length !== 1 ? 'es' : ''}`;

  if (advisories.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-20 text-center">
      <i class="fas fa-inbox text-5xl text-gray-300 mb-4 block"></i>
      <p class="text-base font-semibold text-gray-600">No Advisory Classes</p>
    </td></tr>`;
    renderPagination(0); return;
  }

  const pagData = getPaginatedData(advisories);
  const gradeColors = { 7:"bg-blue-100 text-blue-800", 8:"bg-orange-100 text-orange-800", 9:"bg-purple-100 text-purple-800", 10:"bg-green-100 text-green-800" };

  tbody.innerHTML = pagData.map(adv => {
    const gc = gradeColors[adv.grade_level] || "bg-gray-100 text-gray-800";
    const cc = adv.student_count >= 40 ? "bg-red-100 text-red-800" : adv.student_count >= 35 ? "bg-orange-100 text-orange-800" : "bg-emerald-100 text-emerald-800";
    return `
      <tr class="hover:bg-gray-50 transition border-b border-gray-100">
        <td class="py-3 px-6">
          <div class="text-sm font-semibold text-gray-900"><i class="fas fa-door-open mr-1.5 text-blue-500 text-xs"></i>${escapeHtml(adv.advisory_name)}</div>
        </td>
        <td class="py-3 px-6 text-sm text-gray-700">${escapeHtml(adv.teacher_name)}</td>
        <td class="py-3 px-6">
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ${gc}">Grade ${adv.grade_level}</span>
        </td>
        <td class="py-3 px-6">
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold ${cc}">
            <i class="fas fa-users text-xs"></i>${adv.student_count}/40
          </span>
        </td>
        <td class="py-3 px-6 text-xs text-gray-400">${formatDate(adv.created_at)}</td>
        <td class="py-3 px-3 text-center">
          <button onclick="viewAdvisoryDetails(${adv.advisory_id}, '${adv.grade_level}')"
            class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-xs font-bold hover:bg-blue-600 transition flex items-center gap-1 mx-auto">
            <i class="fas fa-eye"></i> View
          </button>
        </td>
        <td class="py-3 px-3 text-center">
          <button onclick="openTeacherProfileModal(${adv.advisory_id})"
            class="px-3 py-1.5 bg-purple-500 text-white rounded-lg text-xs font-bold hover:bg-purple-600 transition flex items-center gap-1 mx-auto">
            <i class="fas fa-user-edit"></i>
          </button>
        </td>
      </tr>`;
  }).join("");

  renderPagination(advisories.length);
}

// ============================================================
// SUBJECT TEACHERS LIST
// ============================================================
function displaySubjectTeachersList(teachers) {
  const tbody  = document.getElementById("tableBody");
  const countEl = document.getElementById("resultCount");

  document.getElementById("mainTableHead").innerHTML = `
    <tr class="bg-[#043915] text-white">
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-user-tie mr-2"></i>Teacher Name</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-envelope mr-2"></i>Email</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap"><i class="fas fa-briefcase mr-2"></i>Role</th>
      <th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap" colspan="4"><i class="fas fa-calendar mr-2"></i>Date Assigned</th>
    </tr>`;

  countEl.innerHTML = teachers.length === 0
    ? '<i class="fas fa-database text-gray-400 mr-2"></i>No subject teachers found'
    : `<i class="fas fa-database text-gray-400 mr-2"></i>Showing ${teachers.length} subject teacher${teachers.length !== 1 ? 's' : ''}`;

  if (teachers.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-20 text-center">
      <i class="fas fa-inbox text-5xl text-gray-300 mb-4 block"></i>
      <p class="text-base font-semibold text-gray-600">No Subject Teachers</p>
    </td></tr>`;
    renderPagination(0); return;
  }

  const pagData = getPaginatedData(teachers);
  tbody.innerHTML = pagData.map(t => `
    <tr class="hover:bg-gray-50 transition border-b border-gray-100">
      <td class="py-3 px-6">
        <div class="text-sm font-semibold text-gray-900">${escapeHtml(t.teacher_name)}</div>
      </td>
      <td class="py-3 px-6 text-sm text-gray-500">${escapeHtml(t.teacher_email || "—")}</td>
      <td class="py-3 px-6">
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
          <i class="fas fa-book-open mr-1 text-xs"></i>Subject Teacher
        </span>
      </td>
      <td class="py-3 px-6 text-xs text-gray-400" colspan="4">${formatDate(t.assigned_at)}</td>
    </tr>`).join("");

  renderPagination(teachers.length);
}

// ============================================================
// FILTERS
// ============================================================
function applyFilters() {
  currentPage = 1;
  const role = document.getElementById("filterTeacherRole").value;
  if (role === "advisory")     { currentView = "advisory"; loadAdvisoryList(); }
  else if (role === "subject") { currentView = "subject";  loadSubjectTeachersList(); }
  else                         { currentView = "default";  loadFilteredData(); }
  updateTableTitle();
}

function updateTableTitle() {
  const role = document.getElementById("filterTeacherRole").value;
  const el   = document.getElementById("pageTitle");
  if (role === "advisory")    el.textContent = "Advisory Classes";
  else if (role === "subject") el.textContent = "Subject Teachers";
  else                         el.textContent = "Advisory Class Management";
}

function loadFilteredData() {
  const fd = new FormData();
  fd.append("action",       "get_filtered_data");
  fd.append("teacher_role", document.getElementById("filterTeacherRole").value);
  fd.append("grade_level",  document.getElementById("filterGrade").value);
  fd.append("date_filter",  document.getElementById("filterDate").value);
  fd.append("search",       document.getElementById("searchInput").value);
  fd.append("sort_by",      "student_name");
  fd.append("sort_order",   document.getElementById("sortName").value);
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => { if (data.success) { allData = data.data; displayTableData(data.data); } })
    .catch(e => console.error("Error:", e));
}

function loadAdvisoryList() {
  const search = document.getElementById("searchInput").value;
  const fd = new FormData();
  fd.append("action",     "get_advisory_list");
  fd.append("sort_by",    "advisory_name");
  fd.append("sort_order", document.getElementById("sortName").value);
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        let filtered = data.data;
        if (search.trim()) {
          filtered = filtered.filter(a =>
            a.advisory_name.toLowerCase().includes(search.toLowerCase()) ||
            a.teacher_name.toLowerCase().includes(search.toLowerCase()) ||
            String(a.grade_level).includes(search)
          );
        }
        allData = filtered;
        displayAdvisoryList(filtered);
      }
    })
    .catch(e => console.error("Error:", e));
}

function loadSubjectTeachersList() {
  const search = document.getElementById("searchInput").value;
  const fd = new FormData();
  fd.append("action", "get_subject_teachers");
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        let filtered = data.data;
        if (search.trim()) {
          filtered = filtered.filter(t =>
            t.teacher_name.toLowerCase().includes(search.toLowerCase()) ||
            (t.teacher_email || "").toLowerCase().includes(search.toLowerCase())
          );
        }
        allData = filtered;
        displaySubjectTeachersList(filtered);
      }
    })
    .catch(e => console.error("Error:", e));
}

function resetFilters() {
  document.getElementById("filterTeacherRole").value = "";
  document.getElementById("filterGrade").value       = "";
  document.getElementById("sortName").value          = "ASC";
  document.getElementById("filterDate").value        = "";
  document.getElementById("searchInput").value       = "";
  currentPage = 1; currentView = "default";
  loadFilteredData(); updateTableTitle();
  showToast("Filters cleared.", "success");
}

// ============================================================
// TEACHER ASSIGN MODAL
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
  const role = document.getElementById("teacherRoleType").value;
  const af   = document.getElementById("advisoryFields");
  const nm   = document.getElementById("advisoryNameInput");
  const gr   = document.getElementById("advisoryGradeLevelInput");
  if (role === "advisory") { af.classList.remove("hidden"); nm.required = true; gr.required = true; }
  else { af.classList.add("hidden"); nm.required = false; gr.required = false; }
}
function submitTeacherAssignment(event) {
  event.preventDefault();
  const fd = new FormData(event.target);
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) { showToast(data.message, "success"); closeTeacherModal(); setTimeout(() => location.reload(), 1500); }
      else showToast(data.message, "error");
    })
    .catch(() => showToast("Error occurred.", "error"));
}

// ============================================================
// STUDENT ASSIGN MODAL
// ============================================================
function openStudentModal() {
  const sel = document.getElementById("modalAdvisoryTeacher");
  if (sel.options.length <= 1) { showToast("Please assign an advisory teacher first.", "error"); return; }
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
  ["all","7","8","9","10","11","12"].forEach(g => {
    const t = document.getElementById(`gradeTab_${g}`);
    if (t) t.classList.remove("ring-2","ring-[#043915]","font-black");
  });
  document.getElementById("gradeTab_all")?.classList.add("ring-2","ring-[#043915]","font-black");
}
function loadAllAvailableStudents() {
  document.getElementById("modalStudentTable").innerHTML = `<tr><td colspan="5" class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2 block"></i><span class="text-sm text-gray-500">Loading students…</span></td></tr>`;
  const fd = new FormData(); fd.append("action","get_unassigned_students");
  fetch(window.location.href, { method:"POST", body:fd })
    .then(r => r.json())
    .then(data => { if (data.success) { allAvailableStudents = data.data; displayFilteredStudents(); } else document.getElementById("modalStudentTable").innerHTML = `<tr><td colspan="5" class="py-10 text-center text-sm text-red-500">Error loading students.</td></tr>`; })
    .catch(() => { document.getElementById("modalStudentTable").innerHTML = `<tr><td colspan="5" class="py-10 text-center text-sm text-red-500">Error loading students.</td></tr>`; });
}
function filterModalStudents(grade) {
  currentGradeFilter = grade;
  ["all","7","8","9","10","11","12"].forEach(g => {
    const t = document.getElementById(`gradeTab_${g}`);
    if (t) { t.classList.toggle("ring-2", g === grade); t.classList.toggle("ring-[#043915]", g === grade); t.classList.toggle("font-black", g === grade); }
  });
  displayFilteredStudents();
}
function displayFilteredStudents() {
  const tbody    = document.getElementById("modalStudentTable");
  const sel      = document.getElementById("modalAdvisoryTeacher");
  const selOpt   = sel.value ? sel.selectedOptions[0] : null;
  const advGrade = selOpt ? selOpt.getAttribute("data-grade-level") : null;

  let filtered = allAvailableStudents;
  if (advGrade) filtered = filtered.filter(s => s.grade_level === advGrade);
  else if (currentGradeFilter !== "all") filtered = filtered.filter(s => s.grade_level === currentGradeFilter);

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" class="py-12 text-center"><i class="fas fa-inbox text-4xl text-gray-300 mb-3 block"></i><p class="text-sm text-gray-500">No available students${advGrade ? ' for Grade '+advGrade : ''}.</p></td></tr>`;
    return;
  }
  const gc = { "7":"bg-blue-100 text-blue-800","8":"bg-orange-100 text-orange-800","9":"bg-purple-100 text-purple-800","10":"bg-green-100 text-green-800" };
  tbody.innerHTML = filtered.map(s => {
    let opts = ""; for (let g = 7; g <= 12; g++) opts += `<option value="${g}" ${String(g) === String(s.grade_level) ? "selected" : ""}>Grade ${g}</option>`;
    return `
      <tr class="hover:bg-gray-50 transition" data-grade="${s.grade_level}" data-student-id="${s.user_id}">
        <td class="py-3 px-4"><input type="checkbox" name="student_ids[]" value="${s.user_id}" class="student-checkbox w-4 h-4 text-[#043915] border-gray-300 rounded focus:ring-[#043915]"><input type="hidden" name="grade_levels[${s.user_id}]" id="gradeHidden_${s.user_id}" value="${s.grade_level}"></td>
        <td class="py-3 px-4"><p class="text-sm font-semibold text-gray-900">${escapeHtml(s.name)}</p></td>
        <td class="py-3 px-4 text-sm text-gray-600 font-mono">${escapeHtml(s.lrn || "—")}</td>
        <td class="py-3 px-4"><span class="px-2 py-1 ${gc[s.grade_level] || 'bg-gray-100 text-gray-800'} rounded-lg text-xs font-bold">Grade ${s.grade_level}</span></td>
        <td class="py-3 px-4"><select onchange="updateStudentGradeInModal(this,${s.user_id})" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs bg-white text-gray-700 focus:ring-2 focus:ring-[#043915]">${opts}</select></td>
      </tr>`;
  }).join("");
}
function updateStudentGradeInModal(sel, studentId) {
  const grade = sel.value;
  const hi = document.getElementById(`gradeHidden_${studentId}`);
  if (hi) hi.value = grade;
}
function toggleAllVisibleStudents(checked) {
  document.querySelectorAll(".student-checkbox").forEach(cb => { const r = cb.closest("tr"); if (r && r.style.display !== "none") cb.checked = checked; });
}
function confirmStudentAssignment() {
  const sel      = document.getElementById("modalAdvisoryTeacher");
  const advId    = sel.value;
  const selected = document.querySelectorAll(".student-checkbox:checked");
  if (!advId) { showToast("Select an advisory teacher.", "error"); return; }
  if (selected.length === 0) { showToast("Select at least one student.", "error"); return; }
  const opt    = sel.selectedOptions[0];
  const count  = parseInt(opt.getAttribute("data-current-count")) || 0;
  if (count + selected.length > 40) { showToast(`Only ${40 - count} slots available.`, "error"); return; }
  document.getElementById("hiddenAdvisoryId").value = advId;
  const fd = new FormData(document.getElementById("assignStudentsForm"));
  fetch(window.location.href, { method:"POST", body:fd })
    .then(r=>r.json())
    .then(data => { if (data.success) { showToast(data.message,"success"); closeStudentModal(); setTimeout(()=>location.reload(),1500); } else showToast(data.message,"error"); })
    .catch(()=>showToast("Error.","error"));
}
document.getElementById("modalAdvisoryTeacher")?.addEventListener("change", function() {
  const opt = this.selectedOptions[0];
  const ci  = document.getElementById("advisoryCapacityInfo");
  if (!this.value) { ci.classList.add("hidden"); displayFilteredStudents(); return; }
  const cnt = parseInt(opt.getAttribute("data-current-count")) || 0;
  const gr  = opt.getAttribute("data-grade-level");
  document.getElementById("currentStudentCount").textContent = cnt;
  document.getElementById("remainingSlots").textContent      = 40 - cnt;
  document.getElementById("advisoryGradeLevel").textContent  = gr;
  ci.classList.remove("hidden");
  displayFilteredStudents();
});

// ============================================================
// REASSIGN MODAL
// ============================================================
function openReassignModal(assignmentId, studentName, currentGrade) {
  document.getElementById("reassignAssignmentId").value = assignmentId;
  document.getElementById("reassignStudentName").textContent = studentName;
  document.getElementById("reassignCurrentGrade").value = currentGrade;
  const sel = document.getElementById("reassignAdvisorySelect");
  Array.from(sel.options).forEach(o => {
    if (o.value) { const og = o.getAttribute("data-grade"); o.disabled = og !== String(currentGrade); if (o.disabled) o.classList.add("text-gray-400"); else o.classList.remove("text-gray-400"); }
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
  const fd  = new FormData(event.target);
  const sel = document.getElementById("reassignAdvisorySelect").selectedOptions[0];
  const ag  = sel?.getAttribute("data-grade");
  const cg  = fd.get("current_grade");
  if (ag !== String(cg)) { showToast("Grade mismatch. Cannot reassign.", "error"); return; }
  fetch(window.location.href, { method:"POST", body:fd })
    .then(r=>r.json())
    .then(data => { if (data.success) { showToast(data.message,"success"); closeReassignModal(); loadFilteredData(); } else showToast(data.message,"error"); })
    .catch(()=>showToast("Error.","error"));
}

// ============================================================
// REMOVE MODAL
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
  fetch(window.location.href, { method:"POST", body:new FormData(event.target) })
    .then(r=>r.json())
    .then(data => { if (data.success) { showToast(data.message,"success"); closeRemoveAdvisoryModal(); loadFilteredData(); } else showToast(data.message,"error"); })
    .catch(()=>showToast("Error.","error"));
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
  fetch(window.location.href, { method:"POST", body:new FormData(event.target) })
    .then(r=>r.json())
    .then(data => { if (data.success) { showToast(data.message,"success"); closeConvertTeacherModal(); setTimeout(()=>location.reload(),1500); } else showToast(data.message,"error"); })
    .catch(()=>showToast("Error.","error"));
}

// ============================================================
// VIEW ADVISORY DETAILS
// ============================================================
function viewAdvisoryDetails(advisoryId, advisoryGrade) {
  currentAdvisoryGrade = advisoryGrade;
  const fd = new FormData(); fd.append("action","get_advisory_students"); fd.append("advisory_id",advisoryId);
  fetch(window.location.href, { method:"POST", body:fd })
    .then(r=>r.json())
    .then(data => { if (data.success) displayAdvisoryStudents(data.data, advisoryId, advisoryGrade); })
    .catch(e=>console.error("Error:",e));
}

function displayAdvisoryStudents(students, advisoryId, advisoryGrade) {
  const modal   = document.getElementById("viewAdvisoryModal");
  const list    = document.getElementById("advisoryStudentsList");
  const promCtrl= document.getElementById("gradePromotionControls");

  document.getElementById("advisoryDetailTitle").innerHTML    = `<i class="fas fa-users text-blue-600 mr-2"></i>Advisory Students`;
  document.getElementById("advisoryDetailSubtitle").textContent= `${students.length}/40 Students · Grade ${advisoryGrade}`;

  if (students.length > 0 && promCtrl) { promCtrl.classList.remove("hidden"); setupGradePromotionDropdown(advisoryGrade); }
  else if (promCtrl) promCtrl.classList.add("hidden");

  if (students.length === 0) {
    list.innerHTML = `<div class="py-16 text-center"><i class="fas fa-inbox text-5xl text-gray-200 mb-4"></i><p class="text-sm text-gray-500">No students assigned yet.</p></div>`;
  } else {
    const gc = { 7:"bg-blue-100 text-blue-800",8:"bg-orange-100 text-orange-800",9:"bg-purple-100 text-purple-800",10:"bg-green-100 text-green-800" };
    list.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      ${students.map(s => `
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 hover:shadow-sm transition">
          <div class="flex items-start gap-3 mb-2">
            <input type="checkbox" class="student-promotion-checkbox mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded"
              data-assignment-id="${s.assignment_id}" data-student-id="${s.student_id}"
              data-student-name="${escapeHtml(s.student_name)}" data-current-grade="${s.grade_level}"
              onchange="updateSelectedCount()">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-900 truncate">${escapeHtml(s.student_name)}</p>
              <p class="text-xs text-gray-400 font-mono">${escapeHtml(s.lrn)}</p>
            </div>
            <span class="px-2 py-0.5 rounded-lg text-xs font-bold ${gc[s.grade_level]||'bg-gray-100 text-gray-800'} shrink-0">Gr.${s.grade_level}</span>
          </div>
          <p class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i>${formatDate(s.assigned_date)}</p>
        </div>`).join("")}
    </div>`;
  }

  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function setupGradePromotionDropdown(currentGrade) {
  const dd  = document.getElementById("bulkGradeSelect");
  const grN = parseInt(currentGrade);
  dd.innerHTML = '<option value="">Select Grade Level</option>';
  if (grN >= 10) {
    document.getElementById("gradePromotionControls").innerHTML = `
      <div class="bg-gray-100 rounded-xl p-4 text-center border border-gray-200">
        <i class="fas fa-info-circle text-gray-500 text-lg mb-2 block"></i>
        <p class="text-sm font-bold text-gray-600">Grade 10 — highest level. No promotion available.</p>
      </div>`;
    return;
  }
  const next = document.createElement("option");
  next.value = grN + 1; next.textContent = `Grade ${grN + 1}`;
  dd.appendChild(next); dd.disabled = false;
}
function toggleAllStudentsPromotion(checked) {
  document.querySelectorAll(".student-promotion-checkbox").forEach(cb => cb.checked = checked);
  updateSelectedCount();
}
function updateSelectedCount() {
  const sel = document.querySelectorAll(".student-promotion-checkbox:checked");
  const cnt = sel.length;
  document.getElementById("selectedCount").textContent = `${cnt} student${cnt !== 1 ? 's' : ''} selected`;
  const all = document.querySelectorAll(".student-promotion-checkbox");
  const sa  = document.getElementById("selectAllStudentsPromotion");
  if (sa && all.length > 0) sa.checked = cnt === all.length;
}
function confirmBulkGradePromotion() {
  const selected  = document.querySelectorAll(".student-promotion-checkbox:checked");
  const newGrade  = document.getElementById("bulkGradeSelect").value;
  if (!selected.length) { showToast("Select at least one student.", "error"); return; }
  if (!newGrade) { showToast("Select a grade level.", "error"); return; }
  const curGrade = selected[0].getAttribute("data-current-grade");
  const allSame  = Array.from(selected).every(cb => cb.getAttribute("data-current-grade") === curGrade);
  if (!allSame) { showToast("All selected students must be in the same grade.", "error"); return; }
  if (parseInt(newGrade) <= parseInt(curGrade)) { showToast("Can only promote to higher grade.", "error"); return; }
  showPromotionConfirmation(selected, curGrade, newGrade);
}
function showPromotionConfirmation(selected, curGrade, newGrade) {
  const existing = document.getElementById("promotionConfirmModal");
  if (existing) existing.remove();
  const names   = Array.from(selected).slice(0,5).map(cb => cb.getAttribute("data-student-name"));
  const cnt     = selected.length;
  const modal   = document.createElement("div");
  modal.id      = "promotionConfirmModal";
  modal.className = "fixed inset-0 bg-black/60 flex items-center justify-center z-[200] backdrop-blur-sm p-4";
  modal.innerHTML = `
    <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-arrow-up text-blue-600 text-xl"></i></div>
        <h2 class="text-lg font-bold text-gray-900">Confirm Grade Promotion</h2>
        <p class="text-sm text-gray-600 mt-1">Promote ${cnt} student${cnt>1?'s':''} from Grade ${curGrade} → Grade ${newGrade}</p>
      </div>
      <div class="bg-blue-50 rounded-xl p-4 mb-5 max-h-40 overflow-y-auto">
        <ul class="space-y-1 text-sm text-blue-800">
          ${names.map(n=>`<li class="flex items-center gap-2"><i class="fas fa-check text-xs text-blue-500"></i>${n}</li>`).join("")}
          ${cnt>5?`<li class="text-xs text-blue-600 font-bold mt-1">+${cnt-5} more students</li>`:''}
        </ul>
      </div>
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-5 flex gap-3">
        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 shrink-0"></i>
        <p class="text-xs text-amber-800">Students will be removed from current class and made available for Grade ${newGrade} assignment.</p>
      </div>
      <div class="flex gap-3">
        <button onclick="closePromotionConfirm()" class="flex-1 px-4 py-3 border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-50 transition">Cancel</button>
        <button onclick="executePromotion()" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition">Promote</button>
      </div>
    </div>`;
  document.body.appendChild(modal);
}
function closePromotionConfirm() { document.getElementById("promotionConfirmModal")?.remove(); }
function executePromotion() {
  const selected = document.querySelectorAll(".student-promotion-checkbox:checked");
  const newGrade = document.getElementById("bulkGradeSelect").value;
  const ids      = Array.from(selected).map(cb => cb.getAttribute("data-assignment-id"));
  closePromotionConfirm();
  const fd = new FormData(); fd.append("action","bulk_update_student_grade"); ids.forEach(id=>fd.append("assignment_ids[]",id)); fd.append("new_grade",newGrade);
  fetch(window.location.href, { method:"POST", body:fd })
    .then(r=>r.json())
    .then(data => {
      closeViewAdvisoryModal();
      const m = document.createElement("div");
      m.className = "fixed inset-0 bg-slate-900/60 flex items-center justify-center z-[300] backdrop-blur-md p-4";
      m.innerHTML = `<div class="bg-white w-full max-w-sm rounded-3xl p-10 shadow-2xl text-center">
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-5"><i class="fas fa-check-circle text-emerald-600 text-2xl"></i></div>
        <h2 class="text-xl font-black text-slate-800 mb-2">${data.success ? 'Promotion Successful' : 'Partial Success'}</h2>
        <p class="text-sm text-slate-500">${data.message}</p>
        <p class="text-xs text-gray-400 mt-3">Refreshing in 3 seconds…</p>
      </div>`;
      document.body.appendChild(m);
      setTimeout(() => window.location.reload(), 3000);
    })
    .catch(() => window.location.reload());
}
function closeViewAdvisoryModal() {
  const m = document.getElementById("viewAdvisoryModal");
  m.classList.add("hidden"); m.classList.remove("flex");
  const sa = document.getElementById("selectAllStudentsPromotion");
  if (sa) sa.checked = false;
  const bg = document.getElementById("bulkGradeSelect");
  if (bg) bg.value = "";
  currentAdvisoryGrade = null;
}

// ============================================================
// UTILITIES
// ============================================================
function escapeHtml(text) {
  const d = document.createElement("div"); d.textContent = String(text ?? ""); return d.innerHTML;
}
function escapeJs(text) {
  return String(text ?? "").replace(/\\/g,"\\\\").replace(/'/g,"\\'").replace(/"/g,'\\"');
}
function formatDate(ds) {
  if (!ds) return "—";
  const d = new Date(ds);
  if (isNaN(d)) return ds;
  return d.toLocaleDateString("en-US", { year:"numeric", month:"short", day:"numeric" });
}

// ============================================================
// INIT
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
  checkAdvisoryAvailability();
  loadFilteredData();

  document.getElementById("filterTeacherRole")?.addEventListener("change", () => { currentPage = 1; applyFilters(); });
  ["filterGrade","sortName","filterDate"].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => { currentPage = 1; applyFilters(); });
  });
  let searchTimer;
  document.getElementById("searchInput")?.addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { currentPage = 1; applyFilters(); }, 350);
  });
});

function checkAdvisoryAvailability() {
  const sel = document.getElementById("modalAdvisoryTeacher");
  const has = sel && sel.options.length > 1;
  document.getElementById("assignStudentBtn")?.classList.toggle("hidden", !has);
  document.getElementById("noAdvisoryMessage")?.classList.toggle("hidden", has);
}