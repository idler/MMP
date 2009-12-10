<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

@define('LIMB_TESTS_RUNNER_FILE_FILTER', '*Test.class.php;*test.php;*Test.php');

/**
 * class lmbTestOptions.
 *
 * @package tests_runner
 * @version $Id$
 */
class lmbTestOptions
{
  static protected $options = array('file_filter' => LIMB_TESTS_RUNNER_FILE_FILTER,
                           'tests_filter' => array(),
                           'methods_filter' => array(),
                           'groups_filter' => array(),
                           'verbose' => false);
  static protected $has_defaults = false;

  static function set($name, $value)
  {
    self :: _initDefaults();
    self :: _check($name);

    self :: $options[$name] = $value;
  }

  static function get($name)
  {
    self :: _initDefaults();
    self :: _check($name);

    if(isset(self :: $options[$name]))
      return self :: $options[$name];
  }

  static protected function _initDefaults()
  {
    if(self :: $has_defaults)
      return;

    self :: $has_defaults = true;
  }

  static protected function _check($key)
  {
    if(!in_array($key, array_keys(self :: $options)))
      throw new Exception("Test option '$key' is not supported");
  }
}


