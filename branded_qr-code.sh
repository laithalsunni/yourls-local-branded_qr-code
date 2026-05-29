#!/bin/bash

# ==============================================================================
# Branded QR Code Suite - Underscore Target Safe Installer
# ==============================================================================

set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_PLUGIN_NAME="branded_qr_code"

echo "====================================================="
echo "⚙️  Initializing Branded QR Code Suite Installation"
echo "====================================================="

# 1. Map absolute execution root pathways
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: Execute this installer within your YOURLS layout root."
    exit 1
fi

echo "✔ Confirmed YOURLS Root: $YOURLS_ROOT"

# 2. Hard purge legacy subdirectories and bad formatting name variations
if [ -d "$YOURLS_ROOT/qr" ]; then
    echo "🧹 Wiping legacy standalone /qr directories..."
    sudo rm -rf "$YOURLS_ROOT/qr"
fi

if [ -f "$YOURLS_ROOT/.htaccess" ]; then
    sudo sed -i '/rewrite.*qr/d' "$YOURLS_ROOT/.htaccess" || true
fi

# Deep clean broken folder caches causing metadata panel block outs
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr_code"

# 3. Initialize fresh underscored target plugin space
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_PLUGIN_NAME"
echo "📂 Synchronizing Workspace at: $PLUGIN_DIR"
sudo mkdir -p "$PLUGIN_DIR"

# 4. Pull distribution assets directly from main branch components
echo "📥 Downloading production-ready script manifests..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/inline-qrcode.js" -o "$PLUGIN_DIR/inline-qrcode.js"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"

# 5. Set operational system permission levels
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Installation successfully corrected!"
echo "👉 Refresh Manage Plugins to activate."
echo "====================================================="
