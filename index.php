<?php
// ── index.php ── Visitor login + welcome screen ───────────────────────────────
session_start();

// If admin is already logged in, redirect to dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="manifest" href="/NEUProject/manifest.json">
<meta name="theme-color" content="#2d6a4f">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="NEU Library">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEU Library Visitor Log</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--navy:#0d1f0d;--navy2:#122012;--teal:#16a34a;--teal-light:#22c55e;--gold:#dc2626;--white:#ffffff;--gray:#86a886;--danger:#dc2626;--success:#16a34a;--card-bg:#122012;--input-bg:#1a2d1a;--border:#1e4a1e}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--navy);color:var(--white);min-height:100vh;overflow-x:hidden}
body::before{content:'';position:fixed;inset:0;background:url('assets/neu.jpg') center/cover;opacity:.18;pointer-events:none;z-index:0}
/* ── SCREENS ── */
.screen{display:none;position:relative;z-index:1}
.screen.active{display:flex;flex-direction:column;min-height:100vh}
/* ── LOGIN ── */
#loginScreen{align-items:center;justify-content:center;padding:2rem}
.login-card{background:var(--card-bg);border:1px solid var(--border);border-radius:20px;padding:3rem 2.5rem;width:100%;max-width:440px;box-shadow:0 30px 80px rgba(0,0,0,.5);animation:fadeUp .5s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}
.logo-row{display:flex;align-items:center;gap:.75rem;margin-bottom:2rem}
.logo-icon{width:48px;height:48px;border-radius:12px;overflow:hidden}
.logo-icon img{width:100%;height:100%;object-fit:cover;border-radius:12px}
.logo-text h1{font-family:'Playfair Display',serif;font-size:1.2rem;color:var(--white)}
.logo-text p{font-size:.75rem;color:var(--gray);margin-top:1px}
.login-title{font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:.5rem}
.login-sub{color:var(--gray);font-size:.9rem;margin-bottom:2rem}
.tabs{display:flex;background:var(--input-bg);border-radius:10px;padding:4px;margin-bottom:1.75rem}
.tab{flex:1;padding:.6rem;text-align:center;border-radius:7px;cursor:pointer;font-size:.85rem;font-weight:500;color:var(--gray);transition:.2s}
.tab.active{background:var(--teal);color:var(--white)}
.field{margin-bottom:1rem}
.field label{display:block;font-size:.8rem;color:var(--gray);margin-bottom:.4rem;font-weight:500}
.field input,.field select{width:100%;padding:.75rem 1rem;border-radius:10px;background:var(--input-bg);border:1px solid var(--border);color:var(--white);font-family:'DM Sans',sans-serif;font-size:.9rem;transition:.2s}
.field input:focus,.field select:focus{outline:none;border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.15)}
.field select option{background:var(--navy2)}
.btn{width:100%;padding:.85rem;border-radius:10px;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.95rem;transition:.2s}
.btn-primary{background:linear-gradient(135deg,#16a34a,#22c55e);color:var(--white)}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(22,163,74,.4)}
.blocked-msg{color:var(--danger);font-size:.85rem;text-align:center;margin-top:.75rem;display:none}
.err-msg{color:var(--danger);font-size:.85rem;text-align:center;margin-top:.75rem;display:none}
/* ── WELCOME ── */
#welcomeScreen{align-items:center;justify-content:center;text-align:center;padding:2rem}
.welcome-card{max-width:480px;animation:fadeUp .5s ease}
.welcome-icon img{width:100px;height:70px;object-fit:cover;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.4)}
.welcome-name{font-family:'Playfair Display',serif;font-size:2.4rem;color:#dc2626;margin-bottom:.5rem;margin-top:1.5rem}
.welcome-program{color:var(--teal);font-size:1rem;margin-bottom:.5rem}
.welcome-msg{font-size:1.6rem;font-weight:700;margin-bottom:.5rem}
.welcome-sub{color:var(--gray);font-size:.9rem;margin-bottom:.75rem}
.welcome-reason{display:inline-block;padding:.35rem .85rem;background:rgba(14,165,196,.15);border:1px solid var(--teal);border-radius:20px;font-size:.85rem;color:var(--teal);margin-bottom:2rem}
.countdown{color:var(--gray);font-size:.8rem;margin-top:1.5rem}
</style>
</head>
<body>

<!-- ── LOGIN SCREEN ── -->
<div id="loginScreen" class="screen active">
  <div class="login-card">
    <div class="logo-row">
      <div class="logo-icon"><img src="assets/neu.jpg" alt="NEU Logo"></div>
      <div class="logo-text">
        <h1>NEU Library</h1>
        <p>New Era University</p>
      </div>
    </div>
    <h2 class="login-title">Visitor Log</h2>
    <p class="login-sub">Sign in with your credentials to log your visit.</p>

    <div class="tabs">
      <div class="tab active" onclick="switchTab('visitor')">Visitor</div>
      <div class="tab"        onclick="switchTab('admin')">Admin</div>
    </div>

    <!-- VISITOR FORM -->
    <div id="visitorForm">
      <div class="field">
        <label>RFID or Institutional Email</label>
        <input type="text" id="rfidInput" placeholder="e.g. 2024-00123 or jdoe@neu.edu.ph">
      </div>
      <div class="field">
        <label>Reason for Visit</label>
        <select id="reasonInput">
          <option value="">Select reason…</option>
          <option>Reading</option>
          <option>Researching</option>
          <option>Use of Computer</option>
          <option>Meeting</option>
          <option>Printing / Scanning</option>
          <option>Borrowing Books</option>
          <option>Other</option>
        </select>
      </div>
      <div class="field">
        <label>Program / Department</label>
        <input type="text" id="programInput" placeholder="e.g. BSIT, BSCS, Faculty">
      </div>
      <div class="field">
  <label>I am a</label>
<select id="visitorType">
  <option value="Student">Student</option>
  <option value="Staff">Staff</option>
  <option value="Faculty">Faculty</option>
</select>
</div>
      <div class="field">
        <label>Full Name</label>
        <input type="text" id="nameInput" placeholder="Juan Dela Cruz">
      </div>
      <button class="btn btn-primary" onclick="visitorLogin()">Log Visit →</button>
      <p class="blocked-msg" id="blockedMsg">⛔ You are not allowed to use the library at this time.</p>
    </div>

    <!-- ADMIN FORM -->
    <div id="adminForm" style="display:none">
      <div class="field">
        <label>Admin Username</label>
        <input type="text" id="adminUser" placeholder="admin">
      </div>
      <div class="field">
        <label>Admin Password</label>
        <input type="password" id="adminPass" placeholder="••••••">
      </div>
      <button class="btn btn-primary" onclick="adminLogin()">Access Dashboard →</button>
      <p class="err-msg" id="adminErrMsg">Invalid credentials.</p>
    </div>
  </div>
</div>

<!-- ── WELCOME SCREEN ── -->
<div id="welcomeScreen" class="screen">
  <div class="welcome-card">
<div class="welcome-icon"><img src="assets/lib.jpg" alt="Library"></div>    <div class="welcome-name"    id="wName"></div>
    <div class="welcome-program" id="wProgram"></div>
    <div class="welcome-msg">Welcome to NEU Library!</div>
    <div class="welcome-sub"     id="wTime"></div>
    <div class="welcome-reason"  id="wReason"></div>
    <div class="countdown">Redirecting in <span id="countNum">5</span>s…</div>
  </div>
</div>

<script>
// ── tab toggle ─────────────────────────────────────────────────────────────────
function switchTab(tab) {
  document.querySelectorAll('.tab').forEach((t, i) =>
    t.classList.toggle('active', (i===0&&tab==='visitor')||(i===1&&tab==='admin'))
  );
  document.getElementById('visitorForm').style.display = tab==='visitor' ? 'block' : 'none';
  document.getElementById('adminForm').style.display   = tab==='admin'   ? 'block' : 'none';
  document.getElementById('blockedMsg').style.display  = 'none';
  document.getElementById('adminErrMsg').style.display = 'none';
}

// ── visitor login ──────────────────────────────────────────────────────────────
function visitorLogin() {
  const rfid    = document.getElementById('rfidInput').value.trim();
  const reason  = document.getElementById('reasonInput').value;
  const program = document.getElementById('programInput').value.trim();
  const visitorType = document.getElementById('visitorType').value;
  const name    = document.getElementById('nameInput').value.trim();

  if (!rfid || !reason || !program || !name) { alert('Please fill in all fields.'); return; }

  document.getElementById('blockedMsg').style.display = 'none';

const body = new URLSearchParams({ rfid, reason, program, name, visitor_type: visitorType });  fetch('api.php?action=visitor_login', { method:'POST', body })
    .then(r => r.json())
    .then(res => {
      if (!res.success && res.blocked) {
        document.getElementById('blockedMsg').style.display = 'block';
      } else if (!res.success) {
        alert(res.message || 'Error logging visit.');
      } else {
        showWelcome(res.entry);
      }
    });
}

function showWelcome(entry) {
  showScreen('welcomeScreen');
  document.getElementById('wName').textContent    = entry.name;
  document.getElementById('wProgram').textContent = entry.program;
  document.getElementById('wReason').textContent  = '📌 ' + entry.reason;
  document.getElementById('wTime').textContent    =
    new Date(entry.timestamp.replace(' ','T')).toLocaleString('en-PH',{dateStyle:'long',timeStyle:'short'});

  let n = 5;
  document.getElementById('countNum').textContent = n;
  const t = setInterval(() => {
    n--;
    document.getElementById('countNum').textContent = n;
    if (n <= 0) { clearInterval(t); showScreen('loginScreen'); resetForm(); }
  }, 1000);
}

function resetForm() {
  ['rfidInput','programInput','nameInput'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('reasonInput').value   = '';
  document.getElementById('blockedMsg').style.display = 'none';
}

// ── admin login ────────────────────────────────────────────────────────────────
function adminLogin() {
  const username = document.getElementById('adminUser').value.trim();
  const password = document.getElementById('adminPass').value;

  document.getElementById('adminErrMsg').style.display = 'none';

  const body = new URLSearchParams({ username, password });
  fetch('api.php?action=admin_login', { method:'POST', body })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        window.location.href = 'admin.php';
      } else {
        document.getElementById('adminErrMsg').style.display = 'block';
      }
    });
}

// ── screen helper ──────────────────────────────────────────────────────────────
function showScreen(id) {
  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  document.getElementById(id).classList.add('active');
}

// ── keyboard shortcuts ─────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key !== 'Enter') return;
  const isAdmin = document.getElementById('adminForm').style.display !== 'none';
  isAdmin ? adminLogin() : visitorLogin();
});
</script>
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/NEUProject/sw.js')
        .then(reg => console.log('SW registered'))
        .catch(err => console.error('SW failed:', err));
    });
  }
</script>
</body>
</html>