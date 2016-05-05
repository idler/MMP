<?php

abstract class AbstractMigration
{
    /**
     *
     * @var Mysqli
     */
    protected $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Apply this migration
     */
    public function runUp()
    {
        foreach ($this->buildPreup() as $query) {
            Output::verbose('PREUP: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::verbose($this->db->error);
            }
        }
        foreach ($this->buildUp() as $query) {
            Output::verbose('UP: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::verbose($this->db->error);
            }
        }
        foreach ($this->buildPostup() as $query) {
            Output::verbose('POSTUP: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::verbose($this->db->error);
            }
        }
        $verT  = Helper::get('versiontable');
        $rev   = $this->getRev();
        $query = "INSERT INTO `{$verT}` SET `rev`={$rev}";
        Output::verbose($query);
        $this->db->query($query);
    }

    /**
     * Build list of sql queries for the preup event
     */
    protected function buildPreup()
    {
        return isset($this->preup) ? $this->preup : array();
    }

    /**
     * Build list of sql queries for the up event
     */
    protected function buildUp()
    {
        return isset($this->up) ? $this->up : array();
    }

    /**
     * Build list of sql queries for the postup event
     */
    protected function buildPostup()
    {
        return isset($this->postup) ? $this->postup : array();
    }

    /**
     * Get current revision number
     */
    protected function getRev()
    {
        return isset($this->rev) ? $this->rev : 0;
    }

    /**
     * Revert this migration
     */
    public function runDown()
    {
        foreach ($this->buildPredown() as $query) {
            Output::verbose('PREDOWN: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::verbose($this->db->error);
            }
        }
        foreach ($this->buildDown() as $query) {
            Output::verbose('DOWN:'.$query);
            $this->db->query($query);
        }
        foreach ($this->buildPostdown() as $query) {
            Output::verbose('POSTDOWN: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::verbose($this->db->error);
            }
        }
        $verT  = Helper::get('versiontable');
        $rev   = $this->getRev();
        $query = "DELETE FROM `{$verT}` WHERE `rev`={$rev}";
        Output::verbose($query);
        $this->db->query($query);
    }

    /**
     * Build list of sql queries for the predown event
     */
    protected function buildPredown()
    {
        return isset($this->predown) ? $this->predown : array();
    }

    /**
     * Build list of sql queries for the down event
     */
    protected function buildDown()
    {
        return isset($this->down) ? $this->down : array();
    }

    /**
     * Build list of sql queries for the postdown event
     */
    protected function buildPostdown()
    {
        return isset($this->postdown) ? $this->postdown : array();
    }

    /**
     * Get current alias
     */
    protected function getAlias()
    {
        return (Helper::get('aliasprefix') ?: '').(string)(isset($this->alias) ? $this->alias : 0);
    }
}