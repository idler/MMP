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
      Helper::verbose('UP: '.$query);
      if($this->db->query($query)) Helper::verbose("Ok");
       else Helper::verbose($this->db->error);
    }
    $verT = Helper::get('versiontable');
    $query = "INSERT INTO `{$verT}` SET `rev`={$this->rev}";
    Helper::verbose($query);
    $this->db->query($query);
  }
  public function runDown()
  {
    foreach($this->down as $query)
    {
      Helper::verbose($query);
      $this->db->query($query);
    }
    $verT = Helper::get('versiontable');
    $query = "DELETE FROM `{$verT}` WHERE `rev`={$this->rev}";
    Helper::verbose($query);
    $this->db->query($query);
  }
}