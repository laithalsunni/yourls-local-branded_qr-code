#!/bin/bash
# ==============================================================================
# Branded QR Code Production Installer Script for YOURLS (Fixed)
# ==============================================================================
set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_FOLDER="branded_qr_code"

echo "====================================================="
echo "⚙️  Initializing Branded QR Code Suite Setup Matrix"
echo "====================================================="

# 1. Verify execution directory context
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

echo "✔ Confirmed YOURLS Root: $YOURLS_ROOT"

# 2. Create plugin directory (remove old if exists)
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_FOLDER"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code" 2>/dev/null
sudo rm -rf "$PLUGIN_DIR" 2>/dev/null
sudo mkdir -p "$PLUGIN_DIR"

echo "📂 Synchronizing Workspace at: $PLUGIN_DIR"

# 3. Download production files from GitHub
echo "📥 Downloading production-ready script manifests..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/inline-qrcode.js" -o "$PLUGIN_DIR/inline-qrcode.js"

# 4. Set correct permissions
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Installer script successfully fixed!"
echo "====================================================="
