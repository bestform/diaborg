<?php

namespace bestform\diaborg\data;


interface EntryModelInterface {

    function __construct($timestamp, $value, $be, $insulin);

    function getTimestamp();
    function getValue();
    function getBE();
    function getInsulin();

} 