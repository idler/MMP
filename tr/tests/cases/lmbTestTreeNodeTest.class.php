<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbTestTreeNode.class.php');

class lmbTestTreeNodeTest extends lmbTestRunnerBase
{
  function testAddChildren()
  {
    $a = new lmbTestTreeNode();
    $a_b = new lmbTestTreeNode();

    $root = new lmbTestTreeNode();
    $root->addChild($a);
    $a->addChild($a_b);

    $this->assertNull($root->getParent());
    $this->assertTrue($a->getParent() === $root);
    $this->assertTrue($a_b->getParent() === $a);
  }

  function testFindChildByPath()
  {
    $a = new lmbTestTreeNode();
    $a_b = new lmbTestTreeNode();
    $c = new lmbTestTreeNode();

    $root = new lmbTestTreeNode();
    $root->addChild($a);
    $a->addChild($a_b);
    $root->addChild($c);

    $this->assertTrue($root->findChildByPath('/') === $root);
    $this->assertTrue($root->findChildByPath('/0') === $a);
    $this->assertTrue($root->findChildByPath('/0/0') === $a_b);
    $this->assertTrue($root->findChildByPath('/1') === $c);
    $this->assertNull($root->findChildByPath('/100'));

    $this->assertTrue($a->findChildByPath('/0') === $a_b);
    $this->assertNull($a->findChildByPath('/100'));
  }
}


