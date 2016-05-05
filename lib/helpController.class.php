<?php

class helpController extends AbstractController
{
    public function runStrategy()
    {
        $output
            = '
[1;34m[41m                    MySQL Migration with PHP                         [0m
---------------------------------------------------------------------
Usage:
  ./migrate.php [options] command [command arguments]

Available commands:

  [1;32mhelp:[0m       display this help and exit
  [1;32mschema:[0m     create schema for initial migration/installation
  [1;32minit:[0m       load initial schema (install)
  [1;32madd:[0m        add existig database to versioning (keep data alive)
  [1;32mcreate:[0m     create new migration
  [1;32mlist:[0m       list available migrations and mark current version
  [1;32mmigrate:[0m    migrate to specified time
  
Available options:

  --config              Path to alternate config.ini file that will override the default
  --host                database host
  --user                grant this user DDL modify access
  --password            if empty leave it blank: --password=
  --db                  database name
  --savedir             directory to save schema.php and migrations
  --verbose             if value On script will output each executed query
  --versiontable        table to keep db revisions new row for each revision
  --versiontable-engine table engine for versiontable (MyISAM, InnoDB)
  --aliastable          table to keep human-readable aliases for each revision
  --aliasprefix         prefix for each human-readable alias
  
For migrate command you can use strtotime format, integer timestamps, or aliases
Examples:
*********************************************************************
./migrate.php migrate yesterday
./migrate.php migrate -2 hour
./migrate.php migrate +2 month
./migrate.php migrate 20 September 2001
./migrate.php migrate 1441402159
./migrate.php migrate my_alias_prefix_v5
./migrate.php migrate
********************************************************************
Last example will update your database to latest version


---------------------------------------------------------------------
Licenced under: GPL v3
Author: Maxim Antonov <max.antonoff@gmail.com>
Author: Sergey Arbuzov <info@whitediver.com>  		

';
        //Strip color output since Windows doesn't support it
        if (PHP_OS === 'WINNT') {
            $output = preg_replace('/\\033\\[\\d+(;\\d+)?m/i', '', $output);
        }
        echo $output;
    }
}