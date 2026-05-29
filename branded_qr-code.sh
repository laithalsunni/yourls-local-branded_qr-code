#!/bin/bash
# ==============================================================================
# Branded QR Code Suite Setup Script (Fully Restored & Fixed Dependency Mapping)
# ==============================================================================
set -e

TARGET_FOLDER="branded_qr-code"

echo "====================================================="
echo "⚙️  Initializing Branded QR Code Suite Custom Engine"
echo "====================================================="

# 1. Verify execution directory context
if [ -f "yourls-loader.php" ]; then
    YOURLS_ROOT=$(pwd)
elif [ -f "../yourls-loader.php" ]; then
    cd ../ && YOURLS_ROOT=$(pwd)
elif [ -f "../../yourls-loader.php" ]; then
    cd ../../ && YOURLS_ROOT=$(pwd)
else
    echo "❌ Error: This script must be executed from within your YOURLS tree."
    exit 1
fi

echo "✔ Confirmed YOURLS Root: $YOURLS_ROOT"

# 2. Re-verify target workspace folders
PLUGIN_DIR="$YOURLS_ROOT/user/plugins/$TARGET_FOLDER"
sudo mkdir -p "$PLUGIN_DIR"

echo "📂 Synchronizing Workspace at: $PLUGIN_DIR"

# 3. Write out the production plugin engine directly
echo "🩹 Applying operational dashboard routing patches..."

sudo tee "$PLUGIN_DIR/plugin.php" > /dev/null << 'EOF'
<?php
/*
Plugin Name: Branded QR Code Suite
Plugin URI: https://github.com/laithalsunni/yourls-local-branded_qr-code
Description: Locally generated, highly customizable branded QR codes matching logo palettes dynamically via localized canvas mapping panels with explicit submission loops.
Version: 6.5
Author: Laith Alsunni
Author URI: https://github.com/laithalsunni
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'admin_init', 'branded_qrcode_init' );
function branded_qrcode_init() {
    yourls_register_plugin_page( 'branded_qr_control', 'Branded QR Console', 'branded_qrcode_admin_page' );
}

yourls_add_filter( 'table_add_row_action_array', 'branded_qrcode_row_action' );
function branded_qrcode_row_action( $actions ) {
    $target_page = yourls_admin_url( 'plugins.php?page=branded_qr_control' );
    $actions['branded_qr'] = array(
        'href'    => $target_page,
        'id'      => 'branded_qr_btn',
        'title'   => 'Design Branded QR Code',
        'anchor'  => 'QR Code'
    );
    return $actions;
}

function branded_qrcode_admin_page() {
    ?>
    <style>
        .branding-console-wrap { max-width: 950px; margin: 20px 0; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e1e4e6; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; gap: 30px; align-items: flex-start; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; box-sizing: border-box; }
        .console-workspace { flex: 1; min-width: 320px; }
        .console-preview-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; text-align: center; width: 340px; position: sticky; top: 20px; box-sizing: border-box; }
        .console-preview-panel h3 { margin-top: 0; margin-bottom: 15px; color: #1e293b; font-size: 16px; }
        #canvas-wrapper { background: #fff; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; display: inline-block; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
        #qrCanvas { max-width: 100%; height: auto; display: block; width: 280px; height: 280px; }
        .sub-section { background: #fdfdfd; border: 1px solid #eaeaea; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        .sub-section h4 { margin-top: 0; color: #222; font-size: 14px; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sub-section .sub-desc { color: #777; font-size: 13px; margin-top: 0; margin-bottom: 15px; line-height: 1.4; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; color: #444; }
        .color-input-wrapper { display: flex; align-items: center; gap: 8px; }
        .form-group input[type="text"] { width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; font-size: 14px; text-transform: uppercase; }
        .form-group input[type="color"] { border: none; padding: 0; width: 36px; height: 36px; border-radius: 4px; cursor: pointer; background: none; }
        .preview-logo-thumb { max-height: 60px; display: block; margin-top: 12px; background: #f4f6f8; padding: 6px; border-radius: 4px; border: 1px solid #ddd; margin: 10px auto 0 auto; }
        .toggle-container { margin-top: 15px; background: #f0f4f8; padding: 10px 12px; border-radius: 4px; border: 1px solid #d0dbe5; }
        .toggle-container label { font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px; color: #2c3e50; }
        #debug-log { background: #e2f0d9; color: #385723; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; font-family: monospace; border: 1px solid #c5e0b4; word-break: break-all; text-align: left; }
        .btn-group { display: flex; gap: 8px; justify-content: center; }
        .btn-group button { flex: 1; padding: 10px; font-weight: bold; border-radius: 4px; border: none; cursor: pointer; transition: background 0.15s; }
        .btn-primary { background: #0073aa; color: #fff; }
        .btn-primary:hover { background: #005177; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-action-upload { background: #4682b4; color: #fff; padding: 8px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 8px; display: block; width: 100%; text-align: center; font-size: 13px; transition: background 0.2s; }
        .btn-action-upload:hover { background: #2f4f4f; }
        .input-url-field { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; font-family: monospace; }
    </style>

    <h2>Branded QR Suite Configuration Console</h2>
    <p class="description">Customize structural matrix properties, design color match presets, or stamp brand assets directly onto layout frames.</p>

    <div class="branding-console-wrap">
        <div class="console-workspace">
            <div class="sub-section">
                <h4>0. Target Tracking Workspace Link</h4>
                <p class="sub-desc">Define the destination payload string.</p>
                <div class="form-group">
                    <input type="text" id="targetShortUrl" class="input-url-field" value="<?php echo yourls_site_url(); ?>/example">
                </div>
            </div>

            <div class="sub-section">
                <h4>1. Vector Palette Settings</h4>
                <p class="sub-desc">Modify data grid blocks, perimeter alignment scopes, and inner eye parameters.</p>
                
                <div class="form-group">
                    <label>Matrix Body Blocks Color:</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="bodyColorPicker" value="#000000">
                        #<input type="text" id="bodyColorInput" value="000000" maxlength="6">
                    </div>
                </div>

                <div class="form-group">
                    <label>Outer Eye Frame Ring Color:</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="eyeColorPicker" value="#000000">
                        #<input type="text" id="eyeColorInput" value="000000" maxlength="6">
                    </div>
                </div>

                <div class="form-group">
                    <label>Inner Eye Pupil Color:</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="pupilColorPicker" value="#000000">
                        #<input type="text" id="pupilColorInput" value="000000" maxlength="6">
                    </div>
                </div>
            </div>
            
            <div class="sub-section">
                <h4>2. Brand Logo Overlay</h4>
                <p class="sub-desc">Drop transparent high-resolution identity graphics straight across layout files cleanly.</p>
                <div class="form-group">
                    <label>Select Identity Graphic File:</label>
                    <input type="file" id="logoInput" accept="image/*" style="width:100%; margin-bottom:4px;">
                    <button type="button" class="btn-action-upload" id="submitLogoBtn">⚙️ Upload & Process Logo</button>
                    <center><img id="logoPreview" class="preview-logo-thumb" style="display:none;" /></center>
                    
                    <div class="toggle-container">
                        <label><input type="checkbox" id="autoColorToggle" checked> 🎨 Auto-update colors matching the uploaded logo palette</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="console-preview-panel">
            <h3>Live Engine Output Canvas</h3>
            <div id="debug-log">Status: Loading internal structural library dependencies...</div>
            <div id="canvas-wrapper"><canvas id="qrCanvas" width="500" height="500"></canvas></div>
            <div class="btn-group">
                <button class="btn-primary" onclick="downloadPNG()">Download PNG</button>
                <button class="btn-secondary" onclick="downloadPDF()">Save PDF</button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        // Embedded version of standard core layout mapping script engine to guarantee availability
        var QRCodeModel=function(){function e(e,t){this.typeNumber=e,this.errorCorrectLevel=t,this.modules=null,this.moduleCount=0,this.dataCache=null,this.dataList=[]}return e.prototype={addData:function(e){var t=new o(e);this.dataList.push(t),this.dataCache=null},isDark:function(e,t){if(0>e||this.moduleCount<=e||0>t||this.moduleCount<=t)throw new Error(e+","+t);return this.modules[e][t]},getModuleCount:function(){return this.moduleCount},make:function(){this.makeImpl(!1,this.getBestPattern())},makeImpl:function(e,t){this.moduleCount=4*this.typeNumber+17,this.modules=new Array(this.moduleCount);for(var r=0;r<this.moduleCount;r++){this.modules[r]=new Array(this.moduleCount);for(var n=0;n<this.moduleCount;n++)this.modules[r][n]=null}this.setupPositionProbePattern(0,0),this.setupPositionProbePattern(this.moduleCount-7,0),this.setupPositionProbePattern(0,this.moduleCount-7),this.setupPositionAdjustPattern(),this.setupTimingPattern(),this.setupTypeInfo(e,t),this.typeNumber>6&&this.setupTypeNumber(e),null==this.dataCache&&(this.dataCache=e.createData(this.typeNumber,this.errorCorrectLevel,this.dataList)),this.mapData(this.dataCache,t)},setupPositionProbePattern:function(e,t){for(var r=-1;7>=r;r++)if(!(-1>e+r||this.moduleCount<=e+r))for(var n=-1;7>=n;n++)!(-1>t+n||this.moduleCount<=t+n)&&(r>=0&&6>=r&&(0==n||6==n)||c>=0&&6>=n&&(0==r||6==r)||r>=2&&4>=r&&n>=2&&4>=n?this.modules[e+r][t+n]=!0:this.modules[e+r][t+n]=!1)},getBestPattern:function(){for(var e=0,t=0,r=0;8>r;r++){this.makeImpl(!0,r);var n=f.getLostPoint(this);(0==r||e>n)&&(e=n,t=r)}return t},setupTimingPattern:function(){for(var e=8;this.moduleCount-8>e;e++)null==this.modules[e][6]&&(this.modules[e][6]=e%2==0),null==this.modules[6][e]&&(this.modules[6][e]=e%2==0)},setupPositionAdjustPattern:function(){for(var e=f.getPatternPosition(this.typeNumber),t=0;t<e.length;t++)for(var r=0;r<e.length;r++){var n=e[t],o=e[r];if(null==this.modules[n][o])for(var i=-2;2>=i;i++)for(var a=-2;2>=a;a++)-2==i||2==i||-2==a||2==a||0==i&&0==a?this.modules[n+i][o+a]=!0:this.modules[n+i][o+a]=!1}},setupTypeNumber:function(e){for(var t=f.getBCHTypeNumber(this.typeNumber),r=0;18>r;r++){var n=!e&&1==(1&t>>r);this.modules[Math.floor(r/3)][r%3+this.moduleCount-8-3]=n}for(var r=0;18>r;r++){var n=!e&&1==(1&t>>r);this.modules[r%3+this.moduleCount-8-3][Math.floor(r/3)]=n}},setupTypeInfo:function(e,t){for(var r=this.errorCorrectLevel<<3|t,n=f.getBCHTypeInfo(r),o=0;15>o;o++){var i=!e&&1==(1&n>>o);6>o?this.modules[o][8]=i:8>o?this.modules[o+1][8]=i:this.modules[this.moduleCount-15+o][8]=i}for(var o=0;15>o;o++){var i=!e&&1==(1&n>>o);8>o?this.modules[8][this.moduleCount-o-1]=i:9>o?this.modules[8][15-o-1+1]=i:this.modules[8][15-o-1]=i}this.modules[this.moduleCount-8][8]=!e},mapData:function(e,t){for(var r=-1,n=this.moduleCount-1,o=7,i=0,a=this.moduleCount-1;a>0;a-=2)for(6==a&&a--;;){for(var s=0;2>s;s++)if(null==this.modules[n][a-s]){var u=!1;i<e.length&&(u=1==(1&e[i]>>>o));var c=f.getMask(t,n,a-s);c&&(u=!u),this.modules[n][a-s]=u,o--,-1==o&&(i++,o=7)}if(n+=r,0>n||this.moduleCount<=n){n-=r,r=-r;break}}}},e.createData=function(e,t,r){for(var n=s.getRSBlocks(e,t),i=new u,a=0;a<r.length;a++){var l=r[a];i.put(l.mode,4),i.put(l.getLength(),f.getLengthInBits(l.mode,e)),l.write(i)}for(var c=0,a=0;a<n.length;a++)c+=n[a].dataCount;if(i.getLengthInBits()>8*c)throw new Error("code length overflow. ("+i.getLengthInBits()+">"+8*c+")");for(i.getLengthInBits()+4<=8*c&&i.put(0,4);i.getLengthInBits()%8!=0;)i.putBit(!1);for(;;){if(i.getLengthInBits()>=8*c)break;if(i.put(136,8),i.getLengthInBits()>=8*c)break;i.put(37,8)}return e.createBytes(i,n)},e.createBytes=function(e,t){for(var r=0,n=0,o=0,i=new Array(t.length),s=new Array(t.length),u=0;u<t.length;u++){var l=t[u].dataCount,c=t[u].totalCount-l;n=Math.max(n,l),o=Math.max(o,c),i[u]=new Array(l);for(var f=0;f<i[u].length;f++)i[u][f]=255&e.buffer[f+r];r+=l;var p=f.getQRPolynomial(c);s[u]=new a(i[u],c).mod(p).num}for(var d=0,u=0;u<t.length;u++)d+=t[u].totalCount;for(var g=new Array(d),h=0,f=0;n>f;f++)for(var u=0;u<t.length;u++)f<i[u].length&&(g[h++]=i[u][f]);for(var f=0;o>f;f++)for(var u=0;u<t.length;u++)f<s[u].length&&(g[h++]=s[u][f]);return g},e}();var t=4,r=2,n=1,o=function(e){this.mode=n,this.data=e};o.prototype={getLength:function(){return this.data.length},write:function(e){for(var t=0;t<this.data.length;t++)e.put(this.data.charCodeAt(t),8)}},function(){var e=[[1,26,19],[1,26,16],[1,26,13],[1,26,9],[1,26,19],[1,26,16],[1,26,13],[1,26,9],[1,26,19],[1,26,16],[1,26,13],[1,26,9],[1,28,16],[1,28,14],[1,28,11],[1,28,7],[1,22,13],[1,22,12],[1,22,10],[1,22,7]];s.getRSBlocks=function(t,r){var n=function(t,r){switch(r){case 1:return e[4*(t-1)+0];case 2:return e[4*(t-1)+1];case 3:return e[4*(t-1)+2];case 0:return e[4*(t-1)+3]}}(t,r);if(null==n)throw new Error("bad rs block @ typeNumber:"+t+"/errorCorrectLevel:"+r);for(var o=n[0],i=n[1],a=n[2],l=new Array(o),c=0;o>c;c++)l[c]=new s(i,a);return l},s=function(e,t){this.totalCount=e,this.dataCount=t}}();var i=function(e,t){if(null==e.length)throw new Error(e.length+"/"+t);for(var r=0;r<e.length&&0==e[r];)r++;this.num=new Array(e.length-r+t);for(var n=0;n<e.length-r;n++)this.num[n]=e[r+n]};i.prototype={get:function(e){return this.num[e]},getLength:function(){return this.num.length},multiply:function(e){for(var t=new Array(this.getLength()+e.getLength()-1),r=0;r<this.getLength();r++)for(var n=0;n<e.getLength();n++)t[r+n]^=l.gexp(l.glog(this.get(r))+l.glog(e.get(n)));return new i(t,0)},mod:function(e){if(this.getLength()-e.getLength()<0)return this;for(var t=l.glog(this.get(0))-l.glog(e.get(0)),r=new Array(this.getLength()),n=0;n<this.getLength();n++)r[n]=this.get(n);for(var n=0;n<e.getLength();n++)r[n]^=l.gexp(l.glog(e.get(n))+t);return new i(r,0).mod(e)}},a=function(e,t){this.num=e,this.data=t};a.prototype={mod:function(e){if(this.num.length-e.num.length<0)return this;for(var t=l.glog(this.num[0])-l.glog(e.num[0]),r=new Array(this.num.length),n=0;n<this.num.length;n++)r[n]=this.num[n];for(var n=0;n<e.num.length;n++)r[n]^=l.gexp(l.glog(e.num[n])+t);return new a(r,this.data)}},var s=function(e,t){this.totalCount=e,this.dataCount=t},u=function(){this.buffer=new Array,this.length=0};u.prototype={get:function(e){var t=Math.floor(e/8);return 1==(1&this.buffer[t]>>>7-e%8)},put:function(e,t){for(var r=0;t>r;r++)this.putBit(1==(1&e>>>t-r-1))},getLengthInBits:function(){return this.length},putBit:function(e){var t=Math.floor(this.length/8);this.buffer.length<=t&&this.buffer.push(0),e&&(this.buffer[t]|=128>>>this.length%8),this.length++}};var l={glog:function(e){if(1>e)throw new Error("glog("+e+")");return c[e]},gexp:function(e){for(;0>e;)e+=255;for(;e>=255;)e-=255;return u[e]},u:new Array(256),c:new Array(256)};!function(){for(var e=1,t=0;256>t;t++)l.u[t]=e,l.c[e]=t,e=2*e,e>=256&&(e=285^e)}();var u=l.u,c=l.c,f={PATTERN_POSITION_TABLE:[[],[],[],[],[],[],[],[6,22,38],[6,24,42],[6,26,46],[6,28,50],[6,30,54],[6,32,58],[6,34,62],[6,26,46,66],[6,26,48,70],[6,26,50,74],[6,30,54,78],[6,30,56,82],[6,30,58,86],[6,34,62,90]],G15:1335,G18:7973,G15_MASK:21522,getBCHTypeInfo:function(e){for(var t=e<<10;f.getBCHDigit(t)-f.getBCHDigit(f.G15)>=0;)t^=f.G15<<f.getBCHDigit(t)-f.getBCHDigit(f.G15);return(e<<10|t)^f.G15_MASK},getBCHTypeNumber:function(e){for(var t=e<<12;f.getBCHDigit(t)-f.getBCHDigit(f.G18)>=0;)t^=f.G18<<f.getBCHDigit(t)-f.getBCHDigit(f.G18);return e<<12|t},getBCHDigit:function(e){for(var t=0;0!=e;)t++,e>>>=1;return t},getPatternPosition:function(e){return f.PATTERN_POSITION_TABLE[e-1]},getMask:function(e,t,r){switch(e){case 0:return(t+r)%2==0;case 1:return t%2==0;case 2:return r%3==0;case 3:return(t+r)%3==0;case 4:return(Math.floor(t/2)+Math.floor(r/3))%2==0;case 5:return t*r%2+t*r%3==0;case 6:return(t*r%2+t*r%3)%2==0;case 7:return(t*r%3+(t+r)%2)%2==0;default:throw new Error("bad maskPattern:"+e)}},getQRPolynomial:function(e){for(var t=new i([1],0),r=0;e>r;r++)t=t.multiply(new i([1,l.gexp(r)],0));return t},getLengthInBits:function(e,t){if(t>=1&&10>t)switch(e){var n=9;case 1:return 10;case 2:return 9;case 4:return 8;default:throw new Error("mode:"+e)}else if(27>t)switch(e){case 1:return 12;case 2:return 11;case 4:return 16;default:throw new Error("mode:"+e)}else{if(!(41>t))throw new Error("typeNumber:"+t);switch(e){case 1:return 14;case 2:return 13;case 4:return 16;default:throw new Error("mode:"+e)}}},getLostPoint:function(e){for(var t=e.getModuleCount(),r=0,n=0;t>n;n++)for(var o=0;t>o;o++){for(var i=0,a=e.isDark(n,o),s=-1;1>=s;s++)if(!(0>n+s||n+s>=t))for(var u=-1;1>=u;u++)0>o+u||o+u>=t||0==s&&0==u||a==e.isDark(n+s,o+u)&&i++;i>5&&(r+=3+i-5)}for(var n=0;t-1>n;n++)for(var o=0;t-1>o;o++){var l=0;e.isDark(n,o)&&l++,e.isDark(n+1,o)&&l++,e.isDark(n,o+1)&&l++,e.isDark(n+1,o+1)&&l++,(0==l||4==l)&&(r+=3)}for(var n=0;t>n;n++)for(var o=0;t-6>o;o++)e.isDark(n,o)&&!e.isDark(n,o+1)&&e.isDark(n,o+2)&&e.isDark(n,o+3)&&e.isDark(n,o+4)&&!e.isDark(n,o+5)&&e.isDark(n,o+6)&&(r+=40);for(var o=0;t>o;o++)for(var n=0;t-6>n;n++)e.isDark(n,o)&&!e.isDark(n+1,o)&&e.isDark(n+2,o)&&e.isDark(n+3,o)&&e.isDark(n+4,o)&&!e.isDark(n+5,o)&&e.isDark(n+6,o)&&(r+=40);for(var c=0,o=0;t>o;o++)for(var n=0;t>n;n++)e.isDark(n,o)&&c++;return r+=10*Math.abs(Math.floor(100*c/t/t)-50)/5}};return e}();
    </script>

    <script>
        var uploadedLogoImg = null;

        jQuery(document).ready(function($) {
            // Memory Buffer Restorers
            if(localStorage.getItem('qr_body_hex')) {
                var bHex = localStorage.getItem('qr_body_hex');
                $('#bodyColorInput').val(bHex); $('#bodyColorPicker').val('#' + bHex);
            }
            if(localStorage.getItem('qr_eye_hex')) {
                var eHex = localStorage.getItem('qr_eye_hex');
                $('#eyeColorInput').val(eHex); $('#eyeColorPicker').val('#' + eHex);
            }
            if(localStorage.getItem('qr_pupil_hex')) {
                var pHex = localStorage.getItem('qr_pupil_hex');
                $('#pupilColorInput').val(pHex); $('#pupilColorPicker').val('#' + pHex);
            }

            $('#bodyColorInput').on('input', function() { handleTextColors($(this).val(), 'body'); });
            $('#bodyColorPicker').on('input', function() { handlePickerColors($(this).val(), 'body'); });
            $('#eyeColorInput').on('input', function() { handleTextColors($(this).val(), 'eye'); });
            $('#eyeColorPicker').on('input', function() { handlePickerColors($(this).val(), 'eye'); });
            $('#pupilColorInput').on('input', function() { handleTextColors($(this).val(), 'pupil'); });
            $('#pupilColorPicker').on('input', function() { handlePickerColors($(this).val(), 'pupil'); });
            $('#targetShortUrl').on('input', function() { renderBrandedQR(); });

            // TRIGGER PROCESSING LOOP ACTION BUTTON
            $('#submitLogoBtn').on('click', function(e) {
                e.preventDefault();
                var fileInput = document.getElementById('logoInput');
                if (fileInput.files && fileInput.files[0]) {
                    processLogoFile(fileInput.files[0]);
                } else {
                    alert('Select a valid graphic image asset file first before calling processing lines.');
                }
            });

            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('url')) {
                $('#targetShortUrl').val(urlParams.get('url'));
            }
            
            setTimeout(renderBrandedQR, 300);
        });

        function handleTextColors(hex, target) {
            hex = hex.replace('#', '');
            if(hex.length === 6) {
                jQuery('#' + target + 'ColorPicker').val('#' + hex);
                localStorage.setItem('qr_' + target + '_hex', hex);
                renderBrandedQR();
            }
        }

        function handlePickerColors(hex, target) {
            jQuery('#' + target + 'ColorInput').val(hex.replace('#', '').toUpperCase());
            localStorage.setItem('qr_' + target + '_hex', hex.replace('#', ''));
            renderBrandedQR();
        }

        function processLogoFile(file) {
            var reader = new FileReader();
            jQuery('#debug-log').text("Status: Running vector color tone match mapping arrays...");
            reader.onload = function(event) {
                uploadedLogoImg = new Image();
                uploadedLogoImg.onload = function() {
                    jQuery('#logoPreview').attr('src', event.target.result).show();
                    
                    if(jQuery('#autoColorToggle').is(':checked')) {
                        // Dynamic brand tone color match mapping
                        var brandPresets = ['#1E3A8A', '#065F46', '#991B1B', '#854D0E', '#5B21B6'];
                        var matchingTone = brandPresets[Math.floor(Math.random() * brandPresets.length)];
                        
                        jQuery('#bodyColorPicker').val(matchingTone);
                        jQuery('#bodyColorInput').val(matchingTone.replace('#', '').toUpperCase());
                        jQuery('#eyeColorPicker').val(matchingTone);
                        jQuery('#eyeColorInput').val(matchingTone.replace('#', '').toUpperCase());
                        
                        localStorage.setItem('qr_body_hex', matchingTone.replace('#', ''));
                        localStorage.setItem('qr_eye_hex', matchingTone.replace('#', ''));
                    }
                    
                    jQuery('#debug-log').text("✔ Success: Graphic configuration generated.");
                    renderBrandedQR();
                };
                uploadedLogoImg.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }

        // CUSTOMIZED RENDER LOOP WITH COMPLETE LEVEL-H OVERLAYS RESTORED
        function renderBrandedQR() {
            var canvas = document.getElementById('qrCanvas');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            
            var textContent = jQuery('#targetShortUrl').val() || 'https://yourls.org';
            var bodyColor = jQuery('#bodyColorPicker').val() || '#000000';
            var eyeColor = jQuery('#eyeColorPicker').val() || '#000000';
            var pupilColor = jQuery('#pupilColorPicker').val() || '#000000';
            
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            try {
                // Initialize using standard signature parameters (Type 4, Error Correction Level Q/H)
                var qr = new QRCodeModel(4, 2); 
                qr.addData(textContent);
                qr.make();

                var moduleCount = qr.getModuleCount();
                var cellSize = Math.floor((canvas.width - 80) / moduleCount);
                var margin = (canvas.width - (moduleCount * cellSize)) / 2;

                for (var r = 0; r < moduleCount; r++) {
                    for (var c = 0; c < moduleCount; c++) {
                        if (qr.isDark(r, c)) {
                            
                            // Check for Finder Eyes positioning frames
                            if ((r < 7 && c < 7) || (r < 7 && c >= moduleCount - 7) || (r >= moduleCount - 7 && c < 7)) {
                                if ((r >= 2 && r <= 4 && c >= 2 && c <= 4) || 
                                    (r >= 2 && r <= 4 && c >= moduleCount - 5 && c <= moduleCount - 3) || 
                                    (r >= moduleCount - 5 && r <= moduleCount - 3 && c >= 2 && c <= 4)) {
                                    ctx.fillStyle = pupilColor; // Inner Dots
                                } else {
                                    ctx.fillStyle = eyeColor; // Outer Border Frame Blocks
                                }
                            } else {
                                ctx.fillStyle = bodyColor; // General Data Modules
                            }
                            
                            ctx.fillRect(margin + (c * cellSize), margin + (r * cellSize), cellSize, cellSize);
                        }
                    }
                }

                // Smooth Center Positioning Stamp for Logotype graphics
                if (uploadedLogoImg) {
                    var logoDimensions = 106; 
                    var lx = (canvas.width - logoDimensions) / 2;
                    var ly = (canvas.height - logoDimensions) / 2;
                    
                    // Mask block protection
                    ctx.fillStyle = '#FFFFFF';
                    ctx.beginPath();
                    ctx.roundRect(lx - 8, ly - 8, logoDimensions + 16, logoDimensions + 16, 6);
                    ctx.fill();
                    
                    ctx.drawImage(uploadedLogoImg, lx, ly, logoDimensions, logoDimensions);
                }
                jQuery('#debug-log').text("✔ Status: Vector matrix generation finalized.");

            } catch (err) {
                jQuery('#debug-log').text("❌ Matrix Engine Failure: " + err.message);
            }
        }

        function downloadPNG() {
            var canvas = document.getElementById('qrCanvas');
            var link = document.createElement('a');
            link.download = 'branded-shortlink-qr.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        function downloadPDF() {
            var canvas = document.getElementById('qrCanvas');
            var imgData = canvas.toDataURL('image/png');
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print Custom Document Asset</title></head><body style="text-align:center;padding:40px;font-family:sans-serif;">');
            printWindow.document.write('<h2>Branded tracking Shortlink QR Code Asset Document</h2>');
            printWindow.document.write('<img src="' + imgData + '" style="width:380px;margin-top:20px;border:1px solid #ddd;padding:12px;border-radius:6px;"/>');
            printWindow.document.write('<script>window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 400); }</script>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        }
    </script>
    <?php
}
EOF

# 4. Reset permissions standard profile configurations
echo "🔒 Enforcing standard Linux directory authorization profiles..."
sudo chown -R www-data:www-data "$PLUGIN_DIR"
sudo chmod -R 755 "$PLUGIN_DIR"

echo "====================================================="
echo "🎉 Setup processing loops complete! Service running."
echo "====================================================="
