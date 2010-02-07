<?php
require_once __DIR__.'/AbstractController.class.php';

class helpController extends AbstractController
{

  public function runStrategy()
  {
    echo <<<HELP

\033[40m                    MySQL Migration with PHP                         \033[49m
---------------------------------------------------------------------
  help:       display this help and exit
  schema:     create schema for initial migration/installation
  init:       load initial schema (install)
  create:     create new migration
  migrate:    migrate to specified time
  
In migrate comand you can use strtotime format
Examples:
*********************************************************************
./migrate.php migrate yestarday
./migrate.php migrate -2 hour
./migrate.php migrate +2 month
./migrate.php migrate 20 September 2001
./migrate.php migrate
********************************************************************
Last example will update your database to last version


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