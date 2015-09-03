<?php

abstract class AbstractController
{
  protected $db   = null;
  protected $args = [];

  function __construct($db = null, $args = [])
  {
    $this->db   = $db;
    $this->args = $args;
  }

  abstract public function runStrategy();
}