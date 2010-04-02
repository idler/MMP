#!/usr/bin/env php
<?php

require_once __DIR__.'/init.php';

$cnf = __DIR__.'/config.ini';
$config =  file_exists($cnf) ? parse_ini_file($cnf) : array();

//require_once(__DIR__.'/lib/Helper.class.php');

Helper::setConfig($config);

$controller = Helper::getController($argv);
$controller->runStrategy();

