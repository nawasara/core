<div>
    <div class="mt-6">
        <h3>Basic Toaster</h3>
        {{-- @include('nawasara-core::pages.blade-component.toaster') --}}
        <x-nawasara-core::button @click="Toast.success('Success message!')">
            Toaster Success
        </x-nawasara-core::button>

        <x-nawasara-core::button color="danger" @click="Toast.error('Error message!')">
            Toaster Error
        </x-nawasara-core::button>

        <x-nawasara-core::button color="danger" @click="Toast.warning('Error message!')">
            Toaster Warning
        </x-nawasara-core::button>

        <x-nawasara-core::button color="success" @click="Toast.info('Error message!')">
            Toaster Info
        </x-nawasara-core::button>

        <x-nawasara-core::button color="success" @click="Toast.warning('Loading message!', {showProgress: true})">
            Toaster show progress
        </x-nawasara-core::button>
    </div>
</div>
