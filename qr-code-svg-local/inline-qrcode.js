/**
 * Branded QR Engine - Administrative UI Hook Script
 */
function inject_branded_qr_interface() {
    // Fetch the target string value straight out of the native YOURLS copy container element
    var generatedShortUrl = $( '#copylink' ).attr('value');
    
    if (!generatedShortUrl) {
        return;
    }

    // Safely structure the cross-link routing destination directly to your custom interactive dashboard
    var customizedStudioUrl = BRANDED_QR_WEBROOT + '?url=' + encodeURIComponent(generatedShortUrl);
    
    // Standard preview fallback layout blocks matching exact container geometry specifications
    // Clicking this container immediately redirects the administrator straight into the custom editor workshop
    var placeholderThumbUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' + encodeURIComponent(generatedShortUrl);
    
    var htmlPayload = "" +
        "<div id='branded-qr-hook-block' class='branded-share-qr share'>" +
        "  <a href='" + customizedStudioUrl + "' title='⚡ Click to customize and brand this QR Code!'>" +
        "    <img src='" + placeholderThumbUrl + "' alt='Branded QR Code Studio' />" +
        "    <span style='display:block; font-size:10px; color:#0073aa; font-weight:bold; margin-top:3px;'>⚡ Brand & Customize</span>" +
        "  </a>" +
        "</div>";

    // Prevent duplicate object instances if short links are regenerated sequentially without page reloads
    if ( $( '#branded-qr-hook-block' ).length > 0 ) {
        $( '#branded-qr-hook-block' ).remove();
    }
    
    // Append the element directly into the core administrative dashboard structure layout panel
    if ($( "#shareboxes" ).length > 0) {  
        $( "#shareboxes" ).append( htmlPayload );
    }
}

// Fire injection on document ready event states natively
$(document).ready(function() { 
    inject_branded_qr_interface(); 
});
