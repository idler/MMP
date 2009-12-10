#!/usr/bin/env php
<?php

$cnf = dirname(__FILE__).'/config.ini';
$config =  file_exists($cnf) ? parse_ini_file($cnf) : array();

require_once(dirname(__FILE__).'/lib/Factory.class.php');

Factory::setConfig($config);

$controller = Factory::getController($argv);
$controller->runStrategy();




