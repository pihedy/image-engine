<?php

define('STDIN', fopen("php://stdin","r"));

// App autoloader by Composer.
require_once APP_PATH_ROOT . '/src/vendor/autoload.php';

// Reading settings.
$settings = require_once APP_PATH_ROOT . '/src/app/config.php';

// Global app reference.
$app = new \Slim\App(['settings' => $settings]);

// Destroying the global var (made only for prepare the app).
unset($settings);

// Global container reference.
$container = $app->getContainer();

// Requiring dependencies.
require_once APP_PATH_ROOT . '/src/app/dependencies.php';

// Requiring services.
require_once APP_PATH_ROOT . '/src/app/services.php';

// Requiring routes.
require_once APP_PATH_ROOT . '/src/app/routes.php';

$app->run();
