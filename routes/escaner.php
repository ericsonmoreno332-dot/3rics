<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);

$urlEntrada = app_url('index.php?r=asistencia_entrada');
$urlSalida = app_url('index.php?r=asistencia_salida');

$title = 'Escáner QR';
ob_start();
?>

<div class="max-w-3xl mx-auto ui-animate-entry">
    
    <!-- Hero Title -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-[#284b63] to-[#3c6e71] text-white text-3xl shadow-lg shadow-[#284b63]/30 mb-4 animate-bounce-slight">
            📷
        </div>
        <h1 class="text-3xl font-extrabold font-display text-transparent bg-clip-text bg-gradient-to-r from-[#284b63] to-[#3c6e71]">
            Escáner de Asistencia
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm max-w-lg mx-auto">
            Apunta la cámara al código QR del practicante para registrar su hora exacta automáticamente.
        </p>
    </div>



    <div class="ui-panel rounded-3xl p-6 sm:p-8 relative overflow-hidden shadow-2xl">
        <!-- Glow effects -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-[#284b63]/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-[#3c6e71]/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center">
            
            <!-- Action Toggle -->
            <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-xl mb-8 w-full max-w-sm">
                <label class="flex-1 text-center cursor-pointer relative group">
                    <input type="radio" name="tipo_accion" value="entrada" checked class="peer sr-only">
                    <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-slate-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-[#284b63] dark:peer-checked:text-[#3c6e71] peer-checked:shadow-sm">
                        Registrar Entrada
                    </div>
                </label>
                <label class="flex-1 text-center cursor-pointer relative group">
                    <input type="radio" name="tipo_accion" value="salida" class="peer sr-only">
                    <div class="py-2.5 rounded-lg text-sm font-semibold text-slate-500 dark:text-slate-400 transition-all peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-amber-500 dark:peer-checked:text-amber-400 peer-checked:shadow-sm">
                        Registrar Salida
                    </div>
                </label>
            </div>

            <!-- QR Reader Container -->
            <div class="relative w-full max-w-sm aspect-square mx-auto rounded-2xl overflow-hidden border-4 border-slate-200 dark:border-slate-700 bg-black shadow-inner">
                <!-- Overlay targeting brackets -->
                <div class="absolute inset-0 z-20 pointer-events-none flex items-center justify-center p-8">
                    <div class="w-full h-full relative">
                        <!-- Top Left -->
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-white opacity-80 rounded-tl-lg transition-colors duration-300" id="bracket-tl"></div>
                        <!-- Top Right -->
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-white opacity-80 rounded-tr-lg transition-colors duration-300" id="bracket-tr"></div>
                        <!-- Bottom Left -->
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-white opacity-80 rounded-bl-lg transition-colors duration-300" id="bracket-bl"></div>
                        <!-- Bottom Right -->
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-white opacity-80 rounded-br-lg transition-colors duration-300" id="bracket-br"></div>
                        <!-- Scanning laser line -->
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

            <!-- Status Indicator -->
            <div class="mt-8 flex items-center gap-3 bg-slate-50 dark:bg-slate-800/50 px-5 py-3 rounded-full border border-slate-100 dark:border-slate-700">
                <div class="relative flex h-3 w-3">
                  <span id="statusPing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span id="statusDot" class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </div>
                <p id="qr_status" class="text-sm font-medium text-slate-600 dark:text-slate-300">
                    Cámara lista, esperando código...
                </p>
            </div>
            
            <!-- Fallback message -->
            <p class="mt-4 text-[11px] text-slate-400 dark:text-slate-500 text-center">
                El formato del código debe ser: <code class="bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded text-slate-600 dark:text-slate-300">REGIS|12345678</code> o un DNI de 8 dígitos.
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
@keyframes bounce-slight {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
.animate-bounce-slight {
    animation: bounce-slight 3s ease-in-out infinite;
}
</style>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const urlEntrada = <?= json_encode($urlEntrada, JSON_THROW_ON_ERROR) ?>;
const urlSalida = <?= json_encode($urlSalida, JSON_THROW_ON_ERROR) ?>;

function parsePayload(text) {
  const t = String(text || '').trim();
  if (t.startsWith('REGIS|')) return t.slice(6).replace(/\D/g,'');
  if (/^\d{8}$/.test(t)) return t;
  return '';
}

const statusEl = document.getElementById('qr_status');
const statusPing = document.getElementById('statusPing');
const statusDot = document.getElementById('statusDot');
const form = document.getElementById('formQr');
const dniInput = document.getElementById('qr_dni');
const laser = document.getElementById('laser');
const brackets = ['bracket-tl', 'bracket-tr', 'bracket-bl', 'bracket-br'].map(id => document.getElementById(id));

function setStatus(text, type = 'info') {
    statusEl.textContent = text;
    if (type === 'error') {
        statusPing.classList.replace('bg-emerald-400', 'bg-red-400');
        statusDot.classList.replace('bg-emerald-500', 'bg-red-500');
        brackets.forEach(b => b.classList.replace('border-white', 'border-red-500'));
        laser.style.display = 'none';
    } else if (type === 'success') {
        statusPing.classList.replace('bg-emerald-400', 'bg-[#284b63]');
        statusDot.classList.replace('bg-emerald-500', 'bg-[#284b63]');
        brackets.forEach(b => b.classList.replace('border-white', 'border-[#284b63]'));
        laser.style.display = 'none';
    } else {
        // info/ready
        statusPing.classList.replace('bg-red-400', 'bg-emerald-400');
        statusPing.classList.replace('bg-[#284b63]', 'bg-emerald-400');
        statusDot.classList.replace('bg-red-500', 'bg-emerald-500');
        statusDot.classList.replace('bg-[#284b63]', 'bg-emerald-500');
        brackets.forEach(b => {
            b.classList.remove('border-red-500', 'border-[#284b63]');
            b.classList.add('border-white');
        });
        laser.style.display = 'block';
    }
}

function syncFormAction() {
  const salida = document.querySelector('input[name="tipo_accion"]:checked')?.value === 'salida';
  form.action = salida ? urlSalida : urlEntrada;
}

document.querySelectorAll('input[name="tipo_accion"]').forEach(el => {
  el.addEventListener('change', syncFormAction);
});
syncFormAction();

const html5QrCode = new Html5Qrcode("reader");
let isProcessing = false;

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
    
    // Pequeño delay visual para que el usuario vea el éxito antes de enviar
    setTimeout(() => {
        html5QrCode.stop().then(() => form.submit()).catch(() => form.submit());
    }, 800);
  },
  () => {}
).catch(err => { 
    setStatus('No se pudo acceder a la cámara.', 'error'); 
    console.error(err);
});
</script>

<style>
/* Forzar que el video del html5qrcode se adapte bonito al contenedor */
#reader video {
    object-fit: cover;
    border-radius: 1rem;
    width: 100% !important;
    height: 100% !important;
}
#reader {
    border: none !important;
}
</style>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
