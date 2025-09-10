<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-green-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo-nandur-panguripan.png') }}" class="h-12 mx-auto mb-2" alt="Logo" />
                <h2 class="mt-2 text-2xl font-bold text-green-800">Daftar Akun Baru</h2>
                <p class="mt-1 text-sm text-green-700">Silakan isi data di bawah untuk membuat akun.</p>
            </div>

            {{-- <x-validation-errors class="mb-4" /> --}}

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf
                {!! RecaptchaV3::field('register') !!}
                @error('g-recaptcha-response')
                    <span class="text-red-600 text-xs">{{ $message }}</span>
                @enderror
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required autofocus autocomplete="name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        value="{{ old('name') }}" />
                    @error('name')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        value="{{ old('email') }}" />
                    @error('email')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input id="username" name="username" type="text" required maxlength="16" autocomplete="username"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        value="{{ old('username') }}" />
                    @error('username')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        value="{{ old('password') }}" />
                    @error('password')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi
                        Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        value="{{ old('password_confirmation') }}" />
                </div>

                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                    <div class="mt-4">
                        <x-label for="terms">
                            <div class="flex items-center">
                                <x-checkbox name="terms" id="terms" required />

                                <div class="ms-2">
                                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' =>
                                            '<a target="_blank" href="' .
                                            route('terms.show') .
                                            '" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">' .
                                            __('Terms of Service') .
                                            '</a>',
                                        'privacy_policy' =>
                                            '<a target="_blank" href="' .
                                            route('policy.show') .
                                            '" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">' .
                                            __('Privacy Policy') .
                                            '</a>',
                                    ]) !!}
                                </div>
                            </div>
                        </x-label>
                    </div>
                @endif

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium bg-green-600 text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Daftar
                    </button>
                </div>
                <div class="text-center text-sm text-gray-600">
                    Sudah punya akun? <a href="{{ route('login') }}" class="text-green-700 hover:underline">Login</a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
