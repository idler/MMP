<?php

class listController extends AbstractController
{

  protected $queries = array();

  public function runStrategy()
  {

    $db = Helper::getDbObject();

    $migrations = Helper::getAllMigrations();

    $revisions = Helper::getDatabaseVersions($db);
    $revision = Helper::getDatabaseVersion($db);

    foreach($migrations as $migration)
    {
      $prefix = ($migration == $revision) ? ' *** ' : '     ';

      //Mark any unapplied revisions
      if($migration < $revision && !in_array($migration, $revisions))
        $prefix .= '[n] ';
      else
        $prefix .= '    ';

      echo $prefix . date('r',$migration) . "\n";
    }
  }
}

