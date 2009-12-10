<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbDetachedFixture.class.php');

class lmbDetachedFixtureTest extends lmbTestRunnerBase
{
  function setUp()
  {
    $this->_rmdir(LIMB_VAR_DIR);
    mkdir(LIMB_VAR_DIR);
  }

  function tearDown()
  {
    $this->_rmdir(LIMB_VAR_DIR);
  }

  function testSetupTearDown()
  {
    file_put_contents(LIMB_VAR_DIR . '/.setup.php', '<?php echo "wow"; ?>');
    file_put_contents(LIMB_VAR_DIR . '/.teardown.php', '<?php echo "hey"; ?>');

    $fixture = new lmbDetachedFixture(LIMB_VAR_DIR . '/.setup.php',
                                      LIMB_VAR_DIR . '/.teardown.php');

    ob_start();
    $fixture->setUp();
    $fixture->tearDown();
    $str = ob_get_contents();
    ob_end_clean();
    $this->assertEqual($str, 'wowhey');
  }

  function testFixtureCanAccessThisWithoutPHPErrors()
  {
    file_put_contents(LIMB_VAR_DIR . '/.setup.php', '<?php $this->test = "test"; ?>');
    file_put_contents(LIMB_VAR_DIR . '/.teardown.php', '<?php echo $this->test; ?>');

    $fixture = new lmbDetachedFixture(LIMB_VAR_DIR . '/.setup.php',
                                      LIMB_VAR_DIR . '/.teardown.php');

    ob_start();

    $old = error_reporting(E_ALL);
    $fixture->setUp();
    $fixture->tearDown();
    error_reporting($old);

    $str = ob_get_contents();
    ob_end_clean();
    $this->assertEqual($str, 'test');
  }
}

