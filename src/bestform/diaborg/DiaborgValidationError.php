<?php

namespace bestform\diaborg;

class DiaborgValidationError {

    public static $LEVEL_ERROR = "error";
    public static $LEVEL_WARNING = "warning";

    private $level = null;
    private $message = null;
    private $key = null;

    public function __construct($level, $key, $message)
    {
        $this->level = $level;
        $this->key = $key;
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getKey()
    {
        return $this->key;
    }

}