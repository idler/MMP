<?php
require_once dirname(__FILE__).'/AbstractController.class.php';

class helpController extends AbstractController
{

  public function runStrategy()
  {
    echo <<<HELP
This is the test help

HELP;
  }
}