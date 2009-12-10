<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/lmbTestTreePath.class.php');

/**
 * abstract class lmbTestTreeNode.
 *
 * @package tests_runner
 * @version $Id: lmbTestTreeNode.class.php 7486 2009-01-26 19:13:20Z pachanga $
 */
class lmbTestTreeNode
{
  protected $parent;
  protected $children = array();

  function setParent($parent)
  {
    $this->parent = $parent;
  }

  function getParent()
  {
    return $this->parent;
  }

  function addChild($child)
  {
    $child->setParent($this);
    $this->children[] = $child;
  }

  function getChildren()
  {
    $this->_loadChildren();
    return $this->children;
  }

  function getParents()
  {
    $parents = array();
    $node = $this->parent;
    while($node)
    {
      array_unshift($parents, $node);
      $node = $node->getParent();
    }
    return $parents;
  }  

  protected function _loadChildren(){}

  function findChildByPath($path)
  {
    return $this->_traverseArrayPath(lmbTestTreePath :: toArray($path));
  }

  protected function _traverseArrayPath($array_path, &$nodes = array())
  {
    $nodes[] = $this;

    // return itself in case of / path
    if(!$array_path)
      return $this;

    if(sizeof($array_path) == 1)
    {
      $child = $this->_getImmediateChildByIndex(array_shift($array_path));
      $nodes[] = $child;
      return $child;
    }

    $index = array_shift($array_path);

    if(!$child = $this->_getImmediateChildByIndex($index))
      return null;

    if($child->isTerminal())
      return null;

    return $child->_traverseArrayPath($array_path, $nodes);
  }

  protected function _getImmediateChildByIndex($index)
  {
    $children = $this->getChildren();
    if(isset($children[$index]))
      return $children[$index];
  }

  function isSkipped()
  {
    return false;
  }

  function isTerminal()
  {
    return false;
  }

  function getTestLabel()
  {
    return 'Test Group';
  }

  function createTestCase($is_first = true)
  {
    if($is_first && $this->_hasSkippedParents())
      return null;

    $test = $this->_doCreateTestCase();

    //all parents are traversed in case some test customization is required
    if($is_first)
    {
      foreach($this->getParents() as $node)
        $node->_prepareTestCase($test);
    }

    $this->_prepareTestCase($test);
    //using getter instead of raw property, since child classes may need lazy loading 
    $children = $this->getChildren();
    foreach($children as $child)
    {
      if($child->isSkipped())
        continue;
      $test->addTestCase($child->createTestCase(false));
    }
    return $test;
  }

  protected function _hasSkippedParents()
  {
    if(!$this->parent)
      return false;

    $parent = $this->parent;
    while($parent)
    {
      if($parent->isSkipped())
        return true;
      $parent = $parent->getParent();
    }
    return false;
  }

  protected function _doCreateTestCase()
  {
    //we need to delay inclusion of SimpleTest as much as possible
    require_once(dirname(__FILE__) . '/lmbTestGroup.class.php');
    return new lmbTestGroup($this->getTestLabel());
  }

  protected function _prepareTestCase($test){}
}


