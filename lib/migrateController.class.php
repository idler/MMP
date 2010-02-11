<?php
require_once __DIR__.'/AbstractController.class.php';

class migrateController extends AbstractController
{

  protected $queries = array();

  public function runStrategy()
  {

    $db = Factory::getDbObject();


    if(count($this->args) < 3) $this->args[2] = 'now';

    array_shift($this->args);
    array_shift($this->args);

    $str = implode(' ', $this->args);

    $target_migration = strtotime($str);
    echo "Migrating to ".date('r',$target_migration)."\n";

    if(false === $target_migration) throw new Exception("Time is not correct");

    $migrations = Factory::getAllMigrations();

    $revision = Factory::getDatabaseVersion($db);

    $direction = $revision <= $target_migration ? 'Up' : 'Down';

    if($direction === 'Down')
    {
      $migrations = array_reverse($migrations);
      
      foreach($migrations as $migration)
      { echo "$migration\n";
        if($migration>$revision) continue;        
        if($migration < $target_migration) break;
        Factory::applyMigration($migration, $db, $direction);
      }

    }
    else
    {
      foreach($migrations as $migration)
      {
        if($migration<=$revision) continue;
        if($migration > $target_migration) break;
        Factory::applyMigration($migration, $db, $direction);
      }
    }

  }
}

