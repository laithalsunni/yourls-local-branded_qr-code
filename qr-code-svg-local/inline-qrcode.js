/**
 * Branded QR Engine - Ironclad Administrative DOM Hook Script
 */
(function($) {
    function inject_branded_qr_interface() {
        // Prevent execution if it's already injected on the screen
        if ($('#branded-qr-hook-block').length > 0) {
            return;
        }

        // Try standard share page input first, then fall back to the dynamic dashboard creator input
        var generatedShortUrl = $('#copylink').attr('value') || $('#share_link').attr('value');
        
        // If the box exists but has no link value yet, skip and wait
        if (!generatedShortUrl) {
            return;
        }

        // Target your custom interactive dashboard studio URL
        var customizedStudioUrl = BRANDED_QR_WEBROOT + '?url=' + encodeURIComponent(generatedShortUrl);
        
        // Use an un-throttled public global fallback engine for the admin dashboard thumbnail preview
        var placeholderThumbUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' + encodeURIComponent(generatedShortUrl);
        
        var htmlPayload = "" +
            "<div id='branded-qr-hook-block' class='branded-share-qr share' style='float: right; text-align: center; margin-left: 15px;'>" +
            "  <a href='" + customizedStudioUrl + "' title='⚡ Click to customize and brand this QR Code!' target='_blank' style='text-decoration: none; display: block;'>" +
            "    <img src='" + placeholderThumbUrl + "' alt='Branded QR Studio' style='width: 100px; height: 100px; border: 1px solid #e1e4e6; border-radius: 4px; padding: 4px; background: #fff;' />" +
            "    <span style='display:block; font-size:11px; color:#0073aa; font-weight:bold; margin-top:5px;'>⚡ Brand QR Code</span>" +
            "  </a>" +
            "</div>";

        // Inject straight into the container panel
        var $shareboxes = $("#shareboxes");
        if ($shareboxes.length > 0) {  
            $shareboxes.append(htmlPayload);
            // Force clear layouts to make sure float wrappers display side-by-side beautifully
            $shareboxes.css({"display": "block", "overflow": "hidden"});
            console.log("✔ Branded QR: Extension layout linked successfully.");
        }
    }

    // --- DOM WATCHDOG ENGINE ---
    // This constantly observes the webpage's body for the dynamic shareboxes element creation
    var observer = new MutationObserver(function(mutations) {
        if ($("#shareboxes").length > 0 && $('#branded-qr-hook-block').length === 0) {
            // Check if our input fields actually contain string paths before running
            if ($('#copylink').attr('value') || $('#share_link').attr('value')) {
                inject_branded_qr_interface();
            }
        }
    });

    // Fire on load, on AJAX events, and initialize the active watchdog observer
    $(document).ready(function() {
        inject_branded_qr_interface();
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    $(document).ajaxComplete(function() {
        setTimeout(inject_branded_qr_interface, 50);
    });

})(jQuery);
