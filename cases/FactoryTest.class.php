<?php

class HelperTest extends UnitTestCase
{
  public function testGetDefaultControllerFromHelper()
  {
    $this->assertIsA(Helper::getController(), 'helpController');
    $this->assertIsA(Helper::getController(), 'AbstractController');
  }

  public function testGetSpecifiedControllerFromHelper()
  {
    $this->assertIsA(Helper::getController('help'), 'helpController');
    $this->assertIsA(Helper::getController('init'), 'initController');

  }

  public function testGetNotExistentControllerFromHelperException()
  {
    if(Helper::getController('foo', array('bar')) !== false)
      $this->fail();
    else
      $this->pass();
     
  }
}