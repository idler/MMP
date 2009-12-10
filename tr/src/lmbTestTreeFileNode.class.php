<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__). '/lmbTestTreeTerminalNode.class.php');
require_once(dirname(__FILE__). '/lmbTestUserException.class.php');

/**
 * class lmbTestTreeFileNode.
 *
 * @package tests_runner
 * @version $Id: lmbTestTreeFileNode.class.php 7486 2009-01-26 19:13:20Z pachanga $
 */
class lmbTestTreeFileNode extends lmbTestTreeTerminalNode
{
  protected $file;

  function __construct($file)
  {
    $this->file = $file;
  }

  function getFile()
  {
    return $this->file;
  }

  function getTestLabel()
  {
    return basename($this->file);
  }

  protected function _getClassesDefinedInFile()
  {
     if(!preg_match_all('~\Wclass\s+(\w+)~', file_get_contents($this->file), $matches))
       return array();
     return $matches[1];
  }

  protected function _isClassFiltered($class)
  {
    $filter = lmbTestOptions :: get('tests_filter');
    if(!$filter)
      return false;
    return !in_array($class, $filter);
  }

  protected function _isClassGroupFiltered($class)
  {
    if(!$groups = lmbTestOptions :: get('groups_filter'))
      return false;

    $refclass = new ReflectionClass($class);
    $doc = $refclass->getDocComment();

    //if there's a group filter and no group annotation class is filtered
    if(!preg_match('~.*@group\s+([^\n]+).*~s', $doc, $matches))
      return true;

    $doc_groups = array_map('trim', explode(',', trim($matches[1])));
    foreach($doc_groups as $group)
    {
      if(in_array($group, $groups))
        return false;
    }
    return true;
  }

  protected function _isFiltered($class)
  {
    return $this->_isClassFiltered($class) ||
           $this->_isClassGroupFiltered($class);
  }

  protected function _prepareTestCase($test)
  {
    require_once($this->file);
    $candidates = $this->_getClassesDefinedInFile();

    $loader = new SimpleFileLoader();
    foreach($loader->selectRunnableTests($candidates) as $class)
    {
      if($this->_isFiltered($class))
        continue;

      $case = new $class();
      $case->filter(lmbTestOptions :: get('methods_filter'));
      $test->addTestCase($case);
      //a dirty SimpleTest PHP4 compatibility hack
      //otherwise $case is overwrittne since it is a reference
      unset($case);
    }
  }
}

