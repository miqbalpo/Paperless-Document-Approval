<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-xs text-gray-500 mb-1">Judul Dokumen</p>
                <p class="font-bold text-gray-800">{{$documentRoutingApproval->document->title}}</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4">
                <p class="text-xs text-gray-500 mb-1">Nama Approver</p>
                <p class="font-bold text-gray-800">{{$documentRoutingApproval->user->name}}</p>
            </div>
        </div>

        <div class="mb-4">
            <button id="addSignatureBtn" 
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-sm shadow">
                + Add signature box
            </button>
        </div>

                <!-- PDF Viewer -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-4">
            <div id="pdfWrapper"
                class="relative border-2 border-indigo-200 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 shadow-inner 
                        max-h-[80vh] overflow-auto">
                <div class="relative bg-white m-4 shadow-2xl rounded-lg flex items-center justify-center" id="pdfPage">
                <canvas id="pdfCanvasNew" class="w-full h-auto"></canvas>

                <!-- Signature Box Template (hidden) -->
                <div id="signatureTemplate" class="signature-box template absolute cursor-move border-3 border-gray-900 bg-gray-100 rounded-xl p-3 shadow-2xl hover:shadow-indigo-300 transition-shadow"
                    style="display:none; left: 100px; top: 400px; width: 200px; height: 80px;">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-gray-900 flex items-center gap-1">TANDA TANGAN</span>
                        <div class="flex items-center gap-1">
                            <button type="button" class="lock-btn text-green-600 hover:text-green-800 bg-green-50 rounded-full p-1 text-xs transition" title="Lock & save">
                                <i class="fas fa-check">Y</i>
                            </button>
                            <button type="button" class="delete-btn text-red-500 hover:text-red-700 bg-red-50 rounded-full p-1 text-xs transition" title="Delete">
                                <i class="fas fa-times">X</i>
                            </button>
                        </div>
                    </div>
                    <div class="text-xs text-gray-900 font-semibold">Nama Approval</div>
                    <div class="text-xs text-gray-500 mt-1">Jabatan</div>
                    <div class="absolute bottom-0 right-0 w-5 h-5 cursor-se-resize rounded-tl-lg shadow-md resize-handle"></div>
                </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="bg-white rounded-xl shadow-md p-4 flex items-center justify-center gap-3 mt-4">
                <button onclick="prevPage()" id="prevBtn"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm font-medium transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-chevron-left"></i> <span class="text-xs">&lt; Prev</span>
                </button>
                <div class="text-center">
                <div class="text-xs text-gray-500 mb-1">Halaman</div>
                <div class="font-bold text-gray-800">
                    <span id="currentPage">1</span> / <span id="totalPages">1</span>
                </div>
                </div>
                <button onclick="nextPage()" id="nextBtn"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm font-medium transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="text-xs">Next &gt;</span> <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            </div>
        </div>


        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-xl p-4">
                <div class="text-sm font-semibold text-gray-800">Halaman Ditandatangani: <span id="signedPage">1</span></div>
                <div class="text-xs text-gray-600 mt-1">
                    Posisi: (<span class="font-mono font-bold text-indigo-600" id="positionX">100</span>, <span class="font-mono font-bold text-indigo-600" id="positionY">400</span>)
                </div>
            </div>

          <div class="flex gap-2">
            <button onclick="cancelSign()" 
                class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold transition shadow-lg text-sm">
                Cancel
            </button>

            <button onclick="saveSign()" 
                class="flex-1 px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold transition shadow-lg text-sm">
                Save
            </button>
        </div>

        </div>
    </div>
    <!-- PDF.js -->
  <script src="{{ asset('js/pdf.min.js') }}"></script>
  <script>
    const url = "{{ route('document.preview', ['document' => $documentRoutingApproval->document->id]) }}";
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";

    let pdfDoc = null;
    let pageNum = 1;
    const canvas = document.getElementById('pdfCanvasNew');
    const ctx = canvas.getContext('2d');
    const storeUrl = "{{ route('document.positioning.store', $documentRoutingApproval) }}";

    async function renderPage(num) {
      const page = await pdfDoc.getPage(num);
      const viewport = page.getViewport({ scale: 1.5 });

      canvas.height = viewport.height;
      canvas.width = viewport.width;

      const renderContext = {
        canvasContext: ctx,
        viewport: viewport
      };
      await page.render(renderContext).promise;

    // update wrapper & page height sesuai tinggi tampil (menggunakan ukuran ter-render)
    const displayedHeight = canvas.getBoundingClientRect().height;
    const pdfWrapper = document.getElementById('pdfWrapper');
    const pdfPageEl = document.getElementById('pdfPage');
    // set the inner page height to match the rendered canvas height
    if (pdfPageEl) pdfPageEl.style.height = displayedHeight + 'px';
    // include outer margins (m-4 -> 16px top + 16px bottom)
    const marginOffset = 32;
    if (pdfWrapper) pdfWrapper.style.height = (displayedHeight + marginOffset) + 'px';

    // render signature boxes for this page
    renderBoxesForPage(num);

      // update pagination
      document.getElementById('currentPage').textContent = num;
      document.getElementById('totalPages').textContent = pdfDoc.numPages;
      document.getElementById('prevBtn').disabled = num === 1;
      document.getElementById('nextBtn').disabled = num === pdfDoc.numPages;
    }

    function clearBoxes() {
        const nodes = Array.from(pdfPageEl.querySelectorAll('.signature-box'));
        nodes.forEach(n => {
            if (n.id === 'signatureTemplate') return;
            if (n.parentNode) n.parentNode.removeChild(n);
        });
    }

    function renderBoxesForPage(page) {
        clearBoxes();
        // render temp (unsaved) boxes first
        const temp = tempBoxes[page] || [];
        temp.forEach(t => {
            createSignatureBox({ id: t.id, left: t.left, top: t.top, width: t.width, height: t.height, page: page, locked: false });
        });
        // render saved boxes (locked)
        savedSignatures.filter(s => s.page === page).forEach(s => {
            createSignatureBox({ id: s.id, left: s.x, top: s.y, width: s.width, height: s.height, page: page, locked: true });
        });
    }

    function updatePosition(x, y) {
        const px = document.getElementById('positionX');
        const py = document.getElementById('positionY');
        if (px) px.textContent = Math.round(x);
        if (py) py.textContent = Math.round(y);
    }

    pdfjsLib.getDocument(url).promise.then(function(pdf) {
      pdfDoc = pdf;
      renderPage(pageNum);
      // render saved boxes for first page if any exist
      renderBoxesForPage(pageNum);
    });

    function prevPage() {
      if (pageNum <= 1) return;
      pageNum--;
      renderPage(pageNum);
    }

    function nextPage() {
      if (pageNum >= pdfDoc.numPages) return;
      pageNum++;
      renderPage(pageNum);
    }

        // Multi signature boxes: add, drag, resize, lock(save), delete
        const pdfPageEl = document.getElementById('pdfPage');
        const signatureTemplate = document.getElementById('signatureTemplate');
        const addSignatureBtn = document.getElementById('addSignatureBtn');

        // load saved signatures from server (if any)
        let savedSignatures = JSON.parse(@json($documentRoutingApproval->signature_position ?? '[]') );
        let tempBoxes = {}; // { pageNum: [ { id, left, top, width, height } ] }
        let activeBox = null;
        let dragState = { isDragging: false, isResizing: false, startX: 0, startY: 0, startLeft: 0, startTop: 0, startWidth: 0, startHeight: 0 };

        function createSignatureBox(opts = {}) {
            const box = signatureTemplate.cloneNode(true);
            const id = opts.id || `signatureBox_${Date.now()}`;
            box.id = id;
            box.classList.remove('template');
            box.classList.add('signature-box');
            box.style.display = 'block';
            box.style.position = 'absolute';
            box.style.left = (opts.left || 100) + 'px';
            box.style.top = (opts.top || 400) + 'px';
            box.style.width = (opts.width || 200) + 'px';
            box.style.height = (opts.height || 80) + 'px';
            const boxPage = opts.page || pageNum;
            box.dataset.page = boxPage;

            const lockBtn = box.querySelector('.lock-btn');
            const delBtn = box.querySelector('.delete-btn');
            const resizeHandle = box.querySelector('.resize-handle');

            lockBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                lockBox(box);
            });

            delBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                removeBox(box);
            });

            // pointer-down on box
            box.addEventListener('mousedown', function(e) {
                if (box.classList.contains('locked')) return;
                activeBox = box;
                if (e.target.classList.contains('resize-handle')) {
                    dragState.isResizing = true;
                    dragState.startWidth = box.offsetWidth;
                    dragState.startHeight = box.offsetHeight;
                } else {
                    dragState.isDragging = true;
                }
                dragState.startX = e.clientX;
                dragState.startY = e.clientY;
                dragState.startLeft = box.offsetLeft;
                dragState.startTop = box.offsetTop;
                e.preventDefault();
            });

            pdfPageEl.appendChild(box);
            updatePosition(parseInt(box.style.left), parseInt(box.style.top));

            // register in tempBoxes if not locked; update existing entry if present
            if (!opts.locked) {
                const pageKey = boxPage;
                tempBoxes[pageKey] = tempBoxes[pageKey] || [];
                const existingIdx = tempBoxes[pageKey].findIndex(t => t.id === id);
                const entry = { id, left: parseInt(box.style.left), top: parseInt(box.style.top), width: parseInt(box.style.width), height: parseInt(box.style.height) };
                if (existingIdx === -1) tempBoxes[pageKey].push(entry); else tempBoxes[pageKey][existingIdx] = entry;
            } else {
                // if locked, style accordingly
                box.classList.add('locked');
                if (resizeHandle) resizeHandle.style.display = 'none';
                if (lockBtn) { lockBtn.classList.add('bg-green-600','text-white'); lockBtn.title = 'Saved'; }
            }

            return box;
        }

        document.addEventListener('mousemove', function(e) {
            if (!activeBox) return;
            if (dragState.isDragging) {
                const deltaX = e.clientX - dragState.startX;
                const deltaY = e.clientY - dragState.startY;
                let newLeft = dragState.startLeft + deltaX;
                let newTop = dragState.startTop + deltaY;

                const maxLeft = pdfPageEl.offsetWidth - activeBox.offsetWidth;
                const maxTop = pdfPageEl.offsetHeight - activeBox.offsetHeight;

                newLeft = Math.max(0, Math.min(newLeft, maxLeft));
                newTop = Math.max(0, Math.min(newTop, maxTop));

                activeBox.style.left = newLeft + 'px';
                activeBox.style.top = newTop + 'px';
                updatePosition(newLeft, newTop);
            } else if (dragState.isResizing) {
                const deltaX = e.clientX - dragState.startX;
                const deltaY = e.clientY - dragState.startY;
                let newWidth = dragState.startWidth + deltaX;
                let newHeight = dragState.startHeight + deltaY;

                newWidth = Math.max(150, newWidth);
                newHeight = Math.max(60, newHeight);

                activeBox.style.width = newWidth + 'px';
                activeBox.style.height = newHeight + 'px';
            }
        });

        document.addEventListener('mouseup', function() {
            // update tempBoxes entry for activeBox when finished moving/resizing
            if (activeBox && !activeBox.classList.contains('locked')) {
                const pid = parseInt(activeBox.dataset.page || pageNum);
                const arr = tempBoxes[pid] || [];
                const idx = arr.findIndex(t => t.id === activeBox.id);
                if (idx !== -1) {
                    arr[idx].left = Math.round(activeBox.offsetLeft);
                    arr[idx].top = Math.round(activeBox.offsetTop);
                    arr[idx].width = Math.round(activeBox.offsetWidth);
                    arr[idx].height = Math.round(activeBox.offsetHeight);
                }
            }
            dragState.isDragging = false;
            dragState.isResizing = false;
            activeBox = null;
        });

        function lockBox(box) {
            const left = Math.round(box.offsetLeft);
            const top = Math.round(box.offsetTop);
            const width = Math.round(box.offsetWidth);
            const height = Math.round(box.offsetHeight);
            const entry = { id: box.id, page: pageNum, x: left, y: top, width, height };
            // avoid duplicates
            const existing = savedSignatures.find(s => s.id === box.id);
            if (!existing) savedSignatures.push(entry);
            // remove from tempBoxes if present
            const pid = entry.page;
            if (tempBoxes[pid]) tempBoxes[pid] = tempBoxes[pid].filter(t => t.id !== box.id);
            box.classList.add('locked');
            const resizeHandle = box.querySelector('.resize-handle');
            if (resizeHandle) resizeHandle.style.display = 'none';
            const lockBtn = box.querySelector('.lock-btn');
            if (lockBtn) {
                lockBtn.classList.add('bg-green-600','text-white');
                lockBtn.title = 'Saved';
            }
            updateSavedInfo();
        }

        function removeBox(box) {
            // remove from saved if exists
            savedSignatures = savedSignatures.filter(s => s.id !== box.id);
            // remove from tempBoxes if exists
            const pid = parseInt(box.dataset.page || pageNum);
            if (tempBoxes[pid]) tempBoxes[pid] = tempBoxes[pid].filter(t => t.id !== box.id);
            if (box && box.parentNode) box.parentNode.removeChild(box);
            updateSavedInfo();
        }

        function updateSavedInfo() {
            const signedPageEl = document.getElementById('signedPage');
            signedPageEl.textContent = savedSignatures.length ? savedSignatures[savedSignatures.length - 1].page : pageNum;
            // could update other UI or hidden inputs here for form submission
        }

        function resetSignatureTemplate() {
            signatureTemplate.style.left = '100px';
            signatureTemplate.style.top = '400px';
            signatureTemplate.style.width = '200px';
            signatureTemplate.style.height = '80px';
        }

        addSignatureBtn.addEventListener('click', function() {
                createSignatureBox({ page: pageNum });
            });

        // Updated saveSign to POST savedSignatures to controller
        async function saveSign() {
            if (!savedSignatures || savedSignatures.length === 0) {
                alert('Belum ada tanda tangan yang disimpan. Gunakan tombol centang pada box untuk menyimpan.');
                return;
            }
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            try {
                const resp = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ positions: savedSignatures })
                });

                const data = await resp.json().catch(() => ({}));
                if (resp.ok) {
                    alert(data.message || 'Positions stored successfully.');
                } else {
                    console.error('Store error', data);
                    alert(data.message || 'Gagal menyimpan posisi.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat mengirim data.');
            }
        }

        // Initialize
        resetSignatureTemplate();
  </script>

    {{-- <script>
        let currentPageNum = 1;
        const totalPagesNum = 5;

        const signatureBox = document.getElementById('signatureBox');
        const pdfPage = document.getElementById('pdfPage');
        let isDragging = false;
        let isResizing = false;
        let startX, startY, startLeft, startTop, startWidth, startHeight;

        signatureBox.addEventListener('mousedown', function(e) {
            if (e.target.id === 'resizeHandle') {
                isResizing = true;
                startWidth = signatureBox.offsetWidth;
                startHeight = signatureBox.offsetHeight;
            } else {
                isDragging = true;
            }
            startX = e.clientX;
            startY = e.clientY;
            startLeft = signatureBox.offsetLeft;
            startTop = signatureBox.offsetTop;
            e.preventDefault();
        });

        document.addEventListener('mousemove', function(e) {
            if (isDragging) {
                const deltaX = e.clientX - startX;
                const deltaY = e.clientY - startY;
                let newLeft = startLeft + deltaX;
                let newTop = startTop + deltaY;

                const maxLeft = pdfPage.offsetWidth - signatureBox.offsetWidth;
                const maxTop = pdfPage.offsetHeight - signatureBox.offsetHeight;
                
                newLeft = Math.max(0, Math.min(newLeft, maxLeft));
                newTop = Math.max(0, Math.min(newTop, maxTop));

                signatureBox.style.left = newLeft + 'px';
                signatureBox.style.top = newTop + 'px';
                
                updatePosition(newLeft, newTop);
            } else if (isResizing) {
                const deltaX = e.clientX - startX;
                const deltaY = e.clientY - startY;
                let newWidth = startWidth + deltaX;
                let newHeight = startHeight + deltaY;

                newWidth = Math.max(150, newWidth);
                newHeight = Math.max(60, newHeight);

                signatureBox.style.width = newWidth + 'px';
                signatureBox.style.height = newHeight + 'px';
            }
        });

        document.addEventListener('mouseup', function() {
            isDragging = false;
            isResizing = false;
        });

        function updatePosition(x, y) {
            document.getElementById('positionX').textContent = Math.round(x);
            document.getElementById('positionY').textContent = Math.round(y);
        }

        function resetSignature() {
            signatureBox.style.left = '100px';
            signatureBox.style.top = '400px';
            signatureBox.style.width = '200px';
            signatureBox.style.height = '80px';
            updatePosition(100, 400);
        }

        function updatePagination() {
            document.getElementById('currentPage').textContent = currentPageNum;
            document.getElementById('signedPage').textContent = currentPageNum;
            
            document.getElementById('prevBtn').disabled = currentPageNum === 1;
            document.getElementById('nextBtn').disabled = currentPageNum === totalPagesNum;
        }

        function prevPage() {
            if (currentPageNum > 1) {
                currentPageNum--;
                updatePagination();
                showPageChangeEffect('prev');
            }
        }

        function nextPage() {
            if (currentPageNum < totalPagesNum) {
                currentPageNum++;
                updatePagination();
                showPageChangeEffect('next');
            }
        }

        function showPageChangeEffect(direction) {
            const page = document.getElementById('pdfPage');
            page.style.opacity = '0.5';
            setTimeout(() => {
                page.style.opacity = '1';
            }, 150);
        }

        function cancelSign() {
            if (confirm('Apakah Anda yakin ingin membatalkan penandatanganan?')) {
                alert('Penandatanganan dibatalkan');
            }
        }

        function saveSign() {
            const x = Math.round(signatureBox.offsetLeft);
            const y = Math.round(signatureBox.offsetTop);
            const page = currentPageNum;
            
            alert(`Tanda tangan disimpan!\n\nHalaman: ${page}\nPosisi: (${x}, ${y})`);
        }

        // Initialize
        updatePosition(100, 400);
        updatePagination();
    </script> --}}
</x-app-layout>