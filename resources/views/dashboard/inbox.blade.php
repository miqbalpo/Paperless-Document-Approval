<x-app-layout>
    <div class="bg-gray-100 min-h-screen">
        <div class="bg-gray-50 min-h-screen w-full" x-data="{ tab: 'waiting' }">

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <p class="text-gray-500 text-sm">Total Documents</p>
                    <p class="text-3xl font-semibold text-gray-700">{{ $documentsCount }}</p>
                </div>
                <div class="bg-yellow-100 rounded-xl shadow p-4 text-center">
                    <p class="text-gray-700 text-sm">Waiting Approval</p>
                    <p class="text-3xl font-semibold text-yellow-600">{{ $waitingTotal }}</p>
                </div>
                <div class="bg-green-100 rounded-xl shadow p-4 text-center">
                    <p class="text-gray-700 text-sm">Approved</p>
                    <p class="text-3xl font-semibold text-green-600">{{ $approvedTotal }}</p>
                </div>
                <div class="bg-red-100 rounded-xl shadow p-4 text-center">
                    <p class="text-gray-700 text-sm">Rejected</p>
                    <p class="text-3xl font-semibold text-red-600">{{ $rejectedTotal }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-3 mb-4">
                <button @click="tab = 'waiting'"
                    :class="tab === 'waiting' ? 'bg-white shadow text-gray-800' : 'bg-gray-200 text-gray-600'"
                    class="px-4 py-2 rounded-lg font-medium focus:outline-none transition">
                    Waiting Approval
                </button>
                <button @click="tab = 'archive'"
                    :class="tab === 'archive' ? 'bg-white shadow text-gray-800' : 'bg-gray-200 text-gray-600'"
                    class="px-4 py-2 rounded-lg font-medium focus:outline-none transition">
                    Archive
                </button>
            </div>

            <!-- Table Container -->
            <div class="bg-white shadow rounded-lg p-6">

                <!-- Waiting Approval Table -->
                <div x-show="tab === 'waiting'">
                    <h2 class="text-lg font-semibold mb-4">Waiting Approval</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-700 border-collapse">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="py-2 px-3 text-left">Doc Number</th>
                                    <th class="py-2 px-3 text-left">Title</th>
                                    <th class="py-2 px-3 text-center">Total Approver</th>
                                    <th class="py-2 px-3 text-center">Approved</th>
                                    <th class="py-2 px-3 text-center">Pending</th>
                                    <th class="py-2 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <span hidden>{{ $count = 1 }}</span>
                                @foreach ($documentsData as $doc)
                                    @php
                                        $docId = $doc->id;
                                        $status = $documentStatuses[$docId] ?? 'waiting';
                                    @endphp

                                    @if ($status == 'waiting')
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-2 px-3">{{ $doc->document_number }}</td>
                                            <td class="py-2 px-3">{{ $doc->title }}</td>
                                            <td class="py-2 px-3 text-center">{{ $approversCount[$docId] ?? 0 }}</td>
                                            <td class="py-2 px-3 text-center">{{ $approvedCount[$docId] ?? 0 }}</td>
                                            <td class="py-2 px-3 text-center">{{ $waitingCount[$docId] ?? 0 }}</td>
                                            <td class="py-2 px-3 text-center">
                                                <span
                                                    class="bg-yellow-100 text-yellow-700 text-xs px-3 py-1 rounded-full font-medium">
                                                    Waiting
                                                </span>
                                            </td>
                                        </tr>
                                        <span hidden>{{ $count++ }}</span>
                                    @endif
                                @endforeach

                                {{-- Show message if no waiting documents --}}
                                @if ($waitingTotal == 0)
                                    <tr>
                                        <td colspan="6" class="py-4 px-3 text-center text-gray-500">
                                            No waiting documents found.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Archive Table -->
                <div x-show="tab === 'archive'">
                    <h2 class="text-lg font-semibold mb-4">Archive</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-700 border-collapse">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="py-2 px-3 text-left">Doc Number</th>
                                    <th class="py-2 px-3 text-left">Title</th>
                                    <th class="py-2 px-3 text-center">Total Approver</th>
                                    <th class="py-2 px-3 text-center">Approved</th>
                                    <th class="py-2 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <span hidden>{{ $count = 1 }}</span>
                                @php $hasOtherStatus = false; @endphp
                                @foreach ($documentsData as $doc)
                                    @php
                                        $status = $documentStatuses[$doc->id] ?? 'waiting';
                                    @endphp

                                    @if ($status != 'waiting')
                                        @php $hasOtherStatus = true; @endphp
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-2 px-3">{{ $doc->document_number }}</td>
                                            <td class="py-2 px-3">{{ $doc->title }}</td>
                                            <td class="py-2 px-3 text-center">{{ $approversCount[$doc->id] ?? 0 }}</td>
                                            <td class="py-2 px-3 text-center">{{ $approvedCount[$doc->id] ?? 0 }}</td>
                                            <td class="py-2 px-3 text-center">
                                                @if ($status == 'approved')
                                                    <span
                                                        class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-medium">
                                                        Approved
                                                    </span>
                                                @elseif ($status == 'rejected')
                                                    <span
                                                        class="bg-red-100 text-red-700 text-xs px-3 py-1 rounded-full font-medium">
                                                        Rejected
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <span hidden>{{ $count++ }}</span>
                                    @endif
                                @endforeach

                                {{-- Show message if no non-waiting documents --}}
                                @if (!$hasOtherStatus)
                                    <tr>
                                        <td colspan="6" class="py-4 px-3 text-center text-gray-500">
                                            No approved or rejected documents found.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
