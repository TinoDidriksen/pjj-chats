<?php

require_once __DIR__.'/configs/config.default.php';
if (file_exists(__DIR__.'/configs/config.local.php')) {
    require_once __DIR__.'/configs/config.local.php';
}
