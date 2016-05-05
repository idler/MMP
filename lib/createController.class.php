<?php

class createController extends AbstractController
{
    protected $queries = array();

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
        $alias    = Helper::getCurrentAlias();
        $filename = Helper::get('savedir')."/migration{$version}.php";
        $content  = Helper::createMigrationContent($version, $alias, $difference);
        file_put_contents($filename, $content);
        Output::verbose("file: {$filename} written!");
        $vTab = Helper::get('versiontable');
        $db->query("INSERT INTO `{$vTab}` SET rev={$version}");
        $aTab = Helper::get('aliastable');
        if (false !== $alias && false !== $aTab) {
            $db->query("INSERT INTO `{$aTab}` SET rev={$version}, alias='{$alias}'");
        }

        return true;
    }
}