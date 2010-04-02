<?php
require_once __DIR__ . '/AbstractController.class.php';

class createController extends AbstractController
{
  
  protected $queries = array();
  
  public function runStrategy()
  {

    $db = Helper::getDbObject();
    $tmpdb = Helper::getTmpDbObject();

    Helper::loadTmpDb($tmpdb);

    $diff = new dbDiff($db, $tmpdb);
    $difference = $diff->getDifference();
    if (!count($difference['up']) && !count($difference['down']))
    {
      echo "Your database have no changes from last version\n";
      exit(0);
    }

    $version = Helper::getCurrentVersion();
    $filename = Helper::get('savedir') . "/migration{$version}.php";
    $content = Helper::createMigrationContent($version, $difference);
    file_put_contents($filename, $content);
    Helper::verbose("file: {$filename} writed!");
    $vTab = Helper::get('versiontable');
    $db->query("INSERT INTO `{$vTab}` SET rev={$version}");
  }

}
