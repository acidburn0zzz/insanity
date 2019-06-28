<?php namespace Lusito\InSanity;

class Translator
{
    protected $translations;

    public function __construct(array $translations = [])
    {
        $this->translations = [
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
        ];

        foreach ($translations as $name => $rule)
            $this->translations[$name] = $rule;
    }

    public function translateField($name, $label)
    {
        return $label;
    }

    public function translateRule($rule)
    {
        return $this->translations[$rule] ?? ($rule . ' (No translation found)');
    }
}
