<?php
require_once __DIR__ . '/AbstractController.class.php';

class createController extends AbstractController
{
  
  protected $queries = array();
  
  public function runStrategy()
  {

    $db = Factory::getDbObject();
    $tmpdb = Factory::getTmpDbObject();

    Factory::loadTmpDb($tmpdb);

    $diff = new dbDiff($db, $tmpdb);
    $difference = $diff->getDifference();
    if (!count($difference['up']) && !count($difference['down']))
    {
      echo "Your database have no changes from last version\n";
      exit(0);
    }

    $version = Factory::getCurrentVersion();
    $filename = Factory::get('savedir') . "/migration{$version}.php";
    $content = Factory::createMigrationContent($version, $difference);
    file_put_contents($filename, $content);
    Factory::verbose("file: {$filename} writed!");
    $vTab = Factory::get('versiontable');
    $db->query("INSERT INTO `{$vTab}` SET rev={$version}");
  }

}
