<?php
// ── admin.php ── Admin-only dashboard page ───────────────────────────────────
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
$adminUser = htmlspecialchars($_SESSION['admin_user'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEU Library – Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
:root{--navy:#0d1f0d;--navy2:#122012;--teal:#16a34a;--teal-light:#22c55e;--gold:#dc2626;--cream:#f0fdf4;--white:#ffffff;--gray:#86a886;--danger:#dc2626;--success:#16a34a;--card-bg:#122012;--input-bg:#1a2d1a;--border:#1e4a1e}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--navy);color:var(--white);min-height:100vh;overflow-x:hidden}
body::before{content:'';position:fixed;inset:0;background:url('assets/neu.jpg') center/cover;opacity:.18;pointer-events:none;z-index:0}
.screen{position:relative;z-index:1;display:flex;flex-direction:column;min-height:100vh}
/* NAV */
.dash-nav{background:var(--navy2);border-bottom:1px solid var(--border);padding:.75rem 2rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap}
.dash-nav .brand{font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--gold);margin-right:auto;display:flex;align-items:center;gap:.5rem}
.dash-nav .brand img{width:28px;height:28px;border-radius:6px;object-fit:cover}
.nav-btn{padding:.5rem 1.1rem;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--gray);cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.85rem;transition:.2s}
.nav-btn:hover,.nav-btn.active{background:var(--teal);border-color:var(--teal);color:var(--white)}
.nav-btn.danger:hover{background:var(--danger);border-color:var(--danger);color:var(--white)}
/* BODY */
.dash-body{flex:1;padding:2rem;overflow-y:auto}
/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem}
.stat-card{background:var(--card-bg);border:1px solid var(--border);border-radius:14px;padding:1.25rem 1.5rem;position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#16a34a,#22c55e)}
.stat-card.gold::before{background:linear-gradient(90deg,#dc2626,#f87171)}
.stat-card.green::before{background:linear-gradient(90deg,#16a34a,#4ade80)}
.stat-card.red::before{background:linear-gradient(90deg,var(--danger),#f87171)}
.stat-label{font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem}
.stat-val{font-size:2rem;font-weight:700;font-family:'Playfair Display',serif}
.stat-icon{position:absolute;right:1rem;top:50%;transform:translateY(-50%);font-size:2rem;opacity:.15}
/* CONTROLS */
.controls-row{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center}
.search-box{flex:1;min-width:220px;padding:.65rem 1rem;border-radius:10px;background:var(--input-bg);border:1px solid var(--border);color:var(--white);font-family:'DM Sans',sans-serif;font-size:.9rem}
.search-box::placeholder{color:var(--gray)}
.search-box:focus{outline:none;border-color:var(--teal)}
.filter-select{padding:.65rem 1rem;border-radius:10px;background:var(--input-bg);border:1px solid var(--border);color:var(--white);font-family:'DM Sans',sans-serif;font-size:.85rem;cursor:pointer}
.filter-select:focus{outline:none;border-color:var(--teal)}
.filter-select option{background:var(--navy2)}
.pdf-btn{padding:.65rem 1.2rem;border-radius:10px;border:1px solid #dc2626;background:transparent;color:#dc2626;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:600;transition:.2s;display:flex;align-items:center;gap:.4rem}
.pdf-btn:hover{background:#dc2626;color:var(--white)}
.date-range{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap}
.date-range input[type=date]{padding:.6rem .75rem;border-radius:8px;background:var(--input-bg);border:1px solid var(--border);color:var(--white);font-family:'DM Sans',sans-serif;font-size:.85rem}
.date-range input[type=date]:focus{outline:none;border-color:var(--teal)}
.date-range span{color:var(--gray);font-size:.85rem}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--gray);padding:.55rem 1rem;border-radius:8px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.82rem;transition:.2s}
.btn-outline:hover{border-color:var(--teal);color:var(--teal)}
/* TABLE */
.table-wrap{background:var(--card-bg);border:1px solid var(--border);border-radius:14px;overflow:hidden}
.table-head{padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.table-head h2{font-size:1rem;font-weight:600}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.75rem;font-weight:600}
.badge-teal{background:rgba(14,165,196,.15);color:var(--teal)}
table{width:100%;border-collapse:collapse}
thead th{padding:.85rem 1.25rem;text-align:left;font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;background:var(--navy2);border-bottom:1px solid var(--border)}
tbody tr{border-bottom:1px solid rgba(30,58,95,.5);transition:.15s}
tbody tr:hover{background:rgba(14,165,196,.04)}
tbody tr:last-child{border-bottom:none}
tbody td{padding:.9rem 1.25rem;font-size:.875rem}
.td-reason{display:inline-block;padding:.2rem .6rem;background:rgba(14,165,196,.1);border-radius:6px;font-size:.8rem;color:var(--teal-light)}
.td-status{display:inline-flex;align-items:center;gap:.35rem;font-size:.8rem;font-weight:600}
.td-status.in{color:var(--success)}
.dot{width:6px;height:6px;border-radius:50%;background:currentColor}
.block-btn{padding:.3rem .7rem;border-radius:6px;border:1px solid var(--danger);background:transparent;color:var(--danger);cursor:pointer;font-size:.75rem;font-family:'DM Sans',sans-serif;transition:.2s}
.block-btn:hover{background:var(--danger);color:var(--white)}
.unblock-btn{padding:.3rem .7rem;border-radius:6px;border:1px solid var(--success);background:transparent;color:var(--success);cursor:pointer;font-size:.75rem;font-family:'DM Sans',sans-serif;transition:.2s}
.unblock-btn:hover{background:var(--success);color:var(--white)}
.blocked-row td{opacity:.5}
/* DASH SECTIONS */
.dash-section{display:none}
.dash-section.active{display:block}
/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);align-items:center;justify-content:center;z-index:100}
.modal-overlay.open{display:flex}
.modal{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:2rem;width:100%;max-width:400px}
.modal h3{font-family:'Playfair Display',serif;font-size:1.3rem;margin-bottom:1rem}
.modal p{color:var(--gray);font-size:.9rem;margin-bottom:1.5rem}
.modal-btns{display:flex;gap:.75rem}
.modal-btns button{flex:1;padding:.75rem;border-radius:8px;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:600;transition:.2s}
.btn-cancel{background:var(--input-bg);color:var(--gray)}
.btn-confirm-danger{background:var(--danger);color:var(--white)}
.btn-confirm-danger:hover{opacity:.85}
.empty{padding:3rem;text-align:center;color:var(--gray)}
.empty-icon{font-size:2.5rem;margin-bottom:.75rem}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}
@media(max-width:600px){.dash-body{padding:1rem}.dash-nav{padding:.75rem 1rem}thead{display:none}tbody tr{display:block;padding:.75rem 1rem}tbody td{display:flex;justify-content:space-between;align-items:center;padding:.3rem 0;font-size:.82rem}tbody td::before{content:attr(data-label);color:var(--gray);font-size:.75rem}}
</style>
</head>
<body>
<div class="screen">

  <nav class="dash-nav">
    <div class="brand">
      <img src="assets/neu.jpg" alt="NEU Logo">
      NEU Library Admin
    </div>
    <span style="font-size:.8rem;color:var(--gray);" id="liveClock"></span>
    <span style="font-size:.8rem;color:var(--teal);">👤 <?= $adminUser ?></span>
    <button class="nav-btn active" id="navOverview" onclick="showSection('overview')">Overview</button>
    <button class="nav-btn"         id="navVisitors" onclick="showSection('visitors')">Visitors</button>
    <button class="nav-btn"         id="navManage"   onclick="showSection('manage')">Manage Users</button>
    <button class="nav-btn danger" onclick="adminLogout()">Log Out</button>
  </nav>

  <div class="dash-body">

    <!-- OVERVIEW -->
    <div id="overview" class="dash-section active">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:1.25rem;">Dashboard Overview</h2>
      <div class="stats-grid">
        <div class="stat-card">          <div class="stat-label">Total Visits</div><div class="stat-val" id="statTotal">—</div><div class="stat-icon">📋</div></div>
        <div class="stat-card gold">     <div class="stat-label">Today</div>       <div class="stat-val" id="statToday">—</div><div class="stat-icon">📅</div></div>
        <div class="stat-card green">    <div class="stat-label">This Week</div>   <div class="stat-val" id="statWeek">—</div> <div class="stat-icon">📊</div></div>
        <div class="stat-card red">      <div class="stat-label">This Month</div>  <div class="stat-val" id="statMonth">—</div><div class="stat-icon">🗓️</div></div>
      </div>

      <div class="controls-row" style="margin-bottom:1rem;">
        <div class="date-range">
          <span>From</span><input type="date" id="dateFrom" onchange="renderLog()">
          <span>To</span>  <input type="date" id="dateTo"   onchange="renderLog()">
          <button class="btn-outline" onclick="clearDates()">Clear</button>
        </div>
      </div>

      <div class="controls-row">
        <input class="search-box" type="text" id="searchLog" placeholder="🔍  Search by name, program, reason…" oninput="renderLog()">
        <select class="filter-select" id="filterReason" onchange="renderLog()">
          <option value="">All Reasons</option>
          <option>Reading</option><option>Researching</option><option>Use of Computer</option>
          <option>Meeting</option><option>Printing / Scanning</option><option>Borrowing Books</option><option>Other</option>
        </select>
       <select class="filter-select" id="filterVisitorType" onchange="renderLog()">
  <option value="">All Visitors</option>
  <option value="Student">Student</option>
  <option value="Staff">Staff</option>
  <option value="Faculty">Faculty</option>
</select>
        <button class="pdf-btn" onclick="exportPDF()">⬇ Export PDF</button>
      </div>

      <div class="table-wrap">
        <div class="table-head">
          <h2>Visitor Log <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;color:#22c55e;font-family:'DM Sans',sans-serif;margin-left:.5rem;"><span style="width:7px;height:7px;border-radius:50%;background:#22c55e;animation:pulse 1.5s infinite;display:inline-block;"></span>LIVE</span></h2>
          <span class="badge badge-teal" id="logCount">0 records</span>
        </div>
        <div style="overflow-x:auto;">
          <table>
<thead><tr><th>#</th><th>Name</th><th>ID / Email</th><th>Program</th><th>Type</th><th>Reason</th><th>Date & Time</th><th>Status</th></tr></thead>            <tbody id="logBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- VISITORS -->
    <div id="visitors" class="dash-section">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:1.25rem;">All Visitors</h2>
      <div class="controls-row">
        <input class="search-box" type="text" id="searchVisitors" placeholder="🔍  Search visitors…" oninput="renderVisitors()">
        <button class="pdf-btn" onclick="exportVisitorsPDF()">⬇ Export PDF</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>ID / Email</th><th>Program</th><th>Visits</th><th>Last Visit</th><th style="text-align:right">Actions</th></tr></thead>
          <tbody id="visitorsBody"></tbody>
        </table>
      </div>
    </div>

    <!-- MANAGE -->
    <div id="manage" class="dash-section">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:1.25rem;">Blocked Visitors</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>ID / Email</th><th>Program</th><th>Reason</th><th>Actions</th></tr></thead>
          <tbody id="blockedBody"></tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- CONFIRM MODAL -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal">
    <h3 id="modalTitle">Confirm Action</h3>
    <p  id="modalMsg">Are you sure?</p>
    <div class="modal-btns">
      <button class="btn-cancel" onclick="closeModal()">Cancel</button>
      <button class="btn-confirm-danger" id="modalConfirmBtn">Confirm</button>
    </div>
  </div>
</div>

<script>
// ── helpers ────────────────────────────────────────────────────────────────────
function api(action, method, body) {
  const url = 'api.php?action=' + action;
  const opts = { method: method || 'GET', headers: {} };
  if (body) { opts.method = 'POST'; opts.body = new URLSearchParams(body); }
  return fetch(url).then ? fetch(url, opts).then(r => r.json()) : Promise.reject();
}
function get(action, params) {
  const qs = new URLSearchParams(params || {});
  return fetch('api.php?action=' + action + '&' + qs).then(r => r.json());
}
function post(action, data) {
  const body = new URLSearchParams(data);
  return fetch('api.php?action=' + action, { method: 'POST', body }).then(r => r.json());
}

// ── clock ──────────────────────────────────────────────────────────────────────
function tick() {
  document.getElementById('liveClock').textContent =
    new Date().toLocaleString('en-PH', { dateStyle:'medium', timeStyle:'medium' });
}
setInterval(tick, 1000); tick();

// ── sections ───────────────────────────────────────────────────────────────────
function showSection(id) {
  ['overview','visitors','manage'].forEach(s => {
    document.getElementById(s).classList.toggle('active', s === id);
    const btn = document.getElementById('nav' + s.charAt(0).toUpperCase() + s.slice(1));
    if (btn) btn.classList.toggle('active', s === id);
  });
  if (id === 'overview') { loadStats(); renderLog(); }
  if (id === 'visitors') renderVisitors();
  if (id === 'manage')   renderBlocked();
}

// ── stats ──────────────────────────────────────────────────────────────────────
function loadStats() {
  get('get_stats').then(res => {
    if (!res.success) return;
    document.getElementById('statTotal').textContent = res.data.total;
    document.getElementById('statToday').textContent = res.data.today;
    document.getElementById('statWeek').textContent  = res.data.week;
    document.getElementById('statMonth').textContent = res.data.month;
  });
}

// ── log table ──────────────────────────────────────────────────────────────────
let _logData = [];
function renderLog() {
  const q      = document.getElementById('searchLog').value;
  const reason = document.getElementById('filterReason').value;
  const visitorType = document.getElementById('filterVisitorType').value;
  const from   = document.getElementById('dateFrom').value;
  const to     = document.getElementById('dateTo').value;
get('get_log', { q, reason, from, to, visitor_type: visitorType }).then(res => {    _logData = res.data || [];
    const tbody = document.getElementById('logBody');
    document.getElementById('logCount').textContent = _logData.length + ' records';
    if (!_logData.length) {
      tbody.innerHTML = `<tr><td colspan="7"><div class="empty"><div class="empty-icon">🔍</div>No matching records.</div></td></tr>`;
      return;
    }
    tbody.innerHTML = _logData.map((v, i) => `
      <tr>
        <td data-label="#">${i+1}</td>
        <td data-label="Name"><strong>${esc(v.name)}</strong></td>
        <td data-label="ID">${esc(v.rfid)}</td>
     <td data-label="Program">${esc(v.program)}</td>
        <td data-label="Type">${esc(v.visitor_type || 'Student')}</td>
        <td data-label="Reason"><span class="td-reason">${esc(v.reason)}</span></td>
        <td data-label="Date">${fmtDT(v.timestamp)}</td>
        <td data-label="Status"><span class="td-status in"><span class="dot"></span>Logged</span></td>
      </tr>`).join('');
  });
}
function clearDates() {
  document.getElementById('dateFrom').value = '';
  document.getElementById('dateTo').value   = '';
  renderLog();
}

// ── visitors table ─────────────────────────────────────────────────────────────
let _visitorsData = [];
function renderVisitors() {
  const q = document.getElementById('searchVisitors').value;
  get('get_visitors', { q }).then(res => {
    _visitorsData = res.data || [];
    const tbody = document.getElementById('visitorsBody');
    if (!_visitorsData.length) {
      tbody.innerHTML = `<tr><td colspan="7"><div class="empty"><div class="empty-icon">👤</div>No visitors found.</div></td></tr>`;
      return;
    }
    tbody.innerHTML = _visitorsData.map((v, i) => `
      <tr class="${v.is_blocked ? 'blocked-row' : ''}">
        <td data-label="#">${i+1}</td>
        <td data-label="Name"><strong>${esc(v.name)}</strong></td>
        <td data-label="ID">${esc(v.rfid)}</td>
        <td data-label="Program">${esc(v.program)}</td>
        <td data-label="Visits">${v.visit_count}</td>
        <td data-label="Last Visit">${fmtD(v.last_visit)}</td>
        <td data-label="Actions" style="text-align:right">
          ${v.is_blocked
            ? `<button class="unblock-btn" onclick="unblockUser('${esc(v.rfid)}')">Unblock</button>`
            : `<button class="block-btn"   onclick="openBlockModal('${esc(v.rfid)}','${esc(v.name)}','${esc(v.program)}')">Block</button>`}
        </td>
      </tr>`).join('');
  });
}

// ── blocked table ──────────────────────────────────────────────────────────────
let _blockedData = [];
function renderBlocked() {
  get('get_blocked').then(res => {
    _blockedData = res.data || [];
    const tbody = document.getElementById('blockedBody');
    if (!_blockedData.length) {
      tbody.innerHTML = `<tr><td colspan="6"><div class="empty"><div class="empty-icon">✅</div>No blocked visitors.</div></td></tr>`;
      return;
    }
    tbody.innerHTML = _blockedData.map((v, i) => `
      <tr>
        <td data-label="#">${i+1}</td>
        <td data-label="Name"><strong>${esc(v.name)}</strong></td>
        <td data-label="ID">${esc(v.rfid)}</td>
        <td data-label="Program">${esc(v.program||'—')}</td>
        <td data-label="Reason"><span class="td-reason">${esc(v.block_reason)}</span></td>
        <td data-label="Actions"><button class="unblock-btn" onclick="unblockUser('${esc(v.rfid)}')">Unblock</button></td>
      </tr>`).join('');
  });
}

// ── block / unblock ────────────────────────────────────────────────────────────
let _modalAction = null;
function openBlockModal(rfid, name, program) {
  document.getElementById('modalTitle').textContent = 'Block Visitor';
  document.getElementById('modalMsg').textContent   = `Block "${name}" (${rfid}) from using the library?`;
  document.getElementById('modalConfirmBtn').textContent = 'Block';
  _modalAction = () => {
    post('block_visitor', { rfid, name, program }).then(() => {
      closeModal(); renderVisitors(); renderBlocked();
    });
  };
  document.getElementById('confirmModal').classList.add('open');
}
function unblockUser(rfid) {
  post('unblock_visitor', { rfid }).then(() => { renderVisitors(); renderBlocked(); });
}
function closeModal() { document.getElementById('confirmModal').classList.remove('open'); }
document.getElementById('modalConfirmBtn').addEventListener('click', () => { if (_modalAction) _modalAction(); });

// ── logout ─────────────────────────────────────────────────────────────────────
function adminLogout() {
  post('admin_logout').then(() => { window.location.href = 'index.php'; });
}

// ── PDF export ─────────────────────────────────────────────────────────────────
function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.setFontSize(16); doc.text('NEU Library – Visitor Log', 14, 18);
  doc.setFontSize(9); doc.setTextColor(120);
  doc.text('Generated: ' + new Date().toLocaleString('en-PH'), 14, 26);
  doc.setTextColor(0);
  let y = 35;
  const cols = ['#','Name','ID/Email','Program','Reason','Date & Time'];
  const widths = [8,38,38,30,30,40];
  let x = 14;
  doc.setFontSize(8);
  cols.forEach((c,i) => { doc.setFont(undefined,'bold'); doc.text(c, x, y); x += widths[i]; });
  y += 4; doc.line(14, y, 196, y); y += 4;
  _logData.forEach((v, idx) => {
    if (y > 270) { doc.addPage(); y = 18; }
    x = 14; doc.setFont(undefined,'normal');
    [String(idx+1), v.name, v.rfid, v.program, v.reason, fmtDT(v.timestamp)]
      .forEach((c, i) => { doc.text(String(c).substring(0,22), x, y); x += widths[i]; });
    y += 7;
  });
  doc.save('NEU_Library_Log.pdf');
}
function exportVisitorsPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.setFontSize(16); doc.text('NEU Library – Visitor Summary', 14, 18);
  doc.setFontSize(9); doc.setTextColor(120);
  doc.text('Generated: ' + new Date().toLocaleString('en-PH'), 14, 26);
  doc.setTextColor(0);
  let y = 35;
  const cols = ['#','Name','ID/Email','Program','Visits','Last Visit'];
  const widths = [8,45,45,35,18,35];
  let x = 14;
  doc.setFontSize(8);
  cols.forEach((c,i) => { doc.setFont(undefined,'bold'); doc.text(c, x, y); x += widths[i]; });
  y += 4; doc.line(14, y, 196, y); y += 4;
  _visitorsData.forEach((v, idx) => {
    if (y > 270) { doc.addPage(); y = 18; }
    x = 14; doc.setFont(undefined,'normal');
    [String(idx+1), v.name, v.rfid, v.program, String(v.visit_count), fmtD(v.last_visit)]
      .forEach((c, i) => { doc.text(String(c).substring(0,24), x, y); x += widths[i]; });
    y += 7;
  });
  doc.save('NEU_Visitors_Summary.pdf');
}

// ── utils ──────────────────────────────────────────────────────────────────────
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmtDT(ts) { return ts ? new Date(ts.replace(' ','T')).toLocaleString('en-PH',{dateStyle:'medium',timeStyle:'short'}) : '—'; }
function fmtD(ts)  { return ts ? new Date(ts.replace(' ','T')).toLocaleDateString('en-PH',{dateStyle:'medium'}) : '—'; }

// ── auto-refresh ───────────────────────────────────────────────────────────────
setInterval(() => {
  loadStats();
  if (document.getElementById('overview').classList.contains('active')) renderLog();
  if (document.getElementById('visitors').classList.contains('active')) renderVisitors();
}, 10000);

// ── init ───────────────────────────────────────────────────────────────────────
loadStats();
renderLog();
</script>
</body>
</html>