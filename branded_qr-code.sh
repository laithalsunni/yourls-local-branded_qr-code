#!/bin/bash

# ==============================================================================
# Branded QR Code Suite - High Compatibility Installer
# ==============================================================================

set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_PLUGIN_NAME="branded_qr-code"

echo "====================================================="
echo "⚙️  Initializing Branded QR Code Suite Installation"
echo "====================================================="

# 1. Scope active folder structure context
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

# 2. Complete Legacy Cleanup (Purging old detached architecture)
if [ -d "$YOURLS_ROOT/qr" ]; then
    echo "🧹 Removing legacy /qr web directories..."
    sudo rm -rf "$YOURLS_ROOT/qr"
fi

if [ -f "$YOURLS_ROOT/.htaccess" ]; then
    echo "🔀 Cleaning legacy rewrite footprints from .htaccess..."
    # Drop any matching .qr routing rules safely without breaking core blocks
    sudo sed -i '/rewrite.*qr/d' "$YOURLS_ROOT/.htaccess" || true
fi

# 3. Create Clean Local Extension Infrastructure Workspace
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_PLUGIN_NAME"
echo "📂 Synchronizing Workspace at: $PLUGIN_DIR"
sudo mkdir -p "$PLUGIN_DIR"

# 4. Fetch Distribution Deliverables Natively
echo "📥 Downloading production-ready script manifests..."
sudo curl -sSL "$REPO_URL/user/plugins/$TARGET_PLUGIN_NAME/plugin.php" -o "$PLUGIN_DIR/plugin.php"
sudo curl -sSL "$REPO_URL/user/plugins/$TARGET_PLUGIN_NAME/inline-qrcode.js" -o "$PLUGIN_DIR/inline-qrcode.js"
sudo curl -sSL "$REPO_URL/user/plugins/$TARGET_PLUGIN_NAME/qrcode.min.js" -o "$PLUGIN_DIR/qrcode.min.js"

# 5. Set Safe Server Permission Profiles
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Branded QR Suite successfully loaded!"
echo "👉 Navigate to 'Manage Plugins' to activate it."
echo "====================================================="
