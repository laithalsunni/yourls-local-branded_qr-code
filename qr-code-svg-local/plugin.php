<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable, vector-perfect branded QR codes matching logo palettes dynamically.
Version: 2.2
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

// Secure direct execution check
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Hook script dependencies straight into administrative head blocks
yourls_add_action( 'html_head', 'branded_qr_suite_dashboard_assets' );

function branded_qr_suite_dashboard_assets() {
    $plugin_url = yourls_plugin_url( dirname( __FILE__ ) );
    $site_url = yourls_site_url();
    
    // Explicitly calculate paths to locate libraries loaded on the local web server root
    $local_engine_js = rtrim($site_url, '/') . '/qr/js/qrcode.min.js';
    $custom_studio_html = rtrim($site_url, '/') . '/qr/index.html';
    
    echo "\n\n";
    echo "<script type=\"text/javascript\">\n";
    echo "  window.BRANDED_QR_STUDIO_PATH = '" . $custom_studio_html . "';\n";
    echo "</script>\n";
    echo "<script type=\"text/javascript\" src=\"" . $local_engine_js . "\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"" . $plugin_url . "/inline-qrcode.js\"></script>\n";
}
