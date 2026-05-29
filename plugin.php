<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable, vector-perfect branded QR codes matching logo palettes dynamically.
Version: 2.0
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

// Secure core environment execution hook check
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Hook script assets into the HTML header
yourls_add_action( 'html_head', 'branded_qrcode_assets' );

function branded_qrcode_assets() {
    // Dynamically calculate our plugin directory path URL
    $plugin_url = yourls_plugin_url( dirname( __FILE__ ) );
    
    // Inject structural dependencies cleanly into the DOM header
    echo '<script type="text/javascript" src="' . $plugin_url . '/qrcode.min.js"></script>' . "\n";
    echo '<script type="text/javascript" src="' . $plugin_url . '/inline-qrcode.js"></script>' . "\n";
}
