<?php namespace Lusito\InSanity;

class InSanity
{
    private $defaultInput;
    private $fields = [];
    private $ruleHandler;
    private $errorHandler;

    public function __construct(array $defaultInput = null, $ruleHandler = null, $errorHandler = null)
    {
        $this->ruleHandler = $ruleHandler ?? new RuleHandler();
        $this->errorHandler = $errorHandler ?? new ErrorHandler();
        $this->defaultInput = $defaultInput ?? $_POST;
    }

    public function __call($name, $args)
    {
        $label = $args[0];
        $input = $args[1] ?? $this->defaultInput;
        $value = $input[$name] ?? null;

        return $this->fields[$name] = new Field($this->errorHandler, $this->ruleHandler, $name, $label, $value);
    }

    public function __get($name)
    {
        return $this->fields[$name] ?? null;
    }

    public function getErrors()
    {
        return $this->errorHandler->getErrors();
    }

    public function toJSON()
    {
        $json = [];
        foreach ($this->fields as $name => $field)
            $json[$name] = $field->getValue();
        return $json;
    }
}
