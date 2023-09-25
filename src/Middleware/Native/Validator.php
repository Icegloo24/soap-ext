<?php
namespace SoapExt\Middleware\Native;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use SoapExt\Exceptions\SoapExtFault;

class Validator implements ValidatorInterface
{
    
    private $errors;
    
    public function __construct()
    {
        $this->errors = [];
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function appendError(string $error)
    {
        $this->errors[] = $error;
    }
    
    public function validate(string $request, Wsdl $wsdl): bool
    {
        $dom = new \DOMDocument('1.0');
        $dom->strictErrorChecking = true;
        try {
            if(!$dom->loadXML($request)) {
                $this->appendError('Could not load DOMDocument due to problems in Syntax');
                return false;
            }
        }catch(\Exception $e) {
            $this->appendError($e->getMessage());
            return false;
        }
        if($wsdl == null) {
            throw new SoapExtFault('DOM', 'WSDL should not be NULL');
        }
//         if($dom->getElementsByTagName('Body')->item(0) == null) {
//             echo "\n\n request: \n".$request;
//             echo "\n dom: \n".$dom->C14N();
//             return false;
//         }
        return $wsdl->validate($dom->getElementsByTagName('Body')->item(0), $this);
    }
    
}

