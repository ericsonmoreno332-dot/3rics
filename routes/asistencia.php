<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo = db();

$urlEntrada = app_url('index.php?r=asistencia_entrada');
$urlSalida = app_url('index.php?r=asistencia_salida');

$title = 'Registro de Asistencia';
ob_start();
?>

<!-- Action Toggle -->
<div class="flex items-center p-1 bg-slate-100 dark:bg-stone-800 rounded-xl mb-8 w-full max-w-md mx-auto">
    <label class="flex-1 text-center cursor-pointer relative group">
        <input type="radio" name="modo_registro" value="manual" checked class="peer sr-only" onchange="toggleModo()">
        <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-[#26263A] dark:peer-checked:text-[#7A7AA3] peer-checked:shadow-sm">
            Manual (DNI)
        </div>
    </label>
    <label class="flex-1 text-center cursor-pointer relative group">
        <input type="radio" name="modo_registro" value="qr" class="peer sr-only" onchange="toggleModo()">
        <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-[#26263A] dark:peer-checked:text-[#7A7AA3] peer-checked:shadow-sm">
            Escáner QR
        </div>
    </label>
</div>

<!-- ═══ MODO MANUAL ══════════════════════ -->
<div id="modoManual">
    <?php if ($user['rol'] === 'admin' || $user['rol'] === 'supervisor'): ?>
    <div class="ui-panel rounded-2xl p-6 lg:p-8 mb-6 relative overflow-hidden group max-w-3xl mx-auto">
        <!-- Glow effects behind -->
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-[#26263A]/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center relative z-10">
            <!-- Left Side: Form -->
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl flex items-center justify-center bg-slate-100 dark:bg-stone-800 shrink-0">
                        <span class="text-2xl">⌨️</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-display font-bold text-slate-800 dark:text-stone-100">Registro Manual</h2>
                        <p class="text-sm text-slate-500 dark:text-stone-400 mt-0.5">Ingresa el DNI del practicante</p>
                    </div>
                </div>

                <hr class="border-slate-200 dark:border-stone-700/50">

                <form method="post" action="<?= e($urlEntrada) ?>" id="formDniEntrada" class="space-y-5">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-stone-300 mb-1.5">DNI (8 dígitos)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">👤</span>
                            <input name="dni" placeholder="Ingrese el DNI" maxlength="8" pattern="\d{8}" required 
                                   class="w-full rounded-xl border border-slate-300 dark:border-stone-600 bg-transparent pl-10 pr-4 py-2.5 text-sm text-slate-800 dark:text-stone-200 focus:outline-none focus:border-[#26263A] focus:ring-1 focus:ring-[#26263A] transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-stone-300 mb-1.5">Observación (opcional)</label>
                        <textarea name="observacion" rows="2" placeholder="Notas sobre el registro..." 
                                  class="w-full rounded-xl border border-slate-300 dark:border-stone-600 bg-transparent px-4 py-2.5 text-sm text-slate-800 dark:text-stone-200 focus:outline-none focus:border-[#26263A] focus:ring-1 focus:ring-[#26263A] transition-all"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button type="submit" name="accion" value="entrada" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold text-white transition-all hover:brightness-110 active:scale-[0.98]" style="background: linear-gradient(135deg, #26263A, #7A7AA3);">
                            <span class="text-lg">➜</span> Registrar Entrada
                        </button>
                        <button type="submit" formaction="<?= e($urlSalida) ?>" name="accion" value="salida" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 dark:border-stone-600 bg-transparent py-3 text-sm font-semibold text-slate-700 dark:text-stone-300 transition-all hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98]">
                            <span class="text-lg">←</span> Registrar Salida
                        </button>
                    </div>
                </form>
            </div>
            <!-- Right Side: Graphic Illustration -->
            <div class="hidden md:flex items-center justify-center h-full relative">
                <div class="w-48 h-48 rounded-full border border-dashed border-[#26263A]/30 animate-[spin_60s_linear_infinite]"></div>
                <div class="absolute text-5xl">👤</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══ MODO QR ══════════════════════ -->
<div id="modoQR" style="display: none;" class="max-w-3xl mx-auto ui-animate-entry">
    <div class="ui-panel rounded-3xl p-6 sm:p-8 relative overflow-hidden shadow-2xl">
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-[#26263A]/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center">
            
            <div class="flex items-center p-1 bg-slate-100 dark:bg-stone-800 rounded-xl mb-8 w-full max-w-sm">
                <label class="flex-1 text-center cursor-pointer relative group">
                    <input type="radio" name="tipo_accion_qr" value="entrada" checked class="peer sr-only">
                    <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-[#26263A] dark:peer-checked:text-[#7A7AA3] peer-checked:shadow-sm">
                        Registrar Entrada
                    </div>
                </label>
                <label class="flex-1 text-center cursor-pointer relative group">
                    <input type="radio" name="tipo_accion_qr" value="salida" class="peer sr-only">
                    <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-amber-500 dark:peer-checked:text-amber-400 peer-checked:shadow-sm">
                        Registrar Salida
                    </div>
                </label>
            </div>

            <!-- QR Reader Container -->
            <div class="relative w-full max-w-sm aspect-square mx-auto rounded-2xl overflow-hidden border-4 border-slate-200 dark:border-stone-700 bg-black shadow-inner">
                <div class="absolute inset-0 z-20 pointer-events-none flex items-center justify-center p-8">
                    <div class="w-full h-full relative">
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-white opacity-80 rounded-tl-lg transition-colors duration-300" id="bracket-tl"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-white opacity-80 rounded-tr-lg transition-colors duration-300" id="bracket-tr"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-white opacity-80 rounded-bl-lg transition-colors duration-300" id="bracket-bl"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-white opacity-80 rounded-br-lg transition-colors duration-300" id="bracket-br"></div>
                        <div class="absolute left-0 w-full h-0.5 bg-red-500 shadow-[0_0_8px_#ef4444] animate-[scan_2.5s_ease-in-out_infinite]" id="laser"></div>
                    </div>
                </div>
                <div id="reader" class="w-full h-full object-cover"></div>
            </div>

            <form method="post" id="formQr" action="<?= e($urlEntrada) ?>" class="hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="dni" id="qr_dni" value="">
                <input type="hidden" name="metodo" value="qr">
            </form>

            <div class="mt-8 flex items-center gap-3 bg-slate-50 dark:bg-stone-800/50 px-5 py-3 rounded-full border border-slate-100 dark:border-stone-700">
                <div class="relative flex h-3 w-3">
                  <span id="statusPing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span id="statusDot" class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </div>
                <p id="qr_status" class="text-sm font-medium text-slate-600 dark:text-stone-300">
                    Cámara lista, esperando código...
                </p>
            </div>
            
            <p class="mt-4 text-[11px] text-slate-400 dark:text-stone-500 text-center">
                El formato del código debe ser: <code class="bg-slate-100 dark:bg-stone-800 px-1 py-0.5 rounded text-slate-600 dark:text-stone-300">REGIS|12345678</code> o un DNI de 8 dígitos.
            </p>
        </div>
    </div>
</div>

<style>
@keyframes scan {
  0% { top: 0%; opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { top: 100%; opacity: 0; }
}
/* Fix black borders from html5-qrcode */
#reader {
    overflow: hidden !important;
    border: none !important;
}
#reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
}
#reader img {
    display: none !important;
}
#reader div {
    border: none !important;
}
#qr-shaded-region {
    display: none !important;
}
</style>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const urlEntrada = <?= json_encode($urlEntrada, JSON_THROW_ON_ERROR) ?>;
const urlSalida = <?= json_encode($urlSalida, JSON_THROW_ON_ERROR) ?>;

// Toggle functionality
let html5QrCode = null;
function toggleModo() {
    const modo = document.querySelector('input[name="modo_registro"]:checked').value;
    const divManual = document.getElementById('modoManual');
    const divQR = document.getElementById('modoQR');
    
    if (modo === 'manual') {
        divManual.style.display = 'block';
        divQR.style.display = 'none';
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                html5QrCode = null;
            }).catch(() => {});
        }
    } else {
        divManual.style.display = 'none';
        divQR.style.display = 'block';
        initScanner();
    }
}

function parsePayload(text) {
  const t = String(text || '').trim();
  if (t.startsWith('REGIS|')) return t.slice(6).replace(/\D/g,'');
  if (/^\d{8}$/.test(t)) return t;
  return '';
}

const statusEl = document.getElementById('qr_status');
const statusPing = document.getElementById('statusPing');
const statusDot = document.getElementById('statusDot');
const formQr = document.getElementById('formQr');
const dniInput = document.getElementById('qr_dni');
const laser = document.getElementById('laser');
const brackets = ['bracket-tl', 'bracket-tr', 'bracket-bl', 'bracket-br'].map(id => document.getElementById(id));

function setStatus(text, type = 'info') {
    statusEl.textContent = text;
    if (type === 'error') {
        statusPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-red-400';
        statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-red-500';
        brackets.forEach(b => b.className = b.className.replace(/border-(white|\[#26263A\])/g, 'border-red-500'));
        laser.style.display = 'none';
    } else if (type === 'success') {
        statusPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-[#26263A]';
        statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-[#26263A]';
        brackets.forEach(b => b.className = b.className.replace(/border-(white|red-500)/g, 'border-[#26263A]'));
        laser.style.display = 'none';
    } else {
        statusPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-emerald-400';
        statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-emerald-500';
        brackets.forEach(b => {
            b.className = b.className.replace(/border-(red-500|\[#26263A\])/g, 'border-white');
        });
        laser.style.display = 'block';
    }
}

function syncFormAction() {
  const salida = document.querySelector('input[name="tipo_accion_qr"]:checked')?.value === 'salida';
  formQr.action = salida ? urlSalida : urlEntrada;
}

document.querySelectorAll('input[name="tipo_accion_qr"]').forEach(el => {
  el.addEventListener('change', syncFormAction);
});
syncFormAction();

let isProcessing = false;

function initScanner() {
    if (html5QrCode) return;
    html5QrCode = new Html5Qrcode("reader");
    isProcessing = false;
    
    html5QrCode.start(
      { facingMode: "environment" },
      { fps: 15 },
      (decodedText) => {
        if (isProcessing) return;
        
        const dni = parsePayload(decodedText);
        if (dni.length !== 8) {
          setStatus('Código no reconocido, intente nuevamente', 'error');
          setTimeout(() => { if (!isProcessing) setStatus('Cámara lista, esperando código...', 'info'); }, 2000);
          return;
        }
        
        isProcessing = true;
        syncFormAction();
        setStatus('¡DNI ' + dni + ' detectado! Registrando...', 'success');
        dniInput.value = dni;
        
        setTimeout(() => {
            html5QrCode.stop().then(() => formQr.submit()).catch(() => formQr.submit());
        }, 800);
      },
      () => {}
    ).catch(err => { 
        setStatus('No se pudo acceder a la cámara.', 'error');
    });
}

// Soporte para escáner físico USB en cualquier parte de la pantalla
let usbScannerBuffer = "";
let usbScannerTimer = null;
document.addEventListener('keydown', function(e) {
    if (e.key === 'Shift' || e.key === 'Control' || e.key === 'Alt') return;

    if (e.key === 'Enter') {
        if (usbScannerBuffer.startsWith('REGIS|')) {
            e.preventDefault();
            const dni = parsePayload(usbScannerBuffer);
            if (dni.length === 8) {
                if (isProcessing) return; // Evitar múltiples escaneos
                isProcessing = true;
                
                syncFormAction();
                setStatus('¡Código capturado por escáner físico! Registrando en 3 segundos...', 'success');
                dniInput.value = dni;
                
                setTimeout(() => {
                    // Detener la cámara web si estaba encendida
                    if (html5QrCode && html5QrCode.isScanning) {
                        html5QrCode.stop().then(() => formQr.submit()).catch(() => formQr.submit());
                    } else {
                        formQr.submit();
                    }
                }, 3000); // 3 segundos de delay preventivo
            }
        }
        usbScannerBuffer = "";
        return;
    }

    usbScannerBuffer += e.key;
    clearTimeout(usbScannerTimer);
    usbScannerTimer = setTimeout(() => {
        usbScannerBuffer = "";
    }, 50); // Un escáner USB escribe extremadamente rápido, mucho menos de 50ms por tecla
});

</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
