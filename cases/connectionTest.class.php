<?php
class connectionTest extends UnitTestCase
{
  function testConnectionFromFactory()
  {
    $db = Factory::getDbObject();
    $this->assertTrue($db->ping());
  }

  function testDefaultConnectionsIsSame()
  {
    $db1 = Factory::getDbObject();
    $db2 = Factory::getDbObject();
    $this->assertReference($db1, $db2);
  }

  function testConnectionWithInsertedAndDefaultConfigsAreNotSame()
  {
    $db1 = Factory::getDbObject();
    $db2 = Factory::getDbObject(Factory::getConfig());
    $db3 = Factory::getDbObject();
    $this->assertReference($db1, $db3);
    $this->assertEqual($db1->thread_id,$db3->thread_id);
    $this->assertNotEqual($db1->thread_id, $db2->thread_id);
    $this->assertNotEqual($db3->thread_id, $db2->thread_id);
  }

}