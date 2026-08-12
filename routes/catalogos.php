<?php

declare(strict_types=1);

$user = require_roles(['admin']);
$pdo = db();

if (is_post()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'add_area') {
        $n = trim((string) ($_POST['nombre_area'] ?? ''));
        if ($n !== '') {
            $pdo->prepare('INSERT INTO areas (nombre, estado) VALUES (?, 1)')->execute([$n]);
            flash('ok', 'Área creada');
        }
    } elseif ($action === 'del_area') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM areas WHERE id = ?')->execute([$id]);
            flash('ok', 'Área eliminada');
        }
    } elseif ($action === 'add_inst') {
        $n = trim((string) ($_POST['nombre_inst'] ?? ''));
        $tipo = (string) ($_POST['tipo_inst'] ?? 'universidad');
        if (!in_array($tipo, ['universidad', 'instituto'], true)) {
            $tipo = 'universidad';
        }
        if ($n !== '') {
            $pdo->prepare('INSERT INTO instituciones (nombre, tipo) VALUES (?,?)')->execute([$n, $tipo]);
            flash('ok', 'Institución creada');
        }
    } elseif ($action === 'del_inst') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM instituciones WHERE id = ?')->execute([$id]);
            flash('ok', 'Institución eliminada');
        }
    }
    redirect(app_url('index.php?r=catalogos'));
}

$areas = $pdo->query('SELECT id, nombre, encargado, cargo FROM areas ORDER BY nombre')->fetchAll();
$insts = $pdo->query('SELECT id, nombre, tipo FROM instituciones ORDER BY nombre')->fetchAll();

$title = 'Áreas e instituciones';
ob_start();
?>
<div class="ui-grid-asist">
    <div class="ui-panel-p6">
        <h2 class="ui-section-title-mb">Áreas</h2>
        <form method="post" class="flex gap-2 mb-6">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_area">
            <input name="nombre_area" placeholder="Nueva área" maxlength="80" oninput="this.value = this.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ .,-]/g, '')" class="ui-field-grow" required>
            <button type="submit" class="ui-btn-primary-soft">Añadir</button>
        </form>
        <ul class="ui-divider-list">
            <?php foreach ($areas as $a): ?>
                <li class="flex justify-between items-center py-2.5">
                    <div>
                        <span class="font-medium text-slate-800 dark:text-stone-100"><?= e($a['nombre']) ?></span>
                        <?php if (!empty($a['encargado'])): ?>
                            <p class="text-xs text-slate-500 dark:text-stone-400 mt-0.5">
                                💼 Jefe: <?= e($a['encargado']) ?><?= !empty($a['cargo']) ? ' — ' . e($a['cargo']) : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <form method="post" onsubmit="return confirm('¿Eliminar área?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="del_area">
                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                        <button type="submit" class="ui-btn-ghost-danger">Eliminar</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="ui-panel-p6">
        <h2 class="ui-section-title-mb">Instituciones</h2>
        <form method="post" class="space-y-2 mb-6">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_inst">
            <input name="nombre_inst" placeholder="Nombre" maxlength="80" oninput="this.value = this.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ .,-]/g, '')" class="ui-field" required>
            <select name="tipo_inst" class="ui-field">
                <option value="universidad">Universidad</option>
                <option value="instituto">Instituto</option>
            </select>
            <button type="submit" class="ui-btn-primary-soft">Añadir</button>
        </form>
        <ul class="ui-divider-list">
            <?php foreach ($insts as $i): ?>
                <li class="flex justify-between items-center py-2">
                    <span><?= e($i['nombre']) ?> <span class="ui-type-muted">(<?= e($i['tipo']) ?>)</span></span>
                    <form method="post" onsubmit="return confirm('¿Eliminar institución?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="del_inst">
                        <input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
                        <button type="submit" class="ui-btn-ghost-danger">Eliminar</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
