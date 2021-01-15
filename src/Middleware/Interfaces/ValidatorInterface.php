<?php
namespace SoapExt\Middleware\Interfaces;

interface ValidatorInterface {
    
    public function validate(string $request, WsdlLoaderInterface $wsdl): bool;
    
    public function getErrors(): array;
    
}

