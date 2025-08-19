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

                    <a class="p-2 flex items-center text-sm text-gray-800 dark:text-neutral-200 focus:outline-none focus:text-green-600 dark:focus:text-green-400"
                        href="/dashboard" aria-current="page">
                        <svg class="shrink-0 size-4 me-3 md:me-2 block md:hidden" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                            <path
                                d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                        Beranda
                    </a>

                    <a class="p-2 flex items-center text-sm text-gray-800 dark:text-neutral-200 focus:outline-none focus:text-green-600 dark:focus:text-green-400"
                        href="/planting-activity">
                        Komponen
                    </a>

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
</header>
<!-- ========== END HEADER ========== -->
