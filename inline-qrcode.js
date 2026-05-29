/**
 * Branded QR Code Suite - Local Coordinate Engine
 * Fixed: correct QRCode library API (typeNumber + errorLevel)
 */

// Polyfill for CanvasRenderingContext2D.roundRect
if (!CanvasRenderingContext2D.prototype.roundRect) {
    CanvasRenderingContext2D.prototype.roundRect = function(x, y, w, h, r) {
        if (w < 2 * r) r = w / 2;
        if (h < 2 * r) r = h / 2;
        this.moveTo(x + r, y);
        this.lineTo(x + w - r, y);
        this.quadraticCurveTo(x + w, y, x + w, y + r);
        this.lineTo(x + w, y + h - r);
        this.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        this.lineTo(x + r, y + h);
        this.quadraticCurveTo(x, y + h, x, y + h - r);
        this.lineTo(x, y + r);
        this.quadraticCurveTo(x, y, x + r, y);
        return this;
    };
}

function log(msg) {
    var logBox = jQuery('#debug-log');
    if (logBox.length) logBox.text("Status: " + msg);
    console.log(msg);
}

function handleTextColors(val, type) {
    val = val.replace('#', '');
    if (val.length === 6) {
        jQuery('#' + type + 'ColorPicker').val("#" + val);
        localStorage.setItem('qr_' + type + '_hex', val);
        renderBrandedQR();
    }
}

function handlePickerColors(val, type) {
    var hex = val.replace('#', '').toUpperCase();
    jQuery('#' + type + 'ColorInput').val(hex);
    localStorage.setItem('qr_' + type + '_hex', hex);
    renderBrandedQR();
}

function handleLogoUpload(event) {
    var file = event.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var logoData = e.target.result;
        localStorage.setItem('qr_logo_base64', logoData);
        jQuery('#logoPreview').attr('src', logoData).show();
        log("Logo loaded into memory.");

        if (jQuery('#autoColorToggle').is(':checked')) {
            extractColorsFromLogo(logoData);
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
        var canvas = document.createElement('canvas');
        canvas.width = 50;
        canvas.height = 50;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, 50, 50);
        var data = ctx.getImageData(0, 0, 50, 50).data;
        var colorMap = {};
        for (var i = 0; i < data.length; i += 4) {
            var r = data[i], g = data[i+1], b = data[i+2], a = data[i+3];
            if (a < 200) continue;
            var brightness = (r*299 + g*587 + b*114) / 1000;
            if (brightness < 240 && brightness > 15) {
                var hex = ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
                colorMap[hex] = (colorMap[hex] || 0) + 1;
            }
        }
        var sorted = Object.keys(colorMap).sort(function(a,b){ return colorMap[b]-colorMap[a]; });
        if (sorted.length >= 2) {
            var primary = sorted[0];
            var secondary = sorted[1];
            jQuery('#bodyColorInput').val(primary);
            jQuery('#bodyColorPicker').val("#" + primary);
            jQuery('#eyeColorInput').val(secondary);
            jQuery('#eyeColorPicker').val("#" + secondary);
            localStorage.setItem('qr_body_hex', primary);
            localStorage.setItem('qr_eye_hex', secondary);
            log("Auto colors extracted: body=" + primary + ", eye=" + secondary);
        } else if (sorted.length === 1) {
            jQuery('#bodyColorInput').val(sorted[0]);
            jQuery('#bodyColorPicker').val("#" + sorted[0]);
            localStorage.setItem('qr_body_hex', sorted[0]);
            log("Auto color extracted (only one dominant)");
        }
        renderBrandedQR();
    };
}

function renderBrandedQR() {
    var canvas = document.getElementById('qrCanvas');
    if (!canvas) return;
    var targetLink = jQuery('#targetShortUrl').val() || window.location.href;
    var bodyHex = "#" + (jQuery('#bodyColorInput').val() || "000000");
    var eyeHex = "#" + (jQuery('#eyeColorInput').val() || "000000");
    var savedLogo = localStorage.getItem('qr_logo_base64');

    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    if (typeof QRCode === 'undefined') {
        log("QRCode library missing");
        return;
    }

    try {
        // --- Correct QR code generation using the library's API ---
        // TypeNumber = 0 (auto select), ErrorCorrectLevel = 2 (H - high)
        var qr = new QRCode(0, 2);
        qr.addData(targetLink);
        qr.make();
        
        var size = qr.getModuleCount();
        if (!size) throw new Error("Cannot extract QR matrix");
        
        var cell = canvas.width / size;
        log("Drawing " + size + "x" + size + " modules");

        // --- Draw data modules (skip eyes & center area) ---
        var centerStart = Math.floor(size * 0.34);
        var centerEnd = Math.ceil(size * 0.66);
        ctx.fillStyle = bodyHex;
        for (var row = 0; row < size; row++) {
            for (var col = 0; col < size; col++) {
                if (!qr.isDark(row, col)) continue;
                // Skip the three 7x7 eye zones
                if ((row < 7 && col < 7) ||
                    (row < 7 && col >= size-7) ||
                    (row >= size-7 && col < 7)) continue;
                // Skip center area where logo will go
                if (row >= centerStart && row < centerEnd && col >= centerStart && col < centerEnd) continue;

                ctx.beginPath();
                ctx.arc(col * cell + cell/2, row * cell + cell/2, cell * 0.88, 0, 2*Math.PI);
                ctx.fill();
            }
        }

        // --- Draw three position detection eyes (rounded) ---
        var eyePositions = [
            { x: 0, y: 0 },
            { x: size-7, y: 0 },
            { x: 0, y: size-7 }
        ];
        var eyeSize = 7 * cell;
        eyePositions.forEach(function(pos) {
            var x = pos.x * cell, y = pos.y * cell;
            // Outer ring
            ctx.fillStyle = eyeHex;
            ctx.beginPath();
            ctx.roundRect(x, y, eyeSize, eyeSize, cell * 1.2);
            ctx.fill();
            // Inner white ring
            ctx.fillStyle = '#FFFFFF';
            ctx.beginPath();
            ctx.roundRect(x + cell, y + cell, 5*cell, 5*cell, cell * 0.8);
            ctx.fill();
            // Core pupil
            ctx.fillStyle = bodyHex;
            ctx.beginPath();
            ctx.roundRect(x + 2*cell, y + 2*cell, 3*cell, 3*cell, cell * 0.4);
            ctx.fill();
        });

        // --- Overlay brand logo ---
        if (savedLogo) {
            var logoImg = new Image();
            logoImg.onload = function() {
                var targetW = canvas.width * 0.24;
                var targetH = targetW * (logoImg.height / logoImg.width);
                var lx = (canvas.width - targetW) / 2;
                var ly = (canvas.height - targetH) / 2;
                // White background behind logo
                ctx.fillStyle = '#FFFFFF';
                ctx.beginPath();
                ctx.roundRect(lx - 6, ly - 6, targetW + 12, targetH + 12, 6);
                ctx.fill();
                ctx.drawImage(logoImg, lx, ly, targetW, targetH);
                log("Logo overlay complete");
            };
            logoImg.src = savedLogo;
        } else {
            log("Ready – upload a logo");
        }
    } catch (err) {
        log("Error: " + err.message);
    }
}

function downloadPNG() {
    var canvas = document.getElementById('qrCanvas');
    if (!canvas) return;
    var link = document.createElement('a');
    link.download = 'branded-qr.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

function downloadPDF() {
    if (window.jspdf && window.jspdf.jsPDF) {
        var canvas = document.getElementById('qrCanvas');
        var pdf = new window.jspdf.jsPDF();
        var imgData = canvas.toDataURL('image/png');
        pdf.addImage(imgData, 'PNG', 20, 20, 170, 170);
        pdf.save('branded-qr.pdf');
    } else {
        alert("jspdf library not loaded. Please check your internet connection.");
    }
}
