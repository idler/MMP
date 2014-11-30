<?php

class lmbPHPUnitTestCase extends UnitTestCase
{
  function __construct($label = false)
  {
    parent::UnitTestCase($label);
  }

  function assertEquals($first, $second, $message = '%s')
  {
    return $this->assertEqual($first, $second, $message);
  }

  function assertRegexp($pattern, $subject, $message = '%s')
  {
    return $this->assertPattern($pattern, $subject, $message);
  }

  function error($message = '%s')
  {
  	return $this->fail($message);
  }
}