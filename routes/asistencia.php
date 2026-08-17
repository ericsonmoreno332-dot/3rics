<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo = db();

$urlEntrada = app_url('index.php?r=asistencia_entrada');
$urlSalida = app_url('index.php?r=asistencia_salida');

$modoInit = $_GET['modo'] ?? 'manual';
if (!in_array($modoInit, ['manual', 'qr', 'lector'], true)) {
    $modoInit = 'manual';
}

$title = 'Registro de Asistencia';
ob_start();
?>

<!-- Action Toggle -->
<div class="flex items-center p-1 bg-slate-100 dark:bg-stone-800 rounded-xl mb-8 w-full max-w-lg mx-auto">
    <label class="flex-1 text-center cursor-pointer relative group">
        <input type="radio" name="modo_registro" value="manual" <?= ($modoInit !== 'qr' && $modoInit !== 'lector') ? 'checked' : '' ?> class="peer sr-only" onchange="toggleModo()">
        <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-[#26263A] dark:peer-checked:text-[#7A7AA3] peer-checked:shadow-sm">
            Manual (DNI)
        </div>
    </label>
    <label class="flex-1 text-center cursor-pointer relative group">
        <input type="radio" name="modo_registro" value="qr" <?= $modoInit === 'qr' ? 'checked' : '' ?> class="peer sr-only" onchange="toggleModo()">
        <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-[#26263A] dark:peer-checked:text-[#7A7AA3] peer-checked:shadow-sm">
            Escáner QR (Cámara)
        </div>
    </label>
    <label class="flex-1 text-center cursor-pointer relative group">
        <input type="radio" name="modo_registro" value="lector" <?= $modoInit === 'lector' ? 'checked' : '' ?> class="peer sr-only" onchange="toggleModo()">
        <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-[#26263A] dark:peer-checked:text-[#7A7AA3] peer-checked:shadow-sm">
            Lector QR (Físico)
        </div>
    </label>
</div>

<!-- ═══ MODO MANUAL ══════════════════════ -->
<div id="modoManual" <?= ($modoInit === 'qr' || $modoInit === 'lector') ? 'style="display: none;"' : '' ?>>
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
                    <input type="hidden" name="redirect_to" value="<?= e(app_url('index.php?r=asistencia&modo=manual')) ?>">
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-stone-300 mb-1.5">DNI (8 dígitos)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">👤</span>
                            <input name="dni" placeholder="Ingrese el DNI" maxlength="8" pattern="\d{8}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required 
                                   class="w-full rounded-xl border border-slate-300 dark:border-stone-600 bg-transparent pl-10 pr-4 py-2.5 text-sm text-slate-800 dark:text-stone-200 focus:outline-none focus:border-[#26263A] focus:ring-1 focus:ring-[#26263A] transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-stone-300 mb-1.5">Observación (opcional)</label>
                        <textarea name="observacion" rows="2" placeholder="Notas sobre el registro..." maxlength="250" 
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
<div id="modoQR" <?= $modoInit !== 'qr' ? 'style="display: none;"' : '' ?> class="max-w-3xl mx-auto ui-animate-entry">
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
                <input type="hidden" name="redirect_to" value="<?= e(app_url('index.php?r=asistencia&modo=qr')) ?>">
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

<!-- ═══ MODO LECTOR QR (FÍSICO) ══════════════════════ -->
<div id="modoLector" <?= $modoInit === 'lector' ? '' : 'style="display: none;"' ?> class="max-w-3xl mx-auto ui-animate-entry">
    <div class="ui-panel rounded-3xl p-6 sm:p-8 relative overflow-hidden shadow-2xl">
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-[#7A7AA3]/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center">
            <!-- Selector de Entrada / Salida -->
            <div class="flex items-center p-1 bg-slate-100 dark:bg-stone-800 rounded-xl mb-8 w-full max-w-sm">
                <label class="flex-1 text-center cursor-pointer relative group">
                    <input type="radio" name="tipo_accion_lector" value="entrada" checked class="peer sr-only">
                    <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-[#26263A] dark:peer-checked:text-[#7A7AA3] peer-checked:shadow-sm">
                        Registrar Entrada
                    </div>
                </label>
                <label class="flex-1 text-center cursor-pointer relative group">
                    <input type="radio" name="tipo_accion_lector" value="salida" class="peer sr-only">
                    <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-stone-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-amber-500 dark:peer-checked:text-amber-400 peer-checked:shadow-sm">
                        Registrar Salida
                    </div>
                </label>
            </div>

            <!-- Graphic Icon for physical reader -->
            <div class="relative w-full max-w-sm aspect-[4/3] mx-auto rounded-2xl flex flex-col items-center justify-center border-4 border-dashed border-[#7A7AA3]/40 bg-slate-50 dark:bg-stone-900/60 p-6 text-center">
                <span class="text-6xl mb-4 animate-[float_3s_ease-in-out_infinite]">🔌</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-stone-200">Lector Físico Activo</h3>
                <p class="text-xs text-slate-500 dark:text-stone-400 mt-2 max-w-xs">
                    Acerque el código QR al lector. El campo se mantendrá enfocado automáticamente.
                </p>
                
                <!-- Input field (focused automatically) -->
                <div class="mt-5 w-full relative">
                    <input id="lectorInput" type="text" autocomplete="off"
                           class="w-full text-center font-mono font-bold tracking-widest text-lg bg-white dark:bg-stone-800 border-2 border-[#7A7AA3]/50 focus:border-[#26263A] focus:ring-4 focus:ring-[#26263A]/20 dark:text-white rounded-xl py-3 px-4 transition-all focus:outline-none"
                           placeholder="[ LISTO PARA ESCANEAR ]">
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 flex h-3.5 w-3.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                    </div>
                </div>
            </div>

            <form method="post" id="formLector" action="<?= e($urlEntrada) ?>" class="hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="dni" id="lector_dni" value="">
                <input type="hidden" name="metodo" value="qr">
                <input type="hidden" name="redirect_to" value="<?= e(app_url('index.php?r=asistencia&modo=lector')) ?>">
            </form>

            <div class="mt-8 flex items-center gap-3 bg-slate-50 dark:bg-stone-800/50 px-5 py-3 rounded-full border border-slate-100 dark:border-stone-700">
                <p id="lector_status" class="text-xs font-semibold text-slate-600 dark:text-stone-300">
                    Alineando foco del cursor...
                </p>
            </div>
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
let isProcessing = false;

document.addEventListener('DOMContentLoaded', () => {
    // Cooldown inicial de 2 segundos para evitar lecturas consecutivas accidentales
    isProcessing = true;

    const mode = document.querySelector('input[name="modo_registro"]:checked')?.value;
    if (mode === 'qr') {
        initScanner();
    } else if (mode === 'lector') {
        focusLector();
        const statusText = document.getElementById('lector_status');
        if (statusText) {
            statusText.innerHTML = '<span class="text-amber-500 font-semibold">⏳ Inicializando lector (espera 2s)...</span>';
        }
    }

    setTimeout(() => {
        isProcessing = false;
        const currentMode = document.querySelector('input[name="modo_registro"]:checked')?.value;
        if (currentMode === 'lector') {
            const statusText = document.getElementById('lector_status');
            if (statusText) {
                statusText.innerHTML = '<span class="text-emerald-500 font-semibold">🟢 Listo para recibir lectura del escáner</span>';
            }
        } else if (currentMode === 'qr') {
            setStatus('Cámara lista, esperando código...', 'info');
        }
    }, 2000);

    // Mantener enfoque automático en el lector si está activo
    const lectorInput = document.getElementById('lectorInput');
    if (lectorInput) {
        lectorInput.addEventListener('blur', () => {
            const currentMode = document.querySelector('input[name="modo_registro"]:checked')?.value;
            if (currentMode === 'lector') {
                setTimeout(() => lectorInput.focus(), 80);
            }
        });

        // Enviar al presionar enter (el lector USB envía Enter por defecto)
        lectorInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                processLectorInput(lectorInput.value);
            }
        });
    }
});

function toggleModo() {
    const modo = document.querySelector('input[name="modo_registro"]:checked').value;
    const divManual = document.getElementById('modoManual');
    const divQR = document.getElementById('modoQR');
    const divLector = document.getElementById('modoLector');
    
    if (modo === 'manual') {
        divManual.style.display = 'block';
        divQR.style.display = 'none';
        divLector.style.display = 'none';
        stopCamera();
    } else if (modo === 'qr') {
        divManual.style.display = 'none';
        divQR.style.display = 'block';
        divLector.style.display = 'none';
        initScanner();
    } else if (modo === 'lector') {
        divManual.style.display = 'none';
        divQR.style.display = 'none';
        divLector.style.display = 'block';
        stopCamera();
        focusLector();
    }
}

function stopCamera() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
            html5QrCode = null;
        }).catch(() => {});
    }
}

function focusLector() {
    const input = document.getElementById('lectorInput');
    const statusText = document.getElementById('lector_status');
    if (input) {
        input.focus();
        if (statusText && !isProcessing) {
            statusText.innerHTML = '<span class="text-emerald-500 font-semibold">🟢 Listo para recibir lectura del escáner</span>';
        }
    }
}

function parsePayload(text) {
  const t = String(text || '').trim();
  
  // 1. Si contiene "REGIS" (insensible a mayúsculas) y una secuencia de 8 dígitos
  const regisMatch = t.match(/REGIS\D*(\d{8})/i);
  if (regisMatch) {
      return regisMatch[1];
  }
  
  // 2. Si es solo un DNI de 8 dígitos
  if (/^\d{8}$/.test(t)) {
      return t;
  }
  
  // 3. Si contiene una URL con el DNI en los parámetros o al final
  const urlMatch = t.match(/(?:dni|id|code|data)[=\/](\d{8})/i);
  if (urlMatch) {
      return urlMatch[1];
  }
  
  // 4. Extracción de seguridad: la primera secuencia de 8 dígitos
  const anyDni = t.match(/\b\d{8}\b/);
  if (anyDni) {
      return anyDni[0];
  }
  
  // 5. Fallback extremo: limpiar todo lo que no sea número y ver si quedan exactamente 8 dígitos
  const cleanNums = t.replace(/\D/g, '');
  if (cleanNums.length === 8) {
      return cleanNums;
  }
  
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
    if (!statusEl) return;
    statusEl.textContent = text;
    if (type === 'error') {
        statusPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-red-400';
        statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-red-500';
        brackets.forEach(b => { if (b) b.className = b.className.replace(/border-(white|\[#26263A\])/g, 'border-red-500'); });
        if (laser) laser.style.display = 'none';
    } else if (type === 'success') {
        statusPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-[#26263A]';
        statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-[#26263A]';
        brackets.forEach(b => { if (b) b.className = b.className.replace(/border-(white|red-500)/g, 'border-[#26263A]'); });
        if (laser) laser.style.display = 'none';
    } else {
        statusPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-emerald-400';
        statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-emerald-500';
        brackets.forEach(b => {
            if (b) b.className = b.className.replace(/border-(red-500|\[#26263A\])/g, 'border-white');
        });
        if (laser) laser.style.display = 'block';
    }
}

function syncFormAction() {
  const salida = document.querySelector('input[name="tipo_accion_qr"]:checked')?.value === 'salida';
  formQr.action = salida ? urlSalida : urlEntrada;
}

document.querySelectorAll('input[name="tipo_accion_qr"]').forEach(el => {
  el.addEventListener('change', syncFormAction);
});
if (formQr) syncFormAction();

function initScanner() {
    if (html5QrCode) return;
    html5QrCode = new Html5Qrcode("reader");
    
    // Si ya pasó el cooldown inicial, se inicia listo. Si no, se bloquea temporalmente.
    if (isProcessing) {
        setStatus('Inicializando cámara (cooldown activo)...', 'info');
    } else {
        setStatus('Iniciando cámara...', 'info');
    }
    
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
        if (dniInput) dniInput.value = dni;
        
        setTimeout(() => {
            html5QrCode.stop().then(() => formQr.submit()).catch(() => formQr.submit());
        }, 800);
      },
      () => {}
    ).then(() => {
        if (!isProcessing) {
            setStatus('Cámara lista, esperando código...', 'info');
        } else {
            setStatus('Cámara lista, esperando (cooldown activo)...', 'info');
        }
    }).catch(err => { 
        setStatus('No se pudo acceder a la cámara.', 'error');
    });
}

function processLectorInput(value) {
    if (isProcessing) return;
    const dni = parsePayload(value);
    const statusText = document.getElementById('lector_status');
    const formLector = document.getElementById('formLector');
    const lectorDni = document.getElementById('lector_dni');
    const lectorInput = document.getElementById('lectorInput');
    
    if (dni.length !== 8) {
        if (statusText) {
            statusText.innerHTML = '<span class="text-red-500 font-semibold">❌ Código no reconocido: "' + escapeHTML(value) + '"</span>';
        }
        if (lectorInput) lectorInput.value = '';
        setTimeout(focusLector, 2000);
        return;
    }
    
    isProcessing = true;
    if (statusText) {
        statusText.innerHTML = '<span class="text-pisco-accent font-semibold">⏳ DNI ' + dni + ' leído. Registrando...</span>';
    }
    
    // Sincronizar acción de entrada/salida para el lector
    const salida = document.querySelector('input[name="tipo_accion_lector"]:checked')?.value === 'salida';
    if (formLector) {
        formLector.action = salida ? urlSalida : urlEntrada;
    }
    if (lectorDni) lectorDni.value = dni;
    
    setTimeout(() => {
        if (formLector) formLector.submit();
    }, 400);
}

function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
    );
}

// Soporte global para escáner físico USB en cualquier parte de la pantalla (compatibilidad hacia atrás)
let usbScannerBuffer = "";
let usbScannerTimer = null;
document.addEventListener('keydown', function(e) {
    // Si estamos en la pestaña Lector y enfocados, no interferir con la entrada local
    const modo = document.querySelector('input[name="modo_registro"]:checked')?.value;
    if (modo === 'lector' && document.activeElement === document.getElementById('lectorInput')) {
        return;
    }
    
    if (e.key === 'Shift' || e.key === 'Control' || e.key === 'Alt') return;

    if (e.key === 'Enter') {
        if (/^REGIS/i.test(usbScannerBuffer)) {
            e.preventDefault();
            const dni = parsePayload(usbScannerBuffer);
            if (dni.length === 8) {
                if (isProcessing) return;
                isProcessing = true;
                
                // Mapea al modo actual o por defecto a entrada
                const formToSubmit = formQr || document.getElementById('formLector') || document.getElementById('formDniEntrada');
                if (formToSubmit) {
                    const isSalida = (modo === 'qr' && document.querySelector('input[name="tipo_accion_qr"]:checked')?.value === 'salida') || 
                                     (modo === 'lector' && document.querySelector('input[name="tipo_accion_lector"]:checked')?.value === 'salida');
                    
                    formToSubmit.action = isSalida ? urlSalida : urlEntrada;
                    
                    const inputDni = document.getElementById('qr_dni') || document.getElementById('lector_dni') || document.querySelector('input[name="dni"]');
                    if (inputDni) inputDni.value = dni;
                    
                    setTimeout(() => {
                        if (html5QrCode && html5QrCode.isScanning) {
                            html5QrCode.stop().then(() => formToSubmit.submit()).catch(() => formToSubmit.submit());
                        } else {
                            formToSubmit.submit();
                        }
                    }, 500);
                }
            }
        }
        usbScannerBuffer = "";
        return;
    }

    usbScannerBuffer += e.key;
    clearTimeout(usbScannerTimer);
    usbScannerTimer = setTimeout(() => {
        usbScannerBuffer = "";
    }, 50);
});
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
