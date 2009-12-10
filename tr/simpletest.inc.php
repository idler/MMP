<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

/**
 * @package tests_runner
 * @version $Id$
 */
@define('SIMPLE_TEST', dirname(__FILE__) . '/lib/simpletest/');

if(!@include_once(SIMPLE_TEST . '/unit_tester.php'))
{
  echo('SIMPLE_TEST constant doesn\'t point to SimpleTest installation directory(' . SIMPLE_TEST . ')');
  exit(1);
}

require_once(SIMPLE_TEST . '/mock_objects.php');
require_once(SIMPLE_TEST . '/reporter.php');

