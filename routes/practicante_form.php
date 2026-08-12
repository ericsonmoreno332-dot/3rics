<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$pdo = db();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$row = null;
if ($id > 0) {
    $row = practicante_por_id($pdo, $id);
    if (!$row) {
        flash('err', 'Practicante no encontrado');
        redirect(app_url('index.php?r=practicantes'));
    }
}

$areas = $pdo->query('SELECT id, nombre FROM areas ORDER BY nombre')->fetchAll();
$insts = $pdo->query('SELECT id, nombre, tipo FROM instituciones ORDER BY nombre')->fetchAll();
$carreras_list = $pdo->query('SELECT nombre FROM carreras ORDER BY nombre')->fetchAll(PDO::FETCH_COLUMN);

$inst_name = '';
$area_name = '';
if ($row) {
    foreach ($insts as $i) {
        if ($row['institucion_id'] == $i['id']) {
            $inst_name = $i['nombre'] . ' (' . $i['tipo'] . ')';
            break;
        }
    }
    foreach ($areas as $a) {
        if ($row['area_id'] == $a['id']) {
            $area_name = $a['nombre'];
            break;
        }
    }
}

$title = $row ? 'Editar practicante' : 'Nuevo practicante';
ob_start();
?>
<div class="grid md:grid-cols-3 gap-6">
    <!-- Columna Izquierda: Vista Previa del Perfil -->
    <div class="md:col-span-1 space-y-6">
        <div class="ui-panel p-6 flex flex-col items-center text-center shadow-md relative overflow-hidden border border-slate-200 dark:border-stone-800">
            <!-- Barra decorativa degradada -->
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#26263A] via-[#26263A] to-[#7A7AA3]"></div>
            
            <!-- Avatar con Iniciales y Animación -->
            <div class="h-28 w-28 rounded-full flex items-center justify-center text-4xl font-bold text-white shrink-0 shadow-lg border-2 border-white dark:border-stone-800 bg-gradient-to-br from-[#26263A] to-[#7A7AA3] animate-float mt-4 mb-4 select-none" id="previewAvatar">
                <?= $row ? mb_strtoupper(mb_substr($row['nombres'], 0, 1) . mb_substr($row['apellidos'], 0, 1)) : 'P' ?>
            </div>
            
            <!-- Nombre y Carrera -->
            <h2 class="font-display font-bold text-xl text-slate-800 dark:text-stone-100 mt-2 truncate max-w-full" id="previewName">
                <?= $row ? e(nombre_completo($row['nombres'], $row['apellidos'])) : 'Nuevo Practicante' ?>
            </h2>
            <p class="text-xs text-slate-500 dark:text-stone-400 mt-1 font-medium" id="previewCarrera">
                <?= $row ? e($row['carrera']) : 'Carrera profesional' ?>
            </p>
            
            <!-- Badge de Estado -->
            <div class="mt-4" id="previewStatusWrap">
                <?php
                $status = $row['estado'] ?? 'activo';
                $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/50';
                if ($status === 'finalizado') {
                    $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border-blue-200 dark:border-blue-900/50';
                } elseif ($status === 'suspendido') {
                    $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-900/50';
                }
                ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border <?= $badgeClass ?>" id="previewStatus">
                    ● <?= e(ucfirst($status)) ?>
                </span>
            </div>

            <!-- Detalles Rápidos -->
            <div class="w-full mt-6 pt-6 border-t border-slate-100 dark:border-stone-800/80 space-y-3.5 text-left text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">DNI:</span>
                    <span class="font-mono font-semibold text-slate-700 dark:text-stone-300" id="previewDni"><?= $row ? e($row['dni']) : '—' ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Institución:</span>
                    <span class="font-semibold text-slate-700 dark:text-stone-300 text-right truncate max-w-[160px]" id="previewInst" title="<?= e($inst_name) ?>"><?= $row ? e($inst_name ?: '—') : '—' ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Área:</span>
                    <span class="font-semibold text-slate-700 dark:text-stone-300 text-right truncate max-w-[160px]" id="previewArea" title="<?= e($area_name) ?>"><?= $row ? e($area_name ?: '—') : '—' ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">F. Inicio:</span>
                    <span class="font-semibold text-slate-700 dark:text-stone-300" id="previewFechaInicio"><?= $row && $row['fecha_inicio'] ? e($row['fecha_inicio']) : '—' ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">F. Fin:</span>
                    <span class="font-semibold text-slate-700 dark:text-stone-300" id="previewFechaFin"><?= $row && $row['fecha_fin'] ? e($row['fecha_fin']) : '—' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Formulario de Datos -->
    <div class="md:col-span-2">
        <div class="ui-panel p-6 border border-slate-200 dark:border-stone-800 shadow-md">
            <form method="post" action="<?= e(app_url('index.php?r=practicante_save')) ?>" enctype="multipart/form-data" class="space-y-6" id="formPracticante">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $row ? (int) $row['id'] : 0 ?>">
                
                <h3 class="font-display font-bold text-lg text-pisco-sky dark:text-pisco-skylt border-b border-slate-100 dark:border-stone-800 pb-3">
                    Información Personal y Académica
                </h3>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="ui-label">DNI <span class="ui-required">*</span></label>
                        <div class="flex gap-2">
                            <input name="dni" id="inputDni" required maxlength="8" pattern="\d{8}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="<?= e($row['dni'] ?? '') ?>" class="ui-field font-mono" placeholder="12345678">
                            <button type="button" id="btnConsultarDni" class="ui-btn-outline px-3" title="Consultar RENIEC">
                                <span id="dniLoading" class="hidden">⌛</span>
                                <span id="dniIcon">🔍</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="ui-label">Estado</label>
                        <select name="estado" id="inputEstado" class="ui-field">
                            <?php foreach (['activo', 'finalizado', 'suspendido'] as $e): ?>
                                <option value="<?= $e ?>" <?= (($row['estado'] ?? 'activo') === $e) ? 'selected' : '' ?>><?= e(ucfirst($e)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="ui-label">Nombres <span class="ui-required">*</span></label>
                        <input name="nombres" id="inputNombres" required readonly value="<?= e($row['nombres'] ?? '') ?>" class="ui-field bg-slate-50 dark:bg-stone-800/50 cursor-not-allowed" placeholder="Se llena con el DNI">
                    </div>
                    <div>
                        <label class="ui-label">Apellidos <span class="ui-required">*</span></label>
                        <input name="apellidos" id="inputApellidos" required readonly value="<?= e($row['apellidos'] ?? '') ?>" class="ui-field bg-slate-50 dark:bg-stone-800/50 cursor-not-allowed" placeholder="Se llena con el DNI">
                    </div>
                    <div class="ui-panel-highlight">
                        <span class="ui-muted-inline">Nombre completo (automático):</span>
                        <span class="font-semibold text-[#26263A] dark:text-[#DCDCEC]" id="spanFullName"><?= e(nombre_completo($row['nombres'] ?? '', $row['apellidos'] ?? '')) ?></span>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="ui-label">Carrera profesional <span class="ui-required">*</span></label>
                        <input name="carrera" id="inputCarrera" list="carrerasList" required value="<?= e($row['carrera'] ?? '') ?>" class="ui-field" autocomplete="off" placeholder="Escriba para buscar carrera...">
                        <datalist id="carrerasList">
                            <?php foreach ($carreras_list as $c): ?>
                                <option value="<?= e($c) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="ui-label">Institución de Procedencia</label>
                        <input type="hidden" name="institucion_id" id="hidden_institucion_id" value="<?= e((string) ($row['institucion_id'] ?? '')) ?>">
                        <input type="text" id="search_institucion" list="list_instituciones" value="<?= e($inst_name) ?>" class="ui-field" placeholder="Escriba para buscar institución..." autocomplete="off">
                        <datalist id="list_instituciones">
                            <?php foreach ($insts as $i): ?>
                                <option data-id="<?= (int) $i['id'] ?>" value="<?= e($i['nombre']) ?> (<?= e($i['tipo']) ?>)"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="ui-label">Área Asignada</label>
                        <input type="hidden" name="area_id" id="hidden_area_id" value="<?= e((string) ($row['area_id'] ?? '')) ?>">
                        <input type="text" id="search_area" list="list_areas" value="<?= e($area_name) ?>" class="ui-field" placeholder="Escriba para buscar área..." autocomplete="off">
                        <datalist id="list_areas">
                            <?php foreach ($areas as $a): ?>
                                <option data-id="<?= (int) $a['id'] ?>" value="<?= e($a['nombre']) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="ui-label">Fecha Inicio Prácticas</label>
                        <input type="date" name="fecha_inicio" id="inputFechaInicio" value="<?= e($row['fecha_inicio'] ?? '') ?>" class="ui-field flatpickr-date" placeholder="Seleccione fecha">
                    </div>
                    <div>
                        <label class="ui-label">Fecha Fin Prácticas</label>
                        <input type="date" name="fecha_fin" id="inputFechaFin" value="<?= e($row['fecha_fin'] ?? '') ?>" class="ui-field flatpickr-date" placeholder="Seleccione fecha">
                    </div>
                    <div>
                        <label class="ui-label">Correo Electrónico</label>
                        <input type="email" name="correo" id="inputCorreo" maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '').replace(/-{2,}/g, '-')" value="<?= e($row['correo'] ?? '') ?>" class="ui-field" placeholder="ejemplo@correo.com">
                    </div>
                    <div>
                        <label class="ui-label">Teléfono de Contacto</label>
                        <input name="telefono" id="inputTelefono" maxlength="9" pattern="\d{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="<?= e($row['telefono'] ?? '') ?>" class="ui-field" placeholder="999999999">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 dark:border-stone-800">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button type="submit" class="ui-btn-primary-wide w-full sm:w-auto justify-center">Guardar Cambios</button>
                        <a href="<?= e(app_url('index.php?r=practicantes')) ?>" class="ui-btn-outline-wide w-full sm:w-auto justify-center">Cancelar</a>
                    </div>
                    <?php if ($row): ?>
                        <button type="button" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 hover:bg-red-700 active:scale-95 px-5 py-2 text-sm font-semibold text-white transition-all duration-150 cursor-pointer border-none" onclick="confirmarEliminacion(event, '<?= e(nombre_completo($row['nombres'], $row['apellidos'])) ?>')">
                            🗑️ Eliminar Practicante
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Flatpickr CSS y JS (DEBE cargarse antes del script de inicialización) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
<style>
.flatpickr-calendar {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
    border: 1px solid #334155 !important;
}
</style>

<script>
document.getElementById('btnConsultarDni')?.addEventListener('click', async function() {
    const dniInput = document.getElementById('inputDni');
    const dni = dniInput.value.trim();
    const isDark = document.documentElement.classList.contains('dark');
    
    if (dni.length !== 8) {
        Swal.fire({
            icon: 'error',
            title: 'DNI Inválido',
            text: 'Por favor, ingrese un DNI válido de 8 dígitos.',
            background: isDark ? '#0f172a' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#0f172a',
            confirmButtonColor: '#26263A'
        });
        return;
    }

    const btn = this;
    const icon = document.getElementById('dniIcon');
    const loading = document.getElementById('dniLoading');
    const inputNombres = document.querySelector('input[name="nombres"]');
    const inputApellidos = document.querySelector('input[name="apellidos"]');

    btn.disabled = true;
    icon.classList.add('hidden');
    loading.classList.remove('hidden');

    try {
        const url = '<?= app_url("index.php?r=api_dni&dni=") ?>' + dni;
        const resp = await fetch(url);
        
        if (!resp.ok) throw new Error('Error en la respuesta del servidor');
        
        const res = await resp.json();

        if (res.ok) {
            inputNombres.value = res.data.nombres.trim();
            inputApellidos.value = (res.data.apellido_paterno + ' ' + res.data.apellido_materno).trim();
            
            // Forzar actualización de la UI
            inputNombres.dispatchEvent(new Event('input'));
            inputApellidos.dispatchEvent(new Event('input'));
            inputNombres.dispatchEvent(new Event('change'));
            inputApellidos.dispatchEvent(new Event('change'));
            
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: isDark ? '#1e293b' : '#ffffff',
                color: isDark ? '#f1f5f9' : '#0f172a',
            });
            Toast.fire({
                icon: 'success',
                title: 'Datos de ' + inputNombres.value + ' cargados desde RENIEC'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error de API',
                text: res.msg,
                background: isDark ? '#0f172a' : '#ffffff',
                color: isDark ? '#f1f5f9' : '#0f172a',
                confirmButtonColor: '#26263A'
            });
        }
    } catch (err) {
        console.error('Error en fetch:', err);
        Swal.fire({
            icon: 'error',
            title: 'Error de Conexión',
            text: 'No se pudo consultar el DNI: ' + err.message,
            background: isDark ? '#0f172a' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#0f172a',
            confirmButtonColor: '#26263A'
        });
    } finally {
        btn.disabled = false;
        icon.classList.remove('hidden');
        loading.classList.add('hidden');
    }
});

function setupDatalist(inputId, hiddenId, listId) {
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const list = document.getElementById(listId);
    
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
        if (hidden.value === '') {
            input.value = '';
        }
    });
}

function confirmarEliminacion(event, nombre) {
    event.preventDefault();
    const isDark = document.documentElement.classList.contains('dark');
    
    Swal.fire({
        title: '¿Estás completamente seguro?',
        html: `Estás a punto de eliminar al practicante <strong class="text-[#26263A] dark:text-[#7A7AA3]">${nombre}</strong>.<br><br><span class="text-xs text-red-500 font-bold block bg-red-50 dark:bg-red-950/30 p-3 rounded-lg border border-red-200 dark:border-red-900/50">⚠️ ADVERTENCIA: Esta acción es irreversible. Se borrarán permanentemente todos sus registros de asistencia y su cuenta de usuario vinculada al mismo tiempo.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, borrar por completo',
        cancelButtonText: 'Cancelar',
        background: isDark ? '#0f172a' : '#ffffff',
        color: isDark ? '#f1f5f9' : '#0f172a',
        iconColor: '#dc2626',
        customClass: {
            popup: 'rounded-2xl border border-slate-200 dark:border-stone-800 shadow-xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const deleteUrl = <?= $row ? json_encode(app_url('index.php?r=practicante_delete&id=' . (int) $row['id'] . '&_csrf=' . urlencode(csrf_token()))) : "''" ?>;
            if (deleteUrl) {
                window.location.href = deleteUrl;
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupDatalist('search_institucion', 'hidden_institucion_id', 'list_instituciones');
    setupDatalist('search_area', 'hidden_area_id', 'list_areas');
    
    // Sincronización en tiempo real con la tarjeta de vista previa
    const inputDni = document.getElementById('inputDni');
    const inputNombres = document.getElementById('inputNombres');
    const inputApellidos = document.getElementById('inputApellidos');
    const inputCarrera = document.getElementById('inputCarrera');
    const inputEstado = document.getElementById('inputEstado');
    const inputFechaInicio = document.getElementById('inputFechaInicio');
    const inputFechaFin = document.getElementById('inputFechaFin');
    const searchInst = document.getElementById('search_institucion');
    const searchArea = document.getElementById('search_area');

    const previewDni = document.getElementById('previewDni');
    const previewName = document.getElementById('previewName');
    const previewCarrera = document.getElementById('previewCarrera');
    const previewStatus = document.getElementById('previewStatus');
    const previewStatusWrap = document.getElementById('previewStatusWrap');
    const previewFechaInicio = document.getElementById('previewFechaInicio');
    const previewFechaFin = document.getElementById('previewFechaFin');
    const previewInst = document.getElementById('previewInst');
    const previewArea = document.getElementById('previewArea');
    const previewAvatar = document.getElementById('previewAvatar');
    const spanFullName = document.getElementById('spanFullName');

    function updatePreview() {
        const dni = inputDni.value.trim() || '—';
        const nombres = inputNombres.value.trim();
        const apellidos = inputApellidos.value.trim();
        const carrera = inputCarrera.value.trim() || 'Carrera profesional';
        const estado = inputEstado.value;
        const fechaIni = inputFechaInicio.value || '—';
        const fechaFin = inputFechaFin.value || '—';
        const inst = searchInst.value.trim() || '—';
        const area = searchArea.value.trim() || '—';

        if (previewDni) previewDni.textContent = dni;
        
        const fullName = (nombres || apellidos) ? (nombres + ' ' + apellidos).trim() : 'Nuevo Practicante';
        if (previewName) {
            previewName.textContent = fullName;
            previewName.title = fullName;
        }
        if (spanFullName) spanFullName.textContent = (nombres || apellidos) ? (nombres + ' ' + apellidos).trim() : '—';
        
        if (previewCarrera) previewCarrera.textContent = carrera;
        if (previewFechaInicio) previewFechaInicio.textContent = fechaIni;
        if (previewFechaFin) previewFechaFin.textContent = fechaFin;
        
        if (previewInst) {
            previewInst.textContent = inst;
            previewInst.title = inst;
        }
        if (previewArea) {
            previewArea.textContent = area;
            previewArea.title = area;
        }

        // Iniciales del Avatar
        if (previewAvatar) {
            let initials = '';
            if (nombres) initials += nombres.substring(0, 1).toUpperCase();
            if (apellidos) initials += apellidos.substring(0, 1).toUpperCase();
            previewAvatar.textContent = initials || 'P';
        }

        // Estilo del badge de estado
        if (previewStatus && previewStatusWrap) {
            previewStatus.innerHTML = '● ' + estado.charAt(0).toUpperCase() + estado.slice(1);
            let badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/50';
            if (estado === 'finalizado') {
                badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border-blue-200 dark:border-blue-900/50';
            } else if (estado === 'suspendido') {
                badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-900/50';
            }
            previewStatus.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border ' + badgeClass;
        }
    }

    // Escuchadores de eventos
    [inputDni, inputNombres, inputApellidos, inputCarrera, inputFechaInicio, inputFechaFin].forEach(input => {
        input?.addEventListener('input', updatePreview);
        input?.addEventListener('change', updatePreview);
    });
    inputEstado?.addEventListener('change', updatePreview);
    
    // Sobrescribir setupDatalist original para disparar también la actualización de vista previa
    const origSetupDatalist = setupDatalist;
    window.setupDatalist = function(inputId, hiddenId, listId) {
        origSetupDatalist(inputId, hiddenId, listId);
        const inp = document.getElementById(inputId);
        if (inp) {
            inp.addEventListener('input', updatePreview);
            inp.addEventListener('blur', updatePreview);
            // También escuchar cuando se selecciona del datalist mediante cambio manual
            inp.addEventListener('change', updatePreview);
        }
    };

    // Volver a inicializar con la función extendida
    window.setupDatalist('search_institucion', 'hidden_institucion_id', 'list_instituciones');
    window.setupDatalist('search_area', 'hidden_area_id', 'list_areas');

    // Inicializar Flatpickr
    flatpickr(".flatpickr-date", {
        locale: "es",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        disableMobile: "true",
        onChange: function() {
            updatePreview();
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
