#!/bin/bash

# ==============================================================================
# Branded QR Code Deployment Script for YOURLS
# Automatically deploys frontend components to webroot and backend to plugins.
# ==============================================================================

# Exit immediately if any command returns a non-zero status
set -e

# --- CONFIGURATION ---
REPO_URL="https://github.com/laithalsunni/yourls-local-branded_qr-code.git"
TEMP_DIR="/tmp/yourls_qr_clone_$(date +%s)"
TARGET_PLUGIN_NAME="branded_qr-code"

echo "================================================"
echo "Starting Branded QR Engine Deployment"
echo "================================================"

# 1. Determine running context and locate paths
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
    echo "✔ Success: Execution context located at YOURLS Root: $YOURLS_ROOT"
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../
    YOURLS_ROOT=$(pwd)
    echo "✔ Success: Shifted context to YOURLS Root: $YOURLS_ROOT"
else
    echo "❌ Error: This script must be executed inside your YOURLS installation tree."
    echo "Please move this script to your main YOURLS directory or your user/plugins folder and try again."
    exit 1
fi

PLUGIN_DIR="$YOURLS_ROOT/user/plugins"
WEB_QR_DIR="$YOURLS_ROOT/qr"

# 2. Pull the latest code assets into isolation
echo "-> Cloning source assets from GitHub branch..."
git clone "$REPO_URL" "$TEMP_DIR"

# 3. Deploy Frontend Assets
echo "-> Processing webroot interface configurations..."
if [ -d "$TEMP_DIR/qr" ]; then
    # Create web folder if missing
    mkdir -p "$WEB_QR_DIR"
    # Copy files over cleanly
    cp -Rf "$TEMP_DIR/qr/"* "$WEB_QR_DIR/"
    echo "✔ Frontend interface modules copied to: $WEB_QR_DIR"
else
    echo "⚠️ Warning: 'qr' web interface directory not found in repository repository layout."
fi

# 4. Deploy Backend Plugin Hook System
echo "-> Synchronizing plugin core mapping hooks..."
FINAL_PLUGIN_PATH="$PLUGIN_DIR/$TARGET_PLUGIN_NAME"
mkdir -p "$FINAL_PLUGIN_PATH"

# Copy all root plugin files (plugin.php, readme, etc) excluding the subfolders
find "$TEMP_DIR" -maxdepth 1 -type f -exec cp -f {} "$FINAL_PLUGIN_PATH/" \;

# Copy internal plugin tracking dependencies if any exist 
if [ -d "$TEMP_DIR/qr-code-svg-local" ]; then
    cp -Rf "$TEMP_DIR/qr-code-svg-local/"* "$FINAL_PLUGIN_PATH/"
fi
echo "✔ Backend hooks synchronized to: $FINAL_PLUGIN_PATH"

# 5. Asset Permission Tuning
echo "-> Optimizing file access permissions..."
find "$WEB_QR_DIR" -type d -exec chmod 755 {} \;
find "$WEB_QR_DIR" -type f -exec chmod 644 {} \;
find "$FINAL_PLUGIN_PATH" -type d -exec chmod 755 {} \;
find "$FINAL_PLUGIN_PATH" -type f -exec chmod 644 {} \;

# 6. Housekeeping Cleanup
echo "-> Cleaning workspace transient states..."
rm -rf "$TEMP_DIR"

echo "================================================"
echo "🎉 Deployment Complete!"
echo "================================================"
echo "1. Go to your YOURLS Admin Panel -> Plugins"
echo "2. Activate the Branded QR Code extension."
echo "3. Test your link by appending '.qr' to any short URL."
echo "================================================"
