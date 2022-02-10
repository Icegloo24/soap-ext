<?php
namespace SoapExt\Middleware\Wsdl;

use \DOMDocument;
use SoapExt\Wsdl;

class Schema
{
    
    private $ns;
    
    private $type;
    private $accessor;
    
    public function __construct(string $ns, DOMDocument $doc=null, Wsdl $wsdl=null)
    {
        $this->ns = $ns;
        $this->type = [];
        $this->accessor = [];
        if($doc != null) {
            foreach($doc->getElementsByTagName("complexType") as $complex) {
                $complexType = new ComplexType($complex, $this->ns);
                $this->type[$complexType->getName()] = $complexType;
            }
            foreach($doc->getElementsByTagName("simpleType") as $complex) {
                $complexType = new SimpleType($complex, $this->ns);
                $this->type[$complexType->getName()] = $complexType;
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
        $this->accessor[$name][] = $wsdl->getType($type_ns, $type);
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
    }
    
}

