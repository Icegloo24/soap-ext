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
                                    $info['name'] = $attr->nodeValue;
                                }
                                break;
                            case 'type':
                                if(strpos($attr->nodeValue, ':')) {
                                    $splitted = explode(':', $attr->nodeValue);
                                    $info['type_ns'] = $complex->lookupNamespaceUri($splitted[0]);
                                    $info['type'] = $splitted[1];
                                }else {
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
            //$childNodes = $info['name_ns']!=null?$element->getElementsByTagNameNS($info['name_ns'], $info['name']):$element->getElementsByTagName($info['name']);
            $childNodes = [];
            foreach($info['name_ns']!=null?$element->getElementsByTagNameNS($info['name_ns'], $info['name']):$element->getElementsByTagName($info['name']) as $child) {
                if($this->getTypeNs($child) == $info['complex']->getNamespace() && $this->getType($child) == $info['complex']->getName() && $child->parentNode === $element) {
                    $childNodes[] = $child;
                }
            }
            if(count($childNodes) < $info['min'] || count($childNodes) > ($info['max']!=0?$info['max']:9999)) {
                $valide = false;
                $validator->appendError(
                    "Error at Line '".$element->getLineNo().
                    "' :: Too Many/Few Nodes with Name:'".$info['name_ns'].":".$info['name'].
                    "' of Type:'".$info['complex']->getNamespace().":".$info['complex']->getName()."'!");
            }else {
                //echo "\n";
                foreach($childNodes as $childNode) {
                    $valide = $valide && $info['complex']->validate($childNode, $validator);
                }
            }
        }
        if($this->extension != null) {
            //echo "\n";
            $valide = $valide && $this->extension->validate($element, $validator);
        }
        
        return $valide;
    }
    
    
    public function link(Wsdl $wsdl)
    {
        if(isset($this->extension)) {
            $this->extension = $wsdl->getContent($this->extension['ns'], $this->extension['name']);
        }
        $sequences = [];
        //echo ','.$this->getName();
        foreach($this->sequences as $info) {
            //echo '+';
            $sequences[] = 
                [
                    'complex'=>$wsdl->getContent($info['type_ns']??$this->ns, $info['type']),
                    'min'=>$info['min']??0, 
                    'max'=>$info['max']??0,
                    'name'=>$info['name'],
                    'name_ns'=>$info['name_ns']??$this->ns
                ];
        }
        //echo '->'.json_encode($sequences);
        $this->sequences = $sequences;
    }
    
}

