<?php
namespace SoapExt\Middleware\Native;

use SoapExt\Middleware\Interfaces\RequestBuilderInterface;

use DOMDocument;
use DOMNode;

class RequestBuilder implements RequestBuilderInterface {
    
    private static $PFX_SOAP_ENV = "SOAP-ENV";
    private static $NS_SOAP_ENV = "http://schemas.xmlsoap.org/soap/envelope/";
    private static $PFX_XSI = "xsi";
    private static $NS_XSI = "http://www.w3.org/2001/XMLSchema-instance";
    
    private $attributes;
    
    private $registered_ns;
    
    private $ns_index;
    
    public function __construct()
    {
        $this->attributes = array();
        $this->registered_ns = array(self::$NS_SOAP_ENV=>self::$PFX_SOAP_ENV);
        $this->ns_index = 0;
    }
    
    public function buildRequest($arguments, $headers, $wsdlLoader = null): string
    {
        $dom = new DOMDocument('1.0');
        /*
         * Build Nodes || First Header then Body
         */
        $env = $dom->createElementNS(self::$NS_SOAP_ENV, self::$PFX_SOAP_ENV.":Envelope");
        
        if($headers != null) {
            $header = $dom->createElementNS(self::$NS_SOAP_ENV, self::$PFX_SOAP_ENV.":Header");
            
            $this->appendArrayToNode($dom, $header, json_decode(json_encode($headers), true), $wsdlLoader);
            
            $env->appendChild($header);
        }
        
        $body = $dom->createElementNS(self::$NS_SOAP_ENV, self::$PFX_SOAP_ENV.":Body");
        
        $this->appendArrayToNode($dom, $body, json_decode(json_encode($arguments), true), $wsdlLoader);
        
        $env->appendChild($body);
        $dom->appendChild($env);
        
            
        /*
         * Append Attributes
         */
        foreach($this->attributes as $key => $value) {
            $this->appendAttributesToNode($dom, $key, $value);
        }
        
        return $dom->saveXML();
    }
    
    
    protected function getNamespacePrefix(string $namespace): string
    {
        if(!isset($this->registered_ns[$namespace])) {
            $this->registered_ns[$namespace] = "ns".$this->ns_index++;
        }
        return $this->registered_ns[$namespace];
    }
    
    
    protected function registerNamespacePrefix(DOMDocument &$owner, string $namespace)
    {
        $registered_ns = $owner->createAttributeNS(self::$NS_XSI, self::$PFX_XSI.":".$this->getNamespacePrefix($namespace));
        $registered_ns->value = $namespace;
        $owner->firstChild->appendChild($registered_ns);
    }
    
    
    protected function appendAttributesToNode(DOMDocument &$owner, &$key, &$value)
    {
        $attribute = $owner->createAttributeNS(self::$NS_XSI, self::$PFX_XSI.":type");
        if(!isset($this->registered_ns[$value['ns']])) {
            $this->registerNamespacePrefix($owner, $value['ns']);
        }
        $attribute->value = $this->getNamespacePrefix($value['ns']).":".$key;
        $value['node']->setAttributeNodeNS($attribute);
    }
    
    
    protected function appendArrayToNode(DOMDocument &$owner, DOMNode &$node, array $arguments, $wsdlLoader = null)
    {
        if(isset($arguments['enc_type']) || isset($arguments['enc_value']) || isset($arguments['enc_name']) || isset($arguments['enc_namens'])) {
            if(isset($arguments['enc_type']) && isset($arguments['enc_value']) && isset($arguments['enc_name'])) {
                switch($arguments['enc_type']) {
                    case XSD_STRING:
                        if(is_array($arguments['enc_value'])) {
                            throw new \SoapFault("SOAP", "SOAP-ERROR: Wrong Encoding used '101' at Argument ".$arguments['enc_name']."!");
                        }
                        if(array_key_exists('enc_namens', $arguments)) {
                            $argument = $owner->createElementNS($arguments['enc_namens'], 
                                            $this->getNamespacePrefix($arguments['enc_namens']).":".$arguments['enc_name'], $arguments['enc_value']);
                        }else {
                            $argument = $owner->createElement($arguments['enc_name'], $arguments['enc_value']);
                        }
                        break;
                    case SOAP_ENC_OBJECT:
                        if(!is_array($arguments['enc_value'])) {
                            throw new \SoapFault("SOAP", "SOAP-ERROR: Wrong Encoding used '301' at Argument ".$arguments['enc_name']."!");
                        }
                        if(array_key_exists('enc_namens', $arguments)) {
                            $argument = $owner->createElementNS($arguments['enc_namens'], 
                                            $this->getNamespacePrefix($arguments['enc_namens']).":".$arguments['enc_name']);
                        }else {
                            $argument = $owner->createElement($arguments['enc_name']);
                        }
                        $this->appendArrayToNode($owner, $argument, $arguments['enc_value'], $wsdlLoader);
                        break;
                    default:
                }
                if(isset($argument)) {
                    if(isset($arguments['enc_stype']) && isset($arguments['enc_ns'])) {
                        $this->attributes[$arguments['enc_stype']] = ['node'=>&$argument, 'ns'=>$arguments['enc_ns']];
                    }
                    $node->appendChild($argument);
                }
            }
        }else {
            foreach($arguments as $value) {
                if(is_array($value)) {
                    $this->appendArrayToNode($owner, $node, $value, $wsdlLoader);
                }
            }
        }
    }
    
}

