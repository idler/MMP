<?php
require_once dirname(__FILE__).'/AbstractController.class.php';

class createController extends AbstractController
{

  protected $queries = array();

  public function runStrategy()
  {
    
    $db = Factory::getDbObject();
    $tmpdb = Factory::getTmpDbObject();

    $fname = Factory::get('savedir').'/schema.php';
    if(!file_exists($fname))
    {
      echo "File: {$fname} not exists!\n";
      exit;
    }
   
    require_once $fname;
    $sc = new Schema();
    $sc->load($tmpdb);

    $migrations = $this->getAllMigrations();
    foreach($migrations as $revision){
      $this->applyMigration($revision,$tmpdb);
    }
   
  }

  protected function getAllMigrations()
  {
    $dir = Factory::get('savedir');
    $files = glob($dir.'/migration*.php');
    $result = array();
    foreach($files as $file)
    {
      $key = preg_replace('#[^0-9]#is', '', $file);
      $result[] = $key;
    }
    sort($result,SORT_NUMERIC);
    return $result;
  }

  protected function applyMigration($revision,$db)
  {
    require_once Factory::get('savedir').'/migration'.$revision.'.php';
    $classname = 'Migration'.$revision;
    $migration = new $classname($db);
    $migration->runUp();
  }

}
