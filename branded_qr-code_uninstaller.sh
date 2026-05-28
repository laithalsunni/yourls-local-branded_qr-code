#!/bin/bash

# ==============================================================================
# Branded QR Code Clean Uninstaller Script for YOURLS (With Deactivation Guard)
# ==============================================================================

set -e

TARGET_PLUGIN_NAME="branded_qr-code"

echo "================================================"
echo "Starting Branded QR Engine Uninstallation"
echo "================================================"

# 1. Locate execution context relative to YOURLS layout
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
    echo "✔ Success: Located execution context at YOURLS Root: $YOURLS_ROOT"
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../
    YOURLS_ROOT=$(pwd)
    echo "✔ Success: Shifted execution context to YOURLS Root: $YOURLS_ROOT"
else
    echo "❌ Error: This uninstaller must be executed inside your YOURLS root folder structure."
    exit 1
fi

PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_PLUGIN_NAME"
WEB_QR_DIR="$YOURLS_ROOT/qr"

# 2. Deactivation Sequence
echo "-> Deactivating add-on from active plugins index..."
# If your environment uses YOURLS CLI command runners, we call it here; 
# otherwise, physically dropping the folder below will force YOURLS to auto-disable it on next load.
if [ -f "user/cli.php" ]; then
    printf "wp-cli / yourls-cli signature caught. Running deactivation command...\n"
    sudo php user/cli.php plugin deactivate "$TARGET_PLUGIN_NAME" || true
fi

# 3. Remove the backend plugin hook extension directory
if [ -d "$PLUGIN_DIR" ]; then
    echo "-> Removing plugin backend hooks from $PLUGIN_DIR..."
    sudo rm -rf "$PLUGIN_DIR"
    echo "✔ Backend plugin extension directories dropped."
else
    echo "ℹ Notice: Plugin extension path not found. Skipping backend removal."
fi

# 4. Clean up the public web facing QR folder
if [ -d "$WEB_QR_DIR" ]; then
    echo "-> Removing public webroot assets from $WEB_QR_DIR..."
    sudo rm -rf "$WEB_QR_DIR"
    echo "✔ Public webroot assets successfully dropped."
else
    echo "ℹ Notice: Public web directory not found. Skipping webroot cleanup."
fi

echo "================================================"
echo "🎉 Clean Uninstallation Process Complete!"
echo "================================================"
