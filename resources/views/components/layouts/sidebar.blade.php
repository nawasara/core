<aside id="sidebar"
    class="relative w-full md:w-96 h-screen bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col">


    <!-- Navigation -->
    <nav class="flex-1 py-6 px-2 overflow-y-auto">
        @php $currentUrl = url()->current(); @endphp
        <ul class="space-y-4 px-3">
            @foreach (app('nawasara.menu') as $menu)
                @if (!empty($menu['submenu']))
                    <!-- Section heading -->
                    <li>
                        <div class="mb-1 text-xs font-bold text-gray-850 uppercase tracking-wider dark:text-white">
                            {{ $menu['label'] }}
                        </div>
                        <ul class="space-y-1 border-l border-gray-200 dark:border-gray-700">
                            @foreach ($menu['submenu'] as $submenu)
                                @php $isActive = $currentUrl === url($submenu['url']); @endphp
                                <li>
                                    <a href="{{ url($submenu['url']) }}" @class([
                                        'flex items-center gap-2 px-4 py-1.5 text-sm rounded-none border-l-3 transition',
                                        'border-transparent text-gray-700 dark:text-gray-300 hover:border-blue-600 hover:text-blue-700 dark:hover:text-gray-100' => !$isActive,
                                        'border-blue-600 text-blue-700 dark:text-blue-400 font-semibold' => $isActive,
                                    ])>
                                        @if (!empty($submenu['icon']))
                                            <i class="{{ $submenu['icon'] }} text-base"></i>
                                        @endif
                                        <span>{{ $submenu['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    @php $isActive = $currentUrl === url($menu['url']); @endphp
                    <li>
                        <a href="{{ url($menu['url']) }}" @class([
                            'flex items-center gap-2 px-4 py-1.5 text-sm font-medium rounded-none border-l-3 transition',
                            'border-transparent text-gray-700 dark:text-gray-300 hover:border-blue-600 hover:text-blue-700 dark:hover:text-gray-100' => !$isActive,
                            'border-blue-600 text-blue-700 dark:text-blue-400 font-semibold' => $isActive,
                        ])>
                            @if (!empty($menu['icon']))
                                <i class="{{ $menu['icon'] }} text-base"></i>
                            @endif
                            <span>{{ $menu['label'] }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
</aside>
