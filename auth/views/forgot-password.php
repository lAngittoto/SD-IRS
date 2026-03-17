<?php
ob_start();
$appRoot = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>

<div class="fp-wrap">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="fp-card">

        <!-- Header -->
        <div class="fp-header">
            <div class="fp-lock-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h1 class="fp-title">Reset Password</h1>
            <p class="fp-subtitle">Recover your account securely</p>
        </div>

        <!-- Wizard -->
        <div class="fp-wizard">
            <div class="wizard-track">
                <div class="wizard-line-fill" id="wizardFill"></div>
            </div>
            <?php foreach([['1','Email'],['2','Verify'],['3','OTP'],['4','Password']] as $s): ?>
            <div class="wizard-step <?= $s[0]==='1'?'active':'' ?>" data-step="<?= $s[0] ?>">
                <div class="wizard-bubble"><?= $s[0] ?></div>
                <span class="wizard-label"><?= $s[1] ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Body -->
        <div class="fp-body">
            <div id="messageBox"></div>

            <!-- SLIDE 1: Email -->
            <div class="slide active" id="slide1">
                <div class="fp-field">
                    <label class="fp-label">Email Address</label>
                    <div class="fp-input-wrap">
                        <svg class="fp-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,12 2,6"></polyline></svg>
                        <input type="email" id="email_input" placeholder="your.email@school.com" class="fp-input">
                    </div>
                </div>
                <button class="fp-btn fp-btn-primary" onclick="goToStep2()">
                    <span>Find My Account</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </div>

            <!-- SLIDE 2: Verify -->
            <div class="slide" id="slide2">
                <div class="fp-field">
                    <label class="fp-label">Full Name</label>
                    <div class="fp-input-wrap">
                        <svg class="fp-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <input type="text" id="username_input" placeholder="Enter your full name" class="fp-input">
                    </div>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Teacher ID</label>
                    <div class="fp-input-wrap">
                        <svg class="fp-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        <input type="text" id="teacher_id_input" placeholder="e.g. T-001 or N/A" class="fp-input">
                    </div>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Confirm Email</label>
                    <div class="fp-input-wrap">
                        <svg class="fp-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,12 2,6"></polyline></svg>
                        <input type="email" id="teacher_email_input" placeholder="Confirm your email" class="fp-input">
                    </div>
                </div>
                <button class="fp-btn fp-btn-primary" onclick="verifyAndSendOTP()">
                    <span>Verify &amp; Send OTP</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
                <button class="fp-btn fp-btn-ghost" onclick="goBack(1)">Back</button>
            </div>

            <!-- SLIDE 3: OTP -->
            <div class="slide" id="slide3">
                <p class="fp-otp-hint">Enter the 6-digit code sent to your email</p>
                <div class="fp-otp-row" id="otpContainer">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" disabled>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" disabled>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" disabled>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" disabled>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" disabled>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" disabled>
                </div>
                <div class="fp-timer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Time left: <strong id="timer">10:00</strong></span>
                </div>
                <button class="fp-btn fp-btn-primary" id="verifyBtn" onclick="verifyOTP()">
                    <span>Verify OTP</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button class="fp-btn fp-btn-outline fp-btn-sm" id="resendBtn" onclick="resendOTP()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 .49-4.7"></path></svg>
                    Resend Code
                </button>
                <button class="fp-btn fp-btn-ghost" onclick="goBack(2)">Back</button>
            </div>

            <!-- SLIDE 4: New Password -->
            <div class="slide" id="slide4">
                <div class="fp-field">
                    <label class="fp-label">New Password</label>
                    <div class="fp-input-wrap">
                        <svg class="fp-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="new_password" placeholder="Create a strong password" class="fp-input" oninput="checkPasswordStrength()">
                        <button type="button" class="fp-eye-btn" onclick="toggleEye('new_password','eye1')" tabindex="-1">
                            <svg id="eye1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <div class="fp-strength-wrap">
                        <div class="fp-strength-track"><div class="fp-strength-bar" id="strengthBar"></div></div>
                        <span class="fp-strength-label" id="strengthLabel"></span>
                    </div>
                    <div class="fp-reqs">
                        <div class="fp-req" id="req-length"><div class="fp-req-dot"></div><span>At least 8 characters</span></div>
                        <div class="fp-req" id="req-upper"><div class="fp-req-dot"></div><span>One uppercase (A–Z)</span></div>
                        <div class="fp-req" id="req-lower"><div class="fp-req-dot"></div><span>One lowercase (a–z)</span></div>
                        <div class="fp-req" id="req-number"><div class="fp-req-dot"></div><span>One number (0–9)</span></div>
                    </div>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Confirm Password</label>
                    <div class="fp-input-wrap">
                        <svg class="fp-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="confirm_password" placeholder="Re-enter your password" class="fp-input" oninput="checkConfirm()">
                        <button type="button" class="fp-eye-btn" onclick="toggleEye('confirm_password','eye2')" tabindex="-1">
                            <svg id="eye2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <p class="fp-match-hint hidden" id="matchHint"></p>
                </div>
                <button class="fp-btn fp-btn-primary" id="resetBtn" onclick="resetPassword()">
                    <span>Reset Password</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button class="fp-btn fp-btn-ghost" onclick="goBack(3)">Back</button>
            </div>

        </div><!-- /.fp-body -->

        <div class="fp-footer">
            Remember your password?
            <a href="<?php echo $appRoot; ?>/index.php" class="fp-login-link">Login here</a>
        </div>

    </div><!-- /.fp-card -->
</div>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --green:       #043915;
    --green-dark:  #032a0f;
    --green-mid:   #065c22;
    --yellow:      #f8c922;
    --yellow-dark: #e6b70f;
    --yellow-pale: #fffbea;
}

/* ── Wrap ── */
.fp-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: var(--green-dark);
    position: relative;
    overflow: hidden;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

/* Blobs */
.blob { position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none; }
.blob-1 { width: 520px; height: 520px; background: var(--green);     opacity: .6;  top: -150px; left: -120px; }
.blob-2 { width: 380px; height: 380px; background: var(--green-mid); opacity: .55; bottom: -90px; right: -80px; }
.blob-3 { width: 240px; height: 240px; background: var(--yellow);    opacity: .08; top: 35%; left: 56%; }

/* ── Card ── */
.fp-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 460px;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 32px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.05);
    overflow: hidden;
}

/* ── Header ── */
.fp-header {
    background: linear-gradient(135deg, var(--green-dark) 0%, var(--green) 100%);
    padding: 2.25rem 2rem 2rem;
    text-align: center;
    position: relative;
}
.fp-header::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--yellow);
}
.fp-lock-icon {
    width: 58px; height: 58px;
    background: var(--yellow);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    color: var(--green);
    box-shadow: 0 8px 24px rgba(248,201,34,.4);
}
.fp-title  { font-size: 1.65rem; font-weight: 800; color: #fff; letter-spacing: -.02em; }
.fp-subtitle { font-size: .825rem; color: rgba(255,255,255,.5); margin-top: .3rem; }

/* ── Wizard ── */
.fp-wizard {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1.5rem 2.5rem 0;
    position: relative;
}
.wizard-track {
    position: absolute;
    top: 2.55rem; left: 3.8rem; right: 3.8rem;
    height: 3px;
    background: #e5e7eb;
    border-radius: 99px;
    overflow: hidden;
    z-index: 0;
}
.wizard-line-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--green), var(--yellow));
    width: 0%;
    transition: width .4s ease;
    border-radius: 99px;
}
.wizard-step {
    display: flex; flex-direction: column; align-items: center;
    gap: .5rem; position: relative; z-index: 1;
}
.wizard-bubble {
    width: 36px; height: 36px; border-radius: 50%;
    background: #e5e7eb; color: #9ca3af;
    font-size: .75rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    border: 2.5px solid #e5e7eb;
    transition: all .3s ease;
}
.wizard-step.active .wizard-bubble {
    background: var(--green); border-color: var(--green);
    color: var(--yellow); box-shadow: 0 4px 14px rgba(4,57,21,.35);
}
.wizard-step.done .wizard-bubble {
    background: var(--green); border-color: var(--green); color: var(--yellow);
}
.wizard-step.done .wizard-bubble::before { content: '✓'; font-size: .8rem; }
.wizard-label { font-size: .64rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .07em; transition: color .3s; }
.wizard-step.active .wizard-label { color: var(--green); }
.wizard-step.done   .wizard-label { color: var(--green-mid); }

/* ── Body ── */
.fp-body { padding: 1.75rem 2rem 1.5rem; }

/* Slides */
.slide { display: none; animation: slideUp .3s ease; }
.slide.active { display: block; }
@keyframes slideUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }

/* Messages */
#messageBox { margin-bottom: 0; }
.fp-msg {
    padding: .7rem 1rem; border-radius: 10px;
    font-size: .8rem; font-weight: 600; margin-bottom: 1rem;
    display: flex; align-items: center; gap: .5rem;
    animation: slideUp .25s ease;
}
.fp-msg.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.fp-msg.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.fp-msg.info    { background: var(--yellow-pale); color: var(--green); border: 1px solid var(--yellow); }

/* Fields */
.fp-field { margin-bottom: 1rem; }
.fp-label { display: block; font-size: .71rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .45rem; }
.fp-input-wrap { position: relative; display: flex; align-items: center; }
.fp-input-icon { position: absolute; left: .85rem; width: 16px; height: 16px; color: #9ca3af; pointer-events: none; }
.fp-input {
    width: 100%; padding: .75rem 2.75rem .75rem 2.5rem;
    border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-family: inherit; font-size: .875rem; color: #111827;
    background: #f9fafb; outline: none;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.fp-input:focus { border-color: var(--green); background: #fff; box-shadow: 0 0 0 3px rgba(4,57,21,.1); }
.fp-input::placeholder { color: #d1d5db; }

.fp-eye-btn {
    position: absolute; right: .75rem;
    background: none; border: none; cursor: pointer; color: #9ca3af;
    padding: .25rem; display: flex; align-items: center; justify-content: center;
    border-radius: 6px; transition: color .2s;
}
.fp-eye-btn:hover { color: var(--green); }

/* Strength */
.fp-strength-wrap { display: flex; align-items: center; gap: .6rem; margin-top: .5rem; }
.fp-strength-track { flex: 1; height: 4px; background: #e5e7eb; border-radius: 99px; overflow: hidden; }
.fp-strength-bar { height: 100%; width: 0%; border-radius: 99px; transition: width .35s ease, background .35s ease; }
.fp-strength-label { font-size: .7rem; font-weight: 700; min-width: 42px; text-align: right; }

/* Requirements */
.fp-reqs { margin-top: .7rem; display: grid; grid-template-columns: 1fr 1fr; gap: .3rem .75rem; }
.fp-req { display: flex; align-items: center; gap: .4rem; font-size: .71rem; color: #9ca3af; font-weight: 500; transition: color .2s; }
.fp-req-dot { width: 7px; height: 7px; border-radius: 50%; background: #d1d5db; flex-shrink: 0; transition: background .2s; }
.fp-req.met { color: var(--green); }
.fp-req.met .fp-req-dot { background: var(--green); }

/* Match hint */
.fp-match-hint { font-size: .72rem; font-weight: 600; margin-top: .4rem; }
.fp-match-hint.match-ok  { color: var(--green); }
.fp-match-hint.match-err { color: #dc2626; }
.fp-match-hint.hidden { display: none; }

/* Buttons */
.fp-btn {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    width: 100%; padding: .8rem 1.25rem; border-radius: 10px;
    font-family: inherit; font-size: .875rem; font-weight: 700;
    cursor: pointer; border: none; transition: all .2s ease;
    margin-top: .6rem; letter-spacing: .02em;
}
.fp-btn-primary {
    background: var(--yellow); color: var(--green);
    box-shadow: 0 4px 14px rgba(248,201,34,.4);
}
.fp-btn-primary:hover:not(:disabled) {
    background: var(--yellow-dark);
    box-shadow: 0 6px 20px rgba(248,201,34,.55);
    transform: translateY(-1px);
}
.fp-btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }
.fp-btn-ghost { background: #f3f4f6; color: #6b7280; font-size: .8rem; }
.fp-btn-ghost:hover { background: #e5e7eb; color: #374151; }
.fp-btn-outline { background: transparent; color: var(--green); border: 1.5px solid var(--green); font-size: .8rem; }
.fp-btn-outline:hover { background: rgba(4,57,21,.06); }
.fp-btn-sm { padding: .5rem 1rem; font-size: .78rem; }

/* OTP */
.fp-otp-hint { text-align: center; font-size: .82rem; color: #6b7280; margin-bottom: 1.25rem; font-weight: 500; }
.fp-otp-row  { display: flex; gap: .5rem; justify-content: center; margin-bottom: 1rem; }
.otp-input {
    width: 46px; height: 54px; text-align: center;
    font-size: 1.4rem; font-weight: 800;
    border: 2px solid #e5e7eb; border-radius: 10px;
    color: var(--green); background: #f9fafb; outline: none;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.otp-input:focus  { border-color: var(--green); background: #fff; box-shadow: 0 0 0 3px rgba(4,57,21,.1); }
.otp-input.filled { border-color: var(--green); background: #f0fdf4; }
.otp-input:disabled { background: #f3f4f6; opacity: .5; }

.fp-timer { display: flex; align-items: center; justify-content: center; gap: .4rem; font-size: .8rem; font-weight: 600; color: #6b7280; margin-bottom: .75rem; }
#timer { color: var(--green); font-weight: 800; }

/* Footer */
.fp-footer { text-align: center; padding: 1rem 2rem 1.5rem; font-size: .8rem; color: #9ca3af; font-weight: 500; border-top: 1px solid #f3f4f6; }
.fp-login-link { color: var(--green); font-weight: 700; text-decoration: none; margin-left: .25rem; }
.fp-login-link:hover { text-decoration: underline; }
</style>

<script>
const APP_ROOT      = '<?php echo $appRoot; ?>';
const controllerURL = APP_ROOT + '/api/forgot-password-controller.php';
const sendOTPURL    = APP_ROOT + '/api/send-otp-forgot.php';

let otpToken = '', resetId = '', timerInterval = null;

function setWizardStep(step) {
    document.querySelectorAll('.wizard-step').forEach(el => {
        const s = parseInt(el.dataset.step);
        const b = el.querySelector('.wizard-bubble');
        el.classList.remove('active','done');
        if (s === step)      { el.classList.add('active'); b.textContent = s; }
        else if (s < step)   { el.classList.add('done');   b.textContent = ''; }
        else                 { b.textContent = s; }
    });
    document.getElementById('wizardFill').style.width = (((step-1)/3)*100) + '%';
}

function showSlide(n) {
    document.querySelectorAll('.slide').forEach(s => s.classList.remove('active'));
    document.getElementById('slide'+n).classList.add('active');
    setWizardStep(n);
    document.getElementById('messageBox').innerHTML = '';
}

const ICONS = {
    success: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`,
    error:   `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`,
    info:    `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`,
};
function showMessage(text, type='info') {
    document.getElementById('messageBox').innerHTML = `<div class="fp-msg ${type}">${ICONS[type]||''}<span>${text}</span></div>`;
}

function goToStep2() {
    const email = document.getElementById('email_input').value.trim();
    if (!email || !isValidEmail(email)) { showMessage('Please enter a valid email address.','error'); return; }
    fetch(controllerURL, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=step1_findEmail&email='+encodeURIComponent(email) })
    .then(r=>r.json()).then(d => { if(d.success){showSlide(2);showMessage('Account found! Enter your verification details.','success');}else showMessage(d.message,'error'); })
    .catch(e=>showMessage('Error: '+e.message,'error'));
}

function verifyAndSendOTP() {
    const name=document.getElementById('username_input').value.trim(), tid=document.getElementById('teacher_id_input').value.trim(), email=document.getElementById('teacher_email_input').value.trim();
    if(!name||!tid||!email){showMessage('All fields are required.','error');return;}
    fetch(sendOTPURL, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=verifyAndSendOTP&teacher_name='+encodeURIComponent(name)+'&teacher_id='+encodeURIComponent(tid)+'&teacher_email='+encodeURIComponent(email) })
    .then(r=>r.json()).then(d => {
        if(d.success){ otpToken=d.token; showSlide(3); document.querySelectorAll('.otp-input').forEach(i=>i.disabled=false); showMessage('OTP sent to '+d.email,'success'); startTimer(); setTimeout(()=>document.querySelector('.otp-input:not([disabled])').focus(),120); }
        else showMessage(d.message,'error');
    }).catch(e=>showMessage('Error: '+e.message,'error'));
}

function verifyOTP() {
    const otp=Array.from(document.querySelectorAll('.otp-input')).map(i=>i.value).join('');
    if(otp.length!==6){showMessage('Please enter all 6 digits.','error');return;}
    if(!otpToken){showMessage('Token missing. Please resend OTP.','error');return;}
    const btn=document.getElementById('verifyBtn'); btn.disabled=true; btn.querySelector('span').textContent='Verifying…';
    fetch(controllerURL, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=step3_verifyOTP&token='+encodeURIComponent(otpToken)+'&otp='+encodeURIComponent(otp) })
    .then(r=>r.json()).then(d => {
        if(d.success){ resetId=d.reset_id; clearInterval(timerInterval); showMessage('OTP verified! Create your new password.','success'); setTimeout(()=>showSlide(4),500); }
        else{ showMessage(d.message,'error'); btn.disabled=false; btn.querySelector('span').textContent='Verify OTP'; }
    }).catch(e=>{ showMessage('Error: '+e.message,'error'); btn.disabled=false; btn.querySelector('span').textContent='Verify OTP'; });
}

function resetPassword() {
    const np=document.getElementById('new_password').value, cp=document.getElementById('confirm_password').value;
    if(np!==cp){showMessage('Passwords do not match.','error');return;}
    const btn=document.getElementById('resetBtn'); btn.disabled=true; btn.querySelector('span').textContent='Resetting…';
    fetch(controllerURL, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=step4_resetPassword&new_password='+encodeURIComponent(np)+'&confirm_password='+encodeURIComponent(cp)+'&reset_id='+encodeURIComponent(resetId) })
    .then(r=>r.json()).then(d => {
        if(d.success){ showMessage(d.message,'success'); setTimeout(()=>window.location.href=APP_ROOT+'/index.php',2200); }
        else{ showMessage(d.message,'error'); btn.disabled=false; btn.querySelector('span').textContent='Reset Password'; }
    }).catch(e=>{ showMessage('Error: '+e.message,'error'); btn.disabled=false; btn.querySelector('span').textContent='Reset Password'; });
}

function resendOTP() {
    const btn=document.getElementById('resendBtn'); btn.disabled=true;
    fetch(controllerURL, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=resendOTP' })
    .then(r=>r.json()).then(d => {
        if(d.success){ otpToken=d.token; document.querySelectorAll('.otp-input').forEach(i=>{i.value='';i.classList.remove('filled');}); startTimer(); showMessage('New OTP sent to your email.','success'); }
        else showMessage(d.message,'error'); btn.disabled=false;
    }).catch(e=>{ showMessage('Error: '+e.message,'error'); btn.disabled=false; });
}

function goBack(step){ clearInterval(timerInterval); showSlide(step); }

function startTimer() {
    let t=600; clearInterval(timerInterval);
    timerInterval=setInterval(()=>{ const m=Math.floor(t/60),s=t%60; document.getElementById('timer').textContent=String(m).padStart(2,'0')+':'+String(s).padStart(2,'0'); if(t<=0){clearInterval(timerInterval);showMessage('OTP expired. Please request a new one.','error');} t--; },1000);
}

function toggleEye(inputId,iconId) {
    const inp=document.getElementById(inputId), icon=document.getElementById(iconId), show=inp.type==='password';
    inp.type=show?'text':'password';
    icon.innerHTML=show
        ?`<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`
        :`<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
}

function checkPasswordStrength() {
    const pw=document.getElementById('new_password').value;
    const checks={length:pw.length>=8,upper:/[A-Z]/.test(pw),lower:/[a-z]/.test(pw),number:/[0-9]/.test(pw)};
    ['length','upper','lower','number'].forEach(k=>document.getElementById('req-'+k).classList.toggle('met',checks[k]));
    const count=Object.values(checks).filter(Boolean).length;
    const bar=document.getElementById('strengthBar'), lbl=document.getElementById('strengthLabel');
    bar.style.width=((count/4)*100)+'%';
    const cfg=[null,{bg:'#ef4444',txt:'Weak',color:'#ef4444'},{bg:'#f59e0b',txt:'Fair',color:'#f59e0b'},{bg:'#3b82f6',txt:'Good',color:'#3b82f6'},{bg:'#043915',txt:'Strong',color:'#043915'}];
    if(count>0&&cfg[count]){bar.style.background=cfg[count].bg;lbl.textContent=cfg[count].txt;lbl.style.color=cfg[count].color;}
    else{bar.style.background='#e5e7eb';lbl.textContent='';}
    checkConfirm();
}

function checkConfirm() {
    const np=document.getElementById('new_password').value, cp=document.getElementById('confirm_password').value, hint=document.getElementById('matchHint');
    if(!cp){hint.className='fp-match-hint hidden';return;}
    if(np===cp){hint.textContent='✓ Passwords match';hint.className='fp-match-hint match-ok';}
    else{hint.textContent='✗ Passwords do not match';hint.className='fp-match-hint match-err';}
}

document.addEventListener('DOMContentLoaded',()=>{
    const con=document.getElementById('otpContainer');
    con.addEventListener('input',e=>{
        const inputs=[...document.querySelectorAll('.otp-input')], idx=inputs.indexOf(e.target);
        e.target.value=e.target.value.replace(/\D/g,'').slice(-1);
        e.target.classList.toggle('filled',e.target.value!=='');
        if(e.target.value&&idx<inputs.length-1) inputs[idx+1].focus();
    });
    con.addEventListener('keydown',e=>{
        const inputs=[...document.querySelectorAll('.otp-input')], idx=inputs.indexOf(e.target);
        if(e.key==='Backspace'&&!e.target.value&&idx>0){inputs[idx-1].focus();inputs[idx-1].value='';inputs[idx-1].classList.remove('filled');}
        if(e.key==='Enter') verifyOTP();
    });
    con.addEventListener('paste',e=>{
        e.preventDefault();
        const d=e.clipboardData.getData('text').replace(/\D/g,'').slice(0,6);
        const inputs=[...document.querySelectorAll('.otp-input')];
        d.split('').forEach((c,i)=>{if(inputs[i]){inputs[i].value=c;inputs[i].classList.add('filled');}});
        (inputs[d.length]||inputs[inputs.length-1]).focus();
    });
    setWizardStep(1);
});

function isValidEmail(e){return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);}

</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/structure.php';
?>