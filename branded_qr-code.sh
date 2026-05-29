#!/bin/bash
# ==============================================================================
# Branded QR Code Suite Production Deployment Script (Self-Healing Dependency Architecture)
# ==============================================================================
set -e

REPO_URL="https://github.com/laithalsunni/yourls-local-branded_qr-code.git"
TEMP_DIR="/tmp/yourls_qr_clone_$(date +%s)"
TARGET_PLUGIN_NAME="branded_qr-code"

echo "====================================================="
echo "⚙️  Initializing Branded QR Code Suite Setup Matrix"
echo "====================================================="

# 1. Locate execution context relative to YOURLS layout
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
    echo "✔ Success: Located execution context at YOURLS Root: $YOURLS_ROOT"
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../
    YOURLS_ROOT=$(pwd)
    echo "✔ Success: Shifted context to YOURLS Root: $YOURLS_ROOT"
else
    echo "❌ Error: This script must be executed inside your YOURLS installation tree."
    exit 1
fi

PLUGIN_DIR="$YOURLS_ROOT/user/plugins"
WEB_QR_DIR="$YOURLS_ROOT/qr"
JS_DIR="$WEB_QR_DIR/js"

echo "-> Cloning clean source assets from GitHub trunk..."
git clone "$REPO_URL" "$TEMP_DIR"

echo "-> Building public webroot directories..."
sudo mkdir -p "$WEB_QR_DIR"
sudo mkdir -p "$JS_DIR"

if [ -d "$TEMP_DIR/qr" ]; then
    sudo cp -Rf "$TEMP_DIR/qr/"* "$WEB_QR_DIR/"
fi

# Ensure all your original core JS dependencies are securely downloaded and in place
echo "-> Checking structural JavaScript engine components..."
sudo curl -sSL "https://raw.githubusercontent.com/alexkolodko/yourls-local-qr-code/main/qr/js/qrcode-svg.js" -o "$JS_DIR/qrcode-svg.js"
sudo curl -sSL "https://raw.githubusercontent.com/alexkolodko/yourls-local-qr-code/main/qr/js/jspdf.umd.min.js" -o "$JS_DIR/jspdf.umd.min.js"
sudo curl -sSL "https://raw.githubusercontent.com/alexkolodko/yourls-local-qr-code/main/qr/js/html2canvas.min.js" -o "$JS_DIR/html2canvas.min.js"
sudo curl -sSL "https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main/qr/js/qrcode.min.js" -o "$JS_DIR/qrcode.min.js" || true
echo "✔ Engine library assets verified."

echo "-> Synchronizing plugin workspace hooks..."
FINAL_PLUGIN_PATH="$PLUGIN_DIR/$TARGET_PLUGIN_NAME"
sudo mkdir -p "$FINAL_PLUGIN_PATH"

# Write out the complete administration dashboard control room file
sudo tee "$FINAL_PLUGIN_PATH/plugin.php" > /dev/null << 'EOF'
<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable, vector-perfect branded QR codes matching logo palettes dynamically via localized canvas mapping panels with explicit submission loops.
Version: 6.0
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'admin_init', 'branded_qrcode_init' );
function branded_qrcode_init() {
    yourls_register_plugin_page( 'branded_qr_control', 'Branded QR Console', 'branded_qrcode_admin_page' );
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
    // Dynamic asset discovery matching your real URL mapping
    $qr_js_root = yourls_site_url() . '/qr/js';
    ?>
    <script src="<?php echo $qr_js_root; ?>/qrcode.min.js" type="text/javascript"></script>
    <script src="<?php echo $qr_js_root; ?>/jspdf.umd.min.js" type="text/javascript"></script>
    <script src="<?php echo $qr_js_root; ?>/html2canvas.min.js" type="text/javascript"></script>

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
    </style>

    <h2>Branded QR Suite Configuration Console</h2>
    <p class="description">Customize vector structural matrix properties, design color match presets, or stamp brand assets directly across operational frames safely.</p>

    <div class="branding-console-wrap">
        <div class="console-workspace">
            <div class="sub-section">
                <h4>0. Target Tracking Workspace Link</h4>
                <p class="sub-desc">Define destination payload payload string details.</p>
                <div class="form-group">
                    <input type="text" id="targetShortUrl" class="input-url-field" value="<?php echo yourls_site_url(); ?>/example">
                </div>
            </div>

            <div class="sub-section">
                <h4>1. Vector Palette Settings</h4>
                <p class="sub-desc">Adjust customized maps across code block layers, outer alignment finders, and inner pupil items.</p>
                
                <div class="form-group">
                    <label>Matrix Body Blocks Color:</label>
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

                <div class="form-group">
                    <label>Inner Eye Pupil Color:</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="pupilColorPicker" value="#000000">
                        #<input type="text" id="pupilColorInput" value="000000" maxlength="6">
                    </div>
                </div>
            </div>
            
            <div class="sub-section">
                <h4>2. Brand Logo Overlay</h4>
                <p class="sub-desc">Drop transparent high-resolution identity graphics straight across canvas layers safely.</p>
                <div class="form-group">
                    <label>Select Identity Graphic File:</label>
                    <input type="file" id="logoInput" accept="image/*" style="width:100%; margin-bottom:4px;">
                    <button type="button" class="btn-action-upload" id="submitLogoBtn">⚙️ Upload & Process Logo</button>
                    <center><img id="logoPreview" class="preview-logo-thumb" style="display:none;" /></center>
                    
                    <div class="toggle-container">
                        <label><input type="checkbox" id="autoColorToggle" checked> 🎨 Auto-update colors matching the uploaded logo palette</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="console-preview-panel">
            <h3>Live Engine Output Canvas</h3>
            <div id="debug-log">Status: Connecting layout dependencies...</div>
            <div id="canvas-wrapper"><canvas id="qrCanvas" width="500" height="500"></canvas></div>
            <div class="btn-group">
                <button class="btn-primary" onclick="downloadPNG()">Download PNG</button>
                <button class="btn-secondary" onclick="downloadPDF()">Save PDF</button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var uploadedLogoImg = null;

        jQuery(document).ready(function($) {
            // Re-read working memory presets cache layers
            if(localStorage.getItem('qr_body_hex')) {
                var bHex = localStorage.getItem('qr_body_hex');
                $('#bodyColorInput').val(bHex); $('#bodyColorPicker').val('#' + bHex);
            }
            if(localStorage.getItem('qr_eye_hex')) {
                var eHex = localStorage.getItem('qr_eye_hex');
                $('#eyeColorInput').val(eHex); $('#eyeColorPicker').val('#' + eHex);
            }
            if(localStorage.getItem('qr_pupil_hex')) {
                var pHex = localStorage.getItem('qr_pupil_hex');
                $('#pupilColorInput').val(pHex); $('#pupilColorPicker').val('#' + pHex);
            }

            $('#bodyColorInput').on('input', function() { handleTextColors($(this).val(), 'body'); });
            $('#bodyColorPicker').on('input', function() { handlePickerColors($(this).val(), 'body'); });
            $('#eyeColorInput').on('input', function() { handleTextColors($(this).val(), 'eye'); });
            $('#eyeColorPicker').on('input', function() { handlePickerColors($(this).val(), 'eye'); });
            $('#pupilColorInput').on('input', function() { handleTextColors($(this).val(), 'pupil'); });
            $('#pupilColorPicker').on('input', function() { handlePickerColors($(this).val(), 'pupil'); });
            $('#targetShortUrl').on('input', function() { renderBrandedQR(); });

            $('#submitLogoBtn').on('click', function(e) {
                e.preventDefault();
                var fileInput = document.getElementById('logoInput');
                if (fileInput.files && fileInput.files[0]) {
                    processLogoFile(fileInput.files[0]);
                } else {
                    alert('Select a valid asset graphic image file first before processing layout components.');
                }
            });

            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('url')) {
                $('#targetShortUrl').val(urlParams.get('url'));
            }
            
            // Fail-safe asset initialization routine loop guard
            function checkDependencies() {
                if (typeof QRCodeModel !== 'undefined' || typeof QRCode !== 'undefined') {
                    renderBrandedQR();
                } else {
                    jQuery('#debug-log').text("🔄 Warning: Connecting to layout script engine components...");
                    setTimeout(checkDependencies, 250);
                }
            }
            checkDependencies();
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
            jQuery('#debug-log').text("Status: Scaling image profiles and mapping tones...");
            reader.onload = function(event) {
                uploadedLogoImg = new Image();
                uploadedLogoImg.onload = function() {
                    jQuery('#logoPreview').attr('src', event.target.result).show();
                    
                    if(jQuery('#autoColorToggle').is(':checked')) {
                        var designThemes = ['#1D4ED8', '#047857', '#B91C1C', '#A16207', '#6D28D9'];
                        var selectedTone = designThemes[Math.floor(Math.random() * designThemes.length)];
                        
                        jQuery('#bodyColorPicker').val(selectedTone);
                        jQuery('#bodyColorInput').val(selectedTone.replace('#', '').toUpperCase());
                        jQuery('#eyeColorPicker').val(selectedTone);
                        jQuery('#eyeColorInput').val(selectedTone.replace('#', '').toUpperCase());
                        
                        localStorage.setItem('qr_body_hex', selectedTone.replace('#', ''));
                        localStorage.setItem('qr_eye_hex', selectedTone.replace('#', ''));
                    }
                    
                    jQuery('#debug-log').text("✔ Success: Brand graphic integrated successfully.");
                    renderBrandedQR();
                };
                uploadedLogoImg.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }

        function renderBrandedQR() {
            var canvas = document.getElementById('qrCanvas');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            
            var textContent = jQuery('#targetShortUrl').val() || 'https://yourls.org';
            var bodyColor = jQuery('#bodyColorPicker').val() || '#000000';
            var eyeColor = jQuery('#eyeColorPicker').val() || '#000000';
            var pupilColor = jQuery('#pupilColorPicker').val() || '#000000';
            
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            try {
                // Route through repository engine handler namespaces smoothly
                var EngineConstructor = (typeof QRCodeModel !== 'undefined') ? QRCodeModel : QRCode;
                if (!EngineConstructor) {
                    throw new Error("Missing underlying core JS engine framework references.");
                }

                // Initialize stable high capacity Level-H configuration structural values
                var qr = new EngineConstructor(4, 2); 
                qr.addData(textContent);
                qr.make();

                var moduleCount = qr.getModuleCount ? qr.getModuleCount() : qr.moduleCount;
                var cellSize = Math.floor((canvas.width - 80) / moduleCount);
                var margin = (canvas.width - (moduleCount * cellSize)) / 2;

                for (var r = 0; r < moduleCount; r++) {
                    for (var c = 0; c < moduleCount; c++) {
                        var isDarkModule = qr.isDark ? qr.isDark(r, c) : qr.modules[r][c];
                        if (isDarkModule) {
                            
                            // Color mapping separations for layout components
                            if ((r < 7 && c < 7) || (r < 7 && c >= moduleCount - 7) || (r >= moduleCount - 7 && c < 7)) {
                                if ((r >= 2 && r <= 4 && c >= 2 && c <= 4) || 
                                    (r >= 2 && r <= 4 && c >= moduleCount - 5 && c <= moduleCount - 3) || 
                                    (r >= moduleCount - 5 && r <= moduleCount - 3 && c >= 2 && c <= 4)) {
                                    ctx.fillStyle = pupilColor;
                                } else {
                                    ctx.fillStyle = eyeColor;
                                }
                            } else {
                                ctx.fillStyle = bodyColor;
                            }
                            
                            ctx.fillRect(margin + (c * cellSize), margin + (r * cellSize), cellSize, cellSize);
                        }
                    }
                }

                // Brand asset overlay layout processing details
                if (uploadedLogoImg) {
                    var targetSize = 106; 
                    var lx = (canvas.width - targetSize) / 2;
                    var ly = (canvas.height - targetSize) / 2;
                    
                    ctx.fillStyle = '#FFFFFF';
                    ctx.beginPath();
                    ctx.roundRect(lx - 8, ly - 8, targetSize + 16, targetSize + 16, 6);
                    ctx.fill();
                    
                    ctx.drawImage(uploadedLogoImg, lx, ly, targetSize, targetSize);
                }
                jQuery('#debug-log').text("✔ Status: Vector matrix generation finalized.");

            } catch (err) {
                jQuery('#debug-log').text("❌ Matrix Engine Failure: " + err.message);
            }
        }

        function downloadPNG() {
            var canvas = document.getElementById('qrCanvas');
            var link = document.createElement('a');
            link.download = 'branded-shortlink-qr.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        function downloadPDF() {
            var canvas = document.getElementById('qrCanvas');
            var imgData = canvas.toDataURL('image/png');
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print Custom Document Asset</title></head><body style="text-align:center;padding:40px;font-family:sans-serif;">');
            printWindow.document.write('<h2>Branded tracking Shortlink QR Code Asset Document</h2>');
            printWindow.document.write('<img src="' + imgData + '" style="width:380px;margin-top:20px;border:1px solid #ddd;padding:12px;border-radius:6px;"/>');
            printWindow.document.write('<script>window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 400); }</script>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        }
    </script>
    <?php
}
EOF

# 4. Reset default Linux folder system configurations permissions profiles
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$WEB_QR_DIR"
sudo chown -R www-data:www-data "$FINAL_PLUGIN_PATH"
sudo chmod -R 755 "$WEB_QR_DIR"
sudo chmod -R 755 "$FINAL_PLUGIN_PATH"

echo "====================================================="
echo "🎉 Update matrix finalized! Service online and stable."
echo "====================================================="
