<x-nawasara-core::layouts.app>
    <x-slot:title>
        Form Component - Nawasara Core
    </x-slot:title>

    <x-nawasara-core::layouts.container>

        <x-slot name="title">
            <x-nawasara-core::layouts.page-title>Form - Nawasara Core</x-nawasara-core::layouts.page-title>
        </x-slot>
        <x-nawasara-core::layouts.card>
            <form class="space-y-6" method="POST" action="#">
                @csrf
                <div>
                    <x-nawasara-core::form.input id="name" name="name" label="Name" placeholder="Enter your name"
                        required autofocus />
                </div>
                <div>
                    <x-nawasara-core::form.label for="email" label="Email" />
                    <x-nawasara-core::form.input id="email" name="email" type="email" label=""
                        placeholder="Enter your email" required />
                </div>
                <div>
                    <x-nawasara-core::form.label for="password" label="Password" />
                    <x-nawasara-core::form.input id="password" name="password" usePasswordField="true"
                        useGenPassword="true" label="" placeholder="Enter your password" required />
                </div>
                <div>
                    <x-nawasara-core::form.label for="gender" label="Gender" />
                    <div class="flex gap-4">
                        <x-nawasara-core::form.radio id="male" name="gender" value="male" label="Male" />
                        <x-nawasara-core::form.radio id="female" name="gender" value="female" label="Female" />
                    </div>
                </div>
                <div>
                    <x-nawasara-core::form.label for="role" label="Role" />
                    <x-nawasara-core::form.select id="role" name="role">
                        <option value="">Select role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="guest">Guest</option>
                    </x-nawasara-core::form.select>
                </div>

                <div>
                    <x-nawasara-core::form.label for="dropdown" label="Role (Dropdown)" />
                    <x-nawasara-core::form.select-dropdown name="dropdown" defaultValue="user">
                        <button type="button" class="hs-dropdown-item w-full text-left" value="admin">Admin</button>
                        <button type="button" class="hs-dropdown-item w-full text-left" value="user">User</button>
                        <button type="button" class="hs-dropdown-item w-full text-left" value="guest">Guest</button>
                    </x-nawasara-core::form.select-dropdown>
                </div>
                <div>
                    <x-nawasara-core::form.label for="bio" label="Bio" />
                    <x-nawasara-core::form.textarea id="bio" name="bio" label="Biodata"
                        placeholder="Tell us about yourself..." rows="3" />
                </div>
                <div class="flex items-center">
                    <x-nawasara-core::form.checkbox id="agree" name="agree" label="label" />
                    <label for="agree" class="ml-2 text-sm text-gray-600">I agree to the terms and conditions</label>
                </div>
                <div>
                    <button type="submit"
                        class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Submit</button>
                </div>
            </form>
        </x-nawasara-core::layouts.card>
    </x-nawasara-core::layouts.container>

</x-nawasara-core::layouts.app>
