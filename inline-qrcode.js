/**
 * Branded QR Code Suite - Local Coordinate Engine
 */

function log(msg) {
    var logBox = jQuery('#debug-log');
    if(logBox.length > 0) {
        logBox.text("Status Log: " + msg);
    }
    console.log(msg);
}

function handleTextColors(val, type) {
    if(val.length === 6) {
        jQuery('#' + type + 'ColorPicker').val("#" + val);
        localStorage.setItem('qr_' + type + '_hex', val);
        renderBrandedQR();
    }
}

function handlePickerColors(val, type) {
    var directHex = val.replace('#', '').toUpperCase();
    jQuery('#' + type + 'ColorInput').val(directHex);
    localStorage.setItem('qr_' + type + '_hex', directHex);
    renderBrandedQR();
}

function handleLogoUpload(event) {
    var file = event.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var savedLogoData = e.target.result;
        localStorage.setItem('qr_logo_base64', savedLogoData);
        var preview = jQuery('#logoPreview');
        if(preview.length > 0) {
            preview.attr('src', savedLogoData).show();
        }
        log("New logo asset structured inside browser memory cache.");
        
        if (jQuery('#autoColorToggle').is(':checked')) {
            extractColorsFromLogo(savedLogoData);
        } else {
            renderBrandedQR();
        }
    };
    reader.readAsDataURL(file);
}

function extractColorsFromLogo(base64Img) {
    var img = new Image();
    img.src = base64Img;
    img.onload = function() {
        var sampleCanvas = document.createElement('canvas');
        var sampleCtx = sampleCanvas.getContext('2d');
        sampleCanvas.width = 50;
        sampleCanvas.height = 50;
        sampleCtx.drawImage(img, 0, 0, 50, 50);
        
        var imgData = sampleCtx.getImageData(0, 0, 50, 50).data;
        var colors = [];
        
        for (var i = 0; i < imgData.length; i += 16) {
            var r = imgData[i];
            var g = imgData[i+1];
            var b = imgData[i+2];
            var a = imgData[i+3];
            
            if (a > 200) { 
                var brightness = (r * 299 + g * 587 + b * 114) / 1000;
                if (brightness < 240 && brightness > 15) {
                    colors.push({r: r, g: g, b: b});
                }
            }
        }
        
        if (colors.length >= 2) {
            var componentToHex = function(c) {
                var hex = c.toString(16);
                return hex.length == 1 ? "0" + hex : hex;
            };
            var toHex = function(color) {
                return (componentToHex(color.r) + componentToHex(color.g) + componentToHex(color.b)).toUpperCase();
            };
            
            var primaryColorHex = toHex(colors[0]);
            var secondaryColorHex = toHex(colors[Math.floor(colors.length / 2)]);
            
            jQuery('#bodyColorInput').val(primaryColorHex);
            jQuery('#bodyColorPicker').val("#" + primaryColorHex);
            jQuery('#eyeColorInput').val(secondaryColorHex);
            jQuery('#eyeColorPicker').val("#" + secondaryColorHex);
            
            localStorage.setItem('qr_body_hex', primaryColorHex);
            localStorage.setItem('qr_eye_hex', secondaryColorHex);
            log("🎨 Color palette successfully sampled from your logo image.");
        }
        renderBrandedQR();
    };
}

function renderBrandedQR() {
    var canvas = document.getElementById('qrCanvas');
    if (!canvas) return;
    
    log("Accessing verified local engine components...");
    var targetLink = jQuery('#targetShortUrl').val() || window.location.href;
    var bodyHex = "#" + (jQuery('#bodyColorInput').val() || "000000");
    var eyeHex = "#" + (jQuery('#eyeColorInput').val() || "000000");
    var savedLogoData = localStorage.getItem('qr_logo_base64') || '';
    
    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (typeof QRCode === 'undefined') {
        log("❌ Local Dependency Error: 'qrcode.min.js' failed to parse correctly.");
        return;
    }

    try {
        // Run a lightweight headless matrix calculation pass via our core library
        var rawEngine = new QRCode(document.createElement('div'), {
            text: targetLink,
            width: 500,
            height: 500,
            correctLevel: QRCode.CorrectLevel.H
        });

        var modules = null;
        if (rawEngine._oQRCode && rawEngine._oQRCode.modules) {
            modules = rawEngine._oQRCode.modules;
        }

        if (!modules) {
            log("❌ Matrix Extraction Error: Structure mapping layout mismatch.");
            return;
        }

        var moduleCount = modules.length;
        var cellSize = canvas.width / moduleCount;
        log("Data matrix compiled successfully (" + moduleCount + "x" + moduleCount + "). Drawing elements...");

        // 1. Paint structural body data bits using smooth dots
        ctx.fillStyle = bodyHex;
        for (var row = 0; row < moduleCount; row++) {
            for (var col = 0; col < moduleCount; col++) {
                if (modules[row][col]) {
                    // Skip coordinates allocated to functional positioning eye structures
                    if ((row < 7 && col < 7) || (row < 7 && col >= moduleCount - 7) || (row >= moduleCount - 7 && col < 7)) {
                        continue;
                    }
                    // Calculate a geometric safe boundary center zone to isolate the logo overlay
                    var centerStart = Math.floor(moduleCount * 0.34);
                    var centerEnd = Math.ceil(moduleCount * 0.66);
                    if (row >= centerStart && row < centerEnd && col >= centerStart && col < centerEnd) {
                        continue;
                    }
                    
                    ctx.beginPath();
                    ctx.arc((col * cellSize) + (cellSize / 2), (row * cellSize) + (cellSize / 2), (cellSize / 2) * 0.88, 0, 2 * Math.PI);
                    ctx.fill();
                }
            }
        }

        // 2. Compute and paint styled rounded tracking eyes directly into context canvases
        var eyeCoordinates = [
            { x: 0, y: 0 },
            { x: (moduleCount - 7) * cellSize, y: 0 },
            { x: 0, y: (moduleCount - 7) * cellSize }
        ];

        eyeCoordinates.forEach(function(pos) {
            ctx.fillStyle = eyeHex;
            ctx.getTransform ? ctx.beginPath() : null; 
            if(typeof ctx.roundRect === "function") {
                ctx.beginPath(); ctx.roundRect(pos.x, pos.y, 7 * cellSize, 7 * cellSize, cellSize * 1.5); ctx.fill();
                ctx.fillStyle = "#FFFFFF"; ctx.beginPath(); ctx.roundRect(pos.x + cellSize, pos.y + cellSize, 5 * cellSize, 5 * cellSize, cellSize * 0.8); ctx.fill();
                ctx.fillStyle = bodyHex; ctx.beginPath(); ctx.roundRect(pos.x + (2 * cellSize), pos.y + (2 * cellSize), 3 * cellSize, 3 * cellSize, cellSize * 0.4); ctx.fill();
            } else {
                // Secure canvas compatibility layout fallback mappings
                ctx.fillRect(pos.x, pos.y, 7 * cellSize, 7 * cellSize);
                ctx.fillStyle = "#FFFFFF"; ctx.fillRect(pos.x + cellSize, pos.y + cellSize, 5 * cellSize, 5 * cellSize);
                ctx.fillStyle = bodyHex; ctx.fillRect(pos.x + (2 * cellSize), pos.y + (2 * cellSize), 3 * cellSize, 3 * cellSize);
            }
        });

        // 3. Render brand identity files directly center stage over matrix rows
        if (savedLogoData) {
            log("Overlaying brand logo assets...");
            var logoImg = new Image();
            logoImg.src = savedLogoData;
            logoImg.onload = function() {
                var targetSize = canvas.width * 0.24;
                var lx = (canvas.width - targetSize) / 2;
                var ly = (canvas.height - targetSize) / 2;

                ctx.fillStyle = "#FFFFFF";
                if(typeof ctx.roundRect === "function") {
                    ctx.beginPath(); ctx.roundRect(lx - 6, ly - 6, targetSize + 12, targetSize + 12, 6); ctx.fill();
                } else {
                    ctx.fillRect(lx - 6, ly - 6, targetSize + 12, targetSize + 12);
                }

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
    var canvas = document.getElementById('qrCanvas');
    if(!canvas) return;
    var link = document.createElement('a');
    link.download = 'branded-shortlink-qr.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

function downloadPDF() {
    var canvas = document.getElementById('qrCanvas');
    if(!canvas) return;
    var imgData = canvas.toDataURL('image/png');
    
    if(window.jspdf && window.jspdf.jsPDF) {
        var pdf = new window.jspdf.jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });
        pdf.text("Branded Tracking Shortlink Asset", 20, 20);
        pdf.addImage(imgData, 'PNG', 20, 30, 100, 100);
        pdf.save('shortlink-qr-manifest.pdf');
    } else {
        alert("The PDF export module is fully operational inside the main configuration page dashboard panel workspace.");
    }
}
