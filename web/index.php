<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app['twig'] = new Twig_Environment(new Twig_Loader_Filesystem(__DIR__ . '/../snippets'));

$rawdata = file_get_contents(__DIR__ . '/../data/data.json');
$data = json_decode($rawdata, true);
if(null === $data){
    $data = array();
}


$date = new DateTime();
$timestamp = $date->getTimestamp();


// routes
$app->get('/', function() use ($app) {
    return $app->redirect('/index.php/list');
    }
);
$app->get('/list', function(\Symfony\Component\HttpFoundation\Request $request) use ($data, $app) {
        $keys = array_keys($data);
        sort($keys);
        $entries = array();
        /** @var Twig_Environment $twig */
        $twig = $app["twig"];
        foreach($keys as $key){
            $entries[] = array(
                "date" => date('d. m.', $key),
                "time" => date('H:i', $key),
                "values" => $data[$key]
            );
        }

        $content = $twig->render('list.html.twig', array("entries" => $entries));
        return $twig->render('base.html.twig', array('content' => $content, 'print' => $request->get('print') === "1"));
    }
);
$app->get('/clear', function() use ($app) {
        file_put_contents(__DIR__ . '/../data/data.json', '');
        return $app->redirect('/index.php/list');
    }
);

$app->get('/add', function(\Symfony\Component\HttpFoundation\Request $request) use ($data, $app) {
        /** @var Twig_Environment $twig */
        $twig = $app["twig"];
        $content =  $twig->render('form.html.twig', array(
                "lastyear" => $request->get("lastyear"),
                "lastmonth" => $request->get("lastmonth"),
                "lastday" => $request->get("lastday")
            ));
        return $twig->render('base.html.twig', array('content' => $content));
    }
);

$app->post('/add', function(\Symfony\Component\HttpFoundation\Request $request) use ($data, $app) {
        $date = new DateTime();
        $date->setDate($request->get("year"), $request->get("month"), $request->get("day"));
        $date->setTime($request->get("hour"), $request->get("minute"));
        $entry = array(
                "value" => $request->get("value"),
                "insulin" => $request->get("insulin"),
                "BE" => $request->get("BE"),
        );
        $data[$date->getTimestamp()] = $entry;

        file_put_contents(__DIR__ . '/../data/data.json', json_encode($data));

        return $app->redirect('/index.php/add?lastyear='.$request->get('year').'&lastmonth='.$request->get('month').'&lastday='.$request->get('day'));
    }
);

$app->run();