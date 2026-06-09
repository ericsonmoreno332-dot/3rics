<?php

declare(strict_types=1);

$user = current_user();
if ($user) {
    if (is_practicante_user($user)) {
        redirect(app_url('index.php?r=mi_panel'));
    }
    redirect(app_url('index.php?r=inicio'));
}
redirect(app_url('index.php?r=login'));
