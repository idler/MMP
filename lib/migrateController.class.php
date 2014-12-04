<?php

class migrateController extends AbstractController
{

  protected $queries = array();

  public function runStrategy()
  {
    $revision = 0;
    $db = Helper::getDbObject();


    if(empty($this->args)) $this->args[] = 'now';

    $str = implode(' ', $this->args);

    $target_migration = strtotime($str);

    if(false === $target_migration) throw new Exception("Time is not correct");

    $migrations = Helper::getAllMigrations();

    $revisions = Helper::getDatabaseVersions($db);
    if($revisions === false) throw new Exception('Could not access revisions table');

    if(!empty($revisions))
    {
      $revision = max($revisions);
    }
    else
    {
      Output::error('Revision table is empty. Initial schema not applied properly?');
      return false;
    }
    
    $unapplied_migrations = array_diff($migrations, $revisions);
    
    if(empty($unapplied_migrations) && $revision == max($migrations) && $target_migration > $revision)
    {
      echo 'No new migrations available' . PHP_EOL;
      return true;
    }
    elseif($revision < min($migrations) && $target_migration < $revision)
    {
      echo 'No older migrations available' . PHP_EOL;
      return true;
    }
    else
    {
      echo "Will migrate to: " . date('r',$target_migration) . PHP_EOL . PHP_EOL;
    }

    $direction = $revision <= $target_migration ? 'Up' : 'Down';

    if($direction === 'Down')
    {
      $migrations = array_reverse($migrations);
      
      foreach($migrations as $migration)
      {
        if($migration>$revision) continue;
        //Rollback only applied revisions, skip the others
        if(!in_array($migration, $revisions)) continue;
        if($migration < $target_migration) break;
        echo "ROLLBACK: " . date('r',$migration) . "\n";
        Helper::applyMigration($migration, $db, $direction);
      }
    }
    else
    {
      foreach($migrations as $migration)
      {
        //Apply previously unapplied revisions to "catch up"
        if($migration<=$revision && in_array($migration, $revisions)) continue;
        if($migration > $target_migration) break;
        echo "APPLY: " . date('r',$migration) . "\n";
        Helper::applyMigration($migration, $db, $direction);
      }
    }

  }
}

