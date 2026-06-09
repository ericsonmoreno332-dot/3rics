<?php

declare(strict_types=1);

$user = require_roles(['practicante']);
$pid = (int) ($user['practicante_id'] ?? 0);
if ($pid <= 0) {
    http_response_code(403);
    exit('Cuenta sin practicante asociado');
}

$pdo = db();
$p = practicante_por_id($pdo, $pid);
if (!$p) {
    logout();
    redirect(app_url('index.php?r=login'));
}

$qr_url = app_url('index.php?r=qr_png&id=' . $pid);

$title = 'Mi Código QR';
ob_start();
?>

<div class="max-w-md mx-auto space-y-6">

    <!-- Header card -->
    <div class="rounded-2xl overflow-hidden shadow-sky">
        <div class="px-6 py-5 text-center"
             style="background: linear-gradient(135deg, #353535 0%, #284b63 100%);">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-3"
                 style="background:rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.3);">
                <span class="text-3xl">📱</span>
            </div>
            <h2 class="text-xl font-bold text-white">Mi Código QR Personal</h2>
            <p class="text-sm text-white/70 mt-1">
                <?= e(nombre_completo($p['nombres'], $p['apellidos'])) ?>
            </p>
            <p class="text-xs font-mono text-white/50 mt-0.5">DNI <?= e($p['dni']) ?></p>
        </div>
    </div>

    <?php if (practicante_activo($p)): ?>
    <!-- QR Card -->
    <div class="ui-panel p-6 flex flex-col items-center gap-5 text-center">
        <div class="bg-white p-4 rounded-2xl shadow-gold border-2 border-pisco-accent/20">
            <img src="<?= e($qr_url) ?>"
                 alt="Código QR de <?= e($p['nombres']) ?>"
                 class="w-56 h-56 object-contain block"
                 id="qrImage">
        </div>
        <div class="space-y-2 w-full">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Muestre este código frente al <strong>escáner QR</strong> para registrar su asistencia automáticamente.
            </p>
            <a href="<?= e($qr_url) ?>"
               download="qr_<?= e($p['dni']) ?>.png"
               class="ui-btn-primary-wide w-full justify-center">
                ⬇️ Descargar QR
            </a>
            <button onclick="window.print()" class="ui-btn-outline-wide w-full justify-center">
                🖨️ Imprimir
            </button>
        </div>
    </div>

    <!-- Instructions -->
    <div class="ui-panel p-5 space-y-3">
        <h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm">¿Cómo usar tu QR?</h3>
        <ol class="space-y-2 text-sm text-slate-500 dark:text-slate-400">
            <li class="flex items-start gap-2">
                <span class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#284b63;">1</span>
                Abre esta página en tu celular o imprime el código.
            </li>
            <li class="flex items-start gap-2">
                <span class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#284b63;">2</span>
                Acércalo al escáner QR al momento de entrar o salir.
            </li>
            <li class="flex items-start gap-2">
                <span class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#284b63;">3</span>
                El sistema registrará tu asistencia en tiempo real.
            </li>
        </ol>
    </div>

    <?php else: ?>
    <div class="ui-alert-amber text-center">
        <p class="font-semibold">QR no disponible</p>
        <p class="text-sm mt-1">Tu estado actual es <strong><?= e($p['estado']) ?></strong>. El QR solo está activo cuando estás en estado <strong>Activo</strong>.</p>
    </div>
    <?php endif; ?>

    <div class="text-center">
        <a href="<?= e(app_url('index.php?r=mi_panel')) ?>" class="ui-btn-outline">← Volver a Mi Panel</a>
    </div>
</div>

<style>
@media print {
    .ui-aside, header, .ui-btn-outline, .ui-btn-primary-wide { display: none !important; }
    body { background: white !important; }
}
</style>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
