<?php

require __DIR__.'/../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;

$app->get('/', function(Request $request) use ($app) {
    return $app['twig']->render('form.html.twig');
});

$app['debug'] = true;
$app->run();
