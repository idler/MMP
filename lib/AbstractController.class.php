<?php

abstract class AbstractController
{
    protected $db   = null;
    protected $args = array();

    function __construct($db = null, $args = array())
    {
        $this->db   = $db;
        $this->args = $args;
    }

    public abstract function runStrategy();
}