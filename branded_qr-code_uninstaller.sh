#!/bin/bash

# ==============================================================================
# Branded QR Code Suite - Clean Uninstaller Script
# ==============================================================================

set -e

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

# Wipe out any variation matching our plugin profile
echo "-> Sweeping plugin target paths..."
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr-code"
sudo rm -rf "$YOURLS_ROOT/user/plugins/branded_qr_code"

echo "====================================================="
echo "🎉 System uninstalled and wiped cleanly!"
echo "====================================================="
