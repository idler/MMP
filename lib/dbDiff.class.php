<?php

class dbDiff
{

  /**
   *
   * @var mysqli
   */
  protected $current;
  /**
   *
   * @var mysqli
   */
  protected $published;
  /**
   *
   * @var array
   */
  protected $difference = array('up' => array(), 'down' => array());
  
  protected function up($sql)
  {
    $this->difference['up'][] = $sql;
  }
  
  protected function down($sql)
  {
    $this->difference['down'][] = $sql;
  }
  
  public function __construct($currentDbVersion, $lastPublishedDbVersion)
  {
    $this->current = $currentDbVersion;
    $this->published = $lastPublishedDbVersion;
  }
  
  public function getDifference()
  {
    $current_tables = $this->getTables($this->current);
    $published_tables = $this->getTables($this->published);
    sort($current_tables);
    sort($published_tables);
    $this->createFullTableDifference($current_tables, $published_tables);

    $common = array_intersect($current_tables, $published_tables);
    $this->createDifferenceBetweenTables($common);
    return $this->difference;
  }
  
  protected function createFullTableDifference($current_tables, $published_tables)
  {

    sort($current_tables);
    sort($published_tables);

    $create = array_diff($current_tables, $published_tables);
    $drop = array_diff($published_tables, $current_tables);
    foreach ($create as $table) $this->addCreateTable($table, $this->current);
    foreach ($drop as $table) $this->addDropTable($table, $this->published);
  }
  
  protected function getTables($db)
  {
    $res = $db->query('show tables');
    $tables = array();
    while ($row = $res->fetch_array(MYSQLI_NUM))
    {
      $tables[] = $row[0];
    }
    return $tables;
  }
  
  protected function addCreateTable($tname, $db)
  {
    $this->down($this->dropTable($tname));
    $this->up($this->dropTable($tname));
    $this->up(Factory::getSqlForTableCreation($tname, $db));
  }
  
  protected function addDropTable($tname, $db)
  {
    $this->up($this->dropTable($tname));
    $this->down($this->dropTable($tname));
    $this->down(Factory::getSqlForTableCreation($tname, $db));
  }
  
  protected function createDifferenceBetweenTables($tables)
  {
    foreach ($tables as $table)
    {
      $query = "DESCRIBE `{$table}`";
      $table_current_columns = $this->getColumnList($this->current->query($query));
      $table_published_columns = $this->getColumnList($this->published->query($query));

      $this->createDifferenceInsideTable($table, $table_current_columns, $table_published_columns);
    }
  }
  
  protected function getColumnList($result)
  {
    $columns = array();
    while ($row = $result->fetch_assoc())
    {
      unset($row['Key']);
      $columns[] = $row;
    }
    return $columns;
  }
  
  protected function createDifferenceInsideTable($table, $table_current_columns, $table_published_columns)
  {

    foreach ($table_current_columns as $current_column)
    {
      $column_for_compare = $this->checkColumnExists($current_column, $table_published_columns);

      if (!$column_for_compare)
      {
        $sql = $this->addColumn($table, $current_column);
        $this->up($sql);
        $this->down($this->dropColumn($table, $current_column));
      }
      else
      {
        if ($current_column === $column_for_compare) continue;
        $sql = $this->changeColumn($table, $current_column);
        $this->up($sql);
        $sql = $this->changeColumn($table, $column_for_compare);
        $this->down($sql);
      }
    }


    foreach ($table_published_columns as $published_column)
    {

      $has = $this->checkColumnExists($published_column, $table_current_columns);
      
      if (!$has)
      {
        $sql = $this->addColumn($table, $published_column);
        $this->down($sql);
        $this->up($this->dropColumn($table, $published_column));
      }
    }
  }
  
  protected function addSqlExtras( & $sql, $column)
  {
    if ($column['Null'] === 'NO') $sql .= " not null ";
    if (!is_null($column['Default'])) $sql .= " default \\'{$column['Default']}\\' ";
  }
  
  protected function changeColumn($table, $column)
  {
    $sql = "ALTER TABLE `{$table}` CHANGE " .
      " `{$column['Field']}` `{$column['Field']}` " .
      " {$column['Type']} ";
    $this->addSqlExtras($sql, $column);
    return $sql;
  }
  
  protected function addColumn($table, $column)
  {
    $sql = "ALTER TABLE `{$table}` ADD `{$column['Field']}` {$column['Type']} ";
    $this->addSqlExtras($sql, $column);
    return $sql;
  }
  
  protected function dropColumn($table, $column)
  {
    return "ALTER TABLE `{$table}` DROP {$column['Field']}";
  }
  
  protected function dropTable($t)
  {
    return "DROP TABLE IF EXISTS `{$t}`";
  }
  
  protected function checkColumnExists($column, $column_list)
  {
    foreach ($column_list as $compare_column)
    {
      if ($compare_column['Field'] === $column['Field'])
      {
        return $compare_column;
      }
    }
    return false;
  }

}

