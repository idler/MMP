<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbTestTreeFilePathNode.class.php');

class lmbTestTreeFilePathNodeTest extends lmbTestRunnerBase
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

  function testLoadChildrenForFilePathEndingWithFile()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');
    touch($this->var_dir . '/a/b/bar_test.php');
    touch($this->var_dir . '/a/b/foo_test.php');

    $node = new lmbTestTreeFilePathNode($this->var_dir . '/a/b/foo_test.php', -3);
    $kids = $node->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeShallowDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a'));

    $kids = $kids[0]->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeShallowDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a/b'));

    $kids = $kids[0]->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeFileNode');
    $this->assertEqual($kids[0]->getFile(), realpath($this->var_dir . '/a/b/foo_test.php'));
  }

  function testLoadChildrenForFilePathEndingWithDir()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');
    touch($this->var_dir . '/a/b/bar_test.php');
    touch($this->var_dir . '/a/b/foo_test.php');

    $node = new lmbTestTreeFilePathNode($this->var_dir . '/a/b', -2);
    $kids = $node->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeShallowDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a'));

    $kids = $kids[0]->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a/b'));
  }

  function testLoadChildrenUseAutoOffsetForFile()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');
    touch($this->var_dir . '/a/b/bar_test.php');
    touch($this->var_dir . '/a/b/foo_test.php');

    $node = new lmbTestTreeFilePathNode($this->var_dir . '/a/b/bar_test.php');
    $kids = $node->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeFileNode');
    $this->assertEqual($kids[0]->getFile(), realpath($this->var_dir . '/a/b/bar_test.php'));
  }

  function testLoadChildrenUseAutoOffsetForFileWithArtifacts()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');
    file_put_contents($this->var_dir . '/a/.skipif.php', '<?php return false; ?>');
    touch($this->var_dir . '/a/b/bar_test.php');
    touch($this->var_dir . '/a/b/foo_test.php');

    $node = new lmbTestTreeFilePathNode($this->var_dir . '/a/b/bar_test.php');
    $kids = $node->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeShallowDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a'));

    $kids = $kids[0]->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeShallowDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a/b'));

    $kids = $kids[0]->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeFileNode');
    $this->assertEqual($kids[0]->getFile(), realpath($this->var_dir . '/a/b/bar_test.php'));
  }

  function testLoadChildrenUseAutoOffsetForDir()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');
    touch($this->var_dir . '/a/b/bar_test.php');
    touch($this->var_dir . '/a/b/foo_test.php');

    $node = new lmbTestTreeFilePathNode($this->var_dir . '/a/b/');
    $kids = $node->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a/b'));
  }

  function testLoadChildrenUseAutoOffsetForDirWithArtifacts()
  {
    mkdir($this->var_dir . '/a');
    mkdir($this->var_dir . '/a/b');
    file_put_contents($this->var_dir . '/a/.skipif.php', '<?php return false; ?>');
    touch($this->var_dir . '/a/b/bar_test.php');
    touch($this->var_dir . '/a/b/foo_test.php');

    $node = new lmbTestTreeFilePathNode($this->var_dir . '/a/b');
    $kids = $node->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeShallowDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a'));

    $kids = $kids[0]->getChildren();
    $this->assertEqual(sizeof($kids), 1);
    $this->assertIsA($kids[0], 'lmbTestTreeDirNode');
    $this->assertEqual($kids[0]->getDir(), realpath($this->var_dir . '/a/b'));
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

    $node = new lmbTestTreeFilePathNode($this->var_dir . '/a', -1);
    $child = $node->findChildByPath('/0/0/1');
    $this->_runNodeAndAssertOutput($child, "setupsetup2" . $test2->getOutput() . "teardown2teardown");
  }
}


