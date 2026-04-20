<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FBManager') }} — @yield('title', 'Accueil')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bleu-france: #002395;
            --blanc: #FFFFFF;
            --rouge-france: #ED2939;
            --gris-clair: #F0F0F0;
            --gris-moyen: #D4D4D4;
            --gris-fonce: #333333;
            --bleu-ciel: #5B9BD5;
            --vert-ok: #2E7D32;
            --jaune-doux: #FFF3CD;
            --rouge-doux: #F8D7DA;
            --vert-doux: #D4EDDA;
            --ombre: 0 1px 3px rgba(0,0,0,0.06);
            --radius: 6px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gris-clair);
            color: var(--gris-fonce);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── NAVBAR ─── */
        .navbar {
            background: var(--bleu-france);
            color: var(--blanc);
            padding: 0;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 60px;
            min-height: 60px;
            flex-wrap: wrap;
        }
        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: var(--blanc);
            text-decoration: none;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand .logo-icon {
            width: 32px;
            height: 32px;
            background: var(--blanc);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bleu-france);
            font-weight: bold;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
        }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 0;
            list-style: none;
        }
        .navbar-links a {
            color: var(--blanc);
            text-decoration: none;
            padding: 18px 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            letter-spacing: 1px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .navbar-links a:hover,
        .navbar-links a.active {
            background: rgba(255,255,255,0.15);
        }
        .navbar-links a.active {
            border-bottom: 3px solid var(--rouge-france);
        }
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .navbar-user span {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
        }
        .btn-logout {
            background: var(--rouge-france);
            color: var(--blanc);
            border: none;
            padding: 8px 18px;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            cursor: pointer;
            border-radius: var(--radius);
            transition: opacity 0.2s;
        }
        .btn-logout:hover { opacity: 0.85; }

        /* Mobile menu toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: 2px solid var(--blanc);
            color: var(--blanc);
            font-size: 24px;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
        }

        /* ─── MAIN ─── */
        .main-content {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 30px 60px;
        }

        /* ─── CARDS ─── */
        .card {
            background: var(--blanc);
            border-radius: var(--radius);
            box-shadow: var(--ombre);
            border: 1px solid var(--gris-moyen);
            overflow: hidden;
        }
        .card-header {
            background: var(--bleu-france);
            color: var(--blanc);
            padding: 15px 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-body {
            padding: 20px;
        }

        /* ─── PAGE HEADER ─── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            color: var(--bleu-france);
            line-height: 1.6;
        }

        /* ─── SEARCH BAR ─── */
        .search-bar {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-bar input {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            padding: 10px 16px;
            border: 2px solid var(--gris-moyen);
            border-radius: var(--radius);
            outline: none;
            width: 300px;
            max-width: 100%;
            transition: border-color 0.2s;
        }
        .search-bar input:focus {
            border-color: var(--bleu-france);
        }
        .search-bar button {
            padding: 10px 20px;
        }

        /* ─── BUTTONS ─── */
        .btn {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            padding: 10px 24px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s, transform 0.1s;
            letter-spacing: 0.5px;
        }
        .btn:hover { opacity: 0.9; }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: var(--bleu-france); color: var(--blanc); }
        .btn-danger { background: var(--rouge-france); color: var(--blanc); }
        .btn-success { background: var(--vert-ok); color: var(--blanc); }
        .btn-secondary { background: var(--gris-moyen); color: var(--gris-fonce); }
        .btn-sm { font-size: 17px; padding: 6px 14px; }

        /* ─── TABLE ─── */
        .table-container { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Poppins', sans-serif;
        }
        th {
            background: var(--bleu-france);
            color: var(--blanc);
            padding: 12px 16px;
            text-align: left;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            letter-spacing: 1px;
            white-space: nowrap;
        }
        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gris-moyen);
            font-size: 15px;
        }
        tr:hover td { background: rgba(0,35,149,0.03); }

        /* ─── BADGES ─── */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            letter-spacing: 0.5px;
        }
        .badge-admin { background: var(--rouge-france); color: var(--blanc); }
        .badge-user { background: var(--bleu-ciel); color: var(--blanc); }
        .badge-nonlu { background: var(--rouge-france); color: var(--blanc); }
        .badge-lu { background: var(--vert-ok); color: var(--blanc); }
        .badge-count {
            background: var(--bleu-france);
            color: var(--blanc);
            min-width: 28px;
            text-align: center;
            border-radius: 50%;
            padding: 4px 8px;
        }

        /* ─── ALERTS ─── */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
        }
        .alert-success { background: var(--vert-doux); color: var(--vert-ok); }
        .alert-error { background: var(--rouge-doux); color: var(--rouge-france); }

        /* ─── FORMS ─── */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            margin-bottom: 6px;
            color: var(--bleu-france);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            padding: 10px 14px;
            border: 2px solid var(--gris-moyen);
            border-radius: var(--radius);
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--bleu-france);
        }
        .form-error {
            color: var(--rouge-france);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            margin-top: 4px;
        }

        /* ─── POST CARDS ─── */
        .post-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
        }
        .post-card {
            background: var(--blanc);
            border: 1px solid var(--gris-moyen);
            border-radius: var(--radius);
            box-shadow: var(--ombre);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .post-card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: var(--gris-moyen);
        }
        .post-card-body {
            padding: 16px;
        }
        .post-card-body h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            color: var(--bleu-france);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .post-card-body p {
            font-size: 14px;
            line-height: 1.5;
            color: #555;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .post-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-top: 1px solid var(--gris-moyen);
            background: #FAFAFA;
        }
        .post-card-footer .meta {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: #888;
        }

        /* ─── COMMENT LIST ─── */
        .comment-item {
            padding: 16px;
            border-bottom: 1px solid var(--gris-moyen);
        }
        .comment-item:last-child { border-bottom: none; }
        .comment-author {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            color: var(--bleu-france);
            margin-bottom: 4px;
        }
        .comment-text {
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 6px;
        }
        .comment-date {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: #999;
        }

        /* ─── MESSENGER ─── */
        .convo-list { list-style: none; }
        .convo-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--gris-moyen);
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
            gap: 16px;
        }
        .convo-item:hover { background: rgba(0,35,149,0.04); }
        .convo-item.non-lu { background: var(--jaune-doux); }
        .convo-item.non-lu:hover { background: #FFEEBA; }
        .convo-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--bleu-france);
            color: var(--blanc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            flex-shrink: 0;
        }
        .convo-info { flex: 1; min-width: 0; }
        .convo-name {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            color: var(--bleu-france);
            margin-bottom: 3px;
        }
        .convo-preview {
            font-size: 14px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .convo-meta {
            text-align: right;
            flex-shrink: 0;
        }
        .convo-date {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            color: #999;
        }

        /* Chat messages */
        .chat-container {
            max-height: 600px;
            overflow-y: auto;
            padding: 20px;
            background: #F8F9FA;
        }
        .chat-msg {
            display: flex;
            margin-bottom: 16px;
        }
        .chat-msg.sent { justify-content: flex-end; }
        .chat-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 15px;
            line-height: 1.5;
        }
        .chat-msg.received .chat-bubble {
            background: var(--blanc);
            border: 1px solid var(--gris-moyen);
            border-bottom-left-radius: 4px;
        }
        .chat-msg.sent .chat-bubble {
            background: var(--bleu-france);
            color: var(--blanc);
            border-bottom-right-radius: 4px;
        }
        .chat-sender {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: var(--bleu-france);
            margin-bottom: 4px;
        }
        .chat-time {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #999;
            margin-top: 6px;
        }
        .chat-msg.sent .chat-time { color: rgba(255,255,255,0.7); }

        /* ─── PAGINATION ─── */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            padding: 20px 0;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pagination-wrapper a,
        .pagination-wrapper span {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            padding: 8px 14px;
            border: 1px solid var(--gris-moyen);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--bleu-france);
            transition: all 0.2s;
        }
        .pagination-wrapper a:hover {
            background: var(--bleu-france);
            color: var(--blanc);
        }
        .pagination-wrapper .active span {
            background: var(--bleu-france);
            color: var(--blanc);
            border-color: var(--bleu-france);
        }
        .pagination-wrapper .disabled span {
            color: var(--gris-moyen);
        }

        /* ─── EMPTY STATE ─── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            color: #999;
        }

        /* ─── FOOTER ─── */
        .footer {
            text-align: center;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: #999;
            border-top: 2px solid var(--gris-moyen);
            margin-top: auto;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 768px) {
            .menu-toggle { display: block; }
            .navbar-links {
                display: none;
                flex-direction: column;
                width: 100%;
                order: 3;
            }
            .navbar-links.open { display: flex; }
            .navbar-links a {
                padding: 14px 20px;
                border-top: 1px solid rgba(255,255,255,0.1);
            }
            .navbar-user {
                order: 2;
            }
            .page-header { flex-direction: column; align-items: flex-start; }
            .page-header h1 { font-size: 14px; }
            .search-bar { width: 100%; }
            .search-bar input { width: 100%; }
            .post-grid { grid-template-columns: 1fr; }
            .card-header { font-size: 20px; }
            .chat-bubble { max-width: 85%; }
        }

        @media (max-width: 480px) {
            .navbar-brand { font-size: 11px; }
            .main-content { padding: 15px 10px; }
            th, td { padding: 8px 10px; font-size: 13px; }
        }
    </style>
</head>
<body>
    @auth
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="/posts" class="navbar-brand">
                <span class="logo-icon">fb</span>
                FBManager
            </a>

            <button class="menu-toggle" onclick="document.querySelector('.navbar-links').classList.toggle('open')">
                &#9776;
            </button>

            <ul class="navbar-links">
                <li><a href="/posts" class="{{ request()->is('posts*') ? 'active' : '' }}"><i class="fa-brands fa-facebook"></i> Publications</a></li>
                {{-- <li><a href="/messenger" class="{{ request()->is('messenger*') ? 'active' : '' }}"><i class="fa-brands fa-facebook-messenger"></i> Messenger</a></li> --}}
                @if(auth()->user()->isAdmin())
                <li><a href="/users" class="{{ request()->is('users*') ? 'active' : '' }}"><i class="fa-solid fa-users-gear"></i> Utilisateurs</a></li>
                @endif
            </ul>

            <div class="navbar-user">
                <span>{{ auth()->user()->username }}</span>
                <form action="/logout" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="btn-logout">Deconnexion</button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        FBManager &copy; {{ date('Y') }} — Facebook Comment Manager
    </footer>
</body>
</html>
