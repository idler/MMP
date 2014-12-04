<?php

class dbTestCase extends UnitTestCase
{
  protected function buildSchema()
  {
  	$this->runController("schema");
  }

  protected function buildMigration()
  {
  	$this->assertTrue( $this->runController("create"), "Did not create migration as requested" );
  }

  protected function assertNoMigration()
  {
    $this->assertFalse( $this->runController("create"), 'No migration expected but one has been created' );
  }

  protected function query( $query )
  {
    $db = $this->getDb();
    $this->assertTrue( $db->query( $query ), "Couldn't execute query: '$query', error: {$db->error}" );
  }

  protected function getController( $name )
  {
  	return Helper::getController( $name, $this->getConfig() );
  }

  protected function runController( $name )
  {
    ob_start();
  	$ret = $this->getController( $name )->runStrategy();
    ob_end_clean();
    return $ret;
  }

  protected function getConfig()
  {
  	global $conf;
  	return $conf;
  }

  protected function getDb() 
  {
    if ( !$this->db ) $this->db = Helper::getDbObject();
    return $this->db;
  }

  private $db = null;
}