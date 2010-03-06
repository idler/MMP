<?php

class dbDiff
{

  /**
   *
   * @var mysqli
   */
  protected $actual;
  /**
   *
   * @var mysqli
   */
  protected $last;
  protected $difference = array('up' => array(), 'down' => array());
  
  protected function up($sql)
  {
    $this->difference['up'][] = $sql;
  }
  
  protected function down($sql)
  {
    $this->difference['down'][] = $sql;
  }
  
  public function __construct($actualDbVersion, $lastDbVersion)
  {
    $this->actual = $actualDbVersion;
    $this->last = $lastDbVersion;
  }
  
  public function getDifference()
  {
    $atab = $this->getTables($this->actual);
    $ltab = $this->getTables($this->last);
    sort($atab);
    sort($ltab);
    $this->createFullTableDifference($atab, $ltab);

    $common = array_intersect($atab, $ltab);
    $this->createDifferenceBetweenTables($common);
    return $this->difference;
  }
  
  public function createFullTableDifference($atab, $ltab)
  {

    sort($atab);
    sort($ltab);

    $create = array_diff($atab, $ltab);
    $drop = array_diff($ltab, $atab);
    foreach ($create as $table) $this->addCreateTable($table, $this->actual);
    foreach ($drop as $table) $this->addDropTable($table, $this->last);
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
    ;
    $this->down(Factory::getSqlForTableCreation($tname, $db));
  }
  
  protected function createDifferenceBetweenTables($tables)
  {
    foreach ($tables as $table)
    {
      $query = "DESCRIBE `{$table}`";
      $ares = $this->actual->query($query);
      $lres = $this->last->query($query);
      $acols = $lcols = array();
      while ($row = $ares->fetch_assoc())
      {
        unset($row['Key']);
        $acols[] = $row;
      }
      while ($row = $lres->fetch_assoc())
      {
        unset($row['Key']);
        $lcols[] = $row;
      }
      $this->createDifferenceByTable($table, $acols, $lcols);
    }
  }
  
  protected function createDifferenceByTable($table, $acols, $lcols)
  {

    foreach ($acols as $column)
    {
      $memory = null;
      $has = false;
      foreach ($lcols as $col)
      {
        if ($col['Field'] === $column['Field'])
        {
          $has = true;
          $memory = $col;
          break;
        }
      }
      if (!$has)
      {
        $sql = $this->addColumn($table, $column);
        $this->addSqlExtras($sql, $column);
        /*if ($column['Key'] === 'PRI')
        {
          $sql .= " PRIMARY KEY ";
          $this->up($this->dropPrimary($table));
        }*/
        $this->up($sql);
        $this->down($this->dropColumn($table, $column));
      }
      else
      {
        if ($column === $memory) continue;
        $sql = $this->changeColumn($table, $column);
        $this->addSqlExtras($sql, $column);
        /*if ($column['Key'] === 'PRI')
        {
          $sql .= " PRIMARY KEY ";
          $this->up($this->dropPrimary($table));
        }*/
        $this->up($sql);



        $sql = $this->changeColumn($table, $memory);
        $this->addSqlExtras($sql, $memory);
        /*if ($memory['Key'] === 'PRI')
        {
          $sql .= " PRIMARY KEY ";
          $this->down($this->dropPrimary($table));
        }*/
        $this->down($sql);
      }
    }


    foreach ($lcols as $column)
    {

      $has = false;
      foreach ($acols as $col)
      {
        if ($col['Field'] === $column['Field'])
        {
          $has = true;
          break;
        }
      }
      if (!$has)
      {
        $sql = $this->addColumn($table, $column);
        $this->addSqlExtras($sql, $column);
        /*if ($column['Key'] === 'PRI')
        {
          $sql .= " PRIMARY KEY ";
          $this->down($this->dropPrimary($table));
        }*/
        $this->down($sql);
        $this->up($this->dropColumn($table, $column));
      }
    }
  }
  
  protected function addSqlExtras( & $sql, $column)
  {
    if ($column['Null'] === 'NO') $sql .= " not null ";
    if (!is_null($column['Default'])) $sql .= " default \\'{$column['Default']}\\' ";
    if ($column['Extra'] != '') $sql .= " {$column['extra']} ";
  }
  
  protected function addColumn($table, $column)
  {
    return "ALTER TABLE `{$table}` ADD `{$column['Field']}` {$column['Type']} ";
  }
  
  protected function dropColumn($table, $column)
  {
    return "ALTER TABLE `{$table}` DROP {$column['Field']}";
  }
  
  protected function dropTable($t)
  {
    return "DROP TABLE IF EXISTS `{$t}`";
  }
  
  protected function changeColumn($table, $column)
  {
    return "ALTER TABLE `{$table}` CHANGE " .
      " `{$column['Field']}` `{$column['Field']}` " .
      " {$column['Type']} ";
  }
  
  protected function dropPrimary($table)
  {
    return "ALTER TABLE `{$table}` DROP PRIMARY KEY";
  }

}

