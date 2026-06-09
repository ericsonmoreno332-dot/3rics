<?php

declare(strict_types=1);

$user = require_roles(['admin']);
$pdo = db();

$id = (int) ($_GET['id'] ?? 0);
$row = null;
if ($id > 0) {
    $st = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND rol IN ('admin','supervisor','practicante') LIMIT 1");
    $st->execute([$id]);
    $row = $st->fetch();
    if (!$row) {
        flash('err', 'Usuario no encontrado');
        redirect(app_url('index.php?r=usuarios'));
    }
}

// Load lookup data for the embedded practicante form
$areas        = $pdo->query('SELECT id, nombre FROM areas ORDER BY nombre')->fetchAll();
$insts        = $pdo->query('SELECT id, nombre, tipo FROM instituciones ORDER BY nombre')->fetchAll();
$carreras_list = $pdo->query('SELECT nombre FROM carreras ORDER BY nombre')->fetchAll(PDO::FETCH_COLUMN);

// ── Retrieve old form data (if any validation redirect occurred) ─────────────
// Calling old() the first time loads+clears $_SESSION['_old'].
$old_rol           = old('rol');
$old_estado_usr    = old('estado_usuario');
$old_dni           = old('dni');
$old_nombres_pract = old('nombres_pract');
$old_apellidos     = old('apellidos');
$old_carrera       = old('carrera');
$old_correo        = old('correo');
$old_telefono      = old('telefono');
$old_estado        = old('estado');
$old_fecha_inicio  = old('fecha_inicio');
$old_fecha_fin     = old('fecha_fin');
$old_inst_id       = old('institucion_id');
$old_area_id       = old('area_id');
$old_username      = old('username');
$old_username_admin = old('username_admin');
$old_nombres_admin = old('nombres');
$has_old = ($old_rol !== '');  // flag: true if we came from a validation redirect

// If editing a practicante user, resolve the text names for datalists
$inst_name = '';
$area_name = '';
$pract_row = null;
if ($row && $row['rol'] === 'practicante' && $row['practicante_id']) {
    $pract_row = practicante_por_id($pdo, (int) $row['practicante_id']);
    if ($pract_row) {
        foreach ($insts as $i) {
            if ($pract_row['institucion_id'] == $i['id']) {
                $inst_name = $i['nombre'] . ' (' . $i['tipo'] . ')';
                break;
            }
        }
        foreach ($areas as $a) {
            if ($pract_row['area_id'] == $a['id']) {
                $area_name = $a['nombre'];
                break;
            }
        }
    }
}

// If old data present, override pract_row and names with the old input
if ($has_old) {
    if (!$pract_row) $pract_row = [];
    $pract_row['dni']            = $old_dni ?: ($pract_row['dni'] ?? '');
    $pract_row['nombres']        = $old_nombres_pract ?: ($pract_row['nombres'] ?? '');
    $pract_row['apellidos']      = $old_apellidos ?: ($pract_row['apellidos'] ?? '');
    $pract_row['carrera']        = $old_carrera ?: ($pract_row['carrera'] ?? '');
    $pract_row['correo']         = $old_correo ?: ($pract_row['correo'] ?? '');
    $pract_row['telefono']       = $old_telefono ?: ($pract_row['telefono'] ?? '');
    $pract_row['estado']         = $old_estado ?: ($pract_row['estado'] ?? 'activo');
    $pract_row['fecha_inicio']   = $old_fecha_inicio ?: ($pract_row['fecha_inicio'] ?? '');
    $pract_row['fecha_fin']      = $old_fecha_fin ?: ($pract_row['fecha_fin'] ?? '');
    $pract_row['institucion_id'] = $old_inst_id ?: ($pract_row['institucion_id'] ?? '');
    $pract_row['area_id']        = $old_area_id ?: ($pract_row['area_id'] ?? '');

    // Resolve institution and area names from old IDs
    $inst_name = '';
    $area_name = '';
    foreach ($insts as $i) {
        if ($pract_row['institucion_id'] == $i['id']) {
            $inst_name = $i['nombre'] . ' (' . $i['tipo'] . ')';
            break;
        }
    }
    foreach ($areas as $a) {
        if ($pract_row['area_id'] == $a['id']) {
            $area_name = $a['nombre'];
            break;
        }
    }
}

$title = $row ? 'Editar usuario' : 'Nuevo usuario';
ob_start();
?>
<div class="max-w-4xl mx-auto">
    <div class="ui-panel p-4 sm:p-6 shadow-2xl relative overflow-hidden">
        <!-- Efectos de luz de fondo -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 bg-pisco-sky/10 rounded-full blur-3xl pointer-events-none transition-all duration-700"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-72 h-72 bg-pisco-gold/10 rounded-full blur-3xl pointer-events-none transition-all duration-700"></div>

        <div class="flex items-center gap-4 mb-8 relative z-10 border-b border-slate-100 dark:border-slate-800 pb-5">
            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-[#353535] to-[#284b63] flex items-center justify-center text-white text-2xl shadow-[0_4px_20px_rgba(70,129,137,0.3)] border border-white/10 transform transition hover:scale-105">
                <?= $row ? '✏️' : '✨' ?>
            </div>
            <div>
                <h2 class="text-2xl font-display font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                    <?= $row ? 'Editar cuenta de usuario' : 'Crear nueva cuenta' ?>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    <?= $row ? 'Actualiza los permisos y datos de acceso.' : 'Configura el rol y las credenciales para el nuevo integrante.' ?>
                </p>
            </div>
        </div>

        <form method="post" action="<?= e(app_url('index.php?r=usuario_save')) ?>" class="space-y-6 relative z-10">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $row ? (int) $row['id'] : 0 ?>">

            <div class="grid sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                <div>
                    <label class="ui-label text-pisco-sky dark:text-pisco-skylt flex items-center gap-2">
                        <span>🛡️</span> Rol del sistema
                    </label>
                    <?php $current_rol = $has_old ? $old_rol : ($row['rol'] ?? 'supervisor'); ?>
                    <select name="rol" id="rolSelect" class="ui-field mt-1 cursor-pointer font-medium shadow-sm transition hover:border-pisco-sky">
                        <?php foreach (['supervisor', 'admin', 'practicante'] as $rol): ?>
                            <option value="<?= $rol ?>" <?= ($current_rol === $rol) ? 'selected' : '' ?>><?= e(ucfirst($rol)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="ui-label text-pisco-sky dark:text-pisco-skylt flex items-center gap-2">
                        <span>🟢</span> Estado de la cuenta
                    </label>
                    <?php $current_estado_usr = $has_old ? $old_estado_usr : ($row['estado'] ?? 'activo'); ?>
                    <select name="estado_usuario" class="ui-field mt-1 cursor-pointer font-medium shadow-sm transition hover:border-pisco-sky">
                        <option value="activo" <?= ($current_estado_usr === 'activo' ? 'selected' : '') ?>>Activo</option>
                        <option value="inactivo" <?= ($current_estado_usr === 'inactivo' ? 'selected' : '') ?>>Inactivo</option>
                    </select>
                </div>
            </div>

    <!-- ══ BLOQUE PRACTICANTE (solo visible cuando rol = practicante) ══ -->
    <div id="practicanteWrap" style="display:none;">
        <div class="rounded-xl border border-pisco-sky/30 bg-pisco-sky/5 p-5 space-y-4">
            <p class="text-sm font-semibold text-pisco-sky">Datos del practicante</p>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">DNI <span class="ui-required">*</span></label>
                    <div class="flex gap-2">
                        <input name="dni" id="inputDni" maxlength="8" pattern="\d{8}"
                               value="<?= e($pract_row['dni'] ?? '') ?>"
                               class="ui-field" placeholder="12345678"
                               <?= (!$row || $row['rol'] === 'practicante') ? '' : '' ?>>
                        <button type="button" id="btnConsultarDni" class="ui-btn-primary-soft shrink-0 px-3" title="Consultar RENIEC">
                            <span id="dniIcon">🔍</span>
                            <span id="dniLoading" class="hidden">⌛</span>
                        </button>
                    </div>
                    <p id="dniStatus" class="text-xs mt-1 font-medium"></p>
                </div>
                <div>
                    <label class="ui-label">Estado</label>
                    <select name="estado" class="ui-field">
                        <?php foreach (['activo', 'finalizado', 'suspendido'] as $e_opt): ?>
                            <option value="<?= $e_opt ?>" <?= (($pract_row['estado'] ?? 'activo') === $e_opt) ? 'selected' : '' ?>><?= e(ucfirst($e_opt)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="ui-label">Nombres <span class="ui-required">*</span></label>
                    <input name="nombres_pract" id="inputNombresPract" value="<?= e($pract_row['nombres'] ?? '') ?>" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">Apellidos <span class="ui-required">*</span></label>
                    <input name="apellidos" id="inputApellidos" value="<?= e($pract_row['apellidos'] ?? '') ?>" class="ui-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="ui-label">Carrera profesional <span class="ui-required">*</span></label>
                    <input name="carrera" list="carrerasList" value="<?= e($pract_row['carrera'] ?? '') ?>" class="ui-field" placeholder="Escriba para buscar..." autocomplete="off">
                    <datalist id="carrerasList">
                        <?php foreach ($carreras_list as $c): ?>
                            <option value="<?= e($c) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="ui-label">Institución</label>
                    <input type="hidden" name="institucion_id" id="hidden_institucion_id" value="<?= e((string) ($pract_row['institucion_id'] ?? '')) ?>">
                    <input type="text" id="search_institucion" list="list_instituciones" value="<?= e($inst_name) ?>" class="ui-field" placeholder="Escriba para buscar..." autocomplete="off">
                    <datalist id="list_instituciones">
                        <?php foreach ($insts as $i): ?>
                            <option data-id="<?= (int) $i['id'] ?>" value="<?= e($i['nombre']) ?> (<?= e($i['tipo']) ?>)"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="ui-label">Área asignada</label>
                    <input type="hidden" name="area_id" id="hidden_area_id" value="<?= e((string) ($pract_row['area_id'] ?? '')) ?>">
                    <input type="text" id="search_area" list="list_areas" value="<?= e($area_name) ?>" class="ui-field" placeholder="Escriba para buscar..." autocomplete="off">
                    <datalist id="list_areas">
                        <?php foreach ($areas as $a): ?>
                            <option data-id="<?= (int) $a['id'] ?>" value="<?= e($a['nombre']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="ui-label">Fecha inicio prácticas</label>
                    <input type="date" name="fecha_inicio" value="<?= e($pract_row['fecha_inicio'] ?? '') ?>" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">Fecha fin prácticas</label>
                    <input type="date" name="fecha_fin" value="<?= e($pract_row['fecha_fin'] ?? '') ?>" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">Correo</label>
                    <input type="email" name="correo" value="<?= e($pract_row['correo'] ?? '') ?>" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">Teléfono</label>
                    <input name="telefono" value="<?= e($pract_row['telefono'] ?? '') ?>" class="ui-field">
                </div>
            </div>
        </div>

        <!-- Nombre de usuario para practicante -->
        <div class="mt-4">
            <label class="ui-label">Nombre de la cuenta (usuario) <span class="ui-required">*</span></label>
            <input name="username" id="inputUsername" value="<?= e($has_old ? $old_username : ($row['username'] ?? '')) ?>" class="ui-field-mono" autocomplete="off" placeholder="ej: 12345678">
        </div>
        <div class="mt-4">
            <label class="ui-label">Contraseña <?= $row ? '<span class="ui-hint">(vacío = no cambiar)</span>' : '<span class="ui-required">*</span>' ?></label>
            <div class="relative">
                <input type="password" name="password" id="inputPassword" class="ui-field pr-12" autocomplete="new-password" placeholder="••••••••">
                <button type="button" onclick="togglePass('inputPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-pisco-sky transition-colors p-1" title="Mostrar/ocultar contraseña">
                    <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <svg class="eye-closed w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
            <p class="text-xs mt-1.5 text-slate-400 dark:text-slate-500 flex items-center gap-1.5">🔐 Mínimo 8 caracteres, debe incluir letras y números</p>
        </div>
        <div class="mt-4" id="confirmWrap">
            <label class="ui-label">Confirmar contraseña <?= $row ? '<span class="ui-hint">(si cambias la contraseña)</span>' : '<span class="ui-required">*</span>' ?></label>
            <div class="relative">
                <input type="password" name="password_confirm" id="inputPasswordConfirm" class="ui-field pr-12" autocomplete="new-password" placeholder="••••••••">
                <button type="button" onclick="togglePass('inputPasswordConfirm', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-pisco-sky transition-colors p-1" title="Mostrar/ocultar contraseña">
                    <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <svg class="eye-closed w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
            <p id="passMsg" class="text-xs mt-1 font-medium text-red-500 hidden">Las contraseñas no coinciden</p>
        </div>
        <!-- hidden practicante_id (for editing) -->
        <input type="hidden" name="practicante_id" id="practicanteId" value="<?= e((string) ($row['practicante_id'] ?? '')) ?>">
    </div>

    <!-- ══ BLOQUE ADMIN / SUPERVISOR ══ -->
    <div id="adminWrap" class="mt-4">
        <div class="grid sm:grid-cols-2 gap-5 p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm transition-all hover:shadow-md">
            <div class="sm:col-span-2">
                <label class="ui-label flex items-center gap-2"><span>👤</span> Nombres completos</label>
                <input name="nombres" id="inputNombresAdmin" value="<?= e($has_old ? $old_nombres_admin : (($row && $row['rol'] !== 'practicante') ? $row['nombres'] : '')) ?>" class="ui-field shadow-sm" placeholder="Ej. Juan Pérez">
            </div>
            <div>
                <label class="ui-label flex items-center gap-2"><span>🔑</span> Usuario</label>
                <input name="username_admin" id="inputUsernameAdmin" value="<?= e($has_old ? $old_username_admin : (($row && $row['rol'] !== 'practicante') ? $row['username'] : '')) ?>" class="ui-field-mono shadow-sm" placeholder="usuario_admin">
            </div>
            <div>
                <label class="ui-label flex items-center gap-2"><span>🔒</span> Contraseña <?= $row ? '<span class="ui-hint">(vacío = no cambiar)</span>' : '' ?></label>
                <div class="relative">
                    <input type="password" name="password_admin" id="inputPasswordAdmin" class="ui-field shadow-sm pr-12" autocomplete="new-password" placeholder="••••••••">
                    <button type="button" onclick="togglePass('inputPasswordAdmin', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-pisco-sky transition-colors p-1" title="Mostrar/ocultar contraseña">
                        <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        <svg class="eye-closed w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
                <p class="text-xs mt-1.5 text-slate-400 dark:text-slate-500 flex items-center gap-1.5">🔐 Mínimo 8 caracteres, debe incluir letras y números</p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 pt-6 mt-6 border-t border-slate-200 dark:border-slate-700 relative z-10">
        <button type="submit" id="btnGuardar" class="ui-btn-primary-wide shadow-lg hover:shadow-pisco-sky/30">
            <span>💾</span> Guardar Cambios
        </button>
        <a href="<?= e(app_url('index.php?r=usuarios')) ?>" class="ui-btn-outline-wide">
            Cancelar
        </a>
    </div>
        </form>
    </div>
</div>

<script>
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    
    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');
    
    if (isPassword) {
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const rolSelect        = document.getElementById('rolSelect');
    const practicanteWrap  = document.getElementById('practicanteWrap');
    const adminWrap        = document.getElementById('adminWrap');
    const inputDni         = document.getElementById('inputDni');
    const btnConsultarDni  = document.getElementById('btnConsultarDni');
    const dniIcon          = document.getElementById('dniIcon');
    const dniLoading       = document.getElementById('dniLoading');
    const dniStatus        = document.getElementById('dniStatus');
    const inputNombresPract= document.getElementById('inputNombresPract');
    const inputApellidos   = document.getElementById('inputApellidos');
    const inputUsername    = document.getElementById('inputUsername');
    const inputPassword    = document.getElementById('inputPassword');
    const inputPasswordConfirm = document.getElementById('inputPasswordConfirm');
    const passMsg          = document.getElementById('passMsg');

    function toggleRol() {
        if (rolSelect.value === 'practicante') {
            practicanteWrap.style.display = 'block';
            adminWrap.style.display       = 'none';
        } else {
            practicanteWrap.style.display = 'none';
            adminWrap.style.display       = 'block';
        }
    }
    rolSelect.addEventListener('change', toggleRol);
    toggleRol();

    // ── RENIEC DNI lookup ──────────────────────────────────────────
    btnConsultarDni.addEventListener('click', async function() {
        const dni = inputDni.value.trim();
        if (dni.length !== 8 || !/^\d{8}$/.test(dni)) {
            dniStatus.textContent = 'Ingrese 8 dígitos numéricos';
            dniStatus.className = 'text-xs mt-1 font-medium text-red-500';
            return;
        }
        btnConsultarDni.disabled = true;
        dniIcon.classList.add('hidden');
        dniLoading.classList.remove('hidden');
        dniStatus.textContent = '';

        try {
            const url = '<?= app_url("index.php?r=api_dni&dni=") ?>' + dni;
            const resp = await fetch(url);
            const res  = await resp.json();
            if (res.ok) {
                inputNombresPract.value = res.data.nombres.trim();
                inputApellidos.value    = (res.data.apellido_paterno + ' ' + res.data.apellido_materno).trim();
                if (!inputUsername.value) inputUsername.value = dni;
                dniStatus.textContent   = '✅ Datos cargados de RENIEC';
                dniStatus.className     = 'text-xs mt-1 font-medium text-pisco-sky';
            } else {
                dniStatus.textContent = '⚠️ No encontrado en RENIEC. Complete los datos manualmente.';
                dniStatus.className   = 'text-xs mt-1 font-medium text-amber-600';
                if (!inputUsername.value) inputUsername.value = dni;
            }
        } catch(err) {
            dniStatus.textContent = '⚠️ Error de conexión. Complete los datos manualmente.';
            dniStatus.className   = 'text-xs mt-1 font-medium text-amber-600';
        } finally {
            btnConsultarDni.disabled = false;
            dniIcon.classList.remove('hidden');
            dniLoading.classList.add('hidden');
        }
    });

    inputDni.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); btnConsultarDni.click(); }
    });

    // ── Confirm password validation ────────────────────────────────
    function checkPasswords() {
        if (!inputPassword || !inputPasswordConfirm) return;
        const p  = inputPassword.value;
        const pc = inputPasswordConfirm.value;
        if (pc && p !== pc) {
            passMsg.classList.remove('hidden');
        } else {
            passMsg.classList.add('hidden');
        }
    }
    if (inputPassword) inputPassword.addEventListener('input', checkPasswords);
    if (inputPasswordConfirm) inputPasswordConfirm.addEventListener('input', checkPasswords);

    // ── Datalist sync (Institución & Área) ────────────────────────
    function setupDatalist(inputId, hiddenId, listId) {
        const input  = document.getElementById(inputId);
        const hidden = document.getElementById(hiddenId);
        const list   = document.getElementById(listId);
        if (!input || !hidden || !list) return;
        input.addEventListener('input', function() {
            let selectedId = '';
            for (let option of list.options) {
                if (option.value === input.value) {
                    selectedId = option.getAttribute('data-id');
                    break;
                }
            }
            hidden.value = selectedId;
        });
        input.addEventListener('blur', function() {
            if (hidden.value === '') input.value = '';
        });
    }
    setupDatalist('search_institucion', 'hidden_institucion_id', 'list_instituciones');
    setupDatalist('search_area',        'hidden_area_id',        'list_areas');

    // ── Block submit if passwords don't match ─────────────────────
    document.querySelector('form').addEventListener('submit', function(e) {
        if (rolSelect.value === 'practicante' && inputPassword && inputPasswordConfirm) {
            const p  = inputPassword.value;
            const pc = inputPasswordConfirm.value;
            if (p && p !== pc) {
                e.preventDefault();
                passMsg.classList.remove('hidden');
                inputPasswordConfirm.focus();
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
