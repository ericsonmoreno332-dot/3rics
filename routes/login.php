<?php

declare(strict_types=1);

if (current_user()) {
    redirect(app_url('index.php?r=home'));
}

// Ocultar cabecera y márgenes por defecto en la plantilla
$hide_header = true;

if (is_post()) {
    verify_csrf();

    // ── Mitigación de fuerza bruta ────────────────────────────────
    $maxAttempts = 5;
    $lockoutSeconds = 30;
    $attempts = $_SESSION['login_attempts'] ?? 0;
    $lockUntil = $_SESSION['login_lock_until'] ?? 0;

    if ($attempts >= $maxAttempts && time() < $lockUntil) {
        $wait = $lockUntil - time();
        flash('err', 'Demasiados intentos fallidos. Espere ' . $wait . ' segundos antes de intentar de nuevo.');
        redirect(app_url('index.php?r=login'));
    }

    // Si ya pasó el bloqueo, resetear el contador
    if (time() >= $lockUntil) {
        $_SESSION['login_attempts'] = 0;
        $attempts = 0;
    }

    $u = trim((string) input('username', ''));
    $p = trim((string) input('password', ''));
    if (attempt_login(db(), $u, $p)) {
        // Login exitoso: limpiar intentos
        unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
        redirect(app_url('index.php?r=home'));
    }

    // Login fallido: incrementar contador
    $_SESSION['login_attempts'] = $attempts + 1;
    if ($attempts + 1 >= $maxAttempts) {
        $_SESSION['login_lock_until'] = time() + $lockoutSeconds;
    }

    if (isset($_SESSION['login_inactive_user'])) {
        unset($_SESSION['login_inactive_user']);
        flash('err', 'Su cuenta de usuario está desactivada. Por favor, contacte con el administrador.');
    } else {
        flash('err', 'Usuario o contraseña incorrectos');
    }
    redirect(app_url('index.php?r=login'));
}

$title = 'Iniciar sesión';
ob_start();
?>

<div class="min-h-screen w-full flex items-center justify-center md:items-stretch md:justify-start bg-slate-100 dark:bg-stone-950 transition-colors duration-300 relative overflow-hidden">
    
    <!-- Imagen de fondo difuminada para móviles -->
    <div class="absolute inset-0 md:hidden z-0">
        <img src="<?= e(app_url('municipio.jpg')) ?>" alt="Fondo Municipalidad de Pisco" class="w-full h-full object-cover filter blur-[4px] brightness-[0.4]">
        <div class="absolute inset-0 bg-slate-950/50"></div>
    </div>

    <!-- COLUMNA IZQUIERDA: FORMULARIO (Flotante en móvil, panel completo en escritorio) -->
    <div class="w-[92%] sm:w-[440px] md:w-[45%] lg:w-[40%] xl:w-[35%] my-auto md:my-0 md:h-screen max-h-[95vh] overflow-y-auto md:overflow-visible md:max-h-none flex flex-col justify-between p-8 sm:p-10 md:p-12 lg:p-16 bg-white/95 dark:bg-stone-900/95 md:bg-white md:dark:bg-stone-900 backdrop-blur-md md:backdrop-blur-none rounded-2xl md:rounded-none shadow-2xl md:shadow-xl z-10 transition-all duration-300 border border-white/20 md:border-none">
        
        <!-- Cabecera de Columna: Logo e interruptor de tema -->
        <div class="flex items-center justify-between mb-8 md:mb-4">
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-full flex items-center justify-center overflow-hidden border-2 border-[#7A7AA3] bg-white shadow-sm shrink-0">
                    <img src="<?= e(app_url('assets/img/escudo-pisco.png')) ?>" alt="Escudo de Pisco" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                    <span style="display:none; font-size:22px;">🏛️</span>
                </div>
                <div>
                    <h2 class="font-display font-bold text-slate-800 dark:text-white text-sm leading-tight tracking-wide">MUNICIPALIDAD</h2>
                    <p class="text-xs text-slate-500 dark:text-stone-400 font-medium">Provincial de Pisco</p>
                </div>
            </div>
            
            <!-- Botón de tema -->
            <button type="button" id="loginThemeToggle" class="p-2.5 rounded-xl border border-slate-200 dark:border-stone-700 text-slate-500 dark:text-stone-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-95" aria-label="Cambiar tema">
                <!-- Sol en modo oscuro -->
                <svg class="w-5 h-5 hidden dark:block text-[#7A7AA3]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                </svg>
                <!-- Luna en modo claro -->
                <svg class="w-5 h-5 block dark:hidden text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
            </button>
        </div>

        <!-- Formulario principal -->
        <div class="my-auto py-4">
            <!-- Carrusel de GIFs -->
            <div class="mb-5 flex justify-center">
                <div class="relative w-44 h-44 rounded-2xl overflow-hidden shadow-xl border-[3px] border-[#26263A] ring-4 ring-[#DCDCEC]/30"
                     style="background:#26263A;">
                    <?php
                    $gifs = [
                        'https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExdHdsZjV1azA3NWJwNG44Yzd0M2l1cGYybXZiOG1heHlqNjZya3I0aCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/5AHtj2UbniPlKqw11Y/giphy.gif',
                        'https://media2.giphy.com/media/v1.Y2lkPTc5MGI3NjExdmxmNjNxcms2c2dua3Nldnh5bHZwZzVqb292YmY1cDZ6Z2toa213ZiZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/b3Z7rVmiXJwrMZX524/giphy.gif',
                        'https://media4.giphy.com/media/v1.Y2lkPTc5MGI3NjExYTVkYjRtMGhzeGs5ZHNmc3hra3B5Z2tjc2c3MGp4ZHhwejR4MmY3dSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/Aa4L8PwWZ7wF28dtbk/giphy.gif',
                        'https://media3.giphy.com/media/v1.Y2lkPTc5MGI3NjExamk5eHp3ZmhtZTliMHBuNDg1OGszMDg4bm12bWgwcXplemVqZjg3dSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/fs6yE0mCGBXBfB5mvZ/giphy.gif',
                    ];
                    foreach ($gifs as $i => $gif): ?>
                    <img id="gif-slide-<?= $i ?>" src="<?= e($gif) ?>" alt="Imagen <?= $i+1 ?>"
                         class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 <?= $i === 0 ? 'opacity-100' : 'opacity-0' ?>">
                    <?php endforeach; ?>
                    <!-- Indicadores -->
                    <div class="absolute bottom-2 left-0 right-0 flex justify-center gap-1.5 z-10">
                        <?php foreach ($gifs as $i => $_): ?>
                        <span id="gif-dot-<?= $i ?>" class="block h-1.5 rounded-full transition-all duration-300 <?= $i === 0 ? 'w-4 bg-[#7A7AA3]' : 'w-1.5 bg-white/40' ?>" style="<?= $i === 0 ? 'box-shadow:0 0 6px #7A7AA3cc' : '' ?>"></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <h1 class="font-display font-bold text-3xl leading-tight mb-2 text-[#26263A] dark:text-white">
                ¡Bienvenido!
            </h1>
            <p class="text-sm mb-6 text-slate-600 dark:text-stone-300">
                Control de asistencia de practicantes. Ingresa tus credenciales para acceder.
            </p>

            <!-- Mensajes de error -->
            <?php if ($err = flash('err')): ?>
            <div class="mb-5 rounded-xl px-4 py-3 text-sm font-medium flex items-start gap-3 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-300 border-l-4 border-red-600 dark:border-red-500 animate-fade-in">
                <span class="text-base shrink-0 mt-0.5">⚠️</span> 
                <div>
                    <p class="font-bold">Error de ingreso</p>
                    <p class="text-xs text-red-600/90 dark:text-red-400/90 mt-0.5"><?= e($err) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-5">
                <?= csrf_field() ?>

                <!-- Usuario -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-stone-400 mb-2">
                        Usuario
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 dark:text-stone-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <input name="username" type="text" required autocomplete="username"
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 dark:border-stone-700 bg-slate-50 dark:bg-stone-800 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-stone-500 focus:outline-none focus:border-[#26263A] focus:ring-2 focus:ring-[#26263A]/20 transition-all font-medium"
                               placeholder="Ingresa tu usuario o DNI">
                    </div>
                </div>

                <!-- Contraseña -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-stone-400">
                            Contraseña
                        </label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 dark:text-stone-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2v2a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2h6zM7 11V7a5 5 0 0110 0v4"></path>
                            </svg>
                        </span>
                        <input name="password" id="loginPassword" type="password" required autocomplete="current-password"
                               class="w-full pl-11 pr-11 py-3 rounded-xl border border-slate-200 dark:border-stone-700 bg-slate-50 dark:bg-stone-800 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-stone-500 focus:outline-none focus:border-[#26263A] focus:ring-2 focus:ring-[#26263A]/20 transition-all font-medium"
                               placeholder="••••••••">
                        <button type="button" id="togglePasswordBtn" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 dark:text-stone-500 hover:text-[#26263A] dark:hover:text-[#26263A] transition-colors" aria-label="Mostrar contraseña">
                            <!-- Ojo abierto -->
                            <svg id="eyeIconOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <!-- Ojo cerrado -->
                            <svg id="eyeIconClose" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 012.22-3.882m9.684-2.88a9.982 9.982 0 012.23 3.882l-1.954 6.84M21 21l-2-2m-13.875 0L3.125 3m1.5 1.5l14.75 14.75M9.88 9.88a3 3 0 104.24 4.24"></path>
                            </svg>
                        </button>
                    </div>
                </div>


                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white hover:brightness-110 active:scale-[0.99] border-b-4 shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2" style="background:linear-gradient(to right,#26263A,#26263A);border-bottom-color:#26263A;">
                    <span>Ingresar al Sistema</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Pie de Columna: Derechos de autor -->
        <div class="mt-8 pt-4 border-t border-slate-100 dark:border-stone-800 text-center md:text-left">
            <p class="text-xs text-slate-400 dark:text-stone-500 font-medium">
                &copy; <?= date('Y') ?> Municipalidad Provincial de Pisco.
            </p>
        </div>
    </div>

    <!-- COLUMNA DERECHA: IMAGEN DE FONDO Y CAPA DE MARCA (Sólo en pantallas medianas y superiores) -->
    <div class="hidden md:flex flex-1 relative items-center justify-center bg-slate-950 overflow-hidden">
        <!-- Imagen del Municipio -->
        <img src="<?= e(app_url('municipio.jpg')) ?>" alt="Municipalidad Provincial de Pisco" 
             class="absolute inset-0 w-full h-full object-cover object-center scale-105 animate-fade-in"
             style="filter: brightness(0.65);">
        
        <!-- Overlays de gradiente para dar el look Premium -->
        <div class="absolute inset-0 bg-gradient-to-tr from-slate-950 via-slate-900/50 to-[#26263A]/20 mix-blend-multiply"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-transparent to-transparent"></div>

        <!-- Contenido textual encima de la imagen -->
        <div class="relative z-10 p-12 lg:p-16 max-w-xl text-white space-y-6">
            <span class="inline-block bg-[#7A7AA3] text-slate-950 text-[10px] font-extrabold px-3 py-1.5 rounded-full uppercase tracking-widest shadow-lg">
                Portal Oficial
            </span>
            
            <div class="space-y-3">
                <h1 class="font-display font-bold text-4xl lg:text-5xl leading-tight drop-shadow-lg">
                    Municipalidad Provincial <br>
                    <span class="text-[#DCDCEC]">de Pisco</span>
                </h1>
                <p class="text-slate-200 text-base lg:text-lg leading-relaxed font-light drop-shadow-sm">
                    Control de asistencia digital y geolocalizado para practicantes de todas las áreas municipales.
                </p>
            </div>

            <div class="h-1 w-20 bg-[#7A7AA3] rounded-full"></div>

            <!-- Información municipal con reloj digital y slogan -->
            <div class="flex items-center gap-4 text-[#DCDCEC] text-xs font-semibold">
                <span class="text-lg">🏛️</span>
                <span class="italic">"3ricso"</span>
                <span class="text-white/20">|</span>
                <span id="loginClock" class="font-mono text-white tracking-widest bg-black/40 px-3 py-1.5 rounded-lg border border-white/10 shadow-inner">00:00:00</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Reloj Digital en la columna derecha
    const clockEl = document.getElementById('loginClock');
    if (clockEl) {
        const updateClock = () => {
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            clockEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
        };
        updateClock();
        setInterval(updateClock, 1000);
    }

    // 2. Visibilidad de Contraseña
    const pwdInput = document.getElementById('loginPassword');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const eyeOpen = document.getElementById('eyeIconOpen');
    const eyeClose = document.getElementById('eyeIconClose');

    if (pwdInput && toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isPwd = pwdInput.type === 'password';
            pwdInput.type = isPwd ? 'text' : 'password';
            if (isPwd) {
                eyeOpen.classList.add('hidden');
                eyeClose.classList.remove('hidden');
            } else {
                eyeOpen.classList.remove('hidden');
                eyeClose.classList.add('hidden');
            }
        });
    }

    // 3. Carrusel de GIFs
    (function() {
        const total = 4;
        let current = 0;
        const interval = 2800;
        function goTo(next) {
            const prev = current;
            current = next % total;
            const prevImg = document.getElementById('gif-slide-' + prev);
            const nextImg = document.getElementById('gif-slide-' + current);
            const prevDot = document.getElementById('gif-dot-' + prev);
            const nextDot = document.getElementById('gif-dot-' + current);
            if (prevImg) { prevImg.classList.remove('opacity-100'); prevImg.classList.add('opacity-0'); }
            if (nextImg) { nextImg.classList.add('opacity-100'); nextImg.classList.remove('opacity-0'); }
            if (prevDot) { prevDot.style.boxShadow=''; prevDot.classList.remove('w-4','bg-[#7A7AA3]'); prevDot.classList.add('w-1.5','bg-white/40'); }
            if (nextDot) { nextDot.classList.remove('w-1.5','bg-white/40'); nextDot.classList.add('w-4','bg-[#7A7AA3]'); nextDot.style.boxShadow='0 0 6px #7A7AA3cc'; }
        }
        setInterval(() => goTo(current + 1), interval);
    })();

    // 4. Alternador de Tema Local
    const themeBtn = document.getElementById('loginThemeToggle');
    const rootHtml = document.documentElement;
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const isDark = rootHtml.classList.toggle('dark');
            document.cookie = 'theme=' + (isDark ? 'dark' : 'light') + ';path=/;max-age=31536000';
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';