<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branded Local QR Code Generator</title>
    
    <script src="js/qrcode-svg.js"></script>
    <script src="js/jspdf.umd.min.js"></script>
    <script src="js/html2canvas.min.js"></script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 650px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
        h1 { margin-top: 0; color: #111; font-size: 24px; }
        #debug-log { background: #e2f0d9; color: #385723; padding: 10px; border-radius: 4px; margin: 10px 0; font-size: 13px; font-family: monospace; border: 1px solid #c5e0b4; }
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
    <div id="debug-log">Status Log: Initializing localized script matrix...</div>
    <p style="color: #666; font-size: 14px;" id="target-url-text">Parsing tracking link payload...</p>

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

<script>
    const logEl = document.getElementById('debug-log');
    function log(msg) { logEl.innerText = "Status Log: " + msg; console.log(msg); }

    // Read URL params passed down by YOURLS
    const urlParams = new URLSearchParams(window.location.search);
    let shortUrl = urlParams.get('url') || urlParams.get('content') || window.location.href;
    document.getElementById('target-url-text').innerText = "Short Link Target: " + shortUrl;

    // Local storage data recovery configurations
    if(localStorage.getItem('qr_body_hex')) document.getElementById('bodyColorInput').value = localStorage.getItem('qr_body_hex');
    if(localStorage.getItem('qr_eye_hex')) document.getElementById('eyeColorInput').value = localStorage.getItem('qr_eye_hex');
    
    let savedLogoData = localStorage.getItem('qr_logo_base64') || '';
    if(savedLogoData) {
        const preview = document.getElementById('logoPreview');
        preview.src = savedLogoData;
        preview.style.display = 'block';
    }

    // Assign UI interaction event bindings
    document.getElementById('logoInput').addEventListener('change', handleLogoUpload);
    document.getElementById('bodyColorInput').addEventListener('input', applyBrandingChanges);
    document.getElementById('eyeColorInput').addEventListener('input', applyBrandingChanges);
    document.getElementById('generateBtn').addEventListener('click', renderBrandedQR);

    window.onload = function() {
        setTimeout(renderBrandedQR, 300);
    };

    function handleLogoUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            savedLogoData = e.target.result;
            localStorage.setItem('qr_logo_base64', savedLogoData);
            const preview = document.getElementById('logoPreview');
            preview.src = savedLogoData;
            preview.style.display = 'block';
            log("New logo loaded into browser memory cache.");
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
        log("Accessing local generator components...");
        const bodyHex = "#" + document.getElementById('bodyColorInput').value;
        const eyeHex = "#" + document.getElementById('eyeColorInput').value;
        
        const canvas = document.getElementById('qrCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Fallback checks for the local alexkolodko library engine architecture
        if (typeof QRCode === 'undefined') {
            log("❌ Local Dependency Error: 'js/qrcode-svg.js' is missing or unreadable on your server folder structure.");
            return;
        }

        try {
            // Instantiate the internal model using alexkolodko's exact native parameters
            const qrInstance = new QRCode({
                content: shortUrl,
                padding: 0,
                width: 500,
                height: 500,
                ecl: "H"
            });

            // Read the binary map directly from the generated object modules
            const modules = qrInstance.qrcode.modules;
            const moduleCount = modules.length;
            const cellSize = canvas.width / moduleCount;
            log("Local data matrix compiled. Drawing grid layout...");

            // 1. Draw rounded body data dots
            ctx.fillStyle = bodyHex;
            for (let row = 0; row < moduleCount; row++) {
                for (let col = 0; col < moduleCount; col++) {
                    if (modules[row][col]) {
                        // Skip layout bounds of corner eye matrices
                        if ((row < 7 && col < 7) || (row < 7 && col >= moduleCount - 7) || (row >= moduleCount - 7 && col < 7)) {
                            continue;
                        }
                        // Skip canvas center coordinates window to allow logo spacing
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

            // 2. Draw styled position eyes custom colors
            const eyeCoordinates = [
                { x: 0, y: 0 },
                { x: (moduleCount - 7) * cellSize, y: 0 },
                { x: 0, y: (moduleCount - 7) * cellSize }
            ];

            eyeCoordinates.forEach(pos => {
                // Outer ring structural framing
                ctx.fillStyle = eyeHex;
                ctx.beginPath();
                ctx.roundRect(pos.x, pos.y, 7 * cellSize, 7 * cellSize, cellSize * 1.5);
                ctx.fill();

                // Clear mask square
                ctx.fillStyle = "#FFFFFF";
                ctx.beginPath();
                ctx.roundRect(pos.x + cellSize, pos.y + cellSize, 5 * cellSize, 5 * cellSize, cellSize * 0.8);
                ctx.fill();

                // Center tracking pupil
                ctx.fillStyle = bodyHex;
                ctx.beginPath();
                ctx.roundRect(pos.x + (2 * cellSize), pos.y + (2 * cellSize), 3 * cellSize, 3 * cellSize, cellSize * 0.4);
                ctx.fill();
            });

            // 3. Render brand icon assets overlay inside canvas center
            if (savedLogoData) {
                log("Overlaying custom image layers...");
                const logoImg = new Image();
                logoImg.src = savedLogoData;
                logoImg.onload = function() {
                    const targetSize = canvas.width * 0.24;
                    const lx = (canvas.width - targetSize) / 2;
                    const ly = (canvas.height - targetSize) / 2;

                    // Clean out a white backing box behind the image
                    ctx.fillStyle = "#FFFFFF";
                    ctx.beginPath();
                    ctx.roundRect(lx - 6, ly - 6, targetSize + 12, targetSize + 12, 6);
                    ctx.fill();

                    ctx.drawImage(logoImg, lx, ly, targetSize, targetSize);
                    log("✔ Success: QR code beautifully compiled locally.");
                };
            } else {
                log("✔ Success: QR code compiled locally (Waiting for logo attachment).");
            }

        } catch (err) {
            log("❌ Canvas Draw Failure: " + err.message);
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
