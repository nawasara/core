{{-- ui-lint-skip: glassmorphism login-style page with custom design language; not part of admin UI system --}}
<div
    class="min-h-screen flex items-center justify-center
    bg-gradient-to-br from-indigo-500 via-blue-400 to-cyan-300
    dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-500">

    <div
        class="bg-white/30 dark:bg-gray-800/50 backdrop-blur-xl rounded-3xl shadow-2xl 
        w-full max-w-md p-8 text-center border border-white/30 dark:border-gray-700 transition-all duration-500">

        {{-- Logo / Avatar --}}
        <div class="flex justify-center mb-6">
            <div class="bg-white/70 dark:bg-gray-700 rounded-full p-3 shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-600 dark:text-indigo-400"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 14l6.16-3.422A12.083 12.083 0 0118 20.944M12 14L5.84 10.578A12.083 12.083 0 006 20.944M12 14v8" />
                </svg>
            </div>
        </div>

        {{-- Title --}}
        <h1 class="text-2xl font-bold text-white dark:text-gray-100 mb-2">Pilih Peran Anda</h1>
        <p class="text-white/80 dark:text-gray-400 text-sm mb-8">
            Silakan pilih role untuk melanjutkan ke sistem
        </p>

        {{-- Role Selection --}}
        <div class="space-y-3">
            @foreach ($roles as $role)
                <button wire:click="switchRole('{{ $role->name }}')"
                    class="w-full px-5 py-4 bg-white/70 dark:bg-gray-700/80 text-gray-800 dark:text-gray-100 
                        rounded-2xl font-semibold hover:bg-white hover:dark:bg-gray-600 hover:shadow-lg 
                        transition duration-300 flex items-center justify-between group">
                    <span>{{ ucfirst($role->name) }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 
                        transition-transform group-hover:translate-x-1"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="mt-10 text-xs text-white/70 dark:text-gray-500">
            &copy; {{ date('Y') }} Nawasara. All rights reserved.
        </div>
    </div>
</div>
