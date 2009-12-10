<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

/**
 * class lmbTestGroup.
 *
 * @package tests_runner
 * @version $Id: lmbTestGroup.class.php 7486 2009-01-26 19:13:20Z pachanga $
 */
class lmbTestGroup extends TestSuite
{
  protected $_fixtures = array();

  function addFixture($fixture)
  {
    $this->_fixtures[] = $fixture;
    //fixture is setup once added, since fixture may contain some stuff 
    //required even before actual tests execution
    $fixture->setup();
  }

  function run($reporter)
  {
    $res = parent :: run($reporter);

    $this->_tearDownFixture();

    return $res;
  }

  protected function _tearDownFixture()
  {
    foreach(array_reverse($this->_fixtures) as $fixture)
      $fixture->tearDown();
  }
}

