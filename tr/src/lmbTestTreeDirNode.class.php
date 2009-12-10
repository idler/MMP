<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/lmbTestTreeShallowDirNode.class.php');
require_once(dirname(__FILE__) . '/lmbTestTreeFileNode.class.php');
require_once(dirname(__FILE__) . '/lmbDetachedFixture.class.php');
require_once(dirname(__FILE__) . '/lmbTestFileFilter.class.php');
require_once(dirname(__FILE__) . '/lmbTestOptions.class.php');

/**
 * class lmbTestTreeDirNode.
 *
 * @package tests_runner
 * @version $Id: lmbTestTreeDirNode.class.php 7486 2009-01-26 19:13:20Z pachanga $
 */
class lmbTestTreeDirNode extends lmbTestTreeShallowDirNode
{
  protected static $file_filter;
  protected $loaded;

  function createTestCase($is_first = true)
  {
    $this->_loadChildren();
    return parent :: createTestCase($is_first);
  }

  static function getFileFilter()
  {
    if(!is_object(self :: $file_filter))
      self :: setFileFilter(lmbTestOptions :: get('file_filter'));
    return self :: $file_filter;
  }

  static function setFileFilter($filter)
  {
    $prev = self :: $file_filter;

    if(is_object($filter))
      $obj = $filter;
    elseif(is_array($filter))
      $obj = new lmbTestFileFilter($filter);
    else
      $obj = new lmbTestFileFilter(explode(';', $filter));

    self :: $file_filter = $obj;
    return $prev;
  }

  function _loadChildren()
  {
    if(!is_null($this->loaded) && $this->loaded)
      return;

    $dir_items = $this->getDirItems();

    foreach($dir_items as $item)
    {
      if(is_dir($item))
        $this->addChild(new lmbTestTreeDirNode($item));
      else
        $this->addChild(new lmbTestTreeFileNode($item));
    }
    $this->loaded = true;
  }

  function getDirItems()
  {
    $clean_and_sorted = array();
    $dir_items = scandir($this->dir);

    foreach($dir_items as $item)
    {
      if($item{0} == '.' || (!is_dir($this->dir . '/' . $item) && !$this->_isFileAllowed($item)))
        continue;
      $clean_and_sorted[$item] = $this->dir . '/' . $item;
    }

    uasort($clean_and_sorted, array($this, '_dirSorter'));
    return $clean_and_sorted;
  }

  protected function _isFileAllowed($file)
  {
    $filter = self :: getFileFilter();

    if($filter && !$filter->match($file))
      return false;
    return true;
  }

  protected function _dirSorter($a, $b)
  {
    if(is_dir($a) && !is_dir($b))
      return -1;
    elseif(!is_dir($a) && is_dir($b))
      return 1;
    return strcmp($a, $b);
  }
}


