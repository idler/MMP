<?php
require_once __DIR__.'/AbstractController.class.php';

class helpController extends AbstractController
{

  public function runStrategy()
  {
    echo <<<HELP

\033[1;34m\033[41m                    MySQL Migration with PHP                         \033[0m
---------------------------------------------------------------------
  \033[32mhelp:\033[0m       display this help and exit
  \033[32mschema:\033[0m     create schema for initial migration/installation
  \033[32minit:\033[0m       load initial schema (install)
  \033[32mcreate:\033[0m     create new migration
  \033[32mmigrate:\033[0m    migrate to specified time
  
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