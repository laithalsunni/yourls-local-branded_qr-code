# Branded QR Code Suite for YOURLS

A modern, fast, server-side independent QR code compilation engine built specifically for YOURLS. It embeds a native vector canvas generation framework right inside your short-link workspace dashboard.

## ✨ Core Features
* **Zero Sub-directories:** No messy `/qr/` folders, external styling dependencies, or extra rewrite patterns.
* **Native Settings Control Panel:** Update color palettes, tracking eye configurations, and brand logos directly inside the **Manage Plugins** menu interface.
* **High Density Error Correction:** Employs high error correction density rules to safely protect structural data, making sure links scan reliably even with branding icons layered directly over the grid matrices.
* **Vector Engine Rendering:** Compiles client-side using native HTML5 canvases for crisp, perfect scanning scales.

## 🚀 One-Line Production Server Installation

Run this terminal command inside your active YOURLS installation directory (typically `/var/www/html`):

```bash
curl -sSL [https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main/branded_qr-code.sh](https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main/branded_qr-code.sh) -o /tmp/install.sh && chmod +x /tmp/install.sh && ./tmp/install.sh && rm /tmp/install.sh

```

## 🗑️ Clean Uninstallation Commands

To completely remove the suite, deactivate hooks, and delete file tracks, run:

```bash
curl -sSL [https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main/branded_qr-code_uninstaller.sh](https://raw.githubusercontent.com/laithalsunni/yourls-local-branded_qr-code/main/branded_qr-code_uninstaller.sh) -o /tmp/uninstall.sh && chmod +x /tmp/uninstall.sh && ./tmp/uninstall.sh && rm /tmp/uninstall.sh

```

---

*Maintained by [Laith Alsunni*](https://github.com/laithalsunni)

```

### Next Steps
1. Push these files up to your GitHub repository.
2. Log into your server terminal profile at `/var/www/html` and run the single installation command from the README. 
3. Perform a hard browser reload (**`Ctrl + F5`**) on your dashboard, and everything will render cleanly!
