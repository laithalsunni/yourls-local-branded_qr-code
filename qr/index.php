<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branded Local QR Code Generator</title>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode2/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 650px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
        h1 { margin-top: 0; color: #111; font-size: 24px; }
        #debug-log { background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin: 10px 0; font-size: 13px; font-family: monospace; border: 1px solid #ffeeba; }
        #canvas-wrapper { margin: 25px auto; display: inline-block; background: #fff; padding: 15px; border: 1px solid #e1e4e6; border-radius: 6px; min-height: 500px; min-width: 500px; }
        .btn-group { display: flex; gap: 10px; justify-content: center; margin-bottom: 30px; }
        button { background: #0073aa; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 14px; transition: background 0.2s; }
        button:hover { background: #005177; }
        button.secondary { background: #e1e4e6; color: #333; }
        button.secondary:hover { background: #d1d4d6; }
        button.success { background: #46b450; border-bottom: 3px solid #239230; font-size: 16px; padding: 12px 28px; margin-bottom: 10px; }
        button.success:hover { background: #2e9b3d; }
        .admin-panel { margin-top: 40px; border-top: 2px dashed #e1e4e6; padding-top: 25px; text-align: left; }
        .admin-panel h3 { margin-top: 0; color: #444; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; }
        .form-group input[type="text"] { width: 120px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 14px; }
        .preview-logo-thumb { max-height: 60px; display: block; margin-top: 8px; background: #f4f6f8; padding: 6px; border-radius: 4px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <h1>Branded QR Code Export</h1>
    <div id="debug-log">Status Log: Initializing systems...</div>
    <p style="color: #666; font-size: 14px;" id="target-url-text">Parsing link payload...</p>

    <div id="canvas-wrapper">
        <canvas id="qrCanvas" width="500" height="500"></canvas>
    </div>

    <div>
        <button class="success" id="generateBtn">⚡ Generate / Refresh QR Code</button>
    </div>

    <div class="btn-group">
        <button onclick="downloadPNG()">Download PNG</button>
        <button class="secondary" onclick="downloadPDF()">Save as PDF</button>
    </div>

    <div class="admin-panel">
        <h3>Branding Control Console</h3>
        <p style="color:#777; font-size:13px; margin-top:-10px; margin-bottom:20px;">Upload elements and instantly tune custom hex attributes below.</p>
        
        <div class="form-group">
            <label>Upload Brand Logo overlay (PNG/JPG):</label>
            <input type="file" id="logoInput" accept="image/*">
            <img id="logoPreview" class="preview-logo-thumb" style="display:none;" />
        </div>
        
        <div class="form-group">
            <label>Matrix Body & Pupils Color (HEX):</label>
            #<input type="text" id="bodyColorInput" value="321F21" maxlength="6">
        </div>
        
        <div class="form-group">
            <label>Outer Eye Frame Ring Color (HEX):</label>
            #<input type="text" id="eyeColorInput" value="A35E39" maxlength="6">
        </div>
    </div>
</div>

<div id="hidden-qr-buffer" style="display:none;"></div>

<script>
    const logEl = document.getElementById('debug-log');
    function log(msg) { logEl.innerText = "Status Log: " + msg; console.log(msg); }

    // Parse URL targets cleanly
    const urlParams = new URLSearchParams(window.location.search);
    let shortUrl = urlParams.get('url') || urlParams.get('content');
    
    if(!shortUrl) {
        shortUrl = window.location.href;
        log("Warning: No target URL parameter found. Using fallback page location.");
    }
    document.getElementById('target-url-text').innerText = "Short Link Target: " + shortUrl;

    // Local storage restoration bindings
    if(localStorage.getItem('qr_body_hex')) document.getElementById('bodyColorInput').value = localStorage.getItem('qr_body_hex');
    if(localStorage.getItem('qr_eye_hex')) document.getElementById('eyeColorInput').value = localStorage.getItem('qr_eye_hex');
    
    let savedLogoData = localStorage.getItem('qr_logo_base64') || '';
    if(savedLogoData) {
        const preview = document.getElementById('logoPreview');
        preview.src = savedLogoData;
        preview.style.display = 'block';
        log("Stored branding logo recovered from session memory cache.");
    }

    // Explicit event handling mappings
    document.getElementById('logoInput').addEventListener('change', handleLogoUpload);
    document.getElementById('bodyColorInput').addEventListener('input', applyBrandingChanges);
    document.getElementById('eyeColorInput').addEventListener('input', applyBrandingChanges);
    document.getElementById('generateBtn').addEventListener('click', renderBrandedQR);

    window.onload = function() {
        setTimeout(renderBrandedQR, 300);
    };

    function handleLogoUpload(event) {
        log("Processing logo graphic ingestion...");
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            savedLogoData = e.target.result;
            localStorage.setItem('qr_logo_base64', savedLogoData);
            const preview = document.getElementById('logoPreview');
            preview.src = savedLogoData;
            preview.style.display = 'block';
            log("Logo ready. Compiling workspace...");
            renderBrandedQR();
        };
        reader.readAsDataURL(file);
    }

    function applyBrandingChanges() {
        localStorage.setItem('qr_body_hex', document.getElementById('bodyColorInput').value);
        localStorage.setItem('qr_eye_hex', document.getElementById('eyeColorInput').value);
        renderBrandedQR();
    }

    function renderBrandedQR() {
        log("Compiling engine canvas layers...");
        const bodyHex = "#" + document.getElementById('bodyColorInput').value;
        const eyeHex = "#" + document.getElementById('eyeColorInput').value;
        
        const canvas = document.getElementById('qrCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Verify core compilation engine availability state blocks
        if (typeof QRCode === 'undefined') {
            log("❌ Critical Engine Error: The core QRCode parsing library failed to load. Check your internet connection.");
            return;
        }

        const buffer = document.getElementById('hidden-qr-buffer');
        buffer.innerHTML = '';
        
        try {
            // Render basic matrix lookup structures
            const qrEngine = new QRCode(buffer, {
                text: shortUrl,
                width: 256,
                height: 256,
                correctLevel: QRCode.CorrectLevel.H // High error density lock
            });

            // Extract binary modules coordinates natively from engine model attributes
            const modules = qrEngine._oQRCode.modules;
            const moduleCount = modules.length;
            const cellSize = canvas.width / moduleCount;
            log("Matrix calculations resolved (" + moduleCount + "x" + moduleCount + " grid). Drawing modules...");

            // Paint standard body tracking dots
            ctx.fillStyle = bodyHex;
            for (let row = 0; row < moduleCount; row++) {
                for (let col = 0; col < moduleCount; col++) {
                    if (modules[row][col]) {
                        // Isolate tracking corners bounds mapping arrays
                        if ((row < 7 && col < 7) || (row < 7 && col >= moduleCount - 7) || (row >= moduleCount - 7 && col < 7)) {
                            continue;
                        }
                        // Create space window for central image badges
                        const centerStart = Math.floor(moduleCount * 0.36);
                        const centerEnd = Math.ceil(moduleCount * 0.64);
                        if (row >= centerStart && row < centerEnd && col >= centerStart && col < centerEnd) {
                            continue;
                        }
                        
                        ctx.beginPath();
                        ctx.arc((col * cellSize) + (cellSize / 2), (row * cellSize) + (cellSize / 2), (cellSize / 2) * 0.85, 0, 2 * Math.PI);
                        ctx.fill();
                    }
                }
            }

            // Draw customized positional tracking eyes
            const eyeCoordinates = [
                { x: 0, y: 0 },
                { x: (moduleCount - 7) * cellSize, y: 0 },
                { x: 0, y: (moduleCount - 7) * cellSize }
            ];

            eyeCoordinates.forEach(pos => {
                ctx.fillStyle = eyeHex;
                ctx.beginPath();
                ctx.roundRect(pos.x, pos.y, 7 * cellSize, 7 * cellSize, cellSize * 1.5);
                ctx.fill();

                ctx.fillStyle = "#FFFFFF";
                ctx.beginPath();
                ctx.roundRect(pos.x + cellSize, pos.y + cellSize, 5 * cellSize, 5 * cellSize, cellSize * 0.8);
                ctx.fill();

                ctx.fillStyle = bodyHex;
                ctx.beginPath();
                ctx.roundRect(pos.x + (2 * cellSize), pos.y + (2 * cellSize), 3 * cellSize, 3 * cellSize, cellSize * 0.4);
                ctx.fill();
            });

            // Mount logo layer assets
            if (savedLogoData) {
                log("Overlaying logo layers...");
                const logoImg = new Image();
                logoImg.src = savedLogoData;
                logoImg.onload = function() {
                    const targetSize = canvas.width * 0.24;
                    const lx = (canvas.width - targetSize) / 2;
                    const ly = (canvas.height - targetSize) / 2;

                    ctx.fillStyle = "#FFFFFF";
                    ctx.beginPath();
                    ctx.roundRect(lx - 6, ly - 6, targetSize + 12, targetSize + 12, 6);
                    ctx.fill();

                    ctx.drawImage(logoImg, lx, ly, targetSize, targetSize);
                    log("✔ Complete: Branded asset generated successfully!");
                };
            } else {
                log("✔ Complete: Vector asset generated successfully (No logo attached).");
            }

        } catch (err) {
            log("❌ Structural Parsing Exception: " + err.message);
        }
    }

    function downloadPNG() {
        const canvas = document.getElementById('qrCanvas');
        const link = document.createElement('a');
        link.download = 'branded-shortlink-qr.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const canvas = document.getElementById('qrCanvas');
        const imgData = canvas.toDataURL('image/png');
        
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });

        pdf.text("Branded Tracking Shortlink Asset", 20, 20);
        pdf.addImage(imgData, 'PNG', 20, 30, 100, 100);
        pdf.save('shortlink-qr-manifest.pdf');
    }
</script>

</body>
</html>
