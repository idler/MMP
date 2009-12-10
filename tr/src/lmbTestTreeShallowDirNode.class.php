<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/lmbTestTreeNode.class.php');
require_once(dirname(__FILE__) . '/lmbDetachedFixture.class.php');

/**
 * class lmbTestTreeShallowDirNode.
 *
 * @package tests_runner
 * @version $Id: lmbTestTreeShallowDirNode.class.php 6020 2007-06-27 15:12:32Z pachanga $
 */
class lmbTestTreeShallowDirNode extends lmbTestTreeNode
{
  protected $dir;
  protected $skipped;

  function __construct($dir)
  {
    if(!is_dir($dir))
      throw new Exception("'$dir' is not a directory!");

    $this->dir = $dir;
  }

  //move this one to a better place, lmbTestDirArtifacts?  
  static function hasArtifacts($dir)
  {
    $artifacts = array('.init.php',
                       '.setup.php',
                       '.teardown.php',
                       '.ignore.php',
                       '.skipif.php');
    foreach($artifacts as $artifact)
    {
      if(file_exists($dir . '/' . $artifact))
        return true;
    }
    return false;
  }

  function getDir()
  {
    return $this->dir;
  }

  function getTestLabel()
  {
    if(file_exists($this->dir . '/.description'))
      return file_get_contents($this->dir . '/.description');
    else
      return 'Group test in "' . $this->dir . '"';
  }

  protected function _prepareTestCase($test)
  {
    $fixture = new lmbDetachedFixture($this->dir . '/.setup.php',
                                      $this->dir . '/.teardown.php');
    //set this fixture to be the first one
    $test->addFixture($fixture);
  }

  function isSkipped()
  {
    if(!is_null($this->skipped))
      return $this->skipped;

    if(file_exists($this->dir . '/.skipif.php'))
      $this->skipped = (bool)include($this->dir . '/.skipif.php');
    elseif(file_exists($this->dir . '/.ignore.php'))//deprecated
      $this->skipped = (bool)include($this->dir . '/.ignore.php');
    else
      $this->skipped = false;

    return $this->skipped;
  }
}


