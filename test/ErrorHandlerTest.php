<?php
use Lusito\InSanity\ErrorHandler;

final class ErrorHandlerTest extends TestCase
{
    public function testSetFailed()
    {
        $handler = new ErrorHandler();
        $this->assertEmpty($handler->getErrors());

        $handler->setFailed('some_field', 'Some field label', 'valid_email');
        $this->assertEquals([
            'some_field' => 'The field "Some field label" must be a valid e-mail address.'
        ], $handler->getErrors());

        $handler->setFailed('some_other_field', 'Some other field label', 'valid_email');
        $this->assertEquals([
            'some_field' => 'The field "Some field label" must be a valid e-mail address.',
            'some_other_field' => 'The field "Some other field label" must be a valid e-mail address.'
        ], $handler->getErrors());

        $handler->setFailed('some_field', 'Some field label', 'is_natural_no_zero');
        $this->assertEquals([
            'some_field' => 'The field "Some field label" may only contain natural values greater than 0.',
            'some_other_field' => 'The field "Some other field label" must be a valid e-mail address.'
        ], $handler->getErrors());
    }

    public function testSetFailedWithParam()
    {
        $handler = new ErrorHandler();
        $this->assertEmpty($handler->getErrors());

        $handler->setFailed('some_field', 'Some field label', 'min_length', 11);
        $this->assertEquals([
            'some_field' => 'The field "Some field label" must not be shorter than 11 characters.'
        ], $handler->getErrors());

        $handler->setFailed('some_other_field', 'Some other field label', 'max_length', 12);
        $this->assertEquals([
            'some_field' => 'The field "Some field label" must not be shorter than 11 characters.',
            'some_other_field' => 'The field "Some other field label" must not be longer than 12 characters.'
        ], $handler->getErrors());

        $handler->setFailed('some_field', 'Some field label', 'exact_length', 13);
        $this->assertEquals([
            'some_field' => 'The field "Some field label" must be exactly 13 characters long.',
            'some_other_field' => 'The field "Some other field label" must not be longer than 12 characters.'
        ], $handler->getErrors());
    }
}
