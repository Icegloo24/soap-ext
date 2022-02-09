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
        $constr = $dom->createElement('simpleType');
        $dom->appendChild($constr);
        $constr->appendChild(new \DOMAttr('name', 'testName'));
        $constr->appendChild(new \DOMAttr('base', 'xsd:decimal'));
        $ns = $dom->createAttribute('xmlns:xsd');
        $ns->value = 'http://www.w3.org/2001/XMLSchema';
        $constr->appendChild($ns);
        $DOM = new \DOMDocument('1.0');
        $DOM->loadXML($dom->saveXML());
        $type = new SimpleType($DOM->firstChild, 'namespace');
        $type->link($this->wsdl);
        
        
        $this->assertTrue($type->validate(new \DOMElement('Test', '10'), $this->validator));
        $this->assertFalse($type->validate(new \DOMElement('Test', 'abc'), $this->validator));
    }
    
    public function testValidationRestriction()
    {
        $dom = new \DOMDocument('1.0');
        $constr = $dom->createElement('simpleType');
        $dom->appendChild($constr);
        $constr->appendChild(new \DOMAttr('name', 'testName'));
        
        $restriction = $dom->createElement('restriction');
        $constr->appendChild($restriction);
        $restriction->appendChild(new \DOMAttr('base', 'xsd:decimal'));
        
        $ns = $dom->createAttribute('xmlns:xsd');
        $ns->value = 'http://www.w3.org/2001/XMLSchema';
        $constr->appendChild($ns);
        
        $DOM = new \DOMDocument('1.0');
        $DOM->loadXML($dom->saveXML());
        $type = new SimpleType($DOM->firstChild, 'namespace');
        $type->link($this->wsdl);
        
        $this->assertTrue($type->validate(new \DOMElement('Test', '10'), $this->validator));
        $this->assertFalse($type->validate(new \DOMElement('Test', 'abc'), $this->validator));
    }
    
    public function testValidationEnum()
    {
        $dom = new \DOMDocument('1.0');
        $constr = $dom->createElement('simpleType');
        $dom->appendChild($constr);
        $constr->appendChild(new \DOMAttr('name', 'testName'));
        
        $enum = $dom->createElement('enumeration');
        $constr->appendChild($enum);
        $enum->appendChild(new \DOMAttr('value', 'true'));
        
        $enum = $dom->createElement('enumeration');
        $constr->appendChild($enum);
        $enum->appendChild(new \DOMAttr('value', 'false'));
        
        $ns = $dom->createAttribute('xmlns:xsd');
        $ns->value = 'http://www.w3.org/2001/XMLSchema';
        $constr->appendChild($ns);
        $DOM = new \DOMDocument('1.0');
        $DOM->loadXML($dom->saveXML());
        $type = new SimpleType($DOM->firstChild, 'namespace');
        $type->link($this->wsdl);
        
        
        $this->assertTrue($type->validate(new \DOMElement('Test', 'true'), $this->validator));
        $this->assertFalse($type->validate(new \DOMElement('Test', 'tuesday'), $this->validator));
    }
    
}

