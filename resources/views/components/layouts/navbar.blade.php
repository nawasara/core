<!-- ========== HEADER ========== -->
<header
    class="sticky top-0 z-30 w-full bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-800 h-16 flex items-center">
    <div class="flex-1 flex items-center justify-between px-6">
        <!-- Search -->
        <form class="w-full max-w-xs">
            <label for="navbar-search" class="sr-only">Search</label>
            <div class="relative">
                <input id="navbar-search" type="search" name="search"
                    class="block w-full pl-10 pr-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="Search...">
                <span class="absolute left-3 top-2.5 text-gray-400 dark:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </span>
            </div>
        </form>
        <!-- End Search -->
        <div class="flex items-center gap-2">
            <!-- Dark Mode Toggle -->
            <button id="theme-toggle" type="button"
                class="p-2 rounded-full border border-gray-200 dark:border-neutral-700 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-neutral-700 focus:outline-none transition">
                <!-- Icon Sun -->
                <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10 3.5a1 1 0 011 1V6a1 1 0 11-2 0V4.5a1 1 0 011-1zm0 9a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM3.5 10a1 1 0 011-1H6a1 1 0 110 2H4.5a1 1 0 01-1-1zm9 6.5a1 1 0 01-1-1V14a1 1 0 112 0v1.5a1 1 0 01-1 1zM15.5 10a1 1 0 011-1H18a1 1 0 110 2h-1.5a1 1 0 01-1-1zM5.64 5.64a1 1 0 011.41 0L8.05 6.64a1 1 0 11-1.41 1.41L5.64 7.05a1 1 0 010-1.41zm8.72 8.72a1 1 0 011.41 0l1 1a1 1 0 11-1.41 1.41l-1-1a1 1 0 010-1.41zM5.64 14.36a1 1 0 000 1.41l1 1a1 1 0 101.41-1.41l-1-1a1 1 0 00-1.41 0zm8.72-8.72a1 1 0 000 1.41l1 1a1 1 0 101.41-1.41l-1-1a1 1 0 00-1.41 0z">
                    </path>
                </svg>
                <!-- Icon Moon -->
                <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.293 13.293a8 8 0 01-11.586-11.586 8 8 0 1011.586 11.586z">
                    </path>
                </svg>
            </button>
            <!-- End Dark Mode Toggle -->
            <!-- Account Profile -->
            @if (Auth::check())
                <div class="relative group">
                    <button type="button"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-semibold focus:outline-none group-hover:bg-gray-200 dark:group-hover:bg-gray-700">
                        @php
                            $user = auth()->user();
                            $avatar = $user->avatar ?? null;
                            $initials = collect(explode(' ', $user->name))
                                ->map(fn($w) => strtoupper($w[0] ?? ''))
                                ->join('');
                        @endphp
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="Avatar"
                                class="w-8 h-8 rounded-full object-cover bg-gray-300 dark:bg-gray-700">
                        @else
                            <span
                                class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200 font-bold text-base">{{ $initials }}</span>
                        @endif
                        <span class="hidden md:inline">{{ $user->name }}</span>
                    </button>
                    <div
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-lg py-1 z-20 hidden group-hover:block">
                        <a href="/profile"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">Profil
                            Saya</a>
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <a href="{{ route('logout') }}" @click.prevent="$root.submit();"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">Keluar</a>
                        </form>
                    </div>
                </div>
            @endif
            <!-- End Account Profile -->
        </div>
    </div>
    <script>
        // Fungsi update icon sesuai mode
        function updateThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('theme-toggle-light-icon').classList.toggle('hidden', isDark);
            document.getElementById('theme-toggle-dark-icon').classList.toggle('hidden', !isDark);
        }
        // Inisialisasi theme dan icon
        function initTheme() {
            if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia(
                    '(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            updateThemeIcons();
        }
        initTheme();
        // Toggle click handler
        document.getElementById('theme-toggle').addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcons();
        });
    </script>
</header>
<!-- ========== END HEADER ========== -->
