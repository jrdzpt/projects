<?php
session_set_cookie_params([
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require_once __DIR__ . '/includes/config.php';

// Redirect to login if not authenticated
if (empty($_SESSION['css_user'])) {
    header('Location: login.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PELCO III – Customer Service</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.min.js"></script>
<style>
  .icon { display:inline-flex; align-items:center; justify-content:center; }
  .icon svg { width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
  .nav-icon .icon svg { width:17px; height:17px; }
  .empty-ico .icon svg { width:40px; height:40px; stroke-width:1.5; opacity:.4; }
  .bm-icon svg { width:15px; height:15px; }
</style>
<style>
:root {
  --green:   #037d3c;
  --green-d: #025a2b;
  --yellow:  #fce704;
  --red:     #fb0908;
  --blue:    #0102f3;

  --bg:      #f5f5f5;
  --white:   #ffffff;
  --border:  #e5e5e5;
  --text:    #111111;
  --text2:   #555555;
  --text3:   #999999;

  --sb-w:    220px;
  --sb-min:  52px;
  --topbar:  56px;
  --t: .2s ease;
}

/* ══════════════════════════════════════════════
   DARK MODE — Full Enhanced Treatment
   ══════════════════════════════════════════════ */
body { transition: background var(--t), color var(--t); }

body.dark {
  --bg:      #0c0e14;
  --white:   #13161f;
  --border:  #232638;
  --text:    #dde1f0;
  --text2:   #8b90ab;
  --text3:   #4e5270;
  --green:   #05a050;
  --green-d: #038a42;
  --red:     #f53535;
  --blue:    #3b5bdb;
}

/* ── Body & scrollbar ── */
body.dark { color-scheme: dark; }
body.dark ::-webkit-scrollbar { width: 8px; height: 8px; }
body.dark ::-webkit-scrollbar-track { background: #0c0e14; }
body.dark ::-webkit-scrollbar-thumb { background: #2a2d40; border-radius: 4px; }
body.dark ::-webkit-scrollbar-thumb:hover { background: #383c58; }

/* ── Sidebar ── */
body.dark .sb {
  background: linear-gradient(180deg, #111525 0%, #0d1020 100%);
  border-right: 1px solid #1e2235;
  box-shadow: 4px 0 20px rgba(0,0,0,.5);
}
body.dark .sb-logo { border-bottom-color: rgba(255,255,255,.06); }
body.dark .nav-link { color: rgba(255,255,255,.5); }
body.dark .nav-link:hover { background: rgba(5,160,80,.12); color: rgba(255,255,255,.9); }
body.dark .nav-link.active {
  background: rgba(5,160,80,.18);
  color: #fff;
  box-shadow: inset 3px 0 0 var(--green);
}
body.dark .sb-toggle { border-top-color: rgba(255,255,255,.06); }
body.dark .sb-toggle:hover { background: rgba(255,255,255,.04); }
body.dark .toggle-icon { background: rgba(255,255,255,.06); }

/* ── Topbar ── */
body.dark .topbar {
  background: linear-gradient(90deg, #111525 0%, #0d1020 100%);
  border-bottom-color: var(--green);
  box-shadow: 0 2px 16px rgba(0,0,0,.5);
}
body.dark .tb-datetime {
  background: rgba(0,0,0,.3);
  border-color: rgba(255,255,255,.07);
}
body.dark .tb-time,
body.dark .tb-date-day,
body.dark .tb-date-full,
body.dark .tb-time-label { color: #fff !important; }

/* ── Main content area ── */
body.dark .content { background: var(--bg); }

/* ── Toolbar ── */
body.dark .toolbar {
  background: var(--white);
  border-color: var(--border);
  box-shadow: 0 1px 6px rgba(0,0,0,.3);
}
body.dark .tg label { color: var(--text3); }

/* ── Inputs ── */
body.dark input[type=text],
body.dark input[type=date],
body.dark select {
  background: #0c0e14;
  color: var(--text);
  border-color: var(--border);
}
body.dark input[type=text]:focus,
body.dark input[type=date]:focus,
body.dark select:focus {
  border-color: var(--green);
  box-shadow: 0 0 0 3px rgba(5,160,80,.12);
  background: #0f1119;
}
body.dark select option { background: #13161f; color: var(--text); }

/* ── Buttons ── */
body.dark .btn-plain {
  background: transparent;
  color: var(--text2);
  border-color: var(--border);
}
body.dark .btn-plain:hover { color: var(--text); border-color: #4e5270; background: rgba(255,255,255,.04); }
body.dark .btn-yellow { background: #c9b800; color: #0a0a0a; border-color: transparent; }
body.dark .btn-yellow:hover { background: #b5a600; }
body.dark .btn-blue { background: #3b5bdb; }
body.dark .btn-blue:hover { background: #3451c7; }
body.dark .btn-bm { border-color: var(--border); color: var(--text3); }
body.dark .btn-bm:hover { border-color: #d97706; color: #d97706; background: rgba(217,119,6,.08); }
body.dark .btn-bm.bm-on { background: rgba(217,119,6,.12); border-color: #d97706; color: #d97706; }

/* ── Table ── */
body.dark .table-wrap-outer {
  background: var(--white);
  border-color: var(--border);
  box-shadow: 0 2px 12px rgba(0,0,0,.35);
}
body.dark .table-top {
  background: var(--white);
  border-bottom-color: var(--border);
}
body.dark thead th {
  background: #0f1119;
  color: var(--text3);
  border-bottom-color: var(--border);
}
body.dark tbody td { border-bottom-color: var(--border); }
body.dark tbody tr:hover td {
  background: rgba(5,160,80,.05);
}
body.dark tbody tr:last-child td { border-bottom: none; }
body.dark .ref-tag {
  background: rgba(5,160,80,.1);
  border-color: rgba(5,160,80,.2);
  color: #2ecc7a;
}
body.dark .cell-sub { color: var(--text3); }

/* ── Pagination ── */
body.dark .pg { background: #0f1119; border-top-color: var(--border); }
body.dark .pg-btn {
  background: var(--white);
  border-color: var(--border);
  color: var(--text2);
}
body.dark .pg-btn:hover { border-color: var(--green); color: var(--green); background: rgba(5,160,80,.07); }
body.dark .pg-btn.on { background: var(--green); color: #fff; border-color: var(--green); }

/* ── Badges ── */
body.dark .b-open { background: rgba(234,179,8,.08);  color: #fbbf24; border-color: rgba(234,179,8,.2); }
body.dark .b-prog { background: rgba(59,91,219,.1);   color: #93c5fd; border-color: rgba(59,91,219,.25); }
body.dark .b-res  { background: rgba(5,160,80,.1);    color: #4ade80; border-color: rgba(5,160,80,.25); }
body.dark .b-cls  { background: rgba(107,114,128,.08);color: #9ca3af; border-color: rgba(107,114,128,.2); }

/* ── Modals ── */
body.dark .overlay { background: rgba(0,0,0,.6); }
body.dark .modal {
  background: var(--white);
  border: 1px solid var(--border);
  box-shadow: 0 24px 80px rgba(0,0,0,.7);
}
body.dark .modal-bd { background: var(--white); }
body.dark .modal-ft {
  background: #0f1119;
  border-top-color: var(--border);
}
body.dark .fg-row label { color: var(--text3); }
body.dark .fg-row input,
body.dark .fg-row select,
body.dark .fg-row textarea {
  background: #0c0e14;
  color: var(--text);
  border-color: var(--border);
}
body.dark .fg-row input:focus,
body.dark .fg-row select:focus,
body.dark .fg-row textarea:focus {
  border-color: var(--green);
  box-shadow: 0 0 0 3px rgba(5,160,80,.12);
  background: #0f1119;
}
body.dark .fg-row textarea { background: #0c0e14; }
body.dark .d-row label { color: var(--text3); }
body.dark .d-row span { color: var(--text); }

/* ── Export dropdown ── */
body.dark .exp-menu {
  background: #1a1d2b;
  border-color: var(--border);
  box-shadow: 0 8px 32px rgba(0,0,0,.6);
}
body.dark .exp-item { color: var(--text); }
body.dark .exp-item:hover { background: rgba(255,255,255,.04); }

/* ── Report modal ── */
body.dark .report-section h4 { color: var(--text3); border-bottom-color: var(--border); }
body.dark .report-row { border-bottom-color: var(--border); }
body.dark .report-label { color: var(--text); }
body.dark .report-bar-wrap { background: #1e2235; }
body.dark .report-bar { background: var(--green); }
body.dark .report-cnt { color: var(--green); }
body.dark .report-empty { color: var(--text3); }

/* ── Empty states ── */
body.dark .empty-title { color: var(--text2); }
body.dark .empty-sub { color: var(--text3); }
body.dark .load-row td { color: var(--text3); }

/* ── Toast ── */
body.dark .toast { background: #1e2235; border: 1px solid var(--border); box-shadow: 0 4px 20px rgba(0,0,0,.4); }
body.dark .toast.ok  { border-left-color: var(--green); }
body.dark .toast.err { border-left-color: var(--red); }

/* ── Bookmark section ── */
body.dark #bmContent > div { border-bottom-color: var(--border); }
body.dark #bmContent > div:hover { background: rgba(255,255,255,.02); }

/* ── Smooth transition on theme switch ── */
body, body * {
  transition-property: background-color, border-color, color, box-shadow;
  transition-duration: .2s;
  transition-timing-function: ease;
}
/* Exclude animations from transition override */
.spin, @keyframes sp, .modal { transition-duration: unset; }

/* ── Dark toggle btn ── */
.btn-dark {
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.13);
  color: rgba(255,255,255,.75);
  border-radius: 8px;
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background .2s, border-color .2s, box-shadow .2s, transform .15s;
  flex-shrink: 0; position: relative; overflow: hidden;
}
.btn-dark:hover {
  background: rgba(255,255,255,.16);
  border-color: rgba(255,255,255,.25);
  color: #fff;
  transform: scale(1.06);
}
.btn-dark:active { transform: scale(.96); }
.btn-dark svg {
  width: 17px; height: 17px;
  stroke: currentColor; fill: none;
  stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
  transition: transform .35s cubic-bezier(.34,1.56,.64,1), opacity .2s;
}
body.dark .btn-dark {
  background: rgba(5,160,80,.15);
  border-color: rgba(5,160,80,.3);
  color: #4ade80;
  box-shadow: 0 0 12px rgba(5,160,80,.2);
}
body.dark .btn-dark:hover {
  background: rgba(5,160,80,.25);
  border-color: rgba(5,160,80,.45);
  box-shadow: 0 0 18px rgba(5,160,80,.35);
}

/* ── Sortable headers ── */
thead th.sortable { cursor: pointer; user-select: none; position: relative; padding-right: 22px; }
thead th.sortable:hover { color: var(--green); }
thead th.sortable::after { content: '\2195'; position: absolute; right: 6px; top: 50%; transform: translateY(-50%); font-size: 10px; opacity: .35; }
thead th.sort-asc::after  { content: '\2191'; opacity: .9; color: var(--green); }
thead th.sort-desc::after { content: '\2193'; opacity: .9; color: var(--green); }

/* ── Report modal ── */
.report-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 4px; }
.report-section h4 { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text3); margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid var(--border); }
.report-row { display: flex; align-items: center; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid var(--border); gap: 8px; }
.report-row:last-child { border-bottom: none; }
.report-label { font-size: 12.5px; color: var(--text); flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.report-bar-wrap { width: 70px; height: 6px; background: var(--border); border-radius: 3px; flex-shrink: 0; }
.report-bar { height: 6px; background: var(--green); border-radius: 3px; transition: width .4s ease; }
.report-cnt { font-size: 12px; font-weight: 700; color: var(--green); min-width: 24px; text-align: right; flex-shrink: 0; }
.report-total-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(3,125,60,.08); border: 1px solid rgba(3,125,60,.15); border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600; color: var(--green); margin-bottom: 14px; }
.report-empty { text-align: center; padding: 32px 0; color: var(--text3); font-size: 13px; }
.dirty-dot { display: inline-block; width: 7px; height: 7px; background: var(--yellow); border-radius: 50%; margin-left: 6px; vertical-align: middle; box-shadow: 0 0 0 2px rgba(252,231,4,.25); }

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Inter', sans-serif;
  background: var(--bg);
  color: var(--text);
  font-size: 13.5px;
  min-height: 100vh;
}


.sb {
  position: fixed; left: 0; top: 0; bottom: 0;
  width: var(--sb-w);
  background: var(--green);
  display: flex; flex-direction: column;
  z-index: 100;
  transition: width var(--t);
  overflow: hidden;
}
.sb.mini { width: var(--sb-min); }

.sb-logo {
  display: flex; 
  align-items: center; 
  gap: 8px; 
 
  padding: 5px 14px 12px 5px; 
  border-bottom: 1px solid rgba(255,255,255,.12);
  flex-shrink: 0; 
  overflow: hidden; 
  min-height: 56px;
}

.sb-logo img {
  width: 50px; 
  height: 50px; 
  object-fit: contain; 
  flex-shrink: 0;
  border-radius: 2px; 
  padding: 3px;
 
  margin-top: -2px; 
}

.sb-logo-name {
  display: flex;
  flex-direction: column;
  line-height: 1.0; 
  white-space: nowrap;
  transition: opacity var(--t);
  
  margin-top: -2px; 
}


.sb-nav { flex: 1; padding: 8px 6px; overflow: hidden; }

.nav-link {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 10px; border-radius: 6px; margin-bottom: 2px;
  color: rgba(255,255,255,.7); cursor: pointer;
  font-size: 13px; font-weight: 500;
  white-space: nowrap; overflow: hidden;
  transition: var(--t);
  text-decoration: none;
}
.nav-link:hover { background: rgba(255,255,255,.1); color: #fff; }
.nav-link.active { background: rgba(255,255,255,.15); color: #fff; }

.nav-icon {
  font-size: 15px; flex-shrink: 0;
  width: 22px; text-align: center;
}
.nav-text {
  overflow: hidden; white-space: nowrap;
  opacity: 1; transition: opacity var(--t);
}
.sb.mini .nav-text { opacity: 0; }


.sb-toggle {
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  padding: 12px;
  border-top: 1px solid rgba(255,255,255,.1);
  cursor: pointer;
  transition: var(--t);
  background: none; border-left: none; border-right: none; border-bottom: none;
  width: 100%; font-family: inherit;
}
.sb-toggle:hover { background: rgba(255,255,255,.07); }
.sb-toggle:hover .toggle-icon { background: rgba(255,255,255,.18); }

.toggle-icon {
  width: 32px; height: 32px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: rgba(255,255,255,.08);
  border-radius: 8px;
  transition: var(--t);
}

.toggle-icon svg {
  width: 14px; height: 14px;
  stroke: rgba(255,255,255,.65); stroke-width: 2.5;
  fill: none; stroke-linecap: round; stroke-linejoin: round;
  transition: transform var(--t);
}
.sb.mini .toggle-icon svg { transform: rotate(180deg); }


.main {
  margin-left: var(--sb-w);
  min-height: 100vh;
  display: flex; flex-direction: column;
  transition: margin-left var(--t);
}
.main.mini { margin-left: var(--sb-min); }


.topbar {
  height: var(--topbar);
  background: var(--green);
  border-bottom: 3px solid var(--yellow);
  display: flex; align-items: center;
  padding: 0 24px;
  gap: 12px;
  position: sticky; top: 0; z-index: 50;
  box-shadow: 0 2px 8px rgba(3,125,60,.18);
}
.topbar-title { font-size: 15px; font-weight: 600; color: #fff; flex: 1; }
.topbar-right { display: flex; align-items: center; gap: 10px; }
.tb-datetime {
  display: flex; align-items: center; gap: 10px;
  background: rgba(0,0,0,.18);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 8px;
  padding: 6px 14px;
}
.tb-divider {
  width: 1px; height: 28px;
  background: rgba(255,255,255,.15);
}
.tb-time-block { display: flex; flex-direction: column; align-items: center; gap: 1px; }
.tb-date-block { display: flex; flex-direction: column; align-items: center; gap: 1px; }
.tb-time {
  font-family: 'JetBrains Mono', monospace;
  font-size: 16px; font-weight: 700; color: #fff;
  letter-spacing: 1px; line-height: 1;
}
.tb-time-label {
  font-size: 9px; font-weight: 700; color: rgba(255,255,255,.45);
  text-transform: uppercase; letter-spacing: .8px;
}
.tb-date-day {
  font-size: 15px; font-weight: 700; color: var(--white);
  line-height: 1; letter-spacing: .2px;
}
.tb-date-full {
  font-size: 9px; font-weight: 600; color: rgba(255,255,255,.5);
  text-transform: uppercase; letter-spacing: .6px; white-space: nowrap;
}


.content { padding: 20px 24px; flex: 1; }


.toolbar {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 12px 16px;
  margin-bottom: 16px;
  display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;
}
.tg { display: flex; flex-direction: column; gap: 4px; }
.tg label { font-size: 10px; font-weight: 600; color: var(--text3); text-transform: uppercase; letter-spacing: .5px; }

input[type=text], input[type=date], select {
  border: 1px solid var(--border); border-radius: 6px;
  padding: 7px 10px; font-size: 13px; color: var(--text);
  background: var(--white); outline: none; transition: var(--t); font-family: inherit;
}
input[type=text]:focus, input[type=date]:focus, select:focus {
  border-color: var(--green); box-shadow: 0 0 0 3px rgba(3,125,60,.08);
}
.s-input { width: 200px; }


.btn {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 7px 14px; border-radius: 6px; border: none; cursor: pointer;
  font-size: 13px; font-weight: 600; transition: var(--t); font-family: inherit;
}
.btn-primary { background: var(--green); color: #fff; }
.btn-primary:hover { background: var(--green-d); }
.btn-danger  { background: var(--red); color: #fff; }
.btn-danger:hover  { background: #d00000; }
.btn-plain   { background: transparent; color: var(--text2); border: 1px solid var(--border); }
.btn-plain:hover   { border-color: var(--text2); color: var(--text); }
.btn-yellow  { background: var(--yellow); color: #111; border: 1.5px solid rgba(0,0,0,.1); }
.btn-yellow:hover  { background: #e6d100; }
.btn-blue    { background: var(--blue); color: #fff; }
.btn-blue:hover    { background: #0001cc; }
.btn-sm  { padding: 5px 9px; font-size: 12px; }
.btn-icon{ padding: 5px 8px; }
.btn-bm  { background: transparent; color: var(--text3); border: 1px solid var(--border); opacity: .45; }
.btn-bm:hover  { border-color: #f59e0b; color: #f59e0b; opacity: 1; }
.btn-bm.bm-on  { background: #fffbeb; border-color: #f59e0b; color: #d97706; opacity: 1; }


.exp-wrap { position: relative; }
.exp-menu {
  display: none; position: absolute; right: 0; top: calc(100% + 6px);
  background: var(--white); border: 1px solid var(--border); border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0,0,0,.1); z-index: 200; min-width: 180px; overflow: hidden;
}
.exp-menu.open { display: block; }
.exp-item {
  display: flex; align-items: center; gap: 9px;
  padding: 9px 14px; cursor: pointer; transition: var(--t); font-size: 13px; color: var(--text);
}
.exp-item:hover { background: var(--bg); }


.table-wrap-outer {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 8px;
  overflow: hidden;
}
.table-top {
  padding: 12px 16px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.table-top-title { font-size: 13.5px; font-weight: 600; color: var(--text); }
.table-top-count {
  font-size: 11px; font-weight: 600; color: var(--text3);
}
.t-scroll { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
thead th {
  background: #fafafa; padding: 9px 12px; text-align: left;
  font-size: 10.5px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .4px; color: var(--text3);
  border-bottom: 1px solid var(--border); white-space: nowrap;
}
tbody td { padding: 10px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #fafafa; }

.ref-tag {
  font-family: 'JetBrains Mono', monospace;
  font-size: 11px; font-weight: 500; color: var(--green);
  background: rgba(3,125,60,.06); border: 1px solid rgba(3,125,60,.12);
  padding: 2px 7px; border-radius: 4px;
}
.cell-main { font-weight: 600; font-size: 13px; }
.cell-sub  { font-size: 11px; color: var(--text3); margin-top: 2px; }
.actions   { display: flex; gap: 4px; }


.badge {
  display: inline-block; padding: 2px 9px; border-radius: 20px;
  font-size: 11px; font-weight: 600; white-space: nowrap;
}
.b-open { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.b-prog { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.b-res  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.b-cls  { background: #f9fafb; color: #6b7280; border: 1px solid #e5e7eb; }


.pg {
  display: flex; align-items: center; gap: 4px;
  padding: 10px 16px; border-top: 1px solid var(--border);
  background: #fafafa; flex-wrap: wrap;
}
.pg-btn {
  min-width: 30px; height: 30px; padding: 0 7px;
  border: 1px solid var(--border); border-radius: 5px;
  background: var(--white); cursor: pointer;
  font-size: 13px; font-weight: 500; font-family: inherit;
  color: var(--text2); transition: var(--t);
  display: flex; align-items: center; justify-content: center;
}
.pg-btn:hover   { border-color: var(--green); color: var(--green); }
.pg-btn.on      { background: var(--green); color: #fff; border-color: var(--green); }
.pg-btn:disabled{ opacity: .3; pointer-events: none; }
.pg-info { margin-left: auto; font-size: 11px; color: var(--text3); font-weight: 500; }

.empty { text-align: center; padding: 56px 20px; color: var(--text3); }
.empty-ico   { font-size: 36px; margin-bottom: 10px; opacity: .4; }
.empty-title { font-size: 14px; font-weight: 600; color: var(--text2); }
.empty-sub   { font-size: 12px; margin-top: 4px; }
.load-row td { text-align: center; padding: 40px; color: var(--text3); font-size: 13px; }


.overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,.35); z-index: 900;
  align-items: flex-start; justify-content: center;
  padding: 40px 16px; overflow-y: auto;
}
.overlay.open { display: flex; }
.modal {
  background: var(--white); border-radius: 10px;
  width: 100%; max-width: 740px;
  box-shadow: 0 8px 40px rgba(0,0,0,.15);
  animation: mIn .18s ease; overflow: hidden;
}
@keyframes mIn { from { opacity:0; transform:translateY(-12px); } }
.modal-hd {
  padding: 16px 20px;
  background: var(--green);
  border-bottom: 2px solid var(--yellow);
  display: flex; align-items: center; justify-content: space-between;
}
.modal-hd h3 { font-size: 14px; font-weight: 700; color: #fff; }
.modal-x {
  background: none; border: none; color: rgba(255,255,255,.6);
  font-size: 18px; cursor: pointer; line-height: 1; padding: 2px 4px;
}
.modal-x:hover { color: #fff; }
.modal-bd { padding: 20px; }
.modal-ft {
  padding: 12px 20px; border-top: 1px solid var(--border);
  display: flex; gap: 8px; justify-content: flex-end; background: #fafafa;
}

.fg { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.fg .full { grid-column: 1/-1; }
.fg-row { display: flex; flex-direction: column; gap: 4px; }
.fg-row label {
  font-size: 10.5px; font-weight: 700; color: var(--text3);
  text-transform: uppercase; letter-spacing: .4px;
}
.fg-row label .req { color: #e53e3e; margin-left: 2px; font-size: 12px; }
.fg-row input, .fg-row select, .fg-row textarea {
  border: 1px solid var(--border); border-radius: 6px;
  padding: 8px 11px; font-size: 13.5px; color: var(--text);
  background: #fdfdfd; outline: none; transition: var(--t); font-family: inherit; width: 100%;
}
.fg-row input:focus, .fg-row select:focus, .fg-row textarea:focus {
  border-color: var(--green); box-shadow: 0 0 0 3px rgba(3,125,60,.08); background: #fff;
}
.fg-row textarea { resize: vertical; min-height: 76px; }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 24px; }
.detail-grid .full { grid-column: 1/-1; }
.d-row label {
  font-size: 10px; font-weight: 700; color: var(--text3);
  text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 3px;
}
.d-row span { font-size: 13.5px; color: var(--text); display: block; }


.toast-box { position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
.toast {
  background: #111; color: #fff; padding: 10px 15px; border-radius: 8px;
  font-size: 13px; font-weight: 500; box-shadow: 0 4px 20px rgba(0,0,0,.2);
  animation: tIn .2s ease; display: flex; align-items: center; gap: 8px;
  min-width: 200px; max-width: 320px;
}
.toast.ok  { border-left: 3px solid var(--green); }
.toast.err { border-left: 3px solid var(--red); }
@keyframes tIn { from { opacity:0; transform:translateX(20px); } }

.spin {
  display: inline-block; width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,.3); border-top-color: #fff;
  border-radius: 50%; animation: sp .6s linear infinite;
}
@keyframes sp { to { transform:rotate(360deg); } }

#archiveSection { display: none; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sb" id="sb">
  <div class="sb-logo">
    <img src="https://www.pelco3.org/images/logo.png" alt=""
         onerror="this.style.display='none'">
    <div class="sb-logo-name" style="display: flex; flex-direction: column; line-height: 1.1; margin-left: 4px;">
    <span style="color: yellow; font-size: 1.1rem; font-weight: 800; letter-spacing: 0.5px;">PELCO III</span>
    <span style="color: white; font-size: 0.7rem; font-weight: 400;">Customer Service System</span>
</div>
  </div>

  <nav class="sb-nav">
    <div class="nav-link active" id="nav-rec" onclick="showSection('records')">
      <span class="nav-icon"><span class="icon"><svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg></span></span>
      <span class="nav-text">All Records</span>
    </div>
    <div class="nav-link" id="nav-arch" onclick="showSection('archive')">
      <span class="nav-icon"><span class="icon"><svg viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg></span></span>
      <span class="nav-text">Archive</span>
    </div>
    <div class="nav-link" id="nav-bm" onclick="showSection('bookmarks')">
      <span class="nav-icon"><span class="icon"><svg viewBox="0 0 24 24"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></span></span>
      <span class="nav-text">Bookmarks</span>
    </div>
  </nav>

  <button class="sb-toggle" id="sbToggle" onclick="toggleSB()">
    <div class="toggle-icon">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    </div>
  </button>
</aside>

<!-- MAIN -->
<div class="main" id="mainWrap">

  <header class="topbar">
    <div class="topbar-title" id="pageTitle">All Records</div>
    <div class="topbar-right">
      <div class="tb-datetime">
        <div class="tb-date-block">
          <div class="tb-date-day" id="tbDateDay">—</div>
          <div class="tb-date-full" id="tbDateFull">— —</div>
        </div>
        <div class="tb-divider"></div>
        <div class="tb-time-block">
          <div class="tb-time" id="tbTime">00:00:00</div>
          <div class="tb-time-label">Local Time</div>
        </div>
      </div>
      <button class="btn-dark" id="darkToggle" onclick="toggleDark()" title="Toggle dark mode">
        <svg id="darkIcon" viewBox="0 0 24 24" style="width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
      </button>
      <button class="btn btn-plain" style="color:#fff;border-color:rgba(255,255,255,.25);font-size:12px" onclick="openReport()" title="Daily Summary Report">
        <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;margin-right:4px"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/><rect x="12" y="13" width="3" height="5" rx="1"/><rect x="16" y="11" width="3" height="7" rx="1"/></svg>
        Report
      </button>
      <button class="btn btn-yellow" onclick="openModal('add')">+ New Record</button>
    </div>
  </header>

  <div class="content">

    <!-- Records -->
    <div id="recordsSection">
      <div class="toolbar">
        <div class="tg">
          <label>Search</label>
          <input type="text" class="s-input" id="searchInput"
                 placeholder="Ref, account, name…" oninput="debounceLoad()">
        </div>
        <div class="tg">
          <label>Concern</label>
          <select id="fConcernF" onchange="loadRecs(1)"><option value="">All</option></select>
        </div>
        <div class="tg">
          <label>Area / Dept</label>
          <select id="fAreaF" onchange="loadRecs(1)"><option value="">All</option></select>
        </div>
        <div class="tg">
          <label>From</label>
          <input type="date" id="fFrom" onchange="loadRecs(1)">
        </div>
        <div class="tg">
          <label>To</label>
          <input type="date" id="fTo" onchange="loadRecs(1)">
        </div>
        <div class="tg" style="flex-direction:row;align-items:flex-end;gap:6px;margin-left:auto">
          <button class="btn btn-plain" onclick="clearF()">Clear</button>
          <div class="exp-wrap">
            <button class="btn btn-blue" onclick="toggleExp(event)">Export ▾</button>
            <div class="exp-menu" id="expMenu">
              <div class="exp-item" onclick="doExp('pdf')"><span class="icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg></span> PDF</div>
              <div class="exp-item" onclick="doExp('png')"><span class="icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></span> PNG</div>
              <div class="exp-item" onclick="doExp('excel')"><span class="icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg></span> Excel</div>
            </div>
          </div>
        </div>
      </div>

      <div class="table-wrap-outer">
        <div class="table-top">
          <span class="table-top-title">Customer Service Records</span>
          <span class="table-top-count" id="tblInfo">—</span>
        </div>
        <div class="t-scroll">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th class="sortable" data-col="reference_no">Reference No</th>
                <th class="sortable" data-col="account_number">Account No</th>
                <th class="sortable" data-col="account_name">Account Name</th>
                <th>Contact No</th>
                <th>Messenger / Caller</th>
                <th class="sortable" data-col="concern">Concern</th>
                <th class="sortable" data-col="area_dept">Area / Dept</th>
                <th class="sortable" data-col="date_forwarded">Date Forwarded</th>
                <th style="text-align:center">Actions</th>
              </tr>
            </thead>
            <tbody id="recBody">
              <tr class="load-row"><td colspan="10">Loading…</td></tr>
            </tbody>
          </table>
        </div>
        <div class="pg" id="pgBar"></div>
      </div>
    </div>

    <!-- Archive -->
    <div id="archiveSection">
      <div class="table-wrap-outer">
        <div class="table-top">
          <span class="table-top-title">Archived Records</span>
          <span class="table-top-count" id="archInfo">—</span>
        </div>
        <div class="t-scroll">
          <table>
            <thead>
              <tr>
                <th>#</th><th>Ref No</th><th>Account No</th><th>Account Name</th>
                <th>Contact No</th><th>Concern</th><th>Area / Dept</th>
                <th>Archived At</th><th>By</th>
                <th style="text-align:center">Actions</th>
              </tr>
            </thead>
            <tbody id="archBody">
              <tr class="load-row"><td colspan="11">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  <!-- Bookmarks -->
  <div id="bookmarksSection" style="display:none">
    <div class="table-wrap-outer">
      <div class="table-top">
        <span class="table-top-title"><span class="icon" style="margin-right:5px;vertical-align:middle"><svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></span>Bookmarks</span>
        <span class="table-top-count" id="bmInfo">—</span>
      </div>
      <div id="bmContent"></div>
    </div>
  </div>

  </div>
</div>

<!-- MODAL: Add/Edit -->
<div class="overlay" id="mForm">
  <div class="modal">
    <div class="modal-hd">
      <h3 id="mTitle">New Record</h3>
      <button class="modal-x" onclick="closeM('mForm')"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-bd">
      <input type="hidden" id="rId">
      <div class="fg">
        <div class="fg-row">
          <label>Reference No <span class="req">*</span></label>
          <input type="text" id="fRef" placeholder="e.g. CS-2024-00001">
        </div>
        <div class="fg-row">
          <label>Account Number <span class="req">*</span></label>
          <input type="text" id="fAccNo" placeholder="e.g. ACC-10001">
        </div>
        <div class="fg-row full">
          <label>Account Name <span class="req">*</span></label>
          <input type="text" id="fAccName" placeholder="Full name of account holder">
        </div>
        <div class="fg-row full">
          <label>Complete Address <span class="req">*</span></label>
          <input type="text" id="fAddr" placeholder="Street, Barangay, City, Province">
        </div>
        <div class="fg-row">
          <label>Landmark</label>
          <input type="text" id="fLandmark" placeholder="Near landmark">
        </div>
        <div class="fg-row">
          <label>Contact No <span class="req">*</span></label>
          <input type="text" id="fContact" placeholder="09XX-XXX-XXXX">
        </div>
        <div class="fg-row">
          <label>Messenger / Caller <span class="req">*</span></label>
          <input type="text" id="fCaller" placeholder="Name of caller">
        </div>
        <div class="fg-row">
          <label>Date Forwarded <span class="req">*</span></label>
          <input type="date" id="fDate">
        </div>
        <div class="fg-row">
          <label>Concern <span class="req">*</span></label>
          <select id="fConcern" onchange="handleManual('fConcern','fConcernManual')"><option value="">— Select —</option></select>
          <input type="text" id="fConcernManual" placeholder="Type concern manually…" style="display:none;margin-top:6px">
        </div>
        <div class="fg-row">
          <label>Area / Dept <span class="req">*</span></label>
          <select id="fArea" onchange="handleManual('fArea','fAreaManual')"><option value="">— Select —</option></select>
          <input type="text" id="fAreaManual" placeholder="Type area/dept manually…" style="display:none;margin-top:6px">
        </div>
        <div class="fg-row full">
          <label>Notes</label>
          <textarea id="fNotes" placeholder="Additional notes…"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-ft">
      <button class="btn btn-plain" onclick="closeM('mForm')">Cancel</button>
      <button class="btn btn-primary" id="saveBtn" onclick="saveRec()">
        <span id="saveTxt">Save</span>
      </button>
    </div>
  </div>
</div>

<!-- MODAL: View -->
<div class="overlay" id="mView">
  <div class="modal">
    <div class="modal-hd">
      <h3>Record Details</h3>
      <button class="modal-x" onclick="closeM('mView')"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-bd" id="viewBd"></div>
    <div class="modal-ft">
      <button class="btn btn-plain" onclick="closeM('mView')">Close</button>
    </div>
  </div>
</div>

<!-- MODAL: Delete -->
<div class="overlay" id="mDel">
  <div class="modal" style="max-width:400px">
    <div class="modal-hd" style="background:#c00;border-bottom-color:#900">
      <h3>Archive Record?</h3>
      <button class="modal-x" onclick="closeM('mDel')"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-bd">
      <p style="line-height:1.7;color:var(--text2)">
        This record will be moved to Archive.<br>You can restore it anytime.
      </p>
      <input type="hidden" id="delId">
    </div>
    <div class="modal-ft">
      <button class="btn btn-plain" onclick="closeM('mDel')">Cancel</button>
      <button class="btn btn-danger" onclick="doDel()">Move to Archive</button>
    </div>
  </div>
</div>

<!-- MODAL: Daily Report -->
<div class="overlay" id="mReport">
  <div class="modal" style="max-width:680px">
    <div class="modal-hd">
      <h3>Daily Summary Report</h3>
      <button class="modal-x" onclick="closeM('mReport')"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-bd">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
        <div class="tg" style="flex:1">
          <label style="font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px">Date</label>
          <input type="date" id="reportDate" style="width:100%" onchange="loadReport()">
        </div>
        <div id="reportBadge" style="padding-top:20px"></div>
      </div>
      <div id="reportBody"><div class="report-empty">Select a date to view the summary.</div></div>
    </div>
    <div class="modal-ft">
      <button class="btn btn-plain" onclick="closeM('mReport')">Close</button>
    </div>
  </div>
</div>

<!-- MODAL: Unsaved changes warning -->
<div class="overlay" id="mDirty">
  <div class="modal" style="max-width:380px">
    <div class="modal-hd" style="background:#b45309;border-bottom-color:#92400e">
      <h3>Unsaved Changes</h3>
      <button class="modal-x" onclick="closeM('mDirty')"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-bd">
      <p style="line-height:1.7;color:var(--text2)">You have unsaved changes. Are you sure you want to close without saving?</p>
    </div>
    <div class="modal-ft">
      <button class="btn btn-plain" onclick="closeM('mDirty')">Keep Editing</button>
      <button class="btn btn-danger" onclick="forceCloseForm()">Discard Changes</button>
    </div>
  </div>
</div>

<div class="toast-box" id="toastBox"></div>

<script>
let curPage=1, dtimer, isMini=false;

/* ── Bookmarks (localStorage) ── */
let bookmarks = {};
try { bookmarks = JSON.parse(localStorage.getItem('css_bookmarks')||'{}'); } catch(e){}
function saveBMs(){ try{ localStorage.setItem('css_bookmarks', JSON.stringify(bookmarks)); }catch(e){} }
function isBM(id){ return !!bookmarks[id]; }
function toggleBM(id, refNo, name){
  if(bookmarks[id]){
    delete bookmarks[id];
    toast('Bookmark removed','ok');
  } else {
    bookmarks[id]={ id, refNo, name, ts: Date.now() };
    toast('Bookmarked: '+refNo,'ok');
  }
  saveBMs();
  // Update button state
  const btn = document.getElementById('bm-'+id);
  if(btn){ btn.classList.toggle('bm-on', !!bookmarks[id]); }
  // Refresh bookmarks view if visible
  if(document.getElementById('bookmarksSection').style.display!=='none') renderBMs();
}

/* ── Render bookmarks list ── */
function renderBMs(){
  const items = Object.values(bookmarks).sort((a,b)=>b.ts-a.ts);
  document.getElementById('bmInfo').textContent = items.length+' bookmark'+(items.length===1?'':'s');
  const cont = document.getElementById('bmContent');
  if(!cont) return;
  if(!items.length){
    cont.innerHTML=`<div style="padding:56px 20px;text-align:center;color:var(--text3)">
      <div style="font-size:36px;margin-bottom:12px;opacity:.35"><span class="icon"><svg viewBox="0 0 24 24" style="width:40px;height:40px;stroke:currentColor;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></span></div>
      <div style="font-size:14px;font-weight:600;color:var(--text2);margin-bottom:4px">No bookmarks yet</div>
      <div style="font-size:12px">Click the bookmark icon on any record to bookmark it.</div>
    </div>`;
    return;
  }
  cont.innerHTML=items.map(b=>`
    <div style="display:flex;align-items:center;gap:12px;padding:11px 16px;border-bottom:1px solid var(--border)">
      <span style="font-size:16px;display:inline-flex;align-items:center"><svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#f59e0b;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></span>
      <div style="flex:1">
        <div style="font-weight:600;font-size:13px">${esc(b.name)}</div>
        <div style="font-size:11px;color:var(--text3);margin-top:2px;font-family:'JetBrains Mono',monospace">${esc(b.refNo)}</div>
      </div>
      <div style="font-size:11px;color:var(--text3)">${new Date(b.ts).toLocaleDateString('en-PH',{month:'short',day:'numeric'})}</div>
      <button class="btn btn-plain btn-sm" onclick="jumpToRecord(${b.id})">View</button>
      <button class="btn btn-plain btn-sm btn-icon" onclick="toggleBM(${b.id},'${esc(b.refNo)}','${esc(b.name)}')" style="color:var(--text3)"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>`).join('');
}

function jumpToRecord(id){
  showSection('records');

  loadRecs(1).then(()=>{
    setTimeout(()=>{
      const row = document.querySelector(`tr[data-id="${id}"]`);
      if(row){ row.scrollIntoView({behavior:'smooth',block:'center'}); row.style.background='#fffbeb'; setTimeout(()=>row.style.background='',1800); }
    },300);
  });
}

/* ── Clock ── */
;(function tick(){
  const n=new Date();
  const timeEl=document.getElementById('tbTime');
  // date handled separately
  if(timeEl) timeEl.textContent=n.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true});
  const dayEl=document.getElementById('tbDateDay');
  const fullEl=document.getElementById('tbDateFull');
  if(dayEl) dayEl.textContent=n.toLocaleDateString('en-PH',{weekday:'long',day:'numeric'});
  if(fullEl) fullEl.textContent=n.toLocaleDateString('en-PH',{month:'long',year:'numeric'});
  setTimeout(tick,1000);
})();

/* ── Sidebar ── */
function toggleSB(){
  isMini=!isMini;
  document.getElementById('sb').classList.toggle('mini',isMini);
  document.getElementById('mainWrap').classList.toggle('mini',isMini);
}

/* ── Init ── */
(async()=>{
  try{ await loadDropCounts(); }catch(e){}
  try{ await loadDrops(); }catch(e){}
  try{ await loadRecs(1); }catch(e){}
  document.getElementById('fDate').value=new Date().toISOString().slice(0,10);
})();

/* ── Section ── */
function showSection(s){
  document.getElementById('recordsSection').style.display=s==='records'?'block':'none';
  document.getElementById('archiveSection').style.display=s==='archive'?'block':'none';
  document.getElementById('bookmarksSection').style.display=s==='bookmarks'?'block':'none';
  document.getElementById('nav-rec').classList.toggle('active',s==='records');
  document.getElementById('nav-arch').classList.toggle('active',s==='archive');
  document.getElementById('nav-bm').classList.toggle('active',s==='bookmarks');
  const titles={records:'All Records',archive:'Archive',bookmarks:'Bookmarks'};
  document.getElementById('pageTitle').textContent=titles[s]||'';
  if(s==='archive') loadArchive();
  if(s==='bookmarks') renderBMs();
}

/* ── Dropdowns ── */
async function loadDrops(){
  const r=await g('api.php?action=dropdowns');
  if(!r?.success) return;
  fill('fConcernF',r.concern,'All');
  fill('fAreaF',r.area_dept,'All');
  fill('fConcern',r.concern,'— Select —');
  fill('fArea',r.area_dept,'— Select —');
}
function fill(id,opts,ph){
  const el=document.getElementById(id),cur=el.value;
  const isFormField   = ['fConcern','fArea'].includes(id);
  const isFilterField = ['fConcernF','fAreaF'].includes(id);
  const countKey      = id==='fConcernF'?'concern': id==='fAreaF'?'area_dept':null;
  el.innerHTML=`<option value="">${ph}</option>`;
  (opts||[]).forEach(o=>{
    if(o==='__OTHER__') return;
    const op=document.createElement('option');
    op.value=o;
    if(isFilterField && countKey && dropCounts[countKey]){
      const cnt=dropCounts[countKey][o]||0;
      op.textContent= cnt>0 ? `${o} (${cnt})` : o;
    } else {
      op.textContent=o;
    }
    el.appendChild(op);
  });
  if(isFormField){
    const manual=document.createElement('option');
    manual.value='__manual__'; manual.textContent='Other (specify)…';
    el.appendChild(manual);
  }
  if(isFilterField && (opts||[]).includes('__OTHER__')){
    const cntOther = countKey && dropCounts[countKey]
      ? Object.entries(dropCounts[countKey]).filter(([k])=>!(opts||[]).filter(o=>o!=='__OTHER__').includes(k)).reduce((a,[,v])=>a+v,0)
      : 0;
    const other=document.createElement('option');
    other.value='__OTHER__';
    other.textContent = cntOther>0 ? `Other (${cntOther})` : 'Other';
    el.appendChild(other);
  }
  if(cur) el.value=cur;
}

/* ── Manual Input Toggle ── */
function handleManual(selId, inputId){
  const sel=document.getElementById(selId);
  const inp=document.getElementById(inputId);
  if(sel.value==='__manual__'){
    inp.style.display='block';
    inp.focus();
  } else {
    inp.style.display='none';
    inp.value='';
  }
}
/* Set dropdown — fall back to manual if value not in options */
function setSelectOrManual(selId, inputId, val){
  const sel=document.getElementById(selId);
  const inp=document.getElementById(inputId);
  // Try to set the select normally
  sel.value=val;
  if(sel.value===val){
    inp.style.display='none'; inp.value='';
  } else {
    // Value not in list — use manual
    sel.value='__manual__';
    inp.style.display='block';
    inp.value=val;
  }
}

function getFieldVal(selId, inputId){
  const sel=document.getElementById(selId);
  if(sel.value==='__manual__'){
    return document.getElementById(inputId).value.trim();
  }
  return sel.value;
}

/* ── Records ── */
async function loadRecs(p){
  curPage=p||curPage;
  const body=document.getElementById('recBody');
  body.innerHTML='<tr class="load-row"><td colspan="10">Loading…</td></tr>';
  const r=await g('api.php?'+new URLSearchParams({action:'list',page:curPage,...getF()}));
  if(!r?.success){ body.innerHTML='<tr class="load-row"><td colspan="10">Failed to load.</td></tr>'; return; }
  document.getElementById('tblInfo').textContent=`${r.total} record${r.total===1?'':'s'}`;
  if(!r.data.length){
    body.innerHTML=`<tr><td colspan="10"><div class="empty">
      <div class="empty-ico"><span class="icon"><svg viewBox="0 0 24 24" style="width:40px;height:40px;stroke:currentColor;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;opacity:.4"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></span></div>
      <div class="empty-title">No records found</div>
      <div class="empty-sub">Try adjusting filters</div>
    </div></td></tr>`;
    document.getElementById('pgBar').innerHTML=''; return;
  }
  const off=(curPage-1)*15;
  body.innerHTML=r.data.map((row,i)=>`
    <tr data-id="${row.id}">
      <td style="color:var(--text3);font-size:12px">${off+i+1}</td>
      <td><span class="ref-tag">${esc(row.reference_no)}</span></td>
      <td>${esc(row.account_number)}</td>
      <td>
        <div class="cell-main">${esc(row.account_name)}</div>
        <div class="cell-sub">${esc((row.address||'').substring(0,40))}${(row.address||'').length>40?'…':''}</div>
      </td>
      <td>${esc(row.contact_no)}</td>
      <td>${esc(row.messenger_caller)}</td>
      <td>${esc(row.concern)}</td>
      <td>${esc(row.area_dept)}</td>
      <td style="white-space:nowrap;font-size:12.5px">${fmtD(row.date_forwarded)}</td>
      <td>
        <div class="actions" style="justify-content:center">
          <button class="btn btn-plain btn-sm btn-icon" title="View"     onclick="viewRec(${row.id})"><span class="icon"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></span></button>
          <button class="btn btn-primary btn-sm btn-icon" title="Edit"    onclick="openModal('edit',${row.id})"><span class="icon"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span></button>
          <button class="btn btn-bm btn-sm btn-icon ${isBM(row.id)?'bm-on':''}" title="${isBM(row.id)?'Remove Bookmark':'Bookmark'}" id="bm-${row.id}" onclick="toggleBM(${row.id},'${esc(row.reference_no)}','${esc(row.account_name)}')"><span class="icon bm-icon"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></span></button>
          <button class="btn btn-danger btn-sm btn-icon" title="Archive"  onclick="openDel(${row.id})"><span class="icon"><svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg></span></button>
        </div>
      </td>
    </tr>`).join('');
  renderPg(curPage,r.pages,r.total);
}

function getF(){ return {
  search: document.getElementById('searchInput').value,
  concern: document.getElementById('fConcernF').value,
  area: document.getElementById('fAreaF').value,
  date_from: document.getElementById('fFrom').value,
  date_to: document.getElementById('fTo').value,
  sort_col: sortCol,
  sort_dir: sortDir,
};}
function clearF(){
  ['searchInput','fConcernF','fAreaF','fFrom','fTo'].forEach(id=>document.getElementById(id).value='');
  loadRecs(1);
}

/* ── Archive ── */
async function loadArchive(){
  const body=document.getElementById('archBody');
  body.innerHTML='<tr class="load-row"><td colspan="11">Loading…</td></tr>';
  const r=await g('api.php?action=list_archive');
  const d=r?.data||[];
  document.getElementById('archInfo').textContent=`${d.length} record${d.length===1?'':'s'}`;
  if(!d.length){ body.innerHTML=`<tr><td colspan="11"><div class="empty"><div class="empty-ico"><span class="icon"><svg viewBox="0 0 24 24" style="width:40px;height:40px;stroke:currentColor;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;opacity:.4"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg></span></div><div class="empty-title">Archive is empty</div></div></td></tr>`; return; }
  body.innerHTML=d.map((row,i)=>`
    <tr>
      <td style="color:var(--text3);font-size:12px">${i+1}</td>
      <td><span class="ref-tag">${esc(row.reference_no)}</span></td>
      <td>${esc(row.account_number)}</td>
      <td><div class="cell-main">${esc(row.account_name)}</div></td>
      <td>${esc(row.contact_no)}</td>
      <td>${esc(row.concern)}</td>
      <td>${esc(row.area_dept)}</td>
      <td style="font-size:12px;white-space:nowrap">${fmtDT(row.archived_at)}</td>
      <td>${esc(row.archived_by)}</td>
      <td><div class="actions" style="justify-content:center">
        <button class="btn btn-primary btn-sm" onclick="restoreRec(${row.id})">Restore</button>
      </div></td>
    </tr>`).join('');
}
async function restoreRec(id){
  const r=await p('api.php',{action:'restore',id});
  if(r?.success){ toast(r.message,'ok'); loadArchive(); }
  else toast(r?.message||'Failed.','err');
}

/* ── Modal ── */
async function openModal(mode,id=null){
  document.getElementById('mTitle').textContent=mode==='add'?'New Record':'Edit Record';
  document.getElementById('saveTxt').textContent=mode==='add'?'Save':'Update';
  clearForm();
  document.getElementById('fDate').value=new Date().toISOString().slice(0,10);
  if(mode==='edit'&&id){
    const r=await g(`api.php?action=get&id=${id}`);
    if(!r?.success){ toast('Failed to load','err'); return; }
    const d=r.data;
    document.getElementById('rId').value=d.id;
    document.getElementById('fRef').value=d.reference_no;
    document.getElementById('fAccNo').value=d.account_number;
    document.getElementById('fAccName').value=d.account_name;
    document.getElementById('fAddr').value=d.address;
    document.getElementById('fLandmark').value=d.landmark||'';
    document.getElementById('fContact').value=d.contact_no;
    document.getElementById('fCaller').value=d.messenger_caller;
    document.getElementById('fDate').value=d.date_forwarded;
    setSelectOrManual('fConcern','fConcernManual',d.concern);
    setSelectOrManual('fArea','fAreaManual',d.area_dept);
    document.getElementById('fNotes').value=d.notes||'';
  }
  openOv('mForm');
}
function clearForm(){
  ['rId','fRef','fAccNo','fAccName','fAddr','fLandmark','fContact','fCaller','fDate','fNotes']
    .forEach(id=>document.getElementById(id).value='');
  document.getElementById('fConcern').value='';
  document.getElementById('fArea').value='';
  ['fConcernManual','fAreaManual'].forEach(id=>{
    const el=document.getElementById(id);
    el.value=''; el.style.display='none';
  });
  clearDirty();
  // Attach dirty listeners once
  if(!document._dirtyListened){
    document._dirtyListened=true;
    ['fRef','fAccNo','fAccName','fAddr','fLandmark','fContact','fCaller','fDate','fNotes','fConcernManual','fAreaManual']
      .forEach(id=>{ const el=document.getElementById(id); if(el) el.addEventListener('input',markDirty); });
    ['fConcern','fArea'].forEach(id=>{ const el=document.getElementById(id); if(el) el.addEventListener('change',markDirty); });
  }
  // Attach Enter key listeners for form navigation
  if(!document._enterListened){
    document._enterListened=true;
    const formFields=['fRef','fAccNo','fAccName','fAddr','fLandmark','fContact','fCaller','fDate','fConcern','fArea','fNotes'];
    formFields.forEach((id,idx)=>{
      const el=document.getElementById(id);
      if(el) el.addEventListener('keydown',(e)=>{
        if(e.key==='Enter'){
          e.preventDefault();
          if(idx<formFields.length-1){
            document.getElementById(formFields[idx+1]).focus();
          } else {
            document.getElementById('saveBtn').focus();
          }
        }
      });
    });
  }
}
async function saveRec(){
  const id=document.getElementById('rId').value;
  const simpleReq={fRef:'Reference No',fAccNo:'Account Number',fAccName:'Account Name',
              fAddr:'Address',fContact:'Contact No',fCaller:'Messenger/Caller',fDate:'Date Forwarded'};
  for(const [fid,lbl] of Object.entries(simpleReq)){
    if(!document.getElementById(fid).value.trim()){ toast(`${lbl} is required`,'err'); document.getElementById(fid).focus(); return; }
  }
  const concernVal = getFieldVal('fConcern','fConcernManual');
  const areaVal    = getFieldVal('fArea','fAreaManual');
  if(!concernVal){ toast('Concern is required','err'); document.getElementById('fConcern').focus(); return; }
  if(!areaVal){    toast('Area/Dept is required','err'); document.getElementById('fArea').focus(); return; }
  const btn=document.getElementById('saveBtn');
  btn.disabled=true; btn.innerHTML='<span class="spin"></span>';
  const data={
    action:id?'update':'create', id:id||'',
    reference_no:     document.getElementById('fRef').value,
    account_number:   document.getElementById('fAccNo').value,
    account_name:     document.getElementById('fAccName').value,
    address:          document.getElementById('fAddr').value,
    landmark:         document.getElementById('fLandmark').value,
    contact_no:       document.getElementById('fContact').value,
    messenger_caller: document.getElementById('fCaller').value,
    date_forwarded:   document.getElementById('fDate').value,
    concern:          concernVal,
    area_dept:        areaVal,
    notes:            document.getElementById('fNotes').value,
  };
  const r=await p('api.php',data);
  btn.disabled=false; btn.innerHTML='<span id="saveTxt">Save</span>';
  if(r?.success){ clearDirty(); document.getElementById('mForm').classList.remove('open'); toast(r.message,'ok'); loadRecs(curPage); }
  else toast(r?.message||'Save failed.','err');
}

/* ── View ── */
async function viewRec(id){
  const r=await g(`api.php?action=get&id=${id}`);
  if(!r?.success){ toast('Failed to load','err'); return; }
  const d=r.data;
  document.getElementById('viewBd').innerHTML=`
    <div class="detail-grid">
      ${dr('Reference No',`<span class="ref-tag">${esc(d.reference_no)}</span>`)}
      ${dr('Account Number',d.account_number)}
      ${dr('Account Name',`<strong>${esc(d.account_name)}</strong>`)}
      ${dr('Address',d.address,'full')}
      ${dr('Landmark',d.landmark||'—')}
      ${dr('Contact No',d.contact_no)}
      ${dr('Messenger / Caller',d.messenger_caller)}
      ${dr('Concern',d.concern)}
      ${dr('Area / Dept',d.area_dept)}
      ${dr('Date Forwarded',fmtD(d.date_forwarded))}
      ${dr('Created At',fmtDT(d.created_at))}
      ${d.notes?dr('Notes',d.notes,'full'):''}
    </div>`;
  openOv('mView');
}
function dr(lbl,val,cls=''){
  return `<div class="d-row${cls?' '+cls:''}"><label>${lbl}</label><span>${val||'—'}</span></div>`;
}

/* ── Delete ── */
function openDel(id){ document.getElementById('delId').value=id; openOv('mDel'); }
async function doDel(){
  const id=document.getElementById('delId').value;
  const r=await p('api.php',{action:'delete',id,archived_by:'admin'});
  if(r?.success){ toast(r.message,'ok'); closeM('mDel'); loadRecs(curPage); }
  else toast(r?.message||'Error.','err');
}

/* ── Export ── */
function toggleExp(ev){ ev.stopPropagation(); document.getElementById('expMenu').classList.toggle('open'); }
document.addEventListener('click',()=>document.getElementById('expMenu').classList.remove('open'));
function doExp(fmt){
  window.open('export.php?'+new URLSearchParams({...getF(),format:fmt}),'_blank');
  document.getElementById('expMenu').classList.remove('open');
}

/* ── Pagination ── */
function renderPg(page,pages,total){
  const el=document.getElementById('pgBar');
  if(pages<=1){ el.innerHTML=`<div class="pg-info">${total} record${total===1?'':'s'}</div>`; return; }
  let h=`<button class="pg-btn" onclick="loadRecs(${page-1})" ${page===1?'disabled':''}>‹</button>`;
  Array.from({length:pages},(_,i)=>i+1)
    .filter(p=>p===1||p===pages||Math.abs(p-page)<=2)
    .forEach((pp,idx,arr)=>{
      if(idx>0&&pp-arr[idx-1]>1) h+=`<span class="pg-btn" style="pointer-events:none;border:none">…</span>`;
      h+=`<button class="pg-btn${pp===page?' on':''}" onclick="loadRecs(${pp})">${pp}</button>`;
    });
  h+=`<button class="pg-btn" onclick="loadRecs(${page+1})" ${page===pages?'disabled':''}>›</button>`;
  h+=`<div class="pg-info">Page ${page}/${pages} · ${total} records</div>`;
  el.innerHTML=h;
}

/* ── API ── */
async function g(url){ try{ const r=await fetch(url); return r.json(); }catch(e){ return null; } }
async function p(url,data){ try{ const r=await fetch(url,{method:'POST',body:new URLSearchParams(data)}); return r.json(); }catch(e){ return null; } }

/* ── Overlay ── */
function openOv(id){ document.getElementById(id).classList.add('open'); }
function closeM(id){
  if(id==='mForm' && formDirty){ openOv('mDirty'); return; }
  document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.overlay').forEach(el=>
  el.addEventListener('click',ev=>{ if(ev.target===el) el.classList.remove('open'); })
);

/* ── Debounce ── */
function debounceLoad(){ clearTimeout(dtimer); dtimer=setTimeout(()=>loadRecs(1),380); }

/* ── Toast ── */
function toast(msg,type='ok'){
  const c=document.getElementById('toastBox'),t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<span>${type==='ok'?'<svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><polyline points="20 6 9 17 4 12"/></svg>':'<svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'}</span><span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{ t.style.opacity='0'; t.style.transition='.3s'; setTimeout(()=>t.remove(),320); },3000);
}

/* ── Dark Mode ── */
(function(){
  if(localStorage.getItem('css_dark')==='1'){
    document.body.classList.add('dark');
    // Set sun icon immediately without animation on load
    document.addEventListener('DOMContentLoaded',()=>{
      const svg = document.getElementById('darkIcon');
      if(svg) svg.innerHTML = `<circle cx="12" cy="12" r="5"/>
        <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
        <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>`;
    });
  }
})();

function applyDark(on, save=true){
  document.body.classList.toggle('dark', on);
  const btn  = document.getElementById('darkToggle');
  const svg  = btn ? btn.querySelector('svg') : null;

  if(svg){
    // Spin out → swap icon → spin back in
    svg.style.transform = 'rotate(90deg) scale(0)';
    svg.style.opacity   = '0';
    setTimeout(()=>{
      // Clear inner SVG and set correct icon paths
      svg.innerHTML = on
        ? `<circle cx="12" cy="12" r="5"/>
           <line x1="12" y1="1" x2="12" y2="3"/>
           <line x1="12" y1="21" x2="12" y2="23"/>
           <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
           <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
           <line x1="1" y1="12" x2="3" y2="12"/>
           <line x1="21" y1="12" x2="23" y2="12"/>
           <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
           <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>`
        : `<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>`;
      svg.style.transform = 'rotate(-90deg) scale(0)';
      svg.style.opacity   = '0';
      requestAnimationFrame(()=>{
        svg.style.transition = 'transform .35s cubic-bezier(.34,1.56,.64,1), opacity .25s';
        svg.style.transform  = 'rotate(0deg) scale(1)';
        svg.style.opacity    = '1';
      });
    }, 160);
  }



  
  if(save) localStorage.setItem('css_dark', on?'1':'0');
}

function toggleDark(){
  const on = !document.body.classList.contains('dark');
  applyDark(on);
}

/* ── Sorting ── */
let sortCol='created_at', sortDir='desc';
document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('thead th.sortable').forEach(th=>{
    th.addEventListener('click',()=>{
      const col = th.dataset.col;
      if(sortCol===col) sortDir = sortDir==='asc'?'desc':'asc';
      else { sortCol=col; sortDir='asc'; }
      document.querySelectorAll('thead th.sortable').forEach(h=>h.classList.remove('sort-asc','sort-desc'));
      th.classList.add(sortDir==='asc'?'sort-asc':'sort-desc');
      loadRecs(1);
    });
  });
});

/* ── Dropdown counts ── */
let dropCounts = {};
async function loadDropCounts(){
  const r = await g('api.php?action=dropdown_counts');
  if(r?.success) dropCounts = r.counts;
}

/* ── Report ── */
async function openReport(){
  const d = document.getElementById('reportDate');
  if(!d.value) d.value = new Date().toISOString().slice(0,10);
  openOv('mReport');
  await loadReport();
}
async function loadReport(){
  const date = document.getElementById('reportDate').value;
  if(!date){ document.getElementById('reportBody').innerHTML='<div class="report-empty">Select a date.</div>'; return; }
  document.getElementById('reportBody').innerHTML='<div class="report-empty">Loading…</div>';
  const r = await g('api.php?action=daily_summary&date='+encodeURIComponent(date));
  if(!r?.success){ document.getElementById('reportBody').innerHTML='<div class="report-empty">Failed to load.</div>'; return; }
  document.getElementById('reportBadge').innerHTML = r.total>0
    ? `<span style="background:rgba(3,125,60,.1);border:1px solid rgba(3,125,60,.2);border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;color:var(--green)">${r.total} record${r.total===1?'':'s'}</span>`
    : '';
  if(!r.total){
    document.getElementById('reportBody').innerHTML='<div class="report-empty">No records for this date.</div>';
    return;
  }
  const maxC = Math.max(...r.by_concern.map(x=>x.cnt), 1);
  const maxA = Math.max(...r.by_area.map(x=>x.cnt), 1);
  const rowsC = r.by_concern.map(x=>`
    <div class="report-row">
      <span class="report-label" title="${esc(x.concern)}">${esc(x.concern)||'<em>—</em>'}</span>
      <div class="report-bar-wrap"><div class="report-bar" style="width:${Math.round(x.cnt/maxC*100)}%"></div></div>
      <span class="report-cnt">${x.cnt}</span>
    </div>`).join('');
  const rowsA = r.by_area.map(x=>`
    <div class="report-row">
      <span class="report-label" title="${esc(x.area_dept)}">${esc(x.area_dept)||'<em>—</em>'}</span>
      <div class="report-bar-wrap"><div class="report-bar" style="width:${Math.round(x.cnt/maxA*100)}%"></div></div>
      <span class="report-cnt">${x.cnt}</span>
    </div>`).join('');
  document.getElementById('reportBody').innerHTML=`
    <div class="report-grid">
      <div class="report-section"><h4>By Concern</h4>${rowsC}</div>
      <div class="report-section"><h4>By Area / Dept</h4>${rowsA}</div>
    </div>`;
}


let formDirty = false;
function markDirty(){ formDirty = true; const t=document.getElementById('mTitle'); if(t&&!t.querySelector('.dirty-dot')){ const d=document.createElement('span'); d.className='dirty-dot'; t.appendChild(d); } }
function clearDirty(){ formDirty=false; const d=document.querySelector('#mTitle .dirty-dot'); if(d) d.remove(); }
function forceCloseForm(){ clearDirty(); closeM('mDirty'); closeM('mForm'); }


function esc(s){ const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }
function fmtD(s){ if(!s) return '—'; return new Date(s+'T00:00:00').toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'}); }
function fmtDT(s){ if(!s) return '—'; return new Date(s).toLocaleString('en-PH',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}); }
function mkBadge(s){
  const m={'Open':'b-open','In Progress':'b-prog','Resolved':'b-res','Closed':'b-cls'};
  return `<span class="badge ${m[s]||'b-cls'}">${s||'—'}</span>`;
}
</script>
</body>
</html>