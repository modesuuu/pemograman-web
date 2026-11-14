<?php
session_start();

// belum login? jangan kasih lihat dashboard, lempar ke halaman login dulu
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$namaUser = $_SESSION['nama'] ?? 'Pengguna';
$roleUser = $_SESSION['role'] ?? 'user';

// inisial buat avatar, ambil huruf depan dari dua kata pertama namanya
$kataNama = explode(' ', trim($namaUser));
$inisialUser = strtoupper(substr($kataNama[0], 0, 1) . (isset($kataNama[1]) ? substr($kataNama[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edu-Center · Dashboard</title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%235D5FEF'/%3E%3Ctext x='16' y='22' font-family='sans-serif' font-size='18' font-weight='700' fill='white' text-anchor='middle'%3EE%3C/text%3E%3C/svg%3E">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<style>
  /* Theme tokens. Every text/background pair below was measured with the
     WCAG contrast checker: normal text >= 4.5:1, large text >= 3:1. */
  :root, [data-theme="light"] {
    --brand: #5D5FEF;
    --brand-ink: #ffffff;            /* 4.83:1 on --brand */
    --page-bg: #f5f7fb;
    --card-bg: #ffffff;
    --card-border: #e6ebf2;
    --ink: #0f172a;                  /* slate-900 */
    --muted: #64748b;                /* 4.76:1 on white */
    --trend: #15803d;                /* 5.02:1 on white */
    --focus: #5D5FEF;
    --date-indigo-bg: #e0e7ff; --date-indigo-fg: #312e81;  /* 9.27:1 */
    --date-green-bg: #dcfce7;  --date-green-fg: #14532d;   /* >= 4.5:1 */
    --input-bg: #ffffff;
    --overlay: rgba(15, 23, 42, 0.45);
    --chart-grid: rgba(100, 116, 139, 0.16);
    --chart-tick: #64748b;
  }
  [data-theme="dark"] {
    --page-bg: #10141b;
    --card-bg: #1a212b;
    --card-border: rgba(255, 255, 255, 0.09);
    --ink: #f1f5f9;
    --muted: #9aa4b2;                /* 6.28:1 on #1d232a */
    --trend: #4ade80;                /* 9.09:1 on #1d232a */
    --focus: #c7d2fe;                /* 10.62:1 on #1d232a */
    --date-indigo-bg: #312e81; --date-indigo-fg: #e0e7ff;  /* 9.27:1 */
    --date-green-bg: #14532d;  --date-green-fg: #bbf7d0;   /* 7.52:1 */
    --input-bg: #232c38;
    --overlay: rgba(0, 0, 0, 0.6);
    --chart-grid: rgba(154, 164, 178, 0.16);
    --chart-tick: #9aa4b2;
  }
  html { scroll-behavior: smooth; }
  body {
    font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
    background: var(--page-bg);
    color: var(--ink);
    font-variant-numeric: tabular-nums;
  }
  /* Visible keyboard focus everywhere. Never removed without replacement. */
  :is(a, button, input, select, textarea, [tabindex]):focus-visible {
    outline: 3px solid var(--focus);
    outline-offset: 2px;
    border-radius: 6px;
  }
  .card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 1rem;
  }
<?php if ($roleUser !== 'admin'): ?>
  [data-open-modal] { display: none !important; }
<?php endif; ?>
  .btn-brand {
    background: var(--brand);
    color: var(--brand-ink);
    border-radius: 0.75rem;
    font-weight: 700;
    transition: filter 150ms ease, transform 150ms ease;
  }
  .btn-brand:hover { filter: brightness(1.07); }
  .btn-brand:active { transform: translateY(1px); }
  .btn-ghost2 {
    border: 1px solid var(--card-border);
    background: var(--card-bg);
    color: var(--ink);
    border-radius: 0.75rem;
    font-weight: 600;
    transition: border-color 150ms ease, background 150ms ease;
  }
  .btn-ghost2:hover { border-color: var(--brand); }
  .field {
    background: var(--input-bg);
    border: 1px solid var(--card-border);
    border-radius: 0.75rem;
    color: var(--ink);
    width: 100%;
  }
  .field::placeholder { color: var(--muted); opacity: 1; }
  .field[aria-invalid="true"] { border-color: #dc2626; }
  .navlink {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.65rem 0.9rem;
    border-radius: 0.75rem;
    color: #ffffff;
    font-weight: 600; font-size: 0.925rem;
    transition: background 150ms ease;
    width: 100%; text-align: left;
  }
  .navlink:hover { background: rgba(255, 255, 255, 0.12); }
  .navlink[aria-current="page"] { background: #ffffff; color: var(--brand); }
  .navlink svg { flex: none; }
  .soon {
    margin-left: auto;
    font-size: 0.65rem; font-weight: 700;
    letter-spacing: 0.04em; text-transform: uppercase;
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
  }
  .navlink[aria-current="page"] .soon { background: rgba(93, 95, 239, 0.12); color: var(--brand); }
  .kpi-hero-num { font-size: clamp(2.6rem, 5vw, 3.6rem); line-height: 1; font-weight: 800; letter-spacing: -0.02em; }
  .modal-panel { animation: pop 160ms ease-out; }
  @keyframes pop { from { transform: translateY(8px) scale(0.98); opacity: 0; } to { transform: none; opacity: 1; } }
  .toast-in { animation: slideup 180ms ease-out; }
  @keyframes slideup { from { transform: translateY(10px); opacity: 0; } to { transform: none; opacity: 1; } }
  @media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    .modal-panel, .toast-in { animation: none; }
    * { transition: none !important; }
  }
  .visually-hidden {
    position: absolute; width: 1px; height: 1px;
    clip-path: inset(50%); overflow: hidden; white-space: nowrap;
  }
  /* Hide the scrollbar but keep scrolling (wheel, touch, keyboard all still work) */
  .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
</style>
</head>
<body class="min-h-screen antialiased">

<a href="#main" class="visually-hidden">Skip to main content</a>

<div class="flex min-h-screen">

  <!-- ============ SIDEBAR ============ -->
  <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden lg:hidden" style="background: var(--overlay)" data-close-sidebar></div>
  <aside id="sidebar"
         class="fixed lg:sticky top-0 z-40 h-screen w-72 shrink-0 -translate-x-full lg:translate-x-0 transition-transform duration-200 flex flex-col p-5"
         style="background: var(--brand)" aria-label="Primary">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="grid h-11 w-11 place-items-center rounded-xl bg-white text-xl font-extrabold" style="color: var(--brand)" aria-hidden="true">E</span>
        <span>
          <span class="block text-lg font-extrabold leading-tight text-white">Edu-Center</span>
          <span class="block text-xs font-medium text-white">School dashboard</span>
        </span>
      </div>
      <button id="sidebarClose" class="lg:hidden rounded-lg p-2 text-white hover:bg-white/10" aria-label="Close navigation">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>

    <nav class="no-scrollbar mt-8 flex-1 overflow-y-auto" aria-label="Sections">
      <p class="px-3 text-xs font-bold uppercase tracking-wider text-white">Menu</p>
      <ul class="mt-2 space-y-1" id="navMain">
        <li><a class="navlink" href="#overview" aria-current="page">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/></svg>
          Dashboard</a></li>
        <li><a class="navlink" href="#performance">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18"/><path d="M6 16l4.5-5 3.5 3 5-6.5"/></svg>
          Performance</a></li>
        <li><a class="navlink" href="#events">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="16" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg>
          Events</a></li>
      </ul>

      <p class="mt-7 px-3 text-xs font-bold uppercase tracking-wider text-white">General</p>
      <ul class="mt-2 space-y-1">
        <li><button class="navlink" data-soon="Students"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.8-3.4 2.9-5 5.5-5s4.7 1.6 5.5 5"/><circle cx="17" cy="9" r="2.6"/><path d="M15.5 14.6c2.9.1 4.4 1.7 5 4.4"/></svg>Students <span class="soon">Soon</span></button></li>
        <li><a class="navlink" href="mata_kuliah.php"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></svg>Mata Kuliah</a></li>
        <li><button class="navlink" data-soon="Finance"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18M7 15.5h4"/></svg>Finance <span class="soon">Soon</span></button></li>
        <li><button class="navlink" data-soon="Calendar"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="16" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/><circle cx="12" cy="15.5" r="1.4" fill="currentColor" stroke="none"/></svg>Calendar <span class="soon">Soon</span></button></li>
        <li><button class="navlink" data-soon="Chat"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a8 8 0 0 1-8 8H4l2.3-2.9A8 8 0 1 1 21 12z"/></svg>Chat <span class="soon">Soon</span></button></li>
        <li><button class="navlink" data-soon="Notifications"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9a6 6 0 1 0-12 0c0 6-2.5 7-2.5 7h17S18 15 18 9z"/><path d="M10 20a2.2 2.2 0 0 0 4 0"/></svg>Notifications <span class="soon">Soon</span></button></li>
      </ul>
    </nav>

    <div class="mt-6 flex items-center gap-3 rounded-xl bg-black/15 p-3">
      <span class="grid h-10 w-10 flex-none place-items-center rounded-full bg-white text-sm font-extrabold" style="color: var(--brand)" aria-hidden="true"><?= htmlspecialchars($inisialUser) ?></span>
      <span class="min-w-0">
        <span class="block truncate text-sm font-bold text-white"><?= htmlspecialchars($namaUser) ?></span>
        <span class="block text-xs text-white"><?= $roleUser === 'admin' ? 'Administrator' : 'User Biasa (Read Only)' ?></span>
        <span class="block text-xs mt-1"><a href="logout.php" class="font-bold text-white underline">Logout</a></span>
      </span>
    </div>
  </aside>

  <!-- ============ MAIN ============ -->
  <div class="min-w-0 flex-1">
    <main id="main" class="mx-auto max-w-6xl px-4 pb-16 pt-4 sm:px-6 lg:px-8 lg:pt-6">

      <!-- Top bar -->
      <div class="card mb-6 flex items-center gap-3 px-4 py-3">
        <button id="sidebarOpen" class="rounded-lg p-2 lg:hidden" style="color: var(--ink)" aria-label="Open navigation" aria-expanded="false" aria-controls="sidebar">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <p class="hidden text-sm font-semibold md:block" style="color: var(--muted)">Edu-Center overview</p>
        <div class="ml-auto flex items-center gap-2 sm:gap-3">
          <button id="themeToggle" class="rounded-lg p-2.5" style="color: var(--ink)" aria-label="Switch to dark mode">
            <svg id="iconMoon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13.5A8 8 0 0 1 10.5 4 8 8 0 1 0 20 13.5z"/></svg>
            <svg id="iconSun" class="hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.6M12 18.9v2.6M2.5 12h2.6M18.9 12h2.6M5 5l1.8 1.8M17.2 17.2L19 19M19 5l-1.8 1.8M6.8 17.2L5 19"/></svg>
          </button>
          <div class="relative">
            <button id="bellBtn" class="rounded-lg p-2.5" style="color: var(--ink)" aria-label="Notifications" aria-expanded="false" aria-haspopup="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9a6 6 0 1 0-12 0c0 6-2.5 7-2.5 7h17S18 15 18 9z"/><path d="M10 20a2.2 2.2 0 0 0 4 0"/></svg>
            </button>
            <div id="bellPanel" class="card absolute right-0 z-40 mt-2 hidden w-72 p-5 text-center shadow-lg" role="region" aria-label="Notifications">
              <svg class="mx-auto" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--muted)" aria-hidden="true"><path d="M18 9a6 6 0 1 0-12 0c0 6-2.5 7-2.5 7h17S18 15 18 9z"/><path d="M10 20a2.2 2.2 0 0 0 4 0"/></svg>
              <p class="mt-3 font-bold">You are all caught up</p>
              <p class="mt-1 text-sm" style="color: var(--muted)">No new notifications.</p>
            </div>
          </div>
          <div class="flex items-center gap-2.5 pl-1">
            <span class="grid h-10 w-10 place-items-center rounded-full text-sm font-extrabold text-white" style="background: var(--brand)" aria-hidden="true"><?= htmlspecialchars($inisialUser) ?></span>
            <span class="hidden sm:block">
              <span class="block text-sm font-bold leading-tight"><?= htmlspecialchars($namaUser) ?></span>
              <span class="block text-xs" style="color: var(--muted)"><?= $roleUser === 'admin' ? 'Administrator' : 'User Biasa (Read Only)' ?></span>
            </span>
          </div>
        </div>
      </div>

      <!-- Header -->
      <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 id="greeting" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Welcome back, <?= htmlspecialchars(strtok($namaUser, ' ')) ?>.</h1>
          <p class="mt-1 text-sm" style="color: var(--muted)">Here is Edu-Center at a glance for <span id="todayLine">today</span>.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <span class="inline-flex items-center rounded-full px-3.5 py-2 text-xs font-bold" style="background: var(--date-indigo-bg); color: var(--date-indigo-fg)">Academic year 2026/2027</span>
          <button class="btn-brand inline-flex items-center gap-2 px-4 py-2.5 text-sm" data-open-modal="admissionModal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            New admission
          </button>
        </div>
      </header>

      <!-- KPI band -->
      <section id="overview" aria-labelledby="overviewTitle" class="scroll-mt-6">
        <h2 id="overviewTitle" class="visually-hidden">Key figures</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="card p-6 sm:col-span-2 lg:row-span-1">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm font-semibold" style="color: var(--muted)">Total students</p>
                <p class="kpi-hero-num mt-2">6,825</p>
                <p class="mt-2 text-xs font-medium" style="color: var(--muted)">Enrolled students · academic year 2026/2027</p>
              </div>
              <span class="grid h-12 w-12 flex-none place-items-center rounded-2xl" style="background: var(--date-indigo-bg); color: var(--date-indigo-fg)" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.8-3.4 2.9-5 5.5-5s4.7 1.6 5.5 5"/><circle cx="17" cy="9" r="2.6"/><path d="M15.5 14.6c2.9.1 4.4 1.7 5 4.4"/></svg>
              </span>
            </div>
          </div>
          <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-sm font-semibold" style="color: var(--muted)">Teachers</p>
                <p class="mt-1.5 text-3xl font-extrabold tracking-tight">654</p>
                <p class="mt-1.5 text-xs font-medium" style="color: var(--muted)">Teaching staff</p>
              </div>
              <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl" style="background: var(--date-indigo-bg); color: var(--date-indigo-fg)" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5.5 20c.9-3.8 3.4-5.5 6.5-5.5s5.6 1.7 6.5 5.5"/></svg>
              </span>
            </div>
          </div>
          <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-sm font-semibold" style="color: var(--muted)">Events</p>
                <p class="mt-1.5 text-3xl font-extrabold tracking-tight">656</p>
                <p class="mt-1.5 text-xs font-medium" style="color: var(--muted)">On the calendar</p>
              </div>
              <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl" style="background: var(--date-indigo-bg); color: var(--date-indigo-fg)" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="16" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg>
              </span>
            </div>
          </div>
          <div class="card p-5 sm:col-span-2 lg:col-span-4">
            <div class="flex flex-wrap items-center gap-x-8 gap-y-3">
              <div class="flex items-center gap-4">
                <span class="grid h-11 w-11 flex-none place-items-center rounded-2xl" style="background: var(--date-green-bg); color: var(--date-green-fg)" aria-hidden="true">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                </span>
                <div>
                  <p class="text-sm font-semibold" style="color: var(--muted)">Invoices</p>
                  <p class="text-2xl font-extrabold tracking-tight">1,397 <span class="text-sm font-semibold" style="color: var(--muted)">recorded</span></p>
                </div>
              </div>
              <p class="ml-auto text-xs font-medium" style="color: var(--muted)">Figures shown are demo data.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Performance + Events -->
      <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="performance" aria-labelledby="perfTitle" class="card scroll-mt-6 p-5 sm:p-6 lg:col-span-2">
          <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
              <h2 id="perfTitle" class="text-lg font-extrabold tracking-tight">Student and teacher numbers by week</h2>
              <p class="mt-0.5 text-xs font-medium" style="color: var(--muted)"><span id="perfRange">Last 6 weeks</span> · demo data</p>
            </div>
            <label class="flex items-center gap-2 text-xs font-semibold" style="color: var(--muted)">
              Period
              <select id="periodSelect" class="field w-auto px-3 py-2 text-xs font-semibold">
                <option value="6">Last 6 weeks</option>
                <option value="4">Last 4 weeks</option>
                <option value="2">Last 2 weeks</option>
              </select>
            </label>
          </div>
          <div class="relative h-72 sm:h-80">
            <canvas id="performanceChart" role="img" aria-label="Line chart comparing students and teachers per week over the last 6 weeks."></canvas>
          </div>
        </section>

        <section id="events" aria-labelledby="eventsTitle" class="card flex scroll-mt-6 flex-col p-5 sm:p-6">
          <div class="mb-3 flex items-center justify-between gap-3">
            <h2 id="eventsTitle" class="text-lg font-extrabold tracking-tight">Upcoming events</h2>
            <button class="btn-ghost2 inline-flex items-center gap-1.5 px-3 py-2 text-xs" data-open-modal="eventModal">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              New event
            </button>
          </div>
          <label class="visually-hidden" for="eventSearch">Search events</label>
          <div class="relative mb-4">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="color: var(--muted)" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input id="eventSearch" type="search" placeholder="Search events" class="field py-2.5 pl-10 pr-4 text-sm" autocomplete="off">
          </div>
          <ul id="eventList" class="flex-1 space-y-3"></ul>
          <div id="eventEmpty" class="hidden flex-1 flex-col items-center justify-center py-10 text-center">
            <p class="font-bold">No events found</p>
            <p class="mt-1 text-sm" style="color: var(--muted)">No events match <span id="eventEmptyQuery" class="font-semibold"></span>.</p>
            <button id="eventClearSearch" class="btn-ghost2 mt-4 px-4 py-2 text-sm">Clear search</button>
          </div>
        </section>
      </div>

      <!-- Admissions -->
      <section id="admissions" aria-labelledby="admTitle" class="card mt-4 scroll-mt-6 p-5 sm:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 id="admTitle" class="text-lg font-extrabold tracking-tight">Recent admissions</h2>
            <p class="mt-0.5 text-xs font-medium" style="color: var(--muted)">Admissions you add will appear here.</p>
          </div>
          <button class="btn-ghost2 inline-flex items-center gap-1.5 px-3 py-2 text-xs" data-open-modal="admissionModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Add admission
          </button>
        </div>
        <div id="admissionEmpty" class="flex flex-col items-center py-10 text-center">
          <span class="grid h-14 w-14 place-items-center rounded-2xl" style="background: var(--date-indigo-bg); color: var(--date-indigo-fg)" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5.5 20c.9-3.8 3.4-5.5 6.5-5.5s5.6 1.7 6.5 5.5"/></svg>
          </span>
          <p class="mt-4 font-bold">No admissions recorded yet</p>
          <p class="mt-1 max-w-xs text-sm" style="color: var(--muted)">Use the New admission button to record the first one for this academic year.</p>
        </div>
        <ul id="admissionList" class="hidden divide-y" style="border-color: var(--card-border)"></ul>
      </section>

      <footer class="mt-10 text-center text-xs font-medium" style="color: var(--muted)">
        Edu-Center dashboard · Redesign prototype · Figures shown are demo data.
      </footer>
    </main>
  </div>
</div>

<!-- ============ MODALS ============ -->
<div id="admissionModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="admissionTitle">
  <div class="absolute inset-0" style="background: var(--overlay)" data-close-modal></div>
  <div class="modal-panel card relative w-full max-w-md p-6 shadow-xl" style="box-shadow: 0 24px 60px rgba(15,23,42,.18)">
    <div class="mb-5 flex items-start justify-between gap-4">
      <div>
        <h2 id="admissionTitle" class="text-lg font-extrabold tracking-tight">New admission</h2>
        <p class="mt-0.5 text-sm" style="color: var(--muted)">Record a student for academic year 2026/2027.</p>
      </div>
      <button class="rounded-lg p-2" style="color: var(--muted)" data-close-modal aria-label="Close dialog">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <form id="admissionForm" novalidate>
      <div class="space-y-4">
        <div>
          <label for="admName" class="mb-1.5 block text-sm font-semibold">Full name <span aria-hidden="true" style="color: var(--brand)">*</span></label>
          <input id="admName" name="name" type="text" class="field px-3.5 py-2.5 text-sm" placeholder="e.g. Raisa Putri" autocomplete="off">
          <p class="mt-1.5 hidden items-center gap-1.5 text-xs font-semibold text-red-600" data-error-for="admName">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
            <span>Please enter the student's name.</span>
          </p>
        </div>
        <div>
          <label for="admGrade" class="mb-1.5 block text-sm font-semibold">Grade <span aria-hidden="true" style="color: var(--brand)">*</span></label>
          <select id="admGrade" name="grade" class="field px-3.5 py-2.5 text-sm">
            <option value="">Select a grade</option>
            <option>Grade 1</option><option>Grade 2</option><option>Grade 3</option>
            <option>Grade 4</option><option>Grade 5</option><option>Grade 6</option>
            <option>Grade 7</option><option>Grade 8</option><option>Grade 9</option>
            <option>Grade 10</option><option>Grade 11</option><option>Grade 12</option>
          </select>
          <p class="mt-1.5 hidden items-center gap-1.5 text-xs font-semibold text-red-600" data-error-for="admGrade">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
            <span>Please select a grade.</span>
          </p>
        </div>
        <div>
          <label for="admNotes" class="mb-1.5 block text-sm font-semibold">Notes <span class="font-normal" style="color: var(--muted)">(optional)</span></label>
          <textarea id="admNotes" name="notes" rows="2" class="field px-3.5 py-2.5 text-sm" placeholder="Anything the registrar should know"></textarea>
        </div>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" class="btn-ghost2 px-4 py-2.5 text-sm" data-close-modal>Cancel</button>
        <button type="submit" class="btn-brand px-5 py-2.5 text-sm" data-submit-label="Save admission">Save admission</button>
      </div>
    </form>
  </div>
</div>

<div id="eventModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="eventTitle">
  <div class="absolute inset-0" style="background: var(--overlay)" data-close-modal></div>
  <div class="modal-panel card relative w-full max-w-md p-6 shadow-xl" style="box-shadow: 0 24px 60px rgba(15,23,42,.18)">
    <div class="mb-5 flex items-start justify-between gap-4">
      <div>
        <h2 id="eventTitle" class="text-lg font-extrabold tracking-tight">New event</h2>
        <p class="mt-0.5 text-sm" style="color: var(--muted)">Add it to the Upcoming events list.</p>
      </div>
      <button class="rounded-lg p-2" style="color: var(--muted)" data-close-modal aria-label="Close dialog">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <form id="eventForm" novalidate>
      <div class="space-y-4">
        <div>
          <label for="evTitle" class="mb-1.5 block text-sm font-semibold">Event title <span aria-hidden="true" style="color: var(--brand)">*</span></label>
          <input id="evTitle" name="title" type="text" class="field px-3.5 py-2.5 text-sm" placeholder="e.g. Science fair" autocomplete="off">
          <p class="mt-1.5 hidden items-center gap-1.5 text-xs font-semibold text-red-600" data-error-for="evTitle">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
            <span>Please enter an event title.</span>
          </p>
        </div>
        <div>
          <label for="evDetail" class="mb-1.5 block text-sm font-semibold">Details <span class="font-normal" style="color: var(--muted)">(optional)</span></label>
          <input id="evDetail" name="detail" type="text" class="field px-3.5 py-2.5 text-sm" placeholder="e.g. Inter-school competition" autocomplete="off">
        </div>
        <div>
          <label for="evDate" class="mb-1.5 block text-sm font-semibold">Date <span aria-hidden="true" style="color: var(--brand)">*</span></label>
          <input id="evDate" name="date" type="date" class="field px-3.5 py-2.5 text-sm">
          <p class="mt-1.5 hidden items-center gap-1.5 text-xs font-semibold text-red-600" data-error-for="evDate">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
            <span>Please pick a date.</span>
          </p>
        </div>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" class="btn-ghost2 px-4 py-2.5 text-sm" data-close-modal>Cancel</button>
        <button type="submit" class="btn-brand px-5 py-2.5 text-sm" data-submit-label="Save event">Save event</button>
      </div>
    </form>
  </div>
</div>

<div id="toastRegion" class="fixed bottom-5 right-5 z-[60] flex flex-col gap-2" aria-live="polite"></div>

<script>
(function () {
  "use strict";
  var root = document.documentElement;

  /* ---------- Theme: light/dark toggle, persisted ---------- */
  var themeToggle = document.getElementById("themeToggle");
  var iconMoon = document.getElementById("iconMoon");
  var iconSun = document.getElementById("iconSun");
  function cssVar(name) { return getComputedStyle(root).getPropertyValue(name).trim(); }
  function applyTheme(theme) {
    root.setAttribute("data-theme", theme);
    try { localStorage.setItem("edu-theme", theme); } catch (e) {}
    var dark = theme === "dark";
    iconMoon.classList.toggle("hidden", dark);
    iconSun.classList.toggle("hidden", !dark);
    themeToggle.setAttribute("aria-label", dark ? "Switch to light mode" : "Switch to dark mode");
    if (window.__chart) styleChart(window.__chart, dark);
  }
  var savedTheme = null;
  try { savedTheme = localStorage.getItem("edu-theme"); } catch (e) {}
  applyTheme(savedTheme === "dark" ? "dark" : "light");
  themeToggle.addEventListener("click", function () {
    applyTheme(root.getAttribute("data-theme") === "dark" ? "light" : "dark");
  });

  /* ---------- Greeting + real date ---------- */
  var now = new Date();
  var hour = now.getHours();
  var part = hour < 11 ? "Good morning" : hour < 18 ? "Good afternoon" : "Good evening";
  document.getElementById("greeting").textContent = part + ", <?= htmlspecialchars(strtok($namaUser, ' ')) ?>.";
  document.getElementById("todayLine").textContent =
    now.toLocaleDateString("en-GB", { weekday: "long", day: "numeric", month: "long", year: "numeric" });

  /* ---------- Mobile sidebar ---------- */
  var sidebar = document.getElementById("sidebar");
  var overlay = document.getElementById("sidebarOverlay");
  var sidebarOpenBtn = document.getElementById("sidebarOpen");
  function setSidebar(open) {
    sidebar.classList.toggle("-translate-x-full", !open);
    overlay.classList.toggle("hidden", !open);
    sidebarOpenBtn.setAttribute("aria-expanded", String(open));
    document.body.style.overflow = open && window.innerWidth < 1024 ? "hidden" : "";
  }
  sidebarOpenBtn.addEventListener("click", function () { setSidebar(true); });
  document.getElementById("sidebarClose").addEventListener("click", function () { setSidebar(false); });
  overlay.addEventListener("click", function () { setSidebar(false); });

  /* ---------- "Soon" nav items: honest toast instead of a dead link ---------- */
  document.querySelectorAll("[data-soon]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      toast(btn.getAttribute("data-soon") + " is coming soon.");
      if (window.innerWidth < 1024) setSidebar(false);
    });
  });

  /* ---------- Notifications dropdown (honest empty state) ---------- */
  var bellBtn = document.getElementById("bellBtn");
  var bellPanel = document.getElementById("bellPanel");
  function setBell(open) {
    bellPanel.classList.toggle("hidden", !open);
    bellBtn.setAttribute("aria-expanded", String(open));
  }
  bellBtn.addEventListener("click", function (e) { e.stopPropagation(); setBell(bellPanel.classList.contains("hidden")); });
  document.addEventListener("click", function (e) { if (!bellPanel.contains(e.target)) setBell(false); });

  /* ---------- Toast ---------- */
  function toast(message) {
    var region = document.getElementById("toastRegion");
    var el = document.createElement("div");
    el.className = "toast-in card flex items-center gap-2.5 px-4 py-3 text-sm font-semibold shadow-lg";
    el.setAttribute("role", "status");
    el.innerHTML =
      '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="color: var(--trend)" aria-hidden="true"><path d="M4.5 12.5l5 5 10-11"/></svg>' +
      "<span></span>";
    el.querySelector("span").textContent = message;
    region.appendChild(el);
    setTimeout(function () { el.remove(); }, 3200);
  }

  /* ---------- Modals ---------- */
  var lastTrigger = null;
  function openModal(id, trigger) {
    lastTrigger = trigger || null;
    var modal = document.getElementById(id);
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.style.overflow = "hidden";
    var first = modal.querySelector("input, select, textarea");
    if (first) first.focus();
    modal._trap = function (e) {
      if (e.key !== "Tab") return;
      var items = Array.prototype.slice.call(modal.querySelectorAll("button, input, select, textarea"))
        .filter(function (el) { return !el.disabled && el.offsetParent !== null; });
      if (!items.length) return;
      var firstEl = items[0], lastEl = items[items.length - 1];
      if (e.shiftKey && document.activeElement === firstEl) { e.preventDefault(); lastEl.focus(); }
      else if (!e.shiftKey && document.activeElement === lastEl) { e.preventDefault(); firstEl.focus(); }
    };
    modal.addEventListener("keydown", modal._trap);
  }
  function closeModal(modal) {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    document.body.style.overflow = "";
    if (modal._trap) modal.removeEventListener("keydown", modal._trap);
    if (lastTrigger) lastTrigger.focus();
  }
  document.querySelectorAll("[data-open-modal]").forEach(function (btn) {
    btn.addEventListener("click", function () { openModal(btn.getAttribute("data-open-modal"), btn); });
  });
  document.querySelectorAll("[data-close-modal]").forEach(function (el) {
    el.addEventListener("click", function () {
      var modal = el.closest('[role="dialog"]');
      if (modal) closeModal(modal);
    });
  });
  document.addEventListener("keydown", function (e) {
    if (e.key !== "Escape") return;
    var open = document.querySelector('[role="dialog"]:not(.hidden)');
    if (open) { closeModal(open); return; }
    setBell(false);
    setSidebar(false);
  });

  /* ---------- Form validation helpers (error state: icon + text, not color-only) ---------- */
  function setError(input, on) {
    input.setAttribute("aria-invalid", String(on));
    var msg = document.querySelector('[data-error-for="' + input.id + '"]');
    if (msg) { msg.classList.toggle("hidden", !on); msg.classList.toggle("flex", on); }
  }
  function validateField(input) {
    var ok = input.value.trim() !== "";
    setError(input, !ok);
    return ok;
  }
  ["admName", "admGrade", "evTitle", "evDate"].forEach(function (id) {
    var el = document.getElementById(id);
    el.addEventListener("input", function () { if (el.value.trim() !== "") setError(el, false); });
    el.addEventListener("change", function () { if (el.value.trim() !== "") setError(el, false); });
  });
  function submitBusy(form, busy) {
    var btn = form.querySelector('[type="submit"]');
    btn.disabled = busy;
    btn.textContent = busy ? "Saving..." : btn.getAttribute("data-submit-label");
  }

  /* ---------- Admissions: real list with an honest empty state ---------- */
  var admissionList = document.getElementById("admissionList");
  var admissionEmpty = document.getElementById("admissionEmpty");
  function initials(name) {
    return name.trim().split(/\s+/).slice(0, 2).map(function (w) { return w[0].toUpperCase(); }).join("");
  }
  document.getElementById("admissionForm").addEventListener("submit", function (e) {
    e.preventDefault();
    var nameEl = document.getElementById("admName");
    var gradeEl = document.getElementById("admGrade");
    var okName = validateField(nameEl);
    var okGrade = validateField(gradeEl);
    if (!okName || !okGrade) { (!okName ? nameEl : gradeEl).focus(); return; }
    var form = e.target;
    submitBusy(form, true);
    setTimeout(function () {
      var li = document.createElement("li");
      li.className = "flex items-center gap-4 py-4";
      var av = document.createElement("span");
      av.className = "grid h-10 w-10 flex-none place-items-center rounded-full text-xs font-extrabold text-white";
      av.style.background = "var(--brand)";
      av.setAttribute("aria-hidden", "true");
      av.textContent = initials(nameEl.value);
      var mid = document.createElement("div");
      mid.className = "min-w-0 flex-1";
      var nm = document.createElement("p");
      nm.className = "truncate text-sm font-bold";
      nm.textContent = nameEl.value.trim();
      var sub = document.createElement("p");
      sub.className = "text-xs";
      sub.style.color = "var(--muted)";
      var notes = document.getElementById("admNotes").value.trim();
      sub.textContent = gradeEl.value + (notes ? " · " + notes : "");
      mid.appendChild(nm); mid.appendChild(sub);
      var when = document.createElement("span");
      when.className = "flex-none text-xs font-semibold";
      when.style.color = "var(--muted)";
      when.textContent = now.toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" });
      li.appendChild(av); li.appendChild(mid); li.appendChild(when);
      admissionList.prepend(li);
      admissionList.classList.remove("hidden");
      admissionEmpty.classList.add("hidden");
      submitBusy(form, false);
      form.reset();
      closeModal(document.getElementById("admissionModal"));
      toast("Admission added.");
    }, 450);
  });

  /* ---------- Events: seeded demo events + user-added real ones ---------- */
  var eventList = document.getElementById("eventList");
  var eventEmpty = document.getElementById("eventEmpty");
  var eventSearch = document.getElementById("eventSearch");
  var seedEvents = [
    { title: "School Live Concert", detail: "Charity Event 2026", dow: "Wed", day: "3", year: "2026", demo: true, accent: "indigo" },
    { title: "The Story of Danau Toba", detail: "Musical drama", dow: "Fri", day: "28", year: "2026", demo: true, accent: "green" }
  ];
  function dateBlock(ev) {
    var wrap = document.createElement("div");
    wrap.className = "grid h-16 w-16 flex-none place-items-center rounded-2xl text-center";
    wrap.style.background = ev.accent === "green" ? "var(--date-green-bg)" : "var(--date-indigo-bg)";
    wrap.style.color = ev.accent === "green" ? "var(--date-green-fg)" : "var(--date-indigo-fg)";
    wrap.setAttribute("aria-hidden", "true");
    var dow = document.createElement("span");
    dow.className = "text-[11px] font-bold uppercase tracking-wide";
    dow.textContent = ev.dow;
    var day = document.createElement("span");
    day.className = "text-xl font-extrabold leading-none";
    day.textContent = ev.day;
    wrap.appendChild(dow); wrap.appendChild(day);
    return wrap;
  }
  function renderEvents(filter) {
    eventList.innerHTML = "";
    var q = (filter || "").trim().toLowerCase();
    var shown = 0;
    seedEvents.forEach(function (ev) {
      if (q && (ev.title + " " + ev.detail).toLowerCase().indexOf(q) === -1) return;
      shown++;
      var li = document.createElement("li");
      li.className = "flex items-center gap-4";
      li.appendChild(dateBlock(ev));
      var mid = document.createElement("div");
      mid.className = "min-w-0";
      var t = document.createElement("h3");
      t.className = "truncate text-sm font-bold";
      t.textContent = ev.title;
      var d = document.createElement("p");
      d.className = "truncate text-xs";
      d.style.color = "var(--muted)";
      d.textContent = ev.detail + " · " + ev.year;
      mid.appendChild(t); mid.appendChild(d);
      li.appendChild(mid);
      if (ev.demo) {
        var tag = document.createElement("span");
        tag.className = "ml-auto flex-none rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide";
        tag.style.background = "var(--date-indigo-bg)";
        tag.style.color = "var(--date-indigo-fg)";
        tag.textContent = "Demo";
        li.appendChild(tag);
      }
      eventList.appendChild(li);
    });
    var hasResults = shown > 0;
    eventList.classList.toggle("hidden", !hasResults);
    eventEmpty.classList.toggle("hidden", hasResults);
    eventEmpty.classList.toggle("flex", !hasResults);
    if (!hasResults) document.getElementById("eventEmptyQuery").textContent = "\u201C" + filter.trim() + "\u201D";
  }
  renderEvents("");
  eventSearch.addEventListener("input", function () { renderEvents(eventSearch.value); });
  document.getElementById("eventClearSearch").addEventListener("click", function () {
    eventSearch.value = "";
    renderEvents("");
    eventSearch.focus();
  });
  document.getElementById("eventForm").addEventListener("submit", function (e) {
    e.preventDefault();
    var titleEl = document.getElementById("evTitle");
    var dateEl = document.getElementById("evDate");
    var okTitle = validateField(titleEl);
    var okDate = validateField(dateEl);
    if (!okTitle || !okDate) { (!okTitle ? titleEl : dateEl).focus(); return; }
    var form = e.target;
    submitBusy(form, true);
    setTimeout(function () {
      var dt = new Date(dateEl.value + "T00:00:00");
      seedEvents.unshift({
        title: titleEl.value.trim(),
        detail: document.getElementById("evDetail").value.trim() || "School event",
        dow: dt.toLocaleDateString("en-GB", { weekday: "short" }),
        day: String(dt.getDate()),
        year: String(dt.getFullYear()),
        demo: false,
        accent: seedEvents.length % 2 ? "green" : "indigo"
      });
      renderEvents(eventSearch.value);
      submitBusy(form, false);
      form.reset();
      closeModal(document.getElementById("eventModal"));
      toast("Event added.");
    }, 450);
  });

  /* ---------- Chart ---------- */
  var weeks = ["Week 01", "Week 02", "Week 03", "Week 04", "Week 05", "Week 06"];
  var students = [40, 30, 50, 40, 60, 45];
  var teachers = [20, 25, 20, 35, 20, 30];
  var canvas = document.getElementById("performanceChart");
  function styleChart(chart, dark) {
    var grid = cssVar("--chart-grid"), tick = cssVar("--chart-tick");
    chart.options.scales.y.grid.color = grid;
    chart.options.scales.y.ticks.color = tick;
    chart.options.scales.x.ticks.color = tick;
    chart.options.plugins.legend.labels.color = tick;
    chart.update();
  }
  function describeRange(s, t) {
    var lo = Math.min.apply(null, s.concat(t)), hi = Math.max.apply(null, s.concat(t));
    return "Line chart comparing students and teachers per week. Values range from " + lo + " to " + hi + ".";
  }
  var chart = new Chart(canvas, {
    type: "line",
    data: {
      labels: weeks.slice(),
      datasets: [
        {
          label: "Students",
          data: students.slice(),
          borderColor: "#5D5FEF",
          backgroundColor: "rgba(93, 95, 239, 0.08)",
          fill: true,
          tension: 0.4,
          borderWidth: 2.5,
          pointRadius: 0,
          pointHoverRadius: 5,
          pointBackgroundColor: "#5D5FEF"
        },
        {
          label: "Teachers",
          data: teachers.slice(),
          borderColor: "#15803d",
          tension: 0.4,
          borderWidth: 2.5,
          pointRadius: 0,
          pointHoverRadius: 5,
          pointBackgroundColor: "#15803d"
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: "index", intersect: false },
      plugins: {
        legend: {
          display: true,
          position: "top",
          align: "end",
          labels: { usePointStyle: true, pointStyle: "line", boxHeight: 6, font: { size: 12, weight: 600 } }
        },
        tooltip: {
          backgroundColor: "#0f172a",
          padding: 12,
          titleFont: { weight: 700 },
          callbacks: { label: function (ctx) { return " " + ctx.dataset.label + ": " + ctx.parsed.y; } }
        }
      },
      scales: {
        y: { beginAtZero: true, grid: {}, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });
  window.__chart = chart;
  styleChart(chart, root.getAttribute("data-theme") === "dark");
  canvas.setAttribute("aria-label", describeRange(students, teachers) + " Last 6 weeks, demo data.");
  document.getElementById("periodSelect").addEventListener("change", function (e) {
    var n = parseInt(e.target.value, 10);
    chart.data.labels = weeks.slice(-n);
    chart.data.datasets[0].data = students.slice(-n);
    chart.data.datasets[1].data = teachers.slice(-n);
    chart.update();
    document.getElementById("perfRange").textContent = "Last " + n + " weeks";
    canvas.setAttribute("aria-label",
      describeRange(students.slice(-n), teachers.slice(-n)) + " Last " + n + " weeks, demo data.");
  });

  /* ---------- Scrollspy: keep the active nav item honest ----------
     Performance and Events sit side by side in one grid row, so a thin
     IntersectionObserver band hits both at once and the winner is arbitrary.
     Instead, on scroll pick the linked section whose top is closest to (but
     not below) an anchor line; nav order breaks ties left to right. */
  var spyLinks = document.querySelectorAll('#navMain a[href^="#"]');
  var spyTicking = false;
  function updateSpy() {
    spyTicking = false;
    var anchor = window.innerHeight * 0.35;
    var best = null, bestTop = -Infinity;
    spyLinks.forEach(function (a) {
      var s = document.getElementById(a.getAttribute("href").slice(1));
      if (!s) return;
      var top = s.getBoundingClientRect().top;
      if (top <= anchor && top > bestTop) { bestTop = top; best = a; }
    });
    if (!best && spyLinks.length) best = spyLinks[0];
    spyLinks.forEach(function (a) { a.removeAttribute("aria-current"); });
    if (best) best.setAttribute("aria-current", "page");
  }
  window.addEventListener("scroll", function () {
    if (!spyTicking) { spyTicking = true; requestAnimationFrame(updateSpy); }
  }, { passive: true });
  updateSpy();
})();
</script>
</body>
</html>
