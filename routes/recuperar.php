<?php

declare(strict_types=1);

$title = 'Recuperar contraseña';
ob_start();
?>
<div class="ui-login-card-plain">
    <p class="ui-help">Contacte al administrador del sistema para restablecer su acceso. Esta función puede integrarse con correo electrónico en una fase posterior.</p>
    <a class="ui-link-back" href="<?= e(app_url('index.php?r=login')) ?>">← Volver al login</a>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/views/layout.php';
