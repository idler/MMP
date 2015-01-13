<?php

class ConnectionTest extends DbTestCase
{
  public function testConnectionFromHelper()
  {
    $db = Helper::getDbObject();
    $this->assertTrue($db->ping());
  }

  public function testDefaultConnectionsIsSame()
  {
    $db1 = Helper::getDbObject();
    $db2 = Helper::getDbObject();
    $this->assertSame($db1, $db2);
  }

  public function testConnectionWithInsertedAndDefaultConfigsAreNotSame()
  {
    $db1 = Helper::getDbObject();
    $db2 = Helper::getDbObject(Helper::getConfig());
    $db3 = Helper::getDbObject();
    $this->assertSame($db1, $db3);
    $this->assertEquals($db1->thread_id,$db3->thread_id);
    $this->assertNotEquals($db1->thread_id, $db2->thread_id);
    $this->assertNotEquals($db3->thread_id, $db2->thread_id);
  }

}
