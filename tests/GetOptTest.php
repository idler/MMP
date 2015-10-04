<?php

class GetOptTest extends PHPUnit_Framework_TestCase
{
    public function testJustWorks()
    {
        $arg = array();
        $opt = array();
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertEmpty($result);
    }
    
    public function testExtractReq()
    {
        $arg = array('--foo=bar');
        $opt = array('foo'=>array('req_val'));
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertArrayHasKey('foo', $result);
    }
    
    public function testNoReqValue()
    {
        $arg = array('--foo');
        $opt = array('foo'=>array('req_val'));
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertFalse($result);
        $this->assertCount(1, GetOpt::errors());
    }
    
    public function testExtractOpt()
    {
        $arg = array('--foo=bar', '--bob=alice');
        $opt = array(
            'foo'=>array('req_val'),
            'bob'=>array('opt_val')
        );
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertArrayHasKey('foo', $result);
        $this->assertArrayHasKey('bob', $result);
    }
    
    public function testNoOptValue()
    {
        $arg = array('--foo=bar', '--bob');
        $opt = array(
            'foo'=>array('req_val'),
            'bob'=>array('opt_val')
        );
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertCount(2, $result);
        $this->assertNull($result['bob']);
    }
    
    public function testUnknownOpt()
    {
        $arg = array('--foo=bar', '--bob=alice', '--alice=bob');
        $opt = array(
                'foo'=>array('req_val'),
                'bob'=>array('opt_val')
        );
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertFalse($result);
        $this->assertCount(1, GetOpt::errors());
    }
    
    public function testExtractReqShort()
    {
        $arg = array('-f=bar');
        $opt = array('foo'=>array('short'=>'f','req_val'));
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertArrayHasKey('foo', $result);
    }
    
    public function testNoReqShortValue()
    {
        $arg = array('-f');
        $opt = array('foo'=>array('short'=>'f','req_val'));
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertFalse($result);
        $this->assertCount(1, GetOpt::errors());
    }
    
    public function testInvalidShortValue()
    {
        $arg = array('-z ---1');
        $opt = array('foo'=>array('short'=>'f','req_val'));
        $result = GetOpt::extractLeft($arg, $opt);
        $this->assertFalse($result);
        $this->assertCount(1, GetOpt::errors());
    }
}