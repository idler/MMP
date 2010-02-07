<?php

set_include_path(__DIR__);

spl_autoload_register('mmpAutoload');

function mmpAutoload($class)
{
  if(!file_exists(__DIR__.'/lib/'.$class.'.class.php')) 
    throw new Exception("# class {$class} not found \n");
    
  require_once __DIR__.'/lib/'.$class.'.class.php';
}

