/**
 * Branded QR Code Suite - Share Panel Interface Engine
 */
function generate_branded_qrcode() {
    // 1. Fetch target short URL string directly from the core YOURLS share container element
    var targetUrl = jQuery('#copylink').val();
    
    if (!targetUrl) {
        return;
    }

    // 2. Clear any conflicting or broken legacy container structures safely
    if (jQuery('#qrcode2').length > 0) {
        jQuery('#qrcode2').remove();
    }

    // 3. Build a clean canvas target block matching the native layout structure rules
    var qrContainerMarkup = "<div id='qrcode2' class='share' style='float: right; margin-left: 20px; padding: 10px; border: 1px solid #e3eff6; background: #fff; border-radius: 6px; text-align: center;'>" +
                             "<div id='branded_qr_canvas_target'></div>" +
                             "<div style='margin-top: 8px; font-size: 11px; font-weight: bold; color: #2a85b3;'>Brand QR Code</div>" +
                             "</div>";

    // 4. Append container element cleanly into the share area layout
    if (jQuery("#shareboxes").length > 0) {
        jQuery("#shareboxes").append(qrContainerMarkup);
        
        // 5. Initialize the rendering engine framework
        var qrcode = new QRCode(document.getElementById("branded_qr_canvas_target"), {
            text: targetUrl,
            width: 130,
            height: 130,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }
}

// Attach script initialization trigger safely to the standard page loading completion sequence
jQuery(document).ready(function() {
    generate_branded_qrcode();
});
