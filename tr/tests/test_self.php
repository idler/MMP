<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

if(isset($argv[1]))
  define('SIMPLE_TEST', $argv[1]);
require_once(dirname(__FILE__) . '/cases/.setup.php');

$group = new TestSuite();
foreach(glob(dirname(__FILE__) . '/cases/*Test.class.php') as $file)
  $group->addTestFile($file);

if(!$res = $group->run(new TextReporter()))
  exit(1);


