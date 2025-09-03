<!-- ========== HEADER ========== -->
<header
    class="sticky top-4 inset-x-0 
  before:absolute before:inset-0 before:max-w-[66rem] before:mx-2 before:lg:mx-auto before:rounded-[26px] 
  before:border before:border-gray-200 dark:before:border-neutral-700 
  after:absolute after:inset-0 after:-z-[1] after:max-w-[66rem] after:mx-2 after:lg:mx-auto after:rounded-[26px] 
  after:bg-white dark:after:bg-neutral-900 
  flex flex-wrap md:justify-start md:flex-nowrap z-20 w-full">

    <nav
        class="relative max-w-[66rem] w-full md:flex md:items-center md:justify-between md:gap-3 ps-5 pe-2 mx-2 lg:mx-auto py-2">
        <!-- Logo w/ Collapse Button -->
        <div class="flex items-center justify-between">
            <a class="flex-none font-semibold text-xl text-black dark:text-white focus:outline-none focus:opacity-80"
                href="/" aria-label="Brand">
                <img src="{{ asset('vendor/nawasara-core/assets/images/logo.png') }}" class="h-10 mr-3"
                    alt="Flowbite Logo" />
            </a>

            <!-- Collapse Button -->
            <div class="md:hidden">
                <button type="button"
                    class="hs-collapse-toggle relative size-9 flex justify-center items-center text-sm font-semibold rounded-full border border-gray-200 dark:border-neutral-700 text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-neutral-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-neutral-700 disabled:opacity-50 disabled:pointer-events-none"
                    id="hs-header-classic-collapse" aria-expanded="false" aria-controls="hs-header-classic"
                    aria-label="Toggle navigation" data-hs-collapse="#hs-header-classic">
                    <svg class="hs-collapse-open:hidden size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" x2="21" y1="6" y2="6" />
                        <line x1="3" x2="21" y1="12" y2="12" />
                        <line x1="3" x2="21" y1="18" y2="18" />
                    </svg>
                    <svg class="hs-collapse-open:block shrink-0 hidden size-4" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                    <span class="sr-only">Toggle navigation</span>
                </button>
            </div>
            <!-- End Collapse Button -->
        </div>
        <!-- End Logo w/ Collapse Button -->

        <!-- Collapse -->
        <div id="hs-header-classic"
            class="hs-collapse hidden overflow-hidden transition-all duration-300 basis-full grow md:block"
            aria-labelledby="hs-header-classic-collapse">
            <div
                class="overflow-hidden overflow-y-auto max-h-[75vh] 
        [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full 
        [&::-webkit-scrollbar-track]:bg-gray-100 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 
        [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                <div class="py-2 md:py-0 flex flex-col md:flex-row md:items-center md:justify-end gap-0.5 md:gap-1">

                    @foreach (app('nawasara.menu') as $menu)
                        {{-- @can($menu['permission']) --}}
                        @if (!empty($menu['submenu']))
                            <div class="hs-dropdown relative inline-block">
                                <button type="button"
                                    class="p-2 flex items-center text-sm text-gray-800 dark:text-neutral-200 focus:outline-none focus:text-green-600 dark:focus:text-green-400 hs-dropdown-toggle"
                                    aria-haspopup="menu" aria-expanded="false">
                                    @if (!empty($menu['icon']))
                                        <i class="{{ $menu['icon'] }} mr-2"></i>
                                    @endif
                                    {{ $menu['label'] }}
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    class="hs-dropdown-menu hidden absolute z-10 bg-white shadow rounded mt-2 min-w-[160px]">
                                    @foreach ($menu['submenu'] as $submenu)
                                        {{-- @can($submenu['permission']) --}}
                                        <a href="{{ url($submenu['url']) }}" wire:navigate
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            @if (!empty($submenu['icon']))
                                                <i class="{{ $submenu['icon'] }} mr-2"></i>
                                            @endif
                                            {{ $submenu['label'] }}
                                        </a>
                                        {{-- @endcan --}}
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a class="p-2 flex items-center text-sm text-gray-800 dark:text-neutral-200 focus:outline-none focus:text-green-600 dark:focus:text-green-400"
                                href="{{ url($menu['url']) }}" aria-current="page">
                                @if (!empty($menu['icon']))
                                    <i class="{{ $menu['icon'] }} mr-2"></i>
                                @endif
                                {{ $menu['label'] }}
                            </a>
                        @endif
                        {{-- @endcan --}}
                    @endforeach

                    {{-- <div
                        class="hs-dropdown [--strategy:static] md:[--strategy:fixed] [--adaptive:none] [--is-collapse:true] md:[--is-collapse:false] ">
                        <button id="hs-header-classic-dropdown" type="button"
                            class="hs-dropdown-toggle w-full p-2 flex items-center text-sm text-gray-800 hover:text-gray-500 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500 {{ Request::is('budget-source*') || Request::is('seed-source*') || Request::is('seed*') || Request::is('activity-type*') ? 'text-green-600 font-bold' : 'text-gray-800' }}"
                            aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <svg class="shrink-0 size-4 me-3 md:me-2 block md:hidden" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 10 2.5-2.5L3 5" />
                                <path d="m3 19 2.5-2.5L3 14" />
                                <path d="M10 6h11" />
                                <path d="M10 12h11" />
                                <path d="M10 18h11" />
                            </svg>
                            Master
                            <svg class="hs-dropdown-open:-rotate-180 md:hs-dropdown-open:rotate-0 duration-300 shrink-0 size-4 ms-auto md:ms-1"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div class="hs-dropdown-menu transition-[opacity,margin] duration-[0.1ms] md:duration-[150ms] hs-dropdown-open:opacity-100 opacity-0 relative w-full md:w-52 hidden z-10 top-full ps-7 md:ps-0 md:bg-white md:rounded-lg md:shadow-md before:absolute before:-top-4 before:start-0 before:w-full before:h-5 md:after:hidden after:absolute after:top-1 after:start-[18px] after:w-0.5 after:h-[calc(100%-0.25rem)] after:bg-gray-100 dark:md:bg-neutral-800 dark:after:bg-neutral-700"
                            role="menu" aria-orientation="vertical" aria-labelledby="hs-header-classic-dropdown">
                            <div class="py-1 md:px-1 space-y-0.5">
                                @can('sumber.anggaran.view')
                                    <a class="py-2.5 px-2 flex items-center text-sm {{ Request::is('budget-source*') ? 'text-green-600 font-bold' : 'text-gray-800' }} hover:text-gray-500 hover:bg-gray-100 rounded-lg focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                        href="" wire:navigate.hover>
                                        Sumber Anggaran
                                    </a>
                                @endcan


                                @can('sumber.bibit.view')
                                    <a class="py-2.5 px-2 flex items-center text-sm {{ Request::is('seed-source*') ? 'text-green-600 font-bold' : 'text-gray-800' }} hover:text-gray-500 hover:bg-gray-100 rounded-lg focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                        href="" wire:navigate.hover>
                                        Sumber Bibit
                                    </a>
                                @endcan
                                @can('jenis.bibit.view')
                                    <a class="py-2.5 px-2 flex items-center text-sm {{ Request::is('seed') || Request::is('seed/*') ? 'text-green-600 font-bold' : 'text-gray-800' }} hover:text-gray-500 hover:bg-gray-100 rounded-lg focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                        href="" wire:navigate.hover>
                                        Jenis Bibit
                                    </a>
                                @endcan
                                @can('jenis.kegiatan.view')
                                    <a class="py-2.5 px-2 flex items-center text-sm {{ Request::is('activity-type*') ? 'text-green-600 font-bold' : 'text-gray-800' }} hover:text-gray-500 hover:bg-gray-100 rounded-lg focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                        href="" wire:navigate.hover>
                                        Jenis Kegiatan
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div> --}}

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
                    <!-- Button Group -->
                    <div class="hs-dropdown [--strategy:static] md:[--strategy:fixed]">
                        <div class="hs-dropdown-toggle flex items-center gap-x-1.5">
                            <a class="p-2 w-full flex items-center font-bold text-sm text-gray-900 dark:text-white"
                                href="#">
                                <svg class="shrink-0 size-4 me-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                John Doe
                            </a>
                        </div>
                        <div class="hs-dropdown-menu hidden w-full md:w-52">
                            <div class="py-1 md:px-1 space-y-0.5">
                                <a href="/profile"
                                    class="py-2.5 px-2 flex items-center text-sm text-gray-800 dark:text-neutral-200">
                                    Profil Saya
                                </a>
                                <a href="/logout"
                                    class="py-2.5 px-2 flex items-center text-sm text-gray-800 dark:text-neutral-200">
                                    Keluar
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- End Button Group -->

                </div>
            </div>
        </div>
        <!-- End Collapse -->
    </nav>
    <script>
        window.HSStaticMethods.autoInit();
        // initFlowbite()
    </script>
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
