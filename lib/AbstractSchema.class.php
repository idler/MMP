<?php

abstract class AbstractSchema
{
    public function load(Mysqli $db)
    {
        foreach ($this->buildQueries() as $query) {
            Output::verbose($query);
            if (!$db->query($query)) {
                Output::verbose("Fail\n{$query}\n{$db->error}");
            }
        }
    }

    protected function buildQueries()
    {
        return isset($this->queries) ? $this->queries : array();
    }
}