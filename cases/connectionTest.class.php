<?php
class connectionTest extends UnitTestCase
{
  function testConnectionFromHelper()
  {
    $db = Helper::getDbObject();
    $this->assertTrue($db->ping());
  }

  function testDefaultConnectionsIsSame()
  {
    $db1 = Helper::getDbObject();
    $db2 = Helper::getDbObject();
    $this->assertReference($db1, $db2);
  }

  function testConnectionWithInsertedAndDefaultConfigsAreNotSame()
  {
    $db1 = Helper::getDbObject();
    $db2 = Helper::getDbObject(Helper::getConfig());
    $db3 = Helper::getDbObject();
    $this->assertReference($db1, $db3);
    $this->assertEqual($db1->thread_id,$db3->thread_id);
    $this->assertNotEqual($db1->thread_id, $db2->thread_id);
    $this->assertNotEqual($db3->thread_id, $db2->thread_id);
  }

}