<?php
require_once __DIR__.'/helpController.class.php';

class Factory
{
  static protected $config = array();
  static function setConfig($cnf)
  {
    self::$config = $cnf;
  }

  static function getConfig()
  {
    return self::$config;
  }

  static function getController($args=false)
  {
    if(!count(self::$config) || count($args)<2)
      return new HelpController;

    $ctrl = $args[1].'Controller';
    return new $ctrl(null,$args);
  }

  /**
   *
   * @staticvar <type> $db
   * @param array $config
   * @return Mysqli
   */
  static function getDbObject($config=array())
  {
    static $db = null;
    $conf = self::$config;
    if(count($config))
    {
      foreach($config as $option=>$value)
      {
        $conf[$option] = $value;
      }
    }
    else
    {
      if($db) return $db;
      $db = new Mysqli($conf['host'],$conf['user'],$conf['password'],$conf['db']);
      return $db;
    }
    return new Mysqli($conf['host'],$conf['user'],$conf['password'],$conf['db']);
  }

  static function initDirForSavedMigrations()
  {
    if(is_dir(self::$config['savedir'])) return;
    mkdir(self::$config['savedir'], 0755, true);
  }

  static public function get($key)
  {
    return isset(self::$config[$key]) ? self::$config[$key] : false;
  }

  static function verbose($string)
  {
    if(self::get('verbose')) echo $string,"\n";
  }

  static function getTmpDbObject()
  {
    $config = self::getConfig();
    $tmpname = $config['db'].'_'.self::getCurrentVersion();
    $config['db'] = $tmpname;
    $db = self::getDbObject();
    $db->query("create database `{$config['db']}`");
    $tmpdb =  self::getDbObject($config);
    register_shutdown_function(function() use($config,$tmpdb)
    {
        Factory::verbose("database {$config['db']} droped");
        $tmpdb->query("drop database `{$config['db']}`");
      })
    ;
    return $tmpdb;
  }

  static function initVersionTable()
  {
    $db = self::getDbObject();
    $tbl = self::get('versiontable');
    $rev = self::getCurrentVersion();
    $db->query("DROP TABLE IF EXISTS `{$tbl}`");
    $db->query("CREATE TABLE `{$tbl}` (`rev` BIGINT(20) UNSIGNED) ENGINE=MyISAM");
    $db->query("TRUNCATE `{$tbl}`");
    $db->query("INSERT INTO `{$tbl}` VALUES({$rev})");
  }

  static function getCurrentVersion()
  {
    return gmmktime();
  }

  static function getSqlForTableCreation($tname,$db)
  {
    $tres = $db->query("SHOW CREATE TABLE `{$tname}`");
    $trow = $tres->fetch_array(MYSQLI_NUM);
    $query = preg_replace('#AUTO_INCREMENT=\S+#is', '', $trow[1]);
    $query = str_replace("\n",' ',$query);
    $query = str_replace("'", '\\\'', $query);
    return $query;
  }

  static function getDatabaseVersion(Mysqli $db)
  { 
    $tbl = self::get('versiontable');
    $res = $db->query("SELECT max(rev) from `{$tbl}`");
    $row = $res->fetch_array(MYSQLI_NUM);
    return intval($row[0]);    
  }


  static function applyMigration($revision,$db,$direction = 'Up')
  {
    require_once self::get('savedir').'/migration'.$revision.'.php';
    $classname = 'Migration'.$revision;
    $migration = new $classname($db);
    $method = 'run'.$direction;
    $migration->$method();
  }

  static function getAllMigrations()
  {
    $dir = self::get('savedir');
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
  
  static function loadTmpDb($db)
  {
    $fname = self::get('savedir').'/schema.php';
    if(!file_exists($fname))
    {
      echo "File: {$fname} not exists!\n";
      exit;
    }

    require_once $fname;
    $sc = new Schema();
    $sc->load($db);

    $migrations = self::getAllMigrations();
    foreach($migrations as $revision){
      self::applyMigration($revision,$db);
    }

  }

  static function createMigrationContent($version,$diff)
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