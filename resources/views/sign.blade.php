<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF 電子簽名</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900">
<main class="mx-auto max-w-6xl p-6 lg:p-10">
    <section class="mb-6 rounded-xl bg-white p-6 shadow-sm">
        <h1 class="mb-2 text-2xl font-bold">PDF 電子簽名工具</h1>
        <p class="text-sm text-slate-600">上傳 PDF → 手繪簽名 → 點選 PDF 放置簽名 → 下載簽好名的 PDF。</p>
    </section>

    <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <label class="mb-3 block text-sm font-semibold" for="pdf-input">1) 上傳 PDF 檔案</label>
            <input id="pdf-input" type="file" accept="application/pdf" class="mb-4 block w-full rounded-lg border border-slate-300 p-2 text-sm">

            <div id="pdf-wrapper" class="relative overflow-auto rounded-lg border border-slate-200 bg-slate-50 p-2">
                <canvas id="pdf-canvas" class="mx-auto block max-w-full rounded border border-slate-300"></canvas>
                <div id="signature-marker" class="pointer-events-none absolute hidden -translate-x-1/2 -translate-y-1/2 rounded bg-indigo-600 px-2 py-1 text-xs font-medium text-white">
                    簽名位置
                </div>
            </div>

            <p class="mt-3 text-xs text-slate-500">上傳後會顯示第一頁預覽，點擊預覽可設定簽名位置。</p>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-sm font-semibold">2) 滑鼠手繪簽名</h2>
            <canvas id="signature-canvas" width="420" height="220" class="w-full rounded-lg border border-slate-300 bg-white"></canvas>

            <div class="mt-4 flex flex-wrap gap-2">
                <button id="clear-signature" type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm hover:bg-slate-100">清除簽名</button>
                <button id="download-pdf" type="button" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">3) 下載簽名 PDF</button>
            </div>

            <p id="status" class="mt-3 text-xs text-slate-500">等待上傳檔案。</p>
        </div>
    </section>
</main>

<script type="module">
    import { PDFDocument } from 'https://esm.sh/pdf-lib@1.17.1';
    import * as pdfjsLib from 'https://esm.sh/pdfjs-dist@4.8.69';

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://esm.sh/pdfjs-dist@4.8.69/build/pdf.worker.mjs';

    const pdfInput = document.getElementById('pdf-input');
    const pdfCanvas = document.getElementById('pdf-canvas');
    const signatureCanvas = document.getElementById('signature-canvas');
    const signatureMarker = document.getElementById('signature-marker');
    const clearSignatureButton = document.getElementById('clear-signature');
    const downloadButton = document.getElementById('download-pdf');
    const statusLabel = document.getElementById('status');

    const pdfCtx = pdfCanvas.getContext('2d');
    const signatureCtx = signatureCanvas.getContext('2d');

    let uploadedPdfBytes = null;
    let selectedPlacement = null;
    let hasStroke = false;
    let isDrawing = false;

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

    const renderPdfPreview = async () => {
        const pdf = await pdfjsLib.getDocument({ data: uploadedPdfBytes }).promise;
        const page = await pdf.getPage(1);
        const viewport = page.getViewport({ scale: 1.3 });

        pdfCanvas.width = viewport.width;
        pdfCanvas.height = viewport.height;

        await page.render({ canvasContext: pdfCtx, viewport }).promise;

        selectedPlacement = {
            x: pdfCanvas.width * 0.72,
            y: pdfCanvas.height * 0.86,
        };

        signatureMarker.classList.remove('hidden');
        signatureMarker.style.left = `${selectedPlacement.x + pdfCanvas.offsetLeft}px`;
        signatureMarker.style.top = `${selectedPlacement.y + pdfCanvas.offsetTop}px`;

        setStatus('PDF 已讀取。請先簽名，再點預覽頁面選擇簽名位置。');
    };

    const placeSignature = (event) => {
        if (!uploadedPdfBytes) return;

        const rect = pdfCanvas.getBoundingClientRect();
        selectedPlacement = {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top,
        };

        signatureMarker.classList.remove('hidden');
        signatureMarker.style.left = `${selectedPlacement.x + pdfCanvas.offsetLeft}px`;
        signatureMarker.style.top = `${selectedPlacement.y + pdfCanvas.offsetTop}px`;
    };

    const exportSignedPdf = async () => {
        if (!uploadedPdfBytes) return setStatus('請先上傳 PDF。', true);
        if (!hasStroke) return setStatus('請先手繪簽名。', true);
        if (!selectedPlacement) return setStatus('請先點擊 PDF 設定簽名位置。', true);

        const doc = await PDFDocument.load(uploadedPdfBytes);
        const page = doc.getPage(0);
        const image = await doc.embedPng(signatureCanvas.toDataURL('image/png'));

        const scaleX = page.getWidth() / pdfCanvas.width;
        const scaleY = page.getHeight() / pdfCanvas.height;
        const signatureWidth = 180;
        const signatureHeight = signatureWidth * (signatureCanvas.height / signatureCanvas.width);

        const x = selectedPlacement.x * scaleX;
        const y = page.getHeight() - (selectedPlacement.y * scaleY) - signatureHeight;

        page.drawImage(image, {
            x,
            y: Math.max(0, y),
            width: signatureWidth,
            height: signatureHeight,
        });

        const bytes = await doc.save();
        const blob = new Blob([bytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = url;
        link.download = 'signed-document.pdf';
        link.click();

        URL.revokeObjectURL(url);
        setStatus('簽名完成，已下載 signed-document.pdf');
    };

    pdfInput.addEventListener('change', async (event) => {
        const [file] = event.target.files;
        if (!file) return;
        if (file.type !== 'application/pdf') {
            setStatus('請上傳 PDF 檔案。', true);
            return;
        }

        uploadedPdfBytes = await file.arrayBuffer();

        try {
            await renderPdfPreview();
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

    pdfCanvas.addEventListener('click', placeSignature);
    clearSignatureButton.addEventListener('click', () => {
        clearSignatureCanvas();
        setStatus('簽名畫布已清除。');
    });
    downloadButton.addEventListener('click', exportSignedPdf);

    clearSignatureCanvas();
</script>
</body>
</html>
