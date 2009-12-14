<?php
require_once dirname(__FILE__).'/AbstractController.class.php';

class helpController extends AbstractController
{

  public function runStrategy()
  {
    echo <<<HELP

MySQL Migration with PHP
---------------------------------------------------------------------
  help:       display this help and exit
  schema:     create schema for initial migration/installation
  init:       load initial schema (install)
  create:     create new migration

---------------------------------------------------------------------
Licenced under: GPL v3
Author: Maxim Antonov <max.antonoff@gmail.com>


HELP;
/*
    $text ="echo This is the test help";
    printf("\%s[%sm %s\n", chr(27), 43, $text );
    */


  }
}