<?php

declare(strict_types=1);

$user = require_roles(['admin']);
$pdo = db();

$q = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT u.id, u.username, u.nombres, u.rol, u.estado FROM usuarios u';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE (u.username LIKE ? OR u.nombres LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like];
}
$sql .= ' ORDER BY u.nombres, u.username LIMIT 500';
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$admins = [];
$practicantes = [];
foreach ($rows as $r) {
    if (in_array($r['rol'], ['admin', 'supervisor'], true)) {
        $admins[] = $r;
    } else {
        $practicantes[] = $r;
    }
}

$title = 'Usuarios del sistema';
ob_start();
?>

<!-- ═══ HERO HEADER ═══════════════════════════════════════════ -->
<div class="mb-6 ui-animate-entry">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-display text-transparent bg-clip-text bg-gradient-to-r from-[#284b63] to-[#3c6e71]">
                🔐 Usuarios
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Gestiona los accesos administrativos y cuentas de practicantes.</p>
        </div>
        <a href="<?= e(app_url('index.php?r=usuario_form')) ?>" class="ui-btn-primary gap-2 shrink-0 shadow-lg shadow-[#284b63]/20 hover:shadow-[#284b63]/40">
            <span class="text-lg leading-none">＋</span> Nuevo Usuario
        </a>
    </div>
</div>

<!-- ═══ SEARCH BAR ════════════════════════════════════════════ -->
<div class="mb-8 ui-animate-entry delay-100">
    <form method="get" class="relative flex gap-3">
        <input type="hidden" name="r" value="usuarios">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">🔍</span>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por usuario o nombres…"
                   class="w-full pl-9 pr-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#284b63]/30 focus:border-[#284b63] shadow-sm transition-all">
        </div>
        <button type="submit" class="ui-btn-primary px-6">Buscar</button>
    </form>
    <?php if ($q !== ''): ?>
        <div class="mt-2 flex items-center gap-2">
            <span class="text-xs text-slate-500 dark:text-slate-400">Mostrando resultados para "<strong class="text-[#284b63] dark:text-[#3c6e71]"><?= e($q) ?></strong>"</span>
            <a href="<?= e(app_url('index.php?r=usuarios')) ?>" class="text-xs text-red-400 hover:text-red-300 font-medium transition-colors">✕ Limpiar</a>
        </div>
    <?php endif; ?>
</div>

<!-- ═══ TWO COLUMNS GRIDS ══════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 xl:gap-10 ui-animate-entry delay-200">
    
    <!-- Columna 1: Admin & Supervisores -->
    <div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            🛡️ Administradores & Supervisores
            <span class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-xs px-2.5 py-0.5 rounded-full"><?= count($admins) ?></span>
        </h2>
        <div class="space-y-3">
            <?php foreach ($admins as $r): ?>
                <?php
                    $bgGradient = $r['rol'] === 'admin' 
                        ? 'from-indigo-500 to-purple-600' 
                        : 'from-blue-500 to-cyan-500';
                    $initial = mb_strtoupper(mb_substr($r['nombres'], 0, 1));
                ?>
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm flex items-center gap-4 transition-all hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-700">
                    <div class="h-11 w-11 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0 bg-gradient-to-br <?= $bgGradient ?> shadow-sm">
                        <?= $initial ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm text-slate-800 dark:text-slate-200 truncate"><?= e($r['nombres']) ?></p>
                        <p class="text-xs font-mono text-slate-400 mt-0.5">@<?= e($r['username']) ?></p>
                    </div>
                    <div class="flex flex-col items-end gap-2 shrink-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400"><?= e($r['rol']) ?></span>
                            <?php if (($r['estado'] ?? 'activo') === 'activo'): ?>
                                <span class="ui-badge-ok text-[9px] py-0.5 px-2 font-bold uppercase tracking-wider">Activo</span>
                            <?php else: ?>
                                <span class="ui-badge-err text-[9px] py-0.5 px-2 font-bold uppercase tracking-wider">Inactivo</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex gap-1.5">
                            <?php if ((int) $r['id'] !== (int) $user['id']): ?>
                                <a href="<?= e(app_url('index.php?r=usuario_toggle&id=' . (int) $r['id'] . '&_csrf=' . urlencode(csrf_token()))) ?>" 
                                   class="text-slate-400 <?= ($r['estado'] ?? 'activo') === 'activo' ? 'hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10' : 'hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' ?> bg-slate-50 dark:bg-slate-800 h-7 w-7 flex items-center justify-center rounded-md transition-colors" 
                                   title="<?= ($r['estado'] ?? 'activo') === 'activo' ? 'Desactivar usuario' : 'Activar usuario' ?>">
                                    <?= ($r['estado'] ?? 'activo') === 'activo' ? '🚫' : '✅' ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?= e(app_url('index.php?r=usuario_form&id=' . (int) $r['id'])) ?>" class="text-slate-400 hover:text-indigo-500 bg-slate-50 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-indigo-500/10 h-7 w-7 flex items-center justify-center rounded-md transition-colors" title="Editar">✏️</a>
                            <?php if ((int) $r['id'] !== (int) $user['id']): ?>
                                <button type="button" class="text-slate-400 hover:text-red-500 bg-slate-50 hover:bg-red-50 dark:bg-slate-800 dark:hover:bg-red-500/10 h-7 w-7 flex items-center justify-center rounded-md transition-colors border-none cursor-pointer" onclick="confirmarEliminarUsuario(event, '<?= e($r['username']) ?>', '<?= e(app_url('index.php?r=usuario_delete&id=' . (int) $r['id'] . '&_csrf=' . urlencode(csrf_token()))) ?>')" title="Eliminar">🗑️</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!count($admins)): ?>
                <div class="text-center p-6 text-slate-400 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl text-sm">No hay resultados</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna 2: Practicantes -->
    <div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            🎓 Practicantes
            <span class="bg-[#284b63]/10 text-[#284b63] dark:bg-[#3c6e71]/20 dark:text-[#d9d9d9] text-xs px-2.5 py-0.5 rounded-full"><?= count($practicantes) ?></span>
        </h2>
        <div class="space-y-3">
            <?php foreach ($practicantes as $r): ?>
                <?php
                    $initial = mb_strtoupper(mb_substr($r['nombres'], 0, 1));
                ?>
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm flex items-center gap-4 transition-all hover:shadow-md hover:border-[#3c6e71]/50">
                    <div class="h-11 w-11 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0 bg-gradient-to-br from-[#284b63] to-[#3c6e71] shadow-sm">
                        <?= $initial ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm text-slate-800 dark:text-slate-200 truncate"><?= e($r['nombres']) ?></p>
                        <p class="text-xs font-mono text-slate-400 mt-0.5">@<?= e($r['username']) ?></p>
                    </div>
                    <div class="flex flex-col items-end gap-2 shrink-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400"><?= e($r['rol']) ?></span>
                            <?php if (($r['estado'] ?? 'activo') === 'activo'): ?>
                                <span class="ui-badge-ok text-[9px] py-0.5 px-2 font-bold uppercase tracking-wider">Activo</span>
                            <?php elseif (($r['estado'] ?? '') === 'finalizado'): ?>
                                <span class="bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800 rounded-full text-[9px] py-0.5 px-2 font-bold uppercase tracking-wider">Finalizado</span>
                            <?php else: ?>
                                <span class="ui-badge-err text-[9px] py-0.5 px-2 font-bold uppercase tracking-wider"><?= e(ucfirst($r['estado'] ?? 'inactivo')) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex gap-1.5">
                            <?php if ((int) $r['id'] !== (int) $user['id']): ?>
                                <a href="<?= e(app_url('index.php?r=usuario_toggle&id=' . (int) $r['id'] . '&_csrf=' . urlencode(csrf_token()))) ?>" 
                                   class="text-slate-400 <?= ($r['estado'] ?? 'activo') === 'activo' ? 'hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10' : 'hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' ?> bg-slate-50 dark:bg-slate-800 h-7 w-7 flex items-center justify-center rounded-md transition-colors" 
                                   title="<?= ($r['estado'] ?? 'activo') === 'activo' ? 'Desactivar usuario' : 'Activar usuario' ?>">
                                    <?= ($r['estado'] ?? 'activo') === 'activo' ? '🚫' : '✅' ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?= e(app_url('index.php?r=usuario_form&id=' . (int) $r['id'])) ?>" class="text-slate-400 hover:text-[#284b63] bg-slate-50 hover:bg-[#284b63]/10 dark:bg-slate-800 dark:hover:bg-[#284b63]/20 h-7 w-7 flex items-center justify-center rounded-md transition-colors" title="Editar">✏️</a>
                            <?php if ((int) $r['id'] !== (int) $user['id']): ?>
                                <button type="button" class="text-slate-400 hover:text-red-500 bg-slate-50 hover:bg-red-50 dark:bg-slate-800 dark:hover:bg-red-500/10 h-7 w-7 flex items-center justify-center rounded-md transition-colors border-none cursor-pointer" onclick="confirmarEliminarUsuario(event, '<?= e($r['username']) ?>', '<?= e(app_url('index.php?r=usuario_delete&id=' . (int) $r['id'] . '&_csrf=' . urlencode(csrf_token()))) ?>')" title="Eliminar">🗑️</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!count($practicantes)): ?>
                <div class="text-center p-6 text-slate-400 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl text-sm">No hay resultados</div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function confirmarEliminarUsuario(event, username, deleteUrl) {
    event.preventDefault();
    const isDark = document.documentElement.classList.contains('dark');
    
    Swal.fire({
        title: '¿Eliminar usuario?',
        html: `Estás a punto de eliminar al usuario <strong class="text-[#284b63] dark:text-[#3c6e71]">@${username}</strong>.<br><br><span class="text-xs text-red-500 font-medium">Esta acción no se puede deshacer. Si es un practicante, también se borrarán sus datos y asistencias.</span>`,
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
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
