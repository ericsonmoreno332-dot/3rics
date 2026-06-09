<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo = db();

$q = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT p.*, a.nombre AS area_nombre, i.nombre AS institucion_nombre,
        CONCAT(p.nombres, " ", p.apellidos) AS nombre_completo
        FROM practicantes p
        LEFT JOIN areas a ON a.id = p.area_id
        LEFT JOIN instituciones i ON i.id = p.institucion_id';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE (p.dni LIKE ? OR p.nombres LIKE ? OR p.apellidos LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
}
$sql .= ' ORDER BY p.apellidos, p.nombres LIMIT 200';
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$totalCount = count($rows);
$activosCount = 0;
$inactivosCount = 0;
foreach ($rows as $row) {
    if ($row['estado'] === 'activo') $activosCount++;
    else $inactivosCount++;
}

$title = 'Practicantes';
ob_start();
?>

<!-- ═══ HERO HEADER ═══════════════════════════════════════════ -->
<div class="mb-6 ui-animate-entry">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-display text-transparent bg-clip-text bg-gradient-to-r from-[#284b63] to-[#3c6e71]">
                👥 Practicantes
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Gestiona y consulta todos los practicantes registrados.</p>
        </div>
    </div>
</div>

<!-- ═══ MINI STATS ════════════════════════════════════════════ -->
<div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6 ui-animate-entry delay-100">
    <div class="rounded-xl p-3 sm:p-4 bg-gradient-to-br from-[#284b63] to-[#353535] text-white shadow-md">
        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-white/70">Total</p>
        <p class="text-xl sm:text-2xl font-bold mt-1"><?= $totalCount ?></p>
    </div>
    <div class="rounded-xl p-3 sm:p-4 bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-md">
        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-white/70">Activos</p>
        <p class="text-xl sm:text-2xl font-bold mt-1"><?= $activosCount ?></p>
    </div>
    <div class="rounded-xl p-3 sm:p-4 bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md">
        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-white/70">Inactivos</p>
        <p class="text-xl sm:text-2xl font-bold mt-1"><?= $inactivosCount ?></p>
    </div>
</div>

<!-- ═══ SEARCH BAR ════════════════════════════════════════════ -->
<div class="mb-6 ui-animate-entry delay-200">
    <form method="get" class="relative flex gap-3">
        <input type="hidden" name="r" value="practicantes">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">🔍</span>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por DNI, nombre o apellido…"
                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#284b63]/30 focus:border-[#284b63] transition-all">
        </div>
        <button type="submit" class="ui-btn-primary px-5">Buscar</button>
    </form>
    <?php if ($q !== ''): ?>
        <div class="mt-2 flex items-center gap-2">
            <span class="text-xs text-slate-500 dark:text-slate-400">Mostrando resultados para "<strong class="text-[#284b63] dark:text-[#3c6e71]"><?= e($q) ?></strong>"</span>
            <a href="<?= e(app_url('index.php?r=practicantes')) ?>" class="text-xs text-red-400 hover:text-red-300 font-medium transition-colors">✕ Limpiar</a>
        </div>
    <?php endif; ?>
</div>

<!-- ═══ TABLE (DESKTOP) ═══════════════════════════════════════ -->
<div class="hidden sm:block ui-table-wrap-shadow ui-animate-entry delay-300">
    <table class="ui-table-left">
        <thead class="ui-thead">
            <tr>
                <th class="ui-th">Practicante</th>
                <th class="ui-th">Carrera</th>
                <th class="ui-th">Institución</th>
                <th class="ui-th">Área</th>
                <th class="ui-th text-center">Estado</th>
                <th class="ui-th text-center">QR</th>
                <th class="ui-th-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="ui-tbody">
            <?php foreach ($rows as $row): ?>
            <?php
                $initials = mb_strtoupper(mb_substr($row['nombres'], 0, 1) . mb_substr($row['apellidos'], 0, 1));
                $isActive = $row['estado'] === 'activo';
            ?>
            <tr class="ui-tr-hover">
                <!-- Practicante (avatar + name + DNI) -->
                <td class="ui-td">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                             style="background: linear-gradient(135deg, #284b63, #3c6e71);">
                            <?= $initials ?>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm text-slate-800 dark:text-slate-200 truncate"><?= e(nombre_completo($row['nombres'], $row['apellidos'])) ?></p>
                            <p class="text-xs font-mono text-slate-400"><?= e($row['dni']) ?></p>
                        </div>
                    </div>
                </td>
                <td class="ui-td text-sm text-slate-600 dark:text-slate-400"><?= e($row['carrera']) ?></td>
                <td class="ui-td text-sm text-slate-600 dark:text-slate-400"><?= e($row['institucion_nombre'] ?? '—') ?></td>
                <td class="ui-td text-sm text-slate-600 dark:text-slate-400"><?= e($row['area_nombre'] ?? '—') ?></td>
                <!-- Estado badge -->
                <td class="ui-td text-center">
                    <?php if ($row['estado'] === 'activo'): ?>
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Activo
                        </span>
                    <?php elseif ($row['estado'] === 'finalizado'): ?>
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> Finalizado
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> <?= e(ucfirst($row['estado'])) ?>
                        </span>
                    <?php endif; ?>
                </td>
                <!-- QR button -->
                <td class="ui-td text-center">
                    <button type="button" class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-[#284b63]/10 hover:bg-[#284b63]/20 text-[#284b63] dark:text-[#d9d9d9] transition-all duration-200 hover:scale-110 border-none cursor-pointer" onclick="mostrarQR('<?= e(app_url('index.php?r=qr_png&id=' . (int) $row['id'])) ?>', '<?= e(nombre_completo($row['nombres'], $row['apellidos'])) ?>')" title="Ver código QR">
                        📱
                    </button>
                </td>
                <!-- Actions -->
                <td class="ui-td text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="<?= e(app_url('index.php?r=practicante_form&id=' . (int) $row['id'])) ?>"
                           class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-[#284b63] dark:text-[#d9d9d9] bg-[#284b63]/10 hover:bg-[#284b63]/20 transition-all duration-200">
                            ✏️ Editar
                        </a>
                        <button type="button"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-500 bg-red-500/10 hover:bg-red-500/20 transition-all duration-200 border-none cursor-pointer"
                                onclick="confirmarEliminarListado(event, '<?= e(nombre_completo($row['nombres'], $row['apellidos'])) ?>', '<?= e(app_url('index.php?r=practicante_delete&id=' . (int) $row['id'] . '&_csrf=' . urlencode(csrf_token()))) ?>')">
                            🗑️ Eliminar
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!count($rows)): ?>
                <tr><td colspan="7" class="ui-empty">
                    <div class="flex flex-col items-center gap-2 py-8">
                        <span class="text-4xl">📭</span>
                        <p class="text-slate-400 font-medium">No se encontraron practicantes</p>
                        <?php if ($q !== ''): ?>
                            <a href="<?= e(app_url('index.php?r=practicantes')) ?>" class="text-sm text-[#284b63] hover:underline">Limpiar búsqueda</a>
                        <?php endif; ?>
                    </div>
                </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ═══ CARDS (MOBILE) ════════════════════════════════════════ -->
<div class="sm:hidden space-y-3 ui-animate-entry delay-300">
    <?php foreach ($rows as $row): ?>
    <?php
        $initials = mb_strtoupper(mb_substr($row['nombres'], 0, 1) . mb_substr($row['apellidos'], 0, 1));
        $isActive = $row['estado'] === 'activo';
    ?>
    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm">
        <!-- Header: Avatar + Name + Status -->
        <div class="flex items-center gap-3 mb-3">
            <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0"
                 style="background: linear-gradient(135deg, #284b63, #3c6e71);">
                <?= $initials ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm text-slate-800 dark:text-slate-200 truncate"><?= e(nombre_completo($row['nombres'], $row['apellidos'])) ?></p>
                <p class="text-xs font-mono text-slate-400"><?= e($row['dni']) ?></p>
            </div>
            <?php if ($row['estado'] === 'activo'): ?>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 shrink-0">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Activo
                </span>
            <?php elseif ($row['estado'] === 'finalizado'): ?>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 shrink-0">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> Finalizado
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 shrink-0">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> <?= e(ucfirst($row['estado'])) ?>
                </span>
            <?php endif; ?>
        </div>
        <!-- Details -->
        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 mb-3 pl-[52px]">
            <p>📚 <?= e($row['carrera']) ?></p>
            <p>🏫 <?= e($row['institucion_nombre'] ?? '—') ?></p>
            <p>🏢 <?= e($row['area_nombre'] ?? '—') ?></p>
        </div>
        <!-- Actions -->
        <div class="flex items-center gap-2 pl-[52px]">
            <a href="<?= e(app_url('index.php?r=practicante_form&id=' . (int) $row['id'])) ?>"
               class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg px-3 py-2 text-xs font-semibold text-[#284b63] dark:text-[#d9d9d9] bg-[#284b63]/10 hover:bg-[#284b63]/20 transition-all">
                ✏️ Editar
            </a>
            <button type="button" class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-[#284b63]/10 hover:bg-[#284b63]/20 text-[#284b63] dark:text-[#d9d9d9] transition-all border-none cursor-pointer" onclick="mostrarQR('<?= e(app_url('index.php?r=qr_png&id=' . (int) $row['id'])) ?>', '<?= e(nombre_completo($row['nombres'], $row['apellidos'])) ?>')" title="Ver QR">
                📱
            </button>
            <button type="button"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-500 transition-all border-none cursor-pointer"
                    onclick="confirmarEliminarListado(event, '<?= e(nombre_completo($row['nombres'], $row['apellidos'])) ?>', '<?= e(app_url('index.php?r=practicante_delete&id=' . (int) $row['id'] . '&_csrf=' . urlencode(csrf_token()))) ?>')">
                🗑️
            </button>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (!count($rows)): ?>
    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-8 text-center shadow-sm">
        <span class="text-4xl block mb-2">📭</span>
        <p class="text-slate-400 font-medium text-sm">No se encontraron practicantes</p>
    </div>
    <?php endif; ?>
</div>

<!-- ═══ RESULT COUNT ══════════════════════════════════════════ -->
<?php if ($totalCount > 0): ?>
<div class="mt-4 text-center text-xs text-slate-400 dark:text-slate-500">
    Mostrando <strong class="text-slate-600 dark:text-slate-300"><?= $totalCount ?></strong> practicante(s)
</div>
<?php endif; ?>

<script>
function confirmarEliminarListado(event, nombre, deleteUrl) {
    event.preventDefault();
    const isDark = document.documentElement.classList.contains('dark');
    
    Swal.fire({
        title: '¿Estás seguro?',
        html: `¿Deseas eliminar al practicante <strong class="text-[#284b63] dark:text-[#3c6e71]">${nombre}</strong>?<br><span class="text-xs text-red-500 font-medium mt-1 block">Se borrará completamente de la base de datos junto con sus asistencias y cuenta vinculada.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: isDark ? '#0f172a' : '#ffffff',
        color: isDark ? '#f1f5f9' : '#0f172a',
        iconColor: '#ef4444',
        customClass: {
            popup: 'rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = deleteUrl;
        }
    });
}

function mostrarQR(qrUrl, nombre) {
    const isDark = document.documentElement.classList.contains('dark');
    
    Swal.fire({
        title: '',
        html: `
            <div style="text-align:center;">
                <div style="margin-bottom:12px;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#284b63,#3c6e71);margin-bottom:8px;">
                        <span style="font-size:24px;">📱</span>
                    </span>
                    <h3 style="font-size:18px;font-weight:800;color:${isDark ? '#f1f5f9' : '#0f172a'};margin:4px 0 2px;">Código QR</h3>
                    <p style="font-size:13px;color:${isDark ? '#94a3b8' : '#64748b'};font-weight:600;">${nombre}</p>
                </div>
                <div style="background:white;border-radius:16px;padding:16px;display:inline-block;box-shadow:0 4px 24px rgba(0,0,0,0.08);border:1px solid ${isDark ? '#334155' : '#e2e8f0'};">
                    <img src="${qrUrl}" alt="QR de ${nombre}" style="width:220px;height:220px;display:block;image-rendering:pixelated;" />
                </div>
                <div style="margin-top:16px;display:flex;gap:8px;justify-content:center;">
                    <a href="${qrUrl}" download class="swal2-confirm swal2-styled" style="background:linear-gradient(135deg,#284b63,#3c6e71);border-radius:10px;font-size:13px;padding:8px 20px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                        ⬇️ Descargar
                    </a>
                    <button onclick="window.open('${qrUrl}','_blank')" class="swal2-cancel swal2-styled" style="background:transparent;border:1.5px solid ${isDark ? '#334155' : '#cbd5e1'};color:${isDark ? '#94a3b8' : '#64748b'};border-radius:10px;font-size:13px;padding:8px 20px;font-weight:600;">
                        🔗 Abrir
                    </button>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        background: isDark ? '#0f172a' : '#ffffff',
        color: isDark ? '#f1f5f9' : '#0f172a',
        width: 380,
        customClass: {
            popup: 'rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl',
            closeButton: 'text-slate-400 hover:text-slate-600'
        },
        backdrop: `rgba(0,0,0,${isDark ? '0.7' : '0.4'})`
    });
}
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
