<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\Middleware\Test\TestCache;
use SoapExt\Native\Middleware\WsdlLoader;
use SoapExt\Wsdl;

class WsdlTest extends TestCase {
    
    protected $cache;
    
    protected function setUp():void
    {
        $this->cache = new TestCache();
    }
    
    
    public function testBuildWsdlFromTestCache()
    {
        $wsdl = new Wsdl($this->cache->getContent("wsdl.wsdl"));
        
        $this->assertEquals(0, count($wsdl->getIncluded()));
        
        $this->assertEquals("https://localhost:8080/services/testservice", $wsdl->getUri());
        $this->assertEquals('{"Test":"urn:Test"}', json_encode($wsdl->getOperations()));
        
        $this->assertEquals(["schemaLocation"=>"import_controller.xsd"], $wsdl->getToIncludes());
        
        $wsdl->appendNs("schemaLocation", $this->cache->getContent("import_controller.xsd"));
        
        $this->assertEquals(1, count($wsdl->getIncluded()));
        
        $wsdl->appendNs("http://www.test123.uri/0", $this->cache->getContent("import_0.xsd"));
        
        $this->assertEquals(2, count($wsdl->getIncluded()));
        
        $wsdl->appendNs("http://www.test123.uri/1", $this->cache->getContent("import_1.xsd"));
        
        $this->assertEquals(3, count($wsdl->getIncluded()));
        
        $this->assertEquals([], $wsdl->getToIncludes());
    }
    
}

