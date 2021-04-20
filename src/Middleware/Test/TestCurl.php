<?php
namespace SoapExt\Middleware\Test;

use SoapExt\Middleware\Interfaces\CurlInterface;
use SoapExt\Middleware\Tools\UriResolver;

class TestCurl implements CurlInterface
{
    
    use UriResolver;
    
    private $ch;
    
    private $lastResponse="";
    
    private $lastResponseHeader="";
    
    private $lastResponseBody="";
    
    private $lastErrorMessage="";
    
    private $lastError="";
    
    public function __construct($options = array())
    {
        $this->ch = new TestCache();
    }

    public function execute($location, $request = null, $requestHeaders = []): bool
    {
        $this->lastResponseBody = $this->ch->getContent($location);
        return true;
    }
    
    public function resolveUri(string $base, string $reroot): string
    {
        return $this->resolve($base, $reroot);
    }
    
    public function getLastResponse(): string {
        return $this->lastResponse;
    }
    
    public function getLastResponseHeader(): string {
        return $this->lastResponseHeader;
    }
    
    public function getLastResponseBody(): string {
        return $this->lastResponseBody;
    }
    
    public function getLastError(): string {
        return $this->lastError;
    }
    
    public function getLastErrorMessage(): string {
        return $this->lastErrorMessage;
    }
    
    public function getLastRequestHeader(): string {
        return "";
    }
}

