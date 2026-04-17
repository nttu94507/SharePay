<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF 電子簽名</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
<main class="mx-auto max-w-6xl p-6 lg:p-10">
    <section class="mb-6 rounded-xl bg-white p-6 shadow-sm">
        <h1 class="mb-2 text-2xl font-bold">PDF 電子簽名工具</h1>
        <p class="text-sm text-slate-600">上傳 PDF → 預覽全部頁面 → 開啟懸浮視窗簽名模板 → 複製簽名貼紙(可縮放) → 下載簽名後 PDF。</p>
    </section>

    <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <label class="mb-3 block text-sm font-semibold" for="pdf-input">1) 上傳 PDF 檔案</label>
            <input id="pdf-input" type="file" accept="application/pdf" class="mb-4 block w-full rounded-lg border border-slate-300 p-2 text-sm">

            <div id="pdf-wrapper" class="space-y-4 overflow-auto rounded-lg border border-slate-200 bg-slate-50 p-3"></div>
            <p class="mt-3 text-xs text-slate-500">點「新增簽名貼紙」後，再點任一頁放置。貼紙可拖曳、縮放、雙擊刪除。</p>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-sm font-semibold">2) 操作</h2>

            <div class="flex flex-wrap gap-2">
                <button id="open-sign-modal" type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm hover:bg-slate-100">開啟簽名模板</button>
                <button id="add-signature-stamp" type="button" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">新增簽名貼紙</button>
                <button id="download-pdf" type="button" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">下載簽名 PDF</button>
            </div>

            <p id="status" class="mt-3 text-xs text-slate-500">等待上傳檔案。</p>
        </div>
    </section>
</main>

<div id="sign-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4">
    <div class="w-full max-w-xl rounded-xl bg-white p-4 shadow-xl">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold">手繪簽名模板（懸浮視窗）</h3>
            <button id="close-sign-modal" type="button" class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">關閉</button>
        </div>
        <canvas id="signature-canvas" width="600" height="260" class="w-full rounded-lg border border-slate-300 bg-white"></canvas>
        <div class="mt-3 flex gap-2">
            <button id="clear-signature" type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm hover:bg-slate-100">清除模板</button>
        </div>
        <p class="mt-2 text-xs text-slate-500">先完成簽名模板，再點「新增簽名貼紙」放到 PDF。</p>
    </div>
</div>

<script type="module">
    import { PDFDocument } from 'https://esm.sh/pdf-lib@1.17.1';
    import * as pdfjsLib from 'https://esm.sh/pdfjs-dist@4.8.69';

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://esm.sh/pdfjs-dist@4.8.69/build/pdf.worker.mjs';

    const pdfInput = document.getElementById('pdf-input');
    const pdfWrapper = document.getElementById('pdf-wrapper');
    const openModalButton = document.getElementById('open-sign-modal');
    const closeModalButton = document.getElementById('close-sign-modal');
    const signModal = document.getElementById('sign-modal');
    const signatureCanvas = document.getElementById('signature-canvas');
    const clearSignatureButton = document.getElementById('clear-signature');
    const addStampButton = document.getElementById('add-signature-stamp');
    const downloadButton = document.getElementById('download-pdf');
    const statusLabel = document.getElementById('status');

    const signatureCtx = signatureCanvas.getContext('2d');

    let uploadedPdfBytes = null;
    let hasStroke = false;
    let isDrawing = false;
    let isPlacementMode = false;
    let stampIdCounter = 0;

    const pageCanvases = new Map();
    const stamps = [];

    const setStatus = (text, error = false) => {
        statusLabel.textContent = text;
        statusLabel.className = `mt-3 text-xs ${error ? 'text-red-600' : 'text-slate-500'}`;
    };

    const clearSignatureCanvas = () => {
        signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        signatureCtx.fillStyle = '#ffffff';
        signatureCtx.fillRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        signatureCtx.lineWidth = 2.5;
        signatureCtx.lineCap = 'round';
        signatureCtx.strokeStyle = '#0f172a';
        hasStroke = false;
    };

    const pointerToCanvas = (event, canvas) => {
        const rect = canvas.getBoundingClientRect();
        const source = event.touches ? event.touches[0] : event;
        return {
            x: ((source.clientX - rect.left) / rect.width) * canvas.width,
            y: ((source.clientY - rect.top) / rect.height) * canvas.height,
        };
    };

    const startDrawing = (event) => {
        event.preventDefault();
        isDrawing = true;
        const point = pointerToCanvas(event, signatureCanvas);
        signatureCtx.beginPath();
        signatureCtx.moveTo(point.x, point.y);
    };

    const draw = (event) => {
        if (!isDrawing) return;
        event.preventDefault();
        const point = pointerToCanvas(event, signatureCanvas);
        signatureCtx.lineTo(point.x, point.y);
        signatureCtx.stroke();
        hasStroke = true;
    };

    const stopDrawing = () => {
        isDrawing = false;
        signatureCtx.closePath();
    };

    const fitStampIntoLayer = (stamp) => {
        const layerW = stamp.pageLayer.clientWidth;
        const layerH = stamp.pageLayer.clientHeight;
        const minSize = 48;

        stamp.width = Math.max(minSize, Math.min(stamp.width, layerW));
        stamp.height = Math.max(minSize, Math.min(stamp.height, layerH));
        stamp.x = Math.max(0, Math.min(stamp.x, layerW - stamp.width));
        stamp.y = Math.max(0, Math.min(stamp.y, layerH - stamp.height));
    };

    const syncStampView = (stamp) => {
        fitStampIntoLayer(stamp);
        stamp.element.style.left = `${stamp.x}px`;
        stamp.element.style.top = `${stamp.y}px`;
        stamp.element.style.width = `${stamp.width}px`;
        stamp.element.style.height = `${stamp.height}px`;
    };

    const createStampElement = (stamp) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'absolute z-10';
        wrapper.dataset.stampId = String(stamp.id);

        const image = document.createElement('img');
        image.src = signatureCanvas.toDataURL('image/png');
        image.alt = 'signature stamp';
        image.className = 'block h-full w-full cursor-move rounded border border-indigo-300/70 bg-white/60 p-1 shadow';
        image.title = '拖曳可移動';

        const resizeHandle = document.createElement('button');
        resizeHandle.type = 'button';
        resizeHandle.className = 'absolute -bottom-2 -right-2 h-4 w-4 rounded-full border border-indigo-700 bg-indigo-500';
        resizeHandle.title = '拖曳縮放';

        wrapper.appendChild(image);
        wrapper.appendChild(resizeHandle);

        let dragging = false;
        let resizing = false;
        let dragOffsetX = 0;
        let dragOffsetY = 0;
        let startX = 0;
        let startY = 0;
        let startWidth = 0;
        let startHeight = 0;

        wrapper.addEventListener('pointerdown', (event) => {
            if (event.target === resizeHandle) return;
            event.preventDefault();
            dragging = true;
            const rect = wrapper.getBoundingClientRect();
            dragOffsetX = event.clientX - rect.left;
            dragOffsetY = event.clientY - rect.top;
            wrapper.setPointerCapture(event.pointerId);
        });

        wrapper.addEventListener('pointermove', (event) => {
            if (!dragging) return;
            const pageRect = stamp.pageLayer.getBoundingClientRect();
            stamp.x = event.clientX - pageRect.left - dragOffsetX;
            stamp.y = event.clientY - pageRect.top - dragOffsetY;
            syncStampView(stamp);
        });

        wrapper.addEventListener('pointerup', (event) => {
            dragging = false;
            if (wrapper.hasPointerCapture(event.pointerId)) {
                wrapper.releasePointerCapture(event.pointerId);
            }
        });

        resizeHandle.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();
            resizing = true;
            startX = event.clientX;
            startY = event.clientY;
            startWidth = stamp.width;
            startHeight = stamp.height;
            resizeHandle.setPointerCapture(event.pointerId);
        });

        resizeHandle.addEventListener('pointermove', (event) => {
            if (!resizing) return;
            const ratio = signatureCanvas.height / signatureCanvas.width;
            const delta = Math.max(event.clientX - startX, event.clientY - startY);
            stamp.width = startWidth + delta;
            stamp.height = Math.max(48, stamp.width * ratio);
            syncStampView(stamp);
        });

        resizeHandle.addEventListener('pointerup', (event) => {
            resizing = false;
            if (resizeHandle.hasPointerCapture(event.pointerId)) {
                resizeHandle.releasePointerCapture(event.pointerId);
            }
        });

        wrapper.addEventListener('dblclick', () => {
            const index = stamps.findIndex((item) => item.id === stamp.id);
            if (index !== -1) {
                stamps.splice(index, 1);
                wrapper.remove();
                setStatus('已刪除 1 個簽名貼紙。');
            }
        });

        stamp.pageLayer.appendChild(wrapper);
        stamp.element = wrapper;
        syncStampView(stamp);
    };

    const addStampAt = (pageNumber, pageLayer, x, y) => {
        const ratio = signatureCanvas.height / signatureCanvas.width;
        const targetWidth = Math.min(170, pageLayer.clientWidth);
        const targetHeight = targetWidth * ratio;

        const stamp = {
            id: ++stampIdCounter,
            pageNumber,
            pageLayer,
            x: x - targetWidth / 2,
            y: y - targetHeight / 2,
            width: targetWidth,
            height: targetHeight,
            element: null,
        };

        fitStampIntoLayer(stamp);
        stamps.push(stamp);
        createStampElement(stamp);
    };

    const buildPageContainer = (pageNumber, width, height) => {
        const wrapper = document.createElement('section');
        wrapper.className = 'rounded-lg border border-slate-300 bg-white p-2';

        const title = document.createElement('p');
        title.className = 'mb-2 text-xs font-semibold text-slate-500';
        title.textContent = `第 ${pageNumber} 頁`;

        const layer = document.createElement('div');
        layer.className = 'relative mx-auto overflow-hidden';
        layer.style.width = `${width}px`;
        layer.style.height = `${height}px`;

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        canvas.className = 'block max-w-full rounded border border-slate-300';

        layer.appendChild(canvas);
        wrapper.appendChild(title);
        wrapper.appendChild(layer);

        layer.addEventListener('click', (event) => {
            if (!isPlacementMode) return;
            if (!hasStroke) return setStatus('請先在懸浮視窗完成簽名模板。', true);

            const rect = layer.getBoundingClientRect();
            addStampAt(pageNumber, layer, event.clientX - rect.left, event.clientY - rect.top);
            isPlacementMode = false;
            setStatus('已新增 1 個簽名貼紙。需要更多請再按一次「新增簽名貼紙」。');
        });

        return { wrapper, canvas };
    };

    const renderAllPages = async () => {
        pdfWrapper.innerHTML = '';
        stamps.splice(0, stamps.length);
        pageCanvases.clear();

        const pdf = await pdfjsLib.getDocument({ data: uploadedPdfBytes.slice() }).promise;

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
            const page = await pdf.getPage(pageNumber);
            const viewport = page.getViewport({ scale: 1.25 });
            const { wrapper, canvas } = buildPageContainer(pageNumber, viewport.width, viewport.height);
            const ctx = canvas.getContext('2d');

            await page.render({ canvasContext: ctx, viewport }).promise;

            pageCanvases.set(pageNumber, canvas);
            pdfWrapper.appendChild(wrapper);
        }

        setStatus(`PDF 載入完成，共 ${pdf.numPages} 頁。`);
    };

    const exportSignedPdf = async () => {
        if (!uploadedPdfBytes) return setStatus('請先上傳 PDF。', true);
        if (!hasStroke) return setStatus('請先建立簽名模板。', true);
        if (stamps.length === 0) return setStatus('尚未放置任何簽名貼紙。', true);

        const doc = await PDFDocument.load(uploadedPdfBytes.slice());
        const embeddedImage = await doc.embedPng(signatureCanvas.toDataURL('image/png'));

        for (const stamp of stamps) {
            const page = doc.getPage(stamp.pageNumber - 1);
            const canvas = pageCanvases.get(stamp.pageNumber);
            if (!page || !canvas) continue;

            const scaleX = page.getWidth() / canvas.width;
            const scaleY = page.getHeight() / canvas.height;
            const x = stamp.x * scaleX;
            const y = page.getHeight() - ((stamp.y + stamp.height) * scaleY);

            page.drawImage(embeddedImage, {
                x,
                y: Math.max(0, y),
                width: stamp.width * scaleX,
                height: stamp.height * scaleY,
            });
        }

        const bytes = await doc.save();
        const blob = new Blob([bytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = url;
        link.download = 'signed-document.pdf';
        link.click();

        URL.revokeObjectURL(url);
        setStatus(`完成：已輸出 signed-document.pdf（共 ${stamps.length} 個簽名貼紙）。`);
    };

    openModalButton.addEventListener('click', () => {
        signModal.classList.remove('hidden');
        signModal.classList.add('flex');
    });

    closeModalButton.addEventListener('click', () => {
        signModal.classList.add('hidden');
        signModal.classList.remove('flex');
    });

    signModal.addEventListener('click', (event) => {
        if (event.target === signModal) {
            signModal.classList.add('hidden');
            signModal.classList.remove('flex');
        }
    });

    pdfInput.addEventListener('change', async (event) => {
        const [file] = event.target.files;
        if (!file) return;
        if (file.type !== 'application/pdf') return setStatus('請上傳 PDF 檔案。', true);

        uploadedPdfBytes = new Uint8Array(await file.arrayBuffer());
        isPlacementMode = false;

        try {
            await renderAllPages();
        } catch (error) {
            console.error(error);
            setStatus('讀取 PDF 失敗，請確認檔案是否正常。', true);
        }
    });

    signatureCanvas.addEventListener('mousedown', startDrawing);
    signatureCanvas.addEventListener('mousemove', draw);
    signatureCanvas.addEventListener('mouseup', stopDrawing);
    signatureCanvas.addEventListener('mouseleave', stopDrawing);
    signatureCanvas.addEventListener('touchstart', startDrawing, { passive: false });
    signatureCanvas.addEventListener('touchmove', draw, { passive: false });
    signatureCanvas.addEventListener('touchend', stopDrawing);

    clearSignatureButton.addEventListener('click', () => {
        clearSignatureCanvas();
        setStatus('簽名模板已清除。');
    });

    addStampButton.addEventListener('click', () => {
        if (!uploadedPdfBytes) return setStatus('請先上傳 PDF。', true);
        if (!hasStroke) return setStatus('請先建立簽名模板。', true);
        isPlacementMode = true;
        setStatus('請點擊任一頁面放置簽名貼紙。');
    });

    downloadButton.addEventListener('click', exportSignedPdf);

    clearSignatureCanvas();
</script>
</body>
</html>
