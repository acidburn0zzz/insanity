<?php
use Lusito\InSanity\InSanity;

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

final class InSanityTest extends TestCase
{
    public function testDefaultInputNull(): void
    {
        $_POST = ['test' => 'test-value1'];
        $in = new InSanity();
        $field = $in->test('Test');
        $this->assertSame($field, $in->test);
        $this->assertSame('test-value1', $field->getValue());
    }

    public function testDefaultInput(): void
    {
        $data = ['test' => 'test-value2'];
        $in = new InSanity($data);
        $field = $in->test('Test');
        $this->assertSame($field, $in->test);
        $this->assertSame('test-value2', $field->getValue());
    }

    public function testDefaultInputParam(): void
    {
        $data = ['test' => 'test-value3'];
        $in = new InSanity([]);
        $field = $in->test('Test', $data);
        $this->assertSame($field, $in->test);
        $this->assertSame('test-value3', $field->getValue());
    }

    public function testNullField(): void
    {
        $in = new InSanity();
        $this->assertNull($in->test);
    }

    public function testErrors(): void
    {
        $in = new InSanity([]);
        $in->test('Test field')->required;
        $this->assertEquals(['test' => 'The field "Test field" is required.'], $in->getErrors());
    }

    public function testCustomClassValid(): void
    {
        $ruleHandler = new CallAsserter($this, [
            ['test', ['test-value', 'test-param'], true]
        ]);
        $errorHandler = new CallAsserter($this, []);
        $in = new InSanity(['test' => 'test-value'], $ruleHandler, $errorHandler);
        $in->test('Test field')->test('test-param');
        $this->assertEmpty($ruleHandler->expectedCalls);
        $this->assertEmpty($errorHandler->expectedCalls);
    }

    public function testCustomClassInvalid(): void
    {
        $ruleHandler = new CallAsserter($this, [
            ['test_rule', ['test-value', 'test-param'], false]
        ]);
        $errorHandler = new CallAsserter($this, [
            ['setFailed', ['test', 'Test field', 'test_rule', 'test-param'], 'error-message'],
            ['getErrors', [], 'test-errors']
        ]);
        $in = new InSanity(['test' => 'test-value'], $ruleHandler, $errorHandler);
        $in->test('Test field')->test_rule('test-param');
        $this->assertSame('test-errors', $in->getErrors());
        $this->assertEmpty($ruleHandler->expectedCalls);
        $this->assertEmpty($errorHandler->expectedCalls);
        $this->assertSame('error-message', $in->test->getError());
    }
}
