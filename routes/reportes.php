<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo = db();

$f = report_filters_from_request();
$rows = fetch_report_rows($pdo, $f);

$areas = $pdo->query('SELECT id, nombre FROM areas ORDER BY nombre')->fetchAll();
$insts = $pdo->query('SELECT id, nombre FROM instituciones ORDER BY nombre')->fetchAll();
$pracs = $pdo->query('SELECT id, dni, nombres, apellidos FROM practicantes ORDER BY apellidos LIMIT 500')->fetchAll();

$qpdf = http_build_query(array_merge($_GET, ['r' => 'reporte_pdf']));
$qxls = http_build_query(array_merge($_GET, ['r' => 'reporte_excel']));

$title = 'Reportes';
ob_start();
?>
<!-- Hero Title Area -->
<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-emerald-500 mb-2">Reporte de Asistencias</h1>
    <p class="text-slate-400 text-sm">Filtra y exporta el historial de asistencias de los practicantes de forma rápida.</p>
</div>

<!-- Filters Card (Glassmorphism) -->
<div class="relative z-50 bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-2xl mb-8">
    <form method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
        <input type="hidden" name="r" value="reportes">
        
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider ml-1">Desde</label>
            <div class="relative">
                <input type="date" name="desde" value="<?= e($f['desde']) ?>" class="flatpickr-date w-full bg-slate-900/50 border border-slate-700 text-slate-200 text-sm rounded-xl focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 block p-2.5 transition-all outline-none" placeholder="Seleccione fecha">
            </div>
        </div>
        
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider ml-1">Hasta</label>
            <div class="relative">
                <input type="date" name="hasta" value="<?= e($f['hasta']) ?>" class="flatpickr-date w-full bg-slate-900/50 border border-slate-700 text-slate-200 text-sm rounded-xl focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 block p-2.5 transition-all outline-none" placeholder="Seleccione fecha">
            </div>
        </div>
        
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider ml-1">Área</label>
            <select name="area_id" class="searchable-select" placeholder="Todas las áreas">
                <option value="0">Todas las áreas</option>
                <?php foreach ($areas as $a): ?>
                    <option value="<?= (int) $a['id'] ?>" <?= $f['area_id'] === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="space-y-1">
            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider ml-1">Institución</label>
            <select name="institucion_id" class="searchable-select" placeholder="Todas las instituciones">
                <option value="0">Todas las instituciones</option>
                <?php foreach ($insts as $i): ?>
                    <option value="<?= (int) $i['id'] ?>" <?= $f['institucion_id'] === (int) $i['id'] ? 'selected' : '' ?>><?= e($i['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="lg:col-span-2 space-y-1">
            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider ml-1">Practicante</label>
            <select name="practicante_id" class="searchable-select" placeholder="Todos los practicantes">
                <option value="0">Todos los practicantes</option>
                <?php foreach ($pracs as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= $f['practicante_id'] === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['apellidos'] . ', ' . $p['nombres'] . ' (' . $p['dni'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="lg:col-span-2 flex flex-wrap gap-3 sm:justify-end mt-4 lg:mt-0">
            <button type="submit" class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-0.5 transition-all duration-200 outline-none border-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filtrar
            </button>
            <a href="<?= e(app_url('index.php?' . $qpdf)) ?>" class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 rounded-xl bg-slate-800 border border-slate-600 hover:border-red-500 hover:bg-slate-700/50 px-6 py-2.5 text-sm font-bold text-slate-200 hover:text-red-400 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Descargar PDF
            </a>
            <a href="<?= e(app_url('index.php?' . $qxls)) ?>" class="flex-1 sm:flex-none inline-flex justify-center items-center gap-2 rounded-xl bg-slate-800 border border-slate-600 hover:border-green-500 hover:bg-slate-700/50 px-6 py-2.5 text-sm font-bold text-slate-200 hover:text-green-400 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Exportar Excel
            </a>
        </div>
    </form>
</div>

<!-- Results Table -->
<div class="bg-slate-800/60 backdrop-blur-lg border border-slate-700/60 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Top accent line -->
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-teal-500 to-blue-500"></div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-700/80 bg-slate-900/40 text-slate-400 text-xs uppercase tracking-wider font-bold">
                    <th class="px-5 py-4">Fecha</th>
                    <th class="px-5 py-4">DNI</th>
                    <th class="px-5 py-4">Nombre</th>
                    <th class="px-5 py-4">Área</th>
                    <th class="px-5 py-4">Institución</th>
                    <th class="px-5 py-4 text-center">Entrada</th>
                    <th class="px-5 py-4 text-center">Salida</th>
                    <th class="px-5 py-4 text-center">Horas</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="px-5 py-4">Obs.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                <?php foreach ($rows as $r): ?>
                    <?php 
                        $ht = horas_trabajadas($r['hora_entrada'] ?? null, $r['hora_salida'] ?? null);
                        $estado = strtolower((string)$r['estado']);
                        
                        // Determinar color del badge según estado
                        $badgeClass = 'bg-slate-900/50 text-slate-300 border-slate-700'; // Default
                        if ($estado === 'presente') {
                            $badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                        } elseif ($estado === 'tardanza') {
                            $badgeClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                        } elseif ($estado === 'falta') {
                            $badgeClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                        }
                    ?>
                    <tr class="hover:bg-slate-700/30 transition-colors duration-150 group">
                        <td class="px-5 py-4 text-slate-300 font-medium"><?= e((string) $r['fecha']) ?></td>
                        <td class="px-5 py-4 font-mono text-slate-400"><?= e($r['dni']) ?></td>
                        <td class="px-5 py-4 text-slate-200 font-semibold"><?= e(nombre_completo($r['nombres'], $r['apellidos'])) ?></td>
                        <td class="px-5 py-4 text-slate-400 text-xs"><?= e($r['area_nombre'] ?? '') ?></td>
                        <td class="px-5 py-4 text-slate-400 text-xs"><?= e($r['institucion_nombre'] ?? '') ?></td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-block px-2 py-1 bg-slate-900/50 rounded-md font-mono text-slate-300 border border-slate-700/50 group-hover:border-slate-600 transition-colors">
                                <?= $r['hora_entrada'] ? e(substr((string) $r['hora_entrada'], 0, 5)) : '—' ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-block px-2 py-1 bg-slate-900/50 rounded-md font-mono text-slate-300 border border-slate-700/50 group-hover:border-slate-600 transition-colors">
                                <?= $r['hora_salida'] ? e(substr((string) $r['hora_salida'], 0, 5)) : '—' ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center font-mono text-teal-400 font-bold"><?= $ht ? e(substr($ht, 0, 5)) : '—' ?></td>
                        <td class="px-5 py-4 capitalize">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border <?= $badgeClass ?>">
                                <?= e($r['estado']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 max-w-[150px] truncate text-slate-400 text-xs" title="<?= e($r['observacion'] ?? '') ?>">
                            <?= e($r['observacion'] ?? '—') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!count($rows)): ?>
                    <tr>
                        <td colspan="10" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-500">
                                <svg class="w-12 h-12 mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <span class="text-sm font-medium">No se encontraron registros con los filtros actuales</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<!-- Flatpickr CSS y JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<style>
/* Personalización de Tom Select para tema oscuro / tailwind */
.ts-control {
    background-color: rgba(15, 23, 42, 0.5) !important; /* bg-slate-900/50 */
    border: 1px solid #334155 !important; /* border-slate-700 */
    color: #e2e8f0 !important;
    border-radius: 0.75rem !important; /* rounded-xl */
    padding: 0.625rem 0.75rem !important;
    min-height: 42px;
}
.ts-control.focus {
    border-color: #14b8a6 !important; /* teal-500 */
    box-shadow: 0 0 0 2px rgba(20, 184, 166, 0.5) !important;
}
.ts-control > input {
    color: #e2e8f0 !important;
}
.ts-dropdown {
    background-color: #1e293b !important; /* slate-800 */
    border: 1px solid #334155 !important; /* border-slate-700 */
    color: #e2e8f0 !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
}
.ts-dropdown .active {
    background-color: rgba(20, 184, 166, 0.2) !important;
    color: #5eead4 !important; /* teal-300 */
}
.ts-dropdown .option {
    padding: 0.5rem 0.75rem;
}
.ts-dropdown .option:hover {
    background-color: rgba(51, 65, 85, 0.5) !important; /* slate-700 */
}
/* Flatpickr adaptado */
.flatpickr-calendar {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
    border: 1px solid #334155 !important; /* border-slate-700 */
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar TomSelect en selects con la clase searchable-select
    document.querySelectorAll('.searchable-select').forEach(function(el) {
        new TomSelect(el, {
            create: false,
            maxOptions: 100,
            wrapperClass: 'ts-wrapper w-full text-sm',
            copyClassesToDropdown: false,
            copyClassesToWrapper: false
        });
    });

    // Inicializar Flatpickr en inputs de fecha
    flatpickr(".flatpickr-date", {
        locale: "es",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        disableMobile: "true"
    });
});
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
