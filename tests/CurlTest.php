<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\Middleware\Curl;

class CurlTest extends TestCase {
    
    protected $curl;
    protected $timeout;
    
    protected function setUp():void
    {
        $this->timeout = 1;
        $this->curl = new Curl([
            'timeout'=>$this->timeout,
        ]);
    }
    
    
    public function testExecuteOnValidUri()
    {
        $this->assertTrue($this->curl->execute('http://www.google.com'));
        
        $this->assertNotEmpty($this->curl->getLastResponse());
        $this->assertNotEmpty($this->curl->getLastResponseHeader());
        $this->assertNotEmpty($this->curl->getLastResponseBody());
        $this->assertEmpty($this->curl->getLastError());
        $this->assertEmpty($this->curl->getLastErrorMessage());
    }
    
    
    public function testExecuteOnInvalidUri()
    {
        $time=time();
        $this->assertFalse($this->curl->execute('http://www.google.com:81/'));
        
        //Measure Time until Timeout
        $this->assertGreaterThan(time()-$time, $this->timeout+0.01);
        
        $this->assertNotEmpty($this->curl->getLastError());
        $this->assertNotEmpty($this->curl->getLastErrorMessage());
        $this->assertEquals("HTTP", $this->curl->getLastError());
        $this->assertEmpty($this->curl->getLastResponse());
        $this->assertEmpty($this->curl->getLastResponseHeader());
        $this->assertEmpty($this->curl->getLastResponseBody());
    }
    
}

