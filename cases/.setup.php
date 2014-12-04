<?php
require_once __DIR__.'/../init.php';
$conf = parse_ini_file(dirname(__FILE__).'/config.ini');
$conf['savedir'] = __DIR__ . '/temp_data/';
$conf['forceyes'] = TRUE;
$conf['noninteractive'] = TRUE;
exec( "rm -rf " . escapeshellarg($conf['savedir']) );
@mkdir( $conf['savedir'], 0777, TRUE );

require_once __DIR__.'/../lib/Helper.class.php';
Helper::setConfig($conf);

$conn = @Helper::getDbObject();
if ( $conn->connect_error )
{
	die( "Couldn't connect to database ({$conn->connect_errno}) {$conn->connect_error}" );
}

$conn->query( "drop database if exists `{$conf['db']}`" );
$conn->query( "create database `{$conf['db']}`" ) or die( "Couldn't create test database" );
$conn->query( "use `{$conf['db']}`");
$conn->query( "create table test (id int unsigned not null primary key auto_increment, title varchar(40), description text)" );

require_once '.dbTestCase.class.php';
