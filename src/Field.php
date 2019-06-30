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

        // Special handling for some rules
        if ($rule === 'required' && empty($this->value)) {
            $this->setFailed('required');
            return $this;
        } else if ($this->value === null) {
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
        if ($rule === 'boolval')
            return $this->boolval();
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

    public function default($default)
    {
        if ($this->value === null)
            $this->value = $default;
        return $this;
    }

    private static function asBool($value)
    {
        if (is_string($value))
            return !!filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return boolval($value);
    }

    public function boolval()
    {
        if ($this->isArray)
            $this->value = array_map('self::asBool', $this->value);
        else if ($this->value !== null)
            $this->value = self::asBool($this->value);
        return $this;
    }
}
