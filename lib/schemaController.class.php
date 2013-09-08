<?php

class schemaController extends AbstractController
{

  protected $queries = array();

  public function runStrategy()
  {
    Helper::initDirForSavedMigrations();
    Helper::initVersionTable();
  
    $db = Helper::getDbObject();

    foreach( Helper::getTables($db) as $table )
    {
      $query=Helper::getSqlForTableCreation($table, $db);
      $this->queries[] = "DROP TABLE IF EXISTS `{$table}`";
      $this->queries[] = $query;
    }

    foreach( Helper::getRoutines($db, "PROCEDURE") as $routine )
    {
      $query=Helper::getSqlForRoutineCreation($routine, $db, 'PROCEDURE');
      $this->queries[] = "DROP PROCEDURE IF EXISTS `{$routine}`";
      $this->queries[] = $query;
    }

    foreach( Helper::getRoutines($db, "FUNCTION") as $routine )
    {
      $query=Helper::getSqlForRoutineCreation($routine, $db, 'FUNCTION');
      $this->queries[] = "DROP FUNCTION IF EXISTS `{$routine}`";
      $this->queries[] = $query;
    }

    $vtab = Helper::get('versiontable');
    $res = $db->query("SELECT MAX(rev) FROM `{$vtab}`");
    $row = $res->fetch_array(MYSQLI_NUM);
    $this->queries[] = "INSERT INTO `{$vtab}` SET rev={$row[0]}";
    $this->writeInFile();
  }

  protected function writeInFile()
  {
    $content = "<?php\n".
      "class Schema extends AbstractSchema\n".
      "{\n".
      "  protected \$queries = array(\n";
      foreach($this->queries as $q)
      { 
      	$q=str_replace('"', '\"', $q);
        $content .= "    \"{$q}\",\n";
      }
      $content.="  );\n".
      "}\n".
      "\n";
      $fname = Helper::get('savedir').'/schema.php';
      $this->askForRewrite($fname);
      file_put_contents($fname, $content);
  }

  protected function askForRewrite($fname)
  {
    if(!file_exists($fname)) return;
    $c='';
    do{
      if($c!="\n") echo "File: {$fname} already exists! Can I rewrite it [y/n]? ";
      $c = fread(STDIN, 1);

      if($c ==='Y' or $c==='y' ){
        return;
      }
      if($c ==='N' or $c==='n' ){
        echo "\nExit without saving\n"; exit;
      }

    }while(true);
  }
  

}
