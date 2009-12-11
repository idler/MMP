<?php

set_include_path(__DIR__);
function mmpAutoload($class)
{
  if(!file_exists('lib/'.$class.'.class.php')) throw new Exception("# class {$class} not found \n");
  require_once 'lib/'.$class.'.class.php';
}

spl_autoload_register('mmpAutoload');