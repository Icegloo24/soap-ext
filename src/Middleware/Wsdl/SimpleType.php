<?php
namespace SoapExt\Middleware\Wsdl;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use DOMElement;

class SimpleType extends AbstractType
{
    
    private $extension;
    private $enumerations;
    
    public function __construct(DOMElement $complex, $ns)
    {
        parent::__construct($complex, $ns);
        $node = $complex->getElementsByTagName('extension')->item(0);
        if(null != $node) {
            foreach($node->attributes as $attr) {
                if($attr->name == 'base') {
                    if(strpos($attr->nodeValue, ':')) {
                        $splitted = explode(':', $attr->nodeValue);
                        $this->extension = ['ns'=>$complex->lookupNamespaceUri($splitted[0]), 'name'=>$splitted[1]];
                    }else {
                        $this->extension = ['ns'=>$this->ns, 'name'=>$attr->nodeValue];
                    }
                }
            }
        }
        $this->enumerations = [];
        foreach($complex->getElementsByTagName('enumeration') as $element) {
            foreach($element->attributes as $attr) {
                if($attr->name == 'value') {
                    $this->enumerations[] = $attr->nodeValue;
                }
            }
        }
    }
    
    public function validate(DOMElement $element, ValidatorInterface $validator): bool
    {
        $valide = false;
        
        foreach($this->enumerations as $enum) {
            if($enum == $element->nodeValue) {
                $valide = true;
            }
        }
        if(!$valide) {
            $validator->appendError(
                "Error at Line '".$element->getLineNo().
                "' :: Enum Value '$element->nodeValue' not allowed! Allowed are following Enums: ".json_encode($this->enumerations));
        }
        
        return $valide;
    }
    
    public function link(Wsdl $wsdl)
    {
        if(isset($this->extension)) {
            $this->extension = $wsdl->getContent($this->extension['ns'], $this->extension['name']);
        }
    }

}

