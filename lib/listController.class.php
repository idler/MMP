<?php

class listController extends AbstractController
{
    protected $queries = array();

    public function runStrategy()
    {
        $db         = Helper::getDbObject();
        $migrations = Helper::getAllMigrations();
        $revisions  = Helper::getDatabaseVersions($db);
        $revision   = Helper::getDatabaseVersion($db);
        $aliases    = Helper::getDatabaseAliases();
        foreach ($migrations as $migration) {
            $prefix = $migration == $revision ? ' *** ' : '     ';
            $suffix = '    ';
            //Mark any unapplied revisions
            if ($migration < $revision && !in_array($migration, $revisions)) {
                $prefix .= '[n] ';
            } else {
                $prefix .= '    ';
            }
            if (array_key_exists($migration, $aliases)) {
                $suffix .= '('.$aliases[$migration].')';
            }
            echo $prefix.date('r', $migration).$suffix.'
';
        }
    }
}