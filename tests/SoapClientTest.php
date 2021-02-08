<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\SoapClient;
use SoapExt\Middleware\Test\TestCache;
use SoapExt\Middleware\Native\Curl;
use SoapExt\Middleware\Native\RequestBuilder;
use SoapExt\Middleware\Native\WsdlLoader;
use SoapExt\Middleware\Native\RequestAdjustment;
use SoapExt\Middleware\Native\SignatureMaker;

class SoapClientTest extends TestCase {
    
    protected $input;
    protected $options;
    protected $wsdl;
    
    protected function setUp():void
    {
        $this->input = json_decode(file_get_contents(__DIR__."/fixtures/soapvar_test1.json"));
        $this->options = [
            "debug"         => true
        ];
        $this->wsdl = "wsdl.wsdl";
    }
    
    public function testSoapCall()
    {
        
        $client = new SoapClient($this->wsdl, $this->options, 
            [
                new TestCache(), new RequestBuilder(), new WsdlLoader(), new Curl($this->options), 
                new RequestAdjustment(), new SignatureMaker(__DIR__."/fixtures/signing/mycert.pem", __DIR__."/fixtures/signing/privkey.pem")
            ]);
        
        $client->__setSoapHeaders(new \SoapVar("123", XSD_STRING, null, null, "Token", "https://www.token.com"));
        
        $client->Test($this->input);
        //print "\n".$client->__getLastRequest();
        $this->assertNotEmpty($client->__getLastRequest());
        
    }
    
}

