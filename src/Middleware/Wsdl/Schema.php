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
            $this->content['string'] = new BaseType('string');
            $this->content['decimal'] = new BaseType('decimal');
            $this->content['int'] = new BaseType('int');
            $this->content['integer'] = new BaseType('integer');
            $this->content['bool'] = new BaseType('bool');
            $this->content['boolean'] = new BaseType('boolean');
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

