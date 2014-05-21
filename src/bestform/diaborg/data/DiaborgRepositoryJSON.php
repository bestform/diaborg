<?php
/**
 * Created by PhpStorm.
 * User: bestform
 * Date: 5/21/14
 * Time: 10:26 PM
 */

namespace bestform\diaborg\data;


use Silex\Application;

class DiaborgRepositoryJSON implements DiaborgRepositoryInterface
{
    private function getDataDir()
    {
        return __DIR__ . '/../../../../data';
    }

    private function getDataFile()
    {
        return $this->getDataDir() . '/data.json';
    }

    public function getList()
    {
        if(!file_exists($this->getDataFile())){
            return array();
        }
        $rawdata = file_get_contents($this->getDataFile());
        $data = json_decode($rawdata, true);
        if(null === $data){
            $data = array();
        }

        return $data;
    }

    public function getEntry($id)
    {
        // TODO: Implement getEntry() method.
    }

    public function addEntry($timestamp, $value, $insulin, $be)
    {
        $data = $this->getList();
        $entry = array(
            "value" => $value,
            "insulin" => $insulin,
            "BE" => $be
        );
        while(isset($data[$timestamp])){
            $timestamp++;
        }
        $data[$timestamp] = $entry;

        file_put_contents($this->getDataFile(), json_encode($data));
    }

    public function deleteEntry($id)
    {
        $data = $this->getList();
        if(null !== $id){
            if(isset($data[$id])){
                unset($data[$id]);
                file_put_contents($this->getDataFile(), json_encode($data));
            }
        }
    }

    public function clear(Application $app)
    {
        if(!isset($app['debug']) || true !== $app['debug']){
            throw new \Exception('Clearing database only possible in debug mode');
        }

        file_put_contents($this->getDataFile(), '');
    }
}