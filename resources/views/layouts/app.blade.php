<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SALIKSIC')</title>
    <style>
        :root {
            --nav-bg: #101828;
            --nav-text: #f8fafc;
            --nav-muted: rgba(248, 250, 252, 0.6);
            --accent: #2563eb;
            --body-bg: #f1f5f9;
            --surface: #ffffff;
            --border: #e2e8f0;
            --radius: 16px;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: var(--body-bg); color: #0f172a; font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { display: flex; min-height: 100vh; transition: padding-left .25s ease; }
        .app-sidebar {
            width: 260px;
            background: var(--nav-bg);
            color: var(--nav-text);
            display: flex;
            flex-direction: column;
            padding: 28px 22px 32px;
            gap: 28px;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .brand {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .brand .logo {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }
        .brand .tagline {
            font-size: 0.9rem;
            color: var(--nav-muted);
        }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
        nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
            padding: 10px 12px;
            border-radius: 10px;
            font-weight: 500;
            transition: background 0.2s ease, color 0.2s ease;
        }
        nav a:hover { background: rgba(148, 163, 184, 0.18); color: #ffffff; }
        nav a .icon { font-size: 1.2rem; }
        nav a .label { flex: 1; }
        .nav-section { display: flex; flex-direction: column; gap: 10px; }
        .nav-section h3 { margin: 0; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.17em; color: var(--nav-muted); }
        .nav-sublist { margin-top: 4px; margin-left: 10px; padding-left: 10px; border-left: 1px solid rgba(248,250,252,0.12); display: flex; flex-direction: column; gap: 4px; }
        .nav-sublist a { font-size: 0.95rem; padding: 8px 10px 8px 28px; color: var(--nav-muted); position: relative; }
        .nav-sublist a::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: rgba(248,250,252,0.6);
        }
        .nav-sublist a:hover { color: #fff; background: rgba(148,163,184,0.16); }
        .app-main { flex: 1; padding: 32px 36px; display: flex; flex-direction: column; gap: 24px; }
        header.app-header { background: var(--surface); border-radius: var(--radius); padding: 18px 22px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 18px 40px -30px rgba(15, 23, 42, 0.45); }
        header .title { display: flex; flex-direction: column; }
        header .title span:first-child { font-size: 0.78rem; letter-spacing: 0.2em; text-transform: uppercase; color: #64748b; }
        header .title span:last-child { font-size: 1.4rem; font-weight: 600; color: #1e293b; }
        header .actions { display: flex; gap: 10px; align-items: center; }
        .pill { padding: 8px 14px; border-radius: 999px; background: rgba(37, 99, 235, 0.12); color: var(--accent); font-weight: 600; text-decoration: none; border: 1px solid rgba(37, 99, 235, 0.24); }
        .toggle-nav {
            border: 1px solid rgba(148, 163, 184, 0.45);
            background: #fff;
            color: #475569;
            border-radius: 10px;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color .2s ease, background .2s ease;
        }
        .toggle-nav:hover { border-color: #94a3b8; background: #f1f5f9; }
        .toggle-nav svg { width: 18px; height: 18px; }
        main.app-content { flex: 1; }
        body.sidebar-collapsed .app-sidebar { width: 84px; padding: 22px 12px; align-items: center; }
        body.sidebar-collapsed .app-sidebar .tagline,
        body.sidebar-collapsed .app-sidebar .nav-section h3 { display: none; }
        body.sidebar-collapsed nav a { justify-content: center; padding: 12px 0; }
        body.sidebar-collapsed nav a .label { display: none; }
        body.sidebar-collapsed .app-main { padding-left: 0; }
        body.sidebar-collapsed .nav-sublist {
            position: absolute;
            left: 72px;
            top: 0;
            background: var(--nav-bg);
            border: 1px solid rgba(148,163,184,0.22);
            border-radius: 12px;
            padding: 10px;
            display: none;
            box-shadow: 0 16px 30px -24px rgba(15,23,42,0.6);
        }
        body.sidebar-collapsed li { position: relative; }
        body.sidebar-collapsed li:hover > .nav-sublist { display: flex; }
        body.sidebar-collapsed .nav-sublist a { padding: 8px 12px; }
        body.sidebar-collapsed .nav-sublist a::before { display: none; }
        @media (max-width: 960px) {
            body { flex-direction: column; }
            .app-sidebar { flex-direction: row; width: 100%; height: auto; position: static; gap: 16px; padding: 18px 20px; overflow-x: auto; }
            .nav-section { flex-direction: row; align-items: center; gap: 16px; }
            .nav-sublist { margin-left: 0; padding-left: 0; border-left: none; flex-direction: row; gap: 12px; }
            .app-main { padding: 22px; }
            body.sidebar-collapsed .app-sidebar { width: 100%; }
            body.sidebar-collapsed .nav-sublist { position: static; display: flex; background: transparent; border: none; box-shadow: none; padding: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="app-sidebar">
        <div class="brand">
            <span class="logo">SALIKSIC EBM</span>
            <span class="tagline">Critical appraisal, simplified.</span>
        </div>

        <nav class="nav-section">
            <h3>Main</h3>
            <ul>
                <li>
                    <a href="{{ route('home') }}">
                        <span class="icon">🏠</span>
                        <span class="label">Home</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('calculators.index') }}">
                        <span class="icon">🧮</span>
                        <span class="label">Calculators</span>
                    </a>
                    <div class="nav-sublist">
                        <a href="{{ route('therapy.article.form') }}">Therapy / Harm</a>
                        <a href="{{ route('calculators.diagnostics') }}">Diagnostics</a>
                        <a href="{{ route('calculators.prognosis') }}">Prognosis</a>
                    </div>
                </li>
                <li>
                    <a href="{{ route('therapy.studies.list') }}">
                        <span class="icon">📚</span>
                        <span class="label">Studies</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <div class="app-main">
        <header class="app-header">
            <div class="title">
                <span>@yield('title_prefix', 'Evidence-Based Medicine')</span>
                <span>@yield('title', 'SALIKSIC')</span>
            </div>
            <div class="actions">
                <button type="button" class="toggle-nav" id="sidebarToggle" aria-label="Toggle navigation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <a class="pill" href="{{ route('therapy.article.form') }}">New Study</a>
                <a class="pill" href="{{ route('therapy.studies.list') }}">View Studies</a>
            </div>
        </header>

        <main class="app-content">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
    <script>
        (function(){
            const body = document.body;
            const storageKey = 'saliksic-sidebar-collapsed';
            const toggle = document.getElementById('sidebarToggle');
            const apply = (collapsed) => {
                if (collapsed) {
                    body.classList.add('sidebar-collapsed');
                } else {
                    body.classList.remove('sidebar-collapsed');
                }
            };
            const saved = localStorage.getItem(storageKey) === '1';
            apply(saved);
            toggle?.addEventListener('click', () => {
                const next = !body.classList.contains('sidebar-collapsed');
                apply(next);
                localStorage.setItem(storageKey, next ? '1' : '0');
            });
        })();
    </script>
</body>
</html>
