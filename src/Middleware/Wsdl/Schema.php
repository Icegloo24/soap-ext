<?php
namespace SoapExt\Middleware\Wsdl;

use \DOMDocument;
use SoapExt\Wsdl;

class Schema
{
    
    private $ns;
    
    private $type;
    private $element;
    private $accessor;
    
    public function __construct(string $ns, DOMDocument $doc=null, Wsdl $wsdl=null)
    {
        $this->ns = $ns;
        $this->type = [];
        $this->element = [];
        $this->accessor = [];
        if($doc != null) {
            foreach($doc->getElementsByTagName("complexType") as $complex) {
                $type = new ComplexType($complex, $this->ns);
                $this->type[$type->getName()] = $type;
            }
            foreach($doc->getElementsByTagName("simpleType") as $complex) {
                $type = new SimpleType($complex, $this->ns);
                $this->type[$type->getName()] = $type;
            }
            foreach($doc->getElementsByTagName("element") as $complex) {
                if($complex->parentNode->localName == 'schema') {
                    $element = new Element($complex, $this->ns);
                    $this->element[$element->getName()] = $element;
                }
            }
        }else {
            foreach(BaseType::$DATA_TYPES as $type) {
                $this->type[$type] = new BaseType($type);
            }
        }
    }
    
    public function getNamespace():string
    {
        return $this->ns;
    }
    
    public function getType(string $type):AbstractType
    {
        return $this->type[$type]??null;
    }
    
    public function appendAccessor(Wsdl $wsdl, $name, $type, $type_ns)
    {
        if(!isset($this->accessor[$name])) {
            $this->accessor[$name] = [];
        }
        if(!in_array($wsdl->getType($type_ns, $type), $this->accessor[$name])) {
            $this->accessor[$name][] = $wsdl->getType($type_ns, $type);
        }
    }
    
    public function getAccessor($name):array
    {
        return $this->accessor[$name]??[];
    }
    
    public function link(Wsdl $wsdl)
    {
        foreach($this->type as &$complexType) {
            $complexType->link($wsdl);
        }
        foreach($this->element as &$element) {
            $element->link($wsdl);
        }
    }
    
}

