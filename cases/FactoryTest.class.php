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
    $this->assertIsA(Helper::getController(array('','help')), 'helpController');
    $this->assertIsA(Helper::getController(array('','init')), 'initController');

  }

  function testGetNotExistentControllerFromHelperException()
  {
    try{
      Helper::getController(array('foo','bar'));
      $this->fail();
    }catch(Exception  $e)
    {
      $this->pass();
    }
     
  }
}