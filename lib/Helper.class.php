<?php
require_once __DIR__.'/helpController.class.php';

class Helper
{
  static protected $config_tpl = array(
    'config' => array('short' => 'c', 'req_val'),
    'host' => array('req_val'),
    'user' => array('req_val'),
    'password' => array('req_val'),
    'db' => array('req_val'),
    'savedir' => array('req_val'),
    'verbose' => array('req_val'),
    'versiontable' => array('req_val')
  );
  
  static protected $config = array(
    'config' => null, //path to alternate config file
    'host' => null,
    'user' => null,
    'password' => null,
    'db' => null,
    'savedir' => null,
    'verbose' => null,
    'versiontable' => null
  );
  static function setConfig($cnf)
  {
    self::$config = array_replace(self::$config, $cnf);
  }

  static function getConfig()
  {
    return self::$config;
  }

  /**
   * Parse command line into config options and commands with its parameters
   *
   * $param array $args List of arguments provided from command line
   */
  static function parseCommandLineArgs($args)
  {
    $parsed_args = array('options' => array(), 'command' => array('name' => null, 'args' => array()));

    array_shift($args);
    $opts = GetOpt::extractLeft($args, self::$config_tpl);
    if($opts === false)
    {
      Output::error('mmp: ' . reset(GetOpt::errors()));
      exit(1);
    }
    else
      $parsed_args['options'] = $opts;

    //if we didn't traverse the full array just now, move on to command parsing
    if(!empty($args))
      $parsed_args['command']['name'] = array_shift($args);

    //consider any remaining arguments as command arguments
    $parsed_args['command']['args'] = $args;

    return $parsed_args;
  }

  /**
   * Get available controller object
   *
   * With no parameters supplied, returns "help" controller
   *
   * @param string $name Controller name
   * @param array $args Optional controller arguments
   * @return object Initialized controller, False if not found
   */
  static function getController($name=null, $args=array())
  {
    if(empty($name))
      return new helpController;

    $ctrl = $name . 'Controller';
    try
    {
      return new $ctrl(null, $args);
    }
    catch(Exception $e)
    {
      return false;
    }
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

  static function getTmpDbObject()
  {
    $config = self::getConfig();
    $tmpname = $config['db'].'_'.self::getCurrentVersion();
    $config['db'] = $tmpname;
    $db = self::getDbObject();
    $db->query("create database `{$config['db']}`");
    $tmpdb =  self::getDbObject($config);
    $tmpdb->query("SET FOREIGN_KEY_CHECKS = 0");
    register_shutdown_function(function() use($config,$tmpdb)
    {
        Output::verbose("database {$config['db']} has been dropped");
        $tmpdb->query("DROP DATABASE `{$config['db']}`");
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
    return time();
  }

  static function getTables($db)
  {
    $tables = array();
    $result = $db->query('show tables');
    while($row = $result->fetch_array(MYSQLI_NUM))
    {
      $tables[] = $row[0];
    }
    return $tables;
  }

  static function getSqlForTableCreation($tname,$db)
  {
    $tres = $db->query("SHOW CREATE TABLE `{$tname}`");
    $trow = $tres->fetch_array(MYSQLI_NUM);
    $query = preg_replace('#AUTO_INCREMENT=\S+#is', '', $trow[1]);
    $query = preg_replace("#\n\s*#",' ',$query);
    $query = addcslashes($query, '\\\''); //escape slashes and single quotes
    return $query;
  }

  static function getRoutines($db, $type)
  {
    $routines = array();
    $result = $db->query("show $type status where Db=DATABASE()");
    if ( $result === FALSE ) return $routines; // Don't fail if the DB doesn't support STPs
    while($row = $result->fetch_array(MYSQLI_NUM))
    {
      $routines[] = $row[1];
    }
    return $routines;
  }

  static function getSqlForRoutineCreation($rname,$db,$type)
  {
    $tres = $db->query("SHOW CREATE $type `{$rname}`");
    $trow = $tres->fetch_array(MYSQLI_NUM);
    $query = preg_replace('#DEFINER=\S+#is', '', $trow[2]);
    $query = addcslashes($query, '\\\''); //escape slashes and single quotes
    return $query;
  }

  static function getDatabaseVersion(Mysqli $db)
  { 
    $tbl = self::get('versiontable');
    $res = $db->query("SELECT max(rev) FROM `{$tbl}`");
    if($res === false) return false;
    $row = $res->fetch_array(MYSQLI_NUM);
    return intval($row[0]);    
  }

  /**
   * Get all revisions that have been applied to the database
   *
   * @param Mysqli $db Database instance
   * @return array|bool List of applied revisions, False on error
   */
  static function getDatabaseVersions(Mysqli $db)
  {
    $result = array();
    $tbl = self::get('versiontable');
    $res = $db->query("SELECT rev FROM `{$tbl}` ORDER BY rev ASC");
    if ($res === false) return false;

    while($row = $res->fetch_array(MYSQLI_NUM))
      $result[] = $row[0];

    return $result;
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
      echo "File: {$fname} does not exist!\n";
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
