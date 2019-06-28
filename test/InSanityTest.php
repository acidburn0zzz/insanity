<?php
use Lusito\InSanity\InSanity;

final class InSanityTest extends TestCase
{
    public function testDefaultInputNull()
    {
        $_POST = ['test' => 'test-value1'];
        $in = new InSanity();
        $field = $in->test('Test');
        $this->assertSame($field, $in->test);
        $this->assertSame('test-value1', $field->getValue());
    }

    public function testDefaultInput()
    {
        $data = ['test' => 'test-value2'];
        $in = new InSanity($data);
        $field = $in->test('Test');
        $this->assertSame($field, $in->test);
        $this->assertSame('test-value2', $field->getValue());
    }

    public function testDefaultInputParam()
    {
        $data = ['test' => 'test-value3'];
        $in = new InSanity([]);
        $field = $in->test('Test', $data);
        $this->assertSame($field, $in->test);
        $this->assertSame('test-value3', $field->getValue());
    }

    public function testNullField()
    {
        $in = new InSanity();
        $this->assertNull($in->test);
    }

    public function testErrors()
    {
        $in = new InSanity([]);
        $in->test('Test field')->required;
        $this->assertEquals(['test' => 'The field "Test field" is required.'], $in->getErrors());
    }

    public function testCustomClassValid()
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

    public function testCustomClassInvalid()
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

    public function testToJSON()
    {
        $in = new Insanity(['a' => ' x ', 'b' => ' y ', 'c' => ' z ']);
        $in->a('A')->trim;
        $in->b('B')->trim;
        $this->assertEquals(['a' => 'x', 'b' => 'y'], $in->toJSON());
    }
}
