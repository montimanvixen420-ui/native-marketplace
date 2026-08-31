<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TINDA Marketplace</title>

    <!-- Set the theme before first paint, to avoid a flash of the wrong theme -->
    <script>
      (function () {
        var saved = localStorage.getItem('theme');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        var isDark = saved ? saved === 'dark' : prefersDark;
        document.documentElement.classList.toggle('dark', isDark);
      })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        ink: '#111827',
                        'ink-2': '#1F2937',
                        amber: '#F0A93E',
                        teal: '#2563EB',
                        'teal-light': '#EFF6FF',
                        // ── New modern palette (used by redesigned pages) ──
                        brand: '#2563EB',
                        'brand-dark': '#1D4ED8',
                        'brand-light': '#EFF6FF',
                        surface: '#F8FAFC',
                        coral: '#BFDBFE',
                    },
                    fontFamily: {
                        display: ['Manrope', 'sans-serif'],
                        sans: ['DM Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
      /* A denser workspace keeps the primary actions visible on laptop screens. */
      html { font-size:16px; }
      .dashboard-stat { display:flex; min-height:108px; flex-direction:column; justify-content:space-between; border:1px solid #e6eaf0; border-radius:.875rem; background:#fff; padding:1rem; box-shadow:0 1px 2px rgba(15,23,42,.03); transition:.2s ease; }
      .dashboard-stat:hover { transform:translateY(-2px); border-color:#93c5fd; box-shadow:0 10px 20px rgba(15,23,42,.08); }
      .dashboard-stat b { font-family:'Manrope',sans-serif; font-size:1.5rem; color:#0f172a; line-height:1.1; }
      .dashboard-stat small { font-size:.75rem; color:#6b7280; }
      .stat-label,.eyebrow { font-size:.68rem; font-weight:700; letter-spacing:.13em; text-transform:uppercase; color:#6b7280; }
      .dashboard-panel { border:1px solid #e6eaf0; border-radius:.875rem; background:#fff; padding:1.25rem; box-shadow:0 1px 2px rgba(15,23,42,.03); }
      .dashboard-panel h2 { margin-top:.35rem; font-family:'Manrope',sans-serif; font-size:1.2rem; font-weight:700; color:#0f172a; }
      .action-card { border:1px solid #e6eaf0; border-radius:.75rem; padding:1rem; transition:.2s ease; }
      .action-card:hover { border-color:#93c5fd; background:#eff6ff; }
      .action-card span { display:block; margin-bottom:1.5rem; font-size:.7rem; font-weight:700; color:#2563eb; }
      .action-card b { display:block; color:#0f172a; font-size:.875rem; }
      .action-card p { margin-top:.35rem; color:#6b7280; font-size:.75rem; line-height:1.35rem; }
      .dark .dashboard-stat,.dark .dashboard-panel { border-color:rgba(255,255,255,.1); background:#111827; }
      .dark .dashboard-stat b,.dark .dashboard-panel h2,.dark .action-card b { color:#fff; }
      .dark .action-card { border-color:rgba(255,255,255,.1); }.dark .action-card:hover { background:rgba(31,94,77,.2); }
      .field-label { display:block; margin-bottom:.5rem; font-size:.875rem; font-weight:600; color:#263449; }
      .field-input { width:100%; border:1px solid #d1d5db; border-radius:.75rem; background:#fff; padding:.875rem 1rem; font-size:.875rem; color:#263449; outline:none; transition:.2s; }
      .field-input:focus { border-color:#526783; box-shadow:0 0 0 4px rgba(82,103,131,.14); }
      .dark .field-label { color:#fff; }.dark .field-input { border-color:rgba(255,255,255,.15); background:#1B2535; color:#fff; }.dark .field-input::placeholder { color:rgba(255,255,255,.35); }
      .dashboard-topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin:-.2rem 0 1.5rem; padding-bottom:1.15rem; border-bottom:1px solid #e6eaf0; }
      .dashboard-search { display:flex; align-items:center; gap:.6rem; min-width:220px; border:1px solid #e2e8f0; border-radius:.7rem; background:#fff; padding:.65rem .85rem; color:#94a3b8; font-size:.8rem; }
      .dashboard-search kbd { margin-left:auto; border:1px solid #e2e8f0; border-radius:.3rem; padding:.1rem .3rem; color:#94a3b8; font-size:.62rem; }
      .topbar-avatar { display:flex; height:2.25rem; width:2.25rem; align-items:center; justify-content:center; border-radius:999px; background:#dcfce7; color:#15803d; font-size:.75rem; font-weight:800; }
      .stock-meter { height:.45rem; overflow:hidden; border-radius:999px; background:#e5e7eb; }.stock-meter > span { display:block; height:100%; border-radius:inherit; background:#2563EB; }
      .dark .dashboard-topbar { border-color:rgba(255,255,255,.1); }.dark .dashboard-search { border-color:rgba(255,255,255,.1); background:#111827; color:rgba(255,255,255,.5); }
      /* Navigation shell — calm, high-contrast, and consistent across all roles. */
      .app-sidebar { width:16.5rem; overflow-x:hidden; background:#111827; border:0; box-shadow:12px 0 35px rgba(15,23,42,.06); }
      .app-sidebar > div:first-child { overflow-x:hidden; }
      .app-sidebar .app-brand { padding:1rem 1.25rem; letter-spacing:-.04em; color:#fff; }
      .app-sidebar .app-brand-mark { display:inline-flex; width:1.75rem; height:1.75rem; align-items:center; justify-content:center; border-radius:.6rem; background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff; font-size:.8rem; box-shadow:0 8px 20px rgba(37,99,235,.32); }
      .app-sidebar .app-role { border:1px solid rgba(96,165,250,.2); background:rgba(37,99,235,.15); color:#93c5fd; }
      .app-sidebar .app-section-label { color:#94a3b8; }
      .app-sidebar .px-6 { padding-left:1.25rem; padding-right:1.25rem; }
      .app-sidebar .pt-4 { padding-top:.8rem; }.app-sidebar .pb-1\.5 { padding-bottom:.2rem; }
      .app-sidebar .px-4 { padding-left:.85rem; padding-right:.85rem; }
      .app-sidebar .app-side-link { min-height:2.25rem; padding:.48rem .75rem; font-size:.875rem; color:#cbd5e1 !important; }
      .app-sidebar .app-side-link svg { width:1.05rem; height:1.05rem; }
      .app-sidebar .app-side-link:hover { color:#fff !important; }
      .app-sidebar .app-side-active { color:#fff !important; }
      .app-sidebar .app-account { padding:.6rem .75rem; }.app-sidebar .app-account > div:first-child { width:2rem; height:2rem; }
      .app-sidebar .app-footer-rule { padding-top:.55rem; padding-bottom:.55rem; }
      .app-side-link:hover { background:rgba(255,255,255,.055); color:#fff; }
      .app-side-active { background:rgba(37,99,235,.18); border-color:rgba(96,165,250,.13); color:#fff; box-shadow:inset 3px 0 #60a5fa; }
      .app-sidebar .app-account { border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.045); }
      .app-sidebar .app-account:hover { background:rgba(255,255,255,.075); }
      .app-sidebar .app-account-name { color:#f9fafb; }
      .app-sidebar .app-footer-rule { border-color:rgba(255,255,255,.08); }
      /* Keep every role's navigation as one continuous list without section gaps. */
      .app-sidebar .app-brand { padding:.8rem 1rem; font-size:1rem; }
      .app-sidebar .app-brand-mark { width:1.55rem; height:1.55rem; font-size:.7rem; }
      .app-sidebar .px-6 { padding-left:1rem; padding-right:1rem; }
      .app-sidebar .pb-4 { padding-bottom:.55rem; }
      .app-sidebar .pt-4 { padding-top:0; }.app-sidebar .pb-1\.5 { padding-bottom:0; }
      .app-sidebar nav.px-4 { padding-left:.6rem; padding-right:.6rem; }
      .app-sidebar nav.space-y-1 > :not([hidden]) ~ :not([hidden]) { margin-top:0; }
      .app-sidebar .app-section-label { display:none; }
      .app-sidebar .app-side-link { min-height:1.9rem; padding:.3rem .65rem; font-size:.78rem; }
      .app-sidebar .app-side-link svg { width:.92rem; height:.92rem; }
      .app-sidebar .app-section-label { font-size:.58rem; letter-spacing:.14em; }
      .app-sidebar .app-footer-rule { padding-top:.38rem; padding-bottom:.38rem; }
      .app-sidebar .app-account { padding:.45rem .6rem; border-radius:.7rem; }
      .app-sidebar .app-account > div:first-child { width:1.8rem; height:1.8rem; font-size:.65rem; }
      .app-sidebar .app-account-name { font-size:.78rem; }
      @media (max-width: 767px) {
        html { font-size:14px; }
        .min-h-screen.flex { flex-direction: column; }
        .min-h-screen.flex > aside { display: none; }
        .min-h-screen.flex > main { min-width: 0; padding: 1rem !important; }
        .min-h-screen.flex > main .grid.grid-cols-3 { grid-template-columns: minmax(0, 1fr); }
        .min-h-screen.flex > main .col-span-2 { grid-column: span 1 / span 1; }
        main table { min-width:38rem; }
        main div:has(> table) { overflow-x:auto !important; -webkit-overflow-scrolling:touch; }
        .dashboard-topbar { align-items:flex-start; margin-bottom:1rem; padding-bottom:1rem; }
        .dashboard-stat { min-height:92px; padding:.9rem; }.dashboard-panel { padding:1rem; }
        .pos-workspace { display:flex; flex-direction:column; overflow:visible !important; }
        .pos-catalog { overflow:visible !important; padding:1rem !important; }
        .pos-catalog > div:first-child { align-items:flex-start; }.pos-catalog .flex.items-center.justify-between { gap:.75rem; flex-wrap:wrap; }
        .pos-catalog .w-48 { width:100%; }.pos-catalog #pos-sort-select { width:100%; margin-top:.5rem; }
        .pos-cart { width:100% !important; min-height:34rem; border-left:0 !important; border-top:1px solid rgba(148,163,184,.25); }
        #pos-product-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
      }

      /* Light + dark surfaces for pages that still use light-only Tailwind classes. */
      html { color-scheme: light; }
      html.dark { color-scheme: dark; }
      html.dark body { color: #e5e7eb; }
      html.dark .text-gray-900,
      html.dark .text-ink { color: #f9fafb; }
      html.dark .text-gray-800 { color: #e5e7eb; }
      html.dark .text-gray-700 { color: #d1d5db; }
      html.dark .bg-gray-50,
      html.dark .bg-surface { background-color: #111827; }
      html.dark main .border.bg-white,
      html.dark main .overflow-hidden.bg-white,
      html.dark main .shadow-sm.bg-white,
      html.dark main .rounded-lg.bg-white,
      html.dark main .rounded-xl.bg-white,
      html.dark main .rounded-2xl.bg-white { background-color: #1f2937; }
      html.dark .border-gray-100,
      html.dark .border-gray-200,
      html.dark .border-gray-300 { border-color: rgba(255,255,255,.1); }
      html.dark .bg-red-50 { background-color: rgba(127,29,29,.4); }
      html.dark .border-red-200 { border-color: rgba(248,113,113,.35); }
      html.dark .text-red-700 { color: #fecaca; }
      html.dark .bg-green-50 { background-color: rgba(20,83,45,.4); }
      html.dark .border-green-200 { border-color: rgba(74,222,128,.35); }
      html.dark .text-green-700 { color: #bbf7d0; }
      html.dark .bg-blue-50 { background-color: rgba(37,99,235,.22); }
      html.dark .text-blue-700 { color: #93c5fd; }
      html.dark .bg-brand-light { background-color: rgba(37,99,235,.16); }
      html.dark .text-brand-dark { color: #93c5fd; }
      html.dark .bg-amber-50 { background-color: rgba(120,53,15,.4); }
      html.dark .border-amber-100,
      html.dark .border-amber-200 { border-color: rgba(251,191,36,.3); }
      html.dark .text-amber-700,
      html.dark .text-amber-800 { color: #fcd34d; }
      html.dark .bg-amber\/10 { background-color: rgba(240,169,62,.14); }
      html.dark main input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]):not([type="submit"]):not([type="button"]),
      html.dark main select,
      html.dark main textarea {
        background-color: #111827;
        color: #f9fafb;
        border-color: rgba(255,255,255,.15);
      }
      html.dark main input::placeholder,
      html.dark main textarea::placeholder { color: rgba(255,255,255,.35); }
      html.dark table,
      html.dark table td { color: #e5e7eb; border-color: rgba(255,255,255,.08); }
      html.dark table thead th,
      html.dark table.dataTable thead > tr > th,
      html.dark table.dataTable thead > tr > td {
        background-color: #111827 !important;
        color: #9ca3af !important;
        border-color: rgba(255,255,255,.08);
      }
      html.dark .hover\:bg-gray-50:hover { background-color: rgba(255,255,255,.06); }
      html.dark .icon-btn { border-color: rgba(255,255,255,.15); color: #d1d5db; }
      html.dark .icon-btn:hover { background: rgba(255,255,255,.06); }
      html.dark .swal2-popup { background: #1f2937 !important; color: #f9fafb !important; }
      html.dark .swal2-select,
      html.dark .swal2-input,
      html.dark .swal2-textarea { background:#111827 !important; color:#f9fafb !important; border-color:rgba(255,255,255,.2) !important; color-scheme: dark; }
      html.dark .swal2-title,
      html.dark .swal2-html-container { color: #f9fafb !important; }
      html.dark .swal2-timer-progress-bar { background: rgba(255,255,255,.35); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-ink min-h-screen font-sans transition-colors">