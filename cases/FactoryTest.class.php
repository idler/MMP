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
    $this->assertIsA(Helper::getController(array('name' => 'help', 'args' => array())), 'helpController');
    $this->assertIsA(Helper::getController(array('name' => 'init', 'args' => array())), 'initController');

  }

  function testGetNotExistentControllerFromHelperException()
  {
    try{
      Helper::getController(array('name' => 'foo', 'args' => array('bar')));
      $this->fail();
    }catch(Exception  $e)
    {
      $this->pass();
    }
     
  }
}