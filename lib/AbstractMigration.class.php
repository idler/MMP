<?php
abstract class AbstractMigration
{
  /**
   *
   * @var Mysqli
   */
  protected $db;

  protected $up = array();
  protected $down = array();
  public function  __construct(mysqli $db)
  {
    $this->db = $db;
  }
  public function runUp()
  {
    foreach($this->up as $query)
    {
      Factory::verbose($query);
      $this->db->query($query);
    }
    $verT = Factory::get('versiontable');
    $query = "insert into `{$verT}` set `rev`={$this->rev}";
    Factory::verbose($query);
    $this->db->query($query);
  }
  public function runDown()
  {
    foreach($this->down as $query)
    {
      Factory::verbose($query);
      $this->db->query($query);
    }
    $verT = Factory::get('versiontable');
    $query = "delete `{$verT}` where `rev`={$this->rev}";
    Factory::verbose($query);
    $this->db->query($query);
  }
}