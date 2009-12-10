<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com 
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html 
 */
require_once(dirname(__FILE__) . '/../../src/lmbTestTreePath.class.php');

class lmbTestTreePathTest extends UnitTestCase
{
  function testToArray()
  {
    $this->assertEqual(lmbTestTreePath :: toArray('/0/1'), array('0', '1'));
    $this->assertEqual(lmbTestTreePath :: toArray('/0/1/'), array('0', '1'));
    $this->assertEqual(lmbTestTreePath :: toArray('/0/1/../'), array('0'));
  }

  function testNormalize()
  {
    $this->assertEqual(lmbTestTreePath :: normalize('/0////'), '/0');
    $this->assertEqual(lmbTestTreePath :: normalize('/0/1/../'), '/0');
  }
}



