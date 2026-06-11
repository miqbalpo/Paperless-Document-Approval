<x-app-layout>
    <div x-data="{ openModal: false, editForm: {}, showAlert: true }" class="p-8 bg-gray-100 min-h-screen">
        <div class="bg-white rounded-2xl shadow-md p-6 max-w-5xl mx-auto">

            <!-- Form Section -->
            <form action="{{ route('admin.master_data.departement.store') }}" method="post">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-6">
                    <!-- Department Code -->
                    <div>
                        <label for="department_code" class="block text-sm font-semibold text-gray-700 mb-1">
                            Department Code <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="department_code" 
                            name="department_code"
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            placeholder="FIN / HRD / IT" />
                    </div>

                    <!-- Department Name -->
                    <div class="md:col-span-2">
                        <label for="department_name" class="block text-sm font-semibold text-gray-700 mb-1">
                            Department Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="department_name" 
                            name="department_name"
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            placeholder="Finance / Human Resource Department / Creative" />
                    </div>

                    <!-- Button -->
                    <div class="flex justify-start md:justify-end">
                        <button
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-md transition duration-200 w-full md:w-auto justify-center">
                            <i class="bi bi-plus-lg mr-2"></i> Add Department
                        </button>
                    </div>
                </div>
            </form>

            <!-- Alert Section -->
            @php
                $alertClasses = [
                    'success' => 'bg-green-100 border border-green-400 text-green-700',
                    'error' => 'bg-red-100 border border-red-400 text-red-700',
                    'warning' => 'bg-yellow-100 border border-yellow-400 text-yellow-700',
                    'info' => 'bg-blue-100 border border-blue-400 text-blue-700',
                ];
            @endphp

            @foreach (['success', 'error', 'warning', 'info'] as $msg)
                @if (session($msg))
                    <div 
                        x-show="showAlert"
                        x-init="setTimeout(() => showAlert = false, 3000)"
                        x-transition
                        class="{{ $alertClasses[$msg] }} px-4 py-3 rounded relative mb-4" 
                        role="alert">
                        <strong class="font-bold mr-2">{{ ucfirst($msg) }}:</strong>
                        <span class="block sm:inline">{{ session($msg) }}</span>
                        <span @click="showAlert = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                            <svg class="fill-current h-6 w-6 text-{{ $msg === 'error' ? 'red' : ($msg === 'success' ? 'green' : 'gray') }}-700" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 5.652a.5.5 0 0 0-.707 0L10 9.293 6.36 5.652a.5.5 0 1 0-.707.707L9.293 10l-3.64 3.64a.5.5 0 0 0 .707.707L10 10.707l3.64 3.64a.5.5 0 0 0 .707-.707L10.707 10l3.64-3.64a.5.5 0 0 0 0-.708z"/></svg>
                        </span>
                    </div>
                @endif
            @endforeach

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100 text-gray-700 text-sm uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left border-b">Code</th>
                            <th class="px-4 py-2 text-left border-b">Department Name</th>
                            <th class="px-4 py-2 text-left border-b">Created At</th>
                            <th class="px-4 py-2 text-left border-b">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach ($dpt as $dept)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $dept['code'] }}</td>
                                <td class="px-4 py-2">{{ $dept['name'] }}</td>
                                <td class="px-4 py-2">{{ $dept['created_at'] }}</td>
                                <td class="px-4 py-2">
                                    <button
                                        @click="editForm = {{ json_encode($dept) }}; openModal = true"
                                        class="text-blue-600 hover:underline mr-3">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.master_data.departement.destroy', $dept['id']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Edit -->
        <div
            x-show="openModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            x-transition>
            <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Department</h2>

                <form x-bind:action="'{{ route('admin.master_data.departement.update', '') }}/' + editForm.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department Code *</label>
                        <input type="text" id="code" name="code" x-model="editForm.code" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department Name *</label>
                        <input type="text" id="name" name="name" x-model="editForm.name" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" @click="openModal = false" class="mr-3 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Simpan Perubahan</button>
                    </div>
                </form>

                <button @click="openModal = false" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-xl">
                    &times;
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</x-app-layout>
