#!/bin/bash
set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_FOLDER="branded_qr_code"

echo "====================================================="
echo "⚙️  Initializing Branded QR Code Suite Setup Matrix"
echo "====================================================="

# Find YOURLS root
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: This script must be executed from within your YOURLS tree."
    exit 1
fi

echo "✔ YOURLS Root: $YOURLS_ROOT"

PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_FOLDER"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code"
sudo mkdir -p "$PLUGIN_DIR"

echo "📂 Plugin folder: $PLUGIN_DIR"

# Download files
echo "📥 Downloading files..."
sudo curl -sSL "$REPO_URL/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -sSL "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"
sudo curl -sSL "$REPO_URL/inline-qrcode.js" -o "$PLUGIN_DIR/inline-qrcode.js"

# Set permissions
echo "🔒 Setting permissions..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Installation complete! Hard refresh your browser."
echo "====================================================="
