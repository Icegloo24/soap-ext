<?php
namespace SoapExt;

use DOMDocument;
use DOMXPath;

class Wsdl {
    
    private static $PFX_XML_SCHEMA = 'xsd';
    private static $NS_XML_SCHEMA = 'http://www.w3.org/2001/XMLSchema';
    private static $PFX_WSDL_SCHEMA = 'wsdl';
    private static $NS_WSDL_SCHEMA =  'http://schemas.xmlsoap.org/wsdl/';
    
    private $uri;
    
    private $wsdl;
    
    private $toIncludes;
    
    private $included;
    
    public function __construct(string $content)
    {
        $this->toIncludes = array();
        $this->included = array();
        
        $this->wsdl = new DOMDocument('1.0');
        $this->wsdl->loadXML($content);
        
        $xpath = new DOMXPath($this->wsdl);
        $xpath->registerNamespace(self::$PFX_XML_SCHEMA, self::$NS_XML_SCHEMA);
        $xpath->registerNamespace(self::$PFX_WSDL_SCHEMA, self::$NS_WSDL_SCHEMA);
        // Get includes/imports
        $query = './/'.self::$PFX_XML_SCHEMA.':include | .//'.self::$PFX_XML_SCHEMA.':import';
        $query .= ' | .//'.self::$PFX_WSDL_SCHEMA.':include | .//'.self::$PFX_WSDL_SCHEMA.':import';
        
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $location = $node->getAttribute('schemaLocation');
                $this->toIncludes['schemaLocation'] = $location;
            }
        }
        // Get URI
        $query = './/soapbind:address';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $this->uri = $node->getAttribute('location');
            }
        }
    }
    
    /**
     * Append the fetched Content to the included DOMElements mapped by it's Namespace.
     * 
     * @param string $content
     * @param string $ns
     */
    public function appendNs(string $ns, string $content)
    {
        if(key_exists($ns, $this->toIncludes)) {
            unset($this->toIncludes[$ns]);
        }
        $dom = new DOMDocument('1.0');
        $dom->loadXML($content);
        $this->included[$ns] = $dom;
        
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace(self::$PFX_XML_SCHEMA, self::$NS_XML_SCHEMA);
        
        $query = './/'.self::$PFX_XML_SCHEMA.':include | .//'.self::$PFX_XML_SCHEMA.':import';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $location = $node->getAttribute('schemaLocation');
                $namespace = $node->getAttribute('namespace');
                if(!key_exists($namespace, $this->included)) {
                    $this->toIncludes[$namespace] = $location;
                }
            }
        }
    }
    
    /**
     * Returns the Soapbind Adress
     * 
     * @return string
     */
    public function getUri(): string {
        return $this->uri;
    }
    
    /**
     * Returns all Included Schemas mapped by their Namespaces. [ns(string)=>schema(DOM)]
     * 
     * @return array
     */
    public function getIncluded(): array {
        return $this->included;
    }
    
    /**
     * Returns one [key=>value] pair of the next Namespace=>SchemaLocation to include into the Wsdl.
     * 
     * @return array
     */
    public function getNextToInclude(): array {
        foreach($this->toIncludes as $key => $value) {
            return [$key => $value];
        }
        return [];
    }
    
    /**
     * Returns all Soap Actions mapped as [name=>soapAction]
     * 
     * @return array
     */
    public function getOperations(): array
    {
        $methods = array();
        
        $xpath = new DOMXPath($this->wsdl);
        $xpath->registerNamespace(self::$PFX_XML_SCHEMA, self::$NS_XML_SCHEMA);
        $xpath->registerNamespace(self::$PFX_WSDL_SCHEMA, self::$NS_WSDL_SCHEMA);
        
        $query = './/'.self::$PFX_WSDL_SCHEMA.':operation';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                foreach($node->childNodes as $child) {
                    if($child->hasAttributes() && $child->getAttribute('soapAction') != null) {
                        $methods[$node->getAttribute('name')] = $child->getAttribute('soapAction');
                    }
                }
            }
        }
        return $methods;
    }
    
}

