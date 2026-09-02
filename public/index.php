<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Controllers\ProductController;
use App\Controllers\OrderController;

$router = new Router();

$products = new ProductController();
$orders = new OrderController();

$router->add('GET', '/products', [$products, 'index']);
$router->add('GET', '/products/{id}', [$products, 'show']);
$router->add('POST', '/products', [$products, 'store']);
$router->add('PUT', '/products/{id}', [$products, 'update']);
$router->add('DELETE', '/products/{id}', [$products, 'destroy']);

$router->add('POST', '/orders', [$orders, 'store']);
$router->add('GET', '/orders/{id}', [$orders, 'show']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
