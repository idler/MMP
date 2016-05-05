<?php

class Helper
{
    const TAB  = '  ';
    const UP   = 'Up';
    const DOWN = 'Down';
    protected static $config_tpl
        = array(
            'config'              => array('short' => 'c', 'req_val'),
            'host'                => array('req_val'),
            'user'                => array('req_val'),
            'password'            => array('req_val'),
            'db'                  => array('req_val'),
            'savedir'             => array('req_val'),
            'verbose'             => array('req_val'),
            'versiontable'        => array('req_val'),
            'versiontable-engine' => array('opt_val'),
            'aliastable'          => array('opt_val'),
            'aliasprefix'         => array('opt_val'),
            'forceyes'            => array('opt_val'),
            'noninteractive'      => array('opt_val'),
            'noprepost'           => array('opt_val')
        );
    protected static $config
        = array(
            'config'              => null,
            'host'                => null,
            'user'                => null,
            'password'            => null,
            'db'                  => null,
            'savedir'             => null,
            'verbose'             => null,
            'versiontable'        => null,
            'aliastable'          => null,
            'aliasprefix'         => null,
            'versiontable-engine' => 'MyISAM',
            'forceyes'            => false,
            'noninteractive'      => false,
            'noprepost'           => false
        );

    /**
     * Parse command line into config options and commands with its parameters
     *
     * $param array $args List of arguments provided from command line
     *
     * @param $args
     *
     * @return array
     */
    static function parseCommandLineArgs($args)
    {
        $parsed_args = array('options' => array(), 'command' => array('name' => null, 'args' => array()));
        array_shift($args);
        $opts = GetOpt::extractLeft($args, self::$config_tpl);

        if ($opts === false) {
            Output::error('mmp: '.reset(GetOpt::errors()));
            die(1);
        } else {
            $parsed_args['options'] = $opts;
        }
        //if we didn't traverse the full array just now, move on to command parsing
        if (!empty($args)) {
            $parsed_args['command']['name'] = array_shift($args);
        }
        //consider any remaining arguments as command arguments
        $parsed_args['command']['args'] = $args;

        return $parsed_args;
    }

    /**
     * Checks has we enough params to run
     *
     * @return boolean
     */
    static function checkConfigEnough()
    {
        foreach (self::$config as $key => $value) {
            if ($key != 'config' && is_null($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get available controller object
     *
     * With no parameters supplied, returns "help" controller
     *
     * @param string $name Controller name
     * @param array  $args Optional controller arguments
     *
     * @return object Initialized controller, False if not found
     */
    static function getController($name = null, $args = array())
    {
        if (empty($name)) {
            return new helpController();
        }
        $ctrl = $name.'Controller';
        if (!class_exists($ctrl)) {
            return false;
        }
        try {
            return new $ctrl(null, $args);
        } catch (Exception $e) {
            return false;
        }
    }

    static function initDirForSavedMigrations()
    {
        if (is_dir(self::$config['savedir'])) {
            return;
        }
        if (self::$config['savedir'][0]==='~'){
            self::$config['savedir'] = str_replace('~',getenv('HOME'),self::$config['savedir']);
        }
        if (is_dir(self::$config['savedir'])) {
            return;
        }
        mkdir(self::$config['savedir'], 0755, true);
    }
    static function getTmpDbObject()
    {
        $config       = self::getConfig();
        $tmpname      = $config['db'].'_'.self::getCurrentVersion();
        $config['db'] = $tmpname;
        $db           = self::getDbObject();
        $db->query("create database `{$config['db']}`");
        $tmpdb = self::getDbObject($config);
        $tmpdb->query('SET FOREIGN_KEY_CHECKS = 0');
        register_shutdown_function(function () use ($config, $tmpdb) {
            Output::verbose("database {$config['db']} has been dropped");
            $tmpdb->query("DROP DATABASE `{$config['db']}`");
        });

        return $tmpdb;
    }

    static function getConfig()
    {
        return self::$config;
    }

    static function setConfig($cnf)
    {
        self::$config = array_replace(self::$config, $cnf);

    }

    static function getCurrentVersion()
    {
        return time();
    }

    /**
     *
     * @staticvar <type> $db
     *
     * @param array $config
     *
     * @return Mysqli
     */
    static function getDbObject($config = array())
    {
        static $db = null;
        $conf = self::$config;
        if (count($config)) {
            foreach ($config as $option => $value) {
                $conf[$option] = $value;
            }
        } else {
            if ($db) {
                return $db;
            }
            $db = new Mysqli($conf['host'], $conf['user'], $conf['password'], $conf['db']);

            return $db;
        }

        return new Mysqli($conf['host'], $conf['user'], $conf['password'], $conf['db']);
    }

    static function initVersionTable()
    {
        $engine = self::get('versiontable-engine');
        if (!in_array($engine, array('MyISAM', 'InnoDB'))) {
            Output::error('mmp: wrong engine for versiontable "'.$engine.'"');
            die(1);
        }
        $db  = self::getDbObject();
        $tbl = self::get('versiontable');
        $rev = self::getCurrentVersion();
        $db->query("DROP TABLE IF EXISTS `{$tbl}`");
        $db->query("CREATE TABLE `{$tbl}` (`rev` BIGINT(20) UNSIGNED, PRIMARY KEY(`rev`)) ENGINE={$engine}");
        $db->query("TRUNCATE `{$tbl}`");
        $db->query("INSERT INTO `{$tbl}` VALUES({$rev})");
        self::initAliasTable($rev);
    }

    public static function get($key)
    {
        return isset(self::$config[$key]) ? self::$config[$key] : false;
    }

    static function initAliasTable($rev, $alias_suffix = 1)
    {
        $engine = self::get('versiontable-engine');
        if (!in_array($engine, array('MyISAM', 'InnoDB'))) {
            Output::error('mmp: wrong engine for versiontable "'.$engine.'"');
            die(1);
        }
        $db  = self::getDbObject();
        $tbl = self::get('aliastable');
        if (false === $tbl) {
            return;
        }
        $alias_prefix = self::get('aliasprefix') ?: '';
        $alias        = $alias_prefix.$alias_suffix;
        $db->query("DROP TABLE IF EXISTS `{$tbl}`");
        $db->query("CREATE TABLE `{$tbl}` (`rev` BIGINT(20) UNSIGNED, `alias` VARCHAR(32), PRIMARY KEY(`rev`)) ENGINE={$engine}");
        $db->query("TRUNCATE `{$tbl}`");
        $db->query("INSERT INTO `{$tbl}` VALUES({$rev},'{$alias}')");
    }

    static function getCurrentAlias()
    {
        if (false === self::get('aliastable')) {
            return false;
        }

        return (self::get('aliasprefix') ?: '').(string)(Helper::getMaxAliasVersion() + 1);
    }

    static function getMaxAliasVersion()
    {
        $db  = self::getDbObject();
        $tbl = self::get('aliastable');
        if (false === $tbl) {
            return false;
        }
        $alias_prefix = self::get('aliasprefix') ?: '';
        $res          = $db->query("SELECT MAX(REPLACE(`alias`,'{$alias_prefix}','')) FROM `{$tbl}`");
        $row          = $res->fetch_array(MYSQLI_NUM);
        $max_alias    = +$row[0] ?: 0;

        return $max_alias;
    }

    static function geAliasVersionByRev($rev)
    {
        $db  = self::getDbObject();
        $tbl = self::get('aliastable');
        if (false === $tbl) {
            return false;
        }
        $alias_prefix = self::get('aliasprefix') ?: '';
        $res          = $db->query("SELECT REPLACE(`alias`,'{$alias_prefix}','') FROM `{$tbl}` WHERE `rev` = {$rev}");
        $row          = $res->fetch_array(MYSQLI_NUM);
        if (isset($row[0])) {
            return +$row[0];
        } else {
            return null;
        }
    }

    static function getRevByAlias($alias)
    {
        $db  = self::getDbObject();
        $tbl = self::get('aliastable');
        if (false === $tbl) {
            return false;
        }
        if (is_numeric($alias)) {
            $alias = (self::get('aliasprefix') ?: '').$alias;
        }
        $res = $db->query("SELECT `rev` FROM `{$tbl}` WHERE `alias` = '{$alias}'");
        $row = $res->fetch_array(MYSQLI_NUM);
        if (isset($row[0])) {
            return +$row[0];
        } else {
            return null;
        }
    }

    static function getTables(Mysqli $db)
    {
        $tables = array();
        $result = $db->query('show tables');
        if (!$result) {
            return array();
        }
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $tables[] = $row[0];
        }

        return $tables;
    }

    /**
     *
     * @param String $tname
     * @param Mysqli $db
     *
     * @return mixed
     */
    static function getSqlForTableCreation($tname, $db)
    {
        $tres  = $db->query("SHOW CREATE TABLE `{$tname}`");
        $trow  = $tres->fetch_array(MYSQLI_NUM);
        $query = preg_replace('#AUTO_INCREMENT=\\S+#is', '', $trow[1]);

        //$query = preg_replace("#\n\s*#",' ',$query);
        return $query;
    }

    static function getRoutines($type)
    {
        $routines = array();
        $db       = self::getDbObject();
        $result   = $db->query("show {$type} status where Db=DATABASE()");
        if ($result === false) {
            return $routines;
        }
        // Don't fail if the DB doesn't support STPs
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $routines[] = $row[1];
        }

        return $routines;
    }

    static function getSqlForRoutineCreation($rname, Mysqli $db, $type)
    {
        $tres  = $db->query("SHOW CREATE {$type} `{$rname}`");
        $trow  = $tres->fetch_array(MYSQLI_NUM);
        $query = preg_replace('#DEFINER=\\S+#is', '', $trow[2]);

        return $query;
    }

    static function getDatabaseVersion(Mysqli $db)
    {
        $tbl = self::get('versiontable');
        $res = $db->query("SELECT max(rev) FROM `{$tbl}`");
        if ($res === false) {
            return false;
        }
        $row = $res->fetch_array(MYSQLI_NUM);

        return intval($row[0]);
    }

    /**
     * Get all revisions that have been applied to the database
     *
     * @param Mysqli $db Database instance
     *
     * @return array|bool List of applied revisions, False on error
     */
    static function getDatabaseVersions(Mysqli $db)
    {
        $result = array();
        $tbl    = self::get('versiontable');
        $res    = $db->query("SELECT rev FROM `{$tbl}` ORDER BY rev ASC");
        if ($res === false) {
            return false;
        }
        while ($row = $res->fetch_array(MYSQLI_NUM)) {
            $result[] = $row[0];
        }

        return $result;
    }

    static function getDatabaseAliases()
    {
        $result = array();
        $db     = self::getDbObject();
        $tbl    = self::get('aliastable');
        if (false === $tbl) {
            return array();
        }
        $res = $db->query("SELECT rev, alias FROM `{$tbl}` ORDER BY rev ASC");
        if ($res === false) {
            return array();
        }
        while ($row = $res->fetch_array(MYSQLI_NUM)) {
            $result[trim($row[0])] = trim($row[1]);
        }

        return $result;
    }

    static function loadTmpDb($db)
    {
        $fname = self::get('savedir').'/schema.php';
        if (!file_exists($fname)) {
            echo "File: {$fname} does not exist!\n";
            die;
        }
        require_once $fname;
        $sc = new Schema();
        $sc->load($db);
        $migrations = self::getAllMigrations();
        foreach ($migrations as $revision) {
            self::applyMigration($revision, $db);
        }
    }

    static function getAllMigrations()
    {
        $dir    = self::get('savedir');
        $files  = glob($dir.'/migration*.php');
        $result = array();
        foreach ($files as $file) {
            $key      = preg_replace('#[^0-9]#is', '', basename($file));
            $result[] = $key;
        }
        sort($result, SORT_NUMERIC);

        return $result;
    }

    static function applyMigration($revision, $db, $direction = self::UP)
    {
        /** @noinspection PhpIncludeInspection */
        require_once self::get('savedir').'/migration'.$revision.'.php';
        $classname = 'Migration'.$revision;
        $migration = new $classname($db);
        $method    = 'run'.$direction;
        $migration->{$method}();
    }

    static function createMigrationContent($version, $alias, $diff)
    {
        $indent = self::TAB;
        $content
                = '<?php
'.'
'."class Migration{$version} extends AbstractMigration\n".'{
';
        if (!intval(Helper::get('noprepost'))) {
            $content .= "{$indent}/**\n"."{$indent} * @todo Return action which should run before db modification\n"."{$indent} */\n"."{$indent}protected function buildPreup() { return array(); }\n"
                        ."{$indent}/**\n"."{$indent} * @todo Return action which should run after db modification\n"."{$indent} */\n"."{$indent}protected function buildPostup() { return array(); }\n"
                        ."{$indent}/**\n"."{$indent} * @todo Return action which should run before db rollback\n"."{$indent} */\n"."{$indent}protected function buildPredown() { return array(); }\n"
                        ."{$indent}/**\n"."{$indent} * @todo Return action which should run after db rollback\n"."{$indent} */\n"."{$indent}protected function buildPostdown() { return array(); }\n".'
';
        }
        $content .= "{$indent}protected function buildUp()\n"."{$indent}{\n"."{$indent}{$indent}return array(\n";
        foreach ($diff['up'] as $sql) {
            $content .= self::formatString("{$indent}{$indent}{$indent}", ',', $sql);
        }
        $content .= "{$indent}{$indent});\n"."{$indent}}\n".'
'."{$indent}protected function buildDown()\n"."{$indent}{\n"."{$indent}{$indent}return array(\n";
        foreach ($diff['down'] as $sql) {
            $content .= self::formatString("{$indent}{$indent}{$indent}", ',', $sql);
        }
        $content .= "{$indent}{$indent});\n"."{$indent}}\n".'
'."{$indent}protected function getRev() { return {$version}; }\n".'
';
        if (false !== $alias) {
            $content .= "{$indent}protected function getAlias() { return '{$alias}'; }\n".'
';
        }
        $content
            .= '}
';

        return $content;
    }

    private static function formatString($indent, $suffix, $content)
    {
        $result = '';
        $lines  = explode('
', $content);
        for ($i = 0; $i < count($lines); $i++) {
            $isFirst = $i == 0;
            $isLast  = $i >= count($lines) - 1;
            $line    = self::escapeString($lines[$i].($isLast
                    ? ''
                    : '
'));
            // Line prefix contains concatenation operator
            $lineprefix = $isFirst ? '' : '. ';
            // Line suffix contains submitted string suffix
            $linesuffix = $isLast ? $suffix : '';
            $result .= $indent.$lineprefix.'"'.$line.'"'.$linesuffix.'
';
        }

        return $result;
    }

    private static function escapeString($string)
    {
        $convert = array(
            '\\' => '\\\\',
            '
'    => '\\n',
            '
'    => '\\r',
            '"'  => '\\"',
            ''  => '\\v',
            ''  => '\\e',
            ''  => '\\f',
            '$'  => '\\$'
        );
        $ret     = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ch = $string[$i];
            if (isset($convert[$ch])) {
                $ret .= $convert[$ch];
            } else {
                $ret .= $ch;
            }
        }

        return $ret;
    }

    static function createSchema($queries)
    {
        $indent = self::TAB;
        $content
                = '<?php
'.'class Schema extends AbstractSchema
'.'{
'."{$indent}protected function buildQueries()\n"."{$indent}{\n"."{$indent}{$indent}return array(\n";
        foreach ($queries as $q) {
            $content .= self::formatString("{$indent}{$indent}{$indent}", ',', $q);
        }
        $content .= "{$indent}{$indent});\n"."{$indent}}\n".'}
';

        return $content;
    }
}