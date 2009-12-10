<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbTestGroup.class.php');
require_once(dirname(__FILE__) . '/../../src/lmbDetachedFixture.class.php');

Mock :: generate('lmbDetachedFixture', 'MockDetachedFixture');

class DetachedFixtureStub
{
  protected $me;

  function __construct($name)
  {
    $this->me = $name;
  }

  function setUp()
  {
    echo 'setup ' . $this->me;
  }

  function tearDown()
  {
    echo 'teardown ' . $this->me;
  }
}

class lmbTestGroupTest extends lmbTestRunnerBase
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

  function testAddFixture()
  {
    $fixture = new MockDetachedFixture();

    $fixture->expectOnce('setUp');
    $fixture->expectOnce('tearDown');

    $group = new lmbTestGroup(LIMB_VAR_DIR);
    $group->addFixture($fixture);

    ob_start();
    $group->run(new SimpleReporter());
    ob_end_clean();
  }

  function testSeveralFixtures()
  {
    $fixture1 = new DetachedFixtureStub('1');
    $fixture2 = new DetachedFixtureStub('2');

    $group = new lmbTestGroup(LIMB_VAR_DIR);

    //fixture is setup once added
    ob_start();
    $group->addFixture($fixture1);
    $group->addFixture($fixture2);

    $group->run(new SimpleReporter());
    $str = ob_get_contents();
    ob_end_clean(); 
    $this->assertEqual($str, 'setup 1setup 2teardown 2teardown 1');
  }
}

