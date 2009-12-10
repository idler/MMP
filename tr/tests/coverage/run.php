<?php
@define('__PHPCOVERAGE_HOME', dirname(__FILE__) . '/../../lib/spikephpcoverage/src/');

require_once(__PHPCOVERAGE_HOME . '/CoverageRecorder.php');
require_once(dirname(__FILE__) . '/../../src/lmbSummaryCoverageReporter.class.php');

$coverage_include = array('src');
$coverage_exclude = array();
$coverage_reporter = new lmbSummaryCoverageReporter();

$coverage = new CoverageRecorder($coverage_include, $coverage_exclude, $coverage_reporter);
$coverage->startInstrumentation();

include('src/a.php');
include('src/b.php');

$coverage->stopInstrumentation();
$coverage->generateReport();
$coverage_reporter->printTextSummary();
