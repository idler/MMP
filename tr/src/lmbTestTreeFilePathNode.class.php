<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/lmbTestTreeNode.class.php');
require_once(dirname(__FILE__) . '/lmbTestTreeShallowDirNode.class.php');
require_once(dirname(__FILE__) . '/lmbTestTreeDirNode.class.php');
require_once(dirname(__FILE__) . '/lmbTestTreeFileNode.class.php');

/**
 * class lmbTestTreeFilePathNode.
 *
 * @package tests_runner
 * @version $Id: lmbTestTreeFilePathNode.class.php 6020 2007-06-27 15:12:32Z pachanga $
 */
class lmbTestTreeFilePathNode extends lmbTestTreeNode
{
  protected $file_path;
  protected $offset;

  function __construct($file_path, $offset = null)
  {
    if(!is_file($file_path) && !is_dir($file_path))
      throw new Exception("'$file_path' is not a valid file path!");

    $this->file_path = realpath($file_path);
    $this->offset = $offset;
  }

  function getFilePath()
  {
    return $this->file_path;
  }

  protected function _loadChildren()
  {
    $path_items = $this->_getPathItems();
    $total = count($path_items);
    $current = $this;

    for($i=0;$i<$total;$i++)
    {
      $item = $path_items[$i];
      if(is_dir($item))
      {
        if($i+1 == $total)//is last?
          $current->addChild($new = new lmbTestTreeDirNode($item));
        else
          $current->addChild($new = new lmbTestTreeShallowDirNode($item));
      }
      else
        $current->addChild($new = new lmbTestTreeFileNode($item));

      $current = $new;
    }
  }

  protected function _getPathItems()
  {
    $items = array();
    $current = $this->file_path;
    while(($new = dirname($current)) != $current)
    {
      $items[] = $current;
      $current = $new;
    }

    return $this->_applyOffset(array_reverse($items));
  }

  protected function _applyOffset($items)
  {
    $offset = $this->offset;
    if(is_null($offset))
      $offset = $this->_determineOptimalOffset($items);

    return array_slice($items, $offset);
  }

  protected function _determineOptimalOffset($items)
  {
    $offset = 0;
    $total = count($items);
    for($i=0;$i<$total;$i++)
    {
      $item = $items[$i];
      if(is_file($item) || lmbTestTreeShallowDirNode :: hasArtifacts($item))
        break;
      elseif(is_dir($item) && $i+1 == $total)//last dir should be added anyway
        break;

      $offset++;
    }
    return $offset;
  }
}


