<?php
use Lusito\InSanity\Field;
use Lusito\InSanity\RuleHandler;
use Lusito\InSanity\ErrorHandler;

final class FieldTest extends TestCase
{
    const DEFAULT = 'test-default-value';
    const BOOL_TRUE = ['true', 'on', 'yes', '1', 'True', 'ON', 'yeS'];
    const BOOL_FALSE = ['false', 'off', 'no', '0', 'faLse', 'Off', 'No'];

    public function testSetFailed(): void
    {
        $field = $this->createField('test-value');
        $field->setFailed('max_length', 42);
        $this->assertSame('The field "My Field" must not be longer than 42 characters.', $field->getError());
    }

    public function testRequired() {
        $validValues = ['a', ' a', 'a ', ['a', ' a', 'a ']];
        $invalidValues = ['', ' ', "\n", "\r", ['', ' ', "\n", "\r"], [' ', 'a']];

        foreach($validValues as $value) {
            $field = $this->createField($value);
            $this->assertSame($field, $field->required);
            $this->assertSame(null, $field->getError());

            $field = $this->createField($value);
            $this->assertSame($field, $field->required());
            $this->assertSame(null, $field->getError());
        }

        foreach($invalidValues as $value) {
            $field = $this->createField($value);
            $this->assertSame($field, $field->required);
            $this->assertSame('The field "My Field" is required.', $field->getError());

            $field = $this->createField($value);
            $this->assertSame($field, $field->required->min_length(100));
            $this->assertSame('The field "My Field" is required.', $field->getError());

            $field = $this->createField($value);
            $this->assertSame($field, $field->required()->min_length(100));
            $this->assertSame('The field "My Field" is required.', $field->getError());
        }
    }

    public function testRule() {
        $validValues = ['a', ['a', 'b']];
        $invalidValues = ['0', ['a', '0']];
        foreach($validValues as $value) {
            $field = $this->createField($value);
            $this->assertSame($field, $field->is_alpha);
            $this->assertSame(null, $field->getError());
        }
        foreach($invalidValues as $value) {
            $field = $this->createField($value);
            $this->assertSame($field, $field->is_alpha);
            $this->assertSame('The field "My Field" must only contain letters from a-z.', $field->getError());
        }
    }

    public function testRuleParam() {
        $validValues = ['a', 'ab', ['a', 'b', 'ab']];
        $invalidValues = ['abc', ['a', 'abc']];
        foreach($validValues as $value) {
            $field = $this->createField($value);
            $this->assertSame($field, $field->max_length(2));
            $this->assertSame(null, $field->getError());
            $this->assertEquals($value, $field->getValue(), "getValue() should not have changed value after validation");
        }
        foreach($invalidValues as $value) {
            $field = $this->createField($value);
            $this->assertSame($field, $field->max_length(2));
            $this->assertSame('The field "My Field" must not be longer than 2 characters.', $field->getError());
            $this->assertEquals($value, $field->getValue(), "getValue() should not have changed value after validation");
        }
    }

    public function testTrim() {
        $values = ['a', 'a ', ' a', ' a ', ['a', 'a ', ' a', ' a ']];
        $expected = ['a', 'a', 'a', 'a', ['a', 'a', 'a', 'a']];
        foreach($values as $index => $value) {
            $field = $this->createField($value);
            $this->assertSame($field, $field->trim);
            $this->assertSame(null, $field->getError());
            $this->assertEquals($expected[$index], $field->getValue(), "getValue() should have trimmed values after trim");
        }
    }

    public function testGetValue(): void
    {
        $field = new Field(new ErrorHandler(), new RuleHandler(), 'my_field', 'My Field', 'test-value');
        $result = $field->getValue();
        $this->assertSame('test-value', $result);
    }

    private function createField($value) {
        return new Field(new ErrorHandler(), new RuleHandler(), 'my_field', 'My Field', $value);
    }

    /**
     * @dataProvider ruleDataProvider
     */
    public function testVal($rule, $sets): void
    {
        foreach ($sets as $set) {
            [$value, $expected] = $set;
            $field = $this->createField($value);
            $result = $field->$rule(self::DEFAULT);
            if (!is_array($value)) {
                $this->assertSame($expected, $result, "$rule should convert '$value' to '$expected'");
                $this->assertSame($expected, $field->getValue(), "getValue() should return converted value after $rule, '$value' => '$expected'");
            } else {
                $valueAfter = $field->getValue();
                foreach ($value as $index => $value) {
                    $exp = $expected[$index];
                    $res = $result[$index];
                    $res2 = $valueAfter[$index];
                    $this->assertSame($exp, $res, "$rule should convert '$value' to '$exp'");
                    $this->assertSame($exp, $res2, "getValue() should return converted value after $rule, '$value' => '$exp'");
                }
            }
        }
    }

    public function ruleDataProvider()
    {
        return [
            ['floatval', [
                ['1', 1.0],
                ['12.3', 12.3],
                ['+12.4453', 12.4453],
                ['-12.4453', -12.4453],
                [['+13.4453', '-12.4453'], [+13.4453, -12.4453]],
                [null, self::DEFAULT]
            ]],
            ['intval', [
                ['1', 1],
                ['12.3', 12],
                ['+12.4453', 12],
                ['-12.4453', -12],
                [['+13.4453', '-12.4453'], [+13, -12]],
                [null, self::DEFAULT]
            ]],
            ['boolval', [
                ['true', true],
                ['false', false],
                ['1', true],
                ['0', false],
                ['on', true],
                ['off', false],
                ['yes', true],
                ['no', false],
                [0, false],
                [1, true],
                [self::BOOL_TRUE, [true, true, true, true, true, true, true]],
                [self::BOOL_FALSE, [false, false, false, false, false, false, false]],
                [null, self::DEFAULT]
            ]],
            ['val', [
                ['foobar', 'foobar'],
                [['foo', 'bar'], ['foo', 'bar']],
                [null, self::DEFAULT]
            ]]
        ];
    }
}
