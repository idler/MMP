<?php

class HelperTest extends PHPUnit_Framework_TestCase
{
  public function testRequiredParamsShouldBeEnough ()
  {
    $config = array();
    $config['host']         = 'host';
    $config['user']         = 'user';
    $config['password']     = 'password';
    $config['db']           = 'db';
    $config['savedir']      = 'savedir';
    $config['versiontable'] = 'versiontable';
    $config['verbose']      = 'verbose';
    Helper::setConfig($config);
    $this->assertTrue( Helper::checkConfigEnough());
  }
}