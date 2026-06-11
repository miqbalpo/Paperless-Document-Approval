<x-app-layout>
    <div x-data="{
            openModal: false,
            editData: {},
            viewModal: false,
            viewImage: ''
        }"
        class="p-8 bg-gray-100 min-h-screen">

        <div class="bg-white rounded-2xl shadow-md p-6 max-w-5xl mx-auto">

            <!-- Form Section -->
            <form class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end mb-6" method="POST" action="{{ route('admin.master_data.signature.store') }}" enctype="multipart/form-data">
                @csrf
                <!-- Choose Employee -->
                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Choose Employee <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="user_id"
                        id="user_id"
                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                        @foreach ($user as $use)
                            <option value="{{ $use->id}}">{{ $use->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Upload Signature -->
                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Upload Signature (PNG Only)<span class="text-red-500">*</span>
                    </label>
                    <input
                        name="filename"
                        id="filename"
                        type="file"
                        accept="image/png"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Add Button -->
                <div class="flex justify-start md:justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-md transition duration-200">
                        Add Signature
                        <i class="bi bi-plus-lg ml-2"></i>
                    </button>
                </div>
            </form>

            <!-- Table Section -->
            <div class="overflow-x-auto mt-4">
                @foreach (['success', 'error', 'warning', 'info'] as $msg)
                    @if(session($msg))
                        {{-- Tentukan kelas berdasarkan jenis pesan ($msg) --}}
                        @php
                            $color = match($msg) {
                                'success' => 'green',
                                'error' => 'red',
                                'warning' => 'yellow',
                                'info' => 'blue',
                                default => 'gray',
                            };
                        @endphp

                        <div class="bg-{{ $color }}-100 border border-{{ $color }}-400 text-{{ $color }}-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold mr-2">{{ ucfirst($msg) }}:</strong>
                            <span class="block sm:inline">{{ session($msg) }}</span>
                        </div>
                    @endif
                @endforeach
                <table class="min-w-full border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100 text-gray-700 text-sm uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left border-b">Name</th>
                            <th class="px-4 py-2 text-left border-b">Signature</th>
                            <th class="px-4 py-2 text-left border-b">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">

                        @foreach ($signatures as $sig)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $sig->user->name }}</td>
                                <td class="px-4 py-2">
                                    <button
                                        @click="viewImage = '{{ route('admin.master_data.signature.get_signature_file', $sig->id) }}'; viewModal = true"
                                        class="text-blue-600 hover:underline inline-flex items-center">
                                        View <i class="bi bi-box-arrow-up-right ml-1"></i>
                                    </button>
                                </td>
                                <td class="px-4 py-2">
                                    <button
                                        @click="editData = {{ json_encode($sig) }}; openModal = true"
                                        class="text-blue-600 hover:underline mr-3">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.master_data.signature.destroy', $sig->id) }}" method="post" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline"> Delete </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL EDIT -->
        <div
            x-show="openModal"
            x-transition
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        >
            <div class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 relative">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Signature File</h2>

                <form method="post" x-bind:action="'{{ route('admin.master_data.signature.update', '') }}/' + editData.id" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <!-- Choose Employee -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Choose Employee <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="user_id"
                            id="user_id"
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            @foreach ($signatures as $sign)
                                <option value="{{ $sign->user_id}}">{{ $sign->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Upload File -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Upload Signature (PNG Only)<span class="text-red-500">*</span>
                        </label>
                        <input
                            name="filename"
                            id="filename"
                            type="file"
                            accept="image/png"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500"
                        />
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Preview:
                            </label>
                            <img :src="'{{ route('admin.master_data.signature.get_signature_file', ['id_placeholder']) }}'.replace('id_placeholder', editData.id)"
                                alt="Signature Image"
                                class="w-full max-h-80 object-contain rounded-lg border" />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded-md transition">
                            Save
                        </button>
                    </div>
                </form>

                <!-- Close Button -->
                <button
                    @click="openModal = false"
                    class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-xl font-bold"
                >&times;</button>
            </div>
        </div>

        <!-- MODAL VIEW IMAGE -->
        <div
            x-show="viewModal"
            x-transition
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        >
            <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative text-center">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Signature Preview</h2>

                <img :src="viewImage" alt="Signature Image" class="w-full max-h-80 object-contain rounded-lg border" />

                <div class="mt-6 flex justify-center">
                    <button
                        @click="viewModal = false"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-md transition">
                        Close
                    </button>
                </div>

                <button
                    @click="viewModal = false"
                    class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-xl font-bold"
                >&times;</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</x-app-layout>
