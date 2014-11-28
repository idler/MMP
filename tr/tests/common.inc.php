<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

class GeneratedTestClass
{
  protected $class_name;
  protected $parent_class_name = 'UnitTestCase';
  protected $body;

  function __construct($body = '%class%')
  {
    $this->class_name = 'GenClass_' . mt_rand(1, 10000) . uniqid();
    $this->body = $body;
  }

  function getClass()
  {
    return $this->class_name;
  }

  function setParentClass($parent_class_name)
  {
  	$this->parent_class_name = $parent_class_name;
  }

  function getParentClass()
  {
    return $this->parent_class_name;
  }

  function getFileName()
  {
    return $this->class_name . ".class.php";
  }

  function getOutput()
  {
    return $this->class_name . "\n";
  }

  function generate($test_body = false)
  {
    $code = '';
    $code .= "<?php\n";
    $code .= $this->generateClass($test_body);
    $code .= "\n?>";
    return $code;
  }

  function generateFailing()
  {
    $code = '';
    $code .= "<?php\n";
    $code .= $this->generateClassFailing();
    $code .= "\n?>";
    return $code;
  }

  function generateClass($test_body = false)
  {
  	if (!$test_body)
  	  $test_body = "echo \"" . $this->getOutput() . "\";";
    $parts = array();
    $parts['%class_header%'] = "\nclass {$this->class_name} extends {$this->parent_class_name} {\n";
    $parts['%class_footer%'] = "\n}\n";
    $parts['%class%'] = $parts['%class_header%'] .
                        "function testMe()\n{\n".$test_body."\n}" .
                        $parts['%class_footer%'];

    return str_replace(array_keys($parts), array_values($parts), $this->body);
  }

  function generateClassFailing()
  {
  	return $this->generateClass("\$this->assertTrue(false);echo \"" . $this->getOutput() . "\";");
  }

  function generateClassThrowingException()
  {
    return $this->generateClass("throw new Exception('thrown from {$this->getOutput()}');");
  }
}

abstract class lmbTestRunnerBase extends UnitTestCase
{
  function _rmdir($path)
  {
    if(!is_dir($path))
      return;

    $dir = opendir($path);
    while($entry = readdir($dir))
    {
      if(is_file("$path/$entry"))
        unlink("$path/$entry");
      elseif(is_dir("$path/$entry") && $entry != '.' && $entry != '..')
        $this->_rmdir("$path/$entry");
    }
    closedir($dir);
    $res = rmdir($path);
    clearstatcache();
    return $res;
  }

  function _createTestCase($file, $body = '%class%')
  {
    $this->_createDirForFile($file);

    $generated = new GeneratedTestClass($body);
    file_put_contents($file, "<?php\n" . $generated->generateClass() . "\n?>");
    return $generated;
  }

  function _createTestCaseFailing($file, $body = '%class%')
  {
    $this->_createDirForFile($file);

    $generated = new GeneratedTestClass($body);
    file_put_contents($file, "<?php\n" . $generated->generateClassFailing() . "\n?>");
    return $generated;
  }

  function _createTestCaseThrowingException($file, $body = '%class%')
  {
    $this->_createDirForFile($file);

    $generated = new GeneratedTestClass($body);
    file_put_contents($file, "<?php\n" . $generated->generateClassThrowingException() . "\n?>");
    return $generated;
  }

  protected function _createDirForFile($file)
  {
    $dir = dirname($file);
    if (!is_dir($dir)) {
      mkdir($dir, 0777, true);
    }
  }

  function _runNodeAndAssertOutput($node, $expected)
  {
    ob_start();
    $group = $node->createTestCase();
    $group->run(new SimpleReporter());
    $str = ob_get_contents();
    ob_end_clean();
    $this->assertEqual($str, $expected);
  }
}

class lmbTestReporter extends SimpleReporter
{
  protected $output = '';

  function __construct()
  {
  	$this->SimpleReporter();
  }

  function paintFail($message)
  {
    parent::paintFail($message);
    $this->_addToOutput('FAIL', $message);
  }

  function paintError($message)
  {
    parent::paintError($message);
    $this->_addToOutput('FORMATED', $message);
  }

  function paintException($exception)
  {
    parent::paintException($exception);
    $this->_addToOutput('FORMATED', $message);
  }

  function paintSkip($message)
  {
    parent::paintSkip($message);
    $this->_addToOutput('FORMATED', $message);
  }

  protected function _addToOutput($title, $message)
  {
  	$this->output .= $title . ': ' . $message . PHP_EOL;
  }

  function getOutput()
  {
  	return $this->output;
  }
}