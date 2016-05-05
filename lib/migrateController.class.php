<?php

class migrateController extends AbstractController
{
    protected $queries = array();

    /**
     * @return bool
     * @throws Exception
     */
    public function runStrategy()
    {
        $db = Helper::getDbObject();
        if (empty($this->args)) {
            $this->args[] = 'now';
        }
        $str              = implode(' ', $this->args);
        $target_migration = strtotime($str);
        if (false === $target_migration) {
            //did not specify valid time string, check if specified a timestamp
            $target_migration = self::filterValidTimestamp($str);
            if (false === $target_migration) {
                //did not specify valid timestamp, check if specified an alias
                $target_migration = self::filterValidTimestamp(Helper::getRevByAlias($str));
                if (false === $target_migration) {
                    //did not specify valid alias
                    throw new Exception('Time (or alias) is not correct');
                }
            }
        }
        $migrations = Helper::getAllMigrations();
        $revisions  = Helper::getDatabaseVersions($db);
        if ($revisions === false) {
            throw new Exception('Could not access revisions table');
        }
        if (empty($revisions)) {
            Output::error('Revision table is empty. Initial schema not applied properly?');

            return false;
        }
        $revision             = max($revisions);
        $unapplied_migrations = array_diff($migrations, $revisions);
        if (empty($migrations) || empty($unapplied_migrations) && $revision == max($migrations) && $target_migration > $revision) {
            echo '[31m'.'No new migrations available'.'[37m'.PHP_EOL;

            return true;
        }
        if ($revision < min($migrations) && $target_migration < $revision) {
            echo '[31m'.'No older migrations available'.'[37m'.PHP_EOL;

            return true;
        }
        if ($target_migration == $revision) {
            echo '[31m'.'Target migration is the current revision:  '.'[37m'.date('r', $target_migration).self::getMigrationAlias($target_migration).PHP_EOL;

            return true;
        }
        echo 'Will migrate: '.date('r', $revision).self::getMigrationAlias($revision).' ---> '.date('r', $target_migration).self::getMigrationAlias($target_migration).PHP_EOL.PHP_EOL;
        $direction = $revision <= $target_migration ? Helper::UP : Helper::DOWN;
        if ($direction === Helper::DOWN) {
            $migrations = array_reverse($migrations);
            foreach ($migrations as $migration) {
                if ($migration > $revision) {
                    continue;
                }
                //Rollback only applied revisions, skip the others
                if (!in_array($migration, $revisions)) {
                    continue;
                }
                if ($migration <= $target_migration) {
                    break;
                }
                echo str_pad('ROLLBACK:', 10).date('r', $migration).self::getMigrationAlias($migration).PHP_EOL;
                Helper::applyMigration($migration, $db, $direction);
            }
        } else {
            foreach ($migrations as $migration) {
                //Apply previously unapplied revisions to "catch up"
                if ($migration <= $revision && in_array($migration, $revisions)) {
                    continue;
                }
                if ($migration > $target_migration) {
                    break;
                }
                echo str_pad('APPLY:', 10).date('r', $migration).self::getMigrationAlias($migration).PHP_EOL;
                Helper::applyMigration($migration, $db, $direction);
            }
        }
        $current_revision = Helper::getDatabaseVersion($db);
        echo str_pad('NOW AT:', 10).date('r', $current_revision).self::getMigrationAlias($current_revision).PHP_EOL;

        return true;
    }

    protected static function filterValidTimestamp($timestamp)
    {
        return is_numeric($timestamp) && +$timestamp <= PHP_INT_MAX && +$timestamp >= ~PHP_INT_MAX ? +$timestamp : false;
    }

    /**
     * @param $migration String|Integer
     *
     * @return string
     * @internal param $aliases
     *
     */
    protected static function getMigrationAlias($migration)
    {
        static $aliases = null;
        if ($aliases === null) {
            $aliases = Helper::getDatabaseAliases();
        }
        if (array_key_exists($migration, $aliases)) {
            $current_suffix = Helper::TAB.'([32m'.$aliases[$migration].'[37m)';

            return $current_suffix;
        } else {
            $current_suffix = Helper::TAB;

            return $current_suffix;
        }
    }
}