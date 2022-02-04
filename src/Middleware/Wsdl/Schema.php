<?php
namespace SoapExt\Middleware\Wsdl;

use \DOMDocument;
use SoapExt\Wsdl;

class Schema
{
    
    private $ns;
    
    private $content;
    
    public function __construct(string $ns, DOMDocument $doc=null)
    {
        $this->ns = $ns;
        $this->content = [];
        if($doc != null) {
            foreach($doc->getElementsByTagName("complexType") as $complex) {
                $complexType = new ComplexType($complex, $this->ns);
                $this->content[$complexType->getName()] = $complexType;
            }
            foreach($doc->getElementsByTagName("simpleType") as $complex) {
                $complexType = new SimpleType($complex, $this->ns);
                $this->content[$complexType->getName()] = $complexType;
            }
        }else {
            foreach(BaseType::$DATA_TYPES as $type) {
                $this->content[$type] = new BaseType($type);
            }
        }
    }
    
    
    public function getNamespace():string
    {
        return $this->ns;
    }
    
    
    public function getContent(string $name):AbstractType
    {
        return $this->content[$name]??null;
    }
    
    
    public function link(Wsdl $wsdl)
    {
        foreach($this->content as &$complexType) {
            $complexType->link($wsdl);
        }
    }
    
}

