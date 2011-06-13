#!/usr/bin/env php
<?php

require_once __DIR__.'/init.php';

$cli_params = Helper::parseCommandLineArgs($argv);

if(empty($cli_params['options']['config']))
{
  $cnf = __DIR__.'/config.ini';
  $cli_params['options']['config'] = $cnf;
}
else
{
  $cnf = $cli_params['options']['config'];
}

$config = file_exists($cnf) ? parse_ini_file($cnf) : array();
$config = array_replace($config, $cli_params['options']); //command line overrides everything

Helper::setConfig($config);

$controller = Helper::getController($cli_params['command']['name'], $cli_params['command']['args']);
$controller->runStrategy();

