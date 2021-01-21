<?php
namespace SoapExt\Middleware\Interfaces;

interface SignatureMakerInterface {
    
    public function sign(string $request): string;
    
}

