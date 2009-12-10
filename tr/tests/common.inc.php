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

  function getFileName()
  {
    return $this->class_name . ".class.php";
  }

  function getOutput()
  {
    return $this->class_name . "\n";
  }

  function generate()
  {
    $code = '';
    $code .= "<?php\n";
    $code .= $this->generateClass();
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

  function generateClass()
  {
    $parts = array();
    $parts['%class_header%'] = "\nclass {$this->class_name} extends UnitTestCase {\n"; 
    $parts['%class_footer%'] = "\n}\n";
    $parts['%class%'] = $parts['%class_header%'] . 
                        "function testMe() {echo \"" . $this->getOutput() . "\";}" . 
                        $parts['%class_footer%'];

    return str_replace(array_keys($parts), array_values($parts), $this->body);
  }

  function generateClassFailing()
  {
    $parts = array();
    $parts['%class_header%'] = "\nclass {$this->class_name} extends UnitTestCase {\n"; 
    $parts['%class_footer%'] = "\n}\n";
    $parts['%class%'] = $parts['%class_header%'] . 
                        "function testMe() {\$this->assertTrue(false);echo \"" . $this->getOutput() . "\";}" . 
                        $parts['%class_footer%'];

    return str_replace(array_keys($parts), array_values($parts), $this->body);
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
    $dir = dirname($file);
    if(!is_dir($dir))
      mkdir($dir, 0777, true);

    $generated = new GeneratedTestClass($body);
    file_put_contents($file, "<?php\n" . $generated->generateClass() . "\n?>");
    return $generated;
  }

  function _createTestCaseFailing($file, $body = '%class%')
  {
    $dir = dirname($file);
    if(!is_dir($dir))
      mkdir($dir, 0777, true);

    $generated = new GeneratedTestClass($body);
    file_put_contents($file, "<?php\n" . $generated->generateClassFailing() . "\n?>");
    return $generated;
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

