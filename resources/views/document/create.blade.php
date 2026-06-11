<x-app-layout>
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('document.index') }}" class="text-blue-600 hover:text-blue-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-800">Create New Document</h1>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
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
            <!-- Tambahkan method POST dan action -->
            <form method="POST" action="{{ route('document.store') }}" enctype="multipart/form-data">
                @csrf
                <!-- Document Number -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Document Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="document_number"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="DOC-2024-001"
                           required>
                    <!-- <p class="text-xs text-gray-500 mt-1">Nomor dokumen akan di-generate otomatis jika dikosongkan</p> -->
                </div>

                <!-- Title -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Document Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="title"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Masukkan judul dokumen"
                           required>
                </div>

                <!-- Date -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Document Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="document_date"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>

                <!-- Document Type and Department (Row) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Document Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Document Type <span class="text-red-500">*</span>
                        </label>
                        <select name="document_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Document Type</option>
                            @foreach ( $dokumentTypes as $dt )
                                <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <select name="department_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Department</option>
                            @foreach ($departments as $dpt)
                                <option value="{{ $dpt->id }}">{{ $dpt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload File <span class="text-red-500">*</span>
                    </label>
                   <div x-data="{
                        fileName: 'No file chosen',
                        isDragging: false,
                        onFileSelect(event) {
                            const file = event.target.files?.[0] || event.dataTransfer?.files?.[0];
                            if (file) {
                                // Validasi tipe file
                                if (file.type !== 'application/pdf') {
                                    alert('Please upload a PDF file only');
                                    return;
                                }
                                
                                // Validasi ukuran file (10MB)
                                if (file.size > 10 * 1024 * 1024) {
                                    alert('File size must be less than 10MB');
                                    return;
                                }
                                
                                this.fileName = file.name;
                                this.isDragging = false;
                            }
                        },
                        onDragOver(event) {
                            event.preventDefault();
                            this.isDragging = true;
                        },
                        onDragLeave() {
                            this.isDragging = false;
                        }
                    }" 
                        class="border-2 border-dashed rounded-lg p-6 text-center transition cursor-pointer"
                        :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-500'"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent="onDragOver($event)"
                        @dragleave.prevent="onDragLeave()"
                        @drop.prevent="onFileSelect($event)">
                        
                        <input x-ref="fileInput" 
                            name="file" 
                            id="file" 
                            type="file" 
                            accept="application/pdf" 
                            class="hidden" 
                            @change="onFileSelect($event)" />
                        
                        <svg class="w-12 h-12 mx-auto mb-3" 
                            :class="isDragging ? 'text-blue-500' : 'text-gray-400'" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        
                        <template x-if="!isDragging">
                            <div>
                                <p class="text-sm text-gray-600 mb-2">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500 mb-2">PDF (Max 10MB)</p>
                            </div>
                        </template>
                        
                        <template x-if="isDragging">
                            <p class="text-sm text-blue-600 font-medium mb-2">Drop your PDF file here</p>
                        </template>
                        
                        <!-- Menampilkan nama file yang dipilih -->
                        <p x-show="fileName !== 'No file chosen'" 
                        x-text="fileName" 
                        class="text-sm text-blue-600 mb-3 font-medium truncate max-w-xs mx-auto"></p>
                        
                        <button type="button" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Choose File
                        </button>
                    </div>
                </div>

                <!-- Approvers Section -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Approvers <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-4">Pilih approver dan atur urutan approval (drag & drop untuk mengubah urutan)</p>
                      <!-- Hidden input untuk menyimpan urutan approver -->
                    <input type="hidden" name="approver_order" id="approver-order-input" value="">
                    <!-- Approvers List -->
                    <div id="approvers-list" class="space-y-3 mb-4">
                        <!-- Approver Item 1 -->
                        <div class="approver-item border border-gray-300 rounded-lg p-4 bg-white hover:shadow-md transition">
                            <div class="flex items-start gap-3">
                                <!-- Drag Handle -->
                                <div class="drag-handle text-gray-400 hover:text-gray-600 pt-2 cursor-move">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </div>
                                
                                <!-- Order Number -->
                                <div class="flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 rounded-full font-semibold text-sm flex-shrink-0">
                                    1
                                </div>
                                
                                <!-- Approver Select -->
                                <div class="flex-1">
                                    <!-- Tambahkan name attribute dengan format array -->
                                    <select name="approvers[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm approver-select" required>
                                        <option value="">-- Select Approver --</option>
                                        @foreach ( $approvers as $app )
                                            <option value="{{ $app->id }}">{{ $app->name }} ({{ $app->email }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Approver akan menerima email notifikasi secara berurutan</p>
                                    
                    
                                </div>
                              
                                <!-- Remove Button -->
                                <button type="button" class="text-red-600 hover:text-red-700 p-1 remove-approver">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>


                    <!-- Add Approver Button -->
                    <button type="button" id="add-approver-btn"
                        class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Add Another Approver
                        </button>
                </div>

                <!-- Info Box -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Catatan Penting:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Approver akan menerima notifikasi email secara berurutan sesuai urutan yang ditentukan</li>
                                <li>Approver berikutnya hanya akan menerima notifikasi setelah approver sebelumnya melakukan approval</li>
                                <li>Anda dapat mengubah urutan approver dengan drag & drop</li>
                                <li>Minimal 1 approver wajib dipilih</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 justify-end pt-6 border-t border-gray-200">
                    <a href="{{ route('document.index') }} " class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Submit Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const approversList = document.getElementById('approvers-list');
    const addButton = document.getElementById('add-approver-btn');
    const orderInput = document.getElementById('approver-order-input');
    
    // Counter untuk ID unik setiap approver item
    let approverCounter = 1;

    // Fungsi untuk update hidden input
    function updateApproverOrder() {
        const approverItems = approversList.querySelectorAll('.approver-item');
        const approversData = [];
        
        approverItems.forEach((item, index) => {
            const select = item.querySelector('select[name="approvers[]"]');
            const selectedValue = select ? select.value : '';
            
            if (selectedValue) {
                approversData.push({
                    order: index + 1,
                    name: select.options[select.selectedIndex]?.text || '',
                    user_id: selectedValue
                });
            }
        });
        
        // Simpan ke hidden input sebagai JSON
        orderInput.value = JSON.stringify(approversData);
        
        console.log('Updated approver order:', approversData); // Debug
    }

    // Fungsi untuk clone approver dengan ID unik
    function cloneApproverItem(template) {
        const clone = template.cloneNode(true);
        const newId = 'approver-' + approverCounter++;
        
        // Set ID unik untuk item
        clone.dataset.approverId = newId;
        
        // Reset select value
        const select = clone.querySelector('select[name="approvers[]"]');
        if (select) {
            select.selectedIndex = 0;
            // Tambahkan event listener untuk select change
            select.addEventListener('change', updateApproverOrder);
        }
        
        // Update remove button event
        const removeBtn = clone.querySelector('.remove-approver');
        if (removeBtn) {
            removeBtn.addEventListener('click', removeApproverItem);
        }
        
        // Make drag handle draggable
        const dragHandle = clone.querySelector('.drag-handle');
        if (dragHandle) {
            dragHandle.setAttribute('draggable', 'true');
            dragHandle.addEventListener('dragstart', handleDragStart);
        }
        
        return clone;
    }

    // Fungsi untuk menghapus approver
    function removeApproverItem(e) {
        const approverItems = approversList.querySelectorAll('.approver-item');
        
        if (approverItems.length > 1) {
            e.target.closest('.approver-item').remove();
            updateApproverOrder();
            updateOrderNumbers();
        } else {
            alert('Minimal harus ada 1 approver');
        }
    }

    // Fungsi untuk update nomor urut
    function updateOrderNumbers() {
        const approverItems = approversList.querySelectorAll('.approver-item');
        
        approverItems.forEach((item, index) => {
            const orderBadge = item.querySelector('.flex.items-center.justify-center');
            if (orderBadge) {
                orderBadge.textContent = index + 1;
            }
        });
    }

    // Add approver button click handler
    addButton.addEventListener('click', function() {
        const approverItems = approversList.querySelectorAll('.approver-item');
        
        if (approverItems.length > 0) {
            const lastItem = approverItems[approverItems.length - 1];
            const newApprover = cloneApproverItem(lastItem);
            
            // Append ke list
            approversList.appendChild(newApprover);
            
            // Update UI
            updateOrderNumbers();
            updateApproverOrder();
        }
    });

    // Remove approver event delegation
    approversList.addEventListener('click', function(e) {
        if (e.target.closest('.remove-approver')) {
            removeApproverItem(e);
        }
    });

    // Select change event delegation
    approversList.addEventListener('change', function(e) {
        if (e.target.classList.contains('approver-select')) {
            updateApproverOrder();
        }
    });

    // Drag and drop functionality
    let draggedItem = null;

    function handleDragStart(e) {
        draggedItem = e.target.closest('.approver-item');
        setTimeout(() => {
            draggedItem.style.opacity = '0.5';
        }, 0);
    }

    approversList.addEventListener('dragend', function(e) {
        if (draggedItem) {
            draggedItem.style.opacity = '1';
            draggedItem = null;
            updateOrderNumbers();
            updateApproverOrder();
        }
    });

    approversList.addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(approversList, e.clientY);
        const draggable = draggedItem;
        
        if (afterElement == null) {
            approversList.appendChild(draggable);
        } else {
            approversList.insertBefore(draggable, afterElement);
        }
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.approver-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // Initialize drag handles
    document.querySelectorAll('.drag-handle').forEach(handle => {
        handle.setAttribute('draggable', 'true');
        handle.addEventListener('dragstart', handleDragStart);
    });

    // Initialize remove buttons
    document.querySelectorAll('.remove-approver').forEach(btn => {
        btn.addEventListener('click', removeApproverItem);
    });

    // Initialize select change events
    document.querySelectorAll('.approver-select').forEach(select => {
        select.addEventListener('change', updateApproverOrder);
    });

    // Initialize order
    updateApproverOrder();
});

// Optional: Function untuk submit form
function submitApproverForm() {
    const orderInput = document.getElementById('approver-order-input');
    const approverData = JSON.parse(orderInput.value || '[]');
    
    // Validasi minimal 1 approver
    if (approverData.length === 0) {
        alert('Pilih minimal 1 approver');
        return false;
    }
    
    // Validasi semua approver harus dipilih
    const hasEmptySelection = approverData.some(item => !item.user_id);
    if (hasEmptySelection) {
        alert('Semua approver harus dipilih');
        return false;
    }
    
    // Lanjutkan submit form
    return true;
}
    </script>
</x-app-layout>