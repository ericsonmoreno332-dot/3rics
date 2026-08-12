<?php

declare(strict_types=1);

$user = require_roles(['admin']);
$pdo = db();

$pendientes = solicitudes_pendientes($pdo);
$historial  = solicitudes_historial($pdo, 30);

$title = 'Mensajes';
ob_start();
?>

<!-- ══ HEADER ═══════════════════════════════════════════ -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div class="flex items-center gap-4">
        <div class="h-14 w-14 rounded-2xl flex items-center justify-center text-2xl shadow-sm"
             style="background: linear-gradient(135deg, #26263A, #7A7AA3);">
            <span class="text-white">📩</span>
        </div>
        <div>
            <h2 class="text-2xl font-display font-bold text-slate-800 dark:text-stone-100">Mensajes</h2>
            <p class="text-sm text-slate-500 dark:text-stone-400 mt-0.5">Solicitudes de salida pendientes de aprobación</p>
        </div>
    </div>
    <?php if (count($pendientes) > 0): ?>
    <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold text-white shadow-md"
          style="background: linear-gradient(135deg, #dc2626, #ef4444);">
        <span class="inline-block w-2 h-2 rounded-full bg-white animate-pulse"></span>
        <?= count($pendientes) ?> pendiente<?= count($pendientes) > 1 ? 's' : '' ?>
    </span>
    <?php endif; ?>
</div>

<!-- ══ SOLICITUDES PENDIENTES ═══════════════════════════ -->
<?php if (count($pendientes) > 0): ?>
<div class="space-y-4 mb-10">
    <?php foreach ($pendientes as $sol): ?>
    <div class="ui-panel p-5 sm:p-6 relative overflow-hidden group">
        <!-- Glow -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10">
            <!-- Top row: Practicante info -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <div class="h-11 w-11 rounded-xl flex items-center justify-center text-sm font-bold text-white shrink-0"
                         style="background: linear-gradient(135deg, #26263A, #7A7AA3);">
                        <?= mb_strtoupper(mb_substr($sol['nombres'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-stone-100">
                            <?= e($sol['nombres'] . ' ' . $sol['apellidos']) ?>
                        </p>
                        <p class="text-xs text-slate-400 dark:text-stone-500 font-mono">DNI <?= e($sol['dni']) ?></p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold"
                      style="background: rgba(245,158,11,0.15); color: #d97706; border: 1px solid rgba(245,158,11,0.3);">
                    <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Pendiente
                </span>
            </div>

            <!-- Details row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5 p-4 rounded-xl bg-slate-50 dark:bg-stone-800/50 border border-slate-100 dark:border-stone-700">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-stone-500 mb-0.5">Fecha</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-stone-200"><?= e(date('d/m/Y', strtotime((string)$sol['fecha']))) ?></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-stone-500 mb-0.5">Entrada</p>
                    <p class="text-sm font-mono font-semibold text-slate-700 dark:text-stone-200"><?= e(substr((string)$sol['hora_entrada'], 0, 5)) ?></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-stone-500 mb-0.5">Salida propuesta</p>
                    <p class="text-sm font-mono font-bold" style="color: #d97706;"><?= e(substr((string)$sol['hora_propuesta'], 0, 5)) ?></p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-stone-500 mb-0.5">Enviado</p>
                    <p class="text-sm text-slate-700 dark:text-stone-200"><?= e(date('d/m H:i', strtotime((string)$sol['created_at']))) ?></p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Accept -->
                <form method="post" action="<?= e(app_url('index.php?r=solicitud_accion')) ?>" class="flex-1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="solicitud_id" value="<?= (int)$sol['id'] ?>">
                    <input type="hidden" name="accion" value="aceptar">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-semibold text-white transition-all hover:brightness-110 active:scale-[0.98]"
                            style="background: linear-gradient(135deg, #059669, #10b981);"
                            onclick="return confirm('¿Aceptar esta solicitud? Se registrará la hora <?= e(substr((string)$sol['hora_propuesta'], 0, 5)) ?> como salida.')">
                        ✓ Aceptar Salida
                    </button>
                </form>

                <!-- Reject -->
                <form method="post" action="<?= e(app_url('index.php?r=solicitud_accion')) ?>" class="flex-1"
                      id="formRechazar<?= (int)$sol['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="solicitud_id" value="<?= (int)$sol['id'] ?>">
                    <input type="hidden" name="accion" value="rechazar">
                    <div class="flex gap-2">
                        <input type="text" name="mensaje_rechazo" placeholder="Motivo (opcional)..." maxlength="200"
                               class="flex-1 rounded-xl border border-slate-300 dark:border-stone-600 bg-transparent px-3 py-2.5 text-sm text-slate-800 dark:text-stone-200 focus:outline-none focus:border-red-400 focus:ring-1 focus:ring-red-400 transition-all">
                        <button type="submit" 
                                class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold border-2 border-red-400 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all active:scale-[0.98]"
                                onclick="return confirm('¿Rechazar esta solicitud? El practicante deberá enviar una nueva hora.')">
                            ✕ Rechazar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="ui-panel p-12 text-center mb-10">
    <div class="flex flex-col items-center gap-3">
        <span class="text-5xl">✅</span>
        <p class="text-lg font-semibold text-slate-600 dark:text-stone-300">No hay solicitudes pendientes</p>
        <p class="text-sm text-slate-400 dark:text-stone-500">Todas las solicitudes han sido procesadas</p>
    </div>
</div>
<?php endif; ?>

<!-- ══ HISTORIAL ════════════════════════════════════════ -->
<?php if (count($historial) > 0): ?>
<div class="ui-panel overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-stone-700">
        <h3 class="font-bold text-slate-700 dark:text-stone-200 flex items-center gap-2">
            <span>📋</span> Historial de Solicitudes
        </h3>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
              style="background:rgba(70,129,137,0.12); color:#26263A;">Últimas 30</span>
    </div>
    <div class="overflow-x-auto">
        <table class="ui-table-left">
            <thead class="ui-thead">
                <tr>
                    <th class="ui-th">Practicante</th>
                    <th class="ui-th">Fecha</th>
                    <th class="ui-th">Entrada</th>
                    <th class="ui-th">Hora Propuesta</th>
                    <th class="ui-th">Estado</th>
                    <th class="ui-th hidden sm:table-cell">Motivo rechazo</th>
                </tr>
            </thead>
            <tbody class="ui-tbody text-sm">
                <?php foreach ($historial as $h): ?>
                <tr class="ui-tr-hover">
                    <td class="ui-td">
                        <p class="font-medium"><?= e($h['nombres'] . ' ' . $h['apellidos']) ?></p>
                        <p class="text-xs text-slate-400 font-mono"><?= e($h['dni']) ?></p>
                    </td>
                    <td class="ui-td"><?= e(date('d/m/Y', strtotime((string)$h['fecha']))) ?></td>
                    <td class="ui-td font-mono text-xs"><?= e(substr((string)$h['hora_entrada'], 0, 5)) ?></td>
                    <td class="ui-td font-mono text-xs font-bold"><?= e(substr((string)$h['hora_propuesta'], 0, 5)) ?></td>
                    <td class="ui-td">
                        <?php if ($h['estado'] === 'pendiente'): ?>
                            <span class="ui-badge-warn">● Pendiente</span>
                        <?php elseif ($h['estado'] === 'aceptada'): ?>
                            <span class="ui-badge-ok">✓ Aceptada</span>
                        <?php else: ?>
                            <span class="ui-badge-err">✕ Rechazada</span>
                        <?php endif; ?>
                    </td>
                    <td class="ui-td text-xs text-slate-400 max-w-[200px] truncate hidden sm:table-cell"
                        title="<?= e($h['mensaje_rechazo'] ?? '') ?>"><?= e($h['mensaje_rechazo'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
