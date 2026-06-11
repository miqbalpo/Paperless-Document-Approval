<x-app-layout>
    <div x-data="{ openModal: false, editForm : {} , deleteForm : {}, deleteModal:false, showAlert:true    }" class="p-8 bg-gray-100 min-h-screen">
        <div class="bg-white rounded-2xl shadow-md p-6 max-w-5xl mx-auto">

            <form action="{{ route('admin.master_data.user.store') }}" method="POST">
                @method("POST")
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input id="name" name="name" type="text"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Admin">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input id="email" name="email" type="email"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Admin@gmail.com">
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>

                        <input type="password" id="password" name="password"
                            class="password-field w-full border border-gray-300 rounded-lg px-3 py-2 pr-10"
                            placeholder="********">

                        <button type="button"
                            class="toggle-password absolute inset-y-0 right-3 mt-5 flex items-center text-gray-500">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select id="role_id" name="role_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ $role->name == "approver" ? 'selected' : '' }}>
                                    {{ ucfirst($role->name)   }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-start-4 flex justify-end">
                        <button
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg">
                            Add User
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
                    <div x-show="showAlert" x-init="setTimeout(() => showAlert = false, 3000)" x-transition
                        class="{{ $alertClasses[$msg] }} px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold mr-2">{{ ucfirst($msg) }}:</strong>
                        <span class="block sm:inline">{{ session($msg) }}</span>
                        <span @click="showAlert = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                            <svg class="fill-current h-6 w-6 text-{{ $msg === 'error' ? 'red' : ($msg === 'success' ? 'green' : 'gray') }}-700"
                                role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path
                                    d="M14.348 5.652a.5.5 0 0 0-.707 0L10 9.293 6.36 5.652a.5.5 0 1 0-.707.707L9.293 10l-3.64 3.64a.5.5 0 0 0 .707.707L10 10.707l3.64 3.64a.5.5 0 0 0 .707-.707L10.707 10l3.64-3.64a.5.5 0 0 0 0-.708z" />
                            </svg>
                        </span>
                    </div>
                @endif
            @endforeach
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100 text-gray-700 text-sm">
                        <tr>
                            <th class="px-4 py-3 text-center border-b">Username</th>
                            <th class="px-4 py-3 text-center border-b">Email</th>
                            <th class="px-4 py-3 text-center border-b">Role</th>
                            <th class="px-4 py-3 text-center border-b">Action</th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700">
                        @foreach ($users as $doc)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 text-center font-semibold">{{ $doc['name'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $doc['email'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ $doc->getRoleNames()->isEmpty() ? '-' : implode(', ', $doc->getRoleNames()->map(fn($role) => ucfirst($role))->toArray()) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        @click="editForm = {
                                            ...{{ json_encode($doc) }},
                                            role: {{ $doc->roles->first()->id ?? 'null' }}
                                        };
                                        openModal = true"
                                        class="text-blue-600 hover:underline mr-3">
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
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit User</h2>

                <form x-bind:action="'{{ route('admin.master_data.user.update', '') }}/' + editForm.id"  method="POST">
                    @method('PUT')
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                        <input type="text" id="edit_user_name" name="edit_user_name" x-model="editForm.name"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                            value="Admin">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="edit_user_email" name="edit_user_email" x-model="editForm.email"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                            value="Admin@gmail.com">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>

                        <p class="text-sm text-orange-400">If you do not want to change the password, leave this field blank.</p>
                        <div class="relative">
                            <input
                            id="edit_user_password"
                            name="edit_user_password"
                            type="password"
                            class="password-field w-full border border-gray-300 rounded-md px-3 py-2 pr-10
                                focus:ring-blue-500 focus:border-blue-500"
                            placeholder=""
                            autocomplete="new-password">
                            <button type="button"
                                class="toggle-password absolute inset-y-0 right-3 flex items-center text-gray-500">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                        <select id="edit_user_role_id" name="edit_user_role_id" x-model="editForm.role"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>

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
                    Are you sure to delete this user?
                </p>
                <p class="text-gray-500 text-sm mt-1">
                    This action cannot be undone.
                </p>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="deleteModal = false"
                        class="px-4 py-2 rounded-sm border border-gray-300 text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                        Cancel
                    </button>

                    <form x-bind:action="'{{ route('admin.master_data.user.destroy', '') }}/' + deleteForm.id"  method="POST">
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', () => {
                const input = button.previousElementSibling;
                const icon = button.querySelector('i');

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>


</x-app-layout>
