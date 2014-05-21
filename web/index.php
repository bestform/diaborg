<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;
$app['repository'] = $app->share(function () {
    return new \bestform\diaborg\data\DiaborgRepositoryJSON();
});
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../snippets',
    'twig.options' => array(
        'strict_variables' => false
    )
));

// routes
$app->get('/', 'bestform\diaborg\DiaborgController::getRoot');
$app->get('/list', 'bestform\diaborg\DiaborgController::getList');
$app->get('/clear', 'bestform\diaborg\DiaborgController::getClear');
$app->get('/delete', 'bestform\diaborg\DiaborgController::getDelete');
$app->get('/add', 'bestform\diaborg\DiaborgController::getAdd');
$app->post('/add', 'bestform\diaborg\DiaborgController::postAdd');

$app->run();