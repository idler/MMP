<?php
class Config
{
  static protected $instance = null;
  function __construct()
  {
    $ini = parse_ini_file(dirname(__FILE__).'/../config.ini');

  }
  static function instance()
  {
    if(!self::$instance)
      self::$instance = new self;

    return self::$instance;
  }
}