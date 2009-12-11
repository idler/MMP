<?php
require_once __DIR__.'/../init.php';
$conf = parse_ini_file(dirname(__FILE__).'/config.ini');

require_once __DIR__.'/../lib/Factory.class.php';
Factory::setConfig($conf);

mysql_connect($conf['host'],$conf['user'],$conf['password']);
mysql_query("drop database if exists `".$conf['db']."`");
mysql_query("create database `".$conf['db']."`");

mysql_query("create table test (id int unsigned not null primary key auto_increment, title varchar(40), description text");


