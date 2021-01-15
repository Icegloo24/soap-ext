<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use SoapExt\SoapClient;
use SoapExt\Test\TestCache;
use SoapExt\Middleware\Curl;
use SoapExt\Middleware\RequestBuilder;
use SoapExt\Middleware\WsdlLoader;
use SoapExt\Middleware\RequestAdjustment;
use SoapExt\Middleware\SignatureMaker;

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
        
        $client->__setSoapHeaders(
            [
                "enc_type"=>101,
                "enc_value"=>"123",
                "enc_name"=>"Token",
                "enc_namens"=>"https://www.token.com"
            ]);
        
        $client->getQuote($this->input);
        //print "\n".$client->__getLastRequest();
        $this->assertNotEmpty($client->__getLastRequest());
        
    }
    
}

