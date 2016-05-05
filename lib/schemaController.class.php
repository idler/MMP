<?php

class schemaController extends AbstractController
{
    protected $queries = array();

    public function runStrategy()
    {
        Helper::initDirForSavedMigrations();
        Helper::initVersionTable();
        $db = Helper::getDbObject();
        foreach (Helper::getTables($db) as $table) {
            $query           = Helper::getSqlForTableCreation($table, $db);
            $this->queries[] = "DROP TABLE IF EXISTS `{$table}`";
            $this->queries[] = $query;
        }
        foreach (Helper::getRoutines('PROCEDURE') as $routine) {
            $query           = Helper::getSqlForRoutineCreation($routine, $db, 'PROCEDURE');
            $this->queries[] = "DROP PROCEDURE IF EXISTS `{$routine}`";
            $this->queries[] = $query;
        }
        foreach (Helper::getRoutines('FUNCTION') as $routine) {
            $query           = Helper::getSqlForRoutineCreation($routine, $db, 'FUNCTION');
            $this->queries[] = "DROP FUNCTION IF EXISTS `{$routine}`";
            $this->queries[] = $query;
        }
        $vtab            = Helper::get('versiontable');
        $res             = $db->query("SELECT MAX(rev) FROM `{$vtab}`");
        $row             = $res->fetch_array(MYSQLI_NUM);
        $maxrev          = $row[0];
        $this->queries[] = "INSERT INTO `{$vtab}` SET rev={$maxrev}";
        $atab            = Helper::get('aliastable');
        if (false !== $atab) {
            $aprefix         = Helper::get('aliasprefix') ?: '';
            $res             = $db->query("SELECT `alias` FROM `{$atab}` WHERE `rev` = {$maxrev}");
            $row             = $res->fetch_array(MYSQLI_NUM);
            $alias           = isset($row[0]) && $row[0] !== null ? $row[0] : "{$aprefix}0";
            $this->queries[] = "INSERT INTO `{$atab}` SET rev={$maxrev}, alias='{$alias}'";
        }

        return $this->writeInFile();
    }

    protected function writeInFile()
    {
        $content = Helper::createSchema($this->queries);
        $fname   = Helper::get('savedir').'/schema.php';
        if (!$this->askForRewrite($fname)) {
            return false;
        }

        return file_put_contents($fname, $content);
    }

    protected function askForRewrite($fname)
    {
        if (!file_exists($fname)) {
            return true;
        }
        if (intval(Helper::get('forceyes'))) {
            return true;
        }
        if (intval(Helper::get('noninteractive'))) {
            return false;
        }
        $c = '';
        do {
            if ($c != '
'
            ) {
                echo "File: {$fname} already exists! Can I rewrite it [y/n]? ";
            }
            $c = fread(STDIN, 1);
            if ($c === 'Y' or $c === 'y') {
                return true;
            }
            if ($c === 'N' or $c === 'n') {
                echo '
Exit without saving
';

                return false;
            }
        } while (true);

        return true;
    }
}