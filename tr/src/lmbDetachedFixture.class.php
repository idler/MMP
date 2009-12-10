<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com 
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html 
 */

/**
 * class lmbDetachedFixture.
 *
 * @package tests_runner
 * @version $Id: lmbDetachedFixture.class.php 7486 2009-01-26 19:13:20Z pachanga $
 */
class lmbDetachedFixture
{
  protected $_setup;
  protected $_teardown;
  protected $_container = array();

  function __construct($setup, $teardown)
  {
    $this->_setup = $setup;
    $this->_teardown = $teardown;
  }

  function setUp()
  {
    if(file_exists($this->_setup))
      include($this->_setup);
  }

  function tearDown()
  {
    if(file_exists($this->_teardown))
      include($this->_teardown);
  }

  function __set($name, $value)
  {
    $this->_container[$name] = $value;
  }

  function __get($name)
  {
    if(isset($this->_container[$name]))
      return $this->_container[$name];
  }
}

