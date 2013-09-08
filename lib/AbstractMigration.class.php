<?php
abstract class AbstractMigration
{
  /**
   *
   * @var Mysqli
   */
  protected $db;

  /**
   * @var array
   */
  protected $up = array();
  
  /**
   * Actions which runs before db structure modification 
   * @var array
   */
  protected $preup = array();
  
  /**
   * Actions which runs after db structure modification 
   * @var array
   */
  protected $postup = array();
  
  /**
   * @var array
   */
  protected $down = array();
  
  /**
   * Actions which runs before db structure rollback 
   * @var array
   */
  protected $predown = array();
  
  /**
   * Actions which runs after db structure rollback 
   * @var array
   */
  protected $postdown = array();
  protected $rev = 0;
  
  public function  __construct(mysqli $db)
  {
    $this->db = $db;
  }
  
  /**
   * 
   */
  public function runUp()
  {
  	foreach ($this->preup as $query) {
      Output::verbose('UP: '.$query);
      if($this->db->query($query)) Output::verbose("Ok");
       else Output::verbose($this->db->error);
  	}
  	
    foreach($this->up as $query)
    {
      Output::verbose('UP: '.$query);
      if($this->db->query($query)) Output::verbose("Ok");
       else Output::verbose($this->db->error);
    }
    
    foreach ($this->postup as $query) {
      Output::verbose('UP: '.$query);
      if($this->db->query($query)) Output::verbose("Ok");
       else Output::verbose($this->db->error);
    }
    
    $verT = Helper::get('versiontable');
    $query = "INSERT INTO `{$verT}` SET `rev`={$this->rev}";
    Output::verbose($query);
    $this->db->query($query);
  }
  
  /**
   * 
   */
  public function runDown()
  {
  	foreach ($this->predown as $query) {
  		Output::verbose('UP: '.$query);
  		if($this->db->query($query)) Output::verbose("Ok");
  		else Output::verbose($this->db->error);
  	}
  	
    foreach($this->down as $query)
    {
      Output::verbose($query);
      $this->db->query($query);
    }
    
    foreach ($this->postup as $query) {
    	Output::verbose('UP: '.$query);
    	if($this->db->query($query)) Output::verbose("Ok");
    	else Output::verbose($this->db->error);
    }
    
    $verT = Helper::get('versiontable');
    $query = "DELETE FROM `{$verT}` WHERE `rev`={$this->rev}";
    Output::verbose($query);
    $this->db->query($query);
  }
}