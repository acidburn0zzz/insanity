<?php
use Lusito\InSanity\RuleHandler;

final class RuleHandlerTest extends TestCase
{
    const EMPTY = [''];
    const WHITE = [' ', "\t", "\n", "\r", "\r\n", "  \t  \n   \r\n  "];
    const ALPHA = ['a', 'A', 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'];
    const NON_ALPHA_CHARS = ['ä', 'Ä', 'ö', 'ß'];
    const PUNCTUATION = [',', '.', '!', '?', ';', ':'];
    const DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const ZERO = ['0', '00'];
    const NATURAL_NO_ZERO = ['11', '12', '1243', '390889734985'];
    const INTEGER = ['+1', '+12', '-1243', '+390889734985'];
    const FLOAT = ['+1.235', '+12.2342', '-1243.768', '+390889734985.46464'];
    const BAD_FLOAT = ['+1.2.35', '+12.2.342', '-124.3.768', '+3908897.34985.46464'];
    const ALNUM = ['a1', '4b', 'de454sdfsdf4'];
    const DASH = ['-', '_'];
    const ALNUM_DASH = ['-a', 'a_', '1a-', '-b3-'];
    const BOOL = ['true', 'false', 'on', 'off', 'yes', 'no', '1', '0', 'True', 'faLse', 'ON', 'Off', 'yeS', 'No'];

    /**
     * @dataProvider ruleDataProvider
     */
    public function testRule($rule, $trueValues, $falseValues, $allowWhite): void
    {
        $handler = new RuleHandler();
        foreach (array_unique($trueValues) as $value) {
            $this->assertTrue($handler->$rule($value), "'$value' should match $rule");
            $this->assertEquals($allowWhite, $handler->$rule(" $value"), "' $value' should match $rule");
            $this->assertEquals($allowWhite, $handler->$rule("$value "), "'$value ' should match $rule");
            $this->assertEquals($allowWhite, $handler->$rule(" $value "), "' $value ' should match $rule");
        }
        foreach (array_unique($falseValues) as $value) {
            $this->assertFalse($handler->$rule($value), "'$value' should not match $rule");
            $this->assertFalse($handler->$rule(" $value"), "' $value' should not match $rule");
            $this->assertFalse($handler->$rule("$value "), "'$value ' should not match $rule");
            $this->assertFalse($handler->$rule(" $value "), "' $value ' should not match $rule");
        }
    }

    public function ruleDataProvider()
    {
        $is_alnum_dash_not = array_merge(self::NON_ALPHA_CHARS, self::PUNCTUATION, self::WHITE, self::EMPTY, ['+', '*', '/', '%']);
        $is_alnum_not = array_merge($is_alnum_dash_not, self::DASH, self::ALNUM_DASH);
        $is_alpha_not = array_merge($is_alnum_not, self::DIGITS, self::NATURAL_NO_ZERO, self::ALNUM);
        $is_integer_not = array_merge($is_alnum_dash_not, ['1+', '1+3', '*1', '/2'], self::BAD_FLOAT);
        $is_numeric_not = array_diff($is_integer_not, ['1+', '1+3', '*1', '/2'], self::PUNCTUATION);
        $is_bool_not = array_diff(array_merge($is_integer_not, $is_alpha_not), self::BOOL);
        $is_alnum = array_merge(self::DIGITS, self::NATURAL_NO_ZERO, self::ALNUM, self::ALPHA);
        $is_alnum_dash = array_merge($is_alnum, self::DASH, self::ALNUM_DASH);
        $required = array_diff(array_merge($is_integer_not, $is_alpha_not), self::WHITE, self::EMPTY);
        return [
            ['is_alpha', self::ALPHA, $is_alpha_not, false],
            ['is_alnum', $is_alnum , $is_alnum_not, false],
            ['is_alnum_dash', $is_alnum_dash , $is_alnum_dash_not, false],
            ['is_natural', array_merge(self::NATURAL_NO_ZERO, self::ZERO), array_merge($is_alnum_not, self::INTEGER), false],
            ['is_natural_no_zero', array_merge(self::NATURAL_NO_ZERO), array_merge($is_alnum_not, self::INTEGER, self::ZERO), false],
            ['is_integer', array_merge(self::NATURAL_NO_ZERO, self::ZERO, self::INTEGER), $is_integer_not, false],
            ['is_numeric', array_merge(self::NATURAL_NO_ZERO, self::ZERO, self::INTEGER, self::FLOAT), $is_numeric_not, false],
            ['is_bool', self::BOOL, $is_bool_not, false],
            ['required', $required, array_merge(self::EMPTY, self::WHITE), true],
            ['valid_email', ['hello@world.com', 'hel_l.o-34@world.com', 'hello+34@world.com', 'hello@world.foobar'], ['@world.com', '@', 'hello34@wo+rld.com', 'hello@world.b', 'hello@world.4k', 'hello@world.foobarb'], false]
        ];
    }

    
    /**
     * @dataProvider ruleLengthDataProvider
     */
    public function testLengthRule($rule, $length, $trueValues, $falseValues): void
    {
        $handler = new RuleHandler();
        foreach (array_unique($trueValues) as $value)
            $this->assertTrue($handler->$rule($value, $length), "'$value' should match $rule");
        foreach (array_unique($falseValues) as $value)
            $this->assertFalse($handler->$rule($value, $length), "'$value' should not match $rule");
    }

    public function ruleLengthDataProvider()
    {
        return [
            ['min_length', 2, ['ab', 'abc', '  '], ['a', '']],
            ['max_length', 2, ['a', 'ab', '  ', '', ' '], ['abc', '   ', '12345']],
            ['exact_length', 2, ['ab', '  ', '01'], ['a', '', '111']]
        ];
    }

    // fixme: custom regex rules
    // fixme: no custom rule & no php method => false
}
