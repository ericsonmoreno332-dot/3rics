<?php

declare(strict_types=1);

require_login();
logout();
redirect(app_url('index.php?r=login'));
