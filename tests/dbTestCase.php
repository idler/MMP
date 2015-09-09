<?php

class dbTestCase extends PHPUnit_Framework_TestCase
{

  /**
   * Common DB setup code
   */
  protected function setUp()
  {
    // Load test configuration
    if (!file_exists(__DIR__.'/config.ini'))
    {
      $this->fail("Missing valid config.ini file in the unit tests directory");
    }
    $conf = parse_ini_file(__DIR__.'/config.ini');

    // Make sure we have some clean, temporary output dir
    exec( "rm -rf " . escapeshellarg($conf['savedir']) );
    @mkdir( $conf['savedir'], 0777, TRUE );

    // Setup the system
    Helper::setConfig($conf);

    // Test DB connection,
    // Note: we don't specify db name yet, it may not exist
    $conn = new mysqli($conf['host'],$conf['user'],$conf['password']);
    if ( $conn->connect_error )
    {
      $this->fail( "Couldn't connect to database ({$conn->connect_errno}) {$conn->connect_error}" );
    }

    // Create simple clean DB environment
    $conn->query( "drop database if exists `{$conf['db']}`" );
    $conn->query( "create database `{$conf['db']}`" ) or $this->fail( "Couldn't create test database" );
    $conn->query( "use `{$conf['db']}`");
    $conn->query( "create table test (id int unsigned not null primary key auto_increment, title varchar(40), description text)" );
  }

  /**
   * Common code cleaning up temporary files
   */
  protected function tearDown()
  {
    // Remove the temporary folder
    exec( "rm -rf " . escapeshellarg(__DIR__ . '/temp_data/') );
  }

  /**
   * Helper function - create schema file in temporary output folder
   */
  protected function buildSchema()
  {
    $this->runController("schema");
  }

  /**
   * Helper function - build new migration file in temporary output folder
   */
  protected function buildMigration()
  {
    $this->assertTrue( $this->runController("create"), "Did not create migration as requested" );
  }

  /**
   * Helper function - ensure current DB layout will not generate any additional migration
   */
  protected function assertNoMigration()
  {
    $this->assertFalse( $this->runController("create"), 'No migration expected but one has been created' );
  }

    /**
     * Helper function - execute SQL query, it must succeed
     *
     * @param $query
     */
  protected function query( $query )
  {
    $db = $this->getDb();
    $this->assertTrue( $db->query( $query ), "Couldn't execute query: '$query', error: {$db->error}" );
  }

    /**
     * Helper function - create instance of MMP controller class
     *
     * @param $name
     *
     * @return object
     */
  protected function getController( $name )
  {
    return Helper::getController( $name, $this->getConfig() );
  }

    /**
     * Helper function - run given controller
     *
     * @param $name
     *
     * @return
     */
  protected function runController( $name )
  {
    ob_start();
    $ret = $this->getController( $name )->runStrategy();
    ob_end_clean();
    return $ret;
  }

  /**
   * Helper function - get current configuration
   */
  protected function getConfig()
  {
    global $conf;
    return $conf;
  }

  /**
   * Helper function - get current DB connection object
   */
  protected function getDb()
  {
    if ( !$this->db ) $this->db = Helper::getDbObject();
    return $this->db;
  }

  private $db = null;
}
