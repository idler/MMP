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

  protected $difference = array('up'=>array(),'down'=>array());

  public function  __construct($actualDbVersion,$lastDbVersion)
  {
    $this->actual = $actualDbVersion;
    $this->last   = $lastDbVersion;
  }


  public function getDifference()
  {
    $atab = $this->getTables($this->actual);
    $ltab = $this->getTables($this->last);
    sort($atab);
    sort($ltab);
    $this->createFullTableDifference($atab,$ltab);

    $common = array_intersect($atab,$ltab);
    $this->createDifferenceBetweenTables($common);
    return $this->difference;
  }

  public function createFullTableDifference($atab,$ltab)
  {

    sort($atab);
    sort($ltab);

    $create = array_diff($atab,$ltab);
    $drop   = array_diff($ltab,$atab);
    foreach($create as $table) $this->addCreateTable($table, $this->actual);
    foreach($drop as $table) $this->addDropTable($table, $this->last);
  }

  protected function getTables($db)
  {
    $res = $db->query('show tables');
    $tables = array();
    while($row = $res->fetch_array(MYSQLI_NUM))
    {
      $tables[] = $row[0];
    }
    return $tables;
  }

  protected function addCreateTable($tname,$db)
  {
    $this->difference['down'][]= "DROP TABLE IF EXISTS `{$tname}`";
    $this->difference['up'][] = "DROP TABLE IF EXISTS `{$tname}`";
    $this->difference['up'][] = Factory::getSqlForTableCreation($tname, $db);
  }

  protected function addDropTable($tname,$db)
  {
    $this->difference['up'][]= "DROP TABLE IF EXISTS `{$tname}`";
    $this->difference['down'][] = "DROP TABLE IF EXISTS `{$tname}`";
    $this->difference['down'][] = Factory::getSqlForTableCreation($tname, $db);
  }
  protected function createDifferenceBetweenTables($tables)
  {
    foreach($tables as $table)
    {
      $query = "DESCRIBE `{$table}`";
      $ares = $this->actual->query($query);
      $lres = $this->last->query($query);
      $acols = $lcols = array();
      while($row = $ares->fetch_assoc()) $acols[] = $row;
      while($row = $lres->fetch_assoc()) $lcols[] = $row;
      $this->createDifferenceByTable($table,$acols,$lcols);
    }
  }

  protected function createDifferenceByTable($table,$acols,$lcols)
  {

    foreach($acols as $column)
    {
      $memory = null;
      $has = false;
      foreach($lcols as $col)
      {
        if($col['Field'] === $column['Field'])
        {
          $has = true;
          $memory = $col;
          break;
        }
      }
      if(!$has)
      {
        $sql = "ALTER TABLE `{$table}` ADD `{$column['Field']}` {$column['Type']} ";
        if($column['Null'] === 'NO') $sql .= " not null ";
        if(!is_null($column['Default'])) $sql .= " default \\'{$column['Default']}\\' ";
        if($column['Extra'] !='') $sql .= " {$column['extra']} ";
        if($column['Key'] === 'PRI')
        {
          $sql .= " PRIMARY KEY ";
          $this->difference['up'][] = "ALTER TABLE `{$table}` DROP PRIMARY KEY";
        }
        $this->difference['up'][] = $sql;
        $this->difference['down'][] = "ALTER TABLE `{$table}` DROP {$column['Field']}";
      }
      else
      {
        if($column === $memory) continue;
        $sql = "ALTER TABLE `{$table}` CHANGE ".
        " `{$column['Field']}` `{$column['Field']}` ".
        " {$column['Type']} ";
        if($column['Null'] === 'NO') $sql .= " not null ";
        if(!is_null($column['Default'])) $sql .= " default \\'{$column['Default']}\\' ";
        if($column['Extra'] !='') $sql .= " {$column['extra']} ";
        if($column['Key'] === 'PRI')
        {
          $sql .= " PRIMARY KEY ";
          $this->difference['up'][] = "ALTER TABLE `{$table}` DROP PRIMARY KEY";
        }
        $this->difference['up'][] = $sql;



        $sql =  "ALTER TABLE `{$table}` CHANGE ".
        " `{$memory['Field']}` `{$memory['Field']}` ".
        " {$memory['Type']} ";
        if($memory['Null'] === 'NO') $sql .= " not null ";
        if(!is_null($memory['Default'])) $sql .= " default \\'{$memory['Default']}\\' ";
        if($memory['Extra'] !='') $sql .= " {$memory['extra']} ";
        if($memory['Key'] === 'PRI')
        {
          $sql .= " PRIMARY KEY ";
          $this->difference['down'][] = "ALTER TABLE `{$table}` DROP PRIMARY KEY";
        }
        $this->difference['down'][] = $sql;
      }
    }


    foreach($lcols as $column)
    {

      $has = false;
      foreach($acols as $col)
      {
        if($col['Field'] === $column['Field'])
        {
          $has = true;
          break;
        }
      }
      if(!$has)
      {
        $sql = "ALTER TABLE `{$table}` ADD `{$column['Field']}` {$column['Type']} ";
        if($column['Null'] === 'NO') $sql .= " not null ";
        if(!is_null($column['Default'])) $sql .= " default \\'{$column['Default']}\\s' ";
        if($column['Extra'] !='') $sql .= " {$column['extra']} ";
        if($column['Key'] === 'PRI')
        {
          $sql .= " PRIMARY KEY ";
          $this->difference['down'][] = "ALTER TABLE `{$table}` DROP PRIMARY KEY";
        }
        $this->difference['down'][] = $sql;
        $this->difference['up'][] = "ALTER TABLE `{$table}` DROP {$column['Field']}";
      }
    }

  }
}

