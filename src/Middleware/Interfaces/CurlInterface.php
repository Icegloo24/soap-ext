<?php
namespace SoapExt\Middleware\Interfaces;

interface CurlInterface {
    
    public function execute($location, $request = null, $requestHeaders = array()): bool;
    
    public function resolveUri(string $base, string $reroot): string;
    
    public function getLastResponse(): string;
    
    public function getLastResponseHeader(): string;
    
    public function getLastResponseBody(): string;
    
    public function getLastError(): string;
    
    public function getLastErrorMessage(): string;
    
}

