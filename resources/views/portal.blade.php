<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Data Console</title>
    <meta name="description" content="One console for searching, monitoring quality, detecting duplicates, and checking the health of the ws_user dataset.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f1f0fe',
                            100: '#e4e1fd',
                            500: '#667eea',
                            600: '#5a6fd8',
                            700: '#4c5bc4',
                            800: '#764ba2',
                        },
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'sans-serif'],
                    },
                    borderRadius: {
                        xl: '0.75rem',
                        '2xl': '1rem',
                    },
                },
            },
        };
    </script>
    <style>
        html { scroll-behavior: smooth; }
        .brand-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .brand-gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 bg-white">

    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
        <nav class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="#top" class="flex items-center gap-2 font-bold text-lg text-gray-900">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl brand-gradient text-white text-lg">🗂️</span>
                User Data Console
            </a>

            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="#features" class="hover:text-brand-500 transition">Tools</a>
                <a href="#highlight" class="hover:text-brand-500 transition">How it works</a>
                <a href="#footer" class="hover:text-brand-500 transition">About</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="/search" class="hidden sm:inline-block px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 hover:border-brand-500 hover:text-brand-600 transition">
                    Open Search
                </a>
                <button id="menuToggle" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 text-gray-600" aria-label="Toggle menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Mobile menu -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-gray-100 px-6 py-4 space-y-3 bg-white">
            <a href="#features" class="block text-sm font-medium text-gray-700">Tools</a>
            <a href="#highlight" class="block text-sm font-medium text-gray-700">How it works</a>
            <a href="#footer" class="block text-sm font-medium text-gray-700">About</a>
            <a href="/search" class="block text-sm font-semibold text-brand-600">Open Search →</a>
        </div>
    </header>

    <!-- Hero -->
    <section id="top" class="relative overflow-hidden brand-gradient text-white">
        <div class="max-w-6xl mx-auto px-6 py-24 lg:py-32 text-center relative z-10">
            <span class="inline-block px-4 py-1.5 rounded-full bg-white/15 border border-white/20 text-sm font-medium mb-6">
                Internal data platform for the ws_user dataset
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
                One console for every<br class="hidden sm:block"> user-data operation
            </h1>
            <p class="mt-6 text-lg sm:text-xl text-white/85 max-w-2xl mx-auto">
                Search across users, monitor data quality, detect duplicate accounts, and check system
                health — all backed by the same <code class="px-1.5 py-0.5 rounded bg-black/20 text-sm">ws_user</code> dataset,
                in one place.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/search" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white text-brand-700 font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition">
                    Start Searching Users
                </a>
                <a href="/health/dashboard" class="w-full sm:w-auto px-7 py-3.5 rounded-xl border border-white/40 text-white font-semibold hover:bg-white/10 transition">
                    View System Health
                </a>
            </div>
        </div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
    </section>

    <!-- Feature / View Sections -->
    <section id="features" class="max-w-6xl mx-auto px-6 py-20 lg:py-28">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-sm font-semibold text-brand-600 uppercase tracking-wider">Tools</span>
            <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-gray-900">Four tools, one dataset</h2>
            <p class="mt-4 text-gray-600">
                Each tool below is a focused, self-contained application. Open any of them directly —
                no setup, no extra steps.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Search -->
            <a href="/search" class="group block bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition">
                <div class="w-14 h-14 rounded-xl brand-gradient flex items-center justify-center text-2xl mb-6">🔎</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">User Search</h3>
                <p class="text-gray-600 leading-relaxed">
                    Search across users by email, phone, user ID, or name, with type-specific lookups,
                    pagination, and masked phone numbers in the results.
                </p>
                <span class="inline-flex items-center gap-1 mt-5 text-sm font-semibold text-brand-600">
                    Open Search
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </span>
            </a>

            <!-- Quality -->
            <a href="/quality" class="group block bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition">
                <div class="w-14 h-14 rounded-xl brand-gradient flex items-center justify-center text-2xl mb-6">📊</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Data Quality Dashboard</h3>
                <p class="text-gray-600 leading-relaxed">
                    Real-time analysis of the <code class="text-sm">ws_user</code> table: missing emails/phones,
                    invalid formats, duplicate values, impossible birth dates, and detected data issues.
                </p>
                <span class="inline-flex items-center gap-1 mt-5 text-sm font-semibold text-brand-600">
                    Open Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </span>
            </a>

            <!-- Duplicates -->
            <a href="/duplicates" class="group block bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition">
                <div class="w-14 h-14 rounded-xl brand-gradient flex items-center justify-center text-2xl mb-6">🔗</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Duplicate Detection</h3>
                <p class="text-gray-600 leading-relaxed">
                    Find potential duplicate accounts using email, phone, name similarity, IP address,
                    or combined detection — each match scored with a confidence level.
                </p>
                <span class="inline-flex items-center gap-1 mt-5 text-sm font-semibold text-brand-600">
                    Open Detector
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </span>
            </a>

            <!-- Health -->
            <a href="/health/dashboard" class="group block bg-white border border-gray-100 rounded-2xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition">
                <div class="w-14 h-14 rounded-xl brand-gradient flex items-center justify-center text-2xl mb-6">💓</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Health Check Dashboard</h3>
                <p class="text-gray-600 leading-relaxed">
                    Live system status monitoring: database connectivity, total record counts across all
                    tables, and a rolling history chart, auto-refreshed every 5 seconds.
                </p>
                <span class="inline-flex items-center gap-1 mt-5 text-sm font-semibold text-brand-600">
                    Open Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </span>
            </a>
        </div>
    </section>

    <!-- Highlight main feature -->
    <section id="highlight" class="bg-gray-50 border-y border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-20 lg:py-28 grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
            <div>
                <span class="text-sm font-semibold text-brand-600 uppercase tracking-wider">Under the hood</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight">
                    A single query, four tables, zero row multiplication
                </h2>
                <p class="mt-5 text-gray-600 leading-relaxed">
                    Every profile lookup joins <code class="text-sm">ws_user</code> with orders, transactions,
                    and activity logs. Instead of a naive join that multiplies rows across tables, the
                    profile API pre-aggregates each related table with <code class="text-sm">WITH</code>
                    (CTE) clauses before joining — so counts and sums stay correct no matter how many
                    orders or transactions a user has.
                </p>

                <ul class="mt-8 space-y-4">
                    <li class="flex gap-3">
                        <span class="mt-1 w-2 h-2 rounded-full brand-gradient shrink-0"></span>
                        <span class="text-gray-700"><strong class="text-gray-900">Order &amp; transaction summaries</strong> — count and total amount per user, pre-aggregated before the join.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 w-2 h-2 rounded-full brand-gradient shrink-0"></span>
                        <span class="text-gray-700"><strong class="text-gray-900">Activity tracking</strong> — activity count and last-seen timestamp per user.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 w-2 h-2 rounded-full brand-gradient shrink-0"></span>
                        <span class="text-gray-700"><strong class="text-gray-900">Confidence-scored duplicates</strong> — every match from the Duplicate Detector carries a similarity score and a high/medium/low badge.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 w-2 h-2 rounded-full brand-gradient shrink-0"></span>
                        <span class="text-gray-700"><strong class="text-gray-900">Set-based quality checks</strong> — the Quality Dashboard computes missing/invalid/duplicate rates directly in PostgreSQL, with results cached for 10 seconds.</span>
                    </li>
                </ul>
            </div>

            <div class="bg-gray-900 rounded-2xl shadow-xl p-6 sm:p-8 text-sm font-mono text-gray-200 overflow-x-auto">
                <div class="flex items-center gap-1.5 mb-4">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="ml-3 text-gray-400 text-xs">GET /api/user-profile/&#123;user_id&#125;</span>
                </div>
                <pre class="whitespace-pre-wrap leading-relaxed"><span class="text-purple-400">WITH</span> order_summary <span class="text-purple-400">AS</span> (
  <span class="text-purple-400">SELECT</span> user_id, <span class="text-purple-400">COUNT</span>(*) order_count
  <span class="text-purple-400">FROM</span> user_orders <span class="text-purple-400">WHERE</span> user_id = ?
),
transaction_summary <span class="text-purple-400">AS</span> ( ... ),
activity_summary <span class="text-purple-400">AS</span> ( ... )
<span class="text-purple-400">SELECT</span> u.*, os.order_count,
       ts.transaction_count, ac.last_activity
<span class="text-purple-400">FROM</span> ws_user u
<span class="text-purple-400">LEFT JOIN</span> order_summary os <span class="text-purple-400">ON</span> ...
<span class="text-purple-400">LEFT JOIN</span> transaction_summary ts <span class="text-purple-400">ON</span> ...
<span class="text-purple-400">LEFT JOIN</span> activity_summary ac <span class="text-purple-400">ON</span> ...</pre>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="max-w-6xl mx-auto px-6 py-20 lg:py-24">
        <div class="brand-gradient rounded-3xl px-8 py-16 sm:py-20 text-center text-white relative overflow-hidden">
            <h2 class="text-3xl sm:text-4xl font-extrabold">Ready to dig into the data?</h2>
            <p class="mt-4 text-white/85 max-w-xl mx-auto">
                Jump straight into search, or check the quality of the dataset first — every tool is one click away.
            </p>
            <div class="mt-9 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/search" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white text-brand-700 font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition">
                    Open User Search
                </a>
                <a href="/quality" class="w-full sm:w-auto px-7 py-3.5 rounded-xl border border-white/40 text-white font-semibold hover:bg-white/10 transition">
                    Open Quality Dashboard
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer" class="border-t border-gray-100 bg-white">
        <div class="max-w-6xl mx-auto px-6 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                <a href="#top" class="flex items-center gap-2 font-bold text-gray-900 mb-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg brand-gradient text-white text-sm">🗂️</span>
                    User Data Console
                </a>
                <p class="text-sm text-gray-500 leading-relaxed">
                    A single entry point to the search, data quality, duplicate detection, and health
                    monitoring tools built around the <code class="text-xs">ws_user</code> dataset.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Tools</h4>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li><a href="/search" class="hover:text-brand-600 transition">User Search</a></li>
                    <li><a href="/quality" class="hover:text-brand-600 transition">Data Quality Dashboard</a></li>
                    <li><a href="/duplicates" class="hover:text-brand-600 transition">Duplicate Detection</a></li>
                    <li><a href="/health/dashboard" class="hover:text-brand-600 transition">Health Check Dashboard</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">API</h4>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li><a href="/api/search" class="hover:text-brand-600 transition">GET /api/search</a></li>
                    <li><a href="/api/quality" class="hover:text-brand-600 transition">GET /api/quality</a></li>
                    <li><a href="/api/duplicates/find" class="hover:text-brand-600 transition">GET /api/duplicates/find</a></li>
                    <li><a href="/health" class="hover:text-brand-600 transition">GET /health</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Built with</h4>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">Laravel</span>
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">PostgreSQL</span>
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">Vue 3</span>
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">Chart.js</span>
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">Tailwind CSS</span>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-100">
            <div class="max-w-6xl mx-auto px-6 py-6 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} User Data Console. Internal tooling for the ws_user dataset.
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function () {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });
    </script>
</body>
</html>
