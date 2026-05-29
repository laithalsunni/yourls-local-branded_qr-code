#!/bin/bash

# ==============================================================================
# Branded QR Code Suite - Clean Uninstaller Script
# ==============================================================================

set -e

TARGET_PLUGIN_NAME="branded_qr-code"

echo "====================================================="
echo "🗑️ Starting Clean Uninstallation Sequence"
echo "====================================================="

if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: Run this uninstaller within your YOURLS layout structure."
    exit 1
fi

PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_PLUGIN_NAME"

# Drop disk assets completely
if [ -d "$PLUGIN_DIR" ]; then
    echo "-> Sweeping system plugin file trees..."
    sudo rm -rf "$PLUGIN_DIR"
fi

if [ -d "$YOURLS_ROOT/user/plugins/yourls-local-branded_qr-code" ]; then
    sudo rm -rf "$YOURLS_ROOT/user/plugins/yourls-local-branded_qr-code"
fi

echo "====================================================="
echo "🎉 System uninstalled and wiped cleanly!"
echo "====================================================="
