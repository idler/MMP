<?php

define('DIR_SEP', DIRECTORY_SEPARATOR);

set_include_path(__DIR__ . PATH_SEPARATOR . __DIR__ . DIR_SEP . 'lib' . DIR_SEP);

spl_autoload_register('mmpAutoload');

function mmpAutoload($class)
{
  #if(!class_exists(__DIR__.'/lib/'.$class.'.class.php'))
  $paths = explode(PATH_SEPARATOR, get_include_path());

  while($path = array_shift($paths))
  {
    $filename = $path . DIR_SEP . $class . '.class.php';
    if(file_exists($filename))
    {
      require_once $filename;
      return true;
    }
  }
}

