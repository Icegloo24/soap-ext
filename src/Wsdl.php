<?php
namespace SoapExt;

use DOMDocument;
use DOMElement;
use DOMXPath;
use SoapExt\Middleware\Wsdl\AbstractType;
use SoapExt\Middleware\Wsdl\Schema;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use SoapExt\Middleware\Tools\TypeExtracter;

class Wsdl {
    
    use TypeExtracter;
    
    private static $PFX_XML_SCHEMA = 'xsd';
    private static $NS_XML_SCHEMA = 'http://www.w3.org/2001/XMLSchema';
    private static $PFX_WSDL_SCHEMA = 'wsdl';
    private static $NS_WSDL_SCHEMA =  'http://schemas.xmlsoap.org/wsdl/';
    private static $PFX_WSDL_SOAP = 'soapbind';
    private static $NS_WSDL_SOAP = 'http://schemas.xmlsoap.org/wsdl/soap/';
    
    private $uri;
    
    private $wsdl;
    
    private $toIncludes;
    
    private $included;
    private $nsMap;
    
    private $schemes;
    
    public function __construct(string $content=null)
    {
        $this->toIncludes = [];
        $this->included = [];
        $this->nsMap = [];
        $this->schemes = ['http://www.w3.org/2001/XMLSchema'=>new Schema('http://www.w3.org/2001/XMLSchema')];
        
        $this->wsdl = new DOMDocument('1.0');
        if($content != null) {
            $this->wsdl->loadXML($content);
        
            $xpath = new DOMXPath($this->wsdl);
            $pfx_xml_schema = strlen($pfx_xml_schema = $this->wsdl->lookupPrefix(self::$NS_XML_SCHEMA))?$pfx_xml_schema:self::$PFX_XML_SCHEMA;
            $pfx_wsdl_schema = strlen($pfx_wsdl_schema = $this->wsdl->lookupPrefix(self::$NS_WSDL_SCHEMA))?$pfx_wsdl_schema:self::$PFX_WSDL_SCHEMA;
            
            $xpath->registerNamespace($pfx_xml_schema, self::$NS_XML_SCHEMA);
            $xpath->registerNamespace($pfx_wsdl_schema, self::$NS_WSDL_SCHEMA);
            // Get includes/imports
            $query = './/'.$pfx_xml_schema.':include | .//'.$pfx_xml_schema.':import';
            $query .= ' | .//'.$pfx_wsdl_schema.':include | .//'.$pfx_wsdl_schema.':import';
            
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                foreach ($nodes as $node) {
                    $location = $node->getAttribute('schemaLocation');
                    if(strlen($location)) {
                        $this->toIncludes['schemaLocation'] = $location;
                    }
                }
            }
            // Get URI
            $pfx_wsdl_soap = strlen($pfx_wsdl_soap = $this->wsdl->lookupPrefix(self::$NS_WSDL_SOAP))?$pfx_wsdl_soap:self::$PFX_WSDL_SOAP;
            $query = './/'.$pfx_wsdl_soap.':address';
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                foreach ($nodes as $node) {
                    $this->uri = $node->getAttribute('location');
                }
            }
        }
    }
    
    /**
     * Append the fetched Content to the included DOMElements mapped by it's Location.
     *
     * @param string $content
     * @param string $ns
     */
    public function appendNs(string $ns, string $content)
    {
        if(key_exists($ns, $this->toIncludes)) {
            $this->nsMap[$ns] = $this->toIncludes[$ns];
            unset($this->toIncludes[$ns]);
        }
        $dom = new DOMDocument('1.0');
        $dom->loadXML($content);
        $this->included[$ns] = $dom;
        $this->schemes[$ns] = new Schema($ns, $dom, $this);
        
        $xpath = new DOMXPath($dom);
        $pfx_xml_schema = strlen($pfx_xml_schema = $this->wsdl->lookupPrefix(self::$NS_XML_SCHEMA))?$pfx_xml_schema:self::$PFX_XML_SCHEMA;
        $xpath->registerNamespace($pfx_xml_schema, self::$NS_XML_SCHEMA);
        
        $includes = [];
        $query = './/'.$pfx_xml_schema.':include | .//'.self::$PFX_XML_SCHEMA.':import';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $location = $node->getAttribute('schemaLocation');
                $namespace = $node->getAttribute('namespace');
                if(!key_exists($namespace, $this->included)) {
                    $this->toIncludes[$namespace] = $location;
                    $includes[$namespace] = $location;
                }
            }
        }
        return $includes;
    }
    
    /**
     * Returns the Soapbind Adress
     *
     * @return string
     */
    public function getUri():string
    {
        return $this->uri;
    }
    
    /**
     * Returns all Included Schemas mapped by their Namespaces. [ns(string)=>schema(DOM)]
     *
     * @return array
     */
    public function getIncluded():array
    {
        return $this->included;
    }
    
    /**
     * Returns one [key=>value] pair of the next Namespace=>SchemaLocation to include into the Wsdl.
     *
     * @return array
     */
    public function getNextToInclude():array
    {
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
    public function getOperations():array
    {
        $methods = array();
        
        $xpath = new DOMXPath($this->wsdl);
        $pfx_xml_schema = strlen($pfx_xml_schema = $this->wsdl->lookupPrefix(self::$NS_XML_SCHEMA))?$pfx_xml_schema:self::$PFX_XML_SCHEMA;
        $pfx_wsdl_schema = strlen($pfx_wsdl_schema = $this->wsdl->lookupPrefix(self::$NS_WSDL_SCHEMA))?$pfx_wsdl_schema:self::$PFX_WSDL_SCHEMA;
        $xpath->registerNamespace($pfx_xml_schema, self::$NS_XML_SCHEMA);
        $xpath->registerNamespace($pfx_wsdl_schema, self::$NS_WSDL_SCHEMA);
        
        $query = './/'.$pfx_wsdl_schema.':operation';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                foreach($node->childNodes as $child) {
                    if($child->hasAttributes() && $child->getAttribute('soapAction') != null) {
                        $methods[$node->getAttribute('name')] = $child->getAttribute('soapAction');
                    }elseif($child->hasAttributes() && $child->getAttribute('message') != null) {
                        $methods[$node->getAttribute('name')] = $child->getAttribute('message');
                    }
                }
            }
        }
        return $methods;
    }
    
    public function getWsdl(): DOMDocument
    {
        return $this->wsdl;
    }
    
    public function getNsMap(): array {
        return $this->nsMap;
    }
    
    public function getType(?string $type_ns, ?string $type):?AbstractType
    {
        if(isset($this->schemes[$type_ns])) {
            return $this->schemes[$type_ns]->getType($type);
        }return null;
    }
    
    public function appendAccessor($ns, $accessor, $type_ns, $type)
    {
        $this->schemes[$ns]->appendAccessor($this, $accessor, $type, $type_ns);
    }
    
    public function getAccessor($ns, $accessor):array
    {
        return $this->schemes[$ns]->getAccessor($accessor);
    }
    
    public function link()
    {
        foreach($this->schemes as &$schema) {
            $schema->link($this);
        }
    }
    
    public function validate(DOMElement $element, ValidatorInterface $validator):bool
    {
        $valide = true;
        foreach($element->childNodes as $child) {
            if($child->localName != '') {
                $valide = $valide && $this->getType($this->extractTypeNs($child), $this->extractType($child))->validate($child, $validator);
            }
        }
        return $valide;
    }
    
}

