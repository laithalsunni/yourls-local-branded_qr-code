#!/bin/bash
set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_PLUGIN_NAME="branded_qr_code"

echo "====================================================="
echo "⚙️ Initializing Branded QR Code Engine Patch"
echo "====================================================="

if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: Run this installer from within your YOURLS root folder."
    exit 1
fi

# Deep clean legacy variants to prevent metadata conflicts
sudo rm -rf "$YOURLS_ROOT/qr"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code"

PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_PLUGIN_NAME"
sudo mkdir -p "$PLUGIN_DIR"

echo "📥 Sourcing production assets..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/inline-qrcode.js" -o "$PLUGIN_DIR/inline-qrcode.js"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"

echo "🔒 Resetting server permission profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Fixed successfully! Reload your plugin page."
echo "====================================================="
