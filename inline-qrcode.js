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
        
        if (colors.length > 0) {
            colors.sort(function(a,b) {
                return (a.r+a.g+a.b) - (b.r+b.g+b.b);
            });
            
            var primary = colors[0];
            var secondary = colors[Math.floor(colors.length / 2)] || colors[0];
            
            function rgbToHex(c) {
                var hex = ((c.r << 16) | (c.g << 8) | c.b).toString(16).toUpperCase();
                return ("000000" + hex).slice(-6);
            }
            
            var primaryHex = rgbToHex(primary);
            var secondaryHex = rgbToHex(secondary);
            
            jQuery('#bodyColorInput').val(primaryHex);
            jQuery('#bodyColorPicker').val('#' + primaryHex);
            localStorage.setItem('qr_body_hex', primaryHex);
            
            jQuery('#eyeColorInput').val(secondaryHex);
            jQuery('#eyeColorPicker').val('#' + secondaryHex);
            localStorage.setItem('qr_eye_hex', secondaryHex);
            
            log("Palette matching matrix extracted successfully.");
        }
        renderBrandedQR();
    };
}

function renderBrandedQR() {
    var textPayload = jQuery('#targetShortUrl').val() || window.location.href;
    var canvas = document.getElementById('qrCanvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    
    var bodyHex = jQuery('#bodyColorInput').val() || "000000";
    var eyeHex = jQuery('#eyeColorInput').val() || "000000";
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    try {
        // Instantiate using standard engine format options safely (High Density Error Correction 'H')
        var qr = new QRCode(-1, 3);
        qr.addData(textPayload);
        qr.make();
        
        var count = qr.getModuleCount();
        var cellSize = canvas.width / count;
        
        // Draw standard data matrices
        for (var row = 0; row < count; row++) {
            for (var col = 0; col < count; col++) {
                
                // Skip alignment eye markers explicitly
                if ((row < 7 && col < 7) || (row < 7 && col >= count - 7) || (row >= count - 7 && col < 7)) {
                    continue; 
                }
                
                // Keep the absolute center clear of points to protect logo scans
                if (row >= Math.floor(count/2) - 3 && row <= Math.floor(count/2) + 3 &&
                    col >= Math.floor(count/2) - 3 && col <= Math.floor(count/2) + 3) {
                    continue;
                }
                
                if (qr.isDark(row, col)) {
                    ctx.fillStyle = "#" + bodyHex;
                    ctx.fillRect(Math.round(col * cellSize), Math.round(row * cellSize), Math.ceil(cellSize), Math.ceil(cellSize));
                }
            }
        }
        
        // Render alignment position loops manually
        var eyePositions = [
            { x: 0, y: 0 },
            { x: (count - 7) * cellSize, y: 0 },
            { x: 0, y: (count - 7) * cellSize }
        ];
        
        eyePositions.forEach(function(pos) {
            ctx.fillStyle = "#" + eyeHex;
            if (typeof ctx.roundRect === "function") {
                ctx.beginPath(); ctx.roundRect(pos.x, pos.y, 7 * cellSize, 7 * cellSize, cellSize * 1.5); ctx.fill();
                ctx.fillStyle = "#FFFFFF";
                ctx.beginPath(); ctx.roundRect(pos.x + cellSize, pos.y + cellSize, 5 * cellSize, 5 * cellSize, cellSize * 1.0); ctx.fill();
                ctx.fillStyle = "#" + bodyHex;
                ctx.beginPath(); ctx.roundRect(pos.x + 2 * cellSize, pos.y + 2 * cellSize, 3 * cellSize, 3 * cellSize, cellSize * 0.4); ctx.fill();
            } else {
                ctx.fillRect(pos.x, pos.y, 7 * cellSize, 7 * cellSize);
                ctx.fillStyle = "#FFFFFF";
                ctx.fillRect(pos.x + cellSize, pos.y + cellSize, 5 * cellSize, 5 * cellSize);
                ctx.fillStyle = "#" + bodyHex;
                ctx.fillRect(pos.x + 2 * cellSize, pos.y + 2 * cellSize, 3 * cellSize, 3 * cellSize);
            }
        });
        
        // Inject brand logo asset if cached locally
        var savedLogoData = localStorage.getItem('qr_logo_base64');
        if (savedLogoData) {
            var logoImg = new Image();
            logoImg.src = savedLogoData;
            logoImg.onload = function() {
                var targetSize = canvas.width * 0.18; 
                var lx = (canvas.width - targetSize) / 2;
                var ly = (canvas.height - targetSize) / 2;
                
                ctx.fillStyle = '#FFFFFF';
                if (typeof ctx.roundRect === "function") {
                    ctx.beginPath(); ctx.roundRect(lx - 10, ly - 10, targetSize + 20, targetSize + 20, 10); ctx.fill();
                } else {
                    ctx.fillRect(lx - 10, ly - 10, targetSize + 20, targetSize + 20);
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
    
    var libraryInstance = window.jspdf || window.jsPDF;
    if(libraryInstance && libraryInstance.jsPDF) {
        var pdf = new libraryInstance.jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });
        pdf.text("Branded Tracking Shortlink Asset", 20, 20);
        pdf.addImage(imgData, 'PNG', 20, 30, 170, 170);
        pdf.save('branded-shortlink-qr.pdf');
        log("✔ Print document pipeline finalized successfully.");
    } else {
        alert("PDF Generation Library is not available yet. Please check your admin configuration setup.");
        log("❌ PDF Generation Library instance verification failed.");
    }
}
