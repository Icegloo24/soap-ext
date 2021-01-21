<?php
namespace tests;

use SoapExt\Middleware\Native\SignatureMaker;
use PHPUnit\Framework\TestCase;

class SignatureMakerTest extends TestCase {
    
    protected $request;
    protected $signer;
    
    protected function setUp():void
    {
        $this->signer = new SignatureMaker(__DIR__."/fixtures/signing/mycert.pem", __DIR__."/fixtures/signing/privkey.pem");
        
        $dom = new \DOMDocument('1.0');
        $dom->loadXML(file_get_contents(__DIR__."/fixtures/expected_test0.xml"));
        $this->request = $dom->C14N();
    }
    
    
    public function testSignatureForExpressions()
    {
        $signed = $this->signer->sign($this->request);
        
        $this->assertNotEquals(false, strpos($signed, "Timestamp"));
        
        $this->assertNotEquals(false, strpos($signed, "Digest"));
        
        $this->assertNotEquals(false, strpos($signed, "Signature"));
        
        $this->assertNotEquals(false, strpos($signed, "Id"));
    }
    
}

