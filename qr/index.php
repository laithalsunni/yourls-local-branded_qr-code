<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branded Local QR Code Generator</title>
    <script src="js/qrcode-svg.js"></script>
    <script src="js/jspdf.umd.min.js"></script>
    <script src="js/svg2pdf.min.js"></script>
    <script src="js/html2canvas.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 650px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
        h1 { margin-top: 0; color: #111; font-size: 24px; }
        #canvas-wrapper { margin: 25px auto; display: inline-block; background: #fff; padding: 15px; border: 1px solid #e1e4e6; border-radius: 6px; }
        .btn-group { display: flex; gap: 10px; justify-content: center; margin-bottom: 30px; }
        button { background: #0073aa; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 14px; transition: background 0.2s; }
        button:hover { background: #005177; }
        button.secondary { background: #e1e4e6; color: #333; }
        button.secondary:hover { background: #d1d4d6; }
        .admin-panel { margin-top: 40px; border-top: 2px dashed #e1e4e6; padding-top: 25px; text-align: left; }
        .admin-panel h3 { margin-top: 0; color: #444; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; }
        .form-group input[type="text"] { width: 120px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 14px; }
        .form-group input[type="file"] { font-size: 13px; }
        .preview-logo-thumb { max-height: 50px; display: block; margin-top: 8px; background: #f4f6f8; padding: 4px; border-radius: 4px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <h1>Branded QR Code Export</h1>
    <p style="color: #666; font-size: 14px;" id="target-url-text">Loading shortlink data...</p>

    <div id="canvas-wrapper">
        <canvas id="qrCanvas" width="500" height="500"></canvas>
    </div>

    <div class="btn-group">
        <button onclick="downloadPNG()">Download PNG</button>
        <button class="secondary" onclick="downloadPDF()">Save as PDF</button>
    </div>

    <div class="admin-panel">
        <h3>Branding Control Console</h3>
        <p style="color:#777; font-size:13px; margin-top:-10px; margin-bottom:20px;">Configure your custom asset profiles. Setting properties save locally via your browser session parameters.</p>
        
        <div class="form-group">
            <label>Upload Brand Logo overlay (PNG/JPG):</label>
            <input type="file" id="logoInput" accept="image/*" onchange="handleLogoUpload(event)">
            <img id="logoPreview" class="preview-logo-thumb" style="display:none;" />
        </div>
        
        <div class="form-group">
            <label>Matrix Body & Pupils Color (HEX):</label>
            #<input type="text" id="bodyColorInput" value="321F21" maxlength="6" oninput="applyBrandingChanges()">
        </div>
        
        <div class="form-group">
            <label>Outer Eye Frame Ring Color (HEX):</label>
            #<input type="text" id="eyeColorInput" value="A35E39" maxlength="6" oninput="applyBrandingChanges()">
        </div>
    </div>
</div>

<script>
    // Grab configurations from current execution string contextual lookups
    const urlParams = new URLSearchParams(window.location.search);
    const shortUrl = urlParams.get('content') || window.location.href;
    document.getElementById('target-url-text').innerText = "Short Link Target: " + shortUrl;

    // Load initialization parameters from browser session caches
    if(localStorage.getItem('qr_body_hex')) document.getElementById('bodyColorInput').value = localStorage.getItem('qr_body_hex');
    if(localStorage.getItem('qr_eye_hex')) document.getElementById('eyeColorInput').value = localStorage.getItem('qr_eye_hex');
    let savedLogoData = localStorage.getItem('qr_logo_base64') || '';
    if(savedLogoData) {
        const preview = document.getElementById('logoPreview');
        preview.src = savedLogoData;
        preview.style.display = 'block';
    }

    // Initialize compilation tasks
    window.onload = function() {
        renderBrandedQR();
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
        const bodyHex = "#" + document.getElementById('bodyColorInput').value;
        const eyeHex = "#" + document.getElementById('eyeColorInput').value;
        
        const canvas = document.getElementById('qrCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Force high density error tracking to support central graphics
        const qrcode = new QRCode({
            content: shortUrl,
            padding: 2,
            width: 256,
            height: 256,
            ecl: "H" 
        });

        const modules = qrcode.qrcode.modules;
        const moduleCount = modules.length;
        const cellSize = canvas.width / moduleCount;

        // Render standard functional modules mapping lines
        ctx.fillStyle = bodyHex;
        for (let row = 0; row < moduleCount; row++) {
            for (let col = 0; col < moduleCount; col++) {
                if (modules[row][col]) {
                    // Skip layout coordinates calculation properties inside the tracking corner eye regions
                    if ((row < 7 && col < 7) || (row < 7 && col >= moduleCount - 7) || (row >= moduleCount - 7 && col < 7)) {
                        continue;
                    }
                    // Skip execution center bounds to clear space layout overlays for center branding blocks
                    const centerStart = Math.floor(moduleCount * 0.38);
                    const centerEnd = Math.ceil(moduleCount * 0.62);
                    if (row >= centerStart && row < centerEnd && col >= centerStart && col < centerEnd) {
                        continue;
                    }
                    
                    // Render smooth data modules matching the rounded aesthetic of the logo
                    ctx.beginPath();
                    ctx.arc((col * cellSize) + (cellSize / 2), (row * cellSize) + (cellSize / 2), (cellSize / 2) * 0.85, 0, 2 * Math.PI);
                    ctx.fill();
                }
            }
        }

        // Draw Tracking Corner Eyes with Custom Color Mapping
        const eyeCoordinates = [
            { x: 0, y: 0 },                                  // Top Left
            { x: (moduleCount - 7) * cellSize, y: 0 },       // Top Right
            { x: 0, y: (moduleCount - 7) * cellSize }        // Bottom Left
        ];

        eyeCoordinates.forEach(pos => {
            // Draw Outer Ring Frame
            ctx.fillStyle = eyeHex;
            ctx.beginPath();
            ctx.roundRect(pos.x, pos.y, 7 * cellSize, 7 * cellSize, cellSize * 1.5);
            ctx.fill();

            // Internal Isolation Knockout Box Area
            ctx.fillStyle = "#FFFFFF";
            ctx.beginPath();
            ctx.roundRect(pos.x + cellSize, pos.y + cellSize, 5 * cellSize, 5 * cellSize, cellSize * 0.8);
            ctx.fill();

            // Core Tracking Pupil Solid Square Box Area
            ctx.fillStyle = bodyHex;
            ctx.beginPath();
            ctx.roundRect(pos.x + (2 * cellSize), pos.y + (2 * cellSize), 3 * cellSize, 3 * cellSize, cellSize * 0.4);
            ctx.fill();
        });

        // Inject Brand Logo Center Mark Layer
        if (savedLogoData) {
            const logoImg = new Image();
            logoImg.src = savedLogoData;
            logoImg.onload = function() {
                const targetSize = canvas.width * 0.22;
                const lx = (canvas.width - targetSize) / 2;
                const ly = (canvas.height - targetSize) / 2;

                // Clear background box safely underneath image mapping layers
                ctx.fillStyle = "#FFFFFF";
                ctx.beginPath();
                ctx.roundRect(lx - 4, ly - 4, targetSize + 8, targetSize + 8, 6);
                ctx.fill();

                ctx.drawImage(logoImg, lx, ly, targetSize, targetSize);
            };
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
