<div>
    <form wire:submit.prevent="store" class="space-y-6" x-data>
        <x-nawasara-core::form.input id="name" name="name" label="Name" placeholder="Your name" required autofocus
            wire:model.defer="form.name" />
        <x-nawasara-core::form.input id="email" name="email" type="email" label="Email" placeholder="Your email"
            required wire:model.defer="form.email" />
        <x-nawasara-core::form.input id="password" name="password" type="password" label="Password"
            usePasswordField="true" useGenPassword="true" placeholder="Your password"
            wire:model.defer="form.password" />
        <x-nawasara-core::form.select id="role" name="role" label="Role" wire:model.defer="form.role">
            <option value="">-- Pilih Role --</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ $role->name }}</option>
            @endforeach
        </x-nawasara-core::form.select>
        <div class="flex justify-end gap-2">
            <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-200 rounded-md">Batal</button>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md">Simpan</button>
        </div>
    </form>
</div>
