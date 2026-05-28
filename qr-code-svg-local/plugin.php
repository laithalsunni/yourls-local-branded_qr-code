<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable, vector-perfect branded QR codes matching logo palettes dynamically.
Version: 2.1
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

// Secure direct execution check
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Register hooks to inject script parameters straight into the administrative head block
yourls_add_action( 'html_head', 'branded_qr_inject_assets' );

function branded_qr_inject_assets() {
    $plugin_url = yourls_plugin_url( dirname( __FILE__ ) );
    $site_url = yourls_site_url();
    
    echo "\n\n";
    echo "<script type=\"text/javascript\">\n";
    echo "  window.BRANDED_QR_WEBROOT = '" . rtrim($site_url, '/') . "/qr/index.html';\n";
    echo "</script>\n";
    echo "<script src=\"" . $plugin_url . "/inline-qrcode.js\" type=\"text/javascript\"></script>\n";
    echo "<style>div.branded-share-qr { float: right; margin-right: 0.2em !important; padding: 0 5px 10px !important; text-align: center; } div.branded-share-qr img { width: 100px; height: 100px; border: 1px solid #e1e4e6; border-radius: 4px; padding: 4px; background: #fff; transition: transform 0.2s; } div.branded-share-qr img:hover { transform: scale(1.05); }</style>\n";
}
