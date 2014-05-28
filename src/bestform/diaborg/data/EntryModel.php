<?php

namespace bestform\diaborg\data;


class EntryModel implements EntryModelInterface
{

    private $timestamp;
    private $value;
    private $be;
    private $insulin;

    function __construct($timestamp, $value, $be, $insulin)
    {
        $this->timestamp = $timestamp;
        $this->value = $value;
        $this->be = $be;
        $this->insulin = $insulin;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getBE()
    {
        return $this->be;
    }

    /**
     * @return mixed
     */
    public function getInsulin()
    {
        return $this->insulin;
    }

}