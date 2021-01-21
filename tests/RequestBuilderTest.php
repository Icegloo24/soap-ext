<?php
namespace tests;

use SoapExt\Middleware\Native\RequestBuilder;
use PHPUnit\Framework\TestCase;

class RequestBuilderTest extends TestCase {
    
    protected $input;
    protected $builder;
    protected $result;
    
    protected function setUp():void
    {
        $this->input = json_decode(file_get_contents(__DIR__."/fixtures/soapvar_test0.json"));
        $this->builder = new RequestBuilder();
        $dom = new \DOMDocument('1.0');
        $dom->loadXML(file_get_contents(__DIR__."/fixtures/expected_test0.xml"));
        $this->result = $dom->C14N();
    }
    
    public function testBuildRequestForCorrectOutput()
    {
        $request = $this->builder->buildRequest($this->input, []);
        
        $this->assertNotEmpty($request);
        
        $dom_req = new \DOMDocument('1.0');
        $dom_req->loadXML($request);
        $this->assertEquals($dom_req->C14N(), $this->result);
    }
}

