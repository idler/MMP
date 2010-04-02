<?php
require_once __DIR__.'/AbstractController.class.php';

class listController extends AbstractController
{

  protected $queries = array();

  public function runStrategy()
  {

    $db = Helper::getDbObject();

    $migrations = Helper::getAllMigrations();

    $revision = Helper::getDatabaseVersion($db);

    foreach($migrations as $migration)
    {
      $prefix = ($migration == $revision) ? ' *** ' : '     ';
      echo $prefix . date('r',$migration) . "\n";
    }
  }
}

