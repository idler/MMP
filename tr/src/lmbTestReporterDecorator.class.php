<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

/**
 * class lmbTestReporterDecorator.
 *
 * @package tests_runner
 * @version $Id$
 */
class lmbTestReporterDecorator extends SimpleReporterDecorator
{
  function paintCaseEnd($test_name)
  {
    $this->_reporter->paintCaseEnd($test_name);

    echo $this->_reporter->getTestCaseProgress() . " of " . $this->_reporter->getTestCaseCount() . " done({$test_name})\n";
  }
}

