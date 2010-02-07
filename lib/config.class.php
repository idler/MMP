<?php
class Config
{
  static protected $instance = null;
  function __construct()
  {
    $ini = parse_ini_file(__DIR__.'/../config.ini');

  }
  static function instance()
  {
    if(!self::$instance)
      self::$instance = new self;

    return self::$instance;
  }
}
