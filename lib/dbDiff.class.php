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
      $this->createFullTableDifference();
      return $this->difference;
  }

  public function createFullTableDifference()
  {
    $atab = $this->getTables($this->actual);
    $ltab = $this->getTables($this->last);
    sort($atab); sort($ltab);
    
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

}
