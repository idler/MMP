<?php

class FactoryTest extends PHPUnit_Framework_TestCase
{
  public function testGetDefaultControllerFromHelper()
  {
    $this->assertInstanceOf( 'helpController', Helper::getController() );
    $this->assertInstanceOf( 'AbstractController', Helper::getController() );
  }

  public function testGetSpecifiedControllerFromHelper()
  {
    $this->assertInstanceOf( 'helpController', Helper::getController('help') );
    $this->assertInstanceOf( 'initController', Helper::getController('init') );
  }

  public function testGetNotExistentControllerFromHelperException()
  {
    $this->assertTrue( Helper::getController('foo', array('bar')) === false );
  }
}
