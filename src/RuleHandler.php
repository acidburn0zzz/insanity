<?php namespace Lusito\InSanity;

class RuleHandler
{
    private $regexRules;

    public function __construct(array $regexRules = [])
    {
        $this->regexRules = [
            'is_alpha' => '/^([a-z])+$/i',
            'is_alnum' => '/^([a-z0-9])+$/i',
            'is_alnum_dash' => '/^([a-z0-9_\-])+$/i',
            'is_numeric' => '/^[\-+]?[0-9]*\.?[0-9]+$/',
            'is_integer' => '/^[\-+]?[0-9]+$/',
            'is_natural' => '/^[0-9]+$/',
            'is_bool' => '/^(true|false|on|off|yes|no|1|0)$/i',
            'valid_email' => '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix'
        ];

        foreach ($regexRules as $name => $rule)
            $this->regexRules[$name] = $rule;
    }

    public function __call($name, $args)
    {
        $value = $args[0];
        if (isset($this->regexRules[$name]))
            return preg_match($this->regexRules[$name], $value) === 1;

        // Check native PHP functions
        if (function_exists($name)) {
            if (isset($args[1]))
                return $name($value, $args[1]);
            return $name($value);
        }
        return false;
    }

    public function required($value)
    {
        $trimmed = trim($value);
        return !empty($trimmed) || $trimmed === '0' || $trimmed === 0;
    }

    public function min_length($value, $length)
    {
        return mb_strlen($value) >= (int)$length;
    }

    public function max_length($value, $length)
    {
        return mb_strlen($value) <= (int)$length;
    }

    public function exact_length($value, $length)
    {
        return mb_strlen($value) === (int)$length;
    }

    public function is_natural_no_zero($value)
    {
        return $value != 0 && $this->is_natural($value);
    }
}
