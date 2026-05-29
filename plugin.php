<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable, vector-perfect branded QR codes matching logo palettes dynamically via localized canvas mapping panels.
Version: 3.2
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'admin_init', 'branded_qrcode_init' );
function branded_qrcode_init() {
    yourls_register_plugin_page( 'branded_qr_control', 'Branded QR Console', 'branded_qrcode_admin_page' );
}

yourls_add_action( 'html_head', 'branded_qrcode_assets' );
function branded_qrcode_assets() {
    $plugin_url = yourls_plugin_url( dirname( __FILE__ ) );
    echo '<script type="text/javascript" src="' . $plugin_url . '/qrcode.min.js"></script>' . "\n";
    echo '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>' . "\n";
    echo '<script type="text/javascript" src="' . $plugin_url . '/inline-qrcode.js"></script>' . "\n";
}

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

function branded_qrcode_admin_page() {
    ?>
    <style>
        .branding-console-wrap { max-width: 950px; margin: 20px 0; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e1e4e6; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; gap: 30px; align-items: flex-start; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; box-sizing: border-box; }
        .console-workspace { flex: 1; min-width: 320px; }
        .console-preview-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; text-align: center; width: 340px; position: sticky; top: 20px; box-sizing: border-box; }
        .console-preview-panel h3 { margin-top: 0; margin-bottom: 15px; color: #1e293b; font-size: 16px; }
        #canvas-wrapper { background: #fff; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; display: inline-block; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
        #qrCanvas { max-width: 100%; height: auto; display: block; width: 280px; height: 280px; }
        .sub-section { margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; }
        .sub-section h4 { margin: 0 0 8px 0; color: #334155; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sub-desc { margin: 0 0 12px 0; color: #64748b; font-size: 13px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 13px; color: #475569; }
        .color-picker-row { display: flex; gap: 10px; align-items: center; }
        .color-picker-row input[type="text"] { width: 100px; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; font-family: monospace; }
        .color-picker-row input[type="color"] { width: 40px; height: 32px; border: 1px solid #cbd5e1; padding: 0; border-radius: 4px; cursor: pointer; }
        .input-file-field { display: none; }
        .custom-file-upload { display: inline-block; padding: 8px 16px; background: #0284c7; color: #fff; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; transition: background 0.2s; }
        .custom-file-upload:hover { background: #0369a1; }
        .preview-logo-thumb { max-height: 60px; display: none; margin-top: 12px; background: #f4f6f8; padding: 6px; border-radius: 4px; border: 1px solid #ddd; }
        .toggle-container { margin-top: 15px; background: #f0f4f8; padding: 10px 12px; border-radius: 4px; border: 1px solid #d0dbe5; }
        .toggle-container label { font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #2c3e50; }
        #debug-log { background: #e2f0d9; color: #385723; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; font-family: monospace; border: 1px solid #c5e0b4; word-break: break-all; text-align: left; }
        .btn-group { display: flex; gap: 8px; justify-content: center; }
        .btn-group button { flex: 1; padding: 10px; font-weight: 500; border-radius: 6px; font-size: 13px; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; }
        .btn-png { background: #10b981; color: white; }
        .btn-png:hover { background: #059669; }
        .btn-pdf { background: #6366f1; color: white; }
        .btn-pdf:hover { background: #4f46e5; }
        .input-url-field { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: monospace; }
    </style>

    <h2>Branded QR Suite Configuration Console</h2>
    <p class="description">Customize vector-perfect structural matrix properties, color match presets, or stamp brand assets into live canvas nodes seamlessly.</p>
    
    <div class="branding-console-wrap">
        <div class="console-workspace">
            <div class="sub-section">
                <h4>0. Target Tracking Workspace Link</h4>
                <p class="sub-desc">Define the destination payload.</p>
                <div class="form-group">
                    <input type="text" id="targetShortUrl" class="input-url-field" value="<?php echo yourls_site_url(); ?>/example">
                </div>
            </div>

            <div class="sub-section">
                <h4>1. Vector Palette Settings</h4>
                <p class="sub-desc">Adjust color mappings for data grids and eye alignment loops.</p>
                
                <div class="form-group">
                    <label>Matrix Body & Pupils Color:</label>
                    <div class="color-picker-row">
                        <input type="text" id="bodyColorInput" value="000000" maxlength="6" onkeyup="handleTextColors(this.value, 'body')">
                        <input type="color" id="bodyColorPicker" value="#000000" oninput="handlePickerColors(this.value, 'body')">
                    </div>
                </div>

                <div class="form-group">
                    <label>Eye Frame Target Color:</label>
                    <div class="color-picker-row">
                        <input type="text" id="eyeColorInput" value="000000" maxlength="6" onkeyup="handleTextColors(this.value, 'eye')">
                        <input type="color" id="eyeColorPicker" value="#000000" oninput="handlePickerColors(this.value, 'eye')">
                    </div>
                </div>
            </div>

            <div class="sub-section">
                <h4>2. Brand Logo Injection Mesh</h4>
                <p class="sub-desc">Overlay a center corporate visual graphic matrix badge.</p>
                
                <div class="form-group">
                    <label class="custom-file-upload">
                        <input type="file" id="logoUploadInput" class="input-file-field" accept="image/*" onchange="handleLogoUpload(event)">
                        📂 Select Corporate Logo Asset
                    </label>
                    <div class="toggle-container">
                        <label>
                            <input type="checkbox" id="autoColorToggle" value="1">
                            Dynamically extract color rules straight from logo palette
                        </label>
                    </div>
                    <img id="logoPreview" class="preview-logo-thumb" src="" alt="Logo Preview">
                </div>
            </div>
        </div>

        <div class="console-preview-panel">
            <h3>Live Vector Matrix Preview</h3>
            <div id="debug-log">Status Log: Core framework operational. Awaiting vector initialization rendering phase...</div>
            <div id="canvas-wrapper">
                <canvas id="qrCanvas" width="1000" height="1000"></canvas>
            </div>
            <div class="btn-group">
                <button type="button" class="btn-png" onclick="downloadPNG()">Download Vector PNG</button>
                <button type="button" class="btn-pdf" onclick="downloadPDF()">Export Print PDF</button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Load saved settings if any
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
            if(localStorage.getItem('qr_logo_base64')) {
                $('#logoPreview').attr('src', localStorage.getItem('qr_logo_base64')).show();
            }

            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('url')) {
                $('#targetShortUrl').val(urlParams.get('url'));
            } else if($('.share-link').length > 0) {
                $('#targetShortUrl').val($('.share-link').val());
            }

            $('#targetShortUrl').on('input', function() {
                renderBrandedQR();
            });

            setTimeout(renderBrandedQR, 300);
        });
    </script>
    <?php
}
