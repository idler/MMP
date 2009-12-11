<?php
require_once dirname(__FILE__).'/AbstractController.class.php';

class helpController extends AbstractController
{

  public function runStrategy()
  {
    echo <<<HELP

\033[41m \033[1m
    This is the test help

\033[40m\033[0m

HELP;
/*
    $text ="echo This is the test help";
    printf("\%s[%sm %s\n", chr(27), 43, $text );
    */


  }
}