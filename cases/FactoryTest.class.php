<?php

class FactoryTest extends UnitTestCase
{
  function testGetDefaultControllerFromFactory()
  {
    $this->assertIsA(Factory::getController(), 'helpController');
    $this->assertIsA(Factory::getController(), 'AbstractController');
  }

  function testGetSpecifiedControllerFromFactory()
  {
    $this->assertIsA(Factory::getController(array('','help')), 'helpController');
    $this->assertIsA(Factory::getController(array('','init')), 'initController');

  }

  function testGetNotExistentControllerFromFactoryException()
  {
    try{
      Factory::getController(array('foo','bar'));
      $this->fail();
    }catch(Exception  $e)
    {
      $this->pass();
    }
     
  }
}