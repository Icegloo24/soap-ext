<?php
namespace SoapExt\Middleware\Interfaces;

use SoapExt\Wsdl;

interface ValidatorInterface {
    
    public function validate(string $request, Wsdl $wsdl): bool;
    
    public function getErrors(): array;
    
    public function appendError(string $error);
    
}

