<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo = db();

// 1. Asistencia semanal (últimos 7 días)
$sem = $pdo->query(
    "SELECT DATE_FORMAT(fecha, '%W') AS d_en, fecha, COUNT(DISTINCT practicante_id) AS c
     FROM asistencias
     WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND hora_entrada IS NOT NULL
     GROUP BY fecha ORDER BY fecha"
)->fetchAll();

// Mapeo básico de días para el gráfico
$dias_es = ['Monday' => 'Lun', 'Tuesday' => 'Mar', 'Wednesday' => 'Mié', 'Thursday' => 'Jue', 'Friday' => 'Vie', 'Saturday' => 'Sáb', 'Sunday' => 'Dom'];
$semLabels = [];
$semData = [];
foreach ($sem as $row) {
    $semLabels[] = $dias_es[$row['d_en']] ?? substr($row['fecha'], 8, 2);
    $semData[] = (int) $row['c'];
}

// 2. Asistencia mensual (12 meses)
$meses_es = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
$mes = $pdo->query(
    "SELECT DATE_FORMAT(fecha, '%Y-%m') AS m, COUNT(DISTINCT practicante_id) AS c
     FROM asistencias
     WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) AND hora_entrada IS NOT NULL
     GROUP BY m ORDER BY m"
)->fetchAll();

$mesLabels = [];
$mesData = [];
$totalMeses = 0;
foreach ($mes as $row) {
    $numMes = substr($row['m'], 5, 2);
    $mesLabels[] = $meses_es[$numMes] ?? $row['m'];
    $cnt = (int) $row['c'];
    $mesData[] = $cnt;
    $totalMeses += $cnt;
}

$promedioMensual = count($mesData) > 0 ? round($totalMeses / count($mesData), 1) : 0;

// 3. Registros de asistencia de hoy (para la tabla)
$hoy = today_sql();
$list = $pdo->prepare(
    'SELECT a.*, p.dni, p.nombres, p.apellidos, ar.nombre AS area_nombre
     FROM asistencias a
     INNER JOIN practicantes p ON p.id = a.practicante_id
     LEFT JOIN areas ar ON ar.id = p.area_id
     WHERE a.fecha = ?
     ORDER BY a.hora_entrada DESC'
);
$list->execute([$hoy]);
$rows = $list->fetchAll();

$title = 'Estadísticas';
ob_start();
?>

<!-- ═══ GRÁFICOS INFERIORES ══════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- Semanal -->
    <div class="ui-panel rounded-2xl p-6">
        <div class="flex items-start gap-3 mb-6">
            <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-stone-800 flex items-center justify-center text-slate-500 dark:text-stone-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            </div>
            <div>
                <h3 class="font-display font-bold text-base text-slate-800 dark:text-stone-100">Asistencia semanal (últimos 7 días)</h3>
                <p class="text-xs text-slate-500 dark:text-stone-400 mt-0.5">Registros de asistencia por día</p>
            </div>
        </div>
        <div class="relative h-[220px] w-full">
            <canvas id="chartSem"></canvas>
        </div>
    </div>

    <!-- Mensual -->
    <div class="ui-panel rounded-2xl p-6 flex flex-col">
        <div class="flex items-start gap-3 mb-6">
            <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-stone-800 flex items-center justify-center text-slate-500 dark:text-stone-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div>
                <h3 class="font-display font-bold text-base text-slate-800 dark:text-stone-100">Asistencia mensual (12 meses)</h3>
                <p class="text-xs text-slate-500 dark:text-stone-400 mt-0.5">Resumen de asistencia por mes</p>
            </div>
        </div>
        
        <div class="relative h-[140px] w-full mb-4">
            <canvas id="chartMes"></canvas>
        </div>

        <!-- Resumen (Footer del chart) -->
        <div class="mt-auto grid grid-cols-2 gap-4 pt-4 border-t border-slate-200 dark:border-stone-700/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#26263A]/10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#26263A] dark:text-[#7A7AA3]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Promedio mensual</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-stone-100 leading-none mt-1"><?= $promedioMensual ?> <span class="text-xs font-normal text-slate-500">registros</span></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-stone-800 flex items-center justify-center shrink-0">
                    <span class="text-slate-500 dark:text-stone-400 text-lg">∑</span>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Total (12 meses)</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-stone-100 leading-none mt-1"><?= $totalMeses ?> <span class="text-xs font-normal text-slate-500">registros</span></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ TABLA REGISTROS DE HOY ══════════════════════════════ -->
<?php if ($user['rol'] === 'admin'): ?>
<div class="ui-panel rounded-2xl overflow-hidden ui-animate-entry">
    <!-- Header con filtros -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-stone-700">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="font-display font-bold text-base text-slate-800 dark:text-stone-200">Últimos registros de hoy</h2>
            <div class="flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">🔍</span>
                    <input type="text" id="searchTable" placeholder="Buscar practicante..."
                           class="pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs w-44 focus:outline-none focus:ring-1 focus:ring-[#26263A]/30 focus:border-[#26263A] transition">
                </div>
                <select id="filterEstado" class="rounded-lg border border-slate-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#26263A]/30">
                    <option value="">Todos los estados</option>
                    <option value="presente">Puntual</option>
                    <option value="tardanza">Tardanza</option>
                    <option value="falta">Falta</option>
                </select>
                <button type="button" onclick="window.location.reload()" class="h-7 w-7 flex items-center justify-center rounded-lg border border-slate-200 dark:border-stone-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-400 text-xs transition" title="Actualizar">
                    🔄
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm" id="tablaRegistros">
            <thead>
                <tr class="bg-slate-50 dark:bg-stone-800/50">
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">DNI</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Nombre</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Área</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Entrada</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Salida</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Horas</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Estado</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Obs.</th>
                    <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-stone-800">
                <?php foreach ($rows as $r): ?>
                <?php $ht = horas_trabajadas($r['hora_entrada'] ?? null, $r['hora_salida'] ?? null); ?>
                <tr class="hover:bg-[#26263A]/5 dark:hover:bg-[#26263A]/10 transition-colors" data-estado="<?= e($r['estado']) ?>" data-search="<?= e(strtolower($r['dni'] . ' ' . $r['nombres'] . ' ' . $r['apellidos'])) ?>">
                    <td class="px-5 py-3 font-mono text-xs text-slate-500 dark:text-stone-400"><?= e($r['dni']) ?></td>
                    <td class="px-5 py-3 font-semibold text-slate-800 dark:text-stone-200 whitespace-nowrap"><?= e(nombre_completo($r['nombres'], $r['apellidos'])) ?></td>
                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-stone-400 uppercase"><?= e($r['area_nombre'] ?? 'Sin área') ?></td>
                    <td class="px-5 py-3 font-mono text-xs"><?= e(substr((string) $r['hora_entrada'], 0, 5)) ?></td>
                    <td class="px-5 py-3 font-mono text-xs"><?= $r['hora_salida'] ? e(substr((string) $r['hora_salida'], 0, 5)) : '—' ?></td>
                    <td class="px-5 py-3 font-mono text-xs"><?= $ht ? e(substr($ht, 0, 5)) : '—' ?></td>
                    <td class="px-5 py-3">
                        <?php if ($r['estado'] === 'tardanza'): ?>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Tardanza
                            </span>
                        <?php elseif ($r['estado'] === 'presente'): ?>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Puntual
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-slate-100 dark:bg-stone-700 text-slate-500 capitalize">
                                <?= e($r['estado']) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-400 max-w-[120px] truncate" title="<?= e($r['observacion'] ?? '') ?>"><?= e($r['observacion'] ?? '—') ?></td>
                    <td class="px-5 py-3 text-right">
                        <a href="<?= e(app_url('index.php?r=asistencia_edit&id=' . (int) $r['id'])) ?>"
                           class="inline-flex items-center justify-center h-7 w-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-[#26263A] transition"
                           title="Editar">
                            ✏️
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!count($rows)): ?>
                <tr><td colspan="9" class="px-5 py-12 text-center text-slate-400">
                    <div class="flex flex-col items-center gap-2">
                        <span class="text-4xl">📭</span>
                        <p class="font-medium">Sin registros hoy</p>
                    </div>
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer con conteo -->
    <div class="px-5 py-3 border-t border-slate-200 dark:border-stone-700 flex items-center justify-between text-xs text-slate-400">
        <span>Mostrando <span id="showCount"><?= count($rows) ?></span> de <?= count($rows) ?> registros</span>
        <span class="text-[10px]">📅 <?= e($hoy) ?></span>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.classList.contains('dark');
    
    if (typeof Chart !== 'undefined') {
        Chart.defaults.color = isDark ? '#94a3b8' : '#64748b';
        Chart.defaults.font.family = '"Source Sans 3", sans-serif';
        Chart.defaults.scale.grid.color = isDark ? '#1e293b' : '#f1f5f9';
    }

    const brandColor = isDark ? '#7A7AA3' : '#26263A';

    // 1. Weekly smooth line chart (Spline)
    const ctxSem = document.getElementById('chartSem')?.getContext('2d');
    if (ctxSem) {
        // Create gradient for line chart
        const gradientSem = ctxSem.createLinearGradient(0, 0, 0, 220);
        gradientSem.addColorStop(0, isDark ? 'rgba(119, 172, 162, 0.4)' : 'rgba(70, 129, 137, 0.3)');
        gradientSem.addColorStop(1, isDark ? 'rgba(119, 172, 162, 0.0)' : 'rgba(70, 129, 137, 0.0)');

        new Chart(ctxSem, {
            type: 'line',
            data: {
                labels: <?= json_encode($semLabels, JSON_THROW_ON_ERROR) ?>,
                datasets: [{
                    label: 'Asistencias',
                    data: <?= json_encode($semData, JSON_THROW_ON_ERROR) ?>,
                    borderColor: brandColor,
                    backgroundColor: gradientSem,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4, // Smooth curves
                    pointBackgroundColor: brandColor,
                    pointBorderColor: isDark ? '#0f172a' : '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#ffffff',
                        titleColor: isDark ? '#f8fafc' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: { label: ctx => ctx.raw + ' registros' }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, maxTicksLimit: 5 },
                        border: { display: false }
                    },
                    x: { 
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // 2. Monthly bar chart
    const ctxMes = document.getElementById('chartMes')?.getContext('2d');
    if (ctxMes) {
        // Create gradient for bars
        const gradientMes = ctxMes.createLinearGradient(0, 0, 0, 140);
        gradientMes.addColorStop(0, brandColor);
        gradientMes.addColorStop(1, isDark ? 'rgba(119, 172, 162, 0.3)' : 'rgba(70, 129, 137, 0.5)');

        new Chart(ctxMes, {
            type: 'bar',
            data: {
                labels: <?= json_encode($mesLabels, JSON_THROW_ON_ERROR) ?>,
                datasets: [{
                    label: 'Registros',
                    data: <?= json_encode($mesData, JSON_THROW_ON_ERROR) ?>,
                    backgroundColor: gradientMes,
                    hoverBackgroundColor: brandColor,
                    borderRadius: 4,
                    barPercentage: 0.5,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#ffffff',
                        titleColor: isDark ? '#f8fafc' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: { label: ctx => ctx.raw + ' registros' }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 5, maxTicksLimit: 4 },
                        border: { display: false }
                    },
                    x: { 
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // Table search & filter
    const searchInput = document.getElementById('searchTable');
    const filterSelect = document.getElementById('filterEstado');
    const tableRows = document.querySelectorAll('#tablaRegistros tbody tr[data-estado]');

    function filterTable() {
        const q = searchInput?.value.toLowerCase().trim() || '';
        const est = filterSelect?.value || '';
        let visible = 0;
        tableRows.forEach(tr => {
            const matchSearch = !q || tr.dataset.search.includes(q);
            const matchEstado = !est || tr.dataset.estado === est;
            const show = matchSearch && matchEstado;
            tr.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const countSpan = document.getElementById('showCount');
        if (countSpan) countSpan.textContent = visible;
    }
    searchInput?.addEventListener('input', filterTable);
    filterSelect?.addEventListener('change', filterTable);
});
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
