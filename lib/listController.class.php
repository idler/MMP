<?php
require_once __DIR__.'/AbstractController.class.php';

class listController extends AbstractController
{

  protected $queries = array();

  public function runStrategy()
  {

    $db = Helper::getDbObject();


    //if(count($this->args) < 3) $this->args[2] = 'now';
//
//    array_shift($this->args);
//    array_shift($this->args);

//    $str = implode(' ', $this->args);

//    $target_migration = strtotime($str);
//    echo "Migrating to ".date('r',$target_migration)."\n";

//    if(false === $target_migration) throw new Exception("Time is not correct");

    $migrations = Helper::getAllMigrations();

    $revision = Helper::getDatabaseVersion($db);

//    $direction = $revision <= $target_migration ? 'Up' : 'Down';

//    if($direction === 'Down')
//    {
//      $migrations = array_reverse($migrations);
//
      foreach($migrations as $migration)
      { 
        $prefix = ($migration == $revision) ? ' *** ' : '     ';
        echo $prefix . date('r',$migration) . "\n";
      }

//    }
//    else
//    {
//      foreach($migrations as $migration)
//      {
//        if($migration<=$revision) continue;
//        if($migration > $target_migration) break;
//        Helper::applyMigration($migration, $db, $direction);
//      }
//    }

  }
}

