@extends('nawasara-core::layouts.app')

@section('title', 'User Management')

@section('content')
    <div class="py-8">
        <h1 class="text-2xl font-bold mb-6 text-green-500">User Management</h1>
        <div class="bg-white shadow rounded-lg p-6">
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

        </div>
    </div>
@endsection
