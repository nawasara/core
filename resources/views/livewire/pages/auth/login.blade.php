<div>
    <div class="min-h-dvh flex items-center justify-center py-12 px-4 sm:px-6 lgap:px-8">
        <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-900 p-8 rounded-xl shadow-lg">
            <div class="text-center mb-6">
                <img src="{{ asset('vendor/nawasara-core/assets/images/logo.png') }}" class="h-12 mx-auto mb-2"
                    alt="Logo Nandur Panguripan" />
                <h2 class="mt-2 text-2xl font-bold text-green-800 dark:text-green-300">Login Form</h2>
                <p class="mt-1 text-sm text-green-700 dark:text-green-200">It all started here.</p>
            </div>

            @if ($errors->any())
                <!-- Validation Errors -->
                <div class="mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200">
                    <ul class="list-disc list-inside text-sm">
                        <!-- Error messages will appear here -->
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Status Message -->
            <div
                class="mb-4 p-4 rounded-lg bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 text-sm hidden">
                <!-- Status message will appear here -->
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <!-- Recaptcha will be added here -->
                <div class="text-red-600 text-xs hidden"></div>

                <div>
                    <x-nawasara-core::form.label for="email" value="Email / Username" />
                    <x-nawasara-core::form.input id="email" name="email" type="text" required autofocus
                        autocomplete="username" :value="old('email') ?? 'root'" />
                </div>

                <div>
                    <x-nawasara-core::form.label for="password" value="Password" />
                    <x-nawasara-core::form.input id="password" name="password" type="password" value="password"
                        required autocomplete="current-password" usePasswordField />
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-nawasara-core::form.checkbox id="remember_me" name="remember" label="Remember me" />
                    </div>
                    {{-- <a class="text-sm text-green-700 dark:text-green-300 hover:underline" href="">
                        Forget password?
                    </a> --}}
                </div>

                <div>
                    <x-nawasara-core::button type="submit" rounded="md"
                        class="w-full py-2.5 px-4 bg-green-600 dark:bg-green-700 text-white font-medium hover:bg-green-700 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                        Log In
                    </x-nawasara-core::button>
                </div>

                <div class="flex items-center justify-center my-4">
                    <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
                    <span class="mx-2 text-gray-400 dark:text-gray-500 text-xs">atau</span>
                    <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
                </div>

                <div>
                    <x-nawasara-core::button aria-label="Tambah" color="neutral" full rounded="md">
                        {{-- contoh pakai Heroicons Solid "Plus" --}}
                        <x-slot:icon>
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
                        </x-slot:icon>
                        Login with SSO
                    </x-nawasara-core::button>
                </div>
            </form>
        </div>
    </div>
</div>
