<?php
abstract class AbstractSchema {
  function load($db)
  {
    foreach ($this->queries as $query)
    {
      Helper::verbose($query);
      if(!$db->query($query))
      {
        Helper::verbose("Fail\n{$query}\n{$db->error}");
      }
    }
  }

}