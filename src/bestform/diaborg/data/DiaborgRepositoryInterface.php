<?php

namespace bestform\diaborg\data;


use Silex\Application;

interface DiaborgRepositoryInterface {

    public function getList();

    public function getEntry($id);

    public function addEntry($timestamp, $value, $insulin, $be);

    public function deleteEntry($id);

    public function clear(Application $app);

} 