#!/bin/bash

# ==============================================================================
# Branded QR Code Deployment Script for YOURLS (Self-Healing Dependency Patch)
# ==============================================================================

set -e

REPO_URL="https://github.com/laithalsunni/yourls-local-branded_qr-code.git"
TEMP_DIR="/tmp/yourls_qr_clone_$(date +%s)"
TARGET_PLUGIN_NAME="branded_qr-code"

echo "================================================"
echo "Starting Branded QR Engine Deployment"
echo "================================================"

if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
    echo "✔ Success: Execution context located at YOURLS Root: $YOURLS_ROOT"
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../
    YOURLS_ROOT=$(pwd)
    echo "✔ Success: Shifted context to YOURLS Root: $YOURLS_ROOT"
else
    echo "❌ Error: This script must be executed inside your YOURLS installation tree."
    exit 1
fi

PLUGIN_DIR="$YOURLS_ROOT/user/plugins"
WEB_QR_DIR="$YOURLS_ROOT/qr"
JS_DIR="$WEB_QR_DIR/js"

echo "-> Cloning source assets from GitHub branch..."
git clone "$REPO_URL" "$TEMP_DIR"

echo "-> Preparing webroot directory structure..."
mkdir -p "$WEB_QR_DIR"
mkdir -p "$JS_DIR"

if [ -d "$TEMP_DIR/qr" ]; then
    cp -Rf "$TEMP_DIR/qr/"* "$WEB_QR_DIR/"
fi

# SELF-HEALING PATCH: Force-download dependencies locally if they are missing from the repo copy
echo "-> Verifying and downloading internal JavaScript engine components..."
curl -sSL "https://raw.githubusercontent.com/alexkolodko/yourls-local-qr-code/main/qr/js/qrcode-svg.js" -o "$JS_DIR/qrcode-svg.js"
curl -sSL "https://raw.githubusercontent.com/alexkolodko/yourls-local-qr-code/main/qr/js/jspdf.umd.min.js" -o "$JS_DIR/jspdf.umd.min.js"
curl -sSL "https://raw.githubusercontent.com/alexkolodko/yourls-local-qr-code/main/qr/js/html2canvas.min.js" -o "$JS_DIR/html2canvas.min.js"
echo "✔ Core JS engine files successfully secured."

echo "-> Synchronizing plugin backend hooks..."
FINAL_PLUGIN_PATH="$PLUGIN_DIR/$TARGET_PLUGIN_NAME"
mkdir -p "$FINAL_PLUGIN_PATH"
find "$TEMP_DIR" -maxdepth 1 -type f -exec cp -f {} "$FINAL_PLUGIN_PATH/" \;

if [ -d "$TEMP_DIR/qr-code-svg-local" ]; then
    cp -Rf "$TEMP_DIR/qr-code-svg-local/"* "$FINAL_PLUGIN_PATH/"
fi

echo "-> Setting correct server access permissions..."
find "$WEB_QR_DIR" -type d -exec chmod 755 {} \;
find "$WEB_QR_DIR" -type f -exec chmod 644 {} \;
find "$FINAL_PLUGIN_PATH" -type d -exec chmod 755 {} \;
find "$FINAL_PLUGIN_PATH" -type f -exec chmod 644 {} \;

rm -rf "$TEMP_DIR"

echo "================================================"
echo "🎉 Deployment Complete!"
echo "================================================"
