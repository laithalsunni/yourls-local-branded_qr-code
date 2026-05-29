/**
 * Branded QR Code Suite - Dashboard Vector Core Engine
 */
(function($) {
    function render_branded_dashboard_qr() {
        var $shareboxes = $("#shareboxes");
        if (!$shareboxes.length) return;

        // Fetch active links generated on page state transitions
        var trackingUrl = $('#copylink').val() || $('#share_link').val();
        if (!trackingUrl) return;

        // Verify state caches to minimize thread rendering overhead
        if ($('#branded-qr-suite-container').length > 0) {
            if ($('#branded-qr-suite-container').attr('data-url-hash') === trackingUrl) {
                return; // Matches active data instance
            }
            $('#branded-qr-suite-container').remove(); // Link changed, purge old canvas context
        }

        // Fetch configurations passed from backend PHP database lookups
        var cfg = window.BRANDED_QR_CONFIG || { bodyColor: '#000000', eyeColor: '#000000', logoData: '' };

        // Construct a clean, accessible layout block right within the native admin panel wrapper
        var panelLayout = 
            "<div id='branded-qr-suite-container' data-url-hash='" + trackingUrl + "' class='share' style='float:right; text-align:center; margin:0 10px 15px 15px; padding:10px; background:#fff; border:1px solid #ced4da; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.02);'>" +
            "  <div style='position:relative; display:inline-block;'>" +
            "    <canvas id='brandedQRCanvas' width='160' height='160' style='width:160px; height:160px; display:block;'></canvas>" +
            "  </div>" +
            "  <div style='margin-top:8px;'>" +
            "    <button id='downloadBrandedPNG' style='background:#0073aa; color:#fff; border:none; padding:5px 10px; border-radius:4px; font-size:11px; font-weight:bold; cursor:pointer; font-family:sans-serif;'>💾 Save Image</button>" +
            "  </div>" +
            "</div>";

        $shareboxes.append(panelLayout);
        $shareboxes.css({"display": "block", "overflow": "hidden"});

        // Initialize internal DOM compilation routines
        var canvas = document.getElementById('brandedQRCanvas');
        if (!canvas) return;
        var ctx = canvas.getContext('2d');

        // Create a hidden temporary workspace container element
        var workingDiv = document.createElement('div');
        
        new QRCode(workingDiv, {
            text: trackingUrl,
            width: 160,
            height: 160,
            correctLevel: 3 // Set to High Error Correction density to safely fit custom overlays
        });

        // Small delay to allow the background engine loop to finish drawing modules cleanly
        setTimeout(function() {
            var matrixImg = workingDiv.querySelector('img');
            if (!matrixImg) return;

            // Paint canvas elements cleanly using vector coordinates
            ctx.drawImage(matrixImg, 0, 0, 160, 160);

            // 1. Recolor structural data modules matching brand configurations
            var imgData = ctx.getImageData(0, 0, 160, 160);
            var data = imgData.data;
            
            // Parse operational color metrics down to RGB triplets
            var bodyRGB = hexToRgb(cfg.bodyColor);
            var eyeRGB = hexToRgb(cfg.eyeColor);

            for (var i = 0; i < data.length; i += 4) {
                // If a module block pixel is caught dark/black, map it to custom variables
                if (data[i] < 120 && data[i+1] < 120 && data[i+2] < 120 && data[i+3] > 200) {
                    var x = (i / 4) % 160;
                    var y = Math.floor((i / 4) / 160);

                    // Identify and map corner position fields to isolate eye pixels from data body dots
                    if ((x < 28 && y < 28) || (x > 132 && y < 28) || (x < 28 && y > 132)) {
                        data[i]     = eyeRGB.r;
                        data[i+1]   = eyeRGB.g;
                        data[i+2]   = eyeRGB.b;
                    } else {
                        data[i]     = bodyRGB.r;
                        data[i+1]   = bodyRGB.g;
                        data[i+2]   = bodyRGB.b;
                    }
                }
            }
            ctx.putImageData(imgData, 0, 0);

            // 2. Layer the identity logo asset clean into the visual field center
            if (cfg.logoData && cfg.logoData.length > 10) {
                var logoImg = new Image();
                logoImg.src = cfg.logoData;
                logoImg.onload = function() {
                    var size = 160 * 0.24; // Scale safe inside error boundaries
                    var pos = (160 - size) / 2;

                    // Draw a crisp white masking border around the branding graphic overlay
                    ctx.fillStyle = "#FFFFFF";
                    ctx.fillRect(pos - 3, pos - 3, size + 6, size + 6);
                    ctx.drawImage(logoImg, pos, pos, size, size);
                };
            }
        }, 100);

        // Bind interactive event hooks directly to download action strings
        $(document).on('click', '#downloadBrandedPNG', function(e) {
            e.preventDefault();
            var link = document.createElement('a');
            link.download = 'branded-qr-code.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    }

    // Helper conversion math engine
    function hexToRgb(hex) {
        var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
        hex = hex.replace(shorthandRegex, function(m, r, g, b) { return r + r + g + g + b + b; });
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : { r: 0, g: 0, b: 0 };
    }

    // Bind seamlessly into global administrative rendering pipelines
    $(document).ready(render_branded_dashboard_qr);
    $(document).ajaxComplete(render_branded_dashboard_qr);
    setInterval(render_branded_dashboard_qr, 500);

})(jQuery);
