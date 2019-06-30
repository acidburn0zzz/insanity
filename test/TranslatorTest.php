<?php
use Lusito\InSanity\Translator;

final class TranslatorTest extends TestCase
{
    public function testTranslateRule()
    {
        $translator = new Translator();

        $expectations = [
            'is_alpha' => 'The field "{FIELD}" must only contain letters from a-z.',
            'is_alnum' => 'The field "{FIELD}" must only contain letters from a-z and digits.',
            'is_alnum_dash' => 'The field "{FIELD}" must only contain letters from a-z, digits, underscore or dash.',
            'is_numeric' => 'The field "{FIELD}" may only contain numeric values.',
            'is_integer' => 'The field "{FIELD}" may only contain integer values.',
            'is_natural' => 'The field "{FIELD}" may only contain natural values (>= 0).',
            'is_natural_no_zero' => 'The field "{FIELD}" may only contain natural values greater than 0.',
            'is_bool' => 'The field "{FIELD}" may only be one of (true, false, on, off, yes, no, 1, 0).',
            'valid_email' => 'The field "{FIELD}" must be a valid e-mail address.',
            'required' => 'The field "{FIELD}" is required.',
            'min_length' => 'The field "{FIELD}" must not be shorter than {PARAM} characters.',
            'max_length' => 'The field "{FIELD}" must not be longer than {PARAM} characters.',
            'exact_length' => 'The field "{FIELD}" must be exactly {PARAM} characters long.',
            'unknown_rule' => 'unknown_rule (No translation found)'
        ];

        foreach ($expectations as $rule => $translation)
            $this->assertEquals($translation, $translator->translateRule($rule));
    }

    public function testTranslateRuleWithCustomTranslations()
    {
        $translator = new Translator([
            'is_alpha' => 'The input "{FIELD}" must only contain letters from a-z.',
            'unknown_rule' => 'Must be an unknown rule'
        ]);

        $expectations = [
            'is_alpha' => 'The input "{FIELD}" must only contain letters from a-z.',
            'is_alnum' => 'The field "{FIELD}" must only contain letters from a-z and digits.',
            'is_alnum_dash' => 'The field "{FIELD}" must only contain letters from a-z, digits, underscore or dash.',
            'is_numeric' => 'The field "{FIELD}" may only contain numeric values.',
            'is_integer' => 'The field "{FIELD}" may only contain integer values.',
            'is_natural' => 'The field "{FIELD}" may only contain natural values (>= 0).',
            'is_natural_no_zero' => 'The field "{FIELD}" may only contain natural values greater than 0.',
            'is_bool' => 'The field "{FIELD}" may only be one of (true, false, on, off, yes, no, 1, 0).',
            'valid_email' => 'The field "{FIELD}" must be a valid e-mail address.',
            'required' => 'The field "{FIELD}" is required.',
            'min_length' => 'The field "{FIELD}" must not be shorter than {PARAM} characters.',
            'max_length' => 'The field "{FIELD}" must not be longer than {PARAM} characters.',
            'exact_length' => 'The field "{FIELD}" must be exactly {PARAM} characters long.',
            'unknown_rule' => 'Must be an unknown rule',
            'unknown_rule_2' => 'unknown_rule_2 (No translation found)'
        ];

        foreach ($expectations as $rule => $translation)
            $this->assertEquals($translation, $translator->translateRule($rule));
    }

    public function testTranslateField()
    {
        $translator = new Translator();
        $this->assertEquals(
            'Some label',
            $translator->translateField('Some name', 'Some label')
        );
    }
}
