@extends('nawasara-core::layouts.app')

@section('title', 'User Management')

@section('content')
    <div class="py-8">
        <div class="sm:flex sm:items-center mb-5">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Users</h1>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="" type="button"
                    class="block rounded bg-green-800 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm cursor-pointer hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Buat
                    Baru</a>
            </div>
        </div>
        {{-- <div class="bg-white shadow rounded-lg p-6"> --}}
        <div>
            <x-nawasara-core::table :headers="['Aksi', 'Tanggal', 'Jenis Kegiatan']" title="Data Kegiatan Gerakan Penanaman Pohon">
                <!-- Table Content -->
                <x-slot:table>
                    <tr>
                        <td class="m-5">1</td>
                        <td class="m-5">2</td>
                        <td class="m-5">3</td>
                    </tr>
                </x-slot:table>

                <!-- Footer for Pagination -->
                <x-slot:footer>
                    {{-- {{ $this->items->links() }} --}}
                </x-slot:footer>
            </x-nawasara-core::table>
        </div>

        {{-- </div> --}}
    </div>
@endsection
