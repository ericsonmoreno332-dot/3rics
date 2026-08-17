<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo  = db();

// Load all active practicantes for the search dropdown
$practicantes = $pdo->query(
    "SELECT p.id, p.dni, p.nombres, p.apellidos, p.estado,
            a.nombre AS area_nombre
     FROM practicantes p
     LEFT JOIN areas a ON a.id = p.area_id
     ORDER BY p.apellidos, p.nombres"
)->fetchAll();

$title = 'Registro Manual de Asistencia';
ob_start();
?>

<div class="max-w-2xl space-y-6">

    <!-- Header -->
    <div class="rounded-2xl overflow-hidden shadow-sky">
        <div class="px-6 py-5" style="background: linear-gradient(135deg, #26263A 0%, #26263A 100%);">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                ✏️ Registro Manual de Asistencia
            </h2>
            <p class="text-sm text-white/70 mt-1">
                Use este módulo cuando el escáner QR no esté disponible.
            </p>
        </div>
    </div>

    <!-- Buscar practicante -->
    <div class="ui-panel p-6 space-y-4">
        <h3 class="font-bold text-slate-700 dark:text-stone-200">Buscar practicante</h3>
        <div class="flex gap-2">
            <input type="text" id="buscarPract" placeholder="Escriba DNI o nombre…"
                   class="ui-field" autocomplete="off">
        </div>

        <!-- Resultados de búsqueda -->
        <div id="listaPract" class="space-y-2 max-h-64 overflow-y-auto">
            <?php foreach ($practicantes as $pr): ?>
            <button type="button"
                    class="pract-item w-full text-left px-4 py-3 rounded-xl border border-slate-100 dark:border-stone-700 hover:border-pisco-sky hover:bg-pisco-sky/5 transition-all flex items-center gap-3"
                    data-id="<?= (int)$pr['id'] ?>"
                    data-nombre="<?= e($pr['nombres'] . ' ' . $pr['apellidos']) ?>"
                    data-dni="<?= e($pr['dni']) ?>"
                    data-area="<?= e($pr['area_nombre'] ?? '') ?>"
                    data-estado="<?= e($pr['estado']) ?>">
                <span class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white"
                      style="background: linear-gradient(135deg,#26263A,#7A7AA3)">
                    <?= mb_strtoupper(mb_substr($pr['nombres'], 0, 1)) ?>
                </span>
                <div class="min-w-0">
                    <p class="font-semibold text-sm text-slate-700 dark:text-stone-200 truncate">
                        <?= e($pr['nombres'] . ' ' . $pr['apellidos']) ?>
                    </p>
                    <p class="text-xs text-slate-400 font-mono"><?= e($pr['dni']) ?>
                        <?php if ($pr['area_nombre']): ?>· <?= e($pr['area_nombre']) ?><?php endif; ?>
                    </p>
                </div>
                <span class="ml-auto shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full
                    <?= $pr['estado'] === 'activo' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>">
                    <?= e(ucfirst($pr['estado'])) ?>
                </span>
            </button>
            <?php endforeach; ?>
            <?php if (!$practicantes): ?>
                <p class="text-slate-400 text-sm text-center py-4">No hay practicantes registrados.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulario de registro (aparece al seleccionar) -->
    <div id="formRegistro" class="ui-panel overflow-hidden hidden">
        <div id="formHeader" class="px-6 py-4 flex items-center gap-3"
             style="background: linear-gradient(90deg,#26263A,#26263A);">
            <span class="w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold text-white shrink-0"
                  style="background:rgba(255,255,255,0.15)" id="fAvatar"></span>
            <div>
                <p class="font-bold text-white" id="fNombre"></p>
                <p class="text-xs font-mono text-white/60" id="fDni"></p>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="fPractId">

            <!-- Estado actual hoy -->
            <div id="estadoHoy" class="rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2"
                 style="background:#f8fafc; border:1px solid #e2e8f0;">
                <span id="estadoHoyIcon">⌛</span>
                <span id="estadoHoyText">Consultando estado…</span>
            </div>

            <!-- Formularios -->
            <form method="post" action="<?= e(app_url('index.php?r=asistencia_entrada')) ?>" id="formEntrada" class="space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="practicante_id" id="fPractIdEntrada">
                <input type="hidden" name="metodo" value="manual">
                <textarea name="observacion" placeholder="Observación (opcional)" maxlength="250" class="ui-field text-sm" rows="2"></textarea>
                <button type="submit" class="ui-btn-primary w-full justify-center">
                    🕐 Registrar Entrada
                </button>
            </form>

            <form method="post" action="<?= e(app_url('index.php?r=asistencia_salida')) ?>" id="formSalida" class="space-y-3 hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="practicante_id" id="fPractIdSalida">
                <input type="hidden" name="metodo" value="manual">
                <textarea name="observacion" placeholder="Observación (opcional)" maxlength="250" class="ui-field text-sm" rows="2"></textarea>
                <button type="submit" class="ui-btn-primary w-full justify-center">
                    🕐 Registrar Salida
                </button>
            </form>
        </div>
    </div>

</div>

<script>
// Filter search
document.getElementById('buscarPract').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.pract-item').forEach(function(btn) {
        const texto = (btn.dataset.nombre + ' ' + btn.dataset.dni).toLowerCase();
        btn.style.display = (!q || texto.includes(q)) ? 'flex' : 'none';
    });
});

// Select practicante
document.querySelectorAll('.pract-item').forEach(function(btn) {
    btn.addEventListener('click', async function() {
        const id     = this.dataset.id;
        const nombre = this.dataset.nombre;
        const dni    = this.dataset.dni;
        const estado = this.dataset.estado;

        // Highlight selected
        document.querySelectorAll('.pract-item').forEach(b => b.classList.remove('border-pisco-sky','bg-pisco-sky/10'));
        this.classList.add('border-pisco-sky', 'bg-pisco-sky/10');

        // Fill header
        document.getElementById('fAvatar').textContent  = nombre.charAt(0).toUpperCase();
        document.getElementById('fNombre').textContent  = nombre;
        document.getElementById('fDni').textContent     = 'DNI ' + dni;
        document.getElementById('fPractId').value       = id;
        document.getElementById('fPractIdEntrada').value = id;
        document.getElementById('fPractIdSalida').value  = id;

        // Show form
        document.getElementById('formRegistro').classList.remove('hidden');

        if (estado !== 'activo') {
            document.getElementById('estadoHoy').style.background = '#fef9e7';
            document.getElementById('estadoHoy').style.border     = '1px solid #fbbf24';
            document.getElementById('estadoHoyIcon').textContent  = '⚠️';
            document.getElementById('estadoHoyText').textContent  = 'Este practicante no está en estado Activo.';
            document.getElementById('formEntrada').classList.add('hidden');
            document.getElementById('formSalida').classList.add('hidden');
            return;
        }

        // Check today's attendance via API
        document.getElementById('estadoHoyIcon').textContent = '⌛';
        document.getElementById('estadoHoyText').textContent = 'Consultando estado de hoy…';
        document.getElementById('formEntrada').classList.add('hidden');
        document.getElementById('formSalida').classList.add('hidden');

        try {
            const resp = await fetch('<?= app_url("index.php?r=api_estado_hoy&id=") ?>' + id);
            const data = await resp.json();

            if (data.cerrada) {
                document.getElementById('estadoHoy').style.background = '#ecfdf5';
                document.getElementById('estadoHoy').style.border     = '1px solid #6ee7b7';
                document.getElementById('estadoHoyIcon').textContent  = '✅';
                document.getElementById('estadoHoyText').textContent  = 'Ya completó entrada y salida hoy.';
            } else if (data.abierta) {
                document.getElementById('estadoHoy').style.background = '#fffbeb';
                document.getElementById('estadoHoy').style.border     = '1px solid #fcd34d';
                document.getElementById('estadoHoyIcon').textContent  = '🟡';
                document.getElementById('estadoHoyText').textContent  = 'Tiene entrada a las ' + data.hora_entrada + '. Pendiente salida.';
                document.getElementById('formSalida').classList.remove('hidden');
            } else {
                document.getElementById('estadoHoy').style.background = '#f0f9ff';
                document.getElementById('estadoHoy').style.border     = '1px solid #bae6fd';
                document.getElementById('estadoHoyIcon').textContent  = '📋';
                document.getElementById('estadoHoyText').textContent  = 'Sin registros hoy. Puede registrar la entrada.';
                document.getElementById('formEntrada').classList.remove('hidden');
            }
        } catch(e) {
            document.getElementById('estadoHoyIcon').textContent = '⚠️';
            document.getElementById('estadoHoyText').textContent = 'No se pudo consultar. Use los formularios disponibles.';
            document.getElementById('formEntrada').classList.remove('hidden');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
