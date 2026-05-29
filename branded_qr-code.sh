#!/bin/bash

# ==============================================================================
# Branded QR Code Suite - Unified High Compatibility Installer
# ==============================================================================

set -e

# Repository endpoint definition matching your exact folder structures
REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_PLUGIN_NAME="branded_qr-code"

echo "====================================================="
echo "⚙️  Initializing Branded QR Code Suite Installation"
echo "====================================================="

# 1. Verify working context layout
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

# 2. Cleanup broken variations and legacy paths
if [ -d "$YOURLS_ROOT/qr" ]; then
    echo "🧹 Removing legacy /qr web directories..."
    sudo rm -rf "$YOURLS_ROOT/qr"
fi

if [ -f "$YOURLS_ROOT/.htaccess" ]; then
    echo "🔀 Cleaning legacy rewrite footprints from .htaccess..."
    sudo sed -i '/rewrite.*qr/d' "$YOURLS_ROOT/.htaccess" || true
fi

# Clean up any bad folder variants
if [ -d "$YOURLS_ROOT/user/plugins/branded_qr_code" ]; then
    sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr_code"
fi

# 3. Create clean working folder structure workspace
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_PLUGIN_NAME"
echo "📂 Synchronizing Workspace at: $PLUGIN_DIR"
sudo rm -rf "$PLUGIN_DIR"
sudo mkdir -p "$PLUGIN_DIR"

# 4. Download source deliverables from GitHub using your exact repo layout map
echo "📥 Downloading production-ready script manifests..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/inline-qrcode.js" -o "$PLUGIN_DIR/inline-qrcode.js"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"

# 5. Set operational permissions matching your web group layout profiles
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Branded QR Suite successfully loaded!"
echo "👉 Navigate to 'Manage Plugins' to activate it."
echo "====================================================="
