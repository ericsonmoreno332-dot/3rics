<?php

declare(strict_types=1);

$user = require_roles(['practicante']);
$pid = (int) ($user['practicante_id'] ?? 0);
if ($pid <= 0) {
    http_response_code(403);
    exit('Cuenta sin practicante asociado');
}

$pdo = db();
actualizar_practicantes_vencidos($pdo);

$p = practicante_por_id($pdo, $pid);
if (!$p) {
    logout();
    redirect(app_url('index.php?r=login'));
}

$abierta = asistencia_abierta_hoy($pdo, $pid);
$cerrada  = asistencia_cerrada_hoy($pdo, $pid);

$asistencias_pasadas = obtener_asistencias_abiertas_pasadas($pdo, $pid);

$st = $pdo->prepare(
    'SELECT * FROM asistencias WHERE practicante_id = ? ORDER BY fecha DESC, hora_entrada DESC LIMIT 30'
);
$st->execute([$pid]);
$rows = $st->fetchAll();

// Mini stats
$total_horas = 0;
$dias_asist  = 0;
foreach ($rows as $r) {
    $ht = horas_trabajadas($r['hora_entrada'] ?? null, $r['hora_salida'] ?? null);
    if ($ht) {
        [$h, $m] = explode(':', substr($ht, 0, 5));
        $total_horas += (int)$h + (int)$m / 60;
        $dias_asist++;
    }
}

$title = 'Mi panel';
ob_start();
?>

<!-- ══ TOP PROFILE BANNER ════════════════════════════════════════════════════ -->
<div class="rounded-2xl overflow-hidden mb-6 shadow-sky"
     style="background: linear-gradient(135deg, #26263A 0%, #26263A 60%, #7A7AA3 100%);">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-5 px-4 sm:px-6 py-5 sm:py-6">
        <div class="shrink-0 h-16 w-16 sm:h-20 sm:w-20 rounded-2xl flex items-center justify-center text-2xl sm:text-3xl font-bold text-white shadow-md"
             style="background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.3);">
            <?= mb_strtoupper(mb_substr($p['nombres'], 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0 w-full">
            <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-white/60 mb-0.5">Practicante</p>
            <h2 class="text-xl sm:text-2xl font-bold text-white leading-tight break-words">
                <?= e(nombre_completo($p['nombres'], $p['apellidos'])) ?>
            </h2>
            <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2 text-xs sm:text-sm text-white/80">
                <span class="font-mono">DNI <?= e($p['dni']) ?></span>
                <?php if ($p['carrera'] ?? ''): ?><span>· <?= e($p['carrera']) ?></span><?php endif; ?>
                <?php if ($p['area_nombre'] ?? ''): ?><span>· <?= e($p['area_nombre']) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="shrink-0">
            <?php if ($p['estado'] === 'activo'): ?>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold"
                      style="background:rgba(119,172,162,0.3); color:#d1faf5; border:1px solid rgba(119,172,162,0.5)">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Activo
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold"
                      style="background:rgba(255,255,255,0.1); color:#fff; border:1px solid rgba(255,255,255,0.2)">
                    <?= e(ucfirst($p['estado'])) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <!-- Mini stat strip -->
    <div class="grid grid-cols-3 divide-x divide-white/10" style="background:rgba(0,0,0,0.2);">
        <div class="px-2 sm:px-5 py-3 text-center">
            <p class="text-lg sm:text-xl font-bold text-white"><?= $dias_asist ?></p>
            <p class="text-[10px] sm:text-xs text-white/60 mt-0.5 leading-tight">Días asistidos</p>
        </div>
        <div class="px-2 sm:px-5 py-3 text-center">
            <p class="text-lg sm:text-xl font-bold text-white"><?= number_format($total_horas, 1) ?></p>
            <p class="text-[10px] sm:text-xs text-white/60 mt-0.5 leading-tight">Horas registradas</p>
        </div>
        <div class="px-2 sm:px-5 py-3 text-center">
            <?php
                if ($cerrada)      { echo '<p class="text-lg sm:text-xl font-bold text-white">✅</p>'; }
                elseif ($abierta)  { echo '<p class="text-lg sm:text-xl font-bold text-white">🟡</p>'; }
                else               { echo '<p class="text-lg sm:text-xl font-bold text-white text-white/40">—</p>'; }
            ?>
            <p class="text-[10px] sm:text-xs text-white/60 mt-0.5 leading-tight">Hoy</p>
        </div>
    </div>
</div>

<!-- ══ QUICK ACTION CARDS ═════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <!-- QR Card -->
    <?php if (practicante_activo($p)): ?>
    <a href="<?= e(app_url('index.php?r=mi_qr')) ?>"
       class="group ui-panel p-5 flex items-center gap-4 hover:border-pisco-sky transition-all duration-200 hover:shadow-sky no-underline">
        <div class="shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform"
             style="background: linear-gradient(135deg,#26263A,#26263A);">
            📱
        </div>
        <div>
            <p class="font-bold text-slate-700 dark:text-stone-200">Mi Código QR</p>
            <p class="text-xs text-slate-400 mt-0.5">Ver, descargar e imprimir tu código</p>
        </div>
        <span class="ml-auto text-pisco-sky text-lg group-hover:translate-x-1 transition-transform">→</span>
    </a>
    <?php endif; ?>

    <!-- Action Cards para Salidas Pasadas -->
    <?php if (practicante_activo($p) && count($asistencias_pasadas) > 0): ?>
        <?php foreach ($asistencias_pasadas as $pasada): ?>
        <div class="ui-panel p-5 flex flex-col justify-center gap-2">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold px-2 py-1 bg-amber-100 text-amber-800 rounded-md">Fecha: <?= e(date('d/m/Y', strtotime((string)$pasada['fecha']))) ?></span>
                <span class="text-[10px] uppercase text-slate-500 font-bold">Entrada: <?= e(substr((string)$pasada['hora_entrada'], 0, 5)) ?></span>
            </div>
            
            <?php if ($pasada['solicitud_estado'] === 'pendiente'): ?>
                <div class="flex items-center gap-3">
                    <span class="text-2xl">⏳</span>
                    <div>
                        <p class="font-bold text-slate-700 dark:text-stone-200">Salida en revisión</p>
                        <p class="text-xs text-slate-400 mt-0.5">Propusiste salir a las <span class="font-bold text-amber-500"><?= e(substr((string)$pasada['hora_propuesta'], 0, 5)) ?></span></p>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-2 p-2 bg-slate-50 dark:bg-stone-800 rounded-lg border border-slate-100 dark:border-stone-700">El administrador está revisando tu solicitud.</p>
            <?php else: ?>
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl"><?= $pasada['solicitud_estado'] === 'rechazada' ? '❌' : '⚠️' ?></span>
                    <div>
                        <p class="font-bold text-slate-700 dark:text-stone-200"><?= $pasada['solicitud_estado'] === 'rechazada' ? 'Solicitud Rechazada' : 'Olvidaste registrar salida' ?></p>
                        <p class="text-xs text-slate-400 mt-0.5"><?= $pasada['solicitud_estado'] === 'rechazada' ? 'Debes proponer otra hora' : 'Propón la hora en la que saliste' ?></p>
                    </div>
                </div>
                
                <?php if ($pasada['solicitud_estado'] === 'rechazada' && $pasada['mensaje_rechazo']): ?>
                    <p class="text-xs text-red-500 mb-2 font-medium">Motivo: <?= e($pasada['mensaje_rechazo']) ?></p>
                <?php endif; ?>

                <form method="post" action="<?= e(app_url('index.php?r=solicitud_salida')) ?>" class="flex gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="asistencia_id" value="<?= (int)$pasada['asistencia_id'] ?>">
                    <input type="time" name="hora_propuesta" required class="flex-1 rounded-xl border border-slate-300 dark:border-stone-600 bg-transparent px-3 py-2 text-sm text-slate-800 dark:text-stone-200 focus:outline-none focus:border-[#26263A] focus:ring-1 focus:ring-[#26263A] transition-all">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white transition-all hover:brightness-110 active:scale-[0.98]" style="background: linear-gradient(135deg, #26263A, #7A7AA3);">
                        Enviar
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<!-- ══ HISTORIAL ══════════════════════════════════════════════════════════════ -->
<div class="ui-panel overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-stone-700">
        <h3 class="font-bold text-slate-700 dark:text-stone-200 flex items-center gap-2">
            <span>📋</span> Historial de Asistencias
        </h3>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
              style="background:rgba(70,129,137,0.12); color:#26263A;">Últimas 30</span>
    </div>
    <div class="overflow-x-auto">
        <table class="ui-table-left">
            <thead class="ui-thead">
                <tr>
                    <th class="ui-th">Fecha</th>
                    <th class="ui-th">Entrada</th>
                    <th class="ui-th">Salida</th>
                    <th class="ui-th">Horas</th>
                    <th class="ui-th">Estado</th>
                    <th class="ui-th hidden sm:table-cell">Observación</th>
                </tr>
            </thead>
            <tbody class="ui-tbody text-sm">
                <?php foreach ($rows as $r): ?>
                    <?php $ht = horas_trabajadas($r['hora_entrada'] ?? null, $r['hora_salida'] ?? null); ?>
                    <tr class="ui-tr-hover">
                        <td class="ui-td font-medium"><?= e(date('d/m/Y', strtotime((string)$r['fecha']))) ?></td>
                        <td class="ui-td font-mono text-xs"><?= $r['hora_entrada'] ? e(substr((string)$r['hora_entrada'],0,5)) : '<span class="text-slate-300">—</span>' ?></td>
                        <td class="ui-td font-mono text-xs"><?= $r['hora_salida']  ? e(substr((string)$r['hora_salida'],0,5))  : '<span class="text-slate-300">—</span>' ?></td>
                        <td class="ui-td font-mono text-xs font-semibold"><?= $ht ? e(substr($ht,0,5)) : '<span class="text-slate-300">—</span>' ?></td>
                        <td class="ui-td">
                            <?php if ($r['estado'] === 'completada'): ?>
                                <span class="ui-badge-ok">✓ Completa</span>
                            <?php elseif ($r['estado'] === 'abierta'): ?>
                                <span class="ui-badge-warn">● Abierta</span>
                            <?php else: ?>
                                <span class="ui-badge-muted"><?= e($r['estado']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="ui-td text-xs text-slate-400 max-w-[160px] truncate hidden sm:table-cell"
                            title="<?= e($r['observacion'] ?? '') ?>"><?= e($r['observacion'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!count($rows)): ?>
                    <tr><td colspan="6" class="ui-empty py-16">
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-4xl">📭</span>
                            <span>No hay registros de asistencia aún.</span>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
