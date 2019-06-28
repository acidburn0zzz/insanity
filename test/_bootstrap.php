<?php

require_once(__DIR__ . '/../vendor/autoload.php');

abstract class TestCase extends \PHPUnit\Framework\TestCase
{ }

class CallAsserter
{
    private $test;
    public $expectedCalls;
    public function __construct($test, $expectedCalls)
    {
        $this->test = $test;
        $this->expectedCalls = $expectedCalls;
    }

    public function __call($rule, $params)
    {
        $call = array_shift($this->expectedCalls);
        $return = array_pop($call);
        $this->test->assertEquals($call, [$rule, $params]);
        return $return;
    }
}
