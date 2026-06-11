<x-app-layout>
    <div x-data="docTypeModal()" class="p-8 bg-gray-100 min-h-screen">
        <div class="bg-white rounded-2xl shadow-md p-6 max-w-5xl mx-auto">
            <!-- Form -->
            <form method="POST" action="{{ route('admin.master_data.document_type.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-6">
                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Document Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="doc_code" class="w-full border border-gray-300 rounded-lg px-3 py-2"
                            placeholder="Enter document code" required>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Document Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="doc_name" class="w-full border border-gray-300 rounded-lg px-3 py-2"
                            placeholder="Enter document name" required>
                    </div>

                    <!-- Button -->
                    <div class="flex justify-start md:justify-end">
                        <button type="submit"
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg transition w-full md:w-auto justify-center">
                            Add Document Type
                            <i class="bi bi-plus-lg ml-2"></i>
                        </button>
                    </div>
                </div>
            </form>
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
            x-data="{ showAlert: true }"
            x-show="showAlert"
            x-init="setTimeout(() => showAlert = false, 3000)"
            x-transition
            class="{{ $alertClasses[$msg] }} px-4 py-3 rounded relative mb-4 mt-2"
            role="alert">
            <strong class="font-bold mr-2">{{ ucfirst($msg) }}:</strong>
            <span class="block sm:inline">{{ session($msg) }}</span>

            <!-- Tombol Close -->
            <span @click="showAlert = false" 
                class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                <svg class="fill-current h-6 w-6 
                    text-{{ $msg === 'error' ? 'red' : ($msg === 'success' ? 'green' : 'gray') }}-700" 
                    role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 5.652a.5.5 0 0 0-.707 0L10 9.293 6.36 5.652a.5.5 0 1 0-.707.707L9.293 10l-3.64 3.64a.5.5 0 0 0 .707.707L10 10.707l3.64 3.64a.5.5 0 0 0 .707-.707L10.707 10l3.64-3.64a.5.5 0 0 0 0-.708z"/>
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
                            <th class="px-4 py-3 text-center border-b">Code</th>
                            <th class="px-4 py-3 text-center border-b">Name</th>
                            <th class="px-4 py-3 text-center border-b">Action</th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700">
                        @foreach ($documents as $doc)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 text-center font-semibold">{{ $doc['code'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $doc['name'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="editDocTypeForm('{{ $doc->id }}',{
                                        code: '{{ $doc->code }}',
                                        name: '{{ $doc->name }}'})" class="text-blue-600 hover:underline mr-3">
                                        Edit
                                    </button>

                                    <button @click="deleteDocType('{{ $doc->code }}')" class="text-red-600 hover:underline">
                                        Delete
                                    </button>
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
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Document Type</h2>
                <form method="POST" :action="updateUrl" id="editDocTypeForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Code *</label>
                        <input type="text" name="doc_code" x-model="selected.code"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter new document code"
                            required>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Name *</label>
                        <input type="text" name="doc_name" x-model="selected.name"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter new document name"
                            required>
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
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script>
        function docTypeModal() {
            return {
                openModal: false,
                updateUrl: '',

                selected: {
                    doc_code: '',
                    doc_name: '',
                },

                editDocTypeForm(docTypeId, docType) {
                    this.selected = {
                        code: docType.code,
                        name: docType.name,
                    };

                    this.updateUrl = "{{ route('admin.master_data.document_type.update', ':id') }}"
                        .replace(':id', docTypeId);

                    this.openModal = true;
                },

                async deleteDocType(docTypeId) {
                    try {
                        const response = await fetch(`/admin/master_data/document_type/${docTypeId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            this.showNotification('Failed to delete document type', 'error');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        this.showNotification('An error occurred', 'error');
                    }
                }
            }
        }
    </script>
</x-app-layout>
