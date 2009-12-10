<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com 
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html 
 */
require_once(dirname(__FILE__) . '/../../src/lmbTestFileFilter.class.php');

class lmbTestFileFilterTest extends UnitTestCase
{
  function testOneFilterMatch()
  {
    $filter = new lmbTestFileFilter(array('*Test'));
    $this->assertTrue($filter->match('/wow/hey/myTest'));
  }

  function testSeveralFiltersMatch()
  {
    $filter = new lmbTestFileFilter(array('*Test', '*yo'));
    $this->assertTrue($filter->match('/wow/hey/yo'));
  }

  function testNoMatch()
  {
    $filter = new lmbTestFileFilter(array('*Test'));
    $this->assertFalse($filter->match('/wow/hey/wow'));
  }

  function testMatchBasenameOnly()
  {
    $filter = new lmbTestFileFilter(array('*Test'));
    $this->assertFalse($filter->match('/wow/heyTest/wow'));
    $this->assertTrue($filter->match('/wow/foo/heyTest'));
  }
}



