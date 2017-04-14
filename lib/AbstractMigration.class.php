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
     * Start transaction
     */
    protected function begin(){
        $this->db->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT,get_class($this));
    }

    /**
     * Commit transaction
     */
    protected function commit(){
        $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    protected function rollback(){
        $this->db->rollback();
        Output::error("Transaction was rolled back. Now we have to stop further migration!");
        exit();
    }

    /**
     * Apply this migration
     */
    public function runUp()
    {
        $this->begin();

        foreach ($this->buildPreup() as $query) {
            Output::verbose('PREUP: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::error($this->db->error);
                $this->rollback();
            }
        }
        foreach ($this->buildUp() as $query) {
            Output::verbose('UP: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::error($this->db->error);
                $this->rollback();
            }
        }
        foreach ($this->buildPostup() as $query) {
            Output::verbose('POSTUP: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::error($this->db->error);
                $this->rollback();
            }
        }
        $verT  = Helper::get('versiontable');
        $rev   = $this->getRev();
        $query = "INSERT INTO `{$verT}` SET `rev`={$rev}";
        Output::verbose($query);
        $this->db->query($query);

        $this->commit();
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
        $this->begin();

        foreach ($this->buildPredown() as $query) {
            Output::verbose('PREDOWN: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::error($this->db->error);
                $this->rollback();
            }
        }
        foreach ($this->buildDown() as $query) {
            Output::verbose('DOWN:'.$query);
            if($this->db->query($query)){
                Output::verbose('Ok');
            }else{
                Output::error($this->db->error);
                $this->rollback();
            }
        }
        foreach ($this->buildPostdown() as $query) {
            Output::verbose('POSTDOWN: '.$query);
            if ($this->db->query($query)) {
                Output::verbose('Ok');
            } else {
                Output::error($this->db->error);
            }
        }
        $verT  = Helper::get('versiontable');
        $rev   = $this->getRev();
        $query = "DELETE FROM `{$verT}` WHERE `rev`={$rev}";
        Output::verbose($query);
        $this->db->query($query);
        
        $this->commit();
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