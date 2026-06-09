<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo = db();

if (is_post()) {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    
    if (($_POST['accion'] ?? '') === 'eliminar') {
        $st = $pdo->prepare('DELETE FROM asistencias WHERE id = ?');
        $st->execute([$id]);
        flash('ok', 'Registro de asistencia eliminado completamente');
        redirect(app_url('index.php?r=asistencia'));
    }

    $hora_entrada = (string) ($_POST['hora_entrada'] ?? '');
    $hora_salida = (string) ($_POST['hora_salida'] ?? '');
    $estado = (string) ($_POST['estado'] ?? 'presente');
    $obs = trim((string) ($_POST['observacion'] ?? ''));

    if (!in_array($estado, ['presente', 'tardanza', 'falta'], true)) {
        $estado = 'presente';
    }

    $st = $pdo->prepare(
        'UPDATE asistencias SET hora_entrada = ?, hora_salida = ?, estado = ?, observacion = ? WHERE id = ?'
    );
    $he = $hora_entrada !== '' ? $hora_entrada : null;
    $hs = $hora_salida !== '' ? $hora_salida : null;
    $st->execute([$he, $hs, $estado, $obs !== '' ? $obs : null, $id]);
    flash('ok', 'Asistencia actualizada');
    redirect(app_url('index.php?r=asistencia'));
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect(app_url('index.php?r=asistencia'));
}

$st = $pdo->prepare(
    'SELECT a.*, p.dni, p.nombres, p.apellidos FROM asistencias a INNER JOIN practicantes p ON p.id = a.practicante_id WHERE a.id = ?'
);
$st->execute([$id]);
$A = $st->fetch();
if (!$A) {
    flash('err', 'Registro no encontrado');
    redirect(app_url('index.php?r=asistencia'));
}

$title = 'Editar Asistencia';
ob_start();
?>

<div class="max-w-2xl mx-auto ui-animate-entry">
    <form method="post" class="ui-panel rounded-2xl overflow-hidden shadow-lg border border-slate-200 dark:border-slate-700/60 bg-white dark:bg-[#0f172a]">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $A['id'] ?>">

        <!-- Header: Student Info -->
        <div class="px-6 py-6 border-b border-slate-200 dark:border-slate-800 relative overflow-hidden">
            <!-- Decorative background -->
            <div class="absolute inset-0 opacity-20 pointer-events-none" style="background: linear-gradient(135deg, #284b63, transparent);"></div>
            
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl font-bold text-white shrink-0 shadow-md border-2 border-white/10" style="background: linear-gradient(135deg, #284b63, #3c6e71);">
                    <?= mb_strtoupper(mb_substr($A['nombres'], 0, 1)) ?>
                </div>
                <div>
                    <h2 class="text-xl font-display font-bold text-slate-800 dark:text-slate-100 leading-tight">
                        <?= e(nombre_completo($A['nombres'], $A['apellidos'])) ?>
                    </h2>
                    <div class="flex flex-wrap items-center gap-3 mt-1 text-sm">
                        <span class="flex items-center gap-1 text-slate-500 dark:text-slate-400 font-mono">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            <?= e($A['dni']) ?>
                        </span>
                        <span class="text-slate-300 dark:text-slate-600">•</span>
                        <span class="flex items-center gap-1 text-[#284b63] font-semibold bg-[#284b63]/10 px-2.5 py-0.5 rounded-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <?= e((string) $A['fecha']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body: Edit Form -->
        <div class="p-6 space-y-6">
            
            <!-- Time Inputs -->
            <div class="grid sm:grid-cols-2 gap-5">
                <!-- Entrada -->
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50">
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Hora de Entrada
                        </label>
                        <button type="button" onclick="document.getElementById('inEntrada').value=''" class="text-xs text-red-500 hover:text-red-700 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 px-2 py-1 rounded transition-colors flex items-center gap-1" title="Borrar hora">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Limpiar
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🕒</span>
                        <input type="time" step="1" name="hora_entrada" id="inEntrada" value="<?= e(substr((string) ($A['hora_entrada'] ?? ''), 0, 8)) ?>" 
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-[#284b63]/30 focus:border-[#284b63] transition-all">
                    </div>
                </div>

                <!-- Salida -->
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50">
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Hora de Salida
                        </label>
                        <button type="button" onclick="document.getElementById('inSalida').value=''" class="text-xs text-red-500 hover:text-red-700 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 px-2 py-1 rounded transition-colors flex items-center gap-1" title="Borrar hora">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Limpiar
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🕒</span>
                        <input type="time" step="1" name="hora_salida" id="inSalida" value="<?= $A['hora_salida'] ? e(substr((string) $A['hora_salida'], 0, 8)) : '' ?>" 
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-mono focus:outline-none focus:ring-2 focus:ring-[#284b63]/30 focus:border-[#284b63] transition-all">
                    </div>
                </div>
            </div>

            <!-- Estado & Observacion -->
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Estado de la asistencia</label>
                    <select name="estado" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#284b63]/30 focus:border-[#284b63] transition-all appearance-none cursor-pointer">
                        <option value="presente" <?= (($A['estado'] ?? '') === 'presente') ? 'selected' : '' ?>>✅ Puntual</option>
                        <option value="tardanza" <?= (($A['estado'] ?? '') === 'tardanza') ? 'selected' : '' ?>>⏱️ Tardanza</option>
                        <option value="falta" <?= (($A['estado'] ?? '') === 'falta') ? 'selected' : '' ?>>❌ Falta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Observación (opcional)</label>
                    <textarea name="observacion" rows="2" placeholder="Notas sobre la edición..." 
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#284b63]/30 focus:border-[#284b63] transition-all resize-none"><?= e($A['observacion'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 flex flex-col sm:flex-row gap-3">
                <button type="submit" name="accion" value="guardar" class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 rounded-xl py-2.5 px-6 font-semibold text-white transition-all hover:brightness-110 active:scale-[0.98]" style="background: linear-gradient(135deg, #284b63, #3c6e71);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Guardar Cambios
                </button>
                <a href="<?= e(app_url('index.php?r=asistencia')) ?>" class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-transparent py-2.5 px-6 font-semibold text-slate-700 dark:text-slate-300 transition-all hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98]">
                    Cancelar
                </a>
            </div>
        </div>
        
        <!-- Danger Zone -->
        <div class="mt-4 bg-red-50 dark:bg-red-900/10 border-t border-red-100 dark:border-red-900/30 p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-red-800 dark:text-red-400">Eliminar registro por completo</h4>
                    <p class="text-xs text-red-600 dark:text-red-500/80 mt-1">Esta acción borrará la entrada, salida y cualquier observación de este día de forma irreversible.</p>
                </div>
                <button type="submit" name="accion" value="eliminar" 
                        onclick="return confirm('🚨 ¿Estás totalmente seguro de que deseas eliminar este registro de asistencia?');" 
                        class="shrink-0 inline-flex items-center gap-2 rounded-xl border border-red-200 dark:border-red-800 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-500 hover:bg-red-600 hover:text-white dark:hover:bg-red-600 dark:hover:text-white hover:border-red-600 transition-all active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Eliminar
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
