// CONFIG
const ITEMS_PER_PAGE = 100;
let currentPage = 1;
let allData = [];
let currentView = "default";
let currentGradeFilter = "all";
let allAvailableStudents = [];
let currentAdvisoryGrade = null;
let currentStudentHistoryData = null;
let currentStudentProfileData = null;

// TOAST
function showToast(message, type = "success") {
  const tc = document.getElementById("toastContainer");
  const toast = document.createElement("div");
  const bg = type === "success" ? "bg-green-500" : "bg-red-500";
  const icon = type === "success" ? "fas fa-check-circle" : "fas fa-exclamation-circle";
  toast.className = `${bg} text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 pointer-events-auto transform transition-all duration-300`;
  toast.innerHTML = `<i class="${icon}"></i><span class="font-medium text-sm">${message}</span><button onclick="this.parentElement.remove()" class="ml-2 hover:bg-white/20 rounded p-1 transition"><i class="fas fa-times text-xs"></i></button>`;
  tc.appendChild(toast);
  setTimeout(() => { toast.style.opacity = "0"; toast.style.transform = "translateX(100%)"; setTimeout(() => toast.remove(), 300); }, 5000);
}

// PAGINATION
function renderPagination(totalItems) {
  const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));
  const container = document.getElementById("paginationContainer");
  const infoEl = document.getElementById("paginationInfo");
  const start = totalItems === 0 ? 0 : (currentPage - 1) * ITEMS_PER_PAGE + 1;
  const end = Math.min(currentPage * ITEMS_PER_PAGE, totalItems);
  infoEl.innerHTML = totalItems === 0 ? `No records` : `Showing ${start} to ${end} of ${totalItems}`;
  if (totalPages >= 1 && totalItems > 0) {
    const prevDisabled = currentPage === 1;
    const nextDisabled = currentPage === totalPages;
    container.innerHTML = `<div class="flex items-center gap-2"><button onclick="changePage(${currentPage - 1})" ${prevDisabled ? "disabled" : ""} class="px-4 py-2 flex items-center gap-1 rounded-lg bg-white border border-gray-300 text-sm font-bold text-[#043915] hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition">PREV</button><div class="flex items-center gap-1 px-4 py-2 bg-[#043915] rounded-lg"><span class="text-sm font-black text-white">${currentPage}</span>${totalPages > 1 ? `<span class="text-xs text-gray-300 mx-1">/</span><span class="text-sm font-bold text-gray-300">${totalPages}</span>` : ''}</div><button onclick="changePage(${currentPage + 1})" ${nextDisabled ? "disabled" : ""} class="px-4 py-2 flex items-center gap-1 rounded-lg bg-white border border-gray-300 text-sm font-bold text-[#043915] hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition">NEXT</button></div>`;
  } else {
    container.innerHTML = "";
  }
}

function changePage(page) {
  const totalPages = Math.max(1, Math.ceil(allData.length / ITEMS_PER_PAGE));
  if (page < 1 || page > totalPages) return;
  currentPage = page;
  if (currentView === "advisory") displayAdvisoryList(allData);
  else if (currentView === "subject") displaySubjectTeachersList(allData);
  else displayTableData(allData);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function getPaginatedData(data) {
  return data.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);
}

// SCHOOL YEAR MODAL
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
  if (isNaN(startYear) || isNaN(endYear)) { showToast("Please enter valid years.", "error"); return; }
  if (endYear <= startYear) { showToast("End year must be greater than start year.", "error"); return; }
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) { showToast(data.message, "success"); closeSchoolYearModal(); setTimeout(() => location.reload(), 1500); }
      else showToast(data.message || "Update failed.", "error");
    })
    .catch(() => showToast("Error updating school year.", "error"));
}

// STUDENT PROFILE MODAL
function openStudentProfileModal(studentId) {
  const modal = document.getElementById("studentProfileModal");
  if (!modal) return;
  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
  const formContent = document.getElementById("profileFormContent");
  const loadingState = document.getElementById("profileLoadingState");
  if (formContent) formContent.classList.add("hidden");
  if (loadingState) loadingState.classList.remove("hidden");
  document.getElementById("profileStudentId").value = studentId;
  document.getElementById("profileEditMode").value = "0";
  const formData = new FormData();
  formData.append("action", "get_student_profile");
  formData.append("student_id", studentId);
  fetch(window.location.href, { method: "POST", body: formData })
    .then(r => r.json())
    .then(data => {
      if (loadingState) loadingState.classList.add("hidden");
      if (data && data.success && data.data) {
        currentStudentProfileData = data.data;
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
    el.classList.add("bg-gray-50");
    el.classList.remove("bg-white");
  });
  const overlay = document.getElementById("profilePicOverlay");
  if (overlay) overlay.classList.add("hidden");
  document.getElementById("profileViewButtons").classList.remove("hidden");
  const editBtns = document.getElementById("profileEditButtons");
  editBtns.classList.add("hidden");
  editBtns.classList.remove("flex");
  document.getElementById("profileModalTitle").textContent = "Student Profile";
  document.getElementById("profileModalSubtitle").textContent = "View detailed information";
}

function populateStudentProfile(student) {
  const nameParts = (student.name || "").trim().split(/\s+/);
  let firstName = "", lastName = "", mi = "";
  const miIdx = nameParts.findIndex(p => p.endsWith(".") && p.length <= 3);
  if (miIdx !== -1 && miIdx > 0) {
    firstName = nameParts.slice(0, miIdx).join(" ");
    mi = nameParts[miIdx];
    lastName = nameParts.slice(miIdx + 1).join(" ");
  } else if (nameParts.length >= 2) {
    firstName = nameParts[0];
    lastName = nameParts.slice(1).join(" ");
  } else {
    firstName = student.name || "";
  }
  const avatarIcon = document.getElementById("profileAvatarIcon");
  const avatarImg = document.getElementById("profileAvatarImg");
  if (student.profile_pix && student.profile_pix !== '') {
    if (avatarImg) { avatarImg.src = student.profile_pix; avatarImg.classList.remove("hidden"); }
    if (avatarIcon) avatarIcon.classList.add("hidden");
  } else {
    if (avatarImg) avatarImg.classList.add("hidden");
    if (avatarIcon) avatarIcon.classList.remove("hidden");
  }
  document.getElementById("profileFirstName").value = firstName;
  document.getElementById("profileLastName").value = lastName;
  document.getElementById("profileMI").value = mi;
  document.getElementById("profileLrn").value = student.lrn || "";
  document.getElementById("profileContact").value = student.contact_no || "";
  document.getElementById("profileAddress").value = student.home_address || "";
  document.getElementById("profileGuardianName").value = student.guardian_name || "";
  document.getElementById("profileGuardianContact").value = student.guardian_contact || "";
  document.getElementById("originalProfilePix").value = student.profile_pix || "";
  document.getElementById("profileYearLevel").textContent = student.current_grade ? 'Grade ' + student.current_grade : "Not Assigned";
  document.getElementById("profileSection").textContent = student.advisory_name || "Not Assigned";
  document.getElementById("profileAdviser").textContent = student.teacher_name || "—";
}

function closeStudentProfileModal() {
  document.getElementById("studentProfileModal")?.classList.add("hidden");
  document.body.style.overflow = "auto";
  const fi = document.getElementById("profilePictureInput");
  if (fi) fi.value = "";
}

function enableProfileEditMode() {
  document.querySelectorAll(".profile-field").forEach(el => {
    el.removeAttribute("readonly");
    el.disabled = false;
    el.classList.remove("bg-gray-50");
    el.classList.add("bg-white");
  });
  const overlay = document.getElementById("profilePicOverlay");
  if (overlay) overlay.classList.remove("hidden");
  document.getElementById("profileViewButtons").classList.add("hidden");
  document.getElementById("profileEditButtons").classList.remove("hidden");
  document.getElementById("profileEditButtons").classList.add("flex");
  document.getElementById("profileModalTitle").textContent = "Edit Student Record";
  document.getElementById("profileModalSubtitle").textContent = "Update student information and save changes.";
  document.getElementById("profileEditMode").value = "1";
}

function cancelProfileEdit() {
  const studentId = document.getElementById("profileStudentId").value;
  openStudentProfileModal(studentId);
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
  const studentId = document.getElementById("profileStudentId").value;
  const firstName = document.getElementById("profileFirstName").value.trim();
  const lastName = document.getElementById("profileLastName").value.trim();
  const mi = document.getElementById("profileMI").value.trim();
  const lrn = document.getElementById("profileLrn").value.trim();
  const contact = document.getElementById("profileContact").value.trim();
  const address = document.getElementById("profileAddress").value.trim();
  const guardianName = document.getElementById("profileGuardianName").value.trim();
  const guardianContact = document.getElementById("profileGuardianContact").value.trim();
  const fileInput = document.getElementById("profilePictureInput");
  if (!firstName || !lastName) { showToast("First name and last name are required.", "error"); return; }
  if (lrn && lrn.length !== 12) { showToast("LRN must be exactly 12 digits.", "error"); return; }
  if (contact && contact.length !== 11) { showToast("Contact must be 11 digits.", "error"); return; }
  if (guardianContact && guardianContact.length !== 11) { showToast("Guardian contact must be 11 digits.", "error"); return; }
  let profilePicPath = document.getElementById("originalProfilePix").value;
  const doSave = (picPath) => {
    const fd = new FormData();
    fd.append("action", "update_student_info");
    fd.append("student_id", studentId);
    fd.append("first_name", firstName);
    fd.append("last_name", lastName);
    fd.append("mi", mi);
    fd.append("lrn", lrn);
    fd.append("contact_no", contact);
    fd.append("home_address", address);
    fd.append("guardian_name", guardianName);
    fd.append("guardian_contact", guardianContact);
    fd.append("profile_pix", picPath);
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
// STUDENT HISTORY MODAL — with direct print button
// ============================================================
function openStudentHistoryModal(studentId) {
  if (!studentId) return;
  window._currentHistoryStudentId = studentId;

  const modal = document.getElementById("studentHistoryModal");
  const loading = document.getElementById("historyLoadingState");
  const content = document.getElementById("historyContent");
  const nameEl = document.getElementById("historyModalStudentName");

  loading.classList.remove("hidden");
  content.classList.add("hidden");
  content.innerHTML = '';
  modal.classList.remove("hidden");

  const fn = document.getElementById("profileFirstName")?.value || '';
  const ln = document.getElementById("profileLastName")?.value || '';
  nameEl.textContent = [fn, ln].filter(Boolean).join(' ') || 'Student Record';
  window._currentHistoryStudentName = nameEl.textContent;

  const histFd = new FormData();
  histFd.append("action", "get_student_history");
  histFd.append("student_id", studentId);

  const incFd = new FormData();
  incFd.append("action", "get_student_incidents");
  incFd.append("student_id", studentId);

  Promise.all([
    fetch(window.location.href, { method: "POST", body: histFd }).then(r => r.json()),
    fetch(window.location.href, { method: "POST", body: incFd }).then(r => r.json()),
  ]).then(([histRes, incRes]) => {
    const history = (histRes.success ? histRes.data : []) || [];
    const incidents = (incRes.success ? incRes.data : []) || [];
    currentStudentHistoryData = { history, incidents };
    loading.classList.add("hidden");
    content.classList.remove("hidden");
    displayStudentHistory(history, incidents);
  }).catch(err => {
    loading.classList.add("hidden");
    content.classList.remove("hidden");
    content.innerHTML = '<p class="text-center text-red-500 py-10 text-sm">Error: ' + escapeHtml(err.message) + '</p>';
  });
}

function displayStudentHistory(history, incidents) {
  const content = document.getElementById("historyContent");
  if (!content) return;
  if (!history.length && !incidents.length) {
    content.innerHTML = '<div class="py-16 text-center"><i class="fas fa-inbox text-5xl text-gray-200 mb-4"></i><p class="text-sm text-gray-500">No records found.</p></div>';
    return;
  }
  const incByYear = {};
  (incidents || []).forEach(ic => {
    const key = ic.school_year || 'Unknown';
    if (!incByYear[key]) incByYear[key] = [];
    incByYear[key].push(ic);
  });
  let html = '';
  history.forEach((r, i) => {
    const syKey = r.start_year + '-' + r.end_year;
    const yearInc = incByYear[syKey] || [];
    html += '<div class="rounded-2xl overflow-hidden shadow-sm bg-white hover:shadow-md transition-all">' +
      '<div class="bg-gradient-to-r from-[#043915] to-[#032a0f] px-5 py-4 flex items-center justify-between">' +
      '<div class="flex items-center gap-3">' +
      '<div class="w-8 h-8 bg-white/15 rounded-lg flex items-center justify-center text-white text-xs font-bold">' + (i + 1) + '</div>' +
      '<div><p class="font-bold text-white text-sm">S.Y. ' + r.start_year + ' – ' + r.end_year + '</p>' +
      '<p class="text-[#f8c922] text-xs">' + formatDate(r.assigned_date) + '</p></div></div>' +
      '<span class="px-3 py-1 bg-[#f8c922] text-[#043915] rounded-lg text-xs font-bold">Grade ' + r.grade_level + '</span></div>' +
      '<div class="grid grid-cols-2 gap-3 p-4 bg-gray-50">' +
      '<div class="bg-white rounded-lg p-3"><p class="text-xs text-gray-500 mb-1 font-bold">Section</p>' +
      '<p class="text-sm font-bold text-gray-900">' + escapeHtml(r.advisory_name || '—') + '</p></div>' +
      '<div class="bg-white rounded-lg p-3"><p class="text-xs text-gray-500 mb-1 font-bold">Teacher</p>' +
      '<p class="text-sm font-bold text-gray-900">' + escapeHtml(r.teacher_name || '—') + '</p></div></div>' +
      '<div class="p-4"><div class="flex items-center gap-2 mb-3">' +
      '<p class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1">' +
      '<i class="fas fa-clipboard-list"></i> Incidents</p></div>' +
      '<div class="rounded-lg overflow-hidden">' +
      (yearInc.length === 0 ? '<p class="text-xs text-gray-500 italic p-3 bg-gray-50 font-medium">No incidents recorded.</p>' :
      '<table style="width:100%;border-collapse:collapse;font-size:12px;">' +
      '<thead><tr style="background:#f3f4f6;">' +
      '<th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;color:#6b7280;">Violation</th>' +
      '<th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;color:#6b7280;">Location</th>' +
      '<th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;color:#6b7280;">Date</th>' +
      '<th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;color:#6b7280;">Status</th></tr></thead><tbody>' +
      yearInc.map((ic, idx) => '<tr style="background:' + (idx % 2 === 0 ? '#fff' : '#f9fafb') + ';border-bottom:1px solid #f3f4f6;">' +
        '<td style="padding:8px 10px;font-size:12px;font-weight:600;color:#1f2937;">' + escapeHtml(ic.violation_display || '—') + '</td>' +
        '<td style="padding:8px 10px;font-size:12px;color:#6b7280;">' + escapeHtml(ic.location || '—') + '</td>' +
        '<td style="padding:8px 10px;font-size:12px;color:#6b7280;white-space:nowrap;">' + formatDate(ic.created_at) + '</td>' +
        '<td style="padding:8px 10px;"><span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;background:#dcfce7;color:#166534;">' + ic.status + '</span></td></tr>'
      ).join('') + '</tbody></table>') +
      '</div></div></div>';
  });
  content.innerHTML = html || '<p class="text-sm text-gray-400 text-center py-10">No records.</p>';
}

function closeStudentHistoryModal() {
  document.getElementById("studentHistoryModal").classList.add("hidden");
  currentStudentHistoryData = null;
}

// ============================================================
// PRINT STUDENT HISTORY AND PROFILE — direct print (no extra modal)
// ============================================================
function printStudentHistoryAndProfile() {
  if (!currentStudentHistoryData || !currentStudentProfileData) {
    showToast("Please load the student profile first.", "error");
    return;
  }
  const { history, incidents } = currentStudentHistoryData;
  const profile = currentStudentProfileData;
  const studentName = document.getElementById("historyModalStudentName").textContent;

  let historyHTML = '';
  if (history.length === 0) {
    historyHTML = '<p style="padding: 2rem; text-align: center; color: #999;">No academic history found.</p>';
  } else {
    historyHTML = history.map((r) => {
      const syKey = r.start_year + '-' + r.end_year;
      const yearInc = incidents.filter(ic => ic.school_year === syKey) || [];
      let incidentsHTML = '';
      if (yearInc.length === 0) {
        incidentsHTML = '<p style="padding: 0.75rem; color: #999; font-style: italic; font-size: 12px;">No incidents recorded for this school year.</p>';
      } else {
        incidentsHTML = '<table style="width:100%;border-collapse:collapse;margin-top:0.5rem;"><thead><tr style="background:#f3f4f6;"><th style="padding:8px 10px;text-align:left;font-size:11px;font-weight:bold;color:#6b7280;">Violation</th><th style="padding:8px 10px;text-align:left;font-size:11px;font-weight:bold;color:#6b7280;">Location</th><th style="padding:8px 10px;text-align:left;font-size:11px;font-weight:bold;color:#6b7280;">Date</th><th style="padding:8px 10px;text-align:left;font-size:11px;font-weight:bold;color:#6b7280;">Status</th></tr></thead><tbody>';
        incidentsHTML += yearInc.map(ic =>
          '<tr style="border-bottom:1px solid #e5e7eb;"><td style="padding:7px 10px;font-size:12px;color:#374151;">' + escapeHtml(ic.violation_display || '—') + '</td>' +
          '<td style="padding:7px 10px;font-size:12px;color:#6b7280;">' + escapeHtml(ic.location || '—') + '</td>' +
          '<td style="padding:7px 10px;font-size:12px;color:#6b7280;">' + formatDate(ic.created_at) + '</td>' +
          '<td style="padding:7px 10px;"><span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:bold;">' + ic.status + '</span></td></tr>'
        ).join('');
        incidentsHTML += '</tbody></table>';
      }
      return '<div style="margin-bottom:1.5rem;page-break-inside:avoid;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">' +
        '<div style="background:linear-gradient(to right,#043915,#032a0f);color:white;padding:0.75rem 1rem;display:flex;justify-content:space-between;align-items:center;">' +
        '<div><strong style="font-size:14px;">S.Y. ' + r.start_year + ' – ' + r.end_year + '</strong><div style="font-size:11px;color:#f8c922;margin-top:2px;">' + formatDate(r.assigned_date) + '</div></div>' +
        '<span style="background:#f8c922;color:#043915;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:bold;">Grade ' + r.grade_level + '</span></div>' +
        '<div style="padding:0.75rem 1rem;background:#f9fafb;display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">' +
        '<div><span style="font-size:10px;font-weight:bold;color:#6b7280;text-transform:uppercase;">Section</span><div style="font-size:13px;font-weight:600;color:#111;">' + escapeHtml(r.advisory_name || '—') + '</div></div>' +
        '<div><span style="font-size:10px;font-weight:bold;color:#6b7280;text-transform:uppercase;">Teacher</span><div style="font-size:13px;font-weight:600;color:#111;">' + escapeHtml(r.teacher_name || '—') + '</div></div></div>' +
        '<div style="padding:0.75rem 1rem;">' +
        '<div style="font-size:10px;font-weight:bold;color:#043915;text-transform:uppercase;margin-bottom:4px;">Incidents</div>' +
        incidentsHTML + '</div></div>';
    }).join('');
  }

  const printHTML = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + escapeHtml(studentName) + ' – Academic Report</title>' +
    '<style>*{margin:0;padding:0;box-sizing:border-box;}body{font-family:"Segoe UI",Tahoma,Geneva,sans-serif;line-height:1.5;color:#1f2937;background:white;}' +
    '.doc{padding:2rem;max-width:860px;margin:0 auto;}' +
    '.hdr{border-bottom:3px solid #043915;margin-bottom:1.5rem;padding-bottom:1rem;display:flex;justify-content:space-between;align-items:flex-end;}' +
    '.hdr-left h1{font-size:22px;font-weight:900;color:#043915;}.hdr-left p{font-size:12px;color:#6b7280;margin-top:2px;}' +
    '.hdr-right{text-align:right;font-size:11px;color:#6b7280;}' +
    '.sec{margin-bottom:1.5rem;}.sec h2{color:#043915;font-size:13px;font-weight:bold;margin-bottom:0.75rem;border-bottom:2px solid #f8c922;padding-bottom:4px;text-transform:uppercase;letter-spacing:.05em;}' +
    '.grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:0.5rem;}.grid2{display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;}' +
    '.item{padding:0.5rem 0.75rem;background:#f9fafb;border-radius:5px;border:1px solid #e5e7eb;}' +
    '.item-label{font-size:9px;font-weight:bold;color:#6b7280;text-transform:uppercase;}.item-value{font-size:12px;font-weight:600;color:#043915;margin-top:2px;}' +
    '.ftr{border-top:2px solid #043915;margin-top:2rem;padding-top:0.75rem;text-align:center;color:#9ca3af;font-size:10px;}' +
    '@page{margin:0.5in;}@media print{body{background:white;}.doc{padding:1rem;}}</style></head>' +
    '<body><div class="doc">' +
    '<div class="hdr"><div class="hdr-left"><h1>' + escapeHtml(studentName) + '</h1><p>Academic History &amp; Incident Report</p></div>' +
    '<div class="hdr-right"><div>Generated: ' + new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}) + '</div><div>Advisory Class Management System</div></div></div>' +
    '<div class="sec"><h2>Student Information</h2><div class="grid3">' +
    '<div class="item"><div class="item-label">Full Name</div><div class="item-value">' + escapeHtml(studentName) + '</div></div>' +
    '<div class="item"><div class="item-label">LRN</div><div class="item-value" style="font-family:monospace;">' + escapeHtml(profile.lrn || '—') + '</div></div>' +
    '<div class="item"><div class="item-label">Current Grade</div><div class="item-value">' + escapeHtml(profile.current_grade ? 'Grade ' + profile.current_grade : '—') + '</div></div>' +
    '<div class="item"><div class="item-label">Contact</div><div class="item-value" style="font-family:monospace;">' + escapeHtml(profile.contact_no || '—') + '</div></div>' +
    '<div class="item"><div class="item-label">Guardian</div><div class="item-value">' + escapeHtml(profile.guardian_name || '—') + '</div></div>' +
    '<div class="item"><div class="item-label">Section</div><div class="item-value">' + escapeHtml(profile.advisory_name || '—') + '</div></div>' +
    '</div></div>' +
    '<div class="sec"><h2>Contact Details</h2><div class="grid2">' +
    '<div class="item"><div class="item-label">Home Address</div><div class="item-value">' + escapeHtml(profile.home_address || '—') + '</div></div>' +
    '<div class="item"><div class="item-label">Guardian Contact</div><div class="item-value" style="font-family:monospace;">' + escapeHtml(profile.guardian_contact || '—') + '</div></div>' +
    '</div></div>' +
    '<div class="sec"><h2>Academic History &amp; Incidents</h2>' + historyHTML + '</div>' +
    '<div class="ftr"><p>Advisory Class Management System &nbsp;·&nbsp; ' + new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}) + '</p></div>' +
    '</div></body></html>';

  const pw = window.open('', '_blank', 'width=900,height=1200');
  pw.document.write(printHTML);
  pw.document.close();
  setTimeout(() => { pw.focus(); pw.print(); }, 300);
}

// ============================================================
// BULK PROMOTE MODAL
// ============================================================

// Grade lock map: each grade can only promote to the next grade
const GRADE_PROMOTE_MAP = { 7: 8, 8: 9, 9: 10, 10: 11 };

// State for bulk promote
let bulkPromoteStudents = []; // { assignment_id, student_id, student_name, grade_level, unresolved_count }

function openBulkPromoteModal(students) {
  // students = array of { assignment_id, student_id, student_name, grade_level } from advisory view
  bulkPromoteStudents = [];
  const modal = document.getElementById("bulkPromoteModal");
  const listEl = document.getElementById("bulkPromoteList");
  const loadingEl = document.getElementById("bulkPromoteLoading");
  const contentEl = document.getElementById("bulkPromoteContent");

  listEl.innerHTML = '';
  loadingEl.classList.remove("hidden");
  contentEl.classList.add("hidden");
  modal.classList.remove("hidden");
  modal.classList.add("flex");

  // Check unresolved incidents for all students in parallel
  const checks = students.map(s => {
    const fd = new FormData();
    fd.append("action", "check_unresolved_incidents");
    fd.append("student_id", s.student_id);
    return fetch(window.location.href, { method: "POST", body: fd })
      .then(r => r.json())
      .then(data => ({ ...s, unresolved_count: data.unresolved_count || 0 }))
      .catch(() => ({ ...s, unresolved_count: 0 }));
  });

  Promise.all(checks).then(results => {
    bulkPromoteStudents = results;
    loadingEl.classList.add("hidden");
    contentEl.classList.remove("hidden");
    renderBulkPromoteList();
  });
}

function renderBulkPromoteList() {
  const listEl = document.getElementById("bulkPromoteList");
  const selectAllEl = document.getElementById("bulkPromoteSelectAll");

  // Group students by grade so user knows what next grade they go to
  const groups = {};
  bulkPromoteStudents.forEach(s => {
    const g = String(s.grade_level);
    if (!groups[g]) groups[g] = [];
    groups[g].push(s);
  });

  let html = '';
  Object.keys(groups).sort().forEach(grade => {
    const nextGrade = GRADE_PROMOTE_MAP[parseInt(grade)];
    if (!nextGrade) return; // grade 11+ can't promote further here
    html += `<div class="mb-4">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-800">Grade ${grade}</span>
          <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
          <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-green-100 text-green-800">Grade ${nextGrade}</span>
        </div>
        <button onclick="selectGradeGroup('${grade}')" class="text-xs text-[#043915] font-bold hover:underline">Select all Grade ${grade}</button>
      </div>
      <div class="space-y-2">`;

    groups[grade].forEach(s => {
      const blocked = s.unresolved_count > 0;
      const checkboxDisabled = blocked ? 'disabled' : '';
      const rowClass = blocked ? 'opacity-60 bg-red-50' : 'bg-white hover:bg-gray-50';
      html += `<div class="flex items-center gap-3 p-3 rounded-lg border ${blocked ? 'border-red-200' : 'border-gray-200'} ${rowClass} transition">
        <input type="checkbox" class="bulk-promote-checkbox w-4 h-4 text-[#043915] rounded" 
          data-student-id="${s.student_id}" 
          data-assignment-id="${s.assignment_id}" 
          data-grade="${s.grade_level}"
          data-name="${escapeHtml(s.student_name)}"
          ${checkboxDisabled}
        >
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900 truncate">${escapeHtml(s.student_name)}</p>
          ${blocked ? `<p class="text-xs text-red-600 font-medium mt-0.5"><i class="fas fa-exclamation-circle mr-1"></i>${s.unresolved_count} unresolved incident(s) — cannot promote</p>` : `<p class="text-xs text-green-600 font-medium mt-0.5"><i class="fas fa-check-circle mr-1"></i>Ready for promotion</p>`}
        </div>
        ${blocked ? '<span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-lg shrink-0">Blocked</span>' : '<span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-lg shrink-0">Grade ' + nextGrade + '</span>'}
      </div>`;
    });

    html += `</div></div>`;
  });

  listEl.innerHTML = html || '<p class="text-sm text-gray-400 text-center py-8">No students to promote.</p>';

  // Update select all state
  updateBulkPromoteSelectAllState();
}

function updateBulkPromoteSelectAllState() {
  const all = document.querySelectorAll(".bulk-promote-checkbox:not([disabled])");
  const checked = document.querySelectorAll(".bulk-promote-checkbox:not([disabled]):checked");
  const selectAllBtn = document.getElementById("bulkPromoteSelectAll");
  if (!selectAllBtn) return;
  if (all.length === 0) {
    selectAllBtn.textContent = "Select All";
    return;
  }
  selectAllBtn.textContent = checked.length === all.length ? "Deselect All" : "Select All";
}

function toggleBulkPromoteSelectAll() {
  const all = document.querySelectorAll(".bulk-promote-checkbox:not([disabled])");
  const checked = document.querySelectorAll(".bulk-promote-checkbox:not([disabled]):checked");
  const shouldCheck = checked.length !== all.length;
  all.forEach(cb => { cb.checked = shouldCheck; });
  updateBulkPromoteSelectAllState();
}

function selectGradeGroup(grade) {
  const groupCbs = document.querySelectorAll(`.bulk-promote-checkbox[data-grade="${grade}"]:not([disabled])`);
  const allChecked = Array.from(groupCbs).every(cb => cb.checked);
  groupCbs.forEach(cb => { cb.checked = !allChecked; });
  updateBulkPromoteSelectAllState();
}

function closeBulkPromoteModal() {
  const modal = document.getElementById("bulkPromoteModal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
  bulkPromoteStudents = [];
}

function submitBulkPromotion() {
  const checked = document.querySelectorAll(".bulk-promote-checkbox:checked");
  if (checked.length === 0) {
    showToast("Select at least one student to promote.", "error");
    return;
  }

  const toPromote = Array.from(checked).map(cb => ({
    assignment_id: cb.dataset.assignmentId,
    student_id: cb.dataset.studentId,
    current_grade: parseInt(cb.dataset.grade),
    new_grade: GRADE_PROMOTE_MAP[parseInt(cb.dataset.grade)],
    name: cb.dataset.name
  })).filter(s => s.new_grade); // only valid grade promotions

  if (toPromote.length === 0) {
    showToast("No valid promotions selected.", "error");
    return;
  }

  // Disable submit button during processing
  const submitBtn = document.getElementById("bulkPromoteSubmitBtn");
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Promoting…';

  // Fire all promotions sequentially
  let successCount = 0;
  let failCount = 0;
  const promises = toPromote.map(s => {
    const fd = new FormData();
    fd.append("action", "promote_student");
    fd.append("assignment_id", s.assignment_id);
    fd.append("student_id", s.student_id);
    fd.append("current_grade", s.current_grade);
    fd.append("new_grade", s.new_grade);
    return fetch(window.location.href, { method: "POST", body: fd })
      .then(r => r.json())
      .then(data => { if (data.success) successCount++; else failCount++; })
      .catch(() => { failCount++; });
  });

  Promise.all(promises).then(() => {
    submitBtn.disabled = false;
    submitBtn.innerHTML = 'Promote Selected';
    if (successCount > 0) showToast(`${successCount} student(s) promoted successfully.`, "success");
    if (failCount > 0) showToast(`${failCount} promotion(s) failed.`, "error");
    closeBulkPromoteModal();
    if (currentView === "advisory") loadAdvisoryList();
    else loadFilteredData();
  });
}

// ============================================================
// PROMOTE STUDENT MODAL (single — kept for backward compat)
// ============================================================
function openPromoteModal(assignmentId, studentId, currentGrade) {
  document.getElementById("promoteAssignmentId").value = assignmentId;
  document.getElementById("promoteStudentId").value = studentId;
  document.getElementById("promoteCurrentGrade").value = currentGrade;
  const modal = document.getElementById("promoteModal");
  const loading = document.getElementById("promoteLoadingState");
  const content = document.getElementById("promoteContent");
  const nameEl = document.getElementById("promoteStudentInfo");
  loading.classList.remove("hidden");
  content.classList.add("hidden");
  modal.classList.remove("hidden");

  // Set the locked next grade in the dropdown
  const nextGrade = GRADE_PROMOTE_MAP[parseInt(currentGrade)];
  const sel = document.getElementById("promoteNewGrade");
  if (sel) {
    sel.innerHTML = '';
    if (nextGrade) {
      const opt = document.createElement("option");
      opt.value = nextGrade;
      opt.textContent = "Grade " + nextGrade;
      sel.appendChild(opt);
    } else {
      const opt = document.createElement("option");
      opt.value = "";
      opt.textContent = "No next grade available";
      sel.appendChild(opt);
    }
  }

  const fn = document.getElementById("profileFirstName")?.value || '';
  const ln = document.getElementById("profileLastName")?.value || '';
  nameEl.textContent = [fn, ln].filter(Boolean).join(' ') || 'Student';

  const incFd = new FormData();
  incFd.append("action", "check_unresolved_incidents");
  incFd.append("student_id", studentId);
  fetch(window.location.href, { method: "POST", body: incFd })
    .then(r => r.json())
    .then(data => {
      loading.classList.add("hidden");
      content.classList.remove("hidden");
      const unresolvedCount = data.unresolved_count || 0;
      const unresolvedWarning = document.getElementById("promoteUnresolvedWarning");
      const readyMessage = document.getElementById("promoteReadyMessage");
      const submitBtn = document.getElementById("promoteSubmitBtn");
      if (unresolvedCount > 0) {
        unresolvedWarning.classList.remove("hidden");
        readyMessage.classList.add("hidden");
        document.getElementById("unresolvedCount").textContent = unresolvedCount;
        submitBtn.disabled = true;
        submitBtn.classList.add("opacity-50", "cursor-not-allowed");
      } else {
        unresolvedWarning.classList.add("hidden");
        readyMessage.classList.remove("hidden");
        submitBtn.disabled = false;
        submitBtn.classList.remove("opacity-50", "cursor-not-allowed");
      }
    })
    .catch(() => {
      loading.classList.add("hidden");
      content.classList.remove("hidden");
      showToast("Error checking incidents.", "error");
    });
}

function closePromoteModal() {
  document.getElementById("promoteModal").classList.add("hidden");
  document.getElementById("promoteForm").reset();
}

function submitPromotion(event) {
  event.preventDefault();
  const fd = new FormData(event.target);
  const newGrade = parseInt(fd.get("new_grade"));
  const currentGrade = parseInt(fd.get("current_grade"));
  const expectedNext = GRADE_PROMOTE_MAP[currentGrade];
  if (!newGrade || newGrade !== expectedNext) {
    showToast("Can only promote to Grade " + expectedNext + ".", "error");
    return;
  }
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast(data.message, "success");
        closePromoteModal();
        loadFilteredData();
      } else {
        showToast(data.message || "Promotion failed.", "error");
      }
    })
    .catch(() => showToast("Error promoting student.", "error"));
}

// ============================================================
// TEACHER PROFILE MODAL
// ============================================================
function openTeacherProfileModal(advisoryId) {
  const modal = document.getElementById("teacherProfileModal");
  if (!modal) return;
  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
  const formContent = document.getElementById("teacherFormContent");
  const loadingState = document.getElementById("teacherLoadingState");
  if (formContent) formContent.classList.add("hidden");
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
    el.classList.add("bg-gray-50");
    el.classList.remove("bg-white");
  });
  const overlay = document.getElementById("teacherPicOverlay");
  if (overlay) overlay.classList.add("hidden");
  document.getElementById("teacherViewButtons").classList.remove("hidden");
  const editBtns = document.getElementById("teacherEditButtons");
  editBtns.classList.add("hidden");
  editBtns.classList.remove("flex");
  document.getElementById("teacherModalTitle").textContent = "Faculty Profile";
  document.getElementById("teacherModalSubtitle").textContent = "View teacher information";
}

function populateTeacherProfile(teacher) {
  const nameParts = (teacher.name || "").trim().split(/\s+/);
  let firstName = "", lastName = "", suffix = "";
  const suffixWords = ["LPT","PhD","MA","MS","BSc","Jr.","Sr.","II","III","IV"];
  const sufIdx = nameParts.findIndex(p => suffixWords.includes(p));
  if (sufIdx !== -1) {
    suffix = nameParts.slice(sufIdx).join(" ");
    firstName = nameParts[0] || "";
    lastName = nameParts.slice(1, sufIdx).join(" ");
  } else if (nameParts.length >= 2) {
    firstName = nameParts[0];
    lastName = nameParts.slice(1).join(" ");
  } else {
    firstName = teacher.name || "";
  }
  const avatarIcon = document.getElementById("teacherAvatarIcon");
  const avatarImg = document.getElementById("teacherAvatarImg");
  if (teacher.profile_pix && teacher.profile_pix !== '') {
    if (avatarImg) { avatarImg.src = teacher.profile_pix; avatarImg.classList.remove("hidden"); }
    if (avatarIcon) avatarIcon.classList.add("hidden");
  } else {
    if (avatarImg) avatarImg.classList.add("hidden");
    if (avatarIcon) avatarIcon.classList.remove("hidden");
  }
  const recId = document.getElementById("teacherRecordId");
  recId.value = teacher.user_id || "";
  recId.dataset.advisoryId = teacher.advisory_id || "";
  document.getElementById("teacherIdField").value = teacher.teacher_no || "";
  document.getElementById("teacherDeptField").value = teacher.department || "";
  document.getElementById("teacherFirstName").value = firstName;
  document.getElementById("teacherLastName").value = lastName;
  document.getElementById("teacherSuffix").value = suffix;
  document.getElementById("teacherEmail").value = teacher.email || "";
  document.getElementById("teacherContact").value = teacher.contact_no || "";
  document.getElementById("teacherSpecialization").value = teacher.advisory_section || teacher.specialization || "";
  document.getElementById("originalTeacherPix").value = teacher.profile_pix || "";
}

function closeTeacherProfileModal() {
  document.getElementById("teacherProfileModal")?.classList.add("hidden");
  document.body.style.overflow = "auto";
  const fi = document.getElementById("teacherPictureInput");
  if (fi) fi.value = "";
}

function enableTeacherEditMode() {
  document.querySelectorAll(".teacher-field").forEach(el => {
    el.removeAttribute("readonly");
    el.disabled = false;
    el.classList.remove("bg-gray-50");
    el.classList.add("bg-white");
  });
  const overlay = document.getElementById("teacherPicOverlay");
  if (overlay) overlay.classList.remove("hidden");
  document.getElementById("teacherViewButtons").classList.add("hidden");
  document.getElementById("teacherEditButtons").classList.remove("hidden");
  document.getElementById("teacherEditButtons").classList.add("flex");
  document.getElementById("teacherModalTitle").textContent = "Edit Faculty Record";
  document.getElementById("teacherModalSubtitle").textContent = "Update teacher information and save changes.";
  document.getElementById("teacherEditMode").value = "1";
}

function cancelTeacherEdit() {
  const advisoryId = document.getElementById("teacherRecordId").dataset.advisoryId;
  closeTeacherProfileModal();
  if (advisoryId) setTimeout(() => openTeacherProfileModal(advisoryId), 100);
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
  const teacherId = document.getElementById("teacherRecordId").value;
  const idField = document.getElementById("teacherIdField").value.trim();
  const firstName = document.getElementById("teacherFirstName").value.trim();
  const lastName = document.getElementById("teacherLastName").value.trim();
  const suffix = document.getElementById("teacherSuffix").value.trim();
  const email = document.getElementById("teacherEmail").value.trim();
  const contact = document.getElementById("teacherContact").value.trim();
  const department = document.getElementById("teacherDeptField").value;
  const specialization = document.getElementById("teacherSpecialization").value.trim();
  const fileInput = document.getElementById("teacherPictureInput");
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
  const thead = document.getElementById("mainTable")?.querySelector("thead");
  const tbody = document.getElementById("tableBody");
  const countEl = document.getElementById("resultCount");
  if (!thead || !tbody) return;
  thead.innerHTML = '<tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white"><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Student</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">LRN</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Grade</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Teacher</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Class</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Date</th><th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Actions</th></tr>';
  countEl.innerHTML = data.length === 0 ? 'No records found' : 'Showing ' + data.length + ' student' + (data.length !== 1 ? 's' : '');
  if (data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-20 text-center"><i class="fas fa-inbox text-5xl text-gray-300 mb-4 block"></i><p class="text-base font-semibold text-gray-600">No records found</p></td></tr>';
    renderPagination(0);
    return;
  }
  const pagData = getPaginatedData(data);
  const gradeColors = { 7: "bg-blue-100 text-blue-800", 8: "bg-orange-100 text-orange-800", 9: "bg-purple-100 text-purple-800", 10: "bg-green-100 text-green-800" };
  tbody.innerHTML = pagData.map(row => {
    const gc = gradeColors[row.grade_level] || "bg-gray-100 text-gray-800";
    return '<tr class="hover:bg-gray-50 transition border-b border-gray-100"><td class="py-3 px-6"><div class="text-sm font-semibold text-gray-900">' + escapeHtml(row.student_name) + '</div></td><td class="py-3 px-6 text-sm text-gray-600 font-mono">' + escapeHtml(row.lrn || "—") + '</td><td class="py-3 px-6"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ' + gc + '">Grade ' + row.grade_level + '</span></td><td class="py-3 px-6 text-sm text-gray-900">' + escapeHtml(row.teacher_name) + '</td><td class="py-3 px-6"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">' + escapeHtml(row.advisory_name) + '</span></td><td class="py-3 px-6 text-xs text-gray-500">' + formatDate(row.assigned_date) + '</td><td class="py-3 px-6"><div class="flex items-center justify-center gap-1.5 flex-wrap"><button onclick="openStudentProfileModal(' + row.student_id + ')" class="px-2.5 py-1.5 bg-[#043915] text-white rounded-lg text-xs font-bold hover:bg-[#032a0f] transition">View</button><button onclick="openStudentHistoryModal(' + row.student_id + ')" class="px-2.5 py-1.5 bg-[#f8c922] text-[#043915] rounded-lg text-xs font-bold hover:bg-[#e6b70f] transition">History</button><button onclick="openReassignModal(' + row.assignment_id + ', \'' + escapeJs(row.student_name) + '\', ' + row.grade_level + ')" class="px-2.5 py-1.5 bg-orange-500 text-white rounded-lg text-xs font-bold hover:bg-orange-600 transition">Reassign</button><button onclick="openRemoveAdvisoryModal(' + row.assignment_id + ', \'' + escapeJs(row.student_name) + '\')" class="px-2.5 py-1.5 bg-red-500 text-white rounded-lg text-xs font-bold hover:bg-red-600 transition">Remove</button></div></td></tr>';
  }).join("");
  renderPagination(data.length);
}

// ADVISORY LIST
function displayAdvisoryList(advisories) {
  const thead = document.getElementById("mainTable")?.querySelector("thead");
  const tbody = document.getElementById("tableBody");
  const countEl = document.getElementById("resultCount");
  if (!thead || !tbody) return;
  thead.innerHTML = '<tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white"><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Advisory Class</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Teacher</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Grade</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Capacity</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Created</th><th class="py-4 px-6 text-center text-xs font-bold uppercase tracking-wide whitespace-nowrap">Actions</th></tr>';
  countEl.innerHTML = advisories.length === 0 ? 'No classes found' : 'Showing ' + advisories.length + ' class' + (advisories.length !== 1 ? 'es' : '');
  if (advisories.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-20 text-center"><i class="fas fa-inbox text-5xl text-gray-300 mb-4 block"></i><p class="text-base font-semibold text-gray-600">No classes found</p></td></tr>';
    renderPagination(0);
    return;
  }
  const pagData = getPaginatedData(advisories);
  const gradeColors = { 7: "bg-blue-100 text-blue-800", 8: "bg-orange-100 text-orange-800", 9: "bg-purple-100 text-purple-800", 10: "bg-green-100 text-green-800" };
  tbody.innerHTML = pagData.map(adv => {
    const gc = gradeColors[adv.grade_level] || "bg-gray-100 text-gray-800";
    const cc = adv.student_count >= 40 ? "bg-red-100 text-red-800" : adv.student_count >= 35 ? "bg-orange-100 text-orange-800" : "bg-green-100 text-green-800";
    return '<tr class="hover:bg-gray-50 transition border-b border-gray-100"><td class="py-3 px-6"><div class="text-sm font-semibold text-gray-900">' + escapeHtml(adv.advisory_name) + '</div></td><td class="py-3 px-6 text-sm text-gray-700">' + escapeHtml(adv.teacher_name) + '</td><td class="py-3 px-6"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ' + gc + '">Grade ' + adv.grade_level + '</span></td><td class="py-3 px-6"><span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold ' + cc + '">' + adv.student_count + '/40</span></td><td class="py-3 px-6 text-xs text-gray-400">' + formatDate(adv.created_at) + '</td><td class="py-3 px-6 text-center"><div class="flex items-center justify-center gap-1.5"><button onclick="viewAdvisoryDetails(' + adv.advisory_id + ', \'' + adv.grade_level + '\')" class="px-3 py-1.5 bg-[#043915] text-white rounded-lg text-xs font-bold hover:bg-[#032a0f] transition">View</button><button onclick="openTeacherProfileModal(' + adv.advisory_id + ')" class="px-3 py-1.5 bg-[#f8c922] text-[#043915] rounded-lg text-xs font-bold hover:bg-[#e6b70f] transition">Edit</button></div></td></tr>';
  }).join("");
  renderPagination(advisories.length);
}

// SUBJECT TEACHERS LIST
function displaySubjectTeachersList(teachers) {
  const thead = document.getElementById("mainTable")?.querySelector("thead");
  const tbody = document.getElementById("tableBody");
  const countEl = document.getElementById("resultCount");
  if (!thead || !tbody) return;
  thead.innerHTML = '<tr class="bg-gradient-to-r from-[#043915] to-[#032a0f] text-white"><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Teacher Name</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Email</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Department</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Date Assigned</th><th class="py-4 px-6 text-left text-xs font-bold uppercase tracking-wide whitespace-nowrap">Action</th></tr>';
  countEl.innerHTML = teachers.length === 0 ? 'No teachers found' : 'Showing ' + teachers.length + ' teacher' + (teachers.length !== 1 ? 's' : '');
  if (teachers.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-20 text-center"><i class="fas fa-inbox text-5xl text-gray-300 mb-4 block"></i><p class="text-base font-semibold text-gray-600">No teachers found</p></td></tr>';
    renderPagination(0);
    return;
  }
  const pagData = getPaginatedData(teachers);
  tbody.innerHTML = pagData.map(t => '<tr class="hover:bg-gray-50 transition border-b border-gray-100"><td class="py-3 px-6"><div class="text-sm font-semibold text-gray-900">' + escapeHtml(t.teacher_name) + '</div></td><td class="py-3 px-6 text-sm text-gray-500">' + escapeHtml(t.teacher_email || "—") + '</td><td class="py-3 px-6"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">' + (t.department || 'N/A') + '</span></td><td class="py-3 px-6 text-xs text-gray-400">' + formatDate(t.assigned_at) + '</td><td class="py-3 px-6 text-center"><button onclick="openTeacherProfileModal(' + t.user_id + ')" class="px-3 py-1.5 bg-[#043915] text-white rounded-lg text-xs font-bold hover:bg-[#032a0f] transition">View</button></td></tr>').join("");
  renderPagination(teachers.length);
}

// FILTERS
function applyFilters() {
  currentPage = 1;
  const role = document.getElementById("filterTeacherRole").value;
  if (role === "advisory") { currentView = "advisory"; loadAdvisoryList(); }
  else if (role === "subject") { currentView = "subject"; loadSubjectTeachersList(); }
  else { currentView = "default"; loadFilteredData(); }
  updateTableTitle();
}

function updateTableTitle() {
  const role = document.getElementById("filterTeacherRole").value;
  const el = document.getElementById("pageTitle");
  if (el) {
    if (role === "advisory") el.textContent = "Advisory Classes";
    else if (role === "subject") el.textContent = "Subject Teachers";
    else el.textContent = "Advisory Class Management";
  }
}

function loadFilteredData() {
  const fd = new FormData();
  fd.append("action", "get_filtered_data");
  fd.append("teacher_role", document.getElementById("filterTeacherRole").value);
  fd.append("grade_level", document.getElementById("filterGrade").value);
  fd.append("date_filter", document.getElementById("filterDate").value);
  fd.append("search", document.getElementById("searchInput").value);
  fd.append("sort_by", "student_name");
  fd.append("sort_order", document.getElementById("sortName").value);
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => { if (data.success) { allData = data.data; displayTableData(data.data); } })
    .catch(e => console.error("Error:", e));
}

function loadAdvisoryList() {
  const search = document.getElementById("searchInput").value;
  const fd = new FormData();
  fd.append("action", "get_advisory_list");
  fd.append("sort_by", "advisory_name");
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
  document.getElementById("filterGrade").value = "";
  document.getElementById("sortName").value = "ASC";
  document.getElementById("filterDate").value = "";
  document.getElementById("searchInput").value = "";
  currentPage = 1;
  currentView = "default";
  loadFilteredData();
  updateTableTitle();
  showToast("Filters cleared.", "success");
}

// TEACHER ASSIGN MODAL
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
  const af = document.getElementById("advisoryFields");
  const nm = document.getElementById("advisoryNameInput");
  const gr = document.getElementById("advisoryGradeLevelInput");
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

// STUDENT ASSIGN MODAL
function openStudentModal() {
  const sel = document.getElementById("modalAdvisoryTeacher");
  if (sel.options.length <= 1) { showToast("Assign an advisory teacher first.", "error"); return; }
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
}

function loadAllAvailableStudents() {
  document.getElementById("modalStudentTable").innerHTML = '<tr><td colspan="5" class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2 block"></i><span class="text-sm text-gray-500">Loading…</span></td></tr>';
  const fd = new FormData();
  fd.append("action", "get_unassigned_students");
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => { if (data.success) { allAvailableStudents = data.data; displayFilteredStudents(); } else document.getElementById("modalStudentTable").innerHTML = '<tr><td colspan="5" class="py-10 text-center text-sm text-red-500">Error loading students.</td></tr>'; })
    .catch(() => { document.getElementById("modalStudentTable").innerHTML = '<tr><td colspan="5" class="py-10 text-center text-sm text-red-500">Error.</td></tr>'; });
}

function filterModalStudents(grade) {
  currentGradeFilter = grade;
  displayFilteredStudents();
}

function displayFilteredStudents() {
  const tbody = document.getElementById("modalStudentTable");
  const sel = document.getElementById("modalAdvisoryTeacher");
  const selOpt = sel.value ? sel.selectedOptions[0] : null;
  const advGrade = selOpt ? selOpt.getAttribute("data-grade-level") : null;
  let filtered = allAvailableStudents;
  if (advGrade) filtered = filtered.filter(s => s.grade_level === advGrade);
  else if (currentGradeFilter !== "all") filtered = filtered.filter(s => s.grade_level === currentGradeFilter);
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center"><i class="fas fa-inbox text-4xl text-gray-300 mb-3 block"></i><p class="text-sm text-gray-500">No available students.</p></td></tr>';
    return;
  }
  const gc = { "7": "bg-blue-100 text-blue-800", "8": "bg-orange-100 text-orange-800", "9": "bg-purple-100 text-purple-800", "10": "bg-green-100 text-green-800" };
  tbody.innerHTML = filtered.map(s => {
    let opts = "";
    for (let g = 7; g <= 12; g++) opts += '<option value="' + g + '" ' + (String(g) === String(s.grade_level) ? "selected" : "") + '>Grade ' + g + '</option>';
    return '<tr class="hover:bg-gray-50 transition"><td class="py-3 px-4"><input type="checkbox" name="student_ids[]" value="' + s.user_id + '" class="student-checkbox w-4 h-4 text-[#043915] rounded"><input type="hidden" name="grade_levels[' + s.user_id + ']" id="gradeHidden_' + s.user_id + '" value="' + s.grade_level + '"></td><td class="py-3 px-4"><p class="text-sm font-semibold text-gray-900">' + escapeHtml(s.name) + '</p></td><td class="py-3 px-4 text-sm text-gray-600 font-mono">' + escapeHtml(s.lrn || "—") + '</td><td class="py-3 px-4"><span class="px-2 py-1 ' + (gc[s.grade_level] || 'bg-gray-100 text-gray-800') + ' rounded-lg text-xs font-bold">Grade ' + s.grade_level + '</span></td><td class="py-3 px-4"><select onchange="updateStudentGradeInModal(this,' + s.user_id + ')" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs bg-white text-gray-700">' + opts + '</select></td></tr>';
  }).join("");
}

function updateStudentGradeInModal(sel, studentId) {
  const grade = sel.value;
  const hi = document.getElementById('gradeHidden_' + studentId);
  if (hi) hi.value = grade;
}

function toggleAllVisibleStudents(checked) {
  document.querySelectorAll(".student-checkbox").forEach(cb => { const r = cb.closest("tr"); if (r && r.style.display !== "none") cb.checked = checked; });
}

function confirmStudentAssignment() {
  const sel = document.getElementById("modalAdvisoryTeacher");
  const advId = sel.value;
  const selected = document.querySelectorAll(".student-checkbox:checked");
  if (!advId) { showToast("Select an advisory teacher.", "error"); return; }
  if (selected.length === 0) { showToast("Select at least one student.", "error"); return; }
  const opt = sel.selectedOptions[0];
  const count = parseInt(opt.getAttribute("data-current-count")) || 0;
  if (count + selected.length > 40) { showToast('Only ' + (40 - count) + ' slots available.', "error"); return; }
  document.getElementById("hiddenAdvisoryId").value = advId;
  const fd = new FormData(document.getElementById("assignStudentsForm"));
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => { if (data.success) { showToast(data.message, "success"); closeStudentModal(); setTimeout(() => location.reload(), 1500); } else showToast(data.message, "error"); })
    .catch(() => showToast("Error.", "error"));
}

document.getElementById("modalAdvisoryTeacher")?.addEventListener("change", function() {
  const opt = this.selectedOptions[0];
  const ci = document.getElementById("advisoryCapacityInfo");
  if (!this.value) { ci.classList.add("hidden"); displayFilteredStudents(); return; }
  const cnt = parseInt(opt.getAttribute("data-current-count")) || 0;
  const gr = opt.getAttribute("data-grade-level");
  document.getElementById("currentStudentCount").textContent = cnt;
  document.getElementById("remainingSlots").textContent = 40 - cnt;
  document.getElementById("advisoryGradeLevel").textContent = gr;
  ci.classList.remove("hidden");
  displayFilteredStudents();
});

// REASSIGN MODAL
function openReassignModal(assignmentId, studentName, currentGrade) {
  document.getElementById("reassignAssignmentId").value = assignmentId;
  document.getElementById("reassignStudentName").textContent = studentName;
  document.getElementById("reassignCurrentGrade").value = currentGrade;
  const sel = document.getElementById("reassignAdvisorySelect");
  Array.from(sel.options).forEach(o => {
    if (o.value) { const og = o.getAttribute("data-grade"); o.disabled = og !== String(currentGrade); }
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
  const fd = new FormData(event.target);
  const sel = document.getElementById("reassignAdvisorySelect").selectedOptions[0];
  const ag = sel?.getAttribute("data-grade");
  const cg = fd.get("current_grade");
  if (ag !== String(cg)) { showToast("Grade mismatch.", "error"); return; }
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => { if (data.success) { showToast(data.message, "success"); closeReassignModal(); loadFilteredData(); } else showToast(data.message, "error"); })
    .catch(() => showToast("Error.", "error"));
}

// REMOVE MODAL
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
  fetch(window.location.href, { method: "POST", body: new FormData(event.target) })
    .then(r => r.json())
    .then(data => { if (data.success) { showToast(data.message, "success"); closeRemoveAdvisoryModal(); loadFilteredData(); } else showToast(data.message, "error"); })
    .catch(() => showToast("Error.", "error"));
}

// VIEW ADVISORY DETAILS — updated to support bulk promote
function viewAdvisoryDetails(advisoryId, advisoryGrade) {
  currentAdvisoryGrade = advisoryGrade;
  const fd = new FormData();
  fd.append("action", "get_advisory_students");
  fd.append("advisory_id", advisoryId);
  fetch(window.location.href, { method: "POST", body: fd })
    .then(r => r.json())
    .then(data => { if (data.success) displayAdvisoryStudents(data.data, advisoryId, advisoryGrade); })
    .catch(e => console.error("Error:", e));
}

function displayAdvisoryStudents(students, advisoryId, advisoryGrade) {
  const modal = document.getElementById("viewAdvisoryModal");
  const list = document.getElementById("advisoryStudentsList");
  document.getElementById("advisoryDetailTitle").innerHTML = 'Advisory Students';
  document.getElementById("advisoryDetailSubtitle").textContent = students.length + '/40 · Grade ' + advisoryGrade;

  if (students.length === 0) {
    list.innerHTML = '<div class="py-16 text-center"><i class="fas fa-inbox text-5xl text-gray-200 mb-4"></i><p class="text-sm text-gray-500">No students assigned.</p></div>';
  } else {
    const nextGrade = GRADE_PROMOTE_MAP[parseInt(advisoryGrade)];
    const gc = { 7: "bg-blue-100 text-blue-800", 8: "bg-orange-100 text-orange-800", 9: "bg-purple-100 text-purple-800", 10: "bg-green-100 text-green-800" };

    // Build the bulk promote button (only if there's a next grade)
    const bulkBtn = nextGrade
      ? `<button onclick="openBulkPromoteFromAdvisory()" class="px-4 py-2 bg-[#f8c922] text-[#043915] rounded-xl text-xs font-bold hover:bg-[#e6b70f] transition flex items-center gap-2 shadow-sm">
           <i class="fas fa-level-up-alt"></i> Bulk Promote to Grade ${nextGrade}
         </button>`
      : '';

    list.innerHTML = `
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div class="flex items-center gap-3">
          ${nextGrade ? `<label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" id="advisorySelectAll" onchange="toggleAdvisorySelectAll(this.checked)" class="w-4 h-4 text-[#043915] rounded">
            <span class="text-sm font-bold text-gray-700">Select All</span>
          </label>` : ''}
        </div>
        <div>${bulkBtn}</div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        ${students.map(s => `
          <div class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all border border-gray-100">
            <div class="flex items-start gap-3 mb-3">
              ${nextGrade ? `<input type="checkbox" class="advisory-student-checkbox mt-1 w-4 h-4 text-[#043915] rounded shrink-0" 
                data-student-id="${s.student_id}" 
                data-assignment-id="${s.assignment_id}" 
                data-grade="${s.grade_level}"
                data-name="${escapeHtml(s.student_name)}"
                onchange="updateAdvisorySelectAllState()">` : ''}
              <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">${escapeHtml(s.student_name)}</p>
                <p class="text-xs text-gray-500 font-mono">${escapeHtml(s.lrn)}</p>
              </div>
              <span class="px-2 py-0.5 rounded-lg text-xs font-bold ${gc[s.grade_level] || 'bg-gray-100 text-gray-800'} shrink-0">Gr.${s.grade_level}</span>
            </div>
            <div class="flex items-center justify-between mt-2">
              <p class="text-xs text-gray-400">${formatDate(s.assigned_date)}</p>
              <div class="flex gap-2">
                <button onclick="openStudentHistoryModal(${s.student_id})" class="px-2.5 py-1 bg-[#043915] text-white text-xs font-bold rounded-lg hover:bg-[#032a0f] transition">History</button>
                ${nextGrade ? `<button onclick="openPromoteModal(${s.assignment_id}, ${s.student_id}, ${s.grade_level})" class="px-2.5 py-1 bg-[#f8c922] text-[#043915] text-xs font-bold rounded-lg hover:bg-[#e6b70f] transition">Promote</button>` : ''}
              </div>
            </div>
          </div>`).join('')}
      </div>`;

    // Store current advisory students data for bulk promote
    window._currentAdvisoryStudents = students;
  }

  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function updateAdvisorySelectAllState() {
  const all = document.querySelectorAll(".advisory-student-checkbox");
  const checked = document.querySelectorAll(".advisory-student-checkbox:checked");
  const selectAllCb = document.getElementById("advisorySelectAll");
  if (!selectAllCb) return;
  selectAllCb.indeterminate = checked.length > 0 && checked.length < all.length;
  selectAllCb.checked = checked.length === all.length && all.length > 0;
}

function toggleAdvisorySelectAll(checked) {
  document.querySelectorAll(".advisory-student-checkbox").forEach(cb => { cb.checked = checked; });
}

function openBulkPromoteFromAdvisory() {
  const checked = document.querySelectorAll(".advisory-student-checkbox:checked");
  const students = checked.length > 0
    ? Array.from(checked).map(cb => ({
        student_id: cb.dataset.studentId,
        assignment_id: cb.dataset.assignmentId,
        grade_level: parseInt(cb.dataset.grade),
        student_name: cb.dataset.name
      }))
    : (window._currentAdvisoryStudents || []).map(s => ({
        student_id: s.student_id,
        assignment_id: s.assignment_id,
        grade_level: parseInt(s.grade_level),
        student_name: s.student_name
      }));

  if (students.length === 0) {
    showToast("No students to promote.", "error");
    return;
  }

  openBulkPromoteModal(students);
}

function closeViewAdvisoryModal() {
  const m = document.getElementById("viewAdvisoryModal");
  m.classList.add("hidden");
  m.classList.remove("flex");
  currentAdvisoryGrade = null;
  window._currentAdvisoryStudents = null;
}

// UTILITIES
function escapeHtml(text) {
  const d = document.createElement("div");
  d.textContent = String(text ?? "");
  return d.innerHTML;
}

function escapeJs(text) {
  return String(text ?? "").replace(/\\/g, "\\\\").replace(/'/g, "\\'");
}

function formatDate(ds) {
  if (!ds) return "—";
  const d = new Date(ds);
  if (isNaN(d)) return ds;
  return d.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
}

function closePrintModal() {
  document.getElementById("printReportModal").classList.add("hidden");
}

// INIT
document.addEventListener("DOMContentLoaded", function() {
  checkAdvisoryAvailability();
  loadFilteredData();
  document.getElementById("filterTeacherRole")?.addEventListener("change", () => { currentPage = 1; applyFilters(); });
  ["filterGrade", "sortName", "filterDate"].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => { currentPage = 1; applyFilters(); });
  });
  let searchTimer;
  document.getElementById("searchInput")?.addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { currentPage = 1; applyFilters(); }, 350);
  });
  // Delegate checkbox change for bulk promote select all state
  document.addEventListener("change", function(e) {
    if (e.target.classList.contains("bulk-promote-checkbox")) {
      updateBulkPromoteSelectAllState();
    }
  });
});

function checkAdvisoryAvailability() {
  const sel = document.getElementById("modalAdvisoryTeacher");
  const has = sel && sel.options.length > 1;
  document.getElementById("assignStudentBtn")?.classList.toggle("hidden", !has);
  document.getElementById("noAdvisoryMessage")?.classList.toggle("hidden", has);
}

function toggleFilters() {
  const filters = document.getElementById("filterBars");
  filters.classList.toggle("hidden");
}