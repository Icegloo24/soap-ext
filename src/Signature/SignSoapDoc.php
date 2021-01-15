<?php
namespace SoapExt\Signature;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;

/**
 * This Class derived from robrichards/wse-php with some minor changes.
 */

class SignSoapDoc {
    const WSSENS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    const WSSEPFX = 'wsse';
    const WSUNS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    const WSUPFX = 'wsu';
    const WSUNAME = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0';
    
    private $soapNS, $soapPFX;
    /**@var DOMDocument**/
    private $soapDoc = null;
    /**@var DOMElement**/
    private $envelope = null;
    /**@var DOMXPath**/
    private $SOAPXPath = null;
    /**@var DOMElement**/
    private $secNode = null;
    
    public $signBody = true;
    
    /**
     * Construct the SoapDoc Signer.
     *
     * @param DOMDocument|string $doc
     */
    public function __construct($doc) {
        if(is_string($doc)) {
            $this->soapDoc = new DOMDocument();
            $this->soapDoc->loadXML($doc);
            $doc = $this->soapDoc;
        }elseif($doc instanceof DOMDocument) {
            $this->soapDoc = $doc;
        }else {
            throw new Exception('Invalid parameter: valid DOMDocument or String expected');
        }
        $this->envelope = $doc->documentElement;
        $this->soapNS = $this->envelope->namespaceURI;
        $this->soapPFX = $this->envelope->prefix;
        $this->SOAPXPath = new DOMXPath($doc);
        $this->SOAPXPath->registerNamespace('wssoap', $this->soapNS);
        $this->SOAPXPath->registerNamespace('wswsse', self::WSSENS);
        $this->locateSecurityHeader();
    }
    
    /**
     * Add a Timestamp to the Signature.
     *
     * @param number $secondsToExpire
     * @param string|null $id
     * @param string|null $content_created
     * @param string|null $content_expires
     * @return DOMElement
     */
    public function addTimestamp($secondsToExpire = 3600, $id = null, $content_created = null, $content_expires = null) {
        $security = $this->locateSecurityHeader();
        
        $timestamp = $this->soapDoc->createElementNS(self::WSUNS, self::WSUPFX.':Timestamp');
        $security->insertBefore($timestamp, $security->firstChild);
        $currentTime = time();
        $created = $this->soapDoc->createElementNS(self::WSUNS,  self::WSUPFX.':Created', $content_created?$content_created:(gmdate("Y-m-d\TH:i:s", $currentTime).'Z'));
        $timestamp->appendChild($created);
        if (!is_null($secondsToExpire)) {
            $expire = $this->soapDoc->createElementNS(self::WSUNS,  self::WSUPFX.':Expires', $content_expires?$content_expires:(gmdate("Y-m-d\TH:i:s", $currentTime + $secondsToExpire).'Z'));
            $timestamp->appendChild($expire);
        }
        if(!is_null($id)) {
            $attrib = $this->soapDoc->createAttributeNS(self::WSUNS, self::WSUPFX.':Id');
            $attrib->value = $id;
            $timestamp->setAttributeNodeNS($attrib);
        }
        return $timestamp;
    }
    
    /**
     * Add the Certificate to the Signature. The Binary Token will be also canonicalized and digested when added before the Sign!
     *
     * @param string $cert
     * @param boolean $isPEMFormat
     * @param boolean $isDSig
     * @param string|null $id
     * @return DOMElement
     */
    public function addBinaryToken($cert, $isPEMFormat = true, $isDSig = true, $id = null) {
        $security = $this->locateSecurityHeader();
        $data = XMLSecurityDSig::get509XCert($cert, $isPEMFormat);
        
        $token = $this->soapDoc->createElementNS(self::WSSENS, self::WSSEPFX.':BinarySecurityToken', $data);
        $security->insertBefore($token, $security->firstChild);
        
        $token->setAttribute('EncodingType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary');
        if(!is_null($id)) {
            $attrib = $this->soapDoc->createAttributeNS(self::WSUNS, self::WSUPFX.':Id');
            $attrib->value = $id;
            $token->setAttributeNodeNS($attrib);
        }else {
            $attrib = $this->soapDoc->createAttributeNS(self::WSUNS, self::WSUPFX.':Id');
            $attrib->value = XMLSecurityDSig::generateGUID();
            $token->setAttributeNodeNS($attrib);
        }
        $token->setAttribute('ValueType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3');
        return $token;
    }
    
    /**
     * Attach the Token to the signature! Todo after the Signing!
     *
     * @param DOMElement $token
     * @throws Exception
     */
    public function attachTokentoSig($token) {
        if (!($token instanceof DOMElement)) {
            throw new Exception('Invalid parameter: BinarySecurityToken element expected');
        }
        $objXMLSecDSig = new XMLSecurityDSig();
        if ($objDSig = $objXMLSecDSig->locateSignature($this->soapDoc)) {
            $tokenURI = '#'.$token->getAttributeNS(self::WSUNS, 'Id');
            $this->SOAPXPath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
            $query = './secdsig:KeyInfo';
            $nodeset = $this->SOAPXPath->query($query, $objDSig);
            $keyInfo = $nodeset->item(0);
            if (!$keyInfo) {
                $keyInfo = $objXMLSecDSig->createNewSignNode('KeyInfo');
                $objDSig->appendChild($keyInfo);
            }
            
            $tokenRef = $this->soapDoc->createElementNS(self::WSSENS, self::WSSEPFX.':SecurityTokenReference');
            $keyInfo->appendChild($tokenRef);
            $reference = $this->soapDoc->createElementNS(self::WSSENS, self::WSSEPFX.':Reference');
            $reference->setAttribute('ValueType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3');
            $reference->setAttribute('URI', $tokenURI);
            $tokenRef->appendChild($reference);
        } else {
            throw new Exception('Unable to locate digital signature');
        }
    }
    
    /**
     * Sign the whole Soap!
     *
     * @param XMLSecurityKey $objKey
     * @param array|null $options </br>
     *     | Takes Arguments:</br>
     *     | -> insertBefore    => string boolean </br>
     *     | -> canonicalMethod => string (i.e. XMLSecurityDSig::EXC_C14N) </br>
     *     | -> algorithm       => string (i.e. XMLSecurityDSig::SHA1) </br>
     *     | -> include_ns      => string (i.e. "soapenv wsse wsu") </br>
     *     | -> arOptions       => array
     */
    public function signSoapDoc($objKey, $options = null) {
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(
            (is_array($options) && isset($options['canonicalMethod']) ? $options['canonicalMethod'] : XMLSecurityDSig::EXC_C14N),
            (is_array($options) && isset($options['include_ns'])?$options['include_ns']:null));
        
        $arNodes = array();
        if ($this->signBody) {
            foreach ($this->envelope->childNodes as $node) {
                if ($node->namespaceURI == $this->soapNS && $node->localName == 'Body') {
                    $arNodes[] = $node;
                    break;
                }
            }
        }
        foreach ($this->secNode->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $arNodes[] = $node;
            }
        }
        
        $algorithm = (is_array($options) && isset($options['algorithm']))?$options['algorithm']:XMLSecurityDSig::SHA1;
        
        $arOptions = array('prefix' => self::WSUPFX, 'prefix_ns' => self::WSUNS);
        if (is_array($options) && isset($options['arOptions']) && is_array($options['arOptions'])) {
            $arOptions = array_merge($arOptions, $options['arOptions']);
        }
        $objDSig->addReferenceList($arNodes, $algorithm, null, $arOptions);
        
        $objDSig->sign($objKey, $this->secNode);
        
        $insertTop = (is_array($options) && isset($options['insertBefore']))?(bool) $options['insertBefore']:true;
        
        $objDSig->appendSignature($this->secNode, $insertTop);
    }
    
    /**
     *
     * @return string
     */
    public function saveXML() {
        return $this->soapDoc->saveXML();
    }
    
    private function locateSecurityHeader() {
        if ($this->secNode == null) {
            $headers = $this->SOAPXPath->query('//wssoap:Envelope/wssoap:Header');
            $header = $headers->item(0);
            if (!$header) {
                $header = $this->soapDoc->createElementNS($this->soapNS, $this->soapPFX.':Header');
                $this->envelope->insertBefore($header, $this->envelope->firstChild);
            }
            $secnodes = $this->SOAPXPath->query('./wswsse:Security', $header);
            $secnode = null;
            foreach ($secnodes as $node) {
                $secnode = $node;
                break;
            }
            if (!$secnode) {
                $secnode = $this->soapDoc->createElementNS(self::WSSENS, self::WSSEPFX.':Security');
                $header->appendChild($secnode);
            }
            $this->secNode = $secnode;
        }
        return $this->secNode;
    }
}