<?php

declare(strict_types=1);

use App\HistoryController;
use App\UserController;
use App\StockController;
use Slim\App;

return function (App $app) {
    // unprotected routes
    $app->post('/user/create', UserController::class . ':create');

    // Get JWT_TOKEN
    $app->post('/user/login', UserController::class . ':login');

    // protected routes
    $app->get('/history', HistoryController::class . ':history');
    $app->get('/stock', StockController::class . ':stock');
};
