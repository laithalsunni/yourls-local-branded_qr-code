/**
 * Branded QR Engine - Complete Dashboard UI Render Hook
 */
(function($) {
    function process_branded_qr_injection() {
        // Find the native target share box element layout
        var $shareboxes = $("#shareboxes");
        if (!$shareboxes.length) {
            return;
        }

        // Pull active link values from standard share inputs or dynamic AJAX dashboard creators
        var targetShortUrl = $('#copylink').val() || $('#share_link').val();
        if (!targetShortUrl) {
            return;
        }

        // Manage clean target refreshes on multiple sequential shortens
        if ($('#branded-qr-dashboard-wrapper').length > 0) {
            var activeUrlString = $('#branded-qr-dashboard-wrapper').attr('data-rendered-link');
            if (activeUrlString === targetShortUrl) {
                return; // Currently showing correct QR link data
            }
            $('#branded-qr-dashboard-wrapper').remove(); // Clear stale link payload
        }

        // Build container payload structural boundaries
        var customStudioUrl = window.BRANDED_QR_STUDIO_PATH + '?url=' + encodeURIComponent(targetShortUrl);
        
        var structuralLayout = 
            "<div id='branded-qr-dashboard-wrapper' data-rendered-link='" + targetShortUrl + "' class='share' style='float: right; text-align: center; margin: 0 10px 15px 15px; padding: 5px;'>" +
            "  <a href='" + customStudioUrl + "' title='⚡ Click to customize and brand this QR Code!' target='_blank' style='text-decoration: none !important; display: block; border: none;'>" +
            "    <div id='branded-qr-canvas-render' style='width: 100px; height: 100px; padding: 6px; background: #fff; border: 1px solid #ced4da; border-radius: 6px; display: inline-block;'></div>" +
            "    <span style='display: block; font-size: 11px; color: #0073aa; font-weight: bold; margin-top: 6px; font-family: sans-serif; text-decoration: none !important;'>⚡ Brand QR Code</span>" +
            "  </a>" +
            "</div>";

        // Append to wrapper pane layout profile
        $shareboxes.append(structuralLayout);
        $shareboxes.css({"display": "block", "overflow": "hidden"});

        // Compile clean native vector modules straight inside your dashboard view
        if (typeof QRCode !== 'undefined') {
            try {
                new QRCode(document.getElementById("branded-qr-canvas-render"), {
                    text: targetShortUrl,
                    width: 100,
                    height: 100,
                    correctLevel: 3 // High density error correction profile
                });
                
                // Force layout images to scale cleanly within vector parameters
                $("#branded-qr-canvas-render img").css({"width": "100px", "height": "100px", "display": "block"});
            } catch (canvasErr) {
                console.log("Branded QR Engine Matrix Exception: " + canvasErr.message);
            }
        } else {
            // Fallback text helper string if core script dependencies are unreachable
            $("#branded-qr-canvas-render").html("<div style='font-size:10px; color:red; padding-top:35px;'>Script Missing</div>");
        }
    }

    // Bind to page execution lifecycles safely
    $(document).ready(function() {
        process_branded_qr_injection();
    });

    $(document).ajaxComplete(function() {
        process_branded_qr_injection();
    });

    // Throttled 400ms polling loop to track and catch hidden tab changes
    setInterval(process_branded_qr_injection, 400);

})(jQuery);
