<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbPHPUnitTestCase.class.php');

class lmbTestPHPUnitTestCaseAdapterTest extends lmbTestRunnerBase
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

  function testAssertEquals()
  {
  	$this->_mustBePassed('$this->assertEquals(1, 1);');
    $this->_mustBeFailed('$this->assertEquals(42, 0);');
    $this->_mustBeFailed('$this->assertEquals(1, 0, "custom message");', 'custom message');
  }

  function testAssertRegexp()
  {
    $this->_mustBePassed('$this->assertRegexp("/bAr/i", "foo-bar-baz");');
    $this->_mustBeFailed('$this->assertRegexp("/bug/", "foo-bar-baz");');
    $this->_mustBeFailed('$this->assertRegexp("/42/", 0, "custom message");', 'custom message');
  }

  function testError()
  {
    $this->_mustBeFailed('$this->fail("custom message");', 'custom message');
  }

  protected function _mustBePassed($code)
  {
    return $this->_testCodeInAdapter($code, true);
  }

  protected function _mustBeFailed($code, $fail_message = null)
  {
    return $this->_testCodeInAdapter($code, false, $fail_message);
  }

  protected function _testCodeInAdapter($code, $pass, $fail_message = null)
  {
  	$test = new GeneratedTestClass();
    $test->setParentClass('lmbPHPUnitTestCase');

    $test_file = LIMB_VAR_DIR . '/' . uniqid() . '.php';
    file_put_contents($test_file, $test->generate($code));

    $group = new lmbTestGroup();
    $group->addFile($test_file);
    $group->run($reporter = new lmbTestReporter());

    $this->assertEqual($pass, $reporter->getStatus());
    if (!$pass &&  $fail_message)
        $this->assertPattern('/' . $fail_message . '/', $reporter->getOutput(), 'Wrong error message');

    return $reporter->getOutput();
  }
}