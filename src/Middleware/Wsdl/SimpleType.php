<?php
namespace SoapExt\Middleware\Wsdl;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use DOMElement;

class SimpleType extends AbstractType
{
    
    private $restriction;
    private $enumerations;
    
    public function __construct(DOMElement $complex, $ns)
    {
        parent::__construct($complex, $ns);
        foreach($complex->attributes as $attr) {
            if($attr->name == 'base') {
                if(strpos($attr->nodeValue, ':')) {
                    $splitted = explode(':', $attr->nodeValue);
                    $this->restriction = ['ns'=>$complex->lookupNamespaceUri($splitted[0]), 'name'=>$splitted[1]];
                }else {
                    $this->restriction = ['ns'=>$this->ns, 'name'=>$attr->nodeValue];
                }
            }
        }
        if(!isset($this->restriction)) {
            $node = $complex->getElementsByTagName('restriction')->item(0);
            if(null != $node) {
                foreach($node->attributes as $attr) {
                    if($attr->name == 'base') {
                        if(strpos($attr->nodeValue, ':')) {
                            $splitted = explode(':', $attr->nodeValue);
                            $this->restriction = ['ns'=>$complex->lookupNamespaceUri($splitted[0]), 'name'=>$splitted[1]];
                        }else {
                            $this->restriction = ['ns'=>$this->ns, 'name'=>$attr->nodeValue];
                        }
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
        $valide =   empty($this->enumerations) && 
                    $this->restriction->validate($element, $validator);
        
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
        if(isset($this->restriction)) {
            $this->restriction = $wsdl->getContent($this->restriction['ns'], $this->restriction['name']);
        }
    }

}

