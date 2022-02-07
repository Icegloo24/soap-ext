<?php
namespace tests\Wsdl;

use PHPUnit\Framework\TestCase;
use SoapExt\Middleware\Wsdl\BaseType;
use SoapExt\Middleware\Native\Validator;

class BaseTypeTest extends TestCase
{
    
    private $validator;
    
    protected function setUp():void
    {
        $this->validator = new Validator();
    }
    
    public function testValidationString()
    {
        $type = new BaseType('string');
        $this->assertTrue($type->validate(new \DOMElement('Test', '10'), $this->validator));
        $this->assertTrue($type->validate(new \DOMElement('Test', 'true'), $this->validator));
        $this->assertTrue($type->validate(new \DOMElement('Test', 'abc'), $this->validator));
        $this->assertFalse($type->validate(new \DOMElement('Test', null), $this->validator));
    }
    
    public function testValidationDecimal()
    {
        $type = new BaseType('decimal');
        $this->assertTrue($type->validate(new \DOMElement('Test', '10'), $this->validator));
        $this->assertFalse($type->validate(new \DOMElement('Test', 'a02'), $this->validator));
    }
    
    public function testValidationInteger()
    {
        $type = new BaseType('integer');
        $this->assertTrue($type->validate(new \DOMElement('Test', '10'), $this->validator));
        $this->assertFalse($type->validate(new \DOMElement('Test', 'a02'), $this->validator));
    }
    
    public function testValidationBoolean()
    {
        $type = new BaseType('boolean');
        $this->assertTrue($type->validate(new \DOMElement('Test', 'true'), $this->validator));
        $this->assertTrue($type->validate(new \DOMElement('Test', true), $this->validator));
        $this->assertTrue($type->validate(new \DOMElement('Test', 1), $this->validator));
        $this->assertFalse($type->validate(new \DOMElement('Test', 'brot'), $this->validator));
    }
    
}

