<?php
abstract class AbstractSchema {
  function load($db)
  {
    foreach ($this->queries as $query)
    {
      Factory::verbose("$query\n");
      if($db->query($query))
      {
        Factory::verbose("Ok\n");
      }else{
        Factory::verbose("Fail\n{$query}\n{$db->error}\n");
      }
    }
  }

}