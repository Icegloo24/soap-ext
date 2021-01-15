<?php
namespace SoapExt\Middleware;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SoapExt\Middleware\Interfaces\SignatureMakerInterface;
use SoapExt\Signature\SignSoapDoc;

class SignatureMaker implements SignatureMakerInterface {
    
    private $certificate;
    private $privkey;
    
    public function __construct($certificate, $privkey) {
        $this->certificate = $certificate;
        $this->privkey = $privkey;
    }
    
    public function sign(string $request): string
    {
        $dsig = new SignSoapDoc($request);
        $dsig->addTimestamp(300, 'TS-666', null, null);
        
        $token = $dsig->addBinaryToken(file_get_contents($this->certificate), true, true, 'X509-666');
        
        $options = array(
            'insertBefore'=>false,
            'canonicalMethod'=>XMLSecurityDSig::EXC_C14N,
            'algorithm'=>XMLSecurityDSig::SHA1,
            //'include_ns'=>'SOAP-ENV ns1 ns2 wsu',
            /*'arOptions'=>array(
                'overwrite'=>false,
                'force_uri'=>false,
                'include_ns:Body'=>'ns1 ns2',
                'include_ns:Timestamp'=>'wsse SOAP-ENV ns1 ns2',
                'include_ns:BinarySecurityToken'=>''
            )*/
        );
        $pKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
        $pKey->passphrase = '';
        $pKey->loadKey($this->privkey, true, false);
        
        $dsig->signSoapDoc($pKey, $options);
        
        $dsig->attachTokentoSig($token);
        
        return $dsig->saveXML();
    }

}

