<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <div x-data="{
        viewApprover: false,
        selectedDocId: null
        }" class="container mx-auto px-4 py-8">

        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Report</h1>
                    <p class="text-gray-600">Summary by Document Type</p>
                </div>
                <div class="flex gap-3">
                    <button
                        id="export-button"
                        data-total-pages="{{ $documents->lastPage() }}"
                        class="flex items-center gap-2 px-5 py-2.5 bg-red-500 text-white rounded-lg hover:bg-red-700 transition-all shadow-lg font-semibold">
                        <i class="bi bi-file-earmark-excel"></i>
                        <span>Export</span>
                    </button>
                </div>
            </div>
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 my-6">
                <!-- Total Document -->
                <div class="bg-gray-100 rounded-2xl shadow-xl p-6 text-gray-700 transform hover:scale-105 transition-transform">
                    <div class="flex text-center justify-center items-center">
                        <div>
                            <p class="text-gray-800 text-sm font-medium mb-2">Total Document Active</p>
                            <h2 class="text-4xl text-gray-800 font-bold mb-1">{{ number_format($countTotal) }}</h2>
                        </div>
                    </div>
                </div>

                <!-- Total Removed -->
                <div class="bg-gray-300 rounded-2xl shadow-xl p-6 text-gray-700 transform hover:scale-105 transition-transform">
                    <div class="flex text-center justify-center items-center">
                        <div>
                            <p class="text-gray-800 text-sm font-medium mb-2">Total Document Removed</p>
                            <h2 class="text-4xl text-gray-800 font-bold mb-1">{{ number_format($countRemoved) }}</h2>
                        </div>
                    </div>
                </div>

                <!-- Waiting Approval -->
                <div class="bg-yellow-100 rounded-2xl shadow-xl p-6 text-gray-700 transform hover:scale-105 transition-transform">
                    <div class="flex text-center justify-center items-center">
                        <div>
                            <p class="text-yellow-800 text-sm font-medium mb-2">Waiting Approval</p>
                            <h2 class="text-4xl text-yellow-800 font-bold mb-1">{{ number_format($countWaiting) }}</h2>
                        </div>
                    </div>
                </div>

                <!-- Approved -->
                <div class="bg-green-100 rounded-2xl shadow-xl p-6 text-gray-700 transform hover:scale-105 transition-transform">
                    <div class="flex text-center justify-center items-center">
                        <div>
                            <p class="text-green-800 text-sm font-medium mb-2">Approved</p>
                            <h2 class="text-4xl text-green-800 font-bold mb-1">{{ number_format($countApproved) }}</h2>
                        </div>
                    </div>
                </div>

                <!-- Rejected -->
                <div class="bg-red-100 rounded-2xl shadow-xl p-6 text-gray-700 transform hover:scale-105 transition-transform">
                    <div class="flex text-center justify-center items-center">
                        <div>
                            <p class="text-red-800 text-sm font-medium mb-2">Rejected</p>
                            <h2 class="text-4xl text-red-800 font-bold mb-1">{{ number_format($countRejected) }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 md:mb-8 md:px-20 py-10">
                <!-- Bar Chart -->
                <div class="bg-white rounded-2xl p-8 border border-gray-300 hover:shadow-3xl transition-shadow duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Status Comparison</h3>
                        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Bar Chart</span>
                    </div>
                    <div class="h-64 md:h-96">
                        <canvas id="documentBarChart"></canvas>
                    </div>
                </div>
                <div id="chart-data"
                    data-count-total="{{ $countTotal }}"
                    data-count-removed="{{ $countRemoved }}"
                    data-count-waiting="{{ $countWaiting }}"
                    data-count-approved="{{ $countApproved }}"
                    data-count-rejected="{{ $countRejected }}">
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Filters</h3>
            <form method="GET" action="{{ route('dashboard.report.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Document Type</label>
                        <select name="documentType" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">All Types</option>
                            @foreach ($documents->pluck('documentType.name')->unique() as $documentTypeName)
                            <option value="{{ $documentTypeName }}" {{ request('documentType') == $documentTypeName ? 'selected' : '' }}>
                                {{ $documentTypeName }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                            <option value="">All Status</option>

                            @php
                            // Definisikan status yang mungkin ada
                            $statuses = ['Waiting', 'Approved', 'Rejected', 'Active', 'Removed'];
                            @endphp

                            @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}" {{ request('status') == $statusOption ? 'selected' : '' }}>
                                {{ $statusOption }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid md:grid-cols-2 h-1/2 gap-2 my-6">
                        <button type="submit" class="text-center px-2 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium whitespace-nowrap">
                            Apply Filter
                        </button>
                        @if(request()->hasAny(['type', 'date', 'status']))
                        <a href="{{ route('dashboard.report.index') }}" class="w-full text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium whitespace-nowrap">
                            Reset
                        </a>
                        @endif
                    </div>
                </div>
            </form>
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 my-8">
                <!-- Table Section -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden">
                        <div class="overflow-x-auto">
                            <table id="document-table" class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Document Name</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Document Type</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Approvers</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y ">
                                    @foreach ($documents as $index => $doc)
                                    <tr class="hover:bg-purple-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-700">{{ $documents->firstItem() + $loop->index }}</td>
                                        <td class="px-6 py-4 text-center text-sm text-gray-700">
                                            @php
                                            $statusClass = [
                                            'Approved', 'Active' => 'text-green-800 bg-green-100',
                                            'Waiting' => 'text-yellow-800 bg-yellow-100',
                                            'Rejected', 'Removed' => 'text-red-800 bg-red-100',
                                            ];
                                            $statusText = $doc->status ?? '-';
                                            @endphp
                                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold {{ $statusClass[$statusText] ?? 'text-green-800' }}">
                                                <!-- {{ $statusText }} -->
                                                {{ $doc->title }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-gray-700">{{ $doc->documentType->name }}</td>
                                        <td class="px-6 py-4 text-center text-sm text-gray-700">{{ date('d F Y', strtotime($doc->date)) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button
                                                @click="viewApprover = true; selectedDocId = {{ $doc->id }}"
                                                class="text-blue-600 hover:underline inline-flex items-center">
                                                View Approvers <i class="bi bi-box-arrow-up-right ml-1"></i>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $doc->status_color }}">
                                                {{ $doc->approval_status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 pt-6 sm:px-6">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <a href="{{ $documents->previousPageUrl() ?? '#' }}" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $documents->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                            Previous
                        </a>
                        <a href="{{ $documents->nextPageUrl() ?? '#' }}" class="relative ml-3 inline-flex items-center rounded-md border border-gray-800 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $documents->hasMorePages() ? '' : 'opacity-50 cursor-not-allowed' }}">
                            Next
                        </a>
                    </div>

                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ $documents->firstItem() }}</span>
                                to
                                <span class="font-medium">{{ $documents->lastItem() }}</span>
                                of
                                <span class="font-medium">{{ $documents->total() }}</span>
                                results
                            </p>
                        </div>

                        <div>
                            <nav aria-label="Pagination" class="isolate inline-flex -space-x-px rounded-md shadow-xs">
                                {{-- Previous Page --}}
                                <a href="{{ $documents->previousPageUrl() ?? '#' }}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 hover:bg-gray-50 {{ $documents->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    <span class="sr-only">Previous</span>
                                    <i class="bi bi-chevron-left text-black"></i>
                                </a>

                                {{-- Page Numbers --}}
                                @for ($i = 1; $i <= $documents->lastPage(); $i++)
                                    <a href="{{ $documents->url($i) }}"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold {{ $documents->currentPage() == $i ? 'z-10 bg-indigo-600 text-white' : 'text-gray-900 hover:bg-gray-50' }}">
                                        {{ $i }}
                                    </a>
                                    @endfor

                                    {{-- Next Page --}}
                                    <a href="{{ $documents->nextPageUrl() ?? '#' }}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 hover:bg-gray-50 {{ $documents->hasMorePages() ? '' : 'opacity-50 cursor-not-allowed' }}">
                                        <span class="sr-only">Next</span>
                                        <i class="bi bi-chevron-right text-black"></i>
                                    </a>
                            </nav>
                        </div>
                    </div>
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
            x-show="viewApprover" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        >
            <div class="bg-white rounded-2xl shadow-lg p-6 relative w-4/5 max-w-5xl"
                @click.away="viewApprover = false">

                <h2 class="text-lg font-semibold text-gray-800 mb-4">Approvers List</h2>

                <div class="space-y-2">

                    @foreach($documents as $d)

                        <template x-if="selectedDocId == {{ $d->id }}">
                            <div>

                                @if ($d->routingApprovals->isEmpty())
                                    <p class="text-center text-gray-500 italic py-4">
                                        Approver belum dipilih.
                                    </p>
                                @else
                                    @foreach($d->routingApprovals as $approval)

                                        <div class="border border-gray-300 rounded-lg p-3 bg-gray-50 mb-2">

                                            <div class="flex justify-between mb-2">
                                                <span class="font-semibold text-sm text-gray-800">
                                                    {{ $approval->user->name }}
                                                </span>
                                                @php
                                                    $positionColors = [
                                                        'Manager' => 'text-green-900 bg-green-200',
                                                        'Supervisor' => 'text-yellow-900 bg-yellow-200',
                                                        'Staff' => 'text-gray-900 bg-gray-200',
                                                        'Operator' => 'text-blue-900 bg-blue-200',
                                                    ];

                                                    $posName = $approval->user->employeeDetail->position->name ?? '-';
                                                    $badgeColor = $positionColors[$posName] ?? 'bg-gray-600 text-white'; // default warna abu + teks putih
                                                @endphp

                                                <span class="px-2 py-1 text-xs rounded-full {{ $badgeColor }}">
                                                    {{ $posName }}
                                                </span>

                                            </div>

                                            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                                <span>Email sent at: {{ $approval->created_at ? $approval->created_at->format('d F Y') : ' - ' }}</span>

                                                @if (!$approval->approved_at)
                                                    <button class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                                        Send
                                                    </button>
                                                @elseif ($approval->approved_at)
                                                    <button class="px-2 py-1 bg-orange-500 text-white rounded hover:bg-orange-600">
                                                        Resend
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-500">Completed</span>
                                                @endif
                                            </div>

                                            <div class="text-xs pt-2 border-t border-gray-200">
                                                <span class="text-gray-600">Approve Status:</span>

                                                @php
                                                    // Logika Status berdasarkan Timestamp
                                                    $statusLabel = 'PENDING';
                                                    $statusColor = 'text-yellow-600';

                                                    if ($approval->approved_at) {
                                                        $statusLabel = 'APPROVED';
                                                        $statusColor = 'text-green-600';
                                                    } elseif ($approval->rejected_at) {
                                                        $statusLabel = 'REJECTED';
                                                        $statusColor = 'text-red-600';
                                                    }
                                                @endphp

                                                <span class="ml-1 font-bold uppercase {{ $statusColor }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </div>
                                        </div>

                                    @endforeach
                                @endif

                            </div>
                        </template>

                    @endforeach

                </div>

                <div class="mt-5 text-center">
                    <button @click="viewApprover = false" class="bg-blue-600 text-white px-5 py-2 rounded">
                        Close
                    </button>
                    <button
                        @click="viewApprover = false"
                        class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-xl font-bold"
                    >&times;</button>
                </div>

            </div>
        </div>

    </div>



    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.getElementById("export-button").addEventListener("click", async function() {

            const totalPages = Number(document.getElementById("export-button").dataset.totalPages);

            let excelData = [];
            let maxApprover = 0;
            let allRows = [];

            for (let page = 1; page <= totalPages; page++) {
                const res = await fetch(`{{ route('dashboard.report.index') }}?page=${page}`, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const rows = doc.querySelectorAll("#document-table tbody tr");

                rows.forEach(row => {
                    allRows.push(row);
                    const approverBoxes = row.querySelectorAll("td")[4]?.querySelectorAll(".border") || [];
                    if (approverBoxes.length > maxApprover) {
                        maxApprover = approverBoxes.length;
                    }
                });
            }

            let header = [
                "No",
                "Document Name",
                "Document Type",
                "Date",
                "Status"
            ];

            for (let i = 1; i <= maxApprover; i++) {
                header.push(
                    `Approver ${i} Name`,
                    `Approver ${i} Position`,
                    `Approver ${i} Email Sent`,
                    `Approver ${i} Approval Status`
                );
            }

            excelData.push(header);

            allRows.forEach(row => {
                const cols = row.querySelectorAll("td");

                let rowData = [
                    cols[0]?.innerText.trim(),
                    cols[1]?.innerText.trim(),
                    cols[2]?.innerText.trim(),
                    cols[3]?.innerText.trim(),
                    cols[5]?.innerText.trim(),
                ];

                const approverBoxes = cols[4].querySelectorAll(".border");

                approverBoxes.forEach(box => {
                    const approverName = box.querySelector(".font-semibold")?.innerText.trim() || "-";
                    const position = box.querySelector("a")?.innerText.trim() || "-";
                    const emailSent = box.querySelector("span")?.innerText.replace("Email sent at:", "").trim() || "-";
                    const approvalStatus = box.querySelector(
                        ".font-semibold.text-green-600, .font-semibold.text-red-600, .font-semibold.text-yellow-600"
                    )?.innerText.trim() || "-";

                    rowData.push(
                        approverName,
                        position,
                        emailSent,
                        approvalStatus
                    );
                });

                const emptyCells = (maxApprover - approverBoxes.length) * 4;
                for (let i = 0; i < emptyCells; i++) {
                    rowData.push("");
                }

                excelData.push(rowData);
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, "Report");
            XLSX.writeFile(wb, "document-report.xlsx");
        });

        const chartEl = document.getElementById('chart-data');
        const data = [
            Number(chartEl.dataset.countTotal),
            Number(chartEl.dataset.countRemoved),
            Number(chartEl.dataset.countWaiting),
            Number(chartEl.dataset.countApproved),
            Number(chartEl.dataset.countRejected),
        ];
        const ctxBar = document.getElementById('documentBarChart').getContext('2d');
        const documentBarChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Active', 'Removed', 'Waiting', 'Approved', 'Rejected'],
                datasets: [{
                    label: 'Documents',
                    // data: [{{ number_format($countTotal) }}, {{ number_format($countApproved) }}, {{ number_format($countRejected) }}, {{ number_format($countWaiting) }}, {{ number_format($countRemoved) }}],
                    data: data,
                    backgroundColor: [
                        'rgba(156, 163, 175, 0.8)',
                        'rgba(107, 114, 128, 0.8)',
                        'rgba(252, 211, 77, 0.8)',
                        'rgba(52, 211, 153, 0.8)',
                        'rgba(248, 113, 113, 0.8)'

                    ],
                    borderColor: [
                        '#9CA3AF',
                        '#6B7280',
                        '#FCD34D',
                        '#34D399',
                        '#F87171'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toLocaleString();

                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                                label += ` (${percentage}%)`;

                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });
    </script>




</x-app-layout>
