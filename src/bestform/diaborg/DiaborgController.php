<?php

namespace bestform\diaborg;

use bestform\diaborg\data\DiaborgRepositoryInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;



class DiaborgController {


    /**
     * @return DiaborgRepositoryInterface
     */
    private function getRepository(Application $app)
    {
        return $app['repository'];
    }

    public function getRoot(Request $request, Application $app)
    {
        return $app->redirect('/index.php/list');
    }

    public function getList(Request $request, Application $app){
        $data = $this->getRepository($app)->getList();

        $entries = array();
        $dayId = 0;
        foreach($data as $entry){
            $timestamp = $entry->getTimestamp();
            $dateTime = new \DateTime();
            $dateTime->setTimestamp($timestamp);
            $dateTime->setTime(0,0);
            $currentDay = $dateTime->getTimestamp();
            if(!isset($entries[$currentDay])){
                //init day
                $entries[$currentDay] = array();
                $entries[$currentDay]['entries'] = array();
                $entries[$currentDay]['id'] = $dayId++;
                $entries[$currentDay]['date'] = date('l, d. F', $currentDay);
                $entries[$currentDay]['grapharray'] = "{}";
            }

            $entries[$currentDay]['entries'][] = array(
                "time" => date('H:i', $timestamp),
                "value" => $entry->getValue(),
                "insulin" => $entry->getInsulin(),
                "BE" => $entry->getBE(),
                "key" => $entry->getTimestamp()
            );
        }

        $entries = $this->augmentGraphData($entries);

        //$entries = array_reverse($entries);
        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];
        $content = $twig->render('list.html.twig', array("entries" => $entries));

        return $twig->render('base.html.twig', array('content' => $content, 'print' => $request->get('print') === "1"));
    }

    public function getClear(Request $request, Application $app)
    {
        $this->getRepository($app)->clear($app);
        return $app->redirect('/index.php/list');
    }

    public function getDelete(Request $request, Application $app)
    {
        $id = $request->get("id");
        $this->getRepository($app)->deleteEntry($id);

        return $app->redirect('/index.php/list');
    }

    public function getAdd(Request $request, Application $app)
    {
        $vars = array(
            "year" => $request->get("year"),
            "month" => $request->get("month"),
            "day" => $request->get("day")
        );
        // we want to render additional fields again, should an error occur
        if(isset($app['errors'])){
            $additianalVars = array(
                "errors" => $app["errors"],
                "hour" => $request->get("hour"),
                "minute" => $request->get("minute"),
                "value" => $request->get("value"),
                "insulin" => $request->get("insulin"),
                "BE" => $request->get("BE")
            );
            $vars = array_merge($vars, $additianalVars);
        }

        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];
        $content = $twig->render('form.html.twig', $vars);

        return $twig->render('base.html.twig', array('content' => $content));
    }

    public function postAdd(Request $request, Application $app)
    {
        $validator = new DiaborgValidator();
        $errors = $validator->validateDataForNewEntry($request);
        if(count($errors) > 0){
            $apperrors = array();
            /** @var $error DiaborgValidationError */
            foreach($errors as $error){
                $apperrors[$error->getKey()] = $error->getMessage();
            }
            $app['errors'] = $apperrors;
        } else {
            $date = new \DateTime();
            $date->setDate($request->get("year"), $request->get("month"), $request->get("day"));
            $date->setTime($request->get("hour"), $request->get("minute"));
            $timestamp = $date->getTimestamp();
            $this->getRepository($app)->addEntry(
                $timestamp,
                $request->get("value"),
                $request->get("insulin"),
                $request->get("BE")
            );
        }

        return $this->getAdd($request, $app);
    }

    private function augmentGraphData($entries)
    {
        foreach($entries as $key => $dayentry){
            $grapharray = array();
            foreach($dayentry['entries'] as $timeentry){
                if(!empty($timeentry['value'])){
                    $grapharray[] = array("date"=>$timeentry['time'], "value"=>$timeentry['value']);
                }
            }
            $dayentry['grapharray'] = json_encode($grapharray);
            $entries[$key] = $dayentry;
        }

        return $entries;
    }

}