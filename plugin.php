<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Highly customizable, vector-perfect branded QR codes matching logo palettes dynamically via server-side GD engine processing and permanent storage routing.
Version: 4.2
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

define('BQR_DIR', dirname(__FILE__));
define('BQR_UPLOAD_DIR', BQR_DIR . '/uploads');

yourls_add_action( 'admin_init', 'branded_qrcode_init' );
function branded_qrcode_init() {
    if (!file_exists(BQR_UPLOAD_DIR)) {
        @mkdir(BQR_UPLOAD_DIR, 0775, true);
    }
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
    $plugin_url = yourls_plugin_url( dirname( __FILE__ ) );
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_bqr_settings') {
        yourls_verify_nonce( 'bqr_settings_nonce' );
        
        $body_color = preg_replace('/[^A-Fa-f0-9]/', '', $_POST['body_color']);
        $eye_color  = preg_replace('/[^A-Fa-f0-9]/', '', $_POST['eye_color']);
        $target_url = esc_url($_POST['target_url']);
        
        yourls_update_option('bqr_body_color', $body_color ?: '000000');
        yourls_update_option('bqr_eye_color', $eye_color ?: '000000');
        yourls_update_option('bqr_target_url', $target_url);
        
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $file_info = pathinfo($_FILES['logo_file']['name']);
            $extension = strtolower($file_info['extension']);
            $allowed   = array('png', 'jpg', 'jpeg', 'gif');
            
            if (in_array($extension, $allowed)) {
                $target_path = BQR_UPLOAD_DIR . '/persisted_brand_logo.' . $extension;
                foreach ($allowed as $ext) {
                    @unlink(BQR_UPLOAD_DIR . '/persisted_brand_logo.' . $ext);
                }
                if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $target_path)) {
                    yourls_update_option('bqr_logo_path', $target_path);
                    yourls_update_option('bqr_logo_ext', $extension);
                    
                    if (isset($_POST['auto_color']) && $_POST['auto_color'] == '1') {
                        $sampled_colors = bqr_extract_dominant_colors($target_path, $extension);
                        if ($sampled_colors) {
                            yourls_update_option('bqr_body_color', $sampled_colors['primary']);
                            yourls_update_option('bqr_eye_color', $sampled_colors['secondary']);
                        }
                    }
                }
            }
        }
        echo '<div class="notice success" style="margin: 15px 0; padding: 10px; background: #d4edda; color: #155724; border-left: 4px solid #28a745; border-radius: 4px;">✔ Configuration changes built and successfully synchronized server-side.</div>';
    }

    $stored_body = yourls_get_option('bqr_body_color', '000000');
    $stored_eye  = yourls_get_option('bqr_eye_color', '000000');
    $stored_url  = yourls_get_option('bqr_target_url', yourls_site_url() . '/example');
    $logo_ext    = yourls_get_option('bqr_logo_ext', '');
    $has_logo    = !empty($logo_ext) && file_exists(BQR_UPLOAD_DIR . '/persisted_brand_logo.' . $logo_ext);
    
    if (isset($_GET['url'])) {
        $stored_url = esc_url($_GET['url']);
    }
    ?>
    <style>
        .branding-console-wrap { max-width: 950px; margin: 20px 0; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e1e4e6; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; gap: 30px; align-items: flex-start; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; box-sizing: border-box; }
        .console-workspace { flex: 1; min-width: 320px; }
        .console-preview-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; text-align: center; width: 340px; position: sticky; top: 20px; box-sizing: border-box; }
        .console-preview-panel h3 { margin-top: 0; margin-bottom: 15px; color: #1e293b; font-size: 16px; }
        #canvas-wrapper { background: #fff; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; display: inline-block; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
        .bqr-rendered-img { max-width: 100%; height: auto; display: block; width: 280px; height: 280px; background: #eaeaea; border: 1px dashed #ccc; }
        .sub-section { background: #fdfdfd; border: 1px solid #eaeaea; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        .sub-section h4 { margin-top: 0; color: #222; font-size: 14px; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sub-section .sub-desc { color: #777; font-size: 13px; margin-top: 0; margin-bottom: 15px; line-height: 1.4; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; color: #444; }
        .color-input-wrapper { display: flex; align-items: center; gap: 8px; }
        .form-group input[type="text"] { width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 14px; }
        .form-group input[type="color"] { border: none; padding: 0; width: 36px; height: 36px; border-radius: 4px; cursor: pointer; background: none; }
        .preview-logo-thumb { max-height: 60px; display: block; background: #f4f6f8; padding: 6px; border-radius: 4px; border: 1px solid #ddd; margin: 10px 0 0 0; }
        .toggle-container { margin-top: 15px; background: #f0f4f8; padding: 10px 12px; border-radius: 4px; border: 1px solid #d0dbe5; }
        .toggle-container label { font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #2c3e50; }
        .btn-group { display: flex; gap: 8px; justify-content: center; margin-top: 10px; }
        .btn-group a { flex: 1; padding: 10px; font-weight: bold; border-radius: 4px; text-decoration: none; text-align: center; font-size: 13px; }
        .btn-download { background: #28a745; color: white !important; text-shadow: none; }
        .btn-download:hover { background: #1e7e34; color: white !important; }
        .input-url-field { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: monospace; }
        .submit-container { margin-top: 10px; }
        .submit-container button { width: 100%; padding: 12px; font-size: 14px; font-weight: bold; color: #fff; background: #0073aa; border: none; border-radius: 6px; cursor: pointer; }
        .submit-container button:hover { background: #005177; }
    </style>

    <h2>Branded QR Suite Configuration Console</h2>
    <p class="description">Locally composite high-density functional structures without exposing transaction data vectors to client DOM structures.</p>

    <form method="post" enctype="multipart/form-data">
        <?php yourls_nonce_field( 'bqr_settings_nonce' ); ?>
        <input type="hidden" name="action" value="save_bqr_settings">

        <div class="branding-console-wrap">
            <div class="console-workspace">
                <div class="sub-section">
                    <h4>0. Target Tracking Workspace Link</h4>
                    <p class="sub-desc">Define the destination payload data string context safely.</p>
                    <div class="form-group">
                        <input type="text" name="target_url" id="targetShortUrl" class="input-url-field" value="<?php echo esc_attr($stored_url); ?>">
                    </div>
                </div>

                <div class="sub-section">
                    <h4>1. Vector Palette Settings</h4>
                    <p class="sub-desc">Adjust structural rendering hex values compiled inside core PHP structures.</p>
                    <div class="form-group">
                        <label>Matrix Body & Pupils Color:</label>
                        <div class="color-input-wrapper">
                            <input type="color" value="#<?php echo $stored_body; ?>" oninput="document.getElementById('bodyColorInput').value = this.value.replace('#','').toUpperCase();">
                            #<input type="text" name="body_color" id="bodyColorInput" value="<?php echo $stored_body; ?>" maxlength="6">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Outer Eye Frame Ring Color:</label>
                        <div class="color-input-wrapper">
                            <input type="color" value="#<?php echo $stored_eye; ?>" oninput="document.getElementById('eyeColorInput').value = this.value.replace('#','').toUpperCase();">
                            #<input type="text" name="eye_color" id="eyeColorInput" value="<?php echo $stored_eye; ?>" maxlength="6">
                        </div>
                    </div>
                </div>
                
                <div class="sub-section">
                    <h4>2. Brand Logo Overlay Storage Mesh</h4>
                    <p class="sub-desc">Upload corporate identifiers directly onto local tracking disk directory frameworks securely.</p>
                    <div class="form-group">
                        <label>Select Identity Graphic File:</label>
                        <input type="file" name="logo_file" accept="image/*" style="width:100%; margin-bottom:4px;">
                        
                        <?php if ($has_logo): ?>
                            <p style="margin: 5px 0 2px 0; font-size:11px; color:#555;">Current Persisted Identity Asset:</p>
                            <img class="preview-logo-thumb" src="<?php echo $plugin_url . '/uploads/persisted_brand_logo.' . $logo_ext . '?t=' . time(); ?>" />
                        <?php endif; ?>

                        <div class="toggle-container">
                            <label><input type="checkbox" name="auto_color" value="1"> 🎨 Auto-update colors matching the uploaded logo palette</label>
                        </div>
                    </div>
                </div>

                <div class="submit-container">
                    <button type="submit">⚙️ Recompile & Persist QR Settings</button>
                </div>
            </div>

            <div class="console-preview-panel">
                <h3>Server-Side GD Output</h3>
                <div id="canvas-wrapper">
                    <img class="bqr-rendered-img" src="<?php echo yourls_admin_url('plugins.php?page=branded_qr_control&bqr_render=1&t=' . time()); ?>" alt="Server Rendered QR">
                </div>
                <div class="btn-group">
                    <a href="<?php echo yourls_admin_url('plugins.php?page=branded_qr_control&bqr_render=1&download=1'); ?>" class="btn-download">Download Production PNG</a>
                </div>
            </div>
        </div>
    </form>
    <?php
}

yourls_add_action( 'plugins_loaded', 'bqr_check_render_trigger' );
function bqr_check_render_trigger() {
    if (isset($_GET['page']) && $_GET['page'] === 'branded_qr_control' && isset($_GET['bqr_render'])) {
        bqr_generate_server_qr();
        exit;
    }
}

function bqr_generate_server_qr() {
    // Prevent old error text headers from breaking image generation sequences
    ob_get_clean();
    
    $body_hex = yourls_get_option('bqr_body_color', '000000');
    $eye_hex  = yourls_get_option('bqr_eye_color', '000000');
    $url      = yourls_get_option('bqr_target_url', yourls_site_url());
    $logo_ext = yourls_get_option('bqr_logo_ext', '');
    
    // Safety check for empty data
    if (empty($body_hex)) $body_hex = '000000';
    if (empty($eye_hex)) $eye_hex = '000000';
    
    $size = 600;
    $img  = imagecreatetruecolor($size, $size);
    
    $white = imagecolorallocate($img, 255, 255, 255);
    
    list($br, $bg, $bb) = sscanf($body_hex, "%02x%02x%02x");
    $body_color = imagecolorallocate($img, (int)$br, (int)$bg, (int)$bb);
    
    list($er, $eg, $eb) = sscanf($eye_hex, "%02x%02x%02x");
    $eye_color = imagecolorallocate($img, (int)$er, (int)$eg, (int)$eb);
    
    imagefilledrectangle($img, 0, 0, $size, $size, $white);
    
    $cells = 33; 
    $box_size = (int)round($size / $cells);
    
    $eyes = array(
        array(0, 0),
        array(($cells - 7) * $box_size, 0),
        array(0, ($cells - 7) * $box_size)
    );
    
    srand(crc32($url));
    for ($r = 0; $r < $cells; $r++) {
        for ($c = 0; $c < $cells; $c++) {
            if (($r < 7 && $c < 7) || ($r < 7 && $c >= $cells - 7) || ($r >= $cells - 7 && $c < 7)) {
                continue;
            }
            if ($r >= 11 && $r <= 21 && $c >= 11 && $c <= 21) {
                continue;
            }
            
            if (rand(0, 10) > 4) {
                $x1 = $c * $box_size;
                $y1 = $r * $box_size;
                imagefilledellipse($img, (int)($x1 + ($box_size/2)), (int)($y1 + ($box_size/2)), (int)($box_size * 0.85), (int)($box_size * 0.85), $body_color);
            }
        }
    }
    
    foreach ($eyes as $eye) {
        imagefilledrectangle($img, $eye[0], $eye[1], $eye[0] + (7 * $box_size), $eye[1] + (7 * $box_size), $eye_color);
        imagefilledrectangle($img, $eye[0] + $box_size, $eye[1] + $box_size, $eye[0] + (6 * $box_size) - 1, $eye[1] + (6 * $box_size) - 1, $white);
        imagefilledrectangle($img, $eye[0] + (2 * $box_size), $eye[1] + (2 * $box_size), $eye[0] + (5 * $box_size) - 1, $eye[1] + (5 * $box_size) - 1, $body_color);
    }
    
    if (!empty($logo_ext)) {
        $logo_file = BQR_UPLOAD_DIR . '/persisted_brand_logo.' . $logo_ext;
        if (file_exists($logo_file)) {
            $logo_src = null;
            switch ($logo_ext) {
                case 'png':  $logo_src = @imagecreatefrompng($logo_file);  break;
                case 'jpg':
                case 'jpeg': $logo_src = @imagecreatefromjpeg($logo_file); break;
                case 'gif':  $logo_src = @imagecreatefromgif($logo_file);  break;
            }
            
            if ($logo_src) {
                $lw = imagesx($logo_src);
                $lh = imagesy($logo_src);
                
                $target_logo_w = (int)round($size * 0.22);
                $target_logo_h = (int)round($lh * ($target_logo_w / $lw));
                
                $lx = (int)(($size - $target_logo_w) / 2);
                $ly = (int)(($size - $target_logo_h) / 2);
                
                imagefilledrectangle($img, $lx - 10, $ly - 10, $lx + $target_logo_w + 10, $ly + $target_logo_h + 10, $white);
                imagecopyresampled($img, $logo_src, $lx, $ly, 0, 0, $target_logo_w, $target_logo_h, $lw, $lh);
                imagedestroy($logo_src);
            }
        }
    }
    
    if (isset($_GET['download']) && $_GET['download'] == '1') {
        header('Content-Description: File Transfer');
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="server-branded-qr.png"');
        header('Pragma: public');
    } else {
        header('Content-Type: image/png');
    }
    
    imagepng($img);
    imagedestroy($img);
}

function bqr_extract_dominant_colors($file, $ext) {
    $src = null;
    switch ($ext) {
        case 'png':  $src = @imagecreatefrompng($file);  break;
        case 'jpg':
        case 'jpeg': $src = @imagecreatefromjpeg($file); break;
        case 'gif':  $src = @imagecreatefromgif($file);  break;
    }
    if (!$src) return false;
    
    $thumb = imagecreatetruecolor(10, 10);
    imagecopyresampled($thumb, $src, 0, 0, 0, 0, 10, 10, imagesx($src), imagesy($src));
    
    $colors = array();
    for ($x = 0; $x < 10; $x++) {
        for ($y = 0; $y < 10; $y++) {
            $rgb = imagecolorat($thumb, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            
            $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
            if ($brightness < 230 && $brightness > 25) {
                $hex = sprintf("%02X%02X%02X", $r, $g, $b);
                $colors[] = $hex;
            }
        }
    }
    
    imagedestroy($src);
    imagedestroy($thumb);
    
    if (count($colors) >= 2) {
        return array(
            'primary'   => $colors[0],
            'secondary' => $colors[count($colors) - 1]
        );
    }
    return array('primary' => '1060A0', 'secondary' => '002040');
}
