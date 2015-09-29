<?php

class dbDiff
{
    /**
     *
     * @var mysqli main database connection
     */
    protected $current;
    /**
     *
     * @var mysqli temp database connection
     */
    protected $published;
    /**
     *
     * @var array
     */
    protected $difference = array('up' => array(), 'down' => array());

    public function __construct($currentDbVersion, $lastPublishedDbVersion)
    {
        $this->current   = $currentDbVersion;
        $this->published = $lastPublishedDbVersion;
    }

    public function getDifference()
    {
        $this->getTablesDifference();
        $this->getRoutinesDifference('PROCEDURE');
        $this->getRoutinesDifference('FUNCTION');

        return $this->difference;
    }

    protected function getTablesDifference()
    {
        $current_tables   = Helper::getTables($this->current);
        $published_tables = Helper::getTables($this->published);
        $exclude_tables   = Helper::get('exclude_tables');
        if (!empty($exclude_tables)) {
            if ($exclude_tables[0] != '/') {
                $exclude_tables = '/'.$exclude_tables.'/i';
            }
            foreach ($current_tables as $k => $table) {
                if (preg_match($exclude_tables, $table)) {
                    unset($current_tables[$k]);
                }
            }
            foreach ($published_tables as $k => $table) {
                if (preg_match($exclude_tables, $table)) {
                    unset($published_tables[$k]);
                }
            }
        }
        $this->createFullTableDifference($current_tables, $published_tables);
        $common = array_intersect($current_tables, $published_tables);
        $this->createDifferenceBetweenTables($common);
    }

    protected function createFullTableDifference($current_tables, $published_tables)
    {
        sort($current_tables);
        sort($published_tables);
        $create = array_diff($current_tables, $published_tables);
        $drop   = array_diff($published_tables, $current_tables);
        foreach ($create as $table) {
            $this->addCreateTable($table, $this->current);
        }
        foreach ($drop as $table) {
            $this->addDropTable($table, $this->published);
        }
    }

    protected function addCreateTable($tname, $db)
    {
        $this->down($this->dropTable($tname));
        $this->up($this->dropTable($tname));
        $this->up(Helper::getSqlForTableCreation($tname, $db));
    }

    protected function down($sql)
    {
        if (!strlen($sql)) {
            return;
        }
        $this->difference['down'][] = $sql;
    }

    protected function dropTable($t)
    {
        return "DROP TABLE IF EXISTS `{$t}`";
    }

    protected function up($sql)
    {
        if (!strlen($sql)) {
            return;
        }
        $this->difference['up'][] = $sql;
    }

    protected function addDropTable($tname, $db)
    {
        $this->up($this->dropTable($tname));
        $this->down($this->dropTable($tname));
        $this->down(Helper::getSqlForTableCreation($tname, $db));
    }

    protected function createDifferenceBetweenTables($tables)
    {
        foreach ($tables as $table) {
            $query                   = "DESCRIBE `{$table}`";
            $table_current_columns   = $this->getColumnList($this->current->query($query));
            $table_published_columns = $this->getColumnList($this->published->query($query));
            $this->createDifferenceInsideTable($table, $table_current_columns, $table_published_columns);
            $this->createIndexDifference($table);
        }
    }

    protected function getColumnList(mysqli_result $result)
    {
        $columns = array();
        while ($row = $result->fetch_assoc()) {
            unset($row['Key']);
            $columns[] = $row;
        }

        return $columns;
    }

    protected function createDifferenceInsideTable($table, $table_current_columns, $table_published_columns)
    {
        foreach ($table_current_columns as $current_column) {
            $column_for_compare = $this->checkColumnExists($current_column, $table_published_columns);
            if (!$column_for_compare) {
                $this->up($this->addColumn($table, $current_column));
                $this->down($this->dropColumn($table, $current_column));
            } else {
                if ($current_column === $column_for_compare) {
                    continue;
                }
                $sql = $this->changeColumn($table, $current_column);
                $this->up($sql);
                $sql = $this->changeColumn($table, $column_for_compare);
                $this->down($sql);
            }
        }
        foreach ($table_published_columns as $published_column) {
            $has = $this->checkColumnExists($published_column, $table_current_columns);
            if (!$has) {
                $constraint = $this->getConstraintForColumn($this->published, $table, $published_column['Field']);
                //echo "COLUMNS\n\n"; var_dump($constraint);
                if (count($constraint)) {
                    $this->down($this->addConstraint(array('constraint' => $constraint)));
                    $this->up($this->dropConstraint(array('constraint' => $constraint)));
                }
                $this->down($this->addColumn($table, $published_column));
                $this->up($this->dropColumn($table, $published_column));
            }
        }
    }

    protected function checkColumnExists($column, $column_list)
    {
        foreach ($column_list as $compare_column) {
            if ($compare_column['Field'] === $column['Field']) {
                return $compare_column;
            }
        }

        return false;
    }

    protected function addColumn($table, $column)
    {
        $sql = "ALTER TABLE `{$table}` ADD `{$column['Field']}` {$column['Type']}";
        $this->addSqlExtras($sql, $column);

        return $sql;
    }

    protected function addSqlExtras(&$sql, $column)
    {
        if ($column['Null'] === 'NO') {
            $sql .= ' NOT NULL';
        }
        if (!is_null($column['Default'])) {
            if (preg_match('/^bit\\(/i', $column['Type'])) {
                // BIT columns use format b'x' - so we can't wrap those
                $default = $column['Default'];
            } else {
                $default = '\''.$this->current->real_escape_string($column['Default']).'\'';
            }
            $sql .= " DEFAULT {$default}";
        }
    }

    protected function dropColumn($table, $column)
    {
        return "ALTER TABLE `{$table}` DROP `{$column['Field']}`";
    }

    protected function changeColumn($table, $column)
    {
        $sql = "ALTER TABLE `{$table}` CHANGE "." `{$column['Field']}` `{$column['Field']}` {$column['Type']}";
        $this->addSqlExtras($sql, $column);

        return $sql;
    }

    protected function getConstraintForColumn(mysqli $connection, $table, $col_name)
    {
        $q      = 'select database() as dbname';
        $res    = $connection->query($q);
        $row    = $res->fetch_array(MYSQLI_ASSOC);
        $dbname = $row['dbname'];
        Output::verbose("DATABASE: {$row['dbname']}");
        $sql
            = "SELECT k.CONSTRAINT_SCHEMA,k.CONSTRAINT_NAME,k.TABLE_NAME,k.COLUMN_NAME,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME, r.UPDATE_RULE, r.DELETE_RULE FROM information_schema.key_column_usage k LEFT JOIN information_schema.referential_constraints r ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND k.REFERENCED_TABLE_NAME=r.REFERENCED_TABLE_NAME LEFT JOIN information_schema.table_constraints t ON t.CONSTRAINT_SCHEMA = r.CONSTRAINT_SCHEMA WHERE k.constraint_schema='{$dbname}' AND t.CONSTRAINT_TYPE='FOREIGN KEY' AND k.TABLE_NAME='{$table}' AND r.TABLE_NAME='{$table}' AND t.TABLE_NAME='{$table}' AND k.COLUMN_NAME='{$col_name}'";
        Output::verbose($sql);
        $res = $connection->query($sql);
        $row = $res->fetch_array(MYSQLI_ASSOC);
        if (!count($row)) {
            return false;
        }
        $constraint = array(
            'table'     => $table,
            'name'      => $row['CONSTRAINT_NAME'],
            'column'    => $row['COLUMN_NAME'],
            'reference' => array('table' => $row['REFERENCED_TABLE_NAME'], 'column' => $row['REFERENCED_COLUMN_NAME'], 'update' => $row['UPDATE_RULE'], 'delete' => $row['DELETE_RULE'])
        );
        //echo "=================\n\n\n\=========";
        //var_dump($constraint);
        return $constraint;
    }

    protected function addConstraint($index)
    {
        if (!isset($index['constraint']['column']) || !strlen($index['constraint']['column'])) {
            return '';
        }
        $sql = "ALTER TABLE `{$index['constraint']['table']}` "."ADD CONSTRAINT `{$index['constraint']['name']}` "."FOREIGN KEY (`{$index['constraint']['column']}`) "
               ."REFERENCES `{$index['constraint']['reference']['table']}` "."(`{$index['constraint']['reference']['column']}`) "."ON UPDATE {$index['constraint']['reference']['update']} "
               ."ON DELETE {$index['constraint']['reference']['delete']} ";
        //echo  "ADD==================================\n$sql\n\n";
        //var_dump($index['constraint']);
        return $sql;
    }

    protected function dropConstraint($index)
    {
        if (!isset($index['constraint']['column']) || !strlen($index['constraint']['column'])) {
            return '';
        }
        $sql = "ALTER TABLE `{$index['constraint']['table']}` "."DROP FOREIGN KEY `{$index['constraint']['name']}` ";
        //echo  "DELETE==================================\n$sql\n";
        //var_dump($index['constraint']);
        return $sql;
    }

    protected function createIndexDifference($table)
    {
        $current_indexes   = $this->getIndexListFromTable($table, $this->current);
        $published_indexes = $this->getIndexListFromTable($table, $this->published);
        foreach ($current_indexes as $cur_index) {
            $index_for_compare = $this->checkIndexExists($cur_index, $published_indexes);
            if (!$index_for_compare) {
                $this->down($this->dropConstraint($cur_index));
                $this->down($this->dropIndex($cur_index));
                $this->up($this->dropConstraint($cur_index));
                $this->up($this->dropIndex($cur_index));
                $this->up($this->addIndex($cur_index));
                $this->up($this->addConstraint($cur_index));
            } elseif ($index_for_compare === $cur_index) {
                continue;
            } else {
                $this->down($this->dropConstraint($cur_index));
                $this->down($this->dropIndex($cur_index));
                $this->down($this->addIndex($index_for_compare));
                $this->down($this->addConstraint($index_for_compare));
                $this->up($this->dropConstraint($index_for_compare));
                $this->up($this->dropIndex($index_for_compare));
                $this->up($this->addIndex($cur_index));
                $this->up($this->addConstraint($cur_index));
            }
        }
        foreach ($published_indexes as $pub_index) {
            if ($this->checkIndexExists($pub_index, $current_indexes) === false) {
                $this->down($this->dropConstraint($pub_index));
                $this->down($this->dropIndex($pub_index));
                $this->down($this->addIndex($pub_index));
                $this->down($this->addConstraint($pub_index));
                $this->up($this->dropConstraint($pub_index));
                $this->up($this->dropIndex($pub_index));
            }
        }
    }

    protected function getIndexListFromTable($table, mysqli $connection)
    {
        $sql     = "SHOW INDEXES FROM `{$table}`";
        $res     = $connection->query($sql);
        $indexes = array();
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            if (!isset($indexes[$row['Key_name']])) {
                $indexes[$row['Key_name']] = array();
            }
            $indexes[$row['Key_name']]['unique'] = !intval($row['Non_unique']);
            $indexes[$row['Key_name']]['type']   = $row['Index_type'];
            $indexes[$row['Key_name']]['name']   = $row['Key_name'];
            $indexes[$row['Key_name']]['table']  = $row['Table'];
            if (!isset($indexes[$row['Key_name']]['fields'])) {
                $indexes[$row['Key_name']]['fields'] = array();
            }
            $indexes[$row['Key_name']]['fields'][$row['Seq_in_index']] = array('name' => $row['Column_name'], 'length' => $row['Sub_part']);
            $indexes[$row['Key_name']]['constraint']                   = $this->getConstraintForColumn($connection, $table, $row['Column_name']);
        }

        //var_dump($indexes);
        return $indexes;
    }

    protected function checkIndexExists($index, $index_list)
    {
        foreach ($index_list as $comparing_index) {
            if ($index['name'] === $comparing_index['name']) {
                return $comparing_index;
            }
        }

        return false;
    }

    protected function dropIndex($index)
    {
        return "DROP INDEX `{$index['name']}` ON `{$index['table']}`";
    }

    protected function addIndex($index)
    {
        if ($index['name'] === 'PRIMARY') {
            $index_string = "ALTER TABLE `{$index['table']}` ADD PRIMARY KEY";
            $fields       = array();
            foreach ($index['fields'] as $f) {
                $len      = intval($f['length']) ? "({$f['length']})" : '';
                $fields[] = "`{$f['name']}`".$len;
            }
            $index_string .= '('.implode(',', $fields).')';
        } else {
            $index_string = 'CREATE ';
            if ($index['type'] === 'FULLTEXT') {
                $index_string .= ' FULLTEXT ';
            }
            if ($index['unique']) {
                $index_string .= ' UNIQUE ';
            }
            $index_string .= " INDEX `{$index['name']}` ";
            if (in_array($index['type'], array('RTREE', 'BTREE', 'HASH'))) {
                $index_string .= " USING {$index['type']} ";
            }
            $index_string .= " on `{$index['table']}` ";
            $fields = array();
            foreach ($index['fields'] as $f) {
                $len      = intval($f['length']) ? "({$f['length']})" : '';
                $fields[] = "`{$f['name']}`".$len;
            }
            $index_string .= '('.implode(',', $fields).')';
        }

        return $index_string;
    }

    protected function getRoutinesDifference($type)
    {
        $current_routines   = Helper::getRoutines($type);
        $published_routines = Helper::getRoutines($type);
        sort($current_routines);
        sort($published_routines);
        $this->createFullRoutineDifference($current_routines, $published_routines, $type);
        $common = array_intersect($current_routines, $published_routines);
        $this->createDifferenceBetweenRoutines($common, $type);
    }

    protected function createFullRoutineDifference($current_routines, $published_routines, $type)
    {
        $create = array_diff($current_routines, $published_routines);
        $drop   = array_diff($published_routines, $current_routines);
        foreach ($create as $routine) {
            $this->addCreateRoutine($routine, $this->current, $type);
        }
        foreach ($drop as $routine) {
            $this->addDropRoutine($routine, $this->published, $type);
        }
    }

    protected function addCreateRoutine($tname, $db, $type)
    {
        $this->down($this->dropRoutine($tname, $type));
        $this->up($this->dropRoutine($tname, $type));
        $this->up(Helper::getSqlForRoutineCreation($tname, $db, $type));
    }

    protected function dropRoutine($r, $type)
    {
        return "DROP {$type} IF EXISTS `{$r}`";
    }

    protected function addDropRoutine($tname, $db, $type)
    {
        $this->up($this->dropRoutine($tname, $type));
        $this->down($this->dropRoutine($tname, $type));
        $this->down(Helper::getSqlForRoutineCreation($tname, $db, $type));
    }

    protected function createDifferenceBetweenRoutines($routines, $type)
    {
        foreach ($routines as $rname) {
            $currentSql   = Helper::getSqlForRoutineCreation($rname, $this->current, $type);
            $publishedSql = Helper::getSqlForRoutineCreation($rname, $this->published, $type);
            if ($currentSql === $publishedSql) {
                continue;
            }
            $this->up($this->dropRoutine($rname, $type));
            $this->up($currentSql);
            $this->down($this->dropRoutine($rname, $type));
            $this->down($publishedSql);
        }
    }
}