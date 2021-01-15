<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\Test\TestCache;
use SoapExt\Middleware\WsdlLoader;
use SoapExt\Test\TestCurl;

class WsdlLoaderTest extends TestCase {
    
    protected $cache;
    protected $curl;
    protected $loader;
    
    protected function setUp():void
    {
        $this->cache = new TestCache();
        $this->curl = new TestCurl();
        $this->loader = new WsdlLoader();
    }
    
    
    public function testLoadWsdlFromTestCache()
    {
        $wsdl = $this->loader->loadWsdl("wsdl.wsdl", $this->cache);
        
        $this->assertNotEmpty($wsdl);
        
        $this->assertEquals("https://localhost:8080/services/testservice", $wsdl->getUri());
        
        $this->assertEquals(3, count($wsdl->getIncluded()));
    }
    
    
    public function testDownloadWsdlFromTestCurl()
    {
        $wsdl = $this->loader->downloadWsdl("wsdl.wsdl", $this->curl);
        
        $this->assertNotEmpty($wsdl);
        
        $this->assertEquals("https://localhost:8080/services/testservice", $wsdl->getUri());
        
        $this->assertEquals(3, count($wsdl->getIncluded()));
    }
    
}

