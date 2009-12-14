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
      $this->db->query($query);
    }
    $verT = Factory::get('versiontable');
    $this->db->query("insert into `{$verT}` set `rev`={$this->rev}");
  }
  public function runDown()
  {
    foreach($this->down as $query)
    {
      $this->db->query($query);
    }
    $verT = Factory::get('versiontable');
    $this->db->query("delete `{$verT}` where `rev`={$this->rev}");
  }
}