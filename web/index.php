<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';

$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace("bestform", __DIR__ . '/../src');
$loader->register();

$app = new Silex\Application();
$app['debug'] = true;

// routes
$app->get('/', 'bestform\diaborg\DiaborgController::getRoot');
$app->get('/list', 'bestform\diaborg\DiaborgController::getList');
$app->get('/clear', 'bestform\diaborg\DiaborgController::getClear');
$app->get('/delete', 'bestform\diaborg\DiaborgController::getDelete');
$app->get('/add', 'bestform\diaborg\DiaborgController::getAdd');
$app->post('/add', 'bestform\diaborg\DiaborgController::postAdd');

$app->run();