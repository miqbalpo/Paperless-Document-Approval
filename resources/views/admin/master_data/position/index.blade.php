<x-app-layout>
    <div x-data="{ openModal: false, editForm: {}, deleteForm: {}, showAlert: true, deleteModal: false }" class="p-8 bg-gray-100 min-h-screen">
        <div class="bg-white rounded-2xl shadow-md p-6 max-w-5xl mx-auto">
            <!-- Form -->
            <form action="{{ route('admin.master_data.position.store') }}" method="POST">
                @method('POST')
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-6">

                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Position Code <span class="text-red-500">*</span>
                        </label>
                        <input id="position_code" name="position_code" type="text"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="POST-001"
                        >
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Position Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="position_name" name="position_name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Manager">
                    </div>

                    <!-- Button -->
                    <div class="flex justify-start md:justify-end">
                        <button type="submit"
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg transition w-full md:w-auto justify-center">
                            Add Position
                            <i class="bi bi-plus-lg ml-2"></i>
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
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100 text-gray-700 text-sm">
                        <tr>
                            <th class="px-4 py-3 text-center border-b">Code</th>
                            <th class="px-4 py-3 text-center border-b">Name</th>
                            <th class="px-4 py-3 text-center border-b">Action</th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700">
                        @foreach ($positions as $doc)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 text-center font-semibold">{{ $doc['code'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $doc['name'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button 
                                    @click="editForm = {{ json_encode($doc) }};  openModal = true" class="text-blue-600 hover:underline mr-3">
                                        Edit
                                    </button>
                                    <button @click="deleteForm = {{ json_encode($doc) }}; deleteModal = true" class="text-red-500 hover:underline">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="openModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            x-transition>
            <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Position</h2>

                <form x-bind:action="'{{ route('admin.master_data.position.update', '') }}/' + editForm.id"  method="POST">
                    @method('PUT')
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position Code *</label>
                        <input type="text"
                            id="edit_position_code" name="edit_position_code" x-model="editForm.code"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter Position Code">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position Name *</label>
                        <input type="text"
                            id="edit_position_name" name="edit_position_name" x-model="editForm.name"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter Position Name">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" @click="openModal = false"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md">
                            Save
                        </button>
                    </div>
                </form>

                <button @click="openModal = false"
                    class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-xl">
                    &times;
                </button>
            </div>
        </div>

        <div x-show="deleteModal" x-transition.opacity
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg text-center" x-transition
                @click.outside="deleteModal = false">
                <div class="flex justify-center mb-4">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle-fill text-3xl"></i>
                    </div>
                </div>

                <p class="text-gray-800 text-lg">
                    Are you sure to delete this position?
                </p>
                <input type="text" disabled x-model="deleteForm.name">
                <p class="text-gray-500 text-sm mt-1">
                    This action cannot be undone.
                </p>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="deleteModal = false"
                        class="px-4 py-2 rounded-sm border border-gray-300 text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                        Cancel
                    </button>

                    <form x-bind:action="'{{ route('admin.master_data.position.destroy', '') }}/' + deleteForm.id"  method="POST">
                        @method('DELETE')
                        @csrf
                        <button type="submit" @click="handleDelete()"
                        class="px-4 py-2 rounded-sm bg-red-600 text-white hover:bg-red-700 flex items-center gap-2">
                        Delete
                    </button>
                </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</x-app-layout>

