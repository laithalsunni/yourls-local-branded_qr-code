<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branded Local QR Code Generator</title>
    
    <script src="js/qrcode.min.js"></script>
    <script src="js/jspdf.umd.min.js"></script>
    <script src="js/html2canvas.min.js"></script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 650px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
        h1 { margin-top: 0; color: #111; font-size: 24px; margin-bottom: 5px; }
        .section-desc { color: #666; font-size: 14px; margin-top: 0; margin-bottom: 20px; }
        #debug-log { background: #e2f0d9; color: #385723; padding: 10px; border-radius: 4px; margin: 15px 0; font-size: 13px; font-family: monospace; border: 1px solid #c5e0b4; }
        #canvas-wrapper { margin: 25px auto; display: inline-block; background: #fff; padding: 15px; border: 1px solid #e1e4e6; border-radius: 6px; min-height: 500px; min-width: 500px; }
        .btn-group { display: flex; gap: 10px; justify-content: center; margin-bottom: 30px; }
        button { background: #0073aa; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 14px; transition: background 0.2s; }
        button:hover { background: #005177; }
        button.secondary { background: #e1e4e6; color: #333; }
        button.secondary:hover { background: #d1d4d6; }
        button.success { background: #46b450; border-bottom: 3px solid #239230; font-size: 16px; padding: 12px 28px; margin-bottom: 10px; }
        button.success:hover { background: #2e9b3d; }
        
        .admin-panel { margin-top: 40px; border-top: 2px dashed #e1e4e6; padding-top: 25px; text-align: left; }
        .admin-panel h2 { margin-top: 0; color: #111; font-size: 20px; margin-bottom: 5px; }
        .sub-section { background: #fdfdfd; border: 1px solid #eaeaea; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        .sub-section h4 { margin-top: 0; color: #222; font-size: 15px; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sub-section .sub-desc { color: #777; font-size: 13px; margin-top: 0; margin-bottom: 15px; line-height: 1.4; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; color: #444; }
        .color-input-wrapper { display: flex; align-items: center; gap: 8px; }
        .form-group input[type="text"] { width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 14px; text-transform: uppercase; }
        .form-group input[type="color"] { border: none; padding: 0; width: 36px; height: 36px; border-radius: 4px; cursor: pointer; background: none; }
        .preview-logo-thumb { max-height: 60px; display: block; margin-top: 12px; background: #f4f6f8; padding: 6px; border-radius: 4px; border: 1px solid #ddd; }
        .toggle-container { margin-top: 15px; background: #f0f4f8; padding: 10px 12px; border-radius: 4px; border: 1px solid #d0dbe5; }
        .toggle-container label { font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #2c3e50; }
    </style>
</head>
<body>

<div class="container">
    <h1>Branded QR Code Export</h1>
    <p class="section-desc" id="target-url-text">Parsing tracking link payload...</p>
    
    <div id="debug-log">Status Log: Initializing localized script matrix...</div>

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
        <h2>Branding Control Console</h2>
        <p class="section-desc">Customize your tracking link's visual presentation using vector color schemes and graphics overrides below.</p>
        
        <div class="sub-section">
            <h4>1. Vector Palette Settings</h4>
            <p class="sub-desc">Adjust the color mapping profiles for the structural layers. Use the visual color box or type raw 6-character hexadecimal codes manually.</p>
            
            <div class="form-group">
                <label>Matrix Body & Pupils Color:</label>
                <div class="color-input-wrapper">
                    <input type="color" id="bodyColorPicker" value="#000000">
                    #<input type="text" id="bodyColorInput" value="000000" maxlength="6">
                </div>
            </div>
            
            <div class="form-group">
                <label>Outer Eye Frame Ring Color:</label>
                <div class="color-input-wrapper">
                    <input type="color" id="eyeColorPicker" value="#000000">
                    #<input type="text" id="eyeColorInput" value="000000" maxlength="6">
                </div>
            </div>
        </div>
        
        <div class="sub-section">
            <h4>2. Brand Logo Overlay</h4>
            <p class="sub-desc">Upload a high-resolution transparent PNG or clear JPG image asset to position straight inside the center of your data grid structure.</p>
            
            <div class="form-group">
                <label>Select Identity Graphic File:</label>
                <input type="file" id="logoInput" accept="image/*">
                <img id="logoPreview" class="preview-logo-thumb" style="display:none;" />
                
                <div class="toggle-container">
                    <label>
                        <input type="checkbox" id="autoColorToggle"> 
                        🎨 Auto-update colors matching the uploaded logo palette
                    </label>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
    const logEl = document.getElementById('debug-log');
    function log(msg) { logEl.innerText = "Status Log: " + msg; console.log(msg); }

    const urlParams = new URLSearchParams(window.location.search);
    let shortUrl = urlParams.get('url') || urlParams.get('content') || window.location.href;
    
    shortUrl = String(shortUrl);
    document.getElementById('target-url-text').innerText = "Short Link Target: " + shortUrl;

    if(localStorage.getItem('qr_body_hex')) {
        const savedBody = localStorage.getItem('qr_body_hex');
        document.getElementById('bodyColorInput').value = savedBody;
        document.getElementById('bodyColorPicker').value = "#" + savedBody;
    }
    if(localStorage.getItem('qr_eye_hex')) {
        const savedEye = localStorage.getItem('qr_eye_hex');
        document.getElementById('eyeColorInput').value = savedEye;
        document.getElementById('eyeColorPicker').value = "#" + savedEye;
    }
    
    let savedLogoData = localStorage.getItem('qr_logo_base64') || '';
    if(savedLogoData) {
        const preview = document.getElementById('logoPreview');
        preview.src = savedLogoData;
        preview.style.display = 'block';
    }

    document.getElementById('logoInput').addEventListener('change', handleLogoUpload);
    
    document.getElementById('bodyColorInput').addEventListener('input', (e) => handleTextColors(e.target.value, 'body'));
    document.getElementById('bodyColorPicker').addEventListener('input', (e) => handlePickerColors(e.target.value, 'body'));
    
    document.getElementById('eyeColorInput').addEventListener('input', (e) => handleTextColors(e.target.value, 'eye'));
    document.getElementById('eyeColorPicker').addEventListener('input', (e) => handlePickerColors(e.target.value, 'eye'));
    
    document.getElementById('generateBtn').addEventListener('click', renderBrandedQR);

    window.onload = function() {
        setTimeout(renderBrandedQR, 300);
    };

    function handleTextColors(val, type) {
        if(val.length === 6) {
            document.getElementById(type + 'ColorPicker').value = "#" + val;
            localStorage.setItem('qr_' + type + '_hex', val);
            renderBrandedQR();
        }
    }

    function handlePickerColors(val, type) {
        const directHex = val.replace('#', '').toUpperCase();
        document.getElementById(type + 'ColorInput').value = directHex;
        localStorage.setItem('qr_' + type + '_hex', directHex);
        renderBrandedQR();
    }

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
            
            if (document.getElementById('autoColorToggle').checked) {
                extractColorsFromLogo(savedLogoData);
            } else {
                renderBrandedQR();
            }
        };
        reader.readAsDataURL(file);
    }

    function extractColorsFromLogo(base64Img) {
        const img = new Image();
        img.src = base64Img;
        img.onload = function() {
            const sampleCanvas = document.createElement('canvas');
            const sampleCtx = sampleCanvas.getContext('2d');
            sampleCanvas.width = 50;
            sampleCanvas.height = 50;
            sampleCtx.drawImage(img, 0, 0, 50, 50);
            
            const imgData = sampleCtx.getImageData(0, 0, 50, 50).data;
            let colors = [];
            
            for (let i = 0; i < imgData.length; i += 16) {
                const r = imgData[i];
                const g = imgData[i+1];
                const b = imgData[i+2];
                const a = imgData[i+3];
                
                if (a > 200) { 
                    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
                    if (brightness < 240 && brightness > 15) {
                        colors.push({r, g, b});
                    }
                }
            }
            
            if (colors.length >= 2) {
                const toHex = (c) => [c.r, c.g, c.b].map(x => x.toString(16).padStart(2, '0')).join('').toUpperCase();
                
                const primaryColorHex = toHex(colors[0]);
                const secondaryColorHex = toHex(colors[Math.floor(colors.length / 2)]);
                
                document.getElementById('bodyColorInput').value = primaryColorHex;
                document.getElementById('bodyColorPicker').value = "#" + primaryColorHex;
                
                document.getElementById('eyeColorInput').value = secondaryColorHex;
                document.getElementById('eyeColorPicker').value = "#" + secondaryColorHex;
                
                localStorage.setItem('qr_body_hex', primaryColorHex);
                localStorage.setItem('qr_eye_hex', secondaryColorHex);
                log("🎨 Color palette successfully sampled from your logo image.");
            }
            
            renderBrandedQR();
        };
    }

    function renderBrandedQR() {
        log("Accessing verified local engine components...");
        const bodyHex = "#" + document.getElementById('bodyColorInput').value;
        const eyeHex = "#" + document.getElementById('eyeColorInput').value;
        
        const canvas = document.getElementById('qrCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (typeof QRCode === 'undefined') {
            log("❌ Local Dependency Error: 'js/qrcode.min.js' failed to parse correctly.");
            return;
        }

        try {
            const qrInstance = new QRCode({
                content: shortUrl,
                width: 500,
                height: 500,
                ecl: "H"
            });

            let modules = null;
            if (qrInstance.qrcode && qrInstance.qrcode.modules) {
                modules = qrInstance.qrcode.modules;
            } else if (qrInstance._oQRCode && qrInstance._oQRCode.modules) {
                modules = qrInstance._oQRCode.modules;
            }

            if (!modules) {
                log("❌ Matrix Extraction Error: Structure mapping incompatible.");
                return;
            }

            const moduleCount = modules.length;
            const cellSize = canvas.width / moduleCount;
            log("Data matrix compiled successfully (" + moduleCount + "x" + moduleCount + "). Drawing elements...");

            // 1. Paint rounded body data dots
            ctx.fillStyle = bodyHex;
            for (let row = 0; row < moduleCount; row++) {
                for (let col = 0; col < moduleCount; col++) {
                    if (modules[row][col]) {
                        if ((row < 7 && col < 7) || (row < 7 && col >= moduleCount - 7) || (row >= moduleCount - 7 && col < 7)) {
                            continue;
                        }
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

            // 2. Paint styled corner tracking eyes
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

            // 3. Layer the branding graphic into the center
            if (savedLogoData) {
                log("Overlaying brand logo assets...");
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
                    log("✔ Success: Custom branded QR code generated!");
                };
            } else {
                log("✔ Success: Custom vector QR code generated (Awaiting logo upload).");
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
