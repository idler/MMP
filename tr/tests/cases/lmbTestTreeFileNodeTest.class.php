<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbTestTreeFileNode.class.php');

class lmbTestTreeFileNodeTest extends lmbTestRunnerBase
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

  function testCreateTestCase()
  {
    $foo = new GeneratedTestClass();
    $bar = new GeneratedTestClass();
    file_put_contents(LIMB_VAR_DIR . '/module1.php',
    "<?php\n" . $foo->generateClass() . "\n" . $bar->generateClass() . "\n?>");

    $node = new lmbTestTreeFileNode(LIMB_VAR_DIR . '/module1.php');

    $this->_runNodeAndAssertOutput($node, $foo->getOutput() . $bar->getOutput());
  }

  function testCreateTestCaseIgnoreJunkClasses()
  {
    $foo = new GeneratedTestClass();
    $bar = new GeneratedTestClass();
    file_put_contents(LIMB_VAR_DIR . '/module2.php',
    "<?php\n" .
    "//class Foo extends UnitTestCase\n" .
    "\$a = 'class Junky';\n" .
    $foo->generateClass() . "\n" .
    $bar->generateClass() . "\n?>");

    $node = new lmbTestTreeFileNode(LIMB_VAR_DIR . '/module2.php');

    $this->_runNodeAndAssertOutput($node, $foo->getOutput() . $bar->getOutput());
  }

  function testCreateTestClassesWithSomeClassesAlreadyIncluded()
  {
    $foo = new GeneratedTestClass();
    $bar = new GeneratedTestClass();
    file_put_contents($foo_file = LIMB_VAR_DIR . '/foo.php', $foo->generate());
    file_put_contents($bar_file = LIMB_VAR_DIR . '/bar.php',
    "<?php\n" .
    "require_once('$foo_file');\n" .
    $bar->generateClass() .
    "\n?>");

    $node_bar = new lmbTestTreeFileNode(LIMB_VAR_DIR . '/bar.php');
    $this->_runNodeAndAssertOutput($node_bar, $bar->getOutput());

    $node_foo = new lmbTestTreeFileNode(LIMB_VAR_DIR . '/foo.php');
    $this->_runNodeAndAssertOutput($node_foo, $foo->getOutput());
  }

  function testGetTestLabel()
  {
    $foo = new GeneratedTestClass();
    file_put_contents(LIMB_VAR_DIR . '/foo.php', $foo->generate());

    $node = new lmbTestTreeFileNode(LIMB_VAR_DIR . '/foo.php');
    $this->assertEqual($node->getTestLabel(), 'foo.php');
    $group = $node->createTestCase();
    $this->assertEqual($group->getLabel(), 'foo.php');
  }
}


