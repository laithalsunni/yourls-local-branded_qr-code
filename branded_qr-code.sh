#!/bin/bash
# ==============================================================================
# Branded QR Code Installer (Server-Side Edition)
# ==============================================================================
set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_FOLDER="branded_qr_code"

echo "====================================================="
echo "⚙️  Installing Branded QR Code Suite (Server-Side)"
echo "====================================================="

# Locate YOURLS root
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: This script must be run from within your YOURLS tree."
    exit 1
fi

echo "✔ YOURLS Root: $YOURLS_ROOT"

PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_FOLDER"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code" 2>/dev/null
sudo rm -rf "$PLUGIN_DIR" 2>/dev/null
sudo mkdir -p "$PLUGIN_DIR"

echo "📂 Creating plugin folder: $PLUGIN_DIR"

# Download required files
echo "📥 Downloading server-side components..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -H "Cache-Control: no-cache" -sSL "https://raw.githubusercontent.com/t0k4rt/phpqrcode/master/phpqrcode.php" -o "$PLUGIN_DIR/phpqrcode.php"

# Set permissions
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Installation complete!"
echo "👉 Activate 'Branded QR Code Suite' in your YOURLS plugin manager."
echo "====================================================="
