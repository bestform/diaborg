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
                "timestamp" =>  $timestamp,
                "time" => date('H:i', $timestamp),
                "value" => $entry->getValue(),
                "insulin" => $entry->getInsulin(),
                "BE" => $entry->getBE(),
                "key" => $entry->getTimestamp()
            );
        }

        $entries = $this->augmentGraphData($entries);

        $entries = array_reverse($entries);
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
            "date" => $request->get("date")
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
            $dateString = $request->get("date");
            $date = new \DateTime($dateString);
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
        $days = array_keys($entries);
        foreach($entries as $key => $dayentry){
            $daystart = $key;
            $dayend = $key + (24 * 60 * 60);
            $bzarray = array();
            $insulinarray = array();
            $bearray = array();
            $lastValueOfDayBefore = $this->getBorderValue($entries, $key, true);
            if(null !== $lastValueOfDayBefore){
                $bzarray[] = array("date"=>$lastValueOfDayBefore['timestamp'], "value"=>$lastValueOfDayBefore['value'], "daystart"=> $daystart, "dayend"=> $dayend);
            }
            foreach($dayentry['entries'] as $timeentry){
                if(!empty($timeentry['value'])){
                    $bzarray[] = array("date"=>$timeentry['timestamp'], "value"=>$timeentry['value'], "daystart"=> $daystart, "dayend"=> $dayend, "key" => $timeentry["key"]);
                }
                if(!empty($timeentry['insulin'])){
                    $insulinarray[] = array("date"=>$timeentry['timestamp'], "insulin" => $timeentry['insulin'], "key" => $timeentry["key"]);
                }
                if(!empty($timeentry['BE'])){
                    $bearray[] = array("date"=>$timeentry['timestamp'], "BE" => $timeentry["BE"], "key" => $timeentry["key"]);
                }

            }
            $nextValue = $this->getBorderValue($entries, $key, false);
            if(null !== $nextValue){
                $bzarray[] = array("date"=>$nextValue['timestamp'], "value"=>$nextValue['value'], "daystart"=> $daystart, "dayend"=> $dayend);
            }
            $dayentry['bzarray'] = json_encode($bzarray);
            $dayentry['insulinarray'] = json_encode($insulinarray);
            $dayentry['bearray'] = json_encode($bearray);

            $entries[$key] = $dayentry;

        }

        return $entries;
    }

    private function getBorderValue($entries, $key, $before = true)
    {
        $entryKeys = array_keys($entries);
        $foundEntry = null;
        if(!$before){
            $entryKeys = array_reverse($entryKeys);
        }

        foreach($entryKeys as $entrykey){
            if($entrykey === $key){
                $entryWithValue = null;
                if(!$before){
                    $foundEntry['entries'] = array_reverse($foundEntry['entries']);
                }
                foreach($foundEntry['entries'] as $dayEntry){
                    if(!empty($dayEntry['value'])){
                        $entryWithValue = $dayEntry;
                    }
                }
                if(null !== $entryWithValue){
                    $timestamp1 = $entryWithValue['timestamp'];
                    $timestamp2 = $key;
                    if(abs($timestamp1 - $timestamp2) > 24*60*60*2){
                        $entryWithValue = null;
                    }
                }
                return $entryWithValue;
            }
            $foundEntry = $entries[$entrykey];
        }

        return null;
    }

}