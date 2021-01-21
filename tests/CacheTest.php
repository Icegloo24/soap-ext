<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\Middleware\Native\Cache;

class CacheTest extends TestCase {
    
    private $cache;
    
    protected function setUp():void
    {
        $this->cache = new Cache();
    }
    
    
    protected function tearDown()
    {
        $this->cache->clearCache();
    }
    
    
    public function testPutGetContent()
    {
        $cache = new Cache();
        //print $cache->getFile("Hallo");
        
        $this->assertFalse($cache->hasFile("Hallo"));
        
        $cache->putContent("Hallo", "Hallo");
        
        $this->assertTrue($cache->hasFile("Hallo"));
        
        $this->assertEquals("Hallo", $cache->getContent("Hallo"));
    }
    
    
    public function testClearCache()
    {
        $cache = new Cache();
        
        $this->assertTrue($cache->clearCache());
        
        $this->assertFalse($cache->hasFile("Hallo"));
    }
    
}

