<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo = db();
actualizar_practicantes_vencidos($pdo);

// 1. Métricas generales
$totalPracticantes = (int) $pdo->query('SELECT COUNT(*) FROM practicantes')->fetchColumn();
$activos = (int) $pdo->query("SELECT COUNT(*) FROM practicantes WHERE estado='activo'")->fetchColumn();

$asistHoy = (int) $pdo->query(
    "SELECT COUNT(DISTINCT practicante_id) FROM asistencias WHERE fecha = CURDATE() AND hora_entrada IS NOT NULL"
)->fetchColumn();

$tardHoy = (int) $pdo->query(
    "SELECT COUNT(*) FROM asistencias WHERE fecha = CURDATE() AND estado = 'tardanza'"
)->fetchColumn();

$puntualHoy = (int) $pdo->query(
    "SELECT COUNT(*) FROM asistencias WHERE fecha = CURDATE() AND estado = 'presente'"
)->fetchColumn();

$faltasHoy = (int) $pdo->query(
    "SELECT COUNT(*) FROM practicantes p WHERE p.estado = 'activo'
     AND NOT EXISTS (
       SELECT 1 FROM asistencias a WHERE a.practicante_id = p.id AND a.fecha = CURDATE() AND a.hora_entrada IS NOT NULL
     )"
)->fetchColumn();

// Nuevos este mes
$nuevosMes = (int) $pdo->query(
    "SELECT COUNT(*) FROM practicantes WHERE MONTH(fecha_inicio) = MONTH(CURDATE()) AND YEAR(fecha_inicio) = YEAR(CURDATE())"
)->fetchColumn();

// Porcentaje activos
$pctActivos = $totalPracticantes > 0 ? round(($activos / $totalPracticantes) * 100) : 0;

// Asistencia por día de la semana actual (Lun-Dom)
$semana = $pdo->query(
    "SELECT DAYOFWEEK(fecha) AS dow, COUNT(DISTINCT practicante_id) AS c
     FROM asistencias
     WHERE YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1) AND hora_entrada IS NOT NULL
     GROUP BY dow"
)->fetchAll();
$diasSem = [0,0,0,0,0,0,0]; // Lun=0 .. Dom=6
foreach ($semana as $s) {
    $idx = ((int)$s['dow'] + 5) % 7; // MySQL: 1=Sun -> map to Mon=0
    $diasSem[$idx] = (int) $s['c'];
}

// Resumen general porcentajes
$totalReg = $puntualHoy + $tardHoy + $faltasHoy;
$pctPuntual = $totalReg > 0 ? round(($puntualHoy / $totalReg) * 100) : 0;
$pctTard = $totalReg > 0 ? round(($tardHoy / $totalReg) * 100) : 0;
$pctFalta = $totalReg > 0 ? round(($faltasHoy / $totalReg) * 100) : 0;

// Count de registros de hoy para Estado del sistema
$registrosHoyCount = (int) $pdo->query("SELECT COUNT(*) FROM asistencias WHERE fecha = CURDATE()")->fetchColumn();

$title = 'Inicio';
ob_start();
?>

<!-- ═══ METRICAS TOP ═══════════════════════════════════════ -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 mb-6">
    <!-- Total practicantes -->
    <div class="ui-panel rounded-2xl p-4 sm:p-5 ui-animate-entry delay-100 group hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-3">
            <div class="h-11 w-11 rounded-full flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #26263A, #7A7AA3);">
                <span class="text-white text-lg">👥</span>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-stone-500">Total practicantes</p>
                <p class="text-2xl sm:text-3xl font-extrabold tabular-nums text-slate-800 dark:text-stone-100 leading-none mt-0.5"><?= $totalPracticantes ?></p>
            </div>
        </div>
        <div class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
            <span>📈</span> +<?= $nuevosMes ?> este mes
        </div>
    </div>

    <!-- Activos -->
    <div class="ui-panel rounded-2xl p-4 sm:p-5 ui-animate-entry delay-200 group hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-3">
            <div class="h-11 w-11 rounded-full flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #10b981, #34d399);">
                <span class="text-white text-lg">👤</span>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-stone-500">Activos</p>
                <p class="text-2xl sm:text-3xl font-extrabold tabular-nums text-slate-800 dark:text-stone-100 leading-none mt-0.5"><?= $activos ?></p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span><?= $pctActivos ?>% activos</span>
            <div class="flex-1 h-1.5 rounded-full bg-slate-200 dark:bg-stone-700 overflow-hidden">
                <div class="h-full rounded-full bg-emerald-500 transition-all" style="width:<?= $pctActivos ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Asistencias hoy -->
    <div class="ui-panel rounded-2xl p-4 sm:p-5 ui-animate-entry delay-300 group hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-3">
            <div class="h-11 w-11 rounded-full flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                <span class="text-white text-lg">📅</span>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-stone-500">Asistencias hoy</p>
                <p class="text-2xl sm:text-3xl font-extrabold tabular-nums text-slate-800 dark:text-stone-100 leading-none mt-0.5"><?= $asistHoy ?></p>
            </div>
        </div>
        <a href="<?= e(app_url('index.php?r=asistencia')) ?>" class="text-xs text-blue-500 hover:text-blue-700 dark:text-blue-400 font-medium flex items-center gap-1 group-hover:gap-2 transition-all">
            Ver detalles <span>›</span>
        </a>
    </div>

    <!-- Tardanzas hoy -->
    <div class="ui-panel rounded-2xl p-4 sm:p-5 ui-animate-entry delay-400 group hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-3">
            <div class="h-11 w-11 rounded-full flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                <span class="text-white text-lg">⏰</span>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-stone-500">Tardanzas hoy</p>
                <p class="text-2xl sm:text-3xl font-extrabold tabular-nums text-slate-800 dark:text-stone-100 leading-none mt-0.5"><?= $tardHoy ?></p>
            </div>
        </div>
        <a href="<?= e(app_url('index.php?r=reportes')) ?>" class="text-xs text-amber-500 hover:text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1 group-hover:gap-2 transition-all">
            Ver detalles <span>›</span>
        </a>
    </div>

    <!-- Faltas hoy -->
    <div class="ui-panel rounded-2xl p-4 sm:p-5 ui-animate-entry delay-500 group hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-3">
            <div class="h-11 w-11 rounded-full flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #ef4444, #f87171);">
                <span class="text-white text-lg">⚠️</span>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-stone-500">Faltas hoy</p>
                <p class="text-2xl sm:text-3xl font-extrabold tabular-nums text-slate-800 dark:text-stone-100 leading-none mt-0.5"><?= $faltasHoy ?></p>
            </div>
        </div>
        <a href="<?= e(app_url('index.php?r=reportes')) ?>" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 font-medium flex items-center gap-1 group-hover:gap-2 transition-all">
            Ver detalles <span>›</span>
        </a>
    </div>
</div>

<!-- ═══ ACCESOS RÁPIDOS ════════════════════════════════════ -->
<div class="mb-6 ui-animate-entry delay-200">
    <h2 class="font-display text-lg font-bold text-slate-800 dark:text-stone-200 mb-4">Accesos rápidos</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <?php
        $accesos = [
            ['Asistencia', 'Registrar ingreso de practicantes', 'asistencia', 'Registrar', '#26263A', '#7A7AA3', '📋'],
            ['Escáner QR', 'Escanear código QR de practicantes', 'escaner', 'Escanear', '#3b82f6', '#60a5fa', '📷'],
            ['Practicantes', 'Gestionar perfiles y asignaciones', 'practicantes', 'Gestionar', '#10b981', '#34d399', '👤'],
            ['Reportes', 'Generar reportes en PDF / Excel', 'reportes', 'Generar', '#f59e0b', '#fbbf24', '📄'],
        ];
        foreach ($accesos as [$aTitle, $aDesc, $aRoute, $aBtn, $c1, $c2, $aIcon]):
        ?>
        <div class="ui-panel rounded-2xl p-4 sm:p-5 flex flex-col gap-3 hover:shadow-md transition-shadow group">
            <div class="h-12 w-12 rounded-xl flex items-center justify-center text-2xl text-white shrink-0" style="background: linear-gradient(135deg, <?= $c1 ?>, <?= $c2 ?>);">
                <?= $aIcon ?>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-sm text-slate-800 dark:text-stone-200"><?= e($aTitle) ?></h3>
                <p class="text-xs text-slate-400 dark:text-stone-500 mt-0.5 leading-relaxed"><?= e($aDesc) ?></p>
            </div>
            <a href="<?= e(app_url('index.php?r=' . $aRoute)) ?>"
               class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-xs font-semibold border-2 transition-all duration-200 hover:text-white self-start"
               style="border-color: <?= $c1 ?>; color: <?= $c1 ?>;"
               onmouseover="this.style.background='<?= $c1 ?>';this.style.color='#fff'"
               onmouseout="this.style.background='transparent';this.style.color='<?= $c1 ?>'">
                <?= e($aBtn) ?>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ═══ FILA INTERMEDIA: Gráfico semanal + Resumen + Estado ══════ -->
<div class="grid gap-4 lg:grid-cols-3 mb-6 ui-animate-entry delay-300">
    <!-- Asistencias por semana -->
    <div class="ui-panel rounded-2xl p-4 sm:p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-bold text-sm text-slate-800 dark:text-stone-200">Asistencias por semana</h3>
            <span class="text-[10px] font-semibold px-2 py-1 rounded-md bg-slate-100 dark:bg-stone-800 text-slate-500">Esta semana</span>
        </div>
        <div style="height:200px;">
            <canvas id="chartWeek"></canvas>
        </div>
    </div>

    <!-- Resumen general (donut) -->
    <div class="ui-panel rounded-2xl p-4 sm:p-5">
        <h3 class="font-display font-bold text-sm text-slate-800 dark:text-stone-200 mb-4">Resumen general</h3>
        <div class="flex items-center gap-4">
            <div class="relative w-32 h-32 shrink-0">
                <canvas id="chartDonut" width="128" height="128"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-xs text-slate-400 font-medium">Total</span>
                    <span class="text-2xl font-extrabold text-slate-800 dark:text-stone-100"><?= $totalReg ?></span>
                    <span class="text-[10px] text-slate-400">registros</span>
                </div>
            </div>
            <div class="space-y-3 text-sm flex-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="text-slate-600 dark:text-stone-400">Puntuales</span>
                    </div>
                    <span class="font-bold text-slate-800 dark:text-stone-200"><?= $pctPuntual ?>% <span class="font-normal text-slate-400">(<?= $puntualHoy ?>)</span></span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-400 shrink-0"></span>
                        <span class="text-slate-600 dark:text-stone-400">Tardanzas</span>
                    </div>
                    <span class="font-bold text-slate-800 dark:text-stone-200"><?= $pctTard ?>% <span class="font-normal text-slate-400">(<?= $tardHoy ?>)</span></span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-400 shrink-0"></span>
                        <span class="text-slate-600 dark:text-stone-400">Faltas</span>
                    </div>
                    <span class="font-bold text-slate-800 dark:text-stone-200"><?= $pctFalta ?>% <span class="font-normal text-slate-400">(<?= $faltasHoy ?>)</span></span>
                </div>
            </div>
        </div>
        <p class="text-[10px] text-slate-400 mt-3 text-center">⏱ Última actualización: <?= date('H:i') ?></p>
    </div>

    <!-- Estado del sistema -->
    <div class="ui-panel rounded-2xl p-4 sm:p-5">
        <h3 class="font-display font-bold text-sm text-slate-800 dark:text-stone-200 mb-4">Estado del sistema</h3>
        <div class="flex items-center gap-2 mb-4">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Sistema QR activo</span>
        </div>
        <div class="space-y-3 text-sm">
            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-stone-800/50">
                <span class="text-lg mt-0.5">📷</span>
                <div>
                    <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">Último escaneo</p>
                    <?php
                    $lastScan = $pdo->query("SELECT hora_entrada FROM asistencias WHERE fecha = CURDATE() ORDER BY id DESC LIMIT 1")->fetchColumn();
                    ?>
                    <p class="font-medium text-slate-700 dark:text-stone-300"><?= $lastScan ? e(substr($lastScan, 0, 5)) : 'Sin escaneos hoy' ?></p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-stone-800/50">
                <span class="text-lg mt-0.5">🏢</span>
                <div>
                    <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">Hora límite entrada</p>
                    <p class="font-medium text-slate-700 dark:text-stone-300"><?= e(tardanza_limite_hora()) ?></p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-stone-800/50">
                <span class="text-lg mt-0.5">📊</span>
                <div>
                    <p class="text-[11px] text-slate-400 uppercase font-semibold tracking-wide">Registros hoy</p>
                    <p class="font-medium text-slate-700 dark:text-stone-300"><?= $registrosHoyCount ?> registro(s)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.classList.contains('dark');
    if (typeof Chart !== 'undefined') {
        Chart.defaults.color = isDark ? '#94a3b8' : '#64748b';
        Chart.defaults.font.family = '"Source Sans 3", sans-serif';
    }

    // Weekly bar chart
    const weekData = <?= json_encode($diasSem, JSON_THROW_ON_ERROR) ?>;
    const weekLabels = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
    new Chart(document.getElementById('chartWeek'), {
        type: 'bar',
        data: {
            labels: weekLabels,
            datasets: [{
                data: weekData,
                backgroundColor: weekData.map((v,i) => {
                    const today = (new Date().getDay() + 6) % 7;
                    return i === today
                        ? (isDark ? '#7A7AA3' : '#26263A')
                        : (isDark ? 'rgba(119,172,162,0.25)' : 'rgba(70,129,137,0.2)');
                }),
                hoverBackgroundColor: isDark ? '#DCDCEC' : '#26263A',
                borderRadius: 6,
                barPercentage: 0.6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ctx.raw + ' asistencia(s)' }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: isDark ? '#1e293b' : '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Donut chart
    const donutData = [<?= $puntualHoy ?>, <?= $tardHoy ?>, <?= $faltasHoy ?>];
    const hasData = donutData.some(v => v > 0);
    new Chart(document.getElementById('chartDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Puntuales', 'Tardanzas', 'Faltas'],
            datasets: [{
                data: hasData ? donutData : [1],
                backgroundColor: hasData
                    ? ['#10b981', '#fbbf24', '#f87171']
                    : [isDark ? '#1e293b' : '#e2e8f0'],
                borderWidth: 0,
                cutout: '72%',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false }, tooltip: { enabled: hasData } },
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
