<x-app-layout>

    {{--

    ---Beri tombol 'filter' untuk filter berdasarkan 'upload_documents.status' ['active','archived','removed']
    ---Buat Tabel 'upload_document' dengan kolom---
    nomor
    document_number;
    created_by (nama pembuat)
    title;
    date;
    document_type_id (document_type->name)
    departement_id (departement_type->name)
    status
    filename; (Berikan Tombol Untuk melihat dokumen)
    approver :
    ____________________________________________________________________________________
    | Aprover 1 Name |
    | --TOMBOL EDIT APPROVER SIGNATURE POSITION -- (tidak perlu buat backend) | |
    | Email sent at : 12 Des 2023, 14.00 --TOMBOL RESEND-- (tidak perlu buat backend) |
    | Aprove Status : APPROVED |
    ____________________________________________________________________________________
    | Aprover 2 Name |
    | --TOMBOL EDIT APPROVER SIGNATURE POSITION -- (tidak perlu buat backend) |
    | Email sent at : - --TOMBOL SEND-- (tidak perlu buat backend) |
    | Aprove Status : - |
    ____________________________________________________________________________________


    ACTION (
    EDIT,
    DELETE,
    )

    -- Catatan Khusus --
    1) Form `approver` : buat 'multi select options' karena bisa memilih lebih dari 1 approver(user), serta bisa
    mengatur URUTAN approver(user)
    --}}

     <!-- Alert Messages -->
                @foreach (['success', 'error', 'warning', 'info'] as $msg)
                    @if(session($msg))
                        @php
                            $color = match ($msg) {
                                'success' => 'green',
                                'error' => 'red',
                                'warning' => 'yellow',
                                'info' => 'blue',
                                default => 'gray',
                            };
                        @endphp

                        <div class="bg-{{ $color }}-100 border border-{{ $color }}-400 text-{{ $color }}-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <strong class="font-bold mr-2">{{ ucfirst($msg) }}:</strong>
                            <span class="block sm:inline">{{ session($msg) }}</span>
                        </div>
                    @endif
                @endforeach

<div x-data="{
        viewApprovers: false,
        deleteForm: {},
        deleteModal: false,
        selectedDocId: null
    }" class="container mx-auto px-4 py-8">

        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl p-4">

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Filters</h3>
            <form method="GET" action="{{ route('document.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                onchange="this.form.submit()">
                            <option value="">All Status</option>
                            @foreach(['Waiting','Approved','Rejected'] as $statusOption)
                                <option value="{{ $statusOption }}"
                                    {{ request('status') == $statusOption ? 'selected' : '' }}>
                                    {{ $statusOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- APPLY & RESET -->
                    <div class="flex gap-2 items-end">
                        <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Apply Filter
                        </button>

                        <a href="{{ route('document.index') }}"
                            class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Reset
                        </a>
                    </div>

                    <!-- UPLOAD BUTTON -->
                    @if (Auth::check() && Auth::user()->hasRole('admin'))                        
                    <div class="flex items-end justify-start lg:justify-end">
                        <a href="{{ route('document.create') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="bi bi-plus-lg mr-2"></i>
                            Upload Document
                        </a>
                    </div>
                    @endif
                </div>
            </form>

            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 my-8">
                <!-- Table Section -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-300 text-gray-700 text-sm uppercase">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            Document Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            Created By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            Document Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            File</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            Approvers</th>
                                        @if (Auth::check() && Auth::user()->hasRole('admin'))
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                                Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
                                    @foreach($documents as $index => $doc)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $doc->document_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doc->createdBy->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">{{ $doc->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $doc->date ? \Carbon\Carbon::parse($doc->date)->format('d F Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doc->documentType->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doc->departement->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $doc->status_color }}">
                                                {{ $doc->approval_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $previewUrl = route('document.preview', $doc->id);
                                                    // Asumsi: Kita cek apakah URL preview mengarah ke file yang valid 
                                                    // atau apakah URL tersebut hanya string kosong/placeholder
                                                    if (empty($previewUrl) || $previewUrl === '#') {
                                                        $finalUrl = asset("Doc2.pdf");
                                                    } else {
                                                        $finalUrl = $previewUrl;
                                                    }
                                                @endphp

                                            <a href="{{ $finalUrl }}" target="_blank"
                                                class="text-blue-600 hover:underline inline-flex items-center">
                                                View <i class="bi bi-box-arrow-up-right ml-1"></i>
                                            </a>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button
                                                @click="viewApprovers = true; selectedDocId = {{ $doc->id }}"
                                                class="text-blue-600 hover:underline inline-flex items-center">
                                                View Approvers <i class="bi bi-box-arrow-up-right ml-1"></i>
                                            </button>
                                        </td>
                                        @if (Auth::check() && Auth::user()->hasRole('admin'))
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button @click.prevent="deleteForm = {{ json_encode($doc) }}; deleteModal = true"
                                                class="text-red-500 hover:underline">
                                                Delete
                                            </button>
                                        </td>
                                        @endif

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                                <p class="text-sm text-gray-700 hidden sm:block">
                                    Showing 
                                    <span class="font-medium">{{ $documents->firstItem() }}</span> 
                                        to 
                                    <span class="font-medium">{{ $documents->lastItem() }}</span> 
                                        of 
                                    <span class="font-medium">{{ $documents->total() }}</span> 
                                        results
                                </p>
                                <div class="flex gap-1">
                                    <!-- Previous -->
                                    <a href="{{ $documents->previousPageUrl() }}" class="px-3 py-2 border rounded-l-md 
                                                {{ $documents->onFirstPage() ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100' }}">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                    <!-- Page Numbers -->
                                    @for ($i = 1; $i <= $documents->lastPage(); $i++)
                                        <a href="{{ $documents->url($i) }}" class="px-4 py-2 border text-sm font-semibold 
                                                        {{ $documents->currentPage() == $i 
                                                            ? 'bg-indigo-600 text-white' 
                                                            : 'bg-white hover:bg-gray-100' }}">
                                                    {{ $i }}
                                        </a>
                                    @endfor
                                    <!-- Next -->
                                    <a href="{{ $documents->nextPageUrl() }}"
                                        class="px-3 py-2 border rounded-r-md 
                                                {{ $documents->hasMorePages() ? 'hover:bg-gray-100' : 'opacity-40 cursor-not-allowed' }}">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DELETE -->
        <div x-show="deleteModal" x-transition.opacity
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg text-center"
                @click.outside="deleteModal = false" x-transition>

                <div class="flex justify-center mb-4">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle-fill text-3xl"></i>
                    </div>
                </div>

                <p class="text-gray-800 text-lg mb-2">
                    Are you sure you want to delete
                    <span class="font-semibold text-red-600" x-text="deleteForm.document_number"></span>?
                </p>

                <p class="text-gray-500 text-sm mb-4">
                    This action cannot be undone.
                </p>

                <div class="flex justify-end gap-3">

                    <!-- Cancel -->
                    <button @click="deleteModal = false"
                        class="px-4 py-2 rounded-sm border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Cancel
                    </button>

                    <!-- Form Delete -->
                    <form x-bind:action="'{{ route('document.destroy', '') }}/' + deleteForm.id" method="POST">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="px-4 py-2 rounded-sm bg-red-600 text-white hover:bg-red-700">
                            Delete
                        </button>
                    </form>

                </div>

            </div>
        </div>

        @php
            $allApprovals = [];
            foreach($documents as $d) {
                $allApprovals[$d->id] = $d->routingApprovals;
            }
        @endphp

        <div
            x-show="viewApprovers"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            >
            <div class="bg-white rounded-2xl shadow-lg p-6 relative w-11/12 md:w-2/5 max-w-4xl"
                @click.away="viewApprovers = false">

                <h2 class="text-xl font-bold text-gray-800 mb-4">Approvers List</h2>

                <div class="space-y-4 max-h-[70vh] overflow-y-auto p-4">

                    @foreach($documents as $d)
                        <template x-if="selectedDocId == {{ $d->id }}">
                            <div>

                                @if($d->routingApprovals->isEmpty())
                                    <p class="text-center text-gray-500 italic py-4">
                                        Approver belum dipilih.
                                    </p>
                                @else

                                    @foreach($d->routingApprovals as $approval)

                                        @php
                                            $positionColors = [
                                                'Manager' => 'bg-green-500 text-white',
                                                'Supervisor' => 'bg-yellow-400 text-gray-900',
                                                'Staff' => 'bg-gray-300 text-gray-900',
                                                'Operator' => 'bg-blue-400 text-white',
                                            ];

                                            $posName = $approval->user->employeeDetail->position->name ?? '-';
                                            $badgeColor = $positionColors[$posName] ?? 'bg-gray-600 text-white';

                                            $statusLabel = 'Waiting';
                                            $statusColor = 'text-yellow-600';
                                            $approvalTimeStamp = null;

                                            if ($approval->approved_at) {
                                                $statusLabel = 'APPROVED';
                                                $statusColor = 'text-green-600';
                                                $approvalTimeStamp = \Carbon\Carbon::parse($approval->approved_at)->format('d F Y : H:i');
                                            } elseif ($approval->rejected_at) {
                                                $statusLabel = 'REJECTED';
                                                $statusColor = 'text-red-600';
                                                $approvalTimeStamp = \Carbon\Carbon::parse($approval->rejected_at)->format('d F Y : H:i');
                                            }

                                            $isApprovalFinal = $approval->approved_at || $approval->rejected_at;
                                            $isPreviousApproved = false;

                                            if ($approval->sort > 1) {
                                                $prevApproval = $d->routingApprovals->where('sort', $approval->sort - 1)->first();
                                                $isPreviousApproved = $prevApproval && $prevApproval->approved_at;
                                            }
                                        @endphp

                                        <div class="rounded-xl border border-gray-400 bg-white p-5 m-2 shadow-sm">

                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-base font-semibold text-gray-900">
                                                    {{ $approval->user->name }}
                                                </p>

                                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                                                    {{ $posName }}
                                                </span>
                                            </div>

                                            <!-- Email sent -->
                                            <p class="text-sm text-gray-600 mb-4">
                                                Email sent at:
                                                <span class="text-gray-800">
                                                    {{ $approval->email_sent_at ? \Carbon\Carbon::parse($approval->email_sent_at)->format('d F Y : H:i') : '-' }}
                                                </span>
                                            </p>

                                            <!-- ACTION BUTTONS: horizontal -->
                                            <div class="flex items-center gap-3 mb-4">

                                                <!-- Edit Signature -->
                                                <a target="_blank"
                                                href="{{ route('document.positioning.index', ['documentRoutingApproval' => $approval]) }}"
                                                class="px-3 py-1.5 bg-red-500 text-white text-xs rounded-md hover:bg-red-600">
                                                    Edit Signature Position
                                                </a>

                                                <!-- Send / Resend -->
                                                @if(!$isApprovalFinal)
                                                    @if($approval->sort == 1)
                                                        <form method="POST"
                                                            action="{{ route('document.approval.send_approval_email', ['document' => $approval->document]) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="px-3 py-1.5 bg-blue-500 text-white text-xs rounded-md hover:bg-blue-600">
                                                                {{ $approval->email_sent_at ? 'Resend' : 'Send' }}
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($approval->sort > 1 && $isPreviousApproved)
                                                        <form method="POST"
                                                            action="{{ route('document.approval.send_approval_email', ['document' => $approval->document]) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="px-3 py-1.5 bg-yellow-500 text-white text-xs rounded-md hover:bg-yellow-600">
                                                                Resend
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                            </div>

                                            <!-- STATUS -->
                                            <div class="border-t pt-3 text-sm text-gray-700">
                                                <p class="font-medium">
                                                    Approve Status:
                                                    <span class="font-bold {{ $statusColor }}">
                                                        {{ $statusLabel }}
                                                    </span>
                                                </p>

                                                @if ($approvalTimeStamp)
                                                    <p class="text-xs text-gray-600 mt-1">At: {{ $approvalTimeStamp }}</p>
                                                @endif
                                            </div>

                                        </div>
                                        <!-- END CARD -->

                                    @endforeach

                                @endif

                            </div>
                        </template>
                    @endforeach

                </div>

                <div class="mt-6 text-center">
                    <button @click="viewApprovers = false"
                            class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">
                        Close
                    </button>
                </div>

                <button @click="viewApprovers = false"
                        class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">
                    &times;
                </button>

            </div>
        </div>


       
</div>

     

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


    </div>






</x-app-layout>