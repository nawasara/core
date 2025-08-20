@extends('nawasara-core::layouts.app')

@section('content')
    <div class="p-6">
        <div class="bg-white shadow rounded-xl p-6">
            <h1 class="text-2xl font-bold text-gray-800">Selamat Datang di Nawasara Core</h1>
            <p class="mt-2 text-gray-600">
                Ini adalah halaman default dashboard dari <span class="font-semibold">nawasara-core</span>.
            </p>
            <p class="mt-2 text-gray-500 text-sm">
                Anda bisa mengganti halaman ini dengan menonaktifkan `use_default_home`
                di <code>config/nawasara-core.php</code>.
            </p>
        </div>
    </div>
@endsection
