<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/../common.inc.php');
require_once(dirname(__FILE__) . '/../../src/lmbTestShellUI.class.php');

class lmbTestShellUITest extends lmbTestRunnerBase
{
  function setUp()
  {
    $this->_rmdir(LIMB_VAR_DIR);
    mkdir(LIMB_VAR_DIR);
    mkdir(LIMB_VAR_DIR . '/cases');
    $this->_createRunScript();
  }

  function tearDown()
  {
    $this->_rmdir(LIMB_VAR_DIR);
  }

  function testPerformInDirWithAbsolutePath()
  {
    $foo = $this->_createTestCase(LIMB_VAR_DIR . '/cases/foo_test.php');
    $bar = $this->_createTestCase(LIMB_VAR_DIR . '/cases/a/bar_test.php');
    $zoo = $this->_createTestCase(LIMB_VAR_DIR . '/cases/a/z/zoo_test.php');

    $run_dir = LIMB_VAR_DIR . '/cases';
    $ret = $this->_execScript($run_dir, $screen);
    if(!$this->assertEqual($ret, 0))
      echo $screen;

    $this->assertPattern('~1\s+of\s+3\s+done\(' . $zoo->getClass() . '\)~', $screen);
    $this->assertPattern('~2\s+of\s+3\s+done\(' . $bar->getClass() . '\)~', $screen);
    $this->assertPattern('~3\s+of\s+3\s+done\(' . $foo->getClass() . '\)~', $screen);
    $this->assertPattern('~OK~i', $screen);
    $this->assertNoPattern('~Error~i', $screen);
  }

  function testPerformInDirWithRelativePath()
  {
    $foo = $this->_createTestCase(LIMB_VAR_DIR . '/cases/foo_test.php');
    $bar = $this->_createTestCase(LIMB_VAR_DIR . '/cases/a/bar_test.php');
    $zoo = $this->_createTestCase(LIMB_VAR_DIR . '/cases/a/z/zoo_test.php');

    $cwd = getcwd();
    chdir(LIMB_VAR_DIR);
    $ret = $this->_execScript('cases', $screen);

    chdir($cwd);

    if(!$this->assertEqual($ret, 0))
      echo $screen;

    $this->assertPattern('~1\s+of\s+3\s+done\(' . $zoo->getClass() . '\)~', $screen);
    $this->assertPattern('~2\s+of\s+3\s+done\(' . $bar->getClass() . '\)~', $screen);
    $this->assertPattern('~3\s+of\s+3\s+done\(' . $foo->getClass() . '\)~', $screen);
    $this->assertPattern('~OK~i', $screen);
    $this->assertNoPattern('~Error~i', $screen);
  }

  function testPerformTestsWithGlob()
  {
    $foo = $this->_createTestCase(LIMB_VAR_DIR . '/cases/foo_test.php');
    $bar = $this->_createTestCase(LIMB_VAR_DIR . '/cases/a/bar_test.php');
    $zoo = $this->_createTestCase(LIMB_VAR_DIR . '/cases/a/z/zoo_test.php');

    $run_dir = LIMB_VAR_DIR . '/cases/*.php';
    $ret = $this->_execScript($run_dir, $screen);
    if(!$this->assertEqual($ret, 0))
      echo $screen;

    $this->assertPattern('~1\s+of\s+1\s+done\(' . $foo->getClass() . '\)~', $screen);
    $this->assertPattern('~OK~i', $screen);
    $this->assertNoPattern('~Error~i', $screen);
  }

  function testPerformMultipleArgs()
  {
    $foo = $this->_createTestCase($foo_file = LIMB_VAR_DIR . '/cases/foo_test.php');
    $bar = $this->_createTestCase($bar_file = LIMB_VAR_DIR . '/cases/a/bar_test.php');
    $zoo = $this->_createTestCase($zoo_file = LIMB_VAR_DIR . '/cases/a/z/zoo_test.php');

    $ret = $this->_execScript("$bar_file $foo_file $zoo_file", $screen);
    if(!$this->assertEqual($ret, 0))
      echo $screen;

    $this->assertPattern('~1\s+of\s+3\s+done\(' . $bar->getClass() . '\)~', $screen);
    $this->assertPattern('~2\s+of\s+3\s+done\(' . $foo->getClass() . '\)~', $screen);
    $this->assertPattern('~3\s+of\s+3\s+done\(' . $zoo->getClass() . '\)~', $screen);
    $this->assertPattern('~Test cases run:\s*3\/3.*~si', $screen);
    $this->assertNoPattern('~Error~i', $screen);
  }

  function testPerformOnlySelectedTests()
  {
    $foo = $this->_createTestCase($foo_file = LIMB_VAR_DIR . '/cases/foo_test.php');
    $bar = $this->_createTestCase($bar_file = LIMB_VAR_DIR . '/cases/bar_test.php');
    $zoo = $this->_createTestCase($zoo_file = LIMB_VAR_DIR . '/cases/zoo_test.php');

    $ret = $this->_execScript("--tests=" . $foo->getClass(). "," . $zoo->getClass(). " $foo_file $bar_file $zoo_file", $screen);
    if(!$this->assertEqual($ret, 0))
      echo $screen;

    $this->assertPattern('~1\s+of\s+2\s+done\(' . $foo->getClass() . '\)~', $screen);
    $this->assertPattern('~2\s+of\s+2\s+done\(' . $zoo->getClass() . '\)~', $screen);
  }

  function testPerformOnlySelectedMethods()
  {
    $foo_body  = '%class_header%';
    $foo_body .= 'function testFoo(){echo "foo";}';
    $foo_body .= 'function testJunk(){echo "junk";}';
    $foo_body .= '%class_footer%';

    $bar_body  = '%class_header%';
    $bar_body .= 'function testBar(){echo "bar";}';
    $bar_body .= 'function testJunk(){echo "junk";}';
    $bar_body .= '%class_footer%';

    $foo = $this->_createTestCase($foo_file = LIMB_VAR_DIR . '/cases/foo_test.php', $foo_body);
    $bar = $this->_createTestCase($bar_file = LIMB_VAR_DIR . '/cases/bar_test.php', $bar_body);

    $ret = $this->_execScript("--methods=testFoo,testBar $foo_file $bar_file", $screen);
    if(!$this->assertEqual($ret, 0))
      echo $screen;

    $this->assertPattern('~foo1\s+of\s+2\s+done\(' . $foo->getClass() . '\)~', $screen);
    $this->assertPattern('~bar2\s+of\s+2\s+done\(' . $bar->getClass() . '\)~', $screen);
  }

  function testPerformOnlySelectedGroupsOfTests()
  {
    $foo = $this->_createTestCase($foo_file = LIMB_VAR_DIR . '/cases/foo_test.php', "/**\n* @group foo\n*/ %class%");
    $bar = $this->_createTestCase($bar_file = LIMB_VAR_DIR . '/cases/bar_test.php', "/**\n* @group bar,foo\n*/ %class%");
    $junk = $this->_createTestCase($junk_file = LIMB_VAR_DIR . '/cases/junk_test.php', "/*\n* @group junk\n*/ %class%");

    $ret = $this->_execScript("--groups=foo,bar $foo_file $bar_file $junk_file", $screen);
    if(!$this->assertEqual($ret, 0))
      echo $screen;

    $this->assertPattern('~1\s+of\s+2\s+done\(' . $foo->getClass() . '\)~', $screen);
    $this->assertPattern('~2\s+of\s+2\s+done\(' . $bar->getClass() . '\)~', $screen);
    $this->assertNoPattern('~' . $junk->getClass() . '~', $screen);
  }

  function testAutoDefineConstants()
  {
    $c1 = "FOO_" . mt_rand() . "_H" . mt_rand();
    $c2 = "FOO_" . mt_rand() . "_K" . mt_rand();

    $this->_createTestCase($f = LIMB_VAR_DIR . '/cases/foo_test.php', "%class%\n echo '$c1=' . $c1;echo '$c2=' . $c2;");
    $this->_execScript("$f $c1=hey $c2=wow", $screen);

    $this->assertPattern("~$c1=hey~", $screen);
    $this->assertPattern("~$c2=wow~", $screen);
  }

  function testExceptionIsntShownTwice()
  {
    $this->_createTestCaseThrowingException($f = LIMB_VAR_DIR . '/cases/foo_test.php');
    $this->_execScript($f, $screen);
    $this->assertTrue(strpos($screen, 'Exception 1!') === strrpos($screen, 'Exception 1!'), 'Exception is shown twice!');
  }

  function testExceptionLineWhenThrownFromTest()
  {
    $this->_createTestCaseThrowingException($f = LIMB_VAR_DIR . '/cases/foo_test.php');
    $this->_execScript($f, $screen);
    $this->assertPattern("~\[EXP\].*foo_test.php:\d+$~m", $screen);
  }

  function testCoverageSummaryReporter()
  {
    if(!extension_loaded('xdebug'))
    {
      echo "Skipping coverage test since xdebug is not loaded\n";
      return;
    }
    $this->_createTestCase($f = LIMB_VAR_DIR . '/cases/cover_test.php');
    $this->_execScript("-C" . LIMB_VAR_DIR . "/cases $f", $screen);
    $this->assertPattern("~Code\s+Coverage:\s+100%~", $screen);
  }

  function _createRunScript()
  {
    $dir = dirname(__FILE__);
    $simpletest = SIMPLE_TEST;

    $script = <<<EOD
<?php
define('SIMPLE_TEST', '$simpletest');
define('LIMB_VAR_DIR', dirname(__FILE__) . '/var');
require_once('$dir/../../common.inc.php');
require_once('$dir/../../src/lmbTestShellUI.class.php');
require_once('$dir/../../src/lmbTestShellReporter.class.php');

\$ui = new lmbTestShellUI();
\$ui->setReporter(new lmbTestShellReporter());
\$ui->run();
?>
EOD;
    file_put_contents($this->_runScriptName(), $script);
  }

  function _runScriptName()
  {
    return LIMB_VAR_DIR . '/runtests.php';
  }

  function _execScript($args, &$screen)
  {
    exec('php ' . $this->_runScriptName() . ' ' . $args, $out, $ret);
    $screen = implode("\n", $out);
    return $ret;
  }
}


