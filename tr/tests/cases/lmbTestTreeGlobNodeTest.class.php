<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbTestTreeGlobNode.class.php');

class lmbTestTreeGlobNodeTest extends lmbTestRunnerBase
{
  function setUp()
  {
    $this->_rmdir(LIMB_VAR_DIR);
    mkdir(LIMB_VAR_DIR);
  }

  function tearDown()
  {
    $this->_rmdir(LIMB_VAR_DIR);
  }

  function testGlobMatch()
  {
    mkdir(LIMB_VAR_DIR . '/a');
    mkdir(LIMB_VAR_DIR . '/a/tests');
    mkdir(LIMB_VAR_DIR . '/b');
    mkdir(LIMB_VAR_DIR . '/b/tests');

    $test1 = new GeneratedTestClass();
    $test2 = new GeneratedTestClass();
    $junk = new GeneratedTestClass();

    file_put_contents(LIMB_VAR_DIR . '/a/tests/bar_test.php', $test1->generate());
    file_put_contents(LIMB_VAR_DIR . '/b/tests/foo_test.php', $test2->generate());
    file_put_contents(LIMB_VAR_DIR . '/b/junk_test.php', $junk->generate()); //should be ignored

    $root_node = new lmbTestTreeGlobNode(LIMB_VAR_DIR . '/*/tests');

    $this->_runNodeAndAssertOutput($root_node, $test1->getOutput() . $test2->getOutput());
  }

  function testNotAGlobMatch()
  {
    mkdir(LIMB_VAR_DIR . '/a');

    $test1 = new GeneratedTestClass();
    $test2 = new GeneratedTestClass();

    file_put_contents(LIMB_VAR_DIR . '/a/bar_test.php', $test1->generate());
    file_put_contents(LIMB_VAR_DIR . '/a/foo_test.php', $test2->generate());

    $root_node = new lmbTestTreeGlobNode(LIMB_VAR_DIR . '/a');

    $this->_runNodeAndAssertOutput($root_node, $test1->getOutput() . $test2->getOutput());
  }
}


