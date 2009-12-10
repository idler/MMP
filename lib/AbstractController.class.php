<?php

abstract class AbstractController
{
  protected $db = null;

  function __construct($db = null)
  {
    $this->db = $db;
  }

  abstract public function runStrategy();
}