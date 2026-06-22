<?php

declare(strict_types=1);

/** @var string $title */
/** @var string $content */
/** @var array|null $user */
$user = current_user();
$dark = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="es" class="<?= $dark === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Sistema de Practicantes — Municipalidad Provincial de Pisco') ?></title>
    <link rel="icon" type="image/png" href="<?= e(app_url('assets/img/3ricos.png')) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              pisco: {
                dark:    '#353535',
                teal:    '#284b63',
                accent:  '#3c6e71',
                light:   '#d9d9d9',
                // Keep compatibility names mapped to the new palette for safety/harmonization
                sky:     '#284b63',
                skylt:   '#d9d9d9',
                gold:    '#3c6e71',
                goldlt:  '#d9d9d9',
                green:   '#284b63',
                greenlt: '#d9d9d9',
                earth:   '#353535',
                sun:     '#3c6e71',
              }
            },
            fontFamily: {
              display: ['"Playfair Display"', 'Georgia', 'serif'],
              body:    ['"Source Sans 3"', 'sans-serif'],
            },
            boxShadow: {
              'gold': '0 2px 16px 0 rgba(119,172,162,0.18)',
              'sky':  '0 2px 16px 0 rgba(70,129,137,0.15)',
            },
            animation: {
              'fade-in': 'fadeIn 0.5s ease-out forwards',
              'slide-up': 'slideUp 0.5s ease-out forwards',
              'float': 'float 3s ease-in-out infinite',
            },
            keyframes: {
              fadeIn: {
                '0%': { opacity: '0' },
                '100%': { opacity: '1' },
              },
              slideUp: {
                '0%': { transform: 'translateY(10px)', opacity: '0' },
                '100%': { transform: 'translateY(0)', opacity: '1' },
              },
              float: {
                '0%, 100%': { transform: 'translateY(0)' },
                '50%': { transform: 'translateY(-5px)' },
              }
            }
          }
        }
      }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style type="text/tailwindcss">
      /* ─── Base ─────────────────────────────────────────── */
      @layer base {
        body { font-family: 'Source Sans 3', sans-serif; }
        h1,h2,h3 { font-family: 'Playfair Display', Georgia, serif; }
      }

      @layer utilities {
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        .delay-400 { animation-delay: 400ms; }
        .delay-500 { animation-delay: 500ms; }
      }

      @layer components {
        .ui-animate-entry { @apply opacity-0 animate-slide-up; }
        .ui-hover-lift { @apply transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg; }

        /* ─── Shell ─────────────────────────────────────── */
        .ui-body   { @apply h-screen bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased overflow-hidden; }
        .ui-shell  { @apply flex h-screen overflow-hidden; }

        /* ─── Sidebar ───────────────────────────────────── */
        .ui-aside  {
          background: linear-gradient(170deg, #353535 0%, #284b63 45%, #353535 100%);
          border-right: 3px solid #3c6e71;
        }
        .ui-aside-brand {
          @apply p-5 flex items-center gap-3;
          border-bottom: 1px solid rgba(119, 172, 162, 0.35);
          background: rgba(0,0,0,0.18);
        }
        .ui-logo-sm {
          @apply h-12 w-12 rounded-full flex items-center justify-center shrink-0 overflow-hidden;
          border: 2px solid #3c6e71;
          background: #fff;
          box-shadow: 0 0 10px rgba(119, 172, 162, 0.4);
        }
        .ui-logo-sm img { @apply h-full w-full object-contain; }
        .ui-brand-title  { @apply font-display font-bold text-white text-sm leading-tight; }
        .ui-brand-muted  { @apply text-xs text-pisco-goldlt/80 leading-tight; }

        /* Nav */
        .ui-nav-wrap     { @apply flex-1 p-3 space-y-0.5 overflow-y-auto; }
        .ui-nav-section  { @apply text-[10px] uppercase font-semibold tracking-widest text-pisco-goldlt/60 px-3 pt-4 pb-1; }
        .ui-nav-link {
          @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-sky-100 text-sm font-medium transition-all duration-150;
        }
        .ui-nav-link:hover {
          @apply bg-white/10 text-white translate-x-1;
        }
        .ui-nav-link-active {
          @apply text-slate-900 font-semibold shadow-md;
          background: linear-gradient(90deg, #3c6e71, #d9d9d9) !important;
        }
        .ui-nav-icon { @apply text-base w-5 text-center; }

        /* Sidebar footer */
        .ui-aside-footer {
          @apply p-4 text-xs;
          border-top: 1px solid rgba(119, 172, 162, 0.35);
          background: rgba(0,0,0,0.2);
        }
        .ui-aside-user-avatar {
          @apply h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0;
          background: linear-gradient(135deg, #3c6e71, #284b63);
          color: #fff;
        }
        .ui-aside-user-name { @apply font-semibold text-white text-sm; }
        .ui-aside-user-role { @apply text-pisco-goldlt/70 capitalize; }
        .ui-logout-link {
          @apply flex items-center gap-1.5 mt-3 text-red-300 hover:text-red-200 text-xs transition-colors;
        }

        /* ─── Header ────────────────────────────────────── */
        .ui-header {
          @apply sticky top-0 z-10 backdrop-blur-md;
          border-bottom: 2px solid #3c6e71;
          background: linear-gradient(90deg, rgba(255,255,255,0.8) 0%, rgba(157,190,187,0.15) 100%);
        }
        .dark .ui-header {
          background: linear-gradient(90deg, rgba(15,23,42,0.8) 0%, rgba(3,25,38,0.8) 100%);
        }
        .ui-header-inner  { @apply flex items-center justify-between px-4 py-3 lg:px-8; }
        .ui-menu-btn      { @apply lg:hidden p-2 rounded-lg text-pisco-sky hover:bg-pisco-skylt/30; }
        .ui-page-title    { @apply font-display text-lg font-bold text-pisco-sky dark:text-pisco-skylt tracking-tight; }

        /* Breadcrumb stripe */
        .ui-breadcrumb {
          @apply px-4 lg:px-8 py-1.5 text-xs text-slate-500 dark:text-slate-400;
          border-bottom: 1px solid #e2e8f0;
          background: #f8fafc;
        }
        .dark .ui-breadcrumb {
          border-bottom-color: #1e293b;
          background: #0f172a;
        }

        /* Theme toggle */
        .ui-theme-toggle {
          @apply rounded-full border px-3 py-1.5 text-xs font-medium transition-all;
          border-color: #284b63;
          color: #284b63;
        }
        .ui-theme-toggle:hover { background: #284b6322; }

        /* ─── Main content ──────────────────────────────── */
        .ui-main { @apply flex-1 p-4 lg:p-8 overflow-y-auto overflow-x-hidden; }
        .ui-main-col { @apply flex-1 flex flex-col min-w-0 h-full overflow-hidden; }

        /* ─── Flash messages ────────────────────────────── */
        .ui-flash-ok  {
          @apply mx-4 mt-4 lg:mx-8 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2 animate-slide-up;
          background: #eafaf1; color: #1E8449; border-left: 4px solid #1E8449;
        }
        .ui-flash-err {
          @apply mx-4 mt-4 lg:mx-8 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2 animate-slide-up;
          background: #fdedec; color: #922B21; border-left: 4px solid #922B21;
        }

        /* ─── Login ─────────────────────────────────────── */
        .ui-login-wrap {
          @apply min-h-screen flex items-center justify-center p-4;
          background: linear-gradient(135deg, #353535 0%, #284b63 50%, #353535 100%);
        }
        .ui-login-card {
          @apply w-full max-w-md rounded-2xl p-8;
          background: #fff;
          border-top: 4px solid #3c6e71;
          box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }
        .ui-logo-lg {
          @apply h-20 w-20 rounded-full mx-auto mb-4 flex items-center justify-center overflow-hidden;
          border: 3px solid #3c6e71;
          background: #fff;
          box-shadow: 0 4px 20px rgba(119,172,162,0.35);
        }
        .ui-logo-lg img { @apply h-full w-full object-contain; }
        .ui-login-title { @apply text-center font-display text-2xl font-bold text-pisco-sky; }
        .ui-subtitle-center { @apply text-center text-slate-500 text-sm mb-6; }
        .ui-btn-login {
          @apply w-full rounded-xl font-semibold py-3 text-sm transition-all duration-200 text-white;
          background: linear-gradient(90deg, #353535, #284b63);
          border-bottom: 3px solid #3c6e71;
        }
        .ui-btn-login:hover { filter: brightness(1.08); }
        .ui-login-footer { @apply mt-4 text-center text-xs text-slate-400; }

        /* ─── Formularios ───────────────────────────────── */
        .ui-label        { @apply block text-sm font-semibold mb-1 text-slate-700 dark:text-slate-300; }
        .ui-label-plain  { @apply text-sm text-slate-600 dark:text-slate-400; }
        .ui-label-filter { @apply text-xs font-semibold text-slate-500 uppercase tracking-wide; }
        .ui-field {
          @apply w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm
                 focus:outline-none focus:border-pisco-sky focus:ring-2 focus:ring-pisco-sky/20 transition;
        }
        .ui-field-mt    { @apply mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:outline-none focus:border-pisco-sky focus:ring-2 focus:ring-pisco-sky/20 transition; }
        .ui-field-focus { @apply mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:outline-none focus:border-pisco-sky focus:ring-2 focus:ring-pisco-sky/20 transition; }
        .ui-field-mono  { @apply w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-mono focus:outline-none focus:border-pisco-sky; }
        .ui-field-mt-mono { @apply mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-mono; }
        .ui-field-grow  { @apply flex-1 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm; }
        .ui-field-search { @apply flex-1 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm; }
        .ui-required    { @apply text-red-500; }
        .ui-hint        { @apply text-slate-400 font-normal text-xs; }

        /* ─── Botones ───────────────────────────────────── */
        .ui-btn-primary {
          @apply inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition-all duration-150 active:scale-95;
          background: linear-gradient(90deg, #353535, #284b63);
          border-bottom: 2px solid #3c6e71;
        }
        .ui-btn-primary:hover { filter: brightness(1.1); }
        .ui-btn-primary-soft  { @apply inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium text-white; background: #284b63; }
        .ui-btn-primary-wide  { @apply inline-flex items-center gap-2 rounded-xl px-5 py-2 text-sm font-semibold text-white; background: linear-gradient(90deg, #353535, #284b63); border-bottom: 2px solid #3c6e71; }
        .ui-btn-outline       { @apply inline-flex items-center gap-2 rounded-xl border border-pisco-sky px-4 py-2 text-sm font-medium text-pisco-sky hover:bg-pisco-sky/5 transition; }
        .ui-btn-outline-wide  { @apply inline-flex items-center gap-2 rounded-xl border border-pisco-sky px-5 py-2 text-sm font-medium text-pisco-sky hover:bg-pisco-sky/5 transition; }
        .ui-btn-inline        { @apply inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white; background: linear-gradient(90deg, #353535, #284b63); }
        .ui-btn-ghost-danger  { @apply text-red-500 hover:text-red-700 text-xs font-medium transition; }

        /* ─── Paneles ───────────────────────────────────── */
        .ui-panel         { @apply rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm; }
        .ui-panel-p4      { @apply rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 space-y-3 shadow-sm; }
        .ui-panel-p5      { @apply rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm ui-hover-lift; }
        .ui-panel-p6      { @apply rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:p-6 shadow-sm ui-hover-lift; }
        .ui-panel-p6-stack{ @apply rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 shadow-sm; }
        .ui-panel-highlight { @apply sm:col-span-2 rounded-xl bg-pisco-sky/5 dark:bg-pisco-sky/10 px-4 py-3 text-sm border border-pisco-sky/20; }
        .ui-muted-inline  { @apply text-slate-400; }

        /* Stat card */
        .ui-stat-card {
          @apply rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900;
        }
        .ui-stat-card-gold {
          @apply rounded-2xl p-4 sm:p-5 shadow-md border text-white;
          background: linear-gradient(135deg, #3c6e71, #284b63);
          border-color: #3c6e71;
        }
        .ui-stat-card-sky {
          @apply rounded-2xl p-4 sm:p-5 shadow-md border text-white;
          background: linear-gradient(135deg, #353535, #284b63);
          border-color: #284b63;
        }
        .ui-stat-card-green {
          @apply rounded-2xl p-4 sm:p-5 shadow-md border text-white;
          background: linear-gradient(135deg, #284b63, #3c6e71);
          border-color: #3c6e71;
        }

        /* Forms / layouts */
        .ui-form-report     { @apply rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:p-6 mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3 shadow-sm; }
        .ui-form-actions-row { @apply flex flex-wrap items-end gap-2 md:col-span-2 xl:col-span-3; }
        .ui-toolbar         { @apply flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6; }
        .ui-toolbar-search  { @apply flex gap-2 flex-1 max-w-xl; }
        .ui-toolbar-end     { @apply mb-6 flex justify-end; }
        .ui-stack-narrow    { @apply max-w-lg mx-auto space-y-4; }
        .ui-stack-xl        { @apply max-w-xl space-y-6; }
        .ui-grid-2          { @apply grid gap-4 sm:gap-6 lg:grid-cols-2; }
        .ui-grid-asist      { @apply grid gap-4 sm:gap-8 lg:grid-cols-2; }
        .ui-grid-stats      { @apply grid gap-3 grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 mb-8; }
        .ui-chart-block     { @apply mt-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:p-6 shadow-sm; }

        /* ─── Tablas ────────────────────────────────────── */
        .ui-table-wrap        { @apply overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm; }
        .ui-table-wrap-shadow { @apply overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm; }
        .ui-table-head {
          @apply px-5 py-3.5 text-sm font-bold border-b-2 font-display;
          color: #353535; border-bottom-color: #3c6e71;
        }
        .dark .ui-table-head { color: #d9d9d9; border-bottom-color: #3c6e71; }
        .ui-table       { @apply min-w-full text-sm; }
        .ui-table-left  { @apply min-w-full text-sm text-left; }
        .ui-thead       { background: linear-gradient(90deg, #f0f5f5, #e0ebeb); }
        .dark .ui-thead { background: rgba(70,129,137,0.12); }
        .ui-thead-plain { @apply bg-slate-50 dark:bg-slate-800/50; }
        .ui-th          { @apply px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-pisco-sky dark:text-pisco-skylt; }
        .ui-th-right    { @apply px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-pisco-sky dark:text-pisco-skylt; }
        .ui-th-tight    { @apply px-3 py-2 text-left text-xs font-bold uppercase tracking-wide text-pisco-sky dark:text-pisco-skylt; }
        .ui-tbody       { @apply divide-y divide-slate-100 dark:divide-slate-800; }
        .ui-tr-hover    { @apply hover:bg-pisco-sky/5 dark:hover:bg-pisco-sky/10 transition-colors; }
        .ui-td          { @apply px-4 py-3.5; }
        .ui-td-compact  { @apply px-4 py-2.5; }
        .ui-td-tight    { @apply px-3 py-2; }
        .ui-empty       { @apply px-4 py-12 text-center text-slate-400; }

        /* ─── Badges / estados ──────────────────────────── */
        .ui-badge-base      { @apply inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold; }
        .ui-badge-ok        { @apply ui-badge-base; background:#eafaf1; color:#1E8449; }
        .ui-badge-warn      { @apply ui-badge-base; background:#fef9e7; color:#3c6e71; border: 1px solid rgba(119,172,162,0.3); }
        .ui-badge-err       { @apply ui-badge-base; background:#fdedec; color:#922B21; }
        .ui-badge-muted     { @apply ui-badge-base bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300; }

        /* Section headings */
        .ui-section-title {
          @apply font-display text-lg font-bold mb-4;
          color: #353535;
          padding-bottom: 0.5rem;
          border-bottom: 2px solid #3c6e71;
        }
        .dark .ui-section-title { color: #d9d9d9; }

        /* ─── Misceláneos ───────────────────────────────── */
        .ui-qr-reader       { @apply rounded-2xl overflow-hidden border-2 border-pisco-gold bg-black min-h-[240px]; }
        .ui-img-thumb       { @apply h-16 rounded-xl border-2 border-pisco-gold/30; }
        .ui-divider-list    { @apply divide-y divide-slate-100 dark:divide-slate-800 text-sm; }
        .ui-alert-amber     { @apply rounded-xl px-4 py-3 text-sm font-medium; background:#fef9e7; color:#7D6608; border-left: 4px solid #3c6e71; }
        .ui-text-success    { @apply text-sm font-medium text-pisco-green; }

        /* ─── Mobile overlay ────────────────────────────── */
        .ui-mobile-overlay  { z-index: 40; }
        .ui-mobile-backdrop { @apply absolute inset-0 bg-black/60 backdrop-blur-sm; }
        .ui-mobile-sheet    {
          @apply absolute left-0 top-0 h-full w-72 p-5 overflow-y-auto;
          background: linear-gradient(170deg, #353535 0%, #284b63 100%);
          border-right: 3px solid #3c6e71;
        }
        .ui-mobile-nav-title { @apply font-display font-bold text-white text-lg mb-4; }
        .ui-mobile-nav-link  { @apply block py-2.5 px-3 rounded-lg text-sky-100 hover:bg-white/10 hover:text-white text-sm transition; }
        .ui-mobile-nav-danger { @apply block py-2.5 px-3 rounded-lg text-red-300 hover:bg-red-900/20 text-sm mt-4 transition; }
      }
    </style>
</head>
<body class="ui-body">
<div class="ui-shell">

    <?php if ($user && !is_practicante_user($user)): ?>
    <!-- ═══ SIDEBAR ════════════════════════════════════════ -->
    <aside class="ui-aside hidden lg:flex w-64 flex-col shrink-0 h-full">
        <!-- Brand -->
        <div class="ui-aside-brand">
            <div class="ui-logo-sm">
                <!-- Reemplaza src con la ruta real del escudo -->
                <img src="<?= e(app_url('assets/img/escudo-pisco.png')) ?>" alt="Escudo Ciudad de Pisco" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <span style="display:none;align-items:center;justify-content:center;width:100%;height:100%;font-size:20px;">🏛️</span>
            </div>
            <div>
                <p class="ui-brand-muted">Municipalidad Provincial de</p>
                <p class="ui-brand-title">Pisco</p>
                <p class="ui-brand-muted" style="font-style:italic;font-size:10px;">3ricso</p>
            </div>
        </div>

        <!-- Navegación -->
        <nav class="ui-nav-wrap">
            <?php
            $r = $_GET['r'] ?? '';
            $nav = function (string $href, string $label, string $icon) use ($r) {
                $active = $r === $href ? ' ui-nav-link-active' : '';
                echo '<a class="ui-nav-link' . $active . '" href="' . e(app_url('index.php?r=' . $href)) . '">';
                echo '<span class="ui-nav-icon">' . $icon . '</span><span>' . e($label) . '</span></a>';
            };
            if (is_practicante_user($user)) {
                echo '<p class="ui-nav-section">Mi espacio</p>';
                $nav('mi_panel', 'Mi panel', '📋');
            } elseif (is_supervisor($user)) {
                echo '<p class="ui-nav-section">General</p>';
                $nav('inicio',       'Inicio',       '🏠');
                $nav('asistencia',   'Asistencia',   '📅');
                $nav('practicantes', 'Practicantes', '👤');
                $nav('reportes',     'Reportes',     '📄');
            } else {
                echo '<p class="ui-nav-section">General</p>';
                $nav('inicio',      'Inicio',        '🏠');
                $nav('asistencia',  'Asistencia',    '📅');
                $nav('practicantes','Practicantes',  '👤');
                $nav('escaner',     'Escaner QR',    '📷');
                $nav('reportes',    'Reportes',      '📄');
                if (is_admin($user)) {
                    echo '<p class="ui-nav-section">Administración</p>';
                    $nav('usuarios',  'Usuarios',              '🔐');
                    $nav('catalogos', 'Áreas e instituciones', '🏷️');
                }
            }
            ?>
        </nav>


    </aside>
    <?php endif; ?>

    <!-- ═══ CONTENIDO PRINCIPAL ═══════════════════════════ -->
    <div class="ui-main-col">

        <!-- Header -->
        <?php if (!($hide_header ?? false)): ?>
        <header class="ui-header">
            <div class="ui-header-inner">
                <div class="flex items-center gap-3">
                    <?php if ($user): ?>
                        <?php if (!is_practicante_user($user)): ?>
                        <button type="button" class="ui-menu-btn" id="openSidebar" aria-label="Menú">☰</button>
                        <?php else: ?>
                        <?php if (($_GET['r'] ?? '') === 'mi_qr'): ?>
                        <a href="<?= e(app_url('index.php?r=mi_panel')) ?>" class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-[#284b63]/10 text-[#284b63] dark:text-[#3c6e71] hover:bg-[#284b63]/20 transition-colors text-sm font-semibold" title="Volver a Mi Panel">
                            ⬅ Volver
                        </a>
                        <?php else: ?>
                        <a href="<?= e(app_url('index.php?r=logout')) ?>" class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-500/20 transition-colors text-sm font-semibold" title="Cerrar sesión">
                            ⬅ Salir
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <h1 class="ui-page-title"><?= e($title ?? '') ?></h1>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="themeToggle" class="ui-theme-toggle">
                        <span class="dark:hidden">🌙 Oscuro</span>
                        <span class="hidden dark:inline">☀️ Claro</span>
                    </button>
                    <?php if ($user): ?>
                    <!-- User info dropdown -->
                    <div class="relative" id="userMenuWrap">
                        <button type="button" id="userMenuBtn"
                                class="flex items-center gap-2 rounded-full pl-1 pr-3 py-1 text-sm font-medium transition-all hover:bg-pisco-sky/10"
                                style="border: 1.5px solid rgba(70,129,137,0.25);">
                            <span class="h-7 w-7 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                                  style="background: linear-gradient(135deg,#284b63,#3c6e71);">
                                <?= mb_strtoupper(mb_substr($user['nombres'], 0, 1)) ?>
                            </span>
                            <span class="hidden sm:block text-slate-700 dark:text-slate-200 max-w-[120px] truncate">
                                <?= e(explode(' ', $user['nombres'])[0]) ?>
                            </span>
                            <span class="text-slate-400 text-xs">▾</span>
                        </button>
                        <!-- Dropdown -->
                        <div id="userMenu"
                             class="hidden absolute right-0 top-full mt-2 w-52 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 z-50 overflow-hidden">
                            <div class="px-4 py-3" style="border-bottom:1px solid rgba(70,129,137,0.15);">
                                <p class="font-semibold text-sm text-slate-700 dark:text-slate-200 truncate"><?= e($user['nombres']) ?></p>
                                <p class="text-xs text-slate-400 capitalize mt-0.5"><?= e($user['rol']) ?></p>
                            </div>
                            <div class="p-2">
                                <a href="<?= e(app_url('index.php?r=logout')) ?>"
                                   class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <span>⬅</span> Cerrar sesión
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <!-- Flash messages -->
        <?php if (!($hide_header ?? false)): ?>
            <?php if ($msg = flash('ok')): ?>
                <div class="ui-flash-ok"><span>✅</span><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('err')): ?>
                <div class="ui-flash-err"><span>⚠️</span><?= e($msg) ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Página -->
        <main class="<?= ($hide_header ?? false) ? '' : 'ui-main' ?> ui-animate-entry">
            <?= $content ?? '' ?>
        </main>
    </div>
</div>

<!-- ═══ MENÚ MOBILE ════════════════════════════════════════ -->
<?php if ($user && !is_practicante_user($user)): ?>
<div id="mobileNav" class="ui-mobile-overlay fixed inset-0 lg:hidden hidden">
    <div class="ui-mobile-backdrop" data-close-sidebar></div>
    <div class="ui-mobile-sheet">
        <div class="flex items-center gap-3 mb-5 pb-4" style="border-bottom:1px solid rgba(119,172,162,0.35)">
            <div class="ui-logo-sm" style="width:2.5rem;height:2.5rem;">
                <img src="<?= e(app_url('assets/img/escudo-pisco.png')) ?>" alt="Escudo" onerror="this.style.display='none'">
            </div>
            <p class="ui-mobile-nav-title mb-0">Pisco</p>
        </div>
        <nav class="space-y-0.5">
            <?php
            if (is_practicante_user($user)) {
                echo '<a class="ui-mobile-nav-link" href="' . e(app_url('index.php?r=mi_panel')) . '">📋 Mi panel</a>';
            } elseif (is_supervisor($user)) {
                echo '<a class="ui-mobile-nav-link" href="' . e(app_url('index.php?r=inicio')) . '">🏠 Inicio</a>';
                echo '<a class="ui-mobile-nav-link" href="' . e(app_url('index.php?r=asistencia')) . '">📅 Asistencia</a>';
                echo '<a class="ui-mobile-nav-link" href="' . e(app_url('index.php?r=practicantes')) . '">👤 Practicantes</a>';
                echo '<a class="ui-mobile-nav-link" href="' . e(app_url('index.php?r=reportes')) . '">📄 Reportes</a>';
            } else {
                foreach ([
                    'inicio'      => '🏠 Inicio',
                    'asistencia'  => '📅 Asistencia',
                    'practicantes'=> '👤 Practicantes',
                    'escaner'     => '📷 Escaner QR',
                    'reportes'    => '📄 Reportes',
                ] as $k => $lab) {
                    echo '<a class="ui-mobile-nav-link" href="' . e(app_url('index.php?r=' . $k)) . '">' . e($lab) . '</a>';
                }
                if (is_admin($user)) {
                    echo '<a class="ui-mobile-nav-link" href="' . e(app_url('index.php?r=usuarios')) . '">🔐 Usuarios</a>';
                    echo '<a class="ui-mobile-nav-link" href="' . e(app_url('index.php?r=catalogos')) . '">🏷️ Áreas e instituciones</a>';
                }
            }
            ?>
            <a class="ui-mobile-nav-danger" href="<?= e(app_url('index.php?r=logout')) ?>">⬅ Cerrar sesión</a>
        </nav>
    </div>
</div>
<script>
document.getElementById('openSidebar')?.addEventListener('click', () =>
    document.getElementById('mobileNav').classList.remove('hidden'));
document.querySelectorAll('[data-close-sidebar]').forEach(el =>
    el.addEventListener('click', () =>
        document.getElementById('mobileNav').classList.add('hidden')));
</script>
<?php endif; ?>

<!-- ═══ TEMA ════════════════════════════════════════════════ -->
<script>
(function(){
  const btn  = document.getElementById('themeToggle');
  const root = document.documentElement;
  btn?.addEventListener('click', () => {
    const dark = root.classList.toggle('dark');
    document.cookie = 'theme=' + (dark ? 'dark' : 'light') + ';path=/;max-age=31536000';
  });
})();

// ─── User menu dropdown ───────────────────────────────────────
(function(){
  const btn  = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userMenu');
  if (!btn || !menu) return;

  btn.addEventListener('click', function(e) {
    e.stopPropagation();
    menu.classList.toggle('hidden');
  });

  document.addEventListener('click', function(e) {
    if (!document.getElementById('userMenuWrap')?.contains(e.target)) {
      menu.classList.add('hidden');
    }
  });
})();
</script>
</body>
</html>