<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\Middleware\Test\TestCache;
use SoapExt\Middleware\Native\WsdlLoader;
use SoapExt\Middleware\Test\TestCurl;
use SoapExt\Middleware\Native\Cache;
use SoapExt\Middleware\Native\Validator;

class ValidatorTest extends TestCase
{
    
    protected $cache;
    protected $loader;
    protected $validator;
    
    protected function setUp():void
    {
        $this->cache = new TestCache();
        $this->loader = new WsdlLoader();
        $this->validator = new Validator();
    }
    
    protected function tearDown():void
    {
        //echo "\n\n";
    }
    
    public function testValidation()
    {
        $wsdl = $this->loader->loadWsdl("wsdl.wsdl", $this->cache);
        
        $this->validator = new Validator();
        $this->assertTrue($this->validator->validate(file_get_contents(__DIR__."/fixtures/valide_test0.xml"), $wsdl));
        
        $this->validator = new Validator();
        $this->assertFalse($this->validator->validate(file_get_contents(__DIR__."/fixtures/invalide_test0.xml"), $wsdl));
        
        $this->validator = new Validator();
        $this->assertFalse($this->validator->validate(file_get_contents(__DIR__."/fixtures/invalide_test1.xml"), $wsdl));
        
        $this->validator = new Validator();
        $this->assertFalse($this->validator->validate(file_get_contents(__DIR__."/fixtures/invalide_test2.xml"), $wsdl));
    }
    
    public function testErrors()
    {
        $wsdl = $this->loader->loadWsdl("wsdl.wsdl", $this->cache);
        
        $this->validator = new Validator();
        $this->validator->validate(file_get_contents(__DIR__."/fixtures/valide_test0.xml"), $wsdl);
        $this->assertEquals(0, count($this->validator->getErrors()));
        
        $this->validator = new Validator();
        $this->validator->validate(file_get_contents(__DIR__."/fixtures/invalide_test0.xml"), $wsdl);
        $this->assertEquals(1, count($this->validator->getErrors()));
        
        $this->validator = new Validator();
        $this->validator->validate(file_get_contents(__DIR__."/fixtures/invalide_test1.xml"), $wsdl);
        $this->assertEquals(1, count($this->validator->getErrors()));
        
        $this->validator = new Validator();
        $this->validator->validate(file_get_contents(__DIR__."/fixtures/invalide_test2.xml"), $wsdl);
        $this->assertEquals(1, count($this->validator->getErrors()));
    }
    
}

