<?php

namespace bestform\diaborg;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;



class DiaborgController {

    private function getDataDir()
    {
        return __DIR__ . '/../../../data';
    }

    private function getDataFile()
    {
        return $this->getDataDir() . '/data.json';
    }

    private function getData()
    {
        $rawdata = file_get_contents($this->getDataFile());
        $data = json_decode($rawdata, true);
        if(null === $data){
            $data = array();
        }

        return $data;
    }

    /**
     * @return \Twig_Environment
     */
    private function getTwig()
    {
        return new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__ . '/../../../snippets'));
    }

    public function getRoot(Request $request, Application $app)
    {
        return $app->redirect('/index.php/list');
    }

    public function getList(Request $request, Application $app){
        $data = $this->getData();
        $keys = array_keys($data);
        sort($keys);
        $entries = array();
        $twig = $this->getTwig();
        foreach($keys as $key){
            $entries[] = array(
                "date" => date('d. m.', $key),
                "time" => date('H:i', $key),
                "values" => $data[$key],
                "key" => $key
            );
        }

        $content = $twig->render('list.html.twig', array("entries" => $entries));

        return $twig->render('base.html.twig', array('content' => $content, 'print' => $request->get('print') === "1"));
    }

    public function getClear(Request $request, Application $app){
        file_put_contents(__DIR__ . '/../data/data.json', '');

        return $app->redirect('/index.php/list');
    }

    public function getDelete(Request $request, Application $app)
    {
        $data = $this->getData();
        $key = $request->get("id");
        if(null !== $key){
            if(isset($data[$key])){
                unset($data[$key]);
                file_put_contents(__DIR__ . '/../data/data.json', json_encode($data));
            }
        }

        return $app->redirect('/index.php/list');
    }

    public function getAdd(Request $request, Application $app)
    {
        $twig = $this->getTwig();
        $content =  $twig->render('form.html.twig', array(
            "lastyear" => $request->get("lastyear"),
            "lastmonth" => $request->get("lastmonth"),
            "lastday" => $request->get("lastday")
        ));

        return $twig->render('base.html.twig', array('content' => $content));
    }

    public function postAdd(Request $request, Application $app)
    {
        $data = $this->getData();
        $date = new \DateTime();
        $date->setDate($request->get("year"), $request->get("month"), $request->get("day"));
        $date->setTime($request->get("hour"), $request->get("minute"));
        $entry = array(
            "value" => $request->get("value"),
            "insulin" => $request->get("insulin"),
            "BE" => $request->get("BE"),
        );
        $data[$date->getTimestamp()] = $entry;

        file_put_contents($this->getDataFile(), json_encode($data));

        return $app->redirect('/index.php/add?lastyear='.$request->get('year').'&lastmonth='.$request->get('month').'&lastday='.$request->get('day'));
    }

}