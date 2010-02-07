<?php
require_once __DIR__.'/AbstractController.class.php';

class createController extends AbstractController
{

  protected $queries = array();

  public function runStrategy()
  {
    
    $db = Factory::getDbObject();
    $tmpdb = Factory::getTmpDbObject();

    $this->loadTmpDb($tmpdb);

    $diff = new dbDiff($db,$tmpdb);
    $difference = $diff->getDifference();

    $version = Factory::getCurrentVersion();
    $filename = Factory::get('savedir')."/migration{$version}.php";
    $content = $this->createMigrationContent($version,$difference);
    file_put_contents($filename, $content);
    Factory::verbose("file: {$filename} writed!");
    $vTab = Factory::get('versiontable');
    $db->query("INSERT INTO `{$vTab}` SET rev={$version}");
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

  protected function loadTmpDb($db)
  {
    $fname = Factory::get('savedir').'/schema.php';
    if(!file_exists($fname))
    {
      echo "File: {$fname} not exists!\n";
      exit;
    }

    require_once $fname;
    $sc = new Schema();
    $sc->load($db);

    $migrations = $this->getAllMigrations();
    foreach($migrations as $revision){
      Factory::applyMigration($revision,$db);
    }

  }

  protected function createMigrationContent($version,$diff)
  {
      $content = "<?php\n class Migration{$version} extends AbstractMigration\n{\n".
      "  protected \$up = array(\n";
      foreach($diff['up'] as $sql)
      {
        $content .= "    '{$sql}',\n";
      }
      $content .= "  );\n  protected \$down = array(\n";

      foreach($diff['down'] as $sql)
      {
        $content .= "    '{$sql}',\n";
      }
      $content .= "  );\n  protected \$rev = {$version};\n}\n";

      return $content;
  }
}
