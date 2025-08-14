@extends('nawasara-core::layouts.app')

@section('title', 'User Management')

@section('content')
    <div class="py-8">
        <h1 class="text-2xl font-bold mb-6 text-red-500">User Management</h1>
        <div class="bg-white shadow rounded-lg p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $user->name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $user->email }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $user->role ?? '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <a href="#" class="text-blue-600 hover:underline">Edit</a>
                                <a href="#" class="text-red-600 hover:underline ml-2">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
