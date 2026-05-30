<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Server‑side branded QR codes – fully customizable colors + logo overlay.
Version: 4.0
Author: Laith Alsunni
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Include QR code generator library
require_once dirname(__FILE__) . '/phpqrcode.php';

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
    // Handle form submission
    $qr_image = '';
    $error = '';
    if ( isset($_POST['generate_qr']) ) {
        $url = isset($_POST['target_url']) ? trim($_POST['target_url']) : '';
        $body_color = isset($_POST['body_color']) ? ltrim($_POST['body_color'], '#') : '000000';
        $eye_color = isset($_POST['eye_color']) ? ltrim($_POST['eye_color'], '#') : '000000';
        
        if ( empty($url) ) {
            $error = 'Please enter a valid URL.';
        } else {
            // Handle logo upload if provided
            $logo_path = null;
            if ( isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK ) {
                $upload_dir = sys_get_temp_dir();
                $tmp_name = basename($_FILES['logo_file']['name']);
                $logo_path = $upload_dir . '/' . uniqid() . '_' . $tmp_name;
                move_uploaded_file($_FILES['logo_file']['tmp_name'], $logo_path);
            }
            
            // Generate the branded QR code as a temporary PNG file
            $output_file = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            $success = generate_branded_qr($url, $output_file, $body_color, $eye_color, $logo_path);
            
            if ( $success && file_exists($output_file) ) {
                $qr_image = base64_encode(file_get_contents($output_file));
                $qr_image = 'data:image/png;base64,' . $qr_image;
                unlink($output_file);
            } else {
                $error = 'Failed to generate QR code. Check server logs.';
            }
            
            // Cleanup uploaded logo
            if ( $logo_path && file_exists($logo_path) ) {
                unlink($logo_path);
            }
        }
    }
    
    // Display admin page
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
        // Synchronize color picker and text input
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
 * Generate a branded QR code image file.
 *
 * @param string $data       URL or text to encode
 * @param string $outputFile Path where PNG will be saved
 * @param string $bodyColor  Hex color (without #) for data modules
 * @param string $eyeColor   Hex color for outer frames of position markers
 * @param string|null $logo  Path to logo image file (optional)
 * @return bool              Success
 */
function generate_branded_qr($data, $outputFile, $bodyColor, $eyeColor, $logo = null) {
    // Use phpqrcode to generate a raw matrix and a base black/white image
    // We'll generate a high-resolution QR code (say 1000x1000) then recolor.
    $size = 1000; // pixels
    $qr = QRcode::getMatrix($data, QR_ECLEVEL_H); // High error correction for logo overlay
    
    $moduleCount = $qr->getModuleCount();
    $cellSize = $size / $moduleCount;
    
    // Create a truecolor image
    $img = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $white);
    
    // Allocate colors
    $body_rgb = hex2rgb($bodyColor);
    $body_color = imagecolorallocate($img, $body_rgb['r'], $body_rgb['g'], $body_rgb['b']);
    $eye_rgb = hex2rgb($eyeColor);
    $eye_color = imagecolorallocate($img, $eye_rgb['r'], $eye_rgb['g'], $eye_rgb['b']);
    $black = imagecolorallocate($img, 0, 0, 0);
    $gray = imagecolorallocate($img, 128, 128, 128);
    
    // Draw modules
    for ($row = 0; $row < $moduleCount; $row++) {
        for ($col = 0; $col < $moduleCount; $col++) {
            if ($qr->check($row, $col)) {
                $x1 = $col * $cellSize;
                $y1 = $row * $cellSize;
                $x2 = $x1 + $cellSize;
                $y2 = $y1 + $cellSize;
                
                // Determine if this module is part of a position marker (finder pattern)
                $isEye = isInPositionMarker($row, $col, $moduleCount);
                $color = $isEye ? $eye_color : $body_color;
                imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);
            }
        }
    }
    
    // Overlay logo if provided
    if ($logo && file_exists($logo)) {
        $logo_img = imagecreatefromstring(file_get_contents($logo));
        if ($logo_img) {
            $logo_w = imagesx($logo_img);
            $logo_h = imagesy($logo_img);
            $target_size = $size * 0.25; // logo covers 25% of QR
            $dst_x = ($size - $target_size) / 2;
            $dst_y = ($size - $target_size) / 2;
            
            // Create a white background circle/square behind logo for contrast
            $white = imagecolorallocate($img, 255, 255, 255);
            imagefilledrectangle($img, $dst_x - 8, $dst_y - 8, $dst_x + $target_size + 8, $dst_y + $target_size + 8, $white);
            
            // Resample logo
            imagecopyresampled($img, $logo_img, $dst_x, $dst_y, 0, 0, $target_size, $target_size, $logo_w, $logo_h);
            imagedestroy($logo_img);
        }
    }
    
    // Save PNG
    imagepng($img, $outputFile);
    imagedestroy($img);
    return true;
}

/**
 * Check if a given QR module coordinate lies inside a position marker (finder pattern).
 * Finder patterns are 7x7 blocks at three corners: top-left, top-right, bottom-left.
 */
function isInPositionMarker($row, $col, $moduleCount) {
    $markerSize = 7;
    // Top-left
    if ($row < $markerSize && $col < $markerSize) return true;
    // Top-right
    if ($row < $markerSize && $col >= $moduleCount - $markerSize) return true;
    // Bottom-left
    if ($row >= $moduleCount - $markerSize && $col < $markerSize) return true;
    return false;
}

/**
 * Convert hex color to RGB.
 */
function hex2rgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return ['r' => $r, 'g' => $g, 'b' => $b];
}
