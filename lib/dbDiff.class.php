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
    sort($atab); sort($ltab);
    $max = max(count($atab),count($ltab));
    for($i=0;$i<$max;$i++)
    {
      if(!in_array($atab[$i], $ltab))
      {
        $this->addCreateTable($atab[$i],$this->actual);
        unset($atab[$i]);
      }else{
        unset($ltab[$i]); unset($atab[$i]);
      }
    }

    foreach($ltab as $t)
    {
      $this->addDropTable($t,$this->last);
    }
    //var_dump(array($atab,$ltab));
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
    Factory::verbose("Will create $tname");
  }

  protected function addDropTable($tname,$db)
  {
    Factory::verbose("Will drop $tname");
  }

}
