<?php
abstract class AbstractSchema {
  function load($db)
  {
    foreach ($this->queries as $query)
    {
      $this->verbose("$query\n");
      if($db->query($query))
      {
        $this->verbose("Ok\n");
      }else{
        $this->verbose("Fail\n{$query}\n{$db->error}\n");
      }
    }
  }

  protected function verbose($string)
  {
    if(Factory::get('verbose')) echo $string;
  }
}