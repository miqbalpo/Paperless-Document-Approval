<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document Approval</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen flex justify-center items-center p-6">
    {{-- {{ dd($key) }} --}}

    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-7xl">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">
            Document Approval
        </h1>

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

        @php
            // approvals collection, email and prevSign are provided by the controller
            $currentApproval = $approvals->firstWhere('user.email', $email);
            $firstApproval = $approvals->first();
            $isFirst = $currentApproval && $firstApproval && ($currentApproval->sort == $firstApproval->sort);
        @endphp

        <!-- Input Fields -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Document Title <span class="text-red-500">*</span>
                </label>
                <input type="text" value="{{ $documentData->title }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                    readonly />
            </div>

            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Approver Name <span class="text-red-500">*</span>
                </label>
                <input type="text" value="{{ $name }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                    readonly />
            </div>
        </div>

        <!-- PDF & Signature Side by Side -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- PDF Viewer -->
            <div class="md:col-span-2 border rounded-md p-4 flex flex-col items-center justify-center bg-gray-50">
                <canvas id="pdfCanvas" class="w-full rounded-md border bg-white"></canvas>

                <div class="flex justify-center items-center mt-4 space-x-3">
                    <button id="prevPage"
                        class="px-4 py-1 border border-gray-300 rounded-md hover:bg-gray-200 transition">Prev</button>
                    <span id="pageInfo" class="text-gray-600">Page 1 / 1</span>
                    <button id="nextPage"
                        class="px-4 py-1 border border-gray-300 rounded-md hover:bg-gray-200 transition">Next</button>
                </div>
            </div>

            <!-- Right Side (Signature Panel) -->
            <div class="border rounded-lg p-4 flex flex-col justify-between">
                @if ($firstApproval->approved_at != null)
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <strong class="font-bold mr-2">This document was approved at:</strong>
                        <span class="block sm:inline">{{  \Carbon\Carbon::parse($firstApproval->approved_at)->format('d F Y : H:i')}}</span>
                    </div>
                @elseif ($firstApproval->rejected_at != null)
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <strong class="font-bold mr-2">This document was rejected at:</strong>
                        <p>
                            <span class="block sm:inline">{{  \Carbon\Carbon::parse($firstApproval->rejected_at)->format('d F Y : H:i')}}</span>
                        </p>
                    </div>

                @else
                    <div>
                        <!-- Upload form -->
                        <form id="uploadForm" method="POST" enctype="multipart/form-data"
                            action="{{ route('document.approval.upload_signature', ['email' => $email, 'key' => $key]) }}">
                            @csrf
                            <input type="file" id="uploadSignature" name="signature" accept="image/png" class="hidden" />
                        </form>

                        <form method="POST" id="approvalForm">
                            @csrf
                            @method('POST')
                            <div>
                                <h2 class="text-gray-800 font-semibold mb-3">Tanda tangan Anda</h2>

                                <div
                                    class="border border-gray-300 bg-gray-50 rounded-md h-48 flex items-center justify-center mb-4">
                                    <img id="signaturePreview"
                                        src="{{ route('document.approval.get_signature_file', ['email' => $email, 'key' => $key]) }}"
                                        alt="TTD Preview" class="max-h-full object-contain" />
                                </div>

                                <button type="button" id="uploadBtn"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md mb-3 transition">
                                    @if ($isFirst)
                                        Upload TTD Baru
                                    @else
                                        Tambah TTD
                                    @endif
                                </button>

                                @php
                                    $currentUserSignature = $currentApproval->signature;
                                @endphp

                                @if (!$currentUserSignature)
                                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4"
                                        role="alert">
                                        <strong class="font-bold mr-2">Perhatian:</strong>
                                        <span class="block sm:inline">Silakan upload tanda tangan Anda terlebih dahulu sebelum melakukan approval.</span>
                                    </div>
                                @else
                                    <button type="submit"
                                        formaction="{{ route('document.approval.reject', ['email' => $email, 'key' => $key]) }}"
                                        class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2 rounded-md mb-3 transition"
                                        onclick="return confirm('Are you sure you want to reject this document?')">
                                        Reject
                                    </button>
                                    <button type="submit"
                                        formaction="{{ route('document.approval.approve', ['email' => $email, 'key' => $key]) }}"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-md transition"
                                        onclick="return confirm('Are you sure you want to approve this document?')">
                                        Approve
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        const pdfUrl = "{{ route('document.approval.get_pdf_file', ['email' => $email, 'key' => $key]) }} ";
        let pdfDoc = null;
        let pageNum = 1;
        let pageRendering = false;
        const canvas = document.getElementById("pdfCanvas");
        const ctx = canvas.getContext("2d");

        const renderPage = (num) => {
            pageRendering = true;
            pdfDoc.getPage(num).then((page) => {
                const containerWidth = canvas.parentElement.clientWidth;
                const baseViewport = page.getViewport({ scale: 1 });
                const scale = (containerWidth / baseViewport.width) * 0.95;

                const dpi = window.devicePixelRatio || 1;
                const viewport = page.getViewport({ scale: scale * dpi });

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                canvas.style.width = (viewport.width / dpi) + "px";
                canvas.style.height = (viewport.height / dpi) + "px";

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport,
                };
                const renderTask = page.render(renderContext);
                renderTask.promise.then(() => {
                    pageRendering = false;
                    document.getElementById("pageInfo").textContent =
                        `Page ${pageNum} / ${pdfDoc.numPages}`;
                });
            });
        };

        const queueRenderPage = (num) => {
            if (pageRendering) {
                setTimeout(() => queueRenderPage(num), 100);
            } else {
                renderPage(num);
            }
        };

        const prevPage = () => {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        };

        const nextPage = () => {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        };

        pdfjsLib.getDocument(pdfUrl).promise.then((pdfDoc_) => {
            pdfDoc = pdfDoc_;
            renderPage(pageNum);
        });

        document.getElementById("prevPage").addEventListener("click", prevPage);
        document.getElementById("nextPage").addEventListener("click", nextPage);

        const uploadBtn = document.getElementById('uploadBtn');
        const uploadSignature = document.getElementById('uploadSignature');
        const signaturePreview = document.getElementById('signaturePreview');

        uploadBtn.addEventListener('click', () => uploadSignature.click());
        uploadSignature.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file && (file.type === "image/png" || file.type === "image/jpeg")) {
                const reader = new FileReader();
                reader.onload = (e) => signaturePreview.src = e.target.result;
                reader.readAsDataURL(file);

                document.getElementById('uploadForm').submit();
            } else {
                alert("Harap upload file PNG/JPEG saja.");
            }
        });
    </script>

</body>

</html>