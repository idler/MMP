<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
require_once(dirname(__FILE__) . '/lmbTestTreePath.class.php');
require_once(dirname(__FILE__) . '/lmbTestRunner.class.php');
require_once(dirname(__FILE__) . '/lmbTestHTMLReporter.class.php');

/**
 * class lmbTestWebUI.
 *
 * @package tests_runner
 * @version $Id: lmbTestWebUI.class.php 7486 2009-01-26 19:13:20Z pachanga $
 */
class lmbTestWebUI
{
  protected $tree;
  protected $encoding = 'UTF-8';

  function __construct($root_node)
  {
    $this->root_node = $root_node;
  }

  function setEncoding($encoding)
  {
    $this->encoding = $encoding;
  }

  protected function _getReporter()
  {
    return new lmbTestHTMLReporter($this->encoding);
  }

  protected function _getBaseUrl()
  {
    return $_SERVER['PHP_SELF'];
  }

  function run()
  {
    if(isset($_GET['perform']))
    {
      if(is_array($_GET['perform']))
      {
        foreach(array_keys($_GET['perform']) as $path)
          $this->perform(lmbTestTreePath :: normalize($path));
      }
      else
        $this->perform(lmbTestTreePath :: normalize($_GET['perform']));
    }
    elseif(isset($_GET['browse']))
      $this->browse(lmbTestTreePath :: normalize($_GET['browse']));
    else
      $this->browse();
  }

  function perform($path)
  {
    $runner = new lmbTestRunner();
    $runner->setReporter($this->_getReporter());
    $runner->run($this->root_node, $path);

    if(isset($_GET['back']))
      $postfix = '';
    else
      $postfix = '/..';

    echo '<small>' . $runner->getRunTime() . '</small>';
    echo '<p>' . $this->_createBrowseLink($path . $postfix, 'Back') . '</p>';
  }

  function browse($path='/')
  {
    $node = $this->root_node->findChildByPath($path);

    echo '<html><body><style>@import url("style.css");</style>';

    if($this->root_node !== $node)
      echo '<p>' . $this->_createBrowseLink($path . '/..', 'Back');

    $sub_nodes = $node->getChildren();

    $buffer = '';

    $buffer .= "<p>Available test groups in '" . $node->getTestLabel() . "':\n";
    $buffer .= '<p>' . $this->_createPerformLink($path, 'Run all tests from this group', true);

    if(sizeof($sub_nodes))
    {
      $buffer .= "<p><form action='" . $this->_getBaseURL(). "' name='tests' method='GET'>\n";
      $buffer .= $this->_createAntiEmptySelectionHiddenInput();
      $buffer .= $this->_getToggleJS();
      $buffer .= $this->_createRunSelectedButton();
      $buffer .= "<p><a href='#' onclick='toggle_checkbox()'>toggle all</a></p>";
      $buffer .= "<table>";

      foreach($sub_nodes as $index => $sub_node)
      {
        if($index % 2)
          $class_name = 'odd';
        else
          $class_name = 'even';
        $buffer .= "<tr class='{$class_name}'>";
        $buffer .= "<td>" . $this->_createPerformCheckBox("{$path}/{$index}") . "</td>";

        if($sub_node->isTerminal())
        {
          $buffer .= "<td>" . $this->_createPerformLink("{$path}/{$index}", 'P') . "</td>";
        }
        else
        {
          $buffer .= "<td>" . $this->_createPerformLink("{$path}/{$index}", 'P') .
                     $this->_createBrowseLink("{$path}/{$index}", 'B') ."</td>";
        }

        $buffer .= "<td>" . $sub_node->getTestLabel() . "</td></tr>\n";
      }
      $buffer .= "</table>\n";
      $buffer .= $this->_createRunSelectedButton();
      $buffer .= "</form>\n";
    }
    else
      $buffer .= "<p>No groups available.</p> \n";

    echo $buffer;

    echo '</body></html>';
  }

  protected function _createPerformHref($path)
  {
    return $this->_getBaseURL() . "?perform=$path";
  }

  protected function _createPerformLink($path, $title, $need_back = false)
  {
    $back = $need_back ? '&back=1' : '';
    return "<a href='" . $this->_createPerformHref($path) . "$back'>$title</a>&nbsp;";
  }

  protected function _createBrowseHref($path)
  {
    return $this->_getBaseURL() . "?browse=$path";
  }

  protected function _createBrowseLink($path, $title)
  {
    return "<a href='" . $this->_createBrowseHref($path) . "'>$title</a>&nbsp;";
  }

  protected function _createPerformCheckBox($path)
  {
    return "<input type='checkbox' name='perform[$path]'>";
  }

  protected function _createRunSelectedButton()
  {
    return "<input type='submit' value='Run selected tests'>\n";
  }

  protected function _createAntiEmptySelectionHiddenInput()
  {
    if(isset($_GET['perform']))
      return '';
    return "<input type='hidden' name='browse' value='" .
          (isset($_GET['browse']) ? $_GET['browse'] : '/') . "'>";
  }

  protected function _getToggleJS()
  {
    return <<<EOD
<script>
  var toggle_mark = 0;
  function toggle_checkbox() {
    toggle_mark = toggle_mark ? 0 : 1;
    inputs = document.getElementsByTagName('input');
    for(i=0;i<inputs.length;i++) {
      var item = inputs[i];
      item.checked = toggle_mark;
    }
  }
</script>
EOD;
  }
}


