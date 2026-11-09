<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FBManager') }} — @yield('title', 'Accueil')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bleu-france: #002395;
            --bleu-france-dark: #001a6e;
            --bleu-france-soft: #E8EBF5;
            --rouge-france: #ED2939;
            --rouge-doux: #FEE2E2;
            --vert-ok: #047857;
            --vert-doux: #ECFDF5;
            --jaune-doux: #FEF3C7;
            --jaune-fonce: #B45309;

            --canvas: #F5F7FA;
            --surface: #FFFFFF;
            --surface-subtle: #F8FAFC;
            --surface-hover: #F1F5F9;

            --text-primary: #0F172A;
            --text-secondary: #64748B;
            --text-muted: #94A3B8;

            --border: #E2E8F0;
            --border-strong: #CBD5E1;

            --radius-sm: 6px;
            --radius: 8px;
            --radius-lg: 12px;
            --radius-full: 999px;

            --shadow-sm: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
            --shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.06), 0 1px 2px 0 rgba(15, 23, 42, 0.04);
            --shadow-md: 0 4px 12px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 10px 30px rgba(15, 23, 42, 0.12);

            --sidebar-width: 72px;
            --list-width: 340px;

            --ease: cubic-bezier(0.2, 0.8, 0.2, 1);
            --duration: 150ms;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { height: 100%; }
        body {
            font-family: 'Outfit', -apple-system, sans-serif;
            background: var(--canvas);
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Sora', 'Outfit', sans-serif;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }

        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; cursor: pointer; border: none; background: none; }

        /* ═══════════════════════════════════════════════════════
           APP SHELL : Icon Sidebar + Main area
           ═══════════════════════════════════════════════════════ */

        .app {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        /* ─── ICON SIDEBAR (gauche, 72px) ─── */
        .icon-sidebar {
            width: var(--sidebar-width);
            background: var(--bleu-france);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 0;
            flex-shrink: 0;
            color: #fff;
        }
        .icon-sidebar__logo {
            width: 44px;
            height: 44px;
            background: #fff;
            color: var(--bleu-france);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        .icon-sidebar__nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            width: 100%;
            padding: 0 10px;
            align-items: center;
        }
        .icon-sidebar__item {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius);
            color: rgba(255, 255, 255, 0.7);
            font-size: 20px;
            transition: all var(--duration) var(--ease);
            position: relative;
        }
        .icon-sidebar__item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .icon-sidebar__item.is-active {
            background: #fff;
            color: var(--bleu-france);
            box-shadow: var(--shadow-md);
        }
        .icon-sidebar__item[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            margin-left: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--text-primary);
            color: #fff;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            font-weight: 500;
            box-shadow: var(--shadow-md);
        }
        .icon-sidebar__footer {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
        }
        .icon-sidebar__avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fff, #E8EBF5);
            color: var(--bleu-france);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }
        .logout-btn {
            width: 44px;
            height: 36px;
            border-radius: var(--radius-sm);
            color: rgba(255, 255, 255, 0.7);
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--duration) var(--ease);
        }
        .logout-btn:hover { color: #fff; background: rgba(255, 255, 255, 0.1); }

        /* ─── MAIN AREA ─── */
        .main {
            flex: 1;
            display: flex;
            overflow: hidden;
            background: var(--canvas);
        }

        /* ─── LIST COLUMN (320px, pour posts / messenger) ─── */
        .list-col {
            width: var(--list-width);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .list-col__head {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
        }
        .list-col__title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .list-col__title {
            font-family: 'Sora', sans-serif;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .list-col__count {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .list-col__search {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface-subtle);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 8px 12px;
            transition: all var(--duration) var(--ease);
        }
        .list-col__search:focus-within {
            border-color: var(--bleu-france);
            background: var(--surface);
        }
        .list-col__search i { color: var(--text-muted); font-size: 13px; }
        .list-col__search input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-family: inherit;
            font-size: 13px;
            color: var(--text-primary);
        }
        .list-col__body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .list-col__body::-webkit-scrollbar { width: 6px; }
        .list-col__body::-webkit-scrollbar-track { background: transparent; }
        .list-col__body::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 3px; }

        /* ─── CONTENT AREA (reste, pour détail) ─── */
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--canvas);
        }
        .content-full {
            flex: 1;
            overflow-y: auto;
            background: var(--canvas);
        }
        .content-full__inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px;
        }

        /* ─── TOP BAR dans content ─── */
        .top-bar {
            height: 60px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            flex-shrink: 0;
        }
        .top-bar__title {
            font-family: 'Sora', sans-serif;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .top-bar__title .breadcrumb {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 13px;
        }
        .top-bar__actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        /* ─── EMPTY STATE ─── */
        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: var(--text-muted);
            text-align: center;
        }
        .empty-state__icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--bleu-france-soft);
            color: var(--bleu-france);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
        }
        .empty-state__title {
            font-family: 'Sora', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
        }
        .empty-state__text {
            font-size: 14px;
            color: var(--text-secondary);
            max-width: 320px;
            line-height: 1.5;
        }

        /* ─── BUTTONS ─── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 16px;
            height: 38px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all var(--duration) var(--ease);
            white-space: nowrap;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: var(--shadow); }
        .btn:active { transform: scale(0.98); }
        .btn-primary { background: var(--bleu-france); color: #fff; }
        .btn-primary:hover { background: var(--bleu-france-dark); }
        .btn-success { background: var(--vert-ok); color: #fff; }
        .btn-danger { background: var(--rouge-france); color: #fff; }
        .btn-secondary { background: var(--surface); color: var(--text-primary); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--surface-subtle); border-color: var(--border-strong); }
        .btn-ghost { background: transparent; color: var(--text-secondary); }
        .btn-ghost:hover { background: var(--surface-hover); color: var(--text-primary); box-shadow: none; transform: none; }
        .btn-sm { height: 32px; padding: 6px 12px; font-size: 12px; }
        .btn-icon { width: 38px; padding: 0; }

        /* ─── BADGES ─── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-admin { background: var(--rouge-doux); color: #991B1B; }
        .badge-user { background: var(--bleu-france-soft); color: var(--bleu-france); }
        .badge-nonlu { background: var(--rouge-doux); color: #991B1B; }
        .badge-lu { background: var(--vert-doux); color: var(--vert-ok); }
        .badge-count { background: var(--bleu-france); color: #fff; min-width: 22px; text-align: center; justify-content: center; }

        /* ─── ALERTS ─── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: var(--vert-doux); color: var(--vert-ok); border: 1px solid #A7F3D0; }
        .alert-error { background: var(--rouge-doux); color: #991B1B; border: 1px solid #FECACA; }

        /* ─── FORMS ─── */
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 6px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            font-family: inherit;
            font-size: 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            color: var(--text-primary);
            outline: none;
            transition: all var(--duration) var(--ease);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--bleu-france);
            box-shadow: 0 0 0 3px rgba(0, 35, 149, 0.1);
        }
        .form-error { color: var(--rouge-france); font-size: 12px; margin-top: 4px; }

        /* ─── TABLES ─── */
        .table-container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: var(--surface-subtle);
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid var(--border);
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            color: var(--text-primary);
            vertical-align: middle;
        }
        tr:hover td { background: var(--surface-subtle); }
        tr:last-child td { border-bottom: none; }

        /* ─── CARDS ─── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: 'Sora', sans-serif;
            font-size: 15px;
            font-weight: 600;
        }
        .card-body { padding: 20px; }

        /* ─── PAGINATION ─── */
        .pagination-wrapper {
            padding: 12px 16px;
            border-top: 1px solid var(--border);
            background: var(--surface);
            display: flex;
            justify-content: center;
        }
        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
        }
        .pagination a, .pagination span {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            color: var(--text-secondary);
            background: var(--surface);
            border: 1px solid var(--border);
            min-width: 32px;
            text-align: center;
        }
        .pagination a:hover { background: var(--bleu-france-soft); color: var(--bleu-france); }
        .pagination .active span { background: var(--bleu-france); color: #fff; border-color: var(--bleu-france); font-weight: 600; }

        /* ═══════════════════════════════════════════════════════
           MOBILE MENU TOGGLE
           ═══════════════════════════════════════════════════════ */
        .mobile-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: var(--radius);
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-primary);
            font-size: 18px;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1024px) {
            :root { --list-width: 300px; }
        }

        @media (max-width: 768px) {
            .icon-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                transform: translateX(-100%);
                z-index: 500;
                transition: transform var(--duration) var(--ease);
            }
            .icon-sidebar.is-open { transform: translateX(0); }
            .mobile-toggle { display: flex; }
            .list-col { width: 100%; }
            .list-col:not(.is-visible-mobile) { display: none; }
            .content:not(.is-visible-mobile) { display: none; }
            .top-bar { padding: 0 14px; }
            .content-full__inner { padding: 16px; }
        }
    </style>
    @stack('head')
</head>
<body>
    @auth
    <div class="app">
        {{-- ═══ ICON SIDEBAR ═══ --}}
        <aside class="icon-sidebar" id="icon-sidebar">
            <a href="/posts" class="icon-sidebar__logo" title="FBManager">fb</a>
            <nav class="icon-sidebar__nav">
                <a href="/posts" class="icon-sidebar__item {{ request()->is('posts*') ? 'is-active' : '' }}" data-tooltip="Publications">
                    <i class="fa-solid fa-newspaper"></i>
                </a>
                <a href="/messenger" class="icon-sidebar__item {{ request()->is('messenger*') ? 'is-active' : '' }}" data-tooltip="Messenger">
                    <i class="fa-brands fa-facebook-messenger"></i>
                </a>
                @if(auth()->user()->isAdmin())
                <a href="/users" class="icon-sidebar__item {{ request()->is('users*') ? 'is-active' : '' }}" data-tooltip="Utilisateurs">
                    <i class="fa-solid fa-users"></i>
                </a>
                @endif
            </nav>
            <div class="icon-sidebar__footer">
                <div class="icon-sidebar__avatar" data-tooltip="{{ auth()->user()->username }}" title="{{ auth()->user()->username }}">
                    {{ strtoupper(substr(auth()->user()->username, 0, 2)) }}
                </div>
                <form action="/logout" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="logout-btn" title="Déconnexion"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </aside>

        {{-- ═══ MAIN AREA ═══ --}}
        <div class="main">
            @yield('main')
        </div>
    </div>

    {{-- ═══ TOASTS GLOBAUX ═══ --}}
    @if(session('success'))
        <div class="toast-global toast-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="toast-global toast-error">{{ session('error') }}</div>
    @endif
    <style>
        .toast-global {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 18px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            font-size: 14px;
            font-weight: 500;
            z-index: 10000;
            animation: slideIn 0.3s var(--ease);
        }
        .toast-success { background: var(--vert-ok); color: #fff; }
        .toast-error { background: var(--rouge-france); color: #fff; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
    <script>
        // Auto-hide toasts
        setTimeout(() => {
            document.querySelectorAll('.toast-global').forEach(t => {
                t.style.transition = 'all 0.3s'; t.style.opacity = '0'; t.style.transform = 'translateX(100%)';
                setTimeout(() => t.remove(), 300);
            });
        }, 4000);
    </script>
    @endauth

    @guest
        @yield('main')
    @endguest

    @stack('scripts')
</body>
</html>
