/**
 * Branded QR Engine - Administrative UI Hook Script
 */
(function($) {
    function inject_branded_qr_interface() {
        // Find sharebox target container panels
        var $shareboxes = $("#shareboxes");
        if (!$shareboxes.length) {
            return;
        }

        // Fetch values from either standalone page inputs or ajax dashboard panels
        var generatedShortUrl = $('#copylink').val() || $('#share_link').val();
        if (!generatedShortUrl) {
            return;
        }

        // Prevent duplicate generation if already injected on the screen
        if ($('#branded-qr-hook-block').length > 0) {
            // Check if the link has changed (i.e., user shortened another link sequentially)
            var currentLinkedUrl = $('#branded-qr-hook-block a').data('shorturl');
            if (currentLinkedUrl === generatedShortUrl) {
                return; // Everything is correct and up-to-date
            } else {
                $('#branded-qr-hook-block').remove(); // Link mismatch, strip and rebuild
            }
        }

        // Determine destination URL
        var studioPath = window.BRANDED_QR_WEBROOT || (window.location.origin + '/qr/index.html');
        var customizedStudioUrl = studioPath + '?url=' + encodeURIComponent(generatedShortUrl);
        
        // QR image placeholder container
        var placeholderThumbUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' + encodeURIComponent(generatedShortUrl);
        
        var htmlPayload = 
            "<div id='branded-qr-hook-block' class='branded-share-qr share' style='float: right; text-align: center; margin-left: 15px;'>" +
            "  <a href='" + customizedStudioUrl + "' data-shorturl='" + generatedShortUrl + "' title='⚡ Click to customize and brand this QR Code!' target='_blank' style='text-decoration: none; display: block;'>" +
            "    <img src='" + placeholderThumbUrl + "' alt='Branded QR Studio' style='width: 100px; height: 100px; border: 1px solid #e1e4e6; border-radius: 4px; padding: 4px; background: #fff;' />" +
            "    <span style='display:block; font-size:11px; color:#0073aa; font-weight:bold; margin-top:5px; text-decoration:none;'>⚡ Brand QR Code</span>" +
            "  </a>" +
            "</div>";

        $shareboxes.append(htmlPayload);
        $shareboxes.css({"display": "block", "overflow": "hidden"});
    }

    // Bind seamlessly to DOM load events
    $(document).ready(function() {
        inject_branded_qr_interface();
    });

    // Capture standard YOURLS admin panel modifications safely
    $(document).ajaxComplete(function() {
        inject_branded_qr_interface();
    });
    
    // Fallback interval loop running every 500ms to instantly catch dynamic actions
    setInterval(inject_branded_qr_interface, 500);

})(jQuery);
