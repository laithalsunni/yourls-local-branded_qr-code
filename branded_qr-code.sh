#!/bin/bash
# ==============================================================================
# Branded QR Code Production Installer Script for YOURLS (Production Engine v4.2)
# ==============================================================================
set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_FOLDER="branded_qr_code"

echo "====================================================="
echo "⚙️  Initializing Branded QR Code Suite Setup Matrix"
echo "====================================================="

# 1. Verify execution directory context
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: This script must be executed from within your YOURLS tree."
    exit 1
fi

echo "✔ Confirmed YOURLS Root: $YOURLS_ROOT"

# 2. Re-verify target workspace folders
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_FOLDER"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code"
sudo mkdir -p "$PLUGIN_DIR"

echo "📂 Synchronizing Workspace at: $PLUGIN_DIR"

# 3. Pull production static assets from raw repository paths
echo "📥 Downloading production-ready script manifests..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/qr/js/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"

# 4. Write out the production plugin engine directly
echo "🩹 Applying operational dashboard routing patches with dynamic engine..."

sudo tee "$PLUGIN_DIR/plugin.php" > /dev/null << 'EOF'
<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable, vector-perfect branded QR codes matching logo palettes dynamically via localized canvas mapping panels with explicit submission loops.
Version: 4.2
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
        .sub-section { background: #fdfdfd; border: 1px solid #eaeaea; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        .sub-section h4 { margin-top: 0; color: #222; font-size: 14px; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sub-section .sub-desc { color: #777; font-size: 13px; margin-top: 0; margin-bottom: 15px; line-height: 1.4; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; color: #444; }
        .color-input-wrapper { display: flex; align-items: center; gap: 8px; }
        .form-group input[type="text"] { width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 14px; text-transform: uppercase; }
        .form-group input[type="color"] { border: none; padding: 0; width: 36px; height: 36px; border-radius: 4px; cursor: pointer; background: none; }
        .preview-logo-thumb { max-height: 60px; display: block; margin-top: 12px; background: #f4f6f8; padding: 6px; border-radius: 4px; border: 1px solid #ddd; margin: 10px auto 0 auto; }
        .toggle-container { margin-top: 15px; background: #f0f4f8; padding: 10px 12px; border-radius: 4px; border: 1px solid #d0dbe5; }
        .toggle-container label { font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #2c3e50; }
        #debug-log { background: #e2f0d9; color: #385723; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; font-family: monospace; border: 1px solid #c5e0b4; word-break: break-all; text-align: left; }
        .btn-group { display: flex; gap: 8px; justify-content: center; }
        .btn-group button { flex: 1; padding: 10px; font-weight: bold; border-radius: 4px; border: none; cursor: pointer; transition: background 0.15s; }
        .btn-primary { background: #0073aa; color: #fff; }
        .btn-primary:hover { background: #005177; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-action-upload { background: #4682b4; color: #fff; padding: 8px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 8px; display: block; width: 100%; text-align: center; font-size: 13px; transition: background 0.2s; }
        .btn-action-upload:hover { background: #2f4f4f; }
        .input-url-field { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: monospace; }
        #hiddenEngineContainer { display: none; }
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
                    <div class="color-input-wrapper">
                        <input type="color" id="bodyColorPicker" value="#000000">
                        #<input type="text" id="bodyColorInput" value="000000" maxlength="6">
                    </div>
                </div>
            </div>
            
            <div class="sub-section">
                <h4>2. Brand Logo Overlay</h4>
                <p class="sub-desc">Drop transparent high-resolution identity graphics straight across data grids safely.</p>
                <div class="form-group">
                    <label>Select Identity Graphic File:</label>
                    <input type="file" id="logoInput" accept="image/*" style="width:100%; margin-bottom:4px;">
                    <button type="button" class="btn-action-upload" id="submitLogoBtn">⚙️ Upload & Process Logo</button>
                    <center><img id="logoPreview" class="preview-logo-thumb" style="display:none;" /></center>
                </div>
            </div>
        </div>

        <div class="console-preview-panel">
            <h3>Live Engine Output Canvas</h3>
            <div id="debug-log">Status: Awaiting operational loop mapping...</div>
            <div id="canvas-wrapper"><canvas id="qrCanvas" width="500" height="500"></canvas></div>
            <div class="btn-group">
                <button class="btn-primary" onclick="downloadPNG()">Download PNG</button>
            </div>
        </div>
    </div>

    <div id="hiddenEngineContainer"></div>

    <script>
        var uploadedLogoImg = null;

        jQuery(document).ready(function($) {
            // Restore persistent hex color codes if present inside memory buffers
            if(localStorage.getItem('qr_body_hex')) {
                var bHex = localStorage.getItem('qr_body_hex');
                $('#bodyColorInput').val(bHex);
                $('#bodyColorPicker').val('#' + bHex);
            }

            $('#bodyColorInput').on('input', function() { handleTextColors($(this).val(), 'body'); });
            $('#bodyColorPicker').on('input', function() { handlePickerColors($(this).val(), 'body'); });
            $('#targetShortUrl').on('input', function() { renderBrandedQR(); });

            // SUBMIT INTERCEPTOR FOR BRAND ASSET PROCESSING
            $('#submitLogoBtn').on('click', function(e) {
                e.preventDefault();
                var fileInput = document.getElementById('logoInput');
                if (fileInput.files && fileInput.files[0]) {
                    processLogoFile(fileInput.files[0]);
                } else {
                    alert('Select a valid logo asset file first before initiating processor loop.');
                }
            });

            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('url')) {
                $('#targetShortUrl').val(urlParams.get('url'));
            }
            
            setTimeout(renderBrandedQR, 400);
        });

        function handleTextColors(hex, target) {
            hex = hex.replace('#', '');
            if(hex.length === 6) {
                jQuery('#' + target + 'ColorPicker').val('#' + hex);
                localStorage.setItem('qr_' + target + '_hex', hex);
                renderBrandedQR();
            }
        }

        function handlePickerColors(hex, target) {
            jQuery('#' + target + 'ColorInput').val(hex.replace('#', '').toUpperCase());
            localStorage.setItem('qr_' + target + '_hex', hex.replace('#', ''));
            renderBrandedQR();
        }

        function processLogoFile(file) {
            var reader = new FileReader();
            jQuery('#debug-log').text("Status: Compiling brand logo graphic vectors...");
            reader.onload = function(event) {
                uploadedLogoImg = new Image();
                uploadedLogoImg.onload = function() {
                    jQuery('#logoPreview').attr('src', event.target.result).show();
                    jQuery('#debug-log').text("✔ Success: Brand graphic scaled and loaded successfully.");
                    renderBrandedQR();
                };
                uploadedLogoImg.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }

        // CORRECTED CANVAS RENDERING CONTROLLER ENGINE
        function renderBrandedQR() {
            var canvas = document.getElementById('qrCanvas');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            var textContent = jQuery('#targetShortUrl').val() || 'https://yourls.org';
            var bodyColor = jQuery('#bodyColorPicker').val() || '#000000';
            
            // 1. Purge hidden scratchpads and build matrix safely via native qrcode.js compiler
            var container = document.getElementById('hiddenEngineContainer');
            container.innerHTML = "";
            
            try {
                var qrEngine = new QRCode(container, {
                    text: textContent,
                    width: 400,
                    height: 400,
                    colorDark: bodyColor,
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H // Forced High Level (H) to safeguard matrix blocks
                });

                // 2. Extrapolate compilation data from generated image node inside shadow container
                setTimeout(function() {
                    var engineImg = container.querySelector('img');
                    if (!engineImg) {
                        jQuery('#debug-log').text("⚠️ Processing engine stream syncing...");
                        return;
                    }

                    // 3. Clear canvas and draw structural base elements
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    
                    // Stamp generated underlying matrix onto our visible control panel canvas
                    ctx.drawImage(engineImg, 40, 40, 420, 420);

                    // 4. Centered compositing overlay for custom company logo file
                    if (uploadedLogoImg) {
                        var logoDimensions = 100; 
                        var lx = (canvas.width - logoDimensions) / 2;
                        var ly = (canvas.height - logoDimensions) / 2;
                        
                        // Protective background safety buffer block over central code rows
                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(lx - 6, ly - 6, logoDimensions + 12, logoDimensions + 12);
                        
                        // Draw custom image asset node securely
                        ctx.drawImage(uploadedLogoImg, lx, ly, logoDimensions, logoDimensions);
                    }
                    jQuery('#debug-log').text("✔ Status: Vector matrix generation finalized.");
                }, 50);

            } catch (err) {
                jQuery('#debug-log').text("❌ Matrix Failure: " + err.message);
            }
        }

        function downloadPNG() {
            var canvas = document.getElementById('qrCanvas');
            var link = document.createElement('a');
            link.download = 'branded-shortlink-qr.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    </script>
    <?php
}
EOF

# 5. Reset standard Linux directory profiles
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Installer script successfully fixed!"
echo "====================================================="
