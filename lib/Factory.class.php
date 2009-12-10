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
      require_once dirname(__FILE__).'/'.$ctrl.'.class.php';
    return new $ctrl(null);
  }

}