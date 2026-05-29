#!/bin/bash
# ==============================================================================
# Branded QR Code Production Installer Script for YOURLS (Explicit Logo Trigger)
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

# 2. Re-verify target workspace folders
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_FOLDER"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code"
sudo mkdir -p "$PLUGIN_DIR"

echo "📂 Synchronizing Workspace at: $PLUGIN_DIR"

# 3. Pull production static assets from raw repository paths
echo "📥 Downloading production-ready script manifests..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/inline-qrcode.js" -o "$PLUGIN_DIR/inline-qrcode.js"

# 4. Reset standard Linux directory profiles
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Installer script successfully fixed!"
echo "====================================================="
