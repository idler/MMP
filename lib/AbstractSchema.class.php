<?php
abstract class AbstractSchema {
  function load($db)
  {
    foreach ($this->queries as $query)
    {
      Factory::verbose($query);
      if(!$db->query($query))
      {
        Factory::verbose("Fail\n{$query}\n{$db->error}");
      }
    }
  }

}