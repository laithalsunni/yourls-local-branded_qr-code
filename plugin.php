<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Server‑side branded QR codes – custom colors + logo overlay.
Version: 4.1
Author: Laith Alsunni
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

require_once dirname(__FILE__) . '/qrcode.php'; // chillerlan QR library

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

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
        'title'   => 'Generate Branded QR Code',
        'anchor'  => 'QR Code'
    );
    return $actions;
}

function branded_qrcode_admin_page() {
    $qr_image = '';
    $error = '';
    
    if ( isset($_POST['generate_qr']) ) {
        $url = isset($_POST['target_url']) ? trim($_POST['target_url']) : '';
        $body_color = isset($_POST['body_color']) ? ltrim($_POST['body_color'], '#') : '000000';
        $eye_color = isset($_POST['eye_color']) ? ltrim($_POST['eye_color'], '#') : '000000';
        
        if ( empty($url) ) {
            $error = 'Please enter a valid URL.';
        } else {
            // Handle logo upload
            $logo_path = null;
            if ( isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK ) {
                $upload_dir = sys_get_temp_dir();
                $tmp_name = basename($_FILES['logo_file']['name']);
                $logo_path = $upload_dir . '/' . uniqid() . '_' . $tmp_name;
                move_uploaded_file($_FILES['logo_file']['tmp_name'], $logo_path);
            }
            
            $output_file = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            $success = generate_branded_qr($url, $output_file, $body_color, $eye_color, $logo_path);
            
            if ( $success && file_exists($output_file) ) {
                $qr_image = base64_encode(file_get_contents($output_file));
                $qr_image = 'data:image/png;base64,' . $qr_image;
                unlink($output_file);
            } else {
                $error = 'QR generation failed. Check PHP error log.';
            }
            
            if ( $logo_path && file_exists($logo_path) ) {
                unlink($logo_path);
            }
        }
    }
    
    // Display admin page (same as before, but with error display)
    ?>
    <style>
        .qr-console-wrap { max-width: 1000px; margin: 20px 0; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e1e4e6; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; gap: 30px; flex-wrap: wrap; }
        .qr-form-area { flex: 2; min-width: 280px; }
        .qr-preview-area { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; text-align: center; }
        .qr-preview-area img { max-width: 100%; height: auto; border: 1px solid #ccc; background: white; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 6px; }
        .color-input { display: flex; align-items: center; gap: 10px; }
        .color-input input[type="color"] { width: 50px; height: 40px; border: 1px solid #ccc; }
        .color-input input[type="text"] { width: 80px; font-family: monospace; text-transform: uppercase; }
        input[type="text"], input[type="url"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-primary { background: #0073aa; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary:hover { background: #005177; }
        .error-message { color: #d63638; background: #f9e2e2; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
    
    <h2>Branded QR Code Suite – Server‑Side Engine</h2>
    <p>Customize colors, upload a logo, and generate a branded QR code entirely on the server.</p>
    
    <div class="qr-console-wrap">
        <div class="qr-form-area">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Target URL (short or long)</label>
                    <input type="url" name="target_url" value="<?php echo isset($_POST['target_url']) ? esc_url($_POST['target_url']) : yourls_site_url() . '/example'; ?>" required>
                </div>
                <div class="form-group">
                    <label>QR Code Body Color</label>
                    <div class="color-input">
                        <input type="color" name="body_color" value="#<?php echo isset($_POST['body_color']) ? ltrim($_POST['body_color'], '#') : '000000'; ?>">
                        <input type="text" name="body_color_text" value="<?php echo isset($_POST['body_color']) ? ltrim($_POST['body_color'], '#') : '000000'; ?>" maxlength="6">
                    </div>
                </div>
                <div class="form-group">
                    <label>Eye (Position Marker) Color</label>
                    <div class="color-input">
                        <input type="color" name="eye_color" value="#<?php echo isset($_POST['eye_color']) ? ltrim($_POST['eye_color'], '#') : '000000'; ?>">
                        <input type="text" name="eye_color_text" value="<?php echo isset($_POST['eye_color']) ? ltrim($_POST['eye_color'], '#') : '000000'; ?>" maxlength="6">
                    </div>
                </div>
                <div class="form-group">
                    <label>Logo (optional, PNG with transparency recommended)</label>
                    <input type="file" name="logo_file" accept="image/png,image/jpeg,image/webp">
                    <small>Logo will be centered and scaled to ~25% of QR size.</small>
                </div>
                <div class="form-group">
                    <button type="submit" name="generate_qr" class="btn-primary">✨ Generate QR Code</button>
                </div>
            </form>
        </div>
        <div class="qr-preview-area">
            <h3>Preview & Download</h3>
            <?php if ( $error ): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif ( $qr_image ): ?>
                <img src="<?php echo $qr_image; ?>" alt="Branded QR Code" style="max-width: 300px;">
                <p><a href="<?php echo $qr_image; ?>" download="branded_qr.png" class="btn-primary" style="display: inline-block; margin-top: 15px;">Download PNG</a></p>
            <?php else: ?>
                <p style="color: #888;">Fill in the form and click Generate to see your QR code here.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Sync color picker and text field
        document.querySelectorAll('input[name="body_color"]').forEach(function(picker) {
            picker.addEventListener('input', function() {
                document.querySelector('input[name="body_color_text"]').value = this.value.substring(1);
            });
        });
        document.querySelector('input[name="body_color_text"]').addEventListener('input', function() {
            let val = this.value.replace('#', '').toUpperCase();
            if ( /^[0-9A-F]{6}$/i.test(val) ) {
                document.querySelector('input[name="body_color"]').value = '#' + val;
            }
        });
        document.querySelectorAll('input[name="eye_color"]').forEach(function(picker) {
            picker.addEventListener('input', function() {
                document.querySelector('input[name="eye_color_text"]').value = this.value.substring(1);
            });
        });
        document.querySelector('input[name="eye_color_text"]').addEventListener('input', function() {
            let val = this.value.replace('#', '').toUpperCase();
            if ( /^[0-9A-F]{6}$/i.test(val) ) {
                document.querySelector('input[name="eye_color"]').value = '#' + val;
            }
        });
    </script>
    <?php
}

/**
 * Generate branded QR code using chillerlan/php-qrcode.
 */
function generate_branded_qr($data, $outputFile, $bodyColor, $eyeColor, $logo = null) {
    // Options for the QR code
    $options = new QROptions([
        'version'          => 7,               // adjust as needed (1-40)
        'outputType'       => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'         => QRCode::ECC_H,   // high error correction for logo
        'scale'            => 8,               // module size in pixels (final image ~ 400-600px)
        'imageBase64'      => false,
        'bgColor'          => '#ffffff',
        'moduleValues'     => [
            // data modules (the black squares)
            QRCode::M_DATA         => $bodyColor,
            // position finder modules (outer ring and inner dot)
            QRCode::M_FINDER       => $eyeColor,
            QRCode::M_FINDER_DOT   => $bodyColor,
        ]
    ]);
    
    try {
        $qrOutput = (new QRCode($options))->render($data);
        // $qrOutput is a PNG binary string. Save to file first.
        file_put_contents($outputFile, $qrOutput);
        
        // If a logo is provided, overlay it using GD
        if ($logo && file_exists($logo)) {
            $qrImg = imagecreatefrompng($outputFile);
            if (!$qrImg) return false;
            
            $logoImg = imagecreatefromstring(file_get_contents($logo));
            if ($logoImg) {
                $qrW = imagesx($qrImg);
                $qrH = imagesy($qrImg);
                $logoW = imagesx($logoImg);
                $logoH = imagesy($logoImg);
                
                // Scale logo to 25% of QR size
                $targetSize = $qrW * 0.25;
                $dstX = ($qrW - $targetSize) / 2;
                $dstY = ($qrH - $targetSize) / 2;
                
                // White background behind logo (optional, improves contrast)
                $white = imagecolorallocate($qrImg, 255, 255, 255);
                imagefilledrectangle($qrImg, $dstX - 5, $dstY - 5, $dstX + $targetSize + 5, $dstY + $targetSize + 5, $white);
                
                // Resample logo onto QR
                imagecopyresampled($qrImg, $logoImg, $dstX, $dstY, 0, 0, $targetSize, $targetSize, $logoW, $logoH);
                imagedestroy($logoImg);
            }
            // Save the final image
            imagepng($qrImg, $outputFile);
            imagedestroy($qrImg);
        }
        return true;
    } catch (Exception $e) {
        error_log('QR generation error: ' . $e->getMessage());
        return false;
    }
}
