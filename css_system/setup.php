<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PELCO III – System Setup</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Inter', sans-serif;
  background: #f1f5f2;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  
}


.topbar {
  background: #037d3c;
  border-bottom: 3px solid #fce704;
  height: 60px;
  display: flex;
  align-items: center;
  padding: 0 28px;
  gap: 14px;
  box-shadow: 0 2px 8px rgba(3,125,60,.2);
}
.topbar-logo {
  width: 34px; height: 34px;
  background: #fff;
  border-radius: 7px;
  padding: 3px;
  object-fit: contain;
  flex-shrink: 0;
}
.topbar-name {
  font-size: 14px;
  font-weight: 800;
  color: #fce704;
  letter-spacing: .4px;
}
.topbar-sep {
  width: 1px; height: 20px;
  background: rgba(255,255,255,.2);
}
.topbar-title {
  font-size: 13px;
  font-weight: 500;
  color: rgba(255,255,255,.7);
}


.page {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 36px 20px;
}


.card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e3ebe5;
  box-shadow: 0 4px 24px rgba(0,0,0,.08);
  width: 100%;
  max-width: 520px;
  overflow: hidden;
}

.card-head {
  background: #037d3c;
  border-bottom: 3px solid #fce704;
  padding: 22px 28px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.card-head-logo {
  width: 44px; height: 44px;
  background: #fff;
  border-radius: 9px;
  padding: 4px;
  object-fit: contain;
  flex-shrink: 0;
  box-shadow: 0 0 0 2px rgba(252,231,4,.4);
}
.card-head-text h1 {
  font-size: 16px;
  font-weight: 800;
  color: #fff;
  line-height: 1.2;
}
.card-head-text p {
  font-size: 11px;
  color: rgba(255,255,255,.6);
  margin-top: 3px;
  font-weight: 500;
}
.badge {
  margin-left: auto;
  background: rgba(252,231,4,.15);
  border: 1px solid rgba(252,231,4,.35);
  color: #fce704;
  font-size: 10px;
  font-weight: 700;
  padding: 4px 11px;
  border-radius: 20px;
  letter-spacing: .4px;
  white-space: nowrap;
}

.card-body { padding: 28px; }


.form-group { margin-bottom: 16px; }
.form-group label {
  display: block;
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: #037d3c;
  margin-bottom: 5px;
}
.form-group input {
  width: 100%;
  padding: 9px 12px;
  border: 1.5px solid #e3ebe5;
  border-radius: 7px;
  font-size: 13.5px;
  font-family: inherit;
  color: #111;
  background: #fafcfb;
  outline: none;
  transition: .18s;
}
.form-group input:focus {
  border-color: #037d3c;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(3,125,60,.1);
}

.form-hint {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 4px;
}

.divider {
  height: 1px;
  background: #e3ebe5;
  margin: 20px 0;
}


.btn-install {
  width: 100%;
  padding: 11px;
  background: #037d3c;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: .18s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.btn-install:hover { background: #025a2b; }
.btn-install:disabled { opacity: .6; cursor: default; }


.log {
  display: none;
  margin-top: 18px;
  background: #f5faf7;
  border: 1px solid #d1e9d8;
  border-radius: 8px;
  padding: 14px 16px;
  max-height: 220px;
  overflow-y: auto;
  font-size: 12.5px;
  line-height: 1.8;
}
.log::-webkit-scrollbar { width: 4px; }
.log::-webkit-scrollbar-thumb { background: #b7d9c4; border-radius: 4px; }

.step { display: flex; align-items: flex-start; gap: 8px; padding: 2px 0; }
.step .ico { flex-shrink: 0; margin-top: 1px; }
.ok   { color: #166534; }
.err  { color: #dc2626; }
.info { color: #2563eb; }


.success-block {
  display: none;
  margin-top: 18px;
  background: #f0faf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  padding: 16px;
  text-align: center;
}
.success-block p {
  font-size: 12.5px;
  color: #166534;
  margin-bottom: 12px;
  font-weight: 500;
}
.btn-go {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 28px;
  background: #fce704;
  color: #111;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
  text-decoration: none;
  border: 2px solid rgba(0,0,0,.08);
  transition: .18s;
}
.btn-go:hover { background: #e6d100; }


.spin {
  width: 15px; height: 15px;
  border: 2.5px solid rgba(255,255,255,.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: sp .7s linear infinite;
  display: inline-block;
}
@keyframes sp { to { transform: rotate(360deg); } }
</style>
</head>
<body>


<header class="topbar">
  <img class="topbar-logo"
       src="https://www.pelco3.org/images/logo.png" alt="PELCO 3"
       onerror="this.style.display='none'">
  <span class="topbar-name">PELCO III</span>
  <div class="topbar-sep"></div>
  <span class="topbar-title">Customer Service System</span>
</header>

<div class="page">
  <div class="card">

    
    <div class="card-head">
      <img class="card-head-logo"
           src="https://www.pelco3.org/images/logo.png" alt="PELCO 3"
           onerror="this.style.display='none'">
      <div class="card-head-text">
        <h1>Database Setup</h1>
        <p>Configure your connection and install the system</p>
      </div>
      <span class="badge">v1.0</span>
    </div>

    
    <div class="card-body">

      <div class="form-group">
        <label>DB Host</label>
        <input type="text" id="dbHost" value="localhost">
      </div>

      <div class="form-group">
        <label>DB Username</label>
        <input type="text" id="dbUser" value="root">
      </div>

      <div class="form-group">
        <label>DB Password</label>
        <input type="password" id="dbPass" placeholder="Leave blank if none">
        <div class="form-hint">Default XAMPP: leave blank</div>
      </div>

      <div class="form-group">
        <label>Database Name</label>
        <input type="text" id="dbName" value="customer_service_db">
      </div>

      <div class="divider"></div>

      <button class="btn-install" id="installBtn" onclick="runSetup()">
        Install / Setup Database
      </button>

     
      <div class="log" id="log"></div>

     
      <div class="success-block" id="successBlock">
        <p>Installation complete! Your database is ready.</p>
        <a href="index.php" class="btn-go">
          Go to Customer Service System →
        </a>
      </div>

    </div>
  </div>
</div>

<script>
// Move focus to next field when Enter is pressed
const inputs = ['dbHost', 'dbUser', 'dbPass', 'dbName'];
inputs.forEach((id, idx) => {
  document.getElementById(id).addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (idx < inputs.length - 1) {
        document.getElementById(inputs[idx + 1]).focus();
      } else {
        document.getElementById('installBtn').focus();
      }
    }
  });
});

async function runSetup() {
  const btn = document.getElementById('installBtn');
  const log = document.getElementById('log');

  btn.disabled = true;
  btn.innerHTML = '<span class="spin"></span> Installing…';
  log.style.display = 'block';
  log.innerHTML = '<div class="step info"><span class="ico"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:#2563eb;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></span><span>Connecting to database…</span></div>';

  const data = {
    host: document.getElementById('dbHost').value,
    user: document.getElementById('dbUser').value,
    pass: document.getElementById('dbPass').value,
    name: document.getElementById('dbName').value,
  };

  try {
    const res = await fetch('setup_run.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const r = await res.json();

    log.innerHTML = '';
    r.steps.forEach(s => {
      const icon = s.ok
        ? `<svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:#166534;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><polyline points="20 6 9 17 4 12"/></svg>`
        : `<svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:#dc2626;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
      log.innerHTML += `<div class="step ${s.ok ? 'ok' : 'err'}">
        <span class="ico">${icon}</span>
        <span>${s.msg}</span>
      </div>`;
    });

    if (r.success) {
      const doneIcon = `<svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:#166534;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
      log.innerHTML += `<div class="step ok"><span class="ico">${doneIcon}</span><strong>Setup complete!</strong></div>`;
      document.getElementById('successBlock').style.display = 'block';
      btn.innerHTML = 'Installed';
    } else {
      btn.disabled = false;
      btn.innerHTML = 'Retry Setup';
    }
  } catch (e) {
    log.innerHTML = '<div class="step err"><span class="ico"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:#dc2626;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span><span>Request failed. Make sure Apache & MySQL are running.</span></div>';
    btn.disabled = false;
    btn.innerHTML = 'Retry Setup';
  }
}
</script>
</body>
</html>
