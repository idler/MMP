<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbTestTreeDirNode.class.php');
require_once(dirname(__FILE__) . '/../../src/lmbTestFileFilter.class.php');

class lmbTestTreeDirNodeTest extends lmbTestRunnerBase
{
  protected $var_dir;

  function setUp()
  {
    $this->_rmdir(LIMB_VAR_DIR);
    //we need unique temporary dir since test modules are included once
    $this->var_dir = LIMB_VAR_DIR . '/' . mt_rand();
    mkdir(LIMB_VAR_DIR);
    mkdir($this->var_dir);
  }

  function tearDown()
  {
    $this->_rmdir(LIMB_VAR_DIR);
  }

  function testGetChildren()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');
    touch($this->var_dir . '/a/b/bar_test.php');
    touch($this->var_dir . '/a/b/foo_test.php');

    $node = new lmbTestTreeDirNode($this->var_dir);
    $child_nodes = $node->getChildren();
    $this->assertEqual(sizeof($child_nodes), 1);
    $this->assertEqual($child_nodes[0]->getDir(), $this->var_dir . '/a');
    $this->assertFalse($child_nodes[0]->isTerminal());

    $sub_child_nodes = $child_nodes[0]->getChildren();
    $this->assertEqual(sizeof($sub_child_nodes), 1);
    $this->assertEqual($sub_child_nodes[0]->getDir(), $this->var_dir . '/a/b');
    $this->assertFalse($sub_child_nodes[0]->isTerminal());

    $terminal_nodes = $sub_child_nodes[0]->getChildren();

    $this->assertTrue($terminal_nodes[0]->getFile(), $this->var_dir . '/a/b/bar_test.php');
    $this->assertTrue($terminal_nodes[0]->isTerminal());
    $this->assertTrue($terminal_nodes[1]->getFile(), $this->var_dir . '/a/b/foo_test.php');
    $this->assertTrue($terminal_nodes[1]->isTerminal());
  }

  function testUseFileFilter()
  {
    touch($this->var_dir . '/bar_test.php');
    touch($this->var_dir . '/bah.php');
    touch($this->var_dir . '/junk.php');
    touch($this->var_dir . '/FooYo.class.php');

    $prev_filter = lmbTestTreeDirNode :: setFileFilter(array('*test.php', '*Yo.class.php'));

    $node = new lmbTestTreeDirNode($this->var_dir);
    $nodes = $node->getChildren();
    $this->assertEqual(sizeof($nodes), 2);
    $this->assertEqual($nodes[0]->getFile(), $this->var_dir . '/FooYo.class.php');
    $this->assertEqual($nodes[1]->getFile(), $this->var_dir . '/bar_test.php');

    lmbTestTreeDirNode :: setFileFilter($prev_filter);
  }

  function testUseFileFilterAndClassFormat()
  {
    $foo = new GeneratedTestClass();
    touch($this->var_dir . '/junk.php');
    touch($this->var_dir . '/' . $foo->getFileName() . '.yo');

    $prev_filter = lmbTestTreeDirNode :: setFileFilter('*.class.php.yo');

    $node = new lmbTestTreeDirNode($this->var_dir);
    $nodes = $node->getChildren();
    $this->assertEqual(sizeof($nodes), 1);
    $this->assertEqual($nodes[0]->getFile(), $this->var_dir . '/' . $foo->getFileName() . '.yo');

    lmbTestTreeDirNode :: setFileFilter($prev_filter);
  }

  function testFindChildByPath()
  {
    mkdir($this->var_dir . '/a');

    touch($this->var_dir . '/a/bar_test.php');
    touch($this->var_dir . '/a/foo_test.php');

    $node = new lmbTestTreeDirNode($this->var_dir);
    $child_node = $node->findChildByPath('/0/1');
    $this->assertTrue($child_node->isTerminal());
    $this->assertEqual($child_node->getFile(), $this->var_dir . '/a/foo_test.php');
  }

  function testFindNonTerminalGroupByPath()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');
    touch($this->var_dir . '/a/b/bar_test.php');
    touch($this->var_dir . '/a/b/foo_test.php');

    $node = new lmbTestTreeDirNode($this->var_dir);
    $child_node = $node->findChildByPath('/0/0');
    $this->assertFalse($child_node->isTerminal());
    $this->assertEqual($child_node->getDir(), $this->var_dir . '/a/b');
  }

  function testFindChildByOnlySlashPath()
  {
    $node = new lmbTestTreeDirNode($this->var_dir);
    $child_node = $node->findChildByPath('/');
    $this->assertEqual($child_node, $node);
  }

  function testCreateTestCase()
  {
    mkdir($this->var_dir . '/a');

    $test1 = new GeneratedTestClass();
    $test2 = new GeneratedTestClass();

    file_put_contents($this->var_dir . '/a/.setup.php', '<?php echo "wow"; ?>');
    file_put_contents($this->var_dir . '/a/.teardown.php', '<?php echo "hey"; ?>');

    file_put_contents($this->var_dir . '/a/bar_test.php', $test1->generate());
    file_put_contents($this->var_dir . '/a/foo_test.php', $test2->generate());

    $node = new lmbTestTreeDirNode($this->var_dir);

    $this->_runNodeAndAssertOutput($node, "wow" . $test1->getOutput() . $test2->getOutput() . "hey");
  }

  function testUseExternalTestLabel()
  {
    file_put_contents($this->var_dir . '/.description', 'Foo');

    $node = new lmbTestTreeDirNode($this->var_dir);
    $this->assertEqual($node->getTestLabel(), 'Foo');
    $group = $node->createTestCase();
    $this->assertEqual($group->getLabel(), 'Foo');
  }

  function testGetDefaultTestLabel()
  {
    $node = new lmbTestTreeDirNode($this->var_dir);
    $this->assertEqual($node->getTestLabel(), 'Group test in "' . $this->var_dir . '"');
    $group = $node->createTestCase();
    $this->assertEqual($group->getLabel(), 'Group test in "' . $this->var_dir . '"');
  }

  function testSkipTestsDirectory()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');

    $test1 = new GeneratedTestClass();
    $test2 = new GeneratedTestClass();

    file_put_contents($this->var_dir . '/a/bar_test.php', $test1->generate());
    file_put_contents($this->var_dir . '/a/b/foo_test.php', $test2->generate());

    file_put_contents($this->var_dir . '/a/b/.skipif.php', '<?php return true; ?>');

    $root_node = new lmbTestTreeDirNode($this->var_dir);

    $this->_runNodeAndAssertOutput($root_node, $test1->getOutput());
  }

  function testDontSkipTestsDirectory()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');

    $test1 = new GeneratedTestClass();
    $test2 = new GeneratedTestClass();

    file_put_contents($this->var_dir . '/a/bar_test.php', $test1->generate());
    file_put_contents($this->var_dir . '/a/b/foo_test.php', $test2->generate());

    file_put_contents($this->var_dir . '/a/b/.skipif.php', '<?php return false; ?>');

    $root_node = new lmbTestTreeDirNode($this->var_dir);

    $this->_runNodeAndAssertOutput($root_node, $test2->getOutput() . $test1->getOutput());
  }

  function testSkippedDirFixtureSkippedToo()
  {
    mkdir($this->var_dir . '/a');
    $test = new GeneratedTestClass();

    file_put_contents($this->var_dir . '/a/.setup.php', '<?php echo "No!" ?>');
    file_put_contents($this->var_dir . '/a/bar_test.php', $test->generate());

    file_put_contents($this->var_dir . '/a/.skipif.php', '<?php return true; ?>');

    $root_node = new lmbTestTreeDirNode($this->var_dir);

    $this->_runNodeAndAssertOutput($root_node, '');
  }

  function testParentFixturesAreExecuted()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');

    file_put_contents($this->var_dir . '/a/.setup.php', '<?php echo "setup"; ?>');
    file_put_contents($this->var_dir . '/a/.teardown.php', '<?php echo "teardown"; ?>');

    file_put_contents($this->var_dir . '/a/b/.setup.php', '<?php echo "setup2"; ?>');
    file_put_contents($this->var_dir . '/a/b/.teardown.php', '<?php echo "teardown2"; ?>');

    $test1 = new GeneratedTestClass();
    $test2 = new GeneratedTestClass();

    file_put_contents($this->var_dir . '/a/b/bar_test.php', $test1->generate());
    file_put_contents($this->var_dir . '/a/b/foo_test.php', $test2->generate());

    $node = new lmbTestTreeDirNode($this->var_dir);
    $child = $node->findChildByPath('/0/0/1');
    $this->_runNodeAndAssertOutput($child, "setupsetup2" . $test2->getOutput() . "teardown2teardown");
  }
}


