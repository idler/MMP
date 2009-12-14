<?php
require_once dirname(__FILE__).'/helpController.class.php';

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
    return new $ctrl(null);
  }

  /**
   *
   * @staticvar <type> $db
   * @param <type> $config
   * @return Mysqli 
   */
  static function getDbObject($config=array())
  {
    static $db = null;
    $conf = self::$config;
    if(count($config)){
      foreach($config as $option=>$value)
      {
        $conf[$option] = $value;
      }
    }else{
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
    if(Factory::get('verbose')) echo $string;
  }
}