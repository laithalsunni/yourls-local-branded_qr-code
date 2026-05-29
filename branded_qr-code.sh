#!/bin/bash

# ==============================================================================
# Branded QR Code Suite - High Fidelity Interface Deployment Hook
# ==============================================================================

set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_PLUGIN_NAME="branded_qr_code"

echo "====================================================="
echo "⚙️ Initializing Branded QR Code Suite Setup Matrix"
echo "====================================================="

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

# Deep clean legacy and conflicting folders
sudo rm -rf "$YOURLS_ROOT/qr"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr_code"

# Create a clean working workspace layout
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_PLUGIN_NAME"
echo "📂 Synchronizing Workspace at: $PLUGIN_DIR"
sudo mkdir -p "$PLUGIN_DIR"

# Download unedited operational component arrays straight from main branch
echo "📥 Downloading production-ready script manifests..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/inline-qrcode.js" -o "$PLUGIN_DIR/inline-qrcode.js"
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/user/plugins/branded_qr_code/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"

# Adjust file authorization profiles to align with the active server environment
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Installation successfully corrected!"
echo "👉 Refresh Manage Plugins to activate."
echo "====================================================="
