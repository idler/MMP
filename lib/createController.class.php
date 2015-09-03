<?php

class createController extends AbstractController
{

    protected $queries = [];

    public function runStrategy()
    {

        $db    = Helper::getDbObject();
        $tmpdb = Helper::getTmpDbObject();

        Helper::loadTmpDb($tmpdb);

        $diff       = new dbDiff($db, $tmpdb);
        $difference = $diff->getDifference();
        if (!count($difference['up']) && !count($difference['down'])) {
            echo 'Your database has no changes from last revision'.PHP_EOL;

            return false;
        }

        $version  = Helper::getCurrentVersion();
        $filename = Helper::get('savedir')."/migration{$version}.php";
        $content  = Helper::createMigrationContent($version, $difference);
        file_put_contents($filename, $content);
        Output::verbose("file: {$filename} written!");
        $vTab = Helper::get('versiontable');
        $db->query("INSERT INTO `{$vTab}` SET rev={$version}");

        return true;
    }

}
