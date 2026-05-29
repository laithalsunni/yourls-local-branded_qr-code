#!/bin/bash
# ==============================================================================
# Server-Side Branded QR Code Suite - Unified Production Installer
# ==============================================================================
set -e

REPO_URL="https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main"
TARGET_FOLDER="branded_qr-code"

echo "====================================================="
echo "⚙️  Initializing Server-Side QR Suite Setup Matrix"
echo "====================================================="

# 1. Verify system runtime configuration bounds
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: Deployment runtime context requires a YOURLS root folder node layout."
    exit 1
fi

echo "✔ Verified YOURLS Application Path: $YOURLS_ROOT"

# 2. Re-align local plugin file trees
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_FOLDER"
UPLOAD_DIR="$PLUGIN_DIR/uploads"

echo "📂 Rebuilding target directory routing targets..."
sudo mkdir -p "$UPLOAD_DIR"

# 3. Pull source files directly from master branch configurations
echo "📥 Syncing raw codebase configurations from source..."
sudo curl -H "Cache-Control: no-cache" -sSL "$REPO_URL/plugin.php" -o "$PLUGIN_DIR/plugin.php"

# 4. Finalize Linux context permissions
echo "🔒 Restoring folder permission scopes for file generation arrays..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"
sudo chmod -R 775 "$UPLOAD_DIR"

echo "====================================================="
echo "🎉 Server-Side Architecture Suite Installed Successfully!"
echo "====================================================="
