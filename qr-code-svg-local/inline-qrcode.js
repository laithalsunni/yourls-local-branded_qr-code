/**
 * Branded QR Engine - Administrative UI Hook Script
 */
function inject_branded_qr_interface() {
    // FALLBACK PICKER: Try standard share page container (#copylink) first, 
    // then fall back to the main admin panel creator container (#share_link)
    var generatedShortUrl = $( '#copylink' ).attr('value') || $( '#share_link' ).attr('value');
    
    // If neither element is currently active on the screen, check if we can pull it 
    // from the row action clicks or abort gracefully
    if (!generatedShortUrl) {
        return;
    }

    // Safely structure the cross-link routing destination directly to your custom interactive dashboard
    var customizedStudioUrl = BRANDED_QR_WEBROOT + '?url=' + encodeURIComponent(generatedShortUrl);
    
    // Standard preview fallback layout blocks matching exact container geometry specifications
    var placeholderThumbUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' + encodeURIComponent(generatedShortUrl);
    
    var htmlPayload = "" +
        "<div id='branded-qr-hook-block' class='branded-share-qr share'>" +
        "  <a href='" + customizedStudioUrl + "' title='⚡ Click to customize and brand this QR Code!' target='_blank'>" +
        "    <img src='" + placeholderThumbUrl + "' alt='Branded QR Code Studio' />" +
        "    <span style='display:block; font-size:11px; color:#0073aa; font-weight:bold; margin-top:5px; text-decoration:none;'>⚡ Brand QR Code</span>" +
        "  </a>" +
        "</div>";

    // Prevent duplicate object instances if short links are sequentially processed
    if ( $( '#branded-qr-hook-block' ).length > 0 ) {
        $( '#branded-qr-hook-block' ).remove();
    }
    
    // Append the element directly into the core administrative dashboard layout panel
    if ($( "#shareboxes" ).length > 0) {  
        $( "#shareboxes" ).append( htmlPayload );
        
        // Ensure parent containers don't collapse visually on index grids
        $( "#shareboxes" ).css("display", "block").css("overflow", "hidden");
    }
}

// Fire injection on document ready event states natively
$(document).ready(function() { 
    inject_branded_qr_interface(); 
});

// Dynamic fallback: Listen for AJAX link additions on the main index table view
$(document).ajaxComplete(function() {
    inject_branded_qr_interface();
});
