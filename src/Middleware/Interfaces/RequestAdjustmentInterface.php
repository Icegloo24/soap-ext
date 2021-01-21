<?php
namespace SoapExt\Middleware\Interfaces;

interface RequestAdjustmentInterface {
    
    public function modifyRequest(string $request): string;
    
}

