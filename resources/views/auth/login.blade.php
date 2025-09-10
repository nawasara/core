<x-nawasara-core::layouts.guest>
    <div class="min-h-dvh flex items-center justify-center py-12 px-4 sm:px-6 lgap:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
            <div class="text-center mb-6">
                <img src="{{ asset('vendor/nawasara-core/assets/images/logo.png') }}" class="h-12 mx-auto mb-2"
                    alt="Logo Nandur Panguripan" />
                <h2 class="mt-2 text-2xl font-bold text-green-800">Masuk Akun</h2>
                <p class="mt-1 text-sm text-green-700">Silakan login untuk melanjutkan.</p>
            </div>

            @if ($errors->any())
                <!-- Validation Errors -->
                <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-700">
                    <ul class="list-disc list-inside text-sm">
                        <!-- Error messages will appear here -->
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Status Message -->
            <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-700 text-sm hidden">
                <!-- Status message will appear here -->
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <!-- Recaptcha will be added here -->
                <div class="text-red-600 text-xs hidden"></div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email atau
                        Username</label>
                    <input id="email" name="email" type="text" required autofocus autocomplete="username"
                        class="w-full rounded-lg border border-gray-300 p-2.5 shadow-xs focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition"
                        value="{{ old('email') }}" />
                    <div class="text-red-600 text-xs mt-1 hidden"></div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 p-2.5 shadow-xs focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition" />
                    <div class="text-red-600 text-xs mt-1 hidden"></div>
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center gap-2">
                        <input id="remember_me" name="remember" type="checkbox"
                            class="rounded border-gray-300 text-green-600 shadow-xs focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition" />
                        <span class="text-sm text-gray-600">Ingat saya</span>
                    </label>
                    <a class="text-sm text-green-700 hover:underline" href="">
                        Lupa password?
                    </a>
                </div>

                <div>
                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-lg bg-green-600 text-white font-medium shadow-xs hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                        Masuk
                    </button>
                </div>

                <div class="flex items-center justify-center my-4">
                    <span class="h-px flex-1 bg-gray-200"></span>
                    <span class="mx-2 text-gray-400 text-xs">atau</span>
                    <span class="h-px flex-1 bg-gray-200"></span>
                </div>

                <div>
                    <a href="{{ url('/auth/google') }}"
                        class="w-full inline-flex items-center justify-center gap-x-2 rounded-lg bg-white border border-gray-200 shadow-xs px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                        <svg class="w-5 h-5" viewBox="0 0 488 512" fill="currentColor">
                            <path
                                d="M488 261.8c0-17.8-1.5-35-4.3-51.6H249v97.9h135.8c-5.9 32-23.6 59-50.2 77.1v64h81c47.3-43.5 72.4-107.5 72.4-187.4z"
                                fill="#4285F4" />
                            <path
                                d="M249 492c66.6 0 122.5-22.1 163.4-59.9l-81-64c-22.5 15.2-51.2 24-82.4 24-63.3 0-117.1-42.8-136.4-100.3h-83.3v62.9C76.9 434.1 155.7 492 249 492z"
                                fill="#34A853" />
                            <path
                                d="M112.6 291.8c-4.7-14.1-7.4-29.2-7.4-44.8s2.7-30.7 7.4-44.8v-63H29.3C10.4 172.3 0 208.6 0 247s10.4 74.7 29.3 107.8l83.3-63z"
                                fill="#FBBC05" />
                            <path
                                d="M249 97.9c35.9 0 68.1 12.4 93.4 36.6l70.1-70.1C371.5 29.6 315.6 0 249 0 155.7 0 76.9 57.9 29.3 140.2l83.3 63c19.2-57.5 73-100.3 136.4-100.3z"
                                fill="#EA4335" />
                        </svg>
                        <span>Login dengan Google</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Simulasi error dan status messages untuk demo
        document.addEventListener('DOMContentLoaded', function() {
            // Simulasi error message
            // const hasError = Math.random() > 0.5;
            // if (hasError) {
            //     const errorDiv = document.querySelector('.bg-red-50');
            //     errorDiv.classList.remove('hidden');
            //     errorDiv.querySelector('ul').innerHTML = '<li>Email atau password salah</li>';
            // }

            // Simulasi status message
            // const hasStatus = Math.random() > 0.7;
            // if (hasStatus) {
            //     const statusDiv = document.querySelector('.bg-green-100');
            //     statusDiv.classList.remove('hidden');
            //     statusDiv.textContent = 'Login berhasil! Mengalihkan...';
            // }
        });
    </script>
</x-nawasara-core::layouts.guest>
