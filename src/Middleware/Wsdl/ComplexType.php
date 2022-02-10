<?php
namespace SoapExt\Middleware\Wsdl;

use SoapExt\Wsdl;
use \DOMElement;
use \DOMAttr;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use SoapExt\Middleware\Tools\TypeExtracter;

class ComplexType extends AbstractType
{
    use TypeExtracter;
    
    private $extension;
    private $sequences;
    private $wsdl;
    
    public function __construct(DOMElement $complex, $ns)
    {
        parent::__construct($complex, $ns);
        /**@var DOMElement $node */
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
        /**@var DOMElement $node */
        $node = $complex->getElementsByTagName('sequence')->item(0);
        $this->sequences = [];
        if(null != $node) {
            foreach($node->childNodes as $elem) {
                if($elem->localName == 'element') {
                    $info = [];
                    foreach($elem->attributes as $attr) {
                        switch($attr->name) {
                            case 'name':
                                if(strpos($attr->nodeValue, ':')) {
                                    $splitted = explode(':', $attr->nodeValue);
                                    $info['name_ns'] = $complex->lookupNamespaceUri($splitted[0]);
                                    $info['name'] = $splitted[1];
                                }else {
                                    $info['name_ns'] = $this->getNamespace();
                                    $info['name'] = $attr->nodeValue;
                                }
                                break;
                            case 'type':
                                if(strpos($attr->nodeValue, ':')) {
                                    $splitted = explode(':', $attr->nodeValue);
                                    $info['type_ns'] = $complex->lookupNamespaceUri($splitted[0]);
                                    $info['type'] = $splitted[1];
                                }else {
                                    $info['type_ns'] = $this->getNamespace();
                                    $info['type'] = $attr->nodeValue;
                                }
                                break;
                            case 'minOccurs':
                                $info['min'] = intval($attr->nodeValue);
                                break;
                            case 'maxOccurs':
                                $info['max'] = intval($attr->nodeValue);
                                break;
                            default:
                        }
                    }
                    $this->sequences[] = $info;
                }
            }
        }
    }
    
    
    public function validate(DOMElement $element, ValidatorInterface $validator): bool
    {
        //echo $this->getName().'->';
        $valide = true;
        foreach($this->sequences as $info) {
            //echo json_encode($info);
            $childNodes = [];
            
            foreach($info['name_ns']!=null?$element->getElementsByTagNameNS($info['name_ns'], $info['name']):$element->getElementsByTagName($info['name']) as $child) {
                if($child->parentNode === $element) {
                    $accessors = $this->wsdl->getAccessor($this->extractNs($child), $this->extractName($child));
                    if(count($accessors) > 1) {
                        if($this->extractTypeNs($child) == null || $this->extractType($child) == null) {
                            $validator->appendError(
                                "Error at Line '".$child->getLineNo().
                                "' :: Node with Name:'".$this->extractNs($child).":".$this->extractName($child).
                                "' Type has multiple accessors and needs to be defined!");
                        }
                        $type = $this->wsdl->getType($this->extractTypeNs($child), $this->extractType($child));
                        if($info['complex'] == $type && $child->parentNode === $element) {
                            $childNodes[] = $child;
                        }
                    }elseif(count($accessors) == 1) {
                        $type = $accessors[0];
                        if($this->extractTypeNs($child) != null && $this->extractType($child) != null) {
                            $type = $this->wsdl->getType($this->extractTypeNs($child), $this->extractType($child));
                        }
                        $childNodes[] = $child;
                    }else {
                        $validator->appendError(
                            "Error at Line '".$child->getLineNo().
                            "' :: Node with Name:'".$this->extractNs($child).":".$this->extractName($child).
                            "' is not defined in Schema!");
                    }
                }
            }
            
            
            
            if(count($childNodes) < $info['min'] || count($childNodes) > ($info['max']!=0?$info['max']:9999)) {
                $valide = false;
                $validator->appendError(
                    "Error at Line '".$element->getLineNo().
                    "' :: Too Many/Few '".count($childNodes)."' Nodes with Name:'".$info['name_ns'].":".$info['name'].
                    "' of Type:'".$info['complex']->getNamespace().":".$info['complex']->getName()."'!");
            }else {
                foreach($childNodes as $childNode) {
                    $valide = $valide && $info['complex']->validate($childNode, $validator);
                }
            }
        }
        if($this->extension != null) {
            $valide = $valide && $this->extension->validate($element, $validator);
        }
        
        return $valide;
    }
    
    
    public function link(Wsdl $wsdl)
    {
        $this->wsdl = $wsdl;
        if(isset($this->extension)) {
            $this->extension = $wsdl->getType($this->extension['ns'], $this->extension['name']);
        }
        
        $sequences = [];
        foreach($this->sequences as $info) {
            $sequences[] = [
                'complex'=>$wsdl->getType($info['type_ns']??$this->ns, $info['type']),
                'min'=>$info['min']??0,
                'max'=>$info['max']??0,
                'name'=>$info['name'],
                'name_ns'=>$info['name_ns']??$this->ns
            ];
            $wsdl->appendAccessor($info['name_ns'], $info['name'], $info['type_ns'], $info['type']);
        }
        $this->sequences = $sequences;
    }
    
}

