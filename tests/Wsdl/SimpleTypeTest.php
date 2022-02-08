<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\Middleware\Wsdl\SimpleType;
use SoapExt\Middleware\Native\Validator;
use SoapExt\Wsdl;

class SimpleTypeTest extends TestCase
{
    
    private $validator;
    private $wsdl;
    
    protected function setUp():void
    {
        $this->validator = new Validator();
        $this->wsdl = new Wsdl();
    }
    
    public function testValidationBase()
    {
        $dom = new \DOMDocument('1.0');
        //$constr = new \DOMElement('simpleType');
        $constr = $dom->createElement('simpleType');
        $dom->appendChild($constr);
        $constr->appendChild(new \DOMAttr('name', 'testName'));
        $constr->appendChild(new \DOMAttr('base', 'xsd:string'));
        $ns = $dom->createAttribute('xmlns:xsd');
        $ns->value = 'http://www.w3.org/2001/XMLSchema';
        $constr->appendChild($ns);
        $DOM = new \DOMDocument('1.0');
        $DOM->loadXML($dom->saveXML());
        echo $DOM->saveXML();
        $type = new SimpleType($DOM->firstChild, 'namespace');
        $type->link($this->wsdl);
        
        
        $this->assertTrue($type->validate(new \DOMElement('Test', '10'), $this->validator));
        //$this->assertFalse($type->validate(new \DOMElement('Test', 'abc'), $this->validator));
    }
    
    /*public function testValidationRestriction()
    {
        $constr = new \DOMElement('simpleType');
        $constr->appendChild(new \DOMAttr('name', 'testName'));
        $restriction = new \DOMElement('restriction');
        $restriction->appendChild(new \DOMAttr('base', 'xsd:string'));
        $constr->appendChild($restriction);
        $type = new SimpleType($constr);
        
        $this->assertTrue($type->validate(new \DOMElement('Test', '10'), $this->validator));
        $this->assertFalse($type->validate(new \DOMElement('Test', 'abc'), $this->validator));
    }*/
    
}

