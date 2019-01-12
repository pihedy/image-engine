<?php

// Event manager
$container['eventManager'] = function () {
    return new \Slim\Event\SlimEventManager;
};

// Session
$container['session'] = function ($container) {
    return new \SlimSession\Helper;
};

// Sheduler
$container['scheduler'] = function ($container) {
    return new \GO\Scheduler;
};
