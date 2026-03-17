<?php
// Check if user is logged in and must change credentials
if (!isset($_SESSION['user'])) {
    header('Location: /student-discipline-and-incident-reporting-system/public/log-in');
    exit;
}

// If user already changed credentials, redirect to dashboard
if ($_SESSION['user']['must_change_credentials'] == 0) {
    if ($_SESSION['user']['role'] === 'Teacher') {
        header('Location: /student-discipline-and-incident-reporting-system/public/teacher-dashboard');
    } elseif ($_SESSION['user']['role'] === 'admin') {
        header('Location: /student-discipline-and-incident-reporting-system/public/admin-dashboard');
    }
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

$errorMessage   = $_SESSION['error_message']   ?? '';
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['error_message'], $_SESSION['success_message']);

// ── Resolve the app base URL once in PHP so JS never has to guess ──────────
// e.g. http://localhost/student-discipline-and-incident-reporting-system
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Strip /public/... from REQUEST_URI to get the project root
$uriParts   = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$projectIdx = array_search('student-discipline-and-incident-reporting-system', $uriParts);
$appRoot    = ($projectIdx !== false)
    ? $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $projectIdx + 1))
    : $scheme . '://' . $host;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Your Credentials</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #043915 0%, #0a6b2a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center;
            z-index: 1000;
        }

        .modal {
            background: white; border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            max-width: 500px; width: 100%;
            max-height: 90vh; overflow-y: auto;
            animation: slideUp .3s ease-out;
        }

        @keyframes slideUp {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0);    }
        }

        .modal-header {
            background: linear-gradient(135deg, #043915 0%, #0a6b2a 100%);
            color: white; padding: 30px 25px;
            border-radius: 16px 16px 0 0;
        }
        .modal-header h2 { font-size:24px; margin-bottom:8px; font-weight:600; }
        .modal-header p  { font-size:13px; opacity:.9; }

        .modal-content { padding: 30px 25px; }

        .alert {
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 20px; font-size: 13px;
            display: none; animation: slideDown .3s ease-out;
        }
        .alert.show { display: block; }

        @keyframes slideDown {
            from { opacity:0; transform:translateY(-10px); }
            to   { opacity:1; transform:translateY(0);     }
        }

        .alert-success { background:#d4edda; border:1px solid #c3e6cb; color:#155724; }
        .alert-danger  { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; }
        .alert-info    { background:#d1ecf1; border:1px solid #bee5eb; color:#0c5460; }

        .form-section { display: none; }
        .form-section.active { display: block; animation: fadeIn .3s ease-out; }

        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

        .form-group { margin-bottom: 20px; }

        label {
            display: block; font-size: 12px; font-weight: 600;
            color: #333; margin-bottom: 8px;
            text-transform: uppercase; letter-spacing: .5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%; padding: 12px 14px;
            border: 2px solid #e0e0e0; border-radius: 6px;
            font-size: 14px; transition: all .3s ease;
        }
        input:focus {
            outline: none; border-color: #043915;
            box-shadow: 0 0 0 3px rgba(4,57,21,.15);
        }

        .input-wrapper { position: relative; }

        .toggle-btn {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #999; font-size: 18px; padding: 4px 8px;
        }
        .toggle-btn:hover { color: #043915; }

        .password-strength {
            margin-top: 8px; padding: 10px; border-radius: 6px;
            font-size: 12px; font-weight: 600;
            display: none; align-items: center; gap: 8px;
        }
        .strength-bar {
            flex: 1; height: 4px; background: #e0e0e0;
            border-radius: 2px; overflow: hidden;
        }
        .strength-fill { height: 100%; width: 0; transition: all .3s ease; }

        .password-strength.weak   { background:#ffe0e0; color:#d32f2f; display:flex; }
        .password-strength.weak   .strength-fill { background:#d32f2f; width:33%;  }
        .password-strength.fair   { background:#fff3cd; color:#856404; display:flex; }
        .password-strength.fair   .strength-fill { background:#ffc107; width:66%;  }
        .password-strength.strong { background:#e8f5e9; color:#2e7d32; display:flex; }
        .password-strength.strong .strength-fill { background:#4caf50; width:100%; }

        .requirements {
            margin-top: 15px; padding: 12px;
            background: #f5f5f5; border-radius: 6px;
            font-size: 12px; color: #666;
        }
        .requirement {
            padding: 4px 0; display: flex;
            align-items: center; gap: 8px;
        }
        .requirement-icon {
            width:16px; height:16px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:10px; font-weight:bold;
            background:#e0e0e0; color:#999;
        }
        .requirement.met .requirement-icon { background:#4caf50; color:white; }
        .requirement.met { color: #2e7d32; }

        .otp-input-group {
            display:flex; gap:8px; justify-content:center; margin:20px 0;
        }
        .otp-input {
            width:50px; height:50px; font-size:24px; text-align:center;
            border:2px solid #e0e0e0; border-radius:8px; transition:all .3s ease;
        }
        .otp-input:focus {
            outline:none; border-color:#043915;
            box-shadow:0 0 0 3px rgba(4,57,21,.15);
        }
        .otp-input.filled { border-color: #043915; }

        .otp-resend {
            text-align:center; margin-top:20px;
            font-size:13px; color:#666;
        }
        .otp-timer { font-weight:bold; color:#043915; }
        .resend-link {
            color:#f8c922; cursor:pointer;
            text-decoration:none; font-weight:600;
        }
        .resend-link:hover { text-decoration: underline; }
        .resend-link.disabled { color:#ccc; cursor:not-allowed; }

        .button-group { display:flex; gap:12px; margin-top:25px; }

        .btn {
            flex:1; padding:12px; border:none; border-radius:6px;
            font-size:13px; font-weight:600; cursor:pointer;
            transition:all .3s ease;
            text-transform:uppercase; letter-spacing:.5px;
        }
        .btn-primary { background:#043915; color:white; }
        .btn-primary:hover:not(:disabled) {
            background:#032b10;
            box-shadow:0 8px 20px rgba(4,57,21,.4);
        }
        .btn-primary:disabled { background:#ccc; cursor:not-allowed; opacity:.6; }
        .btn-secondary {
            background:#f5f5f5; color:#666;
            border:1px solid #e0e0e0;
        }
        .btn-secondary:hover { background: #e0e0e0; }

        .current-user {
            background:#f9f9f9; padding:12px; border-radius:6px;
            margin-bottom:20px; border-left:4px solid #043915;
        }
        .current-user small { color:#666; font-size:11px; }
        .current-user strong { color:#333; display:block; margin-top:4px; }

        .info-box {
            background:#fffbea; border-left:4px solid #f8c922;
            padding:12px; border-radius:4px;
            margin-bottom:20px; font-size:12px;
            color:#7a5c00; line-height:1.6;
        }

        .spinner {
            border:3px solid #f3f3f3;
            border-top:3px solid #043915;
            border-radius:50%; width:30px; height:30px;
            animation:spin 1s linear infinite;
            margin:0 auto 10px;
        }
        @keyframes spin { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }

        .step-indicator {
            display:flex; gap:12px; margin-bottom:20px; justify-content:center;
        }
        .step {
            width:40px; height:40px; border-radius:50%;
            background:#e0e0e0; color:#999;
            display:flex; align-items:center; justify-content:center;
            font-weight:600; font-size:14px; transition:all .3s ease;
        }
        .step.active    { background:#043915; color:white; }
        .step.completed { background:#4caf50; color:white; }
    </style>
</head>
<body>
<div class="modal-overlay">
  <div class="modal">

    <div class="modal-header">
      <h2>🔐 Secure Your Account</h2>
      <p>Update your credentials to access your dashboard</p>
    </div>

    <div class="modal-content">

      <?php if ($errorMessage): ?>
        <div class="alert alert-danger show"><?= htmlspecialchars($errorMessage) ?></div>
      <?php endif; ?>
      <?php if ($successMessage): ?>
        <div class="alert alert-success show"><?= htmlspecialchars($successMessage) ?></div>
      <?php endif; ?>

      <div class="step-indicator">
        <div class="step active" id="step1">1</div>
        <div class="step"        id="step2">2</div>
        <div class="step"        id="step3">3</div>
      </div>

      <div class="current-user">
        <small>Current Account</small>
        <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong>
      </div>

      <!-- ── SECTION 1: ENTER NEW CREDENTIALS ─────────────────────── -->
      <div class="form-section active" id="section1">
        <div class="info-box">
          <strong>📌 Notice:</strong> For security, you must update your credentials before proceeding.
        </div>

        <div class="form-group">
          <label for="newName">New Username / Full Name</label>
          <div class="input-wrapper">
            <input type="text" id="newName" placeholder="Enter new username (min 8 characters)" required minlength="8">
          </div>
          <div class="requirements">
            <div class="requirement unmet" id="nameReq">
              <div class="requirement-icon">✓</div>
              <span>At least 8 characters</span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="newPassword">New Password</label>
          <div class="input-wrapper">
            <input type="password" id="newPassword" placeholder="Enter strong password" required>
            <button type="button" class="toggle-btn" onclick="togglePass('newPassword', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <div class="password-strength" id="strengthIndicator">
            <span id="strengthText">Weak</span>
            <div class="strength-bar"><div class="strength-fill"></div></div>
          </div>
          <div class="requirements">
            <div class="requirement unmet" id="passLen">
              <div class="requirement-icon">✓</div><span>At least 8 characters</span>
            </div>
            <div class="requirement unmet" id="passUpper">
              <div class="requirement-icon">✓</div><span>Uppercase letter (A-Z)</span>
            </div>
            <div class="requirement unmet" id="passLower">
              <div class="requirement-icon">✓</div><span>Lowercase letter (a-z)</span>
            </div>
            <div class="requirement unmet" id="passNum">
              <div class="requirement-icon">✓</div><span>Number (0-9)</span>
            </div>
            <div class="requirement unmet" id="passSpecial">
              <div class="requirement-icon">✓</div><span>Special character (!@#$%^&*)</span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <div class="input-wrapper">
            <input type="password" id="confirmPassword" placeholder="Re-enter password" required>
            <button type="button" class="toggle-btn" onclick="togglePass('confirmPassword', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <div style="color:#d32f2f;font-size:12px;margin-top:4px;display:none" id="matchError">
            Passwords do not match
          </div>
        </div>

        <div class="button-group">
          <button type="button" class="btn btn-secondary" onclick="logout()">Cancel</button>
          <button type="button" class="btn btn-primary" id="sendOtpBtn"
                  onclick="validateAndSendOtp()" disabled>Send OTP</button>
        </div>
      </div>

      <!-- ── SECTION 2: VERIFY OTP ─────────────────────────────────── -->
      <div class="form-section" id="section2">
        <div class="alert alert-info show">
          <strong>✉️ OTP Sent:</strong> Check your email for the verification code. It expires in 10 minutes.
        </div>

        <div class="form-group">
          <label>Enter OTP Code</label>
          <div class="otp-input-group" id="otpGroup">
            <input class="otp-input" type="text" maxlength="1" inputmode="numeric" data-index="0" autocomplete="off">
            <input class="otp-input" type="text" maxlength="1" inputmode="numeric" data-index="1" autocomplete="off">
            <input class="otp-input" type="text" maxlength="1" inputmode="numeric" data-index="2" autocomplete="off">
            <input class="otp-input" type="text" maxlength="1" inputmode="numeric" data-index="3" autocomplete="off">
            <input class="otp-input" type="text" maxlength="1" inputmode="numeric" data-index="4" autocomplete="off">
            <input class="otp-input" type="text" maxlength="1" inputmode="numeric" data-index="5" autocomplete="off">
          </div>
        </div>

        <div class="otp-resend">
          <p>Didn't receive the code?
            <span class="resend-link disabled" id="resendLink" onclick="resendOtp()">Resend OTP</span>
          </p>
          <p id="resendNote" style="font-size:12px;color:#999;margin-top:4px">
            You can request a new code in <span id="resendCountdown">60</span>s
          </p>
        </div>

        <div class="button-group">
          <button type="button" class="btn btn-secondary" onclick="goBack()">Back</button>
          <button type="button" class="btn btn-primary" id="verifyOtpBtn"
                  onclick="verifyOtp()" disabled>Verify &amp; Continue</button>
        </div>
      </div>

      <!-- ── SECTION 3: SUCCESS ─────────────────────────────────────── -->
      <div class="form-section" id="section3">
        <div style="text-align:center;padding:40px 0">
          <div style="font-size:60px;margin-bottom:20px">✅</div>
          <h3 style="color:#333;margin-bottom:10px;font-size:20px">Credentials Updated!</h3>
          <p style="color:#666;margin-bottom:30px">Your account has been secured.</p>
          <div id="redirectLoader">
            <div class="spinner"></div>
            <p style="color:#043915;margin-top:10px">Redirecting to dashboard...</p>
          </div>
        </div>
      </div>

    </div><!-- /.modal-content -->
  </div><!-- /.modal -->
</div><!-- /.modal-overlay -->

<script>
// ── App-root URL resolved by PHP (no trailing slash) ─────────────────────────
const APP_ROOT = <?= json_encode(rtrim($appRoot, '/')) ?>;

// Controller endpoints — inside public/ so Apache allows direct access
const SEND_OTP_URL   = APP_ROOT + '/public/api/send-otp.php';
const VERIFY_OTP_URL = APP_ROOT + '/public/api/verify-otp-and-update.php';
const DASHBOARD_URL  = APP_ROOT + '/public/teacher-dashboard';

// State
const otpData = {
    newName     : '',
    newPassword : '',
    userId      : <?= (int)($_SESSION['user']['user_id'] ?? 0) ?>,
    userEmail   : <?= json_encode($_SESSION['user']['email'] ?? '') ?>
};

let timerInterval = null;

// ── Password toggle ───────────────────────────────────────────────────────────
function togglePass(fieldId, btn) {
    const f = document.getElementById(fieldId);
    const i = btn.querySelector('i');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye');
    i.classList.toggle('fa-eye-slash');
}

// ── Requirement helpers ───────────────────────────────────────────────────────
function setReq(id, met) {
    const el = document.getElementById(id);
    el.classList.toggle('met',   met);
    el.classList.toggle('unmet', !met);
}

// ── Live validation ───────────────────────────────────────────────────────────
document.getElementById('newName').addEventListener('input', function () {
    setReq('nameReq', this.value.length >= 8);
    updateSendBtn();
});

document.getElementById('newPassword').addEventListener('input', function () {
    const p = this.value;
    const r = {
        len     : p.length >= 8,
        upper   : /[A-Z]/.test(p),
        lower   : /[a-z]/.test(p),
        num     : /[0-9]/.test(p),
        special : /[!@#$%^&*()\-_=+\[\]{};':"\\|,.<>/?]/.test(p)
    };
    setReq('passLen',     r.len);
    setReq('passUpper',   r.upper);
    setReq('passLower',   r.lower);
    setReq('passNum',     r.num);
    setReq('passSpecial', r.special);

    const si  = document.getElementById('strengthIndicator');
    const st  = document.getElementById('strengthText');
    const cnt = Object.values(r).filter(Boolean).length;
    si.classList.remove('weak', 'fair', 'strong');
    if      (cnt <= 2) { si.classList.add('weak');   st.textContent = 'Weak';   }
    else if (cnt <= 4) { si.classList.add('fair');   st.textContent = 'Fair';   }
    else               { si.classList.add('strong'); st.textContent = 'Strong'; }

    updateSendBtn();
});

document.getElementById('confirmPassword').addEventListener('input', function () {
    const mismatch = this.value !== document.getElementById('newPassword').value;
    document.getElementById('matchError').style.display = mismatch ? 'block' : 'none';
    updateSendBtn();
});

function isPasswordStrong(p) {
    return p.length >= 8
        && /[A-Z]/.test(p)
        && /[a-z]/.test(p)
        && /[0-9]/.test(p)
        && /[!@#$%^&*()\-_=+\[\]{};':"\\|,.<>/?]/.test(p);
}

function updateSendBtn() {
    const name    = document.getElementById('newName').value;
    const pass    = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    document.getElementById('sendOtpBtn').disabled = !(
        name.length >= 8 &&
        isPasswordStrong(pass) &&
        confirm === pass && confirm !== ''
    );
}

// ── Step 1 → send OTP ────────────────────────────────────────────────────────
function validateAndSendOtp() {
    const name    = document.getElementById('newName').value.trim();
    const pass    = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;

    if (name.length < 8)        { alert('Username must be at least 8 characters.'); return; }
    if (!isPasswordStrong(pass)) { alert('Password does not meet the requirements.'); return; }
    if (pass !== confirm)        { alert('Passwords do not match.'); return; }

    otpData.newName     = name;
    otpData.newPassword = pass;
    doSendOtp();
}

function doSendOtp() {
    const btn = document.getElementById('sendOtpBtn');
    btn.disabled    = true;
    btn.textContent = 'Sending…';

    fetch(SEND_OTP_URL, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : new URLSearchParams({
            userId : otpData.userId,
            email  : otpData.userEmail
        })
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            showSection('section2');
            markStep(1, 2);
            startTimer();
        } else {
            alert('Failed to send OTP: ' + data.message);
            btn.disabled    = false;
            btn.textContent = 'Send OTP';
        }
    })
    .catch(err => {
        console.error('Send OTP error:', err);
        alert('Could not reach the server.\n\nEndpoint tried: ' + SEND_OTP_URL + '\n\nDetails: ' + err.message);
        btn.disabled    = false;
        btn.textContent = 'Send OTP';
    });
}

function resendOtp() {
    const lnk = document.getElementById('resendLink');
    if (lnk.classList.contains('disabled')) return;
    lnk.classList.add('disabled');

    // Clear existing OTP inputs
    document.querySelectorAll('.otp-input').forEach(i => {
        i.value = '';
        i.classList.remove('filled');
    });
    document.getElementById('verifyOtpBtn').disabled = true;
    doSendOtp();
}

// ── Resend cooldown (60s) — OTP itself is valid for 10 minutes ───────────────
function startTimer() {
    clearInterval(timerInterval);
    let secs = 60;
    const countdownEl = document.getElementById('resendCountdown');
    const noteEl      = document.getElementById('resendNote');
    const resendEl    = document.getElementById('resendLink');
    resendEl.classList.add('disabled');
    if (countdownEl) countdownEl.textContent = secs;
    if (noteEl) noteEl.style.display = 'block';

    timerInterval = setInterval(() => {
        secs--;
        if (countdownEl) countdownEl.textContent = secs;
        if (secs <= 0) {
            clearInterval(timerInterval);
            resendEl.classList.remove('disabled');
            if (noteEl) noteEl.style.display = 'none';
        }
    }, 1000);
}

// ── OTP inputs ────────────────────────────────────────────────────────────────
document.querySelectorAll('.otp-input').forEach((input, idx, all) => {
    input.addEventListener('input', function () {
        if (!/^\d$/.test(this.value)) { this.value = ''; return; }
        this.classList.add('filled');
        if (idx < 5) all[idx + 1].focus();
        updateVerifyBtn();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !this.value && idx > 0) {
            all[idx - 1].focus();
            all[idx - 1].value = '';
            all[idx - 1].classList.remove('filled');
            updateVerifyBtn();
        }
    });

    input.addEventListener('paste', function (e) {
        e.preventDefault();
        const digits = e.clipboardData.getData('text').replace(/\D/g, '').split('');
        digits.forEach((d, i) => {
            if (idx + i < all.length) {
                all[idx + i].value = d;
                all[idx + i].classList.add('filled');
            }
        });
        updateVerifyBtn();
    });
});

function updateVerifyBtn() {
    const complete = Array.from(document.querySelectorAll('.otp-input'))
                         .every(i => i.value.length === 1);
    document.getElementById('verifyOtpBtn').disabled = !complete;
}

// ── Step 2 → verify OTP ──────────────────────────────────────────────────────
function verifyOtp() {
    const code = Array.from(document.querySelectorAll('.otp-input'))
                      .map(i => i.value).join('');

    const btn = document.getElementById('verifyOtpBtn');
    btn.disabled    = true;
    btn.textContent = 'Verifying…';

    fetch(VERIFY_OTP_URL, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
        body    : new URLSearchParams({
            userId      : otpData.userId,
            otpCode     : code,
            newName     : otpData.newName,
            newPassword : otpData.newPassword
        })
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            markStep(2, 3);
            showSection('section3');
            setTimeout(() => { window.location.href = DASHBOARD_URL; }, 2000);
        } else {
            alert('OTP verification failed: ' + data.message);
            btn.disabled    = false;
            btn.textContent = 'Verify & Continue';
        }
    })
    .catch(err => {
        console.error('Verify OTP error:', err);
        alert('Could not reach the server.\n\nDetails: ' + err.message);
        btn.disabled    = false;
        btn.textContent = 'Verify & Continue';
    });
}

// ── Navigation helpers ────────────────────────────────────────────────────────
function showSection(id) {
    document.querySelectorAll('.form-section')
            .forEach(s => s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

function markStep(from, to) {
    document.getElementById('step' + from).classList.add('completed');
    document.getElementById('step' + to  ).classList.add('active');
}

function goBack() {
    showSection('section1');
    document.getElementById('step2').classList.remove('active', 'completed');
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = APP_ROOT + '/public/log-in';
    }
}

// Initial button state
updateSendBtn();
</script>
</body>
</html>