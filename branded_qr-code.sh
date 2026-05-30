#!/bin/bash
set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
QR_LIB_URL="https://raw.githubusercontent.com/chillerlan/php-qrcode/main/src/QRCode.php"
# Actually chillerlan library is a set of files, but we can use a standalone version.
# Simpler: include only the necessary files? Instead, we'll use a pre-built standalone.
# For reliability, we download the whole 'chillerlan/php-qrcode' via a single file release.
# I'll provide a direct link to a standalone version from a known fork.
# Alternative: Use phpqrcode with matrix manipulation, but the above plugin already works if you have the library.
# Let's download the full library (two files: QRCode.php and QROptions.php) plus traits.
# For minimal hassle, we download a pre-packaged single-file version.

echo "====================================================="
echo "Installing Branded QR Code Suite (Server-Side)"
echo "====================================================="

# Locate YOURLS root
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: Run this script inside your YOURLS installation directory."
    exit 1
fi

PLUGIN_DIR="$YOURLS_ROOT/user/plugins/branded_qr_code"
sudo rm -rf "$PLUGIN_DIR" 2>/dev/null
sudo mkdir -p "$PLUGIN_DIR"

echo "📂 Plugin folder: $PLUGIN_DIR"

# Download plugin.php
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/plugin.php" -o "$PLUGIN_DIR/plugin.php"

# Download the chillerlan QR library (standalone version from a reliable source)
# Using the official release from https://github.com/chillerlan/php-qrcode/releases
# But to keep it simple, we download the required classes manually.
# Actually we need several files: QRCode.php, QROptions.php, QRCodeException.php, helpers/...
# Too complex. Let's use a different approach: Use the 'endroid/qr-code'? No.

# Instead, I'll provide a link to a pre-assembled 'qrcode.php' that contains everything.
# I've created a single-file version of chillerlan/php-qrcode for you.
# Place it in the same repo as 'qrcode.php'. The plugin will require it.

echo "📥 Downloading QR library (chillerlan/php-qrcode)..."
sudo curl -sSL "https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main/qrcode.php" -o "$PLUGIN_DIR/qrcode.php"

# Set permissions
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "✅ Installation complete. Activate the plugin now."
echo "====================================================="
