<?php namespace Lusito\InSanity;

class ErrorHandler
{
    protected $errors = [];
    protected $translator = null;

    public function setFailed($fieldName, $fieldLabel, $rule, $param = null)
    {
        $translator = $this->getTranslator();
        $translatedField = $translator->translateField($fieldName, $fieldLabel);

        $message = $translator->translateRule($rule);
        $message = str_replace("{FIELD}", $translatedField, $message);
        if ($param !== null)
            $message = str_replace("{PARAM}", $param, $message);

        $this->errors[$fieldName] = $message;
        return $message;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function getTranslator()
    {
        if ($this->translator === null)
            $this->translator = new Translator();
        return $this->translator;
    }
}
