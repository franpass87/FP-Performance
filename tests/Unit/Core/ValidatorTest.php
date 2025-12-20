<?php

namespace FP\PerfSuite\Tests\Unit\Core;

use FP\PerfSuite\Tests\TestCase;
use FP\PerfSuite\Core\Validation\Validator;
use FP\PerfSuite\Core\Validation\ValidationResult;

/**
 * Test Validator
 *
 * @package FP\PerfSuite\Tests\Unit\Core
 */
class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    public function testValidateRequired(): void
    {
        $data = ['name' => 'test'];
        $rules = ['name' => 'required'];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result->isValid());
    }

    public function testValidateRequiredFails(): void
    {
        $data = [];
        $rules = ['name' => 'required'];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
    }

    public function testValidateEmail(): void
    {
        $data = ['email' => 'test@example.com'];
        $rules = ['email' => 'required|email'];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result->isValid());
    }

    public function testValidateEmailFails(): void
    {
        $data = ['email' => 'invalid-email'];
        $rules = ['email' => 'required|email'];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result->isValid());
    }

    public function testValidateMin(): void
    {
        $data = ['age' => 18];
        $rules = ['age' => 'required|min:18'];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result->isValid());
    }

    public function testValidateMinFails(): void
    {
        $data = ['age' => 15];
        $rules = ['age' => 'required|min:18'];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result->isValid());
    }

    public function testValidateMax(): void
    {
        $data = ['age' => 65];
        $rules = ['age' => 'required|max:100'];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result->isValid());
    }

    public function testValidateMaxFails(): void
    {
        $data = ['age' => 150];
        $rules = ['age' => 'required|max:100'];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result->isValid());
    }

    public function testValidateNumeric(): void
    {
        $data = ['count' => '123'];
        $rules = ['count' => 'required|numeric'];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result->isValid());
    }

    public function testValidateNumericFails(): void
    {
        $data = ['count' => 'abc'];
        $rules = ['count' => 'required|numeric'];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result->isValid());
    }

    public function testValidateValue(): void
    {
        $this->assertTrue($this->validator->validateValue('test@example.com', 'email'));
        $this->assertFalse($this->validator->validateValue('invalid-email', 'email'));
    }

    public function testValidateMultipleRules(): void
    {
        $data = [
            'email' => 'test@example.com',
            'age' => 25,
        ];
        $rules = [
            'email' => 'required|email',
            'age' => 'required|numeric|min:18|max:100',
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result->isValid());
    }

    public function testValidationResultData(): void
    {
        $data = ['name' => 'test'];
        $rules = ['name' => 'required'];

        $result = $this->validator->validate($data, $rules);
        $this->assertEquals(['name' => 'test'], $result->getData());
    }
}










