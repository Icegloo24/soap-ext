<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\Middleware\Test\TestCache;
use SoapExt\Middleware\Native\WsdlLoader;
use SoapExt\Middleware\Test\TestCurl;
use SoapExt\Middleware\Native\Cache;

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
    
    
    public function testLoadWsdlFromCacheAfterTestCurl()
    {
        $this->loader->downloadWsdl("wsdl.wsdl", $this->curl);
        
        $cache = new Cache();
        
        $this->loader->cacheWsdl("wsdl.wsdl", $cache);
        
        $this->loader = new WsdlLoader();
        
        $this->assertTrue($this->loader->isCached("wsdl.wsdl", $cache));
        
        $wsdl = $this->loader->loadWsdl("wsdl.wsdl", $cache);
        
        $this->assertNotEmpty($wsdl);
        
        $this->assertEquals("https://localhost:8080/services/testservice", $wsdl->getUri());
        
        $this->assertEquals(3, count($wsdl->getIncluded()));
    }
    
    
    /*public function testLoadWsdlFromCacheAndValidate()
    {
        $this->loader->downloadWsdl("wsdl.wsdl", $this->curl);
        
        $cache = new Cache();
        
        $this->loader->cacheWsdl("wsdl.wsdl", $cache);
        
        $cache->getFile("wsdl.wsdl");
        
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents(__DIR__."/fixtures/expected_test0.xml"));
        
        //$dom->schemaValidate(__DIR__."/fixtures/wsdl/import_controller.xsd");
        $dom->schemaValidate($cache->getFile("import_controller.xsd"));
    }*/
    
}

