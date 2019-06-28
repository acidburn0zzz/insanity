<?php namespace Lusito\InSanity;

class Field
{
    private $errorHandler;
    private $ruleHandler;
    private $name;
    private $label;
    private $value;
    private $isArray;
    private $error = null;

    public function __construct($errorHandler, $ruleHandler, $name, $label, $value)
    {
        $this->errorHandler = $errorHandler;
        $this->ruleHandler = $ruleHandler;
        $this->name = $name;
        $this->label =  $label;
        $this->isArray = is_array($value);
        $this->value = $value;
    }

    public function setFailed($rule, $param = null)
    {
        $this->error = $this->errorHandler->setFailed($this->name, $this->label, $rule, $param);
    }

    public function __call($rule, $params)
    {
        if ($this->error)
            return $this;

        // Special handling for required
        if ($rule === 'required' && empty($this->value)) {
            $this->setFailed('required');
            return $this;
        }

        $newValues = [];
        $param = $params[0] ?? null; // fixme: more params?
        $values = $this->isArray ? $this->value : [$this->value];
        foreach ($values as $value) {
            $result = $this->ruleHandler->$rule($value, $param);

            if ($result === false) {
                $this->setFailed($rule, $param);
                return $this;
            }

            if ($result !== true)
                $value = $result;

            $newValues[] = $value;
        }

        $this->value = $this->isArray ? $newValues : $newValues[0];

        return $this;
    }

    public function __get($rule)
    {
        return $this->__call($rule, []);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getError()
    {
        return $this->error;
    }

    private static function toBool($value)
    {
        if (is_string($value))
            return !!filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return boolval($value);
    }

    public function val($default = '')
    {
        if ($this->value === null)
            $this->value = $default;
        return $this->value;
    }

    public function boolval($default = false)
    {
        if ($this->value === null)
            $this->value = $default;
        else if ($this->isArray)
            $this->value = array_map('self::toBool', $this->value);
        else
            $this->value = self::toBool($this->value);
        return $this->value;
    }

    public function intval($default = 0)
    {
        if ($this->value === null)
            $this->value = $default;
        else if ($this->isArray)
            $this->value = array_map('intval', $this->value);
        else
            $this->value = intval($this->value);
        return $this->value;
    }

    public function floatval($default = 0.0)
    {
        if ($this->value === null)
            $this->value = $default;
        else if ($this->isArray)
            $this->value = array_map('floatval', $this->value);
        else
            $this->value = floatval($this->value);
        return $this->value;
    }
}
