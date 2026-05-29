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

# 1. Gracefully deactivate using internal CLI framework protocols if accessible
if [ -f "$YOURLS_ROOT/user/cli.php" ]; then
    echo "-> Deactivating module hooks..."
    sudo php "$YOURLS_ROOT/user/cli.php" plugin deactivate "$TARGET_PLUGIN_NAME" || true
fi

# 2. Drop disk assets completely
if [ -d "$PLUGIN_DIR" ]; then
    echo "-> Sweeping system plugin file trees..."
    sudo rm -rf "$PLUGIN_DIR"
    echo "✔ Extension storage structures deleted successfully."
else
    echo "ℹ Notice: Plugin storage path already clean."
fi

echo "====================================================="
echo "🎉 System uninstalled and wiped cleanly!"
echo "====================================================="
