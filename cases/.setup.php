<?php
require_once __DIR__.'/../init.php';
$conf = parse_ini_file(dirname(__FILE__).'/config.ini');

require_once __DIR__.'/../lib/Helper.class.php';
Helper::setConfig($conf);

$conn = @Helper::getDbObject();
if ( $conn->connect_error )
{
	die( "Couldn't connect to database ({$conn->connect_errno}) {$conn->connect_error}" );
}

$conn->query( "drop database if exists `{$conf['db']}`" );
$conn->query( "create database `{$conf['db']}`" ) or die( "Couldn't create test database" );
$conn->query( "create table test (id int unsigned not null primary key auto_increment, title varchar(40), description text" );
