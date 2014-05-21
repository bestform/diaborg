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
        $data = $this->getRepository($app)->getList();
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
        $twig = $this->getTwig();
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

        $content =  $twig->render('form.html.twig', $vars);

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

}