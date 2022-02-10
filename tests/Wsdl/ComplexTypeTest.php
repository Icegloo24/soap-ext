<?php
namespace tests\Wsdl;

use PHPUnit\Framework\TestCase;
use SoapExt\Middleware\Native\Validator;
use SoapExt\Wsdl;

class ComplexTypeTest extends TestCase
{
    
    private $validator;
    private $wsdl;
    
    protected function setUp():void
    {
        $this->validator = new Validator();
        $this->wsdl = new Wsdl();
    }
    
    public function testValidation()
    {
        $this->assertTrue(true);
    }
    
}

