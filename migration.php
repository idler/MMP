#!/usr/bin/env php
<?php

require_once __DIR__.'/init.php';

$cnf = __DIR__.'/config.ini';
$config =  file_exists($cnf) ? parse_ini_file($cnf) : array();

require_once(__DIR__.'/lib/Factory.class.php');

Factory::setConfig($config);

$controller = Factory::getController($argv);
$controller->runStrategy();




