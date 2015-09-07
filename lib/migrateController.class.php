<?php

class migrateController extends AbstractController
{

    protected $queries = [];

    /**
     * @param $migration
     * @param $aliases
     *
     * @return string
     */
    protected static function getMigrationAlias($migration, $aliases)
    {
        if (array_key_exists($migration, $aliases)) {
            $current_suffix = Helper::TAB.'('.$aliases[$migration].')';

            return $current_suffix;
        } else {
            $current_suffix = Helper::TAB;

            return $current_suffix;
        }
    }

    protected static function filterValidTimestamp($timestamp)
    {
        return ((is_numeric($timestamp))
                && (+$timestamp <= PHP_INT_MAX)
                && (+$timestamp >= ~PHP_INT_MAX) ? +$timestamp : false);
    }

    public function runStrategy()
    {
        $revision = 0;
        $db       = Helper::getDbObject();


        if (empty($this->args)) {
            $this->args[] = 'now';
        }

        $str = implode(' ', $this->args);

        $target_migration = strtotime($str);

        if (false === $target_migration) { //did not specify valid time string, check if specified a timestamp
            $target_migration = self::filterValidTimestamp($str);
            if (false === $target_migration) { //did not specify valid timestamp, check if specified an alias
                $target_migration = self::filterValidTimestamp(Helper::getRevByAlias($str));
                if (false === $target_migration) { //did not specify valid alias
                    throw new Exception("Time (or alias) is not correct");
                }
            }
        }

        $migrations   = Helper::getAllMigrations();
        $aliases      = Helper::getDatabaseAliases($db);
        $revisions    = Helper::getDatabaseVersions($db);
        $target_alias = $migration_alias = Helper::TAB;


        if ($revisions === false) {
            throw new Exception('Could not access revisions table');
        }

        if (!empty($revisions)) {
            $revision = max($revisions);
        } else {
            Output::error('Revision table is empty. Initial schema not applied properly?');
            return false;
        }

        if (array_key_exists($target_migration, $aliases)) {
            $target_alias .= '('.$aliases[$target_migration].')';
        }

        $unapplied_migrations = array_diff($migrations, $revisions);

        if (empty($migrations) || (empty($unapplied_migrations) && $revision == max($migrations) && $target_migration > $revision)) {
            echo 'No new migrations available'.PHP_EOL;
            return true;
        } elseif ($revision < min($migrations) && $target_migration < $revision) {
            echo 'No older migrations available'.PHP_EOL;
            return true;
        } else {
            if ($target_migration == $revision) {
                echo 'Target migration is the current revision:  '.date('r', $target_migration).$target_alias.PHP_EOL;
                return true;
            } else {
                echo "Will migrate: ".date('r',$revision).self::getMigrationAlias($revision, $aliases) . ' ---> ' . date('r', $target_migration).self::getMigrationAlias($target_migration, $aliases).PHP_EOL.PHP_EOL;
            }
        }

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
                echo str_pad('ROLLBACK:',10).date('r', $migration).self::getMigrationAlias($migration, $aliases).PHP_EOL;
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
                echo str_pad('APPLY:',10).date('r', $migration).self::getMigrationAlias($migration, $aliases).PHP_EOL;
                Helper::applyMigration($migration, $db, $direction);
            }
        }
        $current_revision = Helper::getDatabaseVersion($db);
        echo str_pad('NOW AT:',10).date('r', $current_revision).self::getMigrationAlias($current_revision, $aliases).PHP_EOL;
    }
}

