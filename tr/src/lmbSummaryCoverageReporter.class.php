<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2009 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

/**
 * class lmbSummaryCoverageReporter.
 *
 * @package tests_runner
 * @version $Id$
 */
require_once(__PHPCOVERAGE_HOME . '/CoverageRecorder.php');
require_once(__PHPCOVERAGE_HOME . '/reporter/CoverageReporter.php');
require_once(__PHPCOVERAGE_HOME . '/parser/PHPParser.php');

class lmbSummaryCoverageReporter extends CoverageReporter
{
  protected function createReportDir() {}

  public function generateReport(&$data)
  {
    global $util;
    $this->coverageData =& $data;
    $this->grandTotalFiles = count($this->coverageData);

    if(!empty($this->coverageData)) 
    {
      foreach($this->coverageData as $filename => &$lines) 
      {
        $realFile = realpath($filename);
        $fileLink = $this->outputDir . $util->unixifyPath($realFile). ".html";
        $fileCoverage = $this->markFile($realFile, $fileLink, $lines);
        if(empty($fileCoverage)) 
          return false;
        $this->recordFileCoverageInfo($fileCoverage);
        $this->updateGrandTotals($fileCoverage);

        unset($this->coverageData[$filename]);
      }
    }
  }

  protected function markFile($phpFile, $fileLink, &$coverageLines) 
  {
    global $util;
    $fileLink = $util->replaceBackslashes($fileLink);
    $parentDir = $util->replaceBackslashes(dirname($fileLink));

    $lineCnt = $coveredCnt = $uncoveredCnt = 0;
    $parser = new PHPParser();
    $parser->parse($phpFile);
    $lastLineType = "non-exec";
    $fileLines = array();
    while(($line = $parser->getLine()) !== false) 
    {
      $line = substr($line, 0, strlen($line)-1);
      $lineCnt++;
      $coverageLineNumbers = array_keys($coverageLines);
      if(in_array($lineCnt, $coverageLineNumbers)) 
      {
        $lineType = $parser->getLineType();
        if($lineType == LINE_TYPE_EXEC) 
        {
          $coveredCnt ++;
          $type = "covered";
        }
        else if($lineType == LINE_TYPE_CONT) 
        {
          // XDebug might return this as covered - when it is
          // actually merely a continuation of previous line
          if($lastLineType == "covered") 
          {
            unset($coverageLines[$lineCnt]);
            $type = $lastLineType;
          }
          else 
          {
            if($lineCnt-1 >= 0 && isset($fileLines[$lineCnt-1]["type"])) 
            {
              if($fileLines[$lineCnt-1]["type"] == "uncovered") 
              {
                $uncoveredCnt --;
              }
              $fileLines[$lineCnt-1]["type"] = $lastLineType = "covered";
            }
            $coveredCnt ++;
            $type = "covered";
          }
        }
        else 
        {
          $type = "non-exec";
          $coverageLines[$lineCnt] = 0;
        }
      }
      else if($parser->getLineType() == LINE_TYPE_EXEC) 
      {
        $uncoveredCnt ++;
        $type = "uncovered";
      }
      else if($parser->getLineType() == LINE_TYPE_CONT) 
      {
        $type = $lastLineType;
      }
      else 
      {
        $type = "non-exec";
      }
      // Save line type 
      $lastLineType = $type;

      if(!isset($coverageLines[$lineCnt])) 
      {
        $coverageLines[$lineCnt] = 0;
      }
      $fileLines[$lineCnt] = array("type" => $type, "lineCnt" => $lineCnt, "line" => $line, "coverageLines" => $coverageLines[$lineCnt]);
    }

    return array(
        'filename' => $phpFile,
        'covered' => $coveredCnt,
        'uncovered' => $uncoveredCnt,
        'total' => $lineCnt
        );
  }
}
