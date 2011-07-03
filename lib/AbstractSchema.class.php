<?php
abstract class AbstractSchema {
  function load($db)
  {
    foreach ($this->queries as $query)
    {
      Output::verbose($query);
      if(!$db->query($query))
      {
        Output::verbose("Fail\n{$query}\n{$db->error}");
      }
    }
  }

}