<?php

class HelperTest extends UnitTestCase
{
  function testGetDefaultControllerFromHelper()
  {
    $this->assertIsA(Helper::getController(), 'helpController');
    $this->assertIsA(Helper::getController(), 'AbstractController');
  }

  function testGetSpecifiedControllerFromHelper()
  {
    $this->assertIsA(Helper::getController('help'), 'helpController');
    $this->assertIsA(Helper::getController('init'), 'initController');

  }

  function testGetNotExistentControllerFromHelperException()
  {
    if(Helper::getController('foo', array('bar')) !== false)
      $this->fail();
    else
      $this->pass();
     
  }
}