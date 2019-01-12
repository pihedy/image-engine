<?php

// Project public path.
define('APP_PATH_PUBLIC', __DIR__);

// Project root path.
// Method: env:APP_PATH_ROOT else env:REDIRECT_APP_PATH_ROOT else /../..
define('APP_PATH_ROOT', __DIR__ . DIRECTORY_SEPARATOR . '..');

// Checking the application's main file.
if (!file_exists(APP_PATH_ROOT . '/src/bootstrap/app.php')) {
    error_log(
        'The application\'s main file is not exists in: ' . APP_PATH_ROOT . '/src/bootstrap/app.php'
    );
    exit;
}

// Including the application's main file.
require_once APP_PATH_ROOT . '/src/bootstrap/app.php';
