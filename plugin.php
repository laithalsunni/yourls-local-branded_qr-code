<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable, vector-perfect branded QR codes matching default administration settings.
Version: 3.0
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

// Secure direct execution check
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Register hooks
yourls_add_action( 'html_head', 'branded_qr_suite_inject_dashboard' );
yourls_add_action( 'plugins_loaded', 'branded_qr_suite_init_admin' );

// 1. Inject assets and configuration states directly into the administration UI
function branded_qr_suite_inject_dashboard() {
    $plugin_url = yourls_plugin_url( dirname( __FILE__ ) );
    
    // Fetch stored database options or default back gracefully
    $body_color = yourls_get_option('branded_qr_body_color', '#000000');
    $eye_color  = yourls_get_option('branded_qr_eye_color', '#000000');
    $logo_data  = yourls_get_option('branded_qr_logo_b64', '');
    
    echo "\n\n";
    echo "<script type=\"text/javascript\">\n";
    echo "  window.BRANDED_QR_CONFIG = {\n";
    echo "    bodyColor: '" . esc_js($body_color) . "',\n";
    echo "    eyeColor: '" . esc_js($eye_color) . "',\n";
    echo "    logoData: '" . esc_js($logo_data) . "'\n";
    echo "  };\n";
    echo "</script>\n";
    echo "<script type=\"text/javascript\" src=\"" . $plugin_url . "/qrcode.min.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"" . $plugin_url . "/inline-qrcode.js\"></script>\n";
}

// 2. Register Administration Control Page settings under the Plugins menu
function branded_qr_suite_init_admin() {
    yourls_register_plugin_page( 'branded_qr_settings', 'Branded QR Settings', 'branded_qr_suite_render_admin_page' );
}

// 3. Render the interactive Administration Page UI form panel layout
function branded_qr_suite_render_admin_page() {
    // Check if configuration updates were posted to the server
    if( isset( $_POST['submit_branded_qr'] ) ) {
        yourls_verify_nonce( 'branded_qr_nonce' );
        
        $body = sanitize_hex_color($_POST['body_color']);
        $eye  = sanitize_hex_color($_POST['eye_color']);
        
        yourls_update_option( 'branded_qr_body_color', $body );
        yourls_update_option( 'branded_qr_eye_color', $eye );
        
        // Process base64 file data streaming uploads cleanly
        if( !empty($_FILES['logo_file']['tmp_name']) ) {
            $file_type = $_FILES['logo_file']['type'];
            $allowed = array('image/jpeg', 'image/png', 'image/gif', 'image/jpg');
            
            if( in_array($file_type, $allowed) ) {
                $data = file_get_contents($_FILES['logo_file']['tmp_name']);
                $base64 = 'data:' . $file_type . ';base64,' . base64_encode($data);
                yourls_update_option( 'branded_qr_logo_b64', $base64 );
            }
        } elseif( isset($_POST['clear_logo_flag']) && $_POST['clear_logo_flag'] == '1' ) {
            yourls_update_option( 'branded_qr_logo_b64', '' );
        }
        
        echo '<div class="info" style="background:#e2f0d9; color:#385723; border:1px solid #c5e0b4; padding:10px; margin:10px 0; border-radius:4px; font-weight:bold;">✔ Global Branded QR Engine settings updated successfully!</div>';
    }

    $body_color = yourls_get_option('branded_qr_body_color', '#000000');
    $eye_color  = yourls_get_option('branded_qr_eye_color', '#000000');
    $logo_data  = yourls_get_option('branded_qr_logo_b64', '');
    $nonce = yourls_create_nonce( 'branded_qr_nonce' );
    
    ?>
    <div style="max-width:700px; margin:20px auto; background:#fff; padding:25px; border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.05); font-family:sans-serif;">
        <h2 style="margin-top:0; border-bottom:2px solid #f0f4f8; padding-bottom:10px;">🎨 Branded QR Defaults Console</h2>
        <p style="color:#666; font-size:14px; margin-bottom:25px;">Set up the default appearance profiles for your tracking link QR elements below. These styles apply instantly across your dashboard.</p>
        
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="nonce" value="<?php echo $nonce; ?>" />
            
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:bold; margin-bottom:6px; font-size:14px;">1. Matrix Body Dots Color:</label>
                <input type="color" name="body_color" value="<?php echo echo_html($body_color); ?>" style="width:50px; height:40px; padding:0; border:none; cursor:pointer; vertical-align:middle;" />
                <input type="text" value="<?php echo echo_html($body_color); ?>" disabled style="width:100px; padding:8px; margin-left:10px; background:#f4f6f8; border:1px solid #ddd; text-transform:uppercase; font-family:monospace;" />
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:bold; margin-bottom:6px; font-size:14px;">2. Outer Eye Ring Tracking Color:</label>
                <input type="color" name="eye_color" value="<?php echo echo_html($eye_color); ?>" style="width:50px; height:40px; padding:0; border:none; cursor:pointer; vertical-align:middle;" />
                <input type="text" value="<?php echo echo_html($eye_color); ?>" disabled style="width:100px; padding:8px; margin-left:10px; background:#f4f6f8; border:1px solid #ddd; text-transform:uppercase; font-family:monospace;" />
            </div>

            <div style="margin-bottom:25px; background:#f8fafc; padding:15px; border-radius:6px; border:1px solid #e2e8f0;">
                <label style="display:block; font-weight:bold; margin-bottom:6px; font-size:14px;">3. Center Identity Brand Logo Overlay:</label>
                <p style="color:#777; font-size:12px; margin-top:0;">Upload a clear square image asset (PNG/JPG) to overlay directly into the core matrix grid.</p>
                <input type="file" name="logo_file" accept="image/*" style="margin-bottom:10px;" />
                
                <?php if(!empty($logo_data)): ?>
                    <div style="margin-top:10px; padding-top:10px; border-top:1px dashed #cbd5e1;">
                        <span style="font-size:12px; font-weight:bold; display:block; margin-bottom:5px;">Active Logo Profile Preview:</span>
                        <img src="<?php echo $logo_data; ?>" style="max-height:60px; background:#fff; padding:4px; border:1px solid #cbd5e1; border-radius:4px;" />
                        <label style="display:block; margin-top:8px; font-size:13px; color:#ba1a1a; cursor:pointer;">
                            <input type="checkbox" name="clear_logo_flag" value="1" /> 🚨 Remove current branding logo on next save
                        </label>
                    </div>
                <?php endif; ?>
            </div>

            <div style="border-top:1px solid #f0f4f8; padding-top:15px;">
                <input type="submit" name="submit_branded_qr" value="Save Default Branding Settings" class="button primary" style="background:#0073aa; color:#fff; border:none; padding:10px 20px; font-weight:bold; border-radius:4px; cursor:pointer;" />
            </div>
        </form>
    </div>
    <?php
}

// Simple color field security handling fallback helper
function sanitize_hex_color($color) {
    if (preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
        return $color;
    }
    return '#000000';
}
