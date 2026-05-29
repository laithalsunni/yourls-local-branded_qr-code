<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable, vector-perfect branded QR codes matching logo palettes dynamically via localized canvas mapping panels.
Version: 3.1
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

// Secure execution context verification guard
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// CORRECTED HOOK: Using 'admin_init' instead of 'plugins_loaded' prevents the system from triggering 404 headers on active screens
yourls_add_action( 'admin_init', 'branded_qrcode_init' );
function branded_qrcode_init() {
    yourls_register_plugin_page( 'branded_qr_control', 'Branded QR Console', 'branded_qrcode_admin_page' );
}

// Injects library dependencies safely into headers
yourls_add_action( 'html_head', 'branded_qrcode_assets' );
function branded_qrcode_assets() {
    // Graceful fallback to avoid asset loading issues if file names vary
    $plugin_url = yourls_plugin_url( dirname( __FILE__ ) );
    echo '<script type="text/javascript" src="' . $plugin_url . '/qrcode.min.js"></script>' . "\n";
    echo '<script type="text/javascript" src="' . $plugin_url . '/inline-qrcode.js"></script>' . "\n";
}

// Injects clean action links directly into the action share button rows of links
yourls_add_filter( 'table_add_row_action_array', 'branded_qrcode_row_action' );
function branded_qrcode_row_action( $actions ) {
    $target_page = yourls_admin_url( 'plugins.php?page=branded_qr_control' );
    
    $actions['branded_qr'] = array(
        'href'    => $target_page,
        'id'      => 'branded_qr_btn',
        'title'   => 'Design Branded QR Code',
        'anchor'  => 'QR Code'
    );
    return $actions;
}

// Renders the dedicated visual control layout natively inside the dashboard
function branded_qrcode_admin_page() {
    ?>
    <style>
        .branding-console-wrap { max-width: 950px; margin: 20px 0; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e1e4e6; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; gap: 30px; align-items: flex-start; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; box-sizing: border-box; }
        .console-workspace { flex: 1; min-width: 320px; }
        .console-preview-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; text-align: center; width: 340px; position: sticky; top: 20px; box-sizing: border-box; }
        .console-preview-panel h3 { margin-top: 0; margin-bottom: 15px; color: #1e293b; font-size: 16px; }
        #canvas-wrapper { background: #fff; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; display: inline-block; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
        #qrCanvas { max-width: 100%; height: auto; display: block; width: 280px; height: 280px; }
        .sub-section { background: #fdfdfd; border: 1px solid #eaeaea; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        .sub-section h4 { margin-top: 0; color: #222; font-size: 14px; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sub-section .sub-desc { color: #777; font-size: 13px; margin-top: 0; margin-bottom: 15px; line-height: 1.4; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; color: #444; }
        .color-input-wrapper { display: flex; align-items: center; gap: 8px; }
        .form-group input[type="text"] { width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 14px; text-transform: uppercase; }
        .form-group input[type="color"] { border: none; padding: 0; width: 36px; height: 36px; border-radius: 4px; cursor: pointer; background: none; }
        .preview-logo-thumb { max-height: 60px; display: block; margin-top: 12px; background: #f4f6f8; padding: 6px; border-radius: 4px; border: 1px solid #ddd; }
        .toggle-container { margin-top: 15px; background: #f0f4f8; padding: 10px 12px; border-radius: 4px; border: 1px solid #d0dbe5; }
        .toggle-container label { font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #2c3e50; }
        #debug-log { background: #e2f0d9; color: #385723; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; font-family: monospace; border: 1px solid #c5e0b4; word-break: break-all; text-align: left; }
        .btn-group { display: flex; gap: 8px; justify-content: center; }
        .btn-group button { flex: 1; padding: 10px; font-weight: bold; border-radius: 4px; border: none; cursor: pointer; transition: background 0.15s; }
        .btn-primary { background: #0073aa; color: #fff; }
        .btn-primary:hover { background: #005177; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .input-url-field { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: monospace; }
    </style>

    <h2>Branded QR Suite Configuration Console</h2>
    <p class="description">Customize vector-perfect structural matrix properties, color match presets, or stamp brand assets into live canvas nodes seamlessly.</p>

    <div class="branding-console-wrap">
        <div class="console-workspace">
            <div class="sub-section">
                <h4>0. Target Tracking Workspace Link</h4>
                <p class="sub-desc">Define the destination payload. Changing this inputs any raw tracking URL parameters dynamically into the live vector array pipeline.</p>
                <div class="form-group">
                    <input type="text" id="targetShortUrl" class="input-url-field" value="<?php echo yourls_site_url(); ?>/example">
                </div>
            </div>

            <div class="sub-section">
                <h4>1. Vector Palette Settings</h4>
                <p class="sub-desc">Adjust color mappings for data grids and eye alignment loops. Type 6-digit hex values or click color bars manually.</p>
                
                <div class="form-group">
                    <label>Matrix Body & Pupils Color:</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="bodyColorPicker" value="#000000">
                        #<input type="text" id="bodyColorInput" value="000000" maxlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Outer Eye Frame Ring Color:</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="eyeColorPicker" value="#000000">
                        #<input type="text" id="eyeColorInput" value="000000" maxlength="6">
                    </div>
                </div>
            </div>
            
            <div class="sub-section">
                <h4>2. Brand Logo Overlay</h4>
                <p class="sub-desc">Drop transparent high-resolution PNG or crisp graphic assets straight across data grids to override background segments safely.</p>
                
                <div class="form-group">
                    <label>Select Identity Graphic File:</label>
                    <input type="file" id="logoInput" accept="image/*">
                    <img id="logoPreview" class="preview-logo-thumb" style="display:none;" />
                    
                    <div class="toggle-container">
                        <label>
                            <input type="checkbox" id="autoColorToggle"> 
                            🎨 Auto-update colors matching the uploaded logo palette
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="console-preview-panel">
            <h3>Live Engine Output Canvas</h3>
            <div id="debug-log">Status: Awaiting operational loop mapping...</div>
            
            <div id="canvas-wrapper">
                <canvas id="qrCanvas" width="500" height="500"></canvas>
            </div>

            <div class="btn-group">
                <button class="btn-primary" onclick="downloadPNG()">Download PNG</button>
                <button class="btn-secondary" onclick="downloadPDF()">Save PDF</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            // Restore previous user customizations seamlessly from browser memory cache
            if(localStorage.getItem('qr_body_hex')) {
                var bHex = localStorage.getItem('qr_body_hex');
                $('#bodyColorInput').val(bHex);
                $('#bodyColorPicker').val('#' + bHex);
            }
            if(localStorage.getItem('qr_eye_hex')) {
                var eHex = localStorage.getItem('qr_eye_hex');
                $('#eyeColorInput').val(eHex);
                $('#eyeColorPicker').val('#' + eHex);
            }
            
            // Re-bind change listeners
            $('#bodyColorInput').on('input', function() { handleTextColors($(this).val(), 'body'); });
            $('#bodyColorPicker').on('input', function() { handlePickerColors($(this).val(), 'body'); });
            $('#eyeColorInput').on('input', function() { handleTextColors($(this).val(), 'eye'); });
            $('#eyeColorPicker').on('input', function() { handlePickerColors($(this).val(), 'eye'); });
            $('#targetShortUrl').on('input', function() { renderBrandedQR(); });
            $('#logoInput').on('change', handleLogoUpload);

            // Handle URL arguments passed via administrative row action elements seamlessly
            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('url')) {
                $('#targetShortUrl').val(urlParams.get('url'));
            } else if($('.share-link').length > 0) {
                $('#targetShortUrl').val($('.share-link').val());
            }

            // Run initial compilation loops after page initialization clears
            setTimeout(renderBrandedQR, 300);
        });
    </script>
    <?php
}
