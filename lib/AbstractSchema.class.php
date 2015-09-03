<?php

abstract class AbstractSchema
{

    protected function buildQueries()
    {
        return isset($this->queries) ? $this->queries : [];
    }

    public function load($db)
    {
        foreach ($this->buildQueries() as $query) {
            Output::verbose($query);
            if (!$db->query($query)) {
                Output::verbose("Fail\n{$query}\n{$db->error}");
            }
        }
    }

}